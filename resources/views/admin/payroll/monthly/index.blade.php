@extends('layouts.master')
@section('title', __('index.monthly_payroll'))
@section('action', __('index.monthly_payroll'))
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/monthly-payroll.css') }}?v={{ filemtime(public_path('assets/css/monthly-payroll.css')) }}">
@endsection
@section('main-content')
<section class="content monthly-payroll-page" data-monthly-payroll-page data-error="{{ __('index.salary_refresh_error') }}">
    @include('admin.section.flash_message')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h4 class="mb-1">{{ __('index.monthly_payroll') }}</h4><p class="text-muted small mb-0">{{ __('index.monthly_payroll_source') }}</p></div>
        @can('view_payroll_list')<a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employee-salary.payroll') }}">{{ __('index.monthly_payroll_manage') }}</a>@endcan
    </div>
    <div class="card mb-3"><div class="card-body">
        <form method="get" action="{{ route('admin.monthly-payroll.index') }}" id="monthlyPayrollFilters" data-default-month="{{ now()->format('Y-m') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-2 col-md-4"><label for="monthlyMonth" class="form-label">{{ __('index.month') }}</label><input id="monthlyMonth" type="month" name="month" class="form-control" value="{{ $filters['month'] }}" required></div>
                <div class="col-lg-2 col-md-4"><label for="monthlyBranch" class="form-label">{{ __('index.branch') }}</label><select id="monthlyBranch" name="branch_id" class="form-select"><option value="">{{ __('index.all') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string) $branch->id === (string) $filters['branch_id'])>{{ $branch->name }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4"><label for="monthlyDepartment" class="form-label">{{ __('index.department') }}</label><select id="monthlyDepartment" name="department_id" class="form-select"><option value="">{{ __('index.all') }}</option>@foreach($departments as $department)<option data-branch="{{ $department->branch_id }}" value="{{ $department->id }}" @selected((string) $department->id === (string) $filters['department_id'])>{{ $department->dept_name }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4"><label for="monthlyStatus" class="form-label">{{ __('index.status') }}</label><select id="monthlyStatus" name="status" class="form-select"><option value="">{{ __('index.all') }}</option>@foreach(['generated', 'review', 'locked', 'paid'] as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('index.'.$status) }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4"><label for="monthlySearch" class="form-label">{{ __('index.search') }}</label><input id="monthlySearch" type="search" class="form-control" name="q" value="{{ $filters['q'] }}" maxlength="200" placeholder="{{ __('index.monthly_payroll_search') }}"></div>
                <div class="col-lg-2 col-md-4 d-flex gap-2"><button class="btn btn-primary" type="submit">{{ __('index.filter') }}</button><a class="btn btn-outline-secondary" href="{{ route('admin.monthly-payroll.index') }}" data-monthly-reset>{{ __('index.reset') }}</a></div>
            </div>
            <div class="monthly-payroll-toolbar mt-3">
                <div class="d-flex align-items-center gap-2"><label for="monthlyRows" class="mb-0">{{ __('index.show') }}</label><select id="monthlyRows" name="per_page" class="form-select form-select-sm">@foreach(['10','25','50','100','all'] as $size)<option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size === 'all' ? __('index.all') : $size }}</option>@endforeach</select><span>{{ __('index.entries') }}</span></div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-secondary btn-sm" type="submit"><i data-feather="refresh-cw"></i>{{ __('index.refresh') }}</button>
                    @can('export_monthly_payroll')
                        @foreach(['csv'=>'export_csv','excel'=>'export_excel','print'=>'print','pdf'=>'export_pdf'] as $format=>$label)
                            <a class="btn btn-outline-secondary btn-sm" data-monthly-export="{{ $format }}" href="{{ request()->fullUrlWithQuery(['export'=>$format,'page'=>null]) }}" @if(in_array($format,['print','pdf'])) target="_blank" rel="noopener" @endif>{{ __('index.'.$label) }}</a>
                        @endforeach
                    @endcan
                </div>
            </div>
        </form>
    </div></div>
    <div class="text-danger mb-3" role="status" aria-live="polite" data-monthly-error></div>
    @include('admin.payroll.monthly.results')
    @include('admin.employees.profile.partials.form-modal', ['formModalDirectoryUrl' => route('admin.monthly-payroll.index'), 'formModalRefreshEvent' => 'monthly-payroll:changed'])
</section>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/employee-profile-form-modal.js') }}?v={{ filemtime(public_path('assets/js/employee-profile-form-modal.js')) }}"></script>
    <script src="{{ asset('assets/js/monthly-payroll.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll.js')) }}"></script>
@endsection
