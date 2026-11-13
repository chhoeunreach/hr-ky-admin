(function () {
    'use strict';
    const init = () => {
        const form = document.getElementById('flashFilterForm');
        const tools = document.querySelector('[data-directory-table="flashTable"]');
        if (!form || !tools) return;
        const search = document.getElementById('flashSearch');
        const message = document.getElementById('flashDirectoryMessage');
        let pending, debounce;
        const refresh = async url => {
            pending?.abort();
            const controller = new AbortController();
            pending = controller;
            document.getElementById('flash-list-block').setAttribute('aria-busy', 'true');
            message.classList.add('d-none');
            try {
                const response = await fetch(url, {credentials: 'same-origin', signal: controller.signal, headers: {Accept: 'text/html'}});
                if (!response.ok || response.redirected) throw new Error('Failed');
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const replacement = doc.getElementById('flash-list-block');
                if (!replacement) throw new Error('Missing results');
                if (pending !== controller) return;
                document.getElementById('flash-list-block').replaceWith(replacement);
                const count = document.querySelector('[data-directory-count="flashTable"]');
                if (count) count.textContent = Number(replacement.dataset.directoryTotal).toLocaleString();
                const metrics = doc.querySelector('.fv-metrics');
                if (metrics) document.querySelector('.fv-metrics').replaceWith(metrics);
                window.history.replaceState(null, '', url);
                window.feather?.replace();
                document.dispatchEvent(new CustomEvent('directory:updated'));
            } catch (error) {
                if (error.name !== 'AbortError') {
                    message.textContent = message.dataset.failed;
                    message.classList.remove('d-none');
                }
            } finally {
                if (pending === controller) {
                    pending = null;
                    document.getElementById('flash-list-block')?.removeAttribute('aria-busy');
                }
            }
        };
        const submit = () => {
            clearTimeout(debounce);
            form.elements.namedItem('q').value = search.value;
            const url = new URL(form.action, location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            return refresh(url.toString());
        };
        form.addEventListener('submit', event => {event.preventDefault(); submit();});
        tools.querySelector('[name="per_page"]').addEventListener('change', submit);
        search.addEventListener('input', () => {clearTimeout(debounce); debounce = setTimeout(submit, 300);});
        search.addEventListener('keydown', event => {if (event.key === 'Enter') {event.preventDefault(); submit();}});
        document.addEventListener('click', event => {
            const link = event.target.closest('#flash-list-block .pagination a');
            if (!link) return;
            event.preventDefault();
            clearTimeout(debounce);
            refresh(link.href);
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
