(function () {
    'use strict';

    const messages = window.AppDialogMessages;
    if (!messages || !window.Swal) {
        window.AppDialog = {
            alert: (message) => window.alert(String(message ?? '')),
            confirm: (message) => Promise.resolve(window.confirm(message)),
        };
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (form.matches('form[data-confirm-message]') && !window.confirm(form.dataset.confirmMessage)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        return;
    }

    const dialog = window.Swal.mixin({
        width: 440,
        padding: 24,
        buttonsStyling: false,
        confirmButtonText: messages.ok,
        cancelButtonText: messages.cancel,
        denyButtonText: messages.cancel,
        customClass: {
            popup: 'app-dialog',
            confirmButton: 'app-dialog-button app-dialog-primary',
            cancelButton: 'app-dialog-button app-dialog-secondary',
            denyButton: 'app-dialog-button app-dialog-secondary',
        },
    });
    const fire = dialog.fire.bind(dialog);
    const titles = {
        success: messages.successTitle,
        error: messages.errorTitle,
        warning: messages.warningTitle,
        info: messages.infoTitle,
        question: messages.confirmTitle,
    };

    // Keep the existing Promise results and callbacks used by each page.
    dialog.fire = function (first, text, icon) {
        const options = first && typeof first === 'object' && !first.nodeType
            ? { ...first }
            : { title: first, text: text, icon: icon };
        const confirmation = options.showDenyButton || options.showCancelButton;
        const originalTitle = typeof options.title === 'string' ? options.title.trim() : '';
        const destructive = confirmation && /delete|remove|លុប/i.test(originalTitle);

        if (confirmation) {
            options.icon = options.icon || (destructive ? 'warning' : 'question');
            options.allowOutsideClick = options.allowOutsideClick ?? false;
            options.allowEscapeKey = options.allowEscapeKey ?? false;
            if (!options.text && !options.html && originalTitle && !options.input) {
                options.text = originalTitle;
                options.title = destructive ? messages.deleteTitle : messages.confirmTitle;
            }
            if (!options.confirmButtonText || [messages.yes, 'Yes', 'Yes!'].includes(options.confirmButtonText)) {
                options.confirmButtonText = destructive ? messages.delete : messages.confirm;
            }
            if (!options.denyButtonText || [messages.no, 'No', 'No!'].includes(options.denyButtonText)) {
                options.denyButtonText = messages.cancel;
            }
            options.cancelButtonText = options.cancelButtonText || messages.cancel;
        } else {
            const genericTitles = ['Success', 'Error', 'Warning', 'Info', 'Information', 'Validation Error', 'success', 'error', messages.legacySuccess, messages.legacyError];
            if (!originalTitle || genericTitles.includes(originalTitle)) {
                options.title = titles[options.icon] || messages.infoTitle;
            }
            if (options.text === 'Something went wrong.' || options.text === 'Something went wrong') {
                options.text = messages.requestFailed;
            }
        }
        if (options.padding === '10px 50px 10px 50px') options.padding = 24;
        options.customClass = {
            confirmButton: 'app-dialog-button app-dialog-primary',
            cancelButton: 'app-dialog-button app-dialog-secondary',
            denyButton: 'app-dialog-button app-dialog-secondary',
            ...options.customClass,
            popup: ['app-dialog', destructive ? 'app-dialog-destructive' : '', options.customClass?.popup || ''].filter(Boolean).join(' '),
        };
        return fire(options);
    };
    window.Swal = dialog;

    window.AppDialog = {
        alert: function (message, icon = 'info') {
            return dialog.fire({ title: titles[icon] || messages.infoTitle, text: String(message ?? ''), icon: icon });
        },
        confirm: function (message, options = {}) {
            return dialog.fire({
                title: messages.confirmTitle,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: messages.confirm,
                ...options,
            }).then((result) => result.isConfirmed);
        },
    };

    const confirmedForms = new WeakSet();
    const pendingForms = new WeakSet();
    document.addEventListener('submit', async function (event) {
        const form = event.target;
        if (!form.matches('form[data-confirm-message]')) return;
        if (confirmedForms.has(form)) {
            confirmedForms.delete(form);
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        if (pendingForms.has(form)) return;
        pendingForms.add(form);
        const submitter = event.submitter;
        try {
            const confirmed = await window.AppDialog.confirm(form.dataset.confirmMessage, {
                title: form.dataset.confirmTitle || messages.deleteTitle,
                icon: 'warning',
                confirmButtonText: form.dataset.confirmButton || messages.delete,
                customClass: { popup: form.dataset.confirmDestructive === 'false' ? '' : 'app-dialog-destructive' },
            });
            if (confirmed && form.isConnected) {
                confirmedForms.add(form);
                if (submitter && submitter.form === form) form.requestSubmit(submitter);
                else form.requestSubmit();
                confirmedForms.delete(form);
            }
        } finally {
            pendingForms.delete(form);
        }
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.app-flash-message.alert-success:not(.alert-important)').forEach((element) => {
            let timer;
            const scheduleDismiss = () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    if (element.matches(':hover') || element.contains(document.activeElement)) return;
                    window.bootstrap?.Alert.getOrCreateInstance(element).close();
                }, 8000);
            };
            element.addEventListener('mouseenter', () => clearTimeout(timer));
            element.addEventListener('focusin', () => clearTimeout(timer));
            element.addEventListener('mouseleave', scheduleDismiss);
            element.addEventListener('focusout', scheduleDismiss);
            scheduleDismiss();
        });
    });
})();
