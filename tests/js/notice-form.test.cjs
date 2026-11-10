const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/notice-form.js'), 'utf8');

function setup(ok = true) {
    const events = {}, messages = [], calls = [];
    const button = {disabled: false};
    const error = {textContent: '', hidden: true, classList: {add() {error.hidden = true;}, remove() {error.hidden = false;}}, scrollIntoView() {}};
    const form = {action: '/admin/notices/42', querySelectorAll: () => [button], addEventListener: (name, callback) => {events[name] = callback;}};
    let pending = null;
    const editor = {uploadImages: async () => {calls.push('upload');}, save: () => calls.push('editor-save')};
    vm.runInNewContext(source, {
        document: {readyState: 'complete', getElementById: id => id === 'notification' ? form : error},
        window: {parent: {postMessage: (data, origin) => messages.push({data, origin})}, tinymce: {get: () => editor}},
        location: {origin: 'http://localhost'}, FormData: class {constructor(value) {assert.equal(value, form);}},
        fetch: async (url, options) => {calls.push({url, options}); if (pending) await pending; return {ok, json: async () => ok ? {message: 'Saved', id: 42} : {errors: {title: ['Title is required.']}}};},
    });
    return {events, messages, calls, button, error, setPending(value) {pending = value;}};
}

test('saving uploads editor images before submitting and notifies only the same-origin parent', async () => {
    const env = setup();
    await env.events.submit({preventDefault() {}});
    assert.deepEqual(env.calls.slice(0, 2), ['upload', 'editor-save']);
    assert.equal(env.calls[2].options.headers.Accept, 'application/json');
    assert.equal(env.calls[2].options.method, 'POST');
    assert.equal(env.messages.find(item => item.data.type === 'notice:saved').data.message, 'Saved');
    assert.ok(env.messages.every(item => item.origin === 'http://localhost'));
    assert.equal(env.button.disabled, false);
});

test('validation failures stay in the modal without a saved event', async () => {
    const env = setup(false);
    await env.events.submit({preventDefault() {}});
    assert.equal(env.error.hidden, false);
    assert.equal(env.error.textContent, 'Title is required.');
    assert.equal(env.messages.some(item => item.data.type === 'notice:saved'), false);
    assert.equal(env.button.disabled, false);
});

test('duplicate submissions are ignored while saving', async () => {
    const env = setup();
    let release;
    env.setPending(new Promise(resolve => {release = resolve;}));
    const first = env.events.submit({preventDefault() {}});
    await env.events.submit({preventDefault() {}});
    release(); await first;
    assert.equal(env.calls.filter(item => typeof item === 'object').length, 1);
});
