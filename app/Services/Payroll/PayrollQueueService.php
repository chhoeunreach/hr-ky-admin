<?php

namespace App\Services\Payroll;

use App\Enum\PayslipStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Helpers\SMPush\SMPushNotification;
use App\Models\Branch;
use App\Models\EmployeePayslip;
use App\Models\MonthlyPayrollRecord;
use App\Models\PaymentMethod;
use App\Models\PayrollQueueSession;
use App\Models\PayrollQueueTicket;
use App\Models\SellOutReport;
use App\Models\User;
use App\Services\Tada\TadaService;
use Carbon\Carbon;
use DomainException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
    public function getOrCreateSession(?int $branchId, string $month, ?string $title = null, ?int $departmentId = null, ?int $employeeId = null, int $maxActiveServing = 5): PayrollQueueSession
    {
        $query = PayrollQueueSession::where('month', $month)
            ->where('status', 'open');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }
        $departmentId ? $query->where('department_id', $departmentId) : $query->whereNull('department_id');
        $employeeId ? $query->where('target_employee_id', $employeeId) : $query->whereNull('target_employee_id');

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
                'department_id' => $departmentId,
                'target_employee_id' => $employeeId,
                'month' => $month,
                'title' => $title ?: $defaultTitle,
                'qr_token' => Str::random(32),
                'status' => 'open',
                'max_active_serving' => $maxActiveServing > 0 ? $maxActiveServing : 5,
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
        return DB::transaction(function () use ($qrToken, $employeeId) {
        $session = PayrollQueueSession::where('qr_token', $qrToken)->lockForUpdate()->first();
        if (!$session) {
            throw new DomainException(__('index.payroll_queue_session_not_found') ?: 'មិនមានវគ្គបើកប្រាក់ខែនេះទេ!');
        }

        if ($session->status !== 'open') {
            throw new DomainException(__('index.payroll_queue_session_closed') ?: 'វគ្គបើកប្រាក់ខែនេះត្រូវបានបិទ ឬផ្អាកហើយ!');
        }

        $employee = User::with(['branch', 'department'])
            ->where('is_active', 1)->where('status', 'verified')->find($employeeId);
        if (!$employee) {
            throw new DomainException(__('index.employee_not_found') ?: 'រកមិនឃើញទិន្នន័យបុគ្គលិកនេះទេ!');
        }

        $hasRoster = Schema::hasTable('payroll_queue_invites')
            && DB::table('payroll_queue_invites')->where('session_id', $session->id)->exists();
        if ($hasRoster) {
            $onRoster = DB::table('payroll_queue_invites')->where('session_id', $session->id)
                ->where('employee_id', $employee->id)->where('status', '!=', 'removed')->exists();
            if (!$onRoster) {
                throw new DomainException('This employee is not on the payroll session list.');
            }
        } else {
            if ($session->branch_id && (int) $employee->branch_id !== (int) $session->branch_id) {
                throw new DomainException(__('index.payroll_queue_wrong_branch') ?: 'អ្នកមិនស្ថិតនៅក្នុងសាខាដែលត្រូវបើកប្រាក់ខែនៃវគ្គនេះទេ!');
            }
            if ($session->department_id && (int) $employee->department_id !== (int) $session->department_id) {
                throw new DomainException('This QR pass is only available to the selected department.');
            }
            if ($session->target_employee_id && (int) $employee->id !== (int) $session->target_employee_id) {
                throw new DomainException('This QR pass is assigned to another employee.');
            }
        }

        // Check if ticket already exists for this session
        $existingTicket = PayrollQueueTicket::where('session_id', $session->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existingTicket && $existingTicket->status !== 'skipped') {
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

        if (!$payslip) {
            throw new DomainException(__('index.payslip_not_found') ?: 'Payslip is not available for this payroll session.');
        }
        if ($payslip->status === PayslipStatusEnum::paid->value) {
            return [
                'status' => 'already_paid',
                'message' => 'ប្រាក់ខែរបស់អ្នកត្រូវបានបើករួចរាល់ហើយ!',
                'ticket' => null,
            ];
        }
        if (!in_array($payslip->status, [PayslipStatusEnum::generated->value, PayslipStatusEnum::review->value, PayslipStatusEnum::locked->value], true)) {
            throw new DomainException('This payslip is not ready to join the payroll queue.');
        }

        if ($existingTicket) {
            $existingTicket->update([
                'payslip_id' => $payslip->id,
                'status' => 'waiting',
                'counter_number' => null,
                'joined_at' => now(),
                'called_at' => null,
                'served_at' => null,
            ]);

            return [
                'status' => 'already_queued',
                'message' => 'អ្នកបានចូលជួរដោយជោគជ័យ!',
                'ticket' => $existingTicket->load('session'),
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
        });
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

    /** Call the selected waiting ticket, or the first ticket in line. */
    public function callNext(PayrollQueueSession $session, int $counterNumber, ?int $ticketId = null): ?PayrollQueueTicket
    {
        $ticket = DB::transaction(function () use ($session, $counterNumber, $ticketId) {
            $session = PayrollQueueSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if ($session->status !== 'open') {
                throw new DomainException('This payroll queue session is paused or closed.');
            }
            if ($counterNumber < 1 || $counterNumber > ($session->max_active_serving ?: 5)) {
                throw new DomainException('Select a counter configured for this payroll session.');
            }
            if ($session->tickets()->where('counter_number', $counterNumber)->whereIn('status', ['calling', 'serving'])->exists()) {
                throw new DomainException('This counter is already serving an employee.');
            }

            $ticket = PayrollQueueTicket::where('session_id', $session->id)
                ->where('status', 'waiting')
                ->when($ticketId !== null, fn ($query) => $query->whereKey($ticketId))
                ->orderBy('joined_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($ticketId !== null && !$ticket) {
                throw new DomainException('This ticket is no longer waiting in this payroll session.');
            }

            $ticket?->update([
                'counter_number' => $counterNumber,
                'status' => 'calling',
                'called_at' => now(),
            ]);

            return $ticket;
        }, 3);

        if ($ticket) {
            $this->notifyEmployeeCalled($ticket);
        }

        return $ticket;
    }

    private function notifyEmployeeCalled(PayrollQueueTicket $ticket): void
    {
        try {
            \App\Services\Telegram\TelegramNotificationHub::notifyPayrollQueueCalled($ticket);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Payroll queue call Telegram notification failed', ['error' => $e->getMessage()]);
        }

        $employee = $ticket->employee;
        if (!$employee?->fcm_token) return;
        try {
            SMPushNotification::smSend(
                title: 'Payroll queue: your turn',
                message: "Ticket {$ticket->ticket_number}, please go to counter {$ticket->counter_number}.",
                data: [
                    'type' => 'payroll_queue_call',
                    'ticket_id' => (string) $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'counter_number' => (string) $ticket->counter_number,
                    'session_id' => (string) $ticket->session_id,
                ],
                recipients: [$employee->id => $employee->fcm_token],
                highPriority: true,
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Re-announce / chime ticket.
     */
    public function recallTicket(PayrollQueueTicket $ticket): PayrollQueueTicket
    {
        $updated = PayrollQueueTicket::whereKey($ticket->id)
            ->whereNotNull('counter_number')
            ->whereIn('status', ['calling', 'serving'])
            ->update(['called_at' => now()]);

        if (!$updated) {
            // A second recall in the same second may have the same stored timestamp.
            $ticket->refresh();
            if (!$ticket->counter_number || !in_array($ticket->status, ['calling', 'serving'], true)) {
                throw new DomainException('This ticket is no longer active at a payroll counter.');
            }
        }

        $ticket->refresh();
        $this->notifyEmployeeCalled($ticket);

        return $ticket;
    }

    /** Release the counter and mark the ticket as skipped. */
    public function skipTicket(PayrollQueueTicket $ticket): bool
    {
        return PayrollQueueTicket::whereKey($ticket->id)
            ->whereIn('status', ['calling', 'serving'])
            ->update(['status' => 'skipped', 'counter_number' => null]) > 0;
    }

    /** Mark a called employee as in-process when payroll review begins. */
    public function startPayrollReview(PayrollQueueTicket $ticket): PayrollQueueTicket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket = PayrollQueueTicket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            if ($ticket->status === 'calling') {
                $ticket->update(['status' => 'serving', 'served_at' => now()]);
            } elseif ($ticket->status !== 'serving') {
                throw new Exception('This ticket is no longer active at a payroll counter.');
            }
            return $ticket->fresh(['employee.branch', 'employee.department', 'session']);
        });
    }

    /** Update only the draft incentive, with stale-value protection. */
    public function quickUpdateBonus(PayrollQueueTicket $ticket, float $bonus, float $expectedBonus, float $expectedNet): EmployeePayslip
    {
        return DB::transaction(function () use ($ticket, $bonus, $expectedBonus, $expectedNet) {
            $ticket = PayrollQueueTicket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            if ($ticket->status !== 'serving') throw new Exception('Start payroll review before editing this payslip.');
            $branchId = $ticket->employee?->branch_id;
            abort_unless(AppHelper::canAccessBranch($branchId), 403);
            abort_unless($branchId, 422);
            Branch::whereKey($branchId)->lockForUpdate()->firstOrFail();
            $payslip = $this->resolveSessionPayslip($ticket, lock: true);
            $month = substr((string) $payslip->salary_from, 0, 7);
            if (!in_array($payslip->status, ['generated', 'review'], true)
                || MonthlyPayrollRecord::where('month', $month)->where('branch_id', $branchId)->exists()) {
                throw new Exception('This payroll is locked or has already been saved; the quick update is unavailable.');
            }
            if (round((float) $payslip->bonus, 2) !== round($expectedBonus, 2)
                || round((float) $payslip->net_salary, 2) !== round($expectedNet, 2)) {
                throw new Exception('Payroll changed since it was loaded. Refresh and review it again.');
            }
            $bonus = round($bonus, 2);
            $bonusDelta = round($bonus - (float) $payslip->bonus, 2);
            $payslip->update([
                'bonus' => $bonus,
                'gross_salary' => round((float) $payslip->gross_salary + $bonusDelta, 2),
                'net_salary' => round((float) $payslip->net_salary + $bonusDelta, 2),
                'updated_by' => Auth::id(),
            ]);
            return $payslip->fresh();
        });
    }

    /**
     * Get detailed salary breakdown for confirmation modal:
     * Deductions: ច្បាប់, យឺត, ខ្ចី, រំលស់
     * Earnings: លក់, អ៊ុត, សម្ភារ, ការមិនឈប់, ការមកធ្វើការទៀងទាត់
     */
    public function getPayslipBreakdown(PayrollQueueTicket $ticket): array
    {
        $ticket->load(['session', 'employee' => fn ($query) => $query->withTrashed()->with(['branch', 'department'])]);
        $employee = $ticket->employee;
        $session = $ticket->session;
        $payslip = $this->resolveSessionPayslip($ticket);
        $payslip->load(['payslipDetail.salaryComponent', 'additionalData.salaryComponent']);

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
        $payslipMonth = substr((string) $payslip->salary_from, 0, 7);
        $canQuickUpdate = in_array($payslip->status, ['generated', 'review'], true)
            && !MonthlyPayrollRecord::where('month', $payslipMonth)->where('branch_id', $employee->branch_id)->exists();

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
            'can_quick_update' => $canQuickUpdate,
            'payment_methods' => $paymentMethods,
        ];
    }

    private function resolveSessionPayslip(PayrollQueueTicket $ticket, bool $lock = false): EmployeePayslip
    {
        $month = Carbon::createFromFormat('!Y-m', $ticket->session->month);
        $query = EmployeePayslip::withoutGlobalScope('branch')
            ->where('employee_id', $ticket->employee_id)
            ->where('salary_cycle', 'monthly')
            ->whereBetween('salary_from', [$month->toDateString(), $month->endOfMonth()->toDateString()]);

        if ($lock) {
            $query->lockForUpdate();
        }

        if ($ticket->payslip_id) {
            $payslip = (clone $query)->whereKey($ticket->payslip_id)->first();
            if ($payslip) {
                return $payslip;
            }
        }

        return $query->orderBy('id')->first()
            ?? throw new DomainException(__('index.payslip_not_found'));
    }

    /**
     * Disburse salary to employee, mark payslip paid, settle loans, and auto-call next.
     */
    public function disburseSalary(PayrollQueueTicket $ticket, array $paymentData): array
    {
        $paidOn = $paymentData['paid_on'] ?? now()->toDateString();
        $paymentMethodId = $paymentData['payment_method_id'] ?? null;

        $ticket = DB::transaction(function () use ($ticket, $paidOn, $paymentMethodId, $paymentData) {
            $ticket = PayrollQueueTicket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            if ($ticket->paid_at || $ticket->status === 'paid') {
                throw new DomainException('This ticket has already been paid.');
            }
            if ($ticket->status !== 'serving') {
                throw new DomainException('Move this employee into payroll review before committing payment.');
            }

            $payslip = $this->resolveSessionPayslip($ticket, lock: true);
            if (!in_array($payslip->status, [PayslipStatusEnum::generated->value, PayslipStatusEnum::review->value, PayslipStatusEnum::locked->value], true)) {
                throw new Exception('This payslip is not ready for payment.');
            }
            if (isset($paymentData['expected_net'])
                && round((float) $paymentData['expected_net'], 2) !== round((float) $payslip->net_salary, 2)) {
                throw new DomainException('Payroll changed since it was loaded. Refresh and review it again.');
            }

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
                'payslip_id' => $payslip->id,
                'paid_at' => now(),
                'net_amount_paid' => $payslip->net_salary,
                'payment_method_id' => $paymentMethodId,
                'disbursed_by' => Auth::id(),
                'remarks' => $paymentData['remarks'] ?? null,
            ]);

            return $ticket;
        }, 3);

        try {
            $msg = "ប្រាក់ខែរបស់អ្នកត្រូវបានបើកជោគជ័យនៅថ្ងៃទី " . $paidOn . " ចំនួន $" . number_format($ticket->net_amount_paid, 2);
            SMPushHelper::sendSalaryNotification('បើកប្រាក់ខែជោគជ័យ', $msg, $ticket->employee_id);
            \App\Services\Telegram\TelegramNotificationHub::notifyPayrollQueueServed($ticket, $paymentData);
        } catch (\Throwable $e) {
            report($e);
        }

        $nextCalled = null;
        if ($ticket->counter_number) {
            try {
                $nextCalled = $this->callNext($ticket->session, $ticket->counter_number);
            } catch (DomainException) {
                // The session may have closed or another operator may have filled this counter.
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return [
            'success' => true,
            'ticket' => $ticket->fresh(['employee', 'paymentMethod']),
            'next_ticket' => $nextCalled,
            'message' => 'បានបើកប្រាក់ខែជោគជ័យ!',
        ];
    }
}
