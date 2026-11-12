@extends('layouts.master')
@section('title', 'Social Media Marketing Submission')
@section('action', 'Detail')
@section('button')
@can('social_reward.manage')
<a class="btn btn-primary" href="{{ route('admin.social-marketing.edit', $record) }}">Override / Edit</a>
@endcan
@endsection
@section('main-content')
@include('admin.section.flash_message')
<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-body">
            <h5 class="mb-3">{{ $record->employee?->name ?? 'Employee #'.$record->existing_employee_id }}</h5>
            <table class="table table-sm">
                <tr><th>Employee code</th><td>{{ $record->employee?->employee_code ?? '—' }}</td></tr>
                <tr><th>Branch / department</th><td>{{ $record->employee?->branch?->name ?? '—' }} / {{ $record->employee?->department?->dept_name ?? '—' }}</td></tr>
                <tr><th>Submission date</th><td>{{ optional($record->log_date)->format('Y-m-d') }}</td></tr>
                <tr><th>Reward points</th><td>{{ $record->reward_points }}</td></tr>
                <tr><th>Status</th><td><span class="badge bg-{{ $record->is_locked ? 'warning' : 'success' }}">{{ $record->is_locked ? 'Locked' : 'Overridden' }}</span></td></tr>
                <tr><th>Facebook post</th><td><a href="{{ $record->fb_post_url }}" target="_blank" rel="noopener">{{ $record->fb_post_url }}</a></td></tr>
                <tr><th>Facebook story</th><td><a href="{{ $record->fb_story_url }}" target="_blank" rel="noopener">{{ $record->fb_story_url }}</a></td></tr>
                <tr><th>TikTok</th><td><a href="{{ $record->tiktok_url }}" target="_blank" rel="noopener">{{ $record->tiktok_url }}</a></td></tr>
            </table>
            <div class="row">
                @foreach(['fb_post_photo' => 'Facebook post photo', 'fb_story_photo' => 'Facebook story photo', 'tiktok_photo' => 'TikTok photo'] as $field => $label)
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">{{ $label }}</p>
                        @if($record->{$field})
                            <a href="{{ $record->photoUrl($record->{$field}) }}" target="_blank" rel="noopener"><img class="img-fluid rounded border" src="{{ $record->photoUrl($record->{$field}) }}" alt="{{ $label }}"></a>
                        @else
                            <p class="text-muted">Not uploaded</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3"><div class="card-body">
            <h5>Override audit trail</h5>
            @forelse($record->audits as $audit)
                <div class="border-bottom py-2">
                    <div class="d-flex justify-content-between"><strong>{{ $audit->admin?->name ?? 'Admin #'.$audit->admin_id }}</strong><span class="small text-muted">{{ optional($audit->created_at)->format('Y-m-d H:i') }}</span></div>
                    <p class="mb-0" style="white-space:pre-wrap">{{ $audit->reason }}</p>
                </div>
            @empty
                <p class="text-muted mb-0">No overrides recorded.</p>
            @endforelse
        </div></div>
        @can('social_reward.manage')
        <div class="card"><div class="card-body">
            <form method="post" action="{{ route('admin.social-marketing.destroy', $record) }}" onsubmit="return confirm('Delete this submission and its photos?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger">Delete Submission</button>
            </form>
        </div></div>
        @endcan
    </div>
</div>
@endsection
