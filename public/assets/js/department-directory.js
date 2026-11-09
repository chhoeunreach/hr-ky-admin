(function () {
    'use strict';
    const initialize = () => {
        const modal = document.getElementById('departmentDetailsModal');
        if (!modal) return;
        const element = name => document.getElementById('departmentDetails' + name);
        const messages = JSON.parse(element('Messages').textContent);
        document.addEventListener('click', event => {
            const button = event.target.closest('.show-department-details');
            const row = button?.closest('tr[data-department-details]');
            if (!row) return;
            event.preventDefault();
            const data = JSON.parse(row.dataset.departmentDetails);
            element('Title').textContent = data.name;
            element('Employees').textContent = data.employees.toLocaleString();
            element('EmployeeStatus').textContent = messages.employees + ': ' + data.employee_status;
            const labels = (values, badge) => (values.length ? values : [messages.empty]).map(value => {
                const span = document.createElement('span'); span.textContent = value;
                if (badge) span.className = 'badge bg-light text-primary border';
                return span;
            });
            element('Heads').replaceChildren(...labels(data.heads, false));
            element('Branches').replaceChildren(...labels(data.branches, true));
            element('Entries').replaceChildren(...data.entries.map(entry => {
                const tr = document.createElement('tr');
                [entry.branch, entry.head, entry.employees, entry.address, entry.phone, entry.status].forEach(value => {
                    const td = document.createElement('td'); td.textContent = value === null || value === '' ? messages.empty : value; tr.append(td);
                });
                return tr;
            }));
            element('Defaults').href = data.defaults_url;
            if (element('Edit')) element('Edit').href = data.edit_url;
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
