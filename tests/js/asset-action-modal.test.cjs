const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/assets/js/asset-action-modal.js'), 'utf8');

function setup() {
    const handlers = {}, childHandlers = {}, frameHandlers = {}, modalHandlers = {}, windowHandlers = {};
    const title = {textContent: 'Asset'};
    const child = {addEventListener: (name, handler) => childHandlers[name] = handler, createElement: () => ({})};
    const frame = {contentWindow: {}, contentDocument: child, addEventListener: (name, handler) => frameHandlers[name] = handler,
        removeAttribute: name => {delete frame[name];}};
    const modal = {dataset: {assetsUrl: 'https://hr.test/admin/assets'},
        querySelector: selector => selector === '.modal-title' ? title : frame,
        addEventListener: (name, handler) => modalHandlers[name] = handler};
    const calls = {shows: 0, hides: 0, reloads: 0};
    const window = {location: {href: 'https://hr.test/admin/assets?q=phone', origin: 'https://hr.test', reload: () => calls.reloads++},
        bootstrap: {Modal: {getOrCreateInstance: () => ({show: () => calls.shows++, hide: () => calls.hides++})}},
        addEventListener: (name, handler) => windowHandlers[name] = handler};
    vm.runInNewContext(source, {window, URL, document: {getElementById: () => modal, addEventListener: (name, handler) => handlers[name] = handler}});
    const link = (href, title = 'Edit asset') => ({dataset: {}, title, href, textContent: title,
        getAttribute: name => name === 'href' ? href : null, hasAttribute: name => name === 'data-open-asset-modal'});
    const click = (handler, trigger) => {
        const event = {target: {closest: () => trigger}, prevented: false, preventDefault() {this.prevented = true;}};
        handler(event); return event;
    };
    return {handlers, childHandlers, frameHandlers, modalHandlers, windowHandlers, frame, calls, title, link, click};
}

test('asset actions open in a modal and preserve their URL parameters', () => {
    const s = setup();
    assert.equal(s.click(s.handlers.click, s.link('/admin/assets/14/edit?tab=details')).prevented, true);
    const url = new URL(s.frame.src);
    assert.equal(url.searchParams.get('asset_modal'), '1');
    assert.equal(url.searchParams.get('tab'), 'details');
    assert.equal(s.title.textContent, 'Edit asset');
    assert.equal(s.calls.shows, 1);
    s.modalHandlers['hidden.bs.modal']();
    assert.equal(s.frame.src, undefined);
    assert.equal(s.calls.reloads, 0);
});

test('cross-origin links and messages cannot open or complete asset actions', () => {
    const s = setup();
    assert.equal(s.click(s.handlers.click, s.link('https://other.test/edit')).prevented, false);
    s.windowHandlers.message({origin: 'https://other.test', source: s.frame.contentWindow, data: {type: 'asset-action-saved'}});
    s.windowHandlers.message({origin: 'https://hr.test', source: {}, data: {type: 'asset-action-saved'}});
    assert.equal(s.calls.shows, 0);
    assert.equal(s.calls.hides, 0);
});

test('saving inside the iframe carries modal context and refreshes after closing', () => {
    const s = setup();
    s.click(s.handlers.click, s.link('/admin/assets/14/edit'));
    s.frameHandlers.load();
    const fields = [];
    s.childHandlers.submit({target: {action: 'https://hr.test/admin/assets/14', method: 'post',
        querySelector: () => null, appendChild: field => fields.push(field)}});
    assert.equal(fields[0].name, 'asset_modal');
    assert.equal(fields[0].value, '1');
    s.windowHandlers.message({origin: 'https://hr.test', source: s.frame.contentWindow, data: {type: 'asset-action-saved'}});
    assert.equal(s.calls.hides, 1);
    s.modalHandlers['hidden.bs.modal']();
    assert.equal(s.calls.reloads, 1);
});

test('receipt links inside Edit remain in the modal and Back closes it', () => {
    const s = setup();
    s.click(s.handlers.click, s.link('/admin/assets/14/edit'));
    s.frameHandlers.load();
    s.click(s.childHandlers.click, s.link('https://hr.test/admin/asset-receipt/14', 'Asset Receipt'));
    assert.equal(new URL(s.frame.src).pathname, '/admin/asset-receipt/14');
    assert.equal(s.frame.title, 'Asset Receipt');
    s.click(s.childHandlers.click, s.link('https://hr.test/admin/assets', 'Back'));
    assert.equal(s.calls.hides, 1);
});
