<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\DeviceRiskEngine;
use App\Modules\AppSecurity\Services\RiskSignalReviewService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RiskSignalReviewServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.foreign_key_constraints' => false]);
        DB::purge('sqlite');
        Schema::connection('sqlite')->create('app_device_integrity_checks', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('device_id'); $table->string('signal'); $table->string('severity');
            $table->unsignedTinyInteger('risk_points'); $table->json('context')->nullable(); $table->timestamp('occurred_at');
            $table->string('review_status')->nullable(); $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable(); $table->string('review_reason', 512)->nullable(); $table->timestamps();
        });
        Schema::connection('sqlite')->create('app_security_audit_logs', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('admin_id')->nullable(); $table->string('actor_type')->nullable();
            $table->string('actor_id')->nullable(); $table->string('action'); $table->string('target_type');
            $table->string('target_id'); $table->json('previous_state')->nullable(); $table->json('new_state')->nullable();
            $table->string('reason', 512); $table->string('ip_address', 45)->nullable(); $table->string('user_agent', 512)->nullable();
            $table->timestamp('occurred_at'); $table->timestamps();
        });
    }

    public function test_false_positive_is_audited_and_recalculates_risk(): void
    {
        $id = DB::table('app_device_integrity_checks')->insertGetId([
            'device_id' => 44, 'signal' => 'root_detected', 'severity' => 'medium', 'risk_points' => 15,
            'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $risk = Mockery::mock(DeviceRiskEngine::class);
        $risk->shouldReceive('calculate')->once()->with(44)->andReturn(['risk_score' => 0]);
        $this->instance(DeviceRiskEngine::class, $risk);

        $result = app(RiskSignalReviewService::class)->review($id, 9, 'false_positive', 'Verified during device check', '203.0.113.5', 'test-agent');

        $this->assertSame('false_positive', $result['review_status']);
        $this->assertSame(0, $result['risk_score']);
        $this->assertDatabaseHas('app_device_integrity_checks', [
            'id' => $id, 'review_status' => 'false_positive', 'reviewed_by' => 9,
            'review_reason' => 'Verified during device check',
        ]);
        $this->assertDatabaseHas('app_security_audit_logs', [
            'action' => 'security.risk_signal.reviewed', 'target_type' => 'integrity_check',
            'target_id' => (string) $id, 'admin_id' => 9, 'reason' => 'Verified during device check',
        ]);
    }

    public function test_critical_signal_cannot_be_marked_false_positive(): void
    {
        $id = DB::table('app_device_integrity_checks')->insertGetId([
            'device_id' => 44, 'signal' => 'request_signature_invalid', 'severity' => 'critical', 'risk_points' => 100,
            'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->expectException(ValidationException::class);
        app(RiskSignalReviewService::class)->review($id, 9, 'false_positive', 'Test reason', null, null);
    }

    public function test_review_is_one_time_and_critical_signals_can_be_confirmed(): void
    {
        $id = DB::table('app_device_integrity_checks')->insertGetId([
            'device_id' => 44, 'signal' => 'request_signature_invalid', 'severity' => 'critical', 'risk_points' => 100,
            'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $result = app(RiskSignalReviewService::class)->review($id, 9, 'confirmed', 'Cryptographic failure verified', null, null);
        $this->assertSame('confirmed', $result['review_status']);
        $this->assertNull($result['risk_score']);

        $this->expectException(HttpException::class);
        app(RiskSignalReviewService::class)->review($id, 9, 'confirmed', 'Duplicate review', null, null);
    }
}
