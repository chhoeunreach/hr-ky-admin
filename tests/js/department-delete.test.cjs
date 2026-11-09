const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/department-delete.js'), 'utf8');
const flush = async () => {for (let i = 0; i < 5; i++) await new Promise(resolve => setImmediate(resolve));};
class Element {
    constructor() {this.events = {}; this.hidden = false; this.disabled = false; this.checked = false; this.children = [];}
    addEventListener(type, callback) {this.events[type] = callback;}
    fire(type, event = {}) {return this.events[type]?.(event);}
    replaceChildren(...children) {this.children = children;}
    querySelectorAll() {return [];}
    append(...children) {this.children.push(...children);}
}
function setup() {
    const elements = Object.fromEntries(['UnlinkRecords', 'UnlinkOption', 'UnlinkCount', 'Modal', 'Messages', 'Confirm', 'Refresh', 'RemovePositions', 'Error', 'Loading', 'Details',
        'Corrections', 'FooterCorrections', 'CorrectEmployees', 'CorrectPositions', 'Name', 'Scope', 'Branches', 'PositionCount', 'Positions', 'BlockerList', 'Blockers', 'RemoveOption', 'Permission'].map(name => [name, new Element()]));
    elements.Messages.textContent = JSON.stringify({scope: 'Applies to :count departments', empty: 'No positions', inactive: 'Inactive', failed: 'Failed'});
    let preview = {name: 'Sales', departments_count: 2, branches: ['Main', 'Other'], positions_count: 2,
        positions: [{name: 'Staff', branch: 'Main', active: true}, {name: 'Manager', branch: 'Other', active: false}],
        blockers: [], can_remove_positions: true};
    let readFailure = false;
    let deleteFailure = false;
    let pendingDelete = null;
    const calls = [];
    const events = [];
    const documentEvents = {};
    let shows = 0;
    let hides = 0;
    vm.runInNewContext(source, {
        document: {
            readyState: 'complete',
            getElementById: id => elements[id.replace('departmentDelete', '')],
            createElement: () => new Element(),
            addEventListener: (type, callback) => {documentEvents[type] = callback;},
            dispatchEvent: event => events.push(event.type),
            querySelector: () => ({content: 'token'}),
        },
        fetch: async (url, options) => {
            calls.push({url, options});
            if (options.method === 'DELETE') {
                if (pendingDelete) await pendingDelete;
                return {ok: !deleteFailure, json: async () => ({message: deleteFailure ? 'Reassign first' : 'Deleted'})};
            }
            return {ok: !readFailure, json: async () => readFailure ? {message: 'Preview failed'} : JSON.parse(JSON.stringify(preview))};
        },
        AbortController, CustomEvent: function (type) {this.type = type;},
        window: {bootstrap: {Modal: {getOrCreateInstance: () => ({
            show() {shows++;},
            hide() {let prevented = false; elements.Modal.fire('hide.bs.modal', {preventDefault() {prevented = true;}});
                if (!prevented) {hides++; elements.Modal.fire('hidden.bs.modal');}},
        })}}},
    });
    return {
        ...elements, calls, events, shows: () => shows, hides: () => hides,
        open() {documentEvents.click({target: {closest: () => ({dataset: {href: '/delete/1'}})}, preventDefault() {}});},
        setBlockers(value) {preview.blockers = value;},
        permission(value) {preview.can_remove_positions = value;},
        empty() {preview.positions_count = 0; preview.positions = [];},
        readFailure(value) {readFailure = value;},
        deleteFailure(value) {deleteFailure = value;},
        holdDeletion(promise) {pendingDelete = promise;},
    };
}

test('preview lists all linked positions and requires explicit removal consent', async () => {
    const state = setup(); state.open(); await flush();
    assert.equal(state.shows(), 1);
    assert.equal(state.Positions.children.length, 2);
    assert.match(state.Positions.children[1].textContent, /Inactive/);
    assert.equal(state.Confirm.disabled, true);
    state.RemovePositions.checked = true; state.RemovePositions.fire('change');
    assert.equal(state.Confirm.disabled, false);
    assert.equal(state.calls.length, 1);
    assert.equal(state.calls[0].options.method, undefined);
});

