(() => {
    const initialize = () => {
        const modal = document.getElementById('assetAssignmentModal');
        const form = document.getElementById('assetAssignmentForm');
        const configElement = document.getElementById('assetAssignmentConfig');
        if (!modal || !form || !configElement) return;
        const text = JSON.parse(configElement.textContent);
        const employee = form.querySelector('[name="user_id"]');
        const department = form.querySelector('[name="department_id"]');
        const branch = form.querySelector('[name="branch_id"]');
        const date = form.querySelector('[name="assigned_date"]');
        const submit = form.querySelector('[type="submit"]');
        let submitLabel = submit.textContent;
        let transfer = false;
        const status = modal.querySelector('[data-assignment-status]');
        const error = modal.querySelector('[data-assignment-error]');
        const errorText = modal.querySelector('[data-assignment-error-text]');
        const retry = modal.querySelector('[data-assignment-retry]');
        let employees = [];
        let departments = [];
        let branches = [];
        let generation = 0;
        let loading = false;
        let saving = false;
        let optionsUrl = '';
        const $ = window.jQuery;
        const refreshSelect = select => { if ($?.fn.select2) $(select).trigger('change.select2'); };
        if ($?.fn.select2) {
            [branch, employee, department].forEach(select => $(select).select2({
                dropdownParent: $(modal), width: '100%',
                language: {noResults: () => text.noResults, searching: () => text.searching},
            }));
        }
        const reset = (select, label) => { select.replaceChildren(new Option(label, '')); select.value = ''; refreshSelect(select); };
        const setError = (message = '', canRetry = false) => {
            errorText.textContent = message;
            error.classList.toggle('d-none', !message);
            retry.classList.toggle('d-none', !canRetry);
        };
        const clearFieldErrors = () => form.querySelectorAll('[data-assignment-field-error]').forEach(field => {field.textContent = '';});
        const syncSubmit = () => {
            submit.disabled = loading || saving || !branch.value || !employee.value || !department.value || !date.value || !form.getAttribute('action');
            submit.textContent = saving ? text.saving : submitLabel;
        };
        const employeeLabel = user => {
            const name = [user.name, user.english_name].filter((value, index, list) => value && list.indexOf(value) === index).join(' / ');
            const code = user.employee_code || user.username;
            const username = user.username && user.username !== code ? ' / ' + user.username : '';
            return name + (code ? ' · ' + code + username : '') + ' · ' + (departments.find(item => String(item.id) === String(user.department_id))?.dept_name || '')
                + ' · ' + (branches.find(item => String(item.id) === String(user.branch_id))?.name || '');
        };
        const populateEmployees = departmentId => {
            reset(employee, text.employee);
            const choices = employees.filter(user => (!branch.value || String(user.branch_id) === branch.value)
                && (!departmentId || String(user.department_id) === String(departmentId)));
            choices.forEach(user => employee.add(new Option(employeeLabel(user), String(user.id))));
            employee.disabled = loading || saving || !choices.length;
            status.textContent = choices.length ? '' : text.empty;
            refreshSelect(employee);
            syncSubmit();
        };
        const populateDepartments = branchId => {
            reset(department, text.departments);
            const choices = departments.filter(item => !branchId || String(item.branch_id) === branchId);
            choices.forEach(item => department.add(new Option(item.dept_name, String(item.id))));
            department.disabled = loading || !choices.length;
            refreshSelect(department);
        };
        const updateBranchSummary = () => {
            const summary = modal.querySelector('[data-assignment-summary="branch"]');
            if (summary) summary.textContent = branches.find(item => String(item.id) === branch.value)?.name || text.unavailable;
        };
        const loadOptions = async (selectedBranchId = '') => {
            const current = ++generation;
            loading = true;
            employees = [];
            departments = [];
            reset(branch, text.branch);
            branch.disabled = true;
            reset(employee, text.employee);
            reset(department, text.departments);
            employee.disabled = true;
            department.disabled = true;
            status.textContent = text.loading;
            setError(); clearFieldErrors(); syncSubmit();
            try {
                const url = optionsUrl + (selectedBranchId ? (optionsUrl.includes('?') ? '&' : '?') + 'branch_id=' + encodeURIComponent(selectedBranchId) : '');
                const response = await fetch(url, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
                if (!response.ok) throw new Error('Options unavailable');
                const result = await response.json();
                if (current !== generation) return;
                employees = result.employees.filter(user => !transfer || String(user.id) !== String(result.current_assignment?.user_id));
                date.min = transfer ? (result.current_assignment?.assigned_date || '') : '';
                date.max = text.today;
                departments = result.departments;
                branches = result.branches;
                form.querySelector('[name="asset_id"]').value = result.asset.id;
                result.branches.forEach(item => branch.add(new Option(item.name, String(item.id))));
                branch.value = result.selected_branch_id ? String(result.selected_branch_id) : '';
                branch.disabled = !result.branches.length;
                const branchHelp = modal.querySelector('[data-assignment-branch-help]');
                if (branchHelp) branchHelp.hidden = result.branch_locked;
                refreshSelect(branch);
                const summary = {name: result.asset.name, code: result.asset.asset_code, serial: result.asset.asset_serial_no, branch: result.branches.find(item => String(item.id) === branch.value)?.name || result.asset.branch_name};
                modal.querySelectorAll('[data-assignment-summary]').forEach(item => {item.textContent = summary[item.dataset.assignmentSummary] || text.unavailable;});
                loading = false;
                populateDepartments(branch.value);
                populateEmployees('');
                if (!branches.length) status.textContent = text.noBranches;
            } catch (_) {
                if (current !== generation) return;
                loading = false;
                status.textContent = '';
                setError(text.loadError, true);
            } finally {
                if (current === generation) syncSubmit();
            }
        };
        const onEmployeeChange = () => {
            const chosen = employees.find(user => String(user.id) === employee.value);
            if (chosen) {
                branch.value = String(chosen.branch_id);
                refreshSelect(branch);
                populateDepartments(branch.value);
                department.value = String(chosen.department_id);
                refreshSelect(department);
                updateBranchSummary();
            }
            clearFieldErrors(); syncSubmit();
        };
        const onDepartmentChange = () => {
            const chosen = departments.find(item => String(item.id) === department.value);
            if (chosen) { branch.value = String(chosen.branch_id); refreshSelect(branch); updateBranchSummary(); }
            clearFieldErrors(); populateEmployees(department.value);
        };
        const onBranchChange = () => {
            clearFieldErrors(); populateDepartments(branch.value); populateEmployees(''); updateBranchSummary();
        };
        if ($?.fn.select2) {
            $(branch).on('change', onBranchChange);
            $(employee).on('change', onEmployeeChange);
            $(department).on('change', onDepartmentChange);
        } else {
            branch.addEventListener('change', onBranchChange);
            employee.addEventListener('change', onEmployeeChange);
            department.addEventListener('change', onDepartmentChange);
        }
        date.addEventListener('input', syncSubmit);
        document.addEventListener('click', event => {
            const trigger = event.target.closest('.assignAsset');
            if (!trigger) return;
            event.preventDefault();
            if (saving) return;
            form.reset();
            transfer = trigger.dataset.assignmentMode === 'transfer';
            submitLabel = (transfer ? text.transferLabel : text.assignLabel) || submitLabel;
            const heading = modal.querySelector('.modal-title');
            if (heading) heading.textContent = submitLabel;
            form.setAttribute('action', trigger.dataset.href);
            date.value = text.today;
            optionsUrl = trigger.dataset.optionsUrl;
            modal.querySelectorAll('[data-assignment-summary]').forEach(item => {item.textContent = '—';});
            bootstrap.Modal.getOrCreateInstance(modal).show(trigger);
            loadOptions();
        });
        retry.addEventListener('click', () => loadOptions(branch.value));
        modal.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
        modal.addEventListener('hidden.bs.modal', () => {generation++; loading = false; employees = []; departments = []; syncSubmit();});
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (loading || saving || submit.disabled || !form.reportValidity()) return;
            saving = true;
            setError(); clearFieldErrors(); syncSubmit();
            try {
                const response = await fetch(form.getAttribute('action'), {
                    method: 'POST', credentials: 'same-origin', body: new FormData(form), headers: {'Accept': 'application/json'},
                });
                const result = await response.json();
                if (!response.ok) {
                    for (const [field, messages] of Object.entries(result.errors || {})) {
                        const target = form.querySelector('[data-assignment-field-error="' + field + '"]');
                        if (target) target.textContent = Array.isArray(messages) ? messages.join(' ') : String(messages);
                    }
                    throw new Error(result.error || result.message || text.saveError);
                }
                saving = false;
                bootstrap.Modal.getOrCreateInstance(modal).hide();
                if (window.Swal) await Swal.fire({icon: 'success', title: text.successTitle, text: result.message || text.success, confirmButtonText: text.ok});
                window.location.reload();
            } catch (failure) {
                setError(failure.message === 'Failed to fetch' || failure instanceof SyntaxError ? text.saveError : (failure.message || text.saveError));
            } finally { saving = false; syncSubmit(); }
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
