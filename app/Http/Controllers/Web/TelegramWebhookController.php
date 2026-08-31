<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
            'message.chat' => ['nullable', 'array'],
            'message.chat.id' => ['nullable'],
            'message.chat.type' => ['nullable', 'string'],
            'message.chat.title' => ['nullable', 'string'],
            'message.from' => ['nullable', 'array'],
            'message.from.username' => ['nullable', 'string'],
        ]);

        $message = $update['message'] ?? [];
        $text = trim((string) ($message['text'] ?? ''));
        $chatId = isset($message['chat']['id']) ? (string) $message['chat']['id'] : '';

        if ($chatId === '' || $text === '') {
            return response()->json(['ok' => true]);
        }

        $reply = $this->replyForCommand($text, $chatId, (array) ($message['chat'] ?? []), (array) ($message['from'] ?? []));
        if ($reply === null) {
            return response()->json(['ok' => true]);
        }

        if (! $telegramService->sendMessage($chatId, $reply)) {
            Log::warning('Telegram webhook command reply failed.', [
                'update_id' => $update['update_id'],
                'chat_id' => $chatId,
            ]);
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
            '/help' => "Available commands:\n/start - Start the bot\n/help - Show commands\n/status - Check bot status\n/chatid - Show this chat ID\n/link EMPLOYEE_CODE - Link this chat to your employee profile\n/unlink - Remove this chat from your employee profile",
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
