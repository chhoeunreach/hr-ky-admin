(() => {
    const modal = document.getElementById('salaryReviewHistoryModal');
    if (!modal || !window.bootstrap?.Modal) return;
    const body = modal.querySelector('[data-history-body]');
    const status = modal.querySelector('[data-history-status]');
    const retry = modal.querySelector('[data-history-retry]');
    const panel = modal.querySelector('.modal-body');
    let historyUrl, lastUrl, pending, sequence = 0, closed = true;
    const updateReviewAmounts = form => {
        if (!form) return;
        const baseField = form.querySelector('[name="base_salary"]');
        const revisedField = form.querySelector('[name="revised_salary"]');
        if (!baseField || !revisedField) return;
        const base = Number(baseField.value), revised = Number(revisedField.value);
        const valid = baseField.value !== '' && revisedField.value !== '' && Number.isFinite(base) && Number.isFinite(revised) && base >= 0 && revised >= 0;
        const baseCents = Math.round((base + Number.EPSILON) * 100);
        const difference = Math.round((revised + Number.EPSILON) * 100) - baseCents;
        const increment = difference / 100;
        // Round the percentage from cents, including negative halfway values.
        const percent = baseCents > 0 ? Math.sign(difference) * Math.floor((Math.abs(difference) * 10000 + baseCents / 2) / baseCents) / 100 : 0;
        form.querySelector('[data-review-increment]').textContent = valid ? increment.toFixed(2) : '-';
        form.querySelector('[data-review-percent]').textContent = valid ? percent.toFixed(2) + '%' : '-';
    };
    const errorMessage = async response => {
        try {return (await response.json()).message || modal.dataset.loadError;}
        catch (_) {return modal.dataset.loadError;}
    };
    const load = async href => {
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        url.searchParams.set('salary_history_modal', '1');
        lastUrl = url;
        const requestId = ++sequence;
        pending?.abort(); pending = new AbortController();
        status.textContent = modal.dataset.loading;
        retry.hidden = true;
        panel.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url.href, {credentials: 'same-origin', signal: pending.signal,
                headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error(await errorMessage(response));
            const html = await response.text();
            if (closed || requestId !== sequence) return false;
            const content = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-salary-history-content]');
            if (!content) throw new Error(modal.dataset.loadError);
            body.replaceChildren(content);
            updateReviewAmounts(body.querySelector('[data-salary-review-edit]'));
            status.textContent = '';
            body.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
            return true;
        } catch (error) {
            if (!closed && requestId === sequence && error.name !== 'AbortError') {
                status.textContent = error.message || modal.dataset.loadError;
                retry.hidden = false;
            }
            return false;
        } finally {if (requestId === sequence) panel.setAttribute('aria-busy', 'false');}
    };
    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
        const trigger = event.target.closest('[data-open-salary-history]');
        if (!trigger) return;
        const url = new URL(trigger.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        event.preventDefault();
        closed = false; historyUrl = url;
        body.replaceChildren();
        bootstrap.Modal.getOrCreateInstance(modal).show(trigger);
        load(historyUrl);
    });
    modal.addEventListener('click', event => {
        const link = event.target.closest('[data-salary-history-panel]');
        if (link) {event.preventDefault(); load(link.href);}
        else if (event.target.closest('[data-history-back]')) load(historyUrl);
    });
    retry.addEventListener('click', () => load(lastUrl));
    modal.addEventListener('input', event => updateReviewAmounts(event.target.closest('[data-salary-review-edit]')));
    modal.addEventListener('submit', async event => {
        const form = event.target;
        if (!form.matches('[data-salary-history-form]')) return;
        event.preventDefault();
        if (form.dataset.saving === '1') return;
        form.dataset.saving = '1';
        const buttons = Array.from(form.querySelectorAll('button'));
        buttons.forEach(button => {button.disabled = true;});
        const error = form.querySelector('[data-history-form-error]');
        error.textContent = '';
        const requestId = sequence;
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin',
                headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error(await errorMessage(response));
            const data = await response.json();
            document.dispatchEvent(new CustomEvent('employee-salary:changed'));
            if (!closed && requestId === sequence && await load(historyUrl)) status.textContent = data.message;
        } catch (failure) {error.textContent = failure.message || modal.dataset.loadError;}
        finally {form.dataset.saving = '0'; buttons.forEach(button => {button.disabled = false;});}
    });
    modal.addEventListener('hidden.bs.modal', () => {
        closed = true; sequence++; pending?.abort();
        body.replaceChildren(); status.textContent = ''; retry.hidden = true;
        panel.setAttribute('aria-busy', 'false');
    });
})();
