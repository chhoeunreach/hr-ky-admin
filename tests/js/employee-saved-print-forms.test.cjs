const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-saved-print-forms.js'), 'utf8');
function node(values = {}) {
    return Object.assign({events: {}, children: [], style: {}, textContent: '',
        append(...children) {this.children.push(...children);}, appendChild(child) {this.children.push(child);},
        replaceChildren(...children) {this.children = children;}, setAttribute() {},
        addEventListener(type, handler) {this.events[type] = handler;},
    }, values);
}
function setup({fail = false, latest = null, autoNumber = false, reserveNumber = false, reserveFail = false} = {}) {
    const field = {name: 'warning_reason', value: 'Late arrival', getAttribute: () => null, closest: () => null, matches: () => false};
    let toolbar;
    const number = {textContent: 'WRN-______'};
    const paper = node({dataset: {}, querySelector: () => autoNumber ? number : null, querySelectorAll: () => [field], before(element) {if (element.children) toolbar = element;}});
    Object.defineProperty(paper, 'innerText', {get: () => autoNumber ? 'Warning form ' + number.textContent : 'Warning form'});
    const editBody = node(), error = node(), confirm = node(), title = node();
    const editModal = node({querySelector: selector => ({'[data-save-print-paper]': editBody, '[data-save-print-error]': error, '[data-save-print-confirm]': confirm, '.modal-title': title}[selector])});
    const historyBody = node(), more = node();
    const historyModal = node({querySelector: selector => selector === '[data-saved-form-history]' ? historyBody : more});
    const config = {saveUrl: '/forms', historyUrl: '/forms', csrf: 'token', create: 'Create', update: 'Update', saved: 'Saved', number: 'Form', error: 'Failed', history: 'History', newForm: 'New'};
    if (reserveNumber) {config.warningNumberUrl = '/warning-number'; config.generatingWarningNumber = 'Generating number...';}
    const printButton = node(), requests = [], reservations = [], calls = {show: 0, hide: 0};
    const window = {EmployeeFormPrintPermissions: {warning: true}, location: {href: 'https://hr.test/profile'}};
    vm.runInNewContext(source, {window, URL,
        document: {getElementById: id => ({employeeSavedPrintConfig: {textContent: JSON.stringify(config)}, employeeSavePrintFormModal: editModal, employeeSavedFormHistory: historyModal}[id]),
            querySelector: selector => selector === '#staff-warning-form .employee-warning-paper' ? paper : null,
            querySelectorAll: () => [printButton], createElement: () => node(),
            createComment: () => ({replaceWith() {paper.restored = true;}})},
        bootstrap: {Modal: {getOrCreateInstance: modal => ({show() {calls.show++;}, hide() {calls.hide++; modal.events['hidden.bs.modal']();}})}},
        fetch: async (url, options) => {
            if (url === '/warning-number') {
                reservations.push(url);
                return {ok: !reserveFail, json: async () => reserveFail ? {} : {warning_number_id: 70 + reservations.length, warning_number: 'WRN-' + String(70 + reservations.length).padStart(6, '0')}};
            }
            if (options.method !== 'POST') return {ok: true, json: async () => ({data: latest ? [latest] : []})};
            const data = JSON.parse(options.body); requests.push(data);
            return {ok: !fail, json: async () => fail ? {} : {saved: true, saved_form_id: 40 + requests.length, warning_number: data.saved_form_id || data.warning_number_id ? number.textContent : 'WRN-' + String(40 + requests.length).padStart(6, '0')}};
        },
    });
    return {paper, number, field, printButton, toolbar, confirm, error, editBody, window, requests, reservations, calls};
}

test('forms must be created in a modal before printing and saving restores the preview', async () => {
    const state = setup();
    assert.equal(state.printButton.disabled, true);
    state.toolbar.children[0].events.click();
    assert.equal(state.calls.show, 1);
    assert.equal(state.editBody.children[0], state.paper);
    await state.confirm.events.click();
    assert.equal(state.requests[0].form_type, 'warning');
    assert.equal(state.requests[0].saved_form_id, undefined);
    assert.equal(state.printButton.disabled, false);
    assert.equal(state.paper.restored, true);
    assert.equal(state.window.EmployeeSavedForms.recordId('warning', state.paper), 41);
});

