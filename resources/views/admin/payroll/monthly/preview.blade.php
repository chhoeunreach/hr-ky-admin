@extends('layouts.master')
@section('title', __('index.monthly_payroll_preview'))
@section('action', __('index.monthly_payroll_preview'))
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/monthly-payroll.css') }}?v={{ filemtime(public_path('assets/css/monthly-payroll.css')) }}">
@endsection
@section('main-content')
<section class="content monthly-payroll-page">
    @include('admin.section.flash_message')
    <div class="monthly-payroll-heading"><h4 class="mb-1">{{ __('index.monthly_payroll_preview') }} · {{ $filters['month'] }}</h4><p class="text-muted mb-0">{{ $payload['branch_name'] }}</p></div>
    <div class="alert alert-info">{{ __('index.monthly_payroll_preview_help') }}</div>
    @include('admin.payroll.monthly.summary')
    <form method="post" action="{{ route('admin.monthly-payroll.preview.save', $token) }}" class="monthly-payroll-preview-actions" data-payroll-preview-save>
        @csrf
        <a class="btn btn-outline-secondary" href="{{ $returnUrl }}">{{ __('index.cancel') }}</a>
        <button type="submit" class="btn btn-primary"><i data-feather="save" aria-hidden="true"></i>{{ __('index.monthly_payroll_save_record') }}</button>
    </form>
    @include('admin.payroll.monthly.results')
</section>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/monthly-payroll-generate.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll-generate.js')) }}"></script>
@endsection
