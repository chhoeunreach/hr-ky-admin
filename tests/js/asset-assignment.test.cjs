const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/asset-assignment.js'), 'utf8');
const flush = async () => {for (let i = 0; i < 6; i++) await new Promise(resolve => setImmediate(resolve));};
function node(values = {}) {
    return Object.assign({value: '', textContent: '', disabled: false, dataset: {}, events: {}, options: [],
        classList: {toggle() {}}, addEventListener(type, handler) {this.events[type] = handler;},
        replaceChildren(...options) {this.options = options; this.value = '';}, add(option) {this.options.push(option);},
    }, values);
}
function data(id = 1) {
    return {branches: [{id: 2, name: 'Branch'}], selected_branch_id: 2, branch_locked: true, asset: {id, branch_id: 2, name: 'Asset ' + id, asset_code: '0001', branch_name: 'Branch'},
        departments: [{id: 5, dept_name: 'HR', branch_id: 2}, {id: 6, dept_name: 'IT', branch_id: 2}],
        employees: [{id: 10, name: 'Employee', english_name: 'Latin Name', employee_code: 'EMP10', username: 'self', department_id: 5, branch_id: 2},
            {id: 11, name: 'Other', employee_code: 'EMP11', department_id: 6, branch_id: 2}]};
}
function setup({load = async () => data(), save = async () => ({ok: true, result: {message: 'Saved'}}), searchable = false} = {}) {
    const fields = {user_id: node(), department_id: node(), assigned_date: node(), asset_id: node(), branch_id: node()};
    const button = node({textContent: 'Assign'});
    const errors = {user_id: node(), department_id: node(), assigned_date: node()};
    const form = node({action: '',
        querySelector(selector) {
            if (selector === '[type="submit"]') return button;
            const field = selector.match(/\[name="([^"]+)"\]/)?.[1];
            if (field) return fields[field];
            return errors[selector.match(/data-assignment-field-error="([^"]+)"/)?.[1]];
        },
        querySelectorAll: () => Object.values(errors), reportValidity: () => true,
        getAttribute() {return this.action;}, setAttribute(name, value) {this[name] = value;},
        reset() {Object.values(fields).forEach(field => {field.value = '';});},
    });
    const status = node(), error = node(), errorText = node(), retry = node();
    const summaries = ['name', 'code', 'serial', 'branch'].map(key => node({dataset: {assignmentSummary: key}}));
    const modal = node({querySelector: selector => ({'[data-assignment-status]': status, '[data-assignment-error]': error,
        '[data-assignment-error-text]': errorText, '[data-assignment-retry]': retry}[selector]), querySelectorAll: () => summaries});
    const config = {employee: 'Choose employee', departments: 'All departments', loading: 'Loading', empty: 'No employees',
        branch: 'Choose branch', chooseBranch: 'Select a branch', noBranches: 'No branches', today: '2026-10-24', loadError: 'Load failed', saveError: 'Save failed', unavailable: 'N/A', saving: 'Saving'};
    const events = {}, posts = [], selections = [], reloads = [];
    const jQuery = searchable ? Object.assign(element => ({select2(options) {selections.push({element, options});},
        trigger() {}, on(type, handler) {element.events[type] = handler;}}), {fn: {select2() {}}}) : undefined;
    vm.runInNewContext(source, {
        document: {readyState: 'complete', getElementById: id => ({assetAssignmentModal: modal, assetAssignmentForm: form,
            assetAssignmentConfig: {textContent: JSON.stringify(config)}}[id]), addEventListener: (type, handler) => {events[type] = handler;}},
        window: {jQuery, location: {reload: () => reloads.push(true)}},
        bootstrap: {Modal: {getOrCreateInstance: () => ({show() {}, hide() {modal.events['hidden.bs.modal']();}})}},
        Option: function (text, value) {Object.assign(this, {text, value});},
        FormData: function () {this.fields = Object.fromEntries(Object.entries(fields).map(([key, field]) => [key, field.value]));},
        fetch: async (url, options) => {
            if (options.method === 'POST') {posts.push(options.body.fields); const result = await save(); return {ok: result.ok, json: async () => result.result};}
            return {ok: true, json: async () => load(url)};
        },
    });
    const open = id => events.click({target: {closest: () => ({dataset: {href: '/save/' + id, optionsUrl: '/options/' + id}})}, preventDefault() {}});
    return {fields, button, form, modal, status, errorText, retry, posts, reloads, selections, summaries, open};
}

