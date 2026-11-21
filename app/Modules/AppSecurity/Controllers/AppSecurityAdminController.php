<?php

namespace App\Modules\AppSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AppSecurity\Models\RegisteredDevice;
use App\Modules\AppSecurity\Models\SecurityAuditLog;
use App\Modules\AppSecurity\Models\SecuritySession;
use App\Modules\AppSecurity\Jobs\SendAppSecurityUserNotification;
use App\Modules\AppSecurity\Services\AuditLogCsvExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppSecurityAdminController extends Controller
{
    public function index(Request $request, \App\Modules\AppSecurity\Services\SecurityPolicyService $policy, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization, \App\Modules\AppSecurity\Services\SecurityDashboardAnalyticsService $analyticsService): View
    {
        $authorization->authorizeAny('app_security_view', 'app_security_customer_support');
        $supportOnly = $authorization->hasPermission('app_security_customer_support')
            && !$authorization->allowsAny('app_security_devices');
        $staffApprovalOnly = $authorization->hasPermission('app_security_device_approval')
            && !$authorization->allowsAny('app_security_devices');
        $canViewAnalytics = !$supportOnly && $authorization->allowsAny('app_security_audit', 'app_security_devices');
        $analytics = $canViewAnalytics
            ? $analyticsService->snapshot((int) $policy->value('risk_review_threshold'), (int) $policy->value('risk_block_threshold'))
            : null;
        $deviceQuery = RegisteredDevice::query();
        if ($supportOnly) $deviceQuery->where('user_type', 'customer');
        elseif ($staffApprovalOnly) $deviceQuery->where('user_type', 'staff');
        $query = RegisteredDevice::query()->with([
            'user:id,name,username,email,phone,avatar,role_id',
            'user.role:id,name,slug',
            'approver:id,name,username,email',
            'attestations:id,device_id,verification_status,security_level,verification_summary,verified_at,created_at',
            'latestRiskScore',
        ])->withCount([
            'sessions',
            'sessions as active_sessions_count' => fn ($q) => $q->whereNull('revoked_at')->where('expires_at', '>', now()),
        ]);

        if ($request->filled('status')) {
            $statusVal = $request->string('status')->toString();
            if ($statusVal === 'blocked') {
                $query->where(fn ($q) => $q->where('device_status', 'blocked')->orWhereNotNull('blocked_at'));
            } elseif ($statusVal === 'revoked') {
                $query->where(fn ($q) => $q->where('device_status', 'revoked')->orWhereNotNull('revoked_at'));
            } elseif ($statusVal === 'approved') {
                $query->where('device_status', 'approved')->whereNull('blocked_at')->whereNull('revoked_at');
            } else {
                $query->where('device_status', $statusVal);
            }
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->string('user_type')->toString());
        }

        if ($request->filled('attestation_level')) {
            $attVal = strtolower($request->string('attestation_level')->toString());
            if (in_array($attVal, ['none', 'unverified'], true)) {
                $query->where(fn ($q) => $q->whereNull('attestation_level')->orWhereIn('attestation_level', ['none', 'unverified', '']));
            } else {
                $query->where('attestation_level', $attVal);
            }
        }

        if ($request->filled('risk_level')) {
            $riskVal = $request->string('risk_level')->toString();
            $reviewThreshold = (int) $policy->value('risk_review_threshold');
            $blockThreshold = (int) $policy->value('risk_block_threshold');
            if ($riskVal === 'high') {
                $query->where('risk_score', '>=', $blockThreshold ?: 60);
            } elseif ($riskVal === 'medium') {
                $query->whereBetween('risk_score', [$reviewThreshold ?: 25, ($blockThreshold ?: 60) - 1]);
            } elseif ($riskVal === 'low') {
                $query->where('risk_score', '<', $reviewThreshold ?: 25);
            }
        }

        if ($request->filled('activity')) {
            $actVal = $request->string('activity')->toString();
            if ($actVal === 'online') {
                $query->where('last_seen_at', '>=', now()->subHour());
            } elseif ($actVal === 'today') {
                $query->where('last_seen_at', '>=', now()->subHours(24));
            } elseif ($actVal === 'week') {
                $query->where('last_seen_at', '>=', now()->subDays(7));
            } elseif ($actVal === 'inactive') {
                $query->where(fn ($q) => $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', now()->subDays(7)));
            }
        }

        if ($supportOnly) $query->where('user_type', 'customer');
        elseif ($staffApprovalOnly) $query->where('user_type', 'staff');

        if ($request->filled('q')) {
            $needle = '%' . addcslashes($request->string('q')->toString(), '%_') . '%';
            $query->where(function ($devices) use ($needle): void {
                $devices->where('uuid', 'like', $needle)
                    ->orWhere('installation_uuid', 'like', $needle)
                    ->orWhere('device_model', 'like', $needle)
                    ->orWhere('package_name', 'like', $needle)
                    ->orWhere('public_key_fingerprint', 'like', $needle)
                    ->orWhere('signing_sha256', 'like', $needle)
                    ->orWhere('version_name', 'like', $needle)
                    ->orWhereHas('user', function ($users) use ($needle): void {
                        $users->where('name', 'like', $needle)
                            ->orWhere('username', 'like', $needle)
                            ->orWhere('email', 'like', $needle)
                            ->orWhere('phone', 'like', $needle);
                    });
            });
        }

        $sortBy = $request->string('sort_by', 'last_seen')->toString();
        $sortDir = strtolower($request->string('sort_dir', 'desc')->toString()) === 'asc' ? 'asc' : 'desc';

        match ($sortBy) {
            'registered' => $query->orderBy('registered_at', $sortDir),
            'risk_score' => $query->orderBy('risk_score', $sortDir),
            'model' => $query->orderBy('device_model', $sortDir),
            'status' => $query->orderBy('device_status', $sortDir),
            default => $query->orderByRaw("CASE WHEN last_seen_at IS NULL THEN 1 ELSE 0 END, last_seen_at {$sortDir}"),
        };

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $devices = $query->paginate($perPage)->withQueryString();
        $devices->getCollection()->each(function (RegisteredDevice $device) use ($supportOnly): void {
            $device->makeHidden(['public_key']);
            if ($supportOnly) {
                $device->makeHidden(['installation_uuid', 'public_key_fingerprint', 'package_name', 'signing_sha256', 'version_name', 'version_code', 'android_sdk', 'device_model', 'attestation_level', 'risk_score', 'risk_level', 'last_ip_address', 'approved_by', 'blocked_at', 'revoked_at']);
                $device->unsetRelation('attestations')->unsetRelation('latestRiskScore');
            }
        });

        // Query all connected API sessions for the Device & Sessions tab
        $sessionsQuery = SecuritySession::query()->with([
            'device:id,uuid,device_model,android_version,android_sdk,device_status,last_seen_at',
            'user:id,name,username,email,phone,avatar,role_id',
        ])->latest('last_used_at');

        if ($request->filled('session_status')) {
            $sessStatus = $request->string('session_status')->toString();
            if ($sessStatus === 'active') {
                $sessionsQuery->whereNull('revoked_at')->where('expires_at', '>', now());
            } elseif ($sessStatus === 'revoked') {
                $sessionsQuery->whereNotNull('revoked_at');
            } elseif ($sessStatus === 'expired') {
                $sessionsQuery->whereNull('revoked_at')->where('expires_at', '<=', now());
            }
        }

        if ($request->filled('session_device')) {
            $sessDeviceUuid = $request->string('session_device')->toString();
            $sessionsQuery->whereHas('device', fn ($dq) => $dq->where('uuid', $sessDeviceUuid));
        }

        if ($request->filled('session_q')) {
            $sessNeedle = '%' . addcslashes($request->string('session_q')->toString(), '%_') . '%';
            $sessionsQuery->where(function ($sq) use ($sessNeedle): void {
                $sq->where('uuid', 'like', $sessNeedle)
                    ->orWhere('session_token_sha256', 'like', $sessNeedle)
                    ->orWhereHas('device', fn ($dq) => $dq->where('device_model', 'like', $sessNeedle)->orWhere('uuid', 'like', $sessNeedle))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $sessNeedle)->orWhere('username', 'like', $sessNeedle));
            });
        }

        $allSessions = $sessionsQuery->paginate(25, ['*'], 'sessions_page')->withQueryString();
        $activeSessionsTotal = SecuritySession::query()->whereNull('revoked_at')->where('expires_at', '>', now())->count();
        $totalSessionsCount = SecuritySession::query()->count();

        return view('admin.app-security.index', [
            'devices' => $devices,
            'allSessions' => $allSessions,
            'activeSessionsTotal' => $activeSessionsTotal,
            'totalSessionsCount' => $totalSessionsCount,
            'supportOnly' => $supportOnly,
            'analytics' => $analytics,
            'canViewAnalytics' => $canViewAnalytics,
            'counts' => [
                'total' => (clone $deviceQuery)->count(),
                'pending' => (clone $deviceQuery)->where('device_status', 'pending')->count(),
                'approved' => (clone $deviceQuery)->where('device_status', 'approved')->whereNull('revoked_at')->whereNull('blocked_at')->count(),
                'blocked' => (clone $deviceQuery)->where(fn ($q) => $q->where('device_status', 'blocked')->orWhereNotNull('blocked_at'))->count(),
                'revoked' => (clone $deviceQuery)->where(fn ($q) => $q->where('device_status', 'revoked')->orWhereNotNull('revoked_at'))->count(),
                'high_risk' => (clone $deviceQuery)->where('risk_score', '>=', (int) $policy->value('risk_block_threshold'))->count(),
                'online' => (clone $deviceQuery)->where('last_seen_at', '>=', now()->subHours(24))->count(),
                'attested' => (clone $deviceQuery)->whereIn('attestation_level', ['strongbox', 'tee'])->count(),
                'active_sessions' => $activeSessionsTotal,
                'total_sessions' => $totalSessionsCount,
            ],
            'release' => $supportOnly ? null : \App\Modules\AppSecurity\Models\SecurityRelease::query()
                ->where('release_status', 'active')->orderByDesc('version_code')->first(),
            'releases' => $supportOnly ? collect() : \App\Modules\AppSecurity\Models\SecurityRelease::query()->latest()->limit(30)->get(),
            'securityEnabled' => (bool) $policy->value('enabled'),
            'settings' => $policy->snapshot(),
            'canManageSettings' => $authorization->allowsAny('app_security_settings'),
            'canManageReleases' => $authorization->allowsAny('app_security_releases'),
            'canManageDevices' => $authorization->allowsAny('app_security_devices'),
            'canApproveStaffDevices' => $authorization->allowsAny('app_security_device_approval'),
            'canAssignAdminRoles' => $authorization->isSuperAdmin(),
            'adminRoleOptions' => \App\Modules\AppSecurity\Services\AdminSecurityAuthorization::ADMIN_ROLES,
            'canViewSecurityAlerts' => $authorization->allowsAny('app_security_audit'),
            'canExportAuditLogs' => $authorization->allowsAny('app_security_audit_export'),
            'canReviewRiskSignals' => $authorization->allowsAny('app_security_risk_review'),
            'canAcknowledgeSecurityAlerts' => $authorization->allowsAny('app_security_alert_ack'),
            'securityAlerts' => $authorization->allowsAny('app_security_audit') && \Illuminate\Support\Facades\Schema::hasTable('app_security_alerts')
                ? DB::table('app_security_alerts')->latest('created_at')->limit(25)->get() : collect(),
            'adminUsers' => $authorization->isSuperAdmin() && \Illuminate\Support\Facades\Schema::hasTable('app_security_admin_roles')
                ? DB::table('admins as admins')->leftJoin('app_security_admin_roles as roles', 'roles.admin_id', '=', 'admins.id')
                    ->orderBy('admins.name')->get([
                        'admins.id', 'admins.name', 'admins.username', 'admins.email',
                        'roles.security_role', 'roles.assignment_reason',
                    ])
                : collect(),
        ]);
    }

    public function dashboard(\App\Modules\AppSecurity\Services\SecurityPolicyService $policy, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): JsonResponse
    {
        $authorization->authorizeAny('app_security_view', 'app_security_customer_support');
        $supportOnly = $authorization->hasPermission('app_security_customer_support')
            && !$authorization->allowsAny('app_security_devices');
        if ($supportOnly) {
            $customers = RegisteredDevice::query()->where('user_type', 'customer');
            return response()->json(['devices' => [
                'total' => (clone $customers)->count(),
                'pending' => (clone $customers)->where('device_status', 'pending')->count(),
                'approved' => (clone $customers)->where('device_status', 'approved')->whereNull('revoked_at')->count(),
                'blocked' => (clone $customers)->where('device_status', 'blocked')->count(),
                'revoked' => (clone $customers)->where('device_status', 'revoked')->count(),
            ]]);
        }
        return response()->json([
            'devices' => [
                'total' => RegisteredDevice::query()->count(),
                'pending' => RegisteredDevice::query()->where('device_status', 'pending')->count(),
                'approved' => RegisteredDevice::query()->where('device_status', 'approved')->whereNull('revoked_at')->count(),
                'blocked' => RegisteredDevice::query()->where('device_status', 'blocked')->count(),
                'revoked' => RegisteredDevice::query()->where('device_status', 'revoked')->count(),
                'high_risk' => RegisteredDevice::query()->where('risk_score', '>=', $policy->value('risk_block_threshold'))->count(),
            ],
            'active_sessions' => SecuritySession::query()->whereNull('revoked_at')->where('expires_at', '>', now())->count(),
            'current_release' => \App\Modules\AppSecurity\Models\SecurityRelease::query()
                ->where('release_status', 'active')->orderByDesc('version_code')->first([
                    'version_name', 'version_code', 'apk_sha256', 'released_at',
                ]),
            'recent_security_events' => DB::table('app_security_logs')->orderByDesc('occurred_at')->limit(10)->get(),
        ]);
    }

    public function settings(\App\Modules\AppSecurity\Services\SecurityPolicyService $policy): JsonResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_settings');
        return response()->json(['settings' => $policy->snapshot()]);
    }

    public function showSettings(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): RedirectResponse
    {
        $authorization->authorizeAny('app_security_settings');
        return redirect()->route('admin.app-security.index')->withFragment('security-policy-settings');
    }

    public function updateSettings(Request $request, \App\Modules\AppSecurity\Services\SecurityPolicyService $policy): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_settings');
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'settings.update');
        $data = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'device_approval_required' => ['sometimes', 'boolean'],
            'customer_auto_approval' => ['sometimes', 'boolean'],
            'max_devices_staff' => ['sometimes', 'integer', 'between:0,50'],
            'max_devices_customer' => ['sometimes', 'integer', 'between:0,50'],
            'max_devices_admin' => ['sometimes', 'integer', 'between:0,50'],
            'hardware_attestation' => ['sometimes', 'in:preferred,required'],
            'heartbeat_seconds' => ['sometimes', 'integer', 'between:30,900'],
            'session_ttl_seconds' => ['sometimes', 'integer', 'between:60,3600'],
            'max_sessions_per_device' => ['sometimes', 'integer', 'between:1,20'],
            'offline_grace_seconds' => ['sometimes', 'integer', 'between:0,86400'],
            'refresh_ttl_seconds' => ['sometimes', 'integer', 'between:300,7776000'],
            'challenge_ttl_seconds' => ['sometimes', 'integer', 'between:30,600'],
            'nonce_ttl_seconds' => ['sometimes', 'integer', 'between:60,600'],
            'risk_enabled' => ['sometimes', 'boolean'],
            'risk_window_hours' => ['sometimes', 'integer', 'between:1,720'],
            'risk_review_threshold' => ['sometimes', 'integer', 'between:0,99'],
            'risk_block_threshold' => ['sometimes', 'integer', 'between:1,100'],
            'risk_policy_version' => ['sometimes', 'string', 'min:1', 'max:40'],
            'risk_points_attestation_missing' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_attestation_verification_failed' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_attestation_os_policy_failed' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_root_detected' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_emulator_detected' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_instrumentation_detected' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_debug_build' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_debugger_attached' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_hooking_detected' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_vpn_detected' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_request_nonce_replayed' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_security_session_invalid' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_request_timestamp_expired' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_device_not_authorized' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_registration_velocity' => ['sometimes', 'integer', 'between:0,100'],
            'risk_points_cross_account_key_reuse' => ['sometimes', 'integer', 'between:0,100'],
        ]);
        if ($data === []) abort(422, 'At least one supported setting is required.');
        $review = (int) ($data['risk_review_threshold'] ?? $policy->value('risk_review_threshold'));
        $block = (int) ($data['risk_block_threshold'] ?? $policy->value('risk_block_threshold'));
        if ($block <= $review) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'risk_block_threshold' => 'The high-risk threshold must be greater than the review threshold.',
            ]);
        }

        $adminId = Auth::guard('admin')->id();
        $change = $policy->update($data, $adminId, function (array $before, array $after) use ($adminId, $data, $request): void {
            SecurityAuditLog::query()->create([
                'admin_id' => $adminId,
                'actor_type' => $adminId !== null ? 'admin' : 'user',
                'actor_id' => $adminId ?? $request->user()?->getAuthIdentifier(),
                'action' => 'security.settings.updated',
                'target_type' => 'settings',
                'target_id' => 'app-security',
                'previous_state' => array_intersect_key($before, $data),
                'new_state' => array_intersect_key($after, $data),
                'reason' => 'Updated through App Security settings',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
                'occurred_at' => now(),
            ]);
        });
        if (!$request->expectsJson()) {
            return $this->showSettings(app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class))
                ->with('success', 'App Security settings updated and audited.');
        }
        return response()->json(['settings' => $change['after']]);
    }

    public function updateAdminRole(Request $request, int $adminId, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        abort_unless($authorization->isSuperAdmin(), 403, 'Only a Super Admin can assign App Security administrator roles.');
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'admin_role.update');
        $data = $request->validate([
            'security_role' => ['required', 'in:super_admin,general_manager,it_admin,hr_admin,auditor,customer_support'],
            'reason' => ['required', 'string', 'min:3', 'max:512'],
        ]);
        $actorId = (int) Auth::guard('admin')->id();
        $change = DB::transaction(function () use ($adminId, $actorId, $data): array {
            $admin = DB::table('admins')->where('id', $adminId)->lockForUpdate()->first();
            abort_if(!$admin, 404);
            $current = DB::table('app_security_admin_roles')->where('admin_id', $adminId)->lockForUpdate()->first();
            $previousRole = $current->security_role ?? null;
            if ($previousRole === 'super_admin' && $data['security_role'] !== 'super_admin') {
                $otherSuperAdmins = DB::table('app_security_admin_roles as roles')
                    ->join('admins', 'admins.id', '=', 'roles.admin_id')
                    ->where('roles.security_role', 'super_admin')->where('admins.is_active', true)
                    ->where('admins.id', '!=', $adminId)->count();
                abort_if($otherSuperAdmins === 0, 409, 'Assign another active Super Admin before downgrading the last one.');
            }

            $values = [
                'security_role' => $data['security_role'], 'assigned_by' => $actorId,
                'assignment_reason' => $data['reason'], 'updated_at' => now(),
            ];
            if ($current) {
                DB::table('app_security_admin_roles')->where('admin_id', $adminId)->update($values);
            } else {
                DB::table('app_security_admin_roles')->insert($values + ['admin_id' => $adminId, 'created_at' => now()]);
            }
            SecurityAuditLog::query()->create([
                'admin_id' => $actorId, 'actor_type' => 'admin', 'actor_id' => $actorId,
                'action' => 'security.admin_role.updated', 'target_type' => 'admin_role',
                'target_id' => (string) $adminId,
                'previous_state' => ['security_role' => $previousRole],
                'new_state' => ['security_role' => $data['security_role']],
                'reason' => $data['reason'], 'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 512), 'occurred_at' => now(),
            ]);
            return ['security_role' => $data['security_role']];
        }, 3);

        if (!$request->expectsJson()) {
            return redirect()->route('admin.app-security.index')->with('success', 'Administrator App Security role updated and audited.');
        }
        return response()->json(['admin_id' => $adminId] + $change);
    }

    public function devices(Request $request, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): JsonResponse
    {
        $authorization->authorizeAny('app_security_view', 'app_security_customer_support');
        $query = RegisteredDevice::query()->with('user:id,name,username,role_id');
        $supportOnly = $authorization->hasPermission('app_security_customer_support')
            && !$authorization->allowsAny('app_security_view', 'app_security_devices');
        $staffApprovalOnly = $authorization->hasPermission('app_security_device_approval')
            && !$authorization->allowsAny('app_security_devices');
        if ($supportOnly) {
            $query->where('user_type', 'customer');
        } elseif ($staffApprovalOnly) {
            $query->where('user_type', 'staff');
        }
        if ($request->filled('status')) {
            $query->where('device_status', $request->string('status')->toString());
        }
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->string('user_type')->toString());
        }
        if ($request->filled('q')) {
            $needle = '%' . addcslashes($request->string('q')->toString(), '%_') . '%';
            $query->where(function ($devices) use ($needle): void {
                $devices->where('uuid', 'like', $needle)
                    ->orWhere('installation_uuid', 'like', $needle)
                    ->orWhere('device_model', 'like', $needle)
                    ->orWhereHas('user', fn ($users) => $users->where('name', 'like', $needle)->orWhere('username', 'like', $needle));
            });
        }

        $page = $query->latest('registered_at')->paginate(min(100, max(1, (int) $request->input('per_page', 25))));
        if ($supportOnly) {
            $page->setCollection($page->getCollection()->map(fn (RegisteredDevice $device) => [
                'uuid' => $device->uuid, 'user_type' => $device->user_type,
                'device_status' => $device->device_status, 'device_model' => $device->device_model,
                'version_name' => $device->version_name, 'registered_at' => $device->registered_at,
                'last_seen_at' => $device->last_seen_at,
                'user' => $device->user ? ['name' => $device->user->name, 'username' => $device->user->username] : null,
            ]));
        } else {
            $page->getCollection()->each(fn (RegisteredDevice $device) => $device->makeHidden(['public_key']));
        }
        return response()->json($page);
    }

    public function alerts(): JsonResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_audit');
        return response()->json(DB::table('app_security_alerts')->latest('created_at')->paginate(50));
    }

    public function acknowledgeAlert(Request $request, int $alertId): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $authorization = app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class);
        $authorization->authorizeAny('app_security_alert_ack');
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:512']]);
        $actorId = Auth::guard('admin')->id();
        $changed = DB::transaction(function () use ($alertId, $actorId, $data): bool {
            $alert = DB::table('app_security_alerts')->where('id', $alertId)->lockForUpdate()->first();
            abort_if(!$alert, 404);
            if ($alert->acknowledged_at) return false;
            DB::table('app_security_alerts')->where('id', $alertId)->update([
                'acknowledged_by' => $actorId, 'acknowledged_at' => now(),
                'acknowledgement_reason' => $data['reason'], 'updated_at' => now(),
            ]);
            SecurityAuditLog::query()->create([
                'admin_id' => $actorId, 'actor_type' => 'admin', 'actor_id' => $actorId,
                'action' => 'security.alert.acknowledged', 'target_type' => 'security_alert',
                'target_id' => (string) $alertId,
                'previous_state' => ['acknowledged_at' => null],
                'new_state' => ['acknowledged_at' => now()->toISOString()],
                'reason' => $data['reason'], 'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 512), 'occurred_at' => now(),
            ]);
            return true;
        }, 3);
        if (!$request->expectsJson()) return redirect()->route('admin.app-security.index')->with('success', $changed ? 'Security alert acknowledged and audited.' : 'This security alert was already acknowledged.');
        return response()->json(['alert_id' => $alertId, 'acknowledged' => true, 'changed' => $changed]);
    }

    public function logs(Request $request): JsonResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_audit');
        $filters = $request->validate([
            'event_type' => ['nullable', 'string', 'max:80'],
            'severity' => ['nullable', 'in:info,low,medium,high,critical'],
            'device_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $query = DB::table('app_security_logs')->select([
            'id', 'event_type', 'severity', 'user_id', 'device_id', 'request_uuid',
            'ip_address', 'details', 'occurred_at',
        ]);
        foreach (['event_type', 'severity', 'device_id'] as $column) {
            if (isset($filters[$column])) $query->where($column, $filters[$column]);
        }
        if (isset($filters['from'])) $query->where('occurred_at', '>=', $filters['from']);
        if (isset($filters['to'])) $query->where('occurred_at', '<=', $filters['to'].' 23:59:59');

        return response()->json($query->orderByDesc('occurred_at')->paginate($filters['per_page'] ?? 50));
    }

    public function exportAuditLogs(Request $request, \App\Modules\AppSecurity\Services\AuditLogCsvExportService $exporter): StreamedResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_audit_export');
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:80'],
            'target_type' => ['nullable', 'string', 'max:80'],
            'target_id' => ['nullable', 'string', 'max:80'],
            'admin_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $filters['from'] = isset($filters['from'])
            ? \Illuminate\Support\Carbon::parse($filters['from'])->startOfDay()->toDateTimeString()
            : now()->subDays(90)->startOfDay()->toDateTimeString();
        $filters['to'] = isset($filters['to'])
            ? \Illuminate\Support\Carbon::parse($filters['to'])->endOfDay()->toDateTimeString()
            : now()->endOfDay()->toDateTimeString();

        $actor = Auth::guard('admin')->user() ?: Auth::user();
        $actorId = Auth::guard('admin')->id() ?: $actor?->id;
        SecurityAuditLog::query()->create([
            'admin_id' => $actorId,
            'actor_type' => 'admin',
            'actor_id' => $actorId,
            'action' => 'security.audit.exported',
            'target_type' => 'security_audit_log',
            'target_id' => 'filtered_csv',
            'previous_state' => null,
            'new_state' => ['filters' => $filters, 'maximum_rows' => AuditLogCsvExportService::MAX_ROWS],
            'reason' => 'Administrator requested a filtered CSV audit export.',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'occurred_at' => now(),
        ]);

        $query = DB::table('app_security_audit_logs')->select([
            'id', 'admin_id', 'actor_type', 'actor_id', 'action', 'target_type', 'target_id',
            'previous_state', 'new_state', 'reason', 'ip_address', 'user_agent', 'occurred_at',
        ]);
        foreach (['action', 'target_type', 'target_id', 'admin_id'] as $column) {
            if (isset($filters[$column])) $query->where($column, $filters[$column]);
        }
        $query->where('occurred_at', '>=', $filters['from'])
            ->where('occurred_at', '<=', $filters['to']);
        $totalRows = (clone $query)->count();
        $filename = 'kneayerng-app-security-audit-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(
            function () use ($exporter, $query): void {
                $stream = fopen('php://output', 'wb');
                if ($stream === false) throw new \RuntimeException('Could not open audit CSV output stream.');
                try {
                    $exporter->write($query, $stream);
                } finally {
                    fclose($stream);
                }
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'X-Export-Total-Rows' => (string) $totalRows,
                'X-Export-Truncated' => $totalRows > AuditLogCsvExportService::MAX_ROWS ? 'true' : 'false',
                'Cache-Control' => 'no-store, private',
            ]
        );
    }

    public function riskReports(Request $request, \App\Modules\AppSecurity\Services\SecurityPolicyService $policy): JsonResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_audit', 'app_security_devices');
        $filters = $request->validate([
            'risk_level' => ['nullable', 'in:low,medium,high'],
            'user_type' => ['nullable', 'in:staff,customer,admin'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $from = isset($filters['from']) ? now()->parse($filters['from'])->startOfDay() : now()->subDays(30)->startOfDay();
        $to = isset($filters['to']) ? now()->parse($filters['to'])->endOfDay() : now()->endOfDay();
        $signals = DB::table('app_device_integrity_checks')
            ->whereBetween('occurred_at', [$from, $to])
            ->select('signal', 'severity', DB::raw('COUNT(*) as event_count'), DB::raw('SUM(risk_points) as risk_points'))
            ->groupBy('signal', 'severity')->orderByDesc('event_count')->get();
        $levels = DB::table('app_device_risk_scores')
            ->whereBetween('calculated_at', [$from, $to])
            ->select('risk_level', DB::raw('COUNT(*) as calculation_count'), DB::raw('MAX(risk_score) as maximum_score'))
            ->groupBy('risk_level')->get();
        $devices = RegisteredDevice::query()->with(['user:id,name,username'])
            ->when(isset($filters['risk_level']), function ($query) use ($filters): void {
                $threshold = $filters['risk_level'] === 'high'
                    ? (int) $policy->value('risk_block_threshold')
                    : (int) $policy->value('risk_review_threshold');
                if ($filters['risk_level'] === 'high') $query->where('risk_score', '>=', $threshold);
                elseif ($filters['risk_level'] === 'medium') $query->whereBetween('risk_score', [(int) $policy->value('risk_review_threshold'), max(0, (int) $policy->value('risk_block_threshold') - 1)]);
                else $query->where('risk_score', '<', (int) $policy->value('risk_review_threshold'));
            })
            ->when(isset($filters['user_type']), fn ($query) => $query->where('user_type', $filters['user_type']))
            ->whereHas('riskScores', fn ($scores) => $scores->whereBetween('calculated_at', [$from, $to]))
            ->orderByDesc('risk_score')->paginate($filters['per_page'] ?? 50);
        $devices->getCollection()->each(fn (RegisteredDevice $device) => $device->makeHidden(['public_key']));

        $daily = DB::table('app_device_intelligence')
            ->whereBetween('observed_date', [$from->toDateString(), $to->toDateString()])
            ->select('observed_date', DB::raw('COUNT(DISTINCT device_id) as active_devices'), DB::raw('SUM(authorized_request_count) as authorized_requests'), DB::raw('SUM(network_change_count) as network_changes'))
            ->groupBy('observed_date')->orderBy('observed_date')->get();

        return response()->json([
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'risk_levels' => $levels,
            'signals' => $signals,
            'activity' => $daily,
            'devices' => $devices,
        ]);
    }

    public function releases(): JsonResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_audit', 'app_security_releases');
        $releases = \App\Modules\AppSecurity\Models\SecurityRelease::query()
            ->latest('created_at')->paginate(50, [
                'uuid', 'package_name', 'version_name', 'version_code', 'signing_sha256',
                'apk_sha256', 'apk_size', 'release_notes', 'minimum_supported_version',
                'minimum_android_sdk', 'target_android_sdk', 'release_status', 'is_mandatory',
                'released_at', 'created_at', 'created_by', 'updated_by',
            ]);
        return response()->json($releases);
    }

    public function intelligence(Request $request): JsonResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_audit', 'app_security_devices');
        $filters = $request->validate([
            'device_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $query = DB::table('app_device_intelligence as intelligence')
            ->join('app_registered_devices as devices', 'devices.id', '=', 'intelligence.device_id')
            ->leftJoin('users', 'users.id', '=', 'devices.user_id')
            ->select([
                'intelligence.id', 'devices.uuid as device_uuid', 'devices.user_type',
                'users.name as user_name', 'devices.device_model', 'devices.version_name',
                'intelligence.observed_date', 'intelligence.first_seen_at', 'intelligence.last_seen_at',
                'intelligence.authorized_request_count', 'intelligence.network_change_count',
                'intelligence.version_code', 'intelligence.android_sdk', 'intelligence.observations',
            ]);
        if (isset($filters['device_id'])) $query->where('intelligence.device_id', $filters['device_id']);
        if (isset($filters['from'])) $query->where('intelligence.observed_date', '>=', $filters['from']);
        if (isset($filters['to'])) $query->where('intelligence.observed_date', '<=', $filters['to']);
        return response()->json($query->orderByDesc('intelligence.observed_date')->paginate($filters['per_page'] ?? 50));
    }

    public function showDevice(string $uuid, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): JsonResponse
    {
        $authorization->authorizeAny('app_security_view', 'app_security_customer_support');
        $device = RegisteredDevice::query()->with('user:id,name,username,role_id')
            ->where('uuid', $uuid)->firstOrFail();
        $supportOnly = $authorization->hasPermission('app_security_customer_support')
            && !$authorization->allowsAny('app_security_view', 'app_security_devices');
        $staffApprovalOnly = $authorization->hasPermission('app_security_device_approval')
            && !$authorization->allowsAny('app_security_devices');
        if (($supportOnly && $device->user_type !== 'customer')
            || ($staffApprovalOnly && $device->user_type !== 'staff')) abort(404);
        $device->makeHidden(['public_key']);
        if ($supportOnly) {
            return response()->json(['device' => $device->only([
                'uuid', 'user_type', 'device_status', 'device_model', 'version_name', 'registered_at', 'last_seen_at',
            ]) + ['user' => $device->user ? ['name' => $device->user->name, 'username' => $device->user->username] : null]]);
        }
        return response()->json([
            'device' => $device,
            'sessions' => SecuritySession::query()->where('device_id', $device->id)
                ->latest()->get(['uuid', 'expires_at', 'last_used_at', 'revoked_at', 'revocation_reason']),
            'risk_history' => DB::table('app_device_risk_scores')->where('device_id', $device->id)->latest('calculated_at')->limit(20)->get(),
            'risk_signals' => DB::table('app_device_integrity_checks')->where('device_id', $device->id)->latest('occurred_at')->limit(100)->get(),
            'can_review_risk_signals' => $authorization->allowsAny('app_security_risk_review'),
            'audit_history' => SecurityAuditLog::query()->where('target_type', 'device')->where('target_id', (string) $device->id)->latest('occurred_at')->limit(50)->get(),
        ]);
    }

    public function showDeviceSessions(string $uuid, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): View
    {
        $authorization->authorizeAny('app_security_devices', 'app_security_device_approval');
        $device = RegisteredDevice::query()->with('user:id,name,username')->where('uuid', $uuid)->firstOrFail();
        if (!$authorization->allowsAny('app_security_devices') && $device->user_type !== 'staff') abort(404);
        $sessions = SecuritySession::query()->where('device_id', $device->id)->where('user_id', $device->user_id)
            ->latest()->paginate(30);

        return view('admin.app-security.sessions', compact('device', 'sessions'));
    }

    public function revokeDeviceSession(Request $request, string $deviceUuid, string $sessionUuid, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): RedirectResponse|JsonResponse
    {
        $authorization->authorizeAny('app_security_devices', 'app_security_device_approval');
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'device_session.revoke');
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:512']]);
        $device = RegisteredDevice::query()->where('uuid', $deviceUuid)->firstOrFail();
        if (!$authorization->allowsAny('app_security_devices') && $device->user_type !== 'staff') abort(404);
        $actorId = Auth::guard('admin')->id();

        $changed = DB::transaction(function () use ($device, $sessionUuid, $data, $actorId, $request): bool {
            $session = SecuritySession::query()->where('uuid', $sessionUuid)->where('device_id', $device->id)
                ->where('user_id', $device->user_id)->lockForUpdate()->firstOrFail();
            if ($session->revoked_at !== null) return false;
            $now = now();
            $session->forceFill([
                'revoked_at' => $now,
                'revocation_reason' => 'admin: '.substr($data['reason'], 0, 180),
                'session_token_sha256' => hash('sha256', random_bytes(32)),
                'refresh_token_sha256' => hash('sha256', random_bytes(32)),
            ])->save();

            SecurityAuditLog::query()->create([
                'admin_id' => $actorId, 'actor_type' => 'admin', 'actor_id' => $actorId,
                'action' => 'security.session.revoked', 'target_type' => 'session', 'target_id' => $session->uuid,
                'previous_state' => ['revoked_at' => null], 'new_state' => ['revoked_at' => $now->toIso8601String(), 'device_uuid' => $device->uuid],
                'reason' => $data['reason'], 'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512), 'occurred_at' => $now,
            ]);
            return true;
        }, 3);

        if (!$request->expectsJson()) {
            if ($request->filled('from_index') || !str_contains(url()->previous(), '/sessions')) {
                return redirect()->route('admin.app-security.index', ['view' => 'sessions'])->with('success', $changed ? 'Session revoked and audited.' : 'This session was already revoked.');
            }
            return redirect()->route('admin.app-security.devices.sessions', $device->uuid)->with('success', $changed ? 'Session revoked and audited.' : 'This session was already revoked.');
        }
        return response()->json(['session_id' => $sessionUuid, 'revoked' => true, 'changed' => $changed]);
    }

    public function showRiskSignalReview(string $uuid, \App\Modules\AppSecurity\Services\AdminSecurityAuthorization $authorization): View
    {
        $authorization->authorizeAny('app_security_audit', 'app_security_risk_review');
        $device = RegisteredDevice::query()->with('user:id,name,username,role_id')->where('uuid', $uuid)->firstOrFail();
        $signals = DB::table('app_device_integrity_checks')->where('device_id', $device->id)->latest('occurred_at')->limit(100)->get();
        return view('admin.app-security.device-review', [
            'device' => $device,
            'signals' => $signals,
            'canReviewRiskSignals' => $authorization->allowsAny('app_security_risk_review'),
        ]);
    }

    public function reviewRiskSignal(Request $request, int $signalId): JsonResponse|RedirectResponse
    {
        app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class)->authorizeAny('app_security_risk_review');
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'risk_signal.review');
        $data = $request->validate([
            'disposition' => ['required', 'in:false_positive,confirmed'],
            'reason' => ['required', 'string', 'min:3', 'max:512'],
        ]);
        $adminId = Auth::guard('admin')->id() ?: Auth::id();
        abort_if(!$adminId, 403);
        $result = app(\App\Modules\AppSecurity\Services\RiskSignalReviewService::class)->review(
            $signalId,
            (int) $adminId,
            $data['disposition'],
            $data['reason'],
            $request->ip(),
            $request->userAgent(),
        );
        if (!$request->expectsJson()) {
            $deviceUuid = RegisteredDevice::query()->whereKey($result['device_id'])->value('uuid');
            return redirect()->route('admin.app-security.devices.review', $deviceUuid)
                ->with('success', 'Risk signal review saved and audited.');
        }
        return response()->json($result);
    }

    public function approve(Request $request, string $uuid): JsonResponse
    {
        return $this->transition($request, $uuid, 'approve');
    }

    public function reject(Request $request, string $uuid): JsonResponse
    {
        return $this->transition($request, $uuid, 'reject');
    }

    public function block(Request $request, string $uuid): JsonResponse
    {
        return $this->transition($request, $uuid, 'block');
    }

    public function unblock(Request $request, string $uuid): JsonResponse
    {
        return $this->transition($request, $uuid, 'unblock');
    }

    public function revoke(Request $request, string $uuid): JsonResponse
    {
        return $this->transition($request, $uuid, 'revoke');
    }

    public function requireReregistration(Request $request, string $uuid): JsonResponse
    {
        return $this->transition($request, $uuid, 'require-reregistration');
    }

    private function transition(Request $request, string $uuid, string $action): JsonResponse
    {
        $authorization = app(\App\Modules\AppSecurity\Services\AdminSecurityAuthorization::class);
        $broadDeviceAccess = $authorization->allowsAny('app_security_devices');
        if ($broadDeviceAccess) {
            $authorization->authorizeAny('app_security_devices');
        } else {
            $authorization->authorizeAny('app_security_device_approval');
            abort_unless(in_array($action, ['approve', 'reject'], true), 403, 'Staff approval permission cannot block or revoke devices.');
        }
        app(\App\Modules\AppSecurity\Services\AdminStepUpService::class)->verify($request, 'device.'.$action);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:512']]);
        $actor = Auth::guard('admin')->user() ?: Auth::user();

        $device = DB::transaction(function () use ($uuid, $action, $data, $actor, $request, $broadDeviceAccess): RegisteredDevice {
            $device = RegisteredDevice::query()->where('uuid', $uuid)->lockForUpdate()->firstOrFail();
            if (!$broadDeviceAccess && $device->user_type !== 'staff') {
                abort(403, 'Staff approval permission cannot manage customer devices.');
            }
            $previous = [
                'device_status' => $device->device_status,
                'blocked_at' => $device->blocked_at?->toIso8601String(),
                'revoked_at' => $device->revoked_at?->toIso8601String(),
                'public_key_fingerprint' => $device->public_key_fingerprint,
            ];

            switch ($action) {
                case 'approve':
                    $attestationPolicy = app(\App\Modules\AppSecurity\Services\AttestationOsPolicyService::class)
                        ->forDevice($device);
                    if (!$attestationPolicy['compliant']) {
                        abort(428, \App\Modules\AppSecurity\Services\AttestationOsPolicyService::MESSAGE);
                    }
                    if (in_array($device->device_status, ['revoked', 'rejected'], true) || $device->revoked_at) {
                        abort(409, 'Revoked or rejected devices cannot be approved.');
                    }
                    $device->forceFill([
                        'device_status' => 'approved',
                        'approved_by' => Auth::guard('admin')->id(),
                        'approved_at' => now(),
                        'blocked_at' => null,
                        'pre_block_status' => null,
                    ])->save();
                    break;
                case 'reject':
                    if ($device->device_status !== 'pending') {
                        abort(409, 'Only pending devices can be rejected.');
                    }
                    $device->forceFill(['device_status' => 'rejected'])->save();
                    $this->revokeSessions($device, 'device_rejected');
                    break;
                case 'block':
                    if ($device->revoked_at || $device->device_status === 'revoked') {
                        abort(409, 'Revoked devices cannot be blocked.');
                    }
                    $device->forceFill([
                        'pre_block_status' => $device->device_status,
                        'device_status' => 'blocked',
                        'blocked_at' => now(),
                    ])->save();
                    $this->revokeSessions($device, 'device_blocked');
                    break;
                case 'unblock':
                    if ($device->device_status !== 'blocked' || $device->revoked_at) {
                        abort(409, 'Only blocked, non-revoked devices can be unblocked.');
                    }
                    $device->forceFill([
                        'device_status' => $device->pre_block_status ?: 'approved',
                        'blocked_at' => null,
                        'pre_block_status' => null,
                    ])->save();
                    break;
                case 'revoke':
                    $device->forceFill([
                        'device_status' => 'revoked',
                        'revoked_at' => now(),
                        'blocked_at' => null,
                        'pre_block_status' => null,
                    ])->save();
                    $this->revokeSessions($device, 'device_revoked');
                    break;
                case 'require-reregistration':
                    if ($device->revoked_at || $device->device_status === 'revoked') {
                        abort(409, 'Revoked devices cannot be required to re-register.');
                    }
                    $device->forceFill([
                        'device_status' => 're_registration_required',
                        'blocked_at' => null,
                        'pre_block_status' => null,
                    ])->save();
                    DB::table('app_security_block_rules')->updateOrInsert(
                        ['target_type' => 'key_fingerprint', 'target_value' => strtolower($device->public_key_fingerprint)],
                        ['reason' => 'Key retired by required device re-registration.', 'is_active' => true,
                            'created_by' => Auth::guard('admin')->id(), 'expires_at' => null, 'updated_at' => now(), 'created_at' => now()]
                    );
                    $this->revokeSessions($device, 'device_reregistration_required');
                    break;
            }

            SecurityAuditLog::query()->create([
                'admin_id' => Auth::guard('admin')->id(),
                'actor_type' => Auth::guard('admin')->check() ? 'admin' : 'user',
                'actor_id' => $actor?->getAuthIdentifier(),
                'action' => 'device.' . $action,
                'target_type' => 'device',
                'target_id' => (string) $device->id,
                'previous_state' => $previous,
                'new_state' => [
                    'device_status' => $device->device_status,
                    'blocked_at' => $device->blocked_at?->toIso8601String(),
                    'pre_block_status' => $device->pre_block_status,
                    'revoked_at' => $device->revoked_at?->toIso8601String(),
                ],
                'reason' => $data['reason'],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
                'occurred_at' => now(),
            ]);
            return $device->refresh();
        }, 3);

        $alertAction = match ($action) {
            'approve' => ['device_approved', 'low', 'Device approved'],
            'reject' => ['device_rejected', 'medium', 'Device rejected'],
            'block' => ['device_blocked', 'high', 'Device blocked'],
            'unblock' => ['device_unblocked', 'medium', 'Device unblocked'],
            'revoke' => ['device_revoked', 'high', 'Device revoked'],
            'require-reregistration' => ['device_reregistration_required', 'high', 'Device key retired'],
        };
        app(\App\Modules\AppSecurity\Services\SecurityAlertService::class)->record(
            $alertAction[0].':'.$device->uuid.':'.(string) $device->updated_at,
            $alertAction[0], $alertAction[1], $alertAction[2],
            "Installation {$device->uuid} was {$action} by an administrator. Review its audit history.",
            $device->id, $device->user_id,
            ['device_status' => $device->device_status, 'actor_id' => $actor?->getAuthIdentifier()]
        );

        if (in_array($device->user_type, ['staff', 'customer'], true)) {
            [$title, $message] = match ($action) {
                'approve' => ['App installation approved', 'Your KNEAYERNG installation is approved. Open the app to continue.'],
                'reject' => ['App installation not approved', 'Your KNEAYERNG installation was not approved. Contact support if you need help.'],
                'block' => ['App access restricted', 'Access from a KNEAYERNG installation was restricted. Contact support if you need help.'],
                'unblock' => ['App access restored', 'Access from your KNEAYERNG installation is restored. Open the app and sign in.'],
                'revoke' => ['App installation revoked', 'A KNEAYERNG installation was revoked. Sign in again to register this device.'],
                'require-reregistration' => ['Device security update required', 'Open KNEAYERNG to register a new secure device key.'],
            };
            SendAppSecurityUserNotification::dispatch(
                (int) $device->user_id,
                'app-security:device-action:'.(string) \Illuminate\Support\Str::uuid(),
                $title,
                $message,
            )->afterCommit();
        }

        if (!$request->expectsJson()) {
            return redirect()->route('admin.app-security.index')->with('success', 'Device action recorded.');
        }
        return response()->json(['device' => $device->makeHidden(['public_key']), 'action' => $action]);
    }

    private function revokeSessions(RegisteredDevice $device, string $reason): void
    {
        SecuritySession::query()->where('device_id', $device->id)->whereNull('revoked_at')->update([
            'revoked_at' => now(),
            'revocation_reason' => $reason,
            'session_token_sha256' => hash('sha256', random_bytes(32)),
            'refresh_token_sha256' => hash('sha256', random_bytes(32)),
            'updated_at' => now(),
        ]);
    }
}
