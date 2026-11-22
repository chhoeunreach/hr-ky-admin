<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AppAppearanceApiController;
use App\Http\Controllers\Web\AppAppearanceController;
use App\Http\Middleware\MobileFunctionPermission;
use App\Models\Admin;
use App\Models\AppSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Tests\TestCase;

class AppAppearanceTest extends TestCase
{
    private Admin $admin;
    private string $viewDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile_permissions' => require __DIR__.'/../../config/mobile_permissions.php']);

        Schema::dropIfExists('app_settings');
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('value')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
        Schema::dropIfExists('admins');
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->admin = Admin::create([
            'name' => 'Appearance Admin',
            'username' => 'appearanceadmin',
            'email' => 'appearance@example.test',
            'password' => bcrypt('secret'),
            'is_active' => true,
        ]);

        Gate::before(fn ($user = null, $ability = null) => true);
        Auth::guard('admin')->login($this->admin);

        $this->viewDirectory = sys_get_temp_dir().'/app-appearance-view-'.bin2hex(random_bytes(6));
        mkdir($this->viewDirectory.'/layouts', 0777, true);
        file_put_contents(
            $this->viewDirectory.'/layouts/master.blade.php',
            "@yield('title')|@yield('action')|@yield('styles')|@yield('main-content')|@yield('scripts')"
        );
        view()->getFinder()->prependLocation($this->viewDirectory);
        view()->getFinder()->flush();

