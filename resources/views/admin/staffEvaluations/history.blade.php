@extends('layouts.master')

@section('title', 'Evaluation History')
@section('action', 'Evaluation History')

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
                        <th>Period</th>
                        <th>Evaluator</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($evaluations as $evaluation)
                        <tr>
                            <td>
                                {{ $evaluation->employee?->english_name ?: $evaluation->employee?->name }}
                                <br><small class="text-muted">{{ $evaluation->employee?->employee_code ?: $evaluation->employee?->username }}</small>
                            </td>
                            <td>{{ $evaluation->employee?->department?->dept_name ?: 'N/A' }}</td>
                            <td>{{ $evaluation->employee?->post?->post_name ?: 'N/A' }}</td>
                            <td>{{ $evaluation->evaluation_period ?: 'N/A' }}</td>
                            <td>{{ $evaluation->evaluator?->name ?: 'N/A' }}</td>
                            <td>{{ ucfirst($evaluation->status) }}</td>
                            <td>{{ $evaluation->total_score !== null ? $evaluation->total_score . '/100' : 'Draft' }}</td>
                            <td class="text-nowrap">
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.staff-evaluations.evaluations.show', $evaluation) }}">Open</a>
                                <a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('admin.staff-evaluations.print', $evaluation) }}">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No evaluations yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
                {{ $evaluations->links() }}
            </div>
        </div>
    </section>
@endsection
