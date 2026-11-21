<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Services\AttestationOsPolicyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttestationOsPolicyServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app_security.minimum_attested_android_os_version' => 130000,
            'app_security.minimum_attested_android_patch_level' => 202511,
        ]);
        DB::purge('sqlite');
    }

    public function test_verified_claims_must_meet_both_configured_floors(): void
    {
        $policy = app(AttestationOsPolicyService::class);

        $this->assertTrue($policy->evaluate(['os_version' => 130000, 'os_patch_level' => 202511])['compliant']);
        $this->assertSame(['os_version_below_minimum'], $policy->evaluate([
            'os_version' => 120000,
            'os_patch_level' => 202511,
        ])['reasons']);
        $this->assertSame(['os_patch_below_minimum'], $policy->evaluate([
            'os_version' => 130000,
            'os_patch_level' => 202510,
        ])['reasons']);
    }

    public function test_missing_or_unverified_claims_fail_closed_when_a_floor_is_configured(): void
    {
        $policy = app(AttestationOsPolicyService::class);

        $this->assertSame(['verified_attestation_required'], $policy->evaluate(null, false)['reasons']);
        $this->assertSame(['os_version_claim_missing', 'os_patch_claim_missing'], $policy->evaluate([])['reasons']);
    }

    public function test_policy_defaults_to_disabled_and_does_not_require_attestation(): void
    {
        config([
            'app_security.minimum_attested_android_os_version' => 0,
            'app_security.minimum_attested_android_patch_level' => 0,
        ]);

        $result = app(AttestationOsPolicyService::class)->evaluate(null, false);

        $this->assertTrue($result['compliant']);
        $this->assertFalse(app(AttestationOsPolicyService::class)->enabled());
    }

    public function test_device_without_verified_attestation_fails_closed_when_policy_is_active(): void
    {
        Schema::connection('sqlite')->create('app_device_attestations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('verification_status');
            $table->json('verification_summary')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
        $device = new RegisteredDevice();
        $device->id = 44;

        $result = app(AttestationOsPolicyService::class)->forDevice($device);

        $this->assertFalse($result['compliant']);
        $this->assertSame(['verified_attestation_required'], $result['reasons']);
    }
}
