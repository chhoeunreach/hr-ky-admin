<script>
    $(document).ready(function () {

        $('#notice').select2({
            placeholder:"{{ __('index.select_notice_receiver') }}"
        });

        $('#branch_id').select2();


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.toggleStatus').change(function (event) {
            event.preventDefault();
            let status = $(this).prop('checked') === true ? 1 : 0;
            let href = $(this).attr('href');
            Swal.fire({
                title: '@lang('index.confirm_change_notice_status')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
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

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.confirm_delete_notice')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.sendNotice').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '@lang('index.confirm_send_notice')',
                showDenyButton: true,
                confirmButtonText: `@lang('index.yes')`,
                denyButtonText: `@lang('index.no')`,
                padding:'10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('body').on('click', '#showNoticeDescription', function (event) {
            event.preventDefault();
            let url = $(this).data('href');
            $.get(url, function (data) {


                $('.modal-title').html(@json(__('index.notice_detail_modal_title', ['title' => '']))+': ' + data.data.title );
                $('#description').text((data.data.description));
                $('#addslider').modal('show');
            })
        }).trigger("change");

        $('input[type="checkbox"]').click(function(){
            if($(this).is(":checked")){
                $('#notice').select2('destroy').find('option').prop('selected', 'selected').end().select2();
            }
            else if($(this).is(":not(:checked)")){
                $('#notice').select2('destroy').find('option').prop('selected', false).end().select2();
            }
        });

        $('.reset').click(function(event){
            event.preventDefault();
            $('#notice_receiver').val('');
            $('.fromDate').val('');
            $('.toDate').val('');
        });

        $('#fromDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#toDate').nepaliDatePicker({
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
        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";

        // Ensure filter parameters are arrays and normalize to strings
        const filterEmployeeIds = JSON.parse('{!! json_encode($filterParameters['notice_receiver'] ?? []) !!}');
        let receiverUserIds = [];

        // Handle both array and non-array filter parameters
        if (filterEmployeeIds && filterEmployeeIds.length) {
            if (Array.isArray(filterEmployeeIds)) {
                receiverUserIds = filterEmployeeIds.map(String);
            } else {
                receiverUserIds = [String(filterEmployeeIds)].filter(Boolean);
            }
        } else if ({!! isset($noticeDetail) && !empty($receiverUserIds) ? 'true' : 'false' !!}) {
            // Use form employee IDs if no filter params
            receiverUserIds = {!! isset($noticeDetail) && !empty($receiverUserIds) ? json_encode($receiverUserIds) : '[]' !!}.map(String);
        }

        console.log('Initial receivers: ', receiverUserIds);

        const loadEmployees = async (selectedBranchId) => {
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/get-branch-employee-data') }}/${selectedBranchId}`,
                });

                // Clear existing options
                $('#notice').empty();

                if (response.users && response.users.length > 0) {
                    // Populate employees
                    response.users.forEach(user => {
                        const isSelected = receiverUserIds.includes(String(user.id));
                        $('#notice').append(
                            `<option value="${user.id}" ${isSelected ? 'selected' : ''}>${user.name}</option>`
                        );
                        console.log(`User ${user.id} (${user.name}): ${isSelected ? 'selected' : 'not selected'}`);
                    });
                } else {
                    $('#notice').append('<option disabled>{{ __("index.employee_not_found") }}</option>');
                }

            } catch (error) {
                console.error('Error loading data:', error);
                $('#notice').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                // For filter form and edit form, use provided branch ID or default
                selectedBranchId = branchId || $('#branch_id').val() || defaultBranchId;

                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();

                    // Only reset receiverUserIds if not in filter mode
                    if (!window.location.href.includes('filter')) {
                        receiverUserIds = [];
                    }

                    await loadEmployees(newBranchId);
                });

                if (selectedBranchId) {
                    await loadEmployees(selectedBranchId);
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await loadEmployees(selectedBranchId);
                }
            }
        };

        // Handle "All Employees" checkbox
        $('#checkbox').on('change', function() {
            if(this.checked) {
                $('#notice option').prop('selected', true);
            } else {
                $('#notice option').prop('selected', false);
            }
            $('#notice').trigger('change');
        });

        // Initialize everything
        initializeDropdowns();
    });
</script>
