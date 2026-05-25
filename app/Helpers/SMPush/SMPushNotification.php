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

        $firebase = (new Factory)
            ->withServiceAccount(storage_path('firebase-adminsdk.json'));


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

        $messaging = $firebase->createMessaging();
        $responses = [];

        foreach ($recipients as $userId => $token) {
            if (empty($token)) {
                continue;
            }

            $badgeCount = self::resolveBadgeCountForUser((int) $userId);
            $messageForRecipient = $message
                ->toToken((string) $token)
                ->withApnsConfig(
                    ApnsConfig::new()
                        ->withSound('default')
                        ->withBadge($badgeCount)
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
