@extends('layouts.master')
@section('title', __('index.asset'))
@section('action', __('index.edit'))
@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.assetManagement.assetDetail.common.breadcrumb')
        @include('admin.assetManagement.assetDetail.common.employee-assignment')
        <div class="card">
            <div class="card-body">
                <form id="assetDetailsForm" class="forms-sample" action="{{route('admin.assets.update',$assetDetail->id)}}" enctype="multipart/form-data"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.assetManagement.assetDetail.common.form')
                </form>
            </div>
        </div>
    </section>
    @include('admin.assetManagement.assetDetail.common.assignment')
@endsection

@section('scripts')
    @include('admin.assetManagement.assetDetail.common.form_scripts')
    <script src="{{ asset('assets/js/asset-assignment.js') }}?v={{ filemtime(public_path('assets/js/asset-assignment.js')) }}"></script>
@endsection

