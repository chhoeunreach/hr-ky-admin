
@extends('layouts.master')

@if(request('request_modal') === '1')
    @section('embedded', '1')
    @section('styles')
        <style>
            .page-wrapper { margin-left: 0 !important; width: 100% !important; }
            .page-content { padding: 1rem !important; margin-top: 0 !important; }
            .content { margin: 0 !important; }
        </style>
    @endsection
@endif

@section('title',__('index.team_meeting'))

@section('action',__('index.create_team_meeting'))

@section('button')
    <a href="{{route('admin.team-meetings.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{__('index.back')}}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @if(request('request_modal') !== '1')
            @include('admin.teamMeeting.common.breadcrumb')
        @endif

        <div class="card">
            <div class="card-body pb-0">
                <form id="notification" class="forms-sample" action="{{ route('admin.team-meetings.store', request('request_modal') === '1' ? ['request_modal' => '1'] : []) }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.teamMeeting.common.form')
                </form>
            </div>
        </div>

        @include('admin.post.common.selection-modal')

    </section>
@endsection

@section('scripts')
    @include('admin.teamMeeting.common.scripts')
    <script src="{{ asset('assets/js/post-selection-popup.js') }}?v={{ filemtime(public_path('assets/js/post-selection-popup.js')) }}"></script>
@endsection
