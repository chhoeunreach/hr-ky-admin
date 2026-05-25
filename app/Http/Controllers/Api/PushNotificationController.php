<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PushNotificationController extends Controller
{
    private function pushErrorResponse(string $message, int $statusCode = 422, array $errors = []): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $response['data'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    public function sendPushNotification(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message_type' => ['nullable', 'string', 'in:text,image,voice,location'],
                'chat_message_type' => ['nullable', 'string', 'in:text,image,voice,location'],
                'media_url' => ['nullable', 'string'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'map_url' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->pushErrorResponse(
                    $validator->errors()->first(),
                    422,
                    $validator->errors()->toArray()
                );
            }

            $data = $request->all();
            $messageType = $data['chat_message_type'] ?? $data['message_type'] ?? 'text';
            $notificationBody = match ($messageType) {
                'image' => 'Sent a photo',
                'voice' => 'Sent a voice message',
                'location' => 'Sent a location',
                default => $data['message'],
            };

            $latitude = array_key_exists('latitude', $data) ? (float) $data['latitude'] : null;
            $longitude = array_key_exists('longitude', $data) ? (float) $data['longitude'] : null;
            $mapUrl = $data['map_url']
                ?? (($latitude !== null && $longitude !== null)
                    ? 'https://www.google.com/maps?q=' . $latitude . ',' . $longitude
                    : '');

            SMPushHelper::sendPushNotification(
                $data['title'],
                $data['conversation_id'],
                $notificationBody,
                $data['type'],
                json_decode($data['usernames']),
                $data['project_id'] ?? "",
                $messageType,
                $data['media_url'] ?? '',
                $latitude,
                $longitude,
                $mapUrl
            );

            $response = [
                'status' => true,
                'message' => __('index.successfully_sent_notification'),
                'status_code' => 200,
            ];
            return response()->json($response, 200, $headers = [], $options = 0);
        }catch(\Exception $exception){
            return $this->pushErrorResponse($exception->getMessage(), 400);
        }
    }
}
