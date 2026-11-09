<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\AdminController;
use App\Models\Admin;
use App\Repositories\AdminRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDirectoryTest extends TestCase
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
        Schema::create('admins', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->string('username');
            $table->string('password'); $table->boolean('is_active'); $table->timestamps();
        });
        foreach (range(1, 30) as $index) DB::table('admins')->insert([
            'name' => 'Admin '.str_pad($index, 2, '0', STR_PAD_LEFT), 'email' => 'user'.$index.'@example.test',
            'username' => 'account'.$index, 'password' => 'private-secret-'.$index, 'is_active' => $index % 2,
        ]);
        $this->actingAs(Admin::find(1), 'admin');
    }

    private function results(array $filters = [])
    {
        return app(AdminRepository::class)->getAll(['id', 'name', 'email', 'is_active'], $filters);
    }

    public function test_search_and_specific_filters_combine_without_leaking_active_users(): void
    {
        $this->assertSame([2], $this->results(['search' => 'account2', 'email' => 'user2@', 'is_active' => '0'])->pluck('id')->all());
        $this->assertCount(0, $this->results(['search' => 'account2', 'email' => 'user2@', 'is_active' => '1'])->items());
        $this->assertSame([12], $this->results(['search' => 'user12@'])->pluck('id')->all());
        $this->assertSame([14], $this->results(['name' => 'Admin 14'])->pluck('id')->all());
    }

    public function test_all_exports_every_matching_user_and_page_sizes_remain_bounded(): void
    {
        $this->assertCount(25, $this->results()->items());
        $this->assertCount(30, $this->results(['per_page' => 'all'])->items());
        $this->assertCount(15, $this->results(['per_page' => 'all', 'is_active' => '0'])->items());
        $this->assertCount(10, $this->results(['per_page' => '10'])->items());
        $this->assertCount(25, $this->results(['per_page' => '-1'])->items());
        $this->assertSame(1, $this->results(['per_page' => 'all', 'search' => 'Missing'])->perPage());
    }

    public function test_users_page_renders_shared_controls_without_exposing_credentials(): void
    {
        $request = Request::create('/admin/users', 'GET', ['per_page' => 'all', 'is_active' => '0']);
        app()->instance('request', $request);
        $view = app(AdminController::class)->index($request);
        $this->assertSame('admin.users.index', $view->name());
        $this->assertCount(15, $view->getData()['admins']->items());
        $this->assertArrayNotHasKey('password', $view->getData()['admins']->first()->getAttributes());
        $source = str_replace("@extends('layouts.master')", '', file_get_contents(resource_path('views/admin/users/index.blade.php')));
        $html = Blade::render($source."\n@yield('main-content')", $view->getData());
        $this->assertStringContainsString('id="usersDirectoryTable"', $html);
        $this->assertStringContainsString('data-directory-table="usersDirectoryTable"', $html);
        $this->assertStringContainsString('id="usersFilters"', $html);
        $this->assertStringContainsString('data-directory-export="excel"', $html);
        $this->assertStringContainsString('class="directory-add"', $html);
        $this->assertStringNotContainsString('private-secret-', $html);
        $this->assertStringNotContainsString('id="dataTableExample"', $html);
    }
}
