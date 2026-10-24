const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/resignation/common/scripts.blade.php'), 'utf8');
const source = blade.slice(blade.indexOf('        const initialBranchId'), blade.lastIndexOf('    });'))
    .replace(/const initialBranchId = .*;/, "const initialBranchId = '1';")
    .replace(/const initialDepartmentId = .*;/, "const initialDepartmentId = '2';")
    .replace(/const initialEmployeeId = .*;/, "const initialEmployeeId = '20';")
    .replace(/@json\(url\('([^']+)'\)\)/g, (_, value) => JSON.stringify('/' + value))
    .replace(/@json\(__\('index\.([^']+)'\)\)/g, (_, key) => JSON.stringify(key));
const flush = async () => {for (let i = 0; i < 10; i++) await new Promise(resolve => setImmediate(resolve));};

function setup(information = async id => ({data: {khmer_name: 'Employee ' + id, gender: 'Female'}})) {
    const selectors = new Map();
    function $(selector) {
        if (!selectors.has(selector)) {
            selectors.set(selector, {
                length: 1, value: selector === '#branch_id' ? '1' : '', options: [], handlers: {},
                empty() {this.options = []; this.value = ''; return this;},
                append(option) {this.options.push(option); if (option.selected) this.value = String(option.value); return this;},
                find() {return {prop() {}};},
                prop(key, value) {this[key] = value; return this;},
                trigger() {return this;},
                val(value) {if (value === undefined) return this.value; this.value = value; return this;},
                on(event, handler) {this.handlers[event] = handler; return this;},
            });
        }
        return selectors.get(selector);
    }
    const fields = [
        {dataset: {letterEmployeeField: 'khmer_name'}, value: 'Saved name'},
        {dataset: {letterEmployeeField: 'gender'}, value: 'male'},
    ];
    const button = {disabled: false};
    const status = {textContent: ''};
    let submit;
    const context = vm.createContext({
        $, Option: function (text, value, defaultSelected, selected) {Object.assign(this, {text, value, selected});},
        document: {
            querySelectorAll: () => fields,
            getElementById: id => id === 'resignationForm'
                ? {querySelectorAll: () => [button], addEventListener: (event, handler) => {submit = handler;}}
                : id === 'resignationEmployeeStatus' ? status : null,
        },
        fetch: async url => {
            let result;
            if (url.includes('get-All-Departments')) result = {data: [{id: 2, dept_name: 'HR'}, {id: 3, dept_name: 'Sales'}]};
            else if (url.includes('get-all-employees')) result = {data: [{id: 20, name: 'Original'}, {id: 21, name: 'New'}, {id: 22, name: 'Latest'}]};
            else result = await information(url.split('/').at(-1));
            return {ok: true, json: async () => result};
        },
    });
    vm.runInContext(source, context);
    return {$, fields, button, status, submit: event => submit(event)};
}

test('restores selected employee and preserves saved letter corrections on initial load', async () => {
    const form = setup(async () => {throw new Error('Initial saved information must be preserved');});
    await flush();
    assert.equal(form.$('#department_id').val(), '2');
    assert.equal(form.$('#employee_id').val(), '20');
    assert.equal(form.fields[0].value, 'Saved name');
    assert.equal(form.button.disabled, false);
});

test('changing department clears employee selection and loads the newly selected employee', async () => {
    const form = setup();
    await flush();
    form.$('#department_id').val('3').handlers.change();
    await flush();
    assert.equal(form.$('#employee_id').val(), '');
    assert.equal(form.fields[0].value, '');
    await form.$('#employee_id').val('21').handlers.change();
    assert.equal(form.fields[0].value, 'Employee 21');
    assert.equal(form.fields[1].value, 'female');
});

test('ignores stale employee responses and prevents saving while information is loading', async () => {
    const pending = new Map();
    const form = setup(id => new Promise(resolve => pending.set(id, resolve)));
    await flush();
    const first = form.$('#employee_id').val('21').handlers.change();
    const second = form.$('#employee_id').val('22').handlers.change();
    assert.equal(form.button.disabled, true);
    let prevented = false;
    form.submit({preventDefault() {prevented = true;}});
    assert.equal(prevented, true);
    pending.get('22')({data: {khmer_name: 'Latest employee'}});
    await second;
    pending.get('21')({data: {khmer_name: 'Old response'}});
    await first;
    assert.equal(form.fields[0].value, 'Latest employee');
    assert.equal(form.button.disabled, false);
});

test('reports auto-fill failure and leaves manual completion available', async () => {
    const form = setup(async () => {throw new Error('offline');});
    await flush();
    await form.$('#employee_id').val('21').handlers.change();
    assert.equal(form.status.textContent, 'resignation_employee_load_error');
    assert.equal(form.button.disabled, false);
});
