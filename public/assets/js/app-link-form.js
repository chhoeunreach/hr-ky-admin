(() => {
    const config = document.getElementById('appLinkAccountTypes');
    if (!config) return;
    const types = JSON.parse(config.textContent);
    document.querySelectorAll('[data-account-type]').forEach(select => {
        const platform = document.getElementById(select.dataset.platformId);
        if (!platform) return;
        const placeholder = select.options[0].textContent;
        const required = select.dataset.accountRequired === '1';
        const update = initial => {
            const selected = initial ? select.dataset.selected : '';
            const choices = types[platform.value] || (required ? types.other || {} : Object.assign({}, ...Object.values(types)));
            select.replaceChildren(new Option(placeholder, ''));
            Object.entries(choices).forEach(([key, label]) => select.add(new Option(label, key)));
            select.value = Object.hasOwn(choices, selected) ? selected : '';
            select.required = required && Object.keys(choices).length > 0;
            select.disabled = required && !Object.keys(choices).length;
            const wrapper = select.closest('[data-account-type-wrapper]');
            if (wrapper) wrapper.hidden = select.disabled;
        };
        platform.addEventListener('change', () => {
            select.dataset.selected = select.value;
            update(true);
        });
        update(true);
    });
    document.querySelector('[data-close-link-form]')?.addEventListener('click', () => {
        window.parent.postMessage({type: 'employee-request-form-close'}, window.location.origin);
    });
    const managers = document.getElementById('manager_ids');
    const initializeManagers = () => {
        if (!managers || !window.jQuery?.fn.select2) return;
        window.jQuery(managers).select2({
            width: '100%',
            placeholder: managers.dataset.placeholder,
            closeOnSelect: false,
            allowClear: true,
            templateSelection: person => person.element?.dataset.displayName || person.text,
        });
    };
    // The layout loads Select2 after page scripts, before DOMContentLoaded.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeManagers, {once: true});
    } else {
        initializeManagers();
    }
    const password = document.getElementById('password');
    const toggle = document.querySelector('[data-toggle-link-password]');
    const labels = document.currentScript?.dataset || {};
    toggle?.addEventListener('click', () => {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.textContent = visible ? labels.hide : labels.show;
        toggle.setAttribute('aria-pressed', String(visible));
    });
    const clear = document.querySelector('[name="clear_password"]');
    if (clear && password) {
        const update = () => { password.disabled = clear.checked; if (toggle) toggle.disabled = clear.checked; };
        clear.addEventListener('change', update); update();
    }
})();
