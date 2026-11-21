@extends('layouts.master')
@section('title', __('app_security.device_review'))

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
                            <h3 class="mb-0 fw-bold text-dark">{{ __('app_security.device_review') }}</h3>
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
                            <i class="fa-solid fa-mobile-screen ms-1 me-1"></i> {{ $device->device_model ?: 'Android device' }} ·
                            <span class="font-monospace small">{{ $device->uuid }}</span>
                        </div>
                    </div>
                </div>

                {{-- Risk Score Meter Pill --}}
                <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-4 border">
                    <div>
                        <div class="small text-muted fw-semibold text-uppercase">Composite Risk</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            @php
                                $score = (int) $device->risk_score;
                                $scoreCls = $score >= 60 ? 'text-danger' : ($score >= 25 ? 'text-warning' : 'text-success');
                                $barCls = $score >= 60 ? 'risk-high' : ($score >= 25 ? 'risk-medium' : 'risk-low');
                            @endphp
                            <span class="fs-4 fw-bold {{ $scoreCls }}">{{ $score }}/100</span>
                            <div class="risk-meter-bar" style="width: 80px; height: 8px;">
                                <div class="risk-meter-fill {{ $barCls }}" style="width: {{ max(6, $score) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Signals Table Card --}}
    <div class="security-panel-card">
        <div class="panel-header">
            <h5 class="panel-title">
                <i class="fa-solid fa-shield-virus text-danger"></i>
                <span>Recorded Integrity & Threat Signals</span>
            </h5>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">
                {{ $signals->count() }} signals observed
            </span>
        </div>

        <div class="table-responsive">
            <table class="table security-table align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app_security.signal') }}</th>
                        <th>{{ __('app_security.severity') }}</th>
                        <th>{{ __('app_security.points') }}</th>
                        <th>Observed Timestamp</th>
                        <th>Review Status</th>
                        <th class="text-end">{{ __('app_security.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($signals as $signal)
                    @php $context = json_decode((string) $signal->context, true) ?: []; @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ \Illuminate\Support\Str::headline($signal->signal) }}</div>
                            @if(!empty($context))
                                <div class="font-monospace small text-muted bg-light p-1 rounded-2 mt-1" style="max-width: 450px; font-size: 11px;">
                                    {{ \Illuminate\Support\Str::limit(json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 140) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $sevCls = match(strtolower($signal->severity)) {
                                    'critical' => 'bg-danger text-white',
                                    'high' => 'bg-warning text-dark',
                                    'medium' => 'bg-info text-dark',
                                    default => 'bg-light text-secondary border'
                                };
                            @endphp
                            <span class="badge {{ $sevCls }} px-2 py-1 rounded-pill">
                                {{ ucfirst($signal->severity) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                +{{ $signal->risk_points }} pts
                            </span>
                        </td>
                        <td class="small text-muted">
                            <i class="fa-regular fa-clock me-1"></i> {{ $signal->occurred_at }}
                        </td>
                        <td>
                            @if($signal->review_status)
                                @php
                                    $dispBadge = $signal->review_status === 'false_positive' ? 'bg-info bg-opacity-10 text-info border border-info border-opacity-25' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                @endphp
                                <span class="badge {{ $dispBadge }} px-2 py-1 rounded-pill fw-bold">
                                    <i class="fa-solid fa-check me-1"></i> {{ \Illuminate\Support\Str::headline($signal->review_status) }}
                                </span>
                                <div class="small text-muted mt-1">Admin #{{ $signal->reviewed_by }} · {{ $signal->reviewed_at }}</div>
                                <div class="small text-dark mt-1 fst-italic">"{{ $signal->review_reason }}"</div>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill">
                                    Needs Review
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($canReviewRiskSignals && !$signal->review_status)
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                    onclick="openSignalReviewModal('{{ route('admin.app-security.risk-signals.review', $signal->id) }}', '{{ \Illuminate\Support\Str::headline($signal->signal) }}', '{{ $signal->severity }}', {{ $signal->risk_points }})">
                                    <i class="fa-solid fa-gavel me-1"></i> Review Signal
                                </button>
                            @else
                                <span class="text-muted small">{{ $signal->review_status ? 'Audited' : 'Read only' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fa-solid fa-shield-check fs-1 text-success opacity-50 mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark">Clean Device Record</h5>
                            <p class="text-muted small mb-0">No suspicious risk heuristics or integrity signals recorded for this device.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-header bg-light border-top border-bottom-0">
            <div class="small text-muted">
                <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                Marking a non-critical signal as <strong>False Positive</strong> excludes its penalty from future rolling risk calculations. Critical cryptographic signals cannot be dismissed.
            </div>
        </div>
    </div>

</div>

{{-- Signal Review Modal --}}
@if($canReviewRiskSignals)
<div class="modal fade" id="signalReviewModal" tabindex="-1" aria-labelledby="signalReviewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="signalReviewForm" method="post" action="">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="signalReviewModalTitle">
                        <i class="fa-solid fa-gavel text-primary me-2"></i> Review Threat Signal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Signal Summary --}}
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark fs-6" id="modalSignalName">Signal Name</strong>
                            <span class="badge bg-danger rounded-pill" id="modalSignalPoints">+0 pts</span>
                        </div>
                        <div class="small text-muted">Severity: <span id="modalSignalSeverity" class="fw-semibold">High</span></div>
                    </div>

                    {{-- Disposition --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">{{ __('app_security.disposition') }} <span class="text-danger">*</span></label>
                        <select name="disposition" id="modalSignalDisposition" class="form-select" required>
                            <option value="false_positive">{{ __('app_security.false_positive') }}</option>
                            <option value="confirmed">{{ __('app_security.confirmed_signal') }}</option>
                        </select>
                    </div>

                    {{-- Reason --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">{{ __('app_security.reason') }} <span class="text-danger">*</span></label>
                        <textarea name="reason" id="modalSignalReason" class="form-control" rows="2" minlength="3" maxlength="512" placeholder="{{ __('app_security.reason_placeholder') }}" required></textarea>
                        <div class="preset-chips-wrap mt-2">
                            <span class="preset-chip" onclick="document.getElementById('modalSignalReason').value='Verified internal testing environment'">Verified testing environment</span>
                            <span class="preset-chip" onclick="document.getElementById('modalSignalReason').value='Known corporate VPN profile'">Corporate VPN profile</span>
                            <span class="preset-chip" onclick="document.getElementById('modalSignalReason').value='Confirmed malicious activity'">Confirmed threat</span>
                        </div>
                    </div>

                    {{-- Admin Password --}}
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark mb-1">{{ __('app_security.admin_password') }} <span class="text-danger">*</span></label>
                        <input type="password" name="step_up_password" class="form-control" autocomplete="current-password" placeholder="{{ __('app_security.admin_password_placeholder') }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">{{ __('app_security.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('app_security.save_signal_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    function openSignalReviewModal(actionUrl, signalName, severity, points) {
        const modalEl = document.getElementById('signalReviewModal');
        const form = document.getElementById('signalReviewForm');
        const nameEl = document.getElementById('modalSignalName');
        const pointsEl = document.getElementById('modalSignalPoints');
        const sevEl = document.getElementById('modalSignalSeverity');
        const dispSelect = document.getElementById('modalSignalDisposition');

        if (!modalEl || !form) return;

        form.action = actionUrl;
        nameEl.textContent = signalName;
        pointsEl.textContent = '+' + points + ' pts';
        sevEl.textContent = severity.charAt(0).toUpperCase() + severity.slice(1);

        // If critical, disable false_positive option
        if (dispSelect) {
            const fpOpt = dispSelect.querySelector('option[value="false_positive"]');
            if (severity.toLowerCase() === 'critical') {
                if (fpOpt) fpOpt.disabled = true;
                dispSelect.value = 'confirmed';
            } else {
                if (fpOpt) fpOpt.disabled = false;
                dispSelect.value = 'false_positive';
            }
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endsection
