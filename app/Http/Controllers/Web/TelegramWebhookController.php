<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramService $telegramService): JsonResponse
    {
        if (! $this->hasValidSecret($request)) {
            return response()->json(['ok' => false], 403);
        }

        $update = $request->validate([
            'update_id' => ['required', 'integer'],
            'message' => ['nullable', 'array'],
            'message.message_id' => ['nullable', 'integer'],
            'message.text' => ['nullable', 'string', 'max:4096'],
            'message.caption' => ['nullable', 'string', 'max:4096'],
            'message.chat' => ['nullable', 'array'],
            'message.chat.id' => ['nullable'],
            'message.chat.type' => ['nullable', 'string'],
            'message.chat.title' => ['nullable', 'string'],
            'message.from' => ['nullable', 'array'],
            'message.from.username' => ['nullable', 'string'],
            'message.photo' => ['nullable', 'array'],
            'message.photo.*.file_id' => ['nullable', 'string'],
            'message.photo.*.file_unique_id' => ['nullable', 'string'],
            'message.photo.*.file_size' => ['nullable', 'integer'],
            'message.document' => ['nullable', 'array'],
            'message.document.file_id' => ['nullable', 'string'],
            'message.document.file_name' => ['nullable', 'string'],
            'message.document.mime_type' => ['nullable', 'string'],
            'message.voice' => ['nullable', 'array'],
            'message.voice.file_id' => ['nullable', 'string'],
            'message.voice.mime_type' => ['nullable', 'string'],
            'message.location' => ['nullable', 'array'],
            'message.location.latitude' => ['nullable', 'numeric'],
            'message.location.longitude' => ['nullable', 'numeric'],
        ]);

        $message = $update['message'] ?? [];
        $text = trim((string) ($message['text'] ?? ''));
        $chatId = isset($message['chat']['id']) ? (string) $message['chat']['id'] : '';

        if ($chatId === '') {
            return response()->json(['ok' => true]);
        }

        if ($text !== '') {
            $reply = $this->replyForCommand($text, $chatId, (array) ($message['chat'] ?? []), (array) ($message['from'] ?? []));

            if ($reply !== null) {
                if (! $telegramService->sendMessage($chatId, $reply)) {
                    Log::warning('Telegram webhook command reply failed.', [
                        'update_id' => $update['update_id'],
                        'chat_id' => $chatId,
                    ]);
                }

                return response()->json(['ok' => true]);
            }

            $this->persistInboundMessage($chatId, $text, null);

            return response()->json(['ok' => true]);
        }

        if ($this->persistInboundMedia($chatId, $message, $telegramService)) {
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }

    private function hasValidSecret(Request $request): bool
    {
        $secret = TelegramBotSettings::webhookSecret();

        if ($secret === '') {
            return true;
        }

        $providedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        return $providedSecret !== '' && hash_equals($secret, $providedSecret);
    }

    /**
     * Persist a regular (non-command) text message sent by an employee via
     * Telegram into the Live Chat threads so it appears in the admin console.
     */
    private function persistInboundMessage(string $chatId, string $text, ?array $media): void
    {
        $employee = User::query()
            ->where('status', 'verified')
            ->where('telegram_chat_id', $chatId)
            ->first();

        if (! $employee) {
            return;
        }

        $this->persistMessageToThreads(
            $employee->id,
            $media['type'] ?? ChatMessage::TYPE_TEXT,
            $text,
            $media['media_url'] ?? null,
            null,
            null,
            $chatId
        );
    }

    /**
     * Persist an inbound media message (photo, document, voice, location)
     * received from a linked employee via Telegram.
     */
    private function persistInboundMedia(string $chatId, array $message, TelegramService $telegramService): bool
    {
        $employee = User::query()
            ->where('status', 'verified')
            ->where('telegram_chat_id', $chatId)
            ->first();

        if (! $employee) {
            return false;
        }

        if (! empty($message['location']) && isset($message['location']['latitude'], $message['location']['longitude'])) {
            $latitude = round((float) $message['location']['latitude'], 7);
            $longitude = round((float) $message['location']['longitude'], 7);

            $this->persistMessageToThreads($employee->id, ChatMessage::TYPE_LOCATION, 'Sent a location', null, $latitude, $longitude, $chatId);

            return true;
        }

        $file = $this->resolveInboundFile($message, $telegramService);

        if ($file === null) {
            return false;
        }

        $this->persistMessageToThreads($employee->id, $file['type'], $file['text'], $file['media_url'], null, null, $chatId);

        return true;
    }

    /**
     * Store the inbound Telegram message into every admin-scoped conversation
     * for the employee (and the legacy user-only thread), so that whichever
     * admin opens the employee's chat sees the reply as an unread message.
     */
    private function persistMessageToThreads(int $employeeId, string $type, string $text, ?string $mediaUrl, ?float $latitude, ?float $longitude, string $chatId): void
    {
        @file_put_contents('D:/htdocs/hr-ky-admin1 - Copy/_dbg.log', date('c')." START persistMessageToThreads emp=$employeeId type=$type chat=$chatId\n", FILE_APPEND);

        $existingAdminIds = ChatConversation::query()
            ->where('user_id', $employeeId)
            ->whereNotNull('admin_id')
            ->distinct()
            ->pluck('admin_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        @file_put_contents('D:/htdocs/hr-ky-admin1 - Copy/_dbg.log', date('c')." existingAdminIds=".json_encode($existingAdminIds)."\n", FILE_APPEND);

        $adminIds = $existingAdminIds !== []
            ? $existingAdminIds
            : \App\Models\Admin::query()->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        @file_put_contents('D:/htdocs/hr-ky-admin1 - Copy/_dbg.log', date('c')." adminIds=".json_encode($adminIds)."\n", FILE_APPEND);

        $saved = false;

        foreach ($adminIds as $adminId) {
            try {
                $conversation = ChatConversation::firstOrCreate([
                    'user_id' => $employeeId,
                    'admin_id' => $adminId,
                ]);

                @file_put_contents('D:/htdocs/hr-ky-admin1 - Copy/_dbg.log', date('c')." conviction=$conversation->id admin=$adminId\n", FILE_APPEND);

                $meta = [
                    'admin_id' => $adminId,
                    'external_conversation_id' => 'employee_admin_' . $employeeId . '_' . $adminId,
                    'telegram_status' => 'received',
                    'channel' => 'telegram',
                    'media_url' => $mediaUrl,
                ];

                $message = $this->createInboundMessage($conversation, $employeeId, $type, $text, $mediaUrl, $latitude, $longitude, array_filter($meta, fn ($value) => $value !== null));

                @file_put_contents('D:/htdocs/hr-ky-admin1 - Copy/_dbg.log', date('c')." message=".($message?->id ?? 'NULL')."\n", FILE_APPEND);

                if ($message) {
                    $conversation->update(['last_message_at' => $message->created_at]);
                    $saved = true;
                }
            } catch (\Throwable $throwable) {
                @file_put_contents('D:/htdocs/hr-ky-admin1 - Copy/_dbg.log', date('c')." EXC admin=$adminId ".$throwable->getMessage()."\n".$throwable->getTraceAsString()."\n", FILE_APPEND);
                Log::error('Failed to persist inbound Telegram message.', [
                    'chat_id' => $chatId,
                    'employee_id' => $employeeId,
                    'admin_id' => $adminId,
                    'error' => $throwable->getMessage(),
                ]);
            }
        }

        $legacyConversation = ChatConversation::query()
            ->where('user_id', $employeeId)
            ->whereNull('admin_id')
            ->whereNull('peer_user_id')
            ->orderByDesc('last_message_at')
            ->first();

        if ($legacyConversation && $adminIds === []) {
            try {
                $meta = [
                    'telegram_status' => 'received',
                    'channel' => 'telegram',
                    'media_url' => $mediaUrl,
                ];

                $message = $this->createInboundMessage($legacyConversation, $employeeId, $type, $text, $mediaUrl, $latitude, $longitude, array_filter($meta, fn ($value) => $value !== null));

                if ($message) {
                    $legacyConversation->update(['last_message_at' => $message->created_at]);
                }
            } catch (\Throwable $throwable) {
                Log::error('Failed to persist inbound Telegram message to legacy thread.', [
                    'chat_id' => $chatId,
                    'employee_id' => $employeeId,
                    'error' => $throwable->getMessage(),
                ]);
            }
        }

        if (! $saved && $adminIds === []) {
            Log::warning('No admin thread available for inbound Telegram message.', [
                'chat_id' => $chatId,
                'employee_id' => $employeeId,
            ]);
        }
    }

    private function createInboundMessage(ChatConversation $conversation, int $employeeId, string $type, string $text, ?string $mediaUrl, ?float $latitude, ?float $longitude, array $meta): ?ChatMessage
    {
        $attributes = [
            'sender_type' => ChatMessage::SENDER_USER,
            'sender_id' => $employeeId,
            'message_type' => $type,
            'meta' => $meta,
            'is_read_by_admin' => false,
            'is_read_by_user' => true,
        ];

        if (trim($text) !== '') {
            $attributes['message'] = $text;
        }

        if ($mediaUrl !== null) {
            $attributes['media_url'] = $mediaUrl;
        }

        if ($latitude !== null && $longitude !== null) {
            $attributes['latitude'] = $latitude;
            $attributes['longitude'] = $longitude;
        }

        return $conversation->messages()->create($attributes);
    }

    /**
     * Determine the best file (photo/document/voice) from a Telegram message,
     * download it, and return the resolved type + storage path.
     */
    private function resolveInboundFile(array $message, TelegramService $telegramService): ?array
    {
        if (! empty($message['photo'])) {
            $photos = $message['photo'];
            $fileId = $photos[count($photos) - 1]['file_id'] ?? null;
            $type = ChatMessage::TYPE_IMAGE;
            $text = trim((string) ($message['caption'] ?? ''));
            $mime = null;
        } elseif (! empty($message['document']) && isset($message['document']['file_id'])) {
            $fileId = $message['document']['file_id'];
            $type = ChatMessage::TYPE_FILE;
            $text = trim((string) ($message['caption'] ?? ''));
            $mime = $message['document']['mime_type'] ?? null;
        } elseif (! empty($message['voice']) && isset($message['voice']['file_id'])) {
            $fileId = $message['voice']['file_id'];
            $type = ChatMessage::TYPE_VOICE;
            $text = trim((string) ($message['caption'] ?? ''));
            $mime = $message['voice']['mime_type'] ?? null;
        } else {
            return null;
        }

        $extension = $this->guessExtension($mime, $fileId);
        $uniq = uniqid('', true);
        $targetPath = 'chat/inbound/' . $uniq . '.' . $extension;

        $storedPath = $telegramService->downloadInboundFile($fileId, $targetPath);

        if ($storedPath === null) {
            return null;
        }

        return [
            'type' => $type,
            'text' => $text,
            'media_url' => 'storage/' . $storedPath,
        ];
    }

    private function guessExtension(?string $mime, string $fileId): string
    {
        if ($mime) {
            return match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'audio/ogg' => 'ogg',
                'audio/mpeg' => 'mp3',
                default => 'bin',
            };
        }

        $ext = pathinfo($fileId, PATHINFO_EXTENSION);

        return $ext !== '' ? $ext : 'bin';
    }

    private function replyForCommand(string $text, string $chatId, array $chat, array $from): ?string
    {
        $command = strtok($text, " \n\t") ?: '';
        $command = strtolower(strtok($command, '@') ?: $command);

        $chatType = (string) ($chat['type'] ?? 'unknown');
        $chatTitle = trim((string) ($chat['title'] ?? ''));
        $chatLabel = $chatTitle !== '' ? "{$chatTitle} ({$chatType})" : $chatType;

        if ($command === '/link') {
            return $this->linkEmployee($text, $chatId, $from);
        }

        if ($command === '/unlink') {
            return $this->unlinkEmployee($chatId);
        }

        return match ($command) {
            '/start' => $this->startReply($text, $chatId, $from),
            '/help' => "Available commands:\n/start - Start the bot\n/help - Show commands\n/status - Check bot status\n/chatid - Show this chat ID\n/link EMPLOYEE_CODE - Link this chat to your employee profile\n/unlink - Remove this chat from your employee profile\n\nTip: You can also send a normal message to chat with HR.",
            '/status' => "Telegram bot webhook is online.\nChat: {$chatLabel}\nChat ID: {$chatId}",
            '/chatid' => "Chat ID: {$chatId}",
            default => null,
        };
    }

    private function startReply(string $text, string $chatId, array $from): string
    {
        $parts = preg_split('/\s+/', trim($text), 2);
        $payload = trim((string) ($parts[1] ?? ''));

        if ($payload !== '') {
            $employee = TelegramBotSettings::employeeFromConnectPayload($payload);

            if (! $employee) {
                return 'This Telegram connect link is invalid or expired. Please request a new link from HR.';
            }

            $employee->update([
                'telegram_chat_id' => $chatId,
                'telegram_username' => trim((string) ($from['username'] ?? '')) ?: $employee->telegram_username,
                'telegram_linked_at' => now(),
            ]);

            return 'Telegram linked successfully for ' . $employee->name . '.';
        }

        return 'Telegram bot is connected to HR Admin. Send /help for available commands.';
    }

    private function linkEmployee(string $text, string $chatId, array $from): string
    {
        $parts = preg_split('/\s+/', trim($text), 2);
        $employeeCode = trim((string) ($parts[1] ?? ''));

        if ($employeeCode === '') {
            return 'Please send /link followed by your employee code. Example: /link EMP001';
        }

        $employee = User::query()
            ->where('status', 'verified')
            ->where(function ($query) use ($employeeCode) {
                $query->where('employee_code', $employeeCode)
                    ->orWhere('username', $employeeCode);
            })
            ->first();

        if (! $employee) {
            return 'Employee code not found. Please check your code or contact HR.';
        }

        $employee->update([
            'telegram_chat_id' => $chatId,
            'telegram_username' => trim((string) ($from['username'] ?? '')) ?: $employee->telegram_username,
            'telegram_linked_at' => now(),
        ]);

        return 'Telegram linked successfully for ' . $employee->name . '.';
    }

    private function unlinkEmployee(string $chatId): string
    {
        $employee = User::query()
            ->where('telegram_chat_id', $chatId)
            ->first();

        if (! $employee) {
            return 'This chat is not linked to an employee profile.';
        }

        $employee->update([
            'telegram_chat_id' => null,
            'telegram_linked_at' => null,
        ]);

        return 'Telegram unlinked successfully.';
    }
}
