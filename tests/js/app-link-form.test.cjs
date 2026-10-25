const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/app-link-form.js'), 'utf8');
function setup({platformValue = 'telegram', selected = 'channel', required = true, withManagers = false, readyState = 'complete'} = {}) {
    const events = {}, wrapper = {};
    const platform = {value: platformValue, addEventListener: (type, callback) => {events[type] = callback;}};
    const select = {dataset: {platformId: 'platform', selected, accountRequired: required ? '1' : ''}, options: [{textContent: 'Choose type'}],
        replaceChildren(option) {this.options = [option];}, add(option) {this.options.push(option);}, closest: () => wrapper};
    const toggleEvents = {}, clearEvents = {};
    const password = {type: 'password'}, toggle = {setAttribute() {}, addEventListener(type, callback) {toggleEvents[type] = callback;}};
    const clear = {checked: false, addEventListener(type, callback) {clearEvents[type] = callback;}};
    const readyEvents = {}, managerCalls = [], managerSelection = ['1', '2'];
    const managers = withManagers ? {dataset: {placeholder: 'Search and select people'}, value: managerSelection} : null;
    const browserWindow = {};
    const loadSelect2 = () => {
        browserWindow.jQuery = element => ({select2(options) {managerCalls.push({element, options});}});
        browserWindow.jQuery.fn = {select2() {}};
    };
    if (readyState !== 'loading') loadSelect2();
    vm.runInNewContext(source, {window: browserWindow, document: {
        readyState, addEventListener: (event, callback) => {readyEvents[event] = callback;},
        currentScript: {dataset: {show: 'Show', hide: 'Hide'}},
        getElementById: id => ({platform, password, manager_ids: managers, appLinkAccountTypes: {textContent: JSON.stringify({
            other: {personal: 'Personal Account', page: 'Page', group: 'Group', channel: 'Channel'},
            telegram: {personal: 'Personal Account', group: 'Group', channel: 'Channel'}, facebook: {person: 'Person', page: 'Page', group: 'Group', channel: 'Channel'},
        })}}[id]),
        querySelectorAll: () => [select], querySelector: selector => selector === '[data-toggle-link-password]' ? toggle : clear,
    }, Option: function (label, value) {this.textContent = label; this.value = value;}});
    return {platform, select, wrapper, password, toggle, clear, managers, managerCalls,
        finishLoading: () => {loadSelect2(); readyEvents.DOMContentLoaded();},
        change: () => events.change(), togglePassword: () => toggleEvents.click(), clearPassword: () => clearEvents.change()};
}

test('platform changes offer valid account types and preserve a supported channel selection', () => {
    const state = setup();
    assert.equal(state.select.value, 'channel');
    assert.equal(state.select.required, true);
    state.platform.value = 'facebook'; state.change();
    assert.equal(state.select.value, 'channel');
    assert.deepEqual(Array.from(state.select.options, option => option.value), ['', 'person', 'page', 'group', 'channel']);
    state.platform.value = 'website'; state.change();
    assert.equal(state.select.disabled, false);
    assert.equal(state.select.required, true);
    assert.equal(state.wrapper.hidden, false);
    assert.deepEqual(Array.from(state.select.options, option => option.value), ['', 'personal', 'page', 'group', 'channel']);
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

test('create shows all four account types before selecting a platform and preserves a valid choice', () => {
    const state = setup({platformValue: '', selected: ''});
    assert.equal(state.wrapper.hidden, false);
    assert.equal(state.select.disabled, false);
    assert.deepEqual(Array.from(state.select.options, option => option.value), ['', 'personal', 'page', 'group', 'channel']);
    state.select.value = 'group';
    state.platform.value = 'telegram'; state.change();
    assert.equal(state.select.value, 'group');
});

test('managed by waits for the dropdown library and preserves multiple selected managers', () => {
    const state = setup({withManagers: true, readyState: 'loading'});
    assert.equal(state.managerCalls.length, 0);
    state.finishLoading();
    assert.equal(state.managerCalls.length, 1);
    const {element, options} = state.managerCalls[0];
    assert.equal(element, state.managers);
    assert.equal(options.placeholder, 'Search and select people');
    assert.equal(options.closeOnSelect, false);
    assert.equal(options.allowClear, true);
    assert.deepEqual(state.managers.value, ['1', '2']);
});

test('managed by initializes immediately when the document is already loaded', () => {
    const state = setup({withManagers: true});
    assert.equal(state.managerCalls.length, 1);
});
