<?php

namespace App\Modules\AppSecurity\Services;

use App\Helpers\SMPush\SMPushHelper;
use App\Helpers\SMPush\SMPushNotification;
use RuntimeException;

/** Sends a user-scoped security event through the application's configured FCM transport. */
class AppSecurityMobileNotificationSender
{
    public function send(int $userId, int $notificationId, string $eventKey, string $title, string $message): void
    {
        if (!(bool) config('app_security.user_notifications_enabled', false)) return;
        $recipients = SMPushHelper::getUserFCMToken($userId);
        if ($recipients === []) return;

        $result = SMPushNotification::smSend(
            title: $title,
            message: $message,
            data: [
                'type' => 'general',
                'notification_id' => (string) $notificationId,
                'notification_event_id' => $eventKey,
                'security_event' => 'device_state',
                'title' => $title,
                'message' => $message,
            ],
            recipients: $recipients,
            highPriority: true,
        );
        if (($result['failed'] ?? 0) > 0) {
            throw new RuntimeException($result['error'] ?? 'Security notification push delivery failed.');
        }
    }
}
