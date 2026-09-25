<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceTelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAttendanceTelegramNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 4;

    public int $timeout = 120;

    public function __construct(
        public string $type,
        public int $userId,
        public int $attendanceId,
    ) {}

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(AttendanceTelegramNotificationService $notificationService): void
    {
        $user = User::query()
            ->with([
                'branch:id,name',
                'department:id,dept_name',
                'officeTime:id,opening_time,closing_time,shift',
            ])
            ->find($this->userId);

        $attendance = Attendance::query()->find($this->attendanceId);

        if (!$user || !$attendance) {
            return;
        }

        $notificationService->sendNow($this->type, $user, $attendance);
    }
}

