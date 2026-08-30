<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeDisciplinaryRecord;
use App\Models\EmployeeContract;
use App\Models\EmployeeContractHistory;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmploymentHistory;
use App\Models\EmployeeGoal;
use App\Models\EmployeeImprovementPlan;
use App\Models\EmployeeInterview;
use App\Models\EmployeeJobResponsibility;
use App\Models\EmployeeKpi;
use App\Models\EmployeeLeaveType;
use App\Models\EmployeeProfile;
use App\Models\EmployeeProfileAuditLog;
use App\Models\EmployeeReward;
use App\Models\EmployeeSalaryHistory;
use App\Models\EmployeeTrainingHistory;
use App\Models\LeaveRequestMaster;
use App\Models\PerformanceReview;
use App\Models\Post;
use App\Models\TimeLeave;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeProfileController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('employee.profile.view');
        $employmentStatus = $request->input('employment_status', 'active');
        $requestedPerPage = $request->input('per_page', 25);
        $perPage = $requestedPerPage === 'all'
            ? 'all'
            : (in_array((int) $requestedPerPage, [10, 25, 50, 100], true) ? (int) $requestedPerPage : 25);

        $employeeQuery = User::with([
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
                'employee360Profile:id,employee_id,employment_status,last_working_date',
                'employeePerformanceReviews:id,employee_id,review_type,review_date,next_review_date,status',
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('english_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->branch_id, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($request->department_id, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($request->post_id, fn ($query, $postId) => $query->where('post_id', $postId))
            ->when($employmentStatus, function ($query, $status) {
                $query->where(function ($query) use ($status) {
                    $query->whereHas('employee360Profile', fn ($profileQuery) => $profileQuery->where('employment_status', $status));

                    if (in_array($status, ['active', 'inactive'], true)) {
                        $query->orWhere(function ($fallbackQuery) use ($status) {
                            $fallbackQuery->doesntHave('employee360Profile')
                                ->where('is_active', $status === 'active' ? 1 : 0);
                        });
                    }
                });
            });

        if ($request->review_status) {
            $matchingEmployeeIds = (clone $employeeQuery)
                ->get()
                ->filter(function ($employee) use ($request) {
                    return collect($this->employeeReviewMilestones($employee))
                        ->contains(fn ($milestone) => $milestone['status'] === $request->review_status);
                })
                ->pluck('id');

            $employeeQuery->whereIn('id', $matchingEmployeeIds->isNotEmpty() ? $matchingEmployeeIds : [0]);
        }

        $employeeQuery->latest('id');
        $employees = $perPage === 'all'
            ? $employeeQuery->get()
            : $employeeQuery->paginate($perPage);

        $branches = Branch::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'dept_name')->orderBy('dept_name')->get();
        $posts = Post::select('id', 'post_name')->orderBy('post_name')->get();

        return view('admin.employees.profile.index', compact('employees', 'branches', 'departments', 'posts', 'employmentStatus', 'perPage'));
    }

    public function show(User $employee)
    {
        $this->authorizeEmployeeProfile($employee, 'employee.profile.view');

        $employee->load([
            'branch:id,name,logo',
            'department:id,dept_name',
            'post:id,post_name',
            'supervisor:id,name',
            'officeTime:id,shift,opening_time,closing_time,is_late_check_in,checkin_after',
            'employee360Profile',
            'employeeSalary',
        ]);

        $profile = $employee->employee360Profile ?: new EmployeeProfile(['employee_id' => $employee->id]);
        $latestSalary = EmployeeSalaryHistory::where('employee_id', $employee->id)->latest('effective_date')->latest('id')->first();
        $latestReview = PerformanceReview::with('items')->where('employee_id', $employee->id)->latest('review_date')->latest('id')->first();
        $summary = $this->buildSummary($employee, $profile, $latestSalary, $latestReview);
        $attendanceSummary = $this->attendanceSummary($employee, request('from'), request('to'));
        $leaveBalance = $this->leaveBalance($employee);

        $employmentHistory = EmployeeEmploymentHistory::where('employee_id', $employee->id)->latest('effective_date')->latest('id')->get();
        $salaryHistory = EmployeeSalaryHistory::where('employee_id', $employee->id)->latest('effective_date')->latest('id')->get();
        $interviews = EmployeeInterview::where('employee_id', $employee->id)->latest('interview_date')->latest('id')->get();
        $responsibilities = EmployeeJobResponsibility::where('employee_id', $employee->id)->latest('start_date')->latest('id')->get();
        $kpis = EmployeeKpi::where('employee_id', $employee->id)->latest('id')->get();
        $reviews = PerformanceReview::with('items')->where('employee_id', $employee->id)->latest('review_date')->latest('id')->get();
        $training = EmployeeTrainingHistory::where('employee_id', $employee->id)->latest('training_date')->latest('id')->get();
        $rewards = EmployeeReward::where('employee_id', $employee->id)->latest('reward_date')->latest('id')->get();
        $discipline = EmployeeDisciplinaryRecord::where('employee_id', $employee->id)->latest('incident_date')->latest('id')->get();
        $goals = EmployeeGoal::where('employee_id', $employee->id)->latest('due_date')->latest('id')->get();
        $improvementPlans = EmployeeImprovementPlan::where('employee_id', $employee->id)->latest('start_date')->latest('id')->get();
        $documents = EmployeeDocument::where('employee_id', $employee->id)->latest('document_date')->latest('id')->get();
        $contract = EmployeeContract::firstOrNew(['employee_id' => $employee->id]);
        $contractHistories = EmployeeContractHistory::where('employee_id', $employee->id)->latest('created_at')->limit(20)->get();
        $auditLogs = EmployeeProfileAuditLog::where('employee_id', $employee->id)->latest('created_at')->limit(100)->get();

        $isOwnProfile = auth()->check() && auth()->id() === $employee->id;
        $canViewEmployment = $this->can('employee.employment.view');
        $canViewSalary = $this->can('employee.salary.view') || $this->can('employee.salary.history.view');
        $canViewInterview = $this->can('employee.interview.view');
        $canViewKpi = $this->can('employee.kpi.view');
        $canViewPerformance = $this->can('employee.performance.view') || $isOwnProfile;
        $canViewTraining = $this->can('employee.training.view') || $isOwnProfile;
        $canViewReward = $this->can('employee.reward.view');
        $canViewDiscipline = $this->can('employee.discipline.view');
        $canViewGoal = $this->can('employee.goal.view') || $isOwnProfile;
        $canViewDocument = $this->can('employee.document.view');
        $canViewAudit = $this->can('employee.audit.view');
        $warningOverviewRecords = $canViewDiscipline
            ? EmployeeDisciplinaryRecord::with([
                'employee:id,name,english_name,employee_code,username,branch_id,department_id',
                'employee.branch:id,name',
                'employee.department:id,dept_name',
            ])
                ->when(auth()->user()?->branch_id && auth()->id() !== 1, function ($query) {
                    $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('branch_id', auth()->user()->branch_id));
                })
                ->latest('incident_date')
                ->latest('id')
                ->get()
            : collect();

        return view('admin.employees.profile.show', compact(
            'employee',
            'profile',
            'summary',
            'attendanceSummary',
            'leaveBalance',
            'employmentHistory',
            'salaryHistory',
            'interviews',
            'responsibilities',
            'kpis',
            'reviews',
            'training',
            'rewards',
            'discipline',
            'goals',
            'improvementPlans',
            'documents',
            'contract',
            'contractHistories',
            'auditLogs',
            'warningOverviewRecords',
            'canViewEmployment',
            'canViewSalary',
            'canViewInterview',
            'canViewKpi',
            'canViewPerformance',
            'canViewTraining',
            'canViewReward',
            'canViewDiscipline',
            'canViewGoal',
            'canViewDocument',
            'canViewAudit',
            'latestSalary',
            'latestReview'
        ));
    }

    public function updateProfile(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.profile.edit');

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
            'employment_status' => ['nullable', Rule::in(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'])],
            'last_working_date' => ['nullable', 'date'],
            'employment_end_reason' => ['nullable', 'string'],
            'probation_period' => ['nullable', 'integer', 'min:0'],
            'probation_end_date' => ['nullable', 'date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'weekly_day_off' => ['nullable', 'string', 'max:255'],
            'starting_salary' => ['nullable', 'numeric', 'min:0'],
            'current_base_salary' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'attendance_bonus' => ['nullable', 'numeric', 'min:0'],
            'punctuality_bonus' => ['nullable', 'numeric', 'min:0'],
            'overtime' => ['nullable', 'numeric', 'min:0'],
            'other_benefits' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'salary_payment_date' => ['nullable', 'integer', 'between:1,31'],
        ]);

        if (!$this->can('employee.salary.manage')) {
            $data = collect($data)->except([
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
            ])->all();
        }

        if (in_array($data['employment_status'] ?? null, ['resigned', 'terminated', 'inactive'], true) && empty($data['last_working_date'])) {
            $data['last_working_date'] = now()->toDateString();
        }

        if (in_array($data['employment_status'] ?? null, ['active', 'probation'], true)) {
            $data['last_working_date'] = null;
            $data['employment_end_reason'] = null;
        }

        $profile = EmployeeProfile::firstOrNew(['employee_id' => $employee->id]);
        $old = $profile->exists ? $profile->getOriginal() : null;
        $data[$profile->exists ? 'updated_by' : 'created_by'] = auth()->id();
        $profile->fill($data)->save();

        $this->audit($employee, 'profile', $old ? 'update' : 'create', $profile->id, $old, $profile->fresh()->toArray(), $request);

        return back()->with('success', 'Employee profile saved.');
    }

    public function storeEmployment(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.employment.manage');

        $data = $request->validate([
            'effective_date' => ['nullable', 'date'],
            'old_position_id' => ['nullable', 'exists:posts,id'],
            'new_position_id' => ['nullable', 'exists:posts,id'],
            'old_department_id' => ['nullable', 'exists:departments,id'],
            'new_department_id' => ['nullable', 'exists:departments,id'],
            'old_branch_id' => ['nullable', 'exists:branches,id'],
            'new_branch_id' => ['nullable', 'exists:branches,id'],
            'old_manager_id' => ['nullable', 'exists:users,id'],
            'new_manager_id' => ['nullable', 'exists:users,id'],
            'change_type' => ['required', Rule::in(['promotion', 'transfer', 'demotion', 'department_change', 'branch_change', 'manager_change', 'employment_status_change', 'other'])],
            'reason' => ['nullable', 'string'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'note' => ['nullable', 'string'],
        ]);
        $data['employee_id'] = $employee->id;
        $data['requested_by'] = auth()->id();

        $record = EmployeeEmploymentHistory::create($data);
        $this->audit($employee, 'employment', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Employment history added.');
    }

    public function storeSalary(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.salary.history.manage');

        $data = $request->validate([
            'effective_date' => ['nullable', 'date'],
            'old_base_salary' => ['nullable', 'numeric', 'min:0'],
            'increase_amount' => ['nullable', 'numeric', 'min:0'],
            'increase_percentage' => ['nullable', 'numeric', 'min:0'],
            'new_base_salary' => ['required', 'numeric', 'min:0'],
            'allowance_before' => ['nullable', 'numeric', 'min:0'],
            'allowance_after' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string'],
            'reviewed_by' => ['nullable', 'exists:users,id'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'approval_status' => ['required', Rule::in(['draft', 'pending', 'approved', 'rejected', 'cancelled'])],
            'note' => ['nullable', 'string'],
        ]);
        $data['employee_id'] = $employee->id;
        $data['requested_by'] = auth()->id();

        DB::transaction(function () use ($data, $employee, $request) {
            $record = EmployeeSalaryHistory::create($data);
            $this->audit($employee, 'salary', 'create', $record->id, null, $record->toArray(), $request);

            if ($record->approval_status === 'approved') {
                $profile = EmployeeProfile::firstOrNew(['employee_id' => $employee->id]);
                $old = $profile->exists ? $profile->getOriginal() : null;
                $profile->current_base_salary = $record->new_base_salary;
                $profile->allowances = $record->allowance_after;
                $profile->updated_by = auth()->id();
                if (!$profile->exists) {
                    $profile->created_by = auth()->id();
                }
                $profile->save();
                $this->audit($employee, 'salary', 'approve', $record->id, $old, $profile->fresh()->toArray(), $request);
            }
        });

        return back()->with('success', 'Salary history added.');
    }

    public function storeInterview(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.interview.manage');
        $record = EmployeeInterview::create($this->withEmployee($employee, $request->validate([
            'interview_date' => ['nullable', 'date'],
            'interview_stage' => ['required', Rule::in(['screening', 'first_interview', 'second_interview', 'technical', 'manager', 'final'])],
            'interviewer_id' => ['nullable', 'exists:users,id'],
            'interviewer_name' => ['nullable', 'string', 'max:255'],
            'interviewer_position' => ['nullable', 'string', 'max:255'],
            'recruitment_source' => ['nullable', Rule::in(['facebook', 'tiktok', 'referral', 'walk_in', 'recruitment_agency', 'other'])],
            'result' => ['required', Rule::in(['pending', 'passed', 'failed', 'selected', 'rejected'])],
            'score' => ['nullable', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string'],
            'final_approved_by' => ['nullable', 'exists:users,id'],
        ]), ['created_by' => auth()->id()]));
        $this->audit($employee, 'interview', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Interview record added.');
    }

    public function storeResponsibility(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        $record = EmployeeJobResponsibility::create($this->withEmployee($employee, $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'kpi_target' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]), ['assigned_by' => auth()->id()]));
        $this->audit($employee, 'kpi', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Responsibility added.');
    }

    public function storeKpi(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        $record = EmployeeKpi::create($this->withEmployee($employee, $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_value' => ['nullable', 'numeric'],
            'actual_value' => ['nullable', 'numeric'],
            'unit' => ['required', Rule::in(['number', 'percentage', 'currency', 'minutes', 'hours', 'days', 'custom'])],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'achievement_percentage' => ['nullable', 'numeric', 'min:0'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'manager_comment' => ['nullable', 'string'],
        ]), ['created_by' => auth()->id()]));
        $this->audit($employee, 'kpi', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'KPI added.');
    }

    public function storeReview(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.performance.create');

        $data = $request->validate([
            'review_type' => ['required', Rule::in(['monthly', 'quarterly', 'six_month', 'annual', 'probation', 'special'])],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'review_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'employee_acknowledged', 'manager_approved', 'hr_approved', 'completed', 'rejected'])],
            'strengths' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'manager_comment' => ['nullable', 'string'],
            'employee_comment' => ['nullable', 'string'],
            'final_recommendation' => ['nullable', 'string'],
            'next_review_date' => ['nullable', 'date'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'max_score' => ['required', 'array'],
            'max_score.*' => ['required', 'numeric', 'min:0'],
            'score' => ['required', 'array'],
            'score.*' => ['required', 'numeric', 'min:0'],
            'comment' => ['nullable', 'array'],
            'comment.*' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($employee, $data, $request) {
            $items = [];
            $totalScore = 0;
            foreach ($data['criteria'] as $index => $criteria) {
                $maxScore = (float) ($data['max_score'][$index] ?? 0);
                $score = min((float) ($data['score'][$index] ?? 0), $maxScore);
                $totalScore += $score;
                $items[] = [
                    'criteria' => $criteria,
                    'description' => $data['description'][$index] ?? null,
                    'max_score' => $maxScore,
                    'score' => $score,
                    'weight' => $maxScore,
                    'comment' => $data['comment'][$index] ?? null,
                    'sort_order' => $index + 1,
                ];
            }

            $review = PerformanceReview::create([
                'employee_id' => $employee->id,
                'review_type' => $data['review_type'],
                'period_start' => $data['period_start'] ?? null,
                'period_end' => $data['period_end'] ?? null,
                'review_date' => $data['review_date'] ?? now()->toDateString(),
                'evaluator_id' => auth()->id(),
                'total_score' => $totalScore,
                'grade' => $this->grade($totalScore),
                'status' => $data['status'],
                'strengths' => $data['strengths'] ?? null,
                'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
                'manager_comment' => $data['manager_comment'] ?? null,
                'employee_comment' => $data['employee_comment'] ?? null,
                'final_recommendation' => $data['final_recommendation'] ?? null,
                'next_review_date' => $data['next_review_date'] ?? null,
            ]);
            $review->items()->createMany($items);
            $this->audit($employee, 'performance', 'create', $review->id, null, $review->load('items')->toArray(), $request);
        });

        return back()->with('success', 'Performance review added.');
    }

    public function storeTraining(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.training.manage');
        $record = EmployeeTrainingHistory::create($this->withEmployee($employee, $request->validate([
            'training_date' => ['nullable', 'date'],
            'training_title' => ['required', 'string', 'max:255'],
            'training_type' => ['required', Rule::in(['internal', 'external', 'online', 'on_job_training', 'orientation'])],
            'trainer_name' => ['nullable', 'string', 'max:255'],
            'trainer_employee_id' => ['nullable', 'exists:users,id'],
            'provider' => ['nullable', 'string', 'max:255'],
            'objective' => ['nullable', 'string'],
            'result' => ['nullable', 'string', 'max:255'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'certificate' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]), ['created_by' => auth()->id()]));
        $this->audit($employee, 'training', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Training history added.');
    }

    public function storeReward(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.reward.manage');
        $record = EmployeeReward::create($this->withEmployee($employee, $request->validate([
            'reward_date' => ['nullable', 'date'],
            'reward_type' => ['required', Rule::in(['praise', 'certificate', 'bonus', 'employee_of_month', 'achievement', 'promotion', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reward_amount' => ['nullable', 'numeric', 'min:0'],
            'approved_by' => ['nullable', 'exists:users,id'],
        ]), ['created_by' => auth()->id()]));
        $this->audit($employee, 'reward', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Reward added.');
    }

    public function storeDiscipline(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');
        $data = $request->validate([
            'incident_date' => ['nullable', 'date'],
            'record_type' => ['required', Rule::in(['verbal_warning', 'written_warning', 'final_warning', 'suspension', 'disciplinary_action', 'other'])],
            'severity' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string'],
            'warning_level' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(['draft', 'active', 'resolved', 'cancelled'])],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employee-discipline', 'local');
        }
        $record = EmployeeDisciplinaryRecord::create($this->withEmployee($employee, $data, ['issued_by' => auth()->id()]));
        $this->audit($employee, 'discipline', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Discipline record added.');
    }

    public function storeGoal(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.goal.manage');
        $record = EmployeeGoal::create($this->withEmployee($employee, $request->validate([
            'performance_review_id' => ['nullable', 'exists:performance_reviews,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'progress' => ['required', 'integer', 'between:0,100'],
            'status' => ['required', Rule::in(['not_started', 'in_progress', 'completed', 'overdue', 'cancelled'])],
            'employee_comment' => ['nullable', 'string'],
            'manager_comment' => ['nullable', 'string'],
        ]), ['assigned_by' => auth()->id()]));
        $this->audit($employee, 'goal', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Goal added.');
    }

    public function storeImprovementPlan(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.goal.manage');
        $record = EmployeeImprovementPlan::create($this->withEmployee($employee, $request->validate([
            'performance_review_id' => ['nullable', 'exists:performance_reviews,id'],
            'reason' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'expectations' => ['nullable', 'string'],
            'support_required' => ['nullable', 'string'],
            'progress_notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'completed', 'failed', 'cancelled'])],
            'approved_by' => ['nullable', 'exists:users,id'],
        ]), ['created_by' => auth()->id()]));
        $this->audit($employee, 'goal', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Improvement plan added.');
    }

    public function storeDocument(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.manage');
        $data = $request->validate([
            'document_type' => ['required', Rule::in(['national_id', 'employment_contract', 'cv', 'certificate', 'salary_letter', 'promotion_letter', 'warning_letter', 'performance_review', 'training_certificate', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'max:10240'],
            'document_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('employee-documents', 'local');
        }
        unset($data['file']);

        $record = EmployeeDocument::create($this->withEmployee($employee, $data, ['uploaded_by' => auth()->id()]));
        $this->audit($employee, 'document', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Document added.');
    }

    public function saveContract(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.manage');

        $data = $request->validate([
            'contract_no' => ['nullable', 'string', 'max:255'],
            'contract_date' => ['nullable', 'date'],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'shop_address' => ['nullable', 'string'],
            'shop_representative' => ['nullable', 'string', 'max:255'],
            'birth_address' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'main_responsibilities' => ['nullable', 'string'],
            'additional_responsibilities' => ['nullable', 'string'],
            'asset_responsibilities' => ['nullable', 'string'],
            'probation_salary' => ['nullable', 'numeric', 'min:0'],
            'extra_salary' => ['nullable', 'numeric', 'min:0'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_currency' => ['nullable', Rule::in(['USD', 'KHR'])],
            'probation_period_text' => ['nullable', 'string', 'max:255'],
            'main_contract_period' => ['nullable', 'string', 'max:255'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],
            'payment_date_text' => ['nullable', 'string', 'max:255'],
            'benefits' => ['nullable', 'string'],
            'working_time' => ['nullable', 'string', 'max:255'],
            'working_days' => ['nullable', 'string', 'max:255'],
            'holiday_text' => ['nullable', 'string', 'max:255'],
            'discipline_rules' => ['nullable', 'string'],
            'confidentiality' => ['nullable', 'string'],
            'termination_terms' => ['nullable', 'string'],
            'general_duties' => ['nullable', 'string'],
            'party_a_signature_name' => ['nullable', 'string', 'max:255'],
            'party_b_signature_name' => ['nullable', 'string', 'max:255'],
            'party_a_signed_date' => ['nullable', 'date'],
            'party_b_signed_date' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'active', 'expired', 'terminated', 'cancelled'])],
        ]);

        if (!$this->can('employee.salary.manage')) {
            $data = collect($data)->except([
                'probation_salary',
                'extra_salary',
                'monthly_salary',
                'salary_currency',
                'payment_date_text',
                'benefits',
            ])->all();
        }

        $data['attachments'] = array_values(array_filter($data['attachments'] ?? []));

        $contract = EmployeeContract::firstOrNew(['employee_id' => $employee->id]);
        $old = $contract->exists ? $contract->getOriginal() : null;
        if ($contract->exists) {
            $this->recordContractHistory($contract, 'updated');
        }
        $data[$contract->exists ? 'updated_by' : 'created_by'] = auth()->id();
        $contract->fill($data);
        $contract->employee_id = $employee->id;
        $contract->save();

        $this->audit($employee, 'document', $old ? 'update_contract' : 'create_contract', $contract->id, $old, $contract->fresh()->toArray(), $request);

        return back()->with('success', 'Employee contract saved.');
    }

    public function downloadDocument(User $employee, EmployeeDocument $document)
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.view');

        abort_unless($document->employee_id === $employee->id && $document->file_path, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path);
    }

    private function buildSummary(User $employee, EmployeeProfile $profile, ?EmployeeSalaryHistory $latestSalary, ?PerformanceReview $latestReview): array
    {
        $joiningDate = $employee->joining_date ? Carbon::parse($employee->joining_date) : null;
        $serviceEndDate = $profile->last_working_date ? Carbon::parse($profile->last_working_date) : now();

        return [
            'employee_id' => $employee->employee_code ?: $employee->username,
            'full_name' => $employee->english_name ?: $employee->name,
            'position' => $employee->post?->post_name,
            'department' => $employee->department?->dept_name,
            'branch' => $employee->branch?->name,
            'manager' => $employee->supervisor?->name,
            'join_date' => $employee->joining_date,
            'years_of_service' => $joiningDate ? $joiningDate->diffForHumans($serviceEndDate, true) : null,
            'employment_status' => $profile->employment_status ?: ($employee->is_active ? 'active' : 'inactive'),
            'probation_status' => $profile->probation_end_date && Carbon::parse($profile->probation_end_date)->isFuture() ? 'Probation' : 'Completed',
            'current_base_salary' => $profile->current_base_salary,
            'last_salary_increase' => $latestSalary?->increase_amount,
            'last_evaluation_score' => $latestReview?->total_score,
            'evaluation_grade' => $latestReview?->grade,
            'next_evaluation_date' => $latestReview?->next_review_date,
            'total_warnings' => EmployeeDisciplinaryRecord::where('employee_id', $employee->id)->count(),
            'total_rewards' => EmployeeReward::where('employee_id', $employee->id)->count(),
            'training_completed' => EmployeeTrainingHistory::where('employee_id', $employee->id)->count(),
        ];
    }

    private function recordContractHistory(EmployeeContract $contract, string $action): void
    {
        EmployeeContractHistory::create([
            'employee_id' => $contract->employee_id,
            'employee_contract_id' => $contract->id,
            'action' => $action,
            'snapshot' => $contract->toArray(),
            'created_by' => auth()->id(),
        ]);
    }

    private function employeeReviewMilestones(User $employee): array
    {
        return [
            $this->employeeReviewMilestone($employee, '3M', ['quarterly', 'probation'], 'quarterly', 3),
            $this->employeeReviewMilestone($employee, '6M', ['six_month'], 'six_month', 6),
            $this->employeeReviewMilestone($employee, '12M', ['annual'], 'annual', 12),
            $this->employeeReviewMilestone($employee, 'Yearly', ['annual'], 'annual'),
        ];
    }

    private function employeeReviewMilestone(User $employee, string $label, array $reviewTypes, string $reviewType, ?int $months = null): array
    {
        if (!$employee->joining_date) {
            return ['label' => $label, 'status' => 'N/A', 'date' => null, 'review_type' => $reviewType, 'period_start' => null, 'period_end' => null];
        }

        $joinDate = Carbon::parse($employee->joining_date)->startOfDay();
        $today = now()->startOfDay();
        $dueDate = $months ? $joinDate->copy()->addMonthsNoOverflow($months) : $joinDate->copy()->addYearNoOverflow();

        if (!$months && $today->greaterThanOrEqualTo($dueDate)) {
            $years = max(1, $joinDate->diffInYears($today));
            $dueDate = $joinDate->copy()->addYearsNoOverflow($years);
            if ($dueDate->isFuture()) {
                $dueDate->subYearNoOverflow();
            }
        }

        $done = $employee->employeePerformanceReviews
            ->filter(fn ($review) => in_array($review->review_type, $reviewTypes, true))
            ->contains(fn ($review) => $review->review_date && Carbon::parse($review->review_date)->greaterThanOrEqualTo($dueDate->copy()->subDays(30)));

        $base = [
            'label' => $label,
            'date' => $dueDate->format('Y-m-d'),
            'review_type' => $reviewType,
            'period_start' => $joinDate->format('Y-m-d'),
            'period_end' => $dueDate->format('Y-m-d'),
        ];

        if ($done) {
            return $base + ['status' => 'Done'];
        }

        if ($today->lt($dueDate)) {
            return $base + ['status' => 'Upcoming'];
        }

        return $base + ['status' => $today->diffInDays($dueDate) <= 14 ? 'Due' : 'Overdue'];
    }

    private function attendanceSummary(User $employee, ?string $from = null, ?string $to = null): array
    {
        $from = $from ?: ($employee->joining_date ?: now()->startOfMonth()->toDateString());
        $to = $to ?: now()->endOfMonth()->toDateString();
        $attendance = Attendance::with('officeTime:id,opening_time,closing_time,is_late_check_in,checkin_after')
            ->where('user_id', $employee->id)
            ->whereBetween('attendance_date', [$from, $to])
            ->get();
        $leaveRequests = LeaveRequestMaster::with('leaveType:id,name')
            ->where('requested_by', $employee->id)
            ->whereDate('leave_from', '<=', $to)
            ->whereDate('leave_to', '>=', $from)
            ->get();
        $timeLeaveRequests = TimeLeave::where('requested_by', $employee->id)
            ->whereBetween('issue_date', [$from, $to])
            ->get();
        $isDayOff = fn ($leave) => str_contains(strtolower((string) $leave->leaveType?->name), 'day off');
        $approvedLeaves = (float) $leaveRequests->filter(fn ($leave) => $leave->status === 'approved' && !$isDayOff($leave))->sum('no_of_days');
        $approvedDayOff = (float) $leaveRequests->filter(fn ($leave) => $leave->status === 'approved' && $isDayOff($leave))->sum('no_of_days');
        $pendingLeaves = (float) $leaveRequests->filter(fn ($leave) => $leave->status === 'pending' && !$isDayOff($leave))->sum('no_of_days');
        $pendingDayOff = (float) $leaveRequests->filter(fn ($leave) => $leave->status === 'pending' && $isDayOff($leave))->sum('no_of_days');
        $unapprovedLeaves = (float) $leaveRequests->whereIn('status', ['pending', 'rejected'])->sum('no_of_days');
        $presentDays = $attendance->where('attendance_status', 1)->count();
        $workingDays = Carbon::parse($from)->diffInWeekdays(Carbon::parse($to)) + 1;
        $manualLateGraceMinutes = 16;
        $lateCount = $attendance->filter(function ($record) use ($employee, $leaveRequests, $isDayOff, $manualLateGraceMinutes) {
            $attendanceApproved = $record->attendance_status === null
                || (int) $record->attendance_status === Attendance::ATTENDANCE_APPROVED;
            if (!$attendanceApproved) {
                return false;
            }
            $onLeave = $leaveRequests->contains(
                fn ($leave) => $leave->status === 'approved'
                    && !$isDayOff($leave)
                    && Carbon::parse($record->attendance_date)->gte(Carbon::parse($leave->leave_from))
                    && Carbon::parse($record->attendance_date)->lte(Carbon::parse($leave->leave_to))
            );
            if ($onLeave) {
                return false;
            }
            $checkIn = $record->check_in_at ?: $record->night_checkin;
            $officeTime = $record->officeTime ?: $employee->officeTime;
            if (!$checkIn || !$officeTime?->opening_time) {
                return false;
            }
            $allowed = Carbon::parse($officeTime->opening_time)->addMinutes($manualLateGraceMinutes);

            return Carbon::parse($checkIn)->gt($allowed);
        })->count();
        $noCheckout = $attendance->filter(fn ($record) => ($record->check_in_at || $record->night_checkin) && !$record->check_out_at && !$record->night_checkout)->count();
        $workedHours = (float) $attendance->sum('worked_hour');
        $timeLeaveApproved = $timeLeaveRequests->where('status', 'approved')->count();
        $timeLeavePending = $timeLeaveRequests->where('status', 'pending')->count();
        $officeTime = $employee->officeTime
            ? trim(($employee->officeTime->shift ? $employee->officeTime->shift . ' ' : '') . '(' . $employee->officeTime->opening_time . ' - ' . $employee->officeTime->closing_time . ')')
            : null;
        $notLateUntil = null;
        if ($employee->officeTime?->opening_time) {
            $notLateUntil = Carbon::parse($employee->officeTime->opening_time)
                ->addMinutes($manualLateGraceMinutes)
                ->format('H:i');
        }

        return [
            'from' => $from,
            'to' => $to,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'absent_days' => max(0, $workingDays - $presentDays - $approvedLeaves - $approvedDayOff),
            'approved_leave_days' => $approvedLeaves,
            'leave_days' => $approvedLeaves,
            'off_day_days' => $approvedDayOff,
            'pending_day_off_days' => $pendingDayOff,
            'pending_leave_days' => $pendingLeaves,
            'unapproved_leave_days' => $unapprovedLeaves,
            'late_count' => $lateCount,
            'late_minutes' => 0,
            'early_leave_count' => 0,
            'early_leave_minutes' => 0,
            'overtime_hours' => (float) $attendance->sum('overtime'),
            'time_leave_days' => $timeLeaveApproved,
            'time_leave_requests' => $timeLeaveRequests->count(),
            'pending_time_leave_requests' => $timeLeavePending,
            'no_checkout_days' => $noCheckout,
            'worked_hours' => $workedHours,
            'not_late_until' => $notLateUntil,
            'office_time' => $officeTime,
            'attendance_score' => min(10, max(0, $presentDays)),
        ];
    }

    private function leaveBalance(User $employee): float
    {
        $allocated = (float) ($employee->leave_allocated ?? EmployeeLeaveType::where('employee_id', $employee->id)->sum('days'));
        $used = (float) LeaveRequestMaster::where('requested_by', $employee->id)->where('status', 'approved')->sum('no_of_days');

        return max(0, $allocated - $used);
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

    private function withEmployee(User $employee, array $data, array $extra = []): array
    {
        return array_merge($data, $extra, ['employee_id' => $employee->id]);
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

    private function authorizeEmployeeProfile(User $employee, string $ability): void
    {
        if (auth('admin')->check()) {
            return;
        }

        if (auth()->id() === $employee->id && in_array($ability, [
            'employee.profile.view',
            'employee.performance.view',
            'employee.training.view',
            'employee.goal.view',
        ], true)) {
            return;
        }

        $this->authorize($ability);
    }

    private function can(string $ability): bool
    {
        return auth('admin')->check() || Gate::allows($ability);
    }
}
