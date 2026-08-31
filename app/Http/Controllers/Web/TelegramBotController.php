<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use App\Support\TelegramBotSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function index()
    {
        $settings = TelegramBotSettings::all();

        return view('admin.telegramBot.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_bot_username' => ['nullable', 'string', 'max:255'],
            'telegram_webhook_secret' => ['nullable', 'string', 'max:255'],
            'telegram_connect_link_validity_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
        ]);

        $settings = [
            TelegramBotSettings::BOT_USERNAME => ltrim(trim((string) ($data['telegram_bot_username'] ?? '')), '@'),
            TelegramBotSettings::WEBHOOK_SECRET => trim((string) ($data['telegram_webhook_secret'] ?? '')),
            TelegramBotSettings::CONNECT_LINK_VALIDITY_MINUTES => (int) $data['telegram_connect_link_validity_minutes'],
        ];

        $botToken = trim((string) ($data['telegram_bot_token'] ?? ''));
        if ($botToken !== '') {
            $settings[TelegramBotSettings::BOT_TOKEN] = $botToken;
        }

        TelegramBotSettings::putMany($settings);

        return back()->with('success', 'Telegram bot settings saved.');
    }

    public function testConnection(TelegramService $telegramService): RedirectResponse
    {
        $response = $telegramService->getMe();

        if (! is_array($response) || ($response['ok'] ?? false) !== true) {
            return back()->with(
                'danger',
                'Telegram bot token test failed. ' . ($telegramService->lastError() ?: 'Check the saved token and server logs.')
            );
        }

        $bot = $response['result'] ?? [];
        $username = $bot['username'] ?? 'unknown';

        return back()->with('success', "Telegram bot token is working. Bot: @{$username}");
    }

    public function registerWebhook(TelegramService $telegramService): RedirectResponse
    {
        $url = TelegramBotSettings::webhookUrl();

        if (! str_starts_with($url, 'https://')) {
            return back()->with('danger', 'Telegram webhooks require a public HTTPS URL.');
        }

        if (! $telegramService->setWebhook($url, TelegramBotSettings::webhookSecret())) {
            return back()->with(
                'danger',
                'Webhook registration failed. ' . ($telegramService->lastError() ?: 'Check the saved bot token and server logs.')
            );
        }

        TelegramBotSettings::putMany([
            TelegramBotSettings::WEBHOOK_REGISTERED_AT => now()->format('Y-m-d H:i:s'),
            TelegramBotSettings::WEBHOOK_REGISTERED_URL => $url,
        ]);

        return back()->with('success', 'Telegram webhook registered successfully.');
    }
}
