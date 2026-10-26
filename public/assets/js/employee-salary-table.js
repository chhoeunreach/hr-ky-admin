(() => {
    const toolkit = document.getElementById('employeeSalaryToolkit');
    const table = document.getElementById('employeeSalaryTable');
    const form = document.getElementById('employeeSalaryFilters');
    if (!toolkit || !table || !form) return;
    const headings = Array.from(table.querySelectorAll('thead [data-salary-column]'));
    const rows = Array.from(table.querySelectorAll('tbody tr[data-salary-row]'));
    const menu = toolkit.querySelector('[data-salary-columns]');
    const status = document.querySelector('[data-salary-status]');
    const search = document.getElementById('salarySearch');
    const rowCount = document.getElementById('salaryRows');
    const storageKey = 'hr-employee-salary-columns-v1';
    let saved;
    try { saved = JSON.parse(localStorage.getItem(storageKey)); } catch (_) {}
    const defaultVisible = heading => heading.dataset.defaultVisible !== '0';
    const selectedColumns = () => headings.filter(heading => !heading.hidden).map(heading => heading.dataset.salaryColumn);
    const setColumn = (heading, index, visible) => {
        heading.hidden = !visible;
        rows.forEach(row => {if (row.cells[index]) row.cells[index].hidden = !visible;});
        const empty = table.querySelector('[data-salary-empty]');
        if (empty) empty.colSpan = selectedColumns().length + 1;
    };
    headings.forEach((heading, index) => {
        const label = document.createElement('label');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = Array.isArray(saved) ? saved.includes(heading.dataset.salaryColumn) : defaultVisible(heading);
        setColumn(heading, index, checkbox.checked);
        checkbox.addEventListener('change', () => {
            setColumn(heading, index, checkbox.checked);
            if (!selectedColumns().length) {
                checkbox.checked = true;
                setColumn(heading, index, true);
            }
            try {localStorage.setItem(storageKey, JSON.stringify(selectedColumns()));} catch (_) {}
        });
        label.append(checkbox, document.createTextNode(heading.textContent.trim()));
        menu.appendChild(label);
    });
    if (!selectedColumns().length) {
        headings.forEach((heading, index) => setColumn(heading, index, defaultVisible(heading)));
        menu.querySelectorAll('input').forEach((checkbox, index) => {checkbox.checked = defaultVisible(headings[index]);});
    }
    toolkit.querySelector('[data-salary-reset-columns]')?.addEventListener('click', () => {
        const checkboxes = menu.querySelectorAll('input');
        headings.forEach((heading, index) => {
            const visible = defaultVisible(heading);
            setColumn(heading, index, visible);
            checkboxes[index].checked = visible;
        });
        try {localStorage.removeItem(storageKey);} catch (_) {}
    });
    const exportUrl = (format, orientation) => {
        // Read the current controls so exports include filters the user has just selected.
        const url = new URL(form.action, window.location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        ['page', 'export', 'orientation', 'columns', 'columns[]'].forEach(key => url.searchParams.delete(key));
        selectedColumns().forEach(key => url.searchParams.append('columns[]', key));
        url.searchParams.set('export', format);
        if (orientation) url.searchParams.set('orientation', orientation);
        return url;
    };
    let searchTimer;
    toolkit.querySelectorAll('[data-salary-export]').forEach(link => {
        link.addEventListener('click', () => {
            clearTimeout(searchTimer);
            link.href = exportUrl(link.dataset.salaryExport, link.dataset.orientation).href;
        });
    });
    toolkit.querySelector('[data-salary-copy]')?.addEventListener('click', async event => {
        clearTimeout(searchTimer);
        const button = event.currentTarget;
        button.disabled = true;
        try {
            const response = await fetch(exportUrl('json').href, {headers: {Accept: 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error('Copy unavailable');
            const data = await response.json();
            const safeCell = value => {
                const text = String(value ?? '').replace(/[\t\r\n]+/g, ' ');
                return /^[=+\-@]/.test(text) ? "'" + text : text;
            };
            const text = [data.headings, ...data.rows].map(row => row.map(safeCell).join('\t')).join('\n');
            if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(text);
            else {
                const field = document.createElement('textarea');
                field.value = text;
                field.style.position = 'fixed'; field.style.opacity = '0';
                document.body.appendChild(field); field.select();
                try {if (!document.execCommand('copy')) throw new Error('Copy unavailable');}
                finally {field.remove(); button.focus();}
            }
            status.textContent = toolkit.dataset.copySuccess;
        } catch (_) {status.textContent = toolkit.dataset.copyError;}
        finally {button.disabled = false;}
    });
    rowCount?.addEventListener('change', () => {clearTimeout(searchTimer); form.requestSubmit();});
    search?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => form.requestSubmit(), 600);
    });
    form.addEventListener('submit', () => clearTimeout(searchTimer));
    // Keep menus usable at the bottom of the horizontally scrolling table.
    document.querySelectorAll('#employeeSalaryToolkit [data-bs-toggle="dropdown"], #employeeSalaryTable [data-bs-toggle="dropdown"]').forEach(button => {
        bootstrap.Dropdown.getOrCreateInstance(button, {boundary: document.body,
            popperConfig: config => ({...config, strategy: 'fixed'})});
    });
})();
