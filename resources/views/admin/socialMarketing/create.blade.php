@extends('layouts.master')
@section('title', 'Add Social Media Marketing Submission')
@section('action', 'Create')
@section('main-content')
@include('admin.section.flash_message')
<div class="card"><div class="card-body">
    @include('admin.socialMarketing._form', ['record' => new \App\Models\SocialReward()])
</div></div>
@endsection
