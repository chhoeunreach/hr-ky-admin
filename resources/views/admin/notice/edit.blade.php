@extends('layouts.master')
@if(request('notice_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', __('index.edit_notice'))

@section('action', __('index.edit'))

@section('button')
    <a href="{{ route('admin.notices.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
    </a>
@endsection

@section('main-content')

    <link rel="stylesheet" href="{{ asset('assets/css/notice-form.css') }}?v={{ filemtime(public_path('assets/css/notice-form.css')) }}">
    @if(request('notice_modal') === '1')<script>document.documentElement.classList.add('notice-form-embedded');</script>@endif
    <section class="content notice-form-page">

        @include('admin.section.flash_message')

        @unless(request('notice_modal') === '1')
            @include('admin.notice.common.breadcrumb')
        @endunless

        <div class="card">
            <div class="card-body">
                <form id="notification" class="forms-sample" action="{{ route('admin.notices.update', $noticeDetail->id) }}" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.notice.common.form')
                </form>
            </div>
        </div>

    </section>

@endsection

@section('scripts')
    @include('admin.notice.common.scripts')
    @if(request('notice_modal') === '1')
        @include('admin.notice.common.modal-bridge')
    @endif
@endsection
