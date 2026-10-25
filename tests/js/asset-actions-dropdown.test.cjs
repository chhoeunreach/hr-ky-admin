const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/asset-actions-dropdown.js'), 'utf8');
function setup() {
    const document = {activeElement: null};
    const parent = {appendChild(child) {child.parentElement = this;}, querySelector: () => menu};
    const body = {appendChild(child) {child.parentElement = this;}};
    const buttonEvents = {}, scrollEvents = {}, windowEvents = {};
    const items = [0, 1, 2].map(() => ({focus() {document.activeElement = this;}}));
    const menu = {parentElement: parent, querySelectorAll: () => items, contains: target => items.includes(target)};
    const button = {parentElement: parent, addEventListener(type, callback) {buttonEvents[type] = callback;},
        closest: () => ({addEventListener(type, callback) {scrollEvents[type] = callback;}}),
        focus() {document.activeElement = this;}};
    let config, shown = false;
    class Dropdown {
        constructor(trigger, options) {assert.equal(menu.parentElement, parent); config = options;}
        hide() {if (!shown) return; shown = false; buttonEvents['hidden.bs.dropdown']();}
    }
    Object.assign(document, {body, querySelectorAll: () => [button]});
    vm.runInNewContext(source, {document, bootstrap: {Dropdown}, window: {bootstrap: {Dropdown},
        addEventListener(type, callback, options) {windowEvents[type] = {callback, options};}}});
    return {document, parent, menu, button, items, config, scrollEvents, windowEvents,
        show() {shown = true; buttonEvents['show.bs.dropdown']();},
        key(key, target = items[0]) {
            const event = {key, target, prevented: false, stopped: false,
                preventDefault() {this.prevented = true;}, stopPropagation() {this.stopped = true;}};
            windowEvents.keydown.callback(event); return event;
        }};
}

test('opening an action menu escapes table clipping and uses viewport positioning', () => {
    const state = setup(); state.show();
    assert.equal(state.menu.parentElement, state.document.body);
    assert.equal(state.config.boundary, state.document.body);
    assert.equal(state.config.popperConfig({placement: 'bottom-start'}).strategy, 'fixed');
    state.scrollEvents.scroll();
    assert.equal(state.menu.parentElement, state.parent);
    state.show(); state.windowEvents.resize.callback();
    assert.equal(state.menu.parentElement, state.parent);
});

test('arrow navigation and Escape work before Bootstrap handles portaled menu keys', () => {
    const state = setup(); state.show();
    assert.equal(state.windowEvents.keydown.options.capture, true);
    state.document.activeElement = state.items[0];
    const event = state.key('ArrowDown');
    assert.equal(state.document.activeElement, state.items[1]);
    assert.equal(event.prevented, true); assert.equal(event.stopped, true);
    state.key('End'); assert.equal(state.document.activeElement, state.items[2]);
    state.key('Escape');
    assert.equal(state.document.activeElement, state.button);
    assert.equal(state.menu.parentElement, state.parent);
});

test('keyboard input outside the open menu is left alone', () => {
    const state = setup(); state.show();
    const event = state.key('ArrowDown', {});
    assert.equal(event.prevented, false); assert.equal(event.stopped, false);
});
