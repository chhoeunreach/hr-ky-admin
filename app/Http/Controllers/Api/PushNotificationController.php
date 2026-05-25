<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PushNotificationController extends Controller
{
    public function sendPushNotification(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message_type' => ['nullable', 'string', 'in:text,image,voice'],
                'media_url' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return AppHelper::sendErrorResponse(
                    $validator->errors()->first(),
                    422,
                    $validator->errors()->toArray()
                );
            }

            $data = $request->all();
            $messageType = $data['message_type'] ?? 'text';
            $notificationBody = match ($messageType) {
                'image' => 'Sent a photo',
                'voice' => 'Sent a voice message',
                default => $data['message'],
            };

            SMPushHelper::sendPushNotification(
                $data['title'],
                $data['conversation_id'],
                $notificationBody,
                $data['type'],
                json_decode($data['usernames']),
                $data['project_id'] ?? "",
                $messageType,
                $data['media_url'] ?? ''
            );

            $response = [
                'status' => true,
                'message' => __('index.successfully_sent_notification'),
                'status_code' => 200,
            ];
            return response()->json($response, 200, $headers = [], $options = 0);
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }
}
