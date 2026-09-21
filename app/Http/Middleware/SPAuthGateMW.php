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
        if (Auth::guard('admin')->check() || in_array($roleSlug, ['admin', 'supper-admin']) || in_array($roleId, [1, 7])) {
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

            foreach ($allAllocatedPermissions as $permission) {
                if ($permission->permission_key) {
                    Gate::define($permission->permission_key, function () {
                        return true;
                    });
                }
            }
        }

        return $next($request);
    }
}
