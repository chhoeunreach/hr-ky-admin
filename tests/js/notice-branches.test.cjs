const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const blade = fs.readFileSync(require('node:path').join(__dirname, '../../resources/views/admin/notice/common/scripts.blade.php'), 'utf8');
const source = blade.slice(blade.indexOf('        let employeeLoadVersion'), blade.indexOf('        const initializeDropdowns'))
    .replace(/\{\{[^}]*\}\}/g, 'placeholder');
function setup(ajax) {
    const options = [];
    const select = {empty() {options.length = 0; return this;}, append(option) {options.push(option); return this;}, trigger() {}, prop() {}};
    const $ = () => select;
    $.ajax = ajax;
    const context = {receiverUserIds: ['2'], $, Option: function (name, id, defaultSelected, selected) {Object.assign(this, {name, id, selected});}};
    vm.runInNewContext(source + '\nthis.load = loadEmployees;', context);
    return {load: context.load, options};
}
test('combines branches, deduplicates employees and restores recipients', async () => {
    const env = setup(async ({url}) => ({users: url.endsWith('/1') ? [{id: 1, name: 'A'}, {id: 2, name: 'B'}] : [{id: 2, name: 'B'}, {id: 3, name: 'C'}]}));
    await env.load(['1', '2']);
    assert.deepEqual(env.options.map(option => option.id), [1, 2, 3]);
    assert.deepEqual(env.options.map(option => option.selected), [false, true, false]);
    await env.load([]);
    assert.equal(env.options.length, 0);
});
test('ignores late responses from previous branch selections', async () => {
    let release;
    const env = setup(({url}) => url.endsWith('/1') ? new Promise(resolve => {release = resolve;}) : Promise.resolve({users: [{id: 2, name: 'B'}]}));
    const previous = env.load(['1']);
    await env.load(['2']);
    release({users: [{id: 1, name: 'A'}]});
    await previous;
    assert.deepEqual(env.options.map(option => option.id), [2]);
});
test('failed branch loading does not leave employees from another selection', async () => {
    const env = setup(async () => {throw new Error('Failed');});
    env.options.push({id: 99});
    await env.load(['1']);
    assert.equal(env.options.length, 1);
    assert.match(env.options[0], /option disabled/);
});
