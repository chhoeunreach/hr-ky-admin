<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\MonthlyPayrollController;
use App\Models\EmployeePayslip;
use App\Models\User;
use App\Services\Payroll\MonthlyPayrollReportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MonthlyPayrollTest extends TestCase
{
    private array $abilities = ['view_monthly_payroll', 'export_monthly_payroll'];

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', ':memory:');
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp(); DB::purge('sqlite'); app()->setLocale('en');
        $this->travelTo(now()->setDate(2026, 10, 26));
        Cache::put('payment_currency_symbol', '$');
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('username')->nullable(); $table->string('employee_code')->nullable();
            $table->string('avatar')->nullable(); $table->integer('branch_id')->nullable(); $table->integer('department_id')->nullable();
            $table->date('joining_date')->nullable(); $table->softDeletes();
        });
        Schema::create('branches', function (Blueprint $table) {$table->id(); $table->string('name');});
        Schema::create('departments', function (Blueprint $table) {$table->id(); $table->string('dept_name'); $table->integer('branch_id');});
        Schema::create('employee_payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            foreach ((new EmployeePayslip)->getFillable() as $field) if ($field !== 'employee_id') $table->string($field)->nullable();
        });
        Schema::create('salary_components', function (Blueprint $table) {$table->id(); $table->string('name'); $table->string('component_type');});
        foreach (['employee_payslip_details', 'employee_payslip_additional_details'] as $name) {
            Schema::create($name, function (Blueprint $table) {$table->id(); $table->integer('employee_payslip_id'); $table->integer('salary_component_id'); $table->decimal('amount', 15, 2);});
        }
        Schema::create('sell_out_reports', function (Blueprint $table) {
            $table->id(); $table->integer('user_id'); $table->string('service_type')->nullable(); $table->decimal('commission',15,2); $table->timestamps();
        });
        DB::table('branches')->insert([['id'=>1,'name'=>'HQ'],['id'=>2,'name'=>'Other branch']]);
        DB::table('departments')->insert(['id'=>1,'dept_name'=>'Sales','branch_id'=>1]);
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Manager','username'=>'manager','employee_code'=>'M1','branch_id'=>1,'department_id'=>1],
            ['id'=>20,'name'=>'Employee','username'=>'ky20','employee_code'=>'KY0020','branch_id'=>1,'department_id'=>1],
            ['id'=>21,'name'=>'Other employee','username'=>'other21','employee_code'=>'KY0021','branch_id'=>2,'department_id'=>1],
        ]);
        $this->actingAs(User::findOrFail(1));
        Gate::before(fn ($user, $ability) => in_array($ability, $this->abilities, true));
        $this->payslip(20, ['gross_salary'=>400,'net_salary'=>390,'bonus'=>10,'absent_deduction'=>20,'present_days'=>22]);
        $this->payslip(21, ['gross_salary'=>9999,'net_salary'=>9999]);
        $this->payslip(20, ['salary_from'=>'2026-09-01','salary_to'=>'2026-09-30','net_salary'=>777]);
        $this->payslip(20, ['salary_cycle'=>'weekly','net_salary'=>888]);
        for ($id=100; $id<112; $id++) {
            DB::table('users')->insert(['id'=>$id,'name'=>'Staff '.$id,'username'=>'staff'.$id,'branch_id'=>1,'department_id'=>1]);
            $this->payslip($id, ['gross_salary'=>100,'net_salary'=>100,'status'=>'paid']);
        }
        DB::table('sell_out_reports')->insert([
            ['user_id'=>20,'service_type'=>'Sell','commission'=>3.5,'created_at'=>'2026-10-10 12:00:00'],
            ['user_id'=>20,'service_type'=>'Iron','commission'=>0.2,'created_at'=>'2026-10-11 12:00:00'],
            ['user_id'=>20,'service_type'=>'Sell','commission'=>500,'created_at'=>'2026-09-10 12:00:00'],
            ['user_id'=>21,'service_type'=>'Sell','commission'=>900,'created_at'=>'2026-10-10 12:00:00'],
        ]);
        Route::get('/_test/monthly-payroll', [MonthlyPayrollController::class,'index']);
    }

    private function payslip(int $employeeId, array $data = []): void
    {
        DB::table('employee_payslips')->insert(array_merge(['employee_id'=>$employeeId,'salary_cycle'=>'monthly',
            'salary_from'=>'2026-10-01','salary_to'=>'2026-10-31','status'=>'generated','gross_salary'=>0,'net_salary'=>0],$data));
    }
    private function filters(array $overrides = []): array
    {
        return array_merge(['month'=>'2026-10','branch_id'=>null,'department_id'=>null,'q'=>'','status'=>null,'per_page'=>'10','export'=>null],$overrides);
    }

    public function test_month_branch_commissions_and_saved_totals_are_consistent(): void
    {
        $report = app(MonthlyPayrollReportService::class)->report($this->filters(['branch_id'=>2]));
        $this->assertCount(10,$report['payslips']);
        $this->assertSame(13,$report['summary']['records']);
        $this->assertEquals(1590,$report['summary']['net']);
        $this->assertEquals(3.7,$report['summary']['commission']);
        $this->assertEquals(3.5,$report['commissions'][20]['Sell']);
        $this->assertArrayNotHasKey(21,$report['commissions']);
        $this->assertEquals(390,$report['payslips']->first()->net_salary);
    }

    public function test_filters_and_departed_employees_use_saved_payroll_records(): void
    {
        DB::table('users')->where('id',20)->update(['deleted_at'=>now()]);
        $report = app(MonthlyPayrollReportService::class)->report($this->filters(['q'=>'ky20','per_page'=>'all']));
        $this->assertCount(1,$report['payslips']);
        $this->assertEquals(390,$report['summary']['net']);
        $paid = app(MonthlyPayrollReportService::class)->report($this->filters(['status'=>'paid']));
        $this->assertSame(12,$paid['summary']['records']);
    }

    public function test_fragment_renders_the_sheet_without_the_page_layout(): void
    {
        $this->get('/_test/monthly-payroll?month=2026-10&per_page=10', ['X-Monthly-Payroll-Fragment'=>'1','Accept'=>'application/json'])
            ->assertOk()->assertSee('monthlyPayrollResults')->assertSee('KY0020')->assertSee('Sell')->assertSee('Iron')
            ->assertSee('$390.00')->assertDontSee('Other employee')->assertDontSee('<html',false);
    }

    public function test_monthly_payroll_page_renders_filters_and_toolbar(): void
    {
        $root=sys_get_temp_dir().'/monthly-payroll-layout-'.bin2hex(random_bytes(5));
        mkdir($root.'/layouts',0777,true);
        file_put_contents($root.'/layouts/master.blade.php', "@yield('main-content')");
        $this->app['view']->getFinder()->prependLocation($root);
        try {
            $this->get('/_test/monthly-payroll?month=2026-10')->assertOk()->assertSee('monthlyPayrollFilters')
                ->assertSee('KY0020')->assertSee('HQ')->assertDontSee('Other branch')
                ->assertSee(__('index.export_excel'))->assertSee('employeeProfileFormModal');
        } finally {
            unlink($root.'/layouts/master.blade.php'); rmdir($root.'/layouts'); rmdir($root);
        }
    }

    public function test_permissions_and_invalid_filters_are_enforced(): void
    {
        $this->abilities=[];
        $this->getJson('/_test/monthly-payroll')->assertForbidden();
        $this->abilities=['view_monthly_payroll'];
        $this->getJson('/_test/monthly-payroll?export=csv')->assertForbidden();
        $this->getJson('/_test/monthly-payroll?month=bad')->assertUnprocessable();
        $this->getJson('/_test/monthly-payroll?per_page=9999')->assertUnprocessable();
    }

    public function test_exports_cover_all_filtered_records_and_commission_does_not_change_net(): void
    {
        Excel::fake();
        $this->get('/_test/monthly-payroll?month=2026-10&per_page=10&page=2&export=excel')->assertOk();
        Excel::assertDownloaded('monthly-payroll-2026-10.xlsx', function ($export) {
            $this->assertCount(13,$export->array());
            $headings=$export->headings(); $first=$export->array()[0];
            $this->assertSame('390.00',$first[array_search(__('index.net_salary'),$headings,true)]);
            $this->assertSame('3.50',$first[array_search(__('index.commission').' · Sell',$headings,true)]);
            return true;
        });
        $this->get('/_test/monthly-payroll?month=2026-10&export=pdf')->assertOk()->assertSee('A3 landscape',false)->assertSee('KY0020');
    }

    public function test_print_matches_the_employee_sheet_layout_and_uses_all_filtered_rows(): void
    {
        $response = $this->get('/_test/monthly-payroll?month=2026-10&per_page=10&page=2&export=print&paper=A4&orientation=portrait');
        $response->assertOk()->assertSee('A4 portrait', false)->assertSee('payrollPrintPaper')
            ->assertSee('payrollPrintOrientation')->assertSee('payroll-print-signatures')->assertSee('KY0020')
            ->assertSee('Net salary: $390.00')->assertDontSee('Other employee');
        $html = $response->getContent();
        $this->assertSame(13, substr_count($html, 'class="payroll-sample-table"'));
        $this->assertSame(13, substr_count($html, '<colgroup>'));
        $this->assertSame(13, substr_count($html, 'width="44" height="57"'));
        $this->assertStringContainsString('Kantumruy+Pro', $html);
        $this->getJson('/_test/monthly-payroll?export=print&paper=invalid')->assertUnprocessable();
    }

    public function test_khmer_print_uses_translated_headers_and_commission_columns(): void
    {
        app()->setLocale('km');
        $this->get('/_test/monthly-payroll?month=2026-10&q=ky20&export=print')->assertOk()
            ->assertSee(__('index.monthly_payroll'))->assertSee(__('index.employee_name'))
            ->assertSee(__('index.monthly_payroll_print_note'))->assertSee('Sell')->assertSee('Iron')
            ->assertSee('$390.00')->assertDontSee('index.monthly_payroll');
    }
}
