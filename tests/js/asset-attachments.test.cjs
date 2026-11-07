const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/asset-images.js'), 'utf8');

function element(tag) {
    return {tag, children: [], attrs: {}, handlers: {}, style: {},
        appendChild(node) {this.children.push(node);}, replaceChildren() {this.children = [];},
        setAttribute(key, value) {this.attrs[key] = value;},
        addEventListener(type, handler) {this.handlers[type] = handler;},
        setCustomValidity(message) {this.validationMessage = message;},
        closest() {return {querySelector: () => ({textContent: 'Maximum 10 files'})};}
    };
}

function setup() {
    const input = element('input'), previews = element('div'), choose = element('button');
    const status = {dataset: {empty: 'No files selected'}, textContent: ''};
    const help = {textContent: 'Maximum 10 files'};
    input.click = () => {input.clicked = true;};
    const revoked = [], handlers = {};
    let next = 0;
    vm.runInNewContext(source, {
        document: {getElementById: id => ({images: input, 'asset-image-previews': previews, assetFileChoose: choose, assetFileStatus: status, assetFileHelp: help})[id], createElement: element, querySelectorAll: () => []},
        window: {addEventListener: (type, handler) => {handlers[type] = handler;}},
        URL: {createObjectURL: () => 'blob:' + (++next), revokeObjectURL: url => revoked.push(url)}
    });
    return {input, previews, revoked, handlers, choose, status};
}

test('Word and text files show download links while photos keep image previews', () => {
    const {input, previews} = setup();
    input.files = [{name: 'front.jpg', type: 'image/jpeg'},
        {name: 'manual.docx', type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'},
        {name: 'notes.txt', type: 'text/plain'}];
    input.handlers.change();
    assert.equal(previews.children.length, 3);
    assert.equal(previews.children[0].attrs['data-asset-image'], '');
    assert.equal(previews.children[0].children[0].tag, 'img');
    for (let index = 1; index <= 2; index++) {
        const link = previews.children[index];
        assert.equal(link.attrs['data-asset-image'], undefined);
        assert.equal(link.download, input.files[index].name);
        assert.equal(link.children[1].textContent, input.files[index].name);
        assert.equal(link.children.some(child => child.tag === 'img'), false);
    }
});

test('replacing selected files releases old previews and keeps the total file limit', () => {
    const {input, previews, revoked, handlers} = setup();
    input.files = [{name: 'notes.txt', type: 'text/plain'}];
    input.handlers.change();
    input.files = Array.from({length: 11}, () => ({name: 'manual.docx', type: ''}));
    input.handlers.change();
    assert.equal(revoked[0], 'blob:1');
    assert.equal(previews.children.length, 11);
    assert.equal(input.validationMessage, 'Maximum 10 files');
    handlers.pagehide();
    assert.equal(revoked.length, 12);
    assert.equal(previews.children.length, 0);
});


test('localized file picker opens the input and reports selected filenames without browser English text', () => {
    const {input, choose, status} = setup();
    choose.handlers.click();
    assert.equal(input.clicked, true);
    input.files = [{name: 'ឯកសារ.txt', type: 'text/plain'}];
    input.handlers.change();
    assert.equal(status.textContent, 'ឯកសារ.txt');
    input.files = [];
    input.handlers.change();
    assert.equal(status.textContent, 'No files selected');
});
