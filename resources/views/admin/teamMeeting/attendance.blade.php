@extends('layouts.master')
@section('title', 'Meeting Attendance')
@section('styles')
<style>
    #meeting-qr svg { width: 100%; max-width: 280px; height: auto; }
    @media print {
        body * { visibility: hidden; }
        #meeting-qr-card, #meeting-qr-card * { visibility: visible; }
        #meeting-qr-card { position: absolute; left: 0; top: 0; width: 100%; border: 0; }
        #meeting-qr-card .meeting-screen-only { display: none !important; }
    }
</style>
@endsection
@section('main-content')
<section class="content">
    @include('admin.section.flash_message')
    <a href="{{ route('admin.team-meetings.index') }}" class="btn btn-light mb-3">Back to meetings</a>
    <h3>{{ $meeting->title }}</h3>
    <p>{{ $meeting->venue }} · {{ $meeting->meeting_date }} {{ $meeting->meeting_start_time }}</p>
    <div class="row">
        <div class="col-lg-5 mb-3">
            <div class="card" id="meeting-qr-card"><div class="card-body text-center">
                <h4>Scan to join</h4>
                <h5>{{ $meeting->title }}</h5>
                <p>{{ $meeting->venue }} · {{ $meeting->meeting_date }}</p>
                <p>Check-in: <strong>{{ ucfirst($status) }}</strong></p>
                <p class="small">{{ $opensAt->format('Y-m-d H:i') }} – {{ $closesAt->format('Y-m-d H:i') }}<br>{{ config('app.timezone') }}</p>
                <div class="bg-white p-3 d-inline-block" id="meeting-qr">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(280)->margin(4)->generate($qrPayload) !!}</div>
                <p class="mt-3">Open Meeting Attendance in the mobile app and scan this code to check in.</p>
                <div class="meeting-screen-only">
                <a class="btn btn-outline-primary" href="{{ route('admin.team-meetings.attendance.export', $meeting->id) }}">Export attendance CSV</a>
                <button class="btn btn-outline-secondary" type="button" id="print-meeting-qr">Print QR</button>
                @can('edit_team_meeting')
                <form method="post" action="{{ route('admin.team-meetings.attendance.rotate', $meeting->id) }}" class="mt-3">
                    @csrf
                    <button class="btn btn-warning">Replace QR code</button>
                    <p class="small text-muted">Replacing a QR invalidates all previous copies.</p>
                </form>
                @endcan
                </div>
            </div></div>
        </div>
        <div class="col-lg-7 mb-3">
            <div class="card mb-3"><div class="card-body">
                <h4>Check-in settings</h4>
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                @can('edit_team_meeting')
                <form method="post" action="{{ route('admin.team-meetings.attendance.update', $meeting->id) }}">
                    @csrf @method('PUT')
                    <label for="attendance_enabled">Allow QR check-in</label>
                    <select id="attendance_enabled" name="attendance_enabled" class="form-select mb-3">
                        <option value="1" @selected(old('attendance_enabled', $meeting->attendance_enabled) == 1)>Enabled</option>
                        <option value="0" @selected(old('attendance_enabled', $meeting->attendance_enabled) == 0)>Disabled</option>
                    </select>
                    <label for="attendance_opens_at">Opens at</label>
                    <input id="attendance_opens_at" type="datetime-local" name="attendance_opens_at" class="form-control mb-3" value="{{ old('attendance_opens_at', $meeting->attendance_opens_at?->format('Y-m-d\TH:i')) }}">
                    <label for="attendance_closes_at">Closes at</label>
                    <input id="attendance_closes_at" type="datetime-local" name="attendance_closes_at" class="form-control mb-3" value="{{ old('attendance_closes_at', $meeting->attendance_closes_at?->format('Y-m-d\TH:i')) }}">
                    <p class="small text-muted">Blank times use the start and end of the meeting date. Timezone: {{ config('app.timezone') }}.</p>
                    <button class="btn btn-primary">Save settings</button>
                </form>
                @else
                <p>Ask an administrator with meeting edit permission to change settings.</p>
                @endcan
            </div></div>
            <div class="card"><div class="card-body">
                @php
                    $users = $meeting->teamMeetingParticipator->pluck('participator')->filter()->merge($meeting->meetingAttendances->pluck('user')->filter())->unique('id');
                @endphp
                <h4>Attendance · {{ $users->filter(fn ($user) => $attendances->has($user->id))->count() }} joined / {{ $users->count() }} employees</h4>
                <a href="{{ route('admin.team-meetings.attendance', $meeting->id) }}">Refresh register</a>
                <div class="row mt-3">
                    <div class="col-sm-7 mb-2">
                        <label for="attendance-search">Find employee</label>
                        <input type="search" id="attendance-search" class="form-control" placeholder="Employee name">
                    </div>
                    <div class="col-sm-5 mb-2">
                        <label for="attendance-filter">Attendance status</label>
                        <select id="attendance-filter" class="form-select">
                            <option value="">All employees</option>
                            <option value="joined">Joined</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive"><table class="table" id="attendance-register">
                    <thead><tr><th>Employee</th><th>Status</th><th>Checked in at</th></tr></thead>
                    <tbody>
                    @forelse($users as $user)
                        @php($record = $attendances->get($user->id))
                        <tr data-attendance="{{ $record ? 'joined' : 'pending' }}"><td>{{ $user->name }}</td><td><span class="badge {{ $record ? 'bg-success' : 'bg-secondary' }}">{{ $record ? 'Joined' : 'Pending' }}</span></td><td>{{ $record?->checked_in_at?->format('Y-m-d H:i:s') ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="3">No participants assigned. Edit the meeting to invite employees.</td></tr>
                    @endforelse
                        <tr id="attendance-no-results" hidden><td colspan="3">No employees match your search.</td></tr>
                    </tbody>
                </table></div>
            </div></div>
        </div>
    </div>
</section>
@endsection
@section('scripts')
<script>
 document.getElementById('print-meeting-qr').addEventListener('click', function () { window.print(); });
 const search = document.getElementById('attendance-search');
 const statusFilter = document.getElementById('attendance-filter');
 const attendanceRows = Array.from(document.querySelectorAll('#attendance-register tr[data-attendance]'));
 function filterAttendance() {
     const term = search.value.trim().toLocaleLowerCase();
     attendanceRows.forEach(function (row) {
         row.hidden = !row.cells[0].textContent.toLocaleLowerCase().includes(term)
             || (statusFilter.value !== '' && row.dataset.attendance !== statusFilter.value);
     });
     document.getElementById('attendance-no-results').hidden = attendanceRows.length === 0
         || attendanceRows.some(function (row) { return !row.hidden; });
 }
 search.addEventListener('input', filterAttendance);
 statusFilter.addEventListener('change', filterAttendance);
</script>
@endsection
