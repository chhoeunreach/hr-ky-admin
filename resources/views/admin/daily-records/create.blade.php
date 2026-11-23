@extends('layouts.master')

@section('title', __('daily_records.assign_new_employee_task'))

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
                        <i class="fa-solid fa-list-check me-1"></i> {{ __('daily_records.work_and_operations') }}
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.assign_task') }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.daily-records.index') }}" class="dr-btn-outline py-1 px-3">
                <i class="fa-solid fa-arrow-left"></i> {{ __('daily_records.back_to_center') }}
            </a>
            <a href="{{ route('admin.daily-records.branch-routes') }}" class="dr-btn-outline py-1 px-3">
                <i class="fa-solid fa-route"></i> {{ __('daily_records.branch_route_management') }}
            </a>
            <a href="{{ route('admin.daily-records.submissions') }}" class="dr-btn-outline py-1 px-3">
                <i class="fa-regular fa-calendar-check"></i> {{ __('daily_records.daily_submissions') }}
            </a>
        </div>
    </div>

    {{-- Hero Banner --}}
    <div class="dr-hero-card">
        <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 1;">
            <div class="dr-hero-icon-box">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div>
                <h1 class="dr-hero-title">{{ __('daily_records.assign_new_employee_task') }}</h1>
                <p class="dr-hero-subtitle">
                    {{ __('daily_records.assign_new_task_subtitle') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2 shadow-sm border-0 mb-0" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1"></i>
            <ul class="mb-0 ps-2">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Form Card --}}
    <div class="dr-card">
        <div class="dr-card-header">
            <h2 class="dr-card-title">
                <i class="fa-solid fa-file-pen text-primary"></i> {{ __('daily_records.assignment_configuration') }}
            </h2>
            <span class="dr-meta-badge">{{ __('daily_records.standard_workflow') }}</span>
        </div>
        <div class="dr-card-body">
            <form action="{{ route('admin.daily-records.store') }}" method="POST" enctype="multipart/form-data" class="row g-4">
                @csrf
                <input type="hidden" name="request_id" value="{{ old('request_id', (string) \Illuminate\Support\Str::uuid()) }}">
                
                @if ($followUp)
                    <input type="hidden" name="follow_up_entry_id" value="{{ $followUp->id }}">
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                            <i class="fa-solid fa-link fs-5"></i>
                            <div><strong>{{ __('daily_records.follow_up_action_for') }}</strong> {{ $followUp->title }}</div>
                        </div>
                    </div>
                @endif

                {{-- Section 1: Basic Information --}}
                <div class="col-12">
                    <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> {{ __('daily_records.section_1_basic') }}
                    </h3>
                </div>

                <div class="col-md-8 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-heading me-1"></i> {{ __('daily_records.task_title') }} <span class="text-danger">*</span></label>
                    <input class="dr-form-control" name="title" value="{{ old('title', $followUp ? 'Follow-up: '.mb_substr($followUp->title, 0, 168) : '') }}" maxlength="180" placeholder="{{ __('daily_records.task_title_placeholder') }}" required>
                </div>

                <div class="col-md-4 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-flag me-1"></i> {{ __('daily_records.priority') }}</label>
                    <select class="dr-form-select" name="priority">
                        @foreach ([
                            'low' => __('daily_records.priority_low'),
                            'normal' => __('daily_records.priority_normal'),
                            'high' => __('daily_records.priority_high'),
                            'urgent' => __('daily_records.priority_urgent')
                        ] as $priorityKey => $priorityTitle)
                            <option @selected(old('priority', 'normal') === $priorityKey) value="{{ $priorityKey }}">{{ $priorityTitle }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section 2: Assignment Scope --}}
                <div class="col-12 pt-2">
                    <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-users text-primary me-1"></i> {{ __('daily_records.section_2_scope') }}
                    </h3>
                </div>

                <div class="col-12 dr-form-group">
                    @include('admin.daily-records.employee-picker', ['pickerName' => 'employee_ids[]', 'pickerSelected' => $selected ? [(array) $selected] : []])
                    <div class="form-text text-muted mt-1">
                        <i class="fa-solid fa-info-circle me-1"></i> {{ __('daily_records.scope_helper') }}
                    </div>
                </div>

                <div class="col-md-6 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-building me-1"></i> {{ __('daily_records.department') }} ({{ __('daily_records.any_department') }})</label>
                    <select class="dr-form-select" name="department_id">
                        <option value="">{{ __('daily_records.any_department') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-location-dot me-1"></i> {{ __('daily_records.location_branch') }} ({{ __('daily_records.any_location') }})</label>
                    <select class="dr-form-select" name="branch_id">
                        <option value="">{{ __('daily_records.any_location') }}</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('branch_id') == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section 3: Schedule & Recurrence --}}
                <div class="col-12 pt-2">
                    <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-regular fa-calendar-check text-primary me-1"></i> {{ __('daily_records.section_3_schedule') }}
                    </h3>
                </div>

                <div class="col-md-4 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-hourglass-end me-1"></i> {{ __('daily_records.deadline_ict') }} <span class="text-danger">*</span></label>
                    <input class="dr-form-control" type="datetime-local" name="due_at" value="{{ old('due_at') }}" required>
                </div>

                <div class="col-md-4 dr-form-group">
                    <label class="dr-form-label"><i class="fa-regular fa-bell me-1"></i> {{ __('daily_records.reminder_alert_ict') }}</label>
                    <input class="dr-form-control" type="datetime-local" name="remind_at" value="{{ old('remind_at') }}">
                </div>

                <div class="col-md-4 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-repeat me-1"></i> {{ __('daily_records.repeat_cadence') }}</label>
                    <select class="dr-form-select" name="frequency">
                        <option value="">{{ __('daily_records.one_time_task') }}</option>
                        @foreach ([
                            'daily' => __('daily_records.cadence_daily'),
                            'weekly' => __('daily_records.cadence_weekly'),
                            'monthly' => __('daily_records.cadence_monthly')
                        ] as $frequencyKey => $frequencyLabel)
                            <option @selected(old('frequency') === $frequencyKey) value="{{ $frequencyKey }}">{{ $frequencyLabel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section 4: Instructions & Checklist --}}
                <div class="col-12 pt-2">
                    <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-list-ol text-primary me-1"></i> {{ __('daily_records.section_4_instructions') }}
                    </h3>
                </div>

                <div class="col-12 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-align-left me-1"></i> {{ __('daily_records.detailed_instructions') }}</label>
                    <textarea class="dr-form-control" name="instructions" rows="4" maxlength="10000" placeholder="{{ __('daily_records.instructions_placeholder') }}">{{ old('instructions') }}</textarea>
                </div>

                <div class="col-12 dr-form-group">
                    <label class="dr-form-label"><i class="fa-solid fa-square-check me-1"></i> {{ __('daily_records.checklist_items') }}</label>
                    <textarea class="dr-form-control" name="checklist" rows="3" placeholder="{{ __('daily_records.checklist_placeholder') }}">{{ implode("\n", (array) old('checklist', [])) }}</textarea>
                    <div class="form-text text-muted">{{ __('daily_records.checklist_helper') }}</div>
                </div>

                {{-- Section 5: Attachments --}}
                <div class="col-12 pt-2">
                    <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-paperclip text-primary me-1"></i> {{ __('daily_records.section_5_attachments') }}
                    </h3>
                </div>

                <div class="col-12 dr-form-group">
                    <label class="dr-form-label">{{ __('daily_records.upload_documents') }}</label>
                    <input class="dr-form-control" type="file" name="files[]" multiple>
                    <div class="form-text text-muted">{{ __('daily_records.upload_helper') }}</div>
                </div>

                {{-- Submit Buttons --}}
                <div class="col-12 d-flex align-items-center gap-3 pt-3 border-top">
                    <button class="dr-btn-primary px-4 py-2" type="submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>{{ __('daily_records.assign_task_now') }}</span>
                    </button>
                    <a href="{{ route('admin.daily-records.index') }}" class="dr-btn-outline px-3 py-2">
                        {{ __('daily_records.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@include('admin.daily-records.picker-script')
@endsection
