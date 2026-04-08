
@extends('layouts.master')

@section('title',__('index.team_meeting'))

@section('action',__('index.lists'))

@section('button')
    @can('create_team_meeting')
        <a href="{{ route('admin.team-meetings.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{__('index.create_team_meeting')}}
            </button>
        </a>
    @endcan
@endsection


@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.teamMeeting.common.breadcrumb')

        <div class="search-box p-4 pb-0 bg-white rounded mb-3 box-shadow">
            <form class="forms-sample" action="{{route('admin.team-meetings.index')}}" method="get">
                <div class="col-lg-2">
                    <h5 class="mb-3">{{__('index.team_meeting_filter')}}</h5>
                </div>
                <div class="row align-items-center">

                    <div class="col-lg col-md-6 mb-4">
                        <label for="" class="form-label">{{__('index.participator')}}</label>
                        <input type="text" id="participator" name="participator" value="{{$filterParameters['participator']}}" class="form-control">
                    </div>

                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        <div class="col-lg col-md-6 mb-4">
                            <label for="" class="form-label">{{__('index.from_date')}}</label>
                            <input type="text"  id="fromDate" name="meeting_from" value="{{$filterParameters['meeting_from']}}" placeholder="mm/dd/yyyy" class="form-control fromDate"/>
                        </div>

                        <div class="col-lg col-md-6 mb-4">
                            <label for="" class="form-label">{{__('index.to_date')}}</label>
                            <input type="text" id="toDate" name="meeting_to" value="{{$filterParameters['meeting_to']}}" placeholder="mm/dd/yyyy" class="form-control toDate"/>
                        </div>
                    @else
                        <div class="col-lg col-md-6 mb-4">
                            <label for="" class="form-label">{{__('index.from_date')}}</label>
                            <input type="date"  name="meeting_from" value="{{$filterParameters['meeting_from']}}" class="form-control fromDate">
                        </div>

                        <div class="col-lg mb-4">
                            <label for="" class="form-label">{{__('index.to_date')}}</label>
                            <input type="date"  name="meeting_to" value="{{$filterParameters['meeting_to']}}" class="form-control toDate">
                        </div>
                    @endif

                    <div class="col-lg mt-lg-4 mb-4">
                        <div class="d-flex float-md-end">
                            <button type="submit" class="btn btn-block btn-secondary me-2">{{__('index.filter')}}</button>
                            <a class="btn btn-block btn-primary" href="{{route('admin.team-meetings.index')}}">{{__('index.reset')}}</a>
{{--                            <button type="button" class="btn btn-block btn-primary reset">Reset</button>--}}
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
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
                                <td class="notice-receiver">
                                    <ul class="mb-0">
                                        @foreach(($value->teamMeetingParticipator) as $key => $datum)
                                            <li>{{ $datum->participator ? ucfirst($datum->participator->name) : 'N/A'}}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                @can('show_team_meeting')
                                    <td class="text-center">
                                        <a href=""
                                           id="showMeetingDescription"
                                           data-href="{{route('admin.team-meetings.show',$value->id)}}"
                                           data-id="{{ $value->id }}" title="{{__('index.show_team_meeting')}}">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan

                                @canany(['edit_team_meeting','delete_team_meeting'])
                                    <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        @can('edit_team_meeting')
                                            <li class="me-2">
                                                <a href="{{route('admin.team-meetings.edit',$value->id)}}" title="{{__('index.edit_meeting_detail')}} ">
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
@endsection

@section('scripts')
    @include('admin.teamMeeting.common.scripts')
@endsection






