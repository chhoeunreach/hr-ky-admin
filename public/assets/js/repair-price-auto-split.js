(() => {
    'use strict';

    // Ratios can be tuned in config/repair-pricing.php (injected as
    // window.REPAIR_PRICING) without touching this file.
    const config = (typeof window !== 'undefined' && window.REPAIR_PRICING) || {};
    const share = Number(config.partShare ?? config.part_share);
    const PART_SHARE = Number.isFinite(share) && share > 0 && share < 1 ? share : 0.7;
    const FEE_SHARE = 1 - PART_SHARE;
    const configuredStep = Number(config.priceStep ?? config.price_step);
    const PRICE_STEP = Number.isFinite(configuredStep) && configuredStep > 0 ? configuredStep : 0.5;
    const FIELDS = ['part_cost', 'service_fee', 'selling_price'];
    const round = value => Math.round((value + Number.EPSILON) * 100) / 100;

    // Suggested selling prices round up to a clean price point so the part cost
    // never takes more than its target share of the sale.
    const pricePoint = value => round(Math.ceil((value - 1e-9) / PRICE_STEP) * PRICE_STEP);

    function fieldsFor(input) {
        if (typeof input.closest !== 'function') return null;
        const group = input.closest('[data-price-group], .repair-model-price-row');
        if (!group) return null;
        const fields = {};
        group.querySelectorAll('[data-price-field]').forEach(field => {
            if (FIELDS.includes(field.dataset.priceField)) fields[field.dataset.priceField] = field;
        });
        return fields;
    }

    function write(input, value) {
        // Read-only targets are fine: the script owns the calculated service fee.
        // Disabled targets (private copied costs) stay untouched for the server.
        if (!input || input.disabled) return;
        input.value = value === null ? '' : value.toFixed(2);
    }

    function amount(input) {
        const raw = String(input.value ?? '').trim();
        const value = Number.parseFloat(raw);
        return raw === '' || Number.isNaN(value) ? null : value;
    }

    function sync(input) {
        const field = input.dataset?.priceField;
        // The service fee is read-only and always calculated, so it never drives
        // changes; disabled inputs (hidden rows, private costs) are not edited.
        if (!FIELDS.includes(field) || input.disabled || input.readOnly) return;
        const fields = fieldsFor(input);
        if (!fields || FIELDS.some(name => !fields[name])) return;

        const value = amount(input);
        const part = amount(fields.part_cost);

        if (value === null) {
            // Part cost is the primary value and is never changed automatically.
            if (field === 'part_cost') {
                write(fields.selling_price, null);
                write(fields.service_fee, null);
            } else {
                write(fields.service_fee, null);
            }
            return;
        }

        if (field === 'part_cost') {
            const current = amount(fields.selling_price);
            const manual = current !== null && fields.selling_price.dataset.priceSuggested !== '1';
            // A selling price the user chose is kept; otherwise a clean cost-plus
            // price point is suggested with the part at its target share (~70%).
            const selling = manual ? current : pricePoint(value / PART_SHARE);
            if (!manual && !fields.selling_price.disabled) {
                write(fields.selling_price, selling);
                fields.selling_price.dataset.priceSuggested = '1';
            }
            // The service fee is always the calculated remainder
            // (selling price − part cost, e.g. 500 − 400 = 100).
            write(fields.service_fee, round(Math.max(0, selling - value)));
        } else {
            // Manual selling price: respected from now on, and the service fee
            // is always recalculated as selling price − part cost. Without a part
            // cost the fee falls back to its target share of the sale.
            delete input.dataset.priceSuggested;
            write(fields.service_fee, part === null
                ? round(value * FEE_SHARE)
                : round(Math.max(0, value - part)));
        }
    }

    document.addEventListener('input', event => {
        const input = event.target;
        if (input && input.dataset && input.dataset.priceField) sync(input);
    });
})();
