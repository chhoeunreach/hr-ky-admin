(function () {
    'use strict';

    const initialize = () => {
        const modal = document.getElementById('postSelectionModal');
        if (!modal) return;

        const title = document.getElementById('postSelectionTitle');
        const search = document.getElementById('postSelectionSearch');
        const selectAll = document.getElementById('postSelectionAll');
        const count = document.getElementById('postSelectionCount');
        const list = document.getElementById('postSelectionOptions');
        const empty = document.getElementById('postSelectionEmpty');
        const apply = document.getElementById('postSelectionApply');
        let activeSelect = null;
        let options = [];
        let draft = new Set();

        const updateSelection = () => {
            selectAll.checked = options.length > 0 && draft.size === options.length;
            selectAll.indeterminate = draft.size > 0 && draft.size < options.length;
            selectAll.disabled = options.length === 0;
            count.textContent = `${draft.size} / ${options.length}`;
            apply.disabled = draft.size === 0;
        };

        const renderOptions = () => {
            const query = search.value.trim().toLocaleLowerCase();
            list.replaceChildren();
            const matches = options.filter(option => option.text.toLocaleLowerCase().includes(query));
            matches.forEach(option => {
                const row = document.createElement('label');
                row.className = 'd-flex align-items-center gap-2 py-2 px-1';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'form-check-input m-0 flex-shrink-0';
                checkbox.checked = draft.has(option.value);
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) draft.add(option.value);
                    else draft.delete(option.value);
                    updateSelection();
                });
                const text = document.createElement('span');
                text.textContent = option.text;
                row.append(checkbox, text);
                list.append(row);
            });
            empty.hidden = matches.length > 0;
            updateSelection();
        };

        document.querySelectorAll('select[data-selection-popup="1"]').forEach(select => {
            window.jQuery(select).on('select2:opening.postSelection', event => {
                event.preventDefault();
                if (select.disabled) return;
                activeSelect = select;
                options = Array.from(select.options)
                    .filter(option => option.value !== '' && !option.disabled)
                    .map(option => ({value: option.value, text: option.text, selected: option.selected}));
                draft = new Set(options.filter(option => option.selected).map(option => option.value));
                const label = document.querySelector(`label[for="${select.id}"]`);
                title.textContent = label ? label.textContent.replace(/\s*\*\s*$/, '').trim() : select.dataset.placeholder;
                search.value = '';
                renderOptions();
                window.bootstrap.Modal.getOrCreateInstance(modal).show();
            });
        });

        search.addEventListener('input', renderOptions);
        selectAll.addEventListener('change', () => {
            draft = new Set(selectAll.checked ? options.map(option => option.value) : []);
            renderOptions();
        });
        apply.addEventListener('click', () => {
            if (!activeSelect || !draft.size) return;
            window.jQuery(activeSelect).val([...draft]).trigger('change');
            window.bootstrap.Modal.getOrCreateInstance(modal).hide();
        });
        modal.addEventListener('shown.bs.modal', () => search.focus());
        modal.addEventListener('hidden.bs.modal', () => {
            const control = activeSelect && window.jQuery(activeSelect).data('select2');
            if (control) control.$selection.trigger('focus');
            activeSelect = null;
            draft.clear();
        });
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
