(() => {
    if (!window.bootstrap?.Dropdown) return;
    document.querySelectorAll('[data-asset-actions]').forEach(button => {
        const parent = button.parentElement;
        const menu = parent.querySelector('.asset-actions-menu');
        if (!menu) return;
        const dropdown = new bootstrap.Dropdown(button, {
            boundary: document.body,
            offset: [0, 6],
            popperConfig: defaults => ({...defaults, strategy: 'fixed'}),
        });
        // Escape the table's scroll container and sticky-cell stacking context.
        button.addEventListener('show.bs.dropdown', () => document.body.appendChild(menu));
        button.addEventListener('hidden.bs.dropdown', () => parent.appendChild(menu));
        // Bootstrap normally finds the toggle beside the menu. Keep keyboard
        // navigation working while the open menu is attached to the body.
        window.addEventListener('keydown', event => {
            if (menu.parentElement !== document.body || !menu.contains(event.target)) return;
            if (!['Escape', 'ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault(); event.stopPropagation();
            if (event.key === 'Escape') {
                dropdown.hide(); button.focus(); return;
            }
            const items = Array.from(menu.querySelectorAll('.dropdown-item:not(.disabled):not(:disabled)'));
            if (!items.length) return;
            const current = items.indexOf(document.activeElement);
            let index;
            if (event.key === 'Home') index = 0;
            else if (event.key === 'End') index = items.length - 1;
            else if (current < 0) index = event.key === 'ArrowUp' ? items.length - 1 : 0;
            else index = (current + (event.key === 'ArrowUp' ? -1 : 1) + items.length) % items.length;
            items[index].focus();
        }, {capture: true});
        const close = () => dropdown.hide();
        button.closest('.assets-table-scroll')?.addEventListener('scroll', close, {passive: true});
        window.addEventListener('resize', close, {passive: true});
    });
})();
