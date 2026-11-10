const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const view = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/repairPrice/index.blade.php'), 'utf8');
const source = view.match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/\{\{\s*url\('([^']+)'\)\s*\}\}/g, '/$1')
    .replace(/\{\{[\s\S]*?\}\}/g, 'Translated text');

function setup(confirmed = false) {
    const handlers = {};
    const elements = new Map();
    const shown = [];
    const submissions = [];
    const document = {getElementById: id => ({id})};
    function $(selector) {
        if (selector === document) return {ready: callback => callback()};
        if (typeof selector === 'object') return {data: key => selector.data[key], find: () => ({data: key => selector.button.data[key]})};
        if (!elements.has(selector)) elements.set(selector, {value: null, attrs: {}, props: {}});
        const element = elements.get(selector);
        return {
            on: (event, callback) => {handlers[selector + ':' + event] = callback;},
            val: value => {element.value = value;},
            attr: (key, value) => {element.attrs[key] = value;},
            prop: (key, value) => {element.props[key] = value;},
            // Deliberately omit the jQuery modal plugin, as in the actual layout.
        };
    }
    vm.runInNewContext(source, {
        $, document,
        bootstrap: {Modal: {getOrCreateInstance: element => ({show: () => shown.push(element.id)})}},
        Swal: {fire: async () => ({isConfirmed: confirmed})},
        HTMLFormElement: {prototype: {submit() {submissions.push(this);}}},
    });
    return {handlers, elements, shown, submissions};
}

test('all edit buttons open Bootstrap modals without the jQuery plugin and target the correct record', () => {
    const env = setup();
    for (const [entity, plural] of [['brand', 'brands'], ['category', 'categories'], ['price', 'prices'], ['device', 'devices'], ['service', 'services']]) {
        const name = entity[0].toUpperCase() + entity.slice(1);
        env.handlers[`.edit-${entity}-btn:click`].call({data: {id: 42, name: 'Screen', 'selling-price': '75.00', status: 1}});
        assert.equal(env.shown.at(-1), `edit${name}Modal`);
        assert.equal(env.elements.get(`#edit${name}Form`).attrs.action, `/admin/repair-price/${plural}/42`);
    }
    assert.equal(env.elements.get('#edit_price_selling_price').value, '75.00');
    assert.equal(env.elements.get('#edit_brand_status').props.checked, true);
});

for (const confirmed of [false, true]) {
    test(`delete ${confirmed ? 'confirmation submits' : 'cancellation preserves'} the selected record`, async () => {
        const env = setup(confirmed);
        const form = {button: {data: {title: 'Screen'}}};
        let prevented = false;
        env.handlers['.repair-delete-form:submit'].call(form, {preventDefault() {prevented = true;}});
        await Promise.resolve();
        assert.equal(prevented, true);
        assert.equal(env.submissions.length, confirmed ? 1 : 0);
        if (confirmed) assert.equal(env.submissions[0], form);
    });
}
