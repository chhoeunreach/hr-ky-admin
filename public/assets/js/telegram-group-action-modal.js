(() => {
    const modal = document.getElementById('telegramGroupActionModal');
    if (!modal || !window.bootstrap?.Modal) return;

    const frame = modal.querySelector('[data-telegram-group-action-frame]');
    const title = modal.querySelector('.modal-title');
    const indexUrl = new URL(modal.dataset.indexUrl, window.location.href);

    const modalInstance = () => window.bootstrap.Modal.getOrCreateInstance(modal);

    const refreshBlock = () => {
        // 1. Direct in-place block refresh function
        if (typeof window.refreshTelegramGroupList === 'function') {
            window.refreshTelegramGroupList();
            return true;
        }

        // 2. Toolbar refresh button
        const refreshBtn = document.querySelector('[data-directory-refresh]');
        if (refreshBtn) {
            refreshBtn.click();
            return true;
        }

        // 3. Custom event
        document.dispatchEvent(new CustomEvent('telegram-group:refresh'));
        return false;
    };

    const showSavedToast = (message) => {
        const text = message || 'Telegram group updated successfully.';
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: text
            });
        }
    };

    const open = trigger => {
        const href = trigger.getAttribute('href');
        if (!href) return false;
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) return false;

        url.searchParams.set('telegram_group_modal', '1');
        title.textContent = trigger.dataset.telegramGroupModalTitle || trigger.title || trigger.textContent.trim() || 'Edit Telegram Group';
        frame.title = title.textContent;
        frame.src = url.href;
        modalInstance().show(trigger);
        return true;
    };

    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-open-telegram-group-modal]');
        if (trigger && open(trigger)) {
            event.preventDefault();
        }
    });

    frame.addEventListener('load', () => {
        let child;
        try {
            child = frame.contentDocument;
        } catch (_) {
            return;
        }
        if (!child) return;

        try {
            const style = child.createElement('style');
            style.textContent = `
                html, body { background: #fff !important; }
                .main-wrapper { min-height: 0 !important; }
                .page-wrapper { margin-left: 0 !important; width: 100% !important; min-width: 0 !important; padding: 0 !important; }
                .page-content { padding: 18px 24px !important; margin: 0 !important; min-height: auto !important; }
                .card { border: none !important; box-shadow: none !important; background: transparent !important; }
                .card-body { padding: 0 !important; }
            `;
            child.head.appendChild(style);
        } catch (_) {}

        child.addEventListener('submit', event => {
            const form = event.target;
            const action = new URL(form.action, frame.src);
            if (action.origin !== window.location.origin) return;

            if (!form.querySelector('[name="telegram_group_modal"]')) {
                const field = child.createElement('input');
                field.type = 'hidden';
                field.name = 'telegram_group_modal';
                field.value = '1';
                form.appendChild(field);
            }
        }, true);

        child.addEventListener('click', event => {
            const link = event.target.closest('a[href]');
            if (!link || link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript:')) return;
            const url = new URL(link.href, frame.src);
            if (url.origin !== window.location.origin) return;

            if (url.pathname === indexUrl.pathname) {
                event.preventDefault();
                modalInstance().hide();
            } else if (link.hasAttribute('data-open-telegram-group-modal')) {
                event.preventDefault();
                open(link);
            }
        });
    });

    // Handle postMessage from saved modal with clean reload
    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin) return;
        if (event.data?.type === 'telegram-group-action-saved') {
            modalInstance().hide();
            showSavedToast(event.data?.message);
            setTimeout(() => {
                window.location.reload();
            }, 600);
        }
    });

    modal.addEventListener('hidden.bs.modal', () => {
        frame.removeAttribute('src');
    });
})();
