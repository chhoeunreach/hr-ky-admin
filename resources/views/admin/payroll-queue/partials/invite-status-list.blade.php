<div class="card border-0 shadow-sm payroll-queue-list">
    <div class="directory-list-heading">
        <div class="directory-title">
            <i data-feather="users"></i>
            <h6>{{ __('index.employees_to_pay') }}</h6>
            <span class="directory-count" data-directory-count="queueInviteTable">{{ number_format($invite_employees->count()) }}</span>
        </div>
        <button type="button" class="directory-add border-0" data-bs-toggle="modal" data-bs-target="#qrModal">
            <i data-feather="send"></i> {{ __('index.send_or_show_qr') }}
        </button>
    </div>
    <div class="directory-tools no-print">
        <small class="text-muted me-auto">{{ __('index.track_invite_and_scan_status', ['month' => $session->month]) }}</small>
        <div class="directory-search">
            <label class="visually-hidden" for="queueInviteSearch">{{ __('index.search') }}</label>
            <input type="search" id="queueInviteSearch" class="form-control" placeholder="{{ __('index.search') }} …" autocomplete="off">
        </div>
        <button type="button" class="directory-tool-button directory-refresh" data-queue-refresh title="{{ __('index.refresh') }}">
            <i data-feather="refresh-cw"></i>
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive directory-managed-scroll">
            <table class="table directory-managed-table" id="queueInviteTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>{{ __('index.employee') }}</th>
                        <th>{{ __('index.branch') }} / {{ __('index.department') }}</th>
                        <th>{{ __('index.status') }}</th>
                        <th>{{ __('index.ticket_number') }}</th>
                        <th>{{ __('index.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invite_employees as $index => $employee)
                        @php
                            $status = $employee->queue_display_status;
                            $statusStyle = match ($status) {
                                'waiting' => ['warning', __('index.waiting')],
                                'in_process' => ['primary', __('index.serving')],
                                'paid' => ['success', __('index.paid')],
                                'needs_attention' => ['secondary', __('index.pending')],
                                'invited' => ['info', __('index.pending')],
                                default => ['light text-dark border', __('index.pending')],
                            };
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $employee->avatar_url ?: asset('assets/images/img.png') }}" alt="" class="rounded-circle me-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $employee->english_name ?: $employee->name }}</div>
                                        <small class="text-muted">{{ $employee->employee_code ?: $employee->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $employee->branch?->name ?: '—' }}</div>
                                <small class="text-muted">{{ $employee->department?->dept_name ?: '—' }}</small>
                            </td>
                            <td><span class="badge bg-{{ $statusStyle[0] }}">{{ $statusStyle[1] }}</span></td>
                            <td>{{ $employee->queue_ticket?->ticket_number ?: '—' }}</td>
                            <td class="text-muted small">
                                @if($employee->queue_ticket?->joined_at)
                                    {{ __('index.scanned_at_time', ['time' => $employee->queue_ticket->joined_at->format('d/m/Y h:i A')]) }}
                                @elseif($employee->invite_sent_at)
                                    {{ __('index.invite_sent_at_time', ['time' => \Illuminate\Support\Carbon::parse($employee->invite_sent_at)->format('d/m/Y h:i A')]) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('index.no_employees_in_target') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
