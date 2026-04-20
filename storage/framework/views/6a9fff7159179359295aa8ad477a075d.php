<script>

    $(document).ready(function () {



        $("#employee_id").select2({
            placeholder: "<?php echo e(__('index.select_employee')); ?>"
        });
        $("#department_id").select2({
            placeholder: "<?php echo e(__('index.select_department')); ?>"
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });



        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `<?php echo e(__('index.delete_event_confirmation')); ?>`,
                showDenyButton: true,
                confirmButtonText: `<?php echo e(__('index.yes')); ?>`,
                denyButtonText: `<?php echo e(__('index.no')); ?>`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.removeImage').click(function (event){
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `<?php echo e(__('index.image_delete_confirmation')); ?>`,
                showDenyButton: true,
                confirmButtonText: `<?php echo e(__('index.yes')); ?>`,
                denyButtonText: `<?php echo e(__('index.no')); ?>`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('.nepali_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

    });


    $(document).ready(function () {
        const isAdmin = <?php echo e(auth('admin')->check() ? 'true' : 'false'); ?>;
        const defaultBranchId = <?php echo e(auth()->user()->branch_id ?? 'null'); ?>;
        const branchId = "<?php echo e($filterParameters['branch_id'] ?? null); ?>";

        const filterDepartmentIds = Array.isArray(JSON.parse('<?php echo json_encode($filterParameters['department_id'] ?? []); ?>'))
            ? JSON.parse('<?php echo json_encode($filterParameters['department_id'] ?? []); ?>').map(String)
            : [String(JSON.parse('<?php echo json_encode($filterParameters['department_id'] ?? []); ?>'))].filter(Boolean);
        const filterEmployeeIds = Array.isArray(JSON.parse('<?php echo json_encode($filterParameters['employee_id'] ?? []); ?>'))
            ? JSON.parse('<?php echo json_encode($filterParameters['employee_id'] ?? []); ?>').map(String)
            : [String(JSON.parse('<?php echo json_encode($filterParameters['employee_id'] ?? []); ?>'))].filter(Boolean);

        const formDepartmentIds = <?php echo isset($departmentIds) ? json_encode($departmentIds) : '[]'; ?>.map(String);
        const formEmployeeIds = <?php echo isset($userIds) ? json_encode($userIds) : '[]'; ?>.map(String);

        const isFilterContext = <?php echo e(isset($filterParameters) && !isset($trainingDetail) ? 'true' : 'false'); ?>;
        const departmentIds = isFilterContext && filterDepartmentIds.length > 0 ? filterDepartmentIds : formDepartmentIds;
        let employeeIds = isFilterContext && filterEmployeeIds.length > 0 ? filterEmployeeIds : formEmployeeIds;

        const preloadDepartments = async (selectedBranchId) => {
            if (!selectedBranchId) {
                $('#department_id').empty().append('<option value="select_all"><?php echo e(__("index.select_all")); ?></option>');
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `<?php echo e(url('admin/departments/get-All-Departments')); ?>/${selectedBranchId}`,
                });

                $('#department_id').empty().append('<option value="select_all"><?php echo e(__("index.select_all")); ?></option>');
                if (!response || !response.data || response.data.length === 0) {
                    $('#department_id').append('<option disabled><?php echo e(__("index.no_departments_found")); ?></option>');
                    return;
                }

                response.data.forEach(data => {
                    const isSelected = departmentIds.includes(String(data.id));
                    $('#department_id').append(`
                    <option value="${data.id}" ${isSelected ? "selected" : ""}>
                        ${data.dept_name}
                    </option>
                `);
                });
            } catch (error) {
                $('#department_id').empty().append('<option value="select_all"><?php echo e(__("index.select_all")); ?></option>')
                    .append('<option disabled><?php echo e(__("index.error_loading_departments")); ?></option>');
            }
        };

        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_id').val() || [];
            const filteredDepartments = selectedDepartments.filter(id => id !== 'select_all');

            if (filteredDepartments.length === 0) {
                $('#employee_id').empty().append('<option value="select_all"><?php echo e(__("index.select_all")); ?></option>')
                    .append('<option disabled><?php echo e(__("index.no_employees_found")); ?></option>');
                employeeIds = []; // Clear employeeIds when no departments are selected
                return;
            }

            try {
                const response = await fetch('<?php echo e(route('admin.employees.fetchByDepartment')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    },
                    body: JSON.stringify({ department_ids: filteredDepartments }),
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Employees data:', data);

                $('#employee_id').empty().append('<option value="select_all"><?php echo e(__("index.select_all")); ?></option>');
                if (!data || data.length === 0) {
                    $('#employee_id').append('<option disabled><?php echo e(__("index.no_employees_found")); ?></option>');
                    employeeIds = []; // Clear employeeIds when no employees are found
                    return;
                }

                // Store current employee selections to preserve them
                const currentSelectedEmployees = $('#employee_id').val() || [];
                const validEmployeeIds = data.map(employee => String(employee.id));

                // Update employeeIds to include only valid employees (those in the current department selection)
                employeeIds = employeeIds.filter(id => validEmployeeIds.includes(id))
                    .concat(currentSelectedEmployees.filter(id => validEmployeeIds.includes(String(id))));

                data.forEach(employee => {
                    const isSelected = employeeIds.includes(String(employee.id));
                    $('#employee_id').append(`
                    <option value="${employee.id}" ${isSelected ? "selected" : ""}>
                        ${employee.name}
                    </option>
                `);
                });
            } catch (error) {
                $('#employee_id').empty().append('<option value="select_all"><?php echo e(__("index.select_all")); ?></option>')
                    .append('<option disabled><?php echo e(__("index.error_loading_employees")); ?></option>');
                employeeIds = []; // Clear employeeIds on error
            }
        };

        const handleSelectAll = (selectElement) => {
            const $select = $(selectElement);
            const selectedValues = $select.val() || [];

            if (selectedValues.includes('select_all')) {
                // Select all options except "Select All" and disabled options
                const allOptions = $select.find('option').map(function () {
                    return $(this).val();
                }).get().filter(val => val !== 'select_all' && val !== '' && !$(this).is(':disabled'));
                $select.val(allOptions).trigger('change');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await preloadDepartments(newBranchId);
                    await preloadEmployees();
                });
            } else {
                selectedBranchId = defaultBranchId;
            }

            if (selectedBranchId) {
                await preloadDepartments(selectedBranchId);
                await preloadEmployees();
            }

            // Attach change event listeners for "Select All" functionality
            $('#department_id').on('change', function () {
                handleSelectAll(this);
                preloadEmployees();
            });

            $('#employee_id').on('change', function () {
                handleSelectAll(this);
                // Update employeeIds to reflect current selections
                const selectedEmployees = $(this).val() || [];
                employeeIds = selectedEmployees.filter(id => id !== 'select_all');
            });
        };

        // Initialize everything
        initializeDropdowns();
    });

    document.getElementById('withEventNotification').addEventListener('click', function (event) {

        document.getElementById('eventNotification').value = 1;
    });
</script>
<?php /**PATH /var/www/hr-ky-admin1/resources/views/admin/event/common/scripts.blade.php ENDPATH**/ ?>