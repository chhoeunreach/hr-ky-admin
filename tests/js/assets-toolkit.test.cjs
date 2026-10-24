const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/assets-toolkit.js'), 'utf8');

function element(values = {}) {
    return Object.assign({hidden: false, handlers: {}, children: [], dataset: {},
        addEventListener(type, handler) {this.handlers[type] = handler;},
        append(...nodes) {this.children.push(...nodes);}, appendChild(node) {this.children.push(node);},
    }, values);
}
function setup({stored = null, failedFetch = false} = {}) {
    const headings = ['number', 'asset_code', 'description', 'actions'].map(key => element({dataset: {assetColumn: key}, textContent: key}));
    const rows = [element({cells: headings.map(() => element())})];
    const menu = element({querySelectorAll: () => menu.children.map(label => label.children[0])});
    const exportLink = element({dataset: {assetsExport: 'excel'}});
    const pdfLink = element({dataset: {assetsExport: 'pdf', orientation: 'portrait'}});
    const copy = element({disabled: false});
    const status = {};
    const form = element({requestSubmit() {this.submissions = (this.submissions || 0) + 1;}});
    const search = element({value: 'laptop'});
    const count = element();
    const toolkit = element({dataset: {copySuccess: 'Copied', copyError: 'Copy failed'},
        querySelector: selector => selector === '[data-assets-columns]' ? menu : copy,
        querySelectorAll: () => [exportLink, pdfLink],
    });
    const table = {querySelectorAll: selector => selector.startsWith('thead') ? headings : rows};
    const storedValues = new Map();
    const copied = [];
    const requested = [];
    const timers = [];
    vm.runInNewContext(source, {
        document: {getElementById: id => ({assetsToolkit: toolkit, assetsTable: table, assetsToolkitSearch: form, assetsSearch: search, assetsRows: count}[id]),
            querySelector: () => status, createElement: () => element(), createTextNode: text => ({textContent: text})},
        window: {location: {href: 'http://localhost/admin/assets?branch_id=2&page=3&columns[]=old'}},
        localStorage: {getItem: () => stored, setItem: (key, value) => storedValues.set(key, value)},
        URL, setTimeout: callback => {timers.push(callback); return timers.length;}, clearTimeout() {},
        navigator: {clipboard: {writeText: async text => copied.push(text)}},
        fetch: async url => {requested.push(url); return {ok: !failedFetch, json: async () => ({headings: ['Asset code'], rows: [['00123'], ['=FORMULA']]} )};},
    });
    return {headings, rows, menu, exportLink, pdfLink, copy, status, form, search, count, storedValues, copied, requested, timers};
}

test('column visibility applies to rows and exports and never exports actions', () => {
    const state = setup();
    const checkbox = state.menu.children[2].children[0];
    checkbox.checked = false;
    checkbox.handlers.change();
    assert.equal(state.headings[2].hidden, true);
    assert.equal(state.rows[0].cells[2].hidden, true);
    state.exportLink.handlers.click();
    const url = new URL(state.exportLink.href);
    assert.deepEqual(url.searchParams.getAll('columns[]'), ['number', 'asset_code']);
    assert.equal(url.searchParams.get('branch_id'), '2');
    assert.equal(url.searchParams.get('q'), 'laptop');
    assert.equal(url.searchParams.has('page'), false);
    assert.equal(url.searchParams.get('export'), 'excel');
    state.pdfLink.handlers.click();
    assert.equal(new URL(state.pdfLink.href).searchParams.get('orientation'), 'portrait');
});

test('copy fetches all matching rows rather than scraping the current page', async () => {
    const state = setup();
    await state.copy.handlers.click({currentTarget: state.copy});
    assert.equal(new URL(state.requested[0]).searchParams.get('export'), 'json');
    assert.equal(new URL(state.requested[0]).searchParams.has('page'), false);
    assert.equal(state.copied[0], "Asset code\n00123\n'=FORMULA");
    assert.equal(state.copy.disabled, false);
    assert.equal(state.status.textContent, 'Copied');
});

test('failed copy reports the error and allows a retry', async () => {
    const state = setup({failedFetch: true});
    await state.copy.handlers.click({currentTarget: state.copy});
    assert.equal(state.copy.disabled, false);
    assert.equal(state.status.textContent, 'Copy failed');
    assert.deepEqual(state.copied, []);
});

test('saved columns restore and row count changes submit preserved filters', () => {
    const state = setup({stored: '["asset_code"]'});
    assert.equal(state.headings[0].hidden, true);
    assert.equal(state.rows[0].cells[1].hidden, false);
    state.count.handlers.change();
    assert.equal(state.form.submissions, 1);
    const lastColumn = state.menu.children[1].children[0];
    lastColumn.checked = false;
    lastColumn.handlers.change();
    assert.equal(lastColumn.checked, true);
});
