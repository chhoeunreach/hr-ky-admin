(function () {
    'use strict';
    const initialize = () => document.querySelectorAll('[data-directory-table]').forEach(tools => {
        let table = document.getElementById(tools.dataset.directoryTable);
        const form = document.getElementById(tools.dataset.directoryForm);
        if (!table || !form) return;
        const status = tools.querySelector('[data-directory-status]'), search = tools.querySelector('input[type=search]');
        const live = tools.dataset.directoryLive === 'true';
        const exportUrl = tools.dataset.directoryExportUrl;
        const headingCells = [...table.tHead.rows[0].cells];
        const columns = headingCells.map((cell, index) => ({index, label: cell.textContent.trim() || '#', action: cell.hasAttribute('data-directory-actions')})).filter(column => !column.action);
        // The last column contains record actions and remains available at every size.
        const storageKey = 'directory-hidden-' + table.id;
        let hidden = new Set();
        try {hidden = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]').filter(index => columns.some(column => column.index === index)));} catch (_) {}
        if (hidden.size >= columns.length) hidden.clear();
        const menu = tools.querySelector('[data-directory-columns]');
        menu.closest('.dropdown-menu').addEventListener('click', event => event.stopPropagation());
        columns.forEach(column => {
            const label = document.createElement('label'), checkbox = document.createElement('input');
            checkbox.type = 'checkbox'; checkbox.dataset.directoryColumn = column.index;
            label.append(checkbox, document.createTextNode(column.label)); menu.append(label);
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) hidden.delete(column.index); else hidden.add(column.index);
                if (hidden.size >= columns.length) hidden.delete(column.index);
                save(); applyColumns();
            });
        });
        const save = () => {try {localStorage.setItem(storageKey, JSON.stringify([...hidden]));} catch (_) {}};
        const applyColumns = () => {
            table = document.getElementById(tools.dataset.directoryTable);
            if (!table) return;
            table.classList.add('directory-managed-table');
            for (const row of table.rows) {
                if (row.cells.length < headingCells.length) continue;
                [...row.cells].forEach((cell, index) => cell.classList.toggle('directory-column-hidden', hidden.has(index)));
            }
            menu.querySelectorAll('input').forEach(input => {
                input.checked = !hidden.has(Number(input.dataset.directoryColumn));
                input.disabled = input.checked && hidden.size === columns.length - 1;
            });
        };
        table.classList.add('directory-managed-table');
        // Retain free space for existing action dropdowns; sticky headings follow the page.
        tools.querySelector('[data-directory-restore]').addEventListener('click', () => {hidden.clear(); save(); applyColumns();});
        document.addEventListener('directory:updated', () => {
            applyColumns();
            if (currentSort.index !== null && currentSort.direction !== 'none') {
                sortRows(currentSort.index, currentSort.direction);
            }
        });
        applyColumns();

        // Sortable Column Headers
        let currentSort = { index: null, direction: 'none' };
        const parseValue = raw => {
            if (raw === null || raw === undefined) return { type: 'empty', val: '' };
            const str = String(raw).trim();
            if (!str || str === '—' || str === '-' || str === 'N/A' || str === 'n/a') return { type: 'empty', val: '' };
            const clean = str.replace(/,/g, '');
            if (/^[-+]?\$?\d+(\.\d+)?%?$/.test(clean)) {
                const num = parseFloat(clean.replace(/[$%]/g, ''));
                if (!isNaN(num)) return { type: 'number', val: num };
            }
            if (!isNaN(Number(clean))) return { type: 'number', val: Number(clean) };
            if (/^\d{4}[-/.]\d{1,2}[-/.]\d{1,2}/.test(str)) {
                const t = Date.parse(str);
                if (!isNaN(t)) return { type: 'date', val: t };
            }
            return { type: 'string', val: str };
        };
        const compare = (valA, valB, dir) => {
            const a = parseValue(valA);
            const b = parseValue(valB);
            if (a.type === 'empty' && b.type === 'empty') return 0;
            if (a.type === 'empty') return 1;
            if (b.type === 'empty') return -1;
            let diff = 0;
            if (a.type === 'number' && b.type === 'number') diff = a.val - b.val;
            else if (a.type === 'date' && b.type === 'date') diff = a.val - b.val;
            else diff = String(valA).trim().localeCompare(String(valB).trim(), undefined, { numeric: true, sensitivity: 'base' });
            return dir === 'desc' ? -diff : diff;
        };
        const sortRows = (colIndex, dir) => {
            table = document.getElementById(tools.dataset.directoryTable);
            const tbody = table ? table.tBodies[0] : null;
            if (!tbody) return;
            const rows = [...tbody.rows].filter(r => r.cells.length >= headingCells.length && !r.querySelector('td[colspan]'));
            if (rows.length <= 1) return;
            rows.sort((rowA, rowB) => {
                const cellA = rowA.cells[colIndex];
                const cellB = rowB.cells[colIndex];
                const vA = cellA ? (cellA.dataset.sortValue ?? cellA.dataset.directoryValue ?? textOf(cellA)) : '';
                const vB = cellB ? (cellB.dataset.sortValue ?? cellB.dataset.directoryValue ?? textOf(cellB)) : '';
                return compare(vA, vB, dir);
            });
            rows.forEach(r => tbody.appendChild(r));
        };

        columns.forEach(col => {
            const th = headingCells[col.index];
            if (!th || th.hasAttribute('data-directory-actions') || th.classList.contains('no-sort') || th.dataset.noSort !== undefined) return;
            th.classList.add('directory-sortable');
            th.setAttribute('role', 'columnheader');
            th.setAttribute('tabindex', '0');
            th.setAttribute('aria-sort', 'none');
            th.setAttribute('title', 'Click to sort');
            const onSortClick = () => {
                const nextDir = (currentSort.index === col.index && currentSort.direction === 'asc') ? 'desc' : 'asc';
                currentSort = { index: col.index, direction: nextDir };
                headingCells.forEach(cell => {
                    if (cell.classList.contains('directory-sortable')) {
                        cell.setAttribute('aria-sort', 'none');
                    }
                });
                th.setAttribute('aria-sort', nextDir === 'asc' ? 'ascending' : 'descending');
                sortRows(col.index, nextDir);
                table.dispatchEvent(new CustomEvent('directory:sorted', { detail: { column: col.index, direction: nextDir } }));
            };
            th.addEventListener('click', onSortClick);
            th.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onSortClick();
                }
            });
        });
        const syncSearch = () => {
            const input = [...form.elements].find(input => input.name === tools.dataset.directorySearchKey);
            if (input) input.value = search.value;
            return input;
        };
        const submit = () => {
            if (!syncSearch()) {const input = document.createElement('input'); input.type = 'hidden'; input.name = tools.dataset.directorySearchKey; input.value = search.value; form.append(input);}
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        };
        if (!live) {
            const perPageEl = tools.querySelector('[name="per_page"]');
            if (perPageEl) {
                let submittingPerPage = false;
                const onPerPageChange = () => {
                    if (submittingPerPage) return;
                    submittingPerPage = true;
                    const pageInput = form.querySelector('[name="page"]');
                    if (pageInput) pageInput.value = '1';
                    submit();
                };
                perPageEl.addEventListener('change', onPerPageChange);
                if (window.jQuery) {
                    window.jQuery(perPageEl).on('select2:select', onPerPageChange);
                }
            }
            search.addEventListener('keydown', event => {if (event.key === 'Enter') {event.preventDefault(); submit();}});
            search.addEventListener('search', submit);
        }
        tools.querySelector('[data-directory-refresh]').addEventListener('click', submit);
        const textOf = cell => {
            if (!cell) return '';
            if (cell.dataset.directoryValue !== undefined) return cell.dataset.directoryValue;
            const copy = cell.cloneNode(true);
            const toggle = copy.querySelector('input[type=checkbox]');
            if (toggle) {
                if (toggle.classList.contains('toggleHolidayCheckIn')) return toggle.checked ? tools.dataset.directoryYes : tools.dataset.directoryNo;
                return toggle.checked ? tools.dataset.directoryActive : tools.dataset.directoryInactive;
            }
            copy.querySelectorAll('details').forEach(element => {const summary = element.querySelector('summary');if (summary) summary.remove();});
            copy.querySelectorAll('img').forEach(element => element.replaceWith(element.alt || ''));
            copy.querySelectorAll('script,style,.dropdown-menu,.dropdown-toggle,svg,img,[data-directory-detail],.directory-row-details-button').forEach(element => element.remove());
            copy.querySelectorAll('[title]').forEach(element => {if (element.classList.contains('text-truncate')) element.textContent = element.title;});
            copy.querySelectorAll('br').forEach(element => element.replaceWith('\n'));
            return copy.textContent.replace(/[ \t]+/g, ' ').replace(/\n\s*\n/g, '\n').trim();
        };
        const allResults = async () => {
            syncSearch();
            const url = new URL(form.action, location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            url.searchParams.set('per_page', 'all'); url.searchParams.set('page', '1');
            ['action', 'download_excel'].forEach(key => url.searchParams.delete(key));
            const response = await fetch(url, {credentials:'same-origin', headers:{Accept:'text/html'}});
            if (!response.ok || response.redirected && new URL(response.url).pathname !== url.pathname) throw new Error('Unable to load results');
            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            const results = doc.getElementById(table.id);
            if (!results) throw new Error('Missing results');
            const visible = columns.filter(column => !hidden.has(column.index));
            let finalRows = [...results.tBodies[0].rows].filter(row => row.cells.length === headingCells.length).map(row => visible.map(column => table.id === 'postTable' && column.index === 2 ? (row.dataset.departments ?? textOf(row.cells[column.index])) : table.id === 'postTable' && column.index === 3 ? (row.dataset.branches ?? textOf(row.cells[column.index])) : textOf(row.cells[column.index])));
            if (currentSort.index !== null && currentSort.direction !== 'none') {
                const visIdx = visible.findIndex(c => c.index === currentSort.index);
                if (visIdx !== -1) {
                    finalRows.sort((a, b) => compare(a[visIdx], b[visIdx], currentSort.direction));
                }
            }
            return {headers:visible.map(column => column.label), rows: finalRows};
        };
        const customExportData = async () => {
            syncSearch();
            const url = new URL(exportUrl, location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            ['action', 'download_excel'].forEach(key => url.searchParams.delete(key));
            const response = await fetch(url, {credentials:'same-origin', headers:{Accept:'application/json'}});
            if (!response.ok || response.redirected && new URL(response.url).pathname !== url.pathname) throw new Error('Unable to load export results');
            const payload = await response.json();
            if (!payload.success || !Array.isArray(payload.headers) || !Array.isArray(payload.rows)) throw new Error('Invalid export response');
            const asText = value => value === null || value === undefined ? '' : String(value);
            return {headers:payload.headers.map(asText), rows:payload.rows.map(row => row.map(asText))};
        };
        const saveFile = (blob, extension) => {
            const url = URL.createObjectURL(blob), link = document.createElement('a');
            link.href = url; link.download = table.id + '-' + new Date().toISOString().slice(0,10) + '.' + extension;
            document.body.append(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
        };
        const safeCell = value => /^[=+\-@\t\r]/.test(String(value).trimStart()) ? "'" + value : value;
        const makePrint = (popup, data, orientation) => {
            const doc = popup.document; doc.open(); doc.write('<!doctype html><html><head><meta charset="utf-8"><title></title></head><body></body></html>'); doc.close();
            doc.title = tools.dataset.directoryTitle;
            const style = doc.createElement('style'); style.textContent = '@page{size:A4 '+orientation+';margin:12mm}body{font:12px "Kantumruy Pro",Arial,sans-serif;color:#243044}h1{font-size:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #dfe4ec;padding:8px;text-align:left;vertical-align:top;white-space:pre-line;overflow-wrap:anywhere}th{background:#f2f5fa}thead{display:table-header-group}tr{break-inside:avoid}@media print{button{display:none}}';doc.head.append(style);
            document.querySelectorAll('link[href*="fonts.googleapis.com"]').forEach(link => doc.head.append(link.cloneNode()));
            const h1 = doc.createElement('h1');h1.textContent = tools.dataset.directoryTitle;doc.body.append(h1);
            const printButton = doc.createElement('button');printButton.textContent = tools.querySelector('[data-directory-export=print]').textContent;printButton.addEventListener('click', () => popup.print());doc.body.append(printButton);
            const output = doc.createElement('table'), head = output.createTHead().insertRow(), body = output.createTBody();
            data.headers.forEach(value => {const cell = doc.createElement('th');cell.textContent = value;head.append(cell);});
            data.rows.forEach(row => {const tr = body.insertRow();row.forEach(value => {tr.insertCell().textContent = value;});});doc.body.append(output);
            Promise.resolve(doc.fonts?.ready).then(() => {if (!popup.closed) {popup.focus(); popup.print();}});
        };
        tools.querySelectorAll('[data-directory-export]').forEach(button => button.addEventListener('click', async () => {
            const format = button.dataset.directoryExport;
            const popup = ['print','pdf'].includes(format) ? window.open('', '_blank') : null;
            if (popup) {popup.opener = null;popup.document.body.textContent = tools.dataset.directoryWorking;}
            const buttons = [...tools.querySelectorAll('[data-directory-export]')];buttons.forEach(button => {button.disabled = true;});
            status.hidden = false;status.textContent = tools.dataset.directoryWorking;
            try {
                if (['print','pdf'].includes(format) && !popup) throw new Error('Popup blocked');
                const data = ['copy','csv','excel'].includes(format) && exportUrl ? await customExportData() : await allResults();
                if (format === 'copy') {
                    const text = [data.headers, ...data.rows].map(row => row.map(value => safeCell(value.replace(/\r?\n/g,'; ').replace(/\t/g,' '))).join('\t')).join('\n');
                    try {await navigator.clipboard.writeText(text);} catch (_) {
                        const input = document.createElement('textarea');input.value = text;input.style.cssText = 'position:fixed;left:-9999px';document.body.append(input);input.select();
                        try {if (!document.execCommand('copy')) throw new Error('Copy failed');} finally {input.remove();}
                    }
                    status.textContent = tools.dataset.directoryCopied.replace(':count', data.rows.length.toLocaleString());
                } else if (format === 'csv') {
                    const text = [data.headers, ...data.rows].map(row => row.map(value => '"' + String(safeCell(value)).replace(/"/g,'""') + '"').join(',')).join('\r\n');
                    saveFile(new Blob(['\ufeff',text],{type:'text/csv;charset=utf-8'}),'csv');status.textContent = '';
                } else if (format === 'excel') {saveFile(window.DirectoryWorkbook.create(data.headers,data.rows),'xlsx');status.textContent = '';}
                else {makePrint(popup, data, button.dataset.directoryOrientation || 'landscape');status.textContent = '';}
            } catch (_) {if (popup) popup.close();status.textContent = tools.dataset.directoryFailed;}
            finally {buttons.forEach(button => {button.disabled = false;});}
        }));
    });

    const initGenericSortables = () => {
        document.querySelectorAll('table.table-sortable, table[data-sortable="true"]').forEach(table => {
            if (table.classList.contains('directory-managed-table') || table.classList.contains('dataTable')) return;
            table.classList.add('directory-managed-table');
            const thead = table.tHead;
            if (!thead || !thead.rows.length) return;
            const headingCells = [...thead.rows[0].cells];
            let curSort = { index: null, dir: 'none' };
            headingCells.forEach((th, idx) => {
                if (th.hasAttribute('data-directory-actions') || th.classList.contains('no-sort') || th.dataset.noSort !== undefined) return;
                th.classList.add('directory-sortable');
                th.setAttribute('role', 'columnheader');
                th.setAttribute('tabindex', '0');
                th.setAttribute('aria-sort', 'none');
                th.setAttribute('title', 'Click to sort');
                const runSort = () => {
                    const nextDir = (curSort.index === idx && curSort.dir === 'asc') ? 'desc' : 'asc';
                    curSort = { index: idx, dir: nextDir };
                    headingCells.forEach(c => { if (c.classList.contains('directory-sortable')) c.setAttribute('aria-sort', 'none'); });
                    th.setAttribute('aria-sort', nextDir === 'asc' ? 'ascending' : 'descending');
                    const tbody = table.tBodies[0];
                    if (!tbody) return;
                    const rows = [...tbody.rows].filter(r => r.cells.length >= headingCells.length && !r.querySelector('td[colspan]'));
                    if (rows.length <= 1) return;
                    rows.sort((rA, rB) => {
                        const cA = rA.cells[idx], cB = rB.cells[idx];
                        const vA = cA ? (cA.dataset.sortValue ?? cA.textContent.trim()) : '';
                        const vB = cB ? (cB.dataset.sortValue ?? cB.textContent.trim()) : '';
                        const cleanA = vA.replace(/,/g, '');
                        const cleanB = vB.replace(/,/g, '');
                        const nA = Number(cleanA);
                        const nB = Number(cleanB);
                        let diff = 0;
                        if (!isNaN(nA) && !isNaN(nB) && cleanA !== '' && cleanB !== '') {
                            diff = nA - nB;
                        } else {
                            diff = vA.localeCompare(vB, undefined, { numeric: true, sensitivity: 'base' });
                        }
                        return nextDir === 'desc' ? -diff : diff;
                    });
                    rows.forEach(r => tbody.appendChild(r));
                };
                th.addEventListener('click', runSort);
                th.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); runSort(); } });
            });
        });
    };

    const start = () => {
        initialize();
        initGenericSortables();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
