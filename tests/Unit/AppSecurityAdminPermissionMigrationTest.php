<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityAdminPermissionMigrationTest extends TestCase
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
        Schema::connection('sqlite')->create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('permission_key')->unique();
            $table->unsignedBigInteger('permission_groups_id')->nullable();
        });
        Schema::connection('sqlite')->create('permission_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name', 100)->unique();
            $table->timestamps();
        });
        DB::table('permissions')->insert([
            'name' => 'Existing local permission',
            'permission_key' => 'app_security_view',
        ]);
    }

    public function test_permission_migration_rollback_removes_only_permissions_it_created(): void
    {
        $migration = require database_path('migrations/2026_11_20_101000_add_app_security_role_permissions.php');
        $migration->up();
        $this->assertSame(1, DB::table('permissions')->where('permission_key', 'app_security_view')->count());
        $this->assertSame(1, DB::table('permissions')->where('permission_key', 'app_security_customer_support')->count());

        $migration->down();

        $this->assertSame('Existing local permission', DB::table('permissions')->where('permission_key', 'app_security_view')->value('name'));
        $this->assertSame(0, DB::table('permissions')->where('permission_key', 'app_security_customer_support')->count());
        $this->assertSame(0, DB::table('app_security_schema_migration_objects')->count());
    }
}
