<?php

namespace Tests\Feature;

use App\Repositories\TimeLeaveRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TimeLeaveToolbarTest extends TestCase
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('employee_code');
            $table->softDeletes();
        });
        Schema::create('time_leaves', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by');
            $table->integer('branch_id');
            $table->date('issue_date');
            $table->string('status');
            $table->text('reasons');
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Dara Sok', 'username' => 'dara.s', 'employee_code' => 'KY001'],
            ['id' => 2, 'name' => 'Sophea Chan', 'username' => 'sophea.c', 'employee_code' => 'KY002'],
        ]);
        DB::table('time_leaves')->insert([
            ['id' => 1, 'requested_by' => 1, 'branch_id' => 1, 'issue_date' => '2026-11-06', 'status' => 'pending', 'reasons' => 'Family appointment'],
            ['id' => 2, 'requested_by' => 1, 'branch_id' => 2, 'issue_date' => '2026-11-06', 'status' => 'pending', 'reasons' => 'Family appointment'],
            ['id' => 3, 'requested_by' => 1, 'branch_id' => 1, 'issue_date' => '2026-11-06', 'status' => 'approved', 'reasons' => 'Family appointment'],
            ['id' => 4, 'requested_by' => 2, 'branch_id' => 1, 'issue_date' => '2026-11-06', 'status' => 'pending', 'reasons' => 'Medical appointment'],
            ['id' => 5, 'requested_by' => 1, 'branch_id' => 1, 'issue_date' => '2025-11-06', 'status' => 'pending', 'reasons' => 'Family appointment'],
        ]);
    }

    public function test_search_keeps_branch_status_and_year_constraints(): void
    {
        foreach (['Dara', 'dara.s', 'KY001', 'Family'] as $search) {
            $results = (new TimeLeaveRepository)->getAllEmployeeTimeLeaveRequest([
                'search' => $search, 'branch_id' => 1, 'status' => 'pending', 'year' => '2026', 'per_page' => 10,
            ]);
            $this->assertSame([1], $results->pluck('id')->all(), $search);
        }

        $results = (new TimeLeaveRepository)->getAllEmployeeTimeLeaveRequest([
            'search' => 'Medical', 'branch_id' => 1, 'status' => 'pending', 'year' => '2026', 'per_page' => 10,
        ]);
        $this->assertSame([4], $results->pluck('id')->all());
    }

    public function test_page_size_controls_results_and_rejects_unsupported_sizes(): void
    {
        for ($id = 6; $id <= 35; $id++) {
            DB::table('time_leaves')->insert([
                'id' => $id, 'requested_by' => 1, 'branch_id' => 1,
                'issue_date' => '2026-11-06', 'status' => 'pending', 'reasons' => 'Family appointment',
            ]);
        }
        foreach ([10 => 10, 25 => 25, 50 => 50, 100 => 100, 500 => 25, -1 => 25] as $input => $expected) {
            $results = (new TimeLeaveRepository)->getAllEmployeeTimeLeaveRequest(['per_page' => $input]);
            $this->assertSame($expected, $results->perPage());
            $this->assertCount(min($expected, 35), $results->items());
            $this->assertSame(35, $results->total());
        }
    }
    public function test_browser_table_receives_every_matching_row_without_losing_filters(): void
    {
        for ($id = 6; $id <= 35; $id++) {
            DB::table('time_leaves')->insert([
                'id' => $id, 'requested_by' => 1, 'branch_id' => 1,
                'issue_date' => '2026-11-06', 'status' => 'pending', 'reasons' => 'Family appointment',
            ]);
        }
        $rows = (new TimeLeaveRepository)->getAllEmployeeTimeLeaveRequest([
            'branch_id' => 1, 'status' => 'pending', 'year' => '2026', 'per_page' => 10,
        ], ['*'], ['leaveRequestedBy:id,name,username,employee_code'], false);

        $this->assertCount(32, $rows);
        $this->assertSame(35, $rows->first()->id);
        $this->assertEqualsCanonicalizing([1, 4, ...range(6, 35)], $rows->pluck('id')->all());
        $this->assertSame('KY001', $rows->first()->leaveRequestedBy->employee_code);
    }

}
