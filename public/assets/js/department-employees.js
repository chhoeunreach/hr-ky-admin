(function () {
    'use strict';
    const initialize = () => {
        const modal = document.getElementById('departmentEmployeesModal');
        if (!modal) return;
        const element = name => document.getElementById('departmentEmployees' + name);
        const messages = JSON.parse(document.getElementById('departmentEmployeeMessages').textContent);
        const list = element('List');
        const template = document.getElementById('departmentEmployeeRow');
        let groupName = '';
        let version = 0;
        let controller = null;
        let pendingSaves = 0;
        const headers = () => ({Accept: 'application/json', 'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content});
        const errorMessage = data => Object.values(data.errors || {}).flat()[0] || data.message || messages.failed;
        const request = async (url, options = {}) => {
            const response = await fetch(url, {headers: headers(), ...options});
            const data = await response.json();
            if (!response.ok) throw new Error(errorMessage(data));
            return data;
        };
        const updateCount = () => {
            element('Count').textContent = list.children.length;
            element('Empty').hidden = list.children.length > 0;
        };
        const destroySelects = row => row.querySelectorAll('select.select2-hidden-accessible')
            .forEach(select => window.jQuery(select).select2('destroy'));

        const makeRow = (employee, canEdit) => {
            const row = template.content.firstElementChild.cloneNode(true);
            const find = selector => row.querySelector(selector);
            const form = find('form');
            const change = find('.employee-change');
            const department = find('.employee-department');
            const save = find('.employee-save');
            const cancel = find('.employee-cancel');
            const error = find('.employee-assignment-error');
            let saving = false;
            const display = () => {
                find('.employee-name').textContent = employee.name;
                find('.employee-status').textContent = employee.active ? messages.active : messages.inactive;
                find('.employee-status').className = 'employee-status badge bg-light border ' + (employee.active ? 'text-success' : 'text-secondary');
                find('.employee-avatar').src = employee.avatar;
                find('.employee-assignment').textContent = [employee.branch, employee.department, employee.position].filter(Boolean).join(' · ');
                find('.employee-position-current').textContent = employee.position || '—';
            };
            const updateSave = () => {save.disabled = saving || !department.value || !employee.position
                || (department.value === String(employee.department_id) && String(employee.position_department_id) === department.value);};
            display(); change.hidden = !canEdit;
            department.id = `employee-department-${employee.id}`;
            find('.employee-department-label').htmlFor = department.id;
            window.jQuery(department).on('change', updateSave);

            change.addEventListener('click', async () => {
                change.disabled = true; form.hidden = false; error.hidden = true;
                department.disabled = true; save.disabled = true;
                try {
                    const data = await request(employee.assignment_url);
                    department.replaceChildren(new Option(messages.department, ''));
                    data.departments.forEach(item => department.add(new Option(item.dept_name, item.id)));
                    department.value = String(employee.department_id || '');
                    [department].forEach(select => {
                        if (!window.jQuery(select).hasClass('select2-hidden-accessible')) {
                            window.jQuery(select).select2({width: '100%', dropdownParent: window.jQuery(modal)});
                        }
                    });
                    window.jQuery(department).trigger('change.select2');
                    updateSave();
                } catch (failure) {
                    error.textContent = failure.message || messages.failed; error.hidden = false;
                    change.disabled = false;
                } finally {department.disabled = false;}
            });
            cancel.addEventListener('click', () => {if (!saving) {form.hidden = true; change.disabled = false;}});
            form.addEventListener('submit', async event => {
                event.preventDefault();
                if (save.disabled) return;
                saving = true; ++pendingSaves; updateSave(); cancel.disabled = true;
                department.disabled = true; error.hidden = true;
                try {
                    const data = await request(employee.assignment_url, {method: 'PATCH', body: JSON.stringify({
                        department_id: department.value,
                    })});
                    employee = data.employee;
                    form.hidden = true; change.disabled = false; display();
                    if (employee.department !== groupName) {destroySelects(row); row.remove();}
                    updateCount();
                    element('Success').textContent = data.message; element('Success').hidden = false;
                    document.dispatchEvent(new CustomEvent('department:employees-changed', {detail: {employee_id: employee.id}}));
                } catch (failure) {error.textContent = failure.message || messages.failed; error.hidden = false;}
                finally {
                    saving = false; --pendingSaves; cancel.disabled = false;
                    department.disabled = false; updateSave();
                }
            });
            return row;
        };

        const openEmployees = async url => {
            const currentVersion = ++version;
            controller?.abort(); controller = new AbortController();
            list.querySelectorAll('.employee-assignment-row').forEach(destroySelects);
            list.replaceChildren(); element('Count').textContent = '';
            element('Loading').hidden = false; element('Empty').hidden = true;
            element('Error').hidden = true; element('Success').hidden = true;
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
            try {
                const data = await request(url, {signal: controller.signal});
                if (currentVersion !== version) return;
                groupName = data.name;
                element('Title').textContent = messages.title + ' — ' + groupName;
                list.replaceChildren(...data.employees.map(employee => makeRow(employee, data.can_edit)));
                updateCount();
            } catch (failure) {
                if (currentVersion === version && failure.name !== 'AbortError') {
                    element('Error').textContent = failure.message || messages.failed; element('Error').hidden = false;
                }
            } finally {if (currentVersion === version) element('Loading').hidden = true;}
        };
        document.addEventListener('click', event => {
            const button = event.target.closest('.show-department-employees[data-url]');
            if (!button) return;
            event.preventDefault(); openEmployees(button.dataset.url);
        });
        document.addEventListener('department:open-employees', event => openEmployees(event.detail.url));
        modal.addEventListener('hide.bs.modal', event => {if (pendingSaves) event.preventDefault();});
        modal.addEventListener('hidden.bs.modal', () => {
            ++version; controller?.abort();
            document.dispatchEvent(new CustomEvent('department:employees-closed'));
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
