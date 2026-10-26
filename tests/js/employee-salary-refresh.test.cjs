const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-salary-refresh.js'), 'utf8');
const flush = () => new Promise(setImmediate);
function setup(fetchResponse) {
    const events = {form: {}, page: {}, document: {}, window: {}}, requests = [], dispatched = [], history = [];
    const feedback = {dataset: {}, textContent: ''};
    const search = {id: 'salarySearch', name: 'q', value: 'Sam', selectionStart: 2, selectionEnd: 2,
        focus() {this.focused = true;}, setSelectionRange(start, end) {this.range = [start, end];}};
    const count = {name: 'per_page', value: '25'};
    const branch = {name: 'branch_id', value: '1'};
    const form = {action: '/admin/employee-salaries', elements: [search, count, branch],
        addEventListener(type, handler) {events.form[type] = handler;}};
    const block = label => ({label, attributes: {}, scroller: {scrollLeft: 42}, contains: node => node === search,
        setAttribute(key, value) {this.attributes[key] = value;}, querySelectorAll: () => [],
        querySelector() {return this.scroller;}, replaceWith(next) {root = next;}});
    let root = block('original');
    const page = {dataset: {salaryRefreshError: 'Refresh failed', salaryRefreshSuccess: 'Refreshed'},
        querySelector: () => feedback, contains: () => true,
        addEventListener(type, handler) {events.page[type] = handler;}};
    const location = {href: 'http://hr.test/admin/employee-salaries?q=Sam&branch_id=1', origin: 'http://hr.test'};
    vm.runInNewContext(source, {
        document: {activeElement: search, querySelector: () => page,
            getElementById: id => ({employeeSalaryFilters: form, employeeSalaryResults: root, salarySearch: search}[id]),
            addEventListener(type, handler) {events.document[type] = handler;},
            dispatchEvent(event) {dispatched.push(event);}},
        window: {location, history: {pushState(_state, _title, href) {history.push(href); location.href = href;}},
            addEventListener(type, handler) {events.window[type] = handler;}, feather: {replace() {}}},
        URL, URLSearchParams, AbortController, WeakSet,
        FormData: class {constructor(target) {return (target.elements || []).map(field => [field.name, field.value]);}},
        CustomEvent: class {constructor(type, options = {}) {this.type = type; Object.assign(this, options);}},
        DOMParser: class {parseFromString(html) {return {getElementById: () => html === 'login' ? null : block(html)};}},
        fetch: (url, options) => {
            requests.push({url, options});
            return fetchResponse ? fetchResponse(url, options, requests.length) : Promise.resolve(response('updated'));
        },
    });
    const event = extra => ({preventDefault() {this.prevented = true;}, ...extra});
    return {events, requests, dispatched, history, location, feedback, form, search, count, branch,
        root: () => root, event, submit: async () => {const e = event(); events.form.submit(e); await flush(); return e;}};
}
function response(text, ok = true, message = 'Rejected') {
    return {ok, text: async () => text, json: async () => ({message})};
}
test('filters refresh just the results, retain focus and scroll, and update history', async () => {
    const state = setup();
    const event = await state.submit();
    assert.equal(event.prevented, true);
    assert.equal(state.root().label, 'updated');
    assert.equal(state.requests[0].options.headers['X-Salary-Fragment'], '1');
    assert.equal(new URL(state.requests[0].url).searchParams.get('q'), 'Sam');
    assert.equal(state.search.focused, true);
    assert.deepEqual(state.search.range, [2, 2]);
    assert.equal(state.root().scroller.scrollLeft, 42);
    assert.equal(state.history.length, 1);
    assert.ok(state.dispatched.some(event => event.type === 'employee-salary:rendered'));
    assert.equal(state.root().attributes['aria-busy'], 'false');
});
test('pagination and Reset stay in the page, and modified clicks keep browser behavior', async () => {
    const state = setup();
    const link = {href: 'http://hr.test/admin/employee-salaries?q=Sam&page=2'};
    const event = state.event({target: {closest: selector => selector.includes('footer') ? link : null}});
    state.events.page.click(event); await flush();
    assert.equal(event.prevented, true);
    assert.equal(new URL(state.history[0]).searchParams.get('page'), '2');
    const reset = state.event({target: {closest: selector => selector.includes('reset') ? {href: 'http://hr.test/admin/employee-salaries'} : null}});
    state.events.page.click(reset); await flush();
    assert.equal(state.search.value, '');
    assert.equal(state.count.value, '25');
    const modified = state.event({ctrlKey: true, target: event.target});
    state.events.page.click(modified);
    assert.equal(modified.prevented, undefined);
});
test('failed requests and login responses preserve the existing table and show an error', async () => {
    for (const result of [response('', false, 'Forbidden'), response('login')]) {
        const state = setup(async () => result);
        await state.submit();
        assert.equal(state.root().label, 'original');
        assert.equal(state.feedback.dataset.error, 'true');
        assert.equal(state.root().attributes['aria-busy'], 'false');
        assert.equal(state.history.length, 0);
    }
});
test('a superseded request cannot replace newer search results', async () => {
    const resolvers = [];
    const state = setup(() => new Promise(resolve => resolvers.push(resolve)));
    state.events.form.submit(state.event());
    state.search.value = 'New';
    state.events.form.submit(state.event());
    assert.equal(state.requests[0].options.signal.aborted, true);
    resolvers[1](response('new')); await flush();
    resolvers[0](response('old')); await flush();
    assert.equal(state.root().label, 'new');
});
test('typing during a request stays intact until the next search refresh', async () => {
    let resolve;
    const state = setup(() => new Promise(done => {resolve = done;}));
    state.events.form.submit(state.event());
    state.search.value = 'Still typing';
    resolve(response('stale')); await flush();
    assert.equal(state.root().label, 'original');
    assert.equal(state.search.value, 'Still typing');
});
test('confirmed salary mutations return feedback and refresh the block', async () => {
    const state = setup(async (_url, _options, count) => count === 1 ? response('', true, 'Saved') : response('after-save'));
    const action = {action: 'http://hr.test/admin/employee-salaries/20/delete', matches: () => true,
        elements: [{name: '_method', value: 'DELETE'}, {name: '_token', value: 'csrf-token'}]};
    const event = state.event({target: action});
    await state.events.document['employee-salary:submit'](event);
    assert.equal(event.prevented, true);
    assert.equal(state.requests[0].options.method, 'POST');
    assert.equal(state.root().label, 'after-save');
    assert.equal(state.feedback.textContent, 'Saved');
    assert.equal(state.history.length, 0);
});
test('browser Back restores the block and filter controls without adding history', async () => {
    const state = setup();
    state.location.href = 'http://hr.test/admin/employee-salaries?q=Previous&per_page=10';
    state.events.window.popstate(); await flush();
    assert.equal(state.search.value, 'Previous');
    assert.equal(state.count.value, '10');
    assert.equal(state.history.length, 0);
});
test('a failed cycle update restores the saved value and enables the control', async () => {
    const state = setup(async () => response('', false, 'Cycle rejected'));
    const cycle = {value: 'weekly', dataset: {current: 'monthly'}};
    const action = {action: '/cycle', matches: () => true, closest: () => ({querySelector: () => cycle})};
    await state.events.document['employee-salary:submit'](state.event({target: action}));
    assert.equal(cycle.value, 'monthly');
    assert.equal(cycle.disabled, false);
    assert.equal(state.feedback.textContent, 'Cycle rejected');
    assert.equal(state.root().label, 'original');
});
