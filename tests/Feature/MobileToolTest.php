<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MobileQuoteApiController;
use App\Http\Controllers\Web\BannerSlideshowController;
use App\Http\Controllers\Web\MobileQuoteController;
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

class MobileToolTest extends TestCase
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
            'name' => 'Mobile Tool Admin',
            'username' => 'mobiletool',
            'email' => 'mobiletool@example.test',
            'password' => bcrypt('secret'),
            'is_active' => true,
        ]);

        Gate::before(fn ($user = null, $ability = null) => true);
        Auth::guard('admin')->login($this->admin);

        $this->viewDirectory = sys_get_temp_dir().'/mobile-tool-view-'.bin2hex(random_bytes(6));
        mkdir($this->viewDirectory.'/layouts', 0777, true);
        file_put_contents(
            $this->viewDirectory.'/layouts/master.blade.php',
            "@yield('title')|@yield('action')|@yield('styles')|@yield('main-content')"
        );
        view()->getFinder()->prependLocation($this->viewDirectory);
        view()->getFinder()->flush();

        Route::middleware(SubstituteBindings::class)->group(function () {
            Route::get('/_test/mobile-tool/banner-slides', [BannerSlideshowController::class, 'edit']);
            Route::post('/_test/mobile-tool/banner-slides', [BannerSlideshowController::class, 'update']);
            Route::post('/_test/mobile-tool/banner-slides/reset', [BannerSlideshowController::class, 'reset']);
            Route::get('/_test/mobile-tool/quotes', [MobileQuoteController::class, 'index']);
            Route::post('/_test/mobile-tool/quotes', [MobileQuoteController::class, 'store']);
            Route::post('/_test/mobile-tool/quotes/{quoteId}/update', [MobileQuoteController::class, 'update']);
            Route::post('/_test/mobile-tool/quotes/{quoteId}/toggle', [MobileQuoteController::class, 'toggle']);
            Route::delete('/_test/mobile-tool/quotes/{quoteId}', [MobileQuoteController::class, 'destroy']);
        });
        Route::middleware(MobileFunctionPermission::class)
            ->get('/_test/mobile-tool/api-quotes', [MobileQuoteApiController::class, 'index']);
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

    public function test_banner_slides_page_renders(): void
    {
        $this->get('/_test/mobile-tool/banner-slides')
            ->assertOk()
            ->assertSee('Banner Slides')
            ->assertSee('Playback settings');
    }

    public function test_admin_saves_banner_slides_with_uploaded_image_and_settings(): void
    {
        Storage::fake('public');

        $this->post('/_test/mobile-tool/banner-slides', [
            'slides' => [
                [
                    'id' => 'slide_one',
                    'title' => 'Welcome',
                    'linkUrl' => 'https://example.com/welcome',
                    'imageUrl' => '',
                    'enabled' => '1',
                    'image' => UploadedFile::fake()->image('welcome.jpg'),
                ],
                [
                    'id' => 'slide_two',
                    'title' => 'Promo',
                    'linkUrl' => '',
                    'imageUrl' => 'https://cdn.example.com/promo.jpg',
                    'enabled' => '0',
                ],
            ],
            'intervalSeconds' => 7,
            'autoplay' => '1',
            'showIndicators' => '0',
            'pauseOnTouch' => '0',
            'loop' => '1',
            'transition' => 'slide',
        ])->assertRedirect(route('admin.mobile-tools.banner-slides'));

        $stored = json_decode(AppSetting::where('slug', 'home-banner-slideshow')->value('value'), true);
        $this->assertCount(2, $stored['slides']);
        $this->assertSame(7, $stored['intervalSeconds']);
        $this->assertSame('slide', $stored['transition']);
        $this->assertTrue($stored['autoplay']);
        $this->assertFalse($stored['showIndicators']);
        $this->assertFalse($stored['pauseOnTouch']);
        $this->assertTrue($stored['loop']);
        $this->assertSame('Welcome', $stored['slides'][0]['title']);
        $this->assertSame('https://cdn.example.com/promo.jpg', $stored['slides'][1]['imageUrl']);
        $this->assertFalse($stored['slides'][1]['enabled']);

        $uploaded = $stored['slides'][0]['imageUrl'];
        $this->assertStringContainsString('/storage/mobile-tool/banner-slides/', $uploaded);
        $relative = substr($uploaded, strpos($uploaded, '/storage/') + strlen('/storage/'));
        Storage::disk('public')->assertExists($relative);
    }

    public function test_banner_reset_removes_setting_and_uploaded_images(): void
    {
        Storage::fake('public');

        $this->post('/_test/mobile-tool/banner-slides', [
            'slides' => [
                [
                    'id' => 'slide_one',
                    'title' => 'Welcome',
                    'linkUrl' => '',
                    'imageUrl' => '',
                    'enabled' => '1',
                    'image' => UploadedFile::fake()->image('welcome.jpg'),
                ],
            ],
            'intervalSeconds' => 5,
            'autoplay' => '1',
            'showIndicators' => '1',
            'pauseOnTouch' => '1',
            'loop' => '1',
            'transition' => 'fade',
        ])->assertRedirect();

        $stored = json_decode(AppSetting::where('slug', 'home-banner-slideshow')->value('value'), true);
        $relative = substr($stored['slides'][0]['imageUrl'], strpos($stored['slides'][0]['imageUrl'], '/storage/') + strlen('/storage/'));
        Storage::disk('public')->assertExists($relative);

        $this->post('/_test/mobile-tool/banner-slides/reset')->assertRedirect(route('admin.mobile-tools.banner-slides'));

        $this->assertNull(AppSetting::where('slug', 'home-banner-slideshow')->value('value'));
        Storage::disk('public')->assertMissing($relative);
    }

    public function test_quotes_page_renders_and_crud_roundtrip(): void
    {
        $this->get('/_test/mobile-tool/quotes')
            ->assertOk()
            ->assertSee('Add a quote')
            ->assertSee('No quotes yet');

        $this->post('/_test/mobile-tool/quotes', [
            'text' => 'Small steps every day.',
            'author' => 'Anonymous',
            'enabled' => '1',
        ])->assertRedirect(route('admin.mobile-tools.quotes'));

        $stored = json_decode(AppSetting::where('slug', 'mobile-quotes')->value('value'), true);
        $this->assertCount(1, $stored['quotes']);
        $this->assertTrue($stored['quotes'][0]['enabled']);
        $quoteId = $stored['quotes'][0]['id'];
        $this->assertNotEmpty($quoteId);

        $this->get('/_test/mobile-tool/quotes')
            ->assertOk()
            ->assertSee('Small steps every day.')
            ->assertSee('Anonymous');

        $this->post('/_test/mobile-tool/quotes/'.$quoteId.'/toggle')->assertRedirect();
        $stored = json_decode(AppSetting::where('slug', 'mobile-quotes')->value('value'), true);
        $this->assertFalse($stored['quotes'][0]['enabled']);

        $this->post('/_test/mobile-tool/quotes/'.$quoteId.'/update', [
            'text' => 'Updated quote text.',
            'author' => 'Editor',
            'enabled' => '1',
        ])->assertRedirect(route('admin.mobile-tools.quotes'));
        $stored = json_decode(AppSetting::where('slug', 'mobile-quotes')->value('value'), true);
        $this->assertSame('Updated quote text.', $stored['quotes'][0]['text']);
        $this->assertSame('Editor', $stored['quotes'][0]['author']);
        $this->assertTrue($stored['quotes'][0]['enabled']);

        $this->delete('/_test/mobile-tool/quotes/'.$quoteId)->assertRedirect(route('admin.mobile-tools.quotes'));
        $stored = json_decode(AppSetting::where('slug', 'mobile-quotes')->value('value'), true);
        $this->assertSame([], $stored['quotes']);
    }

    public function test_quote_with_full_banner_customization_roundtrip(): void
    {
        $customStyle = [
            'fontFamily' => 'Battambang',
            'fontSize' => 18,
            'fontWeight' => '800',
            'fontStyle' => 'italic',
            'textAlign' => 'center',
            'color' => '#FFD54F',
            'opacity' => 0.95,
            'rotation' => -7,
            'posX' => 62.5,
            'posY' => 35.0,
            'strokeWidth' => 1.5,
            'strokeColor' => '#000000',
            'gradientEnabled' => true,
            'gradientStart' => '#FFE082',
            'gradientEnd' => '#FF8F00',
            'gradientAngle' => 45,
            'shadowBlur' => 8,
            'shadowColor' => 'rgba(0,0,0,0.8)',
            'bannerImage' => '/assets/images/angkor_wat_banner.jpg',
            'bannerOverlay' => 0.4,
        ];

        $this->post('/_test/mobile-tool/quotes', [
            'text' => 'Styled Motivational Quote',
            'author' => 'Leader',
            'enabled' => '1',
            'style' => json_encode($customStyle),
        ])->assertRedirect(route('admin.mobile-tools.quotes'));

        $stored = json_decode(AppSetting::where('slug', 'mobile-quotes')->value('value'), true);
        $this->assertCount(1, $stored['quotes']);
        $quote = $stored['quotes'][0];
        $this->assertSame('Styled Motivational Quote', $quote['text']);
        $this->assertSame('Leader', $quote['author']);
        $this->assertNotNull($quote['style']);
        $this->assertSame('Battambang', $quote['style']['fontFamily']);
        $this->assertEquals(18, $quote['style']['fontSize']);
        $this->assertEquals(-7, $quote['style']['rotation']);
        $this->assertEquals(62.5, $quote['style']['posX']);
        $this->assertEquals(35.0, $quote['style']['posY']);
        $this->assertTrue($quote['style']['gradientEnabled']);
        $this->assertEquals(1.5, $quote['style']['strokeWidth']);

        $res = $this->getJson('/_test/mobile-tool/api-quotes');
        $res->assertOk();
        $res->assertJsonPath('data.quotes.0.text', 'Styled Motivational Quote');
        $res->assertJsonPath('data.quotes.0.style.fontFamily', 'Battambang');
        $res->assertJsonPath('data.quotes.0.style.rotation', -7);
        $res->assertJsonPath('data.quotes.0.style.gradientEnabled', true);
    }

    public function test_quote_api_returns_only_enabled_quotes(): void
    {
        AppSetting::create([
            'name' => 'Mobile Quotes',
            'slug' => 'mobile-quotes',
            'value' => json_encode(['quotes' => [
                ['id' => 'q1', 'text' => 'First enabled quote', 'author' => 'Author One', 'enabled' => true],
                ['id' => 'q2', 'text' => 'Hidden quote', 'author' => null, 'enabled' => false],
                ['id' => 'q3', 'text' => 'Second enabled quote', 'enabled' => true],
            ]]),
            'status' => 1,
        ]);

        $rule = config('mobile_permissions.functions.MobileQuoteApiController@index');
        $this->assertSame(['attendance', 'quote.view'], $rule);

        $this->getJson('/_test/mobile-tool/api-quotes')
            ->assertOk()
            ->assertJsonCount(2, 'data.quotes')
            ->assertJsonPath('data.quotes.0.id', 'q1')
            ->assertJsonPath('data.quotes.0.author', 'Author One')
            ->assertJsonPath('data.quotes.1.id', 'q3')
            ->assertJsonPath('data.quotes.1.author', null)
            ->assertJsonMissing(['text' => 'Hidden quote']);
    }

    public function test_pages_work_behind_real_admin_auth_and_permission_middleware(): void
    {
        // Mirror a production admin session: admin guard only, no employee login.
        Auth::forgetGuards();
        Auth::shouldUse('web');
        $admin = new Admin;
        $admin->id = 99;
        Auth::guard('admin')->setUser($admin);
        $this->assertNull(auth()->user());
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), '_test/mobile-tool/')) {
                $route->middleware(['admin.auth', 'permission']);
            }
        }

        Storage::fake('public');

        $this->get('/_test/mobile-tool/banner-slides')
            ->assertOk()
            ->assertSee('Banner Slides');
        $this->get('/_test/mobile-tool/quotes')
            ->assertOk()
            ->assertSee('Add a quote');

        $this->post('/_test/mobile-tool/banner-slides', [
            'slides' => [[
                'id' => 'slide_e2e',
                'title' => 'End to end',
                'linkUrl' => '',
                'imageUrl' => '',
                'enabled' => '1',
                'image' => UploadedFile::fake()->image('e2e.jpg'),
            ]],
            'intervalSeconds' => 4,
            'autoplay' => '1',
            'showIndicators' => '1',
            'pauseOnTouch' => '1',
            'loop' => '1',
            'transition' => 'fade',
        ])->assertRedirect(route('admin.mobile-tools.banner-slides'));
        $this->assertNotNull(AppSetting::where('slug', 'home-banner-slideshow')->value('value'));

        $this->post('/_test/mobile-tool/quotes', [
            'text' => 'Middleware verified quote.',
            'enabled' => '1',
        ])->assertRedirect(route('admin.mobile-tools.quotes'));

        $this->getJson('/_test/mobile-tool/api-quotes')
            ->assertOk()
            ->assertJsonPath('data.quotes.0.text', 'Middleware verified quote.');
    }
}
