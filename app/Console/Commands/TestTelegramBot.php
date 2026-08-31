<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramBot extends Command
{
    protected $signature = 'telegram:test
        {--html : Send the test message with Telegram HTML parse mode}
        {--chat-id= : Send directly to a Telegram chat ID}
        {--action= : Route through a Telegram group action/event key}
        {--branch= : Branch name for routed messages}
        {--department= : Department name for routed messages}';

    protected $description = 'Send a safe Telegram test message';

    public function handle(TelegramService $telegramService): int
    {
        $parseMode = $this->option('html') ? 'HTML' : null;
        $message = $parseMode === 'HTML'
            ? '<b>HR Admin Telegram test</b>' . "\n" . 'The Telegram bot connection is working.'
            : 'HR Admin Telegram test' . "\n" . 'The Telegram bot connection is working.';

        $chatId = trim((string) $this->option('chat-id'));
        $actionKey = trim((string) $this->option('action'));

        if ($chatId !== '') {
            $ok = $telegramService->sendMessage($chatId, $message, $parseMode);
        } elseif ($actionKey !== '') {
            $ok = $telegramService->sendToAction(
                $actionKey,
                $message,
                $parseMode,
                (string) $this->option('branch'),
                (string) $this->option('department')
            );
        } else {
            $ok = $telegramService->sendConfiguredMessage($message, $parseMode);
        }

        if (! $ok) {
            $this->error('Telegram test failed. ' . ($telegramService->lastError() ?: 'Check the saved bot token, chat routing, and storage/logs/laravel.log.'));

            return self::FAILURE;
        }

        $this->info('Telegram test message sent successfully.');

        return self::SUCCESS;
    }
}
