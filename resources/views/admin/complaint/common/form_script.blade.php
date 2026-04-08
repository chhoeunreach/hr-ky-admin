<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){



        $("#employee_id").select2({
            placeholder: "Select Employees"
        });
        $("#department_id").select2({
            placeholder: "Select Department"
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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

    tinymce.init({
        selector: '#tinymceExample',
        height: 200,
    });



    $(document).ready(function () {
        const departmentIds = {!! json_encode($departmentIds) ?? '[]' !!};
        const employeeIds = {!! json_encode($employeeIds) ?? '[]' !!};

        const preloadDepartments = async () => {
            const selectedBranchId = $('#branch_id').val();
            if (!selectedBranchId) return;

            try {
                // Only clear options if not preloaded
                if ($('#department_id option').length === 0) {
                    $('#department_id').empty();
                }

                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length == 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
                    return;
                }

                response.data.forEach(data => {
                    const isSelected = departmentIds.includes(data.id);
                    if (!$('#department_id option[value="' + data.id + '"]').length) {
                        $('#department_id').append(`
                        <option value="${data.id}" ${isSelected ? "selected" : ""}>
                            ${data.dept_name}
                        </option>
                    `);
                    }
                });

                $('#department_id').trigger('change');
            } catch (error) {
                console.error('Error loading departments:', error);
                $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_id').val() || [];
            const previouslySelectedEmployees = $('#employee_id').val() || []; // Keep track of currently selected employees

            if (selectedDepartments.length === 0) {
                if ($('#employee_id option').length === 0) {
                    $('#employee_id').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                }
                return;
            }

            try {
                const response = await fetch('{{ route('admin.users.fetchByDepartment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ department_ids: selectedDepartments }),
                });

                const data = await response.json();

                if (!data || data.length === 0) {
                    $('#employee_id').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    return;
                }

                const currentOptions = Array.from($('#employee_id option')).map(option => option.value);

                // Append new employees from fetched data
                data.forEach(employee => {
                    const isSelected = employeeIds.includes(employee.id) || previouslySelectedEmployees.includes(employee.id.toString());
                    if (!currentOptions.includes(employee.id.toString())) {
                        $('#employee_id').append(`
                        <option value="${employee.id}" ${isSelected ? "selected" : ""}>
                            ${employee.name}
                        </option>
                    `);
                    }
                });

                // Remove employees that are not in the fetched data
                $('#employee_id option').each(function () {
                    const employeeId = $(this).val();
                    if (!data.find(employee => employee.id.toString() === employeeId)) {
                        $(this).remove();
                    }
                });
            } catch (error) {
                console.error('Error fetching employees:', error);
                $('#employee_id').empty().append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        // Ensure data is preloaded on page load
        preloadDepartments().then(preloadEmployees);

        // Update departments and employees when branch changes
        $('#branch_id').change(function () {
            preloadDepartments().then(preloadEmployees);
        });

        // Update employees when departments change
        $('#department_id').change(preloadEmployees);
    });


    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });

</script>
