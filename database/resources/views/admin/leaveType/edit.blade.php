
@extends('layouts.master')

@section('title',__('index.leave_type'))

@section('action',__('index.edit'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveType.common.breadcrumb')
        <div class="row">
{{--            <div class="col-lg-2">--}}
{{--                @include('admin.leaveRequest.common.leave_menu')--}}
{{--            </div>--}}
{{--            <div class="col-lg-10">--}}
                <div class="card">
                    <div class="card-body">
                        <form class="forms-sample" action="{{route('admin.leaves.update',$leaveDetail->id)}}"  method="post">
                            @method('PUT')
                            @csrf
                            @include('admin.leaveType.common.form')
                        </form>
                    </div>
                </div>
{{--            </div>--}}
        </div>


    </section>
@endsection

@section('scripts')
    @include('admin.leaveType.common.scripts')
@endsection

