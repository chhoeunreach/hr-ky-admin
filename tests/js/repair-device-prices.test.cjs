const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/repairPrice/partials/form-behavior.blade.php'), 'utf8');
const source = view.slice(view.indexOf('    function toggleCategoryPrices'), view.indexOf("    document.querySelectorAll('.repair-category-picker')"));

class Element {
    constructor() {this.children = []; this.dataset = {};}
    append(...items) {this.children.push(...items);}
    replaceChildren() {this.children = [];}
    querySelectorAll(selector) {
        return this.children.flatMap(child => [
            ...(selector === 'input' && child.type === 'number' ? [child] : []),
            ...child.querySelectorAll(selector),
        ]);
    }
}

function setup({prices = [], selected = true, create = true, update = true, services} = {}) {
    const container = new Element();
    container.dataset.categoryId = '1';
    const checkbox = {checked: selected};
    const option = {querySelector: selector => selector === '[name="category_ids[]"]' ? checkbox : container};
    const picker = {querySelectorAll: selector => selector === '.repair-category-option' ? [option] : [container]};
    const form = {dataset: {prices: JSON.stringify(prices)}, querySelector: () => picker};
    const context = vm.createContext({
        document: {createElement: () => new Element()},
        devicePriceServices: services || [{id: 11, category_id: 1, name: 'Screen Replacement'}, {id: 22, category_id: 2, name: 'Battery'}],
        canCreateDevicePrice: create, canUpdateDevicePrice: update,
        priceLabel: 'Selling price ($)', noServicesLabel: 'Add a service first', newPriceLabel: 'New price',
    });
    vm.runInContext(source, context);
    context.renderDevicePrices(form);
    return {container, checkbox, picker, context, inputs: () => container.querySelectorAll('input')};
}

test('selected category offers a model-specific new price for its own service', () => {
    const env = setup();
    assert.equal(env.container.hidden, false);
    assert.equal(env.inputs().length, 1);
    assert.equal(env.inputs()[0].name, 'new_prices[11]');
    assert.equal(env.inputs()[0].value, '');
    assert.equal(env.inputs()[0].disabled, false);
    assert.equal(env.inputs()[0].min, '0');
});

test('editing loads each existing part variant and avoids a duplicate default price', () => {
    const env = setup({prices: [
        {id: 1, repair_service_id: 11, part_type: null, selling_price: '65.00'},
        {id: 2, repair_service_id: 11, part_type: 'Original', selling_price: '90.00'},
        {id: 3, repair_service_id: 22, part_type: null, selling_price: '35.00'},
    ]});
    assert.deepEqual(env.inputs().map(input => [input.name, input.value]), [
        ['existing_prices[1]', '65.00'], ['existing_prices[2]', '90.00'],
    ]);
    assert.equal(env.inputs()[0].required, true);
});

test('unselecting a category disables its prices and selecting it again preserves entered values', () => {
    const env = setup();
    env.inputs()[0].value = '75';
    env.checkbox.checked = false;
    env.context.toggleCategoryPrices(env.picker);
    assert.equal(env.container.hidden, true);
    assert.equal(env.inputs()[0].disabled, true);
    env.checkbox.checked = true;
    env.context.toggleCategoryPrices(env.picker);
    assert.equal(env.inputs()[0].disabled, false);
    assert.equal(env.inputs()[0].value, '75');
});

test('create-only managers cannot submit existing prices and update-only managers cannot add prices', () => {
    const prices = [{id: 1, repair_service_id: 11, part_type: 'Original', selling_price: '90.00'}];
    const creator = setup({prices, update: false});
    assert.equal(creator.inputs()[0].readOnly, true);
    assert.equal(creator.inputs()[0].disabled, true);
    assert.equal(creator.inputs()[1].name, 'new_prices[11]');
    const updater = setup({prices, create: false});
    assert.equal(updater.inputs().length, 1);
    assert.equal(updater.inputs()[0].disabled, false);
});

test('switching models does not retain the previous model’s price values', () => {
    const first = setup({prices: [{id: 1, repair_service_id: 11, part_type: null, selling_price: '65.00'}]});
    const second = setup();
    assert.equal(first.inputs()[0].value, '65.00');
    assert.equal(second.inputs()[0].value, '');
});
