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
            'actionKey' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_\-]+$/'],
            'branchName' => ['nullable', 'string'],
            'departmentName' => ['nullable', 'string'],
            'messageText' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $ok = $telegramService->sendToAction(
            (string) ($validated['actionKey'] ?? \App\Models\TelegramGroup::ACTION_ATTENDANCE),
            $validated['messageText'],
            null,
            (string) ($validated['branchName'] ?? ''),
            (string) ($validated['departmentName'] ?? ''),
            array_key_exists('latitude', $validated) ? (float) $validated['latitude'] : null,
            array_key_exists('longitude', $validated) ? (float) $validated['longitude'] : null,
        );

        return response()->json([
            'status' => $ok,
            'message' => $ok ? 'Telegram notification sent.' : 'Telegram notification failed. Check server logs.',
        ], $ok ? 200 : 500);
    }
}
