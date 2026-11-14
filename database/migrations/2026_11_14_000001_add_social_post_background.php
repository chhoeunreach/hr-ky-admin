<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('social_posts') && !Schema::hasColumn('social_posts', 'background')) {
            Schema::table('social_posts', function (Blueprint $table) {
                $table->string('background', 32)->nullable()->after('media_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('social_posts') && Schema::hasColumn('social_posts', 'background')) {
            Schema::table('social_posts', function (Blueprint $table) {
                $table->dropColumn('background');
            });
        }
    }
};
