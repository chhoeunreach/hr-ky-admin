<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityNotificationDedupeMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id(); $table->string('table_name', 100)->unique(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('notifications', function (Blueprint $table): void {
            $table->id(); $table->string('title');
        });
    }

    public function test_notification_dedupe_column_is_unique_tracked_and_protected_from_data_loss(): void
    {
        $migration = require database_path('migrations/2026_11_20_150000_add_app_security_notification_dedupe_key.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumn('notifications', 'security_event_key'));
        $this->assertDatabaseHas('app_security_schema_migration_objects', ['table_name' => 'column:notifications.security_event_key']);
        DB::table('notifications')->insert(['title' => 'Security', 'security_event_key' => 'event-1']);
        try {
            $migration->down();
            $this->fail('Rollback should preserve notification delivery history.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('notification history exists', $exception->getMessage());
        }
        DB::table('notifications')->update(['security_event_key' => null]);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('notifications', 'security_event_key'));
    }
}
