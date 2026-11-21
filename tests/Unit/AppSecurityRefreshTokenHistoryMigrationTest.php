<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityRefreshTokenHistoryMigrationTest extends TestCase
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
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id(); $table->string('table_name', 100)->unique(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_sessions', function (Blueprint $table): void {
            $table->id(); $table->uuid('uuid')->unique(); $table->timestamps();
        });
    }

    public function test_migration_tracks_owned_history_table_and_refuses_to_drop_retained_hashes(): void
    {
        $migration = require database_path('migrations/2026_11_20_160000_create_app_security_refresh_token_history.php');
        $migration->up();
        $this->assertTrue(Schema::hasTable('app_security_refresh_token_history'));
        $this->assertDatabaseHas('app_security_schema_migration_objects', ['table_name' => 'app_security_refresh_token_history']);

        DB::table('app_security_refresh_token_history')->insert([
            'session_id' => 1, 'token_sha256' => str_repeat('a', 64), 'expires_at' => now()->addDay(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        try {
            $migration->down();
            $this->fail('Rollback must not remove refresh-token replay evidence.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('archive it before rolling back', $exception->getMessage());
        }
        $this->assertTrue(Schema::hasTable('app_security_refresh_token_history'));

        DB::table('app_security_refresh_token_history')->delete();
        $migration->down();
        $this->assertFalse(Schema::hasTable('app_security_refresh_token_history'));
        $this->assertDatabaseMissing('app_security_schema_migration_objects', ['table_name' => 'app_security_refresh_token_history']);
    }

    public function test_migration_does_not_claim_or_drop_a_preexisting_table(): void
    {
        Schema::connection('sqlite')->create('app_security_refresh_token_history', function (Blueprint $table): void {
            $table->id(); $table->string('preexisting_marker')->nullable();
        });
        $migration = require database_path('migrations/2026_11_20_160000_create_app_security_refresh_token_history.php');
        $migration->up();
        $migration->down();

        $this->assertTrue(Schema::hasColumn('app_security_refresh_token_history', 'preexisting_marker'));
        $this->assertDatabaseMissing('app_security_schema_migration_objects', ['table_name' => 'app_security_refresh_token_history']);
    }
}
