@extends('layouts.master')

@section('title', 'Assign Employee Task')

@section('main-content')
<a href="{{ route('admin.daily-records.index') }}" class="btn btn-sm btn-outline-secondary mb-3">Task &amp; Report Center</a>
<div class="card"><div class="card-body">
    <h1 class="h4 mb-3">Assign Employee Task</h1>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul></div>
    @endif
    <form action="{{ route('admin.daily-records.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
        @csrf
        <input type="hidden" name="request_id" value="{{ old('request_id', (string) \Illuminate\Support\Str::uuid()) }}">
        @if ($followUp)
            <input type="hidden" name="follow_up_entry_id" value="{{ $followUp->id }}">
            <p>Follow-up to {{ $followUp->title }}</p>
        @endif
        <label class="col-md-8">Title
            <input class="form-control" name="title" value="{{ old('title', $followUp ? 'Follow-up: '.mb_substr($followUp->title, 0, 168) : '') }}" maxlength="180" required>
        </label>
        <label class="col-md-4">Priority
            <select class="form-select" name="priority">
                @foreach (['low', 'normal', 'high', 'urgent'] as $priority)
                    <option @selected(old('priority', 'normal') === $priority) value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                @endforeach
            </select>
        </label>
        <div class="col-12">
            @include('admin.daily-records.employee-picker', ['pickerName' => 'employee_ids[]', 'pickerSelected' => $selected ? [(array) $selected] : []])
            <small class="text-muted">Select employees, or choose a department or location to assign to everyone you manage in that group.</small>
        </div>
        <label class="col-md-6">Department
            <select class="form-select" name="department_id"><option value="">Any department</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="col-md-6">Location
            <select class="form-select" name="branch_id"><option value="">Any location</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected(old('branch_id') == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="col-md-4">Deadline (ICT)
            <input class="form-control" type="datetime-local" name="due_at" value="{{ old('due_at') }}" required>
        </label>
        <label class="col-md-4">Reminder (ICT)
            <input class="form-control" type="datetime-local" name="remind_at" value="{{ old('remind_at') }}">
        </label>
        <label class="col-md-4">Repeat
            <select class="form-select" name="frequency"><option value="">Once</option>
                @foreach (['daily', 'weekly', 'monthly'] as $frequency)
                    <option @selected(old('frequency') === $frequency) value="{{ $frequency }}">{{ ucfirst($frequency) }}</option>
                @endforeach
            </select>
        </label>
        <label class="col-12">Instructions
            <textarea class="form-control" name="instructions" rows="4" maxlength="10000">{{ old('instructions') }}</textarea>
        </label>
        <label class="col-12">Checklist (one item per line)
            <textarea class="form-control" name="checklist" rows="3">{{ implode("\n", (array) old('checklist', [])) }}</textarea>
        </label>
        <label class="col-12">Attachments (up to 4, 10 MB each)
            <input class="form-control" type="file" name="files[]" multiple>
        </label>
        <div class="col-12"><button class="btn btn-primary" type="submit">Assign Task</button></div>
    </form>
</div></div>
@include('admin.daily-records.picker-script')
@endsection
