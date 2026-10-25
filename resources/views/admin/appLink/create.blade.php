@extends('layouts.master')

@include('admin.leaveRequest.common.embedded-form')

@section('title', 'Add New Link')

@section('action', 'Create')

@section('button')
    <a href="{{ route('admin.app-links.index') }}">
        <button class="btn btn-sm btn-primary">
            <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}
        </button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @if(request('request_modal') !== '1')
            @include('admin.appLink.common.breadcrumb')
        @endif

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Add New Link</h6>
            </div>
            <div class="card-body">
                <form class="forms-sample" action="{{ route('admin.app-links.store', request('request_modal') === '1' ? ['request_modal' => '1'] : []) }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.appLink.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.appLink.common.scripts')
@endsection
