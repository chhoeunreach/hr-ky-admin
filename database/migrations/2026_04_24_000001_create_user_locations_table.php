<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedDecimal('accuracy', 10, 2);
            $table->unsignedTinyInteger('battery_level')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['updated_at', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
