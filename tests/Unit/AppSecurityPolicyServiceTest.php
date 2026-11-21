<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Modules\AppSecurity\Services\SecurityPolicyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppSecurityPolicyServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'cache.default' => 'array',
            'app_security.risk_review_threshold' => 30,
            'app_security.risk_block_threshold' => 60,
            'app_security.risk_signals.root_detected.points' => 15,
            'app_security.risk_signals.debugger_attached.points' => 20,
        ]);
        DB::purge('sqlite');
        Cache::store('array')->flush();
        Schema::connection('sqlite')->create('app_security_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('setting_key')->unique();
            $table->json('setting_value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function test_overrides_are_validated_typed_cached_and_invalidate_after_update(): void
    {
        $policy = app(SecurityPolicyService::class);
        $this->assertSame(15, $policy->value('risk_points_root_detected'));

        $change = $policy->update([
            'customer_auto_approval' => '0',
            'risk_points_root_detected' => '7',
            'risk_points_cross_account_key_reuse' => '45',
            'risk_policy_version' => 'policy-test-2',
            'max_sessions_per_device' => '2',
            'max_devices_staff' => '3',
            'max_devices_customer' => '5',
            'max_devices_admin' => '1',
            'offline_grace_seconds' => '600',
        ], 9);

        $this->assertFalse($change['after']['customer_auto_approval']);
        $this->assertSame(7, $policy->value('risk_points_root_detected'));
        $this->assertSame(7, $policy->signalDefinition('root_detected')['points']);
        $this->assertSame(45, $policy->value('risk_points_cross_account_key_reuse'));
        $this->assertSame(45, $policy->signalDefinition('cross_account_key_reuse')['points']);
        $this->assertSame('policy-test-2', $policy->value('risk_policy_version'));
        $this->assertSame(2, $change['after']['max_sessions_per_device']);
        $this->assertSame(2, $policy->value('max_sessions_per_device'));
        $this->assertSame(600, $change['after']['offline_grace_seconds']);
        $this->assertSame(600, $policy->value('offline_grace_seconds'));
        $this->assertSame(3, $policy->value('max_devices_staff'));
        $this->assertSame(5, $policy->value('max_devices_customer'));
        $this->assertSame(1, $policy->value('max_devices_admin'));
        $this->assertSame(9, DB::table('app_security_settings')->where('setting_key', 'risk_points_root_detected')->value('updated_by'));
    }

    public function test_deployment_security_flag_cannot_be_changed_through_database_policy(): void
    {
        config(['app_security.enabled' => false]);
        $this->expectException(\InvalidArgumentException::class);
        app(SecurityPolicyService::class)->value('enabled');
    }

    public function test_legacy_audit_schema_upgrades_and_settings_save_returns_to_the_form(): void
    {
        $this->createLegacyAuditSchema();
        DB::table('app_security_audit_logs')->insert([
            'admin_id' => 1, 'action' => 'legacy', 'occurred_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_20_111000_add_actor_to_app_security_audit_logs.php');
        $migration->up();
        $migration->up();
        $this->assertDatabaseHas('app_security_audit_logs', ['action' => 'legacy', 'actor_type' => 'admin', 'actor_id' => 1]);

        Schema::create('app_security_admin_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('admin_id');
            $table->string('security_role');
        });
        DB::table('app_security_admin_roles')->insert(['admin_id' => 1, 'security_role' => 'it_admin']);
        $this->actingAs((new Admin)->forceFill(['id' => 1, 'password' => \Illuminate\Support\Facades\Hash::make('Correct-Horse-77!')]), 'admin');
        $url = route('admin.app-security.settings');
        $form = route('admin.app-security.index').'#security-policy-settings';
        $this->get($url)->assertRedirect($form);
        $this->post($url, ['risk_points_root_detected' => 11, 'max_devices_customer' => 4, 'step_up_password' => 'Correct-Horse-77!'])->assertRedirect($form);

        $this->assertSame(11, app(SecurityPolicyService::class)->value('risk_points_root_detected'));
        $this->assertSame(4, app(SecurityPolicyService::class)->value('max_devices_customer'));
        $this->assertDatabaseHas('app_security_audit_logs', [
            'actor_type' => 'admin', 'actor_id' => 1, 'action' => 'security.settings.updated',
        ]);
    }

    public function test_failed_audit_rolls_back_the_setting_without_publishing_it_to_cache(): void
    {
        $policy = app(SecurityPolicyService::class);
        $this->assertSame(15, $policy->value('risk_points_root_detected'));

        try {
            $policy->update(['risk_points_root_detected' => 11], null, function (): void {
                throw new \RuntimeException('Audit write failed.');
            });
            $this->fail('Expected the audit failure to propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Audit write failed.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('app_security_settings', ['setting_key' => 'risk_points_root_detected']);
        $this->assertSame(15, $policy->value('risk_points_root_detected'));
    }

    private function createLegacyAuditSchema(): void
    {
        Schema::create('app_security_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->json('previous_state')->nullable();
            $table->json('new_state')->nullable();
            $table->string('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }
}
