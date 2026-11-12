<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_post_preferences')) {
            Schema::create('social_post_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->index();
                $table->boolean('notifications_enabled')->default(false);
                $table->timestamp('hidden_at')->nullable();
                $table->timestamps();
                $table->unique(['social_post_id', 'user_id'], 'social_post_preference_unique');
                $table->index(['user_id', 'hidden_at']);
            });
        }

        $group = DB::table('permission_groups')->where('name', 'Mobile Social Feed')->value('id');
        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);
        $group ??= DB::table('permission_groups')->insertGetId([
            'name' => 'Mobile Social Feed',
            'group_type_id' => $apiType,
        ]);

        foreach ([
            'social_feed.follow' => 'Follow Social Post Notifications',
            'social_feed.hide' => 'Hide Social Posts',
        ] as $key => $label) {
            DB::table('permissions')->insertOrIgnore([
                'permission_key' => $key,
                'name' => 'Mobile Social Feed: '.$label,
                'permission_groups_id' => $group,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_preferences');

        $ids = DB::table('permissions')
            ->whereIn('permission_key', ['social_feed.follow', 'social_feed.hide'])
            ->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
