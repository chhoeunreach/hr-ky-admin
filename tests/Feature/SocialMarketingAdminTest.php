<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\SocialMarketingController;
use App\Models\Admin;
use App\Models\SocialReward;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Tests\TestCase;

class SocialMarketingAdminTest extends TestCase
{
    private Admin $admin;
    private string $viewDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['hrs_addon_reward_audit', 'hrs_addon_social_rewards', 'departments', 'branches', 'users', 'admins'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('password')->default('');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('dept_name');
        });
        Schema::create('hrs_addon_social_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('existing_employee_id');
            $table->date('log_date');
            $table->text('fb_post_url');
            $table->text('fb_story_url');
            $table->text('tiktok_url');
            $table->string('fb_post_photo')->nullable();
            $table->string('fb_story_photo')->nullable();
            $table->string('tiktok_photo')->nullable();
            $table->integer('reward_points')->default(1);
            $table->boolean('is_locked')->default(true);
            $table->timestamps();
            $table->unique(['existing_employee_id', 'log_date']);
        });
        Schema::create('hrs_addon_reward_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_record_id');
            $table->unsignedBigInteger('admin_id');
            $table->text('reason');
            $table->timestamps();
        });

        $this->admin = Admin::create([
            'name' => 'Marketing Admin',
            'username' => 'marketing',
            'email' => 'marketing@example.test',
            'password' => bcrypt('secret'),
            'is_active' => true,
        ]);
        User::create([
            'name' => 'Alice Employee',
            'email' => 'alice@example.test',
            'username' => 'alice',
            'employee_code' => 'EMP-001',
            'password' => bcrypt('secret'),
            'company_id' => 1,
            'is_active' => true,
        ]);
        User::create([
            'name' => 'Bob Employee',
            'email' => 'bob@example.test',
            'username' => 'bob',
            'employee_code' => 'EMP-002',
            'password' => bcrypt('secret'),
            'company_id' => 1,
            'is_active' => true,
        ]);

        Gate::before(fn () => true);
        Auth::guard('admin')->login($this->admin);

        $this->viewDirectory = sys_get_temp_dir().'/social-marketing-view-'.bin2hex(random_bytes(6));
        mkdir($this->viewDirectory.'/layouts', 0777, true);
        file_put_contents($this->viewDirectory.'/layouts/master.blade.php', "@yield('title') @yield('button') @yield('main-content')");
        view()->getFinder()->prependLocation($this->viewDirectory);
        view()->getFinder()->flush();

        Route::middleware(SubstituteBindings::class)->group(function () {
            Route::get('/_test/social-marketing', [SocialMarketingController::class, 'index']);
            Route::post('/_test/social-marketing', [SocialMarketingController::class, 'store']);
            Route::get('/_test/social-marketing/{socialReward}', [SocialMarketingController::class, 'show']);
            Route::put('/_test/social-marketing/{socialReward}', [SocialMarketingController::class, 'update']);
            Route::delete('/_test/social-marketing/{socialReward}', [SocialMarketingController::class, 'destroy']);
        });
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

    public function test_admin_list_renders_summary_and_filters_by_employee_and_status(): void
    {
        $alice = User::where('username', 'alice')->firstOrFail();
        SocialReward::create([
            'existing_employee_id' => $alice->id,
            'log_date' => now()->toDateString(),
            'fb_post_url' => 'https://facebook.com/post',
            'fb_story_url' => 'https://facebook.com/story',
            'tiktok_url' => 'https://tiktok.com/video',
            'reward_points' => 1,
            'is_locked' => true,
        ]);

        $this->get('/_test/social-marketing?search=Alice&status=locked')
            ->assertOk()
            ->assertSee('Alice Employee')
            ->assertSee('Social Media Marketing')
            ->assertSee('Add Submission');
    }

    public function test_admin_can_create_override_audit_and_delete_with_photo_cleanup(): void
    {
        Storage::fake('public');
        $alice = User::where('username', 'alice')->firstOrFail();

        $this->post('/_test/social-marketing', [
            'employee_id' => $alice->id,
            'log_date' => now()->toDateString(),
            'fb_post_url' => 'https://facebook.com/post',
            'fb_story_url' => 'https://facebook.com/story',
            'tiktok_url' => 'https://tiktok.com/video',
            'fb_post_photo' => UploadedFile::fake()->image('post.jpg'),
            'fb_story_photo' => UploadedFile::fake()->image('story.jpg'),
            'tiktok_photo' => UploadedFile::fake()->image('tiktok.jpg'),
        ])->assertRedirect();

        $record = SocialReward::firstOrFail();
        $oldPhoto = $record->fb_post_photo;
        $this->assertNotNull($oldPhoto);
        Storage::disk('public')->assertExists($oldPhoto);

        $this->put('/_test/social-marketing/'.$record->id, [
            'fb_post_url' => 'https://facebook.com/new-post',
            'fb_story_url' => 'https://facebook.com/new-story',
            'tiktok_url' => 'https://tiktok.com/new-video',
            'fb_post_photo' => UploadedFile::fake()->image('replacement.jpg'),
            'reason' => 'Corrected the submitted link',
            'unlock' => 1,
        ])->assertRedirect();

        $record->refresh();
        $this->assertFalse($record->is_locked);
        $this->assertSame('https://facebook.com/new-post', $record->fb_post_url);
        $this->assertNotSame($oldPhoto, $record->fb_post_photo);
        Storage::disk('public')->assertMissing($oldPhoto);
        $this->assertDatabaseHas('hrs_addon_reward_audit', [
            'target_record_id' => $record->id,
            'admin_id' => $this->admin->id,
            'reason' => 'Corrected the submitted link',
        ]);

        $remainingPhotos = array_filter([$record->fb_post_photo, $record->fb_story_photo, $record->tiktok_photo]);
        $this->delete('/_test/social-marketing/'.$record->id)->assertRedirect();
        $this->assertDatabaseMissing('hrs_addon_social_rewards', ['id' => $record->id]);
        foreach ($remainingPhotos as $photo) {
            Storage::disk('public')->assertMissing($photo);
        }
    }

    public function test_admin_cannot_create_duplicate_or_invalid_records(): void
    {
        $alice = User::where('username', 'alice')->firstOrFail();
        $payload = [
            'employee_id' => $alice->id,
            'log_date' => now()->toDateString(),
            'fb_post_url' => 'https://facebook.com/post',
            'fb_story_url' => 'https://facebook.com/story',
            'tiktok_url' => 'https://tiktok.com/video',
        ];

        $this->post('/_test/social-marketing', $payload)->assertRedirect();
        $this->assertSame(1, SocialReward::count());

        $this->post('/_test/social-marketing', $payload)->assertSessionHasErrors('log_date');

        $bob = User::where('username', 'bob')->firstOrFail();
        $this->post('/_test/social-marketing', array_merge($payload, [
            'employee_id' => $bob->id,
            'fb_post_url' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors('fb_post_url');
    }
}
