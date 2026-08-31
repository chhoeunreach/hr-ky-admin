<?php

namespace App\Support;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class TelegramBotSettings
{
    public const BOT_TOKEN = 'telegram_bot_token';
    public const BOT_USERNAME = 'telegram_bot_username';
    public const WEBHOOK_SECRET = 'telegram_webhook_secret';
    public const CONNECT_LINK_VALIDITY_MINUTES = 'telegram_connect_link_validity_minutes';
    public const WEBHOOK_REGISTERED_AT = 'telegram_webhook_registered_at';
    public const WEBHOOK_REGISTERED_URL = 'telegram_webhook_registered_url';

    public static function all(): array
    {
        self::ensureDefaultsExist();

        $botToken = self::botToken();

        return [
            self::BOT_TOKEN => $botToken,
            self::BOT_USERNAME => self::get(self::BOT_USERNAME, ''),
            self::WEBHOOK_SECRET => self::get(self::WEBHOOK_SECRET, (string) config('services.telegram.webhook_secret', '')),
            self::CONNECT_LINK_VALIDITY_MINUTES => (int) self::get(self::CONNECT_LINK_VALIDITY_MINUTES, 60),
            self::WEBHOOK_REGISTERED_AT => self::get(self::WEBHOOK_REGISTERED_AT, ''),
            self::WEBHOOK_REGISTERED_URL => self::get(self::WEBHOOK_REGISTERED_URL, ''),
            'webhook_url' => self::webhookUrl(),
            'bot_token_saved' => $botToken !== '',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = GeneralSetting::query()->where('key', $key)->value('value');

        return $value !== null ? $value : $default;
    }

    public static function putMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            GeneralSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name' => Str::headline(str_replace('telegram_', '', $key)),
                    'type' => 'telegram',
                    'value' => (string) $value,
                ]
            );
        }
    }

    public static function webhookSecret(): string
    {
        return trim((string) self::get(self::WEBHOOK_SECRET, config('services.telegram.webhook_secret', '')));
    }

    public static function botToken(): string
    {
        self::ensureBotTokenExists();

        return trim((string) self::get(self::BOT_TOKEN, config('services.telegram.bot_token', '')));
    }

    public static function ensureDefaultsExist(): void
    {
        $defaults = [
            self::BOT_TOKEN => (string) config('services.telegram.bot_token', ''),
            self::BOT_USERNAME => '',
            self::WEBHOOK_SECRET => (string) config('services.telegram.webhook_secret', ''),
            self::CONNECT_LINK_VALIDITY_MINUTES => '60',
            self::WEBHOOK_REGISTERED_AT => '',
            self::WEBHOOK_REGISTERED_URL => '',
        ];

        foreach ($defaults as $key => $default) {
            if (GeneralSetting::query()->where('key', $key)->exists()) {
                continue;
            }

            GeneralSetting::query()->create([
                'name' => Str::headline(str_replace('telegram_', '', $key)),
                'type' => 'telegram',
                'value' => $default,
            ]);
        }
    }

    public static function ensureBotTokenExists(): void
    {
        $savedToken = trim((string) GeneralSetting::query()
            ->where('key', self::BOT_TOKEN)
            ->value('value'));

        if ($savedToken !== '') {
            return;
        }

        $envToken = trim((string) config('services.telegram.bot_token', ''));

        if ($envToken === '') {
            return;
        }

        GeneralSetting::query()->updateOrCreate(
            ['key' => self::BOT_TOKEN],
            [
                'name' => Str::headline(str_replace('telegram_', '', self::BOT_TOKEN)),
                'type' => 'telegram',
                'value' => $envToken,
            ]
        );
    }

    public static function connectLinkValidityMinutes(): int
    {
        return max(1, (int) self::get(self::CONNECT_LINK_VALIDITY_MINUTES, 60));
    }

    public static function webhookUrl(): string
    {
        return Route::has('telegram.webhook') ? route('telegram.webhook') : url('/telegram/webhook');
    }

    public static function connectUrl(User $employee): ?string
    {
        $botUsername = trim((string) self::get(self::BOT_USERNAME, ''));

        if ($botUsername === '') {
            return null;
        }

        return 'https://t.me/' . ltrim($botUsername, '@') . '?start=' . self::connectPayload($employee);
    }

    public static function connectPayload(User $employee): string
    {
        $expiresAt = now()->addMinutes(self::connectLinkValidityMinutes())->timestamp;
        $data = 'e' . $employee->id . '_' . $expiresAt;

        return $data . '_' . self::signature($data);
    }

    public static function employeeFromConnectPayload(string $payload): ?User
    {
        if (! preg_match('/^e(\d+)_(\d+)_([a-f0-9]{16})$/', $payload, $matches)) {
            return null;
        }

        $data = 'e' . $matches[1] . '_' . $matches[2];
        if (! hash_equals(self::signature($data), $matches[3])) {
            return null;
        }

        if ((int) $matches[2] < now()->timestamp) {
            return null;
        }

        return User::query()
            ->where('status', 'verified')
            ->find((int) $matches[1]);
    }

    private static function signature(string $data): string
    {
        return substr(hash_hmac('sha256', $data, (string) config('app.key')), 0, 16);
    }
}
