<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramService
{
    private const TELEGRAM_API_BASE = 'https://api.telegram.org';

    public function sendNotification(
        string $branchName,
        string $departmentName,
        string $messageText,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $parseMode = null
    ): bool {
        $branchName = trim($branchName);
        $departmentName = trim($departmentName);

        $defaultChatId = (string) config('services.telegram.default_chat_id', '');
        $resolvedChatId = $this->resolveChatId($branchName, $departmentName);

        if ($resolvedChatId === null) {
            Log::error('Telegram routing failed (missing or unknown branch/department).', [
                'branchName' => $branchName,
                'departmentName' => $departmentName,
            ]);
        }

        $chatIds = array_values(array_unique(array_filter([$defaultChatId, $resolvedChatId])));

        if ($chatIds === []) {
            Log::error('Telegram notification skipped: no chat_id available (check services.telegram.default_chat_id).');
            return false;
        }

        $allOk = true;
        foreach ($chatIds as $chatId) {
            $messageOk = $this->sendMessage($chatId, $messageText, $parseMode);
            $allOk = $allOk && $messageOk;

            if ($latitude !== null && $longitude !== null) {
                $locationOk = $this->sendLocation($chatId, $latitude, $longitude);
                $allOk = $allOk && $locationOk;
            }
        }

        return $allOk;
    }

    public function resolveChatId(string $branchName, string $departmentName): ?string
    {
        $branchName = trim($branchName);
        $departmentName = trim($departmentName);

        if ($branchName === '' || $departmentName === '') {
            return null;
        }

        $departmentChatIds = [
            'management' => '-1002799577548',
            'ជាង' => '-1002842364173',
        ];

        $departmentKey = Str::lower($departmentName);
        if (isset($departmentChatIds[$departmentKey])) {
            return $departmentChatIds[$departmentKey];
        }

        if (isset($departmentChatIds[$departmentName])) {
            return $departmentChatIds[$departmentName];
        }

        if ($branchName === 'កម្ពុជាក្រោម') {
            if (in_array($departmentName, ['មេឌៀ(KY)', 'អ្នកលក់អនឡាញ(KY)'], true)) {
                return '-1002727901053';
            }

            return '-1002617998738';
        }

        $branchChatIds = [
            'អ៊ីអន' => '-1002705869028',
            'កាប់គោ' => '-1002351902820',
            'ស្តុកធំ' => '-1002509454514',
            'វីអាយភី' => '-1002806714995',
        ];

        return $branchChatIds[$branchName] ?? null;
    }

    public function sendMessage(string $chatId, string $messageText, ?string $parseMode = null): bool
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $messageText,
        ];

        if ($parseMode !== null) {
            $payload['parse_mode'] = $parseMode;
        }

        $response = $this->post('sendMessage', $payload, ['chatId' => $chatId]);

        return $response?->successful() ?? false;
    }

    public function sendLocation(string $chatId, float $latitude, float $longitude): bool
    {
        $payload = [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        $response = $this->post('sendLocation', $payload, ['chatId' => $chatId]);

        return $response?->successful() ?? false;
    }

    private function post(string $method, array $payload, array $context = []): ?Response
    {
        $botToken = (string) config('services.telegram.bot_token', '');

        if ($botToken === '') {
            Log::error('Telegram bot token missing (services.telegram.bot_token).', $context);
            return null;
        }

        $url = rtrim(self::TELEGRAM_API_BASE, '/') . '/bot' . $botToken . '/' . ltrim($method, '/');

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->acceptJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::error('Telegram API request failed.', $context + [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('Telegram API request exception.', $context + [
                'method' => $method,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
