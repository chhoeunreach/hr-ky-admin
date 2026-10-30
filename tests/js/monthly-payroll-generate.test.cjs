const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/monthly-payroll-generate.js'), 'utf8');
function setup(month = null) {
    const events = {}, button = {}, attributes = {};
    const form = {elements: {month: {}, branch_id: {}}, querySelector: () => button,
        addEventListener(type, handler) {events[type] = handler;}, setAttribute(key, value) {attributes[key] = value;}};
    const modal = {querySelector: () => form, addEventListener(type, handler) {events[type] = handler;}};
    vm.runInNewContext(source, {document: {getElementById: id => ({monthlyPayrollGenerateModal: modal,
        monthlyMonth: month ? {value: month} : null, monthlyBranch: {value: '2'}}[id])}});
    return {events, form, button, attributes};
}
test('generation uses the currently selected month after payroll filters change', () => {
    const state = setup('2026-09');state.events['show.bs.modal']({relatedTarget: {dataset: {payrollMonth: '2026-10'}}});
    assert.equal(state.form.elements.month.value, '2026-09');assert.equal(state.form.elements.branch_id.value, '2');
});
test('yearly row opens generation for its own month', () => {
    const state = setup();state.events['show.bs.modal']({relatedTarget: {dataset: {payrollMonth: '2026-03'}}});
    assert.equal(state.form.elements.month.value, '2026-03');
});
test('generation disables repeat submissions while the server saves the record', () => {
    const state = setup();state.events.submit();assert.equal(state.button.disabled, true);
    assert.equal(state.attributes['aria-busy'], 'true');
});
