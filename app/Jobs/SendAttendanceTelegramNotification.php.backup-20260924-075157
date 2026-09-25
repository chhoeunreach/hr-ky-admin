<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceTelegramNotifier;
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

    public function __construct(
        public int $userId,
        public int $attendanceId,
        public array $notificationData = [],
    ) {}

    public function handle(AttendanceTelegramNotifier $notifier): void
    {
        $user = User::query()
            ->with([
                'branch:id,name',
                'department:id,name',
                'officeTime:id,opening_time,closing_time,shift',
            ])
            ->find($this->userId);

        $attendance = Attendance::query()->find($this->attendanceId);

        if (!$user || !$attendance) {
            return;
        }

        $notifier->notify($user, $attendance, $this->notificationData);
    }
}

