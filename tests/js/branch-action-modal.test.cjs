const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/assets/js/branch-action-modal.js'), 'utf8');

function setup() {
    const handlers = {}, childHandlers = {}, frameHandlers = {}, modalHandlers = {}, windowHandlers = {};
    const title = {textContent: 'Branch'};
    const child = {addEventListener: (name, handler) => childHandlers[name] = handler, createElement: () => ({})};
    const frame = {contentWindow: {}, contentDocument: child, addEventListener: (name, handler) => frameHandlers[name] = handler,
        removeAttribute: name => {delete frame[name];}};
    const modal = {dataset: {indexUrl: 'https://hr.test/admin/branch'},
        querySelector: selector => selector === '.modal-title' ? title : frame,
        addEventListener: (name, handler) => modalHandlers[name] = handler};
    const calls = {shows: 0, hides: 0, reloads: 0};
    const window = {location: {href: 'https://hr.test/admin/branch?q=phone', origin: 'https://hr.test', reload: () => calls.reloads++},
        bootstrap: {Modal: {getOrCreateInstance: () => ({show: () => calls.shows++, hide: () => calls.hides++})}},
        addEventListener: (name, handler) => windowHandlers[name] = handler};
    vm.runInNewContext(source, {window, URL, document: {getElementById: () => modal, addEventListener: (name, handler) => handlers[name] = handler}});
    const link = (href, title = 'Edit branch') => ({dataset: {}, title, href, textContent: title,
        getAttribute: name => name === 'href' ? href : null, hasAttribute: name => name === 'data-open-branch-modal'});
    const click = (handler, trigger) => {
        const event = {target: {closest: () => trigger}, prevented: false, preventDefault() {this.prevented = true;}};
        handler(event); return event;
    };
    return {handlers, childHandlers, frameHandlers, modalHandlers, windowHandlers, frame, calls, title, link, click};
}

test('branch edits open in a modal and preserve their URL parameters', () => {
    const s = setup();
    assert.equal(s.click(s.handlers.click, s.link('/admin/branch/14/edit?tab=details')).prevented, true);
    const url = new URL(s.frame.src);
    assert.equal(url.searchParams.get('branch_modal'), '1');
    assert.equal(url.searchParams.get('tab'), 'details');
    assert.equal(s.title.textContent, 'Edit branch');
    assert.equal(s.calls.shows, 1);
    s.modalHandlers['hidden.bs.modal']();
    assert.equal(s.frame.src, undefined);
    assert.equal(s.calls.reloads, 0);
});

test('cross-origin links and messages cannot open or complete branch edits', () => {
    const s = setup();
    assert.equal(s.click(s.handlers.click, s.link('https://other.test/edit')).prevented, false);
    s.windowHandlers.message({origin: 'https://other.test', source: s.frame.contentWindow, data: {type: 'branch-action-saved'}});
    s.windowHandlers.message({origin: 'https://hr.test', source: {}, data: {type: 'branch-action-saved'}});
    assert.equal(s.calls.shows, 0);
    assert.equal(s.calls.hides, 0);
});

test('saving inside the iframe carries modal context and refreshes after closing', () => {
    const s = setup();
    s.click(s.handlers.click, s.link('/admin/branch/14/edit'));
    s.frameHandlers.load();
    const fields = [];
    s.childHandlers.submit({target: {action: 'https://hr.test/admin/branch/14', method: 'post',
        querySelector: () => null, appendChild: field => fields.push(field)}});
    assert.equal(fields[0].name, 'branch_modal');
    assert.equal(fields[0].value, '1');
    s.windowHandlers.message({origin: 'https://hr.test', source: s.frame.contentWindow, data: {type: 'branch-action-saved'}});
    assert.equal(s.calls.hides, 1);
    s.modalHandlers['hidden.bs.modal']();
    assert.equal(s.calls.reloads, 1);
});

test('links inside Edit stay in the modal and Back closes it', () => {
    const s = setup();
    s.click(s.handlers.click, s.link('/admin/branch/14/edit'));
    s.frameHandlers.load();
    s.click(s.childHandlers.click, s.link('https://hr.test/admin/branch/15/edit', 'Edit other branch'));
    assert.equal(new URL(s.frame.src).pathname, '/admin/branch/15/edit');
    assert.equal(s.frame.title, 'Edit other branch');
    s.click(s.childHandlers.click, s.link('https://hr.test/admin/branch', 'Back'));
    assert.equal(s.calls.hides, 1);
});
