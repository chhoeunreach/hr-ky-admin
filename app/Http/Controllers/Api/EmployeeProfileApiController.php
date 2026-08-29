<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmploymentHistory;
use App\Models\EmployeeGoal;
use App\Models\EmployeeImprovementPlan;
use App\Models\EmployeeInterview;
use App\Models\EmployeeJobResponsibility;
use App\Models\EmployeeKpi;
use App\Models\EmployeeProfile;
use App\Models\EmployeeProfileAuditLog;
use App\Models\EmployeeReward;
use App\Models\EmployeeSalaryHistory;
use App\Models\EmployeeTrainingHistory;
use App\Models\LeaveRequestMaster;
use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeProfileApiController extends Controller
{
    public function profile(User $employee)
    {
        $employee->load([
            'branch:id,name',
            'department:id,dept_name',
            'post:id,post_name',
            'supervisor:id,name',
            'officeTime:id,shift,opening_time,closing_time',
            'employee360Profile',
            'employeeSalary',
        ]);

        return response()->json([
            'employee' => $employee,
            'summary' => [
                'employee_id' => $employee->employee_code ?: $employee->username,
                'full_name' => $employee->english_name ?: $employee->name,
                'current_position' => $employee->post?->post_name,
                'department' => $employee->department?->dept_name,
                'branch' => $employee->branch?->name,
                'direct_manager' => $employee->supervisor?->name,
                'join_date' => $employee->joining_date,
                'employment_status' => $employee->employee360Profile?->employment_status ?: ($employee->is_active ? 'active' : 'inactive'),
                'leave_balance' => $this->leaveBalance($employee),
                'last_evaluation' => PerformanceReview::where('employee_id', $employee->id)->latest('review_date')->latest('id')->first(),
            ],
        ]);
    }

    public function updateProfile(Request $request, User $employee)
    {
        $data = $request->validate([
            'national_id' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'current_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['nullable', 'string', 'max:255'],
            'probation_period' => ['nullable', 'integer', 'min:0'],
            'probation_end_date' => ['nullable', 'date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'weekly_day_off' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = EmployeeProfile::firstOrNew(['employee_id' => $employee->id]);
        $old = $profile->exists ? $profile->getOriginal() : null;
        $profile->fill($data + ['updated_by' => auth()->id()]);
        if (!$profile->exists) {
            $profile->created_by = auth()->id();
        }
        $profile->save();
        $this->audit($employee, 'profile', $old ? 'update' : 'create', $profile->id, $old, $profile->fresh()->toArray(), $request);

        return response()->json($profile->fresh());
    }

    public function employmentHistory(User $employee)
    {
        return response()->json(EmployeeEmploymentHistory::where('employee_id', $employee->id)->latest('effective_date')->latest('id')->get());
    }

    public function storeEmploymentHistory(Request $request, User $employee)
    {
        $record = EmployeeEmploymentHistory::create($request->validate([
            'effective_date' => ['nullable', 'date'],
            'old_position_id' => ['nullable', 'exists:posts,id'],
            'new_position_id' => ['nullable', 'exists:posts,id'],
            'old_department_id' => ['nullable', 'exists:departments,id'],
            'new_department_id' => ['nullable', 'exists:departments,id'],
            'old_branch_id' => ['nullable', 'exists:branches,id'],
            'new_branch_id' => ['nullable', 'exists:branches,id'],
            'old_manager_id' => ['nullable', 'exists:users,id'],
            'new_manager_id' => ['nullable', 'exists:users,id'],
            'change_type' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ]) + ['employee_id' => $employee->id, 'requested_by' => auth()->id()]);
        $this->audit($employee, 'employment', 'create', $record->id, null, $record->toArray(), $request);

        return response()->json($record, 201);
    }

    public function salary(User $employee)
    {
        return response()->json(EmployeeProfile::firstOrNew(['employee_id' => $employee->id])->only([
            'starting_salary',
            'current_base_salary',
            'allowances',
            'commission',
            'attendance_bonus',
            'punctuality_bonus',
            'overtime',
            'other_benefits',
            'payment_method',
            'salary_payment_date',
        ]));
    }

    public function salaryHistory(User $employee)
    {
        return response()->json(EmployeeSalaryHistory::where('employee_id', $employee->id)->latest('effective_date')->latest('id')->get());
    }

    public function storeSalaryAdjustment(Request $request, User $employee)
    {
        $data = $request->validate([
            'effective_date' => ['nullable', 'date'],
            'old_base_salary' => ['nullable', 'numeric', 'min:0'],
            'increase_amount' => ['nullable', 'numeric', 'min:0'],
            'increase_percentage' => ['nullable', 'numeric', 'min:0'],
            'new_base_salary' => ['required', 'numeric', 'min:0'],
            'allowance_before' => ['nullable', 'numeric', 'min:0'],
            'allowance_after' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
            'approval_status' => ['nullable', Rule::in(['draft', 'pending', 'approved', 'rejected', 'cancelled'])],
            'note' => ['nullable', 'string'],
        ]);
        $approvalStatus = $data['approval_status'] ?? 'pending';
        unset($data['approval_status']);

        $record = EmployeeSalaryHistory::create($data + [
            'employee_id' => $employee->id,
            'requested_by' => auth()->id(),
            'approval_status' => $approvalStatus,
        ]);
        $this->audit($employee, 'salary', 'create', $record->id, null, $record->toArray(), $request);

        return response()->json($record, 201);
    }

    public function updateSalaryAdjustment(Request $request, EmployeeSalaryHistory $salaryAdjustment)
    {
        $old = $salaryAdjustment->getOriginal();
        $data = $request->validate([
            'effective_date' => ['nullable', 'date'],
            'old_base_salary' => ['nullable', 'numeric', 'min:0'],
            'increase_amount' => ['nullable', 'numeric', 'min:0'],
            'increase_percentage' => ['nullable', 'numeric', 'min:0'],
            'new_base_salary' => ['required', 'numeric', 'min:0'],
            'allowance_before' => ['nullable', 'numeric', 'min:0'],
            'allowance_after' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
            'approval_status' => ['nullable', Rule::in(['draft', 'pending', 'approved', 'rejected', 'cancelled'])],
            'note' => ['nullable', 'string'],
        ]);
        if (empty($data['approval_status'])) {
            unset($data['approval_status']);
        }
        $salaryAdjustment->update($data);
        $this->audit($salaryAdjustment->employee, 'salary', 'update', $salaryAdjustment->id, $old, $salaryAdjustment->fresh()->toArray(), $request);

        return response()->json($salaryAdjustment->fresh());
    }

    public function approveSalaryAdjustment(Request $request, EmployeeSalaryHistory $salaryAdjustment)
    {
        return $this->setSalaryStatus($request, $salaryAdjustment, 'approved');
    }

    public function rejectSalaryAdjustment(Request $request, EmployeeSalaryHistory $salaryAdjustment)
    {
        return $this->setSalaryStatus($request, $salaryAdjustment, 'rejected');
    }

    public function interviews(User $employee)
    {
        return response()->json(EmployeeInterview::where('employee_id', $employee->id)->latest('interview_date')->latest('id')->get());
    }

    public function storeInterview(Request $request, User $employee)
    {
        return $this->storeSimple($request, $employee, EmployeeInterview::class, 'interview', [
            'interview_date' => ['nullable', 'date'],
            'interview_stage' => ['required', 'string', 'max:255'],
            'interviewer_id' => ['nullable', 'exists:users,id'],
            'interviewer_name' => ['nullable', 'string', 'max:255'],
            'interviewer_position' => ['nullable', 'string', 'max:255'],
            'recruitment_source' => ['nullable', 'string', 'max:255'],
            'result' => ['required', 'string', 'max:255'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string'],
            'final_approved_by' => ['nullable', 'exists:users,id'],
        ], ['created_by' => auth()->id()]);
    }

    public function responsibilities(User $employee)
    {
        return response()->json(EmployeeJobResponsibility::where('employee_id', $employee->id)->latest('id')->get());
    }

    public function storeResponsibility(Request $request, User $employee)
    {
        return $this->storeSimple($request, $employee, EmployeeJobResponsibility::class, 'kpi', [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'kpi_target' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ], ['assigned_by' => auth()->id()]);
    }

    public function kpis(User $employee)
    {
        return response()->json(EmployeeKpi::where('employee_id', $employee->id)->latest('id')->get());
    }

    public function performanceReviews(User $employee)
    {
        return response()->json(PerformanceReview::with('items')->where('employee_id', $employee->id)->latest('review_date')->latest('id')->get());
    }

    public function storePerformanceReview(Request $request, User $employee)
    {
        $data = $request->validate([
            'review_type' => ['required', 'string', 'max:255'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'review_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.criteria' => ['required_with:items', 'string', 'max:255'],
            'items.*.max_score' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.score' => ['required_with:items', 'numeric', 'min:0'],
        ]);

        $review = DB::transaction(function () use ($data, $employee, $request) {
            $items = $data['items'] ?? $this->defaultReviewItems();
            $total = 0;
            foreach ($items as $index => $item) {
                $items[$index]['score'] = min((float) $item['score'], (float) $item['max_score']);
                $items[$index]['weight'] = $item['max_score'];
                $items[$index]['sort_order'] = $index + 1;
                $total += $items[$index]['score'];
            }
            unset($data['items']);
            $status = $data['status'] ?? 'draft';
            unset($data['status']);

            $review = PerformanceReview::create($data + [
                'employee_id' => $employee->id,
                'evaluator_id' => auth()->id(),
                'total_score' => $total,
                'grade' => $this->grade($total),
                'status' => $status,
            ]);
            $review->items()->createMany($items);
            $this->audit($employee, 'performance', 'create', $review->id, null, $review->load('items')->toArray(), $request);

            return $review;
        });

        return response()->json($review->load('items'), 201);
    }

    public function performanceReview(PerformanceReview $review)
    {
        return response()->json($review->load('items'));
    }

    public function updatePerformanceReview(Request $request, PerformanceReview $review)
    {
        $old = $review->load('items')->toArray();
        $review->update($request->validate([
            'review_type' => ['required', 'string', 'max:255'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'review_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'manager_comment' => ['nullable', 'string'],
            'employee_comment' => ['nullable', 'string'],
            'final_recommendation' => ['nullable', 'string'],
            'next_review_date' => ['nullable', 'date'],
        ]));
        $this->audit($review->employee, 'performance', 'update', $review->id, $old, $review->fresh()->toArray(), $request);

        return response()->json($review->fresh('items'));
    }

    public function submitPerformanceReview(Request $request, PerformanceReview $review)
    {
        return $this->setReviewStatus($request, $review, 'submitted');
    }

    public function acknowledgePerformanceReview(Request $request, PerformanceReview $review)
    {
        $review->employee_acknowledged_at = now();
        return $this->setReviewStatus($request, $review, 'employee_acknowledged');
    }

    public function approvePerformanceReview(Request $request, PerformanceReview $review)
    {
        $review->approved_at = now();
        return $this->setReviewStatus($request, $review, 'manager_approved');
    }

    public function hrApprovePerformanceReview(Request $request, PerformanceReview $review)
    {
        $review->approved_at = now();
        return $this->setReviewStatus($request, $review, 'hr_approved');
    }

    public function attendanceSummary(Request $request, User $employee)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $attendance = Attendance::where('user_id', $employee->id)->whereBetween('attendance_date', [$from, $to])->get();

        return response()->json([
            'working_days' => Carbon::parse($from)->diffInWeekdays(Carbon::parse($to)) + 1,
            'present_days' => $attendance->where('attendance_status', 1)->count(),
            'absent_days' => max(0, Carbon::parse($from)->diffInWeekdays(Carbon::parse($to)) + 1 - $attendance->where('attendance_status', 1)->count()),
            'approved_leave_days' => (float) LeaveRequestMaster::where('requested_by', $employee->id)->where('status', 'approved')->sum('no_of_days'),
            'unapproved_leave_days' => (float) LeaveRequestMaster::where('requested_by', $employee->id)->whereIn('status', ['pending', 'rejected'])->sum('no_of_days'),
            'late_count' => 0,
            'late_minutes' => 0,
            'early_leave_count' => 0,
            'early_leave_minutes' => 0,
            'overtime_hours' => (float) $attendance->sum('overtime'),
        ]);
    }

    public function training(User $employee)
    {
        return response()->json(EmployeeTrainingHistory::where('employee_id', $employee->id)->latest('training_date')->latest('id')->get());
    }

    public function rewards(User $employee)
    {
        return response()->json(EmployeeReward::where('employee_id', $employee->id)->latest('reward_date')->latest('id')->get());
    }

    public function disciplinaryRecords(User $employee)
    {
        return response()->json(EmployeeDisciplinaryRecord::where('employee_id', $employee->id)->latest('incident_date')->latest('id')->get());
    }

    public function goals(User $employee)
    {
        return response()->json(EmployeeGoal::where('employee_id', $employee->id)->latest('due_date')->latest('id')->get());
    }

    public function documents(User $employee)
    {
        return response()->json(EmployeeDocument::where('employee_id', $employee->id)->latest('document_date')->latest('id')->get());
    }

    public function auditHistory(User $employee)
    {
        return response()->json(EmployeeProfileAuditLog::where('employee_id', $employee->id)->latest('created_at')->get());
    }

    public function improvementPlans(User $employee)
    {
        return response()->json(EmployeeImprovementPlan::where('employee_id', $employee->id)->latest('start_date')->latest('id')->get());
    }

    private function storeSimple(Request $request, User $employee, string $model, string $module, array $rules, array $extra = [])
    {
        $record = $model::create($request->validate($rules) + $extra + ['employee_id' => $employee->id]);
        $this->audit($employee, $module, 'create', $record->id, null, $record->toArray(), $request);

        return response()->json($record, 201);
    }

    private function setSalaryStatus(Request $request, EmployeeSalaryHistory $salaryAdjustment, string $status)
    {
        DB::transaction(function () use ($request, $salaryAdjustment, $status) {
            $old = $salaryAdjustment->getOriginal();
            $salaryAdjustment->approval_status = $status;
            if ($status === 'approved') {
                $salaryAdjustment->approved_by = auth()->id();
            }
            $salaryAdjustment->save();

            if ($status === 'approved') {
                $profile = EmployeeProfile::firstOrNew(['employee_id' => $salaryAdjustment->employee_id]);
                $profile->current_base_salary = $salaryAdjustment->new_base_salary;
                $profile->allowances = $salaryAdjustment->allowance_after;
                $profile->updated_by = auth()->id();
                $profile->save();
            }

            $this->audit($salaryAdjustment->employee, 'salary', $status === 'approved' ? 'approve' : 'reject', $salaryAdjustment->id, $old, $salaryAdjustment->fresh()->toArray(), $request);
        });

        return response()->json($salaryAdjustment->fresh());
    }

    private function setReviewStatus(Request $request, PerformanceReview $review, string $status)
    {
        $old = $review->getOriginal();
        $review->status = $status;
        $review->save();
        $this->audit($review->employee, 'performance', $status, $review->id, $old, $review->fresh()->toArray(), $request);

        return response()->json($review->fresh('items'));
    }

    private function leaveBalance(User $employee): float
    {
        $used = (float) LeaveRequestMaster::where('requested_by', $employee->id)->where('status', 'approved')->sum('no_of_days');

        return max(0, (float) $employee->leave_allocated - $used);
    }

    private function grade(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A - Excellent',
            $score >= 80 => 'B - Very Good',
            $score >= 70 => 'C - Good',
            $score >= 60 => 'D - Needs Development',
            default => 'E - Immediate Improvement Required',
        };
    }

    private function defaultReviewItems(): array
    {
        return [
            ['criteria' => 'Work Quality', 'max_score' => 15, 'score' => 0],
            ['criteria' => 'Task Completion', 'max_score' => 10, 'score' => 0],
            ['criteria' => 'Customer Response', 'max_score' => 10, 'score' => 0],
            ['criteria' => 'Problem Solving', 'max_score' => 10, 'score' => 0],
            ['criteria' => 'Responsibility', 'max_score' => 10, 'score' => 0],
            ['criteria' => 'Attendance & Discipline', 'max_score' => 10, 'score' => 0],
            ['criteria' => 'Teamwork', 'max_score' => 10, 'score' => 0],
            ['criteria' => 'Accepting Guidance', 'max_score' => 5, 'score' => 0],
            ['criteria' => 'Persistence & Patience', 'max_score' => 5, 'score' => 0],
            ['criteria' => 'Learning / Training', 'max_score' => 5, 'score' => 0],
            ['criteria' => 'Communication', 'max_score' => 5, 'score' => 0],
            ['criteria' => 'Initiative', 'max_score' => 5, 'score' => 0],
        ];
    }

    private function audit(User $employee, string $module, string $action, ?int $recordId, ?array $old, ?array $new, Request $request): void
    {
        EmployeeProfileAuditLog::create([
            'employee_id' => $employee->id,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'old_values' => $old,
            'new_values' => $new,
            'performed_by' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 65535),
            'created_at' => now(),
        ]);
    }
}
