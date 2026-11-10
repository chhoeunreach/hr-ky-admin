<script>
(() => {
    function filterSeries(form) {
        const select = form.elements.namedItem('repair_device_series_id');
        if (!select) return;
        const brand = form.elements.namedItem('repair_brand_id').value;
        const type = form.elements.namedItem('repair_device_type_id').value;
        Array.from(select.options).forEach(option => {
            const matches = !option.value || (option.dataset.brandId === brand && option.dataset.typeId === type);
            option.hidden = !matches;
            option.disabled = !matches;
        });
        if (select.selectedOptions[0]?.disabled) select.value = '';
    }

    document.querySelectorAll('.modal form').forEach(form => {
        const modal = form.closest('.modal');
        form.addEventListener('submit', () => {
            const values = {_repair_form: modal.id, _repair_record_id: form.action.match(/\/(\d+)$/)?.[1] || ''};
            Object.entries(values).forEach(([name, value]) => {
                let input = form.elements.namedItem(name);
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    form.append(input);
                }
                input.value = value;
            });
        });
        form.addEventListener('change', event => {
            if (['repair_brand_id', 'repair_device_type_id'].includes(event.target.name)) filterSeries(form);
        });
        modal.addEventListener('show.bs.modal', () => filterSeries(form));
    });

    const previous = {{ Illuminate\Support\Js::from(session()->getOldInput()) }};
    const modal = document.getElementById(previous._repair_form || '');
    if (!modal?.classList.contains('modal')) return;
    const form = modal.querySelector('form');
    const editEntities = {
        editPriceModal: 'prices', editBrandModal: 'brands', editCategoryModal: 'categories',
        editDeviceModal: 'devices', editServiceModal: 'services',
        editCatalogtypesModal: 'types', editCatalogseriesModal: 'series'
    };
    const entity = editEntities[modal.id];
    if (entity) {
        if (!/^\d+$/.test(previous._repair_record_id || '')) return;
        form.action = {{ Illuminate\Support\Js::from(url('admin/repair-price')) }} + '/' + entity + '/' + previous._repair_record_id;
    }
    Array.from(form.elements).forEach(field => {
        if (!field.name || ['_token', '_method'].includes(field.name)) return;
        if (field.type === 'checkbox') field.checked = previous[field.name] === '1';
        else if (Object.hasOwn(previous, field.name)) field.value = previous[field.name] ?? '';
    });
    const errors = document.querySelector('.app-flash-message.alert-danger');
    if (errors) form.querySelector('.modal-body').prepend(errors.cloneNode(true));
    bootstrap.Modal.getOrCreateInstance(modal).show();
})();
</script>
