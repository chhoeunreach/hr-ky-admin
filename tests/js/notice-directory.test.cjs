const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/notice-directory.js'), 'utf8');

function setup(success = true) {
    const events = {}, messages = {}, modalEvents = {}, calls = [], history = [];
    const frame = {contentWindow: {}, addEventListener() {}};
    const modal = {dataset: {addTitle: 'Add Notice', editTitle: 'Edit Notice', failed: 'Failed', savedRefreshFailed: 'Saved but refresh failed'}, addEventListener: (name, callback) => {modalEvents[name] = callback;}};
    const block = {setAttribute() {}, removeAttribute() {}, replaceWith(value) {calls.push(['replace', value]);}};
    const replacement = {dataset: {directoryTotal: '31'}};
    const count = {textContent: '30'};
    const message = {};
    const search = {value: '', addEventListener() {}};
    const form = {action: '/admin/notices', elements: {namedItem: () => ({value: ''})}, addEventListener: (name, callback) => {events['form:' + name] = callback;}};
    const elements = {noticeFilterForm: form, noticeFormModal: modal, noticeFormFrame: frame, noticeFormLoading: {}, noticeFormModalTitle: {}, noticeDirectoryMessage: message, noticeSearch: search, 'notice-list-block': block};
    const document = {
        readyState: 'complete', getElementById: id => elements[id],
        querySelector: selector => selector.includes('data-directory-count') ? count : {querySelector: () => ({addEventListener() {}})},
        addEventListener: (name, callback) => {events[name] = callback;}, dispatchEvent: event => calls.push(['event', event.type]),
    };
    const location = {href: 'http://localhost/admin/notices?page=2&branch_id=1', origin: 'http://localhost'};
    vm.runInNewContext(source, {
        document, location, URL, URLSearchParams, AbortController, setTimeout, clearTimeout,
        window: {location, history: {replaceState: (_, __, url) => history.push(url)}, addEventListener: (name, callback) => {messages[name] = callback;}},
        bootstrap: {Modal: {getOrCreateInstance: () => ({show: () => calls.push(['show']), hide: () => calls.push(['hide'])})}},
        fetch: async url => {calls.push(['fetch', url]); return {ok: success, redirected: false, text: async () => 'html'};},
        DOMParser: class {parseFromString() {return {getElementById: () => replacement};}},
        CustomEvent: class {constructor(type) {this.type = type;}},
    });
    return {events, messages, modalEvents, calls, history, frame, elements, message, count, replacement, location};
}

test('add and edit use the modal without navigating away from the list', () => {
    const env = setup();
    const trigger = {dataset: {url: 'http://localhost/admin/notices/42/edit'}};
    env.events.click({preventDefault() {}, target: {closest: selector => selector === '[data-open-notice-form]' ? trigger : null}});
    assert.equal(env.frame.src, 'http://localhost/admin/notices/42/edit?notice_modal=1');
    assert.equal(env.elements.noticeFormModalTitle.textContent, 'Edit Notice');
    assert.ok(env.calls.some(call => call[0] === 'show'));
    const add = {href: 'http://localhost/admin/notices/create'};
    env.events.click({preventDefault() {}, target: {closest: selector => selector === '[data-open-notice-form]' ? add : null}});
    assert.equal(env.frame.src, 'http://localhost/admin/notices/create?notice_modal=1');
    assert.equal(env.elements.noticeFormModalTitle.textContent, 'Add Notice');
});

test('a trusted save refreshes only the list and retains the current filters and page', async () => {
    const env = setup();
    await env.messages.message({origin: env.location.origin, source: env.frame.contentWindow, data: {type: 'notice:saved', message: 'Saved'}});
    assert.equal(env.calls.find(call => call[0] === 'fetch')[1], env.location.href);
    assert.equal(env.calls.find(call => call[0] === 'replace')[1], env.replacement);
    assert.equal(env.count.textContent, '31');
    assert.equal(env.message.textContent, 'Saved');
    assert.ok(env.calls.some(call => call[0] === 'event' && call[1] === 'directory:updated'));
});

test('untrusted messages cannot close or refresh the modal', async () => {
    const env = setup();
    await env.messages.message({origin: 'http://evil.test', source: env.frame.contentWindow, data: {type: 'notice:saved'}});
    await env.messages.message({origin: env.location.origin, source: {}, data: {type: 'notice:saved'}});
    assert.equal(env.calls.length, 0);
});

test('a failed refresh reports that the notice was saved and leaves the current list intact', async () => {
    const env = setup(false);
    await env.messages.message({origin: env.location.origin, source: env.frame.contentWindow, data: {type: 'notice:saved', message: 'Saved'}});
    assert.equal(env.message.textContent, 'Saved but refresh failed');
    assert.equal(env.calls.some(call => call[0] === 'replace'), false);
});

test('Cancel closes the modal without refreshing and is ignored during a save', async () => {
    const env = setup();
    const send = data => env.messages.message({origin: env.location.origin, source: env.frame.contentWindow, data});
    await send({type: 'notice:saving', saving: true});
    await send({type: 'notice:cancel'});
    assert.equal(env.calls.length, 0);
    await send({type: 'notice:saving', saving: false});
    await send({type: 'notice:cancel'});
    assert.equal(env.calls.length, 1);
    assert.equal(env.calls[0][0], 'hide');
});
