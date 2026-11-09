const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/post-selection-popup.js'), 'utf8');

class Element {
    constructor() { this.children = []; this.events = {}; this.value = ''; this.textContent = ''; }
    addEventListener(type, handler) { this.events[type] = handler; }
    fire(type) { this.events[type]?.(); }
    append(...children) { this.children.push(...children); }
    replaceChildren(...children) { this.children = children; }
    focus() { this.focused = true; }
}

function setup() {
    const ids = ['postSelectionModal', 'postSelectionTitle', 'postSelectionSearch', 'postSelectionAll',
        'postSelectionCount', 'postSelectionOptions', 'postSelectionEmpty', 'postSelectionApply'];
    const elements = Object.fromEntries(ids.map(id => [id, new Element()]));
    const branch = Object.assign(new Element(), {id: 'branch_id', options: [
        {value: '', text: 'Select branch', disabled: true},
        {value: '1', text: 'Main', selected: true}, {value: '2', text: 'Other', selected: false},
    ]});
    const department = Object.assign(new Element(), {id: 'department_id', options: [
        {value: '10', text: 'Sales - Main', selected: true},
        {value: '11', text: 'Sales - Other', selected: true},
        {value: '12', text: 'Finance - Other', selected: false},
    ]});
    const selections = {branch, department};
    const changes = [];
    let shows = 0;
    const jQuery = element => ({
        on(type, handler) {element.events[type] = handler; return this;},
        val(values) {element.options.forEach(option => {option.selected = values.includes(option.value);}); return this;},
        trigger(type) {changes.push([element.id, type]); return this;},
        data() {return {$selection: {trigger() {}}};},
    });
    vm.runInNewContext(source, {
        document: {
            readyState: 'complete', getElementById: id => elements[id], createElement: () => new Element(),
            querySelectorAll: () => [branch, department],
            querySelector: selector => ({textContent: selector.includes('branch_id') ? 'Branch *' : 'Department *'}),
        },
        window: {jQuery, bootstrap: {Modal: {getOrCreateInstance: () => ({
            show() {shows++; elements.postSelectionModal.fire('shown.bs.modal');},
            hide() {elements.postSelectionModal.fire('hidden.bs.modal');},
        })}}},
    });
    return {
        ...elements, branch, department, changes, shows: () => shows,
        open(name) {
            let prevented = false;
            selections[name].events['select2:opening.postSelection']({preventDefault() {prevented = true;}});
            assert.equal(prevented, true);
        },
        selected(name) {return selections[name].options.filter(option => option.selected).map(option => option.value);},
        toggle(index, checked) {
            const checkbox = elements.postSelectionOptions.children[index].children[0];
            checkbox.checked = checked; checkbox.fire('change');
        },
        all(checked) {elements.postSelectionAll.checked = checked; elements.postSelectionAll.fire('change');},
        search(value) {elements.postSelectionSearch.value = value; elements.postSelectionSearch.fire('input');},
    };
}

test('opening uses saved selections and focuses the search field', () => {
    const state = setup(); state.open('branch');
    assert.equal(state.postSelectionTitle.textContent, 'Branch');
    assert.equal(state.postSelectionOptions.children.length, 2);
    assert.equal(state.postSelectionOptions.children[0].children[0].checked, true);
    assert.equal(state.postSelectionAll.indeterminate, true);
    assert.equal(state.postSelectionSearch.focused, true);
    assert.equal(state.shows(), 1);
});

test('Select All applies real branch values and emits the change that reloads departments', () => {
    const state = setup(); state.open('branch'); state.all(true);
    assert.equal(state.postSelectionAll.checked, true);
    assert.equal(state.postSelectionCount.textContent, '2 / 2');
    assert.deepEqual(state.selected('branch'), ['1']);
    state.postSelectionApply.fire('click');
    assert.deepEqual(state.selected('branch'), ['1', '2']);
    assert.deepEqual(state.changes, [['branch_id', 'change']]);
});

test('individual department choices apply without changing branch values', () => {
    const state = setup(); state.open('department'); state.toggle(1, false); state.toggle(2, true);
    state.postSelectionApply.fire('click');
    assert.deepEqual(state.selected('department'), ['10', '12']);
    assert.deepEqual(state.selected('branch'), ['1']);
});

test('cancelling discards the draft and reopening restores saved choices', () => {
    const state = setup(); state.open('department'); state.all(true);
    state.postSelectionModal.fire('hidden.bs.modal');
    assert.deepEqual(state.selected('department'), ['10', '11']);
    assert.deepEqual(state.changes, []);
    state.open('department');
    assert.equal(state.postSelectionCount.textContent, '2 / 3');
});

test('search keeps selections outside its results and Select All includes every option', () => {
    const state = setup(); state.open('department'); state.search('finance');
    assert.equal(state.postSelectionOptions.children.length, 1);
    state.toggle(0, true);
    assert.equal(state.postSelectionCount.textContent, '3 / 3');
    state.all(false);
    assert.equal(state.postSelectionApply.disabled, true);
    state.all(true); state.postSelectionApply.fire('click');
    assert.deepEqual(state.selected('department'), ['10', '11', '12']);
});

