(() => {
    const modal = document.getElementById('leaveRequestFormModal');
    if (!modal) return;

    const frame = modal.querySelector('[data-request-form-frame]');
    const title = modal.querySelector('.modal-title');
    const defaultTitle = title?.textContent;
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-open-leave-request-form], [data-open-request-form]');
        if (!trigger || !window.bootstrap?.Modal) return;

        event.preventDefault();
        const href = trigger.getAttribute('data-request-form-url') || trigger.getAttribute('href');
        if (!href) return;
        const url = new URL(href, window.location.href);
        url.searchParams.set('request_modal', '1');
        if (title) title.textContent = trigger.getAttribute('data-request-modal-title') || defaultTitle;
        frame.title = title?.textContent || defaultTitle;
        frame.src = url.href;
        bootstrap.Modal.getOrCreateInstance(modal).show(trigger);
    });
    modal.addEventListener('hidden.bs.modal', () => {
        frame.removeAttribute('src');
    });
    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow) return;
        if (event.data?.type === 'employee-request-form-close') {
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        } else if (event.data?.type === 'employee-request-form-saved' && typeof event.data.redirectUrl === 'string') {
            const destination = new URL(event.data.redirectUrl, window.location.href);
            if (destination.origin !== window.location.origin) return;
            bootstrap.Modal.getOrCreateInstance(modal).hide();
            window.location.assign(destination.href);
        }
    });
})();
