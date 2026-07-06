<?php

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateKioskDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token || strlen($token) < 32) {
            return $this->unauthorized();
        }

        $device = KioskDevice::query()
            ->with([
                'company:id,name,is_active',
                'branch:id,name,is_active,company_id,branch_location_latitude,branch_location_longitude',
            ])
            ->where('token_hash', hash('sha256', $token))
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (
            !$device ||
            !$device->company?->is_active ||
            !$device->branch?->is_active ||
            $device->branch->company_id !== $device->company_id
        ) {
            return $this->unauthorized();
        }

        $request->attributes->set('kioskDevice', $device);

        if (!$device->provisioned_at && !$request->is('api/kiosk/v1/provision')) {
            return response()->json([
                'status' => false,
                'message' => 'Complete kiosk provisioning with the administrator PIN first.',
                'status_code' => 403,
            ], 403);
        }

        if (!$device->last_seen_at || $device->last_seen_at->lt(now()->subMinutes(2))) {
            $device->forceFill(['last_seen_at' => now()])->saveQuietly();
        }

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Invalid or inactive kiosk credentials.',
            'status_code' => 401,
        ], 401);
    }
}
