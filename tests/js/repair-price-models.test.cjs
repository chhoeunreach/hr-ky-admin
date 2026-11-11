const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/repair-price-models.js'), 'utf8');

function element(extra = {}) {
    return Object.assign({handlers: {}, addEventListener(type, callback) {this.handlers[type] = callback;},
        fire(type, event = {}) {this.handlers[type]?.(event);}}, extra);
}
function priceRow() {
    const labels = Object.fromEntries(['model', 'category', 'service'].map(name => ['[data-pair-' + name + ']', {textContent: ''}]));
    const fields = Object.fromEntries(['part_cost', 'service_fee', 'selling_price'].map(field => {
        const message = {hidden: true};
        return [field, {value: '', disabled: true, hidden: false, dataset: {priceField: field, fieldLabel: field},
            parentElement: {querySelector: () => field === 'selling_price' ? null : message},
            setAttribute() {}, message}];
    }));
    return {dataset: {}, hidden: false, fields, labels, cloneNode: () => priceRow(),
        querySelector: selector => labels[selector], querySelectorAll: () => Object.values(fields)};
}
const template = {source_price_id: 42, source_device_id: 1, repair_category_id: 5, repair_service_id: 8,
    part_name: 'OLED assembly', part_type: 'Original', part_cost: '35.50', service_fee: '10.00', selling_price: '75.00',
    warranty_days: 90, estimated_minutes: 45, availability_status: 'pre_order', note: 'Includes fitting & testing'};

function setup({context = '', viewCost = true} = {}) {
    const fields = Object.fromEntries(['source_price_id', 'copy_source_device_id', 'part_name', 'part_type',
        'warranty_days', 'estimated_minutes', 'availability_status', 'note'].map(name => [name, {value: ''}]));
    function option(value, text, category = '', categoryName = '') {
        const input = {value, checked: false, disabled: false};
        const label = {textContent: text};
        return {input, hidden: false, textContent: text, dataset: {categoryId: category, categoryName},
            querySelector: selector => selector === 'input' ? input : label};
    }
    const options = [option('1', 'Apple iPhone'), option('2', 'Apple iPhone Pro'), option('3', 'Samsung Galaxy')];
    const serviceOptions = [option('8', 'Screen replacement', '5', 'Screen'), option('9', 'Battery replacement', '6', 'Battery')];
    const search = element({value: '', focus() {this.focused = true;}});
    const serviceSearch = element({value: '', focus() {this.focused = true;}});
    const category = element({value: ''});
    const all = element(), clear = element(), allServices = element(), clearServices = element();
    const error = {hidden: true}, serviceError = {hidden: true}, count = {textContent: ''}, serviceCount = {textContent: ''};
    const empty = {hidden: false}, message = {hidden: true};
    const picker = element({dataset: {deviceContext: context, canViewCost: viewCost ? '1' : '0'},
        querySelectorAll: () => options,
        querySelector: selector => ({'.repair-model-search': search, '.repair-model-error': error,
            '.repair-model-count': count, '.repair-models-all': all, '.repair-models-clear': clear})[selector]});
    const servicePicker = element({dataset: {categoryContext: ''}, querySelectorAll: () => serviceOptions,
        querySelector: selector => ({'.repair-service-search': serviceSearch, '.repair-service-category-filter': category,
            '.repair-service-error': serviceError, '.repair-service-count': serviceCount,
            '.repair-services-all': allServices, '.repair-services-clear': clearServices})[selector]});
    const body = {children: [], append(row) {this.children.push(row);}, replaceChildren() {this.children = [];}};
    const rowTemplate = {content: {firstElementChild: priceRow()}};
    const form = element({dataset: {}, elements: {namedItem: name => fields[name]},
        querySelector: selector => ({'.repair-price-model-picker': picker, '.repair-price-service-picker': servicePicker,
            '.repair-pair-price-body': body, '.repair-pair-price-template': rowTemplate,
            '.repair-model-prices-empty': empty, '.repair-copy-costs-message': message})[selector],
        reset() {
            Object.values(fields).forEach(field => {field.value = '';});
            fields.availability_status.value = 'available';
            [...options, ...serviceOptions].forEach(option => {option.input.checked = false;});
        }});
    const modal = element({querySelector: () => form});
    const triggers = [{dataset: {priceTemplate: JSON.stringify(template)}}];
    vm.runInNewContext(source, {document: {getElementById: () => modal, querySelectorAll: () => triggers}});
    const row = (model, service) => body.children.find(row => row.dataset.modelId === model && row.dataset.serviceId === service);
    return {modal, form, picker, servicePicker, options, serviceOptions, fields, search, serviceSearch, category,
        all, clear, allServices, clearServices, error, serviceError, count, serviceCount, body, message, row};
}

test('selecting two models and two services builds four prices with model, category, and service labels', () => {
    const env = setup();
    env.modal.fire('show.bs.modal');
    env.search.value = 'Apple';
    env.search.fire('input');
    env.all.fire('click');
    env.allServices.fire('click');
    assert.equal(env.body.children.filter(row => !row.hidden).length, 4);
    assert.equal(env.row('1', '8').fields.selling_price.name, 'pair_prices[1][8][selling_price]');
    assert.equal(env.row('1', '8').labels['[data-pair-category]'].textContent, 'Screen');
    assert.equal(env.row('2', '9').labels['[data-pair-category]'].textContent, 'Battery');
    assert.equal(env.row('2', '9').labels['[data-pair-model]'].textContent, 'Apple iPhone Pro');
    assert.equal(env.row('2', '9').fields.selling_price.disabled, false);
});

