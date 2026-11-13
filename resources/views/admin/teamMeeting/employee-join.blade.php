
@extends('layouts.master')

@section('title',__('index.employee_join'))

@section('action',__('index.employee_join_status'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.dataTables.min.css') }}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.teamMeeting.common.breadcrumb')

        @php
            $employeeJoinDefaultPageSize = (int) getRecordPerPage();
            $requestedPageSize = request('per_page');
            $employeeJoinPageLength = $requestedPageSize === 'all' ? -1 : (int) ($requestedPageSize ?: $employeeJoinDefaultPageSize);
            if ($employeeJoinPageLength === 0) {
                $employeeJoinPageLength = $employeeJoinDefaultPageSize;
            }
            $employeeJoinPageSizes = array_values(array_unique(array_merge([10, 15, 25, 50, 100, 200, 500, 1000], [$employeeJoinDefaultPageSize])));
            sort($employeeJoinPageSizes);
            $employeeJoinPageSizes[] = 'all';
        @endphp

        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'employeeJoinFilterCollapse', 'directoryExpanded' => false])
            <div id="employeeJoinFilterCollapse" class="collapse">
                <form class="forms-sample directory-filter-form" action="{{route('admin.team-meetings.employee-join')}}" id="employeeJoinFilterForm" method="get">
                    <input type="hidden" id="employeeJoinSearch" name="search" value="">
                    <div class="row g-3 align-items-end">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-xl-3 col-md-6">
                                <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                                <select class="form-control" id="branch_id" name="branch_id">
                                    <option selected disabled>{{ __('index.select_branch') }}</option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $key => $branch)
                                            <option value="{{$branch->id}}"
                                                {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                                {{ucfirst($branch->name)}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @endif
                        <div class="col-xl-3 col-md-6">
                            <label for="department_id" class="form-label">{{ __('index.department') }}</label>
                            <select class="form-control" id="department_id" multiple name="department_id[]"
                                    data-selection-popup="1" data-placeholder="{{ __('index.select_department') }}">
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <label for="team_meeting" class="form-label">{{ __('index.participators') }}</label>
                            <select class="form-control" multiple name="participator[]" id="team_meeting"
                                    data-selection-popup="1" data-placeholder="{{ __('index.select_meeting_participants') }}">
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <label for="attendance_status" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-control" id="attendance_status" name="attendance_status">
                                <option value="">{{ __('index.select_status') }}</option>
                                <option value="joined" @selected(($filterParameters['attendance_status'] ?? null) === 'joined')>{{ __('index.meeting_joined') }}</option>
                                <option value="pending" @selected(($filterParameters['attendance_status'] ?? null) === 'pending')>{{ __('index.meeting_pending') }}</option>
                            </select>
                        </div>

                        @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                            <div class="col-xl-3 col-md-6">
                                <label for="fromDate" class="form-label">{{ __('index.from_date') }}</label>
                                <input type="text" id="fromDate" name="meeting_from" value="{{$filterParameters['meeting_from']}}" placeholder="mm/dd/yyyy" class="form-control meetingDate"/>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <label for="toDate" class="form-label">{{ __('index.to_date') }}</label>
                                <input type="text" id="toDate" name="meeting_to" value="{{$filterParameters['meeting_to']}}" placeholder="mm/dd/yyyy" class="form-control meetingDate"/>
                            </div>
                        @else
                            <div class="col-xl-3 col-md-6">
                                <label for="meeting_from" class="form-label">{{ __('index.from_date') }}</label>
                                <input type="date" id="meeting_from" name="meeting_from" value="{{$filterParameters['meeting_from']}}" class="form-control fromDate">
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <label for="meeting_to" class="form-label">{{ __('index.to_date') }}</label>
                                <input type="date" id="meeting_to" name="meeting_to" value="{{$filterParameters['meeting_to']}}" class="form-control toDate">
                            </div>
                        @endif
                    </div>
                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.team-meetings.employee-join')])
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
            @include('admin.section.directory-toolbar', [
                'directoryTable' => 'meetingEmployeeTable',
                'directoryForm' => 'employeeJoinFilterForm',
                'directorySearch' => 'meetingEmployeeListSearch',
                'directoryRecords' => $joinRecords,
                'directoryTitle' => __('index.employee_join_status'),
                'directoryIcon' => 'user-check',
                'directoryCanAdd' => false,
                'directoryDefaultSize' => (string) $employeeJoinDefaultPageSize,
                'directoryPageSizes' => $employeeJoinPageSizes,
                'directorySearchKey' => 'search',
                'directoryLive' => true,
            ])
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="meetingEmployeeTable" class="table">
                        <thead>
                        <tr>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.status') }}</th>
                            <th>{{ __('index.checked_in_at') }}</th>
                            <th>{{ __('index.team_meeting') }}</th>
                            <th>{{ __('index.meeting_date') }}</th>
                            <th>{{ __('index.branch') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($joinRecords as $record)
                            <tr>
                                <td>{{ $record['employee'] }}</td>
                                <td>
                                    @if($record['joined'])
                                        <span class="badge bg-success">{{ __('index.meeting_joined') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('index.meeting_pending') }}</span>
                                    @endif
                                </td>
                                <td data-order="{{ $record['checked_in_at']?->format('Y-m-d H:i:s') ?? '' }}">{{ $record['checked_in_at']?->format('Y-m-d H:i:s') ?? '—' }}</td>
                                <td>{{ $record['meeting'] }}</td>
                                <td data-order="{{ $record['meeting_date_value'] ?? '' }}">{{ $record['meeting_date'] }}</td>
                                <td>{{ $record['branch'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.teamMeeting.common.scripts')
    <script src="{{ asset('assets/js/dataTables.min.js') }}"></script>
    <script>
        (function () {
            const tableElement = document.getElementById('meetingEmployeeTable');
            if (!tableElement || typeof DataTable === 'undefined') return;

            const table = new DataTable(tableElement, {
                pageLength: {{ $employeeJoinPageLength }},
                order: [],
                ordering: true,
                paging: true,
                searching: true,
                info: false,
                autoWidth: false,
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: null,
                    bottomEnd: null,
                },
                language: {
                    search: '',
                    searchPlaceholder: @json(__('index.search')),
                    zeroRecords: @json(__('index.no_records_found')),
                    infoEmpty: @json(__('index.no_records_found')),
                },
            });

            table.on('draw', function () {
                const count = document.querySelector('[data-directory-count="meetingEmployeeTable"]');
                if (count) count.textContent = table.rows({ search: 'applied' }).count().toLocaleString();
                document.dispatchEvent(new CustomEvent('directory:updated'));
            });

            const searchInput = document.getElementById('meetingEmployeeListSearch');
            const hiddenSearch = document.getElementById('employeeJoinSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    if (hiddenSearch) hiddenSearch.value = this.value;
                    table.search(this.value).draw();
                });
            }

            const pageSize = document.getElementById('per_page');
            if (pageSize) {
                pageSize.addEventListener('change', function () {
                    const length = this.value === 'all' ? -1 : parseInt(this.value, 10);
                    if (!Number.isNaN(length)) table.page.len(length).draw();
                });
            }
        })();
    </script>
    <script src="{{ asset('assets/js/post-selection-popup.js') }}?v={{ filemtime(public_path('assets/js/post-selection-popup.js')) }}"></script>
@endsection
