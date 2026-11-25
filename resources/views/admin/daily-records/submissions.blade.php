@extends('layouts.master')

@section('title', __('daily_records.daily_summary_submissions'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
    <style>
        .dr-submission-kpi { display:block; height:100%; color:inherit; text-decoration:none; transition:transform .15s ease, box-shadow .15s ease; }
        .dr-submission-kpi:hover { color:inherit; transform:translateY(-2px); box-shadow:0 10px 24px rgba(15,23,42,.09); }
        .dr-submission-kpi .dr-card-body { padding:16px; }
        .dr-submission-kpi-label { color:#64748b; font-size:.82rem; font-weight:600; }
        .dr-submission-kpi-value { color:#172033; font-size:1.65rem; font-weight:750; line-height:1.1; }
        .dr-submission-kpi-icon { display:grid; place-items:center; width:36px; height:36px; border-radius:10px; background:#eff6ff; color:#2563eb; }
    </style>
@endsection

@section('main-content')
<div class="dr-container">
    {{-- Header / Breadcrumbs --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.daily-records.index') }}" class="text-decoration-none">
                        <i class="fa-solid fa-list-check me-1"></i> {{ __('daily_records.work_and_operations') }}
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.daily_summary_submissions') }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.daily-records.index') }}" class="dr-btn-outline py-1 px-3">
                <i class="fa-solid fa-list-check"></i> <span>{{ __('daily_records.task_and_report_center') }}</span>
            </a>
            <a href="{{ route('admin.daily-records.branch-routes') }}" class="dr-btn-outline py-1 px-3">
                <i class="fa-solid fa-route"></i> <span>{{ __('daily_records.branch_route_management') }}</span>
            </a>
            <a href="{{ route('admin.daily-records.categories') }}" class="dr-btn-outline py-1 px-3">
                <i class="fa-solid fa-sliders"></i> <span>{{ __('daily_records.categories') }}</span>
            </a>
        </div>
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

    @php
        $submissionCards = [
            ['status' => 'pending', 'label' => __('daily_records.pending_review'), 'count' => (int) ($statusCounts->submitted ?? 0) + (int) ($statusCounts->under_review ?? 0), 'icon' => 'fa-hourglass-half', 'tone' => 'text-warning'],
            ['status' => 'submitted', 'label' => __('index.submitted'), 'count' => (int) ($statusCounts->submitted ?? 0), 'icon' => 'fa-paper-plane', 'tone' => 'text-primary'],
            ['status' => 'under_review', 'label' => __('daily_records.under_review'), 'count' => (int) ($statusCounts->under_review ?? 0), 'icon' => 'fa-magnifying-glass', 'tone' => 'text-info'],
            ['status' => 'approved', 'label' => __('daily_records.kpi_approved'), 'count' => (int) ($statusCounts->approved ?? 0), 'icon' => 'fa-circle-check', 'tone' => 'text-success'],
            ['status' => 'returned', 'label' => __('daily_records.returned_for_correction'), 'count' => (int) ($statusCounts->returned ?? 0), 'icon' => 'fa-rotate-left', 'tone' => 'text-danger'],
            ['status' => 'unsubmitted', 'label' => __('daily_records.unsubmitted_summaries'), 'count' => (int) $missingCount, 'icon' => 'fa-triangle-exclamation', 'tone' => 'text-warning'],
        ];
    @endphp
    <div class="row g-3 mb-3" aria-label="{{ __('daily_records.submission_overview') }}">
        @foreach ($submissionCards as $card)
            @php
                $cardFilters = array_merge($filters, ['status' => $card['status']]);
                if ($card['status'] === 'unsubmitted') $cardFilters['date'] = $filters['date'] ?? $day;
            @endphp
            <div class="col-6 col-md-4 col-xl-2">
                <a class="dr-card dr-submission-kpi" href="{{ route('admin.daily-records.submissions', $cardFilters) }}" aria-label="{{ $card['label'] }}: {{ $card['count'] }}">
                    <div class="dr-card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <span class="dr-submission-kpi-label">{{ $card['label'] }}</span>
                            <span class="dr-submission-kpi-icon {{ $card['tone'] }}"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                        </div>
                        <div class="dr-submission-kpi-value">{{ number_format($card['count']) }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    @php
        $hasSubmissionFilters = collect($filters ?? [])
            ->except(['search', 'per_page', 'page'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->isNotEmpty();
    @endphp
    <div class="directory-filter-panel">
        @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'submissionFilterCollapse', 'directoryExpanded' => $hasSubmissionFilters])
        <div id="submissionFilterCollapse" class="collapse{{ $hasSubmissionFilters ? ' show' : '' }}">
            <form class="forms-sample directory-filter-form" action="{{ route('admin.daily-records.submissions') }}" id="submissionFilterForm" method="get">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionStatus"><i class="fa-solid fa-circle-dot me-1"></i> {{ __('daily_records.review_status') }}</label>
                        <select class="form-select" id="submissionStatus" name="status">
                            @foreach ([
                                'pending' => __('daily_records.pending_review'),
                                'submitted' => __('index.submitted'),
                                'under_review' => __('daily_records.under_review'),
                                'returned' => __('daily_records.returned_for_correction'),
                                'approved' => __('daily_records.kpi_approved'),
                                'unsubmitted' => __('daily_records.unsubmitted_summaries')
                            ] as $key => $label)
                                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionDate"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.report_date_ict') }}</label>
                        <input class="form-control" type="date" id="submissionDate" name="date" value="{{ $filters['date'] ?? ($status === 'unsubmitted' ? $day : '') }}">
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionReviewer"><i class="fa-solid fa-user-shield me-1"></i> {{ __('daily_records.reviewer_id') }}</label>
                        <input class="form-control" type="number" min="1" id="submissionReviewer" placeholder="{{ __('daily_records.reviewer_id') }}" name="reviewer_id" value="{{ $filters['reviewer_id'] ?? '' }}">
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionDepartment"><i class="fa-solid fa-building me-1"></i> {{ __('daily_records.department') }}</label>
                        <select class="form-select" id="submissionDepartment" name="department_id">
                            <option value="">{{ __('daily_records.all_departments') }}</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionBranch"><i class="fa-solid fa-location-dot me-1"></i> {{ __('daily_records.location_branch') }}</label>
                        <select class="form-select" id="submissionBranch" name="branch_id">
                            <option value="">{{ __('daily_records.all_locations') }}</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(($filters['branch_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionFrom"><i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.from_ict') }}</label>
                        <input class="form-control" type="date" id="submissionFrom" name="from" value="{{ $filters['from'] ?? '' }}">
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="submissionTo"><i class="fa-regular fa-calendar-check me-1"></i> {{ __('daily_records.through_ict') }}</label>
                        <input class="form-control" type="date" id="submissionTo" name="to" value="{{ $filters['to'] ?? '' }}">
                    </div>
                </div>

                @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.daily-records.submissions')])
            </form>
        </div>
    </div>

    {{-- Submissions Table Card --}}
    <div class="dr-card">
        @include('admin.section.directory-toolbar', ['directoryTable' => 'submissionTable', 'directoryForm' => 'submissionFilterForm', 'directorySearch' => 'submissionSearch', 'directoryRecords' => $rows, 'directoryTitle' => __('daily_records.daily_summary_submissions'), 'directoryIcon' => 'calendar', 'directoryCanAdd' => false, 'directoryDefaultSize' => '25', 'directoryCurrentSize' => (string) request('per_page', '25'), 'directorySearchKey' => 'search', 'directorySearchInForm' => true, 'directoryLive' => false])
        @if ($status === 'unsubmitted')
            <div class="px-3 py-2 border-bottom">
                <span class="dr-badge dr-badge-warning">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('daily_records.unsubmitted_notice', ['date' => $day]) }}
                </span>
            </div>
        @endif
        <div class="dr-card-body p-0">
            <div class="dr-table-responsive">
                <table id="submissionTable" class="dr-table">
                    <thead>
                        <tr>
                            <th>{{ __('daily_records.employee') }}</th>
                            <th>{{ __('daily_records.reporting_date_ict') }}</th>
                            <th>{{ __('daily_records.status') }}</th>
                            <th>{{ $status === 'unsubmitted' ? __('daily_records.eligible_records') : __('daily_records.version') }}</th>
                            <th class="text-end" data-directory-actions>{{ __('daily_records.actions') }}</th>
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
