(() => {
    const modal = document.getElementById('employeeProfileFormModal');
    if (!modal || !window.bootstrap?.Modal) return;
    const frame = modal.querySelector('[data-employee-form-frame]');
    const title = modal.querySelector('.modal-title');
    const directory = new URL(modal.dataset.directoryUrl, window.location.href);
    let changed = false;
    const instance = () => bootstrap.Modal.getOrCreateInstance(modal);
    const embeddedUrl = href => {
        let url;
        try { url = new URL(href, window.location.href); } catch (_) { return null; }
        if (url.origin !== window.location.origin) return null;
        url.searchParams.set('employee_modal', '1');
        if (modal.dataset.context === 'salary') url.searchParams.set('salary_modal', '1');
        return url;
    };
    const load = (url, label) => {
        if (label) title.textContent = label;
        frame.title = title.textContent;
        frame.src = url.href;
    };
    const open = trigger => {
        const href = trigger.getAttribute('href');
        if (!href) return false;
        const url = embeddedUrl(href);
        if (!url) return false;
        const show = () => {
            load(url, trigger.dataset.employeeFormTitle || trigger.title || trigger.textContent.trim());
            instance().show(trigger);
        };
        const previousModal = trigger.closest('.modal.show');
        if (previousModal && previousModal !== modal) {
            previousModal.addEventListener('hidden.bs.modal', show, {once: true});
            bootstrap.Modal.getOrCreateInstance(previousModal).hide();
        } else show();
        return true;
    };
    document.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const trigger = event.target.closest('[data-open-employee-form]');
        if (trigger && open(trigger)) event.preventDefault();
    });
    document.addEventListener('submit', event => {
        const submitter = event.submitter;
        const form = event.target;
        if (form.hasAttribute('data-employee-modal-submit')) {
            const url = embeddedUrl(form.action);
            if (!url) return;
            form.action = url.href;
            form.target = frame.name;
            changed = true;
            const previous = form.closest('.modal.show');
            const show = () => instance().show(submitter);
            title.textContent = previous?.querySelector('.modal-title')?.textContent || title.textContent;
            frame.title = title.textContent;
            if (previous) {
                previous.addEventListener('hidden.bs.modal', show, {once: true});
                bootstrap.Modal.getOrCreateInstance(previous).hide();
            } else show();
            return;
        }
        if (!submitter?.hasAttribute('data-employee-report')) return;
        const url = embeddedUrl(event.target.action || window.location.href);
        if (!url) return;
        event.preventDefault();
        url.search = '';
        url.searchParams.set('employee_modal', '1');
        for (const [key, value] of new FormData(event.target)) url.searchParams.append(key, value);
        url.searchParams.set(submitter.name, submitter.value);
        load(url, submitter.textContent.trim());
        instance().show(submitter);
    });
    frame.addEventListener('load', () => {
        let child, url;
        try {
            child = frame.contentDocument;
            url = new URL(frame.contentWindow.location.href);
        } catch (_) { return; }
        if (!child || url.origin !== window.location.origin) return;
        if (modal.dataset.context === 'salary' && child.querySelector?.('[data-salary-modal-saved]')) {
            changed = true;
            instance().hide();
            return;
        }
        if (child.querySelector?.('.app-flash-message.alert-success')) changed = true;
        // A successful save may redirect without retaining the embedding flag.
        if (url.pathname.startsWith('/admin/') && url.searchParams.get('employee_modal') !== '1') {
            load(embeddedUrl(url.href)); return;
        }
        child.addEventListener('submit', event => {
            const form = event.target;
            const destination = embeddedUrl(form.action || url.href);
            if (!destination) return;
            if (form.method.toLowerCase() !== 'get') changed = true;
            form.action = destination.href;
            if (!form.querySelector('[name="employee_modal"]')) {
                const field = child.createElement('input');
                field.type = 'hidden'; field.name = 'employee_modal'; field.value = '1';
                form.appendChild(field);
            }
            if (modal.dataset.context === 'salary' && !form.querySelector('[name="salary_modal"]')) {
                const field = child.createElement('input');
                field.type = 'hidden'; field.name = 'salary_modal'; field.value = '1';
                form.appendChild(field);
            }
        }, true);
        child.addEventListener('click', event => {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || link.hasAttribute('download') || link.target === '_blank'
                || link.hasAttribute('data-bs-toggle') || link.matches('[data-open-request-form], [data-open-leave-request-form]')) return;
            const destination = embeddedUrl(link.href);
            if (!destination) return;
            if (destination.pathname === directory.pathname) {
                event.preventDefault(); instance().hide();
            } else if (destination.pathname.startsWith('/admin/')) {
                event.preventDefault();
                load(destination, link.dataset.employeeFormTitle || link.title || link.textContent.trim());
            }
        });
    });
    modal.addEventListener('hidden.bs.modal', () => {
        frame.removeAttribute('src');
        if (changed) {
            if (modal.dataset.refreshEvent) document.dispatchEvent(new CustomEvent(modal.dataset.refreshEvent));
            else window.location.reload();
        }
        changed = false;
    });
})();
