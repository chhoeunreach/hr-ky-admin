<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EvaluationInterview;
use App\Models\EvaluationJobDescription;
use App\Models\User;
use App\Services\Evaluations\EvaluationInterviewAIService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationInterviewController extends Controller
{
    use CustomAuthorizesRequests;

    public function create(Request $request)
    {
        $this->authorize('employee.performance.create');

        $employees = User::with(['branch:id,name', 'department:id,dept_name', 'post:id,post_name'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'english_name', 'employee_code', 'username', 'branch_id', 'department_id', 'post_id']);

        $selectedEmployeeId = (int) $request->query('employee_id');

        return view('admin.staffEvaluations.ai-create', compact('employees', 'selectedEmployeeId'));
    }

    public function store(Request $request, EvaluationInterviewAIService $ai)
    {
        $this->authorize('employee.performance.create');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'evaluation_period' => ['required', 'string', 'max:255'],
            'evaluator_id' => ['nullable', 'exists:users,id'],
        ]);

        $employee = User::with(['branch', 'department', 'post'])->findOrFail($data['employee_id']);

        $interview = DB::transaction(function () use ($data, $employee, $ai) {
            $interview = EvaluationInterview::create([
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'position_id' => $employee->post_id,
                'branch_id' => $employee->branch_id,
                'evaluator_id' => $data['evaluator_id'] ?? auth()->id(),
                'evaluation_period' => $data['evaluation_period'],
                'status' => 'interviewing',
            ]);

            $interview->messages()->create([
                'role' => 'ai',
                'message' => $ai->firstQuestion(),
            ]);

            return $interview;
        });

        return redirect()->route('admin.staff-evaluations.interviews.show', $interview);
    }

    public function show(EvaluationInterview $interview)
    {
        $this->authorize('employee.performance.create');
        $interview->load(['employee.branch', 'employee.department', 'employee.post', 'evaluator', 'messages']);

        return view('admin.staffEvaluations.interview', compact('interview'));
    }

    public function answer(Request $request, EvaluationInterview $interview, EvaluationInterviewAIService $ai)
    {
        $this->authorize('employee.performance.create');

        $data = $request->validate([
            'answer' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($interview, $data, $ai) {
            $interview->messages()->create([
                'role' => 'manager',
                'message' => $data['answer'],
            ]);

            $result = $ai->next($interview->fresh(['messages', 'employee.post']));

            if ($result['status'] === 'ready') {
                $interview->update([
                    'status' => 'ready',
                    'ai_summary' => $result['summary'],
                ]);

                EvaluationJobDescription::updateOrCreate(
                    ['interview_id' => $interview->id],
                    array_merge($result['summary'], [
                        'employee_id' => $interview->employee_id,
                        'position_id' => $interview->position_id,
                        'department_id' => $interview->department_id,
                        'branch_id' => $interview->branch_id,
                        'version' => 1,
                        'status' => 'draft',
                        'ai_generated' => true,
                    ])
                );

                $interview->messages()->create([
                    'role' => 'ai',
                    'message' => '✓ Job Description Ready',
                ]);
            } else {
                $interview->messages()->create([
                    'role' => 'ai',
                    'message' => $result['next_question'],
                ]);
            }
        });

        return redirect()->route('admin.staff-evaluations.interviews.show', $interview);
    }

    public function summary(EvaluationInterview $interview)
    {
        $this->authorize('employee.performance.create');
        $interview->load(['employee.branch', 'employee.department', 'employee.post', 'jobDescription']);
        abort_unless($interview->jobDescription, 404);

        return view('admin.staffEvaluations.job-description', compact('interview'));
    }

    public function updateSummary(Request $request, EvaluationInterview $interview)
    {
        $this->authorize('employee.performance.create');

        $data = $request->validate([
            'job_title' => ['nullable', 'string', 'max:255'],
            'main_purpose' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'string'],
            'daily_tasks' => ['nullable', 'string'],
            'weekly_tasks' => ['nullable', 'string'],
            'monthly_tasks' => ['nullable', 'string'],
            'kpis' => ['nullable', 'string'],
            'required_skills' => ['nullable', 'string'],
            'required_knowledge' => ['nullable', 'string'],
            'tools' => ['nullable', 'string'],
            'common_problems' => ['nullable', 'string'],
            'customer_responsibilities' => ['nullable', 'string'],
            'reporting_responsibilities' => ['nullable', 'string'],
            'leadership_responsibilities' => ['nullable', 'string'],
            'special_responsibilities' => ['nullable', 'string'],
        ]);

        $jobDescription = $interview->jobDescription;
        foreach (['responsibilities', 'daily_tasks', 'weekly_tasks', 'monthly_tasks', 'kpis', 'required_skills', 'required_knowledge', 'tools', 'common_problems', 'customer_responsibilities', 'reporting_responsibilities', 'leadership_responsibilities', 'special_responsibilities'] as $field) {
            $data[$field] = $this->lines($data[$field] ?? '');
        }
        $jobDescription->update($data);

        return back()->with('success', 'Job description updated.');
    }

    public function confirm(EvaluationInterview $interview)
    {
        $this->authorize('employee.performance.create');

        $interview->jobDescription()->update([
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
            'status' => 'confirmed',
        ]);
        $interview->update(['status' => 'confirmed']);

        return redirect()->route('admin.staff-evaluations.templates.generate', $interview->jobDescription);
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
