@extends('layouts.master')

@section('title', 'Edit Link')

@section('action', 'Edit')

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

        @include('admin.appLink.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Edit Link: {{ $linkDetail->name }}</h6>
            </div>
            <div class="card-body">
                <form class="forms-sample" action="{{ route('admin.app-links.update', $linkDetail->id) }}" enctype="multipart/form-data" method="POST">
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
