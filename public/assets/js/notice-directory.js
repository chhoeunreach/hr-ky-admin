(function () {
    'use strict';
    const init = () => {
        const form = document.getElementById('noticeFilterForm');
        const modalElement = document.getElementById('noticeFormModal');
        if (!form || !modalElement) return;
        const frame = document.getElementById('noticeFormFrame');
        const loading = document.getElementById('noticeFormLoading');
        const title = document.getElementById('noticeFormModalTitle');
        const message = document.getElementById('noticeDirectoryMessage');
        const tools = document.querySelector('[data-directory-table="noticeTable"]');
        const search = document.getElementById('noticeSearch');
        let saving = false, pending = null, debounce;
        const report = (text, failed = false) => {
            message.textContent = text;
            message.className = 'alert ' + (failed ? 'alert-danger' : 'alert-success');
        };
        const refresh = async (url = window.location.href) => {
            pending?.abort();
            const controller = new AbortController(); pending = controller;
            const block = document.getElementById('notice-list-block');
            block.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(url, {credentials: 'same-origin', signal: controller.signal, headers: {Accept: 'text/html'}});
                if (!response.ok || response.redirected) throw new Error('Failed');
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const replacement = doc.getElementById('notice-list-block');
                if (!replacement) throw new Error('Failed');
                if (pending !== controller) return false;
                block.replaceWith(replacement);
                const count = document.querySelector('[data-directory-count="noticeTable"]');
                if (count) count.textContent = Number(replacement.dataset.directoryTotal).toLocaleString();
                window.history.replaceState(null, '', url);
                window.feather?.replace();
                document.dispatchEvent(new CustomEvent('directory:updated'));
                return true;
            } catch (error) {
                if (error.name !== 'AbortError') report(modalElement.dataset.failed, true);
                return false;
            } finally {if (pending === controller) {pending = null; document.getElementById('notice-list-block')?.removeAttribute('aria-busy');}}
        };
        const submit = () => {
            form.elements.namedItem('search').value = search.value;
            const url = new URL(form.action, location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            return refresh(url.toString());
        };
        form.addEventListener('submit', event => {event.preventDefault(); submit();});
        tools.querySelector('[name="per_page"]').addEventListener('change', submit);
        search.addEventListener('input', () => {clearTimeout(debounce); debounce = setTimeout(submit, 300);});
        search.addEventListener('keydown', event => {if (event.key === 'Enter') {event.preventDefault(); clearTimeout(debounce); submit();}});
        document.addEventListener('click', event => {
            const trigger = event.target.closest('[data-open-notice-form]');
            if (trigger) {
                event.preventDefault();
                const url = new URL(trigger.href, location.href);
                if (url.origin !== location.origin) return;
                url.searchParams.set('notice_modal', '1');
                title.textContent = url.pathname.endsWith('/create') ? modalElement.dataset.addTitle : modalElement.dataset.editTitle;
                frame.hidden = true; loading.hidden = false; frame.src = url.toString();
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
                return;
            }
            const page = event.target.closest('#notice-list-block .pagination a');
            if (page) {event.preventDefault(); refresh(page.href);}
        });
        frame.addEventListener('load', () => {if (frame.getAttribute('src') !== 'about:blank') {frame.hidden = false; loading.hidden = true;}});
        modalElement.addEventListener('hide.bs.modal', event => {if (saving) event.preventDefault();});
        modalElement.addEventListener('hidden.bs.modal', () => {frame.src = 'about:blank';});
        window.addEventListener('message', async event => {
            if (event.origin !== location.origin || event.source !== frame.contentWindow) return;
            if (event.data?.type === 'notice:saving') {saving = !!event.data.saving; return;}
            if (event.data?.type !== 'notice:saved') return;
            saving = false;
            bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            report(event.data.message);
            if (!await refresh()) report(modalElement.dataset.savedRefreshFailed, true);
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
