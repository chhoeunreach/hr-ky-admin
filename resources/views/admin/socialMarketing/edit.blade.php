@extends('layouts.master')
@section('title', 'Override Social Media Marketing Submission')
@section('action', 'Edit')
@section('main-content')
@include('admin.section.flash_message')
<div class="card"><div class="card-body">
    @include('admin.socialMarketing._form')
</div></div>
@endsection
