<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\ThemeModeController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeModeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userRoleId = (string) ($user?->role_id ?? '');
        $userRoleSlug = strtolower(trim((string) ($user?->role?->slug ?? '')));
        $isAdmin = $userRoleSlug === 'admin' || $userRoleSlug === 'superadmin' || (bool) ($user?->is_admin ?? false);

        $themes = (new ThemeModeController)->config();
        $filtered = [];

        foreach ($themes as $key => $theme) {
            $isActive = (bool) ($theme['is_active'] ?? true);
            if (!$isActive && !$isAdmin) {
                continue;
            }

            $allowedRoles = array_map('strval', (array) ($theme['allowed_roles'] ?? []));
            if (!empty($allowedRoles) && !$isAdmin) {
                $roleMatch = in_array('*', $allowedRoles, true)
                    || ($userRoleId !== '' && in_array($userRoleId, $allowedRoles, true))
                    || ($userRoleSlug !== '' && in_array($userRoleSlug, array_map('strtolower', $allowedRoles), true));

                if (!$roleMatch) {
                    continue;
                }
            }

            $filtered[] = $theme;
        }

        return response()->json([
            'status' => true,
            'data' => $filtered,
        ]);
    }
}
