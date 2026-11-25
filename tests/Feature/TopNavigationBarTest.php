<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TopNavigationBarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.prefix' => '',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'app.supported_locales' => [
                'en' => ['name' => 'English', 'flag' => 'us'],
                'km' => ['name' => 'Khmer', 'flag' => 'kh'],
            ],
        ]);
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('permission_key')->unique();
        });

        Schema::connection('sqlite')->create('permission_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
    }

    public function test_top_navigation_bar_contains_all_required_elements_in_one_line(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Test Administrator',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret'),
        ]);

        Auth::guard('admin')->setUser($admin);

        $view = view('admin.section.nav')->render();

        // 1. Menu toggler
        $this->assertStringContainsString('sidebar-toggler', $view);
        $this->assertStringContainsString('data-feather="menu"', $view);

        // 2. Server status
        $this->assertStringContainsString('navServerStatus', $view);
        $this->assertStringContainsString('nav-status-pulse', $view);
        $this->assertStringContainsString('nav-server-status', $view);

        // 3. Sent location link
        $this->assertStringContainsString('navSentLocation', $view);
        $this->assertStringContainsString(route('track-location'), $view);
        $this->assertStringContainsString('data-feather="map-pin"', $view);

        // 4. Chat link
        $this->assertStringContainsString('navEmployeeChat', $view);
        $this->assertStringContainsString(route('admin.employee-chat'), $view);
        $this->assertStringContainsString('data-feather="message-square"', $view);

        // 5. Notification bell
        $this->assertStringContainsString('notificationDropdown', $view);
        $this->assertStringContainsString('data-feather="bell"', $view);

        // 6. Profile dropdown
        $this->assertStringContainsString('profileDropdown', $view);
        $this->assertStringContainsString(route('admin.logout'), $view);

        // 7. Single-line flex layout
        $this->assertStringContainsString('flex-wrap: nowrap !important;', $view);
        $this->assertStringContainsString('white-space: nowrap !important;', $view);
    }
}
