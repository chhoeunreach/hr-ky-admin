<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\LeaveRequestMaster;
use App\Models\User;
use App\Services\Telegram\TelegramNotificationHub;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendTelegramAttendanceSummaryCommand extends Command
{
    protected $signature = 'telegram:attendance-summary
        {--branch= : Branch ID or Branch Name (leave empty for all branches)}
        {--date= : Date in Y-m-d format (defaults to today)}';

    protected $description = 'Generate and broadcast a daily attendance summary digest to configured Telegram groups';

    public function handle(): int
    {
        $dateStr = $this->option('date') ?: Carbon::today()->toDateString();
        $branchOption = $this->option('branch');

        if ($branchOption) {
            $branch = is_numeric($branchOption)
                ? Branch::find((int) $branchOption)
                : Branch::where('name', 'like', "%{$branchOption}%")->first();

            if (! $branch) {
                $this->error("Branch not found: {$branchOption}");
                return self::FAILURE;
            }

            $this->generateAndSendSummary($dateStr, $branch);
        } else {
            // 1. Overall Company Summary
            $this->generateAndSendSummary($dateStr, null);

            // 2. Individual Branch Summaries for active branches
            if (Schema::hasTable('branches')) {
                $branchQuery = Branch::query();
                if (Schema::hasColumn('branches', 'is_active')) {
                    $branchQuery->where(function ($q) {
                        $q->where('is_active', true)->orWhereNull('is_active');
                    });
                }
                $branches = $branchQuery->get();
                foreach ($branches as $branch) {
                    $this->generateAndSendSummary($dateStr, $branch);
                }
            }
        }

        $this->info('Daily attendance summary processed successfully.');
        return self::SUCCESS;
    }

    private function generateAndSendSummary(string $dateStr, ?Branch $branch): void
    {
        $branchId = $branch?->id;
        $branchName = $branch?->name;

        // 1. Total staff
        $staffQuery = User::query();
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'deleted_at')) {
            $staffQuery->withoutGlobalScope(\Illuminate\Database\Eloquent\SoftDeletingScope::class);
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $staffQuery->where('is_active', 1);
        } elseif (Schema::hasColumn('users', 'status')) {
            $staffQuery->where('status', 'active');
        }
        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }
        $totalStaff = $staffQuery->count();

        // 2. Present staff on date
        $attendancesQuery = Attendance::query()
            ->whereDate('attendance_date', $dateStr)
            ->whereNotNull('check_in_at');

        if ($branchId) {
            $attendancesQuery->whereHas('user', fn ($q) => $q->where('branch_id', $branchId));
        }

        $attendances = $attendancesQuery->get();
        $presentStaff = $attendances->pluck('user_id')->unique()->count();

        // On-time vs Late
        $lateCount = 0;
        $onTimeCount = 0;
        foreach ($attendances as $att) {
            $isLate = false;
            if (isset($att->check_in_type) && strtolower((string) $att->check_in_type) === 'late') {
                $isLate = true;
            } elseif (isset($att->attendance_status) && stripos((string) $att->attendance_status, 'late') !== false) {
                $isLate = true;
            }

            if ($isLate) {
                $lateCount++;
            } else {
                $onTimeCount++;
            }
        }

        // 3. On-leave staff
        $onLeave = 0;
        if (Schema::hasTable('leave_request_masters')) {
            $leaveQuery = LeaveRequestMaster::query()
                ->where('status', 'approved')
                ->whereDate('leave_from', '<=', $dateStr)
                ->whereDate('leave_to', '>=', $dateStr);

            if ($branchId) {
                $leaveQuery->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->orWhereHas('leaveRequestedBy', fn ($sq) => $sq->where('branch_id', $branchId));
                });
            }
            $onLeave = $leaveQuery->count();
        }

        // 4. Absent
        $absent = max(0, $totalStaff - ($presentStaff + $onLeave));

        $formattedDate = Carbon::parse($dateStr)->format('d/m/Y');

        $summaryData = [
            'date' => $formattedDate,
            'total_staff' => $totalStaff,
            'present' => $presentStaff,
            'on_time' => $onTimeCount,
            'late' => $lateCount,
            'on_leave' => $onLeave,
            'absent' => $absent,
        ];

        TelegramNotificationHub::notifyAttendanceSummary($summaryData, $branchName);

        $this->line("Dispatched summary for " . ($branchName ?: 'All Branches') . " ({$presentStaff}/{$totalStaff} present)");
    }
}
