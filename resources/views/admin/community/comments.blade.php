@extends('layouts.master')
@section('title', 'Social Feed Comments')
@section('action', 'Comment moderation')
@section('styles')
<style>
.sc-page{max-width:1100px;color:#172b4d}.sc-hero{border:0;border-radius:14px;background:linear-gradient(120deg,#283b70,#5a4c8c);color:#fff}.sc-hero h2{color:#fff;font-size:1.35rem;font-weight:700;margin:0 0 5px}.sc-hero p{color:rgba(255,255,255,.78);margin:0}.sc-post-context,.sc-comment{border:1px solid #e8edf3;border-radius:12px;background:#fff}.sc-post-context{padding:16px;background:#f8fafc}.sc-comment{padding:16px;margin-bottom:10px}.sc-comment-reply{border-left:3px solid #8a7cc2;background:#faf9fd}.sc-author{font-weight:700;color:#30465f}.sc-meta{font-size:.77rem;color:#718096}.sc-body{white-space:pre-wrap;overflow-wrap:anywhere;color:#475b70;margin:10px 0}.sc-toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.sc-toolbar input{max-width:420px}
</style>
@endsection
@section('main-content')
@php($search = $search ?? '')
<div class="sc-page">
@include('admin.section.flash_message')
<div class="card sc-hero mb-3"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3"><div><h2>Comment moderation</h2><p>Review discussion on post #{{ $post->id }} and remove comments that violate company guidelines.</p></div><a class="btn btn-light text-primary" href="{{ route('admin.community.feed') }}"><i data-feather="arrow-left" class="me-1"></i>Back to Social Feed</a></div></div>
<div class="sc-post-context mb-3"><div class="sc-meta">Original post · {{ $post->author?->name ?? 'Former employee' }} · {{ $post->created_at?->format('M j, Y H:i') }}</div><div class="sc-body mb-0">{{ $post->body ?: '[Media post]' }}</div></div>
<form class="sc-toolbar mb-3" method="get" action="{{ route('admin.community.comments', $post) }}"><input class="form-control" name="q" type="search" value="{{ $search }}" placeholder="Search comment or employee…"><button class="btn btn-outline-primary">Search comments</button>@if($search)<a class="btn btn-light" href="{{ route('admin.community.comments', $post) }}">Clear</a>@endif</form>
@forelse($comments as $comment)
<article class="sc-comment {{ $comment->parent_id ? 'sc-comment-reply' : '' }}"><div class="d-flex justify-content-between align-items-start flex-wrap gap-2"><div><div class="sc-author">{{ $comment->author?->name ?? 'Former employee' }} @if($comment->parent_id)<span class="badge bg-light text-dark">Reply</span>@endif</div><div class="sc-meta">{{ $comment->created_at?->format('M j, Y · H:i') }} @if($comment->updated_at && $comment->updated_at->gt($comment->created_at->copy()->addSecond())) · edited @endif</div></div><form method="post" action="{{ route('admin.community.comment-delete', $comment) }}" onsubmit="return confirm('Remove this comment and its replies?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i data-feather="trash-2" style="width:14px;height:14px" class="me-1"></i>Remove</button></form></div>
@if($comment->parent)<div class="sc-meta mt-2">Replying to {{ $comment->parent->author?->name ?? 'Former employee' }}: “{{ \Illuminate\Support\Str::limit($comment->parent->body, 110) }}”</div>@endif
<div class="sc-body">{{ $comment->body }}</div></article>
@empty<div class="text-center bg-white border rounded p-5"><h5>{{ $search ? 'No matching comments' : 'No comments yet' }}</h5><p class="text-muted mb-0">{{ $search ? 'Try a different search phrase.' : 'Comments on this post will appear here.' }}</p></div>@endforelse
@if($comments->hasPages()){{ $comments->links() }}@endif
</div>
@endsection
