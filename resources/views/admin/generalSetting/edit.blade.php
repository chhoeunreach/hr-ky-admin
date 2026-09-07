
@extends('layouts.master')

@section('title', __('index.edit_general_setting'))

@section('action', __('index.edit_general_setting'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.generalSetting.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form class="forms-sample" action="{{route('admin.general-settings.update',$generalSettingDetail->id)}}"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.generalSetting.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection


