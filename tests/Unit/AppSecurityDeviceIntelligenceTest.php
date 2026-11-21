<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Services\DeviceIntelligenceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityDeviceIntelligenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app.key' => 'base64:'.base64_encode(str_repeat('x', 32)),
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->string('uuid');
            $table->string('version_name')->nullable();
            $table->unsignedBigInteger('version_code')->nullable();
            $table->unsignedSmallInteger('android_sdk')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_intelligence', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->date('observed_date');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->unsignedInteger('authorized_request_count')->default(0);
            $table->unsignedSmallInteger('network_change_count')->default(0);
            $table->char('last_ip_hash', 64)->nullable();
            $table->unsignedBigInteger('version_code')->nullable();
            $table->unsignedSmallInteger('android_sdk')->nullable();
            $table->json('observations')->nullable();
            $table->timestamps();
            $table->unique(['device_id', 'observed_date']);
        });
        DB::table('app_registered_devices')->insert([
            'id' => 1, 'uuid' => 'device-uuid', 'version_name' => '1.0', 'version_code' => 12,
            'android_sdk' => 35, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_authorized_requests_create_daily_privacy_minimized_network_change_summary(): void
    {
        $device = RegisteredDevice::query()->findOrFail(1);
        $service = app(DeviceIntelligenceService::class);
        $first = Request::create('/api/business', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.9']);
        $second = Request::create('/api/business', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.10']);

        $service->recordAuthorizedRequest($device, $first);
        $service->recordAuthorizedRequest($device, $second);

        $row = DB::table('app_device_intelligence')->first();
        $this->assertSame(1, DB::table('app_device_intelligence')->count());
        $this->assertSame(2, (int) $row->authorized_request_count);
        $this->assertSame(1, (int) $row->network_change_count);
        $this->assertSame(12, (int) $row->version_code);
        $this->assertSame(35, (int) $row->android_sdk);
        $this->assertNotSame('203.0.113.10', $row->last_ip_hash);
        $this->assertSame(['network_change' => true], json_decode($row->observations, true));
    }

    public function test_summary_collection_is_skipped_when_optional_table_is_not_installed(): void
    {
        Schema::drop('app_device_intelligence');
        $device = RegisteredDevice::query()->findOrFail(1);
        app(DeviceIntelligenceService::class)->recordAuthorizedRequest(
            $device,
            Request::create('/api/business', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.9'])
        );
        $this->assertFalse(Schema::hasTable('app_device_intelligence'));
    }

    public function test_intelligence_migration_rollback_preserves_a_preexisting_table(): void
    {
        Schema::connection('sqlite')->create('app_security_schema_migration_objects', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name')->unique();
            $table->timestamps();
        });
        $migration = require database_path('migrations/2026_11_20_100000_add_app_device_intelligence.php');
        $migration->down();
        $this->assertTrue(Schema::hasTable('app_device_intelligence'));
    }
}
