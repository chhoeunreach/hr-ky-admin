<?php

namespace Tests\Feature;

use App\Repositories\BranchRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchDirectoryPaginationTest extends TestCase
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
        Schema::create('branches', function (Blueprint $table) {$table->id(); $table->string('name'); $table->timestamps();});
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('avatar')->nullable(); $table->integer('branch_id');
            $table->string('status'); $table->boolean('is_active'); $table->softDeletes();
        });
        foreach (range(1, 30) as $index) DB::table('branches')->insert(['name' => 'Team '.$index]);
        DB::table('branches')->insert(['name' => 'Other']);
    }

    public function test_all_includes_every_filtered_branch_instead_of_only_the_first_page(): void
    {
        $result = app(BranchRepository::class)->getAllCompanyBranches(['name' => 'Team', 'per_page' => 'all']);
        $this->assertSame(30, $result->total());
        $this->assertCount(30, $result->items());
        $this->assertSame(1, $result->lastPage());
    }

    public function test_invalid_sizes_fall_back_to_ten_and_all_handles_empty_results(): void
    {
        $repository = app(BranchRepository::class);
        $this->assertCount(10, $repository->getAllCompanyBranches(['per_page' => '-1'])->items());
        $this->assertCount(25, $repository->getAllCompanyBranches(['per_page' => '25'])->items());
        $empty = $repository->getAllCompanyBranches(['name' => 'Missing', 'per_page' => 'all']);
        $this->assertSame(0, $empty->total());
        $this->assertSame(1, $empty->perPage());
    }
}
