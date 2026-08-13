<?php

namespace App\Services;

use App\Models\TelegramGroup;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        return $this->sendToAction(
            TelegramGroup::ACTION_ATTENDANCE,
            $messageText,
            $parseMode,
            $branchName,
            $departmentName,
            $latitude,
            $longitude
        );
    }

    public function sendToAction(
        string $actionKey,
        string $messageText,
        ?string $parseMode = null,
        ?string $branchName = null,
        ?string $departmentName = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): bool {
        $branchName = trim((string) $branchName);
        $departmentName = trim((string) $departmentName);

        $chatIds = $this->getActionChatIds($actionKey, $branchName, $departmentName);

        if ($chatIds === []) {
            Log::error('Telegram notification skipped: no chat_id available.', [
                'actionKey' => $actionKey,
                'branchName' => $branchName,
                'departmentName' => $departmentName,
            ]);
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

    public function sendToAllKnownChats(string $messageText, ?string $parseMode = null): bool
    {
        $chatIds = $this->getBroadcastChatIds(TelegramGroup::ACTION_NEW_EMPLOYEE);

        if ($chatIds === []) {
            Log::error('Telegram broadcast skipped: no chat IDs available.');
            return false;
        }

        $allOk = true;
        foreach ($chatIds as $chatId) {
            $allOk = $this->sendMessage($chatId, $messageText, $parseMode) && $allOk;
        }

        return $allOk;
    }

    public function sendPhotoToAllKnownChats(string $photoPath, ?string $caption = null, ?string $parseMode = null): bool
    {
        $chatIds = $this->getBroadcastChatIds(TelegramGroup::ACTION_NEW_EMPLOYEE);

        if ($chatIds === []) {
            Log::error('Telegram photo broadcast skipped: no chat IDs available.');
            return false;
        }

        $allOk = true;
        foreach ($chatIds as $chatId) {
            $allOk = $this->sendPhoto($chatId, $photoPath, $caption, $parseMode) !== null && $allOk;
        }

        return $allOk;
    }

    public function resolveChatId(string $branchName, string $departmentName): ?string
    {
        $configuredChatIds = $this->resolveConfiguredChatIds(TelegramGroup::ACTION_ATTENDANCE, $branchName, $departmentName);

        return $configuredChatIds[0] ?? null;
    }

    public function chatIdsForAction(string $actionKey, ?string $branchName = null, ?string $departmentName = null): array
    {
        return $this->getActionChatIds($actionKey, trim((string) $branchName), trim((string) $departmentName));
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

    public function sendPhoto(string $chatId, string $photoPath, ?string $caption = null, ?string $parseMode = null): ?int
    {
        $botToken = (string) config('services.telegram.bot_token', '');

        if ($botToken === '') {
            Log::error('Telegram photo skipped: bot token missing (services.telegram.bot_token).', [
                'chatId' => $chatId,
            ]);
            return null;
        }

        if (! is_file($photoPath)) {
            Log::error('Telegram photo skipped: file not found.', [
                'chatId' => $chatId,
                'photoPath' => $photoPath,
            ]);
            return null;
        }

        $url = rtrim(self::TELEGRAM_API_BASE, '/') . '/bot' . $botToken . '/sendPhoto';

        try {
            $request = Http::timeout(20)
                ->retry(2, 200)
                ->acceptJson()
                ->attach('photo', file_get_contents($photoPath), basename($photoPath));

            $payload = ['chat_id' => $chatId];

            if ($caption !== null && $caption !== '') {
                $payload['caption'] = Str::limit($caption, 1024, '...');
            }

            if ($parseMode !== null) {
                $payload['parse_mode'] = $parseMode;
            }

            $response = $request->post($url, $payload);

            if (! $response->successful()) {
                Log::error('Telegram sendPhoto failed.', [
                    'chatId' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('result.message_id');
        } catch (\Throwable $e) {
            Log::error('Telegram sendPhoto exception.', [
                'chatId' => $chatId,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function sendMediaGroup(string $chatId, array $photoPaths, ?string $caption = null, ?string $parseMode = null): ?array
    {
        $botToken = (string) config('services.telegram.bot_token', '');

        if ($botToken === '') {
            Log::error('Telegram media group skipped: bot token missing (services.telegram.bot_token).', [
                'chatId' => $chatId,
            ]);
            return null;
        }

        $photoPaths = array_values(array_filter($photoPaths, fn (string $path): bool => is_file($path)));

        if ($photoPaths === []) {
            Log::error('Telegram media group skipped: no valid photo files.', [
                'chatId' => $chatId,
            ]);
            return null;
        }

        $url = rtrim(self::TELEGRAM_API_BASE, '/') . '/bot' . $botToken . '/sendMediaGroup';

        try {
            $request = Http::timeout(30)->retry(2, 200)->acceptJson();

            $media = [];
            foreach ($photoPaths as $index => $photoPath) {
                $attachName = 'photo' . $index;
                $request = $request->attach($attachName, file_get_contents($photoPath), basename($photoPath));

                $item = ['type' => 'photo', 'media' => 'attach://' . $attachName];

                if ($index === 0 && $caption !== null && $caption !== '') {
                    $item['caption'] = Str::limit($caption, 1024, '...');

                    if ($parseMode !== null) {
                        $item['parse_mode'] = $parseMode;
                    }
                }

                $media[] = $item;
            }

            $response = $request->post($url, [
                'chat_id' => $chatId,
                'media' => json_encode($media),
            ]);

            if (! $response->successful()) {
                Log::error('Telegram sendMediaGroup failed.', [
                    'chatId' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('result');
        } catch (\Throwable $e) {
            Log::error('Telegram sendMediaGroup exception.', [
                'chatId' => $chatId,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function getActionChatIds(string $actionKey, string $branchName, string $departmentName): array
    {
        $chatIds = $this->resolveConfiguredChatIds($actionKey, $branchName, $departmentName);

        if ($chatIds === []) {
            Log::error('Telegram routing failed (missing or unknown branch/department).', [
                'actionKey' => $actionKey,
                'branchName' => $branchName,
                'departmentName' => $departmentName,
            ]);
        }

        return $chatIds;
    }

    private function getBroadcastChatIds(string $actionKey): array
    {
        return $this->resolveConfiguredChatIds($actionKey, '', '');
    }

    private function resolveConfiguredChatIds(string $actionKey, string $branchName, string $departmentName): array
    {
        try {
            if (! Schema::hasTable('telegram_groups')) {
                return [];
            }

            $groups = TelegramGroup::query()
                ->where('is_active', true)
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Telegram database group lookup failed.', [
                'actionKey' => $actionKey,
                'error' => $exception->getMessage(),
            ]);
            return [];
        }

        $branchName = Str::lower(trim($branchName));
        $departmentName = Str::lower(trim($departmentName));

        $alwaysChatIds = [];
        $routedChatIdsBySpecificity = [];
        foreach ($groups as $group) {
            if (! $this->groupMatchesEvent($group, $actionKey)) {
                continue;
            }

            $groupChatIds = $this->groupChatIds($group);
            if ($groupChatIds === []) {
                continue;
            }

            if ($group->send_for_all) {
                $alwaysChatIds = array_merge($alwaysChatIds, $groupChatIds);
                continue;
            }

            $specificity = $this->routeMatchSpecificity($group, $branchName, $departmentName);
            if ($specificity !== null) {
                $routedChatIdsBySpecificity[$specificity] = array_merge($routedChatIdsBySpecificity[$specificity] ?? [], $groupChatIds);
            }
        }

        $chatIds = $alwaysChatIds;
        if ($routedChatIdsBySpecificity !== []) {
            $maxSpecificity = max(array_keys($routedChatIdsBySpecificity));
            $chatIds = array_merge($chatIds, $routedChatIdsBySpecificity[$maxSpecificity]);
        }

        return array_values(array_unique($chatIds));
    }

    private function groupMatchesEvent(TelegramGroup $group, string $actionKey): bool
    {
        $eventKeys = is_array($group->event_keys) ? $group->event_keys : [];
        $actionKeys = is_array($group->action_keys) && $group->action_keys !== []
            ? $group->action_keys
            : [$group->action_key];

        if ($eventKeys !== []) {
            return in_array($actionKey, $eventKeys, true)
                || ($this->isSellOutEvent($actionKey) && in_array(TelegramGroup::EVENT_SELL_OUT_REPORT, $eventKeys, true));
        }

        return in_array(TelegramGroup::ACTION_GENERAL, $actionKeys, true) || in_array($actionKey, $actionKeys, true);
    }

    private function isSellOutEvent(string $actionKey): bool
    {
        return in_array($actionKey, array_keys(TelegramGroup::sellOutEventOptions()), true);
    }

    private function routeMatchSpecificity(TelegramGroup $group, string $branchName, string $departmentName): ?int
    {
        $groupBranchNames = $this->normalizedRouteNames($group->branch_name);
        $groupDepartmentNames = $this->normalizedRouteNames($group->department_name);

        $branchMatches = $groupBranchNames === [] || ($branchName !== '' && in_array($branchName, $groupBranchNames, true));
        $departmentMatches = $groupDepartmentNames === [] || ($departmentName !== '' && in_array($departmentName, $groupDepartmentNames, true));

        if (! $branchMatches || ! $departmentMatches) {
            return null;
        }

        return (int) ($groupBranchNames !== []) + (int) ($groupDepartmentNames !== []);
    }

    private function groupChatIds(TelegramGroup $group): array
    {
        $chatIds = is_array($group->chat_ids) && $group->chat_ids !== []
            ? $group->chat_ids
            : [$group->chat_id];

        return array_values(array_filter(array_map(function ($chatId) {
            return trim((string) $chatId);
        }, $chatIds)));
    }

    private function normalizedRouteNames(?string $names): array
    {
        return collect(explode(',', (string) $names))
            ->map(fn ($name) => Str::lower(trim($name)))
            ->filter()
            ->values()
            ->toArray();
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
