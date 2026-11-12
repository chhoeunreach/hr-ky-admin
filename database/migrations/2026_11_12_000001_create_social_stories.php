<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_stories')) {
            Schema::create('social_stories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('media_type', 20)->default('text');
                $table->text('media_url')->nullable();
                $table->string('caption', 1000)->nullable();
                $table->timestamp('expires_at')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('social_story_views')) {
            Schema::create('social_story_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_story_id')->constrained('social_stories')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->timestamp('viewed_at')->nullable();
                $table->timestamps();
                $table->unique(['social_story_id', 'user_id'], 'social_story_view_unique');
                $table->index(['user_id', 'viewed_at']);
            });
        }

        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id');
        if ($apiType) {
            $group = DB::table('permission_groups')->where('name', 'Mobile Social Feed')->value('id');
            if (!$group) {
                $group = DB::table('permission_groups')->insertGetId([
                    'name' => 'Mobile Social Feed',
                    'group_type_id' => $apiType,
                ]);
            }
            foreach ([
                'social_feed.stories.view' => 'View Social Stories',
                'social_feed.stories.create' => 'Create Social Stories',
                'social_feed.stories.delete' => 'Delete Own Social Stories',
            ] as $key => $name) {
                DB::table('permissions')->insertOrIgnore([
                    'permission_key' => $key,
                    'name' => $name,
                    'permission_groups_id' => $group,
                ]);
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'social_feed.stories.view',
            'social_feed.stories.create',
            'social_feed.stories.delete',
        ];
        $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        Schema::dropIfExists('social_story_views');
        Schema::dropIfExists('social_stories');
    }
};
