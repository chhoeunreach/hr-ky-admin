<?php

namespace App\Modules\AppSecurity\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendAppSecurityAlertEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [30, 120, 300, 900];

    public function __construct(public readonly int $alertId)
    {
        $this->onQueue('security');
    }

    public function handle(): void
    {
        $recipients = config('app_security.alert_recipients', []);
        if (!(bool) config('app_security.alert_notifications_enabled', false) || $recipients === []) return;

        $alert = DB::table('app_security_alerts')->where('id', $this->alertId)->first();
        if (!$alert || $alert->notification_sent_at) return;
        Mail::raw($alert->message."\nAlert: {$alert->uuid}\nSeverity: {$alert->severity}", function ($message) use ($alert, $recipients): void {
            $message->to($recipients)->subject('[KNEAYERNG App Security] '.$alert->title);
        });
        DB::table('app_security_alerts')->where('id', $alert->id)->update([
            'notification_sent_at' => now(), 'updated_at' => now(),
        ]);
    }
}
