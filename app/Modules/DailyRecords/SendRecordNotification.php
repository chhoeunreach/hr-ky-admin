<?php

namespace App\Modules\DailyRecords;

use App\Helpers\SMPush\SMPushHelper;
use App\Helpers\SMPush\SMPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SendRecordNotification implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $uniqueFor = 600;

    public function __construct(public int $id) {}

    public function uniqueId(): string
    {
        return (string) $this->id;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('daily-record-notice:'.$this->id))->dontRelease()->expireAfter(120)];
    }

    public function handle(): void
    {
        $delivery = DB::transaction(function () {
            $notice = DB::table('daily_record_notifications')->where('id', $this->id)->lockForUpdate()->first();
            if (!$notice || $notice->delivered_at) {
                return null;
            }
            $company = DB::table('users')->where('id', $notice->user_id)->where('is_active', true)
                ->whereNull('deleted_at')->value('company_id');
            if ($company) {
                // Non-general messages are visible in the inbox through their targeted recipient rows.
                $now = now()->toDateTimeString();
                $id = DB::table('notifications')->insertGetId([
                    'title' => $notice->title, 'description' => $notice->message, 'type' => 'daily_records',
                    'notification_publish_date' => $now, 'is_active' => false, 'company_id' => $company,
                    'created_by' => $notice->user_id, 'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('user_notifications')->insert([
                    'notification_id' => $id, 'user_id' => $notice->user_id, 'is_seen' => false,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            DB::table('daily_record_notifications')->where('id', $this->id)->update(['delivered_at' => RecordTime::now()]);
            return isset($id) ? [$id, $notice] : null;
        }, 3);

        if (!$delivery) {
            return;
        }
        [$id, $notice] = $delivery;
        $tokens = SMPushHelper::getUserFCMToken($notice->user_id);
        if ($tokens) {
            try {
                $result = SMPushNotification::smSend($notice->title, $notice->message, [
                    'type' => 'daily_records', 'notification_id' => (string) $id,
                    'notification_event_id' => 'daily-record-'.$notice->id,
                    'title' => $notice->title, 'message' => $notice->message,
                ], $tokens);
                if (($result['failed'] ?? 0) > 0) {
                    Log::warning('Daily Records push delivery failed', ['notification_id' => $id]);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }
}
