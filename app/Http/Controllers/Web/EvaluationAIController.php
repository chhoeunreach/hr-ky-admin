<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EvaluationJobDescription;
use App\Models\EvaluationTemplate;
use App\Services\Evaluations\EmployeeEvaluationAIService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Support\Facades\DB;

class EvaluationAIController extends Controller
{
    use CustomAuthorizesRequests;

    public function generate(EvaluationJobDescription $jobDescription, EmployeeEvaluationAIService $ai)
    {
        $this->authorize('employee.performance.create');

        if (!$jobDescription->confirmed_at) {
            return back()->with('error', 'Please confirm the job description before generating an evaluation template.');
        }

        $template = DB::transaction(function () use ($jobDescription, $ai) {
            $payload = $ai->generateQuestions($jobDescription);
            $template = EvaluationTemplate::create([
                'job_description_id' => $jobDescription->id,
                'employee_id' => $jobDescription->employee_id,
                'position_id' => $jobDescription->position_id,
                'department_id' => $jobDescription->department_id,
                'branch_id' => $jobDescription->branch_id,
                'title' => ($jobDescription->job_title ?: 'Employee') . ' Evaluation Form',
                'version' => $jobDescription->version ?: 1,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($payload['questions'] as $index => $question) {
                $template->questions()->create([
                    'section' => $question['section'],
                    'question_kh' => $question['question_kh'],
                    'question_en' => $question['question_en'] ?? null,
                    'question_type' => $question['question_type'] ?? 'score_1_5',
                    'weight' => $question['weight'],
                    'max_score' => $question['max_score'],
                    'reason' => $question['reason'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }

            return $template;
        });

        return redirect()->route('admin.staff-evaluations.templates.show', $template);
    }
}
