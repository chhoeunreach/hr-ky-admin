@extends('layouts.master')
@section('title', __('index.employee_salary'))
@section('main-content')
    <div data-salary-modal-saved>
        @include('admin.section.flash_message')
    </div>
@endsection
