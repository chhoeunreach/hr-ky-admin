document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('attendanceReportWordExport');
    const paper = document.querySelector('.attendance-confirmation-paper');
    const error = document.getElementById('attendanceReportExportError');
    if (!button || !paper) return;

    const cleanText = element => (element?.textContent || '').replace(/\s+/g, ' ').trim();
    const fieldText = element => {
        const copy = element.cloneNode(true);
        copy.querySelectorAll('input').forEach(input => {
            const value = input.type === 'checkbox' ? (input.checked ? 'X' : '') : input.value;
            input.replaceWith(document.createTextNode(value || '____________'));
        });
        return cleanText(copy);
    };

    button.addEventListener('click', async function () {
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
        error.hidden = true;

        try {
            if (!window.docx) throw new Error('The Word export library is unavailable.');
            const d = window.docx;
            const portrait = paper.dataset.orientation === 'portrait';
            const pageWidth = portrait ? 11906 : 16838;
            const contentWidth = pageWidth - 1134;
            const fontSize = portrait ? 18 : 20;
            const border = { style: d.BorderStyle.SINGLE, size: 4, color: '747474' };
            const nil = { style: d.BorderStyle.NIL, size: 0, color: 'FFFFFF' };
            const borders = { top: border, bottom: border, left: border, right: border };
            const noBorders = { top: nil, bottom: nil, left: nil, right: nil };
            const paragraph = (text, options = {}) => new d.Paragraph({
                alignment: options.center ? d.AlignmentType.CENTER : d.AlignmentType.LEFT,
                spacing: { before: options.before || 0, after: options.after || 0, line: 300 },
                keepNext: Boolean(options.keepNext),
                children: [new d.TextRun({
                    text: String(text || ' '), font: 'Khmer OS Battambang',
                    size: options.size || fontSize, bold: Boolean(options.bold), color: '171717',
                })],
            });
            const cell = (text, width, options = {}) => new d.TableCell({
                width: { size: width, type: d.WidthType.DXA },
                columnSpan: options.columnSpan,
                borders: options.frameless ? noBorders : borders,
                shading: options.heading ? { fill: 'EEF0F2' } : undefined,
                margins: { top: 90, bottom: 90, left: portrait ? 40 : 70, right: portrait ? 40 : 70 },
                verticalAlign: d.VerticalAlign.CENTER,
                children: options.children || [paragraph(text, { bold: options.heading, center: options.center })],
            });
            const table = (rows, widths, frameless = false) => new d.Table({
                width: { size: contentWidth, type: d.WidthType.DXA },
                columnWidths: widths,
                layout: d.TableLayoutType.FIXED,
                borders: frameless ? noBorders : borders,
                rows,
            });
            const children = [];
            const brand = paper.querySelector('.attendance-report-brand');
            const brandParagraphs = [];
            const logo = brand.querySelector('img');
            if (logo) {
                const response = await fetch(logo.src);
                if (!response.ok) throw new Error('Unable to load the report logo.');
                const data = new Uint8Array(await response.arrayBuffer());
                brandParagraphs.push(new d.Paragraph({ children: [new d.ImageRun({ data, transformation: { width: 48, height: 48 } })] }));
            }
            brandParagraphs.push(paragraph(cleanText(brand.querySelector('strong')), { bold: true, size: 24 }));
            brandParagraphs.push(paragraph(cleanText(brand.querySelector('span')), { size: 18 }));
            children.push(table([new d.TableRow({ cantSplit: true, children: [
                cell('', contentWidth, { frameless: true, children: brandParagraphs }),
            ] })], [contentWidth], true));
            children.push(paragraph(cleanText(paper.querySelector('h1')), { bold: true, center: true, size: portrait ? 28 : 32, before: 160, keepNext: true }));
            children.push(paragraph(cleanText(paper.querySelector('.attendance-report-heading > p')), { center: true, size: 18, after: 160, keepNext: true }));
            paper.querySelectorAll('.attendance-report-meta > *').forEach(item => {
                children.push(paragraph(fieldText(item), { after: 40, keepNext: true }));
            });
            children.push(paragraph(cleanText(paper.querySelector('.attendance-report-table-heading')), { bold: true, before: 100, after: 100, keepNext: true }));

            const sourceTable = paper.querySelector('.attendance-confirmation-table');
            const proportions = Array.from(sourceTable.querySelectorAll('col'), col => parseFloat(col.style.width));
            const widths = proportions.map(value => Math.floor(contentWidth * value / 100));
            widths[widths.length - 1] += contentWidth - widths.reduce((total, width) => total + width, 0);
            const headingRows = sourceTable.tHead.rows;
            const firstHeadings = Array.from(headingRows[0].cells);
            const headings = firstHeadings.flatMap(th => th.colSpan > 1
                ? Array.from(headingRows[1].cells, cleanText) : [cleanText(th)]);
            const rows = [new d.TableRow({ tableHeader: true, cantSplit: true, children: headings.map((heading, index) => cell(heading, widths[index], { heading: true, center: true })) })];
            Array.from(sourceTable.tBodies[0].rows).forEach(row => {
                if (row.cells.length !== widths.length) {
                    rows.push(new d.TableRow({ children: [cell(cleanText(row), contentWidth, { center: true, columnSpan: widths.length })] }));
                    return;
                }
                rows.push(new d.TableRow({
                    cantSplit: true,
                    height: { value: 540, rule: d.HeightRule.ATLEAST },
                    children: Array.from(row.cells, (td, index) => {
                        const input = td.querySelector('input');
                        const value = input ? (input.type === 'checkbox' ? (input.checked ? '[X]' : '[ ]') : input.value) : td.innerText;
                        return cell(value, widths[index], { center: [0, 4, 5, 6, 7].includes(index) });
                    }),
                }));
            });
            children.push(table(rows, widths));
            children.push(paragraph(Array.from(paper.querySelectorAll('.attendance-report-summary label'), fieldText).join('     '), { before: 140, after: 100 }));
            children.push(paragraph(cleanText(paper.querySelector('.attendance-report-note')), { size: 18, after: 140 }));
            const signatureWidths = [Math.floor(contentWidth / 3), Math.floor(contentWidth / 3)];
            signatureWidths.push(contentWidth - signatureWidths[0] - signatureWidths[1]);
            children.push(table([new d.TableRow({ cantSplit: true, children: Array.from(paper.querySelectorAll('.attendance-report-signatures > div'), (signature, index) => cell('', signatureWidths[index], {
                frameless: true,
                children: [
                    paragraph(cleanText(signature.querySelector('strong')), { bold: true, center: true }),
                    paragraph('________________________', { center: true, before: 600, after: 80 }),
                    ...Array.from(signature.querySelectorAll('label'), label => paragraph(fieldText(label), { size: 18, after: 50 })),
                ],
            })) })], signatureWidths, true));

            const documentFile = new d.Document({
                styles: { default: { document: { run: { font: 'Khmer OS Battambang', size: fontSize }, paragraph: { spacing: { after: 0, line: 300 } } } } },
                sections: [{
                    properties: { page: {
                        size: { width: 11906, height: 16838, orientation: portrait ? d.PageOrientation.PORTRAIT : d.PageOrientation.LANDSCAPE },
                        margin: { top: 567, right: 567, bottom: 567, left: 567, footer: 240 },
                    } },
                    footers: { default: new d.Footer({ children: [new d.Paragraph({
                        alignment: d.AlignmentType.RIGHT,
                        children: [new d.TextRun({ children: [d.PageNumber.CURRENT, ' / ', d.PageNumber.TOTAL_PAGES], size: 16 })],
                    })] }) },
                    children,
                }],
            });
            const blob = await d.Packer.toBlob(documentFile);
            const url = URL.createObjectURL(blob);
            const download = document.createElement('a');
            download.href = url;
            download.download = button.dataset.fileName;
            document.body.appendChild(download);
            download.click();
            download.remove();
            window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        } catch (exportError) {
            console.error('Daily attendance checklist export failed.', exportError);
            error.hidden = false;
        } finally {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.innerHTML = originalContent;
            if (window.feather) window.feather.replace();
        }
    });
});
