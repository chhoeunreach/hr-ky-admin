<script>

    $(document).ready(function () {



        $("#employee").select2({
            placeholder: "Select Employees"
        });
        $("#department").select2({
            placeholder: "Select Department"
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
                title: `{{__('index.delete_event_confirmation')}}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
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
                title: `{{ __('index.image_delete_confirmation') }}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('#start_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#end_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });



    });
    document.addEventListener('DOMContentLoaded', function () {
        // Department checkbox logic
        const departmentCheckbox = document.getElementById('department_checkbox');

        departmentCheckbox.addEventListener('change', function () {
            if(this.checked){
                $('#department').select2('destroy').find('option').prop('selected', 'selected').end().select2();
            }
            else{
                $('#department').select2('destroy').find('option').prop('selected', false).end().select2();
            }
        });

        // Employee checkbox logic
        const employeeCheckbox = document.getElementById('employee_checkbox');

        employeeCheckbox.addEventListener('change', function () {
            if (this.checked) {
                $('#employee').select2('destroy').find('option').prop('selected', 'selected').end().select2();
            } else {
                $('#employee').select2('destroy').find('option').prop('selected', false).end().select2();
            }
        });
    });

    document.getElementById('withEventNotification').addEventListener('click', function (event) {

        document.getElementById('eventNotification').value = 1;
    });

    document.addEventListener('DOMContentLoaded', () => {
        const departmentSelect = document.getElementById('department');
        const employeeSelect = document.getElementById('employee');

        if (!departmentSelect || !employeeSelect) {
            console.error("One or more select elements are missing.");
            return;
        }


    });


    $('#department').on('change', function () {
        let selectedDepartments = $(this).val();
        const employeeIds = {!! json_encode($userIds) ?? '[]' !!};

        // AJAX call to fetch employees
        fetch('{{ route('admin.users.fetchByDepartment') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ department_ids: selectedDepartments })
        })
            .then(response => response.json())
            .then(data => {
                const employeeDropdown = document.getElementById('employee');
                const currentOptions = Array.from(employeeDropdown.options);

                // Map to store current employees in the dropdown
                const existingEmployees = new Map();
                currentOptions.forEach(option => {
                    if (option.value) {
                        existingEmployees.set(parseInt(option.value), option);
                    }
                });

                // Clear employees from departments that were removed
                if (!selectedDepartments || selectedDepartments.length === 0) {
                    employeeDropdown.innerHTML = '<option value="">@lang('index.all_employees')</option>';
                } else {
                    const fetchedIds = data.map(employee => employee.id);
                    for (const [id, option] of existingEmployees.entries()) {
                        if (!fetchedIds.includes(id)) {
                            option.remove();
                            existingEmployees.delete(id);
                        }
                    }
                }

                // Add new employees
                data.forEach(employee => {
                    if (!existingEmployees.has(employee.id)) {
                        const option = document.createElement('option');
                        option.value = employee.id;
                        option.textContent = employee.name;

                        // Preselect employees if $userIds exist
                        if (employeeIds.includes(employee.id)) {
                            option.selected = true;
                        }

                        employeeDropdown.appendChild(option);
                        existingEmployees.set(employee.id, option);
                    }
                });
            })
            .catch(error => console.error('Error fetching employees:', error));
    });

    {{--$('#department').on('change', function () {--}}
    {{--    let selectedDepartments = $(this).val();--}}
    {{--    const employeeIds = {!! json_encode($userIds) ?? '[]' !!};--}}

    {{--    // AJAX call to fetch employees--}}
    {{--    fetch('{{ route('admin.users.fetchByDepartment') }}', {--}}
    {{--        method: 'POST',--}}
    {{--        headers: {--}}
    {{--            'Content-Type': 'application/json',--}}
    {{--            'X-CSRF-TOKEN': '{{ csrf_token() }}'--}}
    {{--        },--}}
    {{--        body: JSON.stringify({ department_ids: selectedDepartments })--}}
    {{--    })--}}
    {{--        .then(response => response.json())--}}
    {{--        .then(data => {--}}
    {{--            const employeeDropdown = document.getElementById('employee');--}}
    {{--            employeeDropdown.innerHTML = '<option value="">@lang('index.all_employees')</option>';--}}

    {{--            // Populate the employee dropdown--}}
    {{--            data.forEach(employee => {--}}
    {{--                const option = document.createElement('option');--}}
    {{--                option.value = employee.id;--}}
    {{--                option.textContent = employee.name;--}}

    {{--                // Preselect employees if $userIds exist--}}
    {{--                if (employeeIds.includes(employee.id)) {--}}
    {{--                    option.selected = true;--}}
    {{--                }--}}

    {{--                employeeDropdown.appendChild(option);--}}
    {{--            });--}}

    {{--        })--}}
    {{--        .catch(error => console.error('Error fetching employees:', error));--}}

    {{--});--}}
</script>
