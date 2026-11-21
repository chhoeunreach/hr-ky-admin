@extends('layouts.master')
@section('title', 'App Security')

@section('styles')
<style>
    .security-wrap { color: #1f2937; }
    .security-hero { border-radius: 18px; padding: 24px; color: #fff; background: linear-gradient(120deg, #102a56, #155e9b); }
    .security-card { border: 1px solid #e5eaf1; border-radius: 14px; background: #fff; padding: 18px; height: 100%; }
    .security-count { font-size: 28px; font-weight: 800; line-height: 1.1; }
    .security-label { color: #6b7280; font-size: 12px; margin-top: 6px; }
    .security-table th { white-space: nowrap; font-size: 12px; text-transform: uppercase; color: #64748b; }
    .security-table td { vertical-align: middle; }
    .security-actions { display: flex; flex-wrap: wrap; gap: 6px; min-width: 250px; }
    .security-actions form { display: inline-flex; gap: 4px; }
    .security-actions input { width: 130px; font-size: 11px; }
    .security-code { font-family: monospace; font-size: 11px; }
</style>
@endsection

@section('main-content')
<div class="container-fluid security-wrap py-3">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('danger'))
        <div class="alert alert-danger">{{ session('danger') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="security-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="small text-uppercase opacity-75">KNEAYERNG · Security Command Center</div>
            <h2 class="mb-1 text-white">App Security</h2>
            <div class="small">Device approvals, revocation, and release status</div>
        </div>
        <div class="text-end">
            <a class="btn btn-sm btn-outline-light me-2" href="{{ route('admin.showQR') }}" target="_blank" rel="noopener">{{ __('app_settings.pair_mobile_app') }}</a>
            <span class="badge {{ $securityEnabled ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2">
                {{ $securityEnabled ? 'Enforcement enabled' : 'Monitoring setup · enforcement disabled' }}
            </span>
            @unless($supportOnly)<div class="small mt-2">{{ $release ? 'Active release '.$release->version_name.' ('.$release->version_code.')' : 'No active registered release' }}</div>@endunless
            @if($canExportAuditLogs)
                <form method="get" action="{{ route('admin.app-security.logs.export') }}" class="d-flex flex-wrap align-items-center justify-content-end gap-2 mt-2">
                    <label class="small" for="audit-export-from">From</label><input id="audit-export-from" class="form-control form-control-sm" type="date" name="from" value="{{ now()->subDays(90)->toDateString() }}" required style="width:145px">
                    <label class="small" for="audit-export-to">To</label><input id="audit-export-to" class="form-control form-control-sm" type="date" name="to" value="{{ now()->toDateString() }}" required style="width:145px">
                    <button class="btn btn-sm btn-outline-light" type="submit">Export audit CSV</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($supportOnly ? [['Pending', $counts['pending']], ['Approved', $counts['approved']], ['Blocked', $counts['blocked']], ['Revoked', $counts['revoked']]] : [['Pending', $counts['pending']], ['Approved', $counts['approved']], ['Blocked', $counts['blocked']], ['Revoked', $counts['revoked']], ['High risk', $counts['high_risk']]] as [$label, $value])
            <div class="col-6 col-xl"><div class="security-card"><div class="security-count">{{ number_format($value) }}</div><div class="security-label">{{ $label }} installations</div></div></div>
        @endforeach
    </div>

    @include('admin.app-security.partials.analytics')

    @if(!$supportOnly && $canManageSettings)
    <div class="card border-0 shadow-sm mb-4" id="security-policy-settings">
        <div class="card-header bg-white"><h5 class="mb-0">Security policy settings</h5></div>
        <div class="card-body">
            <p class="small text-muted">Changes apply to new challenges, sessions, registrations, and subsequent risk calculations. The deployment enforcement switch stays controlled by server environment configuration.</p>
            <form method="post" action="{{ route('admin.app-security.settings.update') }}">
                @csrf
                <div class="row g-3">
                    @foreach(['device_approval_required' => 'Require staff device approval', 'customer_auto_approval' => 'Auto-approve low-risk customer devices', 'risk_enabled' => 'Enable risk scoring'] as $key => $label)
                        <div class="col-md-4">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" @checked($settings[$key])><span class="form-check-label">{{ $label }}</span></label>
                        </div>
                    @endforeach
                    <div class="col-md-4"><label class="form-label">Attestation policy</label><select class="form-select" name="hardware_attestation"><option value="preferred" @selected($settings['hardware_attestation'] === 'preferred')>Preferred; unsupported or unverifiable devices remain reviewable</option><option value="required" @selected($settings['hardware_attestation'] === 'required')>Required; reject registration unless verified</option></select></div>
                    @foreach(['max_devices_staff' => 'Staff devices per account (0 unlimited)', 'max_devices_customer' => 'Customer devices per account (0 unlimited)', 'max_devices_admin' => 'Admin devices per account (0 unlimited)', 'heartbeat_seconds' => 'Heartbeat seconds', 'session_ttl_seconds' => 'Session lifetime (seconds)', 'max_sessions_per_device' => 'Concurrent sessions per device', 'offline_grace_seconds' => 'Offline lease grace (seconds; 0 disables)', 'refresh_ttl_seconds' => 'Refresh lifetime (seconds)', 'challenge_ttl_seconds' => 'Challenge lifetime (seconds)', 'nonce_ttl_seconds' => 'Nonce lifetime (seconds)', 'risk_window_hours' => 'Risk window (hours)', 'risk_review_threshold' => 'Review threshold', 'risk_block_threshold' => 'High-risk threshold'] as $key => $label)
                        <div class="col-6 col-md-3"><label class="form-label" for="{{ $key }}">{{ $label }}</label><input id="{{ $key }}" class="form-control" type="number" name="{{ $key }}" value="{{ $settings[$key] }}" required></div>
                    @endforeach
                    <div class="col-6 col-md-3"><label class="form-label" for="risk_policy_version">Risk policy version</label><input id="risk_policy_version" class="form-control" name="risk_policy_version" maxlength="40" value="{{ $settings['risk_policy_version'] }}" required></div>
                    @foreach(['risk_points_root_detected' => 'Root indicator points', 'risk_points_emulator_detected' => 'Emulator indicator points', 'risk_points_instrumentation_detected' => 'Instrumentation points', 'risk_points_debug_build' => 'Debug build points', 'risk_points_debugger_attached' => 'Debugger attached points', 'risk_points_hooking_detected' => 'Runtime hooking points', 'risk_points_vpn_detected' => 'VPN indicator points', 'risk_points_attestation_missing' => 'Missing attestation points', 'risk_points_attestation_verification_failed' => 'Failed attestation points', 'risk_points_attestation_os_policy_failed' => 'OS/patch policy failure points', 'risk_points_request_nonce_replayed' => 'Replay points', 'risk_points_security_session_invalid' => 'Invalid session points', 'risk_points_request_timestamp_expired' => 'Expired timestamp points', 'risk_points_device_not_authorized' => 'Unauthorized device points', 'risk_points_registration_velocity' => 'Registration velocity points', 'risk_points_cross_account_key_reuse' => 'Cross-account key reuse points'] as $key => $label)
                        <div class="col-6 col-md-3"><label class="form-label" for="{{ $key }}">{{ $label }}</label><input id="{{ $key }}" class="form-control" type="number" min="0" max="100" name="{{ $key }}" value="{{ $settings[$key] }}" required></div>
                    @endforeach
                    <div class="col-md-4"><label class="form-label" for="settings-step-up-password">Confirm your admin password</label><input id="settings-step-up-password" type="password" name="step_up_password" class="form-control" autocomplete="current-password" required></div>
                    <div class="col-12"><button class="btn btn-primary">Save and audit settings</button><span class="small text-muted ms-2">Thresholds and time values are range-validated.</span></div>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(!$supportOnly && $canAssignAdminRoles)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">Admin security roles</h5></div>
        <div class="card-body">
            <p class="small text-muted">Assign the minimum App Security access each administrator needs. Every change requires a reason and is audited.</p>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                <thead><tr><th>Administrator</th><th>Current role</th><th>New role</th><th>Reason</th><th></th></tr></thead><tbody>
                @forelse($adminUsers as $admin)
                    <tr><td>{{ $admin->name }} <small class="text-muted">{{ $admin->email ?: $admin->username }}</small></td>
                        <td>{{ $adminRoleOptions[$admin->security_role] ?? 'Unassigned' }}</td>
                        <td colspan="3">
                            <form method="post" action="{{ route('admin.app-security.admins.role.update', $admin->id) }}" class="d-flex flex-wrap gap-2">
                                @csrf
                                <select name="security_role" class="form-select form-select-sm" style="max-width:220px" required>
                                    @foreach($adminRoleOptions as $roleKey => $roleLabel)
                                        <option value="{{ $roleKey }}" @selected(($admin->security_role ?? '') === $roleKey)>{{ $roleLabel }}</option>
                                    @endforeach
                                </select>
                                <input name="reason" class="form-control form-control-sm" style="max-width:250px" maxlength="500" placeholder="Reason for assignment" required>
                                <input name="step_up_password" type="password" class="form-control form-control-sm" style="max-width:190px" autocomplete="current-password" placeholder="Admin password" required>
                                <button class="btn btn-sm btn-outline-primary">Save role</button>
                            </form>
                        </td></tr>
                @empty<tr><td colspan="5" class="text-muted">No administrators are available.</td>@endforelse
                </tbody></table></div>
        </div>
    </div>
    @endif

    @unless($supportOnly)
    <div class="row g-3 mb-4">
        @if($canManageReleases)
        <div class="col-xl-5">
            <div class="security-card">
                <h5>Register APK release</h5>
                <p class="small text-muted">APK metadata, certificate and checksum are extracted by Android SDK tools. Uploads stay in private storage until published.</p>
                <form method="post" enctype="multipart/form-data" action="{{ route('admin.app-security.releases.store') }}">
                    @csrf
                    <label class="form-label">Signed APK</label><input type="file" name="apk" class="form-control mb-2" accept=".apk" required>
                    <label class="form-label">Release notes</label><textarea name="release_notes" class="form-control mb-2" rows="3" maxlength="10000"></textarea>
                    <label class="form-label">Minimum supported version code</label><input name="minimum_supported_version" type="number" min="0" value="0" class="form-control mb-2" required>
                    <label class="form-check mb-3"><input type="checkbox" class="form-check-input" name="is_mandatory" value="1"><span class="form-check-label">Require update for older builds</span></label>
                    <button class="btn btn-primary">Verify and save draft</button>
                </form>
            </div>
        </div>
        @endif
        <div class="{{ $canManageReleases ? 'col-xl-7' : 'col-12' }}">
            <div class="security-card"><h5>Release history</h5>
                <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Version</th><th>SHA-256</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                @forelse($releases as $item)
                    <tr><td>{{ $item->version_name }} ({{ $item->version_code }})</td><td class="security-code">{{ substr($item->apk_sha256, 0, 16) }}…</td><td>{{ ucfirst($item->release_status) }}</td><td>
                    @if($canManageReleases && in_array($item->release_status, ['draft', 'testing', 'suspended']))
                    <form method="post" action="{{ route('admin.app-security.releases.activate', $item->uuid) }}" class="d-flex flex-wrap gap-1">@csrf<input required name="reason" class="form-control form-control-sm" placeholder="Reason"><input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password"><button class="btn btn-sm btn-success">Activate</button></form>
                    @endif
                    @if($canManageReleases && $item->release_status !== 'revoked')<form method="post" action="{{ route('admin.app-security.releases.revoke', $item->uuid) }}" class="d-flex flex-wrap gap-1 mt-1">@csrf<input required name="reason" class="form-control form-control-sm" placeholder="Reason"><input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password"><button class="btn btn-sm btn-outline-danger">Revoke</button></form>@endif
                    </td></tr>
                @empty<tr><td colspan="4" class="text-muted">No APK releases registered yet.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </div>
    </div>
    @endunless

    @if($canViewSecurityAlerts)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between"><h5 class="mb-0">Security alerts</h5><span class="badge bg-danger">{{ $securityAlerts->whereNull('acknowledged_at')->count() }} open</span></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>Alert</th><th>Severity</th><th>Created</th><th>Status / action</th></tr></thead><tbody>
            @forelse($securityAlerts as $alert)
                <tr><td><strong>{{ $alert->title }}</strong><div class="small text-muted">{{ $alert->message }}</div></td>
                    <td><span class="badge bg-warning text-dark">{{ ucfirst($alert->severity) }}</span></td>
                    <td class="small">{{ \Illuminate\Support\Carbon::parse($alert->created_at)->diffForHumans() }}</td>
                    <td>@if($alert->acknowledged_at)<span class="badge bg-success">Acknowledged</span><div class="small text-muted">{{ $alert->acknowledgement_reason }}</div>
                        @elseif($canAcknowledgeSecurityAlerts)<form method="post" action="{{ route('admin.app-security.alerts.acknowledge', $alert->id) }}" class="d-flex gap-1">@csrf<input name="reason" class="form-control form-control-sm" maxlength="512" placeholder="Review note" required><button class="btn btn-sm btn-outline-primary">Acknowledge</button></form>
                        @else<span class="badge bg-danger">Open</span>@endif</td></tr>
            @empty<tr><td colspan="4" class="text-center text-muted py-3">No security alerts.</td></tr>@endforelse
            </tbody></table></div>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
                <strong class="me-auto">Registered devices</strong>
                <input name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="max-width:260px" placeholder="Search user, model, or device ID">
                <select name="status" class="form-select form-select-sm" style="max-width:160px">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'approved', 'blocked', 'rejected', 'revoked', 're_registration_required'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status === 're_registration_required' ? 'Re-registration required' : ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm">Filter</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover security-table mb-0">
                <thead><tr><th>User</th><th>Type</th>@unless($supportOnly)<th>Device</th><th>Release</th><th>Trust</th>@endunless<th>Status</th><th>Last seen</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($devices as $device)
                    <tr>
                        <td><div class="fw-semibold">{{ $device->user?->name ?? 'Deleted account' }}</div><small class="text-muted">{{ $device->user?->username }}</small></td>
                        <td>{{ ucfirst($device->user_type) }}</td>
                        @unless($supportOnly)<td><div>{{ $device->device_model ?: 'Android device' }}</div><small class="text-muted">SDK {{ $device->android_sdk ?: 'unknown' }}</small></td>
                        <td><div>{{ $device->version_name }} ({{ $device->version_code }})</div><small class="security-code text-muted">{{ substr($device->signing_sha256, 0, 16) }}…</small></td>
                        <td>
                            <span class="badge bg-light text-dark">{{ ucfirst($device->attestation_level) }}</span>
                            <div class="small text-muted">Risk {{ $device->risk_score }}/100</div>
                            @foreach(array_slice(data_get($device->latestRiskScore?->reasons, 'signals', []), 0, 2) as $riskReason)
                                <div class="small text-muted">{{ \Illuminate\Support\Str::headline($riskReason['signal'] ?? 'signal') }} +{{ $riskReason['points'] ?? 0 }}</div>
                            @endforeach
                            @php($attestation = $device->attestations->sortByDesc('created_at')->first())
                            @if($attestation)
                                <div class="small">Attestation: {{ ucfirst($attestation->verification_status) }}</div>
                                @if(data_get($attestation->verification_summary, 'reason'))
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::headline(data_get($attestation->verification_summary, 'reason')) }}</div>
                                @endif
                            @endif
                        </td>@endunless
                        <td><span class="badge {{ $device->device_status === 'blocked' || $device->blocked_at ? 'bg-danger' : ($device->device_status === 'approved' ? 'bg-success' : 'bg-secondary') }}">{{ $device->device_status === 'blocked' || $device->blocked_at ? 'blocked' : $device->device_status }}</span></td>
                        <td class="small">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                        <td>
                            <div class="security-actions">
                                @if(!$supportOnly && ($canViewSecurityAlerts || $canReviewRiskSignals))
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.app-security.devices.review', $device->uuid) }}">Risk review</a>
                                @endif
                                @if(!$supportOnly && ($canManageDevices || ($canApproveStaffDevices && $device->user_type === 'staff')))
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.app-security.devices.sessions', $device->uuid) }}">Sessions</a>
                                @endif
                                @if($canManageDevices && $device->device_status === 'pending')
                                    @foreach(['approve' => 'Approve', 'reject' => 'Reject'] as $action => $label)
                                        <form method="post" action="{{ route('admin.app-security.devices.'.$action, $device->uuid) }}">
                                            @csrf
                                            <input required name="reason" class="form-control form-control-sm" placeholder="Reason">
                                            <input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password">
                                            <button class="btn btn-sm {{ $action === 'approve' ? 'btn-success' : 'btn-outline-danger' }}">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                @elseif($canApproveStaffDevices && $device->user_type === 'staff' && $device->device_status === 'pending')
                                    @foreach(['approve' => 'Approve', 'reject' => 'Reject'] as $action => $label)
                                        <form method="post" action="{{ route('admin.app-security.devices.'.$action, $device->uuid) }}">
                                            @csrf
                                            <input required name="reason" class="form-control form-control-sm" placeholder="Reason">
                                            <input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password">
                                            <button class="btn btn-sm {{ $action === 'approve' ? 'btn-success' : 'btn-outline-danger' }}">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                @elseif($canManageDevices && $device->blocked_at)
                                    <form method="post" action="{{ route('admin.app-security.devices.unblock', $device->uuid) }}">
                                        @csrf<input required name="reason" class="form-control form-control-sm" placeholder="Reason">
                                        <input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password">
                                        <button class="btn btn-sm btn-warning">Unblock</button>
                                    </form>
                                @elseif($canManageDevices && $device->device_status === 'approved' && !$device->revoked_at)
                                    <form method="post" action="{{ route('admin.app-security.devices.block', $device->uuid) }}">
                                        @csrf<input required name="reason" class="form-control form-control-sm" placeholder="Reason">
                                        <input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password">
                                        <button class="btn btn-sm btn-outline-danger">Block</button>
                                    </form>
                                @endif
                                @if($canManageDevices && !$device->revoked_at && $device->device_status !== 're_registration_required')
                                    <form method="post" action="{{ route('admin.app-security.devices.requireReregistration', $device->uuid) }}" onsubmit="return confirm('Retire this key and require a new device registration?')">
                                        @csrf<input required name="reason" class="form-control form-control-sm" placeholder="Reason">
                                        <input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password">
                                        <button class="btn btn-sm btn-warning">Require re-registration</button>
                                    </form>
                                @endif
                                @if($canManageDevices && !$device->revoked_at)
                                    <form method="post" action="{{ route('admin.app-security.devices.revoke', $device->uuid) }}" onsubmit="return confirm('Revoke this installation and its sessions?')">
                                        @csrf<input required name="reason" class="form-control form-control-sm" placeholder="Reason">
                                        <input required type="password" name="step_up_password" class="form-control form-control-sm" autocomplete="current-password" placeholder="Admin password">
                                        <button class="btn btn-sm btn-danger">Revoke</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $supportOnly ? 5 : 8 }}" class="text-center text-muted py-5">No registered devices match this filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $devices->links() }}</div>
    </div>
</div>
@endsection
