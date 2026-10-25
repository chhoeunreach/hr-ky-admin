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
            const choices = types[platform.value] || (required ? {} : Object.assign({}, ...Object.values(types)));
            select.replaceChildren(new Option(placeholder, ''));
            Object.entries(choices).forEach(([key, label]) => select.add(new Option(label, key)));
            select.value = Object.hasOwn(choices, selected) ? selected : '';
            select.required = required && Object.keys(choices).length > 0;
            select.disabled = required && !Object.keys(choices).length;
            const wrapper = select.closest('[data-account-type-wrapper]');
            if (wrapper) wrapper.hidden = select.disabled;
        };
        platform.addEventListener('change', () => update(false));
        update(true);
    });
    const managers = document.getElementById('manager_ids');
    if (managers && window.jQuery?.fn.select2) {
        window.jQuery(managers).select2({width: '100%', placeholder: managers.dataset.placeholder});
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
