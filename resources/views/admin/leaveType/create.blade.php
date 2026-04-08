
@extends('layouts.master')

@section('title',__('index.leave_type'))

@section('action',__('index.create'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveType.common.breadcrumb')

                <div class="card">
                    <div class="card-body pb-0">
                        <form class="forms-sample" action="{{route('admin.leaves.store')}}"  method="POST">
                            @csrf
                            @include('admin.leaveType.common.form')
                        </form>
                    </div>
                </div>



    </section>
@endsection

@section('scripts')
    @include('admin.leaveType.common.scripts')
@endsection
