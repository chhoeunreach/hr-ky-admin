(() => {
    const toolkit = document.getElementById('assetsToolkit');
    const table = document.getElementById('assetsTable');
    if (!toolkit || !table) return;
    const headings = Array.from(table.querySelectorAll('thead [data-asset-column]'));
    const rows = Array.from(table.querySelectorAll('tbody tr[data-asset-row]'));
    const menu = toolkit.querySelector('[data-assets-columns]');
    const status = document.querySelector('[data-assets-status]');
    const searchForm = document.getElementById('assetsToolkitSearch');
    const search = document.getElementById('assetsSearch');
    const rowCount = document.getElementById('assetsRows');
    const storageKey = 'hr-assets-visible-columns-v4';
    let savedColumns;
    try {
        const stored = JSON.parse(localStorage.getItem(storageKey));
        if (Array.isArray(stored) && stored.every(key => typeof key === 'string')) savedColumns = stored;
    } catch (_) {}
    const selectedColumns = () => headings.filter(heading => !heading.hidden && !['actions', 'receipt'].includes(heading.dataset.assetColumn)).map(heading => heading.dataset.assetColumn);
    const setColumn = (heading, index, visible) => {
        heading.hidden = !visible;
        rows.forEach(row => { if (row.cells[index]) row.cells[index].hidden = !visible; });
    };
    headings.forEach((heading, index) => {
        const label = document.createElement('label');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = !savedColumns || savedColumns.includes(heading.dataset.assetColumn);
        setColumn(heading, index, checkbox.checked);
        checkbox.addEventListener('change', () => {
            setColumn(heading, index, checkbox.checked);
            if (!selectedColumns().length) {
                checkbox.checked = true;
                setColumn(heading, index, true);
            }
            try { localStorage.setItem(storageKey, JSON.stringify(headings.filter(item => !item.hidden).map(item => item.dataset.assetColumn))); } catch (_) {}
        });
        label.append(checkbox, document.createTextNode(heading.textContent.trim()));
        menu.appendChild(label);
    });
    if (!selectedColumns().length) {
        headings.forEach((heading, index) => setColumn(heading, index, true));
        menu.querySelectorAll('input').forEach(input => { input.checked = true; });
    }
    const exportUrl = (format, orientation) => {
        const url = new URL(window.location.href);
        for (const key of Array.from(url.searchParams.keys())) {
            if (key === 'page' || key === 'columns' || key.startsWith('columns[')) url.searchParams.delete(key);
        }
        url.searchParams.set('export', format);
        if (orientation) url.searchParams.set('orientation', orientation);
        else url.searchParams.delete('orientation');
        if (search?.value.trim()) url.searchParams.set('q', search.value.trim());
        else url.searchParams.delete('q');
        selectedColumns().forEach(key => url.searchParams.append('columns[]', key));
        return url;
    };
    toolkit.querySelectorAll('[data-assets-export]').forEach(link => {
        link.addEventListener('click', () => { clearTimeout(searchTimer); link.href = exportUrl(link.dataset.assetsExport, link.dataset.orientation).href; });
    });
    const safeCell = value => {
        const text = String(value ?? '').replace(/[\t\r\n]+/g, ' ');
        return /^[=+\-@]/.test(text) ? "'" + text : text;
    };
    toolkit.querySelector('[data-assets-copy]')?.addEventListener('click', async event => {
        clearTimeout(searchTimer);
        const button = event.currentTarget;
        button.disabled = true;
        try {
            const response = await fetch(exportUrl('json').href, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error('Export unavailable');
            const data = await response.json();
            const text = [data.headings, ...data.rows].map(row => row.map(safeCell).join('\t')).join('\n');
            if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(text);
            else {
                const field = document.createElement('textarea');
                field.value = text;
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                try { if (!document.execCommand('copy')) throw new Error('Copy unavailable'); }
                finally { field.remove(); button.focus(); }
            }
            if (status) status.textContent = toolkit.dataset.copySuccess;
        } catch (_) {
            if (status) status.textContent = toolkit.dataset.copyError;
        } finally { button.disabled = false; }
    });
    rowCount?.addEventListener('change', () => searchForm.requestSubmit());
    let searchTimer;
    search?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => searchForm.requestSubmit(), 600);
    });
    searchForm?.addEventListener('submit', () => clearTimeout(searchTimer));
})();
