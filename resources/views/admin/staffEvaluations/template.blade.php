@extends('layouts.master')

@section('title', 'Evaluation Form Preview')
@section('action', 'Evaluation Preview')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">{{ $template->title }}</h6>
                    <small class="text-muted">{{ $template->employee->english_name ?: $template->employee->name }} · {{ $template->employee->post?->post_name ?: 'N/A' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-success" target="_blank" href="{{ route('admin.staff-evaluations.templates.print-blank', $template) }}">Print Blank Form</a>
                    <form method="post" action="{{ route('admin.staff-evaluations.templates.start', $template) }}">
                        @csrf
                        <button class="btn btn-outline-secondary">Optional Web Score</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.staff-evaluations.templates.update', $template) }}">
                    @csrf
                    @method('PUT')
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>Section</th>
                                <th>Question KH</th>
                                <th>Question EN</th>
                                <th style="width:130px">Type</th>
                                <th style="width:100px">Weight</th>
                                <th style="width:90px">Max</th>
                                <th>Reason</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($template->questions as $question)
                                <tr>
                                    <td><input class="form-control" name="section[]" value="{{ $question->section }}"></td>
                                    <td><textarea class="form-control" name="question_kh[]" rows="2">{{ $question->question_kh }}</textarea></td>
                                    <td><textarea class="form-control" name="question_en[]" rows="2">{{ $question->question_en }}</textarea></td>
                                    <td>
                                        <select class="form-control" name="question_type[]">
                                            @foreach([
                                                'score_1_5' => 'Score 1-5',
                                                'yes_no' => 'Yes/No',
                                                'percentage' => 'Percentage',
                                                'number' => 'Number',
                                                'text_comment' => 'Text',
                                                'target_actual' => 'Target vs Actual',
                                                'pass_fail' => 'Pass/Fail',
                                            ] as $value => $label)
                                                <option value="{{ $value }}" {{ ($question->question_type ?: 'score_1_5') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input class="form-control" type="number" step="0.01" name="weight[]" value="{{ $question->weight }}"></td>
                                    <td><input class="form-control" type="number" min="1" max="5" name="max_score[]" value="{{ $question->max_score }}"></td>
                                    <td><textarea class="form-control" name="reason[]" rows="2">{{ $question->reason }}</textarea></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary">Save Template</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.staff-evaluations.templates.generate', $template->jobDescription) }}">Regenerate All</a>
                </form>
            </div>
        </div>
    </section>
@endsection