test('confirmed deletion sends DELETE with CSRF and refreshes the table', async () => {
    const state = setup(); state.open(); await flush();
    state.RemovePositions.checked = true; state.RemovePositions.fire('change');
    await state.Confirm.fire('click');
    assert.equal(state.calls[1].options.method, 'DELETE');
    assert.deepEqual(JSON.parse(state.calls[1].options.body), {remove_positions: true, unlink_records: false});
    assert.equal(state.calls[1].options.headers['X-CSRF-TOKEN'], 'token');
    assert.deepEqual(state.events, ['department:deleted']);
    assert.equal(state.hides(), 1);
});

test('employee/history blockers prevent deletion even if removal is selected', async () => {
    const state = setup(); state.setBlockers([{label: 'Employees', count: 3}]); state.open(); await flush();
    assert.equal(state.Blockers.hidden, false);
    assert.equal(state.BlockerList.children[0].textContent, 'Employees: 3');
    assert.equal(state.RemovePositions.disabled, true);
    state.RemovePositions.checked = true; state.RemovePositions.fire('change'); await state.Confirm.fire('click');
    assert.equal(state.calls.length, 1);
    assert.equal(state.Confirm.disabled, true);
});

test('Refresh check clears the block after assignments change and resets removal consent', async () => {
    const state = setup(); state.setBlockers([{label: 'Employees', count: 1}]); state.open(); await flush();
    state.setBlockers([]); await state.Refresh.fire('click');
    assert.equal(state.Blockers.hidden, true);
    assert.equal(state.RemovePositions.disabled, false);
    assert.equal(state.RemovePositions.checked, false);
    assert.equal(state.Confirm.disabled, true);
});

test('users without position delete permission cannot remove positions', async () => {
    const state = setup(); state.permission(false); state.open(); await flush();
    assert.equal(state.RemoveOption.hidden, true);
    assert.equal(state.Permission.hidden, false);
    assert.equal(state.Confirm.disabled, true);
});

test('server rejection keeps the dialog open and does not refresh results', async () => {
    const state = setup(); state.deleteFailure(true); state.open(); await flush();
    state.RemovePositions.checked = true; state.RemovePositions.fire('change'); await state.Confirm.fire('click');
    assert.equal(state.Error.hidden, false);
    assert.equal(state.Error.textContent, 'Reassign first');
    assert.equal(state.hides(), 0);
    assert.deepEqual(state.events, []);
});

test('a failed dependency check is recoverable using Refresh check', async () => {
    const state = setup(); state.readFailure(true); state.open(); await flush();
    assert.equal(state.Error.textContent, 'Preview failed');
    assert.equal(state.Confirm.disabled, true);
    state.readFailure(false); await state.Refresh.fire('click');
    assert.equal(state.Error.hidden, true);
    assert.equal(state.Details.hidden, false);
});

test('an empty department needs no position removal option', async () => {
    const state = setup(); state.empty(); state.open(); await flush();
    assert.equal(state.RemoveOption.hidden, true);
    assert.equal(state.Confirm.disabled, false);
    await state.Confirm.fire('click');
    assert.deepEqual(JSON.parse(state.calls[1].options.body), {remove_positions: false, unlink_records: false});
});

test('the dialog cannot close during deletion', async () => {
    const state = setup(); state.empty(); state.open(); await flush();
    let release; state.holdDeletion(new Promise(resolve => {release = resolve;}));
    const deleting = state.Confirm.fire('click');
    let prevented = false; state.Modal.fire('hide.bs.modal', {preventDefault() {prevented = true;}});
    assert.equal(prevented, true);
    assert.equal(state.Refresh.disabled, true);
    release(); await deleting;
    assert.equal(state.hides(), 1);
});

test('optional leave references require explicit unlink consent before deletion', async () => {
    const state = setup(); state.setBlockers([{label: 'Leave Requests Master', count: 23, can_unlink: true}, {label: 'Time Leaves', count: 1, can_unlink: true}]);
    state.open(); await flush();
    assert.equal(state.UnlinkOption.hidden, false);
    assert.equal(state.UnlinkCount.textContent, 24);
    assert.equal(state.RemovePositions.disabled, false);
    state.RemovePositions.checked = true; state.RemovePositions.fire('change');
    assert.equal(state.Confirm.disabled, true);
    state.UnlinkRecords.checked = true; state.UnlinkRecords.fire('change');
    assert.equal(state.Confirm.disabled, false);
    await state.Confirm.fire('click');
    assert.deepEqual(JSON.parse(state.calls[1].options.body), {remove_positions: true, unlink_records: true});
});
