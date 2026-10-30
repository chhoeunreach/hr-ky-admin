const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/monthly-payroll-generate.js'), 'utf8');
function setup(month = null) {
    const events = {}, button = {}, attributes = {};
    const choices = [{value: '1', checked: false}, {value: '2', checked: false}, {value: '3', checked: false, disabled: true}];
    const action = name => ({addEventListener(type, handler) {events[name] = handler;}});
    const all = action('all'), clear = action('clear');
    const form = {dataset: {}, elements: {month: {}}, querySelector: () => button, querySelectorAll: () => choices,
        addEventListener(type, handler) {events[type] = handler;}, setAttribute(key, value) {attributes[key] = value;}};
    const modal = {querySelector: selector => ({'[data-payroll-generate-form]': form, '[data-payroll-branches-all]': all, '[data-payroll-branches-clear]': clear}[selector]), addEventListener(type, handler) {events[type] = handler;}};
    vm.runInNewContext(source, {document: {querySelector: () => null, getElementById: id => ({monthlyPayrollGenerateModal: modal,
        monthlyMonth: month ? {value: month} : null, monthlyBranch: {value: '2'}}[id])}});
    return {events, form, button, attributes, choices};
}
test('generation uses the currently selected month after payroll filters change', () => {
    const state = setup('2026-09');state.events['show.bs.modal']({relatedTarget: {dataset: {payrollMonth: '2026-10'}}});
    assert.equal(state.form.elements.month.value, '2026-09');assert.equal(state.choices[1].checked, true);
    assert.equal(state.choices[0].checked, false);
});
test('All branches selects each enabled branch and Clear leaves an empty selection', () => {
    const state = setup();state.events.all();
    assert.equal(state.choices[0].checked, true);assert.equal(state.choices[1].checked, true);assert.equal(state.choices[2].checked, false);
    state.events.clear();assert.equal(state.choices.some(choice => choice.checked), false);
});
test('reopening the modal preserves multiple choices and an intentionally empty selection', () => {
    const state = setup();const event = {relatedTarget: {dataset: {payrollMonth: '2026-10'}}};
    state.events['show.bs.modal'](event);state.events.all();state.events['show.bs.modal'](event);
    assert.equal(state.choices[0].checked, true);assert.equal(state.choices[1].checked, true);
    state.events.clear();state.events['show.bs.modal'](event);assert.equal(state.choices.some(choice => choice.checked), false);
});
test('yearly row opens generation for its own month', () => {
    const state = setup();state.events['show.bs.modal']({relatedTarget: {dataset: {payrollMonth: '2026-03'}}});
    assert.equal(state.form.elements.month.value, '2026-03');
});
test('generation disables repeat submissions while the server builds the preview', () => {
    const state = setup();state.events.submit();assert.equal(state.button.disabled, true);
    assert.equal(state.attributes['aria-busy'], 'true');
});
test('the preview Save button prevents duplicate submissions without a generation modal', () => {
    let submit;
    const button = {}, attributes = {};
    const save = {querySelector: () => button, addEventListener(type, handler) {submit = handler;}, setAttribute(key, value) {attributes[key] = value;}};
    vm.runInNewContext(source, {document: {querySelector: () => save, getElementById: () => null}});
    submit();assert.equal(button.disabled, true);assert.equal(attributes['aria-busy'], 'true');
});
