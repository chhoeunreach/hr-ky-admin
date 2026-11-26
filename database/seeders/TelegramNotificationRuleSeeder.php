<?php

namespace Database\Seeders;

use App\Services\Telegram\TelegramRuleSyncService;
use Illuminate\Database\Seeder;

class TelegramNotificationRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure telegram groups (channels) exist
        $this->call(TelegramGroupSeeder::class);

        // 2. Generate default notification rules from existing groups and chat IDs
        TelegramRuleSyncService::syncDefaultRules();
    }
}
