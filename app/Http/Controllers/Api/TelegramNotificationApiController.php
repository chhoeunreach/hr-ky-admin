<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramNotificationApiController extends Controller
{
    public function send(Request $request, TelegramService $telegramService): JsonResponse
    {
        $validated = $request->validate([
            'branchName' => ['nullable', 'string'],
            'departmentName' => ['nullable', 'string'],
            'messageText' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $ok = $telegramService->sendNotification(
            (string) ($validated['branchName'] ?? ''),
            (string) ($validated['departmentName'] ?? ''),
            $validated['messageText'],
            array_key_exists('latitude', $validated) ? (float) $validated['latitude'] : null,
            array_key_exists('longitude', $validated) ? (float) $validated['longitude'] : null,
        );

        return response()->json([
            'status' => $ok,
            'message' => $ok ? 'Telegram notification sent.' : 'Telegram notification failed. Check server logs.',
        ], $ok ? 200 : 500);
    }
}
