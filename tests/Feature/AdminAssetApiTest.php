<?php

namespace Tests\Feature;

use App\Http\Middleware\Admin;
use App\Http\Middleware\MobileFunctionPermission;
use App\Http\Middleware\SPAuthGateMW;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminAssetApiTest extends TestCase
{
    private array $abilities = [];

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
            $app['config']->set('database.connections.sqlite.prefix', '');
            $app['config']->set('app_security.enabled', false);
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::purge('sqlite');
        $this->withoutMiddleware([SPAuthGateMW::class, Admin::class]);

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable();
            $table->integer('status')->default(0);
        });
        Schema::create('asset_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('last_number')->default(0);
            $table->timestamps();
        });
        DB::table('asset_code_sequences')->insert(['id' => 1, 'last_number' => 0]);
        foreach (['branches', 'users', 'asset_types', 'permissions', 'permission_groups', 'permission_roles'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->integer('branch_id')->nullable();
                $table->string('permission_key')->nullable();
                $table->integer('permission_groups_id')->nullable();
                $table->integer('permission_id')->nullable();
                $table->integer('role_id')->nullable();
            });
        }
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->string('employee_code')->nullable();
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
        });
        Schema::table('asset_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(1);
        });
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_code')->nullable();
            $table->string('asset_serial_no')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->date('purchased_date')->nullable();
            $table->boolean('is_available')->default(1);
            $table->string('is_working')->default('yes');
            $table->boolean('warranty_available')->default(0);
            $table->date('warranty_end_date')->nullable();
            $table->text('note')->nullable();
            $table->text('phone_numbers')->nullable();
            $table->string('icloud')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->integer('set_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->boolean('is_repaired')->default(0);
            $table->timestamps();
        });
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->integer('asset_id');
            $table->integer('user_id');
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->string('status')->nullable();
            $table->string('return_condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_activities', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->string('asset_name')->nullable();
            $table->integer('assignment_id')->nullable();
            $table->string('event')->nullable();
            $table->integer('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_guard')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamps();
        });
        DB::table('branches')->insert(['id' => 1, 'name' => 'Main Branch']);
        DB::table('users')->insert(['id' => 10, 'name' => 'Asset Admin', 'branch_id' => 1, 'is_active' => 1]);
        DB::table('asset_types')->insert(['id' => 1, 'name' => 'Laptop Type', 'is_active' => 1]);
        foreach (['list_assets', 'create_assets', 'edit_assets', 'delete_assets'] as $perm) {
            DB::table('permissions')->insert(['permission_key' => $perm]);
        }

        $user = User::find(10);
        $this->actingAs($user, 'api');
        Gate::before(fn ($u, $ability) => in_array($ability, $this->abilities, true));
    }

    public function test_admin_assets_list_requires_list_assets_permission(): void
    {
        $this->abilities = [];
        $this->getJson('/api/admin/assets')->assertForbidden();

        $this->abilities = ['list_assets'];
        $response = $this->getJson('/api/admin/assets');
        if ($response->status() !== 200) dump($response->json());
        $response->assertOk()->assertJsonPath('status', true);
    }

    public function test_admin_assets_crud_flow(): void
    {
        $this->abilities = ['list_assets', 'create_assets', 'edit_assets', 'delete_assets'];

        // 1. Form data
        $formRes = $this->getJson('/api/admin/assets/form-data');
        $formRes->assertOk()->assertJsonPath('status', true);

        // 2. Create asset
        $createRes = $this->postJson('/api/admin/assets', [
            'name' => 'MacBook Pro 16 M3',
            'type_id' => 1,
            'branch_id' => 1,
            'purchased_date' => date('Y-m-d'),
            'is_available' => 1,
            'is_working' => 'yes',
            'warranty_available' => 1,
            'warranty_end_date' => date('Y-m-d', strtotime('+1 year')),
            'note' => 'Unit assigned to lead dev',
        ]);
        $createRes->assertOk()->assertJsonPath('status', true);
        $assetId = $createRes->json('data.id');
        $this->assertNotNull($assetId);

        // 3. Show asset
        $showRes = $this->getJson("/api/admin/assets/{$assetId}");
        $showRes->assertOk()->assertJsonPath('data.name', 'MacBook Pro 16 M3');

        // 4. Update asset
        $updateRes = $this->postJson("/api/admin/assets/{$assetId}", [
            'name' => 'MacBook Pro 16 M3 Max Updated',
            'purchased_date' => date('Y-m-d'),
            'type_id' => 1,
            'note' => 'Upgraded memory',
        ]);
        $updateRes->assertOk()->assertJsonPath('status', true);
        $this->assertSame('MacBook Pro 16 M3 Max Updated', Asset::find($assetId)->name);

        // 5. Delete asset
        $deleteRes = $this->deleteJson("/api/admin/assets/{$assetId}");
        $deleteRes->assertOk()->assertJsonPath('status', true);
        $this->assertNull(Asset::find($assetId));
    }
}
