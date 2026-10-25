<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AppLinkApiController;
use App\Http\Controllers\Web\AppLinkController;
use App\Models\AppLink;
use App\Repositories\AppLinkRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AppLinkAccountTest extends TestCase
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
        app()->setLocale('en');
        \Illuminate\Support\Facades\Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('english_name')->nullable(); $table->string('employee_code')->nullable();
            $table->boolean('is_active')->default(1); $table->integer('branch_id')->nullable(); $table->integer('company_id')->nullable(); $table->softDeletes();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Emma', 'is_active' => 1, 'deleted_at' => null],
            ['id' => 2, 'name' => 'Sam', 'is_active' => 1, 'deleted_at' => null],
            ['id' => 3, 'name' => 'Inactive manager', 'is_active' => 0, 'deleted_at' => null],
            ['id' => 4, 'name' => 'Deleted manager', 'is_active' => 1, 'deleted_at' => '2026-10-01 00:00:00']]);
        $this->actingAs(\App\Models\User::findOrFail(1));
        (require database_path('migrations/2026_09_20_000001_create_app_links_table.php'))->up();
        (require database_path('migrations/2026_10_25_000004_add_account_details_to_app_links_table.php'))->up();
        (require database_path('migrations/2026_10_25_000005_create_app_link_managers_table.php'))->up();
        Route::post('/_test/links', [AppLinkController::class, 'store']);
        Route::put('/_test/links/{id}', [AppLinkController::class, 'update']);
        Route::get('/_test/public-links', [AppLinkApiController::class, 'index']);
    }

    private function data(array $overrides = []): array
    {
        return array_replace(['name' => 'Operations channel', 'link_type' => 'telegram', 'account_type' => 'channel',
            'phone_number' => '+855 12 345 678', 'email' => 'link@example.test',
            'url' => 'https://t.me/example', 'username' => 'example-login', 'password' => 'Example-test-password!', 'status' => 1], $overrides);
    }

    public function test_supported_platform_account_types_save_and_passwords_are_encrypted(): void
    {
        foreach (AppLink::ACCOUNT_TYPES as $platform => $types) {
            foreach ($types as $type => $label) {
                $this->post('/_test/links', $this->data(['link_type' => $platform, 'account_type' => $type]))
                    ->assertRedirect(route('admin.app-links.index'))->assertSessionHasNoErrors();
                $link = AppLink::latest('id')->firstOrFail();
                $this->assertSame($type, $link->account_type);
                $this->assertSame('example-login', $link->username);
                $this->assertSame('+855 12 345 678', $link->phone_number);
                $this->assertSame('link@example.test', $link->email);
                $this->assertSame('Example-test-password!', $link->password);
                $this->assertNotSame($link->password, DB::table('app_links')->where('id', $link->id)->value('password'));
            }
        }
    }

    public function test_account_types_are_validated_for_the_selected_platform(): void
    {
        foreach ([['link_type' => 'telegram', 'account_type' => 'page'], ['link_type' => 'facebook', 'account_type' => 'channel'],
            ['account_type' => null], ['link_type' => 'website', 'account_type' => 'group']] as $invalid) {
            $this->post('/_test/links', $this->data($invalid))->assertSessionHasErrors('account_type');
        }
        $this->postJson('/_test/links', $this->data(['link_type' => ['telegram']]))->assertUnprocessable();
        $this->assertSame(0, AppLink::count());
        $this->flushSession();
        $this->post('/_test/links', $this->data(['link_type' => 'website', 'account_type' => null, 'username' => null, 'password' => null]))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.app-links.index'));
    }

    public function test_edits_keep_replace_or_clear_password_and_clear_old_subtype_when_platform_changes(): void
    {
        $this->post('/_test/links', $this->data());
        $link = AppLink::firstOrFail();
        $this->put('/_test/links/'.$link->id, $this->data(['password' => '', 'status' => 0]))->assertSessionHasNoErrors();
        $this->assertSame('Example-test-password!', $link->fresh()->password);
        $this->assertFalse($link->fresh()->status);
        $this->put('/_test/links/'.$link->id, $this->data(['password' => 'Replacement-test-password!']))->assertSessionHasNoErrors();
        $this->assertSame('Replacement-test-password!', $link->fresh()->password);
        $this->put('/_test/links/'.$link->id, $this->data(['clear_password' => 1]))->assertSessionHasErrors('password');
        $this->flushSession();
        $data = $this->data(['password' => '', 'clear_password' => 1, 'link_type' => 'website']);
        unset($data['account_type']);
        $this->put('/_test/links/'.$link->id, $data)->assertSessionHasNoErrors();
        $this->assertNull($link->fresh()->password);
        $this->assertNull($link->fresh()->account_type);
    }

    public function test_public_links_include_subtypes_and_never_include_login_credentials(): void
    {
        $this->post('/_test/links', $this->data());
        $link = AppLink::firstOrFail();
        $this->assertArrayNotHasKey('password', $link->toArray());
        $this->assertArrayNotHasKey('username', $link->toArray());
        $response = $this->getJson('/_test/public-links')->assertOk();
        $response->assertDontSee('Example-test-password!')->assertDontSee('example-login')->assertDontSee('password')->assertDontSee('username')->assertDontSee('link@example.test')->assertDontSee('+855');
        $this->assertStringContainsString('channel', $response->getContent());
    }

    public function test_search_and_filter_find_accounts_without_searching_passwords(): void
    {
        $this->post('/_test/links', $this->data());
        $this->post('/_test/links', $this->data(['link_type' => 'facebook', 'account_type' => 'page', 'username' => 'facebook-login', 'phone_number' => '012345678', 'email' => 'page@example.test']));
        $repo = app(AppLinkRepository::class);
        $this->assertSame(1, $repo->getAllLinksPaginated(['link_type' => 'facebook', 'account_type' => 'page'])->total());
        $this->assertSame(0, $repo->getAllLinksPaginated(['link_type' => 'telegram', 'account_type' => 'page'])->total());
        $this->assertSame(1, $repo->getAllLinksPaginated(['search' => 'facebook-login'])->total());
        $this->assertSame(0, $repo->getAllLinksPaginated(['search' => 'Example-test-password!'])->total());
        $this->assertSame(1, $repo->getAllLinksPaginated(['search' => '012345678'])->total());
        $this->assertSame(1, $repo->getAllLinksPaginated(['search' => 'page@example.test'])->total());
    }

    public function test_forms_show_account_fields_without_rendering_stored_password(): void
    {
        $this->post('/_test/links', $this->data());
        $link = AppLink::firstOrFail();
        foreach (['en', 'km'] as $locale) {
            app()->setLocale($locale);
            $this->view('admin.appLink.common.form', ['linkDetail' => $link, 'linkTypes' => AppLink::LINK_TYPES, 'errors' => new \Illuminate\Support\ViewErrorBag(), 'managerOptions' => AppLink::managerOptions($link)])
                ->assertSee('name="username"', false)->assertSee('name="password"', false)
                ->assertSee('name="phone_number"', false)->assertSee('name="email"', false)
                ->assertSee('name="account_type"', false)->assertSee('name="clear_password"', false)
                ->assertSee('Channel')->assertSee(__('index.link_keep_password'))
                ->assertDontSee('Example-test-password!')->assertDontSee($link->getRawOriginal('password'));
        }
    }

    public function test_contact_details_validate_and_can_be_edited(): void
    {
        $this->post('/_test/links', $this->data(['email' => 'invalid']))->assertSessionHasErrors('email');
        $this->post('/_test/links', $this->data(['phone_number' => str_repeat('1', 51)]))->assertSessionHasErrors('phone_number');
        $this->flushSession();
        $this->post('/_test/links', $this->data());
        $link = AppLink::firstOrFail();
        $this->put('/_test/links/'.$link->id, $this->data(['phone_number' => '012345678', 'email' => 'updated@example.test']))->assertSessionHasNoErrors();
        $this->assertSame('012345678', $link->fresh()->phone_number);
        $this->assertSame('updated@example.test', $link->fresh()->email);
    }

    public function test_creator_is_automatic_and_managers_support_multiple_people_and_removal(): void
    {
        $this->post('/_test/links', $this->data(['manager_ids' => [1, 2], 'created_by' => 2]))->assertSessionHasNoErrors();
        $link = AppLink::firstOrFail();
        $this->assertSame(1, $link->created_by);
        $this->assertSame('Emma', $link->created_by_name);
        $this->assertSame([1, 2], $link->managers->pluck('id')->sort()->values()->all());
        $this->actingAs(\App\Models\User::findOrFail(2));
        $this->put('/_test/links/'.$link->id, $this->data(['manager_ids' => [2], 'created_by' => 2]))->assertSessionHasNoErrors();
        $link = $link->fresh();
        $this->assertSame(1, $link->created_by);
        $this->assertSame('Emma', $link->created_by_name);
        $this->assertSame([2], $link->managers->modelKeys());
        $this->view('admin.appLink.common.ownership', ['value' => $link])->assertSee('Emma')->assertSee('Sam');
        $this->put('/_test/links/'.$link->id, $this->data(['manager_ids' => []]))->assertSessionHasNoErrors();
        $this->assertCount(0, $link->fresh()->managers);
    }

    public function test_manager_selection_rejects_missing_inactive_deleted_or_duplicate_people(): void
    {
        foreach ([[999], [3], [4], [1, 1]] as $ids) {
            $this->post('/_test/links', $this->data(['manager_ids' => $ids]))->assertSessionHasErrors();
        }
        $this->assertSame(0, AppLink::count());
    }

    public function test_failed_save_does_not_flash_password_or_exception_details(): void
    {
        $repo = \Mockery::mock(AppLinkRepository::class);
        $repo->shouldReceive('store')->once()->andThrow(new \RuntimeException('Example-test-password!'));
        $this->app->instance(AppLinkRepository::class, $repo);
        $this->from('/_test/links/create')->post('/_test/links', $this->data())
            ->assertRedirect('/_test/links/create')->assertSessionHas('danger', 'Unable to save the link. Please try again.')
            ->assertSessionMissing('_old_input.password');
    }
}
