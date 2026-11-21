<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\AppSecurity\Services\RegistrationSecurityEventReporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrationSecurityEventReporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.alert_notifications_enabled' => false,
            'app_security.alert_recipients' => [],
        ]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_security_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->string('severity');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->uuid('request_uuid')->unique();
            $table->string('ip_address')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('occurred_at');
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
    }

    public function test_rejected_proof_is_logged_and_alerted_without_storing_key_material(): void
    {
        $user = (new User())->forceFill(['id' => 52]);
        $reporter = app(RegistrationSecurityEventReporter::class);

        $reporter->report($user, 'test-challenge', 'registration_proof_invalid', '203.0.113.7');
        $reporter->report($user, 'test-challenge', 'registration_proof_invalid', '203.0.113.7');

        $this->assertSame(2, DB::table('app_security_logs')->count());
        $this->assertDatabaseHas('app_security_logs', [
            'event_type' => 'registration_proof_invalid', 'severity' => 'critical',
            'user_id' => 52, 'ip_address' => '203.0.113.7',
        ]);
        $logDetails = json_decode((string) DB::table('app_security_logs')->value('details'), true);
        $this->assertSame(['challenge_id' => 'test-challenge'], $logDetails);
        $this->assertSame(1, DB::table('app_security_alerts')
            ->where('alert_type', 'registration_proof_invalid')->count());
        $this->assertSame(52, (int) DB::table('app_security_alerts')->value('user_id'));
    }

    public function test_unknown_registration_failure_is_ignored(): void
    {
        app(RegistrationSecurityEventReporter::class)->report(
            (new User())->forceFill(['id' => 53]), 'challenge', 'unmapped_failure'
        );

        $this->assertSame(0, DB::table('app_security_logs')->count());
        $this->assertSame(0, DB::table('app_security_alerts')->count());
    }

    public function test_untrusted_release_or_signer_is_high_severity(): void
    {
        app(RegistrationSecurityEventReporter::class)->report(
            (new User())->forceFill(['id' => 54]), 'release-challenge', 'registration_release_rejected'
        );

        $this->assertDatabaseHas('app_security_alerts', [
            'alert_type' => 'registration_release_rejected', 'severity' => 'high', 'user_id' => 54,
        ]);
    }
}
