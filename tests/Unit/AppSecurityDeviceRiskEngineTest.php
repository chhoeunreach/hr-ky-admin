<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\DeviceRiskEngine;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Jobs\SendAppSecurityAlertEmail;
use App\Models\User;
use App\Modules\AppSecurity\Controllers\AppSecurityController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AppSecurityDeviceRiskEngineTest extends TestCase
{
    private const DEVICE_ID = 8;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.risk_enabled' => true,
            'app_security.risk_window_hours' => 168,
            'app_security.risk_policy_version' => 'test-v3',
            'app_security.risk_signals' => [
                'attestation_missing' => ['points' => 10, 'severity' => 'low'],
                'security_session_invalid' => ['points' => 25, 'severity' => 'high'],
                'request_nonce_replayed' => ['points' => 40, 'severity' => 'high'],
                'request_signature_invalid' => ['points' => 100, 'severity' => 'critical'],
                'root_detected' => ['points' => 15, 'severity' => 'medium'],
                'emulator_detected' => ['points' => 15, 'severity' => 'medium'],
                'instrumentation_detected' => ['points' => 25, 'severity' => 'high'],
                'debug_build' => ['points' => 10, 'severity' => 'low'],
                'debugger_attached' => ['points' => 20, 'severity' => 'medium'],
                'hooking_detected' => ['points' => 30, 'severity' => 'high'],
                'vpn_detected' => ['points' => 5, 'severity' => 'low'],
                'registration_velocity' => ['points' => 20, 'severity' => 'medium'],
                'cross_account_key_reuse' => ['points' => 35, 'severity' => 'high'],
                'network_transport_wifi' => ['points' => 0, 'severity' => 'info'],
            ],
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->unsignedBigInteger('user_id')->default(1);
            $table->string('device_status');
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_integrity_checks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('signal');
            $table->string('severity');
            $table->unsignedTinyInteger('risk_points');
            $table->text('context')->nullable();
            $table->timestamp('occurred_at');
            $table->string('review_status')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('dedupe_key', 191)->unique();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('alert_type');
            $table->string('severity');
            $table->string('title');
            $table->text('message');
            $table->text('context')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_device_risk_scores', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->unsignedTinyInteger('risk_score');
            $table->string('risk_level');
            $table->string('policy_version');
            $table->text('reasons')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
        });
        DB::table('app_registered_devices')->insert([
            'id' => self::DEVICE_ID, 'uuid' => 'device-uuid', 'user_id' => 1,
            'device_status' => 'approved', 'risk_score' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_recent_signals_accumulate_into_explained_policy_versioned_history(): void
    {
        $engine = app(DeviceRiskEngine::class);
        $engine->recordSignal(self::DEVICE_ID, 'attestation_missing');
        $medium = $engine->recordSignal(self::DEVICE_ID, 'security_session_invalid');
        $high = $engine->recordSignal(self::DEVICE_ID, 'request_nonce_replayed');

        $this->assertSame(35, $medium['risk_score']);
        $this->assertSame('medium', $medium['risk_level']);
        $this->assertSame(75, $high['risk_score']);
        $this->assertSame('high', $high['risk_level']);
        $this->assertSame('test-v3', $high['policy_version']);
        $this->assertCount(3, $high['reasons']);
        $this->assertSame(75, DB::table('app_registered_devices')->value('risk_score'));
        $this->assertSame('approved', DB::table('app_registered_devices')->value('device_status'));
        $this->assertSame(3, DB::table('app_device_risk_scores')->count());
        $latest = RegisteredDevice::query()->findOrFail(self::DEVICE_ID)->latestRiskScore;
        $this->assertSame(75, $latest->risk_score);
        $this->assertCount(3, $latest->reasons['signals']);
        $this->assertSame(3, DB::table('app_security_alerts')->count());
        app(DeviceRiskEngine::class)->calculate(self::DEVICE_ID);
        $this->assertSame(3, DB::table('app_security_alerts')->count());
    }

    public function test_critical_security_signal_creates_one_deduplicated_alert(): void
    {
        $engine = app(DeviceRiskEngine::class);
        $engine->recordSignal(self::DEVICE_ID, 'request_signature_invalid', ['path' => 'api/profile']);
        $engine->recordSignal(self::DEVICE_ID, 'request_signature_invalid', ['path' => 'api/profile']);

        $this->assertSame(1, DB::table('app_security_alerts')
            ->where('alert_type', 'risk_signal_request_signature_invalid')->count());
        $alert = DB::table('app_security_alerts')->where('alert_type', 'risk_signal_request_signature_invalid')->first();
        $this->assertSame('critical', $alert->severity);
        $this->assertSame(self::DEVICE_ID, (int) $alert->device_id);
    }

    public function test_registration_velocity_signal_creates_one_review_alert(): void
    {
        $engine = app(DeviceRiskEngine::class);
        $engine->recordSignal(self::DEVICE_ID, 'registration_velocity', ['registrations_in_window' => 6]);
        $engine->recordSignal(self::DEVICE_ID, 'registration_velocity', ['registrations_in_window' => 7]);

        $alert = DB::table('app_security_alerts')->where('alert_type', 'risk_signal_registration_velocity')->first();
        $this->assertNotNull($alert);
        $this->assertSame('medium', $alert->severity);
        $this->assertSame(1, DB::table('app_security_alerts')->where('alert_type', 'risk_signal_registration_velocity')->count());
        $this->assertStringContainsString('shared networks can produce false positives', $alert->message);
    }

    public function test_false_positive_reviews_leave_future_scores_but_critical_events_always_count(): void
    {
        DB::table('app_device_integrity_checks')->insert([
            ['device_id' => self::DEVICE_ID, 'signal' => 'root_detected', 'severity' => 'medium', 'risk_points' => 15,
                'occurred_at' => now(), 'review_status' => 'false_positive', 'created_at' => now(), 'updated_at' => now()],
            ['device_id' => self::DEVICE_ID, 'signal' => 'root_detected', 'severity' => 'medium', 'risk_points' => 15,
                'occurred_at' => now(), 'review_status' => null, 'created_at' => now(), 'updated_at' => now()],
            ['device_id' => self::DEVICE_ID, 'signal' => 'request_signature_invalid', 'severity' => 'critical', 'risk_points' => 100,
                'occurred_at' => now(), 'review_status' => 'false_positive', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $score = app(DeviceRiskEngine::class)->calculate(self::DEVICE_ID);

        $this->assertSame(100, $score['risk_score']);
        $this->assertSame(['request_signature_invalid'], $score['critical_signals']);
        $this->assertSame(1, $score['reasons'][0]['count']);
    }

    public function test_high_risk_alert_email_is_queued_once_when_configured(): void
    {
        config([
            'app_security.alert_notifications_enabled' => true,
            'app_security.alert_recipients' => ['security@example.test'],
        ]);
        Queue::fake();

        $engine = app(DeviceRiskEngine::class);
        $engine->recordSignal(self::DEVICE_ID, 'request_signature_invalid');
        $engine->calculate(self::DEVICE_ID);

        $this->assertSame(1, DB::table('app_security_alerts')->count());
        Queue::assertPushed(SendAppSecurityAlertEmail::class, 1);
    }

    public function test_critical_cryptographic_failure_is_preserved_and_does_not_auto_block(): void
    {
        $score = app(DeviceRiskEngine::class)->recordSignal(self::DEVICE_ID, 'request_signature_invalid');

        $this->assertSame(100, $score['risk_score']);
        $this->assertSame(['request_signature_invalid'], $score['critical_signals']);
        $this->assertSame('approved', DB::table('app_registered_devices')->value('device_status'));
    }

    public function test_runtime_integrity_signals_are_review_only(): void
    {
        $user = new User();
        $user->id = 1;
        $request = \Illuminate\Http\Request::create(
            '/api/app-security/v1/integrity/report', 'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['signals' => ['debugger_attached', 'hooking_detected']], JSON_THROW_ON_ERROR)
        );
        $request->setUserResolver(fn ($guard = null) => $user);
        $request->headers->set('X-KY-Device', 'device-uuid');

        $response = app(AppSecurityController::class)->reportIntegrity($request, app(DeviceRiskEngine::class));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(50, $response->getData(true)['risk_score']);
        $this->assertFalse($response->getData(true)['automatic_block']);
        $this->assertSame('approved', DB::table('app_registered_devices')->value('device_status'));
    }

    public function test_vpn_and_transport_reports_are_review_only_and_do_not_block(): void
    {
        $user = new User();
        $user->id = 1;
        $request = \Illuminate\Http\Request::create(
            '/api/app-security/v1/integrity/report', 'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['signals' => ['vpn_detected', 'network_transport_wifi']], JSON_THROW_ON_ERROR)
        );
        $request->setUserResolver(fn ($guard = null) => $user);
        $request->headers->set('X-KY-Device', 'device-uuid');

        $response = app(AppSecurityController::class)->reportIntegrity($request, app(DeviceRiskEngine::class));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(5, $response->getData(true)['risk_score']);
        $this->assertFalse($response->getData(true)['automatic_block']);
        $this->assertSame('approved', DB::table('app_registered_devices')->value('device_status'));
    }

    public function test_expired_signals_no_longer_contribute_to_current_score(): void
    {
        $engine = app(DeviceRiskEngine::class);
        $engine->recordSignal(self::DEVICE_ID, 'request_nonce_replayed');
        DB::table('app_device_integrity_checks')->update(['occurred_at' => now()->subDays(8)]);

        $score = $engine->calculate(self::DEVICE_ID);

        $this->assertSame(0, $score['risk_score']);
        $this->assertSame('low', $score['risk_level']);
        $this->assertSame([], $score['reasons']);
    }

    public function test_authenticated_integrity_report_accepts_only_signal_names_and_never_blocks(): void
    {
        $user = new User();
        $user->id = 1;
        $request = \Illuminate\Http\Request::create(
            '/api/app-security/v1/integrity/report', 'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['signals' => ['root_detected', 'emulator_detected']], JSON_THROW_ON_ERROR)
        );
        $request->setUserResolver(fn ($guard = null) => $user);
        $request->headers->set('X-KY-Device', 'device-uuid');

        $response = app(AppSecurityController::class)->reportIntegrity($request, app(DeviceRiskEngine::class));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(30, $response->getData(true)['risk_score']);
        $this->assertFalse($response->getData(true)['automatic_block']);
        $this->assertSame('approved', DB::table('app_registered_devices')->value('device_status'));

        $invalid = \Illuminate\Http\Request::create(
            '/api/app-security/v1/integrity/report', 'POST', [], [], [],
            ['CONTENT_TYPE' => 'application/json'], json_encode(['signals' => ['user_supplied_points']], JSON_THROW_ON_ERROR)
        );
        $invalid->setUserResolver(fn ($guard = null) => $user);
        $invalid->headers->set('X-KY-Device', 'device-uuid');
        try {
            app(AppSecurityController::class)->reportIntegrity($invalid, app(DeviceRiskEngine::class));
            $this->fail('Unrecognized client risk signals must be rejected.');
        } catch (ValidationException) {
            $this->assertSame(2, DB::table('app_device_integrity_checks')->count());
        }
    }
}
