(() => {
    const modal = document.getElementById('departmentActionModal');
    if (!modal || !window.bootstrap?.Modal) return;
    const frame = modal.querySelector('[data-department-action-frame]');
    const title = modal.querySelector('.modal-title');
    const departmentsUrl = new URL(modal.dataset.indexUrl, window.location.href);
    let changed = false;
    const modalInstance = () => window.bootstrap.Modal.getOrCreateInstance(modal);
    const open = trigger => {
        const href = trigger.getAttribute('href');
        if (!href) return false;
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        url.searchParams.set('department_modal', '1');
        title.textContent = trigger.dataset.departmentModalTitle || trigger.title || trigger.textContent.trim();
        frame.title = title.textContent;
        frame.src = url.href;
        modalInstance().show(trigger);
        return true;
    };
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-open-department-modal]');
        if (trigger && open(trigger)) event.preventDefault();
    });
    frame.addEventListener('load', () => {
        let child;
        try { child = frame.contentDocument; } catch (_) { return; }
        if (!child) return;
        child.addEventListener('submit', event => {
            const form = event.target;
            const action = new URL(form.action, frame.src);
            if (action.origin !== window.location.origin) return;
            if (form.method.toLowerCase() !== 'get') changed = true;
            if (!form.querySelector('[name="department_modal"]')) {
                const field = child.createElement('input');
                field.type = 'hidden'; field.name = 'department_modal'; field.value = '1';
                form.appendChild(field);
            }
        }, true);
        child.addEventListener('click', event => {
            const link = event.target.closest('a[href]');
            if (!link || link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript:')) return;
            const url = new URL(link.href, frame.src);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === departmentsUrl.pathname) {
                event.preventDefault(); modalInstance().hide();
            } else if (link.hasAttribute('data-open-department-modal')) {
                event.preventDefault(); open(link);
            }
        });
    });
    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
        if (event.data?.type === 'department-action-saved') {
            changed = true;
            modalInstance().hide();
        }
    });
    modal.addEventListener('hidden.bs.modal', () => {
        frame.removeAttribute('src');
        if (changed) window.location.reload();
        changed = false;
    });
})();
