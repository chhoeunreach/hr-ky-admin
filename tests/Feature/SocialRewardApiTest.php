<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SocialRewardApiTest extends TestCase
{
    protected function setUp(): void
    {
        $this->prepareTestDatabaseFile();
        parent::setUp();

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--force' => true,
        ]);

        $this->createSocialRewardTables();
    }

    public function test_mobile_can_submit_social_reward_day_log_with_photos(): void
    {
        Storage::fake('public');
        $user = $this->makeUser();
        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/hr-ky-admin/social-rewards/submit', [
            'employee_id' => $user->id,
            'existing_employee_id' => $user->id,
            'fb_post_url' => 'https://facebook.com/share/p/abc',
            'fb_story_url' => 'https://facebook.com/stories/abc',
            'tiktok_url' => 'https://tiktok.com/@user/video/123',
            'fb_post_photo' => UploadedFile::fake()->image('fb-post.jpg'),
            'fb_story_photo' => UploadedFile::fake()->image('fb-story.jpg'),
            'tiktok_photo' => UploadedFile::fake()->image('tiktok.jpg'),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.existing_employee_id', $user->id)
            ->assertJsonPath('data.reward_points', 1);

        $this->assertDatabaseHas('hrs_addon_social_rewards', [
            'existing_employee_id' => $user->id,
            'fb_post_url' => 'https://facebook.com/share/p/abc',
        ]);
        $this->assertNotEmpty($response->json('data.fb_post_photo'));
        $this->assertNotEmpty($response->json('data.fb_post_photo_url'));
    }

    public function test_today_and_list_endpoints_return_flutter_compatible_payloads(): void
    {
        $user = $this->makeUser();
        Passport::actingAs($user);

        DB::table('hrs_addon_social_rewards')->insert([
            'existing_employee_id' => $user->id,
            'log_date' => now()->toDateString(),
            'fb_post_url' => 'https://facebook.com/post',
            'fb_post_photo' => 'social-rewards/fb.jpg',
            'fb_story_url' => 'https://facebook.com/story',
            'fb_story_photo' => 'social-rewards/story.jpg',
            'tiktok_url' => 'https://tiktok.com/video',
            'tiktok_photo' => 'social-rewards/tiktok.jpg',
            'reward_points' => 1,
            'is_locked' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/v1/hr-ky-admin/social-rewards/today?employee_id={$user->id}")
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.record.existing_employee_id', $user->id)
            ->assertJsonPath('data.record.fb_post_url', 'https://facebook.com/post');

        $this->getJson("/api/v1/hr-ky-admin/social-rewards?employee_id={$user->id}")
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.existing_employee_id', $user->id);
    }

    public function test_legacy_post_endpoint_creates_url_only_social_reward(): void
    {
        $user = $this->makeUser();
        Passport::actingAs($user);

        $this->postJson('/api/hr-ky-admin/social-rewards', [
            'existing_employee_id' => $user->id,
            'log_date' => now()->toDateString(),
            'fb_post_url' => 'https://facebook.com/post',
            'fb_story_url' => 'https://facebook.com/story',
            'tiktok_url' => 'https://tiktok.com/video',
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.existing_employee_id', $user->id);
    }

    public function test_override_updates_social_reward_when_api_user_has_no_matching_admin(): void
    {
        $user = $this->makeUser();
        Passport::actingAs($user);

        $rewardId = DB::table('hrs_addon_social_rewards')->insertGetId([
            'existing_employee_id' => $user->id,
            'log_date' => now()->toDateString(),
            'fb_post_url' => 'https://facebook.com/old-post',
            'fb_story_url' => 'https://facebook.com/old-story',
            'tiktok_url' => 'https://tiktok.com/old-video',
            'reward_points' => 1,
            'is_locked' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/hr-ky-admin/social-rewards/override', [
            'target_record_id' => $rewardId,
            'fb_post_url' => 'https://facebook.com/new-post',
            'fb_story_url' => 'https://facebook.com/new-story',
            'tiktok_url' => 'https://tiktok.com/new-video',
            'reason' => 'Correct submitted links',
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.fb_post_url', 'https://facebook.com/new-post')
            ->assertJsonPath('data.is_locked', false);

        $this->assertDatabaseHas('hrs_addon_social_rewards', [
            'id' => $rewardId,
            'fb_post_url' => 'https://facebook.com/new-post',
            'is_locked' => false,
        ]);
    }

    private function prepareTestDatabaseFile(): void
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
    }

    private function makeUser(): User
    {
        $role = Role::create([
            'name' => 'Employee',
            'slug' => uniqid('employee_', true),
            'is_active' => 1,
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'username' => uniqid('social_', true),
        ]);
    }

    private function createSocialRewardTables(): void
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username');
                $table->string('email')->unique();
                $table->string('password');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::create('hrs_addon_social_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('existing_employee_id');
            $table->date('log_date');
            $table->text('fb_post_url');
            $table->string('fb_post_photo')->nullable();
            $table->text('fb_story_url');
            $table->string('fb_story_photo')->nullable();
            $table->text('tiktok_url');
            $table->string('tiktok_photo')->nullable();
            $table->integer('reward_points')->default(1);
            $table->boolean('is_locked')->default(true);
            $table->timestamps();
            $table->unique(['existing_employee_id', 'log_date'], 'emp_daily_unique');
        });

        Schema::create('hrs_addon_reward_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('target_record_id');
            $table->unsignedBigInteger('admin_id');
            $table->text('reason');
            $table->timestamps();
        });
    }
}
