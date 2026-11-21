<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_feature_controls')) return;

        Schema::create('mobile_feature_controls', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->boolean('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_feature_controls');
    }
};
