<?php

namespace Tests\Feature;

use App\Http\Middleware\SPAuthGateMW;
use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemDiagnosticsTest extends TestCase
{
    public function test_diagnostics_reports_salary_exclusions_and_redacts_and_filters_log_entries(): void
    {
        config(['database.connections.sqlite.database' => ':memory:', 'app.debug' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('status')->default('verified');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('roles')->insert([['id' => 1, 'name' => 'admin'], ['id' => 2, 'name' => 'staff']]);
        DB::table('users')->insert([['id' => 403, 'role_id' => 1], ['id' => 404, 'role_id' => 2]]);

        $path = tempnam(sys_get_temp_dir(), 'hr-diagnostics-');
        try {
            config(['logging.channels.daily.path' => null, 'logging.channels.single.path' => $path]);
            $time = now()->toDateTimeString();
            file_put_contents($path,
                "[{$time}] local.ERROR: SQLSTATE[42S02] confidential_query_details\n"
                ."[{$time}] local.WARNING: Firebase message private_device_token\n"
                .'['.now()->subDays(8)->toDateTimeString()."] local.ERROR: Password example\n"
            );

            $this->actingAs((new Admin)->forceFill(['id' => 1]), 'admin');
            $response = $this->get(route('admin.maintenance.diagnostics', ['level' => 'warning']));
            $response->assertOk()
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertSee(trans_choice('diagnostics.checks.salary_directory.finding', 1))
                ->assertSee(__('diagnostics.checks.payday_queue.fix'))
                ->assertSee(__('diagnostics.categories.firebase.title'))
                ->assertDontSee(__('diagnostics.categories.schema.title'))
                ->assertDontSee('private_device_token');

            $this->get(route('admin.maintenance.diagnostics', ['level' => 'error']))
                ->assertOk()
                ->assertSee(__('diagnostics.categories.schema.title'))
                ->assertDontSee('confidential_query_details')
                ->assertDontSee(now()->subDays(8)->toDateTimeString())
                ->assertDontSee(__('diagnostics.categories.firebase.title'));
        } finally {
            unlink($path);
        }
    }

    public function test_diagnostics_requires_the_system_admin_guard(): void
    {
        $url = route('admin.maintenance.diagnostics');
        $this->get($url)->assertRedirect(route('admin.login'));

        $this->withoutMiddleware(SPAuthGateMW::class);
        Cache::put('role', ['staff']);
        $user = (new User)->forceFill(['id' => 1]);
        $user->setRelation('role', (new Role)->forceFill(['slug' => 'staff']));
        $this->actingAs($user)->get($url)->assertForbidden();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The page runs through the full route. Stub only the unrelated master layout.
        $this->layoutRoot = sys_get_temp_dir().'/hr-diagnostics-layout-'.bin2hex(random_bytes(8));
        mkdir($this->layoutRoot.'/layouts', 0700, true);
        file_put_contents($this->layoutRoot.'/layouts/master.blade.php', "@yield('main-content')");
        $this->app['view']->getFinder()->prependLocation($this->layoutRoot);
    }

    protected function tearDown(): void
    {
        unlink($this->layoutRoot.'/layouts/master.blade.php');
        rmdir($this->layoutRoot.'/layouts');
        rmdir($this->layoutRoot);

        parent::tearDown();
    }

    private string $layoutRoot;
}
