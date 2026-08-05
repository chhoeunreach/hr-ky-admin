<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('d_card_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('name_khmer');
            $table->string('name_english')->nullable();
            $table->string('position_khmer')->nullable();
            $table->string('position_english')->nullable();
            $table->string('department')->nullable();
            $table->string('branch')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('khqr_account_id')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('d_card_employees');
    }
};
