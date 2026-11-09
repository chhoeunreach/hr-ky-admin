(function () {
    'use strict';
    const initialize = () => {
        const tools = document.getElementById('departmentTableTools');
        if (!tools) return;
        const form = document.getElementById('departmentFilterForm');
        const messages = JSON.parse(document.getElementById('departmentToolsMessages').textContent);
        const status = document.getElementById('departmentToolsStatus');
        const toggles = Array.from(tools.querySelectorAll('[data-department-column-toggle]'));
        const keys = toggles.map(input => input.dataset.departmentColumnToggle);
        const storageKey = 'department-directory-hidden-columns';
        let hidden = new Set();
        try {hidden = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]').filter(key => keys.includes(key)));} catch (_) {}
        if (hidden.size >= keys.length) hidden.clear();
        const visibleKeys = () => keys.filter(key => !hidden.has(key));
        const savePreferences = () => {try {localStorage.setItem(storageKey, JSON.stringify([...hidden]));} catch (_) {}};
        const applyColumns = () => {
            document.querySelectorAll('#departmentTable [data-department-column]').forEach(cell => {
                cell.classList.toggle('department-column-hidden', hidden.has(cell.dataset.departmentColumn));
            });
            const empty = document.querySelector('#departmentTable tbody td[colspan]');
            if (empty) empty.colSpan = visibleKeys().length + 1;
            toggles.forEach(input => {
                input.checked = !hidden.has(input.dataset.departmentColumnToggle);
                input.disabled = input.checked && visibleKeys().length === 1;
            });
        };
        toggles.forEach(input => input.addEventListener('change', () => {
            if (input.checked) hidden.delete(input.dataset.departmentColumnToggle);
            else hidden.add(input.dataset.departmentColumnToggle);
            if (!visibleKeys().length) hidden.delete(input.dataset.departmentColumnToggle);
            savePreferences(); applyColumns();
        }));
        document.getElementById('departmentRestoreColumns').addEventListener('click', () => {hidden.clear(); savePreferences(); applyColumns();});
        document.addEventListener('department:table-updated', applyColumns);
        applyColumns();

        const exportUrl = (format, orientation) => {
            const url = new URL(tools.dataset.exportUrl, window.location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            url.searchParams.delete('per_page'); url.searchParams.delete('page');
            url.searchParams.set('format', format);
            if (orientation) url.searchParams.set('orientation', orientation);
            visibleKeys().forEach(key => url.searchParams.append('columns[]', key));
            return url;
        };
        tools.querySelectorAll('[data-department-export]').forEach(link => link.addEventListener('click', () => {
            link.href = exportUrl(link.dataset.departmentExport, link.dataset.orientation).href;
        }));
        document.getElementById('departmentCopy').addEventListener('click', async event => {
            const button = event.currentTarget;
            button.disabled = true; status.hidden = false; status.textContent = '';
            try {
                const response = await fetch(exportUrl('json'), {headers: {Accept: 'application/json'}});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message);
                const text = [data.headings, ...data.rows].map(row => row.map(value => {
                    const cell = String(value ?? '').replace(/\r?\n/g, '; ').replace(/\t/g, ' ');
                    return /^[=+\-@\r]/.test(cell) ? "'" + cell : cell;
                }).join('\t')).join('\n');
                try {
                    if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
                    await navigator.clipboard.writeText(text);
                } catch (_) {
                    const textarea = document.createElement('textarea');
                    textarea.value = text; textarea.style.cssText = 'position:fixed;opacity:0;left:-9999px';
                    document.body.append(textarea); textarea.select();
                    try {if (!document.execCommand('copy')) throw new Error('Copy unavailable');} finally {textarea.remove();}
                }
                status.textContent = messages.copied.replace(':count', data.rows.length.toLocaleString());
            } catch (_) {status.textContent = messages.failed;}
            finally {button.disabled = false;}
        });
        const filters = document.getElementById('departmentFilterCollapse');
        const label = document.getElementById('departmentFilterToggleLabel');
        filters.addEventListener('shown.bs.collapse', () => {label.textContent = messages.collapse;});
        filters.addEventListener('hidden.bs.collapse', () => {label.textContent = messages.expand;});
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
