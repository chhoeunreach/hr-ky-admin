<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PayrollQueueSession;
use App\Models\PayrollQueueTicket;
use App\Models\User;
use App\Services\Payroll\PayrollQueueService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollQueueController extends Controller
{
    public function __construct(
        protected PayrollQueueService $queueService
    ) {}

    /**
     * Display the main Queue & Waiting Management System dashboard.
     */
    public function index(Request $request): View
    {
        $month = $request->input('month', date('Y-m'));
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;

        if (!auth('admin')->check() && auth()->check() && !AppHelper::canAccessAllBranches()) {
            $branchId = AppHelper::employeeBranchFilter($request->input('branch_id'));
        }

        $session = $this->queueService->getOrCreateSession($branchId, $month);
        $qrSvg = $this->queueService->generateQrCode($session->qr_token, 200);
        $queueData = $this->queueService->getLiveQueueData($session);

        $branches = Branch::where('is_active', 1)->get(['id', 'name']);

        return view('admin.payroll-queue.index', array_merge($queueData, [
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'selectedMonth' => $month,
            'qrSvg' => $qrSvg,
            'joinUrl' => route('payroll-queue.join', ['token' => $session->qr_token]),
        ]));
    }

    /**
     * Real-time polling API for the two panels.
     */
    public function liveStatus(int $sessionId): JsonResponse
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $queueData = $this->queueService->getLiveQueueData($session);

        // Format HTML partials or return structured JSON
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
            'counters_html' => $countersHtml,
            'panels_html' => $panelsHtml,
            'stats' => $queueData['stats'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Call next waiting ticket to counter 1..5.
     */
    public function callNext(Request $request, int $sessionId): JsonResponse
    {
        $counterNumber = (int) $request->input('counter_number', 1);
        if ($counterNumber < 1 || $counterNumber > 5) {
            return response()->json(['success' => false, 'message' => 'លេខតុមិនត្រឹមត្រូវ!'], 400);
        }

        $session = PayrollQueueSession::findOrFail($sessionId);
        $ticket = $this->queueService->callNext($session, $counterNumber);

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
        $ticket = PayrollQueueTicket::findOrFail($ticketId);
        $updated = $this->queueService->recallTicket($ticket);

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
        $ticket = PayrollQueueTicket::findOrFail($ticketId);
        $this->queueService->skipTicket($ticket);

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
        try {
            $ticket = PayrollQueueTicket::findOrFail($ticketId);
            $breakdown = $this->queueService->getPayslipBreakdown($ticket);

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

    /**
     * Disburse salary to employee.
     */
    public function disburseSalary(Request $request, int $ticketId): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'required',
            'paid_on' => 'required|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $ticket = PayrollQueueTicket::findOrFail($ticketId);
            $result = $this->queueService->disburseSalary($ticket, $request->only([
                'payment_method_id',
                'paid_on',
                'remarks',
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
        $ticket = PayrollQueueTicket::with([
            'session',
            'employee.branch',
            'employee.department',
            'payslip',
            'paymentMethod',
            'disbursedBy',
        ])->findOrFail($ticketId);

        $breakdown = null;
        try {
            $breakdown = $this->queueService->getPayslipBreakdown($ticket);
        } catch (Exception $e) {
            // fallback
        }

        return view('admin.payroll-queue.print-slip', compact('ticket', 'breakdown'));
    }

    /**
     * Fullscreen TV Kiosk Display mode for waiting area monitor.
     */
    public function display(int $sessionId): View
    {
        $session = PayrollQueueSession::findOrFail($sessionId);
        $queueData = $this->queueService->getLiveQueueData($session);
        $qrSvg = $this->queueService->generateQrCode($session->qr_token, 180);

        return view('admin.payroll-queue.display', array_merge($queueData, [
            'qrSvg' => $qrSvg,
            'joinUrl' => route('payroll-queue.join', ['token' => $session->qr_token]),
        ]));
    }

    /**
     * 16:9 Widescreen Reception TV Monitor Display.
     */
    public function receptionMonitor(): View
    {
        return view('admin.payroll-queue.reception-monitor');
    }

    /**
     * Public page for employee to scan QR and join queue.
     */
    public function joinQueue(string $token): View
    {
        $session = PayrollQueueSession::where('qr_token', $token)->firstOrFail();
        $user = Auth::user();

        return view('admin.payroll-queue.join', compact('session', 'user'));
    }

    /**
     * Handle submission of queue join.
     */
    public function submitJoinQueue(Request $request, string $token): JsonResponse
    {
        try {
            $session = PayrollQueueSession::where('qr_token', $token)->firstOrFail();

            $employeeId = null;
            if (Auth::check()) {
                $employeeId = Auth::id();
            } else {
                $request->validate([
                    'identifier' => 'required|string',
                ]);
                $term = trim($request->input('identifier'));
                $user = User::where('employee_code', $term)
                    ->orWhere('username', $term)
                    ->orWhere('phone', $term)
                    ->first();

                if (!$user) {
                    throw new Exception('រកមិនឃើញបុគ្គលិកដែលមានលេខកូដ ឬឈ្មោះនេះទេ!');
                }
                $employeeId = $user->id;
            }

            $result = $this->queueService->joinQueue($token, $employeeId);

            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'message' => $result['message'],
                'ticket' => $result['ticket'],
                'redirect' => $result['ticket'] ? route('payroll-queue.my-ticket', ['ticketId' => $result['ticket']->id]) : null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Live mobile ticket status for employee.
     */
    public function myTicket(int $ticketId): View
    {
        $ticket = PayrollQueueTicket::with(['session', 'employee.branch', 'employee.department'])
            ->findOrFail($ticketId);

        $aheadCount = PayrollQueueTicket::where('session_id', $ticket->session_id)
            ->where('status', 'waiting')
            ->where('joined_at', '<', $ticket->joined_at)
            ->count();

        return view('admin.payroll-queue.my-ticket', compact('ticket', 'aheadCount'));
    }

    /**
     * JSON status of employee's ticket for live polling on mobile.
     */
    public function ticketStatus(int $ticketId): JsonResponse
    {
        $ticket = PayrollQueueTicket::findOrFail($ticketId);
        $aheadCount = PayrollQueueTicket::where('session_id', $ticket->session_id)
            ->where('status', 'waiting')
            ->where('joined_at', '<', $ticket->joined_at)
            ->count();

        return response()->json([
            'success' => true,
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'counter_number' => $ticket->counter_number,
            'ahead_count' => $aheadCount,
            'called_at' => $ticket->called_at ? $ticket->called_at->format('h:i A') : null,
        ]);
    }
}
