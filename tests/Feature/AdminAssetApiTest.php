<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdminAssetApiTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::purge('sqlite');

        // Setup test schema
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->integer('status');
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Test Company');
            $table->timestamps();
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name')->default('Engineering');
            $table->integer('branch_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('role_id');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('employee_code')->nullable();
            $table->integer('branch_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('role_id')->nullable();
            $table->boolean('is_active')->default(1);
            $table->string('status')->default('verified');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('asset_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('last_number')->default(0);
        });
        DB::table('asset_code_sequences')->insert(['id' => 1, 'last_number' => 0]);

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id')->nullable();
            $table->integer('type_id');
            $table->string('name');
            $table->string('asset_code')->nullable();
            $table->string('asset_serial_no')->nullable();
            $table->text('phone_numbers')->nullable();
            $table->string('icloud')->nullable();
            $table->text('asset_password')->nullable();
            $table->text('screen_time_passcode')->nullable();
            $table->string('image')->nullable();
            $table->text('images')->nullable();
            $table->string('is_working')->default('yes');
            $table->boolean('is_available')->default(1);
            $table->date('purchased_date')->nullable();
            $table->boolean('warranty_available')->default(0);
            $table->date('warranty_end_date')->nullable();
            $table->text('note')->nullable();
            $table->integer('set_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->boolean('is_repaired')->default(0);
            $table->timestamps();
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('asset_id');
            $table->integer('user_id');
            $table->integer('assigned_by')->nullable();
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->string('status')->default('assigned');
            $table->string('return_condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_10_25_000001_create_asset_activities_table.php'))->up();
    }

    private function createAdminUser(): User
    {
        $role = Role::create(['name' => 'admin', 'slug' => 'admin']);
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'role_id' => $role->id,
            'is_active' => 1,
            'status' => 'verified',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/admin/assets')->assertStatus(401);
        $this->getJson('/api/admin/assets/form-data')->assertStatus(401);
    }

    public function test_form_data_returns_expected_structure(): void
    {
        $admin = $this->createAdminUser();
        Passport::actingAs($admin);

        $branch = Branch::create(['name' => 'Headquarters']);
        $type = AssetType::create(['name' => 'Laptop']);

        $response = $this->getJson('/api/admin/assets/form-data');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => [
                    'types',
                    'branches',
                    'employees',
                    'next_asset_code',
                ],
            ]);
    }

    public function test_can_create_and_list_assets(): void
    {
        $admin = $this->createAdminUser();
        Passport::actingAs($admin);

        $branch = Branch::create(['name' => 'Headquarters']);
        $type = AssetType::create(['name' => 'Laptop']);

        $createResponse = $this->postJson('/api/admin/assets', [
            'name' => 'MacBook Pro 16',
            'type_id' => $type->id,
            'branch_id' => $branch->id,
            'asset_serial_no' => 'C02ABC123XYZ',
            'is_working' => 'yes',
            'is_available' => '1',
            'purchased_date' => '2026-01-15',
            'note' => 'Brand new unit',
        ]);

        $createResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Asset added successfully');

        $assetId = $createResponse->json('data.id');
        $this->assertNotNull($assetId);

        // List assets
        $listResponse = $this->getJson('/api/admin/assets');
        $listResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'MacBook Pro 16')
            ->assertJsonPath('data.0.asset_serial_no', 'C02ABC123XYZ')
            ->assertJsonPath('data.0.status', 'Available')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'asset_code',
                        'asset_serial_no',
                        'type_id',
                        'type_name',
                        'branch_id',
                        'branch_name',
                        'purchased_date',
                        'is_available',
                        'is_working',
                        'note',
                        'status',
                    ],
                ],
                'pagination' => [
                    'total',
                    'last_page',
                    'current_page',
                    'per_page',
                ],
            ]);

        // Get single asset
        $detailResponse = $this->getJson("/api/admin/assets/{$assetId}");
        $detailResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'MacBook Pro 16');

        // Update asset
        $updateResponse = $this->postJson("/api/admin/assets/{$assetId}", [
            'name' => 'MacBook Pro 16 (Updated)',
            'type_id' => $type->id,
            'branch_id' => $branch->id,
            'note' => 'Assigned to manager',
        ]);
        $updateResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Asset updated successfully')
            ->assertJsonPath('data.name', 'MacBook Pro 16 (Updated)');

        // Delete asset
        $deleteResponse = $this->deleteJson("/api/admin/assets/{$assetId}");
        $deleteResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Asset deleted successfully');

        $this->assertEquals(0, Asset::count());
    }
}