test('searchable employee selector stays inside the modal and fills the matching department', async () => {
    const state = setup({searchable: true});
    assert.equal(state.selections.length, 3);
    assert.ok(state.selections[0].options.dropdownParent);
    state.open(1);
    await flush();
    assert.equal(state.fields.user_id.options.length, 3);
    assert.ok(state.fields.user_id.options[1].text.includes('Latin Name'));
    assert.ok(state.fields.user_id.options[1].text.includes('EMP10'));
    state.fields.user_id.value = '10';
    state.fields.user_id.events.change();
    assert.equal(state.fields.department_id.value, '5');
    assert.equal(state.button.disabled, false);
    assert.equal(state.fields.assigned_date.value, '2026-10-24');
});

test('department filter clears a stale employee choice and allows only matching options', async () => {
    const state = setup(); state.open(1); await flush();
    state.fields.user_id.value = '10'; state.fields.user_id.events.change();
    state.fields.department_id.value = '6'; state.fields.department_id.events.change();
    assert.equal(state.fields.user_id.value, '');
    assert.equal(state.fields.user_id.options.length, 2);
    assert.equal(state.fields.user_id.options[1].value, '11');
    assert.equal(state.button.disabled, true);
});

test('late options from a previous asset cannot overwrite the current asset', async () => {
    const pending = new Map();
    const state = setup({load: url => new Promise(resolve => pending.set(url, resolve))});
    state.open(1); await flush(); state.open(2); await flush();
    pending.get('/options/2')(data(2)); await flush();
    pending.get('/options/1')(data(1)); await flush();
    assert.equal(state.fields.asset_id.value, 2);
    assert.equal(state.summaries[0].textContent, 'Asset 2');
});

test('failed option loading can be retried', async () => {
    let failed = true;
    const state = setup({load: async () => {if (failed) throw new Error('offline'); return data();}});
    state.open(1); await flush();
    assert.equal(state.button.disabled, true);
    assert.equal(state.errorText.textContent, 'Load failed');
    failed = false; await state.retry.events.click();
    assert.equal(state.fields.user_id.disabled, false);
});

test('submits the complete selection once and preserves it after validation failure', async () => {
    const state = setup({save: async () => ({ok: false, result: {message: 'Check selection', errors: {user_id: ['Invalid employee']}}})});
    state.open(1); await flush();
    state.fields.user_id.value = '10'; state.fields.user_id.events.change();
    await state.form.events.submit({preventDefault() {}});
    assert.equal(state.posts[0].asset_id, 1);
    assert.equal(state.posts[0].user_id, '10');
    assert.equal(state.posts[0].department_id, '5');
    assert.equal(state.fields.user_id.value, '10');
    assert.equal(state.button.disabled, false);
    assert.equal(state.errorText.textContent, 'Check selection');
    assert.equal(state.reloads.length, 0);
});


test('employee can be selected directly for a branchless asset and fills branch and department', async () => {
    const state = setup({load: async () => ({...data(), branches: [{id: 1, name: 'Other'}, {id: 2, name: 'Branch'}], selected_branch_id: null,
            branch_locked: false, asset: {...data().asset, branch_id: null, branch_name: null}})});
    state.open(1); await flush();
    assert.equal(state.fields.branch_id.value, '');
    assert.equal(state.fields.user_id.disabled, false);
    state.fields.user_id.value = '10'; state.fields.user_id.events.change();
    assert.equal(state.fields.branch_id.value, '2');
    assert.equal(state.fields.department_id.value, '5');
    assert.equal(state.button.disabled, false);
});

test('choosing a branch filters employees locally without another request', async () => {
    let loads = 0;
    const state = setup({load: async () => {loads++; return {...data(), selected_branch_id: null,
        branches: [{id: 1, name: 'Other'}, {id: 2, name: 'Branch'}],
        departments: [...data().departments, {id: 7, dept_name: 'Other department', branch_id: 1}],
        employees: [...data().employees, {id: 12, name: 'Other branch employee', department_id: 7, branch_id: 1}]};}});
    state.open(1); await flush();
    assert.equal(state.fields.user_id.options.length, 4);
    state.fields.branch_id.value = '1'; state.fields.branch_id.events.change();
    assert.equal(loads, 1);
    assert.equal(state.fields.user_id.options.length, 2);
    assert.equal(state.fields.user_id.options[1].value, '12');
    state.fields.user_id.value = '12'; state.fields.user_id.events.change();
    assert.equal(state.fields.department_id.value, '7');
});
