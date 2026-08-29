@extends('layouts.master')

@section('title', 'Employee Evaluation')
@section('action', 'Evaluation Dashboard')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="row">
            @foreach([
                'Employee JD Profiles' => $stats['profiles'],
                'Job Descriptions' => $stats['job_descriptions'],
                'Templates' => $stats['templates'],
                'Evaluations' => $stats['evaluations'],
                'Completed' => $stats['completed'],
                'AI Interviews Pending' => $stats['pending_interviews'],
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
                <h6 class="mb-0">Employee Evaluation Workflow</h6>
                <a class="btn btn-primary btn-sm" href="{{ route('admin.staff-evaluations.ai-create') }}">Create AI Evaluation Form</a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    @foreach(['Select Employee', 'AI Job Interview', 'Review Job Description', 'Generate Template', 'Print A4 Form', 'Optional Web Score'] as $step)
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
                    <div class="card-header"><h6 class="mb-0">Recent Templates</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Template</th><th>Employee</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            @forelse($recentTemplates as $template)
                                <tr>
                                    <td>{{ $template->title }}</td>
                                    <td>{{ $template->employee?->english_name ?: $template->employee?->name }}</td>
                                    <td>{{ ucfirst($template->status) }}</td>
                                    <td><a href="{{ route('admin.staff-evaluations.templates.show', $template) }}">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No templates yet</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">Recent Evaluations</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Employee</th><th>Period</th><th>Score</th><th></th></tr></thead>
                            <tbody>
                            @forelse($recentEvaluations as $evaluation)
                                <tr>
                                    <td>{{ $evaluation->employee?->english_name ?: $evaluation->employee?->name }}</td>
                                    <td>{{ $evaluation->evaluation_period ?: 'N/A' }}</td>
                                    <td>{{ $evaluation->total_score ?: 'Draft' }}</td>
                                    <td><a href="{{ route('admin.staff-evaluations.evaluations.show', $evaluation) }}">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No evaluations yet</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
