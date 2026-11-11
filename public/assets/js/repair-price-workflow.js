(() => {
    const modal = document.getElementById('addPriceModal');
    const form = modal?.querySelector('[data-guided-price-form]');
    if (!form) return;
    const panels = Array.from(form.querySelectorAll('[data-price-step]'));
    const steps = Array.from(form.querySelectorAll('[data-go-step]'));
    const modes = Array.from(form.querySelectorAll('[data-entry-mode]'));
    const modeField = form.elements.namedItem('price_entry_mode');
    const next = form.querySelector('[data-price-next]');
    const back = form.querySelector('[data-price-back]');
    const save = form.querySelector('[data-price-save]');
    const context = form.querySelector('[data-workflow-context]');
    const review = form.querySelector('[data-review-rows]');
    let currentStep = 1;
    let recovering = false;
    let submitting = false;

    function selected(name) {
        return Array.from(form.querySelectorAll('[name="' + name + '[]"]')).filter(input => input.checked && !input.disabled);
    }
    function selectionSummary() {
        const models = selected('repair_device_ids').length;
        const services = selected('repair_service_ids').length;
        context.textContent = context.dataset.label.replace(':models', models).replace(':services', services).replace(':prices', models * services);
    }
    function setMode(mode) {
        modeField.value = mode === 'bulk' ? 'bulk' : 'single';
        form.classList.toggle('repair-entry-single', modeField.value === 'single');
        modes.forEach(button => {
            const active = button.dataset.entryMode === modeField.value;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        form.dispatchEvent(new CustomEvent('repair:selection-mode', {detail: modeField.value}));
        form.querySelectorAll('[name="repair_device_ids[]"], [name="repair_service_ids[]"]').forEach(input => {
            input.type = modeField.value === 'single' ? 'radio' : 'checkbox';
        });
        selectionSummary();
    }
    function validateStep(step) {
        const panel = panels[step - 1];
        if (step === 1 || step === 2) {
            const name = step === 1 ? 'repair_service_ids' : 'repair_device_ids';
            const error = panel.querySelector(step === 1 ? '.repair-service-error' : '.repair-model-error');
            if (!selected(name).length) {
                error.hidden = false;
                panel.querySelector('input[type="search"]').focus();
                return false;
            }
            error.hidden = true;
        }
        for (const input of panel.querySelectorAll('input, select, textarea')) {
            if (input.disabled || !input.willValidate || input.checkValidity()) continue;
            const details = input.closest('details');
            if (details) details.open = true;
            input.reportValidity();
            return false;
        }
        return true;
    }
    function priceText(input) {
        if (input?.disabled) return form.dataset.copiedLabel;
        return '$' + Number(input?.value || 0).toFixed(2);
    }
    function renderReview() {
        review.replaceChildren();
        form.querySelectorAll('.repair-model-price-row').forEach(row => {
            if (row.hidden) return;
            const result = document.createElement('tr');
            const partRow = Array.from(form.querySelectorAll('.repair-category-part-row')).find(category => {
                const service = form.querySelector('[name="repair_service_ids[]"][value="' + row.dataset.serviceId + '"]');
                return category.dataset.categoryId === service.closest('.repair-price-service-option').dataset.categoryId;
            });
            const text = [row.querySelector('[data-pair-model]').textContent, row.querySelector('[data-pair-category]').textContent,
                row.querySelector('[data-pair-service]').textContent,
                partRow?.querySelector('[data-part-field="part_name"]').value || '—',
                partRow?.querySelector('[data-part-field="part_type"]').value || '—'];
            ['part_cost', 'service_fee', 'selling_price'].forEach(field => text.push(priceText(row.querySelector('[data-price-field="' + field + '"]'))));
            text.forEach(value => {const cell = document.createElement('td'); cell.textContent = value; result.append(cell);});
            review.append(result);
        });
        form.querySelectorAll('[data-review-field]').forEach(item => {
            const input = form.elements.namedItem(item.dataset.reviewField);
            item.textContent = input.tagName === 'SELECT' ? input.selectedOptions[0]?.textContent : input.value || '—';
        });
    }
    function showStep(step, focus = true) {
        currentStep = step;
        panels.forEach(panel => {panel.hidden = Number(panel.dataset.priceStep) !== step;});
        steps.forEach(button => {
            if (Number(button.dataset.goStep) === step) button.setAttribute('aria-current', 'step');
            else button.removeAttribute('aria-current');
        });
        back.hidden = step === 1;
        next.hidden = step === 4;
        save.hidden = step !== 4;
        if (step === 4) renderReview();
        selectionSummary();
        modal.querySelector('.modal-body').scrollTop = 0;
        if (focus) panels[step - 1].querySelector('.repair-step-heading, h6')?.focus();
    }
    function goTo(step) {
        if (step > currentStep) {
            for (let i = 1; i < step; i++) {
                showStep(i, false);
                if (!validateStep(i)) return;
            }
        }
        showStep(step);
    }
    modes.forEach(button => button.addEventListener('click', () => {setMode(button.dataset.entryMode); showStep(1);}));
    steps.forEach(button => button.addEventListener('click', () => goTo(Number(button.dataset.goStep))));
    next.addEventListener('click', () => goTo(currentStep + 1));
    back.addEventListener('click', () => showStep(currentStep - 1));
    form.addEventListener('repair:selection-updated', selectionSummary);
    form.addEventListener('repair:restore-prices', () => {recovering = true;});
    form.addEventListener('submit', event => {
        const wasReviewing = currentStep === 4;
        if (submitting) {event.preventDefault(); return;}
        for (let step = 1; step <= 3; step++) {
            showStep(step, false);
            if (!validateStep(step)) {event.preventDefault(); return;}
        }
        showStep(4, false);
        if (wasReviewing && event.submitter === save) {
            submitting = true;
            save.disabled = true;
        } else event.preventDefault();
    });
    modal.addEventListener('show.bs.modal', event => {
        submitting = false;
        save.disabled = false;
        setMode(recovering ? modeField.value : event.relatedTarget?.dataset.priceTemplate ? 'bulk' : 'single');
        showStep(recovering ? 3 : 1, false);
        recovering = false;
    });
})();
