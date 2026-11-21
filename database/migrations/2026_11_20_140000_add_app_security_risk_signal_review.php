<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'app_device_integrity_checks';
    private const MARKERS = [
        'review_status' => 'column:app_device_integrity_checks.review_status',
        'reviewed_by' => 'column:app_device_integrity_checks.reviewed_by',
        'reviewed_at' => 'column:app_device_integrity_checks.reviewed_at',
        'review_reason' => 'column:app_device_integrity_checks.review_reason',
    ];

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasTable('app_security_schema_migration_objects')) return;
        $added = [];
        foreach (self::MARKERS as $column => $_marker) {
            if (!Schema::hasColumn(self::TABLE, $column)) $added[] = $column;
        }
        if ($added === []) return;

        Schema::table(self::TABLE, function (Blueprint $table) use ($added): void {
            foreach ($added as $column) {
                match ($column) {
                    'review_status' => $table->string('review_status', 20)->nullable(),
                    'reviewed_by' => $table->unsignedBigInteger('reviewed_by')->nullable(),
                    'reviewed_at' => $table->timestamp('reviewed_at')->nullable(),
                    'review_reason' => $table->string('review_reason', 512)->nullable(),
                };
            }
        });
        foreach ($added as $column) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => self::MARKERS[$column], 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasTable('app_security_schema_migration_objects')) return;
        $added = DB::table('app_security_schema_migration_objects')
            ->whereIn('table_name', array_values(self::MARKERS))->pluck('table_name')->all();
        if ($added === []) return;
        $addedColumns = array_keys(array_filter(self::MARKERS, fn (string $marker) => in_array($marker, $added, true)));
        if (in_array('review_status', $addedColumns, true)
            && DB::table(self::TABLE)->whereNotNull('review_status')->exists()) {
            throw new RuntimeException('Risk signal review decisions exist; archive them before rolling back this migration.');
        }
        foreach (array_reverse($addedColumns) as $column) {
            if (!Schema::hasColumn(self::TABLE, $column)) continue;
            Schema::table(self::TABLE, fn (Blueprint $table) => $table->dropColumn($column));
        }
        DB::table('app_security_schema_migration_objects')->whereIn('table_name', $added)->delete();
    }
};
