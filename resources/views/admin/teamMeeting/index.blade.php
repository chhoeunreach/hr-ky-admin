
@extends('layouts.master')

@section('title',__('index.team_meeting'))

@section('action',__('index.lists'))

@section('button')
    @can('create_team_meeting')
        <a href="{{ route('admin.team-meetings.create')}}"
           data-open-request-form
           data-request-modal-title="{{ __('index.create_team_meeting') }}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{__('index.create_team_meeting')}}
            </button>
        </a>
    @endcan
@endsection


@section('main-content')

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

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.teamMeeting.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{__('index.team_meeting_filter')}}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.team-meetings.index')}}" method="get">

                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled>{{ __('index.select_branch') }}</option>
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
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-control" id="department_id" multiple name="department_id[]"
                                data-selection-popup="1" data-placeholder="{{ __('index.select_department') }}">
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" multiple name="participator[]" id="team_meeting"
                                data-selection-popup="1" data-placeholder="{{ __('index.select_meeting_participants') }}">
                        </select>
                    </div>

                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text"  id="fromDate" name="meeting_from" value="{{$filterParameters['meeting_from']}}" placeholder="mm/dd/yyyy" class="form-control meetingDate"/>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="text" id="toDate" name="meeting_to" value="{{$filterParameters['meeting_to']}}" placeholder="mm/dd/yyyy" class="form-control meetingDate"/>
                        </div>
                    @else
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  name="meeting_from" value="{{$filterParameters['meeting_from']}}" class="form-control fromDate">
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date"  name="meeting_to" value="{{$filterParameters['meeting_to']}}" class="form-control toDate">
                        </div>
                    @endif

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-block btn-success me-2">{{__('index.filter')}}</button>
                            <a class="btn btn-block btn-primary" href="{{route('admin.team-meetings.index')}}">{{__('index.reset')}}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.meeting_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('index.title')}}</th>
                            <th class="text-center">{{__('index.meeting_date')}}</th>
                            <th class="text-center">{{__('index.start_time')}}</th>
                            <th>{{__('index.participators')}}</th>
                            <th class="text-center">Meeting QR</th>

                            @can('show_team_meeting')
                                <th class="text-center">{{__('index.description')}}</th>
                            @endcan

                            @canany(['edit_team_meeting','delete_team_meeting'])
                                <th class="text-center">{{__('index.action')}}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($teamMeetings as $key => $value)
                            <tr>
                                <td>{{(($teamMeetings->currentPage()- 1 ) * (\App\Models\TeamMeeting::RECORDS_PER_PAGE) + (++$key))}} </td>
                                <td>{{ucfirst($value->title)}}</td>
                                <td class="text-center">{{\App\Helpers\AppHelper::formatDateForView($value->meeting_date)}}</td>
                                <td class="text-center">{{\App\Helpers\AttendanceHelper::changeTimeFormatForAttendanceView($value->meeting_start_time)}}</td>
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
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.team-meetings.attendance', $value->id) }}">QR &amp; Attendance</a>
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
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{__('index.no_records_found')}}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate">
            {{$teamMeetings->appends($_GET)->links()}}
        </div>
    </section>

    @include('admin.teamMeeting.show')
    @include('admin.post.common.selection-modal')
@endsection

@section('scripts')
    @include('admin.teamMeeting.common.scripts')
    <script src="{{ asset('assets/js/post-selection-popup.js') }}?v={{ filemtime(public_path('assets/js/post-selection-popup.js')) }}"></script>
@endsection






