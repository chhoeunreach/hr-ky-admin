<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_polls')) {
            Schema::create('social_polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_post_id')->unique()->constrained('social_posts')->cascadeOnDelete();
                $table->text('question');
                $table->boolean('allow_multiple')->default(false);
                $table->timestamp('closes_at')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('social_poll_options')) {
            Schema::create('social_poll_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_poll_id')->constrained('social_polls')->cascadeOnDelete();
                $table->string('label', 160);
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
                $table->index(['social_poll_id', 'position']);
            });
        }
        if (!Schema::hasTable('social_poll_votes')) {
            Schema::create('social_poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_poll_id')->constrained('social_polls')->cascadeOnDelete();
                $table->foreignId('social_poll_option_id')->constrained('social_poll_options')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->unique(['social_poll_id', 'social_poll_option_id', 'user_id'], 'social_poll_vote_unique');
                $table->index(['social_poll_id', 'user_id']);
            });
        }

        $group = DB::table('permission_groups')->where('name', 'Mobile Social Feed')->value('id');
        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);
        $group ??= DB::table('permission_groups')->insertGetId(['name' => 'Mobile Social Feed', 'group_type_id' => $apiType]);
        foreach (['social_feed.poll' => 'Create Social Polls', 'social_feed.vote' => 'Vote in Social Polls'] as $key => $label) {
            DB::table('permissions')->insertOrIgnore(['permission_key' => $key, 'name' => 'Mobile Social Feed: '.$label, 'permission_groups_id' => $group]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_poll_votes');
        Schema::dropIfExists('social_poll_options');
        Schema::dropIfExists('social_polls');
        $ids = DB::table('permissions')->whereIn('permission_key', ['social_feed.poll', 'social_feed.vote'])->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
