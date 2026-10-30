(() => {
    const form = document.getElementById('employeeProfileFilters');
    const input = document.getElementById('employeeProfileSearch');
    const clear = document.getElementById('employeeProfileSearchClear');
    const error = document.getElementById('employeeProfileSearchError');
    const loading = document.getElementById('employeeProfileSearchLoading');
    if (!form || !input || !clear) return;
    let timer, pending, sequence = 0, composing = false;
    const results = () => document.getElementById('employeeProfileResults');
    const syncClear = () => { clear.style.display = input.value ? 'flex' : 'none'; };
    const cancel = () => {
        window.clearTimeout(timer);
        ++sequence;
        pending?.abort();
        results()?.setAttribute('aria-busy', 'false');
        loading?.classList.add('d-none');
    };
    const formUrl = () => {
        const url = new URL(form.action || window.location.href, window.location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        return url;
    };
    const syncSort = url => {
        for (const name of ['name_direction', 'working_life_direction']) {
            let field = form.querySelector(`[name="${name}"]`);
            if (!url.searchParams.has(name)) { field?.remove(); continue; }
            if (!field) {
                field = document.createElement('input');
                field.type = 'hidden'; field.name = name;
                form.appendChild(field);
            }
            field.value = url.searchParams.get(name);
        }
    };
    const restore = url => {
        const branch = form.querySelector('[name="branch_id"]');
        if (branch) {
            branch.value = url.searchParams.get('branch_id') || '';
            branch.dispatchEvent(new Event('change'));
        }
        for (const field of Array.from(form.elements)) {
            if (!field.name || field.type === 'submit') continue;
            const values = url.searchParams.getAll(field.name);
            if (field.multiple) {
                const selected = values.length ? values : url.searchParams.getAll('department_id');
                Array.from(field.options).forEach(option => { option.selected = selected.includes(option.value); });
            } else {
                field.value = values[0] ?? ({employment_status: 'active', per_page: '25'}[field.name] || '');
            }
        }
        if (window.jQuery) window.jQuery(form).find('select').trigger('change.select2');
        syncSort(url);
        syncClear();
    };
    const initializeDropdowns = () => {
        results()?.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(button => {
            window.bootstrap?.Dropdown.getOrCreateInstance(button, {
                boundary: document.body,
                popperConfig: config => ({...config, strategy: 'fixed'}),
            });
        });
    };
    const refresh = async (url, {push = false} = {}) => {
        cancel();
        const requestNumber = sequence;
        pending = new AbortController();
        results()?.setAttribute('aria-busy', 'true');
        error?.classList.add('d-none');
        loading?.classList.remove('d-none');
        try {
            const response = await fetch(url.href, {
                credentials: 'same-origin', signal: pending.signal, cache: 'no-store',
                headers: {'X-Employee-Profile-Results': '1', 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html'},
            });
            if (!response.ok || response.redirected) throw new Error('Unable to load directory');
            const html = await response.text();
            if (requestNumber !== sequence) return;
            const incoming = new DOMParser().parseFromString(html, 'text/html').getElementById('employeeProfileResults');
            if (!incoming) throw new Error('Missing directory results');
            results().querySelectorAll('[data-bs-toggle="dropdown"]').forEach(button => window.bootstrap?.Dropdown.getInstance(button)?.dispose());
            results().replaceWith(incoming);
            initializeDropdowns();
            window.feather?.replace();
            syncSort(url);
            window.history[push ? 'pushState' : 'replaceState']({}, '', url.href);
        } catch (failure) {
            if (requestNumber === sequence && failure.name !== 'AbortError') error?.classList.remove('d-none');
        } finally {
            if (requestNumber === sequence) {
                results()?.setAttribute('aria-busy', 'false');
                loading?.classList.add('d-none');
            }
        }
    };
    const schedule = () => {
        cancel();
        syncClear();
        if (!composing) timer = window.setTimeout(() => refresh(formUrl()), 350);
    };
    input.addEventListener('input', schedule);
    input.addEventListener('compositionstart', () => { composing = true; cancel(); });
    input.addEventListener('compositionend', () => { composing = false; schedule(); });
    clear.addEventListener('click', () => {
        input.value = '';
        syncClear();
        input.focus();
        refresh(formUrl());
    });
    form.addEventListener('change', cancel);
    form.addEventListener('submit', event => {
        if (event.submitter?.hasAttribute('data-employee-report')) { cancel(); return; }
        event.preventDefault();
        refresh(formUrl(), {push: true});
    });
    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
        const link = event.target.closest('#employeeProfileResults .pagination a[href], #employeeProfileResults [data-profile-results-link]');
        if (!link) return;
        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || destination.pathname !== window.location.pathname) return;
        event.preventDefault();
        const url = formUrl();
        if (link.hasAttribute('data-profile-results-link')) {
            for (const name of ['name_direction', 'working_life_direction']) {
                url.searchParams.delete(name);
                if (destination.searchParams.has(name)) url.searchParams.set(name, destination.searchParams.get(name));
            }
        } else if (input.value === (destination.searchParams.get('search') || '')) {
            url.searchParams.set('page', destination.searchParams.get('page') || '1');
        }
        refresh(url, {push: true});
    });
    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        cancel();
        restore(url);
        refresh(url);
    });
    syncClear();
    initializeDropdowns();
})();
