
@extends('layouts.master')

@section('title',__('index.award_types'))

@section('action',__('index.create'))

@section('button')
    <a href="{{route('admin.award-types.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.awardManagement.types.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="" class="forms-sample" action="{{route('admin.award-types.store')}}"  method="POST">
                    @csrf
                    @include('admin.awardManagement.types.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.awardManagement.types.common.scripts')
@endsection
