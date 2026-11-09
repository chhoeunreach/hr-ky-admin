<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Repositories\LeaveRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveRequestToolbarTest extends TestCase
{
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
        parent::setUp();
        DB::purge('sqlite');
        Schema::create('app_settings', function (Blueprint $table) {$table->string('slug');$table->boolean('status');});
        Schema::create('users', function (Blueprint $table) {
            $table->id();foreach (['name','email','username','employee_code'] as $column) $table->string($column);
            $table->softDeletes();
        });
        foreach (['branches' => 'name','departments' => 'dept_name','leave_types' => 'name'] as $name => $column) {
            Schema::create($name, function (Blueprint $table) use ($column) {$table->id();$table->string($column);});
        }
        Schema::create('leave_requests_master', function (Blueprint $table) {
            $table->id();foreach (['requested_by','branch_id','department_id','leave_type_id'] as $column) $table->integer($column);
            foreach (['leave_from','leave_to','leave_requested_date'] as $column) $table->date($column);
            $table->integer('no_of_days');$table->string('status');$table->text('reasons');$table->string('admin_remark')->nullable();
        });
        DB::table('users')->insert(['id'=>1,'name'=>'Dara','email'=>'dara@example.test','username'=>'dara','employee_code'=>'KY001']);
        DB::table('branches')->insert(['id'=>1,'name'=>'Main']);
        DB::table('departments')->insert(['id'=>1,'dept_name'=>'Sales']);
        DB::table('leave_types')->insert(['id'=>1,'name'=>'Annual']);
        foreach (range(1,31) as $id) DB::table('leave_requests_master')->insert([
            'requested_by'=>1,'branch_id'=>1,'department_id'=>1,'leave_type_id'=>1,'leave_from'=>'2026-11-09','leave_to'=>'2026-11-09',
            'leave_requested_date'=>'2026-11-08','no_of_days'=>1,'status'=>'pending','reasons'=>'Family appointment',
        ]);
        $this->actingAs((new Admin)->forceFill(['id'=>1]), 'admin');
        Gate::before(fn () => true);
    }

    private function filters(array $overrides = []): array
    {
        return array_merge(['branch_id'=>null,'department_id'=>null,'requested_by'=>null,'leave_type'=>null,'search'=>null,'year'=>'2026','month'=>null,'status'=>null,'per_page'=>'25'], $overrides);
    }

    public function test_all_and_larger_page_sizes_include_every_filtered_request(): void
    {
        $repository = app(LeaveRepository::class);
        $this->assertCount(25,$repository->getAllEmployeeLeaveRequest($this->filters())->items());
        $this->assertCount(31,$repository->getAllEmployeeLeaveRequest($this->filters(['per_page'=>'all','search'=>'KY001','status'=>'pending']))->items());
        $this->assertSame(200,$repository->getAllEmployeeLeaveRequest($this->filters(['per_page'=>'200']))->perPage());
        $this->assertSame(0,$repository->getAllEmployeeLeaveRequest($this->filters(['per_page'=>'all','status'=>'approved']))->total());
    }

    public function test_shared_toolbar_stays_outside_the_refreshed_rows_and_filters_start_collapsed(): void
    {
        $request = Request::create('/admin/leave-request','GET',['status'=>'pending','requested_by'=>'1']);
        app()->instance('request',$request);
        $filters = $this->filters(['status'=>'pending','requested_by'=>'1','per_page'=>'all']);
        $records = app(LeaveRepository::class)->getAllEmployeeLeaveRequest($filters,['*'],['leaveType','leaveRequestedBy','branch','department']);
        $source = str_replace("@extends('layouts.master')",'',file_get_contents(resource_path('views/admin/leaveRequest/index.blade.php')));
        $html = Blade::render($source."\n@yield('main-content')",[
            'filterParameters'=>$filters,'leaveDetails'=>$records,'months'=>\App\Helpers\AppHelper::MONTHS,
            'leaveTypes'=>DB::table('leave_types')->pluck('name','id')->all(),
        ]);
        $this->assertStringContainsString('class="collapse" id="leaveRequestFilters"',$html);
        $this->assertStringContainsString('data-directory-table="leaveRequestTable"',$html);
        $this->assertStringContainsString('data-open-leave-request-form',$html);
        $this->assertStringContainsString('data-directory-export="excel"',$html);
        $this->assertStringContainsString('data-directory-value="Family appointment"',$html);
        $this->assertStringContainsString('data-directory-total="31"',$html);
        $this->assertLessThan(strpos($html,'id="leave-request-list-block"'),strpos($html,'data-directory-table="leaveRequestTable"'));
        $this->assertStringNotContainsString('id="dataTableExample"',$html);
        $this->assertStringNotContainsString('id="copy-leave-request-export"',$html);
    }
}
