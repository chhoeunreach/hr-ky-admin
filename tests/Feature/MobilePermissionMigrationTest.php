<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobilePermissionMigrationTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    public function test_catalog_is_assignable_without_granting_roles_and_rollback_preserves_existing_abilities(): void
    {
        Schema::create('permission_group_types', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug');
        });
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('group_type_id');
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->string('permission_key')->unique();
            $table->integer('permission_groups_id');
        });
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id'); $table->integer('role_id');
        });
        foreach ([1, 7] as $roleId) {
            $user = (new \App\Models\User)->forceFill(['id' => 10, 'role_id' => $roleId]);
            $user->setRelation('role', (new \App\Models\Role)->forceFill(['slug' => 'employee']));
            $this->actingAs($user);
            (new \App\Http\Middleware\SPAuthGateMW)->handle(
                \Illuminate\Http\Request::create('/_test/numeric-role'), fn () => response('OK'));
            $this->assertFalse(\Illuminate\Support\Facades\Gate::allows('delete_employee'));
        }
        DB::table('permissions')->insert(['name' => 'Existing Profile Permission',
            'permission_key' => 'view_profile', 'permission_groups_id' => 100]);
        $migration = require __DIR__.'/../../database/migrations/2026_11_11_000001_add_mobile_menu_function_permissions.php';
        $migration->up();
        $migration->up(); // Safe when rerun against existing permissions.
        $catalog = require __DIR__.'/../../config/mobile_permissions.php';
        $keys = DB::table('permissions')->pluck('permission_key')->all();
        foreach ($catalog['menus'] as $key) $this->assertContains($key, $keys);
        foreach ($catalog['functions'] as $rule) {
            if ($rule !== null) $this->assertContains($rule[1], $keys);
        }
        $this->assertSame(0, DB::table('permission_roles')->count());
        $this->assertSame('Existing Profile Permission', DB::table('permissions')->where('permission_key', 'view_profile')->value('name'));
        $migration->down();
        $this->assertTrue(DB::table('permissions')->where('permission_key', 'view_profile')->exists());
    }
}
