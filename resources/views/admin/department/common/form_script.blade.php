

<script>
    $('document').ready(function(){



        $("#branch_id").select2({
            placeholder: "{{ __('index.select_branch') }}"
        });
        $("#dept_head_id").select2({
            placeholder: "{{ __('index.select_department_head') }}"
        });



        const loadUsers = async () => {
            const selectedBranchValue = $('#branch_id').val();
            const selectedBranchIds = Array.isArray(selectedBranchValue)
                ? selectedBranchValue.filter(Boolean)
                : (selectedBranchValue ? [selectedBranchValue] : []);

            // Get existing values (for edit forms or old input)
            let employeeId = "{{ $departmentsDetail->dept_head_id ?? old('dept_head_id') ?? '' }}"; // Array of leader IDs

            $('#dept_head_id').empty();

            if (selectedBranchIds.length !== 1) {
                $('#dept_head_id')
                    .append('<option value="" disabled selected>{{ __('index.select_department_head') }}</option>')
                    .val('')
                    .trigger('change.select2');
                return;
            }

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/employees/get-branch-employee') }}/${selectedBranchIds[0]}`,
                });

                // Clear existing options
                $('#dept_head_id').empty();


                if (!employeeId) {
                    $('#dept_head_id').append('<option value="" disabled selected>{{ __('index.select_department_head') }}</option>');
                }

                // Populate project leaders (multi-select)
                if (response.employee && response.employee.length > 0) {
                    response.employee.forEach(user => {
                        $('#dept_head_id').append(
                            `<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>${user.name}</option>`
                        );
                    });
                } else {
                    $('#dept_head_id').append('<option disabled>{{ __("index.employee_not_found") }}</option>');
                }

                $('#dept_head_id').trigger('change.select2');

            } catch (error) {
                console.error('Error loading data:', error);
                $('#dept_head_id').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                $('#dept_head_id').trigger('change.select2');
            }
        };

        // Load data when branch is selected
        $('#branch_id').change(loadUsers).trigger('change'); // Corrected selector and trigger
    });

</script>
