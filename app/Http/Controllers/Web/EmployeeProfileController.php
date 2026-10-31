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
use App\Models\EmployeeWarningOverviewNote;
use App\Models\LeaveRequestMaster;
use App\Models\PerformanceReview;
use App\Models\Post;
use App\Models\Resignation;
use App\Models\TimeLeave;
use App\Models\User;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeProfileController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('employee.profile.view');
        if ($request->boolean('attendance_report')) {
            $this->authorize('employee.attendance_daily_report.view');
        }
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'report_date' => ['nullable', 'date_format:Y-m-d'],
            'name_direction' => ['nullable', 'in:asc,desc'],
            'working_life_direction' => ['nullable', 'in:asc,desc'],
        ]);
        $departmentIds = $request->input('department_ids', $request->filled('department_id') ? [$request->department_id] : []);
        $availableDepartments = Department::select('id', 'dept_name', 'branch_id')->orderBy('dept_name')->get();
        $departments = $request->filled('branch_id')
            ? $availableDepartments->where('branch_id', $request->branch_id)->values()
            : $availableDepartments;
        $departmentIds = $departments->whereIn('id', $departmentIds)->pluck('id')->all();
        $attendanceReport = $request->boolean('attendance_report');
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
                'employeePerformanceReviews:id,employee_id,review_type,period_end,review_date,next_review_date,status',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                // Match every word across both names and identifiers, ignoring extra spaces.
                foreach (preg_split('/\s+/u', trim($request->input('search')), -1, PREG_SPLIT_NO_EMPTY) as $term) {
                    $query->where(function ($query) use ($term) {
                        foreach (['name', 'english_name', 'username', 'employee_code', 'email', 'phone'] as $column) {
                            $query->orWhere($column, 'like', "%{$term}%");
                        }
                        $code = str_replace('-', '', $term);
                        $query->orWhereRaw("REPLACE(REPLACE(employee_code, '-', ''), ' ', '') LIKE ?", ["%{$code}%"]);
                    });
                }
            })
            ->when($request->branch_id, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->when($departmentIds, fn ($query) => $query->whereIn('department_id', $departmentIds))
            ->when($request->post_id, fn ($query, $postId) => $query->where('post_id', $postId))
            ->when($employmentStatus, function ($query, $status) {
                $query->where(function ($query) use ($status) {
                    $query->whereHas('employee360Profile', fn ($profileQuery) => $profileQuery->where('employment_status', $status));

                    if (in_array($status, ['active', 'inactive'], true)) {
                        $query->orWhere(function ($fallbackQuery) use ($status) {
                            // A profile created for salary/documents can have no status yet.
                            $fallbackQuery->whereDoesntHave('employee360Profile', fn ($profileQuery) => $profileQuery
                                ->whereNotNull('employment_status')->where('employment_status', '!=', ''))
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

        if ($request->filled('working_life_direction')) {
            $direction = $request->input('working_life_direction') === 'asc' ? 'asc' : 'desc';
            $endDate = 'COALESCE((SELECT last_working_date FROM employee_profiles WHERE employee_id = users.id LIMIT 1), ?)';
            $duration = DB::connection()->getDriverName() === 'sqlite'
                ? "ABS(julianday({$endDate}) - julianday(users.joining_date))"
                : "ABS(DATEDIFF({$endDate}, users.joining_date))";
            $employeeQuery->orderByRaw('CASE WHEN users.joining_date IS NULL THEN 1 ELSE 0 END')
                ->orderByRaw("{$duration} {$direction}", [today()->toDateString()])
                ->orderBy('users.id');
        } elseif ($request->filled('name_direction')) {
            $direction = $request->input('name_direction') === 'desc' ? 'desc' : 'asc';
            $employeeQuery->orderByRaw("COALESCE(NULLIF(users.name, ''), users.english_name) {$direction}")->orderBy('users.id');
        } else {
            $employeeQuery->latest('id');
        }
        $employees = $attendanceReport || $perPage === 'all'
            ? $employeeQuery->get()
            : $employeeQuery->paginate($perPage);

        if (!$attendanceReport && $request->header('X-Employee-Profile-Results') === '1') {
            return view('admin.employees.profile.partials.directory-results', compact('employees'));
        }

        $branches = Branch::select('id', 'name', 'logo')->orderBy('name')->get();
        $posts = Post::select('id', 'post_name')->orderBy('post_name')->get();

        if ($attendanceReport) {
            $canPrintAttendanceReport = $this->can('employee.attendance_daily_report.print');
            $canExportAttendanceReport = $this->can('employee.attendance_daily_report.export');
            return view('admin.employees.profile.attendance-report', compact('employees', 'branches', 'departments', 'availableDepartments', 'departmentIds', 'employmentStatus', 'canPrintAttendanceReport', 'canExportAttendanceReport'));
        }

        return view('admin.employees.profile.index', compact('employees', 'branches', 'departments', 'availableDepartments', 'posts', 'employmentStatus', 'perPage', 'departmentIds'));
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

        $evaluationDefaults = app(\App\Services\DepartmentPerformanceDefaults::class)->forEmployee($employee);
        $defaultItems = array_map(fn ($criterion) => [$criterion['criteria'], $criterion['description'], $criterion['max_score']], $evaluationDefaults['evaluation_criteria']);
        $profile = $employee->employee360Profile ?: new EmployeeProfile(['employee_id' => $employee->id]);
        if ($employee->employeeSalary) {
            $salaryAmounts = app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->amounts($employee->employeeSalary);
            $profile->current_base_salary = $salaryAmounts['base'];
            $profile->allowances = $salaryAmounts['allowance'];
        }
        $latestSalary = EmployeeSalaryHistory::where('employee_id', $employee->id)->where('approval_status', 'approved')->latest('effective_date')->latest('id')->first();
        $latestReview = PerformanceReview::with(['items', 'evaluator:id,name,employee_code'])->where('employee_id', $employee->id)->latest('review_date')->latest('id')->first();
        $summary = $this->buildSummary($employee, $profile, $latestSalary, $latestReview);
        $attendanceSummary = $this->attendanceSummary($employee, request('from'), request('to'));
        $leaveBalance = $this->leaveBalance($employee);

        $employmentHistory = EmployeeEmploymentHistory::where('employee_id', $employee->id)->latest('effective_date')->latest('id')->get();
        $salaryHistory = EmployeeSalaryHistory::with(['performanceReview', 'requestedBy', 'approvedBy'])->where('employee_id', $employee->id)->latest('effective_date')->latest('id')->get();
        $firstSalary = $salaryHistory->last();
        $interviews = EmployeeInterview::where('employee_id', $employee->id)->latest('interview_date')->latest('id')->get();
        $responsibilities = EmployeeJobResponsibility::where('employee_id', $employee->id)->latest('start_date')->latest('id')->get();
        $kpis = EmployeeKpi::where('employee_id', $employee->id)->latest('id')->get();
        $reviews = PerformanceReview::with('items')->where('employee_id', $employee->id)->latest('review_date')->latest('id')->get();
        $training = EmployeeTrainingHistory::where('employee_id', $employee->id)->latest('training_date')->latest('id')->get();
        $rewards = EmployeeReward::where('employee_id', $employee->id)->latest('reward_date')->latest('id')->get();
        $discipline = EmployeeDisciplinaryRecord::where('employee_id', $employee->id)->latest('incident_date')->latest('id')->get();
        $goals = EmployeeGoal::where('employee_id', $employee->id)->latest('due_date')->latest('id')->get();
        $improvementPlans = EmployeeImprovementPlan::where('employee_id', $employee->id)->latest('start_date')->latest('id')->get();
        $isOwnProfile = auth()->check() && auth()->id() === $employee->id;
        $canViewLeaveRecords = $this->can('employee.leave_form.view') || $this->can('employee.leave_form.print');
        $canPrintLeaveForm = $this->can('employee.leave_form.print');
        $canExportLeaveForm = $this->can('employee.leave_form.export');
        $canViewTimeLeaveRecords = $this->can('employee.time_leave_form.view') || $this->can('employee.time_leave_form.print');
        $canPrintTimeLeaveForm = $this->can('employee.time_leave_form.print');
        $canExportTimeLeaveForm = $this->can('employee.time_leave_form.export');
        $leavePrintRequests = $canViewLeaveRecords
            ? LeaveRequestMaster::with('leaveType')->where('requested_by', $employee->id)->latest('id')->get()
            : collect();
        $timeLeavePrintRequests = $canViewTimeLeaveRecords
            ? TimeLeave::where('requested_by', $employee->id)->latest('id')->get()
            : collect();
        $selectedLeavePrintRequest = $leavePrintRequests->firstWhere('id', request('leave_request_id'));
        $selectedTimeLeavePrintRequest = $timeLeavePrintRequests->firstWhere('id', request('time_leave_request_id'));
        abort_if(request()->filled('leave_request_id') && !$selectedLeavePrintRequest, 404);
        abort_if(request()->filled('time_leave_request_id') && !$selectedTimeLeavePrintRequest, 404);
        $canViewAttendanceDailyReport = $this->can('employee.attendance_daily_report.view');
        $canPrintAttendanceDailyReport = $this->can('employee.attendance_daily_report.print');
        $canExportAttendanceDailyReport = $this->can('employee.attendance_daily_report.export');

        $documents = EmployeeDocument::where('employee_id', $employee->id)->latest('document_date')->latest('id')->get();
        $contract = EmployeeContract::firstOrNew(['employee_id' => $employee->id]);
        $contractHistories = EmployeeContractHistory::with('creator:id,name')->where('employee_id', $employee->id)->orderByDesc('id')->get();
        $auditLogs = EmployeeProfileAuditLog::where('employee_id', $employee->id)
            ->latest('created_at')->latest('id')->paginate(25, ['*'], 'history_page')
            ->withQueryString()->appends(['tab' => 'history']);
        $canViewEmployment = $this->can('employee.employment.view');
        $canViewResignationForm = $this->can('employee.resignation_form.view');
        $canPrintResignationForm = $this->can('employee.resignation_form.print');
        $canExportResignationForm = $this->can('employee.resignation_form.export');
        $resignationPrintRequests = ($canViewResignationForm || $canPrintResignationForm || $canExportResignationForm)
            ? Resignation::where('employee_id', $employee->id)->latest('id')->get()
            : collect();
        $selectedResignationPrintRequest = $resignationPrintRequests->firstWhere('id', request('resignation_request_id'));
        abort_if(request()->filled('resignation_request_id') && !$selectedResignationPrintRequest, 404);
        $canViewSalary = $this->can('view_salary_list') || $this->can('salary_increment') || $this->can('employee.salary.view') || $this->can('employee.salary.history.view') || $this->can('employee.salary.history.manage') || $this->can('employee.salary.history.edit') || $this->can('employee.salary.history.delete');
        $canViewInterview = $this->can('employee.interview.view');
        $canViewKpi = $this->can('employee.kpi.view');
        $canViewPerformance = $this->can('employee.performance.view') || $this->can('employee.performance.create') || $isOwnProfile;
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

        $overviewNotes = $canViewDiscipline
            ? EmployeeWarningOverviewNote::with('creator:id,name')
                ->where('employee_id', $employee->id)
                ->orderBy('section_key')
                ->latest('id')
                ->get()
            : collect();

        $previousEmployee = User::where('id', '<', $employee->id)->latest('id')->first();
        $nextEmployee = User::where('id', '>', $employee->id)->oldest('id')->first();

        $overview = $this->buildOverview(
            $employee,
            $profile,
            $summary,
            $attendanceSummary,
            $latestReview,
            $reviews,
            $goals,
            $documents,
            $training,
            $discipline,
            $salaryHistory,
            $latestSalary,
            $auditLogs->getCollection()
        );

        return view('admin.employees.profile.show', compact(
            'resignationPrintRequests',
            'selectedResignationPrintRequest',
            'leavePrintRequests',
            'timeLeavePrintRequests',
            'selectedLeavePrintRequest',
            'selectedTimeLeavePrintRequest',
            'employee',
            'profile',
            'summary',
            'attendanceSummary',
            'leaveBalance',
            'employmentHistory',
            'salaryHistory',
            'firstSalary',
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
            'evaluationDefaults',
            'defaultItems',
            'auditLogs',
            'warningOverviewRecords',
            'overviewNotes',
            'overview',
            'previousEmployee',
            'nextEmployee',
            'canViewEmployment',
            'canViewResignationForm',
            'canPrintResignationForm',
            'canExportResignationForm',
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
            'canViewLeaveRecords',
            'canPrintLeaveForm',
            'canExportLeaveForm',
            'canViewTimeLeaveRecords',
            'canPrintTimeLeaveForm',
            'canExportTimeLeaveForm',
            'canViewAttendanceDailyReport',
            'canPrintAttendanceDailyReport',
            'canExportAttendanceDailyReport',
            'latestSalary',
            'latestReview'
        ));
    }

    private function printableProfileForms(): array
    {
        return [
            'complete' => ['employee.complete_form.print', 'Employee Complete Form'],
            'overview' => ['employee.warning_overview.print', 'Warning Overview'],
            'warning' => ['employee.warning_form.print', 'Staff Warning Form'],
            'contract' => ['employee.contract_form.print', 'Employment Contract'],
            'salaryCertificate' => [null, 'Salary Certificate'],
            'leaveLetter' => ['employee.leave_form.print', __('index.leave_request_form')],
            'timeLeaveLetter' => ['employee.time_leave_form.print', __('index.time_leave_request_form')],
            'attendanceDailyReport' => ['employee.attendance_daily_report.print', 'Attendance Daily Report'],
            'resignationLetter' => ['employee.resignation_form.print', 'Resignation Form'],
        ];
    }

    public function savePrintForm(Request $request, User $employee)
    {
        return $this->recordPrint($request, $employee, true);
    }

    public function reserveWarningNumber(Request $request, User $employee)
    {
        $this->authorizeEmployeeProfile($employee, 'employee.profile.view');
        $this->authorizeEmployeeProfile($employee, 'employee.warning_form.print');
        $record = DB::transaction(function () use ($employee, $request) {
            $record = $this->audit($employee, 'document_number', 'reserved', null, null, ['form_type' => 'warning'], $request);
            $record->update(['new_values' => [
                'form_type' => 'warning',
                'warning_number' => 'WRN-' . str_pad((string) $record->id, 6, '0', STR_PAD_LEFT),
            ]]);
            return $record;
        });
        return response()->json(['warning_number_id' => $record->id, 'warning_number' => $record->new_values['warning_number']], 201)
            ->header('Cache-Control', 'private, no-store');
    }

    public function printFormHistory(Request $request, User $employee)
    {
        $forms = $this->printableProfileForms();
        $request->validate(['form_type' => ['required', Rule::in(array_keys($forms))], 'latest' => ['nullable', 'boolean']]);
        $this->authorizeEmployeeProfile($employee, 'employee.profile.view');
        $ability = $forms[$request->input('form_type')][0];
        if ($ability) $this->authorizeEmployeeProfile($employee, $ability);
        else abort_unless($this->can('employee.salary.view') || $this->can('employee.salary.history.view'), 403);
        $records = EmployeeProfileAuditLog::where('employee_id', $employee->id)->where('module', 'document_form')
            ->where('new_values->form_type', $request->input('form_type'))->orderByDesc('id')->paginate($request->boolean('latest') ? 1 : 25);
        return response()->json($records->through(fn ($record) => [
            'id' => $record->id, 'action' => $record->action, 'created_at' => $record->created_at,
            'created_by' => data_get($record->new_values, 'performed_by_name'),
            'document_text' => data_get($record->new_values, 'document_text'),
            'fields' => data_get($record->new_values, 'fields', []),
            'warning_number' => data_get($record->new_values, 'warning_number'),
        ]))->header('Cache-Control', 'private, no-store');
    }

    public function recordPrint(Request $request, User $employee, bool $saveOnly = false)
    {
        $linkedPrint = in_array($request->input('form_type'), ['leaveLetter', 'timeLeaveLetter', 'resignationLetter', 'contract'], true);
        $forms = $this->printableProfileForms();
        $data = $request->validate([
            'form_type' => ['required', Rule::in(array_keys($forms))],
            'source_request_id' => [$linkedPrint ? 'required' : 'prohibited', 'integer', 'min:1'],
            'saved_form_id' => [$saveOnly || $linkedPrint ? 'nullable' : 'required', 'integer', 'min:1'],
            'warning_number_id' => [$request->input('form_type') === 'warning' ? 'nullable' : 'prohibited', 'integer', 'min:1'],
            'document_text' => ['required', 'string', 'max:120000'],
            'fields' => ['present', 'array', 'max:500'],
            'fields.*' => ['array:label,value,checked'],
            'fields.*.label' => ['required', 'string', 'max:500'],
            'fields.*.value' => ['nullable', 'string', 'max:20000'],
            'fields.*.checked' => ['nullable', 'boolean'],
        ], [
            'source_request_id.required' => __('index.select_saved_request_before_print'),
            'saved_form_id.required' => __('index.create_form_before_print'),
        ]);
        $this->authorizeEmployeeProfile($employee, 'employee.profile.view');
        [$ability, $title] = $forms[$data['form_type']];
        if ($ability) {
            $this->authorizeEmployeeProfile($employee, $ability);
        } else {
            abort_unless($this->can('employee.salary.view') || $this->can('employee.salary.history.view'), 403);
        }

        // Persist before the browser is allowed to open its print dialog.
        // This records a request; browsers cannot confirm physical printer completion.
        $record = DB::transaction(function () use ($data, $employee, $request, $title, $linkedPrint, $saveOnly) {
            $source = null;
            $sourceType = null;
            if ($linkedPrint) {
                [$sourceType, $query, $employeeColumn] = match ($data['form_type']) {
                    'leaveLetter' => ['leave', LeaveRequestMaster::query(), 'requested_by'],
                    'timeLeaveLetter' => ['time_leave', TimeLeave::query(), 'requested_by'],
                    'resignationLetter' => ['resignation', Resignation::query(), 'employee_id'],
                    'contract' => ['contract', EmployeeContract::query(), 'employee_id'],
                };
                $source = $query->where($employeeColumn, $employee->id)->whereKey($data['source_request_id'])
                    ->lockForUpdate()->first();
                if (!$source) {
                    throw ValidationException::withMessages(['source_request_id' => __('index.invalid_print_request_link')]);
                }
            }
            $saved = null;
            if (!empty($data['saved_form_id'])) {
                $saved = EmployeeProfileAuditLog::where('employee_id', $employee->id)->where('module', 'document_form')
                    ->where('new_values->form_type', $data['form_type'])->whereKey($data['saved_form_id'])->lockForUpdate()->first();
                if (!$saved) throw ValidationException::withMessages(['saved_form_id' => __('index.create_form_before_print')]);
            }
            if (!$saveOnly && !$linkedPrint && (data_get($saved?->new_values, 'document_text') !== $data['document_text']
                || data_get($saved?->new_values, 'fields') !== $data['fields'])) {
                throw ValidationException::withMessages(['saved_form_id' => __('index.save_changes_before_print')]);
            }
            $numberReservation = null;
            if ($saveOnly && !empty($data['warning_number_id'])) {
                $numberReservation = EmployeeProfileAuditLog::where('employee_id', $employee->id)
                    ->where('module', 'document_number')->where('new_values->form_type', 'warning')
                    ->whereKey($data['warning_number_id'])->lockForUpdate()->first();
                $owner = data_get($numberReservation?->new_values, 'form_record_id');
                if (!$numberReservation || ($owner && (int) $owner !== (int) ($saved?->record_id ?: $saved?->id))) {
                    throw ValidationException::withMessages(['warning_number_id' => __('index.saved_print_form_failed')]);
                }
            }
            $record = $this->audit($employee, $saveOnly ? 'document_form' : 'document_print',
                $saveOnly ? ($saved ? 'update' : 'create') : 'print_requested',
                $saveOnly && $saved ? ($saved->record_id ?: $saved->id) : null, $saveOnly ? $saved?->new_values : null, [
                'saved_form_id' => $saved?->id,
                'form_type' => $data['form_type'],
                'warning_number' => data_get($saved?->new_values, 'warning_number'),
                'document_title' => $title,
                'document_text' => $data['document_text'],
                'fields' => $data['fields'],
                'performed_by_name' => auth()->user()?->name ?? auth('admin')->user()?->name,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_code,
                'paper_size' => 'A4',
                'document_locale' => app()->getLocale(),
                'source_request_type' => $sourceType,
                'source_request_id' => $source?->id,
                'source_request_snapshot' => $source?->only(match ($sourceType) {
                    'leave' => ['id', 'requested_by', 'leave_type_id', 'leave_from', 'leave_to', 'no_of_days', 'leave_requested_date', 'reasons', 'status', 'admin_remark'],
                    'resignation' => ['id', 'employee_id', 'resignation_date', 'last_working_day', 'reason', 'status', 'admin_remark', 'letter_details'],
                    'contract' => array_merge(['id', 'updated_at'], (new EmployeeContract())->getFillable()),
                    default => ['id', 'requested_by', 'issue_date', 'start_time', 'end_time', 'reasons', 'status', 'admin_remark'],
                }),
            ], $request);
            if ($saveOnly && $data['form_type'] === 'warning') {
                $values = $record->new_values;
                $values['warning_number'] = data_get($saved?->new_values, 'warning_number')
                    ?: data_get($numberReservation?->new_values, 'warning_number')
                    ?: 'WRN-' . str_pad((string) ($saved?->record_id ?: $saved?->id ?: $record->id), 6, '0', STR_PAD_LEFT);
                $numberPosition = strpos($values['document_text'], 'WRN-______');
                if ($numberPosition !== false && !data_get($saved?->new_values, 'warning_number')) {
                    $values['document_text'] = substr_replace($values['document_text'], $values['warning_number'], $numberPosition, strlen('WRN-______'));
                }
                $record->update(['new_values' => $values]);
                if ($numberReservation) {
                    $numberReservation->update(['new_values' => array_merge($numberReservation->new_values, [
                        'form_record_id' => $saved?->record_id ?: $saved?->id ?: $record->id,
                    ])]);
                }
            }
            return $record;
        });

        return response()->json($saveOnly ? ['saved' => true, 'saved_form_id' => $record->id, 'warning_number' => data_get($record->new_values, 'warning_number')] : ['recorded' => true, 'print_record_id' => $record->id], 201);
    }

    public function downloadSalaryCertificateWord(User $employee)
    {
        $this->authorizeEmployeeProfile($employee, 'employee.profile.view');
        abort_unless(
            $this->can('employee.salary.view') || $this->can('employee.salary.history.view'),
            403
        );

        $employee->load([
            'branch:id,name,logo',
            'department:id,dept_name',
            'post:id,post_name',
            'employee360Profile',
            'employeeSalary',
        ]);

        $profile = $employee->employee360Profile ?: new EmployeeProfile(['employee_id' => $employee->id]);
        if ($employee->employeeSalary) {
            $salaryAmounts = app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->amounts($employee->employeeSalary);
            $profile->current_base_salary = $salaryAmounts['base'];
            $profile->allowances = $salaryAmounts['allowance'];
        }
        $latestSalary = EmployeeSalaryHistory::where('employee_id', $employee->id)->where('approval_status', 'approved')
            ->latest('effective_date')
            ->latest('id')
            ->first();
        $firstSalary = EmployeeSalaryHistory::where('employee_id', $employee->id)
            ->oldest('effective_date')
            ->oldest('id')
            ->first();
        $contract = EmployeeContract::where('employee_id', $employee->id)->first();
        $logoRelativePath = $employee->branch?->logo
            ? Branch::UPLOAD_PATH . $employee->branch->logo
            : 'assets/images/logo.png';
        $logoPath = public_path($logoRelativePath);
        $certificateLogoData = null;
        if (File::isFile($logoPath)) {
            $certificateLogoData = 'data:' . File::mimeType($logoPath)
                . ';base64,' . base64_encode(File::get($logoPath));
        }
        $content = view('admin.employees.profile.exports.salary-certificate-word', compact(
            'employee',
            'profile',
            'latestSalary',
            'firstSalary',
            'contract',
            'certificateLogoData'
        ))->render();
        $employeeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($employee->english_name ?: $employee->name ?: $employee->employee_code));
        $employeeName = trim($employeeName, '-') ?: 'Employee-' . $employee->id;
        $filename = 'Salary-Certificate-' . $employeeName . '-' . now()->format('Ymd') . '.doc';
    }

    public function updateProfile(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.profile.edit');

        $data = $request->validate([
            'national_id' => ['nullable', 'string', 'max:255'],
            'national_id_expiry_date' => ['nullable', 'date'],
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
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
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

        // Payroll owns the current base and allowance once salary is configured.
        if ($employee->employeeSalary) {
            unset($data['current_base_salary'], $data['allowances']);
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
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }

        $request->merge(['salary_form' => '1']);
        $linkedReview = $request->filled('performance_review_id');
        $data = $request->validate([
            'performance_review_id' => ['nullable', 'integer', Rule::exists('performance_reviews', 'id')
                ->where('employee_id', $employee->id)->where('status', 'completed')],
            'effective_date' => ['required', 'date'],
            'old_base_salary' => ['required', 'numeric', 'min:0'],
            'increase_amount' => ['nullable', 'numeric', 'min:0'],
            'increase_percentage' => ['nullable', 'numeric', 'min:0'],
            'new_base_salary' => ['required', 'numeric', 'gt:old_base_salary', 'max:99999999.99'],
            'allowance_before' => ['nullable', 'numeric', 'min:0'],
            'allowance_after' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'reviewed_by' => ['nullable', 'exists:users,id'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'approval_status' => ['required', Rule::in($linkedReview ? ['pending'] : ['draft', 'pending', 'approved'])],
            'note' => ['nullable', 'string', 'max:5000'],
        ], [
            'new_base_salary.gt' => __('index.salary_increase_must_be_greater'),
            'performance_review_id.exists' => __('index.salary_review_must_be_completed'),
            'approval_status.in' => $linkedReview ? __('index.salary_increase_pending_required') : __('validation.in', ['attribute' => __('index.status')]),
        ], [
            'performance_review_id' => __('index.linked_performance_review'),
            'effective_date' => __('index.effective_date'),
            'old_base_salary' => __('index.old_base_salary'),
            'new_base_salary' => __('index.new_base_salary'),
            'increase_amount' => __('index.increase_amount'),
            'increase_percentage' => __('index.increase_percentage'),
            'allowance_before' => __('index.allowance_before'),
            'allowance_after' => __('index.allowance_after'),
            'approval_status' => __('index.status'),
            'reason' => __('index.reason'),
            'note' => __('index.note'),
        ]);
        if (($data['approval_status'] ?? '') === 'approved') {
            $this->authorizeEmployeeProfile($employee, 'salary_increment');
        }
        if ($linkedReview || isset($data['old_base_salary'])) {
            $data['increase_amount'] = round((float) $data['new_base_salary'] - (float) $data['old_base_salary'], 2);
            $data['increase_percentage'] = (float) $data['old_base_salary'] > 0
                ? round($data['increase_amount'] / (float) $data['old_base_salary'] * 100, 2) : null;
        }
        $data['employee_id'] = $employee->id;
        $data['requested_by'] = auth('admin')->check() ? null : auth()->id();
        $data['requested_by_name'] = (auth('admin')->user() ?? auth()->user())?->name;
        $data['approved_by'] = null;
        $data['approved_by_name'] = null;
        $data['reviewed_by'] = null;

        DB::transaction(function () use ($data, $employee, $request) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $record = EmployeeSalaryHistory::create($data);
            $this->audit($employee, 'salary', 'create', $record->id, null, $record->toArray(), $request);

            if ($record->approval_status === 'approved') {
                $record->update(['approval_status' => 'pending']);
                app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->approve($record);
            }
        });

        if ($linkedReview || $request->input('employee_modal') === '1') {
            return redirect()->route('admin.employees.profile.show', [
                'employee' => $employee->id, 'tab' => 'salary',
                ...($request->input('employee_modal') === '1' ? ['employee_modal' => '1'] : []),
            ])->with('success', __($linkedReview ? 'index.salary_increase_requested' : 'index.salary_history_added'));
        }

        return back()->with('success', __('index.salary_history_added'));
    }

    private function authorizeSalaryHistory(User $employee, EmployeeSalaryHistory $salary, string $ability): void
    {
        $this->authorizeEmployeeProfile($employee, $ability);
        abort_unless((int) $salary->employee_id === (int) $employee->id, 404);
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }
    }

    public function editSalaryHistory(User $employee, EmployeeSalaryHistory $salary)
    {
        $this->authorizeSalaryHistory($employee, $salary, 'employee.salary.history.edit');
        return view('admin.employees.profile.salary-history-edit', compact('employee', 'salary'));
    }

    public function updateSalaryHistory(Request $request, User $employee, EmployeeSalaryHistory $salary): RedirectResponse
    {
        $this->authorizeSalaryHistory($employee, $salary, 'employee.salary.history.edit');
        DB::transaction(function () use ($request, $employee, $salary) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $record = EmployeeSalaryHistory::whereKey($salary->id)->lockForUpdate()->firstOrFail();
            $rules = ['reason' => ['nullable', 'string', 'max:2000'], 'note' => ['nullable', 'string', 'max:5000']];
            if (in_array($record->approval_status, ['draft', 'pending'], true)) {
                $rules += [
                    'effective_date' => ['required', 'date'],
                    'old_base_salary' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
                    'new_base_salary' => ['required', 'numeric', 'gt:old_base_salary', 'max:99999999.99'],
                    'allowance_before' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                    'allowance_after' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
                ];
            } else {
                foreach (['effective_date', 'old_base_salary', 'new_base_salary', 'allowance_before', 'allowance_after'] as $field) {
                    $rules[$field] = ['prohibited'];
                }
            }
            $data = $request->validate($rules);
            if (isset($data['new_base_salary'])) {
                $data['increase_amount'] = round((float) $data['new_base_salary'] - (float) $data['old_base_salary'], 2);
                $data['increase_percentage'] = (float) $data['old_base_salary'] > 0
                    ? round($data['increase_amount'] / (float) $data['old_base_salary'] * 100, 2) : null;
            }
            $old = $record->toArray();
            $record->fill($data)->save();
            $this->audit($employee, 'salary', 'update', $record->id, $old, $record->toArray(), $request);
        });

        return $this->salaryHistoryRedirect($request, $employee)->with('success', __('index.salary_history_updated'));
    }

    public function deleteSalaryHistory(Request $request, User $employee, EmployeeSalaryHistory $salary): RedirectResponse
    {
        $this->authorizeSalaryHistory($employee, $salary, 'employee.salary.history.delete');
        DB::transaction(function () use ($request, $employee, $salary) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $record = EmployeeSalaryHistory::whereKey($salary->id)->lockForUpdate()->firstOrFail();
            $this->audit($employee, 'salary', 'delete', $record->id, $record->toArray(), null, $request);
            $record->delete();
        });

        return $this->salaryHistoryRedirect($request, $employee)->with('success', __('index.salary_history_deleted'));
    }

    private function salaryHistoryRedirect(Request $request, User $employee): RedirectResponse
    {
        return redirect()->route('admin.employees.profile.show', [
            'employee' => $employee->id, 'tab' => 'salary',
            ...($request->input('employee_modal') === '1' ? ['employee_modal' => '1'] : []),
        ]);
    }

    public function updateSalaryStatus(Request $request, User $employee, EmployeeSalaryHistory $salary): RedirectResponse
    {
        $status = $request->validate(['approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'cancelled'])]])['approval_status'];
        $this->authorizeEmployeeProfile($employee, in_array($status, ['approved', 'rejected'], true) ? 'salary_increment' : 'employee.salary.history.manage');
        abort_unless((int) $salary->employee_id === (int) $employee->id, 404);
        if (!auth('admin')->check() && auth()->user()?->branch_id) {
            abort_unless((int) $employee->branch_id === (int) auth()->user()->branch_id, 403);
        }
        DB::transaction(function () use ($request, $employee, $salary, $status) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $record = EmployeeSalaryHistory::whereKey($salary->id)->lockForUpdate()->firstOrFail();
            $allowed = match ($record->approval_status) {
                'draft' => ['pending', 'cancelled'],
                'pending' => ['approved', 'rejected', 'cancelled'],
                default => [],
            };
            if (!in_array($status, $allowed, true)) {
                throw ValidationException::withMessages(['approval_status' => __('index.salary_manage_closed')]);
            }
            if ($status === 'approved') {
                app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->approve($record);
            } else {
                $old = $record->toArray();
                $record->approval_status = $status;
                $record->reviewed_by = auth('admin')->check() ? null : auth()->id();
                $record->save();
                $this->audit($employee, 'salary', $status, $record->id, $old, $record->toArray(), $request);
            }
        });
        return redirect()->route('admin.employees.profile.show', [
            'employee' => $employee->id, 'tab' => 'salary',
            ...($request->input('employee_modal') === '1' ? ['employee_modal' => '1'] : []),
        ])
            ->with('success', __('index.salary_manage_status_saved'));
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

    public function applyDepartmentDefaults(User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        $counts = app(\App\Services\DepartmentPerformanceDefaults::class)->applyToEmployee($employee);
        return back()->with('success', __('index.employee_defaults_applied', $counts));
    }

    private function responsibilityData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'],
            'kpi_target' => ['nullable', 'string', 'max:255'], 'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        if (array_key_exists('weight', $data)) $data['weight'] ??= 0;
        return $data;
    }

    private function kpiData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'],
            'target_value' => ['nullable', 'numeric'], 'actual_value' => ['nullable', 'numeric'],
            'unit' => ['required', Rule::in(['number', 'percentage', 'currency', 'minutes', 'hours', 'days', 'custom'])],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'achievement_percentage' => ['nullable', 'numeric', 'min:0'], 'score' => ['nullable', 'numeric', 'min:0'],
            'manager_comment' => ['nullable', 'string'],
        ]);
        if (array_key_exists('weight', $data)) $data['weight'] ??= 0;
        return $data;
    }

    public function storeResponsibility(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        $request->merge(['performance_record_type' => 'responsibility', 'performance_record_id' => null]);
        $data = $this->responsibilityData($request);
        $data['weight'] ??= 0;
        DB::transaction(function () use ($data, $employee, $request) {
            $record = EmployeeJobResponsibility::create($data + ['employee_id' => $employee->id, 'assigned_by' => auth()->id()]);
            $this->audit($employee, 'responsibility', 'create', $record->id, null, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', __('index.responsibility_saved'));
    }

    public function updateResponsibility(Request $request, User $employee, EmployeeJobResponsibility $responsibility): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        abort_unless((int) $responsibility->employee_id === (int) $employee->id, 404);
        $request->merge(['performance_record_type' => 'responsibility', 'performance_record_id' => $responsibility->id]);
        $data = $this->responsibilityData($request);
        DB::transaction(function () use ($data, $employee, $responsibility, $request) {
            $record = EmployeeJobResponsibility::where('employee_id', $employee->id)->lockForUpdate()->findOrFail($responsibility->id);
            $old = $record->toArray();
            $record->update($data);
            $this->audit($employee, 'responsibility', 'update', $record->id, $old, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', __('index.responsibility_saved'));
    }

    public function destroyResponsibility(Request $request, User $employee, EmployeeJobResponsibility $responsibility): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        abort_unless((int) $responsibility->employee_id === (int) $employee->id, 404);
        DB::transaction(function () use ($employee, $responsibility, $request) {
            $record = EmployeeJobResponsibility::where('employee_id', $employee->id)->lockForUpdate()->findOrFail($responsibility->id);
            $this->audit($employee, 'responsibility', 'delete', $record->id, $record->toArray(), null, $request);
            $record->delete();
        });
        return back()->with('success', __('index.responsibility_deleted'));
    }

    public function storeKpi(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        $request->merge(['performance_record_type' => 'kpi', 'performance_record_id' => null]);
        $data = $this->kpiData($request);
        $data['weight'] ??= 0;
        DB::transaction(function () use ($data, $employee, $request) {
            $record = EmployeeKpi::create($data + ['employee_id' => $employee->id, 'created_by' => auth()->id()]);
            $this->audit($employee, 'kpi', 'create', $record->id, null, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', __('index.employee_kpi_saved'));
    }

    public function updateKpi(Request $request, User $employee, EmployeeKpi $kpi): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        abort_unless((int) $kpi->employee_id === (int) $employee->id, 404);
        $request->merge(['performance_record_type' => 'kpi', 'performance_record_id' => $kpi->id]);
        $data = $this->kpiData($request);
        DB::transaction(function () use ($data, $employee, $kpi, $request) {
            $record = EmployeeKpi::where('employee_id', $employee->id)->lockForUpdate()->findOrFail($kpi->id);
            $old = $record->toArray();
            $record->update($data);
            $this->audit($employee, 'kpi', 'update', $record->id, $old, $record->fresh()->toArray(), $request);
        });
        return back()->with('success', __('index.employee_kpi_saved'));
    }

    public function destroyKpi(Request $request, User $employee, EmployeeKpi $kpi): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.kpi.manage');
        abort_unless((int) $kpi->employee_id === (int) $employee->id, 404);
        DB::transaction(function () use ($employee, $kpi, $request) {
            $record = EmployeeKpi::where('employee_id', $employee->id)->lockForUpdate()->findOrFail($kpi->id);
            $this->audit($employee, 'kpi', 'delete', $record->id, $record->toArray(), null, $request);
            $record->delete();
        });
        return back()->with('success', __('index.employee_kpi_deleted'));
    }

    public function storeReview(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.performance.create');

        $quickComplete = $request->boolean('quick_complete');
        try {
            $data = $request->validate([
                'quick_complete' => ['sometimes', 'boolean'],
                'review_type' => ['required', Rule::in(['monthly', 'quarterly', 'six_month', 'annual', 'probation', 'special'])],
                'period_start' => [$quickComplete ? 'required' : 'nullable', 'date'],
                'period_end' => $quickComplete ? ['required', 'date', 'after_or_equal:period_start'] : ['nullable', 'date'],
                'review_date' => [$quickComplete ? 'required' : 'nullable', 'date'],
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
                'score.*' => $quickComplete ? ['required', 'numeric', 'min:0', 'max:100'] : ['required', 'numeric', 'min:0'],
                'comment' => ['nullable', 'array'],
                'comment.*' => ['nullable', 'string'],
            ], [], [
                'review_type' => __('index.type'),
                'period_start' => __('index.period_start'),
                'period_end' => __('index.period_end'),
                'review_date' => __('index.review_date'),
                'status' => __('index.status'),
                'strengths' => __('index.strengths'),
                'areas_for_improvement' => __('index.areas_for_improvement'),
                'manager_comment' => __('index.manager_comment'),
                'employee_comment' => __('index.comment'),
                'final_recommendation' => __('index.final_recommendation'),
                'next_review_date' => __('index.next_review'),
                'criteria' => __('index.criteria'),
                'criteria.*' => __('index.criteria'),
                'description.*' => __('index.standard'),
                'max_score' => __('index.max'),
                'max_score.*' => __('index.max'),
                'score' => __('index.score'),
                'score.*' => __('index.score'),
                'comment.*' => __('index.comment'),
            ]);

            DB::transaction(function () use ($employee, $data, $request, $quickComplete) {
                if ($quickComplete) {
                    User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
                    $alreadyCompleted = PerformanceReview::where('employee_id', $employee->id)
                        ->where('review_type', $data['review_type'])
                        ->whereDate('period_start', $data['period_start'])
                        ->whereDate('period_end', $data['period_end'])
                        ->where('status', 'completed')->exists();
                    if ($alreadyCompleted) {
                        return;
                    }
                    $data['status'] = 'completed';
                }
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
        } catch (ValidationException $exception) {
            if ($request->input('employee_modal') === '1') {
                $exception->redirectTo(route('admin.employees.profile.show', [
                    'employee' => $employee->id, 'tab' => 'evaluation', 'review_create' => 1, 'employee_modal' => '1',
                ]));
            }
            throw $exception;
        }

        if ($quickComplete) {
            if ($request->input('employee_modal') === '1') {
                return redirect()->route('admin.employees.profile.show', [
                    'employee' => $employee->id, 'tab' => 'evaluation', 'employee_modal' => '1',
                ])->with('success', __('index.performance_review_completed'));
            }
            return back()->with('success', __('index.performance_review_completed'));
        }

        return redirect()->route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'evaluation'])
            ->with('success', __('index.performance_review_added'));
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
        $data = $this->validatedDisciplineData($request);
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('employee-discipline', 'local');
        }
        $record = EmployeeDisciplinaryRecord::create($this->withEmployee($employee, $data, ['issued_by' => auth()->id()]));
        $this->audit($employee, 'discipline', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Discipline record added.');
    }

    public function updateDiscipline(Request $request, User $employee, EmployeeDisciplinaryRecord $discipline): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');
        abort_unless($discipline->employee_id === $employee->id, 404);

        $data = $this->validatedDisciplineData($request);
        if ($request->hasFile('attachment')) {
            if ($discipline->attachment && Storage::disk('local')->exists($discipline->attachment)) {
                Storage::disk('local')->delete($discipline->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('employee-discipline', 'local');
        }

        $old = $discipline->getOriginal();
        $discipline->update($data);
        $this->audit($employee, 'discipline', 'update', $discipline->id, $old, $discipline->fresh()->toArray(), $request);

        return back()->with('success', 'Discipline record updated.');
    }

    public function destroyDiscipline(Request $request, User $employee, EmployeeDisciplinaryRecord $discipline): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');
        abort_unless($discipline->employee_id === $employee->id, 404);

        $old = $discipline->toArray();
        if ($discipline->attachment && Storage::disk('local')->exists($discipline->attachment)) {
            Storage::disk('local')->delete($discipline->attachment);
        }
        $disciplineId = $discipline->id;
        $discipline->delete();
        $this->audit($employee, 'discipline', 'delete', $disciplineId, $old, null, $request);

        return back()->with('success', 'Discipline record deleted.');
    }

    public function viewDisciplineAttachment(Request $request, User $employee, EmployeeDisciplinaryRecord $discipline): BinaryFileResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');
        abort_unless($discipline->employee_id === $employee->id, 404);
        abort_unless($discipline->attachment && Storage::disk('local')->exists($discipline->attachment), 404);

        $path = Storage::disk('local')->path($discipline->attachment);
        $mime = File::mimeType($path) ?: 'application/octet-stream';
        $filename = basename($discipline->attachment);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function storeOverviewNote(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');

        $data = $request->validate([
            'section_key' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $record = EmployeeWarningOverviewNote::create([
            'employee_id' => $employee->id,
            'section_key' => $data['section_key'],
            'content' => $data['content'],
            'created_by' => auth()->id(),
        ]);
        $this->audit($employee, 'warning_overview', 'create', $record->id, null, $record->toArray(), $request);

        return back()->with('success', 'Overview note added.');
    }

    public function updateOverviewNote(Request $request, User $employee, EmployeeWarningOverviewNote $overviewNote): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');
        abort_unless($overviewNote->employee_id === $employee->id, 404);

        $data = $request->validate([
            'section_key' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $old = $overviewNote->getOriginal();
        $overviewNote->update($data);
        $this->audit($employee, 'warning_overview', 'update', $overviewNote->id, $old, $overviewNote->fresh()->toArray(), $request);

        return back()->with('success', 'Overview note updated.');
    }

    public function destroyOverviewNote(Request $request, User $employee, EmployeeWarningOverviewNote $overviewNote): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.discipline.manage');
        abort_unless($overviewNote->employee_id === $employee->id, 404);

        $old = $overviewNote->toArray();
        $id = $overviewNote->id;
        $overviewNote->delete();
        $this->audit($employee, 'warning_overview', 'delete', $id, $old, null, $request);

        return back()->with('success', 'Overview note deleted.');
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
        $validated = $request->validate([
            'documents' => ['required', 'array', 'min:1', 'max:20'],
            'documents.*.document_type' => ['required', Rule::in(['national_id', 'employment_contract', 'cv', 'certificate', 'salary_letter', 'promotion_letter', 'warning_letter', 'performance_review', 'training_certificate', 'other'])],
            'documents.*.title' => ['required', 'string', 'max:255'],
            'documents.*.file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
            'documents.*.document_date' => ['nullable', 'date'],
            'documents.*.expiry_date' => ['nullable', 'date'],
            'documents.*.note' => ['nullable', 'string'],
            'ocr_profile' => ['nullable', 'array'],
            'ocr_profile.national_id' => ['nullable', 'string', 'max:255'],
            'ocr_profile.nationality' => ['nullable', 'string', 'max:255'],
            'ocr_profile.national_id_expiry_date' => ['nullable', 'date'],
        ]);

        $storedPaths = [];
        try {
            DB::transaction(function () use ($validated, $employee, $request, &$storedPaths) {
                foreach ($validated['documents'] as $document) {
                    $file = $document['file'];
                    unset($document['file']);
                    $document['file_path'] = $file->store('employee-documents', 'local');
                    if (!$document['file_path']) {
                        throw new \RuntimeException('Document upload failed.');
                    }
                    $storedPaths[] = $document['file_path'];
                    $record = EmployeeDocument::create($this->withEmployee($employee, $document, ['uploaded_by' => auth()->id()]));
                    $this->audit($employee, 'document', 'create', $record->id, null, $record->toArray(), $request);
                }

                if ($this->can('employee.profile.edit') && !empty($validated['ocr_profile'])) {
                    $profile = EmployeeProfile::firstOrNew(['employee_id' => $employee->id]);
                    $old = $profile->exists ? $profile->getOriginal() : null;
                    $profile->fill(array_intersect_key($validated['ocr_profile'], array_flip([
                        'national_id', 'nationality', 'national_id_expiry_date',
                    ])));
                    $profile->setAttribute($profile->exists ? 'updated_by' : 'created_by', auth()->id());
                    $profile->save();
                    $this->audit($employee, 'profile', $old ? 'update' : 'create', $profile->id, $old, $profile->fresh()->toArray(), $request);
                }
            });
        } catch (\Throwable $exception) {
            if ($storedPaths) {
                Storage::disk('local')->delete($storedPaths);
            }
            throw $exception;
        }

        return redirect()->route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'personal'])
            ->with('success', count($validated['documents']) . ' document(s) added.');
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

        DB::transaction(function () use ($employee, $data, $request) {
            User::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $contract = EmployeeContract::where('employee_id', $employee->id)->lockForUpdate()->first()
                ?? new EmployeeContract(['employee_id' => $employee->id]);
            $old = $contract->exists ? $contract->getOriginal() : null;
            $data[$contract->exists ? 'updated_by' : 'created_by'] = auth()->id();
            $contract->fill($data)->save();
            $this->recordContractHistory($contract->fresh(), $old ? 'updated' : 'created');
            $this->audit($employee, 'document', $old ? 'update_contract' : 'create_contract', $contract->id, $old, $contract->fresh()->toArray(), $request);
        });

        return back()->with('success', 'Employee contract saved.');
    }

    public function downloadDocument(User $employee, EmployeeDocument $document)
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.view');

        abort_unless($document->employee_id === $employee->id && $document->file_path, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path);
    }

    public function viewDocument(User $employee, EmployeeDocument $document)
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.view');

        abort_unless($document->employee_id === $employee->id && $document->file_path, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $path = Storage::disk('local')->path($document->file_path);
        $mimeType = File::mimeType($path);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($document->file_path) . '"',
        ]);
    }

    public function updateDocument(Request $request, User $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.manage');
        abort_unless($document->employee_id === $employee->id, 404);

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['national_id', 'employment_contract', 'cv', 'certificate', 'salary_letter', 'promotion_letter', 'warning_letter', 'performance_review', 'training_certificate', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'document_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx'],
        ]);

        $old = $document->toArray();

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('employee-documents', 'local');
        }
        unset($validated['file']);

        $document->update($validated);
        $this->audit($employee, 'document', 'update', $document->id, $old, $document->fresh()->toArray(), $request);

        return redirect()->route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'personal'])
            ->with('success', __('index.document_updated_successfully') ?? 'Document updated successfully.');
    }

    public function destroyDocument(Request $request, User $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->authorizeEmployeeProfile($employee, 'employee.document.manage');
        abort_unless($document->employee_id === $employee->id, 404);

        $old = $document->toArray();
        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }
        $docId = $document->id;
        $document->delete();
        $this->audit($employee, 'document', 'delete', $docId, $old, null, $request);

        return redirect()->route('admin.employees.profile.show', ['employee' => $employee->id, 'tab' => 'personal'])
            ->with('success', __('index.document_deleted_successfully') ?? 'Document deleted successfully.');
    }

    private function buildOverview(
        User $employee,
        EmployeeProfile $profile,
        array $summary,
        array $attendanceSummary,
        ?PerformanceReview $latestReview,
        $reviews,
        $goals,
        $documents,
        $training,
        $discipline,
        $salaryHistory,
        ?EmployeeSalaryHistory $latestSalary,
        $auditLogs
    ): array {
        $workingDays = (int) ($attendanceSummary['working_days'] ?? 0);
        $presentDays = (int) ($attendanceSummary['present_days'] ?? 0);
        $attendanceRate = $workingDays > 0 ? round(($presentDays / $workingDays) * 100) : 0;

        $leaveAllocated = (float) ($employee->leave_allocated ?? EmployeeLeaveType::where('employee_id', $employee->id)->sum('days'));
        $leaveUsed = (float) LeaveRequestMaster::where('requested_by', $employee->id)->where('status', 'approved')->sum('no_of_days');
        $leaveBalance = max(0, $leaveAllocated - $leaveUsed);
        $pendingLeave = LeaveRequestMaster::where('requested_by', $employee->id)->where('status', 'pending')->count();

        $previousReview = $reviews
            ->when($latestReview, fn ($collection) => $collection->reject(fn ($review) => $review->id === $latestReview->id))
            ->first();
        $trend = 'N/A';
        if ($latestReview && $previousReview) {
            $trend = $latestReview->total_score > $previousReview->total_score
                ? 'improving'
                : ($latestReview->total_score < $previousReview->total_score ? 'declining' : 'stable');
        }
        $scoredReviews = $reviews->sortBy('review_date');

        $goalsActive = $goals->whereIn('status', ['in_progress', 'not_started'])->count();
        $goalsCompleted = $goals->where('status', 'completed')->count();
        $goalsOverdue = $goals->where('status', 'overdue')->count();
        $avgGoalProgress = $goals->isNotEmpty() ? round((float) $goals->avg('progress')) : 0;

        $now = now();
        $documentCount = $documents->count();
        $expiringDocuments = $documents->filter(fn ($document) => $document->expiry_date && $document->expiry_date->gte($now) && $document->expiry_date->lte($now->copy()->addDays(30)))->values();
        $expiredDocuments = $documents->filter(fn ($document) => $document->expiry_date && $document->expiry_date->lt($now))->values();

        $trainingCount = $training->count();
        $trainingCertificates = $training->filter(fn ($item) => filled($item->certificate))->count();

        $activeWarnings = $discipline->whereNotIn('status', ['resolved', 'cancelled'])->count();
        $totalWarnings = $discipline->count();

        $currentBaseSalary = $employee->employeeSalary ? app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->amounts($employee->employeeSalary)['base'] : ($profile->current_base_salary ?? ($latestSalary?->new_base_salary ?? null));
        $allowances = $employee->employeeSalary ? app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->amounts($employee->employeeSalary)['allowance'] : ($profile->allowances ?? ($latestSalary?->allowance_after ?? null));

        $activity = $auditLogs->take(8)->map(function ($log) {
            return [
                'title' => data_get($log->new_values, 'document_title', ucwords(str_replace('_', ' ', (string) $log->module)))
                    . ' · ' . ucwords(str_replace('_', ' ', (string) $log->action)),
                'date' => $log->created_at,
                'by' => data_get($log->new_values, 'performed_by_name', $log->performed_by),
            ];
        })->values();

        return [
            'attendance' => [
                'rate' => $attendanceRate,
                'working_days' => $workingDays,
                'present' => $presentDays,
                'late' => (int) ($attendanceSummary['late_count'] ?? 0),
                'absent' => (int) ($attendanceSummary['absent_days'] ?? 0),
                'early_leave' => (int) ($attendanceSummary['early_leave_count'] ?? 0),
                'overtime_hours' => (float) ($attendanceSummary['overtime_hours'] ?? 0),
                'worked_hours' => (float) ($attendanceSummary['worked_hours'] ?? 0),
                'from' => $attendanceSummary['from'] ?? null,
                'to' => $attendanceSummary['to'] ?? null,
                'chart' => [
                    'labels' => ['Present', 'Late', 'Absent', 'Leave'],
                    'values' => [
                        $presentDays,
                        (int) ($attendanceSummary['late_count'] ?? 0),
                        (int) ($attendanceSummary['absent_days'] ?? 0),
                        (int) ($attendanceSummary['leave_days'] ?? 0),
                    ],
                ],
            ],
            'leave' => [
                'allocated' => $leaveAllocated,
                'used' => $leaveUsed,
                'balance' => $leaveBalance,
                'pending' => $pendingLeave,
            ],
            'performance' => [
                'score' => $latestReview ? (float) $latestReview->total_score : null,
                'grade' => $latestReview?->grade,
                'review_date' => $latestReview?->review_date,
                'period' => $latestReview ? trim(($latestReview->period_start ? $latestReview->period_start->format('Y-m-d') : '') . ' → ' . ($latestReview->period_end ? $latestReview->period_end->format('Y-m-d') : '')) : null,
                'reviewer' => $latestReview?->evaluator?->name,
                'previous_score' => $previousReview ? (float) $previousReview->total_score : null,
                'trend' => $trend,
                'chart' => $reviews->isNotEmpty() ? [
                    'labels' => $scoredReviews->map(fn ($review) => $review->review_date ? $review->review_date->format('M Y') : '')->values()->all(),
                    'scores' => $scoredReviews->map(fn ($review) => (float) $review->total_score)->values()->all(),
                ] : null,
            ],
            'goals' => [
                'active' => $goalsActive,
                'completed' => $goalsCompleted,
                'overdue' => $goalsOverdue,
                'avg_progress' => $avgGoalProgress,
                'top' => $goals->sortByDesc('progress')->take(3)->values(),
            ],
            'documents' => [
                'total' => $documentCount,
                'expiring' => $expiringDocuments,
                'expired' => $expiredDocuments,
                'recent' => $documents->take(3)->values(),
            ],
            'training' => [
                'total' => $trainingCount,
                'certificates' => $trainingCertificates,
                'recent' => $training->take(3)->values(),
            ],
            'discipline' => [
                'active' => $activeWarnings,
                'total' => $totalWarnings,
                'latest' => $discipline->first(),
            ],
            'payroll' => [
                'base_salary' => $currentBaseSalary,
                'allowances' => $allowances,
                'last_adjustment' => $salaryHistory->first(),
            ],
            'employment' => [
                'years_of_service' => $summary['years_of_service'] ?? null,
                'employment_type' => $employee->employment_type ?: ($employee->workspace_type ?: 'N/A'),
                'status' => $summary['employment_status'] ?? ($employee->is_active ? 'active' : 'inactive'),
                'joining_date' => $summary['join_date'] ?? null,
                'probation_end_date' => $profile->probation_end_date,
                'position' => $summary['position'] ?? null,
                'department' => $summary['department'] ?? null,
                'branch' => $summary['branch'] ?? null,
                'manager' => $summary['manager'] ?? null,
            ],
            'personal' => [
                'name' => $employee->name,
                'english_name' => $employee->english_name,
                'gender' => $employee->gender,
                'dob' => $employee->dob,
                'phone' => $employee->phone,
                'email' => $employee->email,
                'address' => $employee->address ?: $profile->current_address,
                'emergency_contact' => $profile->emergency_contact_name
                    ? trim($profile->emergency_contact_name . ($profile->emergency_contact_phone ? ' · ' . $profile->emergency_contact_phone : ''))
                    : null,
            ],
            'activity' => $activity,
            'summary360' => [
                'employment' => ucfirst($summary['employment_status'] ?? 'N/A') . ' / ' . ($employee->employment_type ?: 'N/A'),
                'attendance' => $attendanceRate . '%',
                'performance' => $latestReview ? round((float) $latestReview->total_score) . '%' : 'N/A',
                'leave' => $leaveBalance . ' ' . ($leaveBalance == 1 ? 'Day' : 'Days'),
                'goals' => $avgGoalProgress . '%',
                'training' => $trainingCount . ' ' . ($trainingCount == 1 ? 'Record' : 'Records'),
                'discipline' => $activeWarnings > 0 ? 'Active Warning' : 'No Active Warning',
            ],
        ];
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
            'current_base_salary' => $employee->employeeSalary ? app(\App\Services\Payroll\EmployeeSalaryManagementService::class)->amounts($employee->employeeSalary)['base'] : $profile->current_base_salary,
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
            'snapshot' => $contract->toArray() + ['performed_by_name' => auth('admin')->user()?->name ?: auth()->user()?->name],
            'created_by' => auth()->id(),
        ]);
    }

    private function employeeReviewMilestones(User $employee): array
    {
        return [
            $this->employeeReviewMilestone($employee, __('index.review_three_months'), ['quarterly', 'probation'], 'quarterly', 3),
            $this->employeeReviewMilestone($employee, __('index.review_six_months'), ['six_month'], 'six_month', 6),
            $this->employeeReviewMilestone($employee, __('index.review_twelve_months'), ['annual'], 'annual', 12),
            $this->employeeReviewMilestone($employee, __('index.review_yearly'), ['annual'], 'annual'),
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
            ->contains(fn ($review) => $review->completesMilestone($dueDate));

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

    private function validatedDisciplineData(Request $request): array
    {
        return $request->validate([
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

    private function audit(User $employee, string $module, string $action, ?int $recordId, ?array $old, ?array $new, Request $request): EmployeeProfileAuditLog
    {
        return EmployeeProfileAuditLog::create([
            'employee_id' => $employee->id,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'old_values' => $old,
            'new_values' => $new === null ? null : $new + ['performed_by_name' => (auth('admin')->user() ?? auth()->user())?->name],
            'performed_by' => auth('admin')->check() ? null : auth()->id(),
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
