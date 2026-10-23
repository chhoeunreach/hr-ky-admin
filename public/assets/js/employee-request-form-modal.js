(() => {
    const modal = document.getElementById('leaveRequestFormModal');
    if (!modal) return;

    const frame = modal.querySelector('[data-request-form-frame]');
    const title = modal.querySelector('.modal-title');
    const defaultTitle = title?.textContent;
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-open-leave-request-form]');
        if (!trigger || !window.bootstrap?.Modal) return;

        event.preventDefault();
        const href = trigger.getAttribute('data-request-form-url') || trigger.getAttribute('href');
        if (!href) return;
        const url = new URL(href, window.location.href);
        url.searchParams.set('request_modal', '1');
        if (title) title.textContent = trigger.getAttribute('data-request-modal-title') || defaultTitle;
        frame.src = url.href;
        bootstrap.Modal.getOrCreateInstance(modal).show(trigger);
    });
    modal.addEventListener('hidden.bs.modal', () => {
        frame.removeAttribute('src');
    });
})();
