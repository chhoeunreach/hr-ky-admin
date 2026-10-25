<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_activities', function (Blueprint $table) {
            $table->id();
            // Snapshots survive deletion of the asset, employee, or administrator.
            $table->unsignedBigInteger('asset_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('asset_name');
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->string('event');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_guard')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_activities');
    }
};
