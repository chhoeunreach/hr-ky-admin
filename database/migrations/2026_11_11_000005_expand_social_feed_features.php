<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_posts')) {
            Schema::table('social_posts', function (Blueprint $table) {
                if (!Schema::hasColumn('social_posts', 'visibility')) {
                    $table->string('visibility', 24)->default('company')->after('company_id');
                }
                if (!Schema::hasColumn('social_posts', 'audience_department_ids')) {
                    $table->json('audience_department_ids')->nullable()->after('visibility');
                }
                if (!Schema::hasColumn('social_posts', 'audience_user_ids')) {
                    $table->json('audience_user_ids')->nullable()->after('audience_department_ids');
                }
                if (!Schema::hasColumn('social_posts', 'pinned_at')) {
                    $table->timestamp('pinned_at')->nullable()->after('original_post_id');
                }
                if (!Schema::hasColumn('social_posts', 'pinned_by')) {
                    $table->unsignedBigInteger('pinned_by')->nullable()->after('pinned_at');
                }
                if (!Schema::hasColumn('social_posts', 'is_announcement')) {
                    $table->boolean('is_announcement')->default(false)->after('pinned_by');
                }
            });
        }

        if (Schema::hasTable('social_comments') && !Schema::hasColumn('social_comments', 'parent_id')) {
            Schema::table('social_comments', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('social_post_id');
                $table->index(['social_post_id', 'parent_id']);
            });
        }

        if (!Schema::hasTable('social_post_media')) {
            Schema::create('social_post_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
                $table->string('media_type', 20);
                $table->text('media_url');
                $table->string('file_name')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedSmallInteger('position')->default(0);
                $table->timestamps();
                $table->index(['social_post_id', 'position']);
            });
        }

        if (!Schema::hasTable('social_comment_reactions')) {
            Schema::create('social_comment_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_comment_id')->constrained('social_comments')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->string('reaction', 12);
                $table->timestamps();
                $table->unique(['social_comment_id', 'user_id'], 'social_comment_reaction_unique');
            });
        }

        if (!Schema::hasTable('social_post_saves')) {
            Schema::create('social_post_saves', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->unique(['social_post_id', 'user_id'], 'social_post_save_unique');
                $table->index(['user_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('social_post_reports')) {
            Schema::create('social_post_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->string('reason', 500);
                $table->string('status', 20)->default('open');
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->unique(['social_post_id', 'user_id'], 'social_post_report_unique');
                $table->index(['status', 'created_at']);
            });
        }

        if (!Schema::hasTable('social_notifications')) {
            Schema::create('social_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->unsignedBigInteger('social_post_id')->nullable();
                $table->unsignedBigInteger('social_comment_id')->nullable();
                $table->string('type', 40);
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'read_at', 'created_at']);
            });
        }

        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);
        $group = DB::table('permission_groups')->where('name', 'Mobile Social Feed')->value('id')
            ?: DB::table('permission_groups')->insertGetId(['name' => 'Mobile Social Feed', 'group_type_id' => $apiType]);
        foreach ([
            'social_feed.save' => 'Save Social Posts',
            'social_feed.search' => 'Search Social Feed',
            'social_feed.report' => 'Report Social Posts',
            'social_feed.pin' => 'Pin Social Posts',
            'social_feed.notifications' => 'Read Social Notifications',
            'social_feed.edit_comment' => 'Edit Own Social Comments',
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
        foreach (['social_notifications', 'social_post_reports', 'social_post_saves', 'social_comment_reactions', 'social_post_media'] as $table) {
            Schema::dropIfExists($table);
        }
        if (Schema::hasTable('social_comments') && Schema::hasColumn('social_comments', 'parent_id')) {
            Schema::table('social_comments', fn (Blueprint $table) => $table->dropColumn('parent_id'));
        }
        if (Schema::hasTable('social_posts')) {
            foreach (['visibility', 'audience_department_ids', 'audience_user_ids', 'pinned_at', 'pinned_by', 'is_announcement'] as $column) {
                if (Schema::hasColumn('social_posts', $column)) {
                    Schema::table('social_posts', fn (Blueprint $table) => $table->dropColumn($column));
                }
            }
        }
        $keys = ['social_feed.save', 'social_feed.search', 'social_feed.report', 'social_feed.pin', 'social_feed.notifications', 'social_feed.edit_comment'];
        $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
