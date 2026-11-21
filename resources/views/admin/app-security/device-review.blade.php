@extends('layouts.master')
@section('title', 'App Security Risk Review')

@section('main-content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <a href="{{ route('admin.app-security.index') }}" class="small">← App Security</a>
            <h2 class="mb-0">Risk signal review</h2>
            <div class="text-muted">{{ $device->user?->name ?? 'Deleted account' }} · {{ $device->device_model ?: 'Android device' }} · Risk {{ $device->risk_score }}/100</div>
        </div>
        <span class="badge {{ $device->device_status === 'approved' ? 'bg-success' : 'bg-secondary' }}">{{ $device->device_status }}</span>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Signal</th><th>Severity</th><th>Points</th><th>Observed</th><th>Review</th><th>Decision</th></tr></thead>
        <tbody>
        @forelse($signals as $signal)
            @php($context = json_decode((string) $signal->context, true) ?: [])
            <tr>
                <td><strong>{{ \Illuminate\Support\Str::headline($signal->signal) }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit(json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 180) }}</div></td>
                <td><span class="badge {{ $signal->severity === 'critical' ? 'bg-danger' : ($signal->severity === 'high' ? 'bg-warning text-dark' : 'bg-light text-dark') }}">{{ $signal->severity }}</span></td>
                <td>{{ $signal->risk_points }}</td>
                <td class="small">{{ $signal->occurred_at }}</td>
                <td>
                    @if($signal->review_status)
                        <span class="badge {{ $signal->review_status === 'false_positive' ? 'bg-info text-dark' : 'bg-success' }}">{{ \Illuminate\Support\Str::headline($signal->review_status) }}</span>
                        <div class="small text-muted">Admin #{{ $signal->reviewed_by }} · {{ $signal->reviewed_at }}</div>
                        <div class="small">{{ $signal->review_reason }}</div>
                    @else
                        <span class="badge bg-secondary">Needs review</span>
                    @endif
                </td>
                <td>
                    @if($canReviewRiskSignals && !$signal->review_status)
                        <form method="post" action="{{ route('admin.app-security.risk-signals.review', $signal->id) }}" class="d-flex flex-column gap-2" style="min-width:240px">
                            @csrf
                            <select name="disposition" class="form-select form-select-sm" required>
                                @if($signal->severity !== 'critical')<option value="false_positive">False positive</option>@endif
                                <option value="confirmed">Confirmed signal</option>
                            </select>
                            <input class="form-control form-control-sm" name="reason" minlength="3" maxlength="512" required placeholder="Review reason">
                            <input class="form-control form-control-sm" type="password" name="step_up_password" autocomplete="current-password" required placeholder="Admin password">
                            <button class="btn btn-sm btn-outline-primary">Save audited review</button>
                        </form>
                    @else
                        <span class="text-muted">{{ $signal->review_status ? 'Reviewed' : 'Read only' }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No risk signals have been recorded for this device.</td></tr>
        @endforelse
        </tbody>
    </table></div></div>
    <p class="small text-muted mt-3">Marking a non-critical signal false positive excludes it from future rolling risk scores. Critical cryptographic signals cannot be dismissed. Review does not approve, block, or revoke the device.</p>
</div>
@endsection
