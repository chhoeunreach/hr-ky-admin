<?php

namespace App\Services\Telegram;

use App\Jobs\SendTelegramEventNotification;
use App\Models\AdvanceSalary;
use App\Models\Complaint;
use App\Models\LeaveRequestMaster;
use App\Models\PayrollQueueTicket;
use App\Models\Resignation;
use App\Models\TelegramGroup;
use App\Models\TimeLeave;
use App\Models\User;
use App\Models\Warning;
use App\Support\TelegramBotSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelegramNotificationHub
{
    /**
     * Escape special HTML characters for Telegram HTML parse mode.
     */
    public static function escape(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Core dispatcher that pushes the event notification to the queue.
     */
    public static function dispatch(
        string $eventKey,
        string $messageText,
        ?string $branchName = null,
        ?string $departmentName = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $photoPath = null
    ): void {
        try {
            $botToken = TelegramBotSettings::botToken();
            if ($botToken === '') {
                Log::debug('Telegram notification ignored: Bot token empty.', ['event' => $eventKey]);
                return;
            }

            SendTelegramEventNotification::dispatch(
                $eventKey,
                $messageText,
                'HTML',
                $branchName,
                $departmentName,
                $latitude,
                $longitude,
                $photoPath
            )->afterCommit();
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch Telegram event notification', [
                'eventKey' => $eventKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 1. Leave Request Notification
     */
    public static function notifyLeaveRequest(LeaveRequestMaster $leave): void
    {
        $employee = $leave->leaveRequestedBy;
        $leaveType = $leave->leaveType;
        $branch = $leave->branch ?? $employee?->branch;
        $department = $leave->department ?? $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $typeName = self::escape($leaveType?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $deptName = self::escape($department?->dept_name ?? '');
        $fromDate = $leave->leave_from ? Carbon::parse($leave->leave_from)->format('d/m/Y') : '';
        $toDate = $leave->leave_to ? Carbon::parse($leave->leave_to)->format('d/m/Y') : '';
        $days = (float) ($leave->no_of_days ?? 1);
        $reason = self::escape(strip_tags((string) $leave->reasons));
        $now = Carbon::now()->format('d/m/Y H:i');

        $message = "📝 <b>ការស្នើសុំច្បាប់ឈប់សម្រាកថ្មី (Leave Request)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . ($deptName !== '' ? "📁 <b>ផ្នែក:</b> {$deptName}\n" : '')
            . "📌 <b>ប្រភេទច្បាប់:</b> {$typeName}\n"
            . "📅 <b>កាលបរិច្ឆេទ:</b> {$fromDate} ដល់ {$toDate} ({$days} ថ្ងៃ)\n"
            . ($reason !== '' ? "💬 <b>មូលហេតុ:</b> {$reason}\n" : '')
            . "⏰ <b>ម៉ោងស្នើសុំ:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីពិនិត្យ និងអនុម័ត</i>";

        self::dispatch(
            TelegramGroup::EVENT_LEAVE_REQUEST,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 2. Leave Status Update (Approved, Rejected, Cancelled)
     */
    public static function notifyLeaveStatus(
        LeaveRequestMaster $leave,
        string $status,
        ?string $approvedBy = null,
        ?string $remark = null
    ): void {
        $statusNormalized = strtolower(trim($status));
        $employee = $leave->leaveRequestedBy;
        $leaveType = $leave->leaveType;
        $branch = $leave->branch ?? $employee?->branch;
        $department = $leave->department ?? $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $typeName = self::escape($leaveType?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $approvedByName = self::escape($approvedBy ?? auth()->user()?->name ?? 'System');
        $remarkText = self::escape($remark ?? $leave->admin_remark ?? '');
        $now = Carbon::now()->format('d/m/Y H:i');

        if ($statusNormalized === 'approved') {
            $eventKey = TelegramGroup::EVENT_LEAVE_APPROVED;
            $statusBadge = "✅ <b>អនុម័ត (Approved)</b>";
            $title = "✅ <b>ការអនុម័តច្បាប់ឈប់សម្រាក (Leave Approved)</b>";
        } elseif ($statusNormalized === 'rejected') {
            $eventKey = TelegramGroup::EVENT_LEAVE_REJECTED;
            $statusBadge = "❌ <b>បដិសេធ (Rejected)</b>";
            $title = "❌ <b>ការបដិសេធច្បាប់ឈប់សម្រាក (Leave Rejected)</b>";
        } else {
            $eventKey = TelegramGroup::EVENT_LEAVE_CANCELLED;
            $statusBadge = "⚠️ <b>លុបចោល (Cancelled)</b>";
            $title = "⚠️ <b>ការលុបចោលច្បាប់ឈប់សម្រាក (Leave Cancelled)</b>";
        }

        $message = "{$title}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "📌 <b>ប្រភេទច្បាប់:</b> {$typeName}\n"
            . "📊 <b>ស្ថានភាព:</b> {$statusBadge}\n"
            . "👨‍💼 <b>ចាត់ចែងដោយ:</b> {$approvedByName}\n"
            . ($remarkText !== '' ? "💬 <b>សម្គាល់:</b> {$remarkText}\n" : '')
            . "⏰ <b>កាលបរិច្ឆេទ:</b> {$now}";

        self::dispatch(
            $eventKey,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 3. Time Leave Request (ចេញក្រៅ/មកយឺត)
     */
    public static function notifyTimeLeaveRequest(TimeLeave $timeLeave): void
    {
        $employee = $timeLeave->leaveRequestedBy;
        $branch = $timeLeave->branch ?? $employee?->branch;
        $department = $timeLeave->department ?? $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $deptName = self::escape($department?->dept_name ?? '');
        $issueDate = $timeLeave->issue_date ? Carbon::parse($timeLeave->issue_date)->format('d/m/Y') : '';
        $startTime = self::escape((string) $timeLeave->start_time);
        $endTime = self::escape((string) $timeLeave->end_time);
        $reason = self::escape(strip_tags((string) $timeLeave->reasons));
        $now = Carbon::now()->format('d/m/Y H:i');

        $message = "⏱ <b>ការស្នើសុំចេញក្រៅ/មកយឺត (Time Leave Request)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . ($deptName !== '' ? "📁 <b>ផ្នែក:</b> {$deptName}\n" : '')
            . "📅 <b>កាលបរិច្ឆេទ:</b> {$issueDate}\n"
            . "⏰ <b>ចន្លោះម៉ោង:</b> {$startTime} ➔ {$endTime}\n"
            . ($reason !== '' ? "💬 <b>មូលហេតុ:</b> {$reason}\n" : '')
            . "⏰ <b>ម៉ោងស្នើសុំ:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីពិនិត្យ</i>";

        self::dispatch(
            TelegramGroup::EVENT_TIME_LEAVE_REQUEST,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 4. Time Leave Status Update
     */
    public static function notifyTimeLeaveStatus(
        TimeLeave $timeLeave,
        string $status,
        ?string $approvedBy = null,
        ?string $remark = null
    ): void {
        $statusNormalized = strtolower(trim($status));
        $employee = $timeLeave->leaveRequestedBy;
        $branch = $timeLeave->branch ?? $employee?->branch;
        $department = $timeLeave->department ?? $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $approvedByName = self::escape($approvedBy ?? auth()->user()?->name ?? 'System');
        $remarkText = self::escape($remark ?? $timeLeave->admin_remark ?? '');
        $now = Carbon::now()->format('d/m/Y H:i');

        if ($statusNormalized === 'approved') {
            $eventKey = TelegramGroup::EVENT_TIME_LEAVE_APPROVED;
            $statusBadge = "✅ <b>អនុម័ត (Approved)</b>";
            $title = "✅ <b>ការអនុម័តចេញក្រៅ/មកយឺត (Time Leave Approved)</b>";
        } else {
            $eventKey = TelegramGroup::EVENT_TIME_LEAVE_REJECTED;
            $statusBadge = "❌ <b>បដិសេធ (Rejected)</b>";
            $title = "❌ <b>ការបដិសេធចេញក្រៅ/មកយឺត (Time Leave Rejected)</b>";
        }

        $message = "{$title}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "📊 <b>ស្ថានភាព:</b> {$statusBadge}\n"
            . "👨‍💼 <b>ចាត់ចែងដោយ:</b> {$approvedByName}\n"
            . ($remarkText !== '' ? "💬 <b>សម្គាល់:</b> {$remarkText}\n" : '')
            . "⏰ <b>កាលបរិច្ឆេទ:</b> {$now}";

        self::dispatch(
            $eventKey,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 5. Advance Salary Request
     */
    public static function notifyAdvanceSalaryRequest(AdvanceSalary $advanceSalary): void
    {
        $employee = $advanceSalary->requestedBy;
        $branch = $employee?->branch;
        $department = $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $amount = number_format((float) ($advanceSalary->requested_amount ?? 0), 2);
        $reqDate = $advanceSalary->advance_requested_date ? Carbon::parse($advanceSalary->advance_requested_date)->format('d/m/Y') : '';
        $reason = self::escape(strip_tags((string) $advanceSalary->description));
        $now = Carbon::now()->format('d/m/Y H:i');

        $message = "💵 <b>ការស្នើសុំបុរេប្រាក់ខែ (Advance Salary Request)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "💰 <b>ចំនួនទឹកប្រាក់:</b> \${$amount}\n"
            . "📅 <b>កាលបរិច្ឆេទស្នើសុំ:</b> {$reqDate}\n"
            . ($reason !== '' ? "💬 <b>មូលហេតុ:</b> {$reason}\n" : '')
            . "⏰ <b>ម៉ោងស្នើសុំ:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីពិនិត្យ</i>";

        self::dispatch(
            TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 6. Advance Salary Status Update
     */
    public static function notifyAdvanceSalaryStatus(
        AdvanceSalary $advanceSalary,
        string $status,
        ?string $approvedBy = null,
        ?string $remark = null
    ): void {
        $statusNormalized = strtolower(trim($status));
        $employee = $advanceSalary->requestedBy;
        $branch = $employee?->branch;
        $department = $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $amount = number_format((float) ($advanceSalary->released_amount ?? $advanceSalary->requested_amount ?? 0), 2);
        $approvedByName = self::escape($approvedBy ?? auth()->user()?->name ?? 'System');
        $remarkText = self::escape($remark ?? $advanceSalary->remark ?? '');
        $now = Carbon::now()->format('d/m/Y H:i');

        if ($statusNormalized === 'approved') {
            $eventKey = TelegramGroup::EVENT_ADVANCE_SALARY_APPROVED;
            $statusBadge = "✅ <b>អនុម័ត (Approved)</b>";
            $title = "💰 <b>ការអនុម័តបុរេប្រាក់ខែ (Advance Salary Approved)</b>";
        } else {
            $eventKey = TelegramGroup::EVENT_ADVANCE_SALARY_REJECTED;
            $statusBadge = "❌ <b>បដិសេធ (Rejected)</b>";
            $title = "❌ <b>ការបដិសេធបុរេប្រាក់ខែ (Advance Salary Rejected)</b>";
        }

        $message = "{$title}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "💰 <b>ចំនួនទឹកប្រាក់:</b> \${$amount}\n"
            . "📊 <b>ស្ថានភាព:</b> {$statusBadge}\n"
            . "👨‍💼 <b>ចាត់ចែងដោយ:</b> {$approvedByName}\n"
            . ($remarkText !== '' ? "💬 <b>សម្គាល់:</b> {$remarkText}\n" : '')
            . "⏰ <b>កាលបរិច្ឆេទ:</b> {$now}";

        self::dispatch(
            $eventKey,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 7. Resignation Request
     */
    public static function notifyResignationRequest(Resignation $resignation): void
    {
        $employee = $resignation->employee;
        $branch = $resignation->branch ?? $employee?->branch;
        $department = $resignation->department ?? $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $deptName = self::escape($department?->dept_name ?? '');
        $noticeDate = $resignation->resignation_date ? Carbon::parse($resignation->resignation_date)->format('d/m/Y') : '';
        $lastDay = $resignation->last_working_day ? Carbon::parse($resignation->last_working_day)->format('d/m/Y') : '';
        $reason = self::escape(strip_tags((string) $resignation->reason));
        $now = Carbon::now()->format('d/m/Y H:i');

        $message = "🚪 <b>ការស្នើសុំលាឈប់ពីការងារ (Resignation Request)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . ($deptName !== '' ? "📁 <b>ផ្នែក:</b> {$deptName}\n" : '')
            . "📅 <b>កាលបរិច្ឆេទស្នើ:</b> {$noticeDate}\n"
            . "🛑 <b>ថ្ងៃធ្វើការចុងក្រោយ:</b> {$lastDay}\n"
            . ($reason !== '' ? "💬 <b>មូលហេតុ:</b> {$reason}\n" : '')
            . "⏰ <b>ម៉ោងបញ្ជូន:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីពិនិត្យ</i>";

        self::dispatch(
            TelegramGroup::EVENT_RESIGNATION_REQUEST,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 8. Resignation Status Update
     */
    public static function notifyResignationStatus(
        Resignation $resignation,
        string $status,
        ?string $approvedBy = null,
        ?string $remark = null
    ): void {
        $statusNormalized = strtolower(trim($status));
        $employee = $resignation->employee;
        $branch = $resignation->branch ?? $employee?->branch;
        $department = $resignation->department ?? $employee?->department;

        $empName = self::escape($employee?->name ?? 'N/A');
        $branchName = self::escape($branch?->name ?? '');
        $approvedByName = self::escape($approvedBy ?? auth()->user()?->name ?? 'System');
        $remarkText = self::escape($remark ?? $resignation->admin_remark ?? '');
        $lastDay = $resignation->last_working_day ? Carbon::parse($resignation->last_working_day)->format('d/m/Y') : '';
        $now = Carbon::now()->format('d/m/Y H:i');

        if ($statusNormalized === 'approved') {
            $eventKey = TelegramGroup::EVENT_RESIGNATION_APPROVED;
            $statusBadge = "✅ <b>អនុម័ត (Approved)</b>";
            $title = "✅ <b>ការអនុម័តការលាឈប់ (Resignation Approved)</b>";
        } else {
            $eventKey = TelegramGroup::EVENT_RESIGNATION_REJECTED;
            $statusBadge = "❌ <b>បដិសេធ (Rejected)</b>";
            $title = "❌ <b>ការបដិសេធការលាឈប់ (Resignation Rejected)</b>";
        }

        $message = "{$title}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "🛑 <b>ថ្ងៃធ្វើការចុងក្រោយ:</b> {$lastDay}\n"
            . "📊 <b>ស្ថានភាព:</b> {$statusBadge}\n"
            . "👨‍💼 <b>ចាត់ចែងដោយ:</b> {$approvedByName}\n"
            . ($remarkText !== '' ? "💬 <b>សម្គាល់:</b> {$remarkText}\n" : '')
            . "⏰ <b>កាលបរិច្ឆេទ:</b> {$now}";

        self::dispatch(
            $eventKey,
            $message,
            $branch?->name,
            $department?->dept_name
        );
    }

    /**
     * 9. Warning Notice Issued
     */
    public static function notifyWarningCreated(Warning $warning): void
    {
        $branch = $warning->branch;
        $branchName = self::escape($branch?->name ?? '');
        $subject = self::escape($warning->subject ?? 'Warning Notice');
        $date = $warning->warning_date ? Carbon::parse($warning->warning_date)->format('d/m/Y') : '';
        $creatorName = self::escape($warning->createdBy?->name ?? auth()->user()?->name ?? 'HR');
        $warningNo = self::escape($warning->warning_number ?? 'WRN');

        $empNames = [];
        try {
            if ($warning->relationLoaded('warningEmployee') || ($warning->exists && method_exists($warning, 'warningEmployee'))) {
                $empNames = $warning->warningEmployee?->map(fn($item) => $item->employee?->name)->filter()->all() ?? [];
            }
        } catch (\Throwable $e) {
            // Relation query fallback
        }
        $empList = self::escape(implode(', ', $empNames));

        $message = "⚠️ <b>ការចេញលិខិតព្រមាន (Warning Notice Issued)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "🔢 <b>លេខកូដ:</b> {$warningNo}\n"
            . ($empList !== '' ? "👤 <b>បុគ្គលិកទទួល:</b> {$empList}\n" : '')
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "📌 <b>ប្រធានបទ:</b> {$subject}\n"
            . "📅 <b>កាលបរិច្ឆេទ:</b> {$date}\n"
            . "👨‍💼 <b>ចេញដោយ:</b> {$creatorName}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីមើលលម្អិត</i>";

        self::dispatch(
            TelegramGroup::EVENT_WARNING_CREATED,
            $message,
            $branch?->name
        );
    }

    /**
     * 10. Complaint Submitted
     */
    public static function notifyComplaintCreated(Complaint $complaint): void
    {
        $branch = $complaint->branch;
        $branchName = self::escape($branch?->name ?? '');
        $fromName = self::escape($complaint->complainFrom?->name ?? 'Anonymous');
        $subject = self::escape($complaint->subject ?? 'New Complaint');
        $date = $complaint->complaint_date ? Carbon::parse($complaint->complaint_date)->format('d/m/Y') : '';

        $message = "⚖️ <b>ពាក្យបណ្តឹងថ្មី (New Complaint Submitted)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>អ្នកប្តឹង:</b> {$fromName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "📌 <b>ប្រធានបទ:</b> {$subject}\n"
            . "📅 <b>កាលបរិច្ឆេទ:</b> {$date}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីពិនិត្យ</i>";

        self::dispatch(
            TelegramGroup::EVENT_COMPLAINT_CREATED,
            $message,
            $branch?->name
        );
    }

    /**
     * 11. Payday Queue Ticket Called
     */
    public static function notifyPayrollQueueCalled(PayrollQueueTicket $ticket): void
    {
        $employee = $ticket->employee;
        $session = $ticket->session;
        $branch = $session?->branch ?? $employee?->branch;
        $branchName = self::escape($branch?->name ?? '');

        $empName = self::escape($employee?->name ?? 'N/A');
        $ticketNo = self::escape($ticket->ticket_number ?? 'A001');
        $counterNo = (int) ($ticket->counter_number ?? 1);
        $now = Carbon::now()->format('H:i:s');

        $message = "🔔 <b>ហៅលេខកាតបើកប្រាក់ខែ (Payday Queue Called)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "🎫 <b>លេខសំបុត្រ:</b> <code>{$ticketNo}</code>\n"
            . "🏢 <b>ទៅកាន់បញ្ជរលេខ:</b> <b>បញ្ជរ {$counterNo}</b>\n"
            . "👤 <b>ឈ្មោះបុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "📍 <b>សាខា:</b> {$branchName}\n" : '')
            . "⏰ <b>ម៉ោងហៅ:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "📢 <i>សូមបុគ្គលិកអញ្ជើញមកកាន់បញ្ជរដើម្បីបើកប្រាក់ខែ</i>";

        self::dispatch(
            TelegramGroup::EVENT_PAYROLL_QUEUE_CALLED,
            $message,
            $branch?->name
        );
    }

    /**
     * 12. Payday Queue Salary Disbursed
     */
    public static function notifyPayrollQueueServed(PayrollQueueTicket $ticket, array $paymentData = []): void
    {
        $employee = $ticket->employee;
        $session = $ticket->session;
        $branch = $session?->branch ?? $employee?->branch;
        $branchName = self::escape($branch?->name ?? '');

        $empName = self::escape($employee?->name ?? 'N/A');
        $ticketNo = self::escape($ticket->ticket_number ?? 'A001');
        $amount = number_format((float) ($ticket->net_amount_paid ?? 0), 2);
        $disbursedBy = self::escape($ticket->disbursedBy?->name ?? auth()->user()?->name ?? 'Cashier');
        $now = Carbon::now()->format('d/m/Y H:i');

        $message = "💳 <b>ការបើកប្រាក់ខែរួចរាល់ (Salary Disbursed)</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "🎫 <b>លេខសំបុត្រ:</b> <code>{$ticketNo}</code>\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . ($branchName !== '' ? "🏢 <b>សាខា:</b> {$branchName}\n" : '')
            . "💰 <b>ប្រាក់ខែបើកជាក់ស្តែង:</b> \${$amount}\n"
            . "👨‍💼 <b>អ្នកបើកផ្តល់:</b> {$disbursedBy}\n"
            . "⏰ <b>កាលបរិច្ឆេទ:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "✅ <i>បានបញ្ចប់ដំណើរការបើកប្រាក់ខែដោយជោគជ័យ</i>";

        self::dispatch(
            TelegramGroup::EVENT_PAYROLL_QUEUE_SERVED,
            $message,
            $branch?->name
        );
    }

    /**
     * 14. Branch Route (ការងារចេញក្រៅ / បេសកកម្មចល័ត)
     */
    public static function notifyBranchRoute(User $employee, array $fields, ?string $title = null): void
    {
        $status = strtolower(trim((string) ($fields['status'] ?? 'completed')));
        $originBranch = self::escape((string) ($fields['origin_branch'] ?? $employee->branch?->name ?? 'N/A'));
        $targetBranchesList = (array) ($fields['target_branches'] ?? []);
        $targetBranches = self::escape(implode(', ', $targetBranchesList));
        $empName = self::escape((string) $employee->name);
        $purpose = self::escape(strip_tags((string) ($fields['purpose'] ?? 'N/A')));
        $depTime = self::escape((string) ($fields['departure_time'] ?? '—'));
        $arrTime = self::escape((string) ($fields['arrival_time'] ?? '—'));
        $duration = self::escape((string) ($fields['formatted_duration'] ?? (($fields['total_minutes'] ?? 0) . ' នាទី')));
        $stopsCount = (int) ($fields['checkpoints_count'] ?? count((array) ($fields['checkpoints_data'] ?? [])));
        $now = Carbon::now()->format('d/m/Y H:i');

        if ($status === 'completed') {
            $eventKey = TelegramGroup::EVENT_BRANCH_ROUTE_COMPLETED;
            $statusBadge = "✅ <b>បានបញ្ចប់ (Completed)</b>";
            $header = "🚀 <b>របាយការណ៍ការងារចេញក្រៅ (Branch Route Completed)</b>";
        } elseif ($status === 'cancelled') {
            $eventKey = TelegramGroup::EVENT_BRANCH_ROUTE_CANCELLED;
            $statusBadge = "❌ <b>បានបោះបង់ (Cancelled)</b>";
            $header = "⚠️ <b>ការងារចេញក្រៅត្រូវបានបោះបង់ (Branch Route Cancelled)</b>";
        } else {
            $eventKey = TelegramGroup::EVENT_BRANCH_ROUTE_SUBMITTED;
            $statusBadge = "🚗 <b>កំពុងធ្វើដំណើរ (On Route)</b>";
            $header = "🚗 <b>ការចាប់ផ្តើមការងារចេញក្រៅ (Branch Route Started)</b>";
        }

        $message = "{$header}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "👤 <b>បុគ្គលិក:</b> {$empName}\n"
            . "🏢 <b>ចេញពីសាខា:</b> {$originBranch}\n"
            . "📍 <b>ឆ្ពោះទៅកាន់សាខា:</b> {$targetBranches}\n"
            . "🎯 <b>គោលបំណង:</b> {$purpose}\n"
            . "⏱ <b>ម៉ោងចេញ-ដល់:</b> {$depTime} ➔ {$arrTime}\n"
            . "⏳ <b>រយៈពេលសរុប:</b> {$duration}\n"
            . ($stopsCount > 0 ? "🛑 <b>ចំនួនចំណត (Stops):</b> {$stopsCount} កន្លែង\n" : '')
            . "📊 <b>ស្ថានភាព:</b> {$statusBadge}\n"
            . "⏰ <b>កាលបរិច្ឆេទ:</b> {$now}\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "⚡ <i>សូមចូលទៅកាន់ Admin Dashboard ដើម្បីមើលលម្អិត</i>";

        // Dispatch to Origin Branch (and all-branch groups)
        self::dispatch(
            $eventKey,
            $message,
            $originBranch
        );

        // Also notify each Target Branch group so they know staff is arriving / visited
        foreach ($targetBranchesList as $targetBranch) {
            $targetBranchTrimmed = trim((string) $targetBranch);
            if ($targetBranchTrimmed !== '' && strcasecmp($targetBranchTrimmed, $originBranch) !== 0) {
                self::dispatch(
                    $eventKey,
                    $message,
                    $targetBranchTrimmed
                );
            }
        }
    }

    /**
     * 13. System Alert
     */
    public static function notifySystemAlert(string $title, string $messageText, array $context = []): void
    {
        $escapedTitle = self::escape($title);
        $escapedMsg = self::escape($messageText);
        $now = Carbon::now()->format('d/m/Y H:i:s');

        $contextLines = '';
        foreach ($context as $key => $val) {
            if (is_scalar($val)) {
                $contextLines .= "• <b>" . self::escape((string) $key) . ":</b> " . self::escape((string) $val) . "\n";
            }
        }

        $message = "🚨 <b>{$escapedTitle}</b>\n"
            . "━━━━━━━━━━━━━━━━━━━\n"
            . "{$escapedMsg}\n"
            . ($contextLines !== '' ? "\n<b>ព័ត៌មានបន្ថែម:</b>\n{$contextLines}" : '')
            . "⏰ <b>ម៉ោង:</b> {$now}";

        self::dispatch(
            TelegramGroup::EVENT_SYSTEM_ALERT,
            $message
        );
    }
}
