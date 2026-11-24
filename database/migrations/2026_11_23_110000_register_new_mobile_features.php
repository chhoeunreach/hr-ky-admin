<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure assets feature is enabled
        if (Schema::hasTable('features')) {
            DB::table('features')
                ->where('key', 'assets')
                ->update(['status' => 1, 'updated_at' => now()]);

            $newFeatures = [
                [
                    'group' => 'Office Desk',
                    'name' => 'Store Phone & SIM',
                    'key' => 'store-phone-numbers',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'Office Desk',
                    'name' => 'Company Documents',
                    'key' => 'company-documents',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'Finance',
                    'name' => 'Payday Queue',
                    'key' => 'payroll-queue',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'group' => 'Office Desk',
                    'name' => 'Certificates',
                    'key' => 'certificate',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($newFeatures as $feature) {
                $exists = DB::table('features')->where('key', $feature['key'])->exists();
                if (!$exists) {
                    DB::table('features')->insert($feature);
                } else {
                    DB::table('features')->where('key', $feature['key'])->update([
                        'status' => 1,
                        'name' => $feature['name'],
                        'group' => $feature['group'],
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 2. Ensure mobile_feature_controls has all these enabled
        if (Schema::hasTable('mobile_feature_controls')) {
            $keys = ['assets', 'store-phone-numbers', 'company-documents', 'payroll-queue', 'certificate'];
            foreach ($keys as $k) {
                DB::table('mobile_feature_controls')->updateOrInsert(
                    ['key' => $k],
                    ['enabled' => 1]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('features')) {
            DB::table('features')
                ->whereIn('key', ['store-phone-numbers', 'company-documents', 'payroll-queue', 'certificate'])
                ->delete();
        }

        if (Schema::hasTable('mobile_feature_controls')) {
            DB::table('mobile_feature_controls')
                ->whereIn('key', ['store-phone-numbers', 'company-documents', 'payroll-queue', 'certificate'])
                ->delete();
        }
    }
};
