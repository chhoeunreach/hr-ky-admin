const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/monthly-payroll-print.js'), 'utf8');
const flush = () => new Promise(setImmediate);
function setup({fonts = Promise.resolve(), decode = () => Promise.resolve(), auto = '1'} = {}) {
    const events = {}, style = {}, calls = {print: 0}, variables = {};
    const paper = {value: 'A3', addEventListener(type, handler) {events.paper = handler;}};
    const orientation = {value: 'landscape', addEventListener(type, handler) {events.orientation = handler;}};
    const button = {disabled: false, addEventListener(type, handler) {events.print = handler;}};
    const sheet = {dataset: {autoPrint: auto}, style: {setProperty(key, value) {variables[key] = value;}},
        querySelectorAll: () => [{decode}]};
    vm.runInNewContext(source, {
        document: {fonts: {ready: fonts}, getElementById: id => ({payrollPrintPageStyle: style, payrollPrintPaper: paper, payrollPrintOrientation: orientation}[id]),
            querySelector: selector => selector.includes('sheet') ? sheet : button},
        window: {print() {calls.print++;}, addEventListener(type, handler) {events[type] = handler;}},
    });
    return {events, style, calls, variables, paper, orientation, button};
}
test('paper and orientation choices update print size without navigating', () => {
    const state = setup();
    assert.ok(state.style.textContent.includes('A3 landscape'));
    assert.equal(state.variables['--payroll-sheet-width'], '420mm');
    state.paper.value = 'A4'; state.orientation.value = 'portrait'; state.events.paper();
    assert.ok(state.style.textContent.includes('A4 portrait'));
    assert.equal(state.variables['--payroll-sheet-width'], '210mm');
});
test('printing waits for Khmer fonts and employee photos', async () => {
    let resolveFonts, resolveImage;
    const state = setup({fonts: new Promise(resolve => {resolveFonts = resolve;}), decode: () => new Promise(resolve => {resolveImage = resolve;})});
    const printing = state.events.print();
    assert.equal(state.button.disabled, true);
    assert.equal(state.calls.print, 0);
    resolveFonts(); await flush();
    assert.equal(state.calls.print, 0);
    resolveImage(); await printing;
    assert.equal(state.calls.print, 1);
    assert.equal(state.button.disabled, false);
});
test('automatic print runs once when the preview loads', async () => {
    const state = setup(); state.events.load(); await flush();
    assert.equal(state.calls.print, 1);
});
test('a failed image does not block printing or duplicate the print dialog', async () => {
    const state = setup({decode: () => Promise.reject(new Error('Missing photo'))});
    const first = state.events.print(); state.events.print(); await first;
    assert.equal(state.calls.print, 1);
    assert.equal(state.button.disabled, false);
});
