const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/app-link-form.js'), 'utf8');
function setup({platformValue = 'telegram', selected = 'channel', required = true} = {}) {
    const events = {}, wrapper = {};
    const platform = {value: platformValue, addEventListener: (type, callback) => {events[type] = callback;}};
    const select = {dataset: {platformId: 'platform', selected, accountRequired: required ? '1' : ''}, options: [{textContent: 'Choose type'}],
        replaceChildren(option) {this.options = [option];}, add(option) {this.options.push(option);}, closest: () => wrapper};
    const toggleEvents = {}, clearEvents = {};
    const password = {type: 'password'}, toggle = {setAttribute() {}, addEventListener(type, callback) {toggleEvents[type] = callback;}};
    const clear = {checked: false, addEventListener(type, callback) {clearEvents[type] = callback;}};
    vm.runInNewContext(source, {window: {}, document: {
        currentScript: {dataset: {show: 'Show', hide: 'Hide'}},
        getElementById: id => ({platform, password, appLinkAccountTypes: {textContent: JSON.stringify({
            telegram: {personal: 'Personal', group: 'Group', channel: 'Channel'}, facebook: {person: 'Person', page: 'Page', group: 'Group'},
        })}}[id]),
        querySelectorAll: () => [select], querySelector: selector => selector === '[data-toggle-link-password]' ? toggle : clear,
    }, Option: function (label, value) {this.textContent = label; this.value = value;}});
    return {platform, select, wrapper, password, toggle, clear,
        change: () => events.change(), togglePassword: () => toggleEvents.click(), clearPassword: () => clearEvents.change()};
}

test('platform changes offer only valid account types and clear the previous selection', () => {
    const state = setup();
    assert.equal(state.select.value, 'channel');
    assert.equal(state.select.required, true);
    state.platform.value = 'facebook'; state.change();
    assert.equal(state.select.value, '');
    assert.deepEqual(Array.from(state.select.options, option => option.value), ['', 'person', 'page', 'group']);
    state.platform.value = 'website'; state.change();
    assert.equal(state.select.disabled, true);
    assert.equal(state.select.required, false);
    assert.equal(state.wrapper.hidden, true);
});

test('list filter can search all account types without making the field required', () => {
    const state = setup({platformValue: '', selected: 'page', required: false});
    assert.equal(state.select.value, 'page');
    assert.equal(state.select.options.length, 6);
    assert.equal(state.select.required, false);
    assert.equal(state.select.disabled, false);
});

test('password visibility is optional and removing a saved password disables replacement input', () => {
    const state = setup(); state.togglePassword();
    assert.equal(state.password.type, 'text');
    assert.equal(state.toggle.textContent, 'Hide');
    state.togglePassword(); assert.equal(state.password.type, 'password');
    state.clear.checked = true; state.clearPassword();
    assert.equal(state.password.disabled, true); assert.equal(state.toggle.disabled, true);
    state.clear.checked = false; state.clearPassword();
    assert.equal(state.password.disabled, false);
});
