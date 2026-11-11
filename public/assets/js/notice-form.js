(function () {
    'use strict';
    const init = () => {
        const form = document.getElementById('notification');
        if (!form || window.parent === window) return;
        let saving = false;
        document.addEventListener('click', event => {
            if (!event.target.closest('[data-notice-cancel]')) return;
            event.preventDefault();
            if (!saving) window.parent.postMessage({type: 'notice:cancel'}, location.origin);
        });
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (saving) return;
            saving = true;
            const error = document.getElementById('noticeFormError');
            error.classList.add('d-none');
            const buttons = [...form.querySelectorAll('[type=submit]')];
            buttons.forEach(button => {button.disabled = true;});
            window.parent.postMessage({type: 'notice:saving', saving: true}, location.origin);
            try {
                const editor = window.tinymce?.get('noticeDescription');
                if (editor) {await editor.uploadImages(); editor.save();}
                const response = await fetch(form.action, {
                    method: 'POST', credentials: 'same-origin', body: new FormData(form),
                    headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await response.json();
                if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join('\n') || data.message);
                window.parent.postMessage({type: 'notice:saved', message: data.message}, location.origin);
            } catch (failure) {
                error.textContent = failure.message;
                error.classList.remove('d-none');
                error.scrollIntoView({block: 'nearest'});
            } finally {
                saving = false;
                buttons.forEach(button => {button.disabled = false;});
                window.parent.postMessage({type: 'notice:saving', saving: false}, location.origin);
            }
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
