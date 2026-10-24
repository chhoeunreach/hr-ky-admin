const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/employees/profile/show.blade.php'), 'utf8');
const start = blade.indexOf('        let employeePrintPending = false;');
const end = blade.indexOf('        function printEmployeeCompleteForm()', start);
const source = blade.slice(start, end)
    .replace("@json(route('admin.employees.profile.print-records.store', $employee))", "'/print-records'")
    .replace('@json(csrf_token())', "'csrf-token'")
    .replace(/@json\(__\('index\.([a-z_]+)'\)\)/g, (_, key) => JSON.stringify(key));

function setup(fetch, {sourceRequestId = '1', staticSource = false} = {}) {
    const calls = [];
    const field = {
        value: 'Family event', name: 'reason', id: '', tagName: 'TEXTAREA',
        getAttribute: () => 'Reason', closest: () => null, matches: selector => selector === 'textarea' || (staticSource && selector.includes('[data-print-static-source]')),
    };
    const record = {};
    const printedRecord = {};
    const notice = {};
    const printField = {replaceWith(value) { this.printedValue = value.textContent; }};
    const clone = {
        innerText: 'Leave Request Form',
        querySelectorAll: selector => selector === 'img' ? [] : [printField],
        querySelector: () => printedRecord,
    };
    const paper = {
        dataset: {sourceRequestId, requestTab: 'leave-letter'},
        querySelectorAll: () => [field],
        querySelector: () => record,
        cloneNode: () => clone,
    };
    const context = vm.createContext({
        employeeProfilePrintPermissions: {leaveLetter: true, timeLeaveLetter: true, resignationLetter: true},
        cleanupEmployeeCompletePrintRoot: () => calls.push('cleanup'),
        document: {
            querySelector: selector => selector === '#paper' ? paper : notice,
            createElement: () => ({appendChild() {}}),
            getElementById: () => null,
            body: {appendChild() {}, classList: {add() {}}},
        },
        window: {print: () => calls.push('print'), alert: () => calls.push('error')},
        fetch: async (...args) => {calls.push('save'); return fetch(...args);},
    });
    vm.runInContext(source, context);
    return {calls, record, printedRecord, notice, printField, run: (type = 'leaveLetter') => context.printEmployeeProfilePaper('#paper', 'root', 'printing', type)};
}

test('waits for a saved record before opening print, including form values', async () => {
    let finish;
    let payload;
    const response = new Promise(resolve => {finish = resolve;});
    const {calls, run} = setup(async (url, options) => {
        payload = JSON.parse(options.body);
        assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf-token');
        return response;
    });
    const pending = run();
    assert.deepEqual(calls, ['cleanup', 'save']);
    await run(); // Ignore duplicate clicks while saving.
    assert.deepEqual(calls, ['cleanup', 'save']);
    finish({ok: true, json: async () => ({recorded: true, print_record_id: 41})});
    await pending;
    assert.deepEqual(calls, ['cleanup', 'save', 'print']);
    assert.equal(payload.fields[0].value, 'Family event');
    assert.equal(payload.form_type, 'leaveLetter');
    assert.equal(payload.source_request_id, 1);
});

for (const [label, response] of [
    ['server error', async () => ({ok: false})],
    ['permission rejection', async () => ({ok: false, status: 403})],
    ['network failure', async () => {throw new Error('offline');}],
    ['missing record number', async () => ({ok: true, json: async () => ({recorded: true})})],
    ['missing confirmation', async () => ({ok: true, json: async () => ({})})],
]) {
    test(`blocks printing after ${label}`, async () => {
        const {calls, run} = setup(response);
        await run();
        assert.equal(calls.includes('print'), false);
        assert.equal(calls.at(-1), 'error');
        await run(); // Allow a retry after failure.
        assert.equal(calls.filter(call => call === 'save').length, 2);
    });
}

test('does not save or print a form without permission', async () => {
    const {calls, run} = setup(async () => {throw new Error('must not run');});
    await run('contract');
    assert.deepEqual(calls, []);
});


test('blocks printing without a saved leave request', async () => {
    const {calls, run} = setup(async () => {throw new Error('must not save');}, {sourceRequestId: ''});
    await run();
    assert.deepEqual(calls, ['cleanup', 'error']);
});

test('prints the saved record number and full text rather than a clipped textarea', async () => {
    const {calls, run, record, printedRecord, printField} = setup(async () => ({ok: true, json: async () => ({recorded: true, print_record_id: 41})}));
    await run();
    assert.equal(record.textContent, 'print_record_number: #41');
    assert.equal(printedRecord.textContent, 'print_record_number: #41');
    assert.equal(printField.printedValue, 'Family event');
    assert.equal(calls.at(-1), 'print');
});


test('resignation requires a saved request and waits for print persistence', async () => {
    const missing = setup(async () => {throw new Error('must not save');}, {sourceRequestId: ''});
    await missing.run('resignationLetter');
    assert.deepEqual(missing.calls, ['cleanup', 'error']);
    let payload;
    const saved = setup(async (url, options) => {
        payload = JSON.parse(options.body);
        return {ok: true, json: async () => ({recorded: true, print_record_id: 42})};
    }, {sourceRequestId: '3'});
    await saved.run('resignationLetter');
    assert.equal(payload.source_request_id, 3);
    assert.equal(payload.form_type, 'resignationLetter');
    assert.deepEqual(saved.calls, ['cleanup', 'save', 'print']);
});


test('resignation keeps its visible preview values instead of duplicating hidden export fields', async () => {
    let payload;
    const {calls, run, printField} = setup(async (url, options) => {
        payload = JSON.parse(options.body);
        return {ok: true, json: async () => ({recorded: true, print_record_id: 43})};
    }, {staticSource: true, sourceRequestId: '3'});
    await run('resignationLetter');
    assert.equal(printField.printedValue, undefined);
    assert.equal(payload.fields[0].value, 'Family event');
    assert.equal(calls.at(-1), 'print');
});
