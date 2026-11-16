<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_posts')) {
            Schema::table('social_posts', function (Blueprint $table) {
                $table->index(['company_id', 'deleted_at', 'id'], 'social_posts_feed_scope_idx');
                $table->index(['company_id', 'pinned_at', 'is_announcement', 'id'], 'social_posts_feed_order_idx');
            });
        }

        if (Schema::hasTable('social_reactions')) {
            Schema::table('social_reactions', function (Blueprint $table) {
                $table->index(['social_post_id', 'reaction'], 'social_reactions_post_reaction_idx');
                $table->index(['user_id', 'social_post_id'], 'social_reactions_user_post_idx');
            });
        }

        if (Schema::hasTable('social_post_saves')) {
            Schema::table('social_post_saves', function (Blueprint $table) {
                $table->index(['user_id', 'social_post_id'], 'social_saves_user_post_idx');
            });
        }

        if (Schema::hasTable('social_post_preferences')) {
            Schema::table('social_post_preferences', function (Blueprint $table) {
                $table->index(['user_id', 'social_post_id'], 'social_prefs_user_post_idx');
            });
        }

        if (Schema::hasTable('social_comments')) {
            Schema::table('social_comments', function (Blueprint $table) {
                $table->index(['social_post_id', 'parent_id', 'id'], 'social_comments_thread_idx');
            });
        }

        if (Schema::hasTable('social_poll_votes')) {
            Schema::table('social_poll_votes', function (Blueprint $table) {
                $table->index(['social_poll_option_id', 'social_poll_id'], 'social_poll_option_votes_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('social_posts')) {
            Schema::table('social_posts', function (Blueprint $table) {
                $table->dropIndex('social_posts_feed_scope_idx');
                $table->dropIndex('social_posts_feed_order_idx');
            });
        }

        if (Schema::hasTable('social_reactions')) {
            Schema::table('social_reactions', function (Blueprint $table) {
                $table->dropIndex('social_reactions_post_reaction_idx');
                $table->dropIndex('social_reactions_user_post_idx');
            });
        }

        if (Schema::hasTable('social_post_saves')) {
            Schema::table('social_post_saves', function (Blueprint $table) {
                $table->dropIndex('social_saves_user_post_idx');
            });
        }

        if (Schema::hasTable('social_post_preferences')) {
            Schema::table('social_post_preferences', function (Blueprint $table) {
                $table->dropIndex('social_prefs_user_post_idx');
            });
        }

        if (Schema::hasTable('social_comments')) {
            Schema::table('social_comments', function (Blueprint $table) {
                $table->dropIndex('social_comments_thread_idx');
            });
        }

        if (Schema::hasTable('social_poll_votes')) {
            Schema::table('social_poll_votes', function (Blueprint $table) {
                $table->dropIndex('social_poll_option_votes_idx');
            });
        }
    }
};
