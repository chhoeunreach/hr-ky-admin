<?php

namespace App\Modules\AppSecurity\Jobs;

use App\Models\User;
use App\Modules\AppSecurity\Services\AppSecurityMobileNotificationSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Persists a targeted mobile inbox item, then optionally sends its FCM alert. */
class SendAppSecurityUserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [30, 120, 300, 900];

    public function __construct(
        public readonly int $userId,
        public readonly string $eventKey,
        public readonly string $title,
        public readonly string $message,
    ) {
        $this->onQueue('security');
    }

    public function handle(AppSecurityMobileNotificationSender $sender): void
    {
        $user = User::query()->find($this->userId);
        if (!$user) return;

        $notificationId = $this->persistInboxNotification($user);
        $sender->send($this->userId, $notificationId, $this->eventKey, $this->title, $this->message);
    }

    private function persistInboxNotification(User $user): int
    {
        if (!Schema::hasTable('notifications') || !Schema::hasTable('user_notifications')
            || !Schema::hasColumn('notifications', 'security_event_key')) return 0;

        return DB::transaction(function () use ($user): int {
            $existing = DB::table('notifications')->where('security_event_key', $this->eventKey)->lockForUpdate()->value('id');
            if ($existing) return (int) $existing;
            if (!$user->company_id) return 0;

            $notificationId = DB::table('notifications')->insertGetId([
                'title' => $this->title,
                'description' => $this->message,
                'type' => 'general',
                'notification_publish_date' => now(),
                'notification_for_id' => null,
                'security_event_key' => $this->eventKey,
                'is_active' => 1,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('user_notifications')->insertOrIgnore([
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'is_seen' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return (int) $notificationId;
        }, 3);
    }
}
