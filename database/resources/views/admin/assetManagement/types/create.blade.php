
@extends('layouts.master')

@section('title',__('index.asset_type'))

@section('action',__('index.create'))

@section('button')
    <a href="{{route('admin.asset-types.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.assetManagement.types.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.asset-types.store')}}"  method="POST">
                    @csrf
                    @include('admin.assetManagement.types.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.assetManagement.types.common.scripts')
@endsection
