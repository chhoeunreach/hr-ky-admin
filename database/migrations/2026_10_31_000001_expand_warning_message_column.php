<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warnings', function (Blueprint $table) {
            $table->longText('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Retain the wider column so rolling back cannot truncate saved notices.
    }
};
