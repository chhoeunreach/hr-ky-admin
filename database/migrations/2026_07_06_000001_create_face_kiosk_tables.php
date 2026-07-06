<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosk_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('token_prefix', 12);
            $table->string('token_hash', 64)->unique();
            $table->string('admin_pin_hash');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'is_active']);
        });

        Schema::create('face_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->longText('embedding');
            $table->unsignedSmallInteger('embedding_dimension')->default(192);
            $table->string('model_version', 80);
            $table->decimal('quality_score', 5, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('enrolled_by_device_id')->nullable()
                ->constrained('kiosk_devices')->nullOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'is_active']);
            $table->index('updated_at');
        });

        Schema::create('kiosk_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->foreignId('kiosk_device_id')->constrained('kiosk_devices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->timestamp('captured_at');
            $table->decimal('match_score', 6, 5);
            $table->string('action', 20)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'captured_at']);
            $table->index(['kiosk_device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_attendance_events');
        Schema::dropIfExists('face_profiles');
        Schema::dropIfExists('kiosk_devices');
    }
};
