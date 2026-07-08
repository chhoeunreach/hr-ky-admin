<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hrs_addon_social_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('existing_employee_id');
            $table->date('log_date');
            $table->text('fb_post_url');
            $table->text('fb_story_url');
            $table->text('tiktok_url');
            $table->integer('reward_points')->default(1);
            $table->boolean('is_locked')->default(true);
            $table->timestamps();

            $table->foreign('existing_employee_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->unique(['existing_employee_id', 'log_date'], 'emp_daily_unique');
        });

        Schema::create('hrs_addon_reward_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_record_id');
            $table->unsignedBigInteger('admin_id');
            $table->text('reason');
            $table->timestamps();

            $table->foreign('target_record_id')
                ->references('id')
                ->on('hrs_addon_social_rewards')
                ->cascadeOnDelete();
            $table->foreign('admin_id')
                ->references('id')
                ->on('admins')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hrs_addon_reward_audit');
        Schema::dropIfExists('hrs_addon_social_rewards');
    }
};
