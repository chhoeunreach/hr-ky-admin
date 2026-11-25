<?php

namespace App\Http\Middleware;

use App\Models\PermissionRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SPAuthGateMW
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = Auth::guard('admin')->user() ?: Auth::user();
        $roleSlug = $user?->role?->slug ?? '';
        $roleId = $user?->role_id ?? null;

        // Admin and Super Admin always have FULL access to all features and permissions
        if (Auth::guard('admin')->check() || in_array($roleSlug, ['admin', 'super-admin', 'supper-admin'], true)) {
            Gate::before(function ($user = null, ?string $ability = null) {
                return true; // Always allow for admin users
            });
            return $next($request);
        }

        if (Auth::user()) {
            $allAllocatedPermissions = PermissionRole::select([
                    DB::raw('permission_roles.permission_id as permission_id'),
                    DB::raw('permissions.permission_key as permission_key'),
                    DB::raw('permission_roles.role_id as role_id'),
                ])
                ->leftJoin('permissions', function ($query) {
                    $query->on('permission_roles.permission_id', '=', 'permissions.id');
                })
                ->where('permission_roles.role_id', $roleId)
                ->get();

            $hasAdminAccess = false;
            foreach ($allAllocatedPermissions as $permission) {
                if ($permission->permission_key) {
                    Gate::define($permission->permission_key, function () {
                        return true;
                    });
                    if ($permission->permission_key === 'access_admin_panel') {
                        $hasAdminAccess = true;
                    }
                }
            }

            // Gating Web Admin Portal: If role lacks access_admin_panel and backend_login_authorize is explicitly 0 (mobile only)
            if (!$hasAdminAccess && isset($user->role?->backend_login_authorize) && (int) $user->role->backend_login_authorize === 0) {
                if ($request->is('admin/*') || $request->routeIs('admin.*')) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => false,
                            'message' => __('message.unauthorized_admin_access') ?? 'Your account only has Mobile App access.',
                        ], 403);
                    }
                    Auth::logout();
                    $request->session()->invalidate();
                    return redirect()->route('admin.login')->with('danger', __('message.unauthorized_admin_access') ?? 'គណនីរបស់អ្នកមានសិទ្ធិប្រើប្រាស់តែលើ Mobile App ប៉ុណ្ណោះ មិនអាចចូលកាន់ Web Admin បានទេ។');
                }
            }
        }

        return $next($request);
    }
}
