(() => {
    const modal = document.getElementById('assetActionModal');
    if (!modal || !window.bootstrap?.Modal) return;
    const frame = modal.querySelector('[data-asset-action-frame]');
    const title = modal.querySelector('.modal-title');
    const assetsUrl = new URL(modal.dataset.assetsUrl, window.location.href);
    let changed = false;
    const modalInstance = () => window.bootstrap.Modal.getOrCreateInstance(modal);
    const open = trigger => {
        const href = trigger.getAttribute('href');
        if (!href) return false;
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) return false;
        url.searchParams.set('asset_modal', '1');
        title.textContent = trigger.dataset.assetModalTitle || trigger.title || trigger.textContent.trim();
        frame.title = title.textContent;
        frame.src = url.href;
        modalInstance().show(trigger);
        return true;
    };
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-open-asset-modal]');
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
            if (!form.querySelector('[name="asset_modal"]')) {
                const field = child.createElement('input');
                field.type = 'hidden'; field.name = 'asset_modal'; field.value = '1';
                form.appendChild(field);
            }
        }, true);
        child.addEventListener('click', event => {
            const link = event.target.closest('a[href]');
            if (!link || link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript:')) return;
            const url = new URL(link.href, frame.src);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === assetsUrl.pathname) {
                event.preventDefault(); modalInstance().hide();
            } else if (link.hasAttribute('data-open-asset-modal')) {
                event.preventDefault(); open(link);
            }
        });
    });
    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
        if (event.data?.type === 'asset-action-saved') {
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
