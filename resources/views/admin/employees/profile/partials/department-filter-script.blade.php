<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById(@json($departmentFilterForm));
        const branch = form?.querySelector('select[name="branch_id"]');
        const department = form?.querySelector('select[name="department_ids[]"]');
        const departments = @json($availableDepartments);
        if (!branch || !department) return;

        if (window.jQuery && jQuery.fn.select2) {
            jQuery(department).select2({ width: '100%', closeOnSelect: false });
        }
        function updateDepartments() {
            const selected = new Set(Array.from(department.selectedOptions, option => option.value));
            const options = departments
                .filter(item => !branch.value || String(item.branch_id) === branch.value)
                .map(item => new Option(item.dept_name, item.id, false, selected.has(String(item.id))));
            department.replaceChildren(...options);
            if (window.jQuery && jQuery.fn.select2) jQuery(department).trigger('change');
        }
        branch.addEventListener('change', updateDepartments);
        updateDepartments();
    });
</script>
