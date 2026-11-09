<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\PostController;
use App\Models\Post;
use App\Models\User;
use App\Requests\Post\PostRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use Tests\TestCase;

class PostEditFlowTest extends TestCase
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
        Schema::create('companies', function (Blueprint $table) {$table->id(); $table->string('name');});
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->integer('company_id'); $table->boolean('is_active')->default(true);
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('dept_name'); $table->integer('company_id'); $table->integer('branch_id');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); $table->string('post_name'); $table->integer('branch_id')->nullable(); $table->integer('dept_id');
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->integer('post_id'); $table->softDeletes();
        });
        DB::table('companies')->insert(['id' => 1, 'name' => 'Company']);
        DB::table('branches')->insert([
            ['id' => 1, 'name' => 'Main', 'company_id' => 1],
            ['id' => 2, 'name' => 'Other', 'company_id' => 1],
        ]);
        DB::table('departments')->insert([
            ['id' => 10, 'dept_name' => 'Sales', 'branch_id' => 1, 'company_id' => 1],
            ['id' => 11, 'dept_name' => 'Finance', 'branch_id' => 1, 'company_id' => 1],
            ['id' => 20, 'dept_name' => 'Sales', 'branch_id' => 2, 'company_id' => 1],
        ]);
        $this->actingAs((new User())->forceFill(['id' => 1, 'company_id' => 1]));
        Gate::before(fn () => true);
    }

    private function editData(): array
    {
        return app(PostController::class)->edit(Post::first()->id)->getData();
    }

    public function test_edit_loads_real_branch_options_and_marks_saved_assignments(): void
    {
        Post::create(['post_name' => 'Manager', 'branch_id' => 2, 'dept_id' => 20]);
        $data = $this->editData();
        $this->assertEquals([1, 2], $data['companyDetail']->branches->pluck('id')->all());
        $this->assertSame(['2'], $data['selectedBranchIds']);
        $this->assertSame(['20'], $data['selectedDepartmentIds']);
        $html = view('admin.post.common.form', $data + ['errors' => new ViewErrorBag()])->render();
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(2, $xpath->query('//select[@id="branch_id"]/option[@value!=""]')->length);
        $this->assertSame('2', $xpath->query('//select[@id="branch_id"]/option[@selected]')->item(0)->getAttribute('value'));
    }

    public function test_new_post_loads_all_branches_from_the_real_relationship(): void
    {
        $data = app(PostController::class)->create()->getData();
        $this->assertEquals([1, 2], $data['companyDetail']->branches->pluck('id')->all());
    }

    public function test_older_post_infers_its_missing_branch_from_its_department(): void
    {
        Post::create(['post_name' => 'Manager', 'branch_id' => null, 'dept_id' => 20]);
        $data = $this->editData();
        $this->assertSame(['2'], $data['selectedBranchIds']);
        $this->assertSame(['20'], $data['selectedDepartmentIds']);
    }

    public function test_saving_all_assignments_restores_them_when_reopened(): void
    {
        $post = Post::create(['post_name' => 'Manager', 'branch_id' => 1, 'dept_id' => 10]);
        $request = Mockery::mock(PostRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'post_name' => 'Manager', 'branch_id' => ['1', '2'], 'dept_id' => ['10', '11', '20'], 'is_active' => 1,
        ]);
        app(PostController::class)->update($request, $post->id);
        $this->assertFalse(session()->has('danger'));
        $this->assertSame(3, Post::count());
        $data = $this->editData();
        $this->assertEqualsCanonicalizing(['1', '2'], $data['selectedBranchIds']);
        $this->assertEqualsCanonicalizing(['10', '11', '20'], $data['selectedDepartmentIds']);
    }

    public function test_saving_a_subset_removes_only_unassigned_posts_and_reopens_that_subset(): void
    {
        Post::create(['post_name' => 'Manager', 'branch_id' => 1, 'dept_id' => 10]);
        $post = Post::create(['post_name' => 'Manager', 'branch_id' => 2, 'dept_id' => 20]);
        $request = Mockery::mock(PostRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'post_name' => 'Manager', 'branch_id' => ['2'], 'dept_id' => ['20'], 'is_active' => 1,
        ]);
        app(PostController::class)->update($request, $post->id);
        $this->assertFalse(session()->has('danger'));
        $this->assertSame(1, Post::count());
        $data = $this->editData();
        $this->assertSame(['2'], $data['selectedBranchIds']);
        $this->assertSame(['20'], $data['selectedDepartmentIds']);
    }
}
