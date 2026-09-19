<?php

namespace App\Helpers\SMPush;

use App\Helpers\AppHelper;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class SMPushNotification
{
    private static function resolveBadgeCountForUser(int $userId): int
    {
        $unseenCount = UserNotification::query()
            ->where('user_id', $userId)
            ->where('is_seen', 0)
            ->count();

        return max(1, $unseenCount + 1);
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public static function smSend(string $title,
                                  string $message,
                                  array  $data,
                                  array  $recipients,
                                  bool   $isSilence = false): void
    {
        $data['android_channel_id'] = 'ahpu_channel_11';

        $credentialsPath = storage_path('firebase-adminsdk.json');
        if (!file_exists($credentialsPath) || !is_readable($credentialsPath) || filesize($credentialsPath) === 0) {
            Log::warning("Firebase credentials file missing, empty or unreadable at [{$credentialsPath}]. Push notification skipped.");
            return;
        }

        try {
            $firebase = (new Factory)
                ->withServiceAccount($credentialsPath);
            $messaging = $firebase->createMessaging();
        } catch (\Throwable $e) {
            Log::error("Failed to initialize Firebase with service account [{$credentialsPath}]: " . $e->getMessage());
            return;
        }

        $fromArray = $isSilence ? [] : [
            'notification' => [
                'title' => $title,
                'body' => $message,

            ],
        ];

        $message = CloudMessage
            ::fromArray($fromArray)
            ->withData($data)
            ->withAndroidConfig(
                AndroidConfig::new()
                    ->withSound('default')
            )
        ;

        $responses = [];

        foreach ($recipients as $userId => $token) {
            if (empty($token)) {
                continue;
            }

            $badgeCount = self::resolveBadgeCountForUser((int) $userId);
            $messageForRecipient = $message
                ->toToken((string) $token)
                ->withApnsConfig(
                    ApnsConfig::fromArray([
                        'headers' => [
                            'apns-priority' => '10',
                            'apns-push-type' => 'alert',
                        ],
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => $badgeCount,
                                'content-available' => 1,
                            ],
                        ],
                    ])
                );

            try {
                $responses[$userId] = $messaging->send($messageForRecipient);
            } catch (Exception $exception) {
                $responses[$userId] = [
                    'error' => $exception->getMessage(),
                ];
            }
        }

        Log::info('firebase response '.json_encode($responses));

    }
}
