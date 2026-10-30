const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-profile-directory.js'), 'utf8');
const flush = () => new Promise(setImmediate);
const response = label => ({ok: true, text: async () => label});
function setup() {
    const events = {}, requests = [], history = [], timers = new Map();
    let nextTimer = 0;
    const element = name => ({name, value: '', style: {}, listeners: {},
        addEventListener(type, handler) {this.listeners[type] = handler;},
        classList: {hidden: true, add() {this.hidden = true;}, remove() {this.hidden = false;}},
        focus() {this.focused = true;}});
    const input = element('search'), clear = element(), error = element(), loading = element();
    const status = {...element('employment_status'), value: 'active'};
    const branch = {...element('branch_id'), value: '2'};
    const count = {...element('per_page'), value: '25'};
    const fields = [input, status, branch, count];
    const form = {...element(), action: 'http://hr.test/admin/employees/profile-directory', elements: fields,
        querySelector(selector) {return fields.find(f => selector.includes(`"${f.name}"`));},
        appendChild(field) {fields.push(field);}};
    const block = label => ({label, attributes: {},
        setAttribute(name, value) {this.attributes[name] = value;},
        querySelectorAll: () => [], replaceWith(next) {root = next;}});
    let root = block('original');
    const location = {href: form.action + '?page=4', origin: 'http://hr.test', pathname: '/admin/employees/profile-directory'};
    vm.runInNewContext(source, {
        URL, URLSearchParams, AbortController, Event: class {},
        FormData: class {constructor() {return fields.filter(f => f.name).map(f => [f.name, f.value]);}},
        DOMParser: class {parseFromString(html) {return {getElementById: () => html === 'invalid' ? null : block(html)};}},
        document: {body: {}, getElementById: id => ({employeeProfileFilters: form, employeeProfileSearch: input,
            employeeProfileSearchClear: clear, employeeProfileSearchError: error, employeeProfileSearchLoading: loading,
            employeeProfileResults: root}[id]), addEventListener(type, handler) {events[type] = handler;}, createElement: () => element()},
        window: {location, history: {replaceState(a,b,href) {history.push(['replace', href]);}, pushState(a,b,href) {history.push(['push',href]);}},
            addEventListener(type, handler) {events[type] = handler;},
            setTimeout(fn) {timers.set(++nextTimer,fn);return nextTimer;}, clearTimeout(id) {timers.delete(id);}},
        fetch: (url, options) => new Promise((resolve,reject) => requests.push({url,options,resolve,reject})),
    });
    return {input, clear, form, error, loading, requests, history, events,
        root: () => root,
        type(value) {input.value = value; input.listeners.input();},
        runTimer() {for (const fn of [...timers.values()]) fn();timers.clear();},
        submit(submitter) {let prevented = false;form.listeners.submit({submitter, preventDefault() {prevented=true;}});return prevented;},
    };
}

test('typing stays enabled and only the employee results are replaced', async () => {
    const app = setup();app.input.focus();app.type('Sok');app.runTimer();
    assert.equal(app.requests.length,1);
    assert.equal(app.input.disabled,undefined);
    assert.equal(app.loading.classList.hidden,false);
    app.requests[0].resolve(response('Sok results'));await flush();
    assert.equal(app.root().label,'Sok results');
    assert.equal(app.input.value,'Sok');assert.equal(app.input.focused,true);
    assert.equal(app.loading.classList.hidden,true);
    const url = new URL(app.requests[0].url);
    assert.equal(url.searchParams.get('branch_id'),'2');
    assert.equal(url.searchParams.get('employment_status'),'active');
    assert.equal(url.searchParams.has('page'),false);
    assert.equal(app.requests[0].options.headers['X-Employee-Profile-Results'],'1');
});

test('debounces consecutive keystrokes into one search', () => {
    const app = setup();app.type('S');app.type('So');app.type('Sok');app.runTimer();
    assert.equal(app.requests.length,1);
    assert.equal(new URL(app.requests[0].url).searchParams.get('search'),'Sok');
});

