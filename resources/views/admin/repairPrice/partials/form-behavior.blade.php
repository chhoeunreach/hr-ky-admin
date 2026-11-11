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

    function filterPriceServices(form) {
        const category = form.elements.namedItem('repair_category_id');
        const service = form.elements.namedItem('repair_service_id');
        if (!category || !service) return;
        Array.from(service.options).forEach(option => {
            const matches = !option.value || option.dataset.categoryId === category.value;
            option.hidden = !matches;
            option.disabled = !matches;
        });
        if (!service.value || service.selectedOptions[0]?.disabled) service.value = '';
    }

    const devicePriceServices = {{ Illuminate\Support\Js::from($services->map(fn ($service) => ['id' => $service->id, 'category_id' => $service->repair_category_id, 'name' => app()->getLocale() === 'km' && $service->name_kh ? $service->name_kh : $service->name])) }};
    const canCreateDevicePrice = {{ Illuminate\Support\Js::from(\Illuminate\Support\Facades\Gate::any(['repair_price.create', 'repair_price.manage_prices'])) }};
    const canUpdateDevicePrice = {{ Illuminate\Support\Js::from(\Illuminate\Support\Facades\Gate::any(['repair_price.update', 'repair_price.manage_prices'])) }};
    const priceLabel = {{ Illuminate\Support\Js::from(__('index.selling_price').' ($)') }};
    const noServicesLabel = {{ Illuminate\Support\Js::from(__('repair.category_needs_service')) }};
    const newPriceLabel = {{ Illuminate\Support\Js::from(__('repair.new_category_price')) }};

    function toggleCategoryPrices(picker) {
        picker.querySelectorAll('.repair-category-option').forEach(option => {
            const selected = option.querySelector('[name="category_ids[]"]').checked;
            const prices = option.querySelector('.repair-category-prices');
            if (!prices) return;
            prices.hidden = !selected;
            prices.querySelectorAll('input').forEach(input => {
                input.disabled = !selected || input.readOnly;
            });
        });
    }

    function renderDevicePrices(form) {
        const picker = form.querySelector('.repair-category-picker');
        if (!picker) return;
        const existingPrices = JSON.parse(form.dataset.prices || '[]');
        picker.querySelectorAll('.repair-category-prices').forEach(container => {
            container.replaceChildren();
            const services = devicePriceServices.filter(service => String(service.category_id) === container.dataset.categoryId);
            if (!services.length) {
                const message = document.createElement('p');
                message.className = 'form-text';
                message.textContent = noServicesLabel;
                container.append(message);
            }
            services.forEach(service => {
                const existing = existingPrices.filter(price => Number(price.repair_service_id) === Number(service.id));
                const addRow = (name, value, description, editable) => {
                    const label = document.createElement('label');
                    label.className = 'd-block small mb-2';
                    const title = document.createElement('span');
                    title.textContent = service.name + (description ? ' — ' + description : '') + ' · ' + priceLabel;
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.className = 'form-control form-control-sm mt-1';
                    input.name = name;
                    input.min = '0';
                    input.step = '0.01';
                    input.placeholder = '0.00';
                    input.value = value;
                    input.readOnly = !editable;
                    input.required = name.startsWith('existing_prices');
                    label.append(title, input);
                    container.append(label);
                };
                existing.forEach(price => addRow('existing_prices[' + price.id + ']', price.selling_price, price.part_type || '', canUpdateDevicePrice));
                if (canCreateDevicePrice && !existing.some(price => price.part_type === null)) {
                    addRow('new_prices[' + service.id + ']', '', newPriceLabel, true);
                }
            });
        });
        toggleCategoryPrices(picker);
    }

    document.querySelectorAll('.repair-category-picker').forEach(picker => {
        picker.addEventListener('change', () => toggleCategoryPrices(picker));
    });

    const priceContext = {{ Illuminate\Support\Js::from(['device_id' => $filterParameters['device_id'], 'category_id' => $filterParameters['category_id']]) }};
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
            if (event.target.name === 'repair_category_id') filterPriceServices(form);
        });
        modal.addEventListener('show.bs.modal', () => {
            filterSeries(form);
            if (form.dataset.restoring !== '1') renderDevicePrices(form);
            const category = form.elements.namedItem('repair_category_id');
            const service = form.elements.namedItem('repair_service_id');
            if (!category || !service) return;
            if (form.dataset.restoring === '1') {
                delete form.dataset.restoring;
            } else if (modal.id === 'editPriceModal') {
                category.value = service.selectedOptions[0]?.dataset.categoryId || category.value;
            } else if (modal.id === 'addPriceModal') {
                if (priceContext.device_id && !form.elements.namedItem('source_price_id')?.value) {
                    form.querySelectorAll('[name="repair_device_ids[]"]').forEach(input => {
                        input.checked = input.value === String(priceContext.device_id);
                    });
                }
                if (priceContext.category_id && !form.elements.namedItem('source_price_id')?.value) category.value = priceContext.category_id;
            }
            filterPriceServices(form);
        });
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
    if (modal.id === 'editDeviceModal') {
        const trigger = document.querySelector('.edit-device-btn[data-id="' + previous._repair_record_id + '"]');
        if (trigger) form.dataset.prices = trigger.dataset.prices;
    }
    renderDevicePrices(form);
    form.dispatchEvent(new CustomEvent('repair:restore-prices', {detail: previous}));
    Array.from(form.elements).forEach(field => {
        if (!field.name || ['_token', '_method'].includes(field.name)) return;
        if (field.type === 'checkbox') {
            if (field.name.endsWith('[]')) field.checked = (previous[field.name.slice(0, -2)] || []).map(String).includes(field.value);
            else field.checked = previous[field.name] === '1';
        }
        else {
            const nested = field.name.match(/^(new_prices|existing_prices)\[(\d+)\]$/);
            const modelPrice = field.name.match(/^model_prices\[(\d+)\]\[(part_cost|service_fee|selling_price)\]$/);
            if (modelPrice && Object.hasOwn(previous.model_prices?.[modelPrice[1]] || {}, modelPrice[2])) field.value = previous.model_prices[modelPrice[1]][modelPrice[2]] ?? '';
            else if (nested && Object.hasOwn(previous[nested[1]] || {}, nested[2])) field.value = previous[nested[1]][nested[2]] ?? '';
            else if (Object.hasOwn(previous, field.name)) field.value = previous[field.name] ?? '';
        }
    });
    const categoryPicker = form.querySelector('.repair-category-picker');
    if (categoryPicker) toggleCategoryPrices(categoryPicker);
    const errors = document.querySelector('.app-flash-message.alert-danger');
    if (errors) form.querySelector('.modal-body').prepend(errors.cloneNode(true));
    form.dataset.restoring = '1';
    bootstrap.Modal.getOrCreateInstance(modal).show();
})();
</script>
