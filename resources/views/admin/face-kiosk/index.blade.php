@extends('layouts.master')

@section('title', 'Face Attendance Kiosks')
@section('action', 'Face Attendance Kiosks')

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    @if(session('kiosk_plain_token'))
        <div class="alert alert-warning">
            <div class="row align-items-center g-4">
                <div class="col-md-auto text-center">
                    <button type="button"
                            class="bg-white border-0 rounded p-3 d-inline-block"
                            data-bs-toggle="modal"
                            data-bs-target="#kioskQrModal"
                            aria-label="View kiosk QR code full size">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)
                            ->margin(1)
                            ->generate(session('kiosk_provisioning_payload')) !!}
                    </button>
                    <div class="small text-muted mt-2">Click the QR code to view full size</div>
                </div>
                <div class="col">
                    <h5 class="mb-2">Scan this QR code on the face kiosk</h5>
                    <p class="mb-3">
                        The QR contains the server address and one-time device token.
                        The kiosk PIN is still required to complete login. For security,
                        this QR and token cannot be viewed again.
                    </p>
                    <div class="input-group">
                        <input id="kioskPlainToken" class="form-control font-monospace" readonly
                               value="{{ session('kiosk_plain_token') }}">
                        <button class="btn btn-dark" type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('kioskPlainToken').value)">
                            Copy token
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade"
             id="kioskQrModal"
             tabindex="-1"
             aria-labelledby="kioskQrModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="kioskQrModalLabel">Scan kiosk setup QR code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="kiosk-full-qr bg-white rounded p-3 d-inline-block">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(520)
                                ->margin(2)
                                ->generate(session('kiosk_provisioning_payload')) !!}
                        </div>
                        <p class="text-muted mb-0 mt-3">
                            Scan this code with the Digital HRS Face Kiosk app.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .kiosk-full-qr svg {
                display: block;
                width: min(75vw, 520px);
                height: auto;
            }
        </style>
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['Employees', $stats['employees'], 'users'],
            ['Faces enrolled', $stats['enrolled'], 'user-check'],
            ['Active kiosks', $stats['devices'], 'monitor'],
            ['Events today', $stats['today_events'], 'clock'],
        ] as [$label, $value, $icon])
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                            <i data-feather="{{ $icon }}"></i>
                        </span>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-3 fw-bold">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Provision a kiosk</h6></div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.face-kiosks.devices.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Device name</label>
                            <input class="form-control" name="name" required maxlength="100"
                                   value="{{ old('name') }}" placeholder="Front entrance tablet">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Branch</label>
                            <select class="form-control" name="branch_id" required>
                                <option value="">Select branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Admin PIN</label>
                                <input class="form-control" type="password" inputmode="numeric"
                                       name="admin_pin" required minlength="6" maxlength="12">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm PIN</label>
                                <input class="form-control" type="password" inputmode="numeric"
                                       name="admin_pin_confirmation" required minlength="6" maxlength="12">
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit">Create kiosk</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Kiosk devices</h6></div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr><th>Device</th><th>Branch</th><th>Last seen</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $device->name }}</div>
                                    <small class="text-muted font-monospace">{{ $device->token_prefix }}…</small>
                                </td>
                                <td>{{ $device->branch?->name }}</td>
                                <td>{{ $device->last_seen_at?->diffForHumans() ?: 'Never' }}</td>
                                <td>
                                    <span class="badge {{ $device->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $device->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown">Manage</button>
                                        <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 280px">
                                            <form method="post" class="mb-2"
                                                  action="{{ route('admin.face-kiosks.devices.rotate', $device) }}">
                                                @csrf
                                                <button class="btn btn-outline-warning btn-sm w-100">Rotate token</button>
                                            </form>
                                            <form method="post" class="mb-2"
                                                  action="{{ route('admin.face-kiosks.devices.toggle', $device) }}">
                                                @csrf @method('patch')
                                                <button class="btn btn-outline-secondary btn-sm w-100">
                                                    {{ $device->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            <form method="post"
                                                  action="{{ route('admin.face-kiosks.devices.pin', $device) }}">
                                                @csrf @method('patch')
                                                <input class="form-control form-control-sm mb-1" type="password"
                                                       inputmode="numeric" name="admin_pin" placeholder="New PIN" required>
                                                <input class="form-control form-control-sm mb-1" type="password"
                                                       inputmode="numeric" name="admin_pin_confirmation"
                                                       placeholder="Confirm PIN" required>
                                                <button class="btn btn-outline-primary btn-sm w-100">Update PIN</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No kiosks provisioned.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h6 class="mb-1">Employee face enrollment</h6>
                    <span class="text-muted small">
                        {{ number_format($employees->total()) }} employee(s) found
                    </span>
                </div>
                <form method="get"
                      action="{{ route('admin.face-kiosks.index') }}"
                      class="d-flex gap-2"
                      role="search">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i data-feather="search"></i>
                        </span>
                        <input type="search"
                               name="search"
                               class="form-control"
                               value="{{ $search }}"
                               maxlength="100"
                               placeholder="Name, code, username or email"
                               aria-label="Search employees">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if($search !== '')
                        <a href="{{ route('admin.face-kiosks.index') }}"
                           class="btn btn-outline-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead>
                <tr><th>Employee</th><th>Branch</th><th>Department</th><th>Face status</th><th>Quality</th><th>Enrolled</th></tr>
                </thead>
                <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $employee->name }}</div>
                            <small class="text-muted">{{ $employee->employee_code }}</small>
                        </td>
                        <td>{{ $employee->branch?->name }}</td>
                        <td>{{ $employee->department?->dept_name ?: '—' }}</td>
                        <td>
                            @if($employee->faceProfile?->is_active)
                                <span class="badge bg-success">Enrolled</span>
                            @else
                                <span class="badge bg-warning text-dark">Not enrolled</span>
                            @endif
                        </td>
                        <td>
                            {{ $employee->faceProfile?->quality_score !== null
                                ? number_format($employee->faceProfile->quality_score * 100, 0) . '%'
                                : '—' }}
                        </td>
                        <td>{{ $employee->faceProfile?->enrolled_at?->format('d M Y H:i') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No employees found.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $employees->links() }}
        </div>
    </div>
</section>
@endsection
