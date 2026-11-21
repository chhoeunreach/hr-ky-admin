<?php

namespace App\Modules\AppSecurity\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Checks App Security roles explicitly, bypassing the host app's global admin Gate shortcut. */
class AdminSecurityAuthorization
{
    private const ADMIN_ROLE_PERMISSIONS = [
        'general_manager' => ['app_security_view', 'app_security_devices', 'app_security_audit', 'app_security_alert_ack', 'app_security_risk_review'],
        'it_admin' => ['app_security_view', 'app_security_devices', 'app_security_releases', 'app_security_settings', 'app_security_audit', 'app_security_audit_export', 'app_security_alert_ack', 'app_security_risk_review'],
        'hr_admin' => ['app_security_view', 'app_security_device_approval'],
        'auditor' => ['app_security_view', 'app_security_audit', 'app_security_audit_export', 'app_security_alert_ack'],
        'customer_support' => ['app_security_view', 'app_security_customer_support'],
    ];

    public const ADMIN_ROLES = [
        'super_admin' => 'Super Admin',
        'general_manager' => 'General Manager',
        'it_admin' => 'IT Admin',
        'hr_admin' => 'HR Admin',
        'auditor' => 'Auditor',
        'customer_support' => 'Customer Support',
    ];

    public function allowsAny(string ...$abilities): bool
    {
        if ($this->isSuperAdmin()) return true;
        foreach (array_unique(array_merge($abilities, ['app_security_manage'])) as $ability) {
            if ($this->hasPermission($ability)) return true;
        }
        return false;
    }

    public function hasPermission(string $ability): bool
    {
        if (Auth::guard('admin')->check()) {
            $role = $this->adminSecurityRole((int) Auth::guard('admin')->id());
            if ($role === 'super_admin') return true;
            return in_array($ability, self::ADMIN_ROLE_PERMISSIONS[$role] ?? [], true);
        }

        $user = Auth::user();
        $roleId = $user?->role_id;
        if (!$roleId || !Schema::hasTable('permission_roles') || !Schema::hasTable('permissions')) return false;

        return DB::table('permission_roles')
            ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
            ->where('permission_roles.role_id', $roleId)
            ->where('permissions.permission_key', $ability)
            ->exists();
    }

    public function authorizeAny(string ...$abilities): void
    {
        abort_unless($this->allowsAny(...$abilities), 403, 'You are not authorized to access this App Security function.');
    }

    public function isSuperAdmin(): bool
    {
        if (Auth::guard('admin')->check()) {
            return $this->adminSecurityRole((int) Auth::guard('admin')->id()) === 'super_admin';
        }
        $user = Auth::user();
        $role = strtolower((string) ($user?->role?->slug ?? ''));
        return in_array($role, ['admin', 'super-admin', 'supper-admin'], true);
    }

    private function adminSecurityRole(int $adminId): ?string
    {
        if (!Schema::hasTable('app_security_admin_roles')) return null;
        $role = DB::table('app_security_admin_roles')->where('admin_id', $adminId)->value('security_role');
        return is_string($role) ? $role : null;
    }
}
