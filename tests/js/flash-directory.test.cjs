const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/assets/js/flash-directory.js'), 'utf8');
function setup(ok = true) {
    const events = {}, calls = [], history = [];
    const q = {value: ''};
    const form = {action: 'http://localhost/flashes', elements: {namedItem: () => q}, addEventListener: (name, callback) => {events['form:' + name] = callback;}};
    const search = {value: 'Office', addEventListener: (name, callback) => {events['search:' + name] = callback;}};
    const block = {setAttribute() {}, removeAttribute() {}, replaceWith: value => calls.push(['replace', value])};
    const replacement = {dataset: {directoryTotal: '31'}};
    const message = {dataset: {failed: 'Load failed'}, classList: {add() {}, remove() {}}};
    const count = {};
    const tools = {querySelector: () => ({addEventListener: (name, callback) => {events['size:' + name] = callback;}})};
    const elements = {flashFilterForm: form, flashSearch: search, flashDirectoryMessage: message, 'flash-list-block': block};
    const location = {href: 'http://localhost/flashes?page=3'};
    const context = {
        document: {readyState: 'complete', getElementById: id => elements[id], querySelector: selector => selector.includes('data-directory-count') ? count : tools,
            addEventListener: (name, callback) => {events[name] = callback;}, dispatchEvent: event => calls.push(['event', event.type])},
        window: {history: {replaceState: (_, __, url) => history.push(url)}}, location, URL, URLSearchParams, AbortController, setTimeout, clearTimeout,
        FormData: class {constructor() {return [['q', q.value], ['status', 'live'], ['media_type', 'text'], ['per_page', '50']];}},
        fetch: async url => {calls.push(['fetch', url]); return {ok, redirected: false, text: async () => 'html'};},
        DOMParser: class {parseFromString() {return {getElementById: () => replacement, querySelector: () => null};}},
        CustomEvent: class {constructor(type) {this.type = type;}},
    };
    vm.runInNewContext(source, context);
    return {events, calls, history, message, count, replacement};
}
const settle = () => new Promise(resolve => setImmediate(resolve));
test('filter submissions retain search and filters, reset page and notify column tools', async () => {
    const env = setup();
    env.events['form:submit']({preventDefault() {}});
    await settle();
    const url = new URL(env.calls.find(call => call[0] === 'fetch')[1]);
    assert.equal(url.searchParams.get('q'), 'Office');
    assert.equal(url.searchParams.get('media_type'), 'text');
    assert.equal(url.searchParams.get('status'), 'live');
    assert.equal(url.searchParams.get('per_page'), '50');
    assert.equal(url.searchParams.has('page'), false);
    assert.equal(env.count.textContent, '31');
    assert.ok(env.calls.some(call => call[0] === 'event' && call[1] === 'directory:updated'));
});
test('failed refresh keeps the current table and reports an error', async () => {
    const env = setup(false);
    env.events['size:change']();
    await settle();
    assert.equal(env.calls.some(call => call[0] === 'replace'), false);
    assert.equal(env.message.textContent, 'Load failed');
    assert.equal(env.history.length, 0);
});
test('pagination fetches the selected page without leaving the directory', async () => {
    const env = setup();
    const href = 'http://localhost/flashes?status=live&page=2';
    let prevented = false;
    env.events.click({target: {closest: () => ({href})}, preventDefault() {prevented = true;}});
    await settle();
    assert.equal(prevented, true);
    assert.equal(env.history[0], href);
});
