@extends('layouts.master')

@section('title', __('daily_records.daily_summary_submissions'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
@endsection

@section('main-content')
<div class="dr-container">
    {{-- Header / Breadcrumbs --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.daily-records.index') }}" class="text-decoration-none">
                        <i class="fa-solid fa-list-check me-1"></i> {{ __('daily_records.task_and_report_center') }}
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.daily_summary_submissions') }}</li>
            </ol>
        </nav>
        <a href="{{ route('admin.daily-records.index') }}" class="dr-btn-outline py-1 px-3">
            <i class="fa-solid fa-arrow-left"></i> {{ __('daily_records.back_to_center') }}
        </a>
    </div>

    {{-- Hero Banner --}}
    <div class="dr-hero-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index: 1;">
            <div class="d-flex align-items-center gap-3">
                <div class="dr-hero-icon-box">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <div>
                    <h1 class="dr-hero-title">{{ __('daily_records.daily_summary_submissions') }}</h1>
                    <p class="dr-hero-subtitle">
                        {{ __('daily_records.daily_summary_submissions_subtitle') }}
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a class="dr-btn-primary" href="{{ route('admin.daily-records.create') }}">
                    <i class="fa-solid fa-plus"></i> {{ __('daily_records.assign_task') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="dr-card">
        <div class="dr-card-header">
            <h2 class="dr-card-title">
                <i class="fa-solid fa-sliders text-primary"></i> {{ __('daily_records.filter_submissions') }}
            </h2>
        </div>
        <div class="dr-card-body">
            <form method="get" action="{{ route('admin.daily-records.submissions') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-circle-dot me-1"></i> {{ __('daily_records.review_status') }}</label>
                    <select class="dr-form-select" name="status">
                        @foreach ([
                            'pending' => __('daily_records.pending_review'),
                            'under_review' => __('daily_records.under_review'),
                            'returned' => __('daily_records.returned_for_correction'),
                            'approved' => __('daily_records.kpi_approved'),
                            'unsubmitted' => __('daily_records.unsubmitted_summaries')
                        ] as $key => $label)
                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.report_date_ict') }}</label>
                    <input class="dr-form-control" type="date" name="date" value="{{ $filters['date'] ?? ($status === 'unsubmitted' ? $day : '') }}">
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-user me-1"></i> {{ __('daily_records.employee_search') }}</label>
                    <input class="dr-form-control" name="search" maxlength="100" placeholder="{{ __('daily_records.type_employee_name') }}" value="{{ $filters['search'] ?? '' }}">
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-user-shield me-1"></i> {{ __('daily_records.reviewer_id') }}</label>
                    <input class="dr-form-control" type="number" min="1" placeholder="{{ __('daily_records.reviewer_id') }}" name="reviewer_id" value="{{ $filters['reviewer_id'] ?? '' }}">
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-building me-1"></i> {{ __('daily_records.department') }}</label>
                    <select class="dr-form-select" name="department_id">
                        <option value="">{{ __('daily_records.all_departments') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-location-dot me-1"></i> {{ __('daily_records.location_branch') }}</label>
                    <select class="dr-form-select" name="branch_id">
                        <option value="">{{ __('daily_records.all_locations') }}</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.from_ict') }}</label>
                    <input class="dr-form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>

                <div class="col-sm-6 col-lg-3 dr-form-group">
                    <label class="dr-form-label"><i class="fa-regular fa-calendar-check me-1"></i> {{ __('daily_records.through_ict') }}</label>
                    <input class="dr-form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>

                <div class="col-12 d-flex gap-2 pt-2">
                    <button class="dr-btn-primary" type="submit">
                        <i class="fa-solid fa-filter"></i> {{ __('daily_records.apply_filters') }}
                    </button>
                    <a class="dr-btn-outline" href="{{ route('admin.daily-records.submissions') }}">
                        <i class="fa-solid fa-rotate-left"></i> {{ __('daily_records.reset_filters') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Submissions Table Card --}}
    <div class="dr-card">
        <div class="dr-card-header">
            <div class="d-flex align-items-center gap-2">
                <h2 class="dr-card-title">
                    <i class="fa-solid fa-table-list text-primary"></i> {{ __('daily_records.submissions_list') }}
                </h2>
                <span class="dr-meta-badge">{{ __('daily_records.total_count', ['count' => $rows->total()]) }}</span>
            </div>
            @if ($status === 'unsubmitted')
                <span class="dr-badge dr-badge-warning">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('daily_records.unsubmitted_notice', ['date' => $day]) }}
                </span>
            @endif
        </div>
        <div class="dr-card-body p-0">
            <div class="dr-table-responsive">
                <table class="dr-table">
                    <thead>
                        <tr>
                            <th>{{ __('daily_records.employee') }}</th>
                            <th>{{ __('daily_records.reporting_date_ict') }}</th>
                            <th>{{ __('daily_records.status') }}</th>
                            <th>{{ $status === 'unsubmitted' ? __('daily_records.eligible_records') : __('daily_records.version') }}</th>
                            <th class="text-end">{{ __('daily_records.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $initials = collect(explode(' ', $row->employee ?? 'Staff'))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                                $rowStatus = $row->status ?? 'unsubmitted';
                                $statusBadgeClass = match($rowStatus) {
                                    'approved' => 'dr-badge-success',
                                    'under_review' => 'dr-badge-primary',
                                    'pending' => 'dr-badge-warning',
                                    'returned' => 'dr-badge-danger',
                                    default => 'dr-badge-secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="dr-avatar-pill">
                                        <div class="dr-avatar-circle">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <span class="dr-emp-name">{{ $row->employee }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="small fw-semibold text-dark">
                                        <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                        {{ $row->report_date ?? $day }}
                                    </span>
                                </td>
                                <td>
                                    <span class="dr-badge {{ $statusBadgeClass }}">
                                        <span class="dr-badge-dot"></span>
                                        {{ __('daily_records.kpi_' . $rowStatus) !== 'daily_records.kpi_' . $rowStatus ? __('daily_records.kpi_' . $rowStatus) : ucfirst(str_replace('_', ' ', $rowStatus)) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($status === 'unsubmitted')
                                        <span class="badge bg-light text-dark border">{{ $row->eligible_records }}</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ __('daily_records.version_label', ['version' => $row->version]) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if (isset($row->id))
                                        <a class="dr-btn-primary btn-sm py-1 px-3 text-decoration-none" href="{{ route('admin.daily-records.submission', $row->id) }}">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <span>{{ __('daily_records.open_summary') }}</span>
                                        </a>
                                    @else
                                        <a class="dr-btn-outline btn-sm py-1 px-3 text-decoration-none" href="{{ route('admin.daily-records.index', ['view' => 'daily', 'date' => $day, 'employee_id' => $row->employee_id]) }}">
                                            <i class="fa-regular fa-eye"></i>
                                            <span>{{ __('daily_records.view_records') }}</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="dr-empty-state">
                                        <div class="dr-empty-icon"><i class="fa-regular fa-calendar-check"></i></div>
                                        <div class="dr-empty-title">{{ __('daily_records.no_submissions_found') }}</div>
                                        <p class="dr-empty-desc">{{ __('daily_records.no_submissions_desc') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($rows->hasPages())
                <div class="p-3 border-top">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
