<?php

use App\Models\AppSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('app_settings') && Schema::hasColumn('app_settings', 'value')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->text('value')->nullable()->change();
            });
        }

        // Initialize default app version setting if not already present
        if (Schema::hasTable('app_settings')) {
            $defaultSettings = [
                'target_version' => '13.00',
                'min_version' => '13.00',
                'force_update' => false,
                'alert_title' => 'New Version Available',
                'alert_message' => 'A new version of the app (:target_version) is available. Please update to enjoy the latest features and improvements.',
                'android_url' => 'https://hr.kneayerng.com',
                'ios_url' => 'https://apps.apple.com',
            ];

            AppSetting::firstOrCreate(
                ['slug' => 'app-version-update'],
                [
                    'name' => 'App Version Control',
                    'status' => 1,
                    'value' => json_encode($defaultSettings),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('app_settings')) {
            AppSetting::where('slug', 'app-version-update')->delete();

            if (Schema::hasColumn('app_settings', 'value')) {
                Schema::table('app_settings', function (Blueprint $table) {
                    $table->string('value', 255)->nullable()->change();
                });
            }
        }
    }
};
