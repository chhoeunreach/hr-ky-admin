<?php

namespace App\Modules\AppSecurity\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/** Requires fresh administrator password confirmation for high-impact App Security actions. */
class AdminStepUpService
{
    public function verify(Request $request, string $action): void
    {
        if (!(bool) config('app_security.step_up_required', true)) return;

        $data = $request->validate([
            'step_up_password' => ['required', 'string', 'max:255'],
        ]);
        $actor = Auth::guard('admin')->user() ?: Auth::user();
        abort_unless($actor && $actor->getAuthPassword(), 403, 'Administrator password confirmation is unavailable.');

        $key = 'app-security:step-up:'.hash('sha256', $actor->getAuthIdentifier().'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429, 'Too many password confirmation attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');
        }
        if (!Hash::check($data['step_up_password'], $actor->getAuthPassword())) {
            RateLimiter::hit($key, 60);
            Log::warning('App Security step-up password confirmation failed.', [
                'actor_id' => $actor->getAuthIdentifier(), 'action' => $action, 'ip_address' => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'step_up_password' => 'Password confirmation failed. No changes were made.',
            ]);
        }
        RateLimiter::clear($key);
    }
}
