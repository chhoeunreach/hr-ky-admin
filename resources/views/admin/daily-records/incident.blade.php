@extends('layouts.master')

@section('title', __('daily_records.incident_management'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/daily-records.css') }}?v={{ filemtime(public_path('assets/css/daily-records.css')) }}">
@endsection

@section('main-content')
@php
    $recordTime = \App\Modules\DailyRecords\RecordTime::class;
    $classification = json_decode($entry->classification ?? '{}', true);
    $details = json_decode($entry->details ?? '{}', true);
    $action = route('admin.daily-records.incident-action', $entry->id);
    $isKm = \Illuminate\Support\Facades\App::getLocale() === 'km';
@endphp

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
                    <a href="{{ route('admin.daily-records.index', ['kind' => 'incident']) }}" class="text-decoration-none">{{ __('daily_records.kpi_incidents') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('daily_records.incident_resolution') }}</li>
            </ol>
        </nav>
        <a class="dr-btn-outline py-1 px-3" href="{{ route('admin.daily-records.index', ['kind' => 'incident']) }}">
            <i class="fa-solid fa-arrow-left"></i> {{ __('daily_records.back_to_incidents') }}
        </a>
    </div>

    @include('admin.daily-records.workflow-messages')

    <div class="row g-3">
        {{-- Left Column: Incident Details --}}
        <div class="col-lg-7">
            <div class="dr-card mb-3">
                <div class="dr-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dr-hero-icon-box" style="width: 44px; height: 44px; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); font-size: 1.25rem;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="small text-muted mb-1">
                                {{ ($isKm && !empty($classification['category']['km'])) ? $classification['category']['km'] : ($classification['category']['en'] ?? __('daily_records.type_incident')) }}
                                @if (!empty($classification['subcategory']))
                                    · {{ ($isKm && !empty($classification['subcategory']['km'])) ? $classification['subcategory']['km'] : $classification['subcategory']['en'] }}
                                @endif
                            </div>
                            <h1 class="h5 fw-bold text-dark mb-0">{{ $entry->title }}</h1>
                        </div>
                    </div>
                </div>
                <div class="dr-card-body">
                    {{-- Status Pills --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="dr-badge dr-badge-danger">
                            <span class="dr-badge-dot"></span>
                            {{ ucfirst(str_replace('_', ' ', $entry->status)) }}
                        </span>
                        <span class="dr-badge {{ match($entry->priority ?? 'normal') { 'urgent' => 'dr-priority-urgent', 'high' => 'dr-priority-high', default => 'dr-priority-normal' } }}">
                            {{ __('daily_records.priority_' . ($entry->priority ?? 'normal')) !== 'daily_records.priority_' . ($entry->priority ?? 'normal') ? __('daily_records.priority_' . ($entry->priority ?? 'normal')) : ucfirst($entry->priority ?? 'Normal') }}
                        </span>
                        @if ($entry->sensitive)
                            <span class="dr-badge dr-badge-purple">
                                <i class="fa-solid fa-lock me-1"></i> {{ __('daily_records.confidential') }}
                            </span>
                        @endif
                    </div>

                    {{-- Metadata Grid --}}
                    <div class="dr-meta-grid mb-3">
                        <div class="dr-meta-item">
                            <span class="dr-meta-title">{{ __('daily_records.occurred_time') }}</span>
                            <span class="dr-meta-val"><i class="fa-regular fa-clock me-1 text-muted"></i> {{ $recordTime::display($entry->occurred_at) }} ICT</span>
                        </div>
                        <div class="dr-meta-item">
                            <span class="dr-meta-title">{{ __('daily_records.recorded_in_system') }}</span>
                            <span class="dr-meta-val"><i class="fa-regular fa-calendar-check me-1 text-muted"></i> {{ $recordTime::display($entry->created_at) }} ICT</span>
                        </div>
                        <div class="dr-meta-item">
                            <span class="dr-meta-title">{{ __('daily_records.follow_up_target') }}</span>
                            <span class="dr-meta-val text-warning"><i class="fa-solid fa-hourglass-end me-1"></i> {{ $recordTime::display($entry->follow_up_at) }}</span>
                        </div>
                        @if ($entry->responsible_id)
                            <div class="dr-meta-item">
                                <span class="dr-meta-title">{{ __('daily_records.responsible_staff_id') }}</span>
                                <span class="dr-meta-val text-primary"><i class="fa-solid fa-user me-1"></i> {{ $entry->responsible_id }}</span>
                            </div>
                        @endif
                        @if ($entry->verification)
                            <div class="dr-meta-item col-12">
                                <span class="dr-meta-title">{{ __('daily_records.investigation_finding') }}</span>
                                <span class="dr-meta-val text-success">{{ ucfirst(str_replace('_',' ',$entry->verification)) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Narrative / Body --}}
                    <div class="mb-3">
                        <h3 class="dr-form-label"><i class="fa-solid fa-align-left me-1"></i> {{ __('daily_records.incident_narrative') }}</h3>
                        <div class="dr-instruction-box text-break" style="white-space: pre-wrap;">{{ $entry->body }}</div>
                    </div>

                    {{-- Actions and Solutions --}}
                    @foreach (['immediate_action' => __('daily_records.immediate_action_taken'), 'proposed_solution' => __('daily_records.proposed_resolution')] as $key => $label)
                        @if (!empty($details[$key]))
                            <div class="mb-3">
                                <h3 class="dr-form-label"><i class="fa-solid fa-bolt text-warning me-1"></i> {{ $label }}</h3>
                                <div class="p-3 bg-light rounded text-break">{{ $details[$key] }}</div>
                            </div>
                        @endif
                    @endforeach

                    @if ($entry->solution)
                        <div class="mb-3">
                            <h3 class="dr-form-label text-success"><i class="fa-solid fa-check text-success me-1"></i> {{ __('daily_records.confirmed_solution') }}</h3>
                            <div class="p-3 bg-success-subtle border border-success-subtle rounded text-break">{{ $entry->solution }}</div>
                        </div>
                    @endif

                    @foreach ($classification['fields'] ?? [] as $field)
                        @if (isset($details['fields'][$field['key']]))
                            @php
                                $fieldLabel = $isKm && !empty($field['label_km']) ? $field['label_km'] : ($field['label_en'] ?? $field['key']);
                            @endphp
                            <div class="mb-3">
                                <h3 class="dr-form-label">{{ $fieldLabel }}</h3>
                                <div class="p-2 border rounded bg-light">{{ is_array($details['fields'][$field['key']]) ? implode(', ', $details['fields'][$field['key']]) : $details['fields'][$field['key']] }}</div>
                            </div>
                        @endif
                    @endforeach

                    @if (!$entry->sensitive)
                        <div class="pt-2 border-top">
                            <a class="dr-btn-primary btn-sm" href="{{ route('admin.daily-records.create', ['follow_up' => $entry->id]) }}">
                                <i class="fa-solid fa-plus"></i> {{ __('daily_records.create_follow_up_task') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @include('admin.daily-records.workflow-history')
        </div>

        {{-- Right Column: Operations & Responses --}}
        <div class="col-lg-5 d-grid gap-3 align-content-start">
            {{-- Operational Status --}}
            @if ($states)
                <div class="dr-card">
                    <div class="dr-card-header">
                        <h2 class="dr-card-title">
                            <i class="fa-solid fa-arrows-spin text-primary"></i> {{ __('daily_records.update_operational_status') }}
                        </h2>
                    </div>
                    <div class="dr-card-body">
                        <form method="POST" action="{{ $action }}" class="d-grid gap-3">
                            @csrf
                            <input type="hidden" name="action" value="state">
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.next_target_status') }}</label>
                                <select class="dr-form-select" name="state" required>
                                    @foreach ($states as $state)
                                        <option value="{{ $state }}">{{ ucfirst(str_replace('_',' ',$state)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.solution_provided_resolving') }}</label>
                                <textarea name="solution" class="dr-form-control" rows="3" placeholder="{{ __('daily_records.solution_placeholder') }}" maxlength="10000"></textarea>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.next_follow_up_ict') }}</label>
                                <input type="datetime-local" name="follow_up_at" class="dr-form-control">
                            </div>
                            <button class="dr-btn-primary" type="submit">
                                <i class="fa-solid fa-check"></i> {{ __('daily_records.commit_status_update') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Assign Responsibility --}}
            <div class="dr-card">
                <div class="dr-card-header">
                    <h2 class="dr-card-title">
                        <i class="fa-solid fa-user-plus text-primary"></i> {{ __('daily_records.assign_case_responsibility') }}
                    </h2>
                </div>
                <div class="dr-card-body">
                    <form method="POST" action="{{ $action }}" class="d-grid gap-3">
                        @csrf
                        <input type="hidden" name="action" value="assign">
                        <div class="dr-form-group">
                            @include('admin.daily-records.employee-picker', ['pickerName' => 'responsible_id'])
                        </div>
                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.follow_up_target') }} (ICT)</label>
                            <input type="datetime-local" name="follow_up_at" class="dr-form-control">
                        </div>
                        <button class="dr-btn-outline" type="submit">
                            <i class="fa-solid fa-user-shield"></i> {{ __('daily_records.assign_incident_lead') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Communication / Comments --}}
            <div class="dr-card">
                <div class="dr-card-header">
                    <h2 class="dr-card-title">
                        <i class="fa-solid fa-comments text-primary"></i> {{ __('daily_records.case_communication') }}
                    </h2>
                </div>
                <div class="dr-card-body">
                    <form method="POST" action="{{ $action }}" class="d-grid gap-3">
                        @csrf
                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.message_to_reporter') }}</label>
                            <textarea name="message" class="dr-form-control" rows="3" placeholder="{{ __('daily_records.communication_placeholder') }}" required maxlength="10000"></textarea>
                        </div>
                        <div class="dr-form-group">
                            <label class="dr-form-label">{{ __('daily_records.follow_up_target') }} (ICT)</label>
                            <input type="datetime-local" name="follow_up_at" class="dr-form-control">
                        </div>
                        <div class="d-flex flex-wrap gap-2 pt-1">
                            <button class="dr-btn-outline btn-sm" name="action" value="comment">
                                <i class="fa-regular fa-comment"></i> {{ __('daily_records.comment') }}
                            </button>
                            <button class="dr-btn-outline btn-sm" name="action" value="request_info">
                                <i class="fa-solid fa-circle-question text-info"></i> {{ __('daily_records.request_info') }}
                            </button>
                            <button class="btn btn-sm btn-outline-danger" name="action" value="escalate">
                                <i class="fa-solid fa-arrow-up-right-dots"></i> {{ __('daily_records.escalate_case') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Confidential Verification --}}
            @if ($findings)
                <div class="dr-card border-purple">
                    <div class="dr-card-header">
                        <h2 class="dr-card-title">
                            <i class="fa-solid fa-user-secret text-purple"></i> {{ __('daily_records.confidential_investigation_finding') }}
                        </h2>
                    </div>
                    <div class="dr-card-body">
                        <form method="POST" enctype="multipart/form-data" action="{{ $action }}" class="d-grid gap-3">
                            @csrf
                            <input type="hidden" name="action" value="verify">
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.verification_finding_result') }}</label>
                                <select class="dr-form-select" name="verification" required>
                                    @foreach ($findings as $finding)
                                        <option value="{{ $finding }}">{{ ucfirst(str_replace('_',' ',$finding)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.investigator_notes') }}</label>
                                <textarea name="message" class="dr-form-control" rows="2" placeholder="{{ __('daily_records.investigator_notes_placeholder') }}" required maxlength="10000"></textarea>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.employee_response_statement') }}</label>
                                <textarea name="employee_response" class="dr-form-control" rows="2" placeholder="{{ __('daily_records.employee_response_statement') }}..." maxlength="10000"></textarea>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.formal_management_decision') }}</label>
                                <textarea name="decision" class="dr-form-control" rows="2" placeholder="{{ __('daily_records.decision_placeholder') }}" maxlength="10000"></textarea>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.restricted_evidence_files') }}</label>
                                <input class="dr-form-control" type="file" name="files[]" multiple>
                            </div>
                            <button class="dr-btn-primary" type="submit">
                                <i class="fa-solid fa-lock"></i> {{ __('daily_records.save_confidential_finding') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Reclassify Accordion --}}
            <div class="dr-card">
                <div class="dr-card-body p-3">
                    <details>
                        <summary class="fw-bold text-dark cursor-pointer">
                            <i class="fa-solid fa-tags text-primary me-1"></i> {{ __('daily_records.reclassify_incident_category') }}
                        </summary>
                        <form method="POST" action="{{ $action }}" class="d-grid gap-3 mt-3">
                            @csrf
                            <input type="hidden" name="action" value="reclassify">
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.category') }}</label>
                                <select class="dr-form-select" required name="category_id" id="incident-category">
                                    <option value="">{{ __('daily_records.all_categories') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['name_en'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.subcategory') }}</label>
                                <select class="dr-form-select" required name="subcategory_id" id="incident-subcategory">
                                    <option value="">{{ __('daily_records.all_subcategories') }}</option>
                                    @foreach ($categories as $category)
                                        @foreach ($category['children'] as $child)
                                            <option value="{{ $child['id'] }}" data-category="{{ $category['id'] }}">{{ $child['name_en'] }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="dr-form-group">
                                <label class="dr-form-label">{{ __('daily_records.field_definitions') }}</label>
                                <textarea name="fields_json" class="dr-form-control font-monospace" rows="3" placeholder="{}"></textarea>
                            </div>
                            <button class="dr-btn-outline" type="submit">
                                <i class="fa-solid fa-check"></i> {{ __('daily_records.save_new_classification') }}
                            </button>
                        </form>
                    </details>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.daily-records.picker-script')
<script>
(() => {
    const category = document.getElementById('incident-category');
    const child = document.getElementById('incident-subcategory');
    const update = () => {
        child.value = '';
        for (const option of child.options) {
            option.hidden = Boolean(option.dataset.category) && option.dataset.category !== category.value;
        }
    };
    category.addEventListener('change', update);
    update();
})();
</script>
@endsection
