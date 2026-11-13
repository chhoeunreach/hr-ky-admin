<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

    $(document).ready(function () {


        $("#team_meeting").select2({
            placeholder: "{{ __('index.select_meeting_participants') }}"
        });

        $("#department_id").select2({
                placeholder: "{{ __('index.select_department') }}"
            });


        $('.meetingDate').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            disableAfter: "2089-12-30",
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        document.querySelector('[data-close-request-form]')?.addEventListener('click', event => {
            if (window.parent !== window) {
                event.preventDefault();
                window.parent.postMessage({type: 'employee-request-form-close'}, window.location.origin);
            }
        });

        $('.delete').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{__('index.delete_team_meeting_confirmation')}}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        })

        $('.removeImage').click(function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: `{{ __('index.image_delete_confirmation') }}`,
                showDenyButton: true,
                confirmButtonText: `{{__('index.yes')}}`,
                denyButtonText: `{{__('index.no')}}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            })
        });

        $('body').on('click', '.showMeetingDescription', function (event) {
            event.preventDefault();

            let url = $(this).data('href');

            $.get(url, function (data) {
                $('.meetingTitle').html('Meeting Detail');
                $('.title').text(data.data.title);
                $('.date').text(data.data.meeting_date);
                $('.time').text(data.data.time);
                $('.venue').text(data.data.venue);
                $('.publish_date').text(data.data.meeting_published_at);
                $('.description').text(data.data.description);
                $('.creator').text(data.data.creator);
                $('.image').attr('src', data.data.image);

                $('#meetingDetail').modal('show');
            })
        });

        const showJoinListMessage = function (message, cssClass) {
            const $row = $('<tr>').append(
                $('<td>').attr('colspan', 3).addClass('text-center ' + cssClass).text(message)
            );
            $('#meetingJoinList .join-list-rows').empty().append($row);
        };

        const renderJoinList = function (data) {
            const $modal = $('#meetingJoinList');
            $modal.find('.join-list-meeting-title').text(data.meeting.title);

            const summary = @json(__('index.joined_count_summary'))
                .replace(':joined', data.joined_count)
                .replace(':invited', data.invited_count);
            $modal.find('.join-list-summary').text(summary);

            const $rows = $modal.find('.join-list-rows').empty();
            if (!data.joined || data.joined.length === 0) {
                showJoinListMessage(@json(__('index.no_employee_joined')), 'text-muted');
                return;
            }

            data.joined.forEach(function (employee, index) {
                $rows.append($('<tr>').append(
                    $('<td>').text(index + 1),
                    $('<td>').text(employee.name),
                    $('<td>').text(employee.checked_in_at || '—')
                ));
            });
        };

        const loadJoinList = function (url) {
            showJoinListMessage(@json(__('index.loading')), 'text-muted');
            $.get(url).done(renderJoinList).fail(function () {
                showJoinListMessage(@json(__('index.error_loading_join_list')), 'text-danger');
            });
        };

        $('body').on('click', '.showJoinList', function (event) {
            event.preventDefault();
            $('#meetingJoinList .join-list-meeting-title').text($(this).data('title') || '');
            $('#meetingJoinList .join-list-summary').text('');
            $('#meetingJoinList').modal('show');
            loadJoinList($(this).data('href'));
        });

        $('.reset').click(function (event) {
            event.preventDefault();
            $('#team_meeting').val(null).trigger('change.select2');
            $('#department_id').val(null).trigger('change.select2');
            $('.fromDate').val('');
            $('.toDate').val('');
        });

    });

    $(document).ready(function () {

        const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
        const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};
        const branchId = "{{ $filterParameters['branch_id'] ?? null }}";

        // Ensure filter parameters are arrays and normalize to strings
        const filterDepartmentIds = Array.isArray(JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}'))
            ? JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}').map(String)
            : [String(JSON.parse('{!! json_encode($filterParameters['department_id'] ?? []) !!}'))].filter(Boolean);
        const filterEmployeeIds = Array.isArray(JSON.parse('{!! json_encode($filterParameters['employee_id'] ?? []) !!}'))
            ? JSON.parse('{!! json_encode($filterParameters['participator'] ?? []) !!}').map(String)
            : [String(JSON.parse('{!! json_encode($filterParameters['participator'] ?? []) !!}'))].filter(Boolean);

        const formDepartmentIds = {!! isset($departmentIds) ? json_encode($departmentIds) : '[]' !!}.map(String);
        const formEmployeeIds = {!! isset($participatorIds) ? json_encode($participatorIds) : '[]' !!}.map(String);

        const departmentIds = filterDepartmentIds.length > 0 ? filterDepartmentIds : formDepartmentIds;
        let employeeIds = filterEmployeeIds.length > 0 ? filterEmployeeIds : formEmployeeIds; // Make this mutable
        // Common function to load departments
        const preloadDepartments = async (selectedBranchId) => {
            $('#department_id').empty();
            if (!selectedBranchId) return;

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/departments/get-All-Departments') }}/${selectedBranchId}`,
                });

                if (!response || !response.data || response.data.length === 0) {
                    $('#department_id').append('<option disabled>{{ __("index.no_departments_found") }}</option>');
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

                $('#department_id').trigger('change.select2');
            } catch (error) {
                $('#department_id').empty().append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
            }
        };

        // Common function to load and merge employees
        const preloadEmployees = async () => {
            const selectedDepartments = $('#department_id').val() || [];
            if (selectedDepartments.length === 0) {
                $('#team_meeting').empty().append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                employeeIds = [];
                return;
            }

            // Store current employee selections before clearing
            const currentEmployeeSelections = $('#team_meeting').val() || [];

            try {
                const response = await fetch('{{ route('admin.employees.fetchByDepartment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ department_ids: selectedDepartments }),
                });

                const data = await response.json();
                $('#team_meeting').empty(); // Clear options every time

                if (!data || data.length === 0) {
                    $('#team_meeting').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    employeeIds = [];
                    return;
                }

                // Repopulate with employees, preserving valid selections
                data.forEach(employee => {
                    const employeeIdStr = String(employee.id);
                    const isSelected = currentEmployeeSelections.includes(employeeIdStr) || employeeIds.includes(employeeIdStr);
                    $('#team_meeting').append(`
                    <option value="${employee.id}" ${isSelected ? "selected" : ""}>
                        ${employee.name}
                    </option>
                `);
                });

                // Update employeeIds to reflect current selections
                employeeIds = $('#team_meeting').val() || [];
                $('#team_meeting').trigger('change.select2');
            } catch (error) {
                $('#team_meeting').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
            }
        };

        // Show only a "N selected" badge inside the select2 field instead of the full list of tags
        const updateSelectionSummary = function (element) {
            const $element = $(element);
            const control = $element.data('select2');
            if (!control) return;

            const options = $element.find('option').filter(function () {
                return this.value !== '' && !this.disabled;
            });
            const selectedCount = options.filter(':selected').length;
            const allSelected = options.length > 0 && selectedCount === options.length;
            const $rendered = control.$selection.find('.select2-selection__rendered');

            $rendered.find('.select2-selection__all-summary').remove();
            $rendered.find('.select2-selection__choice').show();

            if (selectedCount === 0) return;

            const countLabel = @json(__('index.selected_count')).replace(':count', selectedCount.toLocaleString());
            const $summary = $('<li class="select2-selection__all-summary"></li>')
                .append($('<span class="badge bg-light text-primary border rounded-pill px-2 py-1"></span>')
                    .text(allSelected ? @json(__('index.all')) + ' · ' + countLabel : countLabel))
                .css({ float: 'left', marginTop: '4px', padding: '0 8px' });

            const $search = $rendered.find('.select2-search--inline');
            if ($search.length) {
                $summary.insertBefore($search);
            } else {
                $rendered.append($summary);
            }

            $rendered.find('.select2-selection__choice').hide();
        };

        const watchSelectionSummary = function (selector) {
            $(selector).each(function () {
                const element = this;
                const control = $(element).data('select2');

                if (control) {
                    control.on('selection:update', () => updateSelectionSummary(element));
                }

                $(element).on('change', () => window.setTimeout(() => updateSelectionSummary(element), 0));
                updateSelectionSummary(element);
            });
        };

        watchSelectionSummary('#department_id, #team_meeting');

        const initializeDropdowns = async () => {
            let selectedBranchId;

            if (isAdmin) {
                selectedBranchId = $('#branch_id').val() || branchId || defaultBranchId;
                $('#branch_id').on('change', async () => {
                    const newBranchId = $('#branch_id').val();
                    await preloadDepartments(newBranchId);
                    await preloadEmployees();
                });

                if (selectedBranchId) {
                    await preloadDepartments(selectedBranchId);
                    await preloadEmployees();
                }
            } else {
                selectedBranchId = defaultBranchId;
                if (selectedBranchId) {
                    await preloadDepartments(selectedBranchId);
                    await preloadEmployees();
                }
            }

            $('#department_id').on('change', preloadEmployees);

            $('#team_meeting').on('change', function () {
                employeeIds = $(this).val() || [];
            });
        };

        // Initialize everything
        initializeDropdowns();
    });


</script>
