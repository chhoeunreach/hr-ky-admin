<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $features = [
        [
            'group' => 'Attendance',
            'name' => 'Attendance',
            'key' => 'attendance',
            'status' => 1,
        ],
        [
            'group' => 'Office Desk',
            'name' => 'Leave Request',
            'key' => 'leave-request',
            'status' => 1,
        ],
        [
            'group' => 'Office Desk',
            'name' => 'Time Leave',
            'key' => 'time-leave',
            'status' => 1,
        ],
        [
            'group' => 'Office Desk',
            'name' => 'Notice',
            'key' => 'notice',
            'status' => 1,
        ],
        [
            'group' => 'Office Desk',
            'name' => 'Holiday',
            'key' => 'holiday',
            'status' => 1,
        ],
        [
            'group' => 'Office Desk',
            'name' => 'Sell Staff Report',
            'key' => 'sell-staff-report',
            'status' => 1,
        ],
    ];

    public function up(): void
    {
        foreach ($this->features as $feature) {
            $existingFeature = DB::table('features')
                ->where('key', $feature['key'])
                ->first();

            if ($existingFeature) {
                DB::table('features')
                    ->where('key', $feature['key'])
                    ->update([
                        'group' => $feature['group'],
                        'name' => $feature['name'],
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('features')->insert([
                'group' => $feature['group'],
                'name' => $feature['name'],
                'key' => $feature['key'],
                'status' => $feature['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('features')
            ->whereIn('key', array_column($this->features, 'key'))
            ->delete();
    }
};
