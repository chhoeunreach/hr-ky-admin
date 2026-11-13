@extends('layouts.master')

@section('title', __('index.asset'))

@section('action', __('index.create'))

@section('main-content')
@include('admin.section.management-ui')

    <section class="content management-page">

        @include('admin.section.flash_message')

        @include('admin.assetManagement.assetDetail.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="assetDetailsForm" class="forms-sample" action="{{route('admin.assets.store')}}" enctype="multipart/form-data"  method="POST">
                    @csrf
                    @include('admin.assetManagement.assetDetail.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.assetManagement.assetDetail.common.form_scripts')
@endsection
