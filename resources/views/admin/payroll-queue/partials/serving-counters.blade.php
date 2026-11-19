<div class="row g-3">
    @foreach($counters as $counterNum => $counter)
        @php
            $ticket = $counter['ticket'] ?? null;
            $isBusy = !empty($ticket);
            $isCalling = $isBusy && $ticket->status === 'calling';
        @endphp
        <div class="col-xl col-lg-4 col-md-6 col-sm-12">
            <div class="card counter-card h-100 border {{ $isCalling ? 'border-primary shadow-lg calling-pulse' : ($isBusy ? 'border-success shadow-sm' : 'border-dashed') }}">
                <div class="card-header d-flex justify-content-between align-items-center py-2 {{ $isCalling ? 'bg-primary text-white' : ($isBusy ? 'bg-success text-white' : 'bg-light') }}">
                    <span class="fw-bold fs-6">
                        <i class="link-icon me-1" data-feather="monitor"></i> {{ $counter['counter_name'] }}
                    </span>
                    @if($isBusy)
                        <span class="badge {{ $isCalling ? 'bg-warning text-dark' : 'bg-light text-success' }} fs-7">
                            {{ $isCalling ? 'កំពុងហៅ...' : 'កំពុងបម្រើ' }}
                        </span>
                    @else
                        <span class="badge bg-secondary fs-7">ទំនេរ</span>
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
                                    ហៅនៅម៉ោង: {{ $ticket->called_at->format('h:i A') }}
                                </div>
                            @endif
                        </div>
                        <div class="action-buttons pt-2 border-top">
                            <button type="button" class="btn btn-success btn-sm w-100 mb-1 fw-bold btn-disburse" 
                                    data-ticket-id="{{ $ticket->id }}" data-ticket-number="{{ $ticket->ticket_number }}">
                                <i class="link-icon me-1" data-feather="dollar-sign"></i> បើកប្រាក់ខែ
                            </button>
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-recall" data-ticket-id="{{ $ticket->id }}" title="ហៅម្តងទៀត">
                                    <i class="link-icon" data-feather="volume-2"></i> ហៅម្តងទៀត
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-skip" data-ticket-id="{{ $ticket->id }}" title="រំលង">
                                    <i class="link-icon" data-feather="skip-forward"></i> រំលង
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="py-4 my-auto text-muted">
                            <i class="link-icon mb-2 text-secondary opacity-50" data-feather="user-plus" style="width: 36px; height: 36px;"></i>
                            <div class="fw-semibold">តុទំនេរ</div>
                            <div class="small text-muted">រង់ចាំការហៅបុគ្គលិកបន្ទាប់</div>
                        </div>
                        <div class="pt-2 border-top">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold btn-call-next" data-counter="{{ $counterNum }}">
                                <i class="link-icon me-1" data-feather="arrow-right-circle"></i> ហៅបន្ទាប់
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
