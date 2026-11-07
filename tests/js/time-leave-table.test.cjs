const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/time-leave-table.js'), 'utf8');

function setup() {
    let options;
    const values = {search: {value: ''}, per_page: {value: ''}};
    const handlers = {};
    const summary = {};
    const search = {setAttribute(key, value) {this[key] = value;}};
    let currentSearch = 'Dara';
    let pageLength = 25;
    const api = {
        on(event, handler) {handlers[event] = handler;},
        search() {return currentSearch;},
        page: {info() {return {start: 25, end: 50, recordsDisplay: 75};}}
    };
    api.page.len = () => pageLength;
    function DataTable(element, config) {options = config; return api;}
    DataTable.isDataTable = () => false;
    const window = {DataTable, pdfMake: {}};
    vm.runInNewContext(source, {
        window,
        document: {
            getElementById(id) {
                return {
                    timeLeaveTable: {}, timeLeaveSummary: summary,
                    timeLeaveFilterForm: {elements: {namedItem: name => values[name]}}
                }[id];
            },
            querySelector() {return search;}
        }
    });
    window.initTimeLeaveTable({
        pageLength: 25, search: 'Dara', title: 'Time leave list',
        show: 'Show', entries: 'entries', searchLabel: 'Search', empty: 'No records',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries'
    });
    return {options, values, handlers, summary, search, window,
        changeState() {currentSearch = 'Medical'; pageLength = 50; handlers.draw();}};
}

test('exports use searched rows across all pages and currently visible columns', () => {
    const {options} = setup();
    const excel = options.buttons.find(button => button.extend === 'excelHtml5');
    assert.equal(excel.exportOptions.columns, ':visible');
    assert.equal(excel.exportOptions.modifier.page, 'all');
    assert.equal(excel.exportOptions.modifier.search, 'applied');
    assert.equal(excel.exportOptions.modifier.order, 'applied');
    assert.equal(excel.exportOptions.escapeExcelFormula, true);
    const node = {
        textContent: 'Eye icon', hasAttribute: key => key === 'data-export',
        getAttribute: () => 'Family appointment'
    };
    assert.equal(excel.exportOptions.format.body('', 0, 5, node), 'Family appointment');
    node.getAttribute = () => '';
    assert.equal(excel.exportOptions.format.body('', 0, 5, node), '');
});

test('print escapes request text and PDF supports both orientations', () => {
    const {options, window} = setup();
    const node = {textContent: '<img src=x onerror=alert(1)>', hasAttribute: () => false};
    const print = options.buttons.find(button => button.extend === 'print');
    assert.equal(print.exportOptions.format.body('', 0, 4, node), '&lt;img src=x onerror=alert(1)&gt;');
    const pdf = options.buttons.find(button => button.extend === 'collection');
    assert.deepEqual(Array.from(pdf.buttons, button => button.orientation), ['portrait', 'landscape']);
    const doc = {defaultStyle: {}, styles: {tableHeader: {}}, content: [{table: {body: [['Date', 'Employee']]}}]};
    pdf.buttons[0].customize(doc);
    assert.equal(doc.defaultStyle.font, 'NotoSansKhmer');
    assert.equal(doc.content[0].table.widths.length, 2);
    assert.ok(window.pdfMake.fonts.NotoSansKhmer.normal.endsWith('.ttf'));
});

test('filter submissions preserve the current search and page size after table changes', () => {
    const state = setup();
    assert.equal(state.search['aria-label'], 'Search');
    assert.equal(state.summary.textContent, 'Showing 26 to 50 of 75 entries');
    state.changeState();
    assert.equal(state.values.search.value, 'Medical');
    assert.equal(state.values.per_page.value, 50);
});
