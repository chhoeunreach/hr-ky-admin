<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityRiskReviewPermissionMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.prefix' => '']);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('permissions', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('permission_key')->unique(); $table->unsignedBigInteger('permission_groups_id')->nullable();
        });
        Schema::connection('sqlite')->create('permission_roles', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('permission_id'); $table->unsignedBigInteger('role_id');
        });
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id(); $table->string('table_name', 100)->unique(); $table->timestamps();
        });
    }

    public function test_permission_migration_is_tracked_and_removes_only_its_own_row(): void
    {
        $migration = require database_path('migrations/2026_11_20_141000_add_app_security_risk_review_permission.php');
        $migration->up();
        $this->assertDatabaseHas('permissions', ['permission_key' => 'app_security_risk_review']);
        $this->assertDatabaseHas('app_security_schema_migration_objects', ['table_name' => 'permission:app_security_risk_review']);
        $permissionId = DB::table('permissions')->where('permission_key', 'app_security_risk_review')->value('id');
        DB::table('permission_roles')->insert(['permission_id' => $permissionId, 'role_id' => 5]);
        $migration->down();
        $this->assertDatabaseMissing('permissions', ['permission_key' => 'app_security_risk_review']);
        $this->assertSame(0, DB::table('permission_roles')->count());
    }
}
