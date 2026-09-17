@extends('layouts.master')

@section('title', __('index.employee_evaluation'))
@section('action', __('index.evaluation_dashboard'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="row">
            @foreach([
                __('index.employee_jd_profiles') => $stats['profiles'],
                __('index.job_descriptions') => $stats['job_descriptions'],
                __('index.templates') => $stats['templates'],
                __('index.evaluations') => $stats['evaluations'],
                __('index.completed') => $stats['completed'],
                __('index.ai_interviews_pending') => $stats['pending_interviews'],
            ] as $label => $value)
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <small class="text-muted">{{ $label }}</small>
                            <h4 class="mb-0 mt-2">{{ $value }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">{{ __('index.employee_evaluation_workflow') }}</h6>
                <a class="btn btn-primary btn-sm" href="{{ route('admin.staff-evaluations.ai-create') }}">{{ __('index.create_ai_evaluation_form') }}</a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    @foreach([
                        __('index.select_employee_step'),
                        __('index.ai_job_interview_step'),
                        __('index.review_job_description_step'),
                        __('index.generate_template_step'),
                        __('index.print_a4_form_step'),
                        __('index.optional_web_score_step'),
                    ] as $step)
                        <div class="col-md-2 col-sm-4 mb-2">
                            <div class="border rounded p-2 h-100">{{ $step }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">{{ __('index.recent_templates') }}</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('index.template') }}</th><th>{{ __('index.employee') }}</th><th>{{ __('index.status') }}</th><th></th></tr></thead>
                            <tbody>
                            @forelse($recentTemplates as $template)
                                <tr>
                                    <td>{{ $template->title }}</td>
                                    <td>{{ $template->employee?->english_name ?: $template->employee?->name }}</td>
                                    <td>{{ ucfirst($template->status) }}</td>
                                    <td><a href="{{ route('admin.staff-evaluations.templates.show', $template) }}">{{ __('index.open') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ __('index.no_templates_yet') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">{{ __('index.recent_evaluations') }}</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>{{ __('index.employee') }}</th><th>{{ __('index.period') }}</th><th>{{ __('index.score') }}</th><th></th></tr></thead>
                            <tbody>
                            @forelse($recentEvaluations as $evaluation)
                                <tr>
                                    <td>{{ $evaluation->employee?->english_name ?: $evaluation->employee?->name }}</td>
                                    <td>{{ $evaluation->evaluation_period ?: 'N/A' }}</td>
                                    <td>{{ $evaluation->total_score ?: __('index.draft') }}</td>
                                    <td><a href="{{ route('admin.staff-evaluations.evaluations.show', $evaluation) }}">{{ __('index.open') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ __('index.no_evaluations_yet') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
