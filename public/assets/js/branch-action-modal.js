(() => {
    const modal = document.getElementById('branchActionModal');
    if (!modal || !window.bootstrap?.Modal) return;
    const frame = modal.querySelector('[data-branch-action-frame]');
    const title = modal.querySelector('.modal-title');
    const branchesUrl = new URL(modal.dataset.indexUrl, window.location.href);
    let changed = false;
    const modalInstance = () => window.bootstrap.Modal.getOrCreateInstance(modal);
    const open = trigger => {
        const href = trigger.getAttribute('href');
        if (!href) return false;
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        url.searchParams.set('branch_modal', '1');
        title.textContent = trigger.dataset.branchModalTitle || trigger.title || trigger.textContent.trim();
        frame.title = title.textContent;
        frame.src = url.href;
        modalInstance().show(trigger);
        return true;
    };
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-open-branch-modal]');
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
            if (!form.querySelector('[name="branch_modal"]')) {
                const field = child.createElement('input');
                field.type = 'hidden'; field.name = 'branch_modal'; field.value = '1';
                form.appendChild(field);
            }
        }, true);
        child.addEventListener('click', event => {
            const link = event.target.closest('a[href]');
            if (!link || link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript:')) return;
            const url = new URL(link.href, frame.src);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === branchesUrl.pathname) {
                event.preventDefault(); modalInstance().hide();
            } else if (link.hasAttribute('data-open-branch-modal')) {
                event.preventDefault(); open(link);
            }
        });
    });
    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
        if (event.data?.type === 'branch-action-saved') {
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
