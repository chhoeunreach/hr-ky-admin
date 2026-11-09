/* Minimal XLSX writer: text cells, UTF-8 XML, uncompressed ZIP. No network dependencies. */
(function (root) {
    'use strict';
    const encoder = new TextEncoder();
    const xml = value => String(value ?? '').replace(/[\x00-\x08\x0b\x0c\x0e-\x1f]/g, '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[char]));
    const column = index => {let result = ''; for (++index; index; index = Math.floor((index - 1) / 26)) result = String.fromCharCode(65 + (index - 1) % 26) + result; return result;};
    const crc32 = bytes => {let crc = -1; for (const byte of bytes) {crc ^= byte; for (let i = 0; i < 8; i++) crc = (crc >>> 1) ^ (0xedb88320 & -(crc & 1));} return (crc ^ -1) >>> 0;};
    const zip = entries => {
        const parts = [], central = []; let offset = 0, centralSize = 0;
        for (const [filename, text] of Object.entries(entries)) {
            const name = encoder.encode(filename), data = encoder.encode(text), crc = crc32(data);
            const header = new Uint8Array(30), view = new DataView(header.buffer);
            view.setUint32(0, 0x04034b50, true); view.setUint16(4, 20, true); view.setUint16(10, 0, true); view.setUint16(12, 33, true);
            view.setUint32(14, crc, true); view.setUint32(18, data.length, true); view.setUint32(22, data.length, true); view.setUint16(26, name.length, true);
            parts.push(header, name, data);
            const directory = new Uint8Array(46), dv = new DataView(directory.buffer);
            dv.setUint32(0, 0x02014b50, true); dv.setUint16(4, 20, true); dv.setUint16(6, 20, true); dv.setUint16(14, 33, true);
            dv.setUint32(16, crc, true); dv.setUint32(20, data.length, true); dv.setUint32(24, data.length, true); dv.setUint16(28, name.length, true); dv.setUint32(42, offset, true);
            central.push(directory, name); centralSize += 46 + name.length; offset += 30 + name.length + data.length;
        }
        const end = new Uint8Array(22), ev = new DataView(end.buffer), count = Object.keys(entries).length;
        ev.setUint32(0, 0x06054b50, true); ev.setUint16(8, count, true); ev.setUint16(10, count, true); ev.setUint32(12, centralSize, true); ev.setUint32(16, offset, true);
        return new Blob([...parts, ...central, end], {type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
    };
    root.DirectoryWorkbook = {create(headers, rows) {
        const worksheet = [headers, ...rows].map((row, index) => `<row r="${index+1}">${row.map((value, cell) => `<c r="${column(cell)}${index+1}" t="inlineStr"${index === 0 ? ' s="1"' : ''}><is><t xml:space="preserve">${xml(value)}</t></is></c>`).join('')}</row>`).join('');
        return zip({
            '[Content_Types].xml':'<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels':'<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml':'<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Directory" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels':'<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml':'<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="2"><xf fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf><xf fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment wrapText="1"/></xf></cellXfs></styleSheet>',
            'xl/worksheets/sheet1.xml':`<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" state="frozen"/></sheetView></sheetViews><sheetFormatPr defaultColWidth="24" defaultRowHeight="16"/><sheetData>${worksheet}</sheetData></worksheet>`
        });
    }};
})(typeof window === 'undefined' ? globalThis : window);
