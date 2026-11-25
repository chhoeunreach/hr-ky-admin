<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StorePhoneNumber;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StorePhoneNumberApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTestDatabaseFile();

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--force' => true,
        ]);

        $this->createStorePhoneSchema();
    }

    public function test_unauthenticated_request_is_denied(): void
    {
        $this->getJson('/api/store-phone-numbers')->assertStatus(401);
        $this->getJson('/api/my-sim-cards')->assertStatus(401);
    }

    public function test_index_returns_list_of_store_phone_numbers(): void
    {
        $user = $this->createTestUser();
        Passport::actingAs($user);

        StorePhoneNumber::create([
            'phone_number' => '012345678',
            'operator' => 'cellcard',
            'sim_slot' => 'sim_1',
            'purpose' => 'cashier',
            'status' => 'active',
        ]);

        StorePhoneNumber::create([
            'phone_number' => '098765432',
            'operator' => 'smart',
            'sim_slot' => 'esim',
            'purpose' => 'delivery',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/store-phone-numbers');
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_operator_and_search(): void
    {
        $user = $this->createTestUser();
        Passport::actingAs($user);

        StorePhoneNumber::create([
            'phone_number' => '012999888',
            'operator' => 'cellcard',
            'sim_slot' => 'sim_1',
            'purpose' => 'cashier',
            'status' => 'active',
        ]);

        StorePhoneNumber::create([
            'phone_number' => '098111222',
            'operator' => 'smart',
            'sim_slot' => 'sim_2',
            'purpose' => 'delivery',
            'status' => 'active',
        ]);

        // Filter by operator smart
        $response = $this->getJson('/api/store-phone-numbers?operator=smart');
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone_number', '098111222');

        // Search query
        $searchResponse = $this->getJson('/api/store-phone-numbers?search=999');
        $searchResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone_number', '012999888');
    }

    public function test_my_sim_cards_returns_only_current_user_sims(): void
    {
        $user = $this->createTestUser();
        $otherUser = User::factory()->create([
            'name' => 'Other User',
            'email' => 'other@example.test',
            'role_id' => $user->role_id,
        ]);

        Passport::actingAs($user);

        StorePhoneNumber::create([
            'phone_number' => '012333444',
            'operator' => 'smart',
            'sim_slot' => 'sim_1',
            'assigned_to' => $user->id,
            'status' => 'active',
        ]);

        StorePhoneNumber::create([
            'phone_number' => '088777666',
            'operator' => 'metfone',
            'sim_slot' => 'sim_1',
            'assigned_to' => $otherUser->id,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/my-sim-cards');
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.phone_number', '012333444');
    }

    private function createTestUser(): User
    {
        $role = Role::create([
            'name' => 'employee',
            'slug' => 'employee',
            'is_active' => 1,
        ]);

        // The route is behind MobileFunctionPermission; the role needs the grants.
        foreach (['list_store_phone', 'mobile.menu.store-phone-numbers'] as $key) {
            $permission = \App\Models\Permission::firstOrCreate(
                ['permission_key' => $key],
                ['name' => $key]
            );
            \App\Models\PermissionRole::firstOrCreate([
                'permission_id' => $permission->id,
                'role_id' => $role->id,
            ]);
        }

        return User::factory()->create([
            'name' => 'Test Employee',
            'email' => 'emp@example.test',
            'role_id' => $role->id,
        ]);
    }

    private function createStorePhoneSchema(): void
    {
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Main Branch');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Test Device');
                $table->string('asset_code')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('store_phone_numbers')) {
            Schema::create('store_phone_numbers', function (Blueprint $table) {
                $table->id();
                $table->string('phone_number', 50)->index();
                $table->string('operator', 50)->default('smart');
                $table->string('sim_slot', 50)->default('sim_1');
                $table->string('purpose', 100)->nullable();
                $table->string('plan_type', 50)->default('prepaid');
                $table->string('serial_number', 100)->nullable();
                $table->unsignedBigInteger('asset_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->date('assigned_date')->nullable();
                $table->string('status', 30)->default('active');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    private function prepareTestDatabaseFile(): void
    {
        $dbPath = database_path('testing_database.sqlite');
        if (!File::exists($dbPath)) {
            File::put($dbPath, '');
        }
    }
}
