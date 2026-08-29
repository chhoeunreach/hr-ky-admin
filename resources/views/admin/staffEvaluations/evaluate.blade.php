@extends('layouts.master')

@section('title', 'Employee Evaluation')
@section('action', 'Score Evaluation')

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">ទម្រង់វាយតម្លៃការងារបុគ្គលិក</h6>
                    <small>{{ $evaluation->employee->english_name ?: $evaluation->employee->name }} · {{ $evaluation->evaluation_period }}</small>
                </div>
                <a class="btn btn-outline-secondary" target="_blank" href="{{ route('admin.staff-evaluations.print', $evaluation) }}">Print</a>
            </div>
            <div class="card-body">
                @if($evaluation->total_score !== null)
                    <div class="alert alert-success">
                        ពិន្ទុសរុប: <strong>{{ number_format($evaluation->total_score, 2) }} / 100</strong>
                        · លទ្ធផល: <strong>{{ $evaluation->result_label }}</strong>
                    </div>
                @endif
                <form method="post" action="{{ route('admin.staff-evaluations.evaluations.submit', $evaluation) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th>ល.រ</th>
                                <th>សំណួរវាយតម្លៃ</th>
                                <th>Weight</th>
                                <th>Score</th>
                                <th>N/A</th>
                                <th>មតិ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($evaluation->answers as $answer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $answer->question_kh }}</strong>
                                        @if($answer->question_en)<br><small class="text-muted">{{ $answer->question_en }}</small>@endif
                                    </td>
                                    <td>{{ $answer->weight }}%</td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @for($score = 5; $score >= 1; $score--)
                                                <label><input type="radio" name="answers[{{ $answer->id }}][score]" value="{{ $score }}" @checked((int)$answer->score === $score)> {{ $score }}</label>
                                            @endfor
                                        </div>
                                    </td>
                                    <td><input type="checkbox" name="answers[{{ $answer->id }}][is_na]" value="1" @checked($answer->is_na)></td>
                                    <td><input class="form-control" name="answers[{{ $answer->id }}][comment]" value="{{ $answer->comment }}"></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Evaluator Comment</label>
                        <textarea class="form-control" name="evaluator_comment" rows="3">{{ $evaluation->evaluator_comment }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Strengths</label>
                            <textarea class="form-control" name="strengths" rows="3">{{ $evaluation->strengths }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Areas for Improvement</label>
                            <textarea class="form-control" name="areas_for_improvement" rows="3">{{ $evaluation->areas_for_improvement }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Next Review Goals</label>
                            <textarea class="form-control" name="next_review_goals" rows="3">{{ $evaluation->next_review_goals }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Support Needed from Company / Manager</label>
                            <textarea class="form-control" name="support_needed" rows="3">{{ $evaluation->support_needed }}</textarea>
                        </div>
                    </div>
                    <div class="employee-human-decision border rounded p-3 mb-3">
                        <h6>Human Final Decision / Recommendation</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Decision</label>
                                <select class="form-control" name="final_decision">
                                    <option value="">No decision selected</option>
                                    @foreach(['Continue Current Position', 'Salary Review', 'Promotion Review', 'Department/Branch Transfer Review', 'Additional Training', 'Performance Improvement Plan', 'Warning Review', 'Other'] as $decision)
                                        <option value="{{ $decision }}" {{ $evaluation->final_decision === $decision ? 'selected' : '' }}>{{ $decision }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Reason / Recommendation</label>
                                <textarea class="form-control" name="decision_reason" rows="3">{{ $evaluation->decision_reason }}</textarea>
                            </div>
                        </div>
                        <small class="text-muted">AI does not make employment decisions. Authorized HR or management must choose and approve any action.</small>
                    </div>
                    <button class="btn btn-primary">Save Score</button>
                </form>
                @if($evaluation->status === 'completed')
                    <form method="post" action="{{ route('admin.staff-evaluations.evaluations.ai-summary', $evaluation) }}" class="mt-3">
                        @csrf
                        <button class="btn btn-outline-primary">Generate AI Summary</button>
                    </form>
                    @if($evaluation->ai_summary)
                        <div class="mt-3 p-3 border rounded">
                            <h6>AI Evaluation Summary</h6>
                            @foreach($evaluation->ai_summary as $title => $items)
                                <strong>{{ ucwords(str_replace('_', ' ', $title)) }}</strong>
                                <ul>
                                    @foreach((array)$items as $item)<li>{{ $item }}</li>@endforeach
                                </ul>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </section>
@endsection
