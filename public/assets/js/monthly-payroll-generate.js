(() => {
    const modal = document.getElementById('monthlyPayrollGenerateModal');
    const form = modal?.querySelector('[data-payroll-generate-form]');
    if (!modal || !form) return;
    modal.addEventListener('show.bs.modal', event => {
        const month = document.getElementById('monthlyMonth')?.value || event.relatedTarget?.dataset.payrollMonth;
        if (month) form.elements.month.value = month;
        const branch = document.getElementById('monthlyBranch') || document.getElementById('yearlyPayrollBranch');
        if (branch?.value) form.elements.branch_id.value = branch.value;
    });
    form.addEventListener('submit', () => {
        form.querySelector('[type="submit"]').disabled = true;
        form.setAttribute('aria-busy', 'true');
    });
})();
