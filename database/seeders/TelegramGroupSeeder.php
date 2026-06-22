<?php

namespace Database\Seeders;

use App\Models\TelegramGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TelegramGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $telegramGroups = [
            [
                'name' => 'Management',
                'chat_id' => '-1002799577548',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'department_name' => 'management',
                'description' => 'Legacy attendance routing for Management department.',
            ],
            [
                'name' => 'ជាង',
                'chat_id' => '-1002842364173',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'department_name' => 'ជាង',
                'description' => 'Legacy attendance routing for ជាង department.',
            ],
            [
                'name' => 'អ៊ីអន',
                'chat_id' => '-1002705869028',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'អ៊ីអន',
                'description' => 'Legacy attendance routing for អ៊ីអន branch.',
            ],
            [
                'name' => 'កាប់គោ',
                'chat_id' => '-1002351902820',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'កាប់គោ',
                'description' => 'Legacy attendance routing for កាប់គោ branch.',
            ],
            [
                'name' => 'ស្តុកធំ',
                'chat_id' => '-1002509454514',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'ស្តុកធំ',
                'description' => 'Legacy attendance routing for ស្តុកធំ branch.',
            ],
            [
                'name' => 'វីអាយភី',
                'chat_id' => '-1002806714995',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'វីអាយភី',
                'description' => 'Legacy attendance routing for វីអាយភី branch.',
            ],
            [
                'name' => 'កម្ពុជាក្រោម - Media KY',
                'chat_id' => '-1002727901053',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'កម្ពុជាក្រោម',
                'department_name' => 'មេឌៀ(KY)',
                'description' => 'Legacy attendance routing for កម្ពុជាក្រោម មេឌៀ(KY).',
            ],
            [
                'name' => 'កម្ពុជាក្រោម - Online Seller KY',
                'chat_id' => '-1002727901053',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'កម្ពុជាក្រោម',
                'department_name' => 'អ្នកលក់អនឡាញ(KY)',
                'description' => 'Legacy attendance routing for កម្ពុជាក្រោម អ្នកលក់អនឡាញ(KY).',
            ],
            [
                'name' => 'កម្ពុជាក្រោម - General',
                'chat_id' => '-1002617998738',
                'action_key' => TelegramGroup::ACTION_ATTENDANCE,
                'event_keys' => [TelegramGroup::EVENT_ATTENDANCE_CHECKIN, TelegramGroup::EVENT_ATTENDANCE_CHECKOUT],
                'branch_name' => 'កម្ពុជាក្រោម',
                'description' => 'Legacy attendance routing for other កម្ពុជាក្រោម departments.',
            ],
            [
                'name' => 'Advance Salary Approved',
                'chat_id' => '-1002000139489',
                'action_key' => TelegramGroup::ACTION_ADVANCE_SALARY_APPROVED,
                'event_keys' => [TelegramGroup::EVENT_ADVANCE_SALARY_APPROVED],
                'send_for_all' => true,
                'description' => 'Legacy advance salary approved notification group.',
            ],
            [
                'name' => 'Leave Default',
                'chat_id' => '-1002727901053',
                'action_key' => TelegramGroup::ACTION_LEAVE,
                'event_keys' => [TelegramGroup::EVENT_LEAVE_REQUEST, TelegramGroup::EVENT_LEAVE_APPROVED, TelegramGroup::EVENT_LEAVE_REJECTED, TelegramGroup::EVENT_TIME_LEAVE_REQUEST],
                'send_for_all' => true,
                'description' => 'Legacy leave notification recipient.',
            ],
            [
                'name' => 'Advance Salary Request',
                'chat_id' => '-1003389081526',
                'action_key' => TelegramGroup::ACTION_ADVANCE_SALARY_REQUEST,
                'event_keys' => [TelegramGroup::EVENT_ADVANCE_SALARY_REQUEST],
                'send_for_all' => true,
                'description' => 'Legacy advance salary request notification group.',
            ],
            [
                'name' => 'New Employee - Management',
                'chat_id' => '-1002799577548',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - ជាង',
                'chat_id' => '-1002842364173',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - អ៊ីអន',
                'chat_id' => '-1002705869028',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - កាប់គោ',
                'chat_id' => '-1002351902820',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - ស្តុកធំ',
                'chat_id' => '-1002509454514',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - វីអាយភី',
                'chat_id' => '-1002806714995',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - កម្ពុជាក្រោម Media/Online',
                'chat_id' => '-1002727901053',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'New Employee - កម្ពុជាក្រោម General',
                'chat_id' => '-1002617998738',
                'action_key' => TelegramGroup::ACTION_NEW_EMPLOYEE,
                'event_keys' => [TelegramGroup::EVENT_NEW_EMPLOYEE],
                'send_for_all' => true,
                'description' => 'Legacy new employee broadcast recipient.',
            ],
            [
                'name' => 'Sell Out Report',
                'chat_id' => '-1002727901053',
                'action_key' => TelegramGroup::EVENT_SELL_OUT_REPORT,
                'event_keys' => array_merge(
                    [TelegramGroup::EVENT_SELL_OUT_REPORT],
                    array_keys(TelegramGroup::sellOutEventOptions())
                ),
                'send_for_all' => true,
                'description' => 'Sell out report notification recipient.',
            ],
        ];

        foreach ($telegramGroups as $telegramGroup) {
            DB::table('telegram_groups')->updateOrInsert(
                [
                    'chat_id' => $telegramGroup['chat_id'],
                    'action_key' => $telegramGroup['action_key'],
                    'branch_name' => $telegramGroup['branch_name'] ?? null,
                    'department_name' => $telegramGroup['department_name'] ?? null,
                ],
                array_merge([
                    'send_for_all' => false,
                    'is_active' => true,
                    'updated_at' => $now,
                ], $telegramGroup, [
                    'event_keys' => json_encode($telegramGroup['event_keys'] ?? [$telegramGroup['action_key']]),
                    'created_at' => $now,
                ])
            );
        }
    }
}
