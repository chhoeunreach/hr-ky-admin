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

        $('#attachment').change(function(){
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            const reader = new FileReader();
            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });
            reader.readAsDataURL(file);
            $('#image-preview').removeClass('d-none')

        })

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('body').on('click', '.delete', function (event) {
            event.preventDefault();
            let title = $(this).data('title');
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.delete_confirmation') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

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




    // trainer add
    $(document).ready(function () {
        let sectionIndex = $('.training-section').length - 1;

        function handleTrainerTypeChange(element) {
            let $section = $(element).closest('.training-section');
            let selectedType = $(element).val();
            let trainerIdDropdown = $section.find('.trainer_id');

            trainerIdDropdown.empty();

            if (selectedType) {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/trainers/get-all-trainers') }}" + '/' + selectedType,
                }).done(function (response) {
                    trainerIdDropdown.append('<option selected disabled>{{ __('index.select_trainer') }}</option>');
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (data) {
                            trainerIdDropdown.append('<option value="' + data.id + '">' + data.name + '</option>');
                        });
                    } else {
                        trainerIdDropdown.append('<option disabled>No trainers available</option>');
                    }
                }).fail(function () {
                    // Handle errors
                    alert('Failed to fetch trainers. Please try again.');
                });
            }
        }

        $('#add-section-btn').on('click', function () {
            sectionIndex++;

            let newSection = `
        <div class="training-section row">
            <div class="col-lg-4 col-md-6 mb-4">
                <label for="trainer_type_${sectionIndex}" class="form-label">{{ __('index.trainer_type') }} <span style="color: red">*</span></label>
                <select class="form-select trainer_type" id="trainer_type_${sectionIndex}" name="trainer_type[${sectionIndex}]" required>
                    <option value="" selected disabled>{{ __('index.select_trainer_type') }}</option>
                    @foreach($trainerTypes as $key => $value)
            <option value="{{ $value->value }}">{{ ucfirst($value->name) }}</option>
                    @endforeach
            </select>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <label for="trainer_id_${sectionIndex}" class="form-label">{{ __('index.trainer') }} <span style="color: red">*</span></label>
                <select class="form-select trainer_id" id="trainer_id_${sectionIndex}" name="trainer_id[${sectionIndex}]" required>
                    <option value="" selected disabled>{{ __('index.select_trainer') }}</option>
                </select>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <button type="button" class="btn btn-danger remove-section-btn" style="margin-top: 30px;">x</button>
            </div>
        </div>`;
            $('#training-section-container').append(newSection);
        });

        $(document).on('click', '.remove-section-btn', function () {
            $(this).closest('.training-section').remove();
        });

        $(document).on('change', '.trainer_type', function () {
            handleTrainerTypeChange(this);
        });
    });

    document.getElementById('withNotification').addEventListener('click', function (event) {

        document.getElementById('notification').value = 1;
    });


</script>
