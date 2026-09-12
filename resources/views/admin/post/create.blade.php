@extends('layouts.master')

@section('title', __('index.create_post_title'))

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">{{ __('index.post_section') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.create') }}</li>
            </ol>
            <a href="{{ route('admin.posts.index') }}" >
                <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
            </a>
        </nav>

        <div class="card border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="link-icon" data-feather="briefcase" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0 fw-bold">{{ __('index.create_post_title') }}</h6>
                        <small class="text-muted">Define job position details, assigned branches, and departments</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <form class="forms-sample" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.post.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
@section('scripts')
    @include('admin.post.common.scripts')
@endsection
