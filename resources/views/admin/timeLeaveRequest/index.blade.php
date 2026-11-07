@php use App\Models\LeaveRequestMaster; @endphp
@php use App\Enum\LeaveStatusEnum; @endphp
@php use App\Helpers\AppHelper; @endphp
@extends('layouts.master')

@section('title',__('index.time_leave_request'))

@section('action',__('index.lists'))

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.bootstrap5.min.css">
    <style>
        .time-leave-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        #timeLeaveTable_wrapper .time-leave-list-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        #timeLeaveTable_wrapper .dt-length, #timeLeaveTable_wrapper .dt-search { display: flex; align-items: center; gap: .4rem; }
        #timeLeaveTable_wrapper .dt-length label, #timeLeaveTable_wrapper .dt-search label { margin: 0; }
        #timeLeaveTable_wrapper .dt-length select { min-width: 85px; margin: 0 .3rem; }
        #timeLeaveTable_wrapper .dt-buttons { display: flex; justify-content: center; gap: .5rem; flex-wrap: wrap; margin: 0 auto; }
        #timeLeaveTable_wrapper .dt-buttons > .btn, #timeLeaveTable_wrapper .dt-button-collection .btn { border: 1px solid #a9b3c7; border-radius: .6rem !important; background: transparent; color: #7887a2; font-size: .85rem; font-weight: 500; padding: .35rem .65rem; box-shadow: none; }
        #timeLeaveTable_wrapper .dt-buttons > .btn:hover, #timeLeaveTable_wrapper .dt-buttons > .btn:focus-visible { background: #f0f4fa; color: #435a80; }
        #timeLeaveTable_wrapper .dt-button-collection .active { background: #edf2fa; color: #435a80; }
        #timeLeaveTable_wrapper .dt-search input { width: 205px; margin: 0; }
        #timeLeaveTable_wrapper .time-leave-table-footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding-top: 1rem; }
        #timeLeaveTable_wrapper .dt-info { padding: 0; }
        #timeLeaveTable_wrapper .dt-paging .pagination { margin: 0; }
        #timeLeaveTable_wrapper table { width: 100% !important; }
        #timeLeaveTable_wrapper th.dt-orderable-asc, #timeLeaveTable_wrapper th.dt-orderable-desc { cursor: pointer; }
        .time-leave-toolbar-actions { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
        .time-leave-toolbar .btn { display: inline-flex; align-items: center; gap: .5rem; border-radius: .6rem; }
        .time-leave-toolbar svg { width: 16px; height: 16px; }
        .time-leave-filter-toggle .filter-chevron { transition: transform .2s ease; }
        .time-leave-filter-toggle[aria-expanded="true"] .filter-chevron { transform: rotate(180deg); }
        .time-leave-filter-panel { border-top: 1px solid var(--bs-border-color, #e9ecef); }
        .time-leave-filter-panel .form-label { font-weight: 500; margin-bottom: .5rem; }
        .time-leave-filter-panel .select2-container { width: 100% !important; }
        .time-leave-filter-actions { display: flex; justify-content: flex-end; gap: .65rem; border-top: 1px solid var(--bs-border-color, #e9ecef); margin-top: 1.25rem; padding-top: 1rem; }
        @media (max-width: 575.98px) {
            .time-leave-toolbar-actions { width: 100%; }
            #timeLeaveTable_wrapper .dt-buttons { width: 100%; order: 3; justify-content: flex-start; }
            #timeLeaveTable_wrapper .dt-search { flex-grow: 1; justify-content: flex-end; }
            #timeLeaveTable_wrapper .dt-search input { width: 100%; max-width: 205px; }
            .time-leave-toolbar-actions .btn { flex-grow: 1; justify-content: center; }
        }
        @media (prefers-reduced-motion: reduce) {
            .time-leave-filter-toggle .filter-chevron { transition: none; }
        }
    </style>
@endsection

@section('main-content')
        <?php
        if (AppHelper::ifDateInBsEnabled()) {
            $filterData['min_year'] = '2076';
            $filterData['max_year'] = '2089';
            $filterData['month'] = 'np';
        } else {
            $filterData['min_year'] = '2020';
            $filterData['max_year'] = '2033';
            $filterData['month'] = 'en';
        }
        ?>

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.employees.profile.partials.request-saved-next-step', ['savedRequestType' => 'time_leave', 'printPermission' => 'employee.time_leave_form.print', 'printTab' => 'time-leave-letter', 'printRequestField' => 'time_leave_request_id'])

        @include('admin.timeLeaveRequest.common.breadcrumb')

        @php
            $activeFilterCount = collect(request()->only(['branch_id', 'department_id', 'requested_by', 'year', 'month', 'status']))
                ->filter(fn ($value) => filled($value))->count();
        @endphp

        <div class="card mb-4">
            <div class="card-body time-leave-toolbar">
                <div>
                    <h6 class="card-title mb-1">{{ __('index.time_leave_list') }}</h6>
                    <span class="text-muted small" id="timeLeaveSummary" aria-live="polite">{{ $timeLeaves->count() }} {{ __('index.entries') }}</span>
                </div>
                <div class="time-leave-toolbar-actions">
                    <button type="button" class="btn btn-outline-primary time-leave-filter-toggle collapsed"
                            data-bs-toggle="collapse" data-bs-target="#timeLeaveFilters"
                            aria-expanded="false" aria-controls="timeLeaveFilters">
                        <i data-feather="filter" aria-hidden="true"></i>
                        {{ __('index.filters') }}
                        @if($activeFilterCount)
                            <span class="badge bg-primary">{{ $activeFilterCount }}</span>
                        @endif
                        <i data-feather="chevron-down" class="filter-chevron" aria-hidden="true"></i>
                    </button>
                    @can('create_time_leave_request')
                        <a class="btn btn-primary" href="{{ route('admin.time-leave-request.create') }}">
                            <i data-feather="plus" aria-hidden="true"></i>{{ __('index.create_time_leave_request') }}
                        </a>
                    @endcan
                </div>
            </div>
            <div id="timeLeaveFilters" class="collapse time-leave-filter-panel">
                <form id="timeLeaveFilterForm" class="forms-sample card-body" action="{{ route('admin.time-leave-request.index') }}" method="get">
                    <input type="hidden" name="search" value="{{ $filterParameters['search'] }}">
                    <input type="hidden" name="per_page" value="{{ $filterParameters['per_page'] }}">
                    <h6 class="mb-3">{{ __('index.time_leave_request_filter') }}</h6>
                    <div class="row g-3">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-4 col-md-6">
                            <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option value="">{{ __('index.select_branch') }}</option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option
                                            {{ $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <!-- Departments Field -->
                    <div class="col-lg-4 col-md-6">
                        <label for="department_id" class="form-label">{{ __('index.department') }}</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">{{ __('index.select_department') }}</option>

                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="requestedBy" class="form-label">{{ __('index.employee') }}</label>
                        <select class="form-select" id="requestedBy" name="requested_by">
                            <option value="">{{ __('index.select_employee') }}</option>

                        </select>

                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="year" class="form-label">{{ __('index.year') }}</label>
                        <input type="number" min="{{ $filterData['min_year']}}"
                               max="{{ $filterData['max_year']}}" step="1"
                               placeholder="{{ __('index.leave_requested_year') }} : {{$filterData['min_year']}}"
                               id="year"
                               name="year" value="{{$filterParameters['year']}}"
                               class="form-control">
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="month" class="form-label">{{ __('index.month') }}</label>
                        <select class="form-select" name="month" id="month">
                            <option
                                value="" {{!isset($filterParameters['month']) ? 'selected': ''}} >{{ __('index.all_month') }}</option>
                            @foreach($months as $key => $value)
                                <option
                                    value="{{$key}}" {{ (isset($filterParameters['month']) && $key == $filterParameters['month'] ) ?'selected':'' }} >
                                    {{$value[$filterData['month']]}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="filter_status" class="form-label">{{ __('index.status') }}</label>
                        <select class="form-select" name="status" id="filter_status">
                            <option
                                value="" {{!isset($filterParameters['status']) ? 'selected': ''}} >{{ __('index.all_status') }}</option>
                            @foreach(LeaveRequestMaster::STATUS as  $value)
                                <option
                                    value="{{$value}}" {{ (isset($filterParameters['status']) && $value == $filterParameters['status'] ) ?'selected':'' }} > {{ __('index.' . $value) }} </option>
                            @endforeach
                        </select>
                    </div>

                    </div>
                    <div class="time-leave-filter-actions">
                        <a class="btn btn-outline-secondary" href="{{ route('admin.time-leave-request.index') }}">{{ __('index.reset') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('index.filter') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="time-leave-table-container">
                    <table id="timeLeaveTable" class="table table-hover align-middle w-100">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.leave_date') }}</th>
                            <th>{{ __('index.start_time') }}</th>
                            <th>{{ __('index.end_time') }}</th>
                            <th>{{ __('index.requested_by') }}</th>
                            @can('time_leave_list')
                                <th class="text-center time-leave-reason">{{ __('index.reason') }}</th>
                            @endcan
                            @can('update_time_leave')
                                <th class="text-center">{{ __('index.status') }}</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $color = [
                                LeaveStatusEnum::approved->value => 'success',
                                LeaveStatusEnum::rejected->value => 'danger',
                                LeaveStatusEnum::pending->value => 'secondary',
                                LeaveStatusEnum::cancelled->value => 'danger'
                            ];

                            ?>
                        @foreach($timeLeaves as $key => $value)
                            <tr>
                                <td data-order="{{ $loop->iteration }}" data-export="{{ $loop->iteration }}">{{ $loop->iteration }} @include('admin.employees.profile.partials.request-print-link', ['requestRecord' => $value, 'printPermission' => 'employee.time_leave_form.print', 'printTab' => 'time-leave-letter', 'printRequestField' => 'time_leave_request_id'])</td>
                                <td data-order="{{ $value->issue_date }}">{{ AppHelper::timeLeaverequestDate($value->issue_date) }}</td>
                                <td data-order="{{ $value->start_time }}">{{ AppHelper::convertLeaveTimeFormat($value->start_time) }}</td>
                                <td data-order="{{ $value->end_time }}">{{ AppHelper::convertLeaveTimeFormat($value->end_time) }}</td>
                                <td data-search="{{ $value->leaveRequestedBy ? implode(' ', [$value->leaveRequestedBy->name, $value->leaveRequestedBy->username, $value->leaveRequestedBy->employee_code]) : __('index.not_available') }}">{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : __('index.not_available')}} </td>

                                @can('time_leave_list')
                                    <td class="text-center" data-search="{{ $value->reasons }}" data-export="{{ $value->reasons }}">
                                        <a href="#" class="showTimeLeaveReason"
                                           data-href="{{ route('admin.time-leave-request.show', $value->id) }}"
                                           title="{{ __('index.show_leave_reason') }}">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan

                                @can('update_time_leave')
                                    <td class="text-center" data-order="{{ $value->status }}" data-export="{{ __('index.' . $value->status) }}">
                                        <a href=""
                                           id="leaveRequestUpdate"
                                           data-href="{{route('admin.time-leave-request.update-status',$value->id)}}"
                                           data-status="{{$value->status}}"
                                           data-remark="{{$value->admin_remark}}"
                                        >
                                            <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                {{ __('index.' . $value->status) }}
                                            </button>
                                        </a>
                                    </td>
                            @endcan
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>

    @include('admin.timeLeaveRequest.show')
    @include('admin.timeLeaveRequest.common.form-model')
@endsection

@section('scripts')
    @include('admin.timeLeaveRequest.common.scripts')
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.20/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.20/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.colVis.min.js"></script>
    <script src="{{ asset('assets/js/time-leave-table.js') }}?v={{ filemtime(public_path('assets/js/time-leave-table.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.showTimeLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('referral').innerText = leaveRequest.name || @json(__('index.admin'));
                                document.getElementById('description').innerText = leaveRequest.reasons || @json(__('index.not_available'));
                                document.getElementById('adminRemark').innerText = leaveRequest.admin_remark || @json(__('index.not_available'));

                                const modal = new bootstrap.Modal(document.getElementById('addslider'));
                                modal.show();
                            } else {
                                console.error('Data format is incorrect or data is missing:', data);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });

        $(document).ready(function () {
            window.initTimeLeaveTable({
                pageLength: @json($filterParameters['per_page']),
                search: @json($filterParameters['search'] ?? ''),
                title: @json(__('index.time_leave_list')),
                show: @json(__('index.show')),
                entries: @json(__('index.entries')),
                searchLabel: @json(__('index.search')),
                empty: @json(__('index.no_records_found')),
                info: @json(__('index.showing') . ' _START_ ' . __('index.to') . ' _END_ ' . __('index.of') . ' _TOTAL_ ' . __('index.entries')),
            });

            // Pre-selected values from $filterParameters
            const branchId = @json($filterParameters['branch_id']);
            let departmentId = @json($filterParameters['department_id']);
            let employeeId = @json($filterParameters['requested_by']);

            const isAdmin = {{ auth('admin')->check() ? 'true' : 'false' }};
            const defaultBranchId = {{ auth()->user()->branch_id ?? 'null' }};

            const loadDepartments = async (selectedBranchId) => {
                $('#department_id').empty().append($('<option>', { value: '', text: @json(__('index.select_department')) })).trigger('change.select2');
                $('#requestedBy').empty().append($('<option>', { value: '', text: @json(__('index.select_employee')) })).trigger('change.select2');
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
                        $('#department_id').append(`<option value="${data.id}" ${data.id == departmentId ? 'selected' : ''}>${data.dept_name}</option>`);
                    });

                    // If departmentId is pre-selected, load employees
                    if (departmentId) {
                        await loadEmployees();
                    }
                } catch (error) {
                    console.error('Error loading departments:', error);
                    $('#department_id').append('<option disabled>{{ __("index.error_loading_departments") }}</option>');
                }
            };

            const loadEmployees = async () => {
                const selectedDepartmentId = $('#department_id').val();
                $('#requestedBy').empty().append($('<option>', { value: '', text: @json(__('index.select_employee')) })).trigger('change.select2');
                if (!selectedDepartmentId) return;

                try {

                    const response = await fetch(`{{ url('admin/employees/get-all-employees') }}/${selectedDepartmentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });

                    const data = await response.json();

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(user => {
                            const employeeLabel = user.username ? `${user.name} (${user.username})` : user.name;
                            $('#requestedBy').append(`<option value="${user.id}" ${user.id == employeeId ? 'selected' : ''}>${employeeLabel}</option>`);
                        });
                    } else {
                        $('#requestedBy').append('<option disabled>{{ __("index.no_employees_found") }}</option>');
                    }
                } catch (error) {
                    console.error('Error loading employees:', error);
                    $('#requestedBy').append('<option disabled>{{ __("index.error_loading_employees") }}</option>');
                }
            };

            // Load departments and employees based on pre-selected branch_id
            const initializeDropdowns = async () => {
                let selectedBranchId;

                if (isAdmin) {
                    selectedBranchId = $('#branch_id').val() || branchId; // Use DOM value or fallback to filterParameters
                    $('#branch_id').change(() => {
                        departmentId = null;
                        employeeId = null;
                        loadDepartments($('#branch_id').val());
                    }); // Bind change event
                } else {
                    selectedBranchId = defaultBranchId; // Non-admin users use their default branch
                }

                if (selectedBranchId) {
                    await loadDepartments(selectedBranchId);
                }
            };

            // Call initialization
            initializeDropdowns();

            // Bind change event for department_id
            $('#department_id').change(() => {
                employeeId = null;
                loadEmployees();
            });
        });

    </script>

@endsection





