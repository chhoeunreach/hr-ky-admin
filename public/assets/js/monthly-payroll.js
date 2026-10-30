(() => {
    const page = document.querySelector('[data-monthly-payroll-page]');
    const form = document.getElementById('monthlyPayrollFilters');
    if (!page || !form) return;
    const error = page.querySelector('[data-monthly-error]');
    let pending, sequence = 0, timer;
    const urlFromForm = () => {
        const url = new URL(form.action, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        return url;
    };
    const filterDepartments = () => {
        const branch = form.elements.branch_id.value;
        for (const option of form.elements.department_id.options) {
            option.hidden = Boolean(branch && option.dataset.branch && option.dataset.branch !== branch);
            option.disabled = option.hidden;
            if (option.hidden && option.selected) form.elements.department_id.value = '';
        }
    };
    const refresh = async (href, restore = false, push = true) => {
        clearTimeout(timer);
        const url = new URL(href, location.href);
        if (url.origin !== location.origin) return;
        const requestId = ++sequence;
        pending?.abort(); pending = new AbortController();
        const block = document.getElementById('monthlyPayrollResults');
        const summary = document.getElementById('monthlyPayrollSummary');
        block.setAttribute('aria-busy', 'true'); error.textContent = '';
        summary?.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url.href, {credentials: 'same-origin', signal: pending.signal,
                headers: {Accept: 'application/json', 'X-Monthly-Payroll-Fragment': '1'}});
            if (!response.ok) throw new Error(page.dataset.error);
            const html = await response.text();
            if (requestId !== sequence) return;
            const fragment = new DOMParser().parseFromString(html, 'text/html');
            const incoming = fragment.getElementById('monthlyPayrollResults');
            const incomingSummary = fragment.getElementById('monthlyPayrollSummary');
            if (!incoming || !incomingSummary) throw new Error(page.dataset.error);
            if (!restore && form.elements.q.value !== (url.searchParams.get('q') || '')) return;
            const left = block.querySelector('.monthly-payroll-scroll').scrollLeft;
            block.replaceWith(incoming);
            summary?.replaceWith(incomingSummary);
            incoming.querySelector('.monthly-payroll-scroll').scrollLeft = left;
            if (restore) {
                for (const field of Array.from(form.elements)) {
                    if (field.name) field.value = url.searchParams.get(field.name) ?? (field.name === 'month' ? form.dataset.defaultMonth : field.name === 'per_page' ? '25' : '');
                }
                filterDepartments();
            }
            if (push && url.href !== location.href) history.pushState({}, '', url.href);
            window.feather?.replace();
        } catch (failure) {if (requestId === sequence && failure.name !== 'AbortError') error.textContent = page.dataset.error;}
        finally {
            if (requestId === sequence) {
                document.getElementById('monthlyPayrollResults').setAttribute('aria-busy', 'false');
                document.getElementById('monthlyPayrollSummary')?.setAttribute('aria-busy', 'false');
            }
        }
    };
    form.addEventListener('submit', event => {event.preventDefault(); refresh(urlFromForm());});
    form.elements.per_page.addEventListener('change', () => form.requestSubmit());
    form.elements.q.addEventListener('input', () => {clearTimeout(timer); timer = setTimeout(() => form.requestSubmit(), 600);});
    form.elements.branch_id.addEventListener('change', filterDepartments);
    filterDepartments();
    page.addEventListener('click', event => {
        if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
        const exportLink = event.target.closest('[data-monthly-export]');
        if (exportLink) {
            clearTimeout(timer);
            const url = urlFromForm(); url.searchParams.set('export', exportLink.dataset.monthlyExport); exportLink.href = url.href;
            return;
        }
        const reset = event.target.closest('[data-monthly-reset]');
        const link = reset || event.target.closest('.monthly-payroll-footer a[href]');
        if (link) {event.preventDefault(); refresh(link.href, Boolean(reset));}
    });
    window.addEventListener('popstate', () => refresh(location.href, true, false));
    document.addEventListener('monthly-payroll:changed', () => refresh(location.href, true, false));
})();
