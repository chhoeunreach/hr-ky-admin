<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('team_meetings', function (Blueprint $table) {
            $table->boolean('attendance_enabled')->default(true);
            $table->unsignedInteger('attendance_qr_version')->default(1);
            $table->dateTime('attendance_opens_at')->nullable();
            $table->dateTime('attendance_closes_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('team_meetings', fn (Blueprint $table) => $table->dropColumn([
            'attendance_enabled', 'attendance_qr_version', 'attendance_opens_at', 'attendance_closes_at',
        ]));
    }
};
