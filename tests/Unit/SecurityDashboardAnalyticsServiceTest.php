<?php

namespace Tests\Unit;

use App\Modules\AppSecurity\Services\SecurityDashboardAnalyticsService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityDashboardAnalyticsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));
        Schema::connection('sqlite')->create('app_registered_devices', function (Blueprint $table): void {
            $table->id(); $table->timestamp('registered_at')->nullable(); $table->unsignedTinyInteger('risk_score')->default(0);
        });
        Schema::connection('sqlite')->create('app_security_logs', function (Blueprint $table): void {
            $table->id(); $table->string('event_type'); $table->timestamp('occurred_at');
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_analytics_groups_fourteen_day_installations_risk_and_security_events(): void
    {
        DB::table('app_registered_devices')->insert([
            ['registered_at' => now()->subDay(), 'risk_score' => 10],
            ['registered_at' => now(), 'risk_score' => 35],
            ['registered_at' => now(), 'risk_score' => 75],
            ['registered_at' => now()->subDays(20), 'risk_score' => 50],
        ]);
        foreach ([
            'request_signature_invalid', 'request_signature_invalid', 'request_nonce_replayed',
            'registration_attestation_failed', 'attestation_os_policy_failed', 'release_verification_failed',
            'refresh_token_reuse', 'session_concurrency_limit', 'security_session_invalid',
        ] as $eventType) {
            DB::table('app_security_logs')->insert(['event_type' => $eventType, 'occurred_at' => now()]);
        }
        DB::table('app_security_logs')->insert([
            'event_type' => 'request_nonce_replayed', 'occurred_at' => now()->subDays(20),
        ]);

        $analytics = app(SecurityDashboardAnalyticsService::class)->snapshot(30, 60);

        $this->assertSame('2026-11-07', $analytics['from']);
        $this->assertSame('2026-11-20', $analytics['to']);
        $this->assertCount(14, $analytics['installations']);
        $this->assertSame(1, $analytics['installations']->firstWhere('date', '2026-11-19')['count']);
        $this->assertSame(2, $analytics['installations']->firstWhere('date', '2026-11-20')['count']);
        $this->assertSame(['low' => 1, 'medium' => 2, 'high' => 1], $analytics['risk_distribution']);
        $this->assertSame(2, $analytics['suspicious_registrations']);
        $this->assertSame(2, $analytics['events']['Invalid signatures']);
        $this->assertSame(1, $analytics['events']['Replay attempts']);
        $this->assertSame(2, $analytics['events']['Attestation failures']);
        $this->assertSame(1, $analytics['events']['Release verification failures']);
        $this->assertSame(3, $analytics['events']['Session anomalies']);
    }

    public function test_admin_dashboard_renders_the_analytics_panel(): void
    {
        $analytics = app(SecurityDashboardAnalyticsService::class)->snapshot(30, 60);
        $html = view('admin.app-security.partials.analytics', [
            'analytics' => $analytics,
            'canViewAnalytics' => true,
        ])->render();

        $this->assertStringContainsString('Installation trend', $html);
        $this->assertStringContainsString('Current risk distribution', $html);
        $this->assertStringContainsString('Invalid signatures', $html);
        $this->assertStringContainsString('14 days', $html);

        $hiddenHtml = view('admin.app-security.partials.analytics', [
            'analytics' => $analytics,
            'canViewAnalytics' => false,
        ])->render();
        $this->assertStringNotContainsString('Installation trend', $hiddenHtml);
    }
}
