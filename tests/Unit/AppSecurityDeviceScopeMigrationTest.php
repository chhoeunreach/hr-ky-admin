<?php

namespace Tests\Unit;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityDeviceScopeMigrationTest extends TestCase
{
    public function test_installation_and_key_are_scoped_per_account(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->uuid('installation_uuid')->unique();
            $table->char('public_key_fingerprint', 64)->unique();
        });

        $migration = require database_path(
            'migrations/2026_11_19_162000_allow_account_scoped_app_security_devices.php'
        );
        $migration->up();

        DB::table('app_registered_devices')->insert([
            'user_id' => 10,
            'installation_uuid' => '799a72bc-2502-40d6-9bfc-d25e080c24e8',
            'public_key_fingerprint' => str_repeat('a', 64),
        ]);
        DB::table('app_registered_devices')->insert([
            'user_id' => 20,
            'installation_uuid' => '799a72bc-2502-40d6-9bfc-d25e080c24e8',
            'public_key_fingerprint' => str_repeat('a', 64),
        ]);

        try {
            DB::table('app_registered_devices')->insert([
                'user_id' => 10,
                'installation_uuid' => '799a72bc-2502-40d6-9bfc-d25e080c24e8',
                'public_key_fingerprint' => str_repeat('a', 64),
            ]);
            $this->fail('One user must not create duplicate records for the same installation.');
        } catch (QueryException) {
            $this->assertSame(2, DB::table('app_registered_devices')->count());
        }

        try {
            $migration->down();
            $this->fail('Rollback must refuse to restore global uniqueness while shared account rows exist.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Cannot restore global installation UUID uniqueness', $exception->getMessage());
            $this->assertSame(2, DB::table('app_registered_devices')->count());
        }

        DB::table('app_registered_devices')->where('user_id', 20)->delete();
        $migration->down();
        try {
            DB::table('app_registered_devices')->insert([
                'user_id' => 20,
                'installation_uuid' => '799a72bc-2502-40d6-9bfc-d25e080c24e8',
                'public_key_fingerprint' => str_repeat('b', 64),
            ]);
            $this->fail('Rollback must restore installation UUID uniqueness.');
        } catch (QueryException) {
            $this->assertSame(1, DB::table('app_registered_devices')->count());
        }
    }
}
