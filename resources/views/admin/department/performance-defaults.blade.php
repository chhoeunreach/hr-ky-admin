@extends('layouts.master')
@section('title', __('index.department_performance_defaults'))
@section('main-content')
<section class="content">
    @include('admin.section.flash_message')
    <div class="card"><div class="card-body">
        <h5>{{ $department->dept_name }}</h5>
        <p class="text-muted">{{ $defaults['label'] }} · {{ __('index.department_defaults_help') }}</p>
        @can('edit_department')
            @can('employee.kpi.manage')
                @if($department->is_active)
                    <form method="post" action="{{ route('admin.departments.performance-defaults.apply', ['id' => $department->id, 'employee_modal' => request('employee_modal')]) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary">{{ __('index.apply_department_defaults') }}</button>
                    </form>
                @endif
            @endcan
        @endcan
        @include('admin.department.partials.performance-defaults')
    </div></div>
</section>
@endsection
