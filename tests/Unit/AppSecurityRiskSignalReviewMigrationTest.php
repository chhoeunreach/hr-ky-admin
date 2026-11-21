<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityRiskSignalReviewMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.prefix' => '']);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id(); $table->string('table_name', 100)->unique(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_integrity_checks', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('device_id'); $table->string('signal');
        });
    }

    public function test_review_metadata_migration_tracks_new_columns_and_refuses_data_loss(): void
    {
        $migration = require database_path('migrations/2026_11_20_140000_add_app_security_risk_signal_review.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumn('app_device_integrity_checks', 'review_status'));
        $this->assertDatabaseHas('app_security_schema_migration_objects', ['table_name' => 'column:app_device_integrity_checks.review_reason']);

        DB::table('app_device_integrity_checks')->insert(['device_id' => 4, 'signal' => 'root_detected', 'review_status' => 'false_positive']);
        try {
            $migration->down();
            $this->fail('Rollback should preserve risk review decisions.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Risk signal review decisions exist', $exception->getMessage());
        }
        $this->assertTrue(Schema::hasColumn('app_device_integrity_checks', 'review_status'));
        DB::table('app_device_integrity_checks')->update(['review_status' => null]);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('app_device_integrity_checks', 'review_status'));
    }
}
