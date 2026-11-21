<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Web\AppSettingController;
use App\Http\Controllers\Web\DashboardController;
use App\Helpers\MobileServerConfiguration;
use App\Models\User;
use App\Modules\AppSecurity\Services\AdminSecurityAuthorization;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            $table->string('name')->nullable();
            $table->string('slug');
            $table->text('value')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function test_saved_download_url_reaches_version_check_and_targeted_update_notification(): void
    {
        DB::unprepared("
            CREATE TABLE users (id INTEGER PRIMARY KEY, company_id INTEGER, status TEXT, is_active INTEGER, fcm_token TEXT, deleted_at DATETIME);
            CREATE TABLE notifications (id INTEGER PRIMARY KEY, title TEXT, description TEXT, type TEXT, notification_for_id INTEGER, notification_publish_date DATETIME, company_id INTEGER, is_active INTEGER, created_by INTEGER, created_at DATETIME, updated_at DATETIME);
            CREATE TABLE user_notifications (id INTEGER PRIMARY KEY, notification_id INTEGER, user_id INTEGER, is_seen INTEGER, created_at DATETIME, updated_at DATETIME);
            INSERT INTO users VALUES (1, 1, 'verified', 1, NULL, NULL), (2, 1, 'verified', 1, NULL, NULL), (3, 1, 'verified', 1, NULL, NULL);
        ");
        (require database_path('migrations/2026_11_20_105000_add_app_update_payload_to_notifications.php'))->up();
        $this->actingAs(User::findOrFail(1));
        Gate::before(fn ($user, $ability) => $ability === 'app_setting' ? true : null);
        $androidUrl = 'https://downloads.example.com/hr.apk?release=14';
        $settings = app(AppSettingController::class);
        $saved = $settings->updateAppVersion(Request::create('/', 'POST', [
            'target_version' => '14.0', 'min_version' => '13.5', 'enabled' => 1,
            'android_url' => $androidUrl, 'ios_url' => 'https://apps.apple.com/example',
        ]));
        $this->assertNotNull($saved->getSession()->get('success'));

        $version = app(DashboardApiController::class)
            ->appVersion(Request::create('/api/app-version', 'GET', ['app_version' => '13.0']))->getData(true)['data'];
        $this->assertSame($androidUrl, $version['android_url']);
        $this->assertTrue($version['force_update']);

        $sent = $settings->sendAppUpdateAlert(Request::create('/', 'POST', [
            'alert_title' => 'version from HR',
            'alert_message' => "Install :target_version.\nThank you.",
            'target_audience' => 'employees', 'employee_ids' => [2],
        ]));
        $this->assertNotNull($sent->getSession()->get('success'));

        $notifications = app(NotificationApiController::class);
        $request = Request::create('/api/notifications');
        $this->actingAs(User::findOrFail(2));
        $data = $notifications->getAllRecentPublishedNotification($request)->response()->getData(true)['data'];
        $this->assertCount(1, $data);
        $this->assertSame('app_update', $data[0]['type']);
        $this->assertSame('version from HR', $data[0]['notification_title']);
        $this->assertSame("Install 14.0.\nThank you.", $data[0]['description']);
        $this->assertSame($androidUrl, $data[0]['app_update']['android_url']);
        $this->assertSame('13.5', $data[0]['app_update']['min_required_version']);

        $this->actingAs(User::findOrFail(3));
        $data = $notifications->getAllRecentPublishedNotification($request)->response()->getData(true)['data'];
        $this->assertSame([], $data);
    }

    public function test_uploaded_apk_is_the_fallback_before_version_settings_are_created(): void
    {
        DB::table('app_settings')->insert([
            'slug' => 'android-apk', 'value' => 'downloads/hr.apk', 'status' => 1,
        ]);
        $response = app(DashboardApiController::class)->appVersion(Request::create('/api/app-version'));
        $this->assertSame(asset('downloads/hr.apk'), $response->getData(true)['data']['android_url']);
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

    public function test_show_qr_page_renders_only_the_single_scan_connect_qr(): void
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
        ])->render();
        $this->assertStringNotContainsString('Connect with automatic backup', $html);
        $this->assertStringNotContainsString('For older mobile apps', $html);
        $this->assertSame(1, substr_count($html, '<svg'));
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

    public function test_print_qr_uses_the_current_request_host_instead_of_static_config(): void
    {
        config(['app.mobile_app_url' => 'https://hr.kytech.info',
            'app.mobile_app_fallback_url' => 'http://192.168.0.113:8001']);
        $request = Request::create('http://192.168.0.113:8000/showQR');
        $this->assertSame('http://192.168.0.113:8000/', MobileServerConfiguration::primaryUrlFromRequest($request));
        $this->assertSame([
            'server_url' => 'http://192.168.0.113:8000/',
            'server_fallback_url' => 'http://192.168.0.113:8001/',
        ], json_decode(base64_decode(MobileServerConfiguration::qrPayloadForRequest($request)), true));
    }

    public function test_print_qr_falls_back_to_static_config_for_localhost_requests(): void
    {
        config(['app.mobile_app_url' => 'https://hr.kytech.info',
            'app.mobile_app_fallback_url' => 'https://hr.rvstechsolution.com']);
        $request = Request::create('http://localhost:8000/showQR');
        $this->assertSame('https://hr.kytech.info/', MobileServerConfiguration::primaryUrlFromRequest($request));
        $this->assertSame([
            'server_url' => 'https://hr.kytech.info/',
            'server_fallback_url' => 'https://hr.rvstechsolution.com/',
        ], json_decode(base64_decode(MobileServerConfiguration::qrPayloadForRequest($request)), true));
    }

    public function test_request_qr_skips_backup_matching_the_request_host(): void
    {
        config(['app.mobile_app_url' => 'https://hr.kytech.info',
            'app.mobile_app_fallback_url' => 'http://192.168.0.113:8000']);
        $request = Request::create('http://192.168.0.113:8000/showQR');
        $this->assertNull(MobileServerConfiguration::qrPayloadForRequest($request));
    }

    public function test_admin_pairing_qr_uses_configured_https_when_app_security_is_enabled(): void
    {
        config(['app_security.enabled' => true, 'app.mobile_app_url' => 'https://hr.example.com/']);
        $request = Request::create('http://192.168.0.113:8000/admin/showQR');
        $controller = app(DashboardController::class);
        $view = $controller->showQR($request);
        $this->assertSame('https://hr.example.com/', base64_decode($view->getData()['url']));
        $this->assertFalse($view->getData()['requiresHttps']);

        config(['app.mobile_app_url' => 'http://localhost:8000']);
        $view = $controller->showQR($request);
        $this->assertNull($view->getData()['url']);
        $this->assertStringContainsString(__('app_settings.pairing_requires_https'), $view->render());
    }

    public function test_admin_pairing_qr_describes_the_local_android_debug_address(): void
    {
        config(['app_security.enabled' => false, 'app.mobile_app_url' => 'http://127.0.0.1:8000']);
        $view = app(DashboardController::class)
            ->showQR(Request::create('http://127.0.0.1:8000/admin/showQR'));
        $this->assertSame('http://127.0.0.1:8000/', base64_decode($view->getData()['url']));
        $this->assertStringContainsString(__('app_settings.pairing_debug_usb'), $view->render());
    }

    public function test_disabled_app_security_policy_is_available_without_its_database_tables(): void
    {
        config(['app_security.enabled' => false]);
        $response = $this->getJson('/api/app-security/v1/config')
            ->assertOk()
            ->assertJsonPath('enabled', false);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_mobile_controls_link_to_existing_settings_using_their_permissions(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 10]));
        Gate::before(fn ($user, $ability) => in_array($ability, ['app_setting', 'role_permission'], true));
        $this->mock(AdminSecurityAuthorization::class, function ($authorization): void {
            $authorization->shouldReceive('allowsAny')->with('app_security_view', 'app_security_customer_support')
                ->andReturn(false);
        });

        $html = view('admin.section.partial.mobileTool')->render();
        $this->assertStringContainsString('href="#mobileAppControls" data-href="#"', $html);
        foreach (['admin.showQR', 'admin.app-settings.index', 'admin.roles.index'] as $route) {
            $url = route($route);
            $this->assertStringContainsString('href="'.$url.'"', $html);
            $this->assertStringContainsString('data-href="'.$url.'"', $html);
        }
        $this->assertStringNotContainsString(route('admin.app-security.index'), $html);
        $this->assertStringNotContainsString(route('admin.feature.index'), $html);
    }
}
