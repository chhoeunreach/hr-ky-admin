(() => {
    const modal = document.getElementById('employeeSalaryDeleteModal');
    if (!modal) return;
    const form = modal.querySelector('[data-salary-delete-form]');
    const error = modal.querySelector('[data-salary-delete-error]');
    modal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        if (!trigger?.dataset.deleteUrl) return;
        form.action = trigger.dataset.deleteUrl;
        modal.querySelector('[data-salary-delete-employee]').textContent = trigger.dataset.employeeName;
        error.textContent = '';
    });
    form.addEventListener('submit', event => {
        event.preventDefault();
        error.textContent = '';
        form.dispatchEvent(new CustomEvent('employee-salary:submit', {bubbles: true, cancelable: true}));
    });
})();
