@extends('layouts.master')

@section('title', 'Evaluation Templates')
@section('action', 'Evaluation Templates')

@section('button')
    <a class="btn btn-primary" href="{{ route('admin.staff-evaluations.ai-create') }}">Create Template with AI</a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Template</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Questions</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>{{ $template->title }}</td>
                            <td>{{ $template->employee?->english_name ?: $template->employee?->name }}</td>
                            <td>{{ $template->employee?->department?->dept_name ?: 'N/A' }}</td>
                            <td>{{ $template->employee?->post?->post_name ?: 'N/A' }}</td>
                            <td>{{ $template->questions_count ?? $template->questions->count() }}</td>
                            <td>v{{ $template->version ?: 1 }}</td>
                            <td>{{ ucfirst($template->status) }}</td>
                            <td class="text-nowrap">
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.staff-evaluations.templates.show', $template) }}">Edit</a>
                                <a class="btn btn-success btn-sm" target="_blank" href="{{ route('admin.staff-evaluations.templates.print-blank', $template) }}">Print Blank</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No templates yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
                {{ $templates->links() }}
            </div>
        </div>
    </section>
@endsection
