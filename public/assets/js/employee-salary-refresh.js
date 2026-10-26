(() => {
    const page = document.querySelector('.employee-salary-page');
    const form = document.getElementById('employeeSalaryFilters');
    const feedback = page?.querySelector('[data-salary-feedback]');
    if (!page || !form || !feedback) return;
    let pending, sequence = 0;
    const notify = (message = '', error = false) => {
        feedback.textContent = message;
        feedback.dataset.error = String(error);
    };
    const formUrl = () => {
        const url = new URL(form.action, window.location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        return url;
    };
    const restoreFilters = url => {
        for (const field of Array.from(form.elements)) {
            if (!field.name || field.type === 'submit') continue;
            field.value = url.searchParams.get(field.name) ?? (field.name === 'per_page' ? '25' : '');
        }
        if (window.jQuery) {
            window.jQuery('#branch_id, #department_id').trigger('change.select2');
        }
        document.dispatchEvent(new CustomEvent('employee-salary:filters-restored', {
            detail: {departmentId: url.searchParams.get('department_id')},
        }));
    };
    const responseError = async response => {
        try {return (await response.json()).message || page.dataset.salaryRefreshError;}
        catch (_) {return page.dataset.salaryRefreshError;}
    };
    const refresh = async (href, {push = true, restore = false, message = ''} = {}) => {
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        const requestNumber = ++sequence;
        pending?.abort();
        pending = new AbortController();
        document.dispatchEvent(new CustomEvent('employee-salary:refresh-start'));
        const current = document.getElementById('employeeSalaryResults');
        current.setAttribute('aria-busy', 'true');
        notify();
        try {
            const response = await fetch(url.href, {credentials: 'same-origin', signal: pending.signal,
                headers: {'X-Salary-Fragment': '1', 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json'}});
            if (!response.ok) throw new Error(await responseError(response));
            const html = await response.text();
            if (requestNumber !== sequence) return false;
            const incoming = new DOMParser().parseFromString(html, 'text/html').getElementById('employeeSalaryResults');
            if (!incoming) throw new Error(page.dataset.salaryRefreshError);
            const active = document.activeElement;
            const search = document.getElementById('salarySearch');
            // Leave newer typing intact until its debounce starts the next request.
            if (!restore && search && search.value !== (url.searchParams.get('q') || '')) return false;
            const focus = current.contains(active) && active.id ? {id: active.id, start: active.selectionStart, end: active.selectionEnd} : null;
            const scroll = current.querySelector('.salary-table-scroll')?.scrollLeft || 0;
            current.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(button => window.bootstrap?.Dropdown.getInstance(button)?.dispose());
            current.replaceWith(incoming);
            if (restore) restoreFilters(url);
            document.dispatchEvent(new CustomEvent('employee-salary:rendered'));
            window.feather?.replace();
            const scroller = incoming.querySelector('.salary-table-scroll');
            if (scroller) scroller.scrollLeft = scroll;
            if (focus) {
                const field = document.getElementById(focus.id);
                field?.focus({preventScroll: true});
                if (typeof focus.start === 'number') field?.setSelectionRange?.(focus.start, focus.end);
            }
            if (push && url.href !== window.location.href) window.history.pushState({}, '', url.href);
            notify(message);
            return true;
        } catch (error) {
            if (requestNumber === sequence && error.name !== 'AbortError') notify(error.message || page.dataset.salaryRefreshError, true);
            return false;
        } finally {
            if (requestNumber === sequence) document.getElementById('employeeSalaryResults')?.setAttribute('aria-busy', 'false');
        }
    };
    form.addEventListener('submit', event => {
        event.preventDefault();
        refresh(formUrl());
    });
    page.addEventListener('click', event => {
        if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
        const reset = event.target.closest('[data-salary-reset]');
        const pagination = event.target.closest('.salary-table-footer a[href]');
        const button = event.target.closest('[data-salary-refresh]');
        if (reset || pagination || button) {
            event.preventDefault();
            refresh(reset?.href || pagination?.href || formUrl(), {restore: Boolean(reset), message: button ? page.dataset.salaryRefreshSuccess : ''});
        }
    });
    window.addEventListener('popstate', () => refresh(window.location.href, {push: false, restore: true}));
    document.addEventListener('employee-salary:changed', () => refresh(window.location.href, {push: false, restore: true, message: page.dataset.salaryRefreshSuccess}));
    const saving = new WeakSet();
    document.addEventListener('employee-salary:submit', async event => {
        const actionForm = event.target;
        if (!page.contains(actionForm) || !actionForm.matches('.deleteEmployeeSalaryForm, .salary-cycle-form, [data-salary-delete-form]')) return;
        event.preventDefault();
        if (saving.has(actionForm)) return;
        saving.add(actionForm);
        const cycle = actionForm.closest?.('td')?.querySelector('.salary-cycle-select');
        const submit = actionForm.querySelector?.('[type="submit"]');
        if (cycle) cycle.disabled = true;
        if (submit) submit.disabled = true;
        try {
            const response = await fetch(actionForm.action, {method: 'POST', body: new FormData(actionForm), credentials: 'same-origin',
                headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error(await responseError(response));
            const data = await response.json();
            const modal = actionForm.closest?.('.modal');
            if (modal) window.bootstrap?.Modal.getInstance(modal)?.hide();
            await refresh(window.location.href, {push: false, restore: true, message: data.message});
        } catch (error) {
            if (cycle) cycle.value = cycle.dataset.current;
            const modalError = actionForm.querySelector?.('[data-salary-delete-error]');
            if (modalError) modalError.textContent = error.message || page.dataset.salaryRefreshError;
            notify(error.message || page.dataset.salaryRefreshError, true);
        } finally {
            saving.delete(actionForm);
            if (cycle) cycle.disabled = false;
            if (submit) submit.disabled = false;
        }
    });
})();
