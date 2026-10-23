const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-profile-review.js'), 'utf8');

function setup(search, {loading = false, canReview = true, canSalary = false, reviewForm = null, salaryError = 'New base salary must be greater than old base salary.'} = {}) {
    const events = {};
    const shown = [];
    const moved = [];
    const modals = {};
    if (canReview) modals.addPerformanceReviewModal = {id: 'addPerformanceReviewModal', dataset: {}};
    if (canSalary) modals.addSalaryHistoryModal = {id: 'addSalaryHistoryModal'};
    function field(value = '') {
        return {value, addEventListener(event, callback) {this[event] = callback;}, setCustomValidity(message) {this.error = message;}};
    }
    const fields = {
        performance_review_id: field('12'), approval_status: field('draft'),
        old_base_salary: field('500'), new_base_salary: field('550'),
        increase_amount: field(), increase_percentage: field(), effective_date: field(),
    };
    fields.approval_status.options = ['draft', 'pending', 'approved', 'rejected'].map(value => ({value}));
    vm.runInNewContext(source, {
        URLSearchParams,
        document: {
            readyState: loading ? 'loading' : 'complete',
            body: {appendChild(modal) {moved.push(modal.id);}},
            getElementById: id => id === 'reviewCreateForm' ? reviewForm : modals[id] || null,
            querySelector: () => canSalary ? {elements: {namedItem: name => fields[name]}, dataset: {salaryIncreaseError: salaryError}} : null,
            addEventListener: (event, callback) => {events[event] = callback;},
        },
        window: {location: {search}, bootstrap: {Modal: {getOrCreateInstance(modal) {
            assert.ok(moved.includes(modal.id), 'dialog must be outside hidden tab before opening');
            return {show() {shown.push(modal.id);}};
        }}}},
    });
    return {shown, moved, fields, ready: () => events.DOMContentLoaded()};
}

test('Full review opens its dialog outside the hidden tab after DOM is ready', () => {
    const state = setup('?tab=evaluation&review_create=1', {loading: true});
    assert.deepEqual(state.shown, []);
    state.ready();
    assert.deepEqual(state.shown, ['addPerformanceReviewModal']);
});

test('salary validation displays the Khmer message supplied by the form', () => {
    const salaryError = 'ប្រាក់ខែគោលថ្មីត្រូវតែច្រើនជាងប្រាក់ខែគោលចាស់។';
    const {fields} = setup('?salary_review=12', {canSalary: true, salaryError});
    fields.new_base_salary.value = '450';
    fields.new_base_salary.input();
    assert.equal(fields.new_base_salary.error, salaryError);
});

test('Full review opens when script loads after the DOM', () => {
    assert.deepEqual(setup('?tab=evaluation&review_create=1').shown, ['addPerformanceReviewModal']);
});

test('ordinary profile navigation does not open a dialog', () => {
    assert.deepEqual(setup('?tab=evaluation').shown, []);
});

test('missing review permission does not attempt to open an absent dialog', () => {
    assert.deepEqual(setup('?review_create=1', {canReview: false}).shown, []);
});

test('salary action opens a linked pending request with calculated increase', () => {
    const {shown, fields} = setup('?tab=salary&salary_review=12', {canSalary: true});
    assert.deepEqual(shown, ['addSalaryHistoryModal']);
    assert.equal(fields.approval_status.value, 'pending');
    assert.equal(fields.approval_status.options.find(option => option.value === 'approved').disabled, true);
    assert.equal(fields.increase_amount.value, '50.00');
    assert.equal(fields.increase_percentage.value, '10.00');
    assert.equal(fields.increase_amount.readOnly, true);
    fields.new_base_salary.value = '600';
    fields.new_base_salary.input();
    assert.equal(fields.increase_amount.value, '100.00');
    assert.equal(fields.increase_percentage.value, '20.00');
    fields.new_base_salary.value = '450';
    fields.new_base_salary.input();
    assert.ok(fields.new_base_salary.error);
    fields.performance_review_id.value = '';
    fields.performance_review_id.change();
    assert.equal(fields.increase_amount.readOnly, false);
    assert.equal(fields.new_base_salary.error, '');
});

test('criteria can be added, scored, and removed while keeping at least one row', () => {
    const events = {};
    const rows = {
        children: [],
        querySelectorAll() { return this.children.map(row => row.button); },
        appendChild(row) { this.children.push(row); },
        get lastElementChild() { return this.children.at(-1); },
        addEventListener(event, callback) { events[event] = callback; },
    };
    function makeRow() {
        const maximum = {value: '10'};
        const score = {value: '0'};
        const criterion = {focus() { this.focused = true; }};
        const row = {
            maximum, score, criterion,
            querySelector: selector => ({'[data-review-max]': maximum, '[data-review-score]': score, '[name="criteria[]"]': criterion})[selector],
            remove() { rows.children.splice(rows.children.indexOf(row), 1); },
        };
        row.button = {closest: () => row};
        return row;
    }
    const original = makeRow();
    rows.children.push(original);
    const add = {addEventListener(event, callback) { this.click = callback; }};
    setup('', {reviewForm: {querySelector: selector => ({
        '[data-review-criteria]': rows,
        '[data-review-criterion-template]': {content: {cloneNode: makeRow}},
        '[data-add-review-criterion]': add,
    })[selector]}});
    assert.equal(original.button.disabled, true);
    add.click();
    assert.equal(rows.children.length, 2);
    const added = rows.lastElementChild;
    assert.equal(added.criterion.focused, true);
    assert.equal(original.button.disabled, false);
    added.score.value = '20';
    events.input({target: {matches: () => true, closest: () => added}});
    assert.equal(added.score.value, '10');
    added.maximum.value = '5';
    events.input({target: {matches: () => true, closest: () => added}});
    assert.equal(added.score.max, '5');
    assert.equal(added.score.value, '5');
    events.click({target: {closest: () => original.button}});
    assert.deepEqual(rows.children, [added]);
    assert.equal(added.button.disabled, true);
    events.click({target: {closest: () => added.button}});
    assert.equal(rows.children.length, 1);
});
