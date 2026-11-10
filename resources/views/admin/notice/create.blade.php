@extends('layouts.master')
@if(request('notice_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', __('index.create_notice'))

@section('action', __('index.create'))

@section('button')
    <a href="{{ route('admin.notices.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> @lang('index.back')</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @unless(request('notice_modal') === '1')
            @include('admin.notice.common.breadcrumb')
        @endunless

        <div class="card">
            <div class="card-body">
                <form id="notification" class="forms-sample" action="{{ route('admin.notices.store') }}" method="POST">
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
