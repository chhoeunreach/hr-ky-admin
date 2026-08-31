<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
use Illuminate\Console\Command;

class ManageTelegramWebhook extends Command
{
    protected $signature = 'telegram:webhook
        {action=info : set, info, or delete}
        {--url= : Public HTTPS webhook URL. Defaults to APP_URL/telegram/webhook}
        {--drop-pending-updates : Ask Telegram to drop pending updates}';

    protected $description = 'Manage the Telegram bot webhook';

    public function handle(TelegramService $telegramService): int
    {
        $action = strtolower((string) $this->argument('action'));
        $dropPendingUpdates = (bool) $this->option('drop-pending-updates');

        return match ($action) {
            'set' => $this->setWebhook($telegramService, $dropPendingUpdates),
            'delete' => $this->deleteWebhook($telegramService, $dropPendingUpdates),
            'info' => $this->showWebhookInfo($telegramService),
            default => $this->failWithMessage('Invalid action. Use set, info, or delete.'),
        };
    }

    private function setWebhook(TelegramService $telegramService, bool $dropPendingUpdates): int
    {
        $url = trim((string) ($this->option('url') ?: route('telegram.webhook')));

        if (! str_starts_with($url, 'https://')) {
            return $this->failWithMessage('Telegram webhooks require a public HTTPS URL.');
        }

        $ok = $telegramService->setWebhook(
            $url,
            TelegramBotSettings::webhookSecret(),
            $dropPendingUpdates
        );

        if (! $ok) {
            return $this->failWithMessage('Telegram webhook setup failed. Check TELEGRAM_BOT_TOKEN and storage/logs/laravel.log.');
        }

        $this->info('Telegram webhook set successfully.');
        $this->line($url);

        return self::SUCCESS;
    }

    private function deleteWebhook(TelegramService $telegramService, bool $dropPendingUpdates): int
    {
        if (! $telegramService->deleteWebhook($dropPendingUpdates)) {
            return $this->failWithMessage('Telegram webhook delete failed. Check TELEGRAM_BOT_TOKEN and storage/logs/laravel.log.');
        }

        $this->info('Telegram webhook deleted successfully.');

        return self::SUCCESS;
    }

    private function showWebhookInfo(TelegramService $telegramService): int
    {
        $info = $telegramService->getWebhookInfo();

        if (! is_array($info)) {
            return $this->failWithMessage('Unable to read Telegram webhook info. Check TELEGRAM_BOT_TOKEN and storage/logs/laravel.log.');
        }

        $result = $info['result'] ?? [];

        $this->info('Telegram webhook info');
        $this->line('URL: ' . ($result['url'] ?? '(not set)'));
        $this->line('Pending updates: ' . ($result['pending_update_count'] ?? 0));

        if (! empty($result['last_error_message'])) {
            $this->warn('Last error: ' . $result['last_error_message']);
        }

        return self::SUCCESS;
    }

    private function failWithMessage(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
