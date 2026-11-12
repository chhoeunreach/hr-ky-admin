<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['social_stories', 'social_posts', 'social_post_media'] as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'aspect_ratio')) continue;
            Schema::table($tableName, fn (Blueprint $table) => $table->decimal('aspect_ratio', 6, 3)->nullable());
        }
    }

    public function down(): void
    {
        foreach (['social_stories', 'social_posts', 'social_post_media'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'aspect_ratio')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('aspect_ratio'));
            }
        }
    }
};
