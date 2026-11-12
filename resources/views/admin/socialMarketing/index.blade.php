@extends('layouts.master')
@section('title', 'Social Media Marketing')
@section('action', 'List')
@section('button')
@can('social_reward.manage')
<a class="btn btn-primary" href="{{ route('admin.social-marketing.create') }}">Add Submission</a>
@endcan
@endsection
@section('main-content')
@include('admin.section.flash_message')
<div class="row mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Matching submissions</p><h4 class="mb-0">{{ number_format($summary->total ?? 0) }}</h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Total points</p><h4 class="mb-0">{{ number_format($summary->points ?? 0) }}</h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Employees</p><h4 class="mb-0">{{ number_format($summary->employees ?? 0) }}</h4></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><p class="text-muted mb-1">Today / locked</p><h4 class="mb-0">{{ number_format($todayCount) }} / {{ number_format($summary->locked ?? 0) }}</h4></div></div></div>
</div>
<div class="card mb-3"><div class="card-body">
<form method="get" class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label" for="search">Employee</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] }}" placeholder="Name, code or username"></div>
    <div class="col-md-2"><label class="form-label" for="date_from">From</label><input class="form-control" type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}"></div>
    <div class="col-md-2"><label class="form-label" for="date_to">To</label><input class="form-control" type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}"></div>
    <div class="col-md-2"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All</option><option value="locked" @selected($filters['status'] === 'locked')>Locked</option><option value="unlocked" @selected($filters['status'] === 'unlocked')>Overridden</option></select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    <div class="col-md-1"><a class="btn btn-light w-100" href="{{ route('admin.social-marketing.index') }}">Reset</a></div>
</form>
</div></div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table align-middle">
<thead><tr><th>Date</th><th>Employee</th><th>Links</th><th>Photos</th><th>Points</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
@forelse($records as $record)
<tr>
    <td>{{ optional($record->log_date)->format('Y-m-d') }}</td>
    <td>{{ $record->employee?->name ?? 'Employee #'.$record->existing_employee_id }}<br><span class="small text-muted">{{ $record->employee?->employee_code }}</span></td>
    <td class="small">
        <a href="{{ $record->fb_post_url }}" target="_blank" rel="noopener">FB post</a><br>
        <a href="{{ $record->fb_story_url }}" target="_blank" rel="noopener">FB story</a><br>
        <a href="{{ $record->tiktok_url }}" target="_blank" rel="noopener">TikTok</a>
    </td>
    <td class="small">
        @foreach(['fb_post_photo' => 'FB post', 'fb_story_photo' => 'FB story', 'tiktok_photo' => 'TikTok'] as $field => $label)
            @if($record->{$field})
                <a href="{{ $record->photoUrl($record->{$field}) }}" target="_blank" rel="noopener">{{ $label }}</a><br>
            @endif
        @endforeach
    </td>
    <td>{{ $record->reward_points }}</td>
    <td><span class="badge bg-{{ $record->is_locked ? 'warning' : 'success' }}">{{ $record->is_locked ? 'Locked' : 'Overridden' }}</span></td>
    <td>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.social-marketing.show', $record) }}">View</a>
        @can('social_reward.manage')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.social-marketing.edit', $record) }}">Edit</a>
        <form class="d-inline" method="post" action="{{ route('admin.social-marketing.destroy', $record) }}" onsubmit="return confirm('Delete this submission and its photos?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
        @endcan
    </td>
</tr>
@empty
<tr><td colspan="7">No Social Media Marketing submissions match these filters.</td></tr>
@endforelse
</tbody></table></div>
{{ $records->links() }}
</div></div>
@endsection
