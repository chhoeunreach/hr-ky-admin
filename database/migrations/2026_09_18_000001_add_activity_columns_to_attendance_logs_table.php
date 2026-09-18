<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_logs', 'action')) {
                $table->string('action')->nullable()->after('identifier');
            }
            if (!Schema::hasColumn('attendance_logs', 'latitude')) {
                $table->double('latitude')->nullable()->after('action');
            }
            if (!Schema::hasColumn('attendance_logs', 'longitude')) {
                $table->double('longitude')->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('attendance_logs', 'note')) {
                $table->text('note')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('attendance_logs', 'source')) {
                $table->string('source')->nullable()->default('app')->after('note');
            }
            if (!Schema::hasColumn('attendance_logs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('source');
            }
            if (!Schema::hasColumn('attendance_logs', 'attendance_id')) {
                $table->unsignedBigInteger('attendance_id')->nullable()->after('created_by')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $columns = ['action', 'latitude', 'longitude', 'note', 'source', 'created_by', 'attendance_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendance_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
