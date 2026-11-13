
@extends('layouts.master')

@section('title',__('index.team_meeting'))

@section('action',__('index.lists'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.dataTables.min.css') }}">
    <style>
        .participant-column {
            max-width: 260px;
        }

        .participant-column .participant-names {
            display: inline-block;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }

        .participant-column .participant-more {
            white-space: nowrap;
            vertical-align: middle;
        }
    </style>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.teamMeeting.common.breadcrumb')

        @php
            $meetingDefaultPageSize = (int) getRecordPerPage();
            $requestedPageSize = request('per_page');
            $meetingPageLength = $requestedPageSize === 'all' ? -1 : (int) ($requestedPageSize ?: $meetingDefaultPageSize);
            if ($meetingPageLength === 0) {
                $meetingPageLength = $meetingDefaultPageSize;
            }
            $meetingPageSizes = array_values(array_unique(array_merge([10, 15, 25, 50, 100, 200, 500, 1000], [$meetingDefaultPageSize])));
            sort($meetingPageSizes);
            $meetingPageSizes[] = 'all';
        @endphp

        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'teamMeetingFilterCollapse', 'directoryExpanded' => false])
            <div id="teamMeetingFilterCollapse" class="collapse">
                <form class="forms-sample directory-filter-form" action="{{route('admin.team-meetings.index')}}" id="teamMeetingFilterForm" method="get">
                    <input type="hidden" id="teamMeetingSearch" name="search" value="">
                    <div class="row g-3 align-items-end">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-xl-3 col-md-6">
                                <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                                <select class="form-control" id="branch_id" name="branch_id">
                                    <option selected disabled>{{ __('index.select_branch') }}</option>
                                    @if(isset($companyDetail))
                                        @foreach($companyDetail->branches()->get() as $key => $branch)
                                            <option value="{{$branch->id}}"
                                                {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id']  == $branch->id) ? 'selected': '' }}>
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

                        @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                            <div class="col-xl-3 col-md-6">
                                <label for="fromDate" class="form-label">{{ __('index.from_date') }}</label>
                                <input type="text"  id="fromDate" name="meeting_from" value="{{$filterParameters['meeting_from']}}" placeholder="mm/dd/yyyy" class="form-control meetingDate"/>
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
                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.team-meetings.index')])
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
            @include('admin.section.directory-toolbar', [
                'directoryTable' => 'teamMeetingTable',
                'directoryForm' => 'teamMeetingFilterForm',
                'directorySearch' => 'teamMeetingListSearch',
                'directoryRecords' => $teamMeetings,
                'directoryTitle' => __('index.meeting_list'),
                'directoryIcon' => 'calendar',
                'directoryPermission' => 'create_team_meeting',
                'directoryAddUrl' => route('admin.team-meetings.create'),
                'directoryAddRequestForm' => true,
                'directoryAddModalTitle' => __('index.create_team_meeting'),
                'directoryAddLabel' => __('index.create_team_meeting'),
                'directoryDefaultSize' => (string) $meetingDefaultPageSize,
                'directoryPageSizes' => $meetingPageSizes,
                'directorySearchKey' => 'search',
                'directoryLive' => true,
            ])
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="teamMeetingTable" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('index.title')}}</th>
                            <th class="text-center">{{__('index.meeting_date')}}</th>
                            <th class="text-center">{{__('index.start_time')}}</th>
                            <th>{{__('index.participators')}}</th>
                            <th class="text-center no-sort">Meeting QR</th>

                            @can('show_team_meeting')
                                <th class="text-center no-sort">{{__('index.description')}}</th>
                            @endcan

                            @canany(['edit_team_meeting','delete_team_meeting'])
                                <th class="text-center no-sort">{{__('index.action')}}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($teamMeetings as $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ucfirst($value->title)}}</td>
                                <td class="text-center" data-order="{{ $value->meeting_date }}">{{\App\Helpers\AppHelper::formatDateForView($value->meeting_date)}}</td>
                                <td class="text-center" data-order="{{ $value->meeting_start_time }}">{{\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceView($value->meeting_start_time)}}</td>
                                <td class="notice-receiver participant-column">
                                    @php
                                        $participantNames = $value->teamMeetingParticipator
                                            ->map(function ($participator) {
                                                return $participator->participator ? ucfirst($participator->participator->name) : null;
                                            })
                                            ->filter()
                                            ->values();
                                        $visibleParticipantNames = $participantNames->take(2);
                                        $remainingParticipantCount = $participantNames->count() - $visibleParticipantNames->count();
                                    @endphp
                                    @if($participantNames->isEmpty())
                                        <span class="text-muted">N/A</span>
                                    @else
                                        <span class="participant-names" title="{{ $participantNames->implode(', ') }}">
                                            {{ $visibleParticipantNames->implode(', ') }}
                                        </span>
                                        @if($remainingParticipantCount > 0)
                                            <span class="participant-more badge bg-light text-dark"
                                                  title="{{ $participantNames->implode(', ') }}">
                                                +{{ $remainingParticipantCount }} {{ __('index.more') }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('show_team_meeting')
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.team-meetings.attendance', $value->id) }}">QR &amp; Attendance</a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary showJoinList"
                                                data-href="{{ route('admin.team-meetings.attendance.joined', $value->id) }}"
                                                data-title="{{ ucfirst($value->title) }}">
                                            {{ __('index.join_list') }}
                                            <span class="badge bg-primary ms-1">{{ $value->meetingAttendances->count() }}</span>
                                        </button>
                                    </div>
                                    @endcan
                                </td>
                                @can('show_team_meeting')
                                    <td class="text-center">
                                        <a class="showMeetingDescription"
                                           data-href="{{ route('admin.team-meetings.show', $value->id) }}"
                                           data-id="{{ $value->id }}"
                                           title="{{ __('index.show_team_meeting') }}"
                                           style="cursor: pointer;">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan

                                @canany(['edit_team_meeting','delete_team_meeting'])
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('edit_team_meeting')
                                            <li class="me-2">
                                                <a href="{{route('admin.team-meetings.edit',$value->id)}}"
                                                   data-open-request-form
                                                   data-request-modal-title="{{ __('index.edit_meeting_detail') }}"
                                                   title="{{__('index.edit_meeting_detail')}} ">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan

                                        @can('delete_team_meeting')
                                            <li class="me-2">
                                                <a class="delete"
                                                   data-href="{{route('admin.team-meetings.delete',$value->id)}}" title="{{__('index.delete_team_meeting')}}">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        @endcan

                                    </ul>
                                </td>
                                @endcanany


                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    @include('admin.teamMeeting.show')
    @include('admin.teamMeeting.join-list')
    @include('admin.post.common.selection-modal')
@endsection

@section('scripts')
    @include('admin.teamMeeting.common.scripts')
    <script src="{{ asset('assets/js/dataTables.min.js') }}"></script>
    <script>
        (function () {
            const tableElement = document.getElementById('teamMeetingTable');
            if (!tableElement || typeof DataTable === 'undefined') return;

            const table = new DataTable(tableElement, {
                pageLength: {{ $meetingPageLength }},
                order: [],
                ordering: true,
                paging: true,
                searching: true,
                info: false,
                autoWidth: false,
                columnDefs: [{ orderable: false, targets: 'no-sort' }],
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
                const count = document.querySelector('[data-directory-count="teamMeetingTable"]');
                if (count) count.textContent = table.rows({ search: 'applied' }).count().toLocaleString();
                document.dispatchEvent(new CustomEvent('directory:updated'));
            });

            const searchInput = document.getElementById('teamMeetingListSearch');
            const hiddenSearch = document.getElementById('teamMeetingSearch');
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






