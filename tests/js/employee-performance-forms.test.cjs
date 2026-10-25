const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-performance-forms.js'), 'utf8');
function setup({reopenType = null, reopenId = null, values = {}, confirm = true} = {}) {
    const events = {}, moved = [], shows = [], tabs = [], confirms = [];
    const modals = {};
    for (const [id, kind] of [['addResponsibilityModal', 'responsibility'], ['addKpiModal', 'kpi']]) {
        const names = ['title', 'name', 'description', 'weight', 'status', 'start_date', 'end_date', 'unit', 'actual_value', 'score', 'assigned_by', '_method', 'performance_record_id'];
        const fields = Object.fromEntries(names.map(name => [name, {value: '', type: name.endsWith('_date') ? 'date' : 'text', focus() {this.focused = true;}}]));
        const title = {}, submit = {};
        const form = {dataset: {performanceForm: kind, storeUrl: '/' + kind, updateUrl: '/' + kind + '/__record__'},
            elements: {namedItem: name => fields[name]}, querySelector: selector => selector === '[type="submit"]' ? submit : fields[kind === 'kpi' ? 'name' : 'title'],
            reset() {Object.values(fields).forEach(field => {field.value = '';}); fields.status.value = 'active'; fields.unit.value = 'number';}};
        const modal = {fields, form, title, submit, events: {}, querySelector: selector => selector === '[data-performance-form]' ? form : title,
            addEventListener(type, callback) {this.events[type] = callback;}};
        modals[id] = modal;
    }
    const config = {reopenType, reopenId, values, create: {responsibility: 'Add responsibility', kpi: 'Add KPI'}, edit: {responsibility: 'Edit responsibility', kpi: 'Edit KPI'}, update: 'Update', deleteConfirm: 'Delete record?'};
    const bootstrap = {Modal: {getOrCreateInstance: modal => ({show() {shows.push(modal); modal.events['show.bs.modal']({});}})}};
    vm.runInNewContext(source, {bootstrap,
        document: {readyState: 'complete', body: {appendChild: modal => moved.push(modal)},
            getElementById: id => id === 'employeePerformanceFormConfig' ? {textContent: JSON.stringify(config)} : modals[id],
            addEventListener: (type, callback) => {events[type] = callback;}},
        window: {bootstrap, employeeGotoTab: tab => tabs.push(tab), confirm: message => {confirms.push(message); return confirm;}},
    });
    const edit = (kind, record, id = 14) => {
        const modal = modals[kind === 'kpi' ? 'addKpiModal' : 'addResponsibilityModal'];
        modal.events['show.bs.modal']({relatedTarget: {hasAttribute: () => true, dataset: {editRecord: JSON.stringify(record), recordId: id, editUrl: '/' + kind + '/' + id}}});
        return modal;
    };
    return {events, modals, moved, shows, tabs, confirms, edit};
}

test('editing opens saved values, including dates and zero results, in the update form', () => {
    const state = setup();
    const modal = state.edit('responsibility', {title: 'Duty', weight: 0, status: 'inactive', start_date: '2026-10-01', assigned_by: 999});
    assert.equal(modal.form.action, '/responsibility/14'); assert.equal(modal.fields._method.value, 'PUT');
    assert.equal(modal.fields.title.value, 'Duty'); assert.equal(modal.fields.weight.value, '0');
    assert.equal(modal.fields.start_date.value, '2026-10-01'); assert.equal(modal.fields.assigned_by.value, '');
    assert.equal(modal.title.textContent, 'Edit responsibility'); assert.equal(state.moved.length, 2);
    const kpi = state.edit('kpi', {name: 'Quality', actual_value: 0, score: 0, description: 'Full details'});
    assert.equal(kpi.fields.actual_value.value, '0'); assert.equal(kpi.fields.score.value, '0');
});

test('opening Add after Edit clears the record and restores POST creation', () => {
    const state = setup(); const modal = state.edit('kpi', {name: 'Old KPI', unit: 'percentage'});
    modal.events['show.bs.modal']({relatedTarget: {hasAttribute: () => false}});
    assert.equal(modal.form.action, '/kpi'); assert.equal(modal.fields._method.value, 'POST');
    assert.equal(modal.fields.name.value, ''); assert.equal(modal.fields.unit.value, 'number');
    assert.equal(modal.fields.performance_record_id.value, ''); assert.equal(modal.submit.textContent, 'Add KPI');
});

test('validation errors reopen the same record with entered values', () => {
    const state = setup({reopenType: 'kpi', reopenId: 14, values: {name: 'Changed', score: 0, weight: 101}});
    const modal = state.modals.addKpiModal;
    assert.equal(state.shows.length, 1); assert.deepEqual(state.tabs, ['kpi']);
    assert.equal(modal.form.action, '/kpi/14'); assert.equal(modal.fields.score.value, '0');
    assert.equal(modal.fields.weight.value, '101'); assert.equal(modal.fields._method.value, 'PUT');
});

test('canceling delete prevents submission and unrelated forms are left alone', () => {
    const state = setup({confirm: false});
    const event = {target: {matches: () => true}, preventDefault() {this.prevented = true;}};
    state.events.submit(event); assert.equal(event.prevented, true); assert.equal(state.confirms.length, 1);
    state.events.submit({target: {matches: () => false}, preventDefault() {assert.fail();}});
    assert.equal(state.confirms.length, 1);
});