test('each model and service combination retains independent prices when selections change', () => {
    const env = setup({context: '1'});
    env.modal.fire('show.bs.modal');
    env.allServices.fire('click');
    env.row('1', '8').fields.selling_price.value = '55';
    env.row('1', '9').fields.selling_price.value = '25';
    env.options[1].input.checked = true;
    env.picker.fire('change');
    env.row('2', '8').fields.selling_price.value = '85';
    env.serviceOptions[0].input.checked = false;
    env.servicePicker.fire('change');
    assert.equal(env.row('1', '8').hidden, true);
    assert.equal(env.row('1', '8').fields.selling_price.disabled, true);
    env.serviceOptions[0].input.checked = true;
    env.servicePicker.fire('change');
    assert.equal(env.row('1', '8').fields.selling_price.value, '55');
    assert.equal(env.row('1', '9').fields.selling_price.value, '25');
    assert.equal(env.row('2', '8').fields.selling_price.value, '85');
});

test('category filtering and service search preserve services already selected in another category', () => {
    const env = setup({context: '1'});
    env.modal.fire('show.bs.modal');
    env.serviceOptions[1].input.checked = true;
    env.servicePicker.fire('change');
    env.category.value = '5';
    env.category.fire('change');
    assert.equal(env.serviceOptions[1].hidden, true);
    env.allServices.fire('click');
    assert.equal(env.serviceOptions[0].input.checked, true);
    assert.equal(env.serviceOptions[1].input.checked, true);
    assert.equal(env.row('1', '9').hidden, false);
    env.clearServices.fire('click');
    assert.equal(env.row('1', '9').fields.selling_price.disabled, true);
});

test('copy fills only the source service prices and permits different values for every target', () => {
    const env = setup();
    env.modal.fire('show.bs.modal', {relatedTarget: {dataset: {priceTemplate: JSON.stringify(template)}}});
    assert.equal(env.serviceOptions[0].input.checked, true);
    assert.equal(env.options[0].input.disabled, true);
    env.all.fire('click');
    assert.equal(env.row('2', '8').fields.selling_price.value, '75.00');
    assert.equal(env.row('3', '8').fields.part_cost.value, '35.50');
    env.row('2', '8').fields.selling_price.value = '80';
    env.row('3', '8').fields.selling_price.value = '110';
    env.serviceOptions[1].input.checked = true;
    env.servicePicker.fire('change');
    assert.equal(env.row('2', '9').fields.selling_price.value, '');
    assert.equal(env.row('2', '8').fields.selling_price.value, '80');
    assert.equal(env.row('3', '8').fields.selling_price.value, '110');
});

test('private copied costs are hidden only for the source service while other services allow their own costs', () => {
    const env = setup({viewCost: false});
    const data = {...template};
    delete data.part_cost;
    delete data.service_fee;
    env.modal.fire('show.bs.modal', {relatedTarget: {dataset: {priceTemplate: JSON.stringify(data)}}});
    env.all.fire('click');
    env.serviceOptions[1].input.checked = true;
    env.servicePicker.fire('change');
    assert.equal(env.row('2', '8').fields.part_cost.disabled, true);
    assert.equal(env.row('2', '8').fields.part_cost.hidden, true);
    assert.equal(env.row('2', '8').fields.part_cost.message.hidden, false);
    assert.equal(env.row('2', '9').fields.part_cost.disabled, false);
    assert.equal(env.row('2', '9').fields.part_cost.hidden, false);
});

test('validation recovery restores separate prices and selections before reopening', () => {
    const env = setup();
    env.form.fire('repair:restore-prices', {detail: {
        repair_device_ids: ['1', '2'], repair_service_ids: ['8', '9'],
        pair_prices: {1: {8: {selling_price: '55'}, 9: {selling_price: '25'}},
            2: {8: {selling_price: '85'}, 9: {selling_price: '40'}}},
    }});
    env.form.dataset.restoring = '1';
    env.modal.fire('show.bs.modal');
    assert.equal(env.row('1', '8').fields.selling_price.value, '55');
    assert.equal(env.row('1', '9').fields.selling_price.value, '25');
    assert.equal(env.row('2', '8').fields.selling_price.value, '85');
    assert.equal(env.row('2', '9').fields.selling_price.value, '40');
});

test('opening a new form after copying clears the source, all service selections, and all prices', () => {
    const env = setup({context: '1'});
    env.modal.fire('show.bs.modal', {relatedTarget: {dataset: {priceTemplate: JSON.stringify(template)}}});
    env.all.fire('click');
    env.modal.fire('show.bs.modal');
    assert.equal(env.fields.source_price_id.value, '');
    assert.equal(env.options[0].input.disabled, false);
    assert.equal(env.options[0].input.checked, true);
    assert.equal(env.serviceOptions[0].input.checked, false);
    assert.equal(env.body.children.length, 0);
});

test('both a model and a repair service are required before submitting', () => {
    const env = setup();
    env.modal.fire('show.bs.modal');
    let prevented = false;
    env.form.fire('submit', {preventDefault() {prevented = true;}});
    assert.equal(prevented, true);
    assert.equal(env.error.hidden, false);
    assert.equal(env.serviceError.hidden, false);
    env.options[1].input.checked = true;
    env.picker.fire('change');
    env.form.fire('submit', {preventDefault() {}});
    assert.equal(env.serviceSearch.focused, true);
    env.serviceOptions[0].input.checked = true;
    env.servicePicker.fire('change');
    prevented = false;
    env.form.fire('submit', {preventDefault() {prevented = true;}});
    assert.equal(prevented, false);
});
