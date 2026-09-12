<?php

namespace App\Http\Controllers\Web;

use App\Exports\AttendanceDayWiseExport;
use App\Exports\UserExport;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\EmployeeLocation;
use App\Models\EmployeeProfile;
use App\Models\Event;
use App\Models\Holiday;
use App\Models\LeaveRequestMaster;
use App\Models\TimeLeave;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\EmployeeLeaveTypeRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\OfficeTimeRepository;
use App\Repositories\PostRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Requests\Leave\LeaveTypeRequest;
use App\Requests\User\ChangePasswordRequest;
use App\Requests\User\UserAccountRequest;
use App\Requests\User\UserCreateRequest;
use App\Requests\User\UserLeaveTypeRequest;
use App\Requests\User\UserUpdateRequest;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Carbon\Carbon;

class UserController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.employees.';


    public function __construct(protected UserRepository              $userRepo,
                                protected CompanyRepository           $companyRepo,
                                protected RoleRepository              $roleRepo,
                                protected OfficeTimeRepository        $officeTimeRepo,
                                protected UserAccountRepository       $accountRepo,
                                protected CompanyRepository           $companyRepository,
                                protected BranchRepository            $branchRepository,
                                protected LeaveTypeRepository         $leaveTypeRepository,
                                protected EmployeeLeaveTypeRepository $employeeLeaveTypeRepository,
                                protected PostRepository              $postRepository,
                                protected TelegramService             $telegramService,

    )
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_employee');
        try {


            $branchIds = $request->query('branch_id', []);
            if (!is_array($branchIds)) {
                $branchIds = filled($branchIds) ? [$branchIds] : [];
            }
            $branchIds = array_values(array_filter($branchIds));

            $departmentIds = $request->query('department_id', []);
            if (!is_array($departmentIds)) {
                $departmentIds = filled($departmentIds) ? [$departmentIds] : [];
            }
            $departmentIds = array_values(array_filter($departmentIds));

            $postIds = $request->query('post_id', []);
            if (!is_array($postIds)) {
                $postIds = filled($postIds) ? [$postIds] : [];
            }
            $postIds = array_values(array_filter($postIds));

            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'search' => $request->search ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'is_active' => $request->is_active ?? null,
                'branch_id' => $branchIds,
                'department_id' => $departmentIds,
                'post_id' => $postIds,
                'role_id' => $request->role_id ?? null,
                'per_page' => $request->per_page ?? getRecordPerPage(),
            ];

            if(!auth('admin')->check() && auth()->check() && filled(auth()->user()->branch_id)){
                $filterParameters['branch_id'] = [auth()->user()->branch_id];
            }

            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name', 'role:id,name','officeTime:id,shift,opening_time,closing_time','supervisor:id,name'];

            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $users = $this->userRepo->getAllUsers($filterParameters, $select, $with);

            $company = $this->companyRepository->getCompanyDetail(['id']);
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($company->id, ['id', 'name']);
            $roles = \App\Models\Role::get(['id', 'name']);

            $userCounts = User::where('company_id', $company->id)
                ->where('status', 'verified')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                ')->first();

            $stats = [
                'total' => (int) ($userCounts->total ?? 0),
                'active' => (int) ($userCounts->active ?? 0),
                'inactive' => (int) ($userCounts->inactive ?? 0),
                'branches' => $branches->count(),
            ];

            if ($request->input('action') == 'export') {
                $fileName = 'users.xlsx';
                return \Maatwebsite\Excel\Facades\Excel::download(new UserExport($users), $fileName);
            }

            return view($this->view . 'index', compact('users', 'filterParameters', 'branches', 'roles', 'stats'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function calendar(Request $request)
    {
        $this->authorize('list_employee');

        try {
            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'search' => $request->search ?? null,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'is_active' => $request->is_active ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'post_id' => $request->post_id ?? null,
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            if (empty($filterParameters['branch_id'])) {
                $filterParameters['department_id'] = null;
                $filterParameters['post_id'] = null;
            }

            if (empty($filterParameters['department_id'])) {
                $filterParameters['post_id'] = null;
            }

            $company = $this->companyRepository->getCompanyDetail(['id']);
            $branches = $this->branchRepository->getLoggedInUserCompanyBranches($company->id, ['id', 'name']);
            $employeeCalendarMonth = $this->employeeCalendarMonth($request);
            $employeeCalendar = $this->employeeCalendarData($filterParameters, $employeeCalendarMonth);

            return view($this->view . 'calendar', compact('filterParameters', 'branches', 'employeeCalendar', 'employeeCalendarMonth'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function employeeCalendarMonth(Request $request): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', (string) $request->input('calendar_month', now()->format('Y-m')))->startOfMonth();
        } catch (Exception $exception) {
            return now()->startOfMonth();
        }
    }

    private function employeeCalendarData(array $filterParameters, Carbon $month): array
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        $days = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $key = $date->toDateString();
            $days[$key] = [
                'date' => $key,
                'day' => $date->day,
                'weekday' => $date->format('D'),
                'is_today' => $date->isToday(),
                'is_weekend' => in_array($date->dayOfWeek, [0, 6]),
                'events' => [],
            ];
        }

        $employees = $this->employeeCalendarEmployeeQuery($filterParameters)
            ->with(['branch:id,name', 'department:id,dept_name', 'post:id,post_name'])
            ->get(['id', 'name', 'dob', 'joining_date', 'branch_id', 'department_id', 'post_id', 'avatar', 'employee_code', 'phone', 'email']);

        $employeeIds = $employees->pluck('id')->all();
        $employeesById = $employees->keyBy('id');
        $summary = [
            'total_events' => 0,
            'birthdays' => 0,
            'anniversaries' => 0,
            'leave_requests' => 0,
            'leave_approved' => 0,
            'leave_pending' => 0,
            'time_leave_requests' => 0,
            'time_leave_approved' => 0,
            'time_leave_pending' => 0,
            'holidays' => 0,
            'company_events' => 0,
        ];

        // 1. Employee Birthdays
        foreach ($employees as $employee) {
            if (empty($employee->dob)) {
                continue;
            }

            try {
                $birthday = Carbon::parse($employee->dob);
                if ((int) $birthday->month !== (int) $month->month) {
                    continue;
                }

                $calendarDate = $month->copy()->day($birthday->day)->toDateString();
                if (!isset($days[$calendarDate])) {
                    continue;
                }

                $turningAge = $month->year - $birthday->year;
                $ageText = $turningAge > 0 ? " (Turns {$turningAge})" : '';
                $avatar = $employee->avatar ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');

                $days[$calendarDate]['events'][] = [
                    'id' => 'birthday-' . $employee->id . '-' . $calendarDate,
                    'type' => 'birthday',
                    'type_label' => 'Birthday',
                    'type_icon' => 'gift',
                    'badge_class' => 'badge-soft-pink',
                    'label' => ucfirst($employee->name),
                    'title' => ucfirst($employee->name) . "'s Birthday" . $ageText,
                    'meta' => 'Birthday' . $ageText,
                    'status' => 'celebration',
                    'date' => $calendarDate,
                    'time' => null,
                    'duration' => 'All Day',
                    'employee_name' => ucfirst($employee->name),
                    'employee_avatar' => $avatar,
                    'employee_code' => $employee->employee_code,
                    'employee_dept' => $employee->department?->dept_name ?? 'General',
                    'employee_branch' => $employee->branch?->name ?? '',
                    'employee_post' => $employee->post?->post_name ?? '',
                    'detail' => trim(($employee->branch?->name ?? '') . (($employee->department?->dept_name ?? '') ? ' - ' . $employee->department?->dept_name : '')),
                    'reason' => 'Celebrates birthday on ' . $birthday->format('M d') . $ageText,
                    'admin_remark' => null,
                    'url' => route('admin.employees.show', $employee->id),
                    'url_label' => 'View Profile',
                ];
                $summary['birthdays']++;
                $summary['total_events']++;
            } catch (Exception $e) {}
        }

        // 2. Work Anniversaries / Joining Date
        foreach ($employees as $employee) {
            if (empty($employee->joining_date)) {
                continue;
            }

            try {
                $joining = Carbon::parse($employee->joining_date);
                if ((int) $joining->month !== (int) $month->month) {
                    continue;
                }

                $calendarDate = $month->copy()->day($joining->day)->toDateString();
                if (!isset($days[$calendarDate])) {
                    continue;
                }

                $years = (int) $month->year - (int) $joining->year;
                $anniversaryTitle = $years > 0 ? "{$years} Year" . ($years > 1 ? 's' : '') . " Anniversary" : "Joined Company";
                $avatar = $employee->avatar ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');

                $days[$calendarDate]['events'][] = [
                    'id' => 'anniversary-' . $employee->id . '-' . $calendarDate,
                    'type' => 'anniversary',
                    'type_label' => 'Anniversary',
                    'type_icon' => 'award',
                    'badge_class' => 'badge-soft-purple',
                    'label' => ucfirst($employee->name),
                    'title' => ucfirst($employee->name) . ' - ' . $anniversaryTitle,
                    'meta' => $anniversaryTitle,
                    'status' => 'milestone',
                    'date' => $calendarDate,
                    'time' => null,
                    'duration' => 'Milestone',
                    'employee_name' => ucfirst($employee->name),
                    'employee_avatar' => $avatar,
                    'employee_code' => $employee->employee_code,
                    'employee_dept' => $employee->department?->dept_name ?? 'General',
                    'employee_branch' => $employee->branch?->name ?? '',
                    'employee_post' => $employee->post?->post_name ?? '',
                    'detail' => $anniversaryTitle . ' (Joined ' . $joining->format('M Y') . ')',
                    'reason' => 'Joined company on ' . $joining->format('M d, Y'),
                    'admin_remark' => null,
                    'url' => route('admin.employees.show', $employee->id),
                    'url_label' => 'View Profile',
                ];
                $summary['anniversaries']++;
                $summary['total_events']++;
            } catch (Exception $e) {}
        }

        // 3. Leave Requests
        if (!empty($employeeIds)) {
            $leaveRequests = LeaveRequestMaster::query()
                ->with('leaveType:id,name')
                ->whereIn('requested_by', $employeeIds)
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->whereDate('leave_from', '<=', $endDate->toDateString())
                ->whereDate('leave_to', '>=', $startDate->toDateString())
                ->get(['id', 'requested_by', 'leave_type_id', 'leave_from', 'leave_to', 'status', 'no_of_days', 'reasons', 'admin_remark', 'early_exit']);

            foreach ($leaveRequests as $leaveRequest) {
                $from = Carbon::parse($leaveRequest->leave_from)->max($startDate);
                $to = Carbon::parse($leaveRequest->leave_to)->min($endDate);
                $employee = $employeesById->get($leaveRequest->requested_by);
                $avatar = $employee?->avatar ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');
                $leaveTypeName = $leaveRequest->leaveType?->name ?? 'Leave';

                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $key = $date->toDateString();
                    if (!isset($days[$key])) {
                        continue;
                    }

                    $days[$key]['events'][] = [
                        'id' => 'leave-' . $leaveRequest->id . '-' . $key,
                        'type' => 'leave',
                        'type_label' => 'Leave',
                        'type_icon' => 'calendar',
                        'badge_class' => $leaveRequest->status === 'approved' ? 'badge-soft-primary' : ($leaveRequest->status === 'pending' ? 'badge-soft-warning' : 'badge-soft-secondary'),
                        'label' => ucfirst($employee?->name ?? 'Employee'),
                        'title' => ucfirst($employee?->name ?? 'Employee') . ' - ' . $leaveTypeName,
                        'meta' => ucfirst($leaveRequest->status) . ' ' . $leaveTypeName,
                        'status' => $leaveRequest->status,
                        'date' => $key,
                        'start_date' => Carbon::parse($leaveRequest->leave_from)->format('M d, Y'),
                        'end_date' => Carbon::parse($leaveRequest->leave_to)->format('M d, Y'),
                        'time' => null,
                        'duration' => ($leaveRequest->no_of_days ?? 1) . ' Day(s)',
                        'employee_name' => ucfirst($employee?->name ?? 'Employee'),
                        'employee_avatar' => $avatar,
                        'employee_code' => $employee?->employee_code,
                        'employee_dept' => $employee?->department?->dept_name ?? 'General',
                        'employee_branch' => $employee?->branch?->name ?? '',
                        'employee_post' => $employee?->post?->post_name ?? '',
                        'detail' => Carbon::parse($leaveRequest->leave_from)->format('M d') . ' - ' . Carbon::parse($leaveRequest->leave_to)->format('M d') . ' (' . ($leaveRequest->no_of_days ?? 1) . 'd)',
                        'reason' => $leaveRequest->reasons ?? 'No specific reason provided',
                        'admin_remark' => $leaveRequest->admin_remark,
                        'url' => route('admin.leave-request.show', $leaveRequest->id),
                        'url_label' => 'View Leave Details',
                    ];
                    $summary['total_events']++;
                }

                $summary['leave_requests']++;
                if ($leaveRequest->status === 'approved') {
                    $summary['leave_approved']++;
                } elseif ($leaveRequest->status === 'pending') {
                    $summary['leave_pending']++;
                }
            }

            // 4. Time Leave Requests
            $timeLeaveRequests = TimeLeave::query()
                ->whereIn('requested_by', $employeeIds)
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get(['id', 'requested_by', 'issue_date', 'start_time', 'end_time', 'status', 'reasons', 'admin_remark']);

            foreach ($timeLeaveRequests as $timeLeaveRequest) {
                $key = Carbon::parse($timeLeaveRequest->issue_date)->toDateString();
                $employee = $employeesById->get($timeLeaveRequest->requested_by);

                if (!isset($days[$key])) {
                    continue;
                }

                $avatar = $employee?->avatar ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar) : asset('assets/images/img.png');
                $timeSlot = $timeLeaveRequest->start_time . ' - ' . $timeLeaveRequest->end_time;

                $days[$key]['events'][] = [
                    'id' => 'time-leave-' . $timeLeaveRequest->id,
                    'type' => 'time_leave',
                    'type_label' => 'Time Leave',
                    'type_icon' => 'clock',
                    'badge_class' => $timeLeaveRequest->status === 'approved' ? 'badge-soft-amber' : ($timeLeaveRequest->status === 'pending' ? 'badge-soft-warning' : 'badge-soft-secondary'),
                    'label' => ucfirst($employee?->name ?? 'Employee'),
                    'title' => ucfirst($employee?->name ?? 'Employee') . ' - Time Leave (' . $timeSlot . ')',
                    'meta' => ucfirst($timeLeaveRequest->status) . ' (' . $timeSlot . ')',
                    'status' => $timeLeaveRequest->status,
                    'date' => $key,
                    'start_date' => Carbon::parse($timeLeaveRequest->issue_date)->format('M d, Y'),
                    'end_date' => Carbon::parse($timeLeaveRequest->issue_date)->format('M d, Y'),
                    'time' => $timeSlot,
                    'duration' => $timeSlot,
                    'employee_name' => ucfirst($employee?->name ?? 'Employee'),
                    'employee_avatar' => $avatar,
                    'employee_code' => $employee?->employee_code,
                    'employee_dept' => $employee?->department?->dept_name ?? 'General',
                    'employee_branch' => $employee?->branch?->name ?? '',
                    'employee_post' => $employee?->post?->post_name ?? '',
                    'detail' => $timeSlot,
                    'reason' => $timeLeaveRequest->reasons ?? 'No specific reason provided',
                    'admin_remark' => $timeLeaveRequest->admin_remark,
                    'url' => route('admin.time-leave-request.show', $timeLeaveRequest->id),
                    'url_label' => 'View Time Leave Details',
                ];
                $summary['time_leave_requests']++;
                $summary['total_events']++;
                if ($timeLeaveRequest->status === 'approved') {
                    $summary['time_leave_approved']++;
                } elseif ($timeLeaveRequest->status === 'pending') {
                    $summary['time_leave_pending']++;
                }
            }
        }

        // 5. Public & Company Holidays
        try {
            $companyId = AppHelper::getAuthUserCompanyId();
            $holidays = Holiday::query()
                ->where('company_id', $companyId)
                ->where('is_active', 1)
                ->whereBetween('event_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get(['id', 'event', 'note', 'event_date', 'is_public_holiday']);

            foreach ($holidays as $holiday) {
                $key = Carbon::parse($holiday->event_date)->toDateString();
                if (!isset($days[$key])) {
                    continue;
                }

                $isPublic = (bool) ($holiday->is_public_holiday ?? false);
                $days[$key]['events'][] = [
                    'id' => 'holiday-' . $holiday->id,
                    'type' => 'holiday',
                    'type_label' => $isPublic ? 'Public Holiday' : 'Company Holiday',
                    'type_icon' => 'flag',
                    'badge_class' => 'badge-soft-danger',
                    'label' => $holiday->event,
                    'title' => $holiday->event,
                    'meta' => $isPublic ? 'Public Holiday' : 'Company Holiday',
                    'status' => $isPublic ? 'public' : 'company',
                    'date' => $key,
                    'time' => null,
                    'duration' => 'All Day',
                    'employee_name' => 'All Staff',
                    'employee_avatar' => null,
                    'employee_code' => null,
                    'employee_dept' => 'Company-wide',
                    'employee_branch' => 'All Branches',
                    'employee_post' => null,
                    'detail' => $holiday->note ?? ($isPublic ? 'Official Public Holiday' : 'Company Holiday'),
                    'reason' => $holiday->note ?? 'Official Holiday observed',
                    'admin_remark' => null,
                    'url' => route('admin.holidays.index'),
                    'url_label' => 'View Holidays',
                ];
                $summary['holidays']++;
                $summary['total_events']++;
            }
        } catch (Exception $e) {}

        // 6. Company Events
        try {
            $events = Event::query()
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate->toDateString())
                              ->where('end_date', '>=', $endDate->toDateString());
                        });
                })
                ->get(['id', 'title', 'start_date', 'end_date', 'start_time', 'end_time', 'location', 'description', 'background_color', 'host']);

            foreach ($events as $evt) {
                $from = Carbon::parse($evt->start_date)->max($startDate);
                $to = Carbon::parse($evt->end_date ?? $evt->start_date)->min($endDate);
                $timeText = ($evt->start_time ? Carbon::parse($evt->start_time)->format('h:i A') : '') . ($evt->end_time ? ' - ' . Carbon::parse($evt->end_time)->format('h:i A') : '');

                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $key = $date->toDateString();
                    if (!isset($days[$key])) {
                        continue;
                    }

                    $days[$key]['events'][] = [
                        'id' => 'event-' . $evt->id . '-' . $key,
                        'type' => 'company_event',
                        'type_label' => 'Company Event',
                        'type_icon' => 'activity',
                        'badge_class' => 'badge-soft-teal',
                        'label' => $evt->title,
                        'title' => $evt->title,
                        'meta' => $timeText ?: ($evt->location ?? 'Event'),
                        'status' => 'event',
                        'date' => $key,
                        'time' => $timeText,
                        'duration' => $timeText ?: 'Scheduled Event',
                        'employee_name' => $evt->host ?? 'Company',
                        'employee_avatar' => null,
                        'employee_code' => null,
                        'employee_dept' => $evt->location ?? 'Headquarters',
                        'employee_branch' => '',
                        'employee_post' => null,
                        'detail' => $evt->location ? 'Location: ' . $evt->location : ($evt->description ?? 'Company Event'),
                        'reason' => $evt->description ?? 'Company Event',
                        'admin_remark' => null,
                        'url' => route('admin.event.index'),
                        'url_label' => 'View Events',
                    ];
                    $summary['total_events']++;
                }
                $summary['company_events']++;
            }
        } catch (Exception $e) {}

        // Calculate Weeks for Week View (with previous and next month padding)
        $weeks = [];
        $firstDayWeekday = $startDate->dayOfWeek; // 0 = Sun, 6 = Sat
        
        $currentWeek = [];
        for ($i = 0; $i < $firstDayWeekday; $i++) {
            $prevDate = $startDate->copy()->subDays($firstDayWeekday - $i);
            $currentWeek[] = [
                'date' => $prevDate->toDateString(),
                'day' => $prevDate->day,
                'weekday' => $prevDate->format('D'),
                'is_current_month' => false,
                'is_today' => $prevDate->isToday(),
                'is_weekend' => in_array($prevDate->dayOfWeek, [0, 6]),
                'events' => [],
            ];
        }

        foreach ($days as $dayData) {
            $dayData['is_current_month'] = true;
            $currentWeek[] = $dayData;
            if (count($currentWeek) === 7) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }

        if (!empty($currentWeek)) {
            $nextDate = $endDate->copy();
            while (count($currentWeek) < 7) {
                $nextDate->addDay();
                $currentWeek[] = [
                    'date' => $nextDate->toDateString(),
                    'day' => $nextDate->day,
                    'weekday' => $nextDate->format('D'),
                    'is_current_month' => false,
                    'is_today' => $nextDate->isToday(),
                    'is_weekend' => in_array($nextDate->dayOfWeek, [0, 6]),
                    'events' => [],
                ];
            }
            $weeks[] = $currentWeek;
        }

        $allFlatEvents = collect($days)
            ->flatMap(fn ($day) => collect($day['events'])->map(fn ($event) => array_merge($event, ['day' => $day['day'], 'weekday' => $day['weekday']])))
            ->sortBy('date')
            ->values();

        $selectedDay = collect($days)->firstWhere('is_today', true) ?? reset($days);
        $upcomingEvents = $allFlatEvents
            ->filter(fn ($e) => $e['date'] >= now()->toDateString())
            ->values()
            ->take(15)
            ->all();

        if (empty($upcomingEvents)) {
            $upcomingEvents = $allFlatEvents->take(15)->all();
        }

        return [
            'days' => $days,
            'weeks' => $weeks,
            'all_events' => $allFlatEvents->all(),
            'start_weekday' => $startDate->dayOfWeek,
            'month_label' => $month->format('F Y'),
            'month_value' => $month->format('Y-m'),
            'previous_month' => $month->copy()->subMonth()->format('Y-m'),
            'next_month' => $month->copy()->addMonth()->format('Y-m'),
            'summary' => $summary,
            'selected_day' => $selectedDay,
            'upcoming_events' => $upcomingEvents,
        ];
    }

    private function employeeCalendarEmployeeQuery(array $filterParameters)
    {
        return User::query()
            ->where('company_id', AppHelper::getAuthUserCompanyId())
            ->where('status', 'verified')
            ->when(isset($filterParameters['employee_name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['employee_name'] . '%');
            })
            ->when(isset($filterParameters['search']), function ($query) use ($filterParameters) {
                $search = $filterParameters['search'];
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('username', 'like', '%' . $search . '%')
                        ->orWhere('employee_code', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->when(isset($filterParameters['email']), function ($query) use ($filterParameters) {
                $query->where('email', 'like', '%' . $filterParameters['email'] . '%');
            })
            ->when(isset($filterParameters['phone']), function ($query) use ($filterParameters) {
                $query->where('phone', $filterParameters['phone']);
            })
            ->when(($filterParameters['is_active'] ?? null) !== null && $filterParameters['is_active'] !== '', function ($query) use ($filterParameters) {
                $query->where('is_active', (int) $filterParameters['is_active']);
            })
            ->when(($filterParameters['is_active'] ?? null) === null || ($filterParameters['is_active'] ?? '') === '', function ($query) {
                $query->where('is_active', UserRepository::IS_ACTIVE);
            })
            ->when(!empty($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(!empty($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(!empty($filterParameters['post_id']), function ($query) use ($filterParameters) {
                $query->where('post_id', $filterParameters['post_id']);
            });
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $employeeCode = AppHelper::getEmployeeCode();

            $bsEnabled = AppHelper::ifDateInBsEnabled();

            return view($this->view . 'create', compact('companyDetail', 'roles', 'employeeCode', 'bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(UserCreateRequest $request, UserAccountRequest $accountRequest, UserLeaveTypeRequest $leaveRequest)
    {
        $this->authorize('create_employee');
        try {
            $validatedData = $request->validated();

            $accountValidatedData = $accountRequest->validated();
            $leaveTypeData = $leaveRequest->validated();

            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_active'] = 1;
            $validatedData['status'] = 'verified';
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $validatedData['allow_holiday_check_in'] = isset($validatedData['allow_holiday_check_in']) ? 1 : 0;
            if (isset($validatedData['telegram_username'])) {
                $validatedData['telegram_username'] = ltrim(trim((string) $validatedData['telegram_username']), '@');
            }
            if (! empty($validatedData['telegram_chat_id'])) {
                $validatedData['telegram_linked_at'] = now();
            }

            DB::beginTransaction();
            $user = $this->userRepo->store($validatedData);
            $accountValidatedData['user_id'] = $user['id'];
            $this->accountRepo->store($accountValidatedData);

            if (!is_null($user['leave_allocated']) && isset($leaveTypeData['leave_type_id'])) {
                foreach ($leaveTypeData['leave_type_id'] as $key => $value) {
                    $input['days'] = $leaveTypeData['days'][$key] ?? 0;
                    $input['is_active'] = $leaveTypeData['is_active'][$key] ?? 0;
                    $input['employee_id'] = $user['id'];
                    $input['leave_type_id'] = $value;

                    $this->employeeLeaveTypeRepository->store($input);

                }
            }

            DB::commit();
            $this->sendNewEmployeeWelcomeTelegramNotification($user->id);
            return redirect()
                ->route('admin.employees.index')
                ->with('success', __('message.add_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_detail_employee');
        try {
            $with = [
                'branch:id,name',
                'company:id,name',
                'post:id,post_name',
                'department:id,dept_name',
                'role:id,name',
                'accountDetail'
            ];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $userDetail = $this->userRepo->findUserDetailById($id, $select, $with);
            return view($this->view . 'show2', compact('userDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    private function sendNewEmployeeWelcomeTelegramNotification(int $userId): void
    {
        try {
            $user = $this->userRepo->findUserDetailById($userId, ['*'], [
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
            ]);

            if (!$user) {
                return;
            }

            $employeeName = htmlspecialchars((string) $user->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $employeeCode = htmlspecialchars((string) ($user->employee_code ?: 'មិនទាន់មាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $branchName = htmlspecialchars((string) (optional($user->branch)->name ?: 'មិនមាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $departmentName = htmlspecialchars((string) (optional($user->department)->dept_name ?: 'មិនមាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $positionName = htmlspecialchars((string) (optional($user->post)->post_name ?: 'មិនមាន'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $joiningDate = $user->joining_date ? date('d-m-Y', strtotime($user->joining_date)) : 'មិនមាន';

            $message = "<b>សូមស្វាគមន៍បុគ្គលិកថ្មី</b>\n"
                . "ឈ្មោះ: {$employeeName}\n"
                . "លេខសម្គាល់: {$employeeCode}\n"
                . "សាខា: {$branchName}\n"
                . "ផ្នែក: {$departmentName}\n"
                . "តួនាទី: {$positionName}\n"
                . "ថ្ងៃចូលបម្រើការងារ: {$joiningDate}\n\n"
                . "សូមស្វាគមន៍មកកាន់ក្រុមការងាររបស់យើង។";

            $avatarPath = $user->avatar ? public_path(User::AVATAR_UPLOAD_PATH . $user->avatar) : null;

            if ($avatarPath && is_file($avatarPath)) {
                $this->telegramService->sendPhotoToAllKnownChats($avatarPath, $message, 'HTML');
                return;
            }

            $this->telegramService->sendToAllKnownChats($message, 'HTML');
        } catch (Exception $exception) {
            Log::warning('New employee welcome Telegram notification failed.', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {

        $this->authorize('edit_employee');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            $roles = $this->roleRepo->getAllActiveRoles();

            $userSelect = ['*'];
            $userWith = [
                'accountDetail',
                'company:id,name',
                'branch:id,name',
                'department:id,dept_name',
                'post:id,post_name',
                'role:id,name,slug',
                'supervisor:id,name',
                'officeTime:id,opening_time,closing_time,shift',
            ];
            $userDetail = $this->userRepo->findUserDetailById($id, $userSelect, $userWith);
            $leaveTypes = $this->leaveTypeRepository->getGenderSpecificPaidLeaveTypes($userDetail->branch_id,$userDetail->gender);
            $employeeLeaveTypes = $this->employeeLeaveTypeRepository->getAll(['id', 'leave_type_id', 'days', 'is_active'], $id);
            $bsEnabled = AppHelper::ifDateInBsEnabled();

            $filteredPosts = isset($userDetail->department_id)
                ? $this->postRepository->getAllActivePostsByDepartmentId($userDetail->department_id, [], ['id', 'post_name'])
                : [];

            $filteredSupervisor = isset($userDetail->department_id)
                ? $this->userRepo->getAllActiveEmployeeByDepartment($userDetail->department_id, ['id','name'])
                : [];

            return view($this->view . 'edit', compact('companyDetail', 'roles', 'userDetail', 'leaveTypes', 'employeeLeaveTypes', 'bsEnabled','filteredSupervisor','filteredPosts'));
        } catch (Exception $exception) {

            return redirect()->back()->with('danger', $exception->getFile());
        }
    }

    public function update(UserUpdateRequest $request, UserAccountRequest $accountRequest, UserLeaveTypeRequest $leaveRequest, $id)
    {
        $this->authorize('edit_employee');
        try {
            $validatedData = $request->validated();

            if (env('DEMO_MODE', false) && (in_array($id, [1, 2]))) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $accountValidatedData = $accountRequest->validated();

            $leaveTypeData = $leaveRequest->validated();


            $userDetail = $this->userRepo->findUserDetailById($id);
            if (in_array($userDetail->username, User::DEMO_USERS_USERNAME)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            $validatedData['allow_holiday_check_in'] = isset($validatedData['allow_holiday_check_in']) ? 1 : 0;
            if (isset($validatedData['telegram_username'])) {
                $validatedData['telegram_username'] = ltrim(trim((string) $validatedData['telegram_username']), '@');
            }
            if (! empty($validatedData['telegram_chat_id']) && empty($userDetail->telegram_linked_at)) {
                $validatedData['telegram_linked_at'] = now();
            } elseif (empty($validatedData['telegram_chat_id'])) {
                $validatedData['telegram_linked_at'] = null;
            }
            DB::beginTransaction();
            $this->userRepo->update($userDetail, $validatedData);
            $this->accountRepo->createOrUpdate($userDetail, $accountValidatedData);

            if (!is_null($validatedData['leave_allocated']) && isset($leaveTypeData['leave_type_id'])) {
                foreach ($leaveTypeData['leave_type_id'] as $key => $value) {
                    $input['days'] = $leaveTypeData['days'][$key];
                    $input['is_active'] = $leaveTypeData['is_active'][$key] ?? 0;

                    $employeeLeaveTypeData = $this->employeeLeaveTypeRepository->findByLeaveType($id, $value);
                    if ($employeeLeaveTypeData) {

                        $this->employeeLeaveTypeRepository->update($employeeLeaveTypeData, $input);
                    } else {
                        $input['employee_id'] = $id;
                        $input['leave_type_id'] = $value;


                        $this->employeeLeaveTypeRepository->store($input);
                    }
                }
            } else {
                $this->employeeLeaveTypeRepository->deleteByEmployee($id);
            }


            DB::commit();
            return redirect()
                ->route('admin.employees.index')
                ->with('success', __('message.update_user'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleIsActiveStatus($id);
            $userDetail = $this->userRepo->findUserDetailById($id, ['id', 'is_active']);
            $profile = EmployeeProfile::firstOrNew(['employee_id' => $userDetail->id]);
            if ((int) $userDetail->is_active === 0) {
                $profile->employment_status = 'inactive';
                $profile->last_working_date = $profile->last_working_date ?: now()->toDateString();
            } else {
                $profile->employment_status = 'active';
                $profile->last_working_date = null;
                $profile->employment_end_reason = null;
            }
            $profile->save();
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('message.user_is_active_changed'),
                    'is_active' => (int) $userDetail->is_active,
                ]);
            }

            return redirect()->back()->with('success', __('message.user_is_active_changed'));
        } catch (Exception $exception) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



    public function delete($id)
    {
        $this->authorize('delete_employee');
        try {

            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            $usersDetail = $this->userRepo->findUserDetailById($id);

            if (!$usersDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }

            if ($usersDetail->id == auth()->user()->id) {
                throw new Exception(__('message._delete_own'), 402);
            }

            DB::beginTransaction();
            $this->userRepo->delete($usersDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_remove'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeWorkSpace($id)
    {
        $this->authorize('edit_employee');
        try {
            $select = ['id', 'workspace_type'];
            $userDetail = $this->userRepo->findUserDetailById($id, $select);
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->changeWorkSpace($userDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.workspace_change'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getAllCompanyEmployeeDetail($branchId)
    {
        try {

            $branch = $this->branchRepository->findBranchDetailById($branchId);

            $selectEmployee = ['id', 'name'];
            $selectOfficeTime = ['id', 'opening_time', 'closing_time'];
            $employees = $this->userRepo->getAllVerifiedEmployeeOfCompany($selectEmployee);
            $officeTime = $this->officeTimeRepo->getALlActiveOfficeTimeByCompanyId($branch->company_id, $selectOfficeTime);

            return response()->json([
                'employee' => $employees,
                'officeTime' => $officeTime
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
    public function getAllBranchEmployees($branchId)
    {
        try {

            $selectEmployee = ['id', 'name'];
            $employees = $this->userRepo->getActiveEmployeeOfBranch($branchId, $selectEmployee);


            return response()->json([
                'employee' => $employees,
            ]);
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changePassword(ChangePasswordRequest $request, $userId)
    {
        $this->authorize('change_password');
        try {
            $validatedData = $request->validated();
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }

            $userDetail = $this->userRepo->findUserDetailById($userId);

            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            DB::beginTransaction();
            $this->userRepo->changePassword($userDetail, $validatedData['new_password']);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_password_change'));

        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function forceLogOutEmployee($employeeId)
    {
        $this->authorize('force_logout');
        try {
            $tokenRepository = app(TokenRepository::class);
            $refreshTokenRepository = app(RefreshTokenRepository::class);

            $userDetail = $this->userRepo->findUserDetailById($employeeId);
            if (!$userDetail) {
                throw new Exception(__('message.user_not_found'), 404);
            }
            $accessToken = $userDetail->tokens;
            DB::beginTransaction();
            foreach ($accessToken as $token) {
                $tokenRepository->revokeAccessToken($token->id);
                $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
            }
            $validatedData['uuid'] = null;
            $validatedData['logout_status'] = 0;
            $validatedData['remember_token'] = null;
            $validatedData['fcm_token'] = null;
            $this->userRepo->update($userDetail, $validatedData);
            DB::commit();
            return redirect()->back()->with('success', __('message.force_logout'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function deleteEmployeeLeaveType($id)
    {
        $this->authorize('delete_employee');
        try {
            $employeeLeaveType = $this->employeeLeaveTypeRepository->find($id);

            if (!$employeeLeaveType) {
                throw new Exception(__('message.employee_leave_not_found'), 404);
            }

            DB::beginTransaction();
            $this->employeeLeaveTypeRepository->delete($employeeLeaveType);
            DB::commit();
            return redirect()->back()->with('success', __('message.employee_leave_removed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function getAllEmployeeByDepartmentId($departmentId): JsonResponse|RedirectResponse
    {
        try {

            $select = ['name', 'username', 'id'];
            $users = $this->userRepo->getAllActiveEmployeeOfDepartment($departmentId, $select);
            return response()->json([
                'data' => $users
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function fetchEmployeesByDepartment(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $departmentIds = $request->input('department_ids');
            $select = ['name', 'id'];

            $employees = $this->userRepo->getActiveEmployeesByDepartment($departmentIds, $select);

            return response()->json($employees);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
    public function fetchDepartmentEmployees(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $departmentIds = $request->input('department_ids');
            $select = ['name', 'id'];

            $employees = $this->userRepo->getActiveEmployeesFromDepartments($departmentIds, $select);

            return response()->json($employees);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

//    public function export()
//    {
//        $fileName = 'users.csv';
//        return \Maatwebsite\Excel\Facades\Excel::download(new UserExport, $fileName);
//    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchEmployeeData($branchId)
    {
        try {

            $users = $this->userRepo->getAllBranchUsers($branchId, ['id','name']);

            return response()->json([
                'users' => $users,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }

    }

    public function toggleHolidayCheckIn($id)
    {
        $this->authorize('edit_employee');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->toggleHolidayCheckIn($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.user_allow_holiday_check_in_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function logs(Request $request)
    {
        $this->authorize('list_employee');
        try {
            $bsEnabled = AppHelper::ifDateInBsEnabled();
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'date' =>  $request->date ?? ( $bsEnabled ? AppHelper::getCurrentDateInBS()  : date('Y-m-d')),
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }

            $logData = $this->userRepo->getLocationLogs($filterData);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

            return view($this->view . 'log', compact('logData', 'companyDetail', 'filterData','bsEnabled'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function liveMap(Request $request)
    {
        $this->authorize('list_employee');

        $filterData = [
            'branch_id' => $request->branch_id ?? null,
            'department_id' => $request->department_id ?? null,
            'employee_id' => $request->employee_id ?? null,
        ];

        if (!auth('admin')->check() && auth()->check()) {
            $filterData['branch_id'] = auth()->user()->branch_id;
        }

        $with = ['branches:id,name'];
        $select = ['id', 'name'];
        $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);

        return view($this->view . 'live-map', compact('companyDetail', 'filterData'));
    }

    /**
     * @throws AuthorizationException
     */
    public function liveMapLocations(Request $request): JsonResponse
    {
        $this->authorize('list_employee');

        try {
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
            ];

            if (!auth('admin')->check() && auth()->check()) {
                $filterData['branch_id'] = auth()->user()->branch_id;
            }

            $staff = User::with(['branch:id,name', 'department:id,dept_name'])
                ->select(['id', 'name', 'email', 'phone', 'avatar', 'branch_id', 'department_id', 'uuid', 'logout_status', 'online_status'])
                ->where('is_active', 1)
                ->where('status', 'verified')
                ->where(function ($query) {
                    $query->where(function ($loginQuery) {
                        $loginQuery->whereNotNull('uuid')
                            ->where('logout_status', User::LOGOUT_STATUS['approve']);
                    })->orWhere('online_status', User::ONLINE);
                })
                ->when(!empty($filterData['branch_id']), function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                })
                ->when(!empty($filterData['department_id']), function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                })
                ->when(!empty($filterData['employee_id']), function ($query) use ($filterData) {
                    $query->where('id', $filterData['employee_id']);
                })
                ->orderBy('name')
                ->get();

            $latestLocationIds = EmployeeLocation::query()
                ->selectRaw('MAX(id)')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereIn('employee_id', $staff->pluck('id'))
                ->groupBy('employee_id');

            $latestLocations = EmployeeLocation::query()
                ->whereIn('id', $latestLocationIds)
                ->get()
                ->keyBy('employee_id');

            $locations = $staff
                ->map(function ($employee) use ($latestLocations) {
                    $location = $latestLocations->get($employee->id);
                    $lastSeenAt = $location?->created_at;

                    return [
                        'employee_id' => $employee->id,
                        'name' => ucfirst($employee->name),
                        'email' => $employee->email,
                        'phone' => $employee->phone,
                        'avatar' => $employee->avatar
                            ? asset(User::AVATAR_UPLOAD_PATH . $employee->avatar)
                            : asset('assets/images/img.png'),
                        'branch' => $employee->branch?->name,
                        'department' => $employee->department?->dept_name,
                        'latitude' => $location ? (float) $location->latitude : null,
                        'longitude' => $location ? (float) $location->longitude : null,
                        'last_seen_at' => $lastSeenAt?->toIso8601String(),
                        'last_seen_human' => $lastSeenAt?->diffForHumans() ?? 'Waiting for GPS',
                        'has_location' => (bool) $location,
                        'map_url' => $location
                            ? 'https://www.google.com/maps?q=' . $location->latitude . ',' . $location->longitude
                            : null,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'updated_at' => now()->toIso8601String(),
                'total' => $locations->count(),
                'locations' => $locations,
            ]);
        } catch (Exception $exception) {
            Log::error('Unable to load live employee locations', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load live employee locations.',
            ], 500);
        }
    }



}
