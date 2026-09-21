<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetLoginTables();
    }

    protected function tearDown(): void
    {
        $this->resetLoginTables();

        parent::tearDown();
    }

    public function test_wrong_admin_password_shows_invalid_credentials_message(): void
    {
        Admin::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login.process'), [
            'user_type' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('danger', __('auth.invalid_credentials'));
    }

    private function resetLoginTables(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('theme_settings');

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('value')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->nullable();
            $table->string('hover_color')->nullable();
            $table->string('dark_primary_color')->nullable();
            $table->string('dark_hover_color')->nullable();
            $table->timestamps();
        });
    }
}
