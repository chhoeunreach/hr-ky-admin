(function () {
    'use strict';

    const initialize = () => {
        const modal = document.getElementById('departmentDeleteModal');
        if (!modal) return;
        const element = name => document.getElementById('departmentDelete' + name);
        const messages = JSON.parse(element('Messages').textContent);
        const confirm = element('Confirm');
        const refresh = element('Refresh');
        const remove = element('RemovePositions');
        const unlink = element('UnlinkRecords');
        let url = '';
        let preview = null;
        let controller = null;
        let version = 0;
        let deleting = false;
        let correcting = false;
        let editingPositions = 0;
        let returnFromEmployees = false;

        const updateConfirm = () => {
            confirm.disabled = deleting || correcting || editingPositions > 0 || !preview
                || preview.blockers.some(blocker => !blocker.can_unlink || !unlink.checked)
                || (preview.positions_count > 0 && (!preview.can_remove_positions || !remove.checked));
        };
        const showError = message => {
            element('Error').textContent = message;
            element('Error').hidden = false;
        };
        const editPosition = async (position, row, button) => {
            if (correcting || editingPositions) return;
            ++editingPositions; updateConfirm(); button.disabled = true;
            const form = document.createElement('form'); form.className = 'border-top mt-2 pt-2';
            const label = document.createElement('label'); label.className = 'form-label small'; label.textContent = messages.department;
            const select = document.createElement('select'); select.className = 'form-select mb-2'; select.required = true;
            select.id = `move-position-${position.id}`; label.htmlFor = select.id;
            const error = document.createElement('p'); error.className = 'text-danger small mb-2'; error.hidden = true;
            const save = document.createElement('button'); save.type = 'submit'; save.className = 'btn btn-primary btn-sm me-2'; save.textContent = messages.save; save.disabled = true;
            const cancel = document.createElement('button'); cancel.type = 'button'; cancel.className = 'btn btn-outline-secondary btn-sm'; cancel.textContent = messages.cancel;
            form.append(label, select, error, save, cancel); row.append(form);
            const close = () => {
                if (window.jQuery(select).hasClass('select2-hidden-accessible')) window.jQuery(select).select2('destroy');
                form.remove(); button.disabled = false; editingPositions = 0; updateConfirm();
            };
            cancel.addEventListener('click', () => {if (!correcting) close();});
            window.jQuery(select).on('change', () => {save.disabled = !select.value || correcting;});
            try {
                const response = await fetch(position.assignment_url, {headers: {Accept: 'application/json'}});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || messages.failed);
                select.add(new Option(messages.department, ''));
                data.departments.forEach(department => select.add(new Option(department.dept_name, department.id)));
                window.jQuery(select).select2({width: '100%', dropdownParent: window.jQuery(modal)});
                if (!data.departments.length) {error.textContent = messages.noDepartments; error.hidden = false;}
            } catch (failure) {error.textContent = failure.message || messages.failed; error.hidden = false;}
            form.addEventListener('submit', async event => {
                event.preventDefault(); if (save.disabled) return;
                correcting = true; updateConfirm(); save.disabled = true; cancel.disabled = true; refresh.disabled = true;
                try {
                    const response = await fetch(position.assignment_url, {
                        method: 'PATCH', headers: {Accept: 'application/json', 'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                        body: JSON.stringify({department_id: select.value}),
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || messages.failed);
                    close();
                    document.dispatchEvent(new CustomEvent('department:positions-changed'));
                    await load();
                } catch (failure) {error.textContent = failure.message || messages.failed; error.hidden = false;}
                finally {correcting = false; save.disabled = !select.value; cancel.disabled = false; refresh.disabled = false; updateConfirm();}
            });
        };
        const render = data => {
            element('Name').textContent = data.name;
            element('Scope').textContent = messages.scope.replace(':count', data.departments_count);
            element('Branches').replaceChildren(...data.branches.map(name => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-primary border'; badge.textContent = name;
                return badge;
            }));
            element('PositionCount').textContent = data.positions_count;
            const rows = data.positions.map(position => {
                const row = document.createElement('li'); row.className = 'list-group-item';
                row.textContent = [position.name, position.branch, !position.active && messages.inactive].filter(Boolean).join(' — ');
                if (data.can_change_positions && position.assignment_url) {
                    const button = document.createElement('button'); button.type = 'button';
                    button.className = 'btn btn-outline-primary btn-sm d-block mt-2'; button.textContent = messages.move;
                    button.disabled = position.assigned_employees > 0;
                    if (button.disabled) button.title = messages.employeesFirst;
                    button.addEventListener('click', () => editPosition(position, row, button)); row.append(button);
                }
                return row;
            });
            if (!rows.length) {
                const row = document.createElement('li'); row.className = 'list-group-item text-muted'; row.textContent = messages.empty;
                rows.push(row);
            }
            element('Positions').replaceChildren(...rows);
            element('BlockerList').replaceChildren(...data.blockers.map(blocker => {
                const row = document.createElement('li'); row.textContent = `${blocker.label}: ${blocker.count}`
                    + (blocker.can_unlink ? ' · ' + messages.canUnlink : ''); return row;
            }));
            element('Blockers').hidden = data.blockers.length === 0;
            element('Corrections').hidden = !data.can_change_positions && !data.can_change_employees;
            element('FooterCorrections').hidden = !data.can_change_positions && !data.can_change_employees;
            element('CorrectEmployees').hidden = !data.can_change_employees;
            element('CorrectPositions').hidden = !data.can_change_positions || !data.positions_count;
            element('RemoveOption').hidden = !data.positions_count || !data.can_remove_positions;
            element('Permission').hidden = !data.positions_count || data.can_remove_positions;
            remove.checked = false;
            remove.disabled = data.blockers.some(blocker => !blocker.can_unlink);
            const unlinkable = data.blockers.filter(blocker => blocker.can_unlink);
            element('UnlinkOption').hidden = unlinkable.length === 0;
            element('UnlinkCount').textContent = unlinkable.reduce((total, blocker) => total + blocker.count, 0);
            unlink.checked = false;
            element('Details').hidden = false;
            updateConfirm();
        };
        const load = async () => {
            const requestVersion = ++version;
            controller?.abort(); controller = new AbortController();
            element('Positions').querySelectorAll('select.select2-hidden-accessible').forEach(select => window.jQuery(select).select2('destroy'));
            editingPositions = 0;
            preview = null; updateConfirm();
            element('Loading').hidden = false; element('Error').hidden = true; element('Details').hidden = true;
            refresh.disabled = true;
            try {
                const response = await fetch(url, {headers: {Accept: 'application/json'}, signal: controller.signal});
                const data = await response.json();
                if (requestVersion !== version) return;
                if (!response.ok) throw new Error(data.message || messages.failed);
                preview = data; render(data);
            } catch (error) {
                if (requestVersion === version && error.name !== 'AbortError') showError(error.message || messages.failed);
            } finally {
                if (requestVersion === version) {element('Loading').hidden = true; refresh.disabled = false;}
            }
        };

        document.addEventListener('click', event => {
            const link = event.target.closest('.deleteBranch[data-href]');
            if (!link) return;
            event.preventDefault(); url = link.dataset.href;
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
            load();
        });
        refresh.addEventListener('click', load);
        element('CorrectPositions').addEventListener('click', () => {
            element('Positions').scrollIntoView({behavior: 'smooth', block: 'start'});
            element('Positions').querySelector('button:not(:disabled)')?.focus();
        });
        element('CorrectEmployees').addEventListener('click', () => {
            if (!preview || correcting || deleting) return;
            const employeesUrl = preview.employees_url;
            returnFromEmployees = true;
            modal.addEventListener('hidden.bs.modal', () => {
                document.dispatchEvent(new CustomEvent('department:open-employees', {detail: {url: employeesUrl}}));
            }, {once: true});
            window.bootstrap.Modal.getOrCreateInstance(modal).hide();
        });
        document.addEventListener('department:employees-closed', () => {
            if (!returnFromEmployees) return;
            returnFromEmployees = false;
            window.bootstrap.Modal.getOrCreateInstance(modal).show(); load();
        });
        document.addEventListener('department:employees-changed', () => {
            if (modal.classList.contains('show') && !deleting) load();
        });
        remove.addEventListener('change', updateConfirm);
        unlink.addEventListener('change', updateConfirm);
        confirm.addEventListener('click', async () => {
            if (confirm.disabled) return;
            deleting = true; updateConfirm(); refresh.disabled = true; element('Error').hidden = true;
            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({remove_positions: remove.checked, unlink_records: unlink.checked}),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || messages.failed);
                deleting = false;
                window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                document.dispatchEvent(new CustomEvent('department:deleted'));
            } catch (error) {
                showError(error.message || messages.failed);
            } finally {
                deleting = false; refresh.disabled = false; updateConfirm();
            }
        });
        modal.addEventListener('hide.bs.modal', event => {if (deleting || correcting) event.preventDefault();});
        modal.addEventListener('hidden.bs.modal', () => {++version; controller?.abort(); preview = null;});
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
