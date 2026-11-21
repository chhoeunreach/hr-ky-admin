<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Modules\AppSecurity\Services\AdminStepUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminStepUpServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app_security.step_up_required' => true, 'cache.default' => 'array', 'cache.limiter' => 'array']);
        Cache::store('array')->flush();
        $admin = (new Admin())->forceFill(['id' => 90, 'password' => Hash::make('Correct-Horse-77!')]);
        Auth::guard('admin')->setUser($admin);
    }

    public function test_correct_password_allows_the_action_without_persisting_confirmation(): void
    {
        $request = Request::create('/admin/app-security/releases/uuid/activate', 'POST', [
            'step_up_password' => 'Correct-Horse-77!',
        ], [], [], ['REMOTE_ADDR' => '203.0.113.10']);

        app(AdminStepUpService::class)->verify($request, 'release.activate');

        $this->assertSame('Correct-Horse-77!', $request->input('step_up_password'));
        $this->assertSame([], session()->all());
    }

    public function test_incorrect_password_is_rejected_and_rate_limited(): void
    {
        $request = Request::create('/admin/app-security/releases/uuid/revoke', 'POST', [
            'step_up_password' => 'wrong-password',
        ], [], [], ['REMOTE_ADDR' => '203.0.113.10']);
        try {
            app(AdminStepUpService::class)->verify($request, 'release.revoke');
            $this->fail('Incorrect confirmation password must not authorize the action.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('step_up_password', $exception->errors());
            $this->assertStringNotContainsString('wrong-password', json_encode($exception->errors()));
        }

        $key = 'app-security:step-up:'.hash('sha256', '90|203.0.113.10');
        for ($attempt = 0; $attempt < 4; $attempt++) RateLimiter::hit($key, 60);
        $this->expectException(HttpException::class);
        app(AdminStepUpService::class)->verify($request, 'release.revoke');
    }

    public function test_confirmation_password_is_never_flashed_as_old_form_input(): void
    {
        $property = new \ReflectionProperty(\App\Exceptions\Handler::class, 'dontFlash');
        $property->setAccessible(true);
        $this->assertContains('step_up_password', $property->getValue(app(\App\Exceptions\Handler::class)));
    }

    public function test_explicit_emergency_flag_can_disable_step_up(): void
    {
        config(['app_security.step_up_required' => false]);
        $request = Request::create('/admin/app-security/settings', 'POST');
        app(AdminStepUpService::class)->verify($request, 'settings.update');
        $this->assertTrue(true);
    }
}
