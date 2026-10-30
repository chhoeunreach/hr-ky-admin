@extends('layouts.master')
@section('title', __('index.monthly_payroll_preview'))
@section('action', __('index.monthly_payroll_preview'))
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/monthly-payroll.css') }}?v={{ filemtime(public_path('assets/css/monthly-payroll.css')) }}">
@endsection
@section('main-content')
<section class="content monthly-payroll-page">
    @include('admin.section.flash_message')
    <div class="monthly-payroll-heading"><h4 class="mb-1">{{ __('index.monthly_payroll_preview') }} · {{ $filters['month'] }}</h4>
        <div class="d-flex flex-wrap gap-2 mt-2">@foreach($branchPreviews as $branchPreview)<span class="badge bg-light text-secondary border">{{ $branchPreview['branch_name'] }} · {{ __(!empty($branchPreview['already_saved']) ? 'index.monthly_payroll_saved_record' : 'index.monthly_payroll_preview') }}</span>@endforeach</div>
    </div>
    <div class="alert alert-info">{{ __('index.monthly_payroll_preview_help') }}</div>
    @if($emptyBranches)<div class="alert alert-light border small">{{ __('index.monthly_payroll_empty_branches') }} {{ implode(' · ', $emptyBranches) }}</div>@endif
    @include('admin.payroll.monthly.summary')
    <form method="post" action="{{ route('admin.monthly-payroll.preview.save', $token) }}" class="monthly-payroll-preview-actions" @if($canSave) data-payroll-preview-save @endif>
        @csrf
        <a class="btn btn-outline-secondary" href="{{ $returnUrl }}">{{ __('index.cancel') }}</a>
        @if($canSave)<button type="submit" class="btn btn-primary"><i data-feather="save" aria-hidden="true"></i>{{ __('index.monthly_payroll_save_record') }}</button>@else<span class="align-self-center text-muted small">{{ __('index.monthly_payroll_all_saved') }}</span>@endif
    </form>
    @include('admin.payroll.monthly.results')
</section>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/monthly-payroll-generate.js') }}?v={{ filemtime(public_path('assets/js/monthly-payroll-generate.js')) }}"></script>
@endsection
