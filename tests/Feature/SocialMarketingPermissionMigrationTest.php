<?php

namespace Tests\Feature;

use Database\Seeders\SocialMarketingPermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SocialMarketingPermissionMigrationTest extends TestCase
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

    public function test_web_permissions_are_created_idempotently_and_roll_back_cleanly(): void
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
        Schema::create('features', function (Blueprint $table) {
            $table->id(); $table->string('group')->nullable(); $table->string('name');
            $table->string('key'); $table->boolean('status')->default(0); $table->timestamps();
        });

        DB::table('permissions')->insert([
            'name' => 'Existing Permission',
            'permission_key' => 'existing.permission',
            'permission_groups_id' => 1,
        ]);

        $migration = require __DIR__.'/../../database/migrations/2026_11_11_000004_add_social_marketing_web_permissions.php';
        $migration->up();
        $migration->up();

        $this->assertSame(1, DB::table('permissions')->where('permission_key', 'social_reward.view')->count());
        $this->assertSame(1, DB::table('permissions')->where('permission_key', 'social_reward.manage')->count());
        $this->assertTrue(DB::table('permissions')->where('permission_key', 'existing.permission')->exists());

        (new SocialMarketingPermissionSeeder())->run();
        $this->assertSame(1, DB::table('permissions')->where('permission_key', 'social_reward.view')->count());
        $this->assertSame(1, DB::table('features')->where('key', 'social-media-marketing')->count());

        $migration->down();
        $this->assertFalse(DB::table('permissions')->where('permission_key', 'social_reward.view')->exists());
        $this->assertFalse(DB::table('permissions')->where('permission_key', 'social_reward.manage')->exists());
        $this->assertTrue(DB::table('permissions')->where('permission_key', 'existing.permission')->exists());
    }
}
