<?php

namespace App\Services\Payroll;

use App\Enum\PayslipStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Models\Branch;
use App\Models\EmployeePayslip;
use App\Models\PaymentMethod;
use App\Models\PayrollQueueSession;
use App\Models\PayrollQueueTicket;
use App\Models\SellOutReport;
use App\Models\User;
use App\Services\Tada\TadaService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PayrollQueueService
{
    public function __construct(
        protected AdvanceSalaryService $advanceSalaryService,
        protected TadaService $tadaService,
        protected MonthlyPayrollLoanService $loanService,
        protected MonthlyPayrollReportService $reportService
    ) {}

    /**
     * Get or create active payroll queue session for branch & month.
     */
    public function getOrCreateSession(?int $branchId, string $month, ?string $title = null): PayrollQueueSession
    {
        $query = PayrollQueueSession::where('month', $month)
            ->where('status', 'open');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        $session = $query->first();

        if (!$session) {
            $branch = $branchId ? Branch::find($branchId) : null;
            $companyId = $branch ? $branch->company_id : (Auth::user()?->company_id ?? 1);
            $branchCode = $branch ? 'B' . $branch->id : 'ALL';
            $monthClean = str_replace('-', '', $month);

            $defaultTitle = 'បើកប្រាក់ខែប្រចាំខែ ' . Carbon::createFromFormat('!Y-m', $month)->translatedFormat('F Y') . ($branch ? ' (' . $branch->name . ')' : '');

            $session = PayrollQueueSession::create([
                'session_code' => 'PQS-' . $monthClean . '-' . $branchCode . '-' . strtoupper(Str::random(4)),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'month' => $month,
                'title' => $title ?: $defaultTitle,
                'qr_token' => Str::random(32),
                'status' => 'open',
                'max_active_serving' => 5,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        }

        return $session;
    }

    /**
     * Generate SVG QR code for the session join URL.
     */
    public function generateQrCode(string $qrToken, int $size = 240): string
    {
        $url = route('payroll-queue.join', ['token' => $qrToken]);
        return (string) QrCode::size($size)->generate($url);
    }

    /**
     * Join queue by QR token and employee ID or employee code.
     */
    public function joinQueue(string $qrToken, int $employeeId): array
    {
        $session = PayrollQueueSession::where('qr_token', $qrToken)->first();
        if (!$session) {
            throw new Exception(__('index.payroll_queue_session_not_found') ?: 'មិនមានវគ្គបើកប្រាក់ខែនេះទេ!');
        }

        if ($session->status !== 'open') {
            throw new Exception(__('index.payroll_queue_session_closed') ?: 'វគ្គបើកប្រាក់ខែនេះត្រូវបានបិទ ឬផ្អាកហើយ!');
        }

        $employee = User::with(['branch', 'department'])->find($employeeId);
        if (!$employee) {
            throw new Exception(__('index.employee_not_found') ?: 'រកមិនឃើញទិន្នន័យបុគ្គលិកនេះទេ!');
        }

        if ($session->branch_id && (int) $employee->branch_id !== (int) $session->branch_id) {
            throw new Exception(__('index.payroll_queue_wrong_branch') ?: 'អ្នកមិនស្ថិតនៅក្នុងសាខាដែលត្រូវបើកប្រាក់ខែនៃវគ្គនេះទេ!');
        }

        // Check if ticket already exists for this session
        $existingTicket = PayrollQueueTicket::where('session_id', $session->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existingTicket) {
            if ($existingTicket->status === 'paid') {
                return [
                    'status' => 'already_paid',
                    'message' => 'អ្នកបានបើកប្រាក់ខែជោគជ័យរួចរាល់ហើយ!',
                    'ticket' => $existingTicket->load('session'),
                ];
            }
            return [
                'status' => 'already_queued',
                'message' => 'អ្នកមានលេខសំបុត្ររួចហើយ!',
                'ticket' => $existingTicket->load('session'),
            ];
        }

        // Find employee payslip for this month
        $monthDate = Carbon::createFromFormat('!Y-m', $session->month);
        $start = $monthDate->copy()->startOfMonth()->toDateString();
        $end = $monthDate->copy()->endOfMonth()->toDateString();

        $payslip = EmployeePayslip::withoutGlobalScope('branch')
            ->where('employee_id', $employee->id)
            ->where('salary_cycle', 'monthly')
            ->whereBetween('salary_from', [$start, $end])
            ->first();

        if ($payslip && $payslip->status === PayslipStatusEnum::paid->value) {
            return [
                'status' => 'already_paid',
                'message' => 'ប្រាក់ខែរបស់អ្នកត្រូវបានបើករួចរាល់ហើយ!',
                'ticket' => null,
            ];
        }

        // Generate Ticket Number: #A-001, #A-002, ...
        $totalTicketsInSession = PayrollQueueTicket::where('session_id', $session->id)->count();
        $ticketNumber = 'A-' . str_pad($totalTicketsInSession + 1, 3, '0', STR_PAD_LEFT);

        $ticket = PayrollQueueTicket::create([
            'session_id' => $session->id,
            'employee_id' => $employee->id,
            'payslip_id' => $payslip?->id,
            'ticket_number' => $ticketNumber,
            'counter_number' => null,
            'status' => 'waiting',
            'joined_at' => now(),
        ]);

        return [
            'status' => 'success',
            'message' => 'អ្នកបានចូលជួរដោយជោគជ័យ!',
            'ticket' => $ticket->load(['session', 'employee']),
        ];
    }

    /**
     * Get live state of the queue for the two panels.
     */
    public function getLiveQueueData(PayrollQueueSession $session): array
    {
        $maxCounters = $session->max_active_serving ?: 5;

        // 1. Panel 1: Serving Counters (1..5)
        $activeServingTickets = PayrollQueueTicket::with([
                'employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
                'payslip',
            ])
            ->where('session_id', $session->id)
            ->whereIn('status', ['calling', 'serving'])
            ->get()
            ->keyBy('counter_number');

        $counters = [];
        for ($i = 1; $i <= $maxCounters; $i++) {
            $counters[$i] = [
                'counter_number' => $i,
                'counter_name' => 'តុទី ' . $i,
                'ticket' => $activeServingTickets->get($i),
                'is_busy' => $activeServingTickets->has($i),
            ];
        }

        // 2. Panel 2 - Left: Waiting List
        $waitingTickets = PayrollQueueTicket::with([
                'employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
            ])
            ->where('session_id', $session->id)
            ->where('status', 'waiting')
            ->orderBy('joined_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 3. Panel 2 - Right: Paid Successfully List
        $paidTickets = PayrollQueueTicket::with([
                'employee' => fn ($q) => $q->withTrashed()->with(['branch', 'department']),
                'paymentMethod',
                'disbursedBy',
            ])
            ->where('session_id', $session->id)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        // Stats
        $stats = [
            'total_joined' => PayrollQueueTicket::where('session_id', $session->id)->count(),
            'total_waiting' => $waitingTickets->count(),
            'total_serving' => $activeServingTickets->count(),
            'total_paid' => PayrollQueueTicket::where('session_id', $session->id)->where('status', 'paid')->count(),
            'total_amount_paid' => (float) PayrollQueueTicket::where('session_id', $session->id)
                ->where('status', 'paid')
                ->sum('net_amount_paid'),
        ];

        return [
            'session' => $session,
            'counters' => $counters,
            'waiting_tickets' => $waitingTickets,
            'paid_tickets' => $paidTickets,
            'stats' => $stats,
        ];
    }

    /**
     * Call next waiting employee to a counter (1..5).
     */
    public function callNext(PayrollQueueSession $session, int $counterNumber): ?PayrollQueueTicket
    {
        $existing = PayrollQueueTicket::where('session_id', $session->id)
            ->where('counter_number', $counterNumber)
            ->whereIn('status', ['calling', 'serving'])
            ->first();

        // If existing ticket is still there, we skip it
        if ($existing) {
            $existing->update([
                'status' => 'skipped',
                'counter_number' => null,
            ]);
        }

        $nextTicket = PayrollQueueTicket::where('session_id', $session->id)
            ->where('status', 'waiting')
            ->orderBy('joined_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextTicket) {
            $nextTicket->update([
                'counter_number' => $counterNumber,
                'status' => 'calling',
                'called_at' => now(),
            ]);
            return $nextTicket->load(['employee.branch', 'employee.department']);
        }

        return null;
    }

    /**
     * Re-announce / chime ticket.
     */
    public function recallTicket(PayrollQueueTicket $ticket): PayrollQueueTicket
    {
        $ticket->update([
            'status' => 'calling',
            'called_at' => now(),
        ]);
        return $ticket->load(['employee.branch', 'employee.department']);
    }

    /**
     * Skip ticket from counter back to waiting list or skipped status.
     */
    public function skipTicket(PayrollQueueTicket $ticket): bool
    {
        return $ticket->update([
            'status' => 'skipped',
            'counter_number' => null,
        ]);
    }

    /**
     * Get detailed salary breakdown for confirmation modal:
     * Deductions: ច្បាប់, យឺត, ខ្ចី, រំលស់
     * Earnings: លក់, អ៊ុត, សម្ភារ, ការមិនឈប់, ការមកធ្វើការទៀងទាត់
     */
    public function getPayslipBreakdown(PayrollQueueTicket $ticket): array
    {
        $ticket->load(['session', 'employee.branch', 'employee.department']);
        $employee = $ticket->employee;
        $session = $ticket->session;

        // Locate payslip
        $payslip = null;
        if ($ticket->payslip_id) {
            $payslip = EmployeePayslip::withoutGlobalScope('branch')
                ->with(['payslipDetail.salaryComponent', 'additionalData.salaryComponent'])
                ->find($ticket->payslip_id);
        }

        if (!$payslip) {
            $monthDate = Carbon::createFromFormat('!Y-m', $session->month);
            $start = $monthDate->copy()->startOfMonth()->toDateString();
            $end = $monthDate->copy()->endOfMonth()->toDateString();

            $payslip = EmployeePayslip::withoutGlobalScope('branch')
                ->with(['payslipDetail.salaryComponent', 'additionalData.salaryComponent'])
                ->where('employee_id', $employee->id)
                ->where('salary_cycle', 'monthly')
                ->whereBetween('salary_from', [$start, $end])
                ->first();

            if ($payslip) {
                $ticket->update(['payslip_id' => $payslip->id]);
            } else {
                // Fallback: check if employee has any active generated/review payslip
                $payslip = EmployeePayslip::withoutGlobalScope('branch')
                    ->with(['payslipDetail.salaryComponent', 'additionalData.salaryComponent'])
                    ->where('employee_id', $employee->id)
                    ->where('salary_cycle', 'monthly')
                    ->where('status', '!=', PayslipStatusEnum::paid->value)
                    ->latest('id')
                    ->first();

                if ($payslip) {
                    $ticket->update(['payslip_id' => $payslip->id]);
                }
            }
        }

        if (!$payslip) {
            throw new Exception(__('index.payslip_not_found') ?: 'មិនទាន់មានតារាងប្រាក់ខែ (Payslip) សម្រាប់បុគ្គលិកនេះនៅឡើយទេ!');
        }

        // 1. Deductions Breakdown (កាត់ប្រាក់ខែ)
        $unpaidAbsence = (float) $payslip->absent_deduction;
        $lateUndertime = (float) $payslip->undertime;
        $salaryAdvance = (float) $payslip->advance_salary;
        $installment = 0.0;
        $warningPenalty = 0.0;
        $otherDeductions = (float) $payslip->tds + (float) $payslip->ssf_deduction + (float) $payslip->pf_deduction;

        $details = $payslip->payslipDetail->concat($payslip->additionalData);
        foreach ($details as $detail) {
            if ($detail->salaryComponent?->component_type !== 'deductions') continue;
            $name = mb_strtolower(trim($detail->salaryComponent->name));
            if (preg_match('/installment|instalment|បង់រំលស់/u', $name)) {
                $installment += (float) $detail->amount;
            } elseif (preg_match('/warning|penalty|ព្រមាន|ពិន័យ/u', $name)) {
                $warningPenalty += (float) $detail->amount;
            } elseif (preg_match('/late|undertime|early departure|មកយឺត|ចេញមុន/u', $name)) {
                $lateUndertime += (float) $detail->amount;
            } elseif (preg_match('/unpaid|absence|absent|មិនបើកប្រាក់|អវត្តមាន/u', $name)) {
                $unpaidAbsence += (float) $detail->amount;
            } elseif (preg_match('/advance|loan repayment|ប្រាក់បុរេប្រទាន|សងប្រាក់កម្ចី/u', $name)) {
                $salaryAdvance += (float) $detail->amount;
            } else {
                $otherDeductions += (float) $detail->amount;
            }
        }

        $totalDeductions = round($unpaidAbsence + $lateUndertime + $salaryAdvance + $installment + $warningPenalty + $otherDeductions, 2);

        // 2. Earnings Breakdown (ចំណូលបន្ថែម)
        // Basic salary & allowances
        $basicSalary = 0.0;
        $earlyPunctuality = 0.0;
        $attendanceBonus = 0.0;
        $overtimeAmount = (float) $payslip->overtime;

        foreach ($details as $detail) {
            if ($detail->salaryComponent?->component_type !== 'earnings') continue;
            $name = mb_strtolower(trim($detail->salaryComponent->name));
            if (preg_match('/basic salary|base salary|ប្រាក់ខែគោល/u', $name)) {
                $basicSalary += (float) $detail->amount;
            } elseif (preg_match('/early|punctuality|មកលឿន|ទៀងទាត់/u', $name)) {
                $earlyPunctuality += (float) $detail->amount;
            } elseif (preg_match('/attendance|មិនឈប់/u', $name)) {
                $attendanceBonus += (float) $detail->amount;
            }
        }
        if ($basicSalary <= 0) {
            $basicSalary = (float) $payslip->gross_salary - $overtimeAmount - (float) $payslip->bonus;
            if ($basicSalary < 0) $basicSalary = (float) $payslip->gross_salary;
        }

        // Commissions from SellOutReport for this month
        $monthDate = Carbon::createFromFormat('!Y-m', $session->month);
        $start = $monthDate->copy()->startOfMonth();
        $end = $monthDate->copy()->endOfMonth();

        $sales = SellOutReport::query()
            ->where('user_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('service_type, SUM(commission) as commission_total')
            ->groupBy('service_type')
            ->get();

        $commissionSell = 0.0;
        $commissionIron = 0.0;
        $commissionMaterial = 0.0;
        $commissionOther = 0.0;

        foreach ($sales as $sale) {
            $type = mb_strtolower(trim((string) $sale->service_type));
            $amt = (float) $sale->commission_total;
            if (in_array($type, ['លក់', 'sell', 'sale'], true)) {
                $commissionSell += $amt;
            } elseif (in_array($type, ['អ៊ុង', 'អ៊ុត', 'iron', 'scots'], true)) {
                $commissionIron += $amt;
            } elseif (in_array($type, ['សម្ភារ', 'material', 'materials'], true)) {
                $commissionMaterial += $amt;
            } else {
                $commissionOther += $amt;
            }
        }

        $generalBonus = (float) $payslip->bonus;
        $grossTotal = (float) $payslip->gross_salary;
        $netSalary = (float) $payslip->net_salary;

        $paymentMethods = PaymentMethod::where('status', 1)->get();

        return [
            'ticket' => $ticket,
            'employee' => $employee,
            'payslip' => $payslip,
            'deductions' => [
                'leave' => round($unpaidAbsence, 2),             // ច្បាប់
                'late_undertime' => round($lateUndertime, 2),   // យឺត / ចេញមុន
                'salary_advance' => round($salaryAdvance, 2),   // ខ្ចីលុយ
                'installment' => round($installment, 2),        // រំលស់
                'penalty' => round($warningPenalty, 2),         // ពិន័យ
                'other' => round($otherDeductions, 2),          // ផ្សេងៗ (SSF, PF, TDS)
                'total' => $totalDeductions,
            ],
            'earnings' => [
                'basic_salary' => round($basicSalary, 2),
                'overtime' => round($overtimeAmount, 2),
                'sell' => round($commissionSell, 2),                     // លក់
                'iron' => round($commissionIron, 2),                     // អ៊ុត
                'material' => round($commissionMaterial, 2),             // សម្ភារ
                'other_commission' => round($commissionOther, 2),
                'attendance_bonus' => round($attendanceBonus, 2),       // ការមិនឈប់
                'punctuality_bonus' => round($earlyPunctuality, 2),      // ការមកធ្វើការទៀងទាត់
                'general_bonus' => round($generalBonus, 2),
                'gross_total' => round($grossTotal, 2),
            ],
            'net_salary' => round($netSalary, 2),
            'payment_methods' => $paymentMethods,
        ];
    }

    /**
     * Disburse salary to employee, mark payslip paid, settle loans, and auto-call next.
     */
    public function disburseSalary(PayrollQueueTicket $ticket, array $paymentData): array
    {
        $session = $ticket->session;
        $payslip = $ticket->payslip;

        if (!$payslip && $ticket->payslip_id) {
            $payslip = EmployeePayslip::withoutGlobalScope('branch')->find($ticket->payslip_id);
        }

        if (!$payslip) {
            $breakdown = $this->getPayslipBreakdown($ticket);
            $payslip = $breakdown['payslip'];
        }

        $paidOn = $paymentData['paid_on'] ?? now()->toDateString();
        $paymentMethodId = $paymentData['payment_method_id'] ?? null;
        $counterNumber = $ticket->counter_number;

        DB::beginTransaction();
        try {
            // Settle Tada if present
            if ($payslip->tada > 0) {
                $this->tadaService->makeSettlement([
                    'is_settled' => 1,
                    'remark' => 'included in payday queue payroll.',
                    'verified_by' => Auth::id(),
                ], $payslip);
            }

            // Settle Advance salary if present
            if ($payslip->advance_salary > 0) {
                $this->advanceSalaryService->advanceSalarySettlement($payslip, [
                    'is_settled' => true,
                    'remark' => 'settled in payday queue payroll',
                ]);
            }

            // Update payslip to PAID
            $payslip->update([
                'paid_on' => $paidOn,
                'status' => PayslipStatusEnum::paid->value,
                'payment_method_id' => $paymentMethodId,
            ]);

            // Update Ticket to PAID
            $ticket->update([
                'status' => 'paid',
                'paid_at' => now(),
                'net_amount_paid' => $payslip->net_salary,
                'payment_method_id' => $paymentMethodId,
                'disbursed_by' => Auth::id(),
                'remarks' => $paymentData['remarks'] ?? null,
            ]);

            DB::commit();

            // Try sending notification
            try {
                $msg = "ប្រាក់ខែរបស់អ្នកត្រូវបានបើកជោគជ័យនៅថ្ងៃទី " . $paidOn . " ចំនួន $" . number_format($payslip->net_salary, 2);
                SMPushHelper::sendSalaryNotification('បើកប្រាក់ខែជោគជ័យ', $msg, $ticket->employee_id);
            } catch (Exception $e) {
                // Ignore push notification errors
            }

            // Automatically call the next person into this freed counter if available
            $nextCalled = null;
            if ($counterNumber) {
                $nextCalled = $this->callNext($session, $counterNumber);
            }

            return [
                'success' => true,
                'ticket' => $ticket->fresh(['employee', 'paymentMethod']),
                'next_ticket' => $nextCalled,
                'message' => 'បានបើកប្រាក់ខែជោគជ័យ!',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
