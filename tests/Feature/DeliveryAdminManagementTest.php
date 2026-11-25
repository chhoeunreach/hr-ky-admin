<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Delivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeliveryAdminManagementTest extends TestCase
{
    private Admin $admin;
    private User $driver;
    private Branch $branch;

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

        Schema::create('admins', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Admin');
            $t->string('username')->default('admin');
            $t->string('email')->default('admin@test.com');
            $t->string('password');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Default Co');
            $t->string('logo')->nullable();
            $t->timestamps();
        });

        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->string('name')->default('Central Branch');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Courier Sok');
            $t->string('email')->default('courier@test.com');
            $t->string('phone')->nullable()->default('012888999');
            $t->string('avatar')->nullable();
            $t->string('username')->default('courier');
            $t->string('password')->default('secret');
            $t->unsignedBigInteger('role_id')->default(1);
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->default(1);
            $t->unsignedBigInteger('department_id')->nullable();
            $t->boolean('status')->default(true);
            $t->boolean('is_active')->default(true);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('branch_id')->nullable();
            $t->unsignedBigInteger('destination_branch_id')->nullable();
            $t->string('delivery_type', 30)->default('customer');
            $t->string('tracking_token', 64)->unique();
            $t->string('tracking_code', 50)->nullable();
            $t->string('invoice_number', 100)->nullable();
            $t->string('customer_name', 255);
            $t->string('customer_phone', 50);
            $t->string('destination_address', 500)->nullable();
            $t->decimal('destination_latitude', 10, 7)->nullable();
            $t->decimal('destination_longitude', 10, 7)->nullable();
            $t->string('origin_address', 500)->nullable();
            $t->decimal('origin_latitude', 10, 7)->nullable();
            $t->decimal('origin_longitude', 10, 7)->nullable();
            $t->decimal('current_latitude', 10, 7)->nullable();
            $t->decimal('current_longitude', 10, 7)->nullable();
            $t->decimal('heading', 6, 2)->nullable();
            $t->decimal('speed', 6, 2)->nullable();
            $t->timestamp('last_ping_at')->nullable();
            $t->string('status', 30)->default('pending');
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->string('proof_photo_path', 500)->nullable();
            $t->string('driver_selfie', 255)->nullable();
            $t->json('product_photos')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id();
            $t->timestamps();
        });

        $this->admin = Admin::create([
            'name' => 'System Admin',
            'username' => 'sysadmin',
            'email' => 'sysadmin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'name' => 'Phnom Penh Main Branch',
        ]);

        $this->driver = User::create([
            'name' => 'Driver Rathana',
            'email' => 'rathana@test.com',
            'phone' => '098112233',
            'username' => 'rathana',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        Gate::before(fn () => true);
    }

    public function test_deliveries_index_page_loads_with_kpis_and_records(): void
    {
        $this->withoutExceptionHandling();
        $delivery = Delivery::create([
            'user_id' => $this->driver->id,
            'branch_id' => $this->branch->id,
            'tracking_token' => 'test-token-12345',
            'tracking_code' => 'DEL-261125-ABCD',
            'invoice_number' => 'INV-2026-0042',
            'customer_name' => 'Chan Borey',
            'customer_phone' => '012345678',
            'destination_address' => 'Russian Blvd, Phnom Penh',
            'destination_latitude' => 11.5600,
            'destination_longitude' => 104.9000,
            'current_latitude' => 11.5550,
            'current_longitude' => 104.9100,
            'speed' => 32.5,
            'status' => Delivery::STATUS_IN_TRANSIT,
            'started_at' => now(),
            'last_ping_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.deliveries.index'));
        $response->assertOk();

        // Check for KPIs
        $response->assertSee('DEL-261125-ABCD');
        $response->assertSee('INV-2026-0042');
        $response->assertSee('Chan Borey');
        $response->assertSee('Driver Rathana');
        $response->assertSee('012345678');

        // Check for Map and Modals
        $response->assertSee('fleetMap');
        $response->assertSee('modalDeliveryDetails');
        $response->assertSee('modalCreateDelivery');
    }

    public function test_admin_can_dispatch_new_delivery(): void
    {
        $payload = [
            'user_id' => $this->driver->id,
            'branch_id' => $this->branch->id,
            'delivery_type' => 'customer',
            'invoice_number' => 'INV-DISPATCH-101',
            'customer_name' => 'Kosal Seng',
            'customer_phone' => '077998877',
            'destination_address' => 'St. 271, Sangkat Toul Tompoung',
            'destination_latitude' => 11.5350,
            'destination_longitude' => 104.9150,
            'notes' => 'Fragile items inside box',
        ];

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.deliveries.store'), $payload);
        $response->assertRedirect(route('admin.deliveries.index'));

        $this->assertDatabaseHas('deliveries', [
            'user_id' => $this->driver->id,
            'invoice_number' => 'INV-DISPATCH-101',
            'customer_name' => 'Kosal Seng',
            'status' => Delivery::STATUS_PENDING,
        ]);
    }

    public function test_admin_can_update_delivery_status(): void
    {
        $delivery = Delivery::create([
            'user_id' => $this->driver->id,
            'branch_id' => $this->branch->id,
            'tracking_token' => 'token-to-update-123',
            'tracking_code' => 'DEL-STATUS-UPDATE',
            'customer_name' => 'Keo Vanna',
            'customer_phone' => '015112233',
            'status' => Delivery::STATUS_IN_TRANSIT,
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.deliveries.status', $delivery->id), [
            'status' => Delivery::STATUS_DELIVERED,
            'notes' => 'Handed to customer safely',
        ]);

        $response->assertRedirect();

        $delivery->refresh();
        $this->assertEquals(Delivery::STATUS_DELIVERED, $delivery->status);
        $this->assertNotNull($delivery->completed_at);
        $this->assertStringContainsString('Handed to customer safely', $delivery->notes);
    }

    public function test_live_locations_endpoint_returns_active_drivers_json(): void
    {
        Delivery::create([
            'user_id' => $this->driver->id,
            'branch_id' => $this->branch->id,
            'tracking_token' => 'live-loc-token-999',
            'tracking_code' => 'DEL-LIVE-999',
            'customer_name' => 'Live Customer',
            'customer_phone' => '012111222',
            'current_latitude' => 11.5580,
            'current_longitude' => 104.9220,
            'heading' => 90.0,
            'speed' => 25.0,
            'status' => Delivery::STATUS_IN_TRANSIT,
            'last_ping_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.deliveries.live-locations'));
        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('count', 1);
        $response->assertJsonPath('deliveries.0.tracking_code', 'DEL-LIVE-999');
        $response->assertJsonPath('deliveries.0.driver_name', 'Driver Rathana');
        $response->assertJsonPath('deliveries.0.speed', 25);
    }

    public function test_show_endpoint_returns_delivery_details_json(): void
    {
        $delivery = Delivery::create([
            'user_id' => $this->driver->id,
            'branch_id' => $this->branch->id,
            'tracking_token' => 'detail-token-888',
            'tracking_code' => 'DEL-DETAIL-888',
            'customer_name' => 'Detail Customer',
            'customer_phone' => '012999000',
            'status' => Delivery::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.deliveries.show', $delivery->id));
        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('delivery.tracking_code', 'DEL-DETAIL-888');
        $response->assertJsonPath('delivery.customer_name', 'Detail Customer');
    }

    public function test_customer_public_tracking_page_renders(): void
    {
        $delivery = Delivery::create([
            'user_id' => $this->driver->id,
            'branch_id' => $this->branch->id,
            'tracking_token' => 'pub-live-token-555',
            'tracking_code' => 'DEL-PUB-555',
            'customer_name' => 'Public Customer Test',
            'customer_phone' => '012555666',
            'status' => Delivery::STATUS_IN_TRANSIT,
        ]);

        $response = $this->get(route('delivery.track.live', 'pub-live-token-555'));
        $response->assertOk();
        $response->assertSee('Public Customer Test');
        $response->assertSee('Driver Rathana');
    }
}
