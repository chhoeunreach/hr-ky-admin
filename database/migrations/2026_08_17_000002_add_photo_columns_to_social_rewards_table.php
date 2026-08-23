<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hrs_addon_social_rewards', function (Blueprint $table) {
            if (!Schema::hasColumn('hrs_addon_social_rewards', 'fb_post_photo')) {
                $table->string('fb_post_photo')->nullable()->after('fb_post_url');
            }
            if (!Schema::hasColumn('hrs_addon_social_rewards', 'fb_story_photo')) {
                $table->string('fb_story_photo')->nullable()->after('fb_story_url');
            }
            if (!Schema::hasColumn('hrs_addon_social_rewards', 'tiktok_photo')) {
                $table->string('tiktok_photo')->nullable()->after('tiktok_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hrs_addon_social_rewards', function (Blueprint $table) {
            if (Schema::hasColumn('hrs_addon_social_rewards', 'fb_post_photo')) {
                $table->dropColumn('fb_post_photo');
            }
            if (Schema::hasColumn('hrs_addon_social_rewards', 'fb_story_photo')) {
                $table->dropColumn('fb_story_photo');
            }
            if (Schema::hasColumn('hrs_addon_social_rewards', 'tiktok_photo')) {
                $table->dropColumn('tiktok_photo');
            }
        });
    }
};
