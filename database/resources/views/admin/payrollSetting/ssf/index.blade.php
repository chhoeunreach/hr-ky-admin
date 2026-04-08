@extends('layouts.master')
@section('title',__('index.ssf'))
@section('page')
    <a href="{{ route('admin.ssf.index')}}">

    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-xl-2 col-lg-3 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-xl-10 col-lg-9 mb-4">
                <div class="card">
{{--                    <div class="card-header">--}}
{{--                        <div class="justify-content-end">--}}
{{--                            SSF--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    <div class="card-body">
                        <h4 class="mb-4">SSF Rule</h4>
                            @if(!isset($ssfDetail) && empty($ssfDetail))
                                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                                      action="{{route('admin.ssf.store')}}">
                            @else
                                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                                      action="{{route('admin.ssf.update', $ssfDetail->id)}}">
                                    @method('PUT')
                            @endif

                                @csrf
                                @include('admin.payrollSetting.ssf.form')
                            </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection






