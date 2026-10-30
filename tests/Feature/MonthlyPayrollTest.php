<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\MonthlyPayrollController;
use App\Models\EmployeePayslip;
use App\Models\User;
use App\Services\Payroll\MonthlyPayrollReportService;
use App\Services\Payroll\MonthlyPayrollRecordService;
use App\Services\Payroll\GeneratePayrollService;
use App\Services\Payroll\MonthlyPayrollLoanService;
use App\Models\MonthlyPayrollRecord;
use App\Models\Branch;
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
        Schema::create('advance_salaries', function (Blueprint $table) {
            $table->id(); $table->integer('employee_id'); $table->decimal('requested_amount', 15, 2)->nullable();
            $table->decimal('released_amount', 15, 2)->nullable(); $table->string('status');
            $table->dateTime('amount_granted_date')->nullable(); $table->boolean('is_settled')->default(false);
        });
        (require database_path('migrations/2026_10_30_000001_create_monthly_payroll_records_table.php'))->up();
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
        Route::post('/_test/monthly-payroll/generate', [MonthlyPayrollController::class,'generate']);
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
            ->assertOk()->assertSee('monthlyPayrollResults')->assertSee('monthlyPayrollSummary')->assertSee('KY0020')
            ->assertSee(app(MonthlyPayrollReportService::class)->columns()['iron']['label'])
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
                ->assertSeeInOrder(['id="monthlyPayrollSummary"', 'id="monthlyPayrollFilters"', 'class="monthly-payroll-toolbar"', 'id="monthlyPayrollResults"'], false)
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
            $this->assertSame('390.00',$first[array_search('ប្រាក់ត្រូវបើក',$headings,true)]);
            $this->assertSame('3.50',$first[array_search('លក់',$headings,true)]);
            return true;
        });
        $this->get('/_test/monthly-payroll?month=2026-10&export=pdf')->assertOk()->assertSee('A3 landscape',false)->assertSee('KY0020');
    }

    public function test_print_matches_the_employee_sheet_layout_and_uses_all_filtered_rows(): void
    {
        $response = $this->get('/_test/monthly-payroll?month=2026-10&per_page=10&page=2&export=print&paper=A4&orientation=portrait');
        $response->assertOk()->assertSee('A4 portrait', false)->assertSee('payrollPrintPaper')
            ->assertSee('payrollPrintOrientation')->assertSee('payroll-print-signatures')->assertSee('KY0020')
            ->assertSee('ប្រាក់ត្រូវបើក')->assertSee('$390.00')->assertDontSee('Other employee');
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
            ->assertSee(__('index.monthly_payroll'))->assertSee('ឈ្មោះខ្មែរ')
            ->assertSee('ប្រាក់ត្រូវបើក')->assertSee('លក់')->assertSee('អ៊ុង')
            ->assertSee('$390.00')->assertDontSee('index.monthly_payroll');
    }

    private function expectedHeadings(): array
    {
        return ['លរ', 'លេខ', 'រូប', 'ឈ្មោះខ្មែរ', 'ប្រាក់ខែគោល', 'តម្លើង', 'ទីតាំង', 'ធ្វើការ',
            'មកលឿន', 'មកយឺត', 'ចេញមុន', 'ថែមម៉ោង', 'ផ្នែក', 'សម្រាក', 'ពិន័យ', 'សុំច្បាប់',
            'ប្រាក់', 'ខ្ចីលុយ', 'កាត់', 'មិនឈប់', 'លក់', 'អ៊ុង', 'សម្ភារ', 'fix', 'ផុស',
            'លើកទឹកចិត្ត', 'ប្រាក់សរុប', 'ប្រាក់ត្រូវបើក'];
    }

    public function test_screen_print_and_spreadsheet_share_the_exact_28_requested_columns(): void
    {
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['q' => 'ky20']));
        $table = $service->exportTable($report);
        $this->assertSame($this->expectedHeadings(), $table['headings']);
        $this->assertCount(28, $table['rows'][0]);
        foreach ([null, 'print'] as $format) {
            $response = $this->get('/_test/monthly-payroll?month=2026-10&q=ky20'.($format ? '&export='.$format : ''), ['X-Monthly-Payroll-Fragment' => '1']);
            $response->assertOk();
            preg_match('/<thead>(.*?)<\/thead>/s', $response->getContent(), $thead);
            preg_match_all('/<th\b[^>]*>(.*?)<\/th>/s', $thead[1], $headers);
            $this->assertSame($this->expectedHeadings(), array_map(fn ($label) => trim(html_entity_decode(strip_tags($label), ENT_QUOTES, 'UTF-8')), $headers[1]));
        }
    }

    public function test_saved_components_fill_custom_columns_without_changing_gross_or_net(): void
    {
        $payslipId = DB::table('employee_payslips')->where('employee_id', 20)->where('salary_cycle', 'monthly')->where('salary_from', '2026-10-01')->value('id');
        foreach (['ប្រាក់ខែគោល' => 250, 'តម្លើង' => 10, 'មកលឿន' => 2, 'មកយឺត' => 3, 'ចេញមុន' => 4,
            'ពិន័យ' => 5, 'ប្រាក់' => 6, 'កាត់' => 7, 'មិនឈប់' => 8, 'ផុស' => 9] as $name => $amount) {
            $componentId = DB::table('salary_components')->insertGetId(['name' => $name, 'component_type' => 'earning']);
            DB::table('employee_payslip_details')->insert(['employee_payslip_id' => $payslipId, 'salary_component_id' => $componentId, 'amount' => $amount]);
        }
        DB::table('employee_payslips')->where('id', $payslipId)->update(['weekends' => 4, 'holidays' => 1, 'leave_days' => 2]);
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['q' => 'ky20']));
        $values = $service->rowValues($report['payslips']->first(), $report['commissions'][20], 1);
        foreach (['basic_salary' => 250, 'increase' => 10, 'early' => 2, 'late' => 3, 'early_out' => 4,
            'penalty' => 5, 'pay' => 6, 'deduction' => 7, 'attendance_bonus' => 8, 'post' => 9,
            'rest' => 5, 'leave_days' => 2, 'gross_salary' => 400, 'net_salary' => 390] as $key => $amount) {
            $this->assertEquals($amount, $values[$key], $key);
        }
    }

    public function test_commission_aliases_use_the_fixed_columns_and_do_not_change_saved_pay(): void
    {
        DB::table('sell_out_reports')->insert([
            ['user_id' => 20, 'service_type' => 'លក់', 'commission' => .5, 'created_at' => '2026-10-15 12:00:00'],
            ['user_id' => 20, 'service_type' => 'Scots', 'commission' => .4, 'created_at' => '2026-10-15 12:00:00'],
            ['user_id' => 20, 'service_type' => 'Material', 'commission' => .25, 'created_at' => '2026-10-15 12:00:00'],
            ['user_id' => 20, 'service_type' => 'Repair', 'commission' => .6, 'created_at' => '2026-10-15 12:00:00'],
            ['user_id' => 20, 'service_type' => 'fix', 'commission' => .8, 'created_at' => '2026-10-15 12:00:00'],
        ]);
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['q' => 'ky20']));
        $values = $service->rowValues($report['payslips']->first(), $report['commissions'][20], 1);
        foreach (['sell' => 4, 'iron' => .6, 'material' => .25, 'repair' => 1.4, 'net_salary' => 390] as $key => $amount) {
            $this->assertEquals($amount, $values[$key], $key);
        }
        $this->assertNull($values['basic_salary']);
        $this->assertNull($values['increase']);
        $this->assertCount(28, $service->columns());
    }

    private function allowMonthlyGeneration(): void
    {
        $this->abilities[] = 'generate_payroll';
        $this->mock(GeneratePayrollService::class, function ($mock) {
            $mock->shouldReceive('getEmployeeSalariesToCreatePayslip')->once()->withArgs(fn ($options) =>
                $options['preserve_existing'] === true && $options['is_bs_enabled'] === false && $options['branch_id'] === 1
                && $options['salary_cycle'] === 'monthly' && $options['department_id'] === null)->andReturn([]);
        });
    }

    public function test_generate_saves_a_branch_month_and_existing_pay_is_preserved(): void
    {
        $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1, 'attendance' => 1])->assertRedirect();
        $record = MonthlyPayrollRecord::firstOrFail();
        $this->assertSame('2026-10', $record->month);
        $this->assertSame(13, $record->summary['records']);
        $this->assertEquals(1590, $record->summary['net']);
        $this->assertCount(28, $record->columns);
        $this->assertSame('Manager', $record->generated_by_name);
        $this->assertCount(13, $record->rows);
        $this->assertEquals(390, $record->rows[0]['values']['net_salary']);
        $this->assertDatabaseCount('employee_payslips', 16);
    }

    public function test_saved_month_is_immutable_after_employee_payslip_and_commission_changes(): void
    {
        $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1])->assertRedirect();
        DB::table('employee_payslips')->where('employee_id', 20)->update(['net_salary' => 999]);
        DB::table('users')->where('id', 20)->update(['name' => 'Changed name']);
        DB::table('sell_out_reports')->where('user_id', 20)->update(['commission' => 100]);
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['q' => 'ky20']));
        $this->assertTrue($report['isSavedReport']);
        $this->assertEquals(390, $report['summary']['net']);
        $values = $service->rowValues($report['payslips']->first(), [], 1);
        $this->assertSame('Employee', $values['employee']);
        $this->assertEquals(3.5, $values['sell']);
        $export = $service->exportTable($report);
        $this->assertSame('390.00', $export['rows'][0][array_search('ប្រាក់ត្រូវបើក', $export['headings'], true)]);
        $this->get('/_test/monthly-payroll?month=2026-10&q=ky20', ['X-Monthly-Payroll-Fragment' => '1'])
            ->assertOk()->assertSee('Saved')->assertSee('$390.00')->assertDontSee('Changed name');
    }

    public function test_duplicate_generation_is_rejected_without_overwriting_the_saved_record(): void
    {
        $this->allowMonthlyGeneration();
        $data = ['month' => '2026-10', 'branch_id' => 1];
        $this->postJson('/_test/monthly-payroll/generate', $data)->assertRedirect();
        $original = MonthlyPayrollRecord::first()->rows;
        $this->postJson('/_test/monthly-payroll/generate', $data)->assertUnprocessable()->assertSee(__('index.monthly_payroll_already_saved'));
        $this->assertDatabaseCount('monthly_payroll_records', 1);
        $this->assertSame($original, MonthlyPayrollRecord::first()->rows);
    }

    public function test_generated_and_older_saved_reports_keep_the_exact_requested_columns(): void
    {
        $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1])->assertRedirect();
        $record = MonthlyPayrollRecord::firstOrFail();
        $this->assertSame($this->expectedHeadings(), array_column($record->columns, 'label'));
        // Simulate an older saved layout, including fields it did not record.
        $rows = $record->rows;
        foreach ($rows as &$row) {
            unset($row['values']['increase'], $row['values']['early'], $row['values']['post']);
        }
        unset($row);
        $record->update(['columns' => ['net_salary' => ['label' => 'Old Net Salary', 'format' => 'money', 'class' => '', 'width' => 1]], 'rows' => $rows]);

        foreach ([null, 'print'] as $format) {
            $response = $this->get('/_test/monthly-payroll?month=2026-10&q=ky20'.($format ? '&export='.$format : ''), ['X-Monthly-Payroll-Fragment' => '1']);
            $response->assertOk()->assertSee('$390.00')->assertDontSee('Old Net Salary');
            preg_match('/<thead>(.*?)<\/thead>/s', $response->getContent(), $thead);
            preg_match_all('/<th\b[^>]*>(.*?)<\/th>/s', $thead[1], $headers);
            $this->assertSame($this->expectedHeadings(), array_map(fn ($label) => trim(html_entity_decode(strip_tags($label), ENT_QUOTES, 'UTF-8')), $headers[1]));
            preg_match('/<tbody>\s*<tr>(.*?)<\/tr>/s', $response->getContent(), $body);
            $this->assertSame(28, preg_match_all('/<td\b/', $body[1]));
        }
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['q' => 'ky20']));
        $table = $service->exportTable($report);
        $this->assertSame($this->expectedHeadings(), $table['headings']);
        $this->assertCount(28, $table['rows'][0]);
        $this->assertSame('390.00', $table['rows'][0][27]);
        $this->assertSame('', $table['rows'][0][5]);
        $this->assertSame($rows, $record->fresh()->rows);
    }

    public function test_empty_month_is_not_marked_as_saved(): void
    {
        $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-11', 'branch_id' => 1])->assertUnprocessable();
        $this->assertDatabaseCount('monthly_payroll_records', 0);
    }

    public function test_generation_requires_its_permission_and_respects_the_users_branch(): void
    {
        $this->mock(GeneratePayrollService::class, fn ($mock) => $mock->shouldNotReceive('getEmployeeSalariesToCreatePayslip'));
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1])->assertForbidden();
        $this->abilities[] = 'generate_payroll';
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 2])->assertForbidden();
        $this->assertDatabaseCount('monthly_payroll_records', 0);
    }

    public function test_failed_generation_rolls_back_all_new_payslips(): void
    {
        $this->mock(GeneratePayrollService::class, function ($mock) {
            $mock->shouldReceive('getEmployeeSalariesToCreatePayslip')->once()->andReturnUsing(function () {
                $this->payslip(20, ['salary_from' => '2026-11-01', 'salary_to' => '2026-11-30']);
                throw new \RuntimeException('Calculation failed');
            });
        });
        try {
            app(MonthlyPayrollRecordService::class)->generate('2026-11', Branch::findOrFail(1), [], '$');
            $this->fail('Expected generation failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Calculation failed', $exception->getMessage());
        }
        $this->assertDatabaseMissing('employee_payslips', ['salary_from' => '2026-11-01']);
        $this->assertDatabaseCount('monthly_payroll_records', 0);
    }

    public function test_generation_engine_skips_every_existing_monthly_payslip_status(): void
    {
        Schema::create('ssf', fn (Blueprint $table) => $table->id());
        $dependencies = [];
        foreach ((new \ReflectionClass(GeneratePayrollService::class))->getConstructor()->getParameters() as $parameter) {
            $dependency = \Mockery::mock($parameter->getType()->getName());
            if ($parameter->getName() === 'employeeSalaryRepository') {
                $dependency->shouldReceive('getAllEmployeeForPayroll')->times(4)->andReturn(collect([(object) ['employee_id' => 20]]));
            } elseif ($parameter->getName() === 'ssfService') {
                $dependency->shouldReceive('getSSFDetailForPayroll')->times(4)->andReturn(null);
            } elseif ($parameter->getName() === 'pfService') {
                $dependency->shouldReceive('getPFDetailForPayroll')->times(4)->andReturn(null);
            } elseif ($parameter->getName() === 'payslipRepository') {
                $dependency->shouldNotReceive('getEmployeePayslipSummary');
            }
            $dependencies[] = $dependency;
        }
        $generator = new GeneratePayrollService(...$dependencies);
        foreach (['generated', 'review', 'locked', 'paid'] as $status) {
            DB::table('employee_payslips')->where('employee_id', 20)->update(['status' => $status]);
            $result = $generator->getEmployeeSalariesToCreatePayslip([
                'salary_cycle' => 'monthly', 'year' => 2026, 'month' => 10,
                'branch_id' => 1, 'is_bs_enabled' => false, 'preserve_existing' => true,
            ]);
            $this->assertEmpty($result['employeeSalary']);
            $this->assertDatabaseHas('employee_payslips', ['employee_id' => 20, 'net_salary' => 390, 'status' => $status]);
        }
    }

    public function test_yearly_overview_contains_twelve_months_and_only_saved_month_totals(): void
    {
        $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1])->assertRedirect();
        $report = app(MonthlyPayrollReportService::class)->yearly($this->filters(), 2026);
        $this->assertCount(12, $report['months']);
        $this->assertSame('2026-01', $report['months']->first()['month']);
        $this->assertSame('2026-12', $report['months']->last()['month']);
        $this->assertEquals(1590, $report['summary']['net']);
        $this->assertSame(0, $report['months'][8]['summary']['records']);
        $this->assertSame(13, $report['months'][9]['summary']['records']);
        $root = sys_get_temp_dir().'/monthly-yearly-layout-'.bin2hex(random_bytes(5));
        mkdir($root.'/layouts', 0777, true);
        file_put_contents($root.'/layouts/master.blade.php', '@yield("main-content")');
        $this->app['view']->getFinder()->prependLocation($root);
        try {
            $response = $this->get('/_test/monthly-payroll?view=yearly&year=2026');
            $response->assertOk()->assertSee('2026-01')->assertSee('2026-12')->assertSee('Generate &amp; Save', false);
            $this->assertSame(12, substr_count($response->getContent(), 'data-payroll-month-row='));
        } finally {
            unlink($root.'/layouts/master.blade.php'); rmdir($root.'/layouts'); rmdir($root);
        }
    }

    private function loan(array $values = []): int
    {
        return DB::table('advance_salaries')->insertGetId(array_replace([
            'employee_id' => 20, 'requested_amount' => 999, 'released_amount' => 100,
            'status' => 'approved', 'amount_granted_date' => '2026-10-01 00:00:00', 'is_settled' => false,
        ], $values));
    }

    private function monthlyLoans(): void
    {
        $this->loan();
        $this->loan(['released_amount' => 25.50, 'amount_granted_date' => '2026-10-31 23:59:59', 'is_settled' => true]);
        $this->loan(['released_amount' => .25, 'amount_granted_date' => '2026-10-15 12:00:00']);
        $this->loan(['released_amount' => 900, 'amount_granted_date' => '2026-09-30 23:59:59']);
        $this->loan(['released_amount' => 700, 'amount_granted_date' => '2026-11-01 00:00:00']);
        $this->loan(['released_amount' => 600, 'amount_granted_date' => '2025-10-15 12:00:00']);
        foreach (['pending', 'processing', 'rejected'] as $status) $this->loan(['released_amount' => 500, 'status' => $status]);
        $this->loan(['released_amount' => 200, 'amount_granted_date' => null]);
    }

    public function test_monthly_loans_use_approved_released_money_including_settled_loans_and_month_end(): void
    {
        $this->monthlyLoans();
        DB::table('employee_payslips')->where('employee_id', 20)->update(['advance_salary' => 7]);
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['q' => 'ky20']));
        $values = $service->rowValues($report['payslips']->first(), [], 1);
        $this->assertEquals(125.75, $values['advance_salary']);
        $this->assertEquals(125.75, $report['summary']['advance_salary']);
        $this->assertEquals(390, $values['net_salary']);
        $this->assertEquals(7, $report['payslips']->first()->advance_salary);
        $this->assertSame('125.75', $service->exportTable($report)['rows'][0][17]);
    }

    public function test_monthly_loan_total_covers_all_filtered_employees_not_only_the_current_page(): void
    {
        $this->monthlyLoans();
        $this->loan(['employee_id' => 111, 'released_amount' => 8]);
        $this->loan(['employee_id' => 21, 'released_amount' => 5000]);
        $service = app(MonthlyPayrollReportService::class);
        $report = $service->report($this->filters(['per_page' => '10', 'branch_id' => 2]));
        $this->assertEquals(133.75, $report['summary']['advance_salary']);
        $this->assertNotContains(111, $report['payslips']->pluck('employee_id'));
        $this->assertEquals(125.75, $service->report($this->filters(['q' => 'ky20']))['summary']['advance_salary']);
        $this->assertEquals(8, $service->report($this->filters(['status' => 'paid']))['summary']['advance_salary']);
        $this->payslip(20, ['advance_salary' => 7]);
        $this->assertEquals(125.75, $service->report($this->filters(['q' => 'ky20']))['summary']['advance_salary']);
    }

    public function test_released_loan_calculation_handles_leap_year_february(): void
    {
        $this->loan(['released_amount' => 5, 'amount_granted_date' => '2024-02-01 00:00:00']);
        $this->loan(['released_amount' => 7, 'amount_granted_date' => '2024-02-29 23:59:59']);
        $this->loan(['released_amount' => 100, 'amount_granted_date' => '2024-03-01 00:00:00']);
        $this->loan(['released_amount' => 200, 'amount_granted_date' => '2023-02-28 12:00:00']);
        $loans = app(MonthlyPayrollLoanService::class);
        $this->assertEquals([20 => 12], $loans->amounts('2024-02', [20]));
        $this->assertEquals([20 => 100], $loans->amounts('2024-03', [20]));
    }

    public function test_saved_monthly_loan_amounts_freeze_and_yearly_totals_add_saved_months(): void
    {
        $this->monthlyLoans(); $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1])->assertRedirect();
        $record = MonthlyPayrollRecord::firstOrFail();
        $this->assertEquals(125.75, $record->summary['advance_salary']);
        $this->assertSame(MonthlyPayrollLoanService::SOURCE, $record->options['loan_amount_source']);
        $september = $record->replicate();
        $rows = $record->rows;
        foreach ($rows as &$row) $row['values']['advance_salary'] = $row['attributes']['employee_id'] === 20 ? 30 : 0;
        unset($row);
        $september->fill(['month' => '2026-09', 'rows' => $rows, 'summary' => array_replace($record->summary, ['advance_salary' => 30])])->save();
        DB::table('advance_salaries')->update(['released_amount' => 5000]);
        $this->loan(['released_amount' => 60]);
        $service = app(MonthlyPayrollReportService::class);
        $this->assertEquals(125.75, $service->report($this->filters())['summary']['advance_salary']);
        $yearly = $service->yearly($this->filters(), 2026);
        $this->assertEquals(155.75, $yearly['summary']['advance_salary']);
        $this->assertEquals(30, $yearly['months'][8]['summary']['advance_salary']);
        $this->assertEquals(125.75, $yearly['months'][9]['summary']['advance_salary']);
        $this->assertEquals(0, $yearly['months'][0]['summary']['advance_salary']);
    }

    public function test_loan_correction_updates_only_legacy_loan_values_and_can_be_rolled_back(): void
    {
        $this->loan(); $this->allowMonthlyGeneration();
        $this->postJson('/_test/monthly-payroll/generate', ['month' => '2026-10', 'branch_id' => 1])->assertRedirect();
        $record = MonthlyPayrollRecord::firstOrFail();
        $options = $record->options; unset($options['loan_amount_source']);
        $rows = $record->rows;
        foreach ($rows as &$row) $row['values']['advance_salary'] = 7;
        unset($row);
        $summary = $record->summary; unset($summary['advance_salary']);
        $record->update(['options' => $options, 'rows' => $rows, 'summary' => $summary]);
        $migration = require database_path('migrations/2026_10_30_000002_correct_monthly_payroll_loan_amounts.php');
        $migration->up();
        $fixed = $record->fresh();
        $this->assertEquals(100, $fixed->rows[0]['values']['advance_salary']);
        $this->assertEquals(0, $fixed->rows[1]['values']['advance_salary']);
        $this->assertEquals(390, $fixed->rows[0]['values']['net_salary']);
        $this->assertEquals(100, $fixed->summary['advance_salary']);
        $this->assertEquals(1590, $fixed->summary['net']);
        $beforeSecondRun = $fixed->rows;
        $migration->up();
        $this->assertSame($beforeSecondRun, $record->fresh()->rows);
        $migration->down();
        $this->assertSame($rows, $record->fresh()->rows);
        $this->assertSame($summary, $record->fresh()->summary);
        $this->assertSame($options, $record->fresh()->options);
    }
}
