<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('home_highlight_display_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->boolean('auto_switch_enabled')->default(true);
            $table->unsignedSmallInteger('late_bonus_seconds')->default(15);
            $table->unsignedSmallInteger('highlights_seconds')->default(15);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('home_highlight_display_settings'); }
};
