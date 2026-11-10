<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SellOut\CommissionSettingsService;
use App\Services\SellOut\ReportingRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class CommissionSettingsTest extends MobilePosReportingTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::table('permissions',function (Blueprint $t) {$t->string('name')->nullable(); $t->integer('permission_groups_id')->nullable();});
        (require database_path('migrations/2026_11_10_000002_create_commission_settings.php'))->up();
        app(CommissionSettingsService::class)->refresh();
    }

    private function configure(array $changes): array
    {
        $c = array_replace_recursive(CommissionSettingsService::defaults(),$changes);
        DB::table('commission_settings')->where('id',1)->update(['configuration'=>json_encode($c)]);
        app(CommissionSettingsService::class)->refresh();
        return $c;
    }

    private function reports(array $query = []): string
    {
        return '/api/sell-out-reports?'.http_build_query($query + ['paginated'=>1,'date_from'=>'2026-11-09','date_to'=>'2026-11-09']);
    }

    public function test_server_configuration_drives_rates_bases_and_conditions_consistently(): void
    {
        $this->configure(['rules'=>[
            'sell'=>['rate'=>0.50,'part_time_quantity'=>20,'full_time_quantity'=>100],
            'material'=>['rate'=>2,'basis'=>'item','minimum_amount'=>0,'require_phone'=>false],
        ]]);
        $this->getJson($this->reports())->assertOk()->assertJsonPath('summary.commission',52.4)
            ->assertJsonPath('commission_policy.rules.sell.rate',0.5);
        $this->assertSame(30.0,ReportingRules::calculate('Sell',60,120,'012345678','full_timer',100));
        $this->assertSame(4.0,ReportingRules::calculate('Material',2,1,'','full_timer'));
        $this->assertSame(0.0,ReportingRules::calculate('Sell',99,120,'012345678','full_timer'));
    }

    public function test_disabling_a_service_or_all_earning_takes_effect_without_rewriting_saved_commissions(): void
    {
        DB::table('sell_out_reports')->where('id',1)->update(['commission'=>17.5]);
        $this->configure(['rules'=>['sell'=>['enabled'=>false]]]);
        $this->getJson($this->reports())->assertOk()->assertJsonPath('summary.commission',0.65);
        $this->getJson('/api/sell-out-reports/1')->assertOk()->assertJsonPath('data.commission','17.50');
        $this->configure(['enabled'=>false]);
        $this->getJson($this->reports(['commission_mode'=>'without_conditions']))->assertOk()->assertJsonPath('summary.commission',0);
        $this->assertEquals(17.5,DB::table('sell_out_reports')->where('id',1)->value('commission'));
    }

    public function test_hidden_roles_cannot_extract_commission_using_reports_details_exports_or_policy(): void
    {
        $this->configure(['roles'=>['2'=>['view'=>false]]]);
        $this->getJson($this->reports(['report_status'=>'qualified','commission_mode'=>'without_conditions']))->assertOk()
            ->assertJsonPath('commission_policy.visible',false)->assertJsonCount(6,'data')
            ->assertJsonMissingPath('summary.commission')->assertJsonMissingPath('categories.0.commission')->assertJsonMissingPath('summary.qualified')
            ->assertJsonMissingPath('data.0.report_commission')->assertJsonMissingPath('data.0.report_status');
        $this->getJson('/api/sell-out-reports')->assertOk()->assertJsonMissingPath('summary.total_commission')
            ->assertJsonPath('data.0.commission',null);
        $this->getJson('/api/sell-out-reports/1')->assertOk()->assertJsonPath('data.commission',null);
        $this->getJson('/api/sell-out-reports/commission-policy')->assertOk()->assertJsonPath('data.can_calculate',false)->assertJsonCount(0,'data.rules');
        $csv = $this->get($this->reports(['export'=>'csv']))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Report commission',$csv);
        $this->assertStringNotContainsString('Commission status',$csv);
    }

    public function test_without_calculation_access_only_saved_commissions_are_returned(): void
    {
        DB::table('sell_out_reports')->where('id',1)->update(['commission'=>7.25]);
        $this->configure(['roles'=>['2'=>['calculate'=>false,'change_mode'=>true]]]);
        $this->getJson($this->reports(['commission_mode'=>'without_conditions']))->assertOk()
            ->assertJsonPath('commission_policy.can_calculate',false)
            ->assertJsonPath('commission_policy.can_change_mode',false)->assertJsonPath('summary.commission',7.25);
    }

    public function test_unauthorized_mode_switch_is_ignored_on_the_server(): void
    {
        DB::table('sell_out_reports')->where('user_id',1)->update(['customer_phone'=>'']);
        $this->configure(['roles'=>['2'=>['change_mode'=>false]]]);
        $this->getJson($this->reports(['commission_mode'=>'without_conditions']))->assertOk()
            ->assertJsonPath('filters.commission_mode','with_conditions')->assertJsonPath('summary.commission',0);
        $this->configure(['default_mode'=>'without_conditions','allow_mode_switch'=>false]);
        $this->getJson($this->reports(['commission_mode'=>'with_conditions']))->assertOk()
            ->assertJsonPath('filters.commission_mode','without_conditions')->assertJsonPath('summary.commission',25.65);
    }

    public function test_admin_can_enable_commission_for_each_type_explicitly(): void
    {
        $this->configure(['rules'=>['buy_in'=>['enabled'=>true,'rate'=>3,'require_phone'=>false]]]);
        $this->getJson($this->reports(['type'=>'buy_in']))->assertOk()->assertJsonPath('summary.commission',3)
            ->assertJsonPath('summary.sales_amount',0)->assertJsonPath('summary.buy_in_amount',500);
        $this->assertSame(3.0,ReportingRules::calculate('Buy In',1,500,'','full_timer'));
        $this->assertSame(0.0,ReportingRules::calculate('iCloud Cus',1,500,'','full_timer'));
    }

    public function test_fractional_rates_round_per_invoice_before_summing(): void
    {
        $this->configure(['rules'=>['iron'=>['rate'=>0.3333]]]);
        DB::table('sell_out_reports')->where('id',4)->update(['service_type'=>'Iron']);
        DB::table('sell_out_reports')->whereIn('id',[3,5])->update(['service_type'=>'Iron']);
        $this->getJson($this->reports(['type'=>'iron']))->assertOk()
            ->assertJsonPath('summary.commission',0.99)->assertJsonPath('data.0.report_commission',0.33);
    }

    public function test_admin_form_renders_all_services_roles_and_revision(): void
    {
        $this->webSession(true);
        \Illuminate\Support\Facades\View::getFinder()->prependLocation(base_path('tests/fixtures/commission-views'));
        $this->get('/admin/commission-settings')->assertOk()->assertSee(__('commission.title'))
            ->assertSee('rules[sell][rate]',false)->assertSee('rules[icloud_cus][rate]',false)
            ->assertSee('roles[2][view]',false)->assertSee('name="revision"',false);
    }

    private function webSession(bool $authorized): void
    {
        // Use a real session identity, authorization and CSRF test bypass;
        // the login guard middleware is covered by existing authentication tests.
        $this->withoutMiddleware([\App\Http\Middleware\Admin::class, \App\Http\Middleware\VerifyCsrfToken::class]);
        $this->actingAs(User::findOrFail(1),'web');
        Gate::define('manage_commission_settings',fn ()=>$authorized);
    }

    public function test_admin_updates_are_authorized_validated_audited_and_revision_checked(): void
    {
        $this->webSession(true);
        $this->withoutExceptionHandling();
        $c = CommissionSettingsService::defaults();
        $c['rules']['sell']['rate'] = 0.75;
        $c['roles'] = ['2'=>['view'=>false,'calculate'=>false,'change_mode'=>false]];
        $this->put('/admin/commission-settings',$c+['revision'=>0])->assertRedirect(route('admin.commission-settings.index'));
        $this->withExceptionHandling();
        $this->assertEquals(0.75,app(CommissionSettingsService::class)->configuration()['rules']['sell']['rate']);
        $this->assertEquals(1,DB::table('commission_setting_changes')->count());
        $this->assertEquals('user:1',DB::table('commission_setting_changes')->value('actor'));
        $this->assertEquals(1,DB::table('commission_settings')->value('revision'));
        $this->put('/admin/commission-settings',$c+['revision'=>0])->assertConflict();
        $this->assertEquals(1,DB::table('commission_setting_changes')->count());
        $c['rules']['sell']['rate'] = -1;
        $this->putJson('/admin/commission-settings',$c+['revision'=>1])->assertUnprocessable();
    }

    public function test_staff_cannot_change_rules_or_hide_controls_for_other_roles(): void
    {
        $this->webSession(false);
        $this->get('/admin/commission-settings')->assertForbidden();
        $this->put('/admin/commission-settings',CommissionSettingsService::defaults()+['revision'=>0])->assertForbidden();
        $this->assertEquals(0,DB::table('commission_settings')->value('revision'));
        $this->assertEquals(0,DB::table('commission_setting_changes')->count());
    }
    public function test_new_submissions_and_admin_calculations_use_the_current_rules(): void
    {
        $this->configure(['default_mode'=>'without_conditions','rules'=>['material'=>['rate'=>3,'basis'=>'item']]]);
        $payload = ['seller_name'=>'Employee','service_type'=>'Material','customer_phone'=>'',
            'commission'=>9999,'lines'=>[['product_name'=>'Material','qty'=>2,'unit_price'=>1]]];
        $this->postJson('/api/sell-out-reports',$payload)->assertCreated()->assertJsonPath('data.commission','6.00');
        $controller = app(\App\Http\Controllers\Web\SellStaffReportController::class);
        $method = new \ReflectionMethod($controller,'calculateCommission');
        $this->assertSame(6.0,$method->invoke($controller,$payload['lines'],'Material','',2,User::findOrFail(1)));
        $this->assertEquals(0,DB::table('sell_out_reports')->where('id',1)->value('commission'));
    }

    public function test_missing_installed_configuration_fails_closed(): void
    {
        DB::table('commission_settings')->delete();
        app(CommissionSettingsService::class)->refresh();
        $this->getJson('/api/sell-out-reports/commission-policy')->assertStatus(503);
        $this->getJson($this->reports())->assertStatus(503);
    }

    public function test_validation_redirect_preserves_revision_to_prevent_overwriting_concurrent_edits(): void
    {
        $this->webSession(true);
        $c = CommissionSettingsService::defaults();
        $c['rules']['sell']['rate'] = -1;
        $this->from('/admin/commission-settings')->put('/admin/commission-settings',$c+['revision'=>0])
            ->assertSessionHasErrors('rules.sell.rate');
        DB::table('commission_settings')->where('id',1)->update(['revision'=>1]);
        \Illuminate\Support\Facades\View::getFinder()->prependLocation(base_path('tests/fixtures/commission-views'));
        $this->get('/admin/commission-settings')->assertOk()->assertSee('name="revision" value="0"',false);
        $c['rules']['sell']['rate'] = 0.5;
        $this->put('/admin/commission-settings',$c+['revision'=>0])->assertConflict();
        $this->assertEquals(0,DB::table('commission_setting_changes')->count());
    }

}
