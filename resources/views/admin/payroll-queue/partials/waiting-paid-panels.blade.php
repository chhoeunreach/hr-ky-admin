<div class="row g-4">
    {{-- Sub-panel 1: Waiting List --}}
    <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm h-100 payroll-queue-list">
            <div class="directory-list-heading">
                <div class="directory-title">
                    <i data-feather="clock"></i>
                    <h6>{{ __('index.waiting') }} (Waiting List)</h6>
                    <span class="directory-count" data-directory-count="queueWaitingTable">{{ number_format($waiting_tickets->count()) }}</span>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold">
                    {{ __('index.waiting') }}: {{ $waiting_tickets->count() }} {{ __('index.persons') }}
                </span>
            </div>
            <div class="directory-tools no-print">
                <div class="directory-search">
                    <label class="visually-hidden" for="queueWaitingSearch">{{ __('index.search') }}</label>
                    <input type="search" id="queueWaitingSearch" class="form-control" placeholder="{{ __('index.search') }} …" autocomplete="off">
                </div>
                <button type="button" class="directory-tool-button directory-refresh" data-queue-refresh title="{{ __('index.refresh') }}">
                    <i data-feather="refresh-cw"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive directory-managed-scroll">
                    <table class="table directory-managed-table" id="queueWaitingTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th style="width: 100px;">{{ __('index.ticket') }}</th>
                                <th>{{ __('index.employee') }}</th>
                                <th>{{ __('index.department') }}</th>
                                <th>{{ __('index.joined_time') }}</th>
                                <th class="text-end" style="width: 100px;">{{ __('index.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waiting_tickets as $index => $wTicket)
                                <tr>
                                    <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-primary fs-7 fw-bold">{{ $wTicket->ticket_number }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $wTicket->employee?->avatar_url ?: asset('assets/images/img.png') }}"
                                                 alt="Avatar" class="rounded-circle me-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $wTicket->employee?->name ?: '—' }}</div>
                                                <small class="text-muted">{{ $wTicket->employee?->employee_code ?: $wTicket->employee?->username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $wTicket->employee?->department?->dept_name ?: '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $wTicket->joined_at ? $wTicket->joined_at->format('h:i A') : '—' }}
                                        </small>
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $maxCounters = $session->max_active_serving ?: 5;
                                        @endphp
                                        @if($maxCounters === 1)
                                            <button class="btn btn-outline-primary btn-xs btn-call-specific" type="button" data-ticket-id="{{ $wTicket->id }}" data-counter="1" title="{{ __('index.call_to_counter_number', ['counter' => 1]) }}">
                                                <i class="link-icon" data-feather="arrow-right-circle" style="width: 12px; height: 12px;"></i> {{ __('index.call_next') }}
                                            </button>
                                        @else
                                            <div class="dropdown">
                                                <button class="btn btn-outline-primary btn-xs dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    {{ __('index.call_to_counter') }}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @for($c = 1; $c <= $maxCounters; $c++)
                                                        <li>
                                                            <a class="dropdown-item btn-call-specific" href="#" data-ticket-id="{{ $wTicket->id }}" data-counter="{{ $c }}">
                                                                {{ __('index.call_to_counter_number', ['counter' => $c]) }}
                                                            </a>
                                                        </li>
                                                    @endfor
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="link-icon mb-2 opacity-50" data-feather="check-circle" style="width: 40px; height: 40px;"></i>
                                        <div>{{ __('index.no_tickets_waiting') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sub-panel 2: Paid Successfully List --}}
    <div class="col-lg-6 col-md-12">
        <div class="card border-0 shadow-sm h-100 payroll-queue-list">
            <div class="directory-list-heading">
                <div class="directory-title">
                    <i data-feather="check-circle"></i>
                    <h6>{{ __('index.paid_successfully') }}</h6>
                    <span class="directory-count" data-directory-count="queuePaidTable">{{ number_format($stats['total_paid'] ?? $paid_tickets->count()) }}</span>
                </div>
                <span class="badge bg-light text-success border px-3 py-2 rounded-pill fw-bold">
                    ${{ number_format($stats['total_amount_paid'] ?? 0, 2) }}
                </span>
            </div>
            <div class="directory-tools no-print">
                <div class="directory-search">
                    <label class="visually-hidden" for="queuePaidSearch">{{ __('index.search') }}</label>
                    <input type="search" id="queuePaidSearch" class="form-control" placeholder="{{ __('index.search') }} …" autocomplete="off">
                </div>
                <button type="button" class="directory-tool-button directory-refresh" data-queue-refresh title="{{ __('index.refresh') }}">
                    <i data-feather="refresh-cw"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive directory-managed-scroll">
                    <table class="table directory-managed-table" id="queuePaidTable">
                        <thead>
                            <tr>
                                <th style="width: 90px;">{{ __('index.ticket') }}</th>
                                <th>{{ __('index.employee') }}</th>
                                <th>{{ __('index.paid_time') }}</th>
                                <th>{{ __('index.payment_method') }}</th>
                                <th class="text-end">{{ __('index.amount_paid') }}</th>
                                <th class="text-end" style="width: 80px;">{{ __('index.payslip_slip') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paid_tickets as $pTicket)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary fs-7">{{ $pTicket->ticket_number }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $pTicket->employee?->avatar_url ?: asset('assets/images/img.png') }}"
                                                 alt="Avatar" class="rounded-circle me-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $pTicket->employee?->name ?: '—' }}</div>
                                                <small class="text-muted">{{ $pTicket->employee?->employee_code ?: $pTicket->employee?->username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $pTicket->paid_at ? $pTicket->paid_at->format('h:i A') : '—' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border">
                                            {{ $pTicket->paymentMethod?->name ?: 'Cash' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        ${{ number_format($pTicket->net_amount_paid ?: 0, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-secondary btn-xs btn-print-slip"
                                                data-ticket-id="{{ $pTicket->id }}" title="{{ __('index.print_payslip') }}">
                                            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="link-icon mb-2 opacity-50" data-feather="inbox" style="width: 40px; height: 40px;"></i>
                                        <div>{{ __('index.no_tickets_paid_yet') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
