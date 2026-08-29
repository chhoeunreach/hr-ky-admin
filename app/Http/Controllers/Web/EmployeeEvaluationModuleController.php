<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeeEvaluation;
use App\Models\EvaluationInterview;
use App\Models\EvaluationJobDescription;
use App\Models\EvaluationTemplate;
use App\Traits\CustomAuthorizesRequests;

class EmployeeEvaluationModuleController extends Controller
{
    use CustomAuthorizesRequests;

    public function dashboard()
    {
        $this->authorize('employee.performance.view');

        $stats = [
            'profiles' => EvaluationJobDescription::distinct()->count('employee_id'),
            'job_descriptions' => EvaluationJobDescription::count(),
            'templates' => EvaluationTemplate::count(),
            'evaluations' => EmployeeEvaluation::count(),
            'completed' => EmployeeEvaluation::where('status', 'completed')->count(),
            'pending_interviews' => EvaluationInterview::whereIn('status', ['draft', 'interviewing', 'ready'])->count(),
        ];

        $recentEvaluations = EmployeeEvaluation::with(['employee:id,name,english_name,employee_code,username,post_id', 'employee.post:id,post_name', 'evaluator:id,name'])
            ->latest('id')
            ->limit(10)
            ->get();

        $recentTemplates = EvaluationTemplate::with(['employee:id,name,english_name,post_id', 'employee.post:id,post_name'])
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.staffEvaluations.dashboard', compact('stats', 'recentEvaluations', 'recentTemplates'));
    }

    public function jobDescriptions()
    {
        $this->authorize('employee.performance.view');

        $jobDescriptions = EvaluationJobDescription::with(['employee:id,name,english_name,employee_code,username,department_id,post_id,branch_id', 'employee.department:id,dept_name', 'employee.post:id,post_name', 'employee.branch:id,name', 'interview'])
            ->latest('id')
            ->paginate(25);

        return view('admin.staffEvaluations.job-descriptions.index', compact('jobDescriptions'));
    }

    public function templates()
    {
        $this->authorize('employee.performance.view');

        $templates = EvaluationTemplate::with(['employee:id,name,english_name,employee_code,username,department_id,post_id,branch_id', 'employee.department:id,dept_name', 'employee.post:id,post_name', 'employee.branch:id,name'])
            ->withCount('questions')
            ->latest('id')
            ->paginate(25);

        return view('admin.staffEvaluations.templates.index', compact('templates'));
    }

    public function history()
    {
        $this->authorize('employee.performance.view');

        $evaluations = EmployeeEvaluation::with(['employee:id,name,english_name,employee_code,username,department_id,post_id,branch_id', 'employee.department:id,dept_name', 'employee.post:id,post_name', 'employee.branch:id,name', 'evaluator:id,name'])
            ->latest('id')
            ->paginate(25);

        return view('admin.staffEvaluations.history', compact('evaluations'));
    }

    public function reports()
    {
        $this->authorize('employee.performance.view');

        $completed = EmployeeEvaluation::where('status', 'completed');
        $report = [
            'completed_count' => (clone $completed)->count(),
            'average_score' => round((float) (clone $completed)->avg('total_score'), 2),
            'excellent_count' => (clone $completed)->where('total_score', '>=', 90)->count(),
            'needs_improvement_count' => (clone $completed)->where('total_score', '<', 70)->count(),
        ];

        $latest = EmployeeEvaluation::with(['employee:id,name,english_name,post_id', 'employee.post:id,post_name'])
            ->where('status', 'completed')
            ->latest('completed_at')
            ->limit(20)
            ->get();

        return view('admin.staffEvaluations.reports', compact('report', 'latest'));
    }

    public function settings()
    {
        $this->authorize('employee.performance.view');

        $defaultSections = [
            ['name' => 'Quality of Work', 'weight' => 15],
            ['name' => 'Task Completion / Productivity', 'weight' => 10],
            ['name' => 'Customer Response', 'weight' => 10],
            ['name' => 'Problem Solving', 'weight' => 10],
            ['name' => 'Responsibility', 'weight' => 10],
            ['name' => 'Discipline / Attendance / Time', 'weight' => 10],
            ['name' => 'Teamwork', 'weight' => 10],
            ['name' => 'Accepting Guidance', 'weight' => 5],
            ['name' => 'Perseverance', 'weight' => 5],
            ['name' => 'Learning / Training', 'weight' => 5],
            ['name' => 'Communication', 'weight' => 5],
            ['name' => 'Initiative', 'weight' => 5],
        ];

        $questionTypes = ['Score 1-5', 'Yes/No', 'Percentage', 'Number', 'Text Comment', 'Target vs Actual', 'Pass/Fail', 'N/A'];
        $evaluationTypes = ['Probation', 'Monthly', 'Quarterly', 'Semi-Annual', 'Annual', 'Promotion Review', 'Performance Improvement Review', 'Custom'];

        return view('admin.staffEvaluations.settings', compact('defaultSections', 'questionTypes', 'evaluationTypes'));
    }
}