test('new typing immediately aborts in-flight work and ignores its late response before the next debounce', async () => {
    const app = setup();app.type('Sok');app.runTimer();
    app.type('Sok Dara');assert.equal(app.requests[0].options.signal.aborted,true);
    app.requests[0].resolve(response('outdated'));await flush();assert.equal(app.root().label,'original');
    app.runTimer();app.requests[1].resolve(response('latest'));await flush();
    assert.equal(app.root().label,'latest');assert.equal(app.input.value,'Sok Dara');
    assert.equal(app.history.length,1);
});

test('late requests cannot replace newer results or change their URL', async () => {
    const app = setup();app.type('Sok');app.runTimer();app.type('Dara');app.runTimer();
    app.requests[1].resolve(response('new'));await flush();
    app.requests[0].resolve(response('old'));await flush();
    assert.equal(app.root().label,'new');assert.equal(app.history.length,1);
    assert.equal(new URL(app.history[0][1]).searchParams.get('search'),'Dara');
});

test('clear cancels the pending debounce and reloads the first page without losing focus', () => {
    const app = setup();app.type('Sok');app.clear.listeners.click();app.runTimer();
    assert.equal(app.requests.length,1);assert.equal(app.input.value,'');assert.equal(app.input.focused,true);
    assert.equal(app.clear.style.display,'none');
    assert.equal(new URL(app.requests[0].url).searchParams.has('page'),false);
});

test('does not search incomplete IME composition', () => {
    const app = setup();app.input.listeners.compositionstart();app.type('ស');app.runTimer();assert.equal(app.requests.length,0);
    app.input.value='សុខ';app.input.listeners.compositionend();app.runTimer();assert.equal(app.requests.length,1);
});

test('Enter submits immediately while the attendance report keeps its existing handler', () => {
    const app = setup();app.type('Sok');assert.equal(app.submit(),true);app.runTimer();assert.equal(app.requests.length,1);
    assert.equal(app.submit({hasAttribute: name => name === 'data-employee-report'}),false);
    assert.equal(app.requests.length,1);
});

for (const [label, result] of [['server failure',{ok:false}],['login redirect',{ok:true,redirected:true}],['invalid fragment',response('invalid')]]) {
    test(`${label} shows an error and leaves the current results and search text intact`, async () => {
        const app = setup();app.type('Dara');app.runTimer();app.requests[0].resolve(result);await flush();
        assert.equal(app.error.classList.hidden,false);assert.equal(app.root().label,'original');
        assert.equal(app.input.value,'Dara');assert.equal(app.loading.classList.hidden,true);
    });
}

test('pagination updates results without a page navigation', async () => {
    const app = setup();let prevented = false;
    app.input.value = 'Sok';
    app.events.click({target:{closest:()=>({href:app.form.action+'?search=Sok&page=2', hasAttribute:()=>false})},preventDefault(){prevented=true;}});
    assert.equal(prevented,true);assert.equal(new URL(app.requests[0].url).searchParams.get('page'),'2');
    app.requests[0].resolve(response('page2'));await flush();assert.equal(app.root().label,'page2');
    assert.equal(app.history[0][0],'push');
});

test('clicking a previous result page while typing uses the new search and resets the page', () => {
    const app = setup();app.type('Dara');
    app.events.click({target:{closest:()=>({href:app.form.action+'?search=Sok&page=2', hasAttribute:()=>false})},preventDefault(){}});
    app.runTimer();assert.equal(app.requests.length,1);
    const url = new URL(app.requests[0].url);
    assert.equal(url.searchParams.get('search'),'Dara');assert.equal(url.searchParams.has('page'),false);
});

test('sorting uses the current search and selected filters', async () => {
    const app = setup();app.type('Dara');
    app.events.click({target:{closest:()=>({href:app.form.action+'?search=Sok&name_direction=asc',hasAttribute:()=>true})},preventDefault(){}});
    const url = new URL(app.requests[0].url);
    assert.equal(url.searchParams.get('search'),'Dara');assert.equal(url.searchParams.get('branch_id'),'2');
    assert.equal(url.searchParams.get('name_direction'),'asc');
    app.requests[0].resolve(response('sorted'));await flush();
    assert.equal(app.form.elements.find(f=>f.name==='name_direction').value,'asc');
});
