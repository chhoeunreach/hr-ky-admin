const {test} = require('node:test');
const assert = require('node:assert/strict');
require('../../public/assets/js/directory-workbook.js');

async function entries(headers, rows) {
    const buffer = Buffer.from(await globalThis.DirectoryWorkbook.create(headers, rows).arrayBuffer());
    const files = new Map();
    let offset = 0;
    while (buffer.readUInt32LE(offset) === 0x04034b50) {
        assert.equal(buffer.readUInt16LE(offset + 8), 0, 'ZIP entries use the store method');
        const size = buffer.readUInt32LE(offset + 18), nameLength = buffer.readUInt16LE(offset + 26);
        const start = offset + 30 + nameLength;
        files.set(buffer.subarray(offset + 30, start).toString(), buffer.subarray(start, start + size).toString());
        offset = start + size;
    }
    assert.equal(buffer.readUInt32LE(offset), 0x02014b50, 'ZIP has a central directory');
    assert.equal(buffer.readUInt32LE(buffer.length - 22), 0x06054b50, 'ZIP has an end record');
    return files;
}

test('Excel preserves Khmer, phone numbers, and formula-like values as text', async () => {
    const files = await entries(['Name', 'Phone', 'Note'], [['ការហ្វេរ', '01234567', '=HYPERLINK("test")']]);
    const sheet = files.get('xl/worksheets/sheet1.xml');
    assert.ok(sheet.includes('ការហ្វេរ'));
    assert.ok(sheet.includes('01234567'));
    assert.match(sheet, /t="inlineStr".*=HYPERLINK\(&quot;test&quot;\)/);
    assert.ok(!sheet.includes('<f>'));
    assert.equal(files.size, 6);
});

test('Excel escapes XML, removes invalid control characters, and supports columns after Z', async () => {
    const files = await entries(Array.from({length: 28}, (_, i) => 'Column ' + i), [[...Array(26).fill(''), '<>&"\'\x00\x01', 'last']]);
    const sheet = files.get('xl/worksheets/sheet1.xml');
    assert.ok(sheet.includes('r="AA2"'));
    assert.ok(sheet.includes('r="AB2"'));
    assert.ok(sheet.includes('&lt;&gt;&amp;&quot;&apos;'));
    assert.ok(!sheet.includes('\x00'));
});

test('Excel exports an empty filtered result with headings and a frozen header', async () => {
    const sheet = (await entries(['Name'], [])).get('xl/worksheets/sheet1.xml');
    assert.equal((sheet.match(/<row /g) || []).length, 1);
    assert.ok(sheet.includes('topLeftCell="A2"'));
    assert.ok(sheet.includes('Name'));
});
