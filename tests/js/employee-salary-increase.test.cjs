const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-salary-increase.js'), 'utf8');

function setup(overrides = {}, values = {}) {
    const fields = {};
    for (const [name, value] of Object.entries({increase_type: 'amount', increase_period: 'period', increment_amount: '100', increment_percent: '', ...values})) {
        fields[name] = {value, addEventListener(name, callback) {this[name] = callback;}, setCustomValidity(error) {this.error = error;}};
    }
    const before = {}, after = {}, wrappers = {amount: [{}], percent: [{}]};
    for (const key of ['gross', 'basic', 'allowance', 'earnings', 'annual']) {
        before[key] = {dataset: {salaryBefore: key}};
        after[key] = {dataset: {salaryAfter: key}};
    }
    const form = {
        dataset: {annual: '12000', periods: '12', basicType: 'percent', basicValue: '80', currency: '$', error: 'Invalid increase', ...overrides},
        elements: {namedItem: name => fields[name]},
        querySelectorAll: selector => selector === '[data-salary-before]' ? Object.values(before)
            : selector === '[data-salary-after]' ? Object.values(after) : selector === '[data-amount-options]' ? wrappers.amount : wrappers.percent,
        addEventListener() {},
    };
    vm.runInNewContext(source, {document: {querySelector: () => form}});
    const amount = (set, key) => Number(set[key].textContent.replace(/[^\d.-]/g, ''));
    return {fields, before, after, wrappers, amount};
}

test('monthly amount preview shows the same basic and allowance breakdown as the server', () => {
    const {amount, before, after, fields} = setup();
    assert.equal(amount(before, 'annual'), 12000);
    assert.equal(amount(after, 'annual'), 13200);
    assert.equal(amount(after, 'basic'), 880);
    assert.equal(amount(after, 'allowance'), 220);
    assert.equal(fields.increment_percent.disabled, true);
});

test('percentage mode retains restored input and fixed basic salary', () => {
    const {amount, after, fields, wrappers} = setup({basicType: 'fixed', basicValue: '800'}, {increase_type: 'percent', increment_percent: '10'});
    assert.equal(amount(after, 'annual'), 13200);
    assert.equal(amount(after, 'basic'), 800);
    assert.equal(amount(after, 'allowance'), 300);
    assert.equal(wrappers.amount[0].hidden, true);
    assert.equal(fields.increment_amount.disabled, true);
});

test('weekly cycle and annual input calculate different increase units', () => {
    const state = setup({periods: '52', annual: '10400', basicValue: '100'}, {increment_amount: '20'});
    assert.equal(state.amount(state.after, 'gross'), 220);
    state.fields.increase_period.value = 'annual';
    state.fields.increase_period.change();
    assert.equal(state.amount(state.after, 'annual'), 10420);
});

test('zero and negative amounts do not display a misleading new salary', () => {
    const state = setup({}, {increment_amount: '-10'});
    assert.equal(state.after.annual.textContent, '—');
    assert.equal(state.fields.increment_amount.error, 'Invalid increase');
    state.fields.increment_amount.value = '0'; state.fields.increment_amount.input();
    assert.equal(state.after.annual.textContent, '—');
});

test('earning components are separated from allowance in the preview', () => {
    const {amount, after} = setup({earnings: JSON.stringify({fixed: 120, basic: 0, ctc: 5})});
    assert.equal(amount(after, 'earnings'), 65);
    assert.equal(amount(after, 'allowance'), 155);
});
