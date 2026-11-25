@extends('layouts.master')

@if(request('department_modal') === '1')
    @section('embedded', '1')
@endif

@section('title', __('index.edit_department'))

@section('button')
    <a href="{{ route('admin.departments.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @unless(request('department_modal') === '1')
            @include('admin.department.common.breadcrumb',['title'=>__('index.edit')])
        @endunless
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.departments.update', $departmentsDetail->id) }}" enctype="multipart/form-data" method="post">
                    @method('PUT')
                    @csrf
                    @if(request('department_modal') === '1')
                        <input type="hidden" name="department_modal" value="1">
                    @endif
                    @include('admin.department.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection
@section('scripts')
    @include('admin.department.common.form_script')
@endsection
