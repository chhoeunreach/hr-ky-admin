@php
    $totalCounters = count($counters);
    $colClass = match($totalCounters) {
        1 => 'col-lg-6 col-md-8 col-sm-12 mx-auto',
        2 => 'col-md-6 col-sm-12',
        3 => 'col-lg-4 col-md-6 col-sm-12',
        4 => 'col-xl-3 col-md-6 col-sm-12',
        default => 'col-xl col-lg-4 col-md-6 col-sm-12',
    };
@endphp

<div class="row g-3">
    @if($totalCounters === 1)
        <div class="col-12 mb-1">
            <div class="alert alert-primary py-2 px-3 small d-flex align-items-center justify-content-between mb-0 border-0 bg-primary bg-opacity-10 text-primary rounded-3">
                <span class="d-flex align-items-center">
                    <i class="link-icon me-2" data-feather="check-circle" style="width: 16px; height: 16px;"></i>
                    <span><strong>{{ __('index.single_counter_mode') }}</strong> — {{ __('index.single_counter_mode_desc') }}</span>
                </span>
                <span class="badge bg-primary px-2 py-1 rounded-pill">{{ __('index.single_counter_only') }}</span>
            </div>
        </div>
    @endif

    @foreach($counters as $counterNum => $counter)
        @php
            $ticket = $counter['ticket'] ?? null;
            $isBusy = !empty($ticket);
            $isCalling = $isBusy && $ticket->status === 'calling';
        @endphp
        <div class="{{ $colClass }}">
            <div class="card counter-card h-100 border {{ $isCalling ? 'border-primary shadow-lg calling-pulse' : ($isBusy ? 'border-success shadow-sm' : 'border-dashed') }}">
                <div class="card-header d-flex justify-content-between align-items-center py-2 {{ $isCalling ? 'bg-primary text-white' : ($isBusy ? 'bg-success text-white' : 'bg-light') }}">
                    <span class="fw-bold fs-6">
                        <i class="link-icon me-1" data-feather="monitor"></i> {{ $counter['counter_name'] }}
                    </span>
                    @if($isBusy)
                        <span class="badge {{ $isCalling ? 'bg-warning text-dark' : 'bg-light text-success' }} fs-7">
                            {{ $isCalling ? __('index.counter_calling') : __('index.counter_serving') }}
                        </span>
                    @else
                        <span class="badge bg-secondary fs-7">{{ __('index.counter_available') }}</span>
                    @endif
                </div>
                <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
                    @if($isBusy)
                        <div>
                            <div class="ticket-badge mb-2">
                                <span class="badge bg-primary fs-3 px-3 py-2 fw-bolder">{{ $ticket->ticket_number }}</span>
                            </div>
                            <div class="employee-info mb-3">
                                <img src="{{ $ticket->employee?->avatar_url ?: asset('assets/images/img.png') }}" 
                                     alt="Employee" class="rounded-circle border mb-2" style="width: 54px; height: 54px; object-fit: cover;">
                                <div class="fw-bold fs-6 text-dark text-truncate">{{ $ticket->employee?->name ?: '—' }}</div>
                                <div class="text-muted small text-truncate">{{ $ticket->employee?->employee_code ?: $ticket->employee?->username }}</div>
                                <div class="badge bg-light text-secondary border text-truncate mt-1">
                                    {{ $ticket->employee?->department?->dept_name ?: 'General' }}
                                </div>
                            </div>
                            @if($ticket->called_at)
                                <div class="small text-muted mb-2">
                                    <i class="link-icon" data-feather="clock" style="width: 14px; height: 14px;"></i>
                                    {{ __('index.called_at') }}: {{ $ticket->called_at->format('h:i A') }}
                                </div>
                            @endif
                        </div>
                        <div class="action-buttons pt-2 border-top">
                            <button type="button" class="btn btn-success btn-sm w-100 mb-1 fw-bold btn-disburse" 
                                    data-ticket-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}">
                                <i class="link-icon me-1" data-feather="dollar-sign"></i> {{ __('index.disburse_salary') }}
                            </button>
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-recall" data-ticket-id="{{ $ticket->id }}" title="{{ __('index.recall') }}">
                                    <i class="link-icon" data-feather="volume-2"></i> {{ __('index.recall') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-skip" data-ticket-id="{{ $ticket->id }}" title="{{ __('index.skip') }}">
                                    <i class="link-icon" data-feather="skip-forward"></i> {{ __('index.skip') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="py-4 my-auto text-muted">
                            <i class="link-icon mb-2 text-secondary opacity-50" data-feather="user-plus" style="width: 36px; height: 36px;"></i>
                            <div class="fw-semibold">{{ __('index.counter_available') }}</div>
                            <div class="small text-muted">{{ __('index.counter_available') }}</div>
                        </div>
                        <div class="pt-2 border-top">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold btn-call-next" data-counter="{{ $counterNum }}">
                                <i class="link-icon me-1" data-feather="arrow-right-circle"></i> {{ __('index.call_next') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
