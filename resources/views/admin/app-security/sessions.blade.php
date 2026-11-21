@extends('layouts.master')
@section('title', 'App Security Sessions')

@section('main-content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <a href="{{ route('admin.app-security.index') }}" class="small">← App Security</a>
            <h2 class="mb-0">Device sessions</h2>
            <div class="text-muted">{{ $device->user?->name ?? 'Deleted account' }} · {{ ucfirst($device->user_type) }} · {{ $device->device_model ?: 'Android device' }}</div>
        </div>
        <span class="badge {{ $device->device_status === 'approved' ? 'bg-success' : 'bg-secondary' }}">{{ $device->device_status }}</span>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Session</th><th>Created</th><th>Last used</th><th>Access expires</th><th>Refresh expires</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($sessions as $session)
            @php($active = !$session->revoked_at && $session->expires_at?->isFuture() && $session->refresh_expires_at?->isFuture())
            <tr>
                <td><code>{{ $session->uuid }}</code></td>
                <td class="small">{{ $session->created_at?->toDateTimeString() }}</td>
                <td class="small">{{ $session->last_used_at?->toDateTimeString() ?? 'Never' }}</td>
                <td class="small">{{ $session->expires_at?->toDateTimeString() }}</td>
                <td class="small">{{ $session->refresh_expires_at?->toDateTimeString() }}</td>
                <td>
                    <span class="badge {{ $active ? 'bg-success' : 'bg-secondary' }}">{{ $session->revoked_at ? 'revoked' : ($active ? 'active' : 'expired') }}</span>
                    @if($session->revocation_reason)<div class="small text-muted">{{ $session->revocation_reason }}</div>@endif
                </td>
                <td>
                    @if($active)
                    <form method="post" action="{{ route('admin.app-security.devices.sessions.revoke', [$device->uuid, $session->uuid]) }}" class="d-flex flex-column gap-1" onsubmit="return confirm('Revoke this device session?')">
                        @csrf
                        <input required name="reason" minlength="3" maxlength="512" class="form-control form-control-sm" placeholder="Reason">
                        <input required type="password" name="step_up_password" autocomplete="current-password" class="form-control form-control-sm" placeholder="Admin password">
                        <button class="btn btn-sm btn-outline-danger">Revoke session</button>
                    </form>
                    @else<span class="text-muted">No action</span>@endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-5">No sessions have been recorded for this device.</td></tr>
        @endforelse
        </tbody>
    </table></div><div class="card-footer">{{ $sessions->links() }}</div></div>
    <p class="small text-muted mt-3">Revocation is effective on the next protected API request. A disconnected device can remain unaware until it reconnects.</p>
</div>
@endsection
