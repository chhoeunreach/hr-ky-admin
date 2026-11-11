const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/repair-price-auto-split.js'), 'utf8');

function field(name, value = '') {
    // The service fee is read-only in the interface: always calculated.
    return {value, disabled: false, readOnly: name === 'service_fee', dataset: {priceField: name}};
}

function group(values = {}) {
    const inputs = Object.fromEntries(['part_cost', 'service_fee', 'selling_price']
        .map(name => [name, field(name, values[name] ?? '')]));
    const list = Object.values(inputs);
    const container = {querySelectorAll: () => list};
    list.forEach(input => {input.closest = () => container;});
    return inputs;
}

function setup(groups = [{}], pricing = null) {
    const rows = groups.map(group);
    const handlers = {};
    const document = {addEventListener: (type, handler) => {handlers[type] = handler;}};
    const context = {document, Number, Math, String};
    if (pricing) context.window = {REPAIR_PRICING: pricing};
    vm.runInNewContext(source, context);
    const fire = (inputs, name, value) => {
        if (value !== undefined) inputs[name].value = value;
        handlers.input({target: inputs[name]});
    };
    return {rows, fire};
}

test('the part cost suggests a cost-plus selling price with the fee as remainder', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '70');
    assert.equal(env.rows[0].part_cost.value, '70');
    assert.equal(env.rows[0].selling_price.value, '100.00');
    assert.equal(env.rows[0].service_fee.value, '30.00');
});

test('suggested selling prices round up to a clean $0.50 price point and never short-change the fee', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '35.50');
    assert.equal(env.rows[0].selling_price.value, '51.00');
    assert.equal(env.rows[0].service_fee.value, '15.50');
    env.fire(env.rows[0], 'part_cost', '45');
    assert.equal(env.rows[0].selling_price.value, '64.50');
    assert.equal(env.rows[0].service_fee.value, '19.50');
});

test('a selling price already on a price point is not rounded up further', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '35');
    assert.equal(env.rows[0].selling_price.value, '50.00');
    assert.equal(env.rows[0].service_fee.value, '15.00');
});

test('editing the selling price lets the service fee absorb the difference and keeps the part cost', () => {
    const env = setup([{part_cost: '35.50', selling_price: '51.00', service_fee: '15.50'}]);
    env.fire(env.rows[0], 'selling_price', '60');
    assert.equal(env.rows[0].part_cost.value, '35.50');
    assert.equal(env.rows[0].service_fee.value, '24.50');
});

test('a selling price below the part cost clamps the service fee at zero instead of going negative', () => {
    const env = setup([{part_cost: '35.50'}]);
    env.fire(env.rows[0], 'selling_price', '30');
    assert.equal(env.rows[0].part_cost.value, '35.50');
    assert.equal(env.rows[0].service_fee.value, '0.00');
});

test('without a part cost the fee falls back to its share of the sale', () => {
    const env = setup();
    env.fire(env.rows[0], 'selling_price', '100');
    assert.equal(env.rows[0].part_cost.value, '');
    assert.equal(env.rows[0].service_fee.value, '30.00');
});

test('the service fee is always recalculated whenever part cost or selling price changes', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '400');
    assert.equal(env.rows[0].selling_price.value, '571.50');
    assert.equal(env.rows[0].service_fee.value, '171.50');
    env.fire(env.rows[0], 'selling_price', '500');
    assert.equal(env.rows[0].service_fee.value, '100.00');
    env.fire(env.rows[0], 'part_cost', '380');
    assert.equal(env.rows[0].selling_price.value, '500');
    assert.equal(env.rows[0].service_fee.value, '120.00');
});

test('the read-only service fee never drives changes to the other fields', () => {
    const env = setup([{part_cost: '400', selling_price: '500', service_fee: '100.00'}]);
    env.rows[0].service_fee.value = '25';
    env.fire(env.rows[0], 'service_fee', '25');
    assert.equal(env.rows[0].part_cost.value, '400');
    assert.equal(env.rows[0].selling_price.value, '500');
    assert.equal(env.rows[0].service_fee.value, '25');
});

test('typing the part cost after setting the selling price keeps that price and the fee becomes the difference', () => {
    const env = setup();
    env.fire(env.rows[0], 'selling_price', '500');
    env.fire(env.rows[0], 'part_cost', '400');
    assert.equal(env.rows[0].selling_price.value, '500');
    assert.equal(env.rows[0].part_cost.value, '400');
    assert.equal(env.rows[0].service_fee.value, '100.00');
    env.fire(env.rows[0], 'part_cost', '380');
    assert.equal(env.rows[0].selling_price.value, '500');
    assert.equal(env.rows[0].service_fee.value, '120.00');
});

