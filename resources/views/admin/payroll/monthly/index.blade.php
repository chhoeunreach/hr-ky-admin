@extends('layouts.master')
@section('title', __('index.monthly_payroll'))
@section('action', __('index.monthly_payroll'))
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/monthly-payroll.css') }}?v={{ filemtime(public_path('assets/css/monthly-payroll.css')) }}">
@endsection
@section('main-content')
<section class="content monthly-payroll-page" data-monthly-payroll-page data-error="{{ __('index.salary_refresh_error') }}">
    @include('admin.section.flash_message')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 monthly-payroll-heading">
        <div><h4 class="mb-1">{{ __('index.monthly_payroll') }}</h4><p class="text-muted small mb-0">{{ __('index.monthly_payroll_source') }}</p></div>
        @can('view_payroll_list')<a class="btn btn-outline-primary btn-sm" href="{{ route('admin.employee-salary.payroll') }}">{{ __('index.monthly_payroll_manage') }}</a>@endcan
    </div>
    @include('admin.payroll.monthly.period-navigation')
    @include('admin.payroll.monthly.summary')
    <form method="get" action="{{ route('admin.monthly-payroll.index') }}" id="monthlyPayrollFilters" data-default-month="{{ now()->format('Y-m') }}">
        <details class="monthly-payroll-filters">
            <summary class="monthly-payroll-filter-heading">
                <span class="monthly-payroll-filter-title"><span class="monthly-payroll-filter-icon"><i data-feather="sliders" aria-hidden="true"></i></span>{{ __('index.filters') }}</span>
                <span class="monthly-payroll-filter-chevron"><i data-feather="chevron-down" aria-hidden="true"></i></span>
            </summary>
            <div class="monthly-payroll-filter-content">
                <div class="monthly-payroll-filter-grid">
                    <div class="monthly-payroll-field">
                        <label for="monthlyMonth">{{ __('index.month') }}</label>
                        <input id="monthlyMonth" type="month" name="month" class="form-control" value="{{ $filters['month'] }}" required>
                    </div>
                    <div class="monthly-payroll-field">
                        <label for="monthlyBranch">{{ __('index.branch') }}</label>
                        <select id="monthlyBranch" name="branch_id" class="form-select"><option value="">{{ __('index.all_branches') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string) $branch->id === (string) $filters['branch_id'])>{{ $branch->name }}</option>@endforeach</select>
                    </div>
                    <div class="monthly-payroll-field">
                        <label for="monthlyDepartment">{{ __('index.department') }}</label>
                        <select id="monthlyDepartment" name="department_id" class="form-select"><option value="">{{ __('index.all_departments') }}</option>@foreach($departments as $department)<option data-branch="{{ $department->branch_id }}" value="{{ $department->id }}" @selected((string) $department->id === (string) $filters['department_id'])>{{ $department->dept_name }}</option>@endforeach</select>
                    </div>
                    <div class="monthly-payroll-field">
                        <label for="monthlyStatus">{{ __('index.status') }}</label>
                        <select id="monthlyStatus" name="status" class="form-select"><option value="">{{ __('index.all_status') }}</option>@foreach(['generated', 'review', 'locked', 'paid'] as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('index.'.$status) }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="monthly-payroll-filter-actions">
                    <a class="btn monthly-payroll-reset" href="{{ route('admin.monthly-payroll.index') }}" data-monthly-reset><i data-feather="rotate-ccw" aria-hidden="true"></i>{{ __('index.reset') }}</a>
                    <button class="btn btn-primary monthly-payroll-apply" type="submit"><i data-feather="check" aria-hidden="true"></i>{{ __('index.apply') }}</button>
                </div>
            </div>
        </details>
        <div class="monthly-payroll-toolbar">
            <div class="monthly-payroll-page-size">
                <label for="monthlyRows">{{ __('index.show') }}</label>
                <select id="monthlyRows" name="per_page" class="form-select form-select-sm">@foreach(['10','25','50','100','all'] as $size)<option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size === 'all' ? __('index.all') : $size }}</option>@endforeach</select>
                <span>{{ __('index.entries') }}</span>
            </div>
            <div class="monthly-payroll-tools" role="group" aria-label="{{ __('index.export') }}">
                <button class="btn monthly-payroll-tool monthly-payroll-refresh" type="submit" aria-label="{{ __('index.refresh') }}" title="{{ __('index.refresh') }}"><i data-feather="refresh-cw" aria-hidden="true"></i><span class="visually-hidden">{{ __('index.refresh') }}</span></button>
                @can('export_monthly_payroll')
                    @foreach(['csv'=>['export_csv','file-text'],'excel'=>['export_excel','grid'],'print'=>['print','printer'],'pdf'=>['export_pdf','file']] as $format=>$tool)
                        <a class="btn monthly-payroll-tool" data-monthly-export="{{ $format }}" href="{{ request()->fullUrlWithQuery(['export'=>$format,'page'=>null]) }}" @if(in_array($format,['print','pdf'])) target="_blank" rel="noopener" @endif><i data-feather="{{ $tool[1] }}" aria-hidden="true"></i><span>{{ __('index.'.$tool[0]) }}</span></a>
                    @endforeach
                @endcan
            </div>
            <div class="monthly-payroll-search">
                <label for="monthlySearch" class="visually-hidden">{{ __('index.search') }}</label>
                <i data-feather="search" aria-hidden="true"></i>
                <input id="monthlySearch" type="search" class="form-control" name="q" value="{{ $filters['q'] }}" maxlength="200" placeholder="{{ __('index.monthly_payroll_search') }}" autocomplete="off">
            </div>
        </div>
    </form>
    <div class="text-danger monthly-payroll-error" role="status" aria-live="polite" data-monthly-error></div>
    @include('admin.payroll.monthly.results')
    @include('admin.employees.profile.partials.form-modal', ['formModalDirectoryUrl' => route('admin.monthly-payroll.index'), 'formModalRefreshEvent' => 'monthly-payroll:changed'])
    @include('admin.payroll.monthly.generate-modal')
</section>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/employee-profile-form-modal.js') }}?v={{ filemtime(public_path('assets/js/employee-profile-form-modal.js')) }}"></script>
    <script src="{{ asset('assets/js/monthly-payroll.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll.js')) }}"></script>
    <script src="{{ asset('assets/js/monthly-payroll-generate.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll-generate.js')) }}"></script>
@endsection