test('editing makes a saved form unprintable until an update is saved as a new revision', async () => {
    const state = setup(); state.toolbar.children[0].events.click(); await state.confirm.events.click();
    state.field.value = 'Updated warning'; state.paper.events.input();
    assert.equal(state.printButton.disabled, true);
    assert.equal(state.window.EmployeeSavedForms.recordId('warning', state.paper), null);
    state.toolbar.children[0].events.click(); await state.confirm.events.click();
    assert.equal(state.requests[1].saved_form_id, 41);
    assert.equal(state.requests[1].fields[0].value, 'Updated warning');
    assert.equal(state.window.EmployeeSavedForms.recordId('warning', state.paper), 42);
    assert.equal(state.printButton.disabled, false);
});

test('failed saving keeps the form modal open and printing disabled', async () => {
    const state = setup({fail: true}); state.toolbar.children[0].events.click(); await state.confirm.events.click();
    assert.equal(state.calls.hide, 0);
    assert.equal(state.printButton.disabled, true);
    assert.equal(state.error.textContent, 'Failed');
    assert.equal(state.confirm.disabled, false);
});

test('an existing saved revision becomes available after reloading the profile', async () => {
    const state = setup({latest: {id: 15, document_text: 'Warning form', fields: [{label: 'warning_reason', value: 'Late arrival', checked: null}]}});
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.window.EmployeeSavedForms.recordId('warning', state.paper), 15);
    assert.equal(state.printButton.disabled, false);
    assert.equal(state.toolbar.children[0].textContent, 'Update');
});

test('server-assigned warning numbers are displayed, retained on edit and renewed for a new form', async () => {
    const state = setup({autoNumber: true});
    state.toolbar.children[0].events.click(); await state.confirm.events.click();
    assert.equal(state.number.textContent, 'WRN-000041');
    assert.equal(state.printButton.disabled, false);
    state.field.value = 'Updated explanation'; state.paper.events.input();
    state.toolbar.children[0].events.click(); await state.confirm.events.click();
    assert.equal(state.number.textContent, 'WRN-000041');
    assert.equal(state.printButton.disabled, false);
    state.toolbar.children[1].events.click();
    assert.equal(state.number.textContent, 'WRN-______');
    await state.confirm.events.click();
    assert.equal(state.number.textContent, 'WRN-000043');
    assert.equal(state.printButton.disabled, false);
});

test('warning number is shown before opening the editor and entered data is saved with that number', async () => {
    const state = setup({autoNumber: true, reserveNumber: true});
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.number.textContent, 'WRN-000071');
    assert.equal(state.requests.length, 0);
    assert.equal(state.printButton.disabled, true);
    state.toolbar.children[0].events.click();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.calls.show, 1);
    state.field.value = 'Explanation entered before saving';
    state.paper.events.input();
    await state.confirm.events.click();
    assert.equal(state.requests[0].fields[0].value, 'Explanation entered before saving');
    assert.equal(state.requests[0].warning_number_id, 71);
    assert.equal(state.number.textContent, 'WRN-000071');
    assert.equal(state.printButton.disabled, false);
    state.toolbar.children[1].events.click();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.number.textContent, 'WRN-000072');
});

test('a numbering failure blocks opening an unnumbered form and allows a retry', async () => {
    const state = setup({autoNumber: true, reserveNumber: true, reserveFail: true});
    await new Promise(resolve => setImmediate(resolve));
    state.toolbar.children[0].events.click();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.calls.show, 0);
    assert.equal(state.requests.length, 0);
    assert.equal(state.printButton.disabled, true);
    assert.equal(state.toolbar.children[0].disabled, false);
});

test('saved warning inputs and the existing number are restored without reserving a new number', async () => {
    const state = setup({autoNumber: true, reserveNumber: true, latest: {
        id: 15, warning_number: 'WRN-000012', document_text: 'Warning form WRN-000012',
        fields: [{label: 'warning_reason', value: 'Previously saved explanation', checked: null}],
    }});
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(state.field.value, 'Previously saved explanation');
    assert.equal(state.number.textContent, 'WRN-000012');
    assert.equal(state.reservations.length, 0);
    assert.equal(state.printButton.disabled, false);
});
