<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushNotification;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\PayrollQueueSession;
use App\Models\PayrollQueueTicket;
use App\Models\User;
use App\Services\Payroll\PayrollQueueService;
use Carbon\Carbon;
use DomainException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PayrollQueueController extends Controller
{
    public function __construct(
        protected PayrollQueueService $queueService
    ) {
        $this->middleware(function (Request $request, \Closure $next) {
            if ($request->routeIs('admin.payroll-queue.*')) {
                abort_unless(Gate::any(['view_payroll_queue', 'manage_payroll_queue', 'call_payroll_queue', 'view_monthly_payroll', 'view_payroll_list']), 403);
            }

            return $next($request);
        });
    }

    /**
     * Display the main Queue & Waiting Management System dashboard.
     */
    public function index(Request $request): View
    {
        $sessionId = $request->input('session_id') ?: $request->input('sessionId');

        if ($sessionId) {
            $session = PayrollQueueSession::with(['branch', 'department', 'targetEmployee'])->findOrFail((int) $sessionId);

            $this->authorizeQueueSession($session);

            $month = $session->month;
            $branchId = $session->branch_id;
            $departmentId = $session->department_id;
            $targetEmployee = $session->targetEmployee;
            $employeeCode = $targetEmployee ? ($targetEmployee->employee_code ?: $targetEmployee->username) : '';
        } else {
            $month = $request->input('month', date('Y-m'));
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
                abort(422, 'Invalid payroll month.');
            }
            $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
            $departmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
            $employeeCode = trim((string) $request->input('employee_code', ''));
            $targetEmployee = null;

            if ($employeeCode !== '') {
                $targetEmployee = User::where('status', 'verified')->where('is_active', 1)
                    ->where(function ($query) use ($employeeCode) {
                        $query->where('employee_code', $employeeCode)->orWhere('username', $employeeCode);
                    })->first();
                if (!$targetEmployee) abort(422, 'Active employee not found. Check the employee code.');
                if ($branchId && (int) $targetEmployee->branch_id !== $branchId) abort(422, 'The employee is outside the selected branch.');
                if ($departmentId && (int) $targetEmployee->department_id !== $departmentId) abort(422, 'The employee is outside the selected department.');
                $branchId ??= $targetEmployee->branch_id ? (int) $targetEmployee->branch_id : null;
                $departmentId ??= $targetEmployee->department_id ? (int) $targetEmployee->department_id : null;
            }

            if ($departmentId) {
                $department = Department::findOrFail($departmentId);
                if ($branchId && (int) $department->branch_id !== $branchId) abort(422, 'The department is outside the selected branch.');
                $branchId ??= $department->branch_id ? (int) $department->branch_id : null;
            }

            if (!auth('admin')->check() && auth()->check() && !AppHelper::canAccessAllBranches()) {
                $allowedBranches = AppHelper::accessibleBranchIds();
                $branchId ??= $allowedBranches[0] ?? null;
                abort_unless(in_array($branchId, $allowedBranches, true), 403);
            }

            $audienceTitle = $targetEmployee
                ? ' · ' . ($targetEmployee->english_name ?: $targetEmployee->name)
                : ($departmentId ? ' · ' . Department::find($departmentId)?->dept_name : '');
            $sessionTitle = 'Payroll ' . $month . ($branchId ? ' · ' . (Branch::find($branchId)?->name ?? '') : '') . $audienceTitle;
            $maxActiveServing = $request->filled('max_active_serving') ? (int) $request->input('max_active_serving') : 5;
            $session = $this->queueService->getOrCreateSession($branchId, $month, $sessionTitle, $departmentId, $targetEmployee?->id, $maxActiveServing);
            if ($request->filled('max_active_serving') && (int) $session->max_active_serving !== $maxActiveServing) {
                $session->update(['max_active_serving' => $maxActiveServing]);
            }
            $session->loadMissing(['branch', 'department', 'targetEmployee']);
        }

        $qrSvg = $this->queueService->generateQrCode($session->qr_token, 200);
        $queueData = $this->queueService->getLiveQueueData($session);
        $inviteEmployees = $this->getInviteEmployees($session);

        $allowedBranches = AppHelper::canAccessAllBranches() ? null : AppHelper::accessibleBranchIds();
        $branches = Branch::where('is_active', 1)
            ->when($allowedBranches !== null, fn ($query) => $query->whereIn('id', $allowedBranches))
            ->get(['id', 'name']);
        $departments = Department::where('is_active', 1)
            ->when($allowedBranches !== null, fn ($query) => $query->whereIn('branch_id', $allowedBranches))
            ->orderBy('dept_name')->get(['id', 'dept_name', 'branch_id']);

        return view('admin.payroll-queue.index', array_merge($queueData, [
            'branches' => $branches,
            'departments' => $departments,
            'selectedBranchId' => $branchId,
            'selectedDepartmentId' => $departmentId,
            'selectedEmployeeCode' => $employeeCode,
            'targetEmployee' => $targetEmployee ?: $session->targetEmployee,
            'selectedMonth' => $month,
            'showQrModal' => $request->boolean('create_qr'),
            'invite_employees' => $inviteEmployees,
            'qrSvg' => $qrSvg,
            'joinUrl' => route('payroll-queue.join', ['token' => $session->qr_token]),
        ]));
    }

    /**
     * Display the directory list of all Payday Queue sessions.
     */
    public function sessions(Request $request): View
    {
        $allowedBranches = AppHelper::canAccessAllBranches() ? null : AppHelper::accessibleBranchIds();
        $branches = Branch::where('is_active', 1)
            ->when($allowedBranches !== null, fn ($query) => $query->whereIn('id', $allowedBranches))
            ->orderBy('name')->get(['id', 'name']);

        $query = PayrollQueueSession::with(['branch', 'department', 'targetEmployee', 'creator'])
            ->withCount([
                'tickets',
                'tickets as waiting_count' => fn ($q) => $q->where('status', 'waiting'),
                'tickets as serving_count' => fn ($q) => $q->whereIn('status', ['calling', 'serving']),
                'tickets as paid_count' => fn ($q) => $q->where('status', 'paid'),
            ]);

        if ($allowedBranches !== null) {
            $query->whereIn('branch_id', $allowedBranches);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->input('department_id'));
        }

        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('session_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($request->input('per_page') ?: getRecordPerPage());
        if ($perPage <= 0 || $request->input('per_page') === 'all') {
            $perPage = 500;
        }

        $sessions = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        $departments = Department::where('is_active', 1)
            ->when($allowedBranches !== null, fn ($query) => $query->whereIn('branch_id', $allowedBranches))
            ->orderBy('dept_name')
            ->get(['id', 'dept_name', 'branch_id']);

        return view('admin.payroll-queue.sessions', [
            'sessions' => $sessions,
            'branches' => $branches,
            'departments' => $departments,
            'filterParameters' => $request->all(),
            'canSelectAllBranches' => $allowedBranches === null,
            'defaultPageSize' => (int) getRecordPerPage(),
        ]);
    }

    /**
     * Store / Create a new Payday Queue session.
     */
    public function storeSession(Request $request)
    {
        $validated = $request->validateWithBag('createPaydayQueue', [
            'month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'title' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id,is_active,1'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id,is_active,1'],
            'employee_code' => ['nullable', 'string'],
            'max_active_serving' => ['nullable', 'integer', 'min:1', 'max:10'],
            'status' => ['nullable', 'string', 'in:open,paused,closed'],
        ]);

        $branchId = !empty($validated['branch_id']) ? (int) $validated['branch_id'] : null;
        $departmentId = !empty($validated['department_id']) ? (int) $validated['department_id'] : null;
        $targetEmployeeId = null;

        $employeeCode = trim($validated['employee_code'] ?? '');
        if ($employeeCode !== '') {
            $employee = User::where('status', 'verified')->where('is_active', 1)->where(function ($q) use ($employeeCode) {
                $q->where('employee_code', $employeeCode)->orWhere('username', $employeeCode);
            })->first();

            if (!$employee) {
                throw ValidationException::withMessages([
                    'employee_code' => 'No active employee was found with that code.',
                ])->errorBag('createPaydayQueue');
            }
            if (($branchId && (int) $employee->branch_id !== $branchId)
                || ($departmentId && (int) $employee->department_id !== $departmentId)) {
                throw ValidationException::withMessages([
                    'employee_code' => 'The employee is outside the selected branch or department.',
                ])->errorBag('createPaydayQueue');
            }
            $targetEmployeeId = $employee->id;
            $branchId = $branchId ?: ($employee->branch_id ? (int) $employee->branch_id : null);
            $departmentId = $departmentId ?: ($employee->department_id ? (int) $employee->department_id : null);
        }

        if ($departmentId) {
            $departmentBranchId = Department::findOrFail($departmentId)->branch_id;
            if ($branchId && (int) $departmentBranchId !== $branchId) {
                throw ValidationException::withMessages([
                    'department_id' => 'The department is outside the selected branch.',
                ])->errorBag('createPaydayQueue');
            }
            $branchId ??= $departmentBranchId;
        }

        if (!AppHelper::canAccessAllBranches()) {
            $allowedBranches = AppHelper::accessibleBranchIds();
            $branchId ??= $allowedBranches[0] ?? null;
            abort_unless(in_array((int) $branchId, $allowedBranches, true), 403);
        }

        $branch = $branchId ? Branch::find($branchId) : null;
        $companyId = $branch ? $branch->company_id : (Auth::user()?->company_id ?? 1);
        $branchCode = $branch ? 'B' . $branch->id : 'ALL';
        $monthClean = str_replace('-', '', $validated['month']);

        $defaultTitle = 'បើកប្រាក់ខែប្រចាំខែ ' . Carbon::createFromFormat('!Y-m', $validated['month'])->translatedFormat('F Y') . ($branch ? ' (' . $branch->name . ')' : '');
        $title = $validated['title'] ?? $defaultTitle;

        $session = PayrollQueueSession::create([
            'session_code' => 'PQS-' . $monthClean . '-' . $branchCode . '-' . strtoupper(Str::random(4)),
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'department_id' => $departmentId,
            'target_employee_id' => $targetEmployeeId,
            'month' => $validated['month'],
            'title' => $title,
            'qr_token' => Str::random(32),
            'status' => $validated['status'] ?? 'open',
            'max_active_serving' => $validated['max_active_serving'] ?? 5,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('index.create_session_success'),
                'data' => $session,
                'redirect_url' => route('admin.payroll-queue.index', ['sessionId' => $session->id]),
            ]);
        }

        return redirect()->route('admin.payroll-queue.index', ['sessionId' => $session->id])
            ->with('success', __('index.create_session_success'));
    }

    /**
     * Show session details (JSON for inspection modal or redirect to live dashboard).
     */
    public function showSession(int $sessionId, Request $request)
    {
        $session = PayrollQueueSession::with(['branch', 'department', 'targetEmployee', 'creator'])
            ->withCount([
                'tickets',
                'tickets as waiting_count' => fn ($q) => $q->where('status', 'waiting'),
                'tickets as serving_count' => fn ($q) => $q->whereIn('status', ['calling', 'serving']),
                'tickets as paid_count' => fn ($q) => $q->where('status', 'paid'),
            ])
            ->findOrFail($sessionId);

        $this->authorizeQueueSession($session);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'session' => $session,
                'qr_svg' => $this->queueService->generateQrCode($session->qr_token, 200),
                'join_url' => route('payroll-queue.join', ['token' => $session->qr_token]),
                'dashboard_url' => route('admin.payroll-queue.index', ['sessionId' => $session->id]),
                'display_url' => route('admin.payroll-queue.display', ['sessionId' => $session->id]),
            ]);
        }

        return redirect()->route('admin.payroll-queue.index', ['sessionId' => $session->id]);
    }

    /**
     * Delete a Payday Queue session and its tickets/invites.
     */
    public function destroySession(int $sessionId, Request $request)
    {
        $session = PayrollQueueSession::findOrFail($sessionId);

        $this->authorizeQueueSession($session);

        DB::transaction(function () use ($session) {
            if (Schema::hasTable('payroll_queue_invites')) {
                DB::table('payroll_queue_invites')->where('session_id', $session->id)->delete();
            }
            $session->tickets()->delete();
            $session->delete();
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('index.delete_session_success'),
            ]);
        }

        return redirect()->route('admin.payroll-queue.sessions.index')
            ->with('success', __('index.delete_session_success'));
    }

    /**
     * Update active serving counters count for the session (flexible: 1 or more than 1).
     */
    public function updateCounters(Request $request, int $sessionId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $this->authorizeQueueSession($session);

        $validated = $request->validate([
            'max_active_serving' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $newCount = (int) $validated['max_active_serving'];

        // Check if any tickets are currently calling/serving at a counter number > newCount
        $activeTicketsOutsideNewLimit = PayrollQueueTicket::where('session_id', $session->id)
            ->whereIn('status', ['calling', 'serving'])
            ->where('counter_number', '>', $newCount)
            ->get();

        if ($activeTicketsOutsideNewLimit->isNotEmpty()) {
            $busyCounters = $activeTicketsOutsideNewLimit->pluck('counter_number')->unique()->sort()->implode(', ');
            return response()->json([
                'success' => false,
                'message' => __('index.cannot_reduce_counters_busy', ['counters' => $busyCounters]),
            ], 422);
        }

        $session->update([
            'max_active_serving' => $newCount,
            'updated_by' => Auth::id(),
        ]);

        $queueData = $this->queueService->getLiveQueueData($session);

        $countersHtml = view('admin.payroll-queue.partials.serving-counters', [
            'counters' => $queueData['counters'],
            'session' => $session,
        ])->render();

        $panelsHtml = view('admin.payroll-queue.partials.waiting-paid-panels', [
            'waiting_tickets' => $queueData['waiting_tickets'],
            'paid_tickets' => $queueData['paid_tickets'],
            'stats' => $queueData['stats'],
            'session' => $session,
        ])->render();

        return response()->json([
            'success' => true,
            'message' => __('index.update_counters_success', ['count' => $newCount]),
            'max_active_serving' => $newCount,
            'counters_html' => $countersHtml,
            'panels_html' => $panelsHtml,
            'stats' => $queueData['stats'],
            'counters' => array_values($queueData['counters']),
        ]);
    }

    /**
     * Real-time polling API for the two panels and TV display.
     */
    public function liveStatus(int $sessionId): JsonResponse
    {
        $session = PayrollQueueSession::with(['branch', 'department'])->findOrFail($sessionId);
        $this->authorizeQueueSession($session);
        $queueData = $this->queueService->getLiveQueueData($session);
        $allEmployees = $this->getInviteEmployees($session);
        $pendingEmployees = $allEmployees->filter(fn ($e) => in_array($e->queue_display_status, ['pending', 'invited']))->values();

        $inRoomTickets = PayrollQueueTicket::with([
                'employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
            ])
            ->where('session_id', $session->id)
            ->whereIn('status', ['calling', 'serving'])
            ->orderBy('counter_number', 'asc')
            ->get();

        $callingTicket = $inRoomTickets->firstWhere('status', 'calling');

        // Format HTML partials for admin dashboard
        $countersHtml = view('admin.payroll-queue.partials.serving-counters', [
            'counters' => $queueData['counters'],
            'session' => $session,
        ])->render();

        $panelsHtml = view('admin.payroll-queue.partials.waiting-paid-panels', [
            'waiting_tickets' => $queueData['waiting_tickets'],
            'paid_tickets' => $queueData['paid_tickets'],
            'stats' => $queueData['stats'],
            'session' => $session,
        ])->render();
        $inviteEmployeesHtml = view('admin.payroll-queue.partials.invite-status-list', [
            'invite_employees' => $allEmployees,
            'session' => $session,
        ])->render();

        return response()->json([
            'success' => true,
            'counters_html' => $countersHtml,
            'panels_html' => $panelsHtml,
            'invite_employees_html' => $inviteEmployeesHtml,
            'stats' => $queueData['stats'],
            'counters' => array_values($queueData['counters']),
            'waiting_tickets' => $queueData['waiting_tickets']->map(fn ($t) => [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'status' => $t->status,
                'joined_at' => $t->joined_at ? $t->joined_at->format('H:i') : null,
                'employee_name' => $t->employee?->english_name ?: ($t->employee?->name ?: 'Staff'),
                'employee_avatar' => $t->employee?->avatar_url ?: asset('assets/images/img.png'),
                'department_name' => $t->employee?->department?->dept_name ?: ($t->employee?->branch?->name ?: 'Staff'),
                'code' => $t->employee?->employee_code ?: $t->employee?->username,
            ]),
            'in_room_tickets' => $inRoomTickets->map(fn ($t) => [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'counter_number' => $t->counter_number,
                'status' => $t->status,
                'employee_name' => $t->employee?->english_name ?: ($t->employee?->name ?: 'Staff'),
                'employee_avatar' => $t->employee?->avatar_url ?: asset('assets/images/img.png'),
                'department_name' => $t->employee?->department?->dept_name ?: ($t->employee?->branch?->name ?: 'Staff'),
                'called_at' => $t->called_at ? $t->called_at->format('H:i') : null,
                'call_timestamp' => $t->called_at?->getTimestamp(),
                'is_calling' => $t->status === 'calling',
            ]),
            'done_tickets' => $queueData['paid_tickets']->take(25)->map(fn ($t) => [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'counter_number' => $t->counter_number,
                'employee_name' => $t->employee?->english_name ?: ($t->employee?->name ?: 'Staff'),
                'employee_avatar' => $t->employee?->avatar_url ?: asset('assets/images/img.png'),
                'department_name' => $t->employee?->department?->dept_name ?: ($t->employee?->branch?->name ?: 'Staff'),
                'paid_at' => $t->paid_at ? $t->paid_at->format('H:i') : null,
            ]),
            'pending_employees' => $pendingEmployees->take(25)->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->english_name ?: $e->name,
                'avatar' => $e->avatar_url ?: asset('assets/images/img.png'),
                'department' => $e->department?->dept_name ?: ($e->branch?->name ?: 'Staff'),
                'code' => $e->employee_code ?: $e->username,
                'status' => $e->queue_display_status,
            ]),
            'calling_ticket' => $callingTicket ? [
                'ticket_number' => $callingTicket->ticket_number,
                'counter_number' => $callingTicket->counter_number,
                'employee_name' => $callingTicket->employee?->english_name ?: $callingTicket->employee?->name,
            ] : null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Call a waiting ticket to a session counter.
     */
    public function callNext(Request $request, int $sessionId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $this->authorizeQueueSession($session);
        $data = $request->validate([
            'counter_number' => ['sometimes', 'required', 'integer', 'min:1'],
            'ticket_id' => ['sometimes', 'required', 'integer', 'min:1'],
        ]);
        $counterNumber = (int) ($data['counter_number'] ?? 1);

        try {
            $ticket = $this->queueService->callNext($session, $counterNumber, isset($data['ticket_id']) ? (int) $data['ticket_id'] : null);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'គ្មានបុគ្គលិកកំពុងរង់ចាំក្នុងជួរទេ!',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'បានហៅសំបុត្រ ' . $ticket->ticket_number . ' មកកាន់តុទី ' . $counterNumber,
            'ticket' => $ticket,
        ]);
    }

    /**
     * Recall ticket (sound alert chime).
     */
    public function recallTicket(int $ticketId): JsonResponse
    {
        $ticket = $this->findOperatorTicket($ticketId);
        try {
            $updated = $this->queueService->recallTicket($ticket);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'បានហៅម្តងទៀតនូវសំបុត្រ ' . $updated->ticket_number,
            'ticket' => $updated,
            'play_sound' => true,
        ]);
    }

    /**
     * Skip ticket from counter.
     */
    public function skipTicket(int $ticketId): JsonResponse
    {
        $ticket = $this->findOperatorTicket($ticketId);
        if (!$this->queueService->skipTicket($ticket)) {
            return response()->json(['success' => false, 'message' => 'This ticket is no longer active at a payroll counter.'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'បានរំលងសំបុត្រ ' . $ticket->ticket_number,
        ]);
    }

    /**
     * Get detailed salary breakdown for confirmation modal.
     */
    public function getPayslipBreakdown(int $ticketId): JsonResponse
    {
        $ticket = $this->findOperatorTicket($ticketId);
        try {
            $breakdown = DB::transaction(function () use ($ticket) {
                $ticket = $this->queueService->startPayrollReview($ticket);
                $breakdown = $this->queueService->getPayslipBreakdown($ticket);
                $breakdown['can_quick_update'] = $breakdown['can_quick_update'] && Gate::allows('edit_payroll');

                return $breakdown;
            });

            return response()->json([
                'success' => true,
                'data' => $breakdown,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /** Quick incentive correction while a cashier is reviewing an active, unsaved draft payslip. */
    public function quickUpdatePayroll(Request $request, int $ticketId): JsonResponse
    {
        $this->authorize('edit_payroll');
        $data = $request->validate([
            'bonus' => ['required', 'numeric', 'min:0', 'max:999999999', 'decimal:0,2'],
            'expected_bonus' => ['required', 'numeric'],
            'expected_net' => ['required', 'numeric'],
        ]);

        $ticket = $this->findOperatorTicket($ticketId);
        try {
            $this->queueService->quickUpdateBonus($ticket, (float) $data['bonus'], (float) $data['expected_bonus'], (float) $data['expected_net']);
            $breakdown = $this->queueService->getPayslipBreakdown($ticket->fresh());
            return response()->json(['success' => true, 'data' => $breakdown]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Disburse salary to employee.
     */
    public function disburseSalary(Request $request, int $ticketId): JsonResponse
    {
        $this->authorize('payroll_payment');
        $request->validate([
            'payment_method_id' => 'required|integer|exists:payment_methods,id,status,1',
            'paid_on' => 'required|date',
            'remarks' => 'nullable|string|max:500',
            'expected_net' => 'sometimes|required|numeric',
        ]);

        $ticket = $this->findOperatorTicket($ticketId);
        try {
            $result = $this->queueService->disburseSalary($ticket, $request->only([
                'payment_method_id',
                'paid_on',
                'remarks',
                'expected_net',
            ]));

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Direct print receipt/slip in Khmer.
     */
    public function printSlip(int $ticketId): View
    {
        $ticket = $this->findOperatorTicket($ticketId);
        abort_unless($ticket->status === 'paid', 404);
        $ticket->load(['employee.branch', 'employee.department', 'payslip', 'paymentMethod', 'disbursedBy']);

        $breakdown = null;
        try {
            $breakdown = $this->queueService->getPayslipBreakdown($ticket);
        } catch (Exception $e) {
            // fallback
        }

        return view('admin.payroll-queue.print-slip', compact('ticket', 'breakdown'));
    }

    /**
     * Fullscreen TV Kiosk Display mode for waiting area monitor (TV, Desktop & iPad).
     */
    public function display(int $sessionId): View
    {
        $session = PayrollQueueSession::with(['branch', 'department'])->findOrFail($sessionId);
        $this->authorizeQueueSession($session);
        $queueData = $this->queueService->getLiveQueueData($session);
        $qrSvg = $this->queueService->generateQrCode($session->qr_token, 160);
        $allEmployees = $this->getInviteEmployees($session);
        $pendingEmployees = $allEmployees->filter(fn ($e) => in_array($e->queue_display_status, ['pending', 'invited']))->values();

        $inRoomTickets = PayrollQueueTicket::with([
                'employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
            ])
            ->where('session_id', $session->id)
            ->whereIn('status', ['calling', 'serving'])
            ->orderBy('counter_number', 'asc')
            ->get();

        return view('admin.payroll-queue.display', array_merge($queueData, [
            'session' => $session,
            'qrSvg' => $qrSvg,
            'joinUrl' => route('payroll-queue.join', ['token' => $session->qr_token]),
            'pending_employees' => $pendingEmployees,
            'in_room_tickets' => $inRoomTickets,
            'done_tickets' => $queueData['paid_tickets'],
        ]));
    }

    /**
     * 16:9 Widescreen Reception TV Monitor Display.
     */
    public function receptionMonitor(): View
    {
        return view('admin.payroll-queue.reception-monitor');
    }

    /** Employees in this payroll scope, with invite and queue state for the dashboard. */
    private function getInviteEmployees(PayrollQueueSession $session)
    {
        $employeeQuery = User::with(['branch', 'department'])
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->when($session->branch_id, fn ($query) => $query->where('branch_id', $session->branch_id))
            ->when($session->department_id, fn ($query) => $query->where('department_id', $session->department_id))
            ->when($session->target_employee_id, fn ($query) => $query->whereKey($session->target_employee_id));
        if (!$session->branch_id && !AppHelper::canAccessAllBranches()) {
            $accessibleBranches = AppHelper::accessibleBranchIds();
            $employeeQuery->whereIn('branch_id', $accessibleBranches ?: [-1]);
        }
        if (!Schema::hasTable('payroll_queue_invites')) {
            return $this->decorateSessionEmployees($employeeQuery->orderBy('name')->get(), $session);
        }

        $rosterQuery = DB::table('payroll_queue_invites')->where('session_id', $session->id);
        if (!$rosterQuery->exists()) {
            $now = now();
            $seedEmployees = $employeeQuery->get(['users.id']);
            if ($seedEmployees->isNotEmpty()) {
                DB::table('payroll_queue_invites')->insertOrIgnore($seedEmployees->map(fn ($employee) => [
                    'session_id' => $session->id,
                    'employee_id' => $employee->id,
                    'status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            }
        }

        $roster = DB::table('payroll_queue_invites')->where('session_id', $session->id)
            ->where('status', '!=', 'removed')->get()->keyBy('employee_id');
        $employees = User::with(['branch', 'department'])->where('status', 'verified')->where('is_active', 1)
            ->whereIn('id', $roster->keys())->orderBy('name')->get();
        return $this->decorateSessionEmployees($employees, $session, $roster);
    }

    private function decorateSessionEmployees($employees, PayrollQueueSession $session, $roster = null)
    {
        $tickets = PayrollQueueTicket::where('session_id', $session->id)->get()->keyBy('employee_id');
        $roster ??= Schema::hasTable('payroll_queue_invites')
            ? DB::table('payroll_queue_invites')->where('session_id', $session->id)->get()->keyBy('employee_id')
            : collect();

        return $employees->map(function (User $employee) use ($tickets, $roster) {
            $ticket = $tickets->get($employee->id);
            $invite = $roster->get($employee->id);
            $employee->setAttribute('queue_ticket', $ticket);
            $employee->setAttribute('invite_sent_at', $invite?->invited_at);
            $employee->setAttribute('queue_display_status', match ($ticket?->status) {
                'waiting' => 'waiting',
                'calling', 'serving' => 'in_process',
                'paid' => 'paid',
                'skipped', 'cancelled' => 'needs_attention',
                default => $invite?->status === 'invited' ? 'invited' : 'pending',
            });
            return $employee;
        });
    }

    private function authorizeQueueSession(PayrollQueueSession $session): void
    {
        if ($session->branch_id) {
            abort_unless(AppHelper::canAccessBranch($session->branch_id), 403);
        } else {
            abort_unless(AppHelper::canAccessAllBranches(), 403);
        }
    }

    private function findOperatorTicket(int $ticketId): PayrollQueueTicket
    {
        $ticket = PayrollQueueTicket::with(['session', 'employee' => fn ($query) => $query->withTrashed()])
            ->findOrFail($ticketId);
        $this->authorizeQueueSession($ticket->session);
        abort_unless(AppHelper::canAccessBranch($ticket->employee?->branch_id), 403);

        return $ticket;
    }

    public function addSessionEmployee(Request $request, int $sessionId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $this->authorizeQueueSession($session);
        abort_unless($session->status === 'open', 422, 'This payroll queue session is closed.');
        abort_unless(Schema::hasTable('payroll_queue_invites'), 503, 'Run migrations before managing this payroll session roster.');
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:users,id']]);
        $employee = User::withoutGlobalScope('branch')->where('status', 'verified')->where('is_active', 1)->findOrFail($data['employee_id']);
        if (!AppHelper::canAccessAllBranches()) {
            abort_unless(in_array((int) $employee->branch_id, AppHelper::accessibleBranchIds(), true), 403);
        }
        if (PayrollQueueTicket::where('session_id', $session->id)->where('employee_id', $employee->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'This employee already has a queue ticket and cannot be re-added.'], 422);
        }

        DB::table('payroll_queue_invites')->updateOrInsert(
            ['session_id' => $session->id, 'employee_id' => $employee->id],
            ['invited_at' => null, 'status' => 'pending', 'removed_at' => null, 'updated_at' => now(), 'created_at' => now()]
        );
        return response()->json(['success' => true, 'message' => 'Employee added to this payroll session.']);
    }

    public function updateSessionEmployee(Request $request, int $sessionId, int $employeeId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $this->authorizeQueueSession($session);
        abort_unless($session->status === 'open', 422, 'This payroll queue session is closed.');
        abort_unless(Schema::hasTable('payroll_queue_invites'), 503, 'Run migrations before managing this payroll session roster.');
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:users,id']]);
        $rosterEntry = DB::table('payroll_queue_invites')->where('session_id', $session->id)->where('employee_id', $employeeId)->where('status', '!=', 'removed')->first();
        abort_unless($rosterEntry, 404);
        abort_if(PayrollQueueTicket::where('session_id', $session->id)->where('employee_id', $employeeId)->exists(), 422, 'An employee who has scanned cannot be changed.');

        $replacement = User::withoutGlobalScope('branch')->where('status', 'verified')->where('is_active', 1)->findOrFail($data['employee_id']);
        if (!AppHelper::canAccessAllBranches()) {
            abort_unless(in_array((int) $replacement->branch_id, AppHelper::accessibleBranchIds(), true), 403);
        }
        abort_if(DB::table('payroll_queue_invites')->where('session_id', $session->id)->where('employee_id', $replacement->id)->where('status', '!=', 'removed')->exists(), 422, 'This employee is already on the session list.');

        DB::transaction(function () use ($session, $employeeId, $replacement) {
            DB::table('payroll_queue_invites')->where('session_id', $session->id)->where('employee_id', $employeeId)->delete();
            DB::table('payroll_queue_invites')->updateOrInsert(
                ['session_id' => $session->id, 'employee_id' => $replacement->id],
                ['invited_at' => null, 'status' => 'pending', 'removed_at' => null, 'updated_at' => now(), 'created_at' => now()]
            );
        });
        return response()->json(['success' => true, 'message' => 'Payroll session employee updated.']);
    }

    public function removeSessionEmployee(int $sessionId, int $employeeId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $this->authorizeQueueSession($session);
        abort_unless($session->status === 'open', 422, 'This payroll queue session is closed.');
        abort_unless(Schema::hasTable('payroll_queue_invites'), 503, 'Run migrations before managing this payroll session roster.');
        abort_if(PayrollQueueTicket::where('session_id', $session->id)->where('employee_id', $employeeId)->exists(), 422, 'An employee who has scanned cannot be removed from the payroll session.');
        $removed = DB::table('payroll_queue_invites')->where('session_id', $session->id)->where('employee_id', $employeeId)->where('status', '!=', 'removed')
            ->update(['status' => 'removed', 'removed_at' => now(), 'updated_at' => now()]);
        return response()->json(['success' => $removed > 0, 'message' => $removed ? 'Employee removed from this payroll session.' : 'Employee is not on this session list.']);
    }

    /** Send the selected payroll queue invitation to the session audience. */
    public function sendInvites(int $sessionId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        abort_unless($session->status === 'open', 422, 'This payroll queue session is closed.');
        $this->authorizeQueueSession($session);

        $recipientQuery = User::query()->where('status', 'verified')->where('is_active', 1)->whereNotNull('fcm_token');
        $eligibleIds = $this->getInviteEmployees($session)
            ->filter(fn ($employee) => in_array($employee->queue_display_status, ['pending', 'invited'], true))
            ->pluck('id');
        $recipients = $recipientQuery->whereIn('id', $eligibleIds)->pluck('fcm_token', 'id')->all();

        if (!$recipients) {
            return response()->json(['success' => false, 'message' => 'No active employees in this scope have a registered app device.'], 422);
        }

        $result = SMPushNotification::smSend(
            title: 'Payroll queue is open',
            message: "Scan to join the {$session->month} payroll queue.",
            data: [
                'type' => 'payroll_queue_invite',
                'session_id' => (string) $session->id,
                'qr_token' => $session->qr_token,
                'join_url' => route('payroll-queue.join', ['token' => $session->qr_token]),
            ],
            recipients: $recipients,
            highPriority: true,
        );

        $acceptedIds = $result['accepted_recipient_ids'] ?? [];
        if ($acceptedIds && Schema::hasTable('payroll_queue_invites')) {
            $invitedAt = now();
            DB::table('payroll_queue_invites')->where('session_id', $session->id)
                ->whereIn('employee_id', $acceptedIds)->whereIn('status', ['pending', 'invited'])
                ->update(['status' => 'invited', 'invited_at' => $invitedAt, 'updated_at' => $invitedAt]);
        }

        $sent = $result['sent'] ?? 0;
        $failed = $result['failed'] ?? count($recipients);
        $message = $sent > 0
            ? "{$sent} employee app(s) notified." . ($failed ? " {$failed} failed." : '')
            : ($result['error'] ?? 'The invitation could not be sent.');

        return response()->json(['success' => $sent > 0, 'sent' => $sent, 'failed' => $failed, 'message' => $message]);
    }

    /**
     * Public page for employee to scan QR and join queue.
     */
    public function joinQueue(string $token): View
    {
        $session = PayrollQueueSession::where('qr_token', $token)->firstOrFail();
        $user = Auth::user();
        if (!$user) abort(401, 'Sign in to the HR app before joining this payroll queue.');

        return view('admin.payroll-queue.join', compact('session', 'user'));
    }

    /**
     * Handle submission of queue join.
     */
    public function submitJoinQueue(Request $request, string $token): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Sign in to the HR app before joining this payroll queue.'], 401);
        }

        try {
            $result = $this->queueService->joinQueue($token, (int) Auth::id());
            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'message' => $result['message'],
                'ticket' => $result['ticket'],
                'redirect' => $result['ticket'] ? route('payroll-queue.my-ticket', ['ticketId' => $result['ticket']->id]) : null,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Live mobile ticket status for employee.
     */
    public function myTicket(int $ticketId): View
    {
        $ticket = PayrollQueueTicket::with(['session', 'employee.branch', 'employee.department'])
            ->findOrFail($ticketId);
        abort_unless(Auth::check() && (int) Auth::id() === (int) $ticket->employee_id, 403);

        $aheadCount = $ticket->peopleAhead();

        return view('admin.payroll-queue.my-ticket', compact('ticket', 'aheadCount'));
    }

    /**
     * JSON status of employee's ticket for live polling on mobile.
     */
    public function ticketStatus(int $ticketId): JsonResponse
    {
        $ticket = PayrollQueueTicket::findOrFail($ticketId);
        abort_unless(Auth::check() && (int) Auth::id() === (int) $ticket->employee_id, 403);
        return response()->json([
            'success' => true,
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'counter_number' => $ticket->counter_number,
            'ahead_count' => $ticket->peopleAhead(),
            'called_at' => $ticket->called_at ? $ticket->called_at->format('h:i A') : null,
        ]);
    }
}
