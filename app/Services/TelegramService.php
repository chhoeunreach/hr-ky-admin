<?php

namespace App\Services;

use App\Models\TelegramGroup;
use App\Models\User;
use App\Support\TelegramBotSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TelegramService
{
    private const TELEGRAM_API_BASE = 'https://api.telegram.org';

    private ?string $lastError = null;

    public function lastError(): ?string
    {
        return $this->lastError;
    }

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
            $this->lastError = 'No active Telegram chat IDs match this action, branch, and department.';
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
            $this->lastError = 'No active Telegram chat IDs are available for broadcast.';
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
            $this->lastError = 'No active Telegram chat IDs are available for photo broadcast.';
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

    public function sendHtmlMessage(string $chatId, string $messageText): bool
    {
        return $this->sendMessage($chatId, $messageText, 'HTML');
    }

    public function getWebhookInfo(): ?array
    {
        return $this->get('getWebhookInfo')?->json();
    }

    public function getMe(): ?array
    {
        return $this->get('getMe')?->json();
    }

    public function getUpdates(?int $offset = null, int $limit = 100, int $timeout = 0): ?array
    {
        $query = [
            'limit' => $limit,
            'timeout' => $timeout,
            'allowed_updates' => json_encode(['message']),
        ];

        if ($offset !== null) {
            $query['offset'] = $offset;
        }

        return $this->get('getUpdates', $query)?->json();
    }

    public function setWebhook(string $url, ?string $secret = null, bool $dropPendingUpdates = false): bool
    {
        $payload = [
            'url' => $url,
            'drop_pending_updates' => $dropPendingUpdates,
        ];

        if ($secret !== null && trim($secret) !== '') {
            $payload['secret_token'] = trim($secret);
        }

        $response = $this->post('setWebhook', $payload, ['url' => $url]);

        return $response?->successful() ?? false;
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): bool
    {
        $response = $this->post('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);

        return $response?->successful() ?? false;
    }

    public function sendToEmployee(User $employee, string $messageText, ?string $parseMode = null): bool
    {
        $chatId = trim((string) $employee->telegram_chat_id);

        if ($chatId === '') {
            Log::warning('Telegram employee notification skipped: employee has no Telegram chat ID.', [
                'employee_id' => $employee->id,
            ]);
            return false;
        }

        return $this->sendMessage($chatId, $messageText, $parseMode);
    }

    public function sendToEmployees(iterable $employees, string $messageText, ?string $parseMode = null): array
    {
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            if (! $employee instanceof User) {
                continue;
            }

            if (trim((string) $employee->telegram_chat_id) === '') {
                $skipped++;
                continue;
            }

            $this->sendToEmployee($employee, $messageText, $parseMode) ? $sent++ : $failed++;
        }

        return compact('sent', 'failed', 'skipped');
    }

    public function sendConfiguredMessage(string $messageText, ?string $parseMode = null): bool
    {
        $chatId = $this->configuredChatId();

        if ($chatId === null) {
            $this->lastError = 'No default Telegram chat ID is configured. Add TELEGRAM_CHAT_ID or create an active Telegram group for General.';
            Log::error('Telegram notification skipped: no default chat ID is available.');
            return false;
        }

        return $this->sendMessage($chatId, $messageText, $parseMode);
    }

    public function sendNewOrderCreated(array $orderData = []): bool
    {
        return $this->sendCommonNotification('New order created', $orderData);
    }

    public function sendPaymentCompleted(array $paymentData = []): bool
    {
        return $this->sendCommonNotification('Payment completed', $paymentData);
    }

    public function sendUserRegistered(array $userData = []): bool
    {
        $message = $this->buildCommonNotificationMessage('User registered', $userData);

        return $this->sendToAction(TelegramGroup::ACTION_NEW_EMPLOYEE, $message, 'HTML')
            || $this->sendConfiguredMessage($message, 'HTML');
    }

    public function sendSystemAlert(string $messageText, array $context = []): bool
    {
        return $this->sendCommonNotification('Admin/System alert', ['message' => $messageText] + $context);
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
        $botToken = TelegramBotSettings::botToken();

        if ($botToken === '') {
            $this->lastError = 'Telegram bot token is missing. Save the bot token first.';
            Log::error('Telegram photo skipped: bot token missing.', [
                'chatId' => $chatId,
            ]);
            return null;
        }

        if (! is_file($photoPath)) {
            $this->lastError = 'Telegram photo file was not found.';
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
                $this->lastError = $this->formatTelegramError('sendPhoto', $this->telegramErrorFromResponse($response), $chatId);
                Log::error('Telegram sendPhoto failed.', [
                    'chatId' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('result.message_id');
        } catch (\Throwable $e) {
            $this->lastError = $this->formatTelegramError('sendPhoto', $e->getMessage(), $chatId);
            Log::error('Telegram sendPhoto exception.', [
                'chatId' => $chatId,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function sendDocument(string $chatId, string $documentPath, ?string $fileName = null, ?string $caption = null, ?string $parseMode = null): bool
    {
        $botToken = TelegramBotSettings::botToken();

        if ($botToken === '') {
            $this->lastError = 'Telegram bot token is missing. Save the bot token first.';
            Log::error('Telegram document skipped: bot token missing.', [
                'chatId' => $chatId,
            ]);
            return false;
        }

        if (! is_file($documentPath)) {
            $this->lastError = 'Telegram document file was not found.';
            Log::error('Telegram document skipped: file not found.', [
                'chatId' => $chatId,
                'documentPath' => $documentPath,
            ]);
            return false;
        }

        $url = rtrim(self::TELEGRAM_API_BASE, '/') . '/bot' . $botToken . '/sendDocument';

        try {
            $request = Http::timeout(30)
                ->retry(2, 200)
                ->acceptJson()
                ->attach('document', file_get_contents($documentPath), $fileName ?: basename($documentPath));

            $payload = ['chat_id' => $chatId];

            if ($caption !== null && $caption !== '') {
                $payload['caption'] = Str::limit($caption, 1024, '...');
            }

            if ($parseMode !== null) {
                $payload['parse_mode'] = $parseMode;
            }

            $response = $request->post($url, $payload);

            if (! $response->successful()) {
                $this->lastError = $this->formatTelegramError('sendDocument', $this->telegramErrorFromResponse($response), $chatId);
                Log::error('Telegram sendDocument failed.', [
                    'chatId' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $this->formatTelegramError('sendDocument', $e->getMessage(), $chatId);
            Log::error('Telegram sendDocument exception.', [
                'chatId' => $chatId,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendMediaGroup(string $chatId, array $photoPaths, ?string $caption = null, ?string $parseMode = null): ?array
    {
        $botToken = TelegramBotSettings::botToken();

        if ($botToken === '') {
            $this->lastError = 'Telegram bot token is missing. Save the bot token first.';
            Log::error('Telegram media group skipped: bot token missing.', [
                'chatId' => $chatId,
            ]);
            return null;
        }

        $photoPaths = array_values(array_filter($photoPaths, fn (string $path): bool => is_file($path)));

        if ($photoPaths === []) {
            $this->lastError = 'Telegram media group has no valid photo files.';
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
                $this->lastError = $this->formatTelegramError('sendMediaGroup', $this->telegramErrorFromResponse($response), $chatId);
                Log::error('Telegram sendMediaGroup failed.', [
                    'chatId' => $chatId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('result');
        } catch (\Throwable $e) {
            $this->lastError = $this->formatTelegramError('sendMediaGroup', $e->getMessage(), $chatId);
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

    private function sendCommonNotification(string $title, array $data = []): bool
    {
        return $this->sendConfiguredMessage($this->buildCommonNotificationMessage($title, $data), 'HTML');
    }

    private function buildCommonNotificationMessage(string $title, array $data = []): string
    {
        $lines = ['<b>' . $this->escapeHtml($title) . '</b>'];

        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            if ($value === null || $value === '') {
                continue;
            }

            $label = Str::headline((string) $key);
            $lines[] = $this->escapeHtml($label) . ': ' . $this->escapeHtml((string) $value);
        }

        return implode("\n", $lines);
    }

    private function configuredChatId(): ?string
    {
        $chatId = trim((string) config('services.telegram.chat_id', ''));

        if ($chatId !== '') {
            return $chatId;
        }

        return $this->getBroadcastChatIds(TelegramGroup::ACTION_GENERAL)[0] ?? null;
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function post(string $method, array $payload, array $context = []): ?Response
    {
        $botToken = TelegramBotSettings::botToken();

        if ($botToken === '') {
            $this->lastError = 'Telegram bot token is missing. Save the bot token first.';
            Log::error('Telegram bot token missing.', $context);
            return null;
        }

        $url = rtrim(self::TELEGRAM_API_BASE, '/') . '/bot' . $botToken . '/' . ltrim($method, '/');

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->acceptJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                $this->lastError = $this->formatTelegramError($method, $this->telegramErrorFromResponse($response), $payload['chat_id'] ?? null);
                Log::error('Telegram API request failed.', $context + [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            $response = $e->response;
            $this->lastError = $this->formatTelegramError(
                $method,
                $response ? $this->telegramErrorFromResponse($response) : $e->getMessage(),
                $payload['chat_id'] ?? null
            );
            Log::error('Telegram API request exception.', $context + [
                'method' => $method,
                'status' => $response?->status(),
                'body' => $response?->body(),
                'exception' => $e->getMessage(),
            ]);
            return null;
        } catch (\Throwable $e) {
            $this->lastError = $this->formatTelegramError($method, $e->getMessage(), $payload['chat_id'] ?? null);
            Log::error('Telegram API request exception.', $context + [
                'method' => $method,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function get(string $method, array $query = [], array $context = []): ?Response
    {
        $botToken = TelegramBotSettings::botToken();

        if ($botToken === '') {
            $this->lastError = 'Telegram bot token is missing. Save the bot token first.';
            Log::error('Telegram bot token missing.', $context);
            return null;
        }

        $url = rtrim(self::TELEGRAM_API_BASE, '/') . '/bot' . $botToken . '/' . ltrim($method, '/');

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->acceptJson()
                ->get($url, $query);

            if (! $response->successful()) {
                $this->lastError = $this->formatTelegramError($method, $this->telegramErrorFromResponse($response));
                Log::error('Telegram API request failed.', $context + [
                    'method' => $method,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            $response = $e->response;
            $this->lastError = $this->formatTelegramError(
                $method,
                $response ? $this->telegramErrorFromResponse($response) : $e->getMessage()
            );
            Log::error('Telegram API request exception.', $context + [
                'method' => $method,
                'status' => $response?->status(),
                'body' => $response?->body(),
                'exception' => $e->getMessage(),
            ]);
            return null;
        } catch (\Throwable $e) {
            $this->lastError = $this->formatTelegramError($method, $e->getMessage());
            Log::error('Telegram API request exception.', $context + [
                'method' => $method,
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function telegramErrorFromResponse(Response $response): string
    {
        $description = $response->json('description') ?: $response->json('message');

        if (is_string($description) && trim($description) !== '') {
            return trim($description);
        }

        $body = trim($response->body());

        return $body !== '' ? Str::limit($body, 300) : 'HTTP ' . $response->status();
    }

    private function formatTelegramError(string $method, string $detail, mixed $chatId = null): string
    {
        if (strtolower(trim($detail)) === 'unauthorized') {
            return 'Telegram bot token is invalid or unauthorized. Save a fresh bot token from @BotFather, then test again.';
        }

        $message = 'Telegram ' . $method . ' failed';
        $chatId = trim((string) $chatId);

        if ($chatId !== '') {
            $message .= ' for chat ID ' . $chatId;
        }

        return $message . ': ' . $detail;
    }
}
