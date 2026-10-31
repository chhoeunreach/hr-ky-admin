const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-salary-history-modal.js'), 'utf8');
const flush = () => new Promise(setImmediate);
const response = (html = 'table', ok = true, message = 'Saved') => ({ok, text: async () => html, json: async () => ({message})});
function setup(responder) {
    const events = {}, calls = {show: 0, changed: 0}, requests = [];
    const body = {replaceChildren(content) {this.content = content;}, querySelector: () => null};
    const status = {}, retry = {hidden: true, addEventListener(type, handler) {events.retry = handler;}}, panel = {setAttribute(key, value) {this[key] = value;}};
    const modal = {dataset: {loading: 'Loading', loadError: 'Unable to load'},
        querySelector: selector => ({'[data-history-body]': body, '[data-history-status]': status, '[data-history-retry]': retry, '.modal-body': panel}[selector]),
        addEventListener(type, handler) {events[type] = handler;}};
    const bootstrap = {Modal: {getOrCreateInstance: () => ({show() {calls.show++;}})}};
    vm.runInNewContext(source, {bootstrap, URL, AbortController,
        window: {bootstrap, location: {href: 'https://hr.test/admin/employee-salaries', origin: 'https://hr.test'}},
        document: {getElementById: () => modal, addEventListener(type, handler) {events['document.'+type] = handler;},
            dispatchEvent() {calls.changed++;}},
        DOMParser: class {parseFromString(html) {return {querySelector: () => html === 'login' ? null : {html}};}},
        CustomEvent: class {}, FormData: class {},
        fetch: (url, options) => {requests.push({url, options}); return responder ? responder(url, options, requests.length) : Promise.resolve(response());},
    });
    const open = () => {
        const event = {target: {closest: () => ({href: '/admin/employee-salaries/salary-increment-history/20'})}, preventDefault() {this.prevented = true;}};
        events['document.click'](event); return event;
    };
    return {events, calls, requests, body, status, retry, panel, open};
}
test('history loads only table content into a modal without page navigation', async () => {
    const state = setup();
    assert.equal(state.open().prevented, true); await flush();
    assert.equal(state.calls.show, 1);
    assert.equal(new URL(state.requests[0].url).searchParams.get('salary_history_modal'), '1');
    assert.equal(state.body.content.html, 'table');
    assert.equal(state.panel['aria-busy'], 'false');
});
test('failed history requests provide retry and closing aborts pending loads', async () => {
    const state = setup(async (_url, _options, count) => count === 1 ? response('', false, 'Forbidden') : response());
    state.open(); await flush();
    assert.equal(state.status.textContent, 'Forbidden');
    assert.equal(state.retry.hidden, false);
    await state.events.retry();
    assert.equal(state.body.content.html, 'table');
    state.events['hidden.bs.modal']();
    assert.equal(state.requests[1].options.signal.aborted, true);
    assert.equal(state.body.content, undefined);
});
test('edit and delete panels load inside the same history modal', async () => {
    const state = setup(); state.open(); await flush();
    let prevented = false;
    state.events.click({target: {closest: selector => selector.includes('panel') ? {href: '/admin/employee-salaries/salary-increment-history/20/1/edit'} : null}, preventDefault() {prevented = true;}});
    await flush();
    assert.equal(prevented, true);
    assert.ok(state.requests[1].url.includes('/1/edit'));
    assert.equal(state.calls.show, 1);
});
test('history saves refresh the table and notify the salary list', async () => {
    const state = setup(); state.open(); await flush();
    const error = {}, button = {};
    const form = {action: '/admin/history/20/1', dataset: {}, matches: () => true, querySelectorAll: () => [button], querySelector: () => error};
    let prevented = false;
    await state.events.submit({target: form, preventDefault() {prevented = true;}});
    assert.equal(prevented, true);
    assert.equal(state.requests[1].options.method, 'POST');
    assert.equal(state.calls.changed, 1);
    assert.equal(state.status.textContent, 'Saved');
    assert.equal(button.disabled, false);
});
test('invalid edits remain in the form with an error and no list refresh', async () => {
    const state = setup(async (_url, _options, count) => count === 2 ? response('', false, 'Invalid remark') : response());
    state.open(); await flush();
    const error = {}, form = {action: '/admin/history/20/1', dataset: {}, matches: () => true, querySelectorAll: () => [], querySelector: () => error};
    await state.events.submit({target: form, preventDefault() {}});
    assert.equal(error.textContent, 'Invalid remark');
    assert.equal(state.calls.changed, 0);
    assert.equal(form.dataset.saving, '0');
});

test('full review edits recalculate annual increments, including decreases and zero-base records', () => {
    const state = setup();
    const base = {value: '12000'}, revised = {value: '13200'}, increment = {}, percent = {};
    const form = {querySelector: selector => ({'[name="base_salary"]': base, '[name="revised_salary"]': revised, '[data-review-increment]': increment, '[data-review-percent]': percent}[selector])};
    const input = () => state.events.input({target: {closest: () => form}});
    input();
    assert.equal(increment.textContent, '1200.00');
    assert.equal(percent.textContent, '10.00%');
    revised.value = '11000'; input();
    assert.equal(increment.textContent, '-1000.00');
    assert.equal(percent.textContent, '-8.33%');
    base.value = '20000'; revised.value = '20201'; input();
    assert.equal(percent.textContent, '1.01%');
    revised.value = '19799'; input();
    assert.equal(percent.textContent, '-1.01%');
    base.value = '0'; revised.value = '100'; input();
    assert.equal(percent.textContent, '0.00%');
    revised.value = ''; input();
    assert.equal(increment.textContent, '-');
    assert.equal(percent.textContent, '-');
});
