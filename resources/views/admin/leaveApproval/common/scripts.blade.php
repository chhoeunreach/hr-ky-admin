<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#departments").select2();
        $("#branch_id").select2();
        $("#role").select2();
        $("#related").select2();




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

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: `{{ __('index.change_status_confirm') }}`,
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }else if (result.isDenied) {
                    (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                }
            })
        })

    });


</script>
{{--<script src="https://code.jquery.com/jquery-3.6.0.js"></script>--}}
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    $(function () {
        $("#sortable").sortable();

        $('#add-approver').on('click', function () {
            let $newRow = $('#sortable .approver-row').first().clone();

            $newRow.find('select').each(function () {
                $(this).val('');
                $(this).removeAttr('required');
            });
            $newRow.find('.employee-wrapper').hide();

            const $lastColumn = $newRow.find('.col-md-2.d-flex');
            if (!$lastColumn.find('.remove-approver').length) {
                $lastColumn.append('<button type="button" class="btn btn-danger btn-sm remove-approver">x</button>');
            }

            $('#sortable').append($newRow);
        });

        $(document).on('click', '.remove-approver', function () {
            $(this).closest('li').remove();
        });

        function toggleEmployeeDropdown(approverSelect) {
            let $row = $(approverSelect).closest('.row');
            let $employeeWrapper = $row.find('.employee-wrapper');

            if ($(approverSelect).val() === 'specific_personnel') {
                $employeeWrapper.show();
                $row.find('.staff-select, .user-dropdown').attr('required', true);
            } else {
                $employeeWrapper.hide();
                $row.find('.staff-select, .user-dropdown').removeAttr('required');
                $row.find('.staff-select, .user-dropdown').val(null); // Reset both dropdowns
            }
        }



        $('.approver-select').each(function () {
            toggleEmployeeDropdown(this);
        });

        $(document).on('change', '.approver-select', function () {
            toggleEmployeeDropdown(this);
        });


        $(document).on('change', '.staff-select', function () {
            let $row = $(this).closest('.row');
            let $userDropdown = $row.find('.user-dropdown'); // Use class selector now
            let roleId = $(this).val();

            if (roleId) {
                $userDropdown.html('<option selected disabled>{{ __("index.select_employee") }}</option>');

                // Fetch users via AJAX
                $.ajax({
                    url: '/admin/leave-approval/get-employees-by-role',
                    method: 'GET',
                    data: { role_id: roleId },
                    success: function (response) {
                        if (response.success && response.data.length > 0) {
                            response.data.forEach(function (user) {
                                $userDropdown.append(
                                    `<option value="${user.id}" ${user.selected ? 'selected' : ''}>${user.name}</option>`
                                );
                            });
                        }
                    },
                    error: function () {
                        console.error('Failed to fetch users for the selected role.');
                    },
                });
            } else {
                $userDropdown.html('<option selected disabled>{{ __("index.select_employee") }}</option>');
            }
        });



    });

    $(document).ready(function () {
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";
        const filterDepartmentIds = {!! json_encode($filterParameters['department_id'] ?? []) !!} || [];
        const formDepartmentIds = {!! isset($departmentId) ? json_encode($departmentId) : '[]' !!};
        const departmentIds = filterDepartmentIds.length > 0 ? filterDepartmentIds.map(String) : formDepartmentIds.map(String);
        let leaveTypeId = "{{ $leaveApprovalDetail->leave_type_id ?? $filterParameters['leave_type_id'] ?? '' }}";

        const loadLeaveTypeAndDepartments = async (selectedBranchId) => {
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-leave-data') }}/${selectedBranchId}`,
                });
                console.log('AJAX response:', response);

                $('#related').empty();
                $('#departments').empty().append('<option value="all">{{ __("index.select_all") }}</option>');

                if (!leaveTypeId) {
                    $('#related').append('<option value="" disabled selected>{{ __('index.select_leave_type') }}</option>');
                }
                if (response.types && response.types.length > 0) {
                    response.types.forEach(type => {
                        const isSelected = String(type.id) === String(leaveTypeId);
                        console.log(`Leave Type ${type.id}: isSelected = ${isSelected}`);
                        $('#related').append(
                            `<option value="${type.id}" ${isSelected ? 'selected' : ''}>${type.name}</option>`
                        );
                    });
                } else {
                    $('#related').append('<option disabled>{{ __("index.leave_type_not_found") }}</option>');
                }

                if (response.departments && response.departments.length > 0) {
                    response.departments.forEach(department => {
                        const isSelected = departmentIds.includes(String(department.id));
                        $('#departments').append(
                            `<option value="${department.id}" ${isSelected ? "selected" : ""}>${department.dept_name}</option>`
                        );
                    });
                } else {
                    $('#departments').append('<option disabled>{{ __("index.no_department_found") }}</option>');
                }

                // Initialize Select2 again to refresh the dropdown
                $('#departments').select2();

                // Handle "Select All" functionality
                $('#departments').on('select2:select', function (e) {
                    const data = e.params.data;
                    if (data.id === 'all') {
                        const allOptions = $('#departments option').not('[value="all"]').not(':disabled');
                        allOptions.prop('selected', true);
                        $('#departments option[value="all"]').prop('selected', false); // Deselect "Select All"
                        $('#departments').trigger('change'); // Trigger change to update Select2
                    } else {
                        // Check if all non-disabled options (excluding "Select All") are selected
                        const allOptions = $('#departments option').not('[value="all"]').not(':disabled');
                        const selectedOptions = $('#departments option:selected').not('[value="all"]').not(':disabled');
                        if (allOptions.length === selectedOptions.length) {
                            $('#departments option[value="all"]').prop('selected', false); // Ensure "Select All" is not selected
                        }
                        $('#departments').trigger('change');
                    }
                });

                $('#departments').on('select2:unselect', function (e) {
                    const data = e.params.data;
                    if (data.id === 'all') {
                        $('#departments option').prop('selected', false);
                        $('#departments').trigger('change'); // Trigger change to update Select2
                    } else {
                        // Always deselect "Select All" when any individual option is unselected
                        $('#departments option[value="all"]').prop('selected', false);
                        $('#departments').trigger('change'); // Trigger change to update Select2
                    }
                });

            } catch (error) {
                $('#related').append('<option disabled>{{ __("index.error_loading_leave_types") }}</option>');
                $('#departments').append('<option disabled>{{ __("index.error_loading_department") }}</option>');
                $('#departments').select2(); // Reinitialize Select2 in case of error
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                if (branchId) {
                    $('#branch_id').val(branchId); // Force set branch_id
                }
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await loadLeaveTypeAndDepartments(newBranchId);
                });
                if (selectedBranchId) {
                    await loadLeaveTypeAndDepartments(selectedBranchId);
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await loadLeaveTypeAndDepartments(selectedBranchId);
                }
            }
        };

        initializeDropdowns();
    });
</script>
