<script src="{{asset('assets/vendors/tinymce/tinymce.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce.js')}}"></script>

<script>
    $('document').ready(function(){

        $("#departments").select2({
        });
        $("#role").select2({
        });




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
                    url: '/admin/leave-approval/get-users-by-role',
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

</script>