test('no results and no choices show the empty state and prevent an empty Apply', () => {
    const state = setup(); state.open('branch'); state.search('missing');
    assert.equal(state.postSelectionEmpty.hidden, false);
    state.postSelectionModal.fire('hidden.bs.modal');
    state.department.options = []; state.open('department');
    assert.equal(state.postSelectionAll.disabled, true);
    assert.equal(state.postSelectionApply.disabled, true);
    state.postSelectionApply.fire('click');
    assert.deepEqual(state.changes, []);
});

test('opening department reads the latest options after a branch change', () => {
    const state = setup(); state.open('department'); state.postSelectionModal.fire('hidden.bs.modal');
    state.department.options = [{value: '15', text: 'New Branch Department', selected: true}];
    state.open('department');
    assert.equal(state.postSelectionCount.textContent, '1 / 1');
    assert.equal(state.postSelectionOptions.children[0].children[1].textContent, 'New Branch Department');
});

test('a department selector cannot open while its choices are loading', () => {
    const state = setup(); state.department.disabled = true; state.open('department');
    assert.equal(state.shows(), 0);
});

function setupDepartmentLoader(initialIds = ['10'], autoSelectAll = false) {
    const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/post/common/scripts.blade.php'), 'utf8');
    const loaderSource = blade.slice(blade.indexOf('const loadDepartments = async'), blade.indexOf('const initializeDropdowns = async'))
        .replace(/\{\{[\s\S]*?\}\}/g, '/departments');
    const select = {options: [], disabled: false};
    const submit = {disabled: false};
    const pending = new Map();
    const jquerySelect = {
        find() {return {filter: callback => ({toArray: () => select.options.filter(option => callback.call(option))})};},
        val() {return select.options.filter(option => option.selected).map(option => String(option.value));},
        empty() {select.options = []; return this;},
        trigger() {return this;},
        append(option) {select.options.push(typeof option === 'string' ? {value: '', disabled: true} : option); return this;},
        prop(name, value) {if (arguments.length === 1) return select[name]; select[name] = value; return this;},
        closest() {return {find: () => ({prop(name, value) {submit[name] = value;}})};},
    };
    const jquery = selector => selector === '#department_id' ? jquerySelect : {text: () => selector};
    jquery.ajax = ({url}) => new Promise((resolve, reject) => pending.set(url.split('/').pop(), {resolve, reject}));
    const context = {
        $: jquery, selectedDepartmentIds: initialIds, autoSelectAllDepartments: autoSelectAll,
        Option: function (text, value, defaultSelected, selected) {Object.assign(this, {text, value: String(value), selected});},
    };
    vm.createContext(context);
    vm.runInContext('let departmentLoadVersion = 0; let retainedDepartmentIds = selectedDepartmentIds; let retainAllDepartments = autoSelectAllDepartments;\n'
        + loaderSource + '\nglobalThis.load = loadDepartments;', context);
    return {
        select, submit, load: context.load,
        respond(branch, ids) {pending.get(String(branch)).resolve({data: ids.map(id => ({id, dept_name: `Department ${id}`}))});},
        selected() {return select.options.filter(option => option.selected).map(option => option.value);},
    };
}

test('initial Edit Post load restores saved department selections', async () => {
    const state = setupDepartmentLoader(['11']); const loading = state.load(['1'], true);
    assert.equal(state.select.disabled, true); assert.equal(state.submit.disabled, true);
    state.respond(1, [10, 11]); await loading;
    assert.deepEqual(state.selected(), ['11']);
    assert.equal(state.select.disabled, false); assert.equal(state.submit.disabled, false);
});

test('branch changes keep the latest department choices instead of the original saved choices', async () => {
    const state = setupDepartmentLoader(['10']); let loading = state.load(['1'], true);
    state.respond(1, [10, 11]); await loading;
    state.select.options.forEach(option => {option.selected = option.value === '11';});
    loading = state.load(['1', '2']); state.respond(1, [10, 11]); state.respond(2, [20]); await loading;
    assert.deepEqual(state.selected(), ['11']);
});

test('All departments stays All when branches are added', async () => {
    const state = setupDepartmentLoader(['10', '11']); let loading = state.load(['1'], true);
    state.respond(1, [10, 11]); await loading;
    loading = state.load(['1', '2']); state.respond(1, [10, 11]); state.respond(2, [20]); await loading;
    assert.deepEqual(state.selected(), ['10', '11', '20']);
});

test('rapid branch changes retain the latest draft and ignore stale department responses', async () => {
    const state = setupDepartmentLoader(['10']); let loading = state.load(['1'], true);
    state.respond(1, [10, 11]); await loading;
    state.select.options.forEach(option => {option.selected = option.value === '11';});
    const stale = state.load(['2']);
    const latest = state.load(['3']);
    state.respond(3, [11, 30]); await latest;
    state.respond(2, [20]); await stale;
    assert.deepEqual(state.selected(), ['11']);
    assert.deepEqual(state.select.options.map(option => option.value), ['11', '30']);
    assert.equal(state.submit.disabled, false);
});
