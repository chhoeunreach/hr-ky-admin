const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-salary-modals.js'), 'utf8');
function setup() {
    const handlers = {}, dispatched = [];
    const error = {textContent: 'Previous error'}, employee = {};
    const form = {addEventListener(type, handler) {handlers[type] = handler;}, dispatchEvent(event) {dispatched.push(event);}};
    const modal = {querySelector: selector => selector.includes('error') ? error : selector.includes('employee]') ? employee : form,
        addEventListener(type, handler) {handlers[type] = handler;}};
    vm.runInNewContext(source, {document: {getElementById: () => modal},
        CustomEvent: class {constructor(type, options) {this.type = type; Object.assign(this, options);}}});
    return {handlers, dispatched, form, error, employee};
}
test('delete confirmation always targets the employee selected in the current menu', () => {
    const state = setup();
    for (const id of [20, 21]) {
        state.handlers['show.bs.modal']({relatedTarget: {dataset: {deleteUrl: '/salary/'+id, employeeName: 'Employee '+id}}});
        assert.equal(state.form.action, '/salary/'+id);
        assert.equal(state.employee.textContent, 'Employee '+id);
        assert.equal(state.error.textContent, '');
    }
});
test('delete form submits through the section refresh handler instead of navigating', () => {
    const state = setup(); let prevented = false;
    state.handlers.submit({preventDefault() {prevented = true;}});
    assert.equal(prevented, true);
    assert.equal(state.dispatched[0].type, 'employee-salary:submit');
    assert.equal(state.dispatched[0].cancelable, true);
});
