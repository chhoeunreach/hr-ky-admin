const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/directory-table-tools.js'), 'utf8');

function element(overrides = {}) {
    const listeners = {};
    const classes = new Set();
    const attributes = new Set();
    const el = {
        dataset: {},
        style: {},
        children: [],
        textContent: '',
        value: '',
        disabled: false,
        hidden: false,
        _listeners: listeners,
        classList: {
            add: (...names) => names.forEach(name => classes.add(name)),
            remove: (...names) => names.forEach(name => classes.delete(name)),
            toggle: (name, force) => force ? classes.add(name) : classes.delete(name),
            contains: name => classes.has(name),
        },
        hasAttribute: name => attributes.has(name),
        setAttribute(name, value) { attributes.add(name); this[name] = value; },
        getAttribute(name) { return this[name]; },
        addEventListener(type, handler) { (listeners[type] ||= []).push(handler); },
        dispatchEvent(event) { (listeners[event.type] || []).forEach(handler => handler(event)); },
        append(...nodes) { this.children.push(...nodes); },
        remove() {},
        click() {},
        select() {},
        cloneNode() {
            return {textContent: this.textContent, querySelectorAll: () => [], querySelector: () => null, replaceWith() {}, remove() {}};
        },
        querySelector: selector => el._query?.[selector] ?? null,
        querySelectorAll: selector => el._queryAll?.[selector] ?? [],
        closest: () => el._closest ?? {addEventListener() {}},
        insertRow: () => element({cells: [], insertCell() { const cell = element(); this.cells.push(cell); return cell; }}),
    };
    return Object.assign(el, overrides);
}

function setup() {
    const headers = ['#', 'Type', 'From'].map(label => element({textContent: label}));
    const row = {cells: headers.map((_, index) => element({textContent: String(index)})), dataset: {}};
    const table = element({
        id: 'leaveRequestTable',
        tHead: {rows: [{cells: headers}]},
        tBodies: [{rows: [row]}],
        rows: [{cells: headers}, row],
    });
    const status = element();
    const search = element({value: 'Dara'});
    const menu = element({_queryAll: {input: []}});
    const restore = element();
    const perPage = element();
    const copy = element({dataset: {directoryExport: 'copy'}, textContent: 'Copy'});
    const excel = element({dataset: {directoryExport: 'excel'}, textContent: 'Excel'});
    const tools = element({
        dataset: {
            directoryTable: table.id,
            directoryForm: 'leaveRequestFilterForm',
            directoryLive: 'false',
            directorySearchKey: 'search',
            directoryTitle: 'Leave Requests',
            directoryWorking: 'Working',
            directoryFailed: 'Failed',
            directoryCopied: 'Copied :count',
            directoryYes: 'Yes',
            directoryNo: 'No',
            directoryActive: 'Active',
            directoryInactive: 'Inactive',
            directoryExportUrl: 'http://hr.test/admin/leave-request/export-data',
        },
        _query: {
            '[data-directory-status]': status,
            'input[type=search]': search,
            '[data-directory-columns]': menu,
            '[data-directory-restore]': restore,
            '[data-directory-refresh]': element(),
            '[name="per_page"]': perPage,
        },
        _queryAll: {'[data-directory-export]': [copy, excel]},
    });
    const form = element({
        id: 'leaveRequestFilterForm',
        action: 'http://hr.test/admin/leave-request',
        elements: [{name: 'search', value: 'Dara'}],
        requestSubmit() {},
    });

    const requests = [], clipboard = [], workbook = {};
    const context = {
        URL, URLSearchParams,
        FormData: class {
            constructor(source) { this.values = [['search', source.elements[0].value], ['per_page', '25']]; }
            *[Symbol.iterator]() { yield* this.values; }
        },
        Blob: class { constructor(parts) { this.parts = parts; } },
        CustomEvent: class { constructor(type) { this.type = type; } },
        fetch: async (url, options) => {
            requests.push({url: String(url), options});
            return {ok: true, redirected: false, json: async () => ({
                success: true,
                headers: ['Date', 'Username'],
                rows: [['08-11-2026', 'dara']],
            })};
        },
        navigator: {clipboard: {writeText: async text => clipboard.push(text)}},
        location: {href: 'http://hr.test/admin/leave-request'},
        localStorage: {getItem: () => null, setItem() {}},
        setTimeout() {},
        document: {
            readyState: 'complete',
            body: {append() {}},
            addEventListener() {},
            createElement: () => element(),
            createTextNode: () => ({}),
            getElementById: id => id === table.id ? table : id === form.id ? form : null,
            querySelectorAll: selector => selector.includes('data-directory-table') ? [tools] : [],
        },
        window: {
            DirectoryWorkbook: {create: (headers, rows) => { workbook.headers = headers; workbook.rows = rows; return {}; }},
        },
    };
    context.URL.createObjectURL = () => 'blob:test';
    context.URL.revokeObjectURL = () => {};
    vm.runInNewContext(source, context);
    return {copy, excel, requests, clipboard, workbook};
}

test('leave request copy and excel use the structured export columns', async () => {
    const app = setup();

    await app.copy._listeners.click[0]();
    assert.equal(app.requests.length, 1);
    const url = new URL(app.requests[0].url);
    assert.equal(url.pathname, '/admin/leave-request/export-data');
    assert.equal(url.searchParams.get('search'), 'Dara');
    assert.equal(app.clipboard[0], 'Date\tUsername\n08-11-2026\tdara');

    await app.excel._listeners.click[0]();
    assert.equal(app.requests.length, 2);
    assert.deepEqual(app.workbook.headers, ['Date', 'Username']);
    assert.deepEqual(app.workbook.rows, [['08-11-2026', 'dara']]);
});
