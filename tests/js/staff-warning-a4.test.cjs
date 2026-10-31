const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/staff-warning-a4.js'), 'utf8');
function setup({height = 1120, contentHeight = 1400, position = 'absolute'} = {}) {
    const properties = {};
    const events = {};
    const content = {scrollHeight: contentHeight};
    const paper = {clientHeight: height, querySelector: () => content, style: {setProperty: (key, value) => {properties[key] = value;}}};
    const context = {
        window: {addEventListener: (event, listener) => {events[event] = listener;}},
        document: {readyState: 'loading', addEventListener() {}, querySelectorAll: () => [paper]},
        getComputedStyle: element => element === content ? {position} : {paddingTop: '38', paddingBottom: '38'},
    };
    vm.runInNewContext(source, context);
    return {fit: () => context.window.StaffWarningA4.fit(paper), properties, events, paper};
}

test('long notices are scaled to the writable A4 area without discarding content', () => {
    const {fit, properties} = setup();
    fit();
    assert.equal(Number(properties['--warning-a4-scale']), (1120 - 76 - 2) / 1400);
});
test('short forms retain their full font size', () => {
    const {fit, properties} = setup({contentHeight: 800});
    fit();
    assert.equal(properties['--warning-a4-scale'], '1');
});
test('hidden profile tabs and readable mobile previews are not scaled', () => {
    for (const options of [{height: 0}, {position: 'static'}]) {
        const {fit, properties} = setup(options);
        fit();
        assert.deepEqual(properties, {});
    }
});
test('browser print recalculates the layout for every letter', () => {
    const {events, properties} = setup();
    events.beforeprint();
    assert.ok(Number(properties['--warning-a4-scale']) < 1);
});
