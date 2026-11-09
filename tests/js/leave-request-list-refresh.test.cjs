const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const template = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/leaveRequest/common/scripts.blade.php'), 'utf8');
const start = template.indexOf('        const refreshLeaveRequestList =');
const end = template.indexOf("        $('body').on('submit', '#updateLeaveStatus'", start);
const source = template.slice(start, end) + '\nglobalThis.refresh = refreshLeaveRequestList;';
const flush = () => new Promise(setImmediate);

function setup() {
    const events = {}, requests = [], replaced = [], history = [], warnings = [];
    let icons = 0;const updated = [], total = {};
    const wrap = promise => ({then: (...args) => wrap(promise.then(...args)), fail(handler) {promise.catch(handler);return this;}});
    const $ = selector => {
        if (selector === '<div>') return {append(html) {this.html = html;return this;}, find(id) {return {length: this.html.includes(id.slice(1)) ? 1 : 0, id};}};
        return {replaceWith(next) {replaced.push([selector, next.id]);}};
    };
    $.parseHTML = html => html;
    $.get = url => wrap(new Promise((resolve, reject) => requests.push({url, resolve, reject})));
    $.Deferred = () => ({reject(error) {this.error = error;return this;}, promise() {return wrap(Promise.reject(this.error));}});
    const context = {URL, $, CustomEvent: class {constructor(type) {this.type = type;}}, feather: {replace() {icons++;}}, Swal: {fire(...args) {warnings.push(args);}},
        document: {addEventListener(type, handler) {events[type] = handler;}, getElementById: () => ({dataset: {directoryTotal: '31'}}), querySelector: () => total, dispatchEvent(event) {updated.push(event.type);}},
        window: {location: {href: 'http://hr.test/admin/leave-request?page=3&status=pending&requested_by=20&per_page=50'},
            history: {replaceState(a,b,url) {history.push(url);}}},
    };
    vm.runInNewContext(source, context);
    return {events, requests, replaced, history, warnings, refresh: context.refresh, updated, total, icons: () => icons};
}

const html = '<div id="leave-request-list-block">new request</div><div id="leave-request-pagination">page 1</div>';

test('adding a leave refreshes the first page, keeps filters, replaces rows and pagination', async () => {
    const app = setup();app.events['employee-request:updated']({detail: {requestType: 'leave'}});
    assert.equal(app.requests.length, 1);
    const url = new URL(app.requests[0].url);
    assert.equal(url.searchParams.has('page'), false);
    assert.equal(url.searchParams.get('status'), 'pending');
    assert.equal(url.searchParams.get('requested_by'), '20');
    assert.equal(url.searchParams.get('per_page'), '50');
    app.requests[0].resolve(html);await flush();
    assert.deepEqual(app.replaced.map(row => row[0]), ['#leave-request-list-block', '#leave-request-pagination']);
    assert.equal(app.history[0], url.href);
    assert.equal(app.icons(), 1);
    assert.equal(app.total.textContent, '31');
    assert.deepEqual(app.updated, ['directory:updated']);
    assert.equal(app.warnings.length, 0);
});

test('status updates keep the current page', async () => {
    const app = setup();app.refresh();
    assert.equal(new URL(app.requests[0].url).searchParams.get('page'), '3');
    app.requests[0].resolve(html);await flush();
    assert.equal(app.replaced.length, 2);
    assert.equal(app.history.length, 0);
});

test('an incomplete response leaves the existing list intact and offers a reload', async () => {
    const app = setup();app.events['employee-request:updated']({detail: {requestType: 'leave'}});
    app.requests[0].resolve('<div id="leave-request-list-block"></div>');await flush();
    assert.equal(app.replaced.length, 0);
    assert.equal(app.history.length, 0);
    assert.equal(app.warnings[0][0], 'Refresh required');
});

test('network failure does not retry saving the request and other request types do not refresh', async () => {
    const app = setup();app.events['employee-request:updated']({detail: {requestType: 'other'}});
    assert.equal(app.requests.length, 0);
    app.events['employee-request:updated']({detail: {requestType: 'leave'}});
    app.requests[0].reject({});await flush();
    assert.equal(app.requests.length, 1);
    assert.match(app.warnings[0][1], /request was saved/);
    assert.equal(app.replaced.length, 0);
});
