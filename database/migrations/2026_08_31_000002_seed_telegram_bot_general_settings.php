<?php

use App\Support\TelegramBotSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ([
            TelegramBotSettings::BOT_TOKEN => (string) env('TELEGRAM_BOT_TOKEN', ''),
            TelegramBotSettings::BOT_USERNAME => '',
            TelegramBotSettings::WEBHOOK_SECRET => (string) env('TELEGRAM_WEBHOOK_SECRET', ''),
            TelegramBotSettings::CONNECT_LINK_VALIDITY_MINUTES => '60',
            TelegramBotSettings::WEBHOOK_REGISTERED_AT => '',
            TelegramBotSettings::WEBHOOK_REGISTERED_URL => '',
        ] as $key => $value) {
            DB::table('general_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'name' => ucwords(str_replace('_', ' ', str_replace('telegram_', '', $key))),
                    'type' => 'telegram',
                    'value' => $value,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('general_settings')
            ->whereIn('key', [
                TelegramBotSettings::BOT_TOKEN,
                TelegramBotSettings::BOT_USERNAME,
                TelegramBotSettings::WEBHOOK_SECRET,
                TelegramBotSettings::CONNECT_LINK_VALIDITY_MINUTES,
                TelegramBotSettings::WEBHOOK_REGISTERED_AT,
                TelegramBotSettings::WEBHOOK_REGISTERED_URL,
            ])
            ->delete();
    }
};
