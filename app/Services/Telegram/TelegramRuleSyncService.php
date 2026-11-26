<?php

namespace App\Services\Telegram;

use App\Models\Branch;
use App\Models\TelegramGroup;
use App\Models\TelegramNotificationRule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TelegramRuleSyncService
{
    /**
     * Generate default notification rules from existing Telegram channels/chat IDs.
     * Returns the count of rules created.
     */
    public static function syncDefaultRules(): int
    {
        if (!Schema::hasTable('telegram_groups') || !Schema::hasTable('telegram_notification_rules')) {
            return 0;
        }

        $createdCount = 0;
        $groups = TelegramGroup::all();

        if ($groups->isEmpty()) {
            return 0;
        }

        // 1. Group channels by event_key
        $eventChannelsMap = [];

        foreach ($groups as $group) {
            $eventKeys = is_array($group->event_keys) ? array_filter($group->event_keys) : [];
            if (empty($eventKeys) && !empty($group->action_key)) {
                $eventKeys = [$group->action_key];
            }

            foreach ($eventKeys as $eventKey) {
                $eventChannelsMap[$eventKey][] = [
                    'group' => $group,
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'branch_name' => $group->branch_name,
                    'branch_id' => $group->branch_id,
                    'send_for_all' => $group->send_for_all,
                ];
            }
        }

        $eventOptions = TelegramGroup::eventOptions();

        // 2. Generate or update rules for each detected event
        foreach ($eventChannelsMap as $eventKey => $items) {
            $channelIds = array_values(array_unique(array_column($items, 'group_id')));
            if (empty($channelIds)) {
                continue;
            }

            // Check if rule already exists for this event
            $existingRule = TelegramNotificationRule::where('event_key', $eventKey)->first();

            if (! $existingRule) {
                $eventLabel = $eventOptions[$eventKey] ?? Str::headline($eventKey);
                $ruleName = "វិធានការ៖ {$eventLabel}";

                // Determine branch scope
                $isAllBranches = true;
                $branchIds = [];
                foreach ($items as $item) {
                    if (!empty($item['branch_name']) && !$item['send_for_all']) {
                        $isAllBranches = false;
                        if (!empty($item['branch_id'])) {
                            $branchIds[] = (int) $item['branch_id'];
                        }
                    }
                }

                TelegramNotificationRule::create([
                    'name' => $ruleName,
                    'event_key' => $eventKey,
                    'event_keys' => [$eventKey],
                    'branch_scope' => $isAllBranches ? 'all' : 'specific',
                    'branch_ids' => !empty($branchIds) ? array_values(array_unique($branchIds)) : null,
                    'channel_ids' => $channelIds,
                    'send_location' => true,
                    'send_selfie' => true,
                    'is_silent' => false,
                    'is_active' => true,
                    'description' => "វិធានការស្វ័យប្រវត្តិបង្កើតចេញពី Chat IDs និង Channels ដែលមានស្រាប់។",
                ]);

                $createdCount++;
            }
        }

        // 3. Ensure Attendance Summary rule is configured
        if (! TelegramNotificationRule::where('event_key', TelegramGroup::EVENT_ATTENDANCE_SUMMARY)->exists()) {
            $managementGroup = TelegramGroup::where('name', 'like', '%Management%')
                ->orWhere('name', 'like', '%HQ%')
                ->first() ?: TelegramGroup::first();

            if ($managementGroup) {
                TelegramNotificationRule::create([
                    'name' => 'របាយការណ៍សង្ខេបវត្តមានប្រចាំថ្ងៃ (Daily Attendance Summary)',
                    'event_key' => TelegramGroup::EVENT_ATTENDANCE_SUMMARY,
                    'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_SUMMARY],
                    'branch_scope' => 'all',
                    'channel_ids' => [$managementGroup->id],
                    'send_location' => false,
                    'send_selfie' => false,
                    'is_silent' => false,
                    'is_active' => true,
                    'description' => 'របាយការណ៍វត្តមានសរុបប្រចាំថ្ងៃ ផ្ញើដោយស្វ័យប្រវត្តិនារៀងរាល់ព្រឹក ម៉ោង 09:30 AM។',
                ]);

                $createdCount++;
            }
        }

        return $createdCount;
    }
}
