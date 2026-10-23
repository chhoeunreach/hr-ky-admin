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
    const title = {textContent: 'Leave Request Form'};
    const modal = {
        querySelector: selector => selector === '.modal-title' ? title : frame,
        addEventListener: (name, handler) => { handlers[name] = handler; },
    };
    const bootstrap = {Modal: {getOrCreateInstance: element => {
        assert.equal(element, modal);
        return {show: trigger => shown.push(trigger)};
    }}};
    vm.runInNewContext(source, {
        document: {
            getElementById: () => modal,
            addEventListener: (name, handler) => { handlers[name] = handler; },
        },
        window: {bootstrap, location: {href: 'http://localhost/admin/leave-request'}},
        bootstrap,
        URL,
    });
    return {handlers, frame, shown, title};
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
