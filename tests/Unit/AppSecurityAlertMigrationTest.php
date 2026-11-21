<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityAlertMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
    }

    public function test_alert_table_migration_is_tracked_and_refuses_data_loss_on_rollback(): void
    {
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name', 100)->unique();
            $table->timestamps();
        });
        $migration = require database_path('migrations/2026_11_20_103000_create_app_security_alerts.php');
        $migration->up();

        $this->assertTrue(Schema::connection('sqlite')->hasTable('app_security_alerts'));
        $this->assertDatabaseHas('app_security_schema_migration_objects', ['table_name' => 'app_security_alerts']);
        DB::table('app_security_alerts')->insert([
            'uuid' => 'alert-uuid', 'dedupe_key' => 'test-alert', 'alert_type' => 'test',
            'severity' => 'high', 'title' => 'Test alert', 'message' => 'Test',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        try {
            $migration->down();
            $this->fail('Rollback should preserve alert history.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('alert history exists', $exception->getMessage());
        }
        $this->assertSame(1, DB::table('app_security_alerts')->count());
    }
}
