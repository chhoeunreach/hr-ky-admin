@extends('layouts.master')

@section('title', __('daily_records.review_daily_summary'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
@endsection

@section('main-content')
<div class="dr-container">
    {{-- Breadcrumb --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.daily-records.index') }}" class="text-decoration-none">
                        <i class="fa-solid fa-list-check me-1"></i> {{ __('daily_records.task_and_report_center') }}
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.daily-records.submissions') }}" class="text-decoration-none">{{ __('daily_records.daily_submissions') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.review_daily_summary') }}</li>
            </ol>
        </nav>
        <a class="dr-btn-outline py-1 px-3" href="{{ route('admin.daily-records.submissions') }}">
            <i class="fa-solid fa-arrow-left"></i> {{ __('daily_records.back_to_submissions') }}
        </a>
    </div>

    @include('admin.daily-records.workflow-messages')

    <div class="row g-3">
        {{-- Left Column: Summary Content & Linked Records --}}
        <div class="col-lg-8">
            <div class="dr-card mb-3">
                <div class="dr-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dr-hero-icon-box" style="width: 42px; height: 42px; font-size: 1.15rem;">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <div>
                            <h1 class="h5 fw-bold text-dark mb-1">{{ $report->title }}</h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="dr-badge dr-badge-info">
                                    <span class="dr-badge-dot"></span>
                                    {{ ucfirst(str_replace('_',' ',$report->status)) }}
                                </span>
                                <span class="badge bg-light text-dark border">{{ __('daily_records.version_label', ['version' => $selected_version]) }}</span>
                                <span class="text-muted small">
                                    <i class="fa-regular fa-calendar me-1"></i> {{ __('daily_records.reporting_date_ict') }}: {{ $report->report_date }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dr-card-body">
                    {{-- Employee & Dept Meta --}}
                    <div class="dr-meta-grid mb-4">
                        <div class="dr-meta-item">
                            <span class="dr-meta-title">{{ __('daily_records.reporting_employee') }}</span>
                            <span class="dr-meta-val"><i class="fa-solid fa-user me-1 text-primary"></i> {{ $report->employee }}</span>
                        </div>
                        <div class="dr-meta-item">
                            <span class="dr-meta-title">{{ __('daily_records.department_and_branch') }}</span>
                            <span class="dr-meta-val"><i class="fa-solid fa-building me-1 text-muted"></i> {{ $report->department ?: '—' }} · {{ $report->location ?: '—' }}</span>
                        </div>
                    </div>

                    {{-- Formatted Content Sections --}}
                    @foreach ([
                        'summary' => __('daily_records.completed_work'),
                        'challenges' => __('daily_records.challenges_faced'),
                        'solutions' => __('daily_records.solutions_implemented'),
                        'pending_work' => __('daily_records.pending_work_next_steps'),
                        'recommendations' => __('daily_records.recommendations')
                    ] as $key => $label)
                        @if (!empty($content[$key]))
                            <div class="mb-4">
                                <h2 class="h6 fw-bold text-dark border-bottom pb-2 mb-2">
                                    <i class="fa-solid fa-circle-dot text-primary me-1" style="font-size: 0.65rem;"></i> {{ $label }}
                                </h2>
                                <div class="dr-instruction-box text-break" style="white-space: pre-wrap;">{{ $content[$key] }}</div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Related Activities and Incidents --}}
                    <div class="mb-4">
                        <h2 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-link text-primary me-1"></i> {{ __('daily_records.related_activities_incidents') }}
                        </h2>
                        <div class="d-grid gap-2">
                            @forelse ($records as $record)
                                <div class="p-3 border rounded bg-light">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                        <a class="fw-bold text-dark text-decoration-none" href="{{ $record->kind === 'incident' ? route('admin.daily-records.incident', $record->id) : route('admin.daily-records.index', ['entry' => $record->id]) }}">
                                            {{ $record->title }}
                                        </a>
                                        <span class="dr-badge {{ $record->kind === 'incident' ? 'dr-badge-danger' : 'dr-badge-primary' }}">
                                            {{ __('daily_records.type_' . $record->kind) !== 'daily_records.type_' . $record->kind ? __('daily_records.type_' . $record->kind) : ucfirst($record->kind) }}
                                        </span>
                                    </div>
                                    @if ($record->body)
                                        <div class="small text-muted text-break mb-1" style="white-space: pre-wrap;">{{ $record->body }}</div>
                                    @endif
                                    @if ($record->solution)
                                        <div class="small text-success text-break"><strong>{{ __('daily_records.solution_provided') }}:</strong> {{ $record->solution }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted small mb-0">{{ __('daily_records.no_daily_records') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Employee Tasks --}}
                    <div class="mb-4">
                        <h2 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-list-check text-primary me-1"></i> {{ __('daily_records.employee_assigned_tasks') }}
                        </h2>
                        <div class="d-grid gap-2">
                            @forelse ($tasks as $task)
                                <div class="p-2 px-3 border rounded d-flex align-items-center justify-content-between gap-2">
                                    <a class="fw-semibold text-dark text-decoration-none" href="{{ route('admin.daily-records.index', ['assignment' => $task->id]) }}">
                                        {{ $task->title }}
                                    </a>
                                    <span class="dr-badge dr-badge-info">
                                        {{ ucfirst(str_replace('_',' ',$task->status)) }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">{{ __('daily_records.no_matching_tasks') }}</p>
                            @endforelse
                        </div>
                        @if ($tasks->hasPages())
                            <div class="pt-2">
                                {{ $tasks->withQueryString()->links() }}
                            </div>
                        @endif
                    </div>

                    {{-- Shortcuts --}}
                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <a class="dr-btn-outline btn-sm" href="{{ route('admin.daily-records.index', ['view' => 'timeline', 'employee_id' => $report->employee_id]) }}">
                            <i class="fa-solid fa-timeline"></i> {{ __('daily_records.view_employee_history') }}
                        </a>
                        <a class="dr-btn-outline btn-sm" href="{{ route('admin.daily-records.create', ['follow_up' => $report->id]) }}">
                            <i class="fa-solid fa-plus"></i> {{ __('daily_records.assign_follow_up_task') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Workflow History Partial --}}
            @include('admin.daily-records.workflow-history')
        </div>

        {{-- Right Column: Manager Review Form & Versions --}}
        <div class="col-lg-4 d-grid gap-3 align-content-start">
            @if ($selected_version == $report->version)
                <div class="dr-card">
                    <div class="dr-card-header">
                        <h2 class="dr-card-title">
                            <i class="fa-solid fa-user-check text-primary"></i> {{ __('daily_records.manager_review_action') }}
                        </h2>
                    </div>
                    <div class="dr-card-body">
                        <form method="POST" action="{{ route('admin.daily-records.submission-review', $report->id) }}" class="d-grid gap-3">
                            @csrf
                            <input type="hidden" name="version" value="{{ $report->version }}">
                            <div>
                                <label class="dr-form-label"><i class="fa-regular fa-comment-dots me-1"></i> {{ __('daily_records.review_notes_feedback_label') }}</label>
                                <textarea class="dr-form-control" name="message" rows="3" placeholder="{{ __('daily_records.manager_feedback_placeholder') }}" maxlength="10000">{{ old('message') }}</textarea>
                            </div>
                            <div class="d-grid gap-2">
                                @if ($report->status === 'submitted')
                                    <button class="dr-btn-outline justify-content-center" name="action" value="start">
                                        <i class="fa-solid fa-play"></i> {{ __('daily_records.start_review') }}
                                    </button>
                                @endif
                                @if (in_array($report->status, ['submitted','under_review'], true))
                                    <button class="dr-btn-primary justify-content-center" name="action" value="approve">
                                        <i class="fa-solid fa-check"></i> {{ __('daily_records.approve_summary') }}
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning d-inline-flex align-items-center justify-content-center gap-2" name="action" value="return">
                                        <i class="fa-solid fa-rotate-left"></i> {{ __('daily_records.return_for_correction') }}
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2" name="action" value="comment">
                                    <i class="fa-regular fa-comment"></i> {{ __('daily_records.add_internal_comment') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Submission Versions --}}
            <div class="dr-card">
                <div class="dr-card-header">
                    <h2 class="dr-card-title">
                        <i class="fa-solid fa-code-branch text-primary"></i> {{ __('daily_records.revision_versions') }}
                    </h2>
                    <span class="dr-meta-badge">{{ __('daily_records.total_count', ['count' => $versions->total()]) }}</span>
                </div>
                <div class="dr-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach ($versions as $version)
                            <a href="{{ route('admin.daily-records.submission', ['entry' => $report->id, 'version' => $version->version]) }}"
                                class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 {{ $version->version == $selected_version ? 'active text-white' : '' }}">
                                <div>
                                    <div class="fw-bold">{{ __('daily_records.version_label', ['version' => $version->version]) }}</div>
                                    <div class="small {{ $version->version == $selected_version ? 'text-white-50' : 'text-muted' }}">
                                        {{ \App\Modules\DailyRecords\RecordTime::display($version->submitted_at) }} ICT
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right small"></i>
                            </a>
                        @endforeach
                    </div>
                    @if ($versions->hasPages())
                        <div class="p-2 border-top">
                            {{ $versions->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