test('the suggestion keeps refreshing while the selling price is still only suggested', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '400');
    assert.equal(env.rows[0].selling_price.value, '571.50');
    assert.equal(env.rows[0].service_fee.value, '171.50');
    env.fire(env.rows[0], 'part_cost', '380');
    assert.equal(env.rows[0].selling_price.value, '543.00');
    assert.equal(env.rows[0].service_fee.value, '163.00');
});

test('overwriting the suggested selling price by hand makes it respected from then on', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '400');
    assert.equal(env.rows[0].selling_price.value, '571.50');
    env.fire(env.rows[0], 'selling_price', '500');
    assert.equal(env.rows[0].service_fee.value, '100.00');
    env.fire(env.rows[0], 'part_cost', '420');
    assert.equal(env.rows[0].selling_price.value, '500');
    assert.equal(env.rows[0].service_fee.value, '80.00');
});

test('the part cost being typed is never reformatted so entry stays uninterrupted', () => {
    const env = setup();
    env.fire(env.rows[0], 'part_cost', '3');
    assert.equal(env.rows[0].part_cost.value, '3');
    assert.equal(env.rows[0].selling_price.value, '4.50');
    assert.equal(env.rows[0].service_fee.value, '1.50');
});

test('a disabled target (private copied cost) is left untouched, a read-only fee is still calculated', () => {
    const env = setup([{}, {}]);
    env.rows[0].service_fee.disabled = true;
    env.rows[0].service_fee.value = '9';
    env.fire(env.rows[0], 'part_cost', '70');
    assert.equal(env.rows[0].service_fee.value, '9');
    env.rows[1].service_fee.disabled = false;
    env.fire(env.rows[1], 'part_cost', '70');
    assert.equal(env.rows[1].service_fee.readOnly, true);
    assert.equal(env.rows[1].service_fee.value, '30.00');
});

test('clearing the part cost clears the derived selling price and service fee', () => {
    const env = setup([{part_cost: '70', service_fee: '30', selling_price: '100'}]);
    env.fire(env.rows[0], 'part_cost', '');
    assert.equal(env.rows[0].selling_price.value, '');
    assert.equal(env.rows[0].service_fee.value, '');
});

test('clearing the selling price clears only the service fee and keeps the part cost', () => {
    const env = setup([{part_cost: '70', service_fee: '30', selling_price: '100'}]);
    env.fire(env.rows[0], 'selling_price', '');
    assert.equal(env.rows[0].part_cost.value, '70');
    assert.equal(env.rows[0].service_fee.value, '');
});

test('each model and service row updates independently', () => {
    const env = setup([{}, {}]);
    env.fire(env.rows[0], 'part_cost', '70');
    env.fire(env.rows[1], 'part_cost', '35');
    assert.equal(env.rows[0].selling_price.value, '100.00');
    assert.equal(env.rows[0].service_fee.value, '30.00');
    assert.equal(env.rows[1].selling_price.value, '50.00');
    assert.equal(env.rows[1].service_fee.value, '15.00');
});

test('the part share and price point come from window.REPAIR_PRICING', () => {
    const env = setup([{}, {}], {partShare: 0.6, priceStep: 0.25});
    env.fire(env.rows[0], 'part_cost', '30');
    assert.equal(env.rows[0].selling_price.value, '50.00');
    assert.equal(env.rows[0].service_fee.value, '20.00');
    env.fire(env.rows[1], 'part_cost', '35.50');
    assert.equal(env.rows[1].selling_price.value, '59.25');
    assert.equal(env.rows[1].service_fee.value, '23.75');
    env.fire(env.rows[1], 'selling_price', '100');
    assert.equal(env.rows[1].service_fee.value, '64.50');
    env.fire(env.rows[1], 'part_cost', '20');
    assert.equal(env.rows[1].selling_price.value, '100');
    assert.equal(env.rows[1].service_fee.value, '80.00');
    env.fire(env.rows[1], 'selling_price', '');
    env.fire(env.rows[1], 'part_cost', '20');
    assert.equal(env.rows[1].selling_price.value, '33.50');
    assert.equal(env.rows[1].service_fee.value, '13.50');
});

test('inputs without a price field are ignored', () => {
    const env = setup();
    let touched = false;
    const stray = {value: '1', disabled: false, readOnly: false, dataset: {}, closest: () => {touched = true;}};
    env.fire({selling_price: stray}, 'selling_price', '100');
    assert.equal(stray.value, '100');
    assert.equal(touched, false);
});
