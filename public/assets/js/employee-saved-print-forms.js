(() => {
    const configNode = document.getElementById('employeeSavedPrintConfig');
    if (!configNode) return;
    const config = JSON.parse(configNode.textContent);
    const forms = {
        complete: ['#complete-form .employee-complete-paper', 'printEmployeeCompleteForm'],
        overview: ['#discipline .employee-overview-paper', 'printOverviewForm'],
        warning: ['#staff-warning-form .employee-warning-paper', 'printStaffWarningForm'],
        salaryCertificate: ['#salary-certificate .employee-salary-certificate-paper', 'printSalaryCertificate'],
        attendanceDailyReport: ['#attendance-daily-report .employee-attendance-daily-report-paper', 'printAttendanceDailyReport'],
    };
    const saved = new Map();
    const snapshot = paper => ({
        document_text: paper.innerText.trim(),
        fields: Array.from(paper.querySelectorAll('input, textarea, select')).map((field, index) => ({
            label: field.getAttribute('aria-label') || field.closest('label')?.textContent?.trim()
                || field.closest('.employee-print-form-field')?.querySelector('span')?.textContent?.trim()
                || field.name || field.id || `Field ${index + 1}`,
            value: field.value.trim() || null,
            checked: field.matches('input[type="checkbox"], input[type="radio"]') ? field.checked : null,
        })),
    });
    window.EmployeeSavedForms = {
        recordId(type, paper) {
            const record = saved.get(type);
            return record?.signature === JSON.stringify(snapshot(paper)) ? record.id : null;
        },
    };
    const editModal = document.getElementById('employeeSavePrintFormModal');
    const editBody = editModal.querySelector('[data-save-print-paper]');
    const editError = editModal.querySelector('[data-save-print-error]');
    const confirm = editModal.querySelector('[data-save-print-confirm]');
    let active, saving = false;
    const openEditor = (paper, save, persist, sync) => {
        const placeholder = document.createComment('saved form position');
        paper.before(placeholder); editBody.replaceChildren(paper); editError.textContent = '';
        confirm.textContent = save.textContent;
        editModal.querySelector('.modal-title').textContent = save.textContent;
        active = {paper, placeholder, persist, sync};
        bootstrap.Modal.getOrCreateInstance(editModal).show(save);
        window.StaffWarningA4?.refresh(paper);
    };
    editModal.addEventListener('hide.bs.modal', event => {if (saving) event.preventDefault();});
    editModal.addEventListener('hidden.bs.modal', () => {
        if (active) {active.placeholder.replaceWith(active.paper); window.StaffWarningA4?.refresh(active.paper); active.sync(); active = null;}
    });
    confirm.addEventListener('click', async () => {
        if (!active || saving) return;
        saving = true; confirm.disabled = true; editError.textContent = '';
        try {
            const result = await active.persist();
            saving = false;
            if (result) bootstrap.Modal.getOrCreateInstance(editModal).hide();
            else editError.textContent = config.error;
        } finally {saving = false; confirm.disabled = false;}
    });
    const historyModal = document.getElementById('employeeSavedFormHistory');
    const historyBody = historyModal.querySelector('[data-saved-form-history]');
    const more = historyModal.querySelector('[data-saved-form-more]');
    let historyType, nextPage, historyGeneration = 0;
    const loadHistory = async (type, page = 1) => {
        const generation = ++historyGeneration;
        if (page === 1) historyBody.replaceChildren();
        more.hidden = true; more.disabled = true;
        try {
            const url = new URL(config.historyUrl, window.location.href);
            url.searchParams.set('form_type', type); url.searchParams.set('page', String(page));
            const response = await fetch(url, {headers: {Accept: 'application/json'}, credentials: 'same-origin'});
            if (!response.ok) throw new Error();
            const result = await response.json();
            if (generation !== historyGeneration) return;
            if (!result.data.length && page === 1) historyBody.textContent = config.empty;
            result.data.forEach(record => {
                const details = document.createElement('details'); details.className = 'border rounded p-3 mb-2';
                const summary = document.createElement('summary');
                const date = new Date(record.created_at).toLocaleString(document.documentElement?.lang || 'km', {timeZone: 'Asia/Bangkok'});
                summary.textContent = `#${record.id} · ${config.actions?.[record.action] || record.action} · ${record.created_by || '—'} · ${date}`;
                if (record.warning_number) summary.textContent += ` · ${config.warningNumber}: ${record.warning_number}`;
                const text = document.createElement('div');
                text.className = 'mt-3'; text.style.whiteSpace = 'pre-wrap'; text.textContent = record.document_text;
                details.append(summary, text);
                (record.fields || []).forEach(field => {
                    const value = document.createElement('div');
                    value.style.whiteSpace = 'pre-wrap';
                    value.textContent = `${field.label}: ${field.value || ''}${field.checked == null ? '' : ' · ' + (field.checked ? config.checked : config.unchecked)}`;
                    details.appendChild(value);
                });
                historyBody.appendChild(details);
            });
            historyType = type; nextPage = result.current_page + 1;
            more.hidden = !result.next_page_url;
        } catch (_) {
            if (generation === historyGeneration) historyBody.textContent = config.error;
        } finally { if (generation === historyGeneration) more.disabled = false; }
    };
    more.addEventListener('click', () => loadHistory(historyType, nextPage));
    historyModal.addEventListener('hidden.bs.modal', () => {historyGeneration++; historyBody.replaceChildren();});
    for (const [type, [selector, printFunction]] of Object.entries(forms)) {
        const paper = document.querySelector(selector);
        if (!paper || !window.EmployeeFormPrintPermissions?.[type]) continue;
        let touched = false;
        const printButtons = Array.from(document.querySelectorAll(`[onclick*="${printFunction}("]`));
        const toolbar = document.createElement('div'); toolbar.className = 'employee-complete-toolbar d-flex flex-wrap align-items-center gap-2 mb-3 no-print';
        const save = document.createElement('button'); save.type = 'button'; save.className = 'btn btn-primary btn-sm'; save.textContent = config.create;
        const fresh = document.createElement('button'); fresh.type = 'button'; fresh.className = 'btn btn-outline-secondary btn-sm'; fresh.textContent = config.newForm;
        const history = document.createElement('button'); history.type = 'button'; history.className = 'btn btn-outline-secondary btn-sm'; history.textContent = config.history;
        const status = document.createElement('span'); status.className = 'small text-muted'; status.setAttribute('role', 'status');
        toolbar.append(save, fresh, history, status); paper.before(toolbar);
        const sync = () => {
            const id = window.EmployeeSavedForms.recordId(type, paper);
            printButtons.forEach(button => {button.disabled = !id;});
            save.textContent = saved.has(type) ? config.update : config.create;
        };
        const changed = () => {touched = true; sync();};
        paper.addEventListener('input', changed); paper.addEventListener('change', changed); sync();
        let numberPromise;
        const ensureWarningNumber = () => {
            if (type !== 'warning' || !config.warningNumberUrl) return Promise.resolve(true);
            const number = paper.querySelector('[data-warning-number]');
            if (paper.dataset.warningNumberId || (saved.has(type) && /^WRN-\d+$/.test(number?.textContent || ''))) return Promise.resolve(true);
            if (numberPromise) return numberPromise;
            numberPromise = (async () => {
                status.textContent = config.generatingWarningNumber;
                try {
                    const response = await fetch(config.warningNumberUrl, {
                        method: 'POST', credentials: 'same-origin',
                        headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': config.csrf},
                        body: JSON.stringify({}),
                    });
                    const result = await response.json();
                    if (!response.ok || !Number.isInteger(result.warning_number_id) || !/^WRN-\d+$/.test(result.warning_number)) throw new Error();
                    paper.dataset.warningNumberId = String(result.warning_number_id);
                    number.textContent = result.warning_number;
                    status.textContent = '';
                    sync();
                    return true;
                } catch (_) {status.textContent = config.error; return false;}
            })().finally(() => {numberPromise = null;});
            return numberPromise;
        };
        const launchEditor = () => {
            if (type !== 'warning' || !config.warningNumberUrl) return openEditor(paper, save, persist, sync);
            save.disabled = true; fresh.disabled = true;
            ensureWarningNumber().then(ready => {if (ready) openEditor(paper, save, persist, sync);})
                .finally(() => {save.disabled = false; fresh.disabled = false;});
        };
        (async () => {
            try {
                const url = new URL(config.historyUrl, window.location.href);
                url.searchParams.set('form_type', type); url.searchParams.set('latest', '1');
                const response = await fetch(url, {headers: {Accept: 'application/json'}, credentials: 'same-origin'});
                if (!response.ok) return;
                const result = await response.json();
                const record = result.data?.[0];
                if (record && !touched && !saved.has(type)) {
                    const warningNumber = paper.querySelector('[data-warning-number]');
                    if (warningNumber && record.warning_number) warningNumber.textContent = record.warning_number;
                    if (type === 'warning') {
                        paper.querySelectorAll('input, textarea, select').forEach(field => {
                            const label = field.getAttribute('aria-label') || field.name || field.id;
                            const value = record.fields?.find(item => item.label === label);
                            if (!value) return;
                            field.value = value.value ?? '';
                            if (field.matches('input[type="checkbox"], input[type="radio"]')) field.checked = Boolean(value.checked);
                        });
                        window.StaffWarningA4?.refresh(paper);
                    }
                    saved.set(type, {id: record.id, signature: JSON.stringify({document_text: record.document_text, fields: record.fields})});
                    status.textContent = `${config.number} #${record.id}`; sync();
                }
            } catch (_) { /* Creating a form remains available if history cannot load. */ }
            if (type === 'warning') await ensureWarningNumber();
        })();
        fresh.addEventListener('click', () => {
            touched = true; saved.delete(type); status.textContent = '';
            const warningNumber = paper.querySelector('[data-warning-number]');
            if (warningNumber) warningNumber.textContent = config.warningNumberUrl ? config.generatingWarningNumber : 'WRN-______';
            if (paper.dataset) delete paper.dataset.warningNumberId;
            sync(); launchEditor();
        });
        history.addEventListener('click', () => {
            bootstrap.Modal.getOrCreateInstance(historyModal).show(history); loadHistory(type);
        });
        const persist = async () => {
            if (!(await ensureWarningNumber())) return false;
            save.disabled = true; fresh.disabled = true; status.textContent = config.saving;
            const data = snapshot(paper);
            try {
                const response = await fetch(config.saveUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': config.csrf},
                    body: JSON.stringify({form_type: type, ...data, ...(saved.has(type) ? {saved_form_id: saved.get(type).id} : {}),
                        ...(type === 'warning' && paper.dataset?.warningNumberId ? {warning_number_id: Number(paper.dataset.warningNumberId)} : {})}),
                });
                const result = await response.json();
                if (!response.ok || result.saved !== true || !Number.isInteger(result.saved_form_id)) throw new Error();
                const warningNumber = paper.querySelector('[data-warning-number]');
                const numberWasPending = warningNumber?.textContent === 'WRN-______';
                if (warningNumber && result.warning_number) warningNumber.textContent = result.warning_number;
                const persisted = {...data, document_text: numberWasPending && result.warning_number ? data.document_text.replace('WRN-______', result.warning_number) : data.document_text};
                saved.set(type, {id: result.saved_form_id, signature: JSON.stringify(persisted)});
                status.textContent = `${config.saved} ${config.number} #${result.saved_form_id}`;
                return true;
            } catch (_) {status.textContent = config.error; return false;}
            finally {save.disabled = false; fresh.disabled = false; sync();}
        };
        save.addEventListener('click', () => {touched = true; launchEditor();});
    }
})();
