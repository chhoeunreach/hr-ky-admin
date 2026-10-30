(() => {
    const save = document.querySelector('[data-payroll-preview-save]');
    save?.addEventListener('submit', () => {
        save.querySelector('[type="submit"]').disabled = true;
        save.setAttribute('aria-busy', 'true');
    });
    const modal = document.getElementById('monthlyPayrollGenerateModal');
    const form = modal?.querySelector('[data-payroll-generate-form]');
    if (!modal || !form) return;
    const branchChoices = Array.from(form.querySelectorAll('[name="branch_ids[]"]'));
    const contextBranch = () => document.getElementById('monthlyBranch') || document.getElementById('yearlyPayrollBranch');
    let lastBranch = form.dataset.restoreBranches === '1' ? contextBranch()?.value || '' : undefined;
    modal.querySelector('[data-payroll-branches-all]')?.addEventListener('click', () => {
        branchChoices.forEach(choice => { if (!choice.disabled) choice.checked = true; });
    });
    modal.querySelector('[data-payroll-branches-clear]')?.addEventListener('click', () => {
        branchChoices.forEach(choice => { choice.checked = false; });
    });
    modal.addEventListener('show.bs.modal', event => {
        const month = document.getElementById('monthlyMonth')?.value || event.relatedTarget?.dataset.payrollMonth;
        if (month) form.elements.month.value = month;
        const branchId = contextBranch()?.value || '';
        if (branchId !== lastBranch) {
            branchChoices.forEach(choice => { choice.checked = Boolean(branchId) && choice.value === branchId; });
            lastBranch = branchId;
        }
    });
    form.addEventListener('submit', () => {
        form.querySelector('[type="submit"]').disabled = true;
        form.setAttribute('aria-busy', 'true');
    });
})();
