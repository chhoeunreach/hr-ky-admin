(() => {
    const initialize = () => {
        const configNode = document.getElementById('employeePerformanceFormConfig');
        if (!configNode || !window.bootstrap?.Modal) return;
        const config = JSON.parse(configNode.textContent);
        for (const id of ['addResponsibilityModal', 'addKpiModal']) {
            const modal = document.getElementById(id);
            if (!modal) continue;
            document.body.appendChild(modal);
            const form = modal.querySelector('[data-performance-form]');
            const kind = form.dataset.performanceForm;
            let restore = config.reopenType === kind ? {id: config.reopenId, values: config.values} : null;
            const populate = (record = {}, id = '', url = '') => {
                form.reset();
                const editing = /^\d+$/.test(String(id));
                form.action = editing ? (url || form.dataset.updateUrl.replace('__record__', String(id))) : form.dataset.storeUrl;
                for (const [name, value] of Object.entries(record)) {
                    const field = form.elements.namedItem(name);
                    if (!field || ['_method', 'employee_id', 'assigned_by', 'created_by'].includes(name)) continue;
                    field.value = value == null ? '' : (field.type === 'date' ? String(value).slice(0, 10) : String(value));
                }
                form.elements.namedItem('_method').value = editing ? 'PUT' : 'POST';
                form.elements.namedItem('performance_record_id').value = editing ? id : '';
                modal.querySelector('.modal-title').textContent = editing ? config.edit[kind] : config.create[kind];
                form.querySelector('[type="submit"]').textContent = editing ? config.update : config.create[kind];
            };
            modal.addEventListener('show.bs.modal', event => {
                if (restore) {
                    const saved = restore; restore = null;
                    populate(saved.values, saved.id); return;
                }
                const trigger = event.relatedTarget;
                if (trigger?.hasAttribute('data-performance-edit')) {
                    populate(JSON.parse(trigger.dataset.editRecord), trigger.dataset.recordId, trigger.dataset.editUrl);
                } else populate();
            });
            modal.addEventListener('shown.bs.modal', () => form.querySelector('[name="title"], [name="name"]')?.focus());
            if (restore) {
                window.employeeGotoTab?.('kpi');
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        }
        document.addEventListener('submit', event => {
            if (!event.target.matches('[data-performance-delete]')) return;
            if (!window.confirm(config.deleteConfirm)) event.preventDefault();
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
