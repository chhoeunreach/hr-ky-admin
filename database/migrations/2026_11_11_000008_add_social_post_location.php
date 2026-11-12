<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_posts') && !Schema::hasColumn('social_posts', 'location_name')) {
            Schema::table('social_posts', function (Blueprint $table) {
                $table->string('location_name', 180)->nullable()->after('body');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('social_posts') && Schema::hasColumn('social_posts', 'location_name')) {
            Schema::table('social_posts', fn (Blueprint $table) => $table->dropColumn('location_name'));
        }
    }
};
