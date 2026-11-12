@extends('layouts.master')
@section('title', 'Social Feed Reports')
@section('action', 'Report review')
@section('styles')
<style>
.sr-page{--line:#e8edf3;--muted:#718096;color:#172b4d}.sr-hero{border:0;border-radius:14px;background:linear-gradient(120deg,#283b70,#5a4c8c);color:#fff}.sr-hero h2{color:#fff;font-size:1.4rem;font-weight:700;margin:0 0 5px}.sr-hero p{color:rgba(255,255,255,.78);margin:0}.sr-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.sr-stat{display:block;border:1px solid var(--line);border-radius:11px;padding:13px 15px;background:white;text-decoration:none;color:inherit}.sr-stat.active,.sr-stat:hover{border-color:#8375ba;box-shadow:0 4px 14px rgba(74,61,130,.08)}.sr-stat-label{font-size:.78rem;color:var(--muted);font-weight:600}.sr-stat-value{font-size:1.35rem;font-weight:750;margin-top:4px}.sr-toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;padding:14px 16px;border-bottom:1px solid var(--line)}.sr-toolbar input{max-width:420px}.sr-report{margin:14px;border:1px solid var(--line);border-radius:12px;padding:17px}.sr-reason{border-left:3px solid #d49b2e;padding:8px 11px;background:#fffaf0;color:#5c4c30;white-space:pre-wrap}.sr-post-text{white-space:pre-wrap;color:#526479;margin:12px 0;overflow-wrap:anywhere}.sr-meta{font-size:.78rem;color:var(--muted)}.sr-actions{display:flex;gap:7px;flex-wrap:wrap}.sr-actions form{margin:0}.sr-empty{text-align:center;padding:50px 20px}
@media(max-width:760px){.sr-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.sr-report{margin:10px;padding:13px}}
</style>
@endsection
@section('main-content')
@php($status = $status ?? 'all')
@php($search = $search ?? '')
<div class="sr-page">
@include('admin.section.flash_message')
<div class="card sr-hero mb-3"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3"><div><h2>Social Feed reports</h2><p>Review employee reports, document outcomes, and take action on posts.</p></div><a class="btn btn-light text-primary" href="{{ route('admin.community.feed') }}"><i data-feather="arrow-left" class="me-1"></i>Back to Social Feed</a></div></div>
<div class="sr-stats mb-3">
<a class="sr-stat {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.community.reports') }}"><div class="sr-stat-label">All reports</div><div class="sr-stat-value">{{ $counts['all'] }}</div></a>
<a class="sr-stat {{ $status === 'open' ? 'active' : '' }}" href="{{ route('admin.community.reports', ['status' => 'open']) }}"><div class="sr-stat-label">Needs review</div><div class="sr-stat-value">{{ $counts['open'] }}</div></a>
<a class="sr-stat {{ $status === 'reviewed' ? 'active' : '' }}" href="{{ route('admin.community.reports', ['status' => 'reviewed']) }}"><div class="sr-stat-label">Reviewed</div><div class="sr-stat-value">{{ $counts['reviewed'] }}</div></a>
<a class="sr-stat {{ $status === 'dismissed' ? 'active' : '' }}" href="{{ route('admin.community.reports', ['status' => 'dismissed']) }}"><div class="sr-stat-label">Dismissed</div><div class="sr-stat-value">{{ $counts['dismissed'] }}</div></a>
</div>
<div class="card" style="border:1px solid #e8edf3;border-radius:13px;overflow:hidden"><div class="card-body p-0"><form class="sr-toolbar" method="get" action="{{ route('admin.community.reports') }}"><input type="hidden" name="status" value="{{ $status }}"><input class="form-control" type="search" name="q" value="{{ $search }}" placeholder="Search report reason, post, or reporter…"><button class="btn btn-outline-primary">Search reports</button>@if($search)<a class="btn btn-light" href="{{ route('admin.community.reports', ['status' => $status]) }}">Clear</a>@endif</form>
@forelse($reports as $report)
<article class="sr-report"><div class="d-flex justify-content-between align-items-start flex-wrap gap-2"><div><strong>{{ $report->post?->author?->name ?? 'Former employee' }}</strong><div class="sr-meta">Reported by {{ $report->reporter?->name ?? 'Former employee' }} · {{ $report->created_at?->format('M j, Y · H:i') }}</div></div><span class="badge bg-{{ $report->status === 'open' ? 'warning text-dark' : ($report->status === 'reviewed' ? 'success' : 'secondary') }}">{{ ucfirst($report->status) }}</span></div>
<div class="sr-post-text">{{ $report->post?->body ?? '[Post removed]' }}</div><div class="sr-reason"><strong>Report reason</strong><br>{{ $report->reason }}</div>
<div class="sr-actions mt-3">@if($report->status === 'open')<form method="post" action="{{ route('admin.community.report-resolve', $report) }}">@csrf<input type="hidden" name="status" value="reviewed"><button class="btn btn-sm btn-outline-success"><i data-feather="check" style="width:14px;height:14px" class="me-1"></i>Mark reviewed</button></form><form method="post" action="{{ route('admin.community.report-resolve', $report) }}">@csrf<input type="hidden" name="status" value="dismissed"><button class="btn btn-sm btn-outline-secondary">Dismiss report</button></form>@endif
@if($report->post)<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.community.post-edit', $report->post) }}">Edit post</a><a class="btn btn-sm btn-outline-info" href="{{ route('admin.community.comments', $report->post) }}">Review comments</a><form method="post" action="{{ route('admin.community.post-delete', $report->post) }}" onsubmit="return confirm('Remove this reported post from the feed?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove post</button></form>@endif
</div></article>
@empty<div class="sr-empty"><h5>{{ $search || $status !== 'all' ? 'No matching reports' : 'No reports yet' }}</h5><p class="text-muted mb-0">{{ $search || $status !== 'all' ? 'Change the search or report status filter.' : 'Employee reports will appear here for moderation.' }}</p></div>@endforelse
@if($reports->hasPages())<div class="p-3 border-top">{{ $reports->links() }}</div>@endif
</div></div></div>
@endsection
