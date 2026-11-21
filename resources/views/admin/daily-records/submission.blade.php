@extends('layouts.master')
@section('title', 'Review daily summary')
@section('main-content')
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.daily-records.submissions') }}">Daily submissions</a>
@include('admin.daily-records.workflow-messages')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <h1 class="h4">{{ $report->title }}</h1>
            <p>{{ $report->employee }} · {{ ucfirst(str_replace('_',' ',$report->status)) }} · Version {{ $selected_version }}</p>
            <p class="small text-muted">{{ $report->department }} · {{ $report->location }} · Reporting date {{ $report->report_date }} ICT</p>
            @foreach (['summary' => 'Completed work', 'challenges' => 'Challenges', 'solutions' => 'Solutions', 'pending_work' => 'Pending work', 'recommendations' => 'Recommendations'] as $key => $label)
                @if (!empty($content[$key]))
                    <h2 class="h6">{{ $label }}</h2><p class="text-break" style="white-space:pre-wrap">{{ $content[$key] }}</p>
                @endif
            @endforeach
            <h2 class="h6">Related activities and incidents</h2>
            <ul>
                @forelse ($records as $record)
                    <li>
                        <a href="{{ $record->kind === 'incident' ? route('admin.daily-records.incident', $record->id) : route('admin.daily-records.index', ['entry' => $record->id]) }}">{{ $record->title }}</a>
                        · {{ ucfirst($record->kind) }}
                        @if ($record->body)<p class="small text-break mb-2" style="white-space:pre-wrap">{{ $record->body }}</p>@endif
                        @if ($record->solution)<p class="small text-break mb-2">Solution · {{ $record->solution }}</p>@endif
                    </li>
                @empty <li>No linked records.</li>
                @endforelse
            </ul>
            <h2 class="h6">Employee tasks (ICT)</h2>
            <ul>
                @forelse ($tasks as $task)
                    <li><a href="{{ route('admin.daily-records.index', ['assignment' => $task->id]) }}">{{ $task->title }}</a> · {{ ucfirst(str_replace('_',' ',$task->status)) }}</li>
                @empty <li>No matching tasks.</li>
                @endforelse
            </ul>
            {{ $tasks->withQueryString()->links() }}
            <a href="{{ route('admin.daily-records.index', ['view' => 'timeline', 'employee_id' => $report->employee_id]) }}">View employee history</a>
            · <a href="{{ route('admin.daily-records.create', ['follow_up' => $report->id]) }}">Assign follow-up task</a>
        </div></div>
        <div class="mt-3">@include('admin.daily-records.workflow-history')</div>
    </div>
    <div class="col-lg-4 d-grid gap-3 align-content-start">
        @if ($selected_version == $report->version)
            <div class="card"><div class="card-body">
                <h2 class="h6">Manager review</h2>
                <form method="POST" action="{{ route('admin.daily-records.submission-review', $report->id) }}" class="d-grid gap-2">
                    @csrf
                    <input type="hidden" name="version" value="{{ $report->version }}">
                    <label>Review notes<textarea class="form-control" name="message" maxlength="10000">{{ old('message') }}</textarea></label>
                    <div class="d-flex gap-2 flex-wrap">
                        @if ($report->status === 'submitted')<button class="btn btn-outline-primary" name="action" value="start">Start review</button>@endif
                        @if (in_array($report->status, ['submitted','under_review'], true))
                            <button class="btn btn-primary" name="action" value="approve">Approve</button>
                            <button class="btn btn-outline-warning" name="action" value="return">Return for correction</button>
                        @endif
                        <button class="btn btn-outline-secondary" name="action" value="comment">Add comment</button>
                    </div>
                </form>
            </div></div>
        @endif
        <div class="card"><div class="card-body">
            <h2 class="h6">Submission versions</h2>
            <ul>
                @foreach ($versions as $version)
                    <li><a href="{{ route('admin.daily-records.submission', ['entry' => $report->id, 'version' => $version->version]) }}">Version {{ $version->version }}</a>
                        · {{ \App\Modules\DailyRecords\RecordTime::display($version->submitted_at) }}</li>
                @endforeach
            </ul>
            {{ $versions->withQueryString()->links() }}
        </div></div>
    </div>
</div>
@endsection
