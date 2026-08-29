@extends('layouts.master')

@section('title', 'Performance Reports')
@section('action', 'Performance Reports')

@section('main-content')
    <section class="content">
        <div class="row">
            @foreach([
                'Completed Evaluations' => $report['completed_count'],
                'Average Score' => $report['average_score'] . '/100',
                'Excellent 90+' => $report['excellent_count'],
                'Below 70' => $report['needs_improvement_count'],
            ] as $label => $value)
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <small class="text-muted">{{ $label }}</small>
                            <h4 class="mb-0 mt-2">{{ $value }}</h4>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Latest Completed Evaluations</h6></div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Employee</th><th>Position</th><th>Period</th><th>Score</th><th>Rating</th><th>Completed</th></tr></thead>
                    <tbody>
                    @forelse($latest as $evaluation)
                        <tr>
                            <td>{{ $evaluation->employee?->english_name ?: $evaluation->employee?->name }}</td>
                            <td>{{ $evaluation->employee?->post?->post_name ?: 'N/A' }}</td>
                            <td>{{ $evaluation->evaluation_period ?: 'N/A' }}</td>
                            <td>{{ $evaluation->total_score }}/100</td>
                            <td>{{ $evaluation->result_label ?: 'N/A' }}</td>
                            <td>{{ $evaluation->completed_at ? $evaluation->completed_at->format('Y-m-d') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No completed evaluations yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
