@extends('layouts.master')
@section('title', 'Incident management')
@section('main-content')
@php
    $recordTime = \App\Modules\DailyRecords\RecordTime::class;
    $classification = json_decode($entry->classification ?? '{}', true);
    $details = json_decode($entry->details ?? '{}', true);
    $action = route('admin.daily-records.incident-action', $entry->id);
@endphp
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.daily-records.index', ['kind' => 'incident']) }}">Incidents</a>
@include('admin.daily-records.workflow-messages')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-body">
            <div class="small text-muted">{{ $classification['category']['en'] ?? 'Incident' }} @if (!empty($classification['subcategory'])) · {{ $classification['subcategory']['en'] }} @endif</div>
            <h1 class="h4">{{ $entry->title }}</h1>
            <p>{{ ucfirst(str_replace('_', ' ', $entry->status)) }} · {{ ucfirst($entry->priority ?? 'Normal') }} @if ($entry->sensitive) · Confidential @endif</p>
            <dl class="row">
                <dt class="col-5">Occurred (ICT)</dt><dd class="col-7">{{ $recordTime::display($entry->occurred_at) }}</dd>
                <dt class="col-5">Recorded (ICT)</dt><dd class="col-7">{{ $recordTime::display($entry->created_at) }}</dd>
                <dt class="col-5">Follow-up (ICT)</dt><dd class="col-7">{{ $recordTime::display($entry->follow_up_at) }}</dd>
                @if ($entry->responsible_id)<dt class="col-5">Responsible employee ID</dt><dd class="col-7">{{ $entry->responsible_id }}</dd>@endif
                @if ($entry->verification)<dt class="col-5">Verification</dt><dd class="col-7">{{ ucfirst(str_replace('_',' ',$entry->verification)) }}</dd>@endif
            </dl>
            <p class="text-break" style="white-space:pre-wrap">{{ $entry->body }}</p>
            @foreach (['immediate_action' => 'Immediate action', 'proposed_solution' => 'Proposed solution'] as $key => $label)
                @if (!empty($details[$key]))<p><strong>{{ $label }}</strong><br>{{ $details[$key] }}</p>@endif
            @endforeach
            @if ($entry->solution)<p><strong>Actual solution</strong><br>{{ $entry->solution }}</p>@endif
            @foreach ($classification['fields'] ?? [] as $field)
                @if (isset($details['fields'][$field['key']]))
                    <p><strong>{{ $field['label_en'] }}</strong><br>{{ is_array($details['fields'][$field['key']]) ? implode(', ', $details['fields'][$field['key']]) : $details['fields'][$field['key']] }}</p>
                @endif
            @endforeach
            @if (!$entry->sensitive)
                <a class="btn btn-outline-primary" href="{{ route('admin.daily-records.create', ['follow_up' => $entry->id]) }}">Create follow-up task</a>
            @endif
        </div></div>
        @include('admin.daily-records.workflow-history')
    </div>
    <div class="col-lg-5 d-grid gap-3 align-content-start">
        @if ($states)
            <div class="card"><div class="card-body">
                <h2 class="h6">Operational status</h2>
                <form method="POST" action="{{ $action }}" class="d-grid gap-2">
                    @csrf
                    <input type="hidden" name="action" value="state">
                    <label>Next status<select class="form-select" name="state" required>
                        @foreach ($states as $state)<option value="{{ $state }}">{{ ucfirst(str_replace('_',' ',$state)) }}</option>@endforeach
                    </select></label>
                    <label>Solution when resolving<textarea name="solution" class="form-control" maxlength="10000"></textarea></label>
                    <label>Follow-up (ICT)<input type="datetime-local" name="follow_up_at" class="form-control"></label>
                    <button class="btn btn-primary">Update status</button>
                </form>
            </div></div>
        @endif
        <div class="card"><div class="card-body">
            <h2 class="h6">Assign responsibility</h2>
            <form method="POST" action="{{ $action }}" class="d-grid gap-2">
                @csrf
                <input type="hidden" name="action" value="assign">
                @include('admin.daily-records.employee-picker', ['pickerName' => 'responsible_id'])
                <label>Follow-up (ICT)<input type="datetime-local" name="follow_up_at" class="form-control"></label>
                <button class="btn btn-outline-primary">Assign incident</button>
            </form>
        </div></div>
        <div class="card"><div class="card-body">
            <h2 class="h6">Communication</h2>
            <form method="POST" action="{{ $action }}" class="d-grid gap-2">
                @csrf
                <label>Message to reporter<textarea name="message" class="form-control" required maxlength="10000"></textarea></label>
                <label>Follow-up (ICT)<input type="datetime-local" name="follow_up_at" class="form-control"></label>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary" name="action" value="comment">Comment</button>
                    <button class="btn btn-outline-primary" name="action" value="request_info">Request information</button>
                    <button class="btn btn-outline-danger" name="action" value="escalate">Escalate</button>
                </div>
            </form>
        </div></div>
        @if ($findings)
            <div class="card"><div class="card-body">
                <h2 class="h6">Confidential verification</h2>
                <form method="POST" enctype="multipart/form-data" action="{{ $action }}" class="d-grid gap-2">
                    @csrf
                    <input type="hidden" name="action" value="verify">
                    <label>Finding<select class="form-select" name="verification" required>
                        @foreach ($findings as $finding)<option value="{{ $finding }}">{{ ucfirst(str_replace('_',' ',$finding)) }}</option>@endforeach
                    </select></label>
                    <label>Review notes<textarea name="message" class="form-control" required maxlength="10000"></textarea></label>
                    <label>Employee response<textarea name="employee_response" class="form-control" maxlength="10000"></textarea></label>
                    <label>Decision<textarea name="decision" class="form-control" maxlength="10000"></textarea></label>
                    <label>Restricted evidence<input class="form-control" type="file" name="files[]" multiple></label>
                    <button class="btn btn-outline-primary">Save finding</button>
                </form>
            </div></div>
        @endif
        <div class="card"><div class="card-body">
            <details>
                <summary>Reclassify incident</summary>
                <form method="POST" action="{{ $action }}" class="d-grid gap-2 mt-2">
                    @csrf
                    <input type="hidden" name="action" value="reclassify">
                    <label>Category<select class="form-select" required name="category_id" id="incident-category">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)<option value="{{ $category['id'] }}">{{ $category['name_en'] }}</option>@endforeach
                    </select></label>
                    <label>Subcategory<select class="form-select" required name="subcategory_id" id="incident-subcategory">
                        <option value="">Select subcategory</option>
                        @foreach ($categories as $category)
                            @foreach ($category['children'] as $child)
                                <option value="{{ $child['id'] }}" data-category="{{ $category['id'] }}">{{ $child['name_en'] }}</option>
                            @endforeach
                        @endforeach
                    </select></label>
                    <label>Configured fields (JSON)<textarea name="fields_json" class="form-control" placeholder="{}"></textarea></label>
                    <button class="btn btn-outline-primary">Save classification</button>
                </form>
            </details>
        </div></div>
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
