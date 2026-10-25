const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/asset-gallery.js'), 'utf8');
function element(tag = 'div') {
    const classes = new Set();
    return {tag, children: [], attrs: {}, events: {}, dataset: {}, hidden: false,
        classList: {add: value => classes.add(value), remove: value => classes.delete(value),
            toggle(value) {if (classes.has(value)) {classes.delete(value); return false;} classes.add(value); return true;}, contains: value => classes.has(value)},
        appendChild(child) {this.children.push(child); child.parent = this;},
        replaceChildren() {this.children = [];},
        setAttribute(key, value) {this.attrs[key] = value;}, removeAttribute(key) {delete this.attrs[key];},
        addEventListener(type, callback) {this.events[type] = callback;},
        querySelector(selector) {return this.children.find(child => child.tag === selector);},
        querySelectorAll() {return this.children.filter(child => child.tag === 'a');},
        closest(selector) {if (selector === '[data-asset-gallery]') return this.parent; if (selector === '.modal') return null;},
        focus() {this.focused = true;},
    };
}
function setup() {
    const viewer = element('dialog'), gallery = element(), body = element('body');
    viewer.dataset = {empty: 'No images', imageLabel: 'Image', zoomLabel: 'Actual size', fitLabel: 'Fit to screen'};
    const selectors = ['[data-image-full]', '[data-image-stage]', '[data-image-error]', '[data-image-zoom]', '[data-image-previous]',
        '[data-image-next]', '[data-image-counter]', '[data-image-original]', '[data-image-download]', '[data-image-close]', '#assetImageViewerTitle'];
    const fields = Object.fromEntries(selectors.map(selector => [selector, element()]));
    viewer.querySelector = selector => fields[selector];
    viewer.showModal = () => {viewer.open = true;};
    viewer.close = () => {viewer.open = false; viewer.events.close();};
    const events = {}, window = {};
    vm.runInNewContext(source, {window, document: {body, getElementById: () => viewer, createElement: element,
        addEventListener: (type, callback) => {events[type] = callback;}}});
    const render = urls => window.AssetGallery.render(gallery, urls, 'Office laptop');
    const open = index => {
        const link = gallery.children[index];
        let prevented = false;
        events.click({target: {closest: () => link}, preventDefault: () => {prevented = true;}});
        assert.equal(prevented, true);
        return link;
    };
    return {viewer, gallery, fields, render, open};
}

test('each thumbnail opens its own original and supports keyboard navigation and zoom', () => {
    const state = setup();
    state.render(['/front.jpg', '/back.png', '/side.webp']);
    assert.equal(state.gallery.children.length, 3);
    const trigger = state.open(1);
    assert.equal(state.viewer.open, true);
    assert.equal(state.fields['[data-image-full]'].src, '/back.png');
    assert.equal(state.fields['[data-image-original]'].href, '/back.png');
    assert.equal(state.fields['[data-image-download]'].href, '/back.png');
    assert.equal(state.fields['[data-image-counter]'].textContent, '2 / 3');
    state.fields['[data-image-zoom]'].events.click();
    assert.equal(state.fields['[data-image-stage]'].classList.contains('is-zoomed'), true);
    state.viewer.events.keydown({key: 'ArrowRight', stopPropagation() {}, preventDefault() {}});
    assert.equal(state.fields['[data-image-full]'].src, '/side.webp');
    assert.equal(state.fields['[data-image-stage]'].classList.contains('is-zoomed'), false);
    state.fields['[data-image-next]'].events.click();
    assert.equal(state.fields['[data-image-full]'].src, '/front.jpg');
    state.fields['[data-image-close]'].events.click();
    assert.equal(state.viewer.open, false);
    assert.equal(trigger.focused, true);
});

test('switching assets clears previous images and an empty gallery shows a useful state', () => {
    const state = setup(); state.render(['/one.jpg', '/two.jpg']);
    state.render(['/new.jpg']); state.open(0);
    assert.equal(state.gallery.children.length, 1);
    assert.equal(state.fields['[data-image-full]'].src, '/new.jpg');
    assert.equal(state.fields['[data-image-previous]'].disabled, true);
    assert.equal(state.fields['[data-image-next]'].disabled, true);
    state.viewer.close(); state.render([]);
    assert.equal(state.gallery.children.length, 1);
    assert.equal(state.gallery.children[0].textContent, 'No images');
});

test('an unavailable image keeps the original link and the next image can still be viewed', () => {
    const state = setup(); state.render(['/missing.jpg', '/available.jpg']); state.open(0);
    state.fields['[data-image-full]'].events.error();
    assert.equal(state.fields['[data-image-error]'].hidden, false);
    assert.equal(state.fields['[data-image-original]'].href, '/missing.jpg');
    state.fields['[data-image-next]'].events.click();
    assert.equal(state.fields['[data-image-error]'].hidden, true);
    assert.equal(state.fields['[data-image-full]'].hidden, false);
    assert.equal(state.fields['[data-image-full]'].src, '/available.jpg');
});
