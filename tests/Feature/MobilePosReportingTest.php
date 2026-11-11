<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SellOut\ReportingRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MobilePosReportingTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('cache.default','array');
            $app['config']->set('cache.limiter','array');
            $app['config']->set('database.default','sqlite');
            $app['config']->set('database.connections.sqlite.database',':memory:');
            $socket = getenv('POS_REPORT_MYSQL_SOCKET');
            if ($socket !== false && $socket !== '') {
                // Opt-in dialect checks use only a disposable, socket-only MySQL.
                // Never use the configured application's database or credentials.
                if (!preg_match('#^/private/tmp/kneayerng-pos-mysql\.[A-Za-z0-9]+/mysql\.sock$#', $socket)
                    && !preg_match('#^/tmp/kneayerng-pos-mysql\.[A-Za-z0-9]+/mysql\.sock$#', $socket)) {
                    throw new \RuntimeException('A disposable POS test socket is required.');
                }
                $app['config']->set('database.default','pos_reporting_test');
                $app['config']->set('database.connections.pos_reporting_test', [
                    'driver'=>'mysql', 'host'=>'localhost', 'unix_socket'=>$socket,
                    'database'=>'pos_reporting_test', 'username'=>'root', 'password'=>'',
                    'charset'=>'utf8mb4', 'collation'=>'utf8mb4_unicode_ci',
                    'prefix'=>'', 'strict'=>true, 'engine'=>null,
                ]);
            }
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        DB::purge(config('database.default'));
        if (config('database.default') === 'pos_reporting_test') Schema::dropAllTables();
        $this->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class]);
        $this->travelTo(now()->setDate(2026,11,9)->setTime(12,0));
        Cache::put('payment_currency_symbol','$');
        Bus::fake();
        Http::preventStrayRequests();
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->integer('role_id')->default(2); $t->integer('branch_id')->nullable(); $t->integer('office_time_id')->nullable(); $t->integer('department_id')->nullable(); $t->softDeletes();
        });
        Schema::create('roles',function (Blueprint $t) {$t->id(); $t->string('name'); $t->string('slug');});
        Schema::create('permissions',function (Blueprint $t) {$t->id(); $t->string('permission_key');});
        Schema::create('permission_roles',function (Blueprint $t) {$t->id(); $t->integer('permission_id'); $t->integer('role_id');});
        DB::table('roles')->insert(['id'=>2,'name'=>'Employee','slug'=>'employee']);
        Schema::create('branches',function (Blueprint $t) {$t->id(); $t->string('name');});
        Schema::create('departments',function (Blueprint $t) {$t->id(); $t->string('dept_name'); $t->integer('branch_id')->nullable();});
        Schema::create('office_times',function (Blueprint $t) {
            $t->id(); $t->integer('branch_id')->nullable(); foreach (['category','opening_time','closing_time','shift','shift_type','is_late_check_in','checkin_after','is_early_check_out','checkout_before'] as $f) $t->string($f)->nullable();
        });
        Schema::create('sell_out_reports',function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); foreach (['invoice_no','original_invoice_no','seller_name','branch_name','customer_name','customer_phone','service_type','payment_method','note','extracted_text'] as $f) $t->string($f)->nullable();
            $t->decimal('total_amount',12,2); $t->decimal('commission',12,2)->default(0); $t->timestamps();
        });
        Schema::create('sell_out_report_lines',function (Blueprint $t) {
            $t->id(); $t->integer('sell_out_report_id'); foreach (['product_name','sku','imei','imei2','serial_number','model_number','color','storage'] as $f) $t->string($f)->nullable();
            $t->integer('qty'); $t->decimal('unit_price',12,2); $t->decimal('subtotal',12,2); $t->timestamps();
        });
        Schema::create('sell_out_report_photos',function (Blueprint $t) {$t->id(); $t->integer('sell_out_report_id'); $t->integer('sell_out_report_line_id')->nullable(); $t->string('photo_url')->nullable(); $t->string('photo_path')->nullable();});
        (require database_path('migrations/2026_11_10_000001_add_mobile_reporting_index.php'))->up();
        DB::table('branches')->insert([['id'=>1,'name'=>'HQ'],['id'=>2,'name'=>'Other']]);
        DB::table('office_times')->insert(['id'=>1,'category'=>'full_timer','branch_id'=>1]);
        DB::table('users')->insert([['id'=>1,'name'=>'Employee','branch_id'=>1,'office_time_id'=>1],['id'=>2,'name'=>'Other employee','branch_id'=>2,'office_time_id'=>1]]);
        Passport::actingAs(User::findOrFail(1));
        $this->record('Sell',60,120);
        $this->record('លក់',40,80);
        $this->record('Material',1,10);
        $this->record('Iron',1,5);
        $this->record('Repair',1,7);
        $this->record('Buy In',1,500);
        $this->record('Sell',999,9999,['user_id'=>2,'branch_name'=>'Other','seller_name'=>'Other employee']);
    }

    private function record(string $type, int $qty, float $amount, array $extra = []): int
    {
        $id = DB::table('sell_out_reports')->insertGetId($extra + ['user_id'=>1,'invoice_no'=>'INV-'.DB::table('sell_out_reports')->count(),'seller_name'=>'Employee','branch_name'=>'HQ','customer_name'=>'Customer','customer_phone'=>'012345678','service_type'=>$type,'payment_method'=>'Cash','total_amount'=>$amount,'created_at'=>now(),'updated_at'=>now()]);
        DB::table('sell_out_report_lines')->insert(['sell_out_report_id'=>$id,'product_name'=>'Phone '.$id,'sku'=>'SKU-'.$id,'qty'=>$qty,'unit_price'=>$amount/$qty,'subtotal'=>$amount]);
        return $id;
    }

    private function url(array $query = []): string
    {
        return '/api/sell-out-reports?'.http_build_query($query + ['paginated'=>1,'date_from'=>'2026-11-09','date_to'=>'2026-11-09']);
    }

    public function test_sellsim_is_a_distinct_sell_type_in_both_languages_and_reporting(): void
    {
        foreach (['Sellsim', 'Sell Sim', 'sell_sim', 'sell-sim', 'SIM Sale', 'លក់ស៊ីម'] as $label) {
            $this->assertSame('sellsim', ReportingRules::canonical($label));
            $this->assertSame(\App\Models\TelegramGroup::EVENT_SELL_OUT_SELLSIM, \App\Models\TelegramGroup::sellOutEventKeyForServiceType($label));
        }
        $this->assertSame('លក់ស៊ីម', \App\Models\TelegramGroup::sellOutEventOptions()[\App\Models\TelegramGroup::EVENT_SELL_OUT_SELLSIM]);
        $this->record('Sellsim', 2, 12);
        $this->record('លក់ស៊ីម', 3, 18);
        $response = $this->getJson($this->url(['type' => 'sellsim']))->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('summary.module_quantity', 5)->assertJsonPath('summary.module_amount', 30)
            ->assertJsonPath('summary.sellsim_amount', 30)->assertJsonPath('summary.sales_amount', 30)
            ->assertJsonPath('summary.commission', 0)->assertJsonPath('data.0.type', 'sellsim');
        $this->assertContains('sellsim', $response->json('options.types'));
        $this->assertContains('sellsim', $response->json('permissions.modules'));
        $this->getJson($this->url())->assertOk()->assertJsonPath('summary.sales_amount', 252)
            ->assertJsonPath('summary.total_transactions', 8)->assertJsonPath('summary.reports', 2);
    }

    public function test_summary_is_scoped_deduplicated_and_excludes_purchase_spending(): void
    {
        DB::table('sell_out_report_photos')->insert([['sell_out_report_id'=>1],['sell_out_report_id'=>1]]);
        $this->getJson($this->url())->assertOk()->assertJsonPath('summary.reports',2)->assertJsonPath('summary.quantity',100)
            ->assertJsonPath('summary.sales_amount',222)->assertJsonPath('summary.buy_in_amount',500)->assertJsonPath('summary.gross_profit',null)
            ->assertJsonPath('summary.commission',25.65)->assertJsonPath('summary.qualified',2)->assertJsonPath('summary.total_transactions',6)
            ->assertJsonPath('permissions.scope','own_reports')->assertJsonPath('meta.per_page',20)->assertJsonPath('capabilities.repair_orders',false);
    }

    public function test_server_pagination_is_bounded_and_summaries_cover_all_pages(): void
    {
        for ($i=0;$i<25;$i++) $this->record('Iron',1,1);
        $first = $this->getJson($this->url())->assertOk()->assertJsonCount(20,'data')->assertJsonPath('meta.total',31)->assertJsonPath('meta.last_page',2)->assertJsonPath('summary.sales_amount',247);
        $second = $this->getJson($this->url(['page'=>2]))->assertOk()->assertJsonCount(11,'data')->assertJsonPath('summary.sales_amount',247);
        $this->assertEmpty(array_intersect(array_column($first->json('data'),'id'),array_column($second->json('data'),'id')));
        $this->getJson($this->url(['per_page'=>'all']))->assertUnprocessable();
        $this->getJson($this->url(['page'=>9999]))->assertOk()->assertJsonPath('meta.current_page',2)->assertJsonCount(11,'data');
    }

    public function test_search_type_branch_employee_and_status_work_together(): void
    {
        $this->getJson($this->url(['type'=>'sell','report_status'=>'qualified','branch_name'=>'HQ','seller_name'=>'Employee']))->assertOk()->assertJsonCount(2,'data')->assertJsonPath('summary.commission',25);
        // Search is part of the period calculation: 60 selected items do not reach 100.
        $this->getJson($this->url(['search'=>'SKU-1']))->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.report_status','failed')->assertJsonPath('summary.commission',0);
        $this->getJson($this->url(['type'=>'material','search'=>'Customer']))->assertOk()->assertJsonCount(1,'data')->assertJsonPath('summary.module_amount',10);
    }

    public function test_unowned_filters_cannot_leak_cross_branch_or_employee_data(): void
    {
        $response = $this->getJson($this->url())->assertOk();
        $this->assertSame(['HQ'],$response->json('options.branches'));
        $this->assertSame(['Employee'],$response->json('options.employees'));
        $this->getJson($this->url(['branch_name'=>'Other']))->assertForbidden();
        $this->getJson($this->url(['seller_name'=>'Other employee']))->assertForbidden();
    }

    public function test_date_filters_drive_summary_chart_and_transactions_consistently(): void
    {
        $this->record('Sell',100,99,['created_at'=>now()->subDay()]);
        $response = $this->getJson($this->url(['date_from'=>'2026-11-08','date_to'=>'2026-11-08']))->assertOk()->assertJsonPath('summary.sales_amount',99)->assertJsonCount(1,'data');
        $this->assertEquals(99,array_sum(array_column($response->json('chart.points'),'amount')));
        $this->getJson($this->url(['date_from'=>'2026-11-07','date_to'=>'2026-11-07']))->assertOk()->assertJsonCount(0,'data')->assertJsonPath('summary.sales_amount',0);
        $this->getJson($this->url(['date_from'=>'2026-11-10','date_to'=>'2026-11-08']))->assertUnprocessable();
    }

    public function test_no_condition_preview_preserves_service_rates_but_never_commissions_buy_in(): void
    {
        DB::table('sell_out_reports')->where('user_id',1)->update(['customer_phone'=>'']);
        $this->getJson($this->url())->assertOk()->assertJsonPath('summary.commission',0);
        $this->getJson($this->url(['commission_mode'=>'without_conditions']))->assertOk()->assertJsonPath('summary.commission',25.65);
        $this->getJson($this->url(['type'=>'buy_in','commission_mode'=>'without_conditions']))->assertOk()->assertJsonPath('data.0.report_commission',0)->assertJsonPath('summary.sales_amount',0);
    }

    public function test_csv_uses_all_matching_records_and_protects_spreadsheet_cells(): void
    {
        DB::table('sell_out_reports')->where('id',1)->update(['customer_name'=>'=1+1']);
        $response = $this->get($this->url(['export'=>'csv']))->assertOk();
        $this->assertStringContainsString("'=1+1", $response->streamedContent());
    }

    public function test_sql_commission_matches_existing_invoice_rules_for_single_invoice_scopes(): void
    {
        foreach (['sell','material','iron','repair','buy_in'] as $type) {
            $response = $this->getJson($this->url(['type'=>$type]))->assertOk();
            foreach ($response->json('data') as $row) {
                $expected = ReportingRules::calculate($row['service_type'],$row['total_qty'],$row['total_amount'],$row['customer_phone'],'full_timer',$row['period_qty']);
                $this->assertEquals($expected,$row['report_commission']);
            }
        }
    }

    public function test_service_rates_and_part_time_threshold_remain_compatible_with_invoice_rules(): void
    {
        foreach (['material'=>0.25,'iron'=>0.20,'repair'=>0.20,'buy_in'=>0] as $type=>$rate) {
            $this->getJson($this->url(['type'=>$type]))->assertOk()->assertJsonPath('summary.commission',$rate);
        }
        DB::table('sell_out_reports')->where('service_type','Material')->update(['total_amount'=>9.99]);
        $this->getJson($this->url(['type'=>'material']))->assertOk()->assertJsonPath('summary.commission',0);
        $this->assertSame(0.0, \App\Models\SellOutReport::findOrFail(1)->calculatedCommission());
        DB::table('office_times')->where('id',1)->update(['category'=>'part_timer']);
        $this->getJson($this->url(['search'=>'SKU-1']))->assertOk()->assertJsonPath('summary.commission',15);
        $this->assertSame(15.0, \App\Models\SellOutReport::findOrFail(1)->calculatedCommission());
        $this->assertEquals(0,DB::table('sell_out_reports')->where('id',1)->value('commission'));
    }

    public function test_reporting_requires_authentication_and_validated_filters(): void
    {
        $this->getJson($this->url(['type'=>'sell;DROP TABLE users']))->assertUnprocessable();
        $this->getJson($this->url(['per_page'=>501]))->assertUnprocessable();
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->getJson($this->url())->assertUnauthorized();
    }

    public function test_create_detail_and_edit_refresh_dashboard_totals_for_each_service(): void
    {
        foreach (['Sell','Material','Iron','Repair','Buy In','iCloud Cus'] as $index => $type) {
            $payload = [
                'seller_name'=>'Employee','branch_name'=>'HQ','customer_name'=>'New Customer',
                'customer_phone'=>'012345678','service_type'=>$type,'payment_method'=>'Cash',
                'original_invoice_no'=>'ORIGINAL-'.$index,
                'lines'=>[['product_name'=>'New product','qty'=>2,'unit_price'=>15,'serial_number'=>'TEST'.$index]],
            ];
            $created = $this->postJson('/api/sell-out-reports',$payload)->assertCreated()
                ->assertJsonPath('data.total_amount','30.00')->assertJsonPath('data.original_invoice_no','ORIGINAL-'.$index);
            $id = $created->json('data.id');
            $this->getJson('/api/sell-out-reports/'.$id)->assertOk()
                ->assertJsonPath('data.lines.0.qty',2)->assertJsonPath('data.lines.0.serial_number','TEST'.$index);
            $payload['lines'][0]['unit_price'] = 20;
            $this->putJson('/api/sell-out-reports/'.$id,$payload)->assertOk()
                ->assertJsonPath('data.total_amount','40.00')->assertJsonPath('data.original_invoice_no','ORIGINAL-'.$index);
        }
        // Four revenue services, one purchase service; iCloud customer records
        // remain accessible without inventing financial rules for that module.
        $this->getJson($this->url())->assertOk()->assertJsonPath('summary.sales_amount',382)
            ->assertJsonPath('summary.buy_in_amount',540)->assertJsonPath('summary.total_transactions',11);
    }

    public function test_detail_and_edit_enforce_ownership_and_today_restriction(): void
    {
        $payload = ['seller_name'=>'Employee','service_type'=>'Iron',
            'lines'=>[['product_name'=>'Iron','qty'=>1,'unit_price'=>1]]];
        $this->getJson('/api/sell-out-reports/7')->assertNotFound();
        $this->putJson('/api/sell-out-reports/7',$payload)->assertNotFound();
        DB::table('sell_out_reports')->where('id',1)->update(['created_at'=>now()->subDay()]);
        $this->getJson($this->url(['date_from'=>'2026-11-08','date_to'=>'2026-11-08']))
            ->assertOk()->assertJsonPath('data.0.can_edit',false);
        $this->putJson('/api/sell-out-reports/1',$payload)->assertForbidden();
    }

    public function test_all_six_services_allow_today_edits_and_lock_at_business_midnight(): void
    {
        $day = now()->startOfDay();
        foreach (['Sell', 'Material', 'Iron', 'Repair', 'Buy In', 'iCloud Cus'] as $type) {
            $this->travelTo($day->copy()->setTime(23, 59, 59));
            $payload = ['seller_name' => 'Employee', 'service_type' => $type,
                'lines' => [['product_name' => 'Editable item', 'qty' => 1, 'unit_price' => 10, 'serial_number' => 'SERIAL123']]];
            $id = $this->postJson('/api/sell-out-reports', $payload)->assertCreated()->json('data.id');
            $payload['lines'][0]['unit_price'] = 20;
            $this->putJson('/api/sell-out-reports/'.$id, $payload)->assertOk()->assertJsonPath('data.total_amount', '20.00');
            $this->travelTo($day->copy()->addDay());
            $payload['lines'][0]['unit_price'] = 99;
            $this->putJson('/api/sell-out-reports/'.$id, $payload)->assertForbidden();
            $this->assertEquals(20, DB::table('sell_out_reports')->where('id', $id)->value('total_amount'));
            DB::table('sell_out_reports')->where('id', $id)->update(['created_at' => $day->copy()->addDays(2)]);
            $this->putJson('/api/sell-out-reports/'.$id, $payload)->assertForbidden();
        }
    }

    public function test_csv_errors_remain_json_for_mobile_clients(): void
    {
        $this->get($this->url(['export'=>'csv', 'per_page'=>501]), ['Accept'=>'text/csv'])
            ->assertUnprocessable()->assertHeader('Content-Type','application/json');
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->get($this->url(['export'=>'csv']), ['Accept'=>'text/csv'])
            ->assertUnauthorized()->assertHeader('Content-Type','application/json');
    }

    public function test_default_today_and_partial_dates_use_business_calendar_boundaries(): void
    {
        $this->record('Iron',1,9,['created_at'=>now()->startOfDay()]);
        $this->record('Iron',1,11,['created_at'=>now()->endOfDay()]);
        $this->record('Iron',1,1000,['created_at'=>now()->addDay()->startOfDay()]);
        $this->getJson('/api/sell-out-reports?paginated=1')->assertOk()
            ->assertJsonPath('summary.total_transactions',8)->assertJsonPath('summary.sales_amount',242)
            ->assertJsonPath('filters.date_from','2026-11-09')->assertJsonPath('filters.date_to','2026-11-09');
        $this->getJson('/api/sell-out-reports?paginated=1&date_from=2026-11-10')->assertOk()
            ->assertJsonPath('summary.sales_amount',1000);
    }

    public function test_legacy_list_and_detail_contracts_remain_available(): void
    {
        $this->withoutExceptionHandling();
        $this->getJson('/api/sell-out-reports')->assertOk()->assertJsonCount(6,'data')->assertJsonStructure(['data'=>[['id','lines','commission']]]);
        $this->getJson('/api/sell-out-reports/1')->assertOk()->assertJsonPath('data.id',1);
    }
    public function test_business_breakdown_contains_sales_and_filtered_commission_per_service(): void
    {
        $response = $this->getJson($this->url())->assertOk()
            ->assertJsonPath('categories.0.type','sell')->assertJsonPath('categories.0.amount',200)
            ->assertJsonPath('categories.0.commission',25)->assertJsonPath('categories.1.commission',0.25)
            ->assertJsonPath('categories.2.commission',0.2)->assertJsonPath('categories.3.commission',0.2)
            ->assertJsonPath('categories.4.commission',0);
        $this->assertEquals($response->json('summary.commission'),array_sum(array_column($response->json('categories'),'commission')));
        $this->getJson($this->url(['type'=>'material']))->assertOk()
            ->assertJsonPath('categories.0.commission',0)->assertJsonPath('categories.1.commission',0.25);
    }

}
