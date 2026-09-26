<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class DetectRunawayLoop
{
    /**
     * Max requests allowed within the decay window before terminating the session.
     */
    protected int $maxRequests = 45; // 45 requests
    protected int $decaySeconds = 10; // in 10 seconds

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip static assets, downloads, and login/logout endpoints
        if ($request->is(
            'login',
            'logout',
            'admin/login',
            'admin/logout',
            'css/*',
            'js/*',
            'images/*',
            'img/*',
            'fonts/*',
            'assets/*',
            'storage/*',
            'favicon.ico'
        )) {
            return $next($request);
        }

        $session = $request->hasSession() ? $request->session() : null;
        $sessionId = $session ? $session->getId() : null;

        // Track by session ID if available, otherwise by client IP + User-Agent fingerprint
        $trackingKey = $sessionId ? "sess_{$sessionId}" : 'ip_' . md5($request->ip() . (string) $request->userAgent());
        $blockKey = "loop_blocked:{$trackingKey}";

        // If this session/client was already flagged as a loop/attack
        if (Cache::has($blockKey)) {
            return $this->expireSession($request, $session);
        }

        $cacheKey = "loop_req_count:{$trackingKey}";
        $currentCount = (int) Cache::get($cacheKey, 0);

        if ($currentCount >= $this->maxRequests) {
            // Block this session key for 60 seconds to suppress subsequent spam hits
            Cache::put($blockKey, true, 60);

            // Log security event
            Log::warning(sprintf(
                '[SECURITY] Runaway Loop / Rapid Attack detected from %s (IP: %s, URL: %s). Reached %d requests in %ds. Expiring session.',
                $trackingKey,
                $request->ip(),
                $request->fullUrl(),
                $currentCount,
                $this->decaySeconds
            ));

            return $this->expireSession($request, $session);
        }

        if ($currentCount === 0) {
            Cache::put($cacheKey, 1, $this->decaySeconds);
        } else {
            Cache::increment($cacheKey);
        }

        return $next($request);
    }

    /**
     * Forcefully terminate the session and redirect or return HTTP 419.
     */
    protected function expireSession(Request $request, $session)
    {
        // Logout authenticated user
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }
        if (Auth::check()) {
            Auth::logout();
        }

        // Invalidate and flush the session
        if ($session) {
            $session->invalidate();
            $session->regenerateToken();
        }

        $loginUrl = Route::has('admin.login') ? route('admin.login') : url('/login');
        $message = 'Session expired due to excessive requests or loop detection. Please log in again.';

        // For AJAX / Fetch / API requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 419,
                'error' => 'session_expired',
                'message' => $message,
                'redirect' => $loginUrl,
            ], 419);
        }

        // For standard browser page requests
        return redirect($loginUrl)
            ->withErrors(['session_expired' => $message])
            ->with('danger', $message)
            ->with('status', ['success' => 0, 'msg' => $message]);
    }
}
