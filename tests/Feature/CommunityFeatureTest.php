<?php
namespace Tests\Feature;

use App\Http\Controllers\Web\CommunityController;
use App\Models\FlashView;
use App\Models\SocialPost;
use App\Models\SocialStory;
use App\Models\User;
use App\Services\Community\CommunityMedia;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CommunityFeatureTest extends TestCase
{
    private array $abilities = [];
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
        config(['mobile_permissions' => require __DIR__.'/../../config/mobile_permissions.php']);
        Schema::create('departments', function (Blueprint $t) { $t->id(); $t->string('dept_name'); $t->integer('company_id')->nullable(); });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->softDeletes(); $t->string('name'); $t->string('avatar')->nullable(); $t->integer('company_id')->nullable(); $t->integer('role_id')->nullable(); $t->integer('branch_id')->nullable(); $t->integer('department_id')->nullable();
        });
        Schema::create('roles', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('slug'); });
        Schema::create('permission_group_types', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('slug'); });
        Schema::create('permission_groups', function (Blueprint $t) { $t->id(); $t->string('name'); $t->integer('group_type_id'); });
        Schema::create('permissions', function (Blueprint $t) { $t->id(); $t->string('name')->unique(); $t->string('permission_key')->unique(); $t->integer('permission_groups_id'); });
        Schema::create('permission_roles', function (Blueprint $t) { $t->integer('permission_id'); $t->integer('role_id'); });
        DB::table('roles')->insert(['id'=>2,'name'=>'Employee','slug'=>'employee']);
        DB::table('departments')->insert([['id'=>4,'dept_name'=>'Operations','company_id'=>1],['id'=>5,'dept_name'=>'Finance','company_id'=>1]]);
        DB::table('users')->insert([
            ['id'=>10,'name'=>'Author','company_id'=>1,'role_id'=>2],
            ['id'=>11,'name'=>'Colleague','company_id'=>1,'role_id'=>2],
            ['id'=>12,'name'=>'Other Company','company_id'=>2,'role_id'=>2],
        ]);
        (require __DIR__.'/../../database/migrations/2026_11_11_000002_create_flash_views_and_social_feed.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_11_000003_add_flash_view_targeting_and_alerts.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_11_000005_expand_social_feed_features.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_11_000006_add_social_polls.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_11_000007_add_social_post_preferences.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_11_000008_add_social_post_location.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_12_000001_create_social_stories.php')->up();
        (require __DIR__.'/../../database/migrations/2026_11_13_000001_create_social_story_reactions.php')->up();
        Gate::before(fn ($user,$ability) => in_array($ability,$this->abilities,true));
        Passport::actingAs(User::findOrFail(10));
        Route::middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)->group(function () {
        Route::post('/_test/community/flash',[CommunityController::class,'saveFlash']);
        Route::post('/_test/community/flash/{flashView}',[CommunityController::class,'saveFlash']);
        Route::delete('/_test/community/posts/{post}',[CommunityController::class,'deletePost']);
        });
    }
    private function grantFeed(): void
    {
        $this->abilities=['mobile.menu.social-feed','social_feed.view','social_feed.create','social_feed.edit','social_feed.delete',
            'social_feed.comment','social_feed.delete_comment','social_feed.react','social_feed.share'];
    }
    private function makePost(array $overrides=[]): SocialPost
    {
        return SocialPost::create($overrides+['user_id'=>10,'company_id'=>1,'body'=>'A team update','media_type'=>'text']);
    }

    private function grantStories(): void
    {
        $this->abilities = [
            'mobile.menu.social-feed',
            'social_feed.view',
            'social_feed.stories.view',
            'social_feed.stories.create',
            'social_feed.stories.delete',
        ];
    }

    public function test_stories_are_company_scoped_expire_after_24_hours_and_mark_seen(): void
    {
        $this->grantStories();
        $id = $this->postJson('/api/social-stories', [
            'media_type' => 'text',
            'caption' => 'Team celebration',
        ])->assertCreated()->json('data.id');
        $this->assertDatabaseHas('social_stories', ['id' => $id, 'company_id' => 1]);

        $this->getJson('/api/social-stories')
            ->assertOk()
            ->assertJsonPath('data.0.is_mine', true)
            ->assertJsonPath('data.0.stories.0.caption', 'Team celebration')
            ->assertJsonPath('data.0.stories.0.is_seen', false);
        $this->postJson('/api/social-stories/'.$id.'/view')
            ->assertOk()
            ->assertJsonPath('viewed', true);
        $this->getJson('/api/social-stories')
            ->assertJsonPath('data.0.stories.0.is_seen', true);

        SocialStory::findOrFail($id)->update(['expires_at' => now()->subMinute()]);
        $this->getJson('/api/social-stories')->assertJsonCount(0, 'data');
                Passport::actingAs(User::findOrFail(12));
        $this->getJson('/api/social-stories')->assertJsonCount(0, 'data');
    }

    public function test_story_reactions_follow_the_facebook_flow(): void
    {
        $this->grantStories();
        $id = $this->postJson('/api/social-stories', [
            'media_type' => 'text',
            'caption' => 'Team celebration',
        ])->assertCreated()->json('data.id');
        $this->assertDatabaseHas('social_stories', ['id' => $id, 'company_id' => 1]);

        Passport::actingAs(User::findOrFail(11));
        $this->getJson('/api/social-stories')
            ->assertOk()
            ->assertJsonPath('data.0.stories.0.can_react', true)
            ->assertJsonPath('data.0.stories.0.my_reaction', null)
            ->assertJsonPath('data.0.stories.0.reactions_count', 0);

        $this->putJson('/api/social-stories/'.$id.'/reaction', ['reaction' => 'love'])
            ->assertOk()
            ->assertJsonPath('data.my_reaction', 'love')
            ->assertJsonPath('data.reactions.love', 1)
            ->assertJsonPath('data.reactions_count', 1);
        $this->assertDatabaseHas('social_story_reactions', [
            'social_story_id' => $id,
            'user_id' => 11,
            'reaction' => 'love',
        ]);

        $this->putJson('/api/social-stories/'.$id.'/reaction', ['reaction' => 'haha'])
            ->assertOk()
            ->assertJsonPath('data.my_reaction', 'haha')
            ->assertJsonPath('data.reactions.love', null)
            ->assertJsonPath('data.reactions.haha', 1)
            ->assertJsonPath('data.reactions_count', 1);

        Passport::actingAs(User::findOrFail(10));
        $this->getJson('/api/social-stories/'.$id.'/reactions')
            ->assertOk()
            ->assertJsonPath('data.0.reaction', 'haha')
            ->assertJsonPath('data.0.user.id', 11);
        $this->putJson('/api/social-stories/'.$id.'/reaction', ['reaction' => 'love'])
            ->assertForbidden();

        Passport::actingAs(User::findOrFail(11));
        $this->putJson('/api/social-stories/'.$id.'/reaction', ['reaction' => null])
            ->assertOk()
            ->assertJsonPath('data.my_reaction', null)
            ->assertJsonPath('data.reactions_count', 0);
        $this->assertDatabaseCount('social_story_reactions', 0);

        Passport::actingAs(User::findOrFail(11));
        $this->putJson('/api/social-stories/'.$id.'/reaction', ['reaction' => 'invalid'])
            ->assertUnprocessable();
    }


    public function test_direct_publishing_ownership_and_company_isolation(): void
    {
        $this->grantFeed();
        $id=$this->postJson('/api/social-feed',['body'=>'Hello team','media_type'=>'text'])->assertCreated()->json('data.id');
        $this->postJson('/api/social-feed/'.$id,['body'=>'Updated','media_type'=>'text'])->assertOk()->assertJsonPath('data.body','Updated');
        $this->makePost(['user_id'=>12,'company_id'=>2]);
        $this->getJson('/api/social-feed')->assertOk()->assertJsonCount(1,'data');
        Passport::actingAs(User::findOrFail(11));
        $this->postJson('/api/social-feed/'.$id,['body'=>'Hijack','media_type'=>'text'])->assertForbidden();
        $this->deleteJson('/api/social-feed/'.$id)->assertForbidden();
        Passport::actingAs(User::findOrFail(12));
        $this->getJson('/api/social-feed/'.$id)->assertNotFound();
        $this->postJson('/api/social-feed/'.$id.'/share')->assertNotFound();
    }
    public function test_read_only_access_cannot_mutate(): void
    {
        $post=$this->makePost(); $this->abilities=['mobile.menu.social-feed','social_feed.view'];
        $this->getJson('/api/social-feed')->assertOk();
        $this->postJson('/api/social-feed',['body'=>'Denied','media_type'=>'text'])->assertForbidden();
        $this->postJson('/api/social-feed/'.$post->id.'/comments',['body'=>'Denied'])->assertForbidden();
        $this->putJson('/api/social-feed/'.$post->id.'/reaction',['reaction'=>'love'])->assertForbidden();
        $this->postJson('/api/social-feed/'.$post->id.'/share')->assertForbidden();
    }
    public function test_comments_require_owner_and_correct_parent(): void
    {
        $this->grantFeed(); $post=$this->makePost(); $other=$this->makePost();
        $id=$this->postJson('/api/social-feed/'.$post->id.'/comments',['body'=>'Great work'])->assertCreated()->json('data.id');
        $this->getJson('/api/social-feed/'.$post->id.'/comments')->assertJsonPath('data.0.body','Great work');
        $this->deleteJson('/api/social-feed/'.$other->id.'/comments/'.$id)->assertNotFound();
        Passport::actingAs(User::findOrFail(11));
        $this->deleteJson('/api/social-feed/'.$post->id.'/comments/'.$id)->assertForbidden();
        Passport::actingAs(User::findOrFail(10));
        $this->deleteJson('/api/social-feed/'.$post->id.'/comments/'.$id)->assertOk();
        $this->assertSame(0,DB::table('social_comments')->count());
    }
    public function test_reactions_are_idempotent_changeable_and_removable(): void
    {
        $this->grantFeed(); $path='/api/social-feed/'.$this->makePost()->id.'/reaction';
        $this->putJson($path,['reaction'=>'like'])->assertOk()->assertJsonPath('data.reactions_count',1);
        $this->putJson($path,['reaction'=>'like'])->assertOk()->assertJsonPath('data.reactions_count',1);
        $this->putJson($path,['reaction'=>'love'])->assertOk()->assertJsonPath('data.my_reaction','love');
        $this->putJson($path,['reaction'=>'invalid'])->assertUnprocessable();
        $this->putJson($path,['reaction'=>null])->assertOk()->assertJsonPath('data.reactions_count',0);
    }
    public function test_post_preferences_follow_notifications_and_hide_from_feed(): void
    {
        $this->grantFeed();
        $this->abilities[]='social_feed.follow';
        $this->abilities[]='social_feed.hide';
        $post=$this->makePost();
        $this->postJson('/api/social-feed/'.$post->id.'/notifications')
            ->assertOk()->assertJsonPath('notifications_enabled',true);
        $this->postJson('/api/social-feed/'.$post->id.'/notifications')
            ->assertOk()->assertJsonPath('notifications_enabled',false);
        $this->postJson('/api/social-feed/'.$post->id.'/hide')
            ->assertOk()->assertJsonPath('hidden',true);
        $this->getJson('/api/social-feed')->assertOk()->assertJsonCount(0,'data');
        $this->assertDatabaseHas('social_post_preferences', [
            'social_post_id'=>$post->id, 'user_id'=>10,
        ]);
    }
    public function test_sharing_and_removed_original(): void
    {
        $this->grantFeed(); $post=$this->makePost();
        $id=$this->postJson('/api/social-feed/'.$post->id.'/share',['body'=>'Read this'])->assertCreated()
            ->assertJsonPath('data.original.body','A team update')->json('data.id');
        $this->postJson('/api/social-feed/'.$id,['body'=>'New caption'])->assertOk();
        $this->assertSame('A team update',$post->fresh()->body);
        $this->deleteJson('/api/social-feed/'.$post->id)->assertOk();
        $this->getJson('/api/social-feed/'.$id)->assertOk()->assertJsonPath('data.original',null);
        $this->postJson('/api/social-feed/'.$id.'/share')->assertStatus(410);
    }
    public function test_flash_schedule_audience_revision_and_receipts(): void
    {
        $this->abilities=['mobile.menu.flash-view','flash_view.view'];
        $flash=FlashView::create(['title'=>'Welcome','body'=>'Hello','is_active'=>true,'role_ids'=>[2],'display_policy'=>'once','priority'=>10]);
        FlashView::create(['title'=>'Draft','is_active'=>false]);
        FlashView::create(['title'=>'Future','is_active'=>true,'starts_at'=>now()->addDay()]);
        FlashView::create(['title'=>'Expired','is_active'=>true,'ends_at'=>now()->subDay()]);
        FlashView::create(['title'=>'Wrong audience','is_active'=>true,'role_ids'=>[99]]);
        $this->getJson('/api/flash-views')->assertOk()->assertJsonCount(1,'data');
        $path='/api/flash-views/'.$flash->id.'/acknowledge';
        $this->postJson($path,['revision'=>1])->assertOk();
        $this->postJson($path,['revision'=>1])->assertOk();
        $this->assertSame(1,DB::table('flash_view_receipts')->count());
        $this->getJson('/api/flash-views')->assertJsonCount(0,'data');
        $flash->update(['revision'=>2]);
        $this->postJson($path,['revision'=>1])->assertStatus(409);
        $this->getJson('/api/flash-views')->assertJsonCount(1,'data');
        $flash->update(['display_policy'=>'session']);
        $this->postJson($path,['revision'=>2])->assertOk();
        $this->getJson('/api/flash-views')->assertJsonCount(1,'data');
    }

    public function test_flash_audience_matches_role_branch_and_department_filters(): void
    {
        $this->abilities=['mobile.menu.flash-view','flash_view.view'];
        DB::table('users')->where('id', 10)->update(['branch_id'=>3, 'department_id'=>4]);
        DB::table('users')->where('id', 11)->update(['branch_id'=>3, 'department_id'=>5]);
        DB::table('users')->where('id', 12)->update(['branch_id'=>9, 'department_id'=>4]);
        $flash=FlashView::create([
            'title'=>'Targeted', 'is_active'=>true, 'role_ids'=>[2],
            'branch_ids'=>[3], 'department_ids'=>[4],
        ]);

        $this->assertTrue($flash->fresh()->visibleTo(User::findOrFail(10)));
        $this->assertFalse($flash->fresh()->visibleTo(User::findOrFail(11)));
        $this->assertFalse($flash->fresh()->visibleTo(User::findOrFail(12)));
    }
    public function test_upload_and_untrusted_media_validation(): void
    {
        $this->grantFeed(); Storage::fake('public');
        $this->postJson('/api/social-feed',['media_type'=>'youtube','media_url'=>'https://youtube.com.evil.test/watch?v=abcdefghijk'])->assertUnprocessable();
        $this->postJson('/api/social-feed',['media_type'=>'link','media_url'=>'javascript:alert(1)'])->assertUnprocessable();
        $this->postJson('/api/social-feed',['media_type'=>'image','media_url'=>'https://example.com/a.jpg, https://www.youtube.com/watch?v=ScMzIvxBSi4'])->assertUnprocessable();
        $this->postJson('/api/social-feed',['media_type'=>'image','media_url'=>'https://example.com/a.jpg,https://example.com/b.jpg'])->assertUnprocessable();
        $this->postJson('/api/social-feed',['media_type'=>'text','body'=>'  '])->assertUnprocessable();
        $this->post('/api/social-feed',['media_type'=>'video','media'=>UploadedFile::fake()->image('image.png')],['Accept'=>'application/json'])->assertUnprocessable();
        $this->assertSame([],Storage::disk('public')->allFiles());
        $this->post('/api/social-feed',['media_type'=>'image','media'=>UploadedFile::fake()->image('image.png')],['Accept'=>'application/json'])->assertCreated();
        $this->assertCount(1,Storage::disk('public')->allFiles());
    }
    public function test_upload_replacement_removes_only_unreferenced_owned_files(): void
    {
        $this->grantFeed(); Storage::fake('public');
        $id=$this->post('/api/social-feed',['media_type'=>'image','media'=>UploadedFile::fake()->image('old.png')],['Accept'=>'application/json'])->assertCreated()->json('data.id');
        $old=Storage::disk('public')->allFiles()[0];
        $this->post('/api/social-feed/'.$id,['media_type'=>'image','media'=>UploadedFile::fake()->image('new.png')],['Accept'=>'application/json'])->assertOk();
        Storage::disk('public')->assertMissing($old);
        $this->assertCount(1,Storage::disk('public')->allFiles());
        $this->deleteJson('/api/social-feed/'.$id)->assertOk();
        $this->assertCount(0,Storage::disk('public')->allFiles());
    }
    public function test_post_and_comment_pagination_and_input_limits(): void
    {
        $this->grantFeed();
        for ($i=0;$i<23;$i++) $this->makePost(['body'=>'Post '.$i]);
        $first=$this->getJson('/api/social-feed')->assertJsonCount(12,'data')->assertJsonPath('total',23)->assertJsonPath('has_more',true)->json();
        $this->getJson('/api/social-feed?page=2')->assertJsonCount(11,'data');
        $this->assertNotEmpty($first['next_cursor']);
        $second=$this->getJson('/api/social-feed?per_page=12&cursor='.urlencode($first['next_cursor']))->assertOk()->assertJsonCount(11,'data')->assertJsonPath('has_more',false)->json();
        $this->assertEmpty(collect($first['data'])->pluck('id')->intersect(collect($second['data'])->pluck('id')));
        $post=SocialPost::first();
        $this->postJson('/api/social-feed/'.$post->id.'/comments',['body'=>str_repeat('x',2001)])->assertUnprocessable();
        $this->postJson('/api/social-feed',['body'=>str_repeat('x',10001),'media_type'=>'text'])->assertUnprocessable();
        $this->post('/api/social-feed',['media_type'=>'image','media'=>UploadedFile::fake()->create('large.jpg',10241,'image/jpeg')],['Accept'=>'application/json'])->assertUnprocessable();
    }
    public function test_missing_company_cannot_read_or_publish_into_a_shared_null_scope(): void
    {
        $this->grantFeed();
        DB::table('users')->where('id',10)->update(['company_id'=>null]);
        Passport::actingAs(User::findOrFail(10));
        $this->getJson('/api/social-feed')->assertForbidden();
        $this->postJson('/api/social-feed',['body'=>'No company','media_type'=>'text'])->assertForbidden();
    }

    public function test_end_only_schedule_and_cleanup_dry_run(): void
    {
        $this->abilities=['flash_view.manage']; Storage::fake('public');
        $this->post('/_test/community/flash',['title'=>'Expires tomorrow','body'=>'Hello','media_type'=>'text',
            'display_policy'=>'once','priority'=>0,'ends_at'=>now()->addDay()->format('Y-m-d H:i:s')])->assertRedirect();
        $this->assertSame(1,FlashView::count());
        Storage::disk('public')->put('community/orphan.jpg','orphan');
        Storage::disk('public')->put('community/retained.jpg','retained');
        touch(Storage::disk('public')->path('community/orphan.jpg'),now()->subDays(2)->timestamp);
        touch(Storage::disk('public')->path('community/retained.jpg'),now()->subDays(2)->timestamp);
        $this->makePost(['media_type'=>'image','media_url'=>'https://backup.example.test/storage/community/retained.jpg']);
        $this->artisan('community:prune-media')->assertSuccessful();
        Storage::disk('public')->assertExists('community/orphan.jpg');
        $this->artisan('community:prune-media',['--delete'=>true])->assertSuccessful();
        Storage::disk('public')->assertMissing('community/orphan.jpg');
        Storage::disk('public')->assertExists('community/retained.jpg');
    }

    public function test_flash_directory_filters_and_page_sizes_include_all_matching_export_rows(): void
    {
        $this->abilities = ['flash_view.manage'];
        Schema::create('branches', function (Blueprint $t) { $t->id(); $t->string('name'); });
        $controller = app(CommunityController::class);
        for ($i = 0; $i < 31; $i++) {
            FlashView::create(['title' => 'Team update '.$i, 'body' => 'Office news',
                'media_type' => $i % 2 ? 'image' : 'text', 'display_policy' => $i % 2 ? 'session' : 'once',
                'is_active' => true, 'priority' => $i]);
        }
        FlashView::create(['title' => 'Draft', 'is_active' => false, 'media_type' => 'text']);
        FlashView::create(['title' => 'Scheduled', 'is_active' => true, 'starts_at' => now()->addDay()]);
        FlashView::create(['title' => 'Expired', 'is_active' => true, 'ends_at' => now()->subDay()]);
        $get = fn ($params = []) => $controller->flashes(\Illuminate\Http\Request::create('/_test/flashes', 'GET', $params))->getData()['items'];
        $this->assertCount(25, $get()->items());
        $this->assertCount(34, $get(['per_page' => 'all'])->items());
        $this->assertSame(50, $get(['per_page' => '50'])->perPage());
        $this->assertSame(25, $get(['per_page' => 'invalid'])->perPage());
        $this->assertSame(31, $get(['status' => 'live'])->total());
        foreach (['draft', 'scheduled', 'expired'] as $status) {
            $this->assertSame(1, $get(['status' => $status])->total());
        }
        $filtered = $get(['status' => 'live', 'q' => 'Office', 'media_type' => 'text', 'display_policy' => 'once', 'per_page' => 'all']);
        $this->assertCount(16, $filtered->items());
        $this->assertSame('Team update 30', $filtered->first()->title);
        $this->assertSame(0, $get(['status' => 'live', 'media_type' => 'image', 'display_policy' => 'once'])->total());
        $this->assertSame(34, $get(['status' => 'invalid', 'media_type' => 'invalid', 'display_policy' => 'invalid'])->total());
        $this->abilities = [];
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $get();
    }

    public function test_admin_templates_render_safe_form_preview_and_moderation_content(): void
    {
        $this->abilities=['flash_view.manage','social_feed.moderate'];
        $directory = sys_get_temp_dir().'/community-view-'.bin2hex(random_bytes(6));
        mkdir($directory.'/layouts',0777,true);
        file_put_contents($directory.'/layouts/master.blade.php', "@yield('title') @yield('button') @yield('main-content')");
        view()->getFinder()->prependLocation($directory);
        view()->getFinder()->flush();
        $errors = new \Illuminate\Support\ViewErrorBag;
        try {
            $item = FlashView::create(['title'=>'<script>alert(1)</script>','body'=>'Plain message']);
            $html = view('admin.community.flash-form',['item'=>$item,'roles'=>\App\Models\Role::all(),
                'types'=>CommunityMedia::TYPES,'errors'=>$errors])->render();
            $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;',$html);
            $this->assertStringContainsString('name="role_ids[]"',$html);
            $this->assertStringContainsString('name="show_again"',$html);
            $html = view('admin.community.flashes',['items'=>FlashView::paginate(20),'errors'=>$errors])->render();
            $this->assertStringContainsString('Flash View popups',$html);
            $this->assertStringContainsString('id="flashFilterForm"', $html);
            $this->assertStringContainsString('data-directory-table="flashTable"', $html);
            $this->assertStringContainsString('data-directory-export="excel"', $html);
            $this->assertStringContainsString('data-directory-columns', $html);
            $this->assertStringContainsString('name="media_type"', $html);
            $this->assertStringContainsString('name="display_policy"', $html);
            $this->assertStringContainsString('id="flash-list-block"', $html);
            $post = $this->makePost();
            $post->comments()->create(['user_id'=>10,'body'=>'A visible comment']);
            $html = view('admin.community.feed',['posts'=>SocialPost::with('author')->withCount(['comments','reactions','shares'])->paginate(20),'errors'=>$errors])->render();
            $this->assertStringContainsString('Review comments',$html);
            $html = view('admin.community.comments',['post'=>$post,'comments'=>$post->comments()->with('author')->paginate(30),'errors'=>$errors])->render();
            $this->assertStringContainsString('A visible comment',$html);
        } finally {
            unlink($directory.'/layouts/master.blade.php'); rmdir($directory.'/layouts'); rmdir($directory);
        }
    }

    public function test_flash_form_shows_field_errors_and_correct_examples(): void
    {
        $errors = new \Illuminate\Support\ViewErrorBag;
        $errors->put('default', new \Illuminate\Support\MessageBag([
            'title' => ['The title field is required.'],
            'media_url' => ['Paste a valid YouTube video URL or iframe.'],
            'ends_at' => ['The end date must be after the start date.'],
        ]));
        $directory = sys_get_temp_dir().'/community-error-view-'.bin2hex(random_bytes(6));
        mkdir($directory.'/layouts', 0777, true);
        file_put_contents($directory.'/layouts/master.blade.php', '@yield("main-content")');
        view()->getFinder()->prependLocation($directory);
        view()->getFinder()->flush();
        try {
            $html = view('admin.community.flash-form', [
                'item' => new FlashView,
                'roles' => \App\Models\Role::all(),
                'types' => CommunityMedia::TYPES,
                'errors' => $errors,
            ])->render();

            $this->assertMatchesRegularExpression('/<input\\b(?=[^>]*\\bid="title")(?=[^>]*\\bis-invalid)[^>]*>/', $html);
            $this->assertStringContainsString('id="title-error"', $html);
            $this->assertStringContainsString('The title field is required.', $html);
            $this->assertMatchesRegularExpression('/<textarea\\b(?=[^>]*\\bid="media_url")(?=[^>]*\\bis-invalid)[^>]*>/', $html);
            $this->assertStringContainsString('id="media-url-error"', $html);
            $this->assertStringContainsString('Paste a valid YouTube video URL or iframe.', $html);
            $this->assertStringContainsString('id="ends-error"', $html);
            $this->assertStringContainsString('https://www.youtube.com/watch?v=ScMzIvxBSi4', $html);
            $this->assertStringContainsString('Review the red messages below', $html);
        } finally {
            unlink($directory.'/layouts/master.blade.php');
            rmdir($directory.'/layouts');
            rmdir($directory);
        }
    }

    public function test_mobile_permission_and_community_migrations_work_together_and_roll_back(): void
    {
        // Existing installs may have registered mobile menu/function abilities first.
        $permissions = require __DIR__.'/../../database/migrations/2026_11_11_000001_add_mobile_menu_function_permissions.php';
        $community = require __DIR__.'/../../database/migrations/2026_11_11_000002_create_flash_views_and_social_feed.php';
        $expanded = require __DIR__.'/../../database/migrations/2026_11_11_000005_expand_social_feed_features.php';
        $location = require __DIR__.'/../../database/migrations/2026_11_11_000008_add_social_post_location.php';
        $location->down();
        $expanded->down();
        $community->down();
        $permissions->up();
        $community->up();
        $expanded->up();
        $location->up();
        foreach (['flash_views','flash_view_receipts','social_posts','social_comments','social_reactions'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
        foreach (['flash_view.manage','flash_view.view','social_feed.moderate','social_feed.create',
            'social_feed.comment','social_feed.react','social_feed.share','mobile.menu.social-feed','mobile.menu.flash-view'] as $ability) {
            $this->assertSame(1, DB::table('permissions')->where('permission_key',$ability)->count());
        }
        $this->assertSame(0, DB::table('permission_roles')->count());
        $expanded->down();
        $community->down();
        $this->assertFalse(Schema::hasTable('social_posts'));
        $this->assertTrue(DB::table('permissions')->where('permission_key','view_profile')->exists());
        $permissions->down();
        $this->assertFalse(DB::table('permissions')->where('permission_key','mobile.menu.profile')->exists());
    }

    public function test_provider_normalization_discards_html(): void
    {
        $media=new CommunityMedia;
        $expected='https://www.youtube.com/embed/abcdefghijk?playsinline=1';
        $this->assertSame($expected,$media->normalize('youtube','https://youtu.be/abcdefghijk'));
        $this->assertSame($expected,$media->normalize('youtube','<iframe src="https://www.youtube.com/embed/abcdefghijk"></iframe><script>alert(1)</script>'));
        $facebook=$media->normalize('facebook','https://www.facebook.com/page/videos/123456/');
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?href=',$facebook);
        $this->assertSame($facebook,$media->normalize('facebook',$facebook));
        $this->expectException(ValidationException::class);
        $media->normalize('facebook','https://www.facebook.com/plugins/video.php?href=https://evil.test');
    }
    public function test_admin_management_and_moderation_require_explicit_permissions(): void
    {
        $post=$this->makePost();
        $this->postJson('/_test/community/flash',['title'=>'Denied'])->assertForbidden();
        $this->deleteJson('/_test/community/posts/'.$post->id)->assertForbidden();
        $this->abilities=['flash_view.manage','social_feed.moderate'];
        $this->post('/_test/community/flash',['title'=>'Admin message','body'=>'Hello','media_type'=>'text',
            'display_policy'=>'once','priority'=>2,'role_ids'=>[2],'is_active'=>1])->assertRedirect();
        $flash=FlashView::firstOrFail(); $this->assertTrue($flash->is_active);
        $this->post('/_test/community/flash/'.$flash->id,['title'=>'Updated','body'=>'Hello','media_type'=>'text',
            'display_policy'=>'session','priority'=>1,'show_again'=>1])->assertRedirect();
        $this->assertSame(2,(int)$flash->fresh()->revision);
        $this->delete('/_test/community/posts/'.$post->id)->assertRedirect();
        $this->assertNull(SocialPost::find($post->id));
    }
}
