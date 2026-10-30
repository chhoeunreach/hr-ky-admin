const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/monthly-attendance-exports.js'), 'utf8');

function setup() {
    let click;
    const state = {form: {entries: [['month', '2026-09'], ['branch_id[]', '2'], ['branch_id[]', '4'],
        ['department_id[]', '7'], ['department_id[]', '8'], ['shift_id[]', '3'], ['shift_id[]', '5']]}, search: {value: ' Sok '} };
    vm.runInNewContext(source, {
        URL, FormData: class {constructor(form) {return form.entries;}},
        window: {location: {href: 'https://hr.test/admin/attendance-monthly?month=2026-10&branch_id%5B%5D=1&department_id%5B%5D=1&search=Old&page=3&per_page=10'}},
        document: {addEventListener(type, handler) {click = handler;},
            getElementById: id => id === 'monthlyAttendanceFilters' ? state.form : state.search},
    });
    state.export = (format, {prevented = false, href = 'https://hr.test/admin/attendance-monthly?month=2026-10&export=old'} = {}) => {
        const link = {dataset: {monthlyExport: format}, href};
        click({target: {closest: () => link}, defaultPrevented: prevented});
        return new URL(link.href);
    };
    return state;
}

for (const format of ['reduc_xlsx', 'reduc_count_xlsx']) {
    test(`${format} uses the current month, multi-selects and search instead of stale URL filters`, () => {
        const state = setup(); const url = state.export(format);
        assert.equal(url.searchParams.get('month'), '2026-09');
        assert.deepEqual(url.searchParams.getAll('branch_id[]'), ['2', '4']);
        assert.deepEqual(url.searchParams.getAll('department_id[]'), ['7', '8']);
        assert.deepEqual(url.searchParams.getAll('shift_id[]'), ['3', '5']);
        assert.equal(url.searchParams.get('search'), 'Sok');
        assert.equal(url.searchParams.get('export'), format);
        assert.equal(url.searchParams.has('page'), false);
        assert.equal(url.searchParams.has('per_page'), false);
    });
}

test('clearing selected filters removes their old values from the export', () => {
    const state = setup(); state.form.entries = [['month', '2026-08']]; state.search.value = '';
    const url = state.export('reduc_xlsx');
    for (const key of ['branch_id[]', 'department_id[]', 'shift_id[]', 'search']) assert.equal(url.searchParams.has(key), false);
});

test('export reads replacement filter controls after an AJAX refresh', () => {
    const state = setup();state.export('reduc_xlsx');
    state.form = {entries: [['month', '2026-07'], ['branch_id[]', '9']]};state.search = {value: 'New employee'};
    const url = state.export('reduc_count_xlsx');
    assert.equal(url.searchParams.get('month'), '2026-07');
    assert.deepEqual(url.searchParams.getAll('branch_id[]'), ['9']);
    assert.equal(url.searchParams.get('search'), 'New employee');
});

test('zero-valued search terms are preserved', () => {
    const state = setup();state.search.value = '0';assert.equal(state.export('reduc_xlsx').searchParams.get('search'), '0');
});

test('other exports share the live filters and PDF preserves its selected orientation', () => {
    const state = setup();
    for (const format of ['csv', 'excel', 'word', 'bonus_xlsx']) assert.equal(state.export(format).searchParams.get('month'), '2026-09');
    const url = state.export('pdf', {href: 'https://hr.test/admin/attendance-monthly?orientation=portrait'});
    assert.equal(url.searchParams.get('orientation'), 'portrait');
    assert.equal(state.export('pdf').searchParams.get('orientation'), 'landscape');
});

test('a prevented click is left to its existing handler', () => {
    const state = setup();assert.equal(state.export('reduc_xlsx', {prevented: true}).searchParams.get('export'), 'old');
});
