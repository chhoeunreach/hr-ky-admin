<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeeEvaluation;
use App\Models\EvaluationTemplate;
use App\Services\Evaluations\EmployeeEvaluationAIService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeEvaluationController extends Controller
{
    use CustomAuthorizesRequests;

    public function showTemplate(EvaluationTemplate $template)
    {
        $this->authorize('employee.performance.create');
        $template->load(['employee.branch', 'employee.department', 'employee.post', 'jobDescription', 'questions']);

        return view('admin.staffEvaluations.template', compact('template'));
    }

    public function updateTemplate(Request $request, EvaluationTemplate $template, EmployeeEvaluationAIService $ai)
    {
        $this->authorize('employee.performance.create');

        $data = $request->validate([
            'section' => ['required', 'array'],
            'section.*' => ['required', 'string', 'max:255'],
            'question_kh' => ['required', 'array'],
            'question_kh.*' => ['required', 'string'],
            'question_en' => ['nullable', 'array'],
            'question_en.*' => ['nullable', 'string'],
            'question_type' => ['nullable', 'array'],
            'question_type.*' => ['nullable', 'string', 'max:50'],
            'weight' => ['required', 'array'],
            'weight.*' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'array'],
            'max_score.*' => ['required', 'integer', 'between:1,5'],
            'reason' => ['nullable', 'array'],
            'reason.*' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($template, $data, $ai) {
            $questions = [];
            foreach ($data['question_kh'] as $index => $questionKh) {
                $questions[] = [
                    'section' => $data['section'][$index] ?? 'Job Performance',
                    'question_kh' => $questionKh,
                    'question_en' => $data['question_en'][$index] ?? null,
                    'question_type' => $data['question_type'][$index] ?? 'score_1_5',
                    'weight' => $data['weight'][$index] ?? 0,
                    'max_score' => $data['max_score'][$index] ?? 5,
                    'reason' => $data['reason'][$index] ?? null,
                ];
            }

            $template->questions()->delete();
            foreach ($ai->normalizeWeights($questions) as $index => $question) {
                $template->questions()->create($question + ['sort_order' => $index + 1]);
            }
        });

        return back()->with('success', 'Template saved and weights normalized.');
    }

    public function start(EvaluationTemplate $template)
    {
        $this->authorize('employee.performance.create');

        $evaluation = DB::transaction(function () use ($template) {
            $template->load(['questions', 'jobDescription.interview']);
            $evaluation = EmployeeEvaluation::create([
                'template_id' => $template->id,
                'job_description_id' => $template->job_description_id,
                'employee_id' => $template->employee_id,
                'evaluator_id' => auth()->id(),
                'evaluation_period' => $template->jobDescription?->interview?->evaluation_period,
                'status' => 'draft',
            ]);

            foreach ($template->questions as $question) {
                $evaluation->answers()->create([
                    'question_id' => $question->id,
                    'section' => $question->section,
                    'question_kh' => $question->question_kh,
                    'question_en' => $question->question_en,
                    'weight' => $question->weight,
                    'max_score' => $question->max_score,
                    'sort_order' => $question->sort_order,
                ]);
            }

            return $evaluation;
        });

        return redirect()->route('admin.staff-evaluations.evaluations.show', $evaluation);
    }

    public function printTemplate(EvaluationTemplate $template)
    {
        $this->authorize('employee.performance.view');
        $template->load(['employee.branch', 'employee.department', 'employee.post', 'jobDescription.interview.evaluator', 'questions']);

        return view('admin.staffEvaluations.print-blank', compact('template'));
    }

    public function show(EmployeeEvaluation $evaluation)
    {
        $this->authorize('employee.performance.create');
        $evaluation->load(['employee.branch', 'employee.department', 'employee.post', 'evaluator', 'answers', 'template.jobDescription']);

        return view('admin.staffEvaluations.evaluate', compact('evaluation'));
    }

    public function submit(Request $request, EmployeeEvaluation $evaluation, EmployeeEvaluationAIService $ai)
    {
        $this->authorize('employee.performance.create');

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.score' => ['nullable', 'integer', 'between:1,5'],
            'answers.*.is_na' => ['nullable'],
            'answers.*.comment' => ['nullable', 'string'],
            'evaluator_comment' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'next_review_goals' => ['nullable', 'string'],
            'support_needed' => ['nullable', 'string'],
            'final_decision' => ['nullable', 'string', 'max:255'],
            'decision_reason' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($evaluation, $data, $ai) {
            $calculationRows = [];
            foreach ($evaluation->answers as $answer) {
                $payload = $data['answers'][$answer->id] ?? [];
                $isNa = isset($payload['is_na']);
                $score = $isNa ? null : ($payload['score'] ?? null);
                $answer->update([
                    'score' => $score,
                    'is_na' => $isNa,
                    'comment' => $payload['comment'] ?? null,
                ]);
                $calculationRows[] = [
                    'score' => $score,
                    'is_na' => $isNa,
                    'weight' => $answer->weight,
                    'max_score' => $answer->max_score,
                ];
            }

            $result = $ai->calculate($evaluation, $calculationRows);
            $evaluation->update([
                'status' => 'completed',
                'total_score' => $result['total_score'],
                'result_label' => $result['result_label'],
                'evaluator_comment' => $data['evaluator_comment'] ?? null,
                'strengths' => $data['strengths'] ?? null,
                'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
                'next_review_goals' => $data['next_review_goals'] ?? null,
                'support_needed' => $data['support_needed'] ?? null,
                'final_decision' => $data['final_decision'] ?? null,
                'decision_reason' => $data['decision_reason'] ?? null,
                'completed_at' => now(),
            ]);

            foreach ($evaluation->answers()->get() as $answer) {
                $weighted = null;
                if (!$answer->is_na && $answer->score) {
                    $weighted = round(($answer->score / max(1, $answer->max_score)) * $answer->weight, 2);
                }
                $answer->update(['weighted_score' => $weighted]);
            }
        });

        return redirect()->route('admin.staff-evaluations.evaluations.show', $evaluation)->with('success', 'Evaluation completed.');
    }

    public function summary(EmployeeEvaluation $evaluation, EmployeeEvaluationAIService $ai)
    {
        $this->authorize('employee.performance.create');
        $evaluation->update(['ai_summary' => $ai->generateSummary($evaluation)]);

        return back()->with('success', 'AI summary generated.');
    }

    public function print(EmployeeEvaluation $evaluation)
    {
        $this->authorize('employee.performance.view');
        $evaluation->load(['employee.branch', 'employee.department', 'employee.post', 'evaluator', 'answers']);

        return view('admin.staffEvaluations.print', compact('evaluation'));
    }
}