        Route::middleware(SubstituteBindings::class)->group(function () {
            Route::get('/_test/mobile-tool/app-appearance', [AppAppearanceController::class, 'edit']);
            Route::post('/_test/mobile-tool/app-appearance', [AppAppearanceController::class, 'update']);
            Route::post('/_test/mobile-tool/app-appearance/reset', [AppAppearanceController::class, 'reset']);
        });
        Route::middleware(MobileFunctionPermission::class)
            ->get('/_test/mobile-tool/api-app-appearance', [AppAppearanceApiController::class, 'show']);
    }

    protected function tearDown(): void
    {
        if (isset($this->viewDirectory)) {
            @unlink($this->viewDirectory.'/layouts/master.blade.php');
            @rmdir($this->viewDirectory.'/layouts');
            @rmdir($this->viewDirectory);
        }

        parent::tearDown();
    }

    public function test_app_appearance_page_renders(): void
    {
        $this->get('/_test/mobile-tool/app-appearance')
            ->assertOk()
            ->assertSee('app-appearance')
            ->assertSee('heat')
            ->assertSee('snow')
            ->assertSee('emoji_effect')
            ->assertSee('refreshEffectsPreview', false)
            ->assertSee('Floating effects preview');
    }

    public function test_admin_saves_background_upload_and_effect_toggles(): void
    {
        Storage::fake('public');

        $this->post('/_test/mobile-tool/app-appearance', [
            'background_type' => 'image',
            'background' => UploadedFile::fake()->image('bg.jpg'),
            'heat' => '1',
            'snow' => '1',
            'floating_hearts' => '0',
            'rose_petals' => '1',
            'heart_glow' => '0',
            'sparkle_effects' => '1',
            'emoji_effect' => '1',
            'emoji_char' => '🎄',
        ])->assertRedirect(route('admin.mobile-tools.app-appearance'));

        $stored = json_decode(AppSetting::where('slug', 'app-appearance')->value('value'), true);
        $this->assertSame('image', $stored['background_type']);
        $this->assertStringContainsString('/storage/mobile-tool/app-appearance/', $stored['background_url']);
        $this->assertTrue($stored['heat']);
        $this->assertTrue($stored['snow']);
        $this->assertFalse($stored['floating_hearts']);
        $this->assertTrue($stored['rose_petals']);
        $this->assertFalse($stored['heart_glow']);
        $this->assertTrue($stored['sparkle_effects']);
        $this->assertTrue($stored['emoji_effect']);
        $this->assertSame('🎄', $stored['emoji_char']);

        $relative = substr($stored['background_url'], strpos($stored['background_url'], '/storage/') + strlen('/storage/'));
        Storage::disk('public')->assertExists($relative);
    }

    public function test_unchecking_effects_persists_false(): void
    {
        $this->post('/_test/mobile-tool/app-appearance', [
            'background_type' => 'none',
            'heat' => '0',
            'snow' => '0',
            'floating_hearts' => '0',
            'rose_petals' => '0',
            'heart_glow' => '0',
            'sparkle_effects' => '0',
            'emoji_effect' => '0',
            'emoji_char' => '✨',
        ])->assertRedirect(route('admin.mobile-tools.app-appearance'));

        $stored = json_decode(AppSetting::where('slug', 'app-appearance')->value('value'), true);
        $this->assertSame('none', $stored['background_type']);
        $this->assertNull($stored['background_url']);
        foreach (['heat', 'snow', 'floating_hearts', 'rose_petals', 'heart_glow', 'sparkle_effects', 'emoji_effect'] as $flag) {
            $this->assertFalse($stored[$flag], "Expected {$flag} to be false");
        }
    }

    public function test_custom_floating_effects_can_be_created_updated_deleted_and_read_by_mobile(): void
    {
        $payload = [
            'background_type' => 'none',
            'custom_effects' => [
                ['id' => 'welcome-stars', 'name' => 'Welcome stars', 'emoji' => '🌟', 'enabled' => '1'],
            ],
        ];

        $this->post('/_test/mobile-tool/app-appearance', $payload)
            ->assertRedirect(route('admin.mobile-tools.app-appearance'));

        $this->assertSame(
            [['id' => 'welcome-stars', 'name' => 'Welcome stars', 'emoji' => '🌟', 'enabled' => true]],
            json_decode(AppSetting::where('slug', 'app-appearance')->value('value'), true)['custom_effects']
        );

        $this->get('/_test/mobile-tool/app-appearance')
            ->assertOk()
            ->assertSee('name="custom_effects[0][name]" value="Welcome stars"', false)
            ->assertSee('name="custom_effects[0][emoji]" value="🌟"', false)
            ->assertSee('data-remove-effect', false)
            ->assertSee('Delete');

        $this->post('/_test/mobile-tool/app-appearance', [
            'background_type' => 'none',
            'custom_effects' => [
                ['id' => 'welcome-stars', 'name' => 'Company celebration', 'emoji' => '🎉'],
            ],
        ])->assertRedirect(route('admin.mobile-tools.app-appearance'));

        $stored = json_decode(AppSetting::where('slug', 'app-appearance')->value('value'), true);
        $this->assertSame('Company celebration', $stored['custom_effects'][0]['name']);
        $this->assertSame('🎉', $stored['custom_effects'][0]['emoji']);
        $this->assertFalse($stored['custom_effects'][0]['enabled']);

        $this->getJson('/_test/mobile-tool/api-app-appearance')
            ->assertOk()
            ->assertJsonPath('data.custom_effects.0.name', 'Company celebration')
            ->assertJsonPath('data.custom_effects.0.emoji', '🎉')
            ->assertJsonPath('data.custom_effects.0.enabled', false);

        $this->post('/_test/mobile-tool/app-appearance', ['background_type' => 'none'])
            ->assertRedirect(route('admin.mobile-tools.app-appearance'));
        $this->assertSame([], json_decode(
            AppSetting::where('slug', 'app-appearance')->value('value'), true
        )['custom_effects']);
    }

    public function test_app_appearance_reset_removes_setting_and_uploaded_file(): void
    {
        Storage::fake('public');

        $this->post('/_test/mobile-tool/app-appearance', [
            'background_type' => 'svg',
            'background' => UploadedFile::fake()->create('bg.svg', 10, 'image/svg+xml'),
            'heat' => '1',
            'snow' => '0',
            'floating_hearts' => '1',
            'rose_petals' => '0',
            'heart_glow' => '1',
            'sparkle_effects' => '1',
            'emoji_effect' => '0',
            'emoji_char' => '✨',
        ])->assertRedirect();

        $stored = json_decode(AppSetting::where('slug', 'app-appearance')->value('value'), true);
        $this->assertSame('svg', $stored['background_type']);
        $relative = substr($stored['background_url'], strpos($stored['background_url'], '/storage/') + strlen('/storage/'));
        Storage::disk('public')->assertExists($relative);

        $this->post('/_test/mobile-tool/app-appearance/reset')
            ->assertRedirect(route('admin.mobile-tools.app-appearance'));

        $this->assertNull(AppSetting::where('slug', 'app-appearance')->value('value'));
        Storage::disk('public')->assertMissing($relative);
    }

    public function test_mobile_api_returns_appearance_config(): void
    {
        AppSetting::create([
            'name' => 'App Appearance',
            'slug' => 'app-appearance',
            'value' => json_encode([
                'background_type' => 'image',
                'background_url' => 'https://hr.example.test/storage/mobile-tool/app-appearance/bg.jpg',
                'heat' => true,
                'snow' => true,
                'floating_hearts' => false,
                'rose_petals' => false,
                'heart_glow' => true,
                'sparkle_effects' => true,
                'emoji_effect' => true,
                'emoji_char' => '🎉',
            ]),
            'status' => 1,
        ]);

        $rule = config('mobile_permissions.functions.AppAppearanceApiController@show');
        $this->assertNull($rule, 'Appearance config read must stay available to every authenticated user.');

        $this->getJson('/_test/mobile-tool/api-app-appearance')
            ->assertOk()
            ->assertJsonPath('data.background_type', 'image')
            ->assertJsonPath('data.background_url', 'https://hr.example.test/storage/mobile-tool/app-appearance/bg.jpg')
            ->assertJsonPath('data.heat', true)
            ->assertJsonPath('data.snow', true)
            ->assertJsonPath('data.floating_hearts', false)
            ->assertJsonPath('data.emoji_effect', true)
            ->assertJsonPath('data.emoji_char', '🎉');
    }

    public function test_mobile_api_returns_defaults_when_never_configured(): void
    {
        $this->getJson('/_test/mobile-tool/api-app-appearance')
            ->assertOk()
            ->assertJsonPath('data.background_type', 'none')
            ->assertJsonPath('data.background_url', null)
            ->assertJsonPath('data.heat', false)
            ->assertJsonPath('data.snow', false)
            ->assertJsonPath('data.floating_hearts', true)
            ->assertJsonPath('data.heart_glow', true)
            ->assertJsonPath('data.sparkle_effects', true)
            ->assertJsonPath('data.emoji_effect', false)
            ->assertJsonPath('data.custom_effects', []);
    }

    public function test_saving_type_without_file_is_rejected(): void
    {
        $this->post('/_test/mobile-tool/app-appearance', [
            'background_type' => 'image',
            'heat' => '1',
            'snow' => '0',
            'floating_hearts' => '1',
            'rose_petals' => '0',
            'heart_glow' => '1',
            'sparkle_effects' => '1',
            'emoji_effect' => '0',
            'emoji_char' => '✨',
        ])->assertSessionHas('danger');

        $this->assertNull(AppSetting::where('slug', 'app-appearance')->value('value'));
    }
}
