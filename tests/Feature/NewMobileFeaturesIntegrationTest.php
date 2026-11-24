<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\DashboardApiController;
use App\Models\User;
use App\Models\Role;
use App\Repositories\FeatureRepository;
use App\Services\Authorization\MobilePermissions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class NewMobileFeaturesIntegrationTest extends TestCase
{
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app_security.enabled' => false]);
        $this->employee = (new User)->forceFill(['id' => 999, 'role_id' => 2]);
        $this->employee->setRelation('role', (new Role)->forceFill(['id' => 2, 'slug' => 'employee']));
    }

    public function test_mobile_features_endpoint_returns_all_new_feature_keys(): void
    {
        Gate::before(fn () => true);
        $this->actingAs($this->employee, 'api');

        $response = $this->getJson('/api/mobile-features');
        $response->assertOk();

        $data = collect($response->json('data'))->keyBy('key');

        $this->assertTrue($data->has('store-phone-numbers'), 'Missing store-phone-numbers in mobile-features');
        $this->assertTrue($data->has('company-documents'), 'Missing company-documents in mobile-features');
        $this->assertTrue($data->has('payroll-queue'), 'Missing payroll-queue in mobile-features');
        $this->assertTrue($data->has('assets'), 'Missing assets in mobile-features');
        $this->assertTrue($data->has('certificate'), 'Missing certificate in mobile-features');
    }

    public function test_home_card_definitions_contain_new_mobile_features(): void
    {
        $ctrl = app(DashboardApiController::class);
        $refMethod = new \ReflectionMethod($ctrl, 'homeCardDefinitions');
        $refMethod->setAccessible(true);
        $cards = collect($refMethod->invoke($ctrl))->keyBy('key');

        $this->assertTrue($cards->has('store-phone-numbers'), 'Home cards missing store-phone-numbers');
        $this->assertTrue($cards->has('company-documents'), 'Home cards missing company-documents');
        $this->assertTrue($cards->has('payroll-queue'), 'Home cards missing payroll-queue');
        $this->assertTrue($cards->has('assets'), 'Home cards missing assets');
    }

    public function test_mobile_permissions_config_declares_all_new_features(): void
    {
        $menus = config('mobile_permissions.menus');
        $this->assertArrayHasKey('store-phone-numbers', $menus);
        $this->assertArrayHasKey('company-documents', $menus);
        $this->assertArrayHasKey('payroll-queue', $menus);
        $this->assertArrayHasKey('certificate', $menus);
        $this->assertArrayHasKey('assets', $menus);

        $functions = config('mobile_permissions.functions');
        $this->assertArrayHasKey('StorePhoneNumberApiController@index', $functions);
        $this->assertArrayHasKey('StorePhoneNumberApiController@mySimCards', $functions);
        $this->assertArrayHasKey('CompanyDocumentApiController@index', $functions);
        $this->assertArrayHasKey('CompanyDocumentApiController@alerts', $functions);
        $this->assertArrayHasKey('PayrollQueueApiController@join', $functions);
        $this->assertArrayHasKey('PayrollQueueApiController@ticket', $functions);
    }
}
