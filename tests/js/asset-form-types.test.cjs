const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/asset-form-types.js'), 'utf8');
const flush = async () => {for (let i = 0; i < 6; i++) await new Promise(resolve => setImmediate(resolve));};
function setup(fetcher = async () => ({types: [{id: 7, name: 'Phone'}]}), config = {}) {
    const button = {disabled: false};
    const branch = {value: '2', addEventListener(type, handler) {this[type] = handler;}};
    const type = {value: '7', options: [{value: '7', text: 'Phone'}], disabled: false,
        closest: () => ({querySelectorAll: () => [button]}),
        replaceChildren(...options) {this.options = options; this.value = '';}, add(option) {this.options.push(option);}};
    const status = {};
    const calls = [];
    vm.runInNewContext(source, {
        document: {readyState: 'complete', getElementById: id => ({branch_id: branch, type, assetTypeStatus: status,
            assetTypeConfig: {textContent: JSON.stringify({url: '/types', initialType: '7', placeholder: 'Select type', empty: 'No types', error: 'Load failed', loading: 'Loading', ...config})}}[id])},
        window: {}, Option: function (text, value) {Object.assign(this, {text, value});},
        fetch: async url => {calls.push(url); return {ok: true, json: async () => fetcher(url)};},
    });
    return {branch, type, status, button, calls};
}

test('loads types using the selected branch even when an account has no default branch', async () => {
    const state = setup(); await flush();
    assert.deepEqual(state.calls, ['/types/2']);
    assert.equal(state.type.value, '7');
    assert.equal(state.type.disabled, false);
    assert.equal(state.button.disabled, false);
});

test('changing branch clears the old type and ignores late responses', async () => {
    const pending = new Map();
    const state = setup(url => new Promise(resolve => pending.set(url, resolve)));
    await flush();
    state.branch.value = '3'; state.branch.change(); await flush();
    pending.get('/types/3')({types: [{id: 9, name: 'New type'}]}); await flush();
    pending.get('/types/2')({types: [{id: 7, name: 'Old type'}]}); await flush();
    assert.equal(state.type.value, '');
    assert.equal(state.type.options[1].value, '9');
});

test('a failed refresh preserves the server-rendered current type', async () => {
    const state = setup(async () => {throw new Error('offline');}); await flush();
    assert.equal(state.type.value, '7');
    assert.equal(state.type.options[0].text, 'Phone');
    assert.equal(state.status.textContent, 'Load failed');
    assert.equal(state.button.disabled, false);
});

test('edit refresh includes the asset id so the saved legacy type remains selectable', async () => {
    const state = setup(undefined, {assetId: 14}); await flush();
    assert.deepEqual(state.calls, ['/types/2?asset_id=14']);
    assert.equal(state.type.value, '7');
    assert.equal(state.button.disabled, false);
});
