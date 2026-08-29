@extends('layouts.master')

@section('title', 'Job Descriptions')
@section('action', 'Job Descriptions')

@section('button')
    <a class="btn btn-primary" href="{{ route('admin.staff-evaluations.ai-create') }}">AI Evaluation Generator</a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Job Title</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Confirmed</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($jobDescriptions as $jobDescription)
                        <tr>
                            <td>
                                {{ $jobDescription->employee?->english_name ?: $jobDescription->employee?->name }}
                                <br><small class="text-muted">{{ $jobDescription->employee?->employee_code ?: $jobDescription->employee?->username }}</small>
                            </td>
                            <td>{{ $jobDescription->employee?->department?->dept_name ?: 'N/A' }}</td>
                            <td>{{ $jobDescription->employee?->post?->post_name ?: 'N/A' }}</td>
                            <td>{{ $jobDescription->job_title ?: 'N/A' }}</td>
                            <td>v{{ $jobDescription->version ?: 1 }}</td>
                            <td>{{ ucfirst($jobDescription->status ?: ($jobDescription->confirmed_at ? 'confirmed' : 'draft')) }}</td>
                            <td>{{ $jobDescription->confirmed_at ? $jobDescription->confirmed_at->format('Y-m-d') : 'No' }}</td>
                            <td class="text-nowrap">
                                @if($jobDescription->interview)
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.staff-evaluations.interviews.summary', $jobDescription->interview) }}">Review</a>
                                @endif
                                @if($jobDescription->confirmed_at)
                                    <a class="btn btn-outline-success btn-sm" href="{{ route('admin.staff-evaluations.templates.generate', $jobDescription) }}">Generate Template</a>
                                @else
                                    <span class="badge bg-warning text-dark">Confirm First</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No job descriptions yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
                {{ $jobDescriptions->links() }}
            </div>
        </div>
    </section>
@endsection
