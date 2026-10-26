const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-salary-table.js'), 'utf8');
function element(values = {}) {
    return Object.assign({hidden: false, handlers: {}, children: [], dataset: {},
        addEventListener(type, handler) {this.handlers[type] = handler;},
        append(...nodes) {this.children.push(...nodes);}, appendChild(node) {this.children.push(node);},
    }, values);
}
function setup(stored = null, failed = false, defaultHidden = []) {
    const headings = ['employee', 'salary', 'payment_type'].map(key => element({dataset: {salaryColumn: key, defaultVisible: defaultHidden.includes(key) ? '0' : '1'}, textContent: key}));
    const rows = [element({cells: headings.map(() => element())})];
    const menu = element({querySelectorAll: () => menu.children.map(label => label.children[0])});
    const link = element({dataset: {salaryExport: 'excel'}});
    const pdf = element({dataset: {salaryExport: 'pdf', orientation: 'portrait'}});
    const copy = element({disabled: false});
    const reset = element();
    const storage = {removed: false};
    const status = {}, copied = [], requests = [], timers = [];
    const search = element({value: 'Employee'}), count = element();
    const form = element({action: '/admin/employee-salaries', requestSubmit() {this.submitted = true;}});
    const toolkit = {dataset: {copySuccess: 'Copied', copyError: 'Failed'},
        querySelector: selector => selector === '[data-salary-columns]' ? menu : selector === '[data-salary-reset-columns]' ? reset : copy,
        querySelectorAll: () => [link, pdf]};
    const table = {querySelectorAll: selector => selector.startsWith('thead') ? headings : rows, querySelector: () => null};
    vm.runInNewContext(source, {
        document: {addEventListener() {}, getElementById: id => ({employeeSalaryToolkit: toolkit, employeeSalaryTable: table,
            employeeSalaryFilters: form, salarySearch: search, salaryRows: count}[id]),
            querySelector: () => status, querySelectorAll: () => [],
            createElement: () => element(), createTextNode: text => ({textContent: text})},
        window: {location: {href: 'http://localhost/admin/employee-salaries?page=3'}},
        localStorage: {getItem: () => stored, setItem() {}, removeItem() {storage.removed = true;}},
        URL, URLSearchParams, FormData: class {constructor() {return [['branch_id', '1'], ['payment_type', 'monthly'], ['q', search.value], ['per_page', '25']];}},
        setTimeout: callback => {timers.push(callback); return timers.length;}, clearTimeout() {},
        navigator: {clipboard: {writeText: async text => copied.push(text)}},
        fetch: async url => {requests.push(url); return {ok: !failed, json: async () => ({headings: ['Employee'], rows: [['=FORMULA'], ['Sam']]})};},
    });
    return {headings, rows, menu, link, pdf, copy, copied, requests, status, form, search, count, timers, reset, storage};
}
test('recommended defaults hide secondary columns and reset restores them after customization', () => {
    const state = setup(null, false, ['payment_type']);
    assert.equal(state.headings[2].hidden, true);
    const checkbox = state.menu.children[2].children[0];
    checkbox.checked = true; checkbox.handlers.change();
    assert.equal(state.rows[0].cells[2].hidden, false);
    state.reset.handlers.click();
    assert.equal(state.rows[0].cells[2].hidden, true);
    assert.equal(checkbox.checked, false);
    assert.equal(state.storage.removed, true);
    state.link.handlers.click();
    assert.deepEqual(new URL(state.link.href).searchParams.getAll('columns[]'), ['employee', 'salary']);
});
test('saved column selections remain available and obsolete selections fall back to recommended columns', () => {
    assert.equal(setup('["payment_type"]', false, ['payment_type']).headings[2].hidden, false);
    assert.equal(setup('["old_column"]', false, ['payment_type']).headings[2].hidden, true);
});
test('exports include current filters and visible columns, without pagination', () => {
    const state = setup('["employee","salary"]');
    state.link.handlers.click();
    const url = new URL(state.link.href);
    assert.equal(url.searchParams.get('branch_id'), '1');
    assert.equal(url.searchParams.get('payment_type'), 'monthly');
    assert.equal(url.searchParams.get('page'), null);
    assert.deepEqual(url.searchParams.getAll('columns[]'), ['employee', 'salary']);
    assert.equal(state.rows[0].cells[2].hidden, true);
    state.pdf.handlers.click();
    assert.equal(new URL(state.pdf.href).searchParams.get('orientation'), 'portrait');
});
test('column visibility keeps at least one data column', () => {
    const state = setup('["employee"]');
    const checkbox = state.menu.children[0].children[0];
    checkbox.checked = false;
    checkbox.handlers.change();
    assert.equal(checkbox.checked, true);
    assert.equal(state.headings[0].hidden, false);
});
test('copy uses all filtered records from the server and escapes spreadsheet formulas', async () => {
    const state = setup();
    await state.copy.handlers.click({currentTarget: state.copy});
    assert.equal(state.copied[0], "Employee\n'=FORMULA\nSam");
    assert.equal(new URL(state.requests[0]).searchParams.get('export'), 'json');
    assert.equal(state.status.textContent, 'Copied');
    assert.equal(state.copy.disabled, false);
});
test('copy reports errors and page size or search submits the filter form', async () => {
    const state = setup(null, true);
    await state.copy.handlers.click({currentTarget: state.copy});
    assert.equal(state.status.textContent, 'Failed');
    state.count.handlers.change();
    assert.equal(state.form.submitted, true);
    state.form.submitted = false;
    state.search.handlers.input(); state.timers[0]();
    assert.equal(state.form.submitted, true);
});
