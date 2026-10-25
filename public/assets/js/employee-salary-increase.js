(() => {
    const form = document.querySelector('[data-payroll-increase]');
    if (!form) return;
    const fields = form.elements;
    const field = name => fields.namedItem(name);
    const annual = Number(form.dataset.annual);
    const periods = Number(form.dataset.periods);
    const basicValue = Number(form.dataset.basicValue);
    const earnings = JSON.parse(form.dataset.earnings || '{"fixed":0,"basic":0,"ctc":0}');
    const round = amount => Math.round((amount + Number.EPSILON) * 100) / 100;
    const breakdown = gross => {
        const basic = form.dataset.basicType === 'percent' ? round(gross * basicValue / 100) : round(basicValue * 12);
        const extra = round(earnings.fixed + basic * earnings.basic / 100 + gross * earnings.ctc / 100);
        return {annual: gross, gross: gross / periods, basic: basic / periods, earnings: extra / periods, allowance: (gross - basic - extra) / periods};
    };
    const money = amount => form.dataset.currency + Number(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const render = (selector, values) => form.querySelectorAll(selector).forEach(node => {
        const key = selector.includes('before') ? node.dataset.salaryBefore : node.dataset.salaryAfter;
        node.textContent = values ? money(values[key]) : '—';
    });
    render('[data-salary-before]', breakdown(annual));
    const update = () => {
        const percent = field('increase_type').value === 'percent';
        const amount = field('increment_amount');
        const percentage = field('increment_percent');
        amount.disabled = percent;
        amount.required = !percent;
        percentage.disabled = !percent;
        percentage.required = percent;
        form.querySelectorAll('[data-amount-options]').forEach(node => {node.hidden = percent;});
        form.querySelectorAll('[data-percent-options]').forEach(node => {node.hidden = !percent;});
        const increase = percent ? round(annual * Number(percentage.value) / 100)
            : round(Number(amount.value) * (field('increase_period').value === 'period' ? periods : 1));
        const selected = percent ? percentage : amount;
        const total = round(annual + increase);
        const valid = Number.isFinite(total) && increase > 0 && total <= 99999999.99 && breakdown(total).allowance >= 0;
        amount.setCustomValidity('');
        percentage.setCustomValidity('');
        selected.setCustomValidity(selected.value && !valid ? form.dataset.error : '');
        render('[data-salary-after]', valid ? breakdown(total) : null);
    };
    ['increase_type', 'increase_period', 'increment_amount', 'increment_percent'].forEach(name => {
        field(name).addEventListener('input', update);
        field(name).addEventListener('change', update);
    });
    form.addEventListener('submit', update);
    update();
})();
