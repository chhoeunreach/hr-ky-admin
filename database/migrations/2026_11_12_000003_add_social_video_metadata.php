<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['social_stories', 'social_posts', 'social_post_media'] as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'thumbnail_url')) {
                    $table->text('thumbnail_url')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'duration_seconds')) {
                    $table->unsignedInteger('duration_seconds')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['social_stories', 'social_posts', 'social_post_media'] as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            $columns = array_values(array_filter(
                ['thumbnail_url', 'duration_seconds'],
                fn (string $column) => Schema::hasColumn($tableName, $column),
            ));
            if ($columns) Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
