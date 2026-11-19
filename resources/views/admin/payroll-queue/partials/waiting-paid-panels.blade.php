<div class="row g-4">
    {{-- Sub-panel 1: Waiting List --}}
    <div class="col-lg-6 col-md-12">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning text-dark p-2 rounded-circle me-2">
                        <i class="link-icon" data-feather="clock" style="width: 18px; height: 18px;"></i>
                    </span>
                    <div>
                        <h5 class="card-title mb-0 fw-bold">បញ្ជីរង់ចាំ (Waiting List)</h5>
                        <small class="text-muted">បុគ្គលិកកំពុងតម្រង់ជួរបើកប្រាក់ខែ</small>
                    </div>
                </div>
                <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">
                    រង់ចាំ: {{ $waiting_tickets->count() }} នាក់
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 60px;">ល.រ</th>
                                <th style="width: 100px;">សំបុត្រ</th>
                                <th>បុគ្គលិក</th>
                                <th>ផ្នែក</th>
                                <th>ម៉ោងចូល</th>
                                <th class="text-end" style="width: 100px;">សកម្មភាព</th>
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
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-xs dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                ហៅមកតុ
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @for($c = 1; $c <= 5; $c++)
                                                    <li>
                                                        <a class="dropdown-item btn-call-specific" href="#" data-ticket-id="{{ $wTicket->id }}" data-counter="{{ $c }}">
                                                            ហៅមកកាន់ តុទី {{ $c }}
                                                        </a>
                                                    </li>
                                                @endfor
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="link-icon mb-2 opacity-50" data-feather="check-circle" style="width: 40px; height: 40px;"></i>
                                        <div>មិនមានបុគ្គលិកកំពុងរង់ចាំក្នុងជួរនៅឡើយទេ</div>
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
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success text-white p-2 rounded-circle me-2">
                        <i class="link-icon" data-feather="check-circle" style="width: 18px; height: 18px;"></i>
                    </span>
                    <div>
                        <h5 class="card-title mb-0 fw-bold">បើកប្រាក់ខែជោគជ័យ (Paid Successfully)</h5>
                        <small class="text-muted">បុគ្គលិកដែលបានបើកប្រាក់ខែរួចរាល់</small>
                    </div>
                </div>
                <div>
                    <span class="badge bg-success fs-6 px-3 py-2 rounded-pill me-1">
                        បានបើក: {{ $stats['total_paid'] ?? $paid_tickets->count() }} នាក់
                    </span>
                    <span class="badge bg-light text-success border fs-6 px-3 py-2 rounded-pill fw-bold">
                        ${{ number_format($stats['total_amount_paid'] ?? 0, 2) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 90px;">សំបុត្រ</th>
                                <th>បុគ្គលិក</th>
                                <th>ម៉ោងបើក</th>
                                <th>វិធីទូទាត់</th>
                                <th class="text-end">ទឹកប្រាក់បានបើក</th>
                                <th class="text-end" style="width: 80px;">បង្កាន់ដៃ</th>
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
                                                data-ticket-id="{{ $pTicket->id }}" title="បោះពុម្ពប័ណ្ណបើកប្រាក់ខែ">
                                            <i class="link-icon" data-feather="printer"></i> ព្រីន
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="link-icon mb-2 opacity-50" data-feather="inbox" style="width: 40px; height: 40px;"></i>
                                        <div>មិនទាន់មានបុគ្គលិកបើកប្រាក់ខែរួចរាល់នៅឡើយទេ</div>
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
