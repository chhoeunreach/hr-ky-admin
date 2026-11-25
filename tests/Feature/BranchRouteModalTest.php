<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Modules\DailyRecords\BranchRouteCalculator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchRouteModalTest extends TestCase
{
    private Admin $admin;

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

        Schema::create('admins', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Admin');
            $t->string('email')->default('admin@example.com');
            $t->string('username')->default('admin');
            $t->string('password')->default('password');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Admin');
            $t->string('slug')->default('admin');
            $t->boolean('backend_login_authorize')->default(1);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('companies', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Default Co');
            $t->timestamps();
        });

        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->string('name')->default('Phnom Penh HQ');
            $t->timestamps();
        });

        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->default(1);
            $t->string('dept_name')->default('Logistics');
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Courier Staff');
            $t->string('email')->default('courier@test.com');
            $t->string('username')->default('courier');
            $t->string('password')->default('secret');
            $t->unsignedBigInteger('role_id')->default(1);
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->default(1);
            $t->unsignedBigInteger('department_id')->default(1);
            $t->boolean('status')->default(true);
            $t->boolean('is_active')->default(true);
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('daily_entries', function (Blueprint $t) {
            $t->id();
            $t->string('kind')->default('activity');
            $t->string('status')->default('completed');
            $t->string('title')->default('Deliver branch goods');
            $t->text('body')->nullable();
            $t->json('details')->nullable();
            $t->boolean('private')->default(false);
            $t->timestamp('archived_at')->nullable();
            $t->unsignedBigInteger('employee_id')->default(1);
            $t->unsignedBigInteger('company_id')->default(1);
            $t->unsignedBigInteger('branch_id')->default(1);
            $t->unsignedBigInteger('department_id')->default(1);
            $t->timestamp('occurred_at')->nullable();
            $t->timestamp('ended_at')->nullable();
            $t->timestamps();
        });

        Schema::create('daily_record_files', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('entry_id');
            $t->string('name');
            $t->string('path')->default('daily-records/file.jpg');
            $t->unsignedBigInteger('bytes')->default(1024);
            $t->boolean('restricted')->default(false);
            $t->timestamps();
        });

        Schema::create('daily_report_submissions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('entry_id');
            $t->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $t) {
            $t->id();
            $t->timestamps();
        });

        $this->admin = Admin::create([
            'name' => 'System Admin',
            'username' => 'sysadmin',
            'email' => 'sys@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        Gate::before(fn () => true);
    }

    public function test_branch_route_calculator_computes_accurate_distance_and_durations(): void
    {
        // Distance between Phnom Penh (11.5564, 104.9282) and Chbar Ampov (11.5300, 104.9500)
        $distance = BranchRouteCalculator::distanceKm(11.5564, 104.9282, 11.5300, 104.9500);
        $this->assertNotNull($distance);
        $this->assertGreaterThan(3.0, $distance);
        $this->assertLessThan(6.0, $distance);

        // Same point distance should be 0.0
        $this->assertEquals(0.0, BranchRouteCalculator::distanceKm(11.5564, 104.9282, 11.5564, 104.9282));

        // Format minutes
        $this->assertEquals('25 mins', BranchRouteCalculator::formatMinutes(25));
        $this->assertEquals('2h 15m', BranchRouteCalculator::formatMinutes(135));
        $this->assertEquals('2 ម៉ោង 15 នាទី', BranchRouteCalculator::formatMinutesKhmer(135));
    }

    public function test_branch_routes_page_renders_modal_with_calculated_distance_and_map(): void
    {
        $this->withoutExceptionHandling();
        $rawRoute = [
            'mission_type' => 'branch_route',
            'route_id' => '11111111-2222-3333-4444-555555555555',
            'purpose' => 'Deliver urgent legal documents',
            'origin_branch' => 'Phnom Penh Main HQ',
            'target_branches' => ['Siem Reap Branch'],
            'departure_time' => '08:30',
            'arrival_time' => '10:45',
            'status' => 'completed',
            'total_minutes' => 135,
            'start_latitude' => 11.5564,
            'start_longitude' => 104.9282,
            'checkpoints_data' => [
                [
                    'branch_name' => 'Chbar Ampov Sub-branch',
                    'action_type' => 'arrival',
                    'arrived_at' => '2026-11-25 09:15:00',
                    'latitude' => 11.5300,
                    'longitude' => 104.9500,
                    'notes' => 'Dropped off envelope',
                    'photo_count' => 1,
                ],
                [
                    'branch_name' => 'Siem Reap Branch',
                    'action_type' => 'arrival',
                    'arrived_at' => '2026-11-25 10:45:00',
                    'latitude' => 13.3671,
                    'longitude' => 103.8448,
                    'notes' => 'Received by manager',
                    'photo_count' => 2,
                ],
            ],
            'final_summary' => 'All documents successfully handed over.',
        ];

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Courier Staff',
            'email' => 'courier@test.com',
            'username' => 'courier',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'company_id' => 1,
            'branch_id' => 1,
            'department_id' => 1,
            'status' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('daily_entries')->insert([
            'id' => 10,
            'kind' => 'activity',
            'status' => 'completed',
            'title' => 'Courier Mission #101',
            'details' => json_encode(['fields' => $rawRoute]),
            'employee_id' => 1,
            'company_id' => 1,
            'branch_id' => 1,
            'department_id' => 1,
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('daily_record_files')->insert([
            'id' => 101,
            'entry_id' => 10,
            'name' => 'checkpoint_delivery_photo.jpg',
            'path' => 'daily-records/test_photo.jpg',
            'bytes' => 204800,
            'restricted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::fake('local');
        Storage::disk('local')->put('daily-records/test_photo.jpg', 'fake-image-binary-data');

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-records.branch-routes'));
        $response->assertOk();

        // 1. Check for modal container and map elements
        $response->assertSee('modalRouteDetails');
        $response->assertSee('routeDetailMap');
        $response->assertSee('modalRouteTimeline');

        // 2. Check for KPI elements
        $response->assertSee('kpiTotalDistance');
        $response->assertSee('kpiTotalDuration');
        $response->assertSee('kpiAvgSpeed');
        $response->assertSee('kpiTotalStops');

        // 3. Check for button trigger with payload
        $response->assertSee('btn-open-route-modal');
        $response->assertSee('Deliver urgent legal documents', false);
        $response->assertSee('Phnom Penh Main HQ', false);
        $response->assertSee('Chbar Ampov Sub-branch', false);

        // 4. Check for Leaflet dependencies
        $response->assertSee('leaflet.css');
        $response->assertSee('leaflet.js');

        // 5. Check for attachment popup lightbox viewer and map gallery triggers
        $response->assertSee('modalAttachmentViewer');
        $response->assertSee('viewerMediaContainer');
        $response->assertSee('btnMapQuickGallery');
        $response->assertSee('checkpoint_delivery_photo.jpg', false);
        $response->assertSee('inline=1', false);

        // 6. Test file download and inline preview for full view popup
        $downloadRes = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-records.record-file', [10, 101]));
        $downloadRes->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadRes->headers->get('content-disposition'));

        $inlineRes = $this->actingAs($this->admin, 'admin')->get(route('admin.daily-records.record-file', [10, 101]) . '?inline=1');
        $inlineRes->assertOk();
        $this->assertStringContainsString('inline', (string) $inlineRes->headers->get('content-disposition'));
    }
}
