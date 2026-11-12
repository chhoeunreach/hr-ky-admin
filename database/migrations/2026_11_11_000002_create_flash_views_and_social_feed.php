<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('flash_views')) {
            Schema::create('flash_views', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('body')->nullable();
            $table->string('media_type', 20)->default('text');
            $table->text('media_url')->nullable();
            $table->text('link_url')->nullable();
            $table->string('link_label', 80)->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('display_policy', 20)->default('once');
            $table->json('role_ids')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('priority')->default(0);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'ends_at']);
            });
        }
        if (!Schema::hasTable('flash_view_receipts')) {
            Schema::create('flash_view_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_view_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('revision');
            $table->timestamps();
            $table->unique(['flash_view_id', 'user_id', 'revision'], 'flash_receipt_unique');
            });
        }
        if (!Schema::hasTable('social_posts')) {
            Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->text('body')->nullable();
            $table->string('media_type', 20)->default('text');
            $table->text('media_url')->nullable();
            $table->foreignId('original_post_id')->nullable()->constrained('social_posts')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'created_at']);
            });
        }
        if (!Schema::hasTable('social_comments')) {
            Schema::create('social_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->text('body');
            $table->timestamps();
            });
        }
        if (!Schema::hasTable('social_reactions')) {
            Schema::create('social_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->string('reaction', 12);
            $table->timestamps();
            $table->unique(['social_post_id', 'user_id']);
            });
        }
        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);
        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'Web', 'slug' => 'web']);
        foreach ([
            ['Flash View', $webType, ['flash_view.manage' => 'Manage Flash View Popups']],
            ['Social Feed', $webType, ['social_feed.moderate' => 'Moderate Social Feed']],
            ['Mobile Flash View', $apiType, ['mobile.menu.flash-view' => 'Flash View Menu', 'flash_view.view' => 'View Flash Popups']],
            ['Mobile Social Feed', $apiType, [
                'mobile.menu.social-feed' => 'Social Feed Menu', 'social_feed.view' => 'Read Social Feed',
                'social_feed.create' => 'Publish Social Posts', 'social_feed.edit' => 'Edit Own Social Posts',
                'social_feed.delete' => 'Delete Own Social Posts', 'social_feed.comment' => 'Comment on Social Posts',
                'social_feed.delete_comment' => 'Delete Own Social Comments', 'social_feed.react' => 'React to Social Posts',
                'social_feed.share' => 'Share Social Posts',
            ]],
        ] as [$name, $type, $abilities]) {
            $group = DB::table('permission_groups')->where('name', $name)->value('id')
                ?: DB::table('permission_groups')->insertGetId(['name' => $name, 'group_type_id' => $type]);
            foreach ($abilities as $key => $label) {
                DB::table('permissions')->insertOrIgnore(['permission_key' => $key, 'name' => $label, 'permission_groups_id' => $group]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_reactions');
        Schema::dropIfExists('social_comments');
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('flash_view_receipts');
        Schema::dropIfExists('flash_views');
        $keys = ['flash_view.manage', 'flash_view.view', 'mobile.menu.flash-view', 'mobile.menu.social-feed',
            'social_feed.moderate', 'social_feed.view', 'social_feed.create', 'social_feed.edit', 'social_feed.delete',
            'social_feed.comment', 'social_feed.delete_comment', 'social_feed.react', 'social_feed.share'];
        $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
