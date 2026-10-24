const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/employee-request-form-modal.js'), 'utf8');

function setup() {
    const handlers = {};
    const frame = {removeAttribute(name) { delete this[name]; }};
    const shown = [];
    const navigated = [];
    const hidden = [];
    frame.contentWindow = {};
    const title = {textContent: 'Leave Request Form'};
    const modal = {
        querySelector: selector => selector === '.modal-title' ? title : frame,
        addEventListener: (name, handler) => { handlers[name] = handler; },
    };
    const bootstrap = {Modal: {getOrCreateInstance: element => {
        assert.equal(element, modal);
        return {show: trigger => shown.push(trigger), hide: () => hidden.push(true)};
    }}};
    vm.runInNewContext(source, {
        document: {
            getElementById: () => modal,
            addEventListener: (name, handler) => { handlers[name] = handler; },
        },
        window: {bootstrap, addEventListener: (name, handler) => {handlers[name] = handler;},
            location: {href: 'http://localhost/admin/leave-request', origin: 'http://localhost', assign: url => navigated.push(url)}},
        bootstrap,
        URL,
    });
    return {handlers, frame, shown, title, hidden, navigated};
}

test('clicking a request icon opens its saved form in a modal and prevents navigation', () => {
    const {handlers, frame, shown} = setup();
    const trigger = {getAttribute: () => '/admin/employees/20/profile?tab=leave-letter&leave_request_id=41'};
    let prevented = false;
    handlers.click({
        target: {closest: () => trigger},
        preventDefault() { prevented = true; },
    });
    assert.equal(prevented, true);
    assert.deepEqual(shown, [trigger]);
    const url = new URL(frame.src);
    assert.equal(url.searchParams.get('leave_request_id'), '41');
    assert.equal(url.searchParams.get('tab'), 'leave-letter');
    assert.equal(url.searchParams.get('request_modal'), '1');
    handlers['hidden.bs.modal']();
    assert.equal(frame.src, undefined);
});

test('unrelated clicks do not open a modal or prevent navigation', () => {
    const {handlers, shown} = setup();
    handlers.click({
        target: {closest: () => null},
        preventDefault() { assert.fail('Unrelated click was intercepted'); },
    });
    assert.deepEqual(shown, []);
});

test('a button with an explicit form URL opens the modal without navigating', () => {
    const {handlers, frame, shown} = setup();
    const trigger = {getAttribute: name => name === 'data-request-form-url'
        ? '/admin/employees/20/profile?tab=leave-letter' : null};
    let prevented = false;
    handlers.click({
        target: {closest: () => trigger},
        preventDefault() { prevented = true; },
    });
    assert.equal(prevented, true);
    assert.deepEqual(shown, [trigger]);
    const url = new URL(frame.src);
    assert.equal(url.searchParams.get('tab'), 'leave-letter');
    assert.equal(url.searchParams.get('request_modal'), '1');
});

test('creating leave opens the entry form for the selected employee on the current page', () => {
    const {handlers, frame, shown} = setup();
    const trigger = {getAttribute: name => name === 'href'
        ? '/admin/leave-request/add?requested_by=20' : null};
    let prevented = false;
    handlers.click({target: {closest: () => trigger}, preventDefault() { prevented = true; }});
    assert.equal(prevented, true);
    assert.deepEqual(shown, [trigger]);
    const url = new URL(frame.src);
    assert.equal(url.pathname, '/admin/leave-request/add');
    assert.equal(url.searchParams.get('requested_by'), '20');
    assert.equal(url.searchParams.get('request_modal'), '1');
});

test('Request List for employee 583 opens over the profile and preserves the employee filter', () => {
    const selector = fs.readFileSync(path.join(__dirname,
        '../../resources/views/admin/employees/profile/partials/print-request-selector.blade.php'), 'utf8');
    const requestListLink = selector.match(/<a\b[^>]*href="\{\{ \$requestListUrl \}\}"[\s\S]*?<\/a>/);
    assert.ok(requestListLink, 'The Request List link must exist');
    assert.match(requestListLink[0], /data-open-leave-request-form/);

    const {handlers, frame, shown, title} = setup();
    const trigger = {getAttribute: name => ({
        href: 'http://127.0.0.1:8000/admin/leave-request?requested_by=583',
        'data-request-modal-title': 'Request List',
    }[name] || null)};
    let prevented = false;
    handlers.click({target: {closest: () => trigger}, preventDefault() { prevented = true; }});
    assert.equal(prevented, true);
    assert.deepEqual(shown, [trigger]);
    const url = new URL(frame.src);
    assert.equal(url.pathname, '/admin/leave-request');
    assert.equal(url.searchParams.get('requested_by'), '583');
    assert.equal(url.searchParams.get('request_modal'), '1');
    assert.equal(title.textContent, 'Request List');
});


test('creating resignation opens the full form with its own translated modal title', () => {
    const {handlers, frame, title} = setup();
    const trigger = {getAttribute: name => ({
        href: '/admin/resignation/create?employee_id=20&return_to_profile=1',
        'data-request-modal-title': 'Create resignation request',
    }[name] || null)};
    let prevented = false;
    handlers.click({target: {closest: selector => {
        assert.ok(selector.includes('[data-open-request-form]'));
        return trigger;
    }}, preventDefault() {prevented = true;}});
    assert.equal(prevented, true);
    const url = new URL(frame.src);
    assert.equal(url.pathname, '/admin/resignation/create');
    assert.equal(url.searchParams.get('employee_id'), '20');
    assert.equal(url.searchParams.get('return_to_profile'), '1');
    assert.equal(url.searchParams.get('request_modal'), '1');
    assert.equal(frame.title, 'Create resignation request');
    assert.equal(title.textContent, 'Create resignation request');
});

test('saving a resignation closes the modal and opens the saved preview in the parent page', () => {
    const {handlers, frame, hidden, navigated} = setup();
    handlers.message({origin: 'http://localhost', source: frame.contentWindow,
        data: {type: 'employee-request-form-saved', redirectUrl: '/admin/employees/20/profile?tab=resignation-letter&resignation_request_id=3'}});
    assert.equal(hidden.length, 1);
    assert.deepEqual(navigated, ['http://localhost/admin/employees/20/profile?tab=resignation-letter&resignation_request_id=3']);
});

test('cancel closes the modal without navigation and ignores unrelated window messages', () => {
    const {handlers, frame, hidden, navigated} = setup();
    const event = {origin: 'http://localhost', source: frame.contentWindow, data: {type: 'employee-request-form-close'}};
    handlers.message({...event, origin: 'https://elsewhere.example'});
    handlers.message({...event, source: {}});
    assert.equal(hidden.length, 0);
    handlers.message(event);
    assert.equal(hidden.length, 1);
    assert.deepEqual(navigated, []);
    handlers.message({...event, data: {type: 'employee-request-form-saved', redirectUrl: 'https://elsewhere.example'}});
    assert.deepEqual(navigated, []);
});
