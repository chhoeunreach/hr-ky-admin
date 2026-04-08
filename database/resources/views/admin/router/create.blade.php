
@extends('layouts.master')

@section('title',__('index.router'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.router.common.breadcrumb')
        <div class="card">
            <div class="card-body pb-0">
                <h4 class="mb-4">@lang('index.router_detail') </h4>
                <form class="forms-sample" action="{{route('admin.routers.store')}}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.router.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
