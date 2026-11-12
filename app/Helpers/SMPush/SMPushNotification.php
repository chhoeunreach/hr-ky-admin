<?php

namespace App\Helpers\SMPush;

use App\Helpers\AppHelper;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class SMPushNotification
{
    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public static function smSend(string $title,
                                  string $message,
                                  array  $data,
                                  array  $recipients,
                                  bool   $isSilence = false,
                                  bool   $highPriority = false): array
    {
        $data['android_channel_id'] = 'ahpu_channel_11';

        $credentialsPath = storage_path('firebase-adminsdk.json');
        if (!file_exists($credentialsPath) || !is_readable($credentialsPath) || filesize($credentialsPath) === 0) {
            Log::warning("Firebase credentials file missing, empty or unreadable at [{$credentialsPath}]. Push notification skipped.");
            return ['sent' => 0, 'failed' => count($recipients), 'error' => 'Firebase service credentials are missing or unreadable.'];
        }

        try {
            $firebase = (new Factory)
                ->withServiceAccount($credentialsPath);
            $messaging = $firebase->createMessaging();
        } catch (\Throwable $e) {
            Log::error("Failed to initialize Firebase with service account [{$credentialsPath}]: " . $e->getMessage());
            return ['sent' => 0, 'failed' => count($recipients), 'error' => 'Firebase could not initialize. Check the service account configuration.'];
        }

        $fromArray = $isSilence ? [] : [
            'notification' => [
                'title' => $title,
                'body' => $message,
            ],
        ];

        $androidConfig = AndroidConfig::fromArray([
            'priority' => $highPriority ? 'high' : 'normal',
            'notification' => [
                'sound' => 'default',
                'channel_id' => 'ahpu_channel_11',
            ],
        ]);

        $baseMessage = CloudMessage
            ::fromArray($fromArray)
            ->withData($data)
            ->withAndroidConfig($androidConfig);

        $userIds = array_keys(array_filter($recipients));
        $unseenCounts = UserNotification::query()
            ->whereIn('user_id', $userIds)
            ->where('is_seen', 0)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
        $outbound = [];

        foreach ($recipients as $userId => $token) {
            if (empty($token)) {
                continue;
            }

            $badgeCount = max(1, (int) ($unseenCounts[$userId] ?? 0) + 1);

            if ($isSilence) {
                $apnsPayload = [
                    'headers' => [
                        'apns-priority' => '5',
                        'apns-push-type' => 'background',
                    ],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                        ],
                    ],
                ];
            } else {
                $apnsPayload = [
                    'headers' => [
                        'apns-priority' => '10',
                        'apns-push-type' => 'alert',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $message,
                            ],
                            'sound' => 'default',
                            'badge' => $badgeCount,
                            'content-available' => 1,
                        ],
                    ],
                ];
            }

            $outbound[$userId] = $baseMessage
                ->toToken((string) $token)
                ->withApnsConfig(ApnsConfig::fromArray($apnsPayload));
        }

        $sent = 0;
        $failed = 0;
        foreach (array_chunk($outbound, 500, true) as $batch) {
            try {
                $reports = $messaging->sendAll(array_values($batch))->getItems();
                foreach ($reports as $index => $report) {
                    $userId = array_keys($batch)[$index] ?? null;
                    if ($report->isSuccess()) {
                        $sent++;
                        continue;
                    }

                    $failed++;
                    $exception = $report->error();
                    Log::warning('Firebase push delivery failed for recipient.', [
                        'user_id' => $userId,
                        'exception' => $exception ? get_class($exception) : null,
                        'code' => $exception?->getCode(),
                    ]);
                }
            } catch (\Throwable $exception) {
                $failed += count($batch);
                Log::error('Firebase push batch failed.', [
                    'recipient_count' => count($batch),
                    'exception' => get_class($exception),
                    'code' => $exception->getCode(),
                ]);
            }
        }

        Log::info('Firebase push delivery completed.', ['accepted' => $sent, 'failed' => $failed]);
        return [
            'sent' => $sent,
            'failed' => $failed,
            'error' => $failed ? 'Firebase rejected some push messages. Check the server log for delivery errors.' : null,
        ];
    }
}
