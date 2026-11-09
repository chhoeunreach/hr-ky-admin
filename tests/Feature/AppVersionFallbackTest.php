<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\DashboardApiController;
use App\Helpers\MobileServerConfiguration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppVersionFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->text('value')->nullable();
            $table->boolean('status')->default(true);
        });
    }

    public function test_app_version_exposes_mobile_backup_with_app_backup_as_fallback(): void
    {
        config(['app.fallback_url' => 'https://backup.example', 'app.mobile_app_fallback_url' => null]);
        $controller = app(DashboardApiController::class);
        $response = $controller->appVersion(Request::create('/api/app-version'));
        $data = $response->getData(true);
        $this->assertTrue($data['status']);
        $this->assertSame('https://backup.example/', $data['data']['server_fallback_url']);

        config(['app.mobile_app_fallback_url' => 'https://mobile-backup.example']);
        $data = $controller->appVersion(Request::create('/api/app-version'))->getData(true);
        $this->assertSame('https://mobile-backup.example/', $data['data']['server_fallback_url']);
    }

    public function test_backup_can_be_disabled(): void
    {
        config(['app.fallback_url' => null, 'app.mobile_app_fallback_url' => null]);
        $data = app(DashboardApiController::class)
            ->appVersion(Request::create('/api/app-version'))->getData(true);
        $this->assertArrayHasKey('server_fallback_url', $data['data']);
        $this->assertNull($data['data']['server_fallback_url']);
    }

    public function test_backup_qr_contains_both_addresses_and_the_page_keeps_legacy_qr(): void
    {
        config(['app.mobile_app_url' => 'http://192.168.0.113:8000',
            'app.mobile_app_fallback_url' => 'http://192.168.0.113:8001']);
        $payload = MobileServerConfiguration::qrPayload();
        $this->assertSame([
            'server_url' => 'http://192.168.0.113:8000/',
            'server_fallback_url' => 'http://192.168.0.113:8001/',
        ], json_decode(base64_decode($payload), true));
        $html = view('admin.print_qr', [
            'url' => base64_encode(MobileServerConfiguration::primaryUrl()),
            'backupUrl' => $payload,
        ])->render();
        $this->assertStringContainsString('Connect with automatic backup', $html);
        $this->assertStringContainsString('For older mobile apps', $html);
        $this->assertSame(2, substr_count($html, '<svg'));
    }

    public function test_invalid_or_same_server_backup_is_not_published(): void
    {
        config(['app.mobile_app_url' => 'https://primary.example/',
            'app.mobile_app_fallback_url' => 'javascript:alert(1)',
            'app.fallback_url' => null]);
        $this->assertNull(MobileServerConfiguration::fallbackUrl());
        $this->assertNull(MobileServerConfiguration::qrPayload());
        config(['app.mobile_app_fallback_url' => 'https://primary.example']);
        $this->assertNull(MobileServerConfiguration::fallbackUrl());
    }
}
