(() => {
    const modal = document.getElementById('addPriceModal');
    if (!modal) return;
    const form = modal.querySelector('form');
    const picker = form.querySelector('.repair-price-model-picker');
    const servicePicker = form.querySelector('.repair-price-service-picker');
    if (!picker || !servicePicker) return;
    const options = Array.from(picker.querySelectorAll('.repair-price-model-option'));
    const inputs = options.map(option => option.querySelector('input'));
    const serviceOptions = Array.from(servicePicker.querySelectorAll('.repair-price-service-option'));
    const serviceInputs = serviceOptions.map(option => option.querySelector('input'));
    const search = picker.querySelector('.repair-model-search');
    const serviceSearch = servicePicker.querySelector('.repair-service-search');
    const categoryFilter = servicePicker.querySelector('.repair-service-category-filter');
    const error = picker.querySelector('.repair-model-error');
    const serviceError = servicePicker.querySelector('.repair-service-error');
    const count = picker.querySelector('.repair-model-count');
    const serviceCount = servicePicker.querySelector('.repair-service-count');
    const source = form.elements.namedItem('source_price_id');
    const sourceDevice = form.elements.namedItem('copy_source_device_id');
    const body = form.querySelector('.repair-pair-price-body');
    const template = form.querySelector('.repair-pair-price-template');
    const emptyPrices = form.querySelector('.repair-model-prices-empty');
    const copiedCosts = form.querySelector('.repair-copy-costs-message');
    const rows = new Map();
    let priceDefaults = {};
    let restoredPrices = {};

    function updateSelection() {
        const models = inputs.filter(input => input.checked && !input.disabled);
        const services = serviceInputs.filter(input => input.checked);
        count.textContent = models.length + ' / ' + inputs.filter(input => !input.disabled).length;
        serviceCount.textContent = services.length + ' / ' + serviceInputs.length;
        if (models.length) error.hidden = true;
        if (services.length) serviceError.hidden = true;
        emptyPrices.hidden = models.length > 0 && services.length > 0;
        rows.forEach(row => {
            row.hidden = true;
            row.querySelectorAll('[data-price-field]').forEach(input => {input.disabled = true;});
        });
        models.forEach(model => services.forEach(service => {
            const key = model.value + ':' + service.value;
            let row = rows.get(key);
            const privateCosts = Boolean(source.value) && picker.dataset.canViewCost !== '1'
                && String(priceDefaults.repair_service_id) === service.value;
            if (!row) {
                row = template.content.firstElementChild.cloneNode(true);
                row.dataset.modelId = model.value;
                row.dataset.serviceId = service.value;
                const modelName = options[inputs.indexOf(model)].querySelector('.form-check-label').textContent.trim();
                const serviceName = serviceOptions[serviceInputs.indexOf(service)].querySelector('.form-check-label').textContent.trim();
                row.querySelector('[data-pair-model]').textContent = modelName;
                row.querySelector('[data-pair-service]').textContent = serviceName;
                row.querySelector('[data-pair-category]').textContent = serviceOptions[serviceInputs.indexOf(service)].dataset.categoryName || ''; 
                row.querySelectorAll('[data-price-field]').forEach(input => {
                    const field = input.dataset.priceField;
                    input.name = 'pair_prices[' + model.value + '][' + service.value + '][' + field + ']';
                    input.setAttribute('aria-label', modelName + ' — ' + serviceName + ' — ' + input.dataset.fieldLabel);
                    const defaults = String(priceDefaults.repair_service_id) === service.value ? priceDefaults : {};
                    input.value = String(restoredPrices[model.value]?.[service.value]?.[field] ?? defaults[field] ?? '');
                });
                body.append(row);
                rows.set(key, row);
            }
            row.hidden = false;
            row.querySelectorAll('[data-price-field]').forEach(input => {
                const privateCost = privateCosts && input.dataset.priceField !== 'selling_price';
                input.disabled = privateCost;
                input.hidden = privateCost;
                const message = input.parentElement.querySelector('[data-private-cost]');
                if (message) message.hidden = !privateCost;
            });
        }));
        copiedCosts.hidden = !(source.value && picker.dataset.canViewCost !== '1');
    }

    function filterModels() {
        const query = search.value.toLocaleLowerCase().trim();
        options.forEach(option => {option.hidden = !option.textContent.toLocaleLowerCase().includes(query);});
    }
    function filterServices() {
        const query = serviceSearch.value.toLocaleLowerCase().trim();
        serviceOptions.forEach(option => {
            option.hidden = Boolean(categoryFilter.value && option.dataset.categoryId !== categoryFilter.value)
                || !option.textContent.toLocaleLowerCase().includes(query);
        });
    }
    function configureCopy() {
        inputs.forEach(input => {
            input.disabled = Boolean(source.value) && input.value === sourceDevice.value;
            if (input.disabled) input.checked = false;
        });
    }
    function wirePicker(container, optionList, inputList, allSelector, clearSelector) {
        container.addEventListener('change', updateSelection);
        container.querySelector(allSelector).addEventListener('click', () => {
            optionList.forEach((option, index) => {
                if (!option.hidden && !inputList[index].disabled) inputList[index].checked = true;
            });
            updateSelection();
        });
        container.querySelector(clearSelector).addEventListener('click', () => {
            inputList.forEach(input => {input.checked = false;});
            updateSelection();
        });
    }
    wirePicker(picker, options, inputs, '.repair-models-all', '.repair-models-clear');
    wirePicker(servicePicker, serviceOptions, serviceInputs, '.repair-services-all', '.repair-services-clear');
    search.addEventListener('input', filterModels);
    serviceSearch.addEventListener('input', filterServices);
    categoryFilter.addEventListener('change', filterServices);
    form.addEventListener('submit', event => {
        const hasModels = inputs.some(input => input.checked && !input.disabled);
        const hasServices = serviceInputs.some(input => input.checked);
        if (hasModels && hasServices) return;
        event.preventDefault();
        error.hidden = hasModels;
        serviceError.hidden = hasServices;
        (hasModels ? serviceSearch : search).focus();
    });
    form.addEventListener('repair:restore-prices', event => {
        const previous = event.detail;
        restoredPrices = previous.pair_prices || {};
        source.value = previous.source_price_id || '';
        sourceDevice.value = previous.copy_source_device_id || '';
        inputs.forEach(input => {input.checked = (previous.repair_device_ids || []).map(String).includes(input.value);});
        serviceInputs.forEach(input => {input.checked = (previous.repair_service_ids || []).map(String).includes(input.value);});
        // Reuse the source service metadata when recovering a copy form after validation.
        const triggers = Array.from(document.querySelectorAll('[data-price-template]'));
        const original = triggers.map(trigger => JSON.parse(trigger.dataset.priceTemplate)).find(value => String(value.source_price_id) === String(source.value));
        priceDefaults = original || {};
        configureCopy();
        updateSelection();
    });
    modal.addEventListener('show.bs.modal', event => {
        if (form.dataset.restoring !== '1') {
            form.reset();
            rows.clear();
            body.replaceChildren();
            priceDefaults = {};
            restoredPrices = {};
            const data = event.relatedTarget?.dataset.priceTemplate;
            if (data) {
                priceDefaults = JSON.parse(data);
                Object.entries(priceDefaults).forEach(([name, value]) => {
                    const field = form.elements.namedItem(name);
                    if (field) field.value = String(value ?? '');
                });
                sourceDevice.value = String(priceDefaults.source_device_id);
                serviceInputs.forEach(input => {input.checked = input.value === String(priceDefaults.repair_service_id);});
                categoryFilter.value = String(priceDefaults.repair_category_id ?? '');
            } else {
                source.value = '';
                sourceDevice.value = '';
                inputs.forEach(input => {input.checked = input.value === picker.dataset.deviceContext;});
                categoryFilter.value = servicePicker.dataset.categoryContext;
            }
        }
        search.value = '';
        serviceSearch.value = '';
        filterModels();
        filterServices();
        configureCopy();
        updateSelection();
        delete form.dataset.restoring;
    });
})();
