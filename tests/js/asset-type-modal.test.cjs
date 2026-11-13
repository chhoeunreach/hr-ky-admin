const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../resources/views/admin/assetManagement/types/common/scripts.blade.php'), 'utf8')
    .replace(/<\/?script>/g, '').replace(/@lang\('[^']*'\)/g, 'Translated').replace(/@json\([^\n]+\)/g, '"Translated"').replace(/\{\{[^}]+\}\}/g, '/admin/asset-types');
function setup() {
    const events = {}, fields = {}, calls = [];
    const document = {getElementById: id => ({id})};
    function $(key) {
        if (key?.domId) key = key.domId;
        if (typeof key === 'object' && key !== document) return {data: () => key.id};
        fields[key] ||= {value: '', attributes: {}};
        const state = fields[key];
        const api = {
            ready(callback) {callback();}, change() {}, click() {}, trigger() {return api;},
            on(name, selector, callback) {events[key + ':' + name + ':' + (callback ? selector : '')] = callback || selector; return api;},
            val(value) {if (arguments.length) {state.value = value; return api;} return state.value;},
            text(value) {state.text = value; return api;},
            attr(name, value) {if (arguments.length === 2) {state.attributes[name] = value; return api;} return state.attributes[name];},
            find() {return {prop() {}};},
        };
        return api;
    }
    $.ajaxSetup = () => {};
    $.ajax = options => {
        calls.push(options);
        if (options.type === 'GET') options.success({assetTypeDetail: {id: 7, name: 'Laptop', branch_id: 2}});
        else {options.beforeSend?.(); options.success({message: 'Saved'}); options.complete?.();}
    };
    vm.runInNewContext(source, {document, $, bootstrap: {Modal: {getOrCreateInstance: modal => ({show() {calls.push(['show', modal.id]);}, hide() {calls.push(['hide', modal.id]);}})}},
        Swal: {fire: options => {calls.push(['notice', options]);}}, location: {reload() {}}, FormData: class {}});
    return {events, fields, calls};
}
test('asset type create and edit dialogs use the Bootstrap 5 modal API', () => {
    const env = setup();
    env.events['[object Object]:click:.create-assetType']();
    assert.ok(env.calls.some(call => Array.isArray(call) && call[0] === 'show' && call[1] === 'assetTypeModal'));
    assert.equal(env.fields['#formMethod'].value, 'POST');
    env.events['[object Object]:click:.edit-assetType'].call({id: 7});
    assert.equal(env.fields['#formMethod'].value, 'PUT');
    assert.equal(env.fields['#name'].value, 'Laptop');
    assert.equal(env.fields['#branch_id'].value, 2);
    assert.equal(env.fields['#assetTypeForm'].attributes.action, '/admin/asset-types/7');
});

test('successful asset type updates close the dialog and preserve the edit method', () => {
    const env = setup();
    env.events['[object Object]:click:.edit-assetType'].call({id: 7});
    env.events['#assetTypeForm:submit:'].call({domId: '#assetTypeForm'}, {preventDefault() {}});
    const request = env.calls.find(call => call.type === 'POST');
    assert.equal(request.url, '/admin/asset-types/7');
    assert.ok(env.calls.some(call => Array.isArray(call) && call[0] === 'hide' && call[1] === 'assetTypeModal'));
});
