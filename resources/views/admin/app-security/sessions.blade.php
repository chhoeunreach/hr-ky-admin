@extends('layouts.master')
@section('title', __('app_security.device_sessions'))

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/app-security.css') }}?v={{ filemtime(public_path('assets/css/app-security.css')) }}">
@endsection

@section('main-content')
<div class="container-fluid security-wrap py-3">

    {{-- Breadcrumb & Back navigation --}}
    <div class="mb-3">
        <a href="{{ route('admin.app-security.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('app_security.back_to_security') }}
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-shield-xmark fs-5 text-danger"></i>
                <div class="fw-semibold">{{ $errors->first() }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Device Header Card --}}
    <div class="security-panel-card mb-4">
        <div class="panel-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="device-user-avatar fs-4" style="width: 52px; height: 52px;">
                        {{ strtoupper(substr($device->user?->name ?? 'D', 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="mb-0 fw-bold text-dark">{{ __('app_security.device_sessions') }}</h3>
                            @php
                                $statusClass = match($device->device_status) {
                                    'approved' => 'status-approved',
                                    'blocked' => 'status-blocked',
                                    'revoked' => 'status-revoked',
                                    're_registration_required' => 'status-re_registration_required',
                                    default => 'status-pending'
                                };
                            @endphp
                            <span class="device-status-badge {{ $statusClass }}">
                                {{ str_replace('_', ' ', $device->device_status) }}
                            </span>
                        </div>
                        <div class="text-muted small mt-1">
                            <strong class="text-dark">{{ $device->user?->name ?? 'Deleted account' }}</strong>
                            ({{ $device->user?->username ?? '—' }}) ·
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">{{ ucfirst($device->user_type) }}</span> ·
                            <i class="fa-solid fa-mobile-screen ms-1 me-1"></i> {{ $device->device_model ?: 'Android device' }}
                        </div>
                    </div>
                </div>

                {{-- Session stats pill --}}
                <div class="bg-light p-3 rounded-4 border d-flex align-items-center gap-3">
                    <i class="fa-solid fa-network-wired fs-4 text-primary"></i>
                    <div>
                        <div class="small text-muted fw-semibold text-uppercase">Recorded Sessions</div>
                        <div class="fs-5 fw-bold text-dark">{{ $sessions->total() }} lifetime</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sessions Table Card --}}
    <div class="security-panel-card">
        <div class="panel-header">
            <h5 class="panel-title">
                <i class="fa-solid fa-key text-primary"></i>
                <span>Cryptographic Token Sessions</span>
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table security-table align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app_security.session_token') }}</th>
                        <th>Created</th>
                        <th>{{ __('app_security.last_used') }}</th>
                        <th>{{ __('app_security.expires_at') }}</th>
                        <th>{{ __('app_security.refresh_expires_at') }}</th>
                        <th>{{ __('app_security.status') }}</th>
                        <th class="text-end">{{ __('app_security.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($sessions as $session)
                    @php $active = !$session->revoked_at && $session->expires_at?->isFuture() && $session->refresh_expires_at?->isFuture(); @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <code class="small text-dark fw-bold">{{ substr($session->uuid, 0, 18) }}…</code>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-2" title="Copy UUID" onclick="navigator.clipboard.writeText('{{ $session->uuid }}'); this.innerHTML='<i class=\'fa-solid fa-check text-success\'></i>'; setTimeout(() => this.innerHTML='<i class=\'fa-regular fa-copy\'></i>', 1500)">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td class="small text-muted">
                            {{ $session->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="small">
                            @if($session->last_used_at)
                                <span class="fw-semibold text-dark">{{ $session->last_used_at->diffForHumans() }}</span>
                                <div class="text-muted" style="font-size: 11px;">{{ $session->last_used_at->format('H:i:s') }}</div>
                            @else
                                <span class="text-muted fst-italic">Never</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($session->expires_at)
                                <span class="{{ $session->expires_at->isPast() ? 'text-muted' : 'text-success fw-semibold' }}">
                                    {{ $session->expires_at->diffForHumans() }}
                                </span>
                                <div class="text-muted" style="font-size: 11px;">{{ $session->expires_at->format('Y-m-d H:i') }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $session->refresh_expires_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td>
                            @if($session->revoked_at)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                    Revoked
                                </span>
                                @if($session->revocation_reason)
                                    <div class="small text-muted mt-1 fst-italic" style="font-size: 11px;">{{ $session->revocation_reason }}</div>
                                @endif
                            @elseif($active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                    <i class="fa-solid fa-circle text-success me-1" style="font-size: 7px;"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill">
                                    Expired
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($active)
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm"
                                    onclick="openRevokeSessionModal('{{ route('admin.app-security.devices.sessions.revoke', [$device->uuid, $session->uuid]) }}', '{{ $session->uuid }}')">
                                    <i class="fa-solid fa-ban me-1"></i> {{ __('app_security.revoke_session') }}
                                </button>
                            @else
                                <span class="text-muted small">No action</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-network-wired fs-1 text-muted opacity-50 mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark">No Active Sessions</h5>
                            <p class="text-muted small mb-0">No active cryptographic sessions recorded for this device.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="panel-header bg-white border-top border-bottom-0">
                <div class="small text-muted">Showing {{ $sessions->firstItem() }} to {{ $sessions->lastItem() }} of {{ $sessions->total() }} sessions</div>
                <div>{{ $sessions->links() }}</div>
            </div>
        @endif

        <div class="panel-header bg-light border-top border-bottom-0">
            <div class="small text-muted">
                <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                Session revocation takes effect immediately on the next protected API request. Disconnected devices are barred upon their next reconnection attempt.
            </div>
        </div>
    </div>

</div>

{{-- Revoke Session Modal --}}
<div class="modal fade" id="revokeSessionModal" tabindex="-1" aria-labelledby="revokeSessionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="revokeSessionForm" method="post" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="revokeSessionModalTitle">
                        <i class="fa-solid fa-ban me-2"></i> {{ __('app_security.revoke_session') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="small text-muted fw-semibold text-uppercase">Target Session UUID</div>
                        <code class="text-dark fw-bold small" id="modalSessionUuid">UUID</code>
                    </div>

                    <div class="alert alert-warning border-0 d-flex align-items-center gap-2 py-2 small mb-3">
                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                        Revoking this session will invalidate its token SHA-256 digest and force the user to re-authenticate.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">{{ __('app_security.reason') }} <span class="text-danger">*</span></label>
                        <textarea name="reason" id="modalRevokeReason" class="form-control" rows="2" minlength="3" maxlength="512" placeholder="{{ __('app_security.reason_placeholder') }}" required></textarea>
                        <div class="preset-chips-wrap mt-2">
                            <span class="preset-chip" onclick="document.getElementById('modalRevokeReason').value='Administrative session termination'">Admin termination</span>
                            <span class="preset-chip" onclick="document.getElementById('modalRevokeReason').value='Suspicious token replay or rotation anomaly'">Token anomaly</span>
                            <span class="preset-chip" onclick="document.getElementById('modalRevokeReason').value='Routine security logout'">Routine logout</span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark mb-1">{{ __('app_security.admin_password') }} <span class="text-danger">*</span></label>
                        <input type="password" name="step_up_password" class="form-control" autocomplete="current-password" placeholder="{{ __('app_security.admin_password_placeholder') }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">{{ __('app_security.cancel') }}</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-ban me-1"></i> Revoke Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openRevokeSessionModal(actionUrl, sessionUuid) {
        const modalEl = document.getElementById('revokeSessionModal');
        const form = document.getElementById('revokeSessionForm');
        const uuidEl = document.getElementById('modalSessionUuid');

        if (!modalEl || !form) return;

        form.action = actionUrl;
        uuidEl.textContent = sessionUuid;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endsection
