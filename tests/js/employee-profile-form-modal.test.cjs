const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-profile-form-modal.js'), 'utf8');
function setup() {
    const events = {}, frameEvents = {}, childEvents = {}, modalEvents = {};
    const title = {textContent: 'Employee profile'};
    const child = {addEventListener: (type, callback) => {childEvents[type] = callback;}, createElement: () => ({})};
    const frame = {name: 'employeeProfileFormFrame', contentDocument: child, contentWindow: {location: {href: 'https://hr.test/admin/employees/20/profile?employee_modal=1'}},
        addEventListener: (type, callback) => {frameEvents[type] = callback;}, removeAttribute(name) {delete this[name];}};
    const modal = {dataset: {directoryUrl: '/admin/employees/profile-directory'},
        querySelector: selector => selector === '.modal-title' ? title : frame,
        addEventListener: (type, callback) => {modalEvents[type] = callback;}};
    const calls = {show: 0, hide: 0, reload: 0};
    const bootstrap = {Modal: {getOrCreateInstance: element => element === modal
        ? {show() {calls.show++;}, hide() {calls.hide++;}} : {hide() {element.hidden = true;}}}};
    vm.runInNewContext(source, {bootstrap, URL, FormData: class {constructor(form) {return form.values;}},
        document: {getElementById: () => modal, addEventListener: (type, callback) => {events[type] = callback;}},
        window: {bootstrap, location: {href: 'https://hr.test/admin/employees/profile-directory?search=Sam', origin: 'https://hr.test', reload() {calls.reload++;}}}});
    const link = (href, extra = {}) => ({href, title: '', textContent: 'Employee 360', dataset: {}, target: '',
        getAttribute: name => name === 'href' ? href : null, closest: () => null, hasAttribute: () => false, matches: () => false, ...extra});
    const click = (handler, trigger) => {const event = {target: {closest: () => trigger}, preventDefault() {this.prevented = true;}}; handler(event); return event;};
    return {events, frameEvents, childEvents, modalEvents, title, frame, calls, link, click};
}

test('directory forms open over the directory and preserve employee and selected tab', () => {
    const state = setup();
    const link = state.link('/admin/employees/20/profile?tab=salary-certificate');
    assert.equal(state.click(state.events.click, link).prevented, true);
    const url = new URL(state.frame.src);
    assert.equal(url.pathname, '/admin/employees/20/profile');
    assert.equal(url.searchParams.get('tab'), 'salary-certificate');
    assert.equal(url.searchParams.get('employee_modal'), '1');
    assert.equal(state.calls.show, 1);
    state.modalEvents['hidden.bs.modal']();
    assert.equal(state.calls.reload, 0);
    assert.equal(state.frame.src, undefined);
});

test('full review switches from the quick review modal after it closes', () => {
    const state = setup(); let afterHidden;
    const previous = {addEventListener: (type, callback) => {afterHidden = callback;}};
    const link = state.link('/admin/employees/20/profile?tab=evaluation&review_create=1', {closest: () => previous});
    state.click(state.events.click, link);
    assert.equal(previous.hidden, true); assert.equal(state.calls.show, 0);
    afterHidden(); assert.equal(state.calls.show, 1);
    assert.equal(new URL(state.frame.src).searchParams.get('review_create'), '1');
});

test('external links and modified clicks retain normal browser behavior', () => {
    const state = setup();
    assert.equal(state.click(state.events.click, state.link('https://other.test/employee')).prevented, undefined);
    state.events.click({ctrlKey: true, target: {closest: () => state.link('/admin/employees/20/profile')}, preventDefault() {assert.fail();}});
    assert.equal(state.calls.show, 0);
});

test('saving stays embedded and refreshes the directory only when closing', () => {
    const state = setup(); state.frameEvents.load();
    const fields = [];
    const form = {action: 'https://hr.test/admin/employees/20/profile/salary', method: 'post', querySelector: () => null, appendChild: field => fields.push(field)};
    state.childEvents.submit({target: form});
    assert.equal(new URL(form.action).searchParams.get('employee_modal'), '1');
    assert.equal(fields[0].name, 'employee_modal');
    assert.equal(state.calls.reload, 0);
    state.modalEvents['hidden.bs.modal']();
    assert.equal(state.calls.reload, 1);
});

test('server redirects retain the modal and restore the embedded layout', () => {
    const state = setup();
    state.frame.contentWindow.location.href = 'https://hr.test/admin/employees/20/profile?tab=salary';
    state.frameEvents.load();
    const url = new URL(state.frame.src);
    assert.equal(url.searchParams.get('employee_modal'), '1');
    assert.equal(url.searchParams.get('tab'), 'salary');
    assert.equal(state.calls.hide, 0);
});

test('salary forms stay in the same modal and returning to the directory closes it', () => {
    const state = setup(); state.frameEvents.load();
    state.click(state.childEvents.click, state.link('https://hr.test/admin/employee-salaries/20/edit'));
    assert.equal(new URL(state.frame.src).pathname, '/admin/employee-salaries/20/edit');
    assert.equal(new URL(state.frame.src).searchParams.get('employee_modal'), '1');
    state.click(state.childEvents.click, state.link('https://hr.test/admin/employees/profile-directory'));
    assert.equal(state.calls.hide, 1);
});

test('existing leave request modal controls keep their own handlers', () => {
    const state = setup(); state.frameEvents.load();
    const link = state.link('/admin/leave-request/add?requested_by=20', {matches: () => true});
    assert.equal(state.click(state.childEvents.click, link).prevented, undefined);
});

test('attendance report opens as a modal using all selected filters', () => {
    const state = setup(); let prevented = false;
    const submitter = {name: 'attendance_report', value: '1', textContent: 'Attendance report', hasAttribute: () => true};
    state.events.submit({submitter, target: {hasAttribute: () => false, action: 'https://hr.test/admin/employees/profile-directory?department_ids[]=9&search=old', values: [['department_ids[]', '2'], ['department_ids[]', '3'], ['search', 'Sam']]}, preventDefault() {prevented = true;}});
    const url = new URL(state.frame.src);
    assert.equal(prevented, true);
    assert.deepEqual(url.searchParams.getAll('department_ids[]'), ['2', '3']);
    assert.equal(url.searchParams.get('attendance_report'), '1');
    assert.equal(state.calls.show, 1);
});

test('quick review saves inside the form modal instead of navigating away from the directory', () => {
    const state = setup(); let afterHidden;
    const previous = {querySelector: () => ({textContent: 'Quick review'}), addEventListener: (type, callback) => {afterHidden = callback;}};
    const form = {action: 'https://hr.test/admin/employees/20/profile/reviews', hasAttribute: () => true, closest: () => previous};
    state.events.submit({target: form, submitter: {}});
    assert.equal(form.target, 'employeeProfileFormFrame');
    assert.equal(new URL(form.action).searchParams.get('employee_modal'), '1');
    assert.equal(previous.hidden, true);
    afterHidden(); assert.equal(state.calls.show, 1);
    state.modalEvents['hidden.bs.modal'](); assert.equal(state.calls.reload, 1);
});

test('saved nested request forms refresh the directory after closing the profile modal', () => {
    const state = setup();
    state.frame.contentDocument.querySelector = () => ({});
    state.frameEvents.load();
    state.modalEvents['hidden.bs.modal']();
    assert.equal(state.calls.reload, 1);
});
