<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_link_managers', function (Blueprint $table) {
            $table->foreignId('app_link_id')->constrained('app_links')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['app_link_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_link_managers');
    }
};
