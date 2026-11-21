@extends('layouts.master')

@section('title', 'ប្រព័ន្ធគ្រប់គ្រងជួរ និងរង់ចាំបើកប្រាក់ខែ (Payday Queue)')
@section('action', __('index.dashboard'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/directory-table-tools.css') }}?v={{ filemtime(public_path('assets/css/directory-table-tools.css')) }}">
    <style>
        .payroll-queue-list {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
        }

        .payroll-queue-list .directory-list-heading {
            min-height: 64px;
            padding: 16px 20px;
        }

        .payroll-queue-list .directory-tools {
            padding: 10px 20px;
        }

        .payroll-queue-list .table {
            margin-bottom: 0;
        }

        .payroll-queue-list .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bs-body-bg, #fff);
            border-top: 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.3);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            color: #232323;
            padding: 12px 15px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .payroll-queue-list .table tbody td {
            padding: 11px 15px;
            vertical-align: middle;
            font-size: 13px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
        }

        .payroll-queue-list .table tbody tr:hover > * {
            background: rgba(13, 110, 253, 0.04);
        }

        .payroll-queue-list .directory-add.border-0 {
            border: 0 !important;
        }
    </style>
@endsection

@section('main-content')
<div class="container-fluid py-2">
    @include('admin.section.flash_message')
    @include('admin.payroll-queue.common.breadcrumb')

    {{-- Header Banner & Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <h4 class="fw-bold mb-1 text-primary">
                        <i class="link-icon me-1" data-feather="users"></i> {{ __('index.payroll_queue_system') }}
                    </h4>
                    <p class="text-muted small mb-0">
                        {{ $session->title }} | {{ __('index.session_code') }}: <code>{{ $session->session_code }}</code>
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('admin.payroll-queue.sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="link-icon me-1" data-feather="list"></i> {{ __('index.lists') }}
                        </a>
                        <a href="{{ route('admin.payroll-queue.display', ['sessionId' => $session->id]) }}" target="_blank" class="btn btn-dark btn-sm">
                            <i class="link-icon me-1" data-feather="tv"></i> {{ __('index.open_tv_display') }}
                        </a>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createQrModal">
                            <i class="link-icon me-1" data-feather="plus"></i> {{ __('index.create_payday_queue') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal">
                            <i class="link-icon me-1" data-feather="grid"></i> {{ __('index.scan_qr') }}
                        </button>
                    </div>
                </div>
                <div class="col-md-7">
                    <form action="{{ route('admin.payroll-queue.index') }}" method="GET" class="row g-2 justify-content-md-end align-items-center">
                        <div class="col-auto">
                            <label class="form-label small mb-0 text-muted">{{ __('index.month') ?? 'Month' }}:</label>
                            <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0 text-muted">{{ __('index.branch') }}:</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">{{ __('index.all_branches') }}</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0 text-muted">{{ __('index.department') }}:</label>
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">{{ __('index.all_departments') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" data-branch="{{ $department->branch_id }}" {{ (string)$selectedDepartmentId === (string)$department->id ? 'selected' : '' }}>
                                        {{ $department->dept_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0 text-muted">បុគ្គលិក (optional):</label>
                            <input type="text" name="employee_code" class="form-control form-control-sm" value="{{ $selectedEmployeeCode }}" placeholder="Employee code">
                        </div>
                        <div class="col-auto align-self-end">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="link-icon" data-feather="filter"></i> ចម្រាញ់
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">បុគ្គលិកចូលជួរសរុប</div>
                        <div class="fs-4 fw-bold" id="statTotalJoined">{{ $stats['total_joined'] }} នាក់</div>
                    </div>
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                        <i class="link-icon text-white" data-feather="users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-dark-50 small fw-semibold">កំពុងរង់ចាំ (Waiting)</div>
                        <div class="fs-4 fw-bold" id="statTotalWaiting">{{ $stats['total_waiting'] }} នាក់</div>
                    </div>
                    <div class="p-2 bg-dark bg-opacity-10 rounded-circle">
                        <i class="link-icon text-dark" data-feather="clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">កំពុងបម្រើ (Now Serving)</div>
                        <div class="fs-4 fw-bold" id="statTotalServing">{{ $stats['total_serving'] }} / {{ count($counters) }} តុ</div>
                    </div>
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                        <i class="link-icon text-white" data-feather="monitor"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">បើកប្រាក់ខែជោគជ័យ (Paid)</div>
                        <div class="fs-4 fw-bold" id="statTotalPaid">{{ $stats['total_paid'] }} នាក់ (${{ number_format($stats['total_amount_paid'], 2) }})</div>
                    </div>
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                        <i class="link-icon text-white" data-feather="check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel 1: Now Serving --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary p-2 rounded-circle me-2">
                    <i class="link-icon text-white" data-feather="layers" style="width: 18px; height: 18px;"></i>
                </span>
                <div>
                    <h5 class="card-title mb-0 fw-bold">{{ __('index.now_serving') }}</h5>
                    <small class="text-muted">{{ __('index.now_serving_desc') }}</small>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Counter Flexibility Selector (1 or more than 1) --}}
                <div class="d-flex align-items-center bg-light p-1 rounded-pill border">
                    <span class="px-2 small fw-bold text-muted d-none d-sm-inline">
                        <i class="link-icon me-1" data-feather="sliders" style="width: 13px; height: 13px;"></i>{{ __('index.active_counters') }}:
                    </span>
                    <div class="btn-group btn-group-sm" role="group" id="quickCounterGroup">
                        @foreach([1, 2, 3, 4, 5] as $cNum)
                            <button type="button" 
                                    class="btn btn-sm btn-counter-pill {{ ($session->max_active_serving ?: 5) == $cNum ? 'btn-primary active fw-bold text-white shadow-sm' : 'btn-light text-dark' }} px-2.5 py-1 rounded-pill transition"
                                    data-counter-count="{{ $cNum }}"
                                    title="{{ $cNum == 1 ? __('index.single_counter_only') : __('index.set_counters_count', ['count' => $cNum]) }}">
                                {{ $cNum }} {{ __('index.counter_unit') }}
                            </button>
                        @endforeach
                    </div>
                    <!-- Dropdown for more counters (6 to 10) -->
                    <div class="dropdown ms-1">
                        <button class="btn btn-sm btn-light dropdown-toggle px-2 py-1 rounded-pill text-muted fw-bold" type="button" id="btnCustomCounters" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('index.more_counters') }}">
                            <span id="labelCurrentMoreCount">{{ ($session->max_active_serving ?: 5) > 5 ? (($session->max_active_serving ?: 5) . ' ' . __('index.counter_unit')) : '⋯' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="btnCustomCounters">
                            <li class="dropdown-header small fw-bold">{{ __('index.select_active_counters') }}</li>
                            @for($n = 1; $n <= 10; $n++)
                                <li>
                                    <a class="dropdown-item btn-counter-select {{ ($session->max_active_serving ?: 5) == $n ? 'active fw-bold' : '' }}" href="#" data-counter-count="{{ $n }}">
                                        {{ $n == 1 ? __('index.single_counter_only') : __('index.set_counters_count', ['count' => $n]) }}
                                    </a>
                                </li>
                            @endfor
                        </ul>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshStatus" title="{{ __('index.refresh') }}">
                    <i class="link-icon" data-feather="refresh-cw"></i> {{ __('index.refresh') }}
                </button>
            </div>
        </div>
        <div class="card-body p-3" id="servingCountersContainer">
            @include('admin.payroll-queue.partials.serving-counters', ['counters' => $counters, 'session' => $session])
        </div>
    </div>

    {{-- Panel 2: Queue Lists (Waiting List & Paid Successfully) --}}
    <div id="waitingPaidPanelsContainer">
        @include('admin.payroll-queue.partials.waiting-paid-panels', [
            'waiting_tickets' => $waiting_tickets,
            'paid_tickets' => $paid_tickets,
            'stats' => $stats,
            'session' => $session,
        ])
    </div>
    <div id="inviteEmployeesContainer" class="mt-4">
        @include('admin.payroll-queue.partials.invite-status-list', ['invite_employees' => $invite_employees, 'session' => $session])
    </div>
</div>

{{-- Create Payroll QR Modal --}}
<div class="modal fade" id="createQrModal" tabindex="-1" aria-labelledby="createQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.payroll-queue.index') }}" method="GET">
                <input type="hidden" name="create_qr" value="1">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="createQrModalLabel">
                        <i class="link-icon me-1" data-feather="plus-square"></i> បង្កើត QR វគ្គបើកប្រាក់ខែ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small">ជ្រើសរើសខែ និងក្រុមបុគ្គលិក។ បន្ទាប់ពីបង្កើត QR នឹងបង្ហាញសម្រាប់ស្កេនចូលជួរ។</p>
                    <div class="mb-3">
                        <label class="form-label">{{ __('index.month') ?? 'Payroll Month' }} <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('index.branch') }}</label>
                        <select name="branch_id" class="form-select">
                            <option value="">{{ __('index.all_branches') }}</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('index.department') }}</label>
                        <select name="department_id" class="form-select">
                            <option value="">{{ __('index.all_departments') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" data-branch="{{ $department->branch_id }}" {{ (string)$selectedDepartmentId === (string)$department->id ? 'selected' : '' }}>{{ $department->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('index.target_employee_code') }} <span class="text-muted">({{ __('index.optional') ?? 'Optional' }})</span></label>
                        <input type="text" name="employee_code" class="form-control" value="{{ $selectedEmployeeCode }}" placeholder="ឧ. EMP001">
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">{{ __('index.active_counters') }}</label>
                        <select name="max_active_serving" class="form-select">
                            <option value="1">1 {{ __('index.counter_unit') }} ({{ __('index.single_counter_only') }})</option>
                            <option value="2">2 {{ __('index.counter_unit') }}</option>
                            <option value="3">3 {{ __('index.counter_unit') }}</option>
                            <option value="4">4 {{ __('index.counter_unit') }}</option>
                            <option value="5" selected>5 {{ __('index.counter_unit') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('index.cancel') ?? 'Cancel' }}</button>
                    <button type="submit" class="btn btn-success"><i class="link-icon me-1" data-feather="grid"></i> {{ __('index.create') ?? 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- QR Code Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="qrModalLabel">
                    <i class="link-icon me-1" data-feather="grid"></i> QR កូដសម្រាប់បុគ្គលិកស្កេនចូលជួរ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <h5 class="fw-bold mb-1">{{ $session->title }}</h5>
                <p class="text-muted small mb-3">QR នេះសម្រាប់: {{ $targetEmployee ? ($targetEmployee->english_name ?: $targetEmployee->name) : ($session->department?->dept_name ?: 'បុគ្គលិកទាំងអស់') }}{{ $session->branch ? ' · '.$session->branch->name : '' }}. ស្កេនដើម្បីចូលជួរ។</p>
                <div class="p-3 bg-light border rounded d-inline-block mb-3 shadow-sm">
                    {!! $qrSvg !!}
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm text-center" id="joinUrlInput" value="{{ $joinUrl }}" readonly>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="btnCopyJoinUrl">
                        <i class="link-icon" data-feather="copy"></i> ចម្លង
                    </button>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-success btn-sm" id="btnSendPayrollInvite">
                        <i class="link-icon me-1" data-feather="send"></i> ផ្ញើ QR ទៅក្រុមគោលដៅ
                    </button>
                    <a href="{{ $joinUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="link-icon me-1" data-feather="external-link"></i> បើកតំណភ្ជាប់
                    </a>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="link-icon me-1" data-feather="printer"></i> បោះពុម្ព QR
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Disburse Salary Modal --}}
@include('admin.payroll-queue.partials.disburse-modal')

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const actionUrls = {
        recall: @js(route('admin.payroll-queue.recall', ['ticketId' => '__ticket__'])),
        skip: @js(route('admin.payroll-queue.skip', ['ticketId' => '__ticket__'])),
        invite: @js(route('admin.payroll-queue.send-invites', ['sessionId' => $session->id])),
    };
    @if($showQrModal)
    new bootstrap.Modal(document.getElementById('qrModal')).show();
    @endif
    const liveStatusUrl = "{{ route('admin.payroll-queue.live-status', ['sessionId' => $session->id]) }}";
    const callNextUrl = "{{ route('admin.payroll-queue.call-next', ['sessionId' => $session->id]) }}";
    const updateCountersUrl = "{{ route('admin.payroll-queue.sessions.update-counters', ['sessionId' => $session->id]) }}";

    document.querySelectorAll('select[name="branch_id"]').forEach(branch => {
        const department = branch.form?.querySelector('select[name="department_id"]');
        if (!department) return;

        function filterDepartments() {
            for (const option of department.options) {
                option.hidden = option.disabled = !!(branch.value && option.value && option.dataset.branch !== branch.value);
            }
            if (department.selectedOptions[0]?.disabled) department.value = '';
        }

        branch.addEventListener('change', filterDepartments);
        filterDepartments();
    });

    // Pleasant 2-tone airport/bank counter audio chime
    function playChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now); // D5
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.5);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.2); // A5
            gain2.gain.setValueAtTime(0.25, now + 0.2);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.2);
            osc2.stop(now + 0.8);
        } catch (e) {
            console.log('Audio chime not supported or blocked');
        }
    }

    // Feather icons re-render helper
    function reInitFeather() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    // Meeting-list style client-side search for queue tables (survives live re-render)
    const queueSearchTerms = {};

    function applyQueueSearch(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const term = (queueSearchTerms[tableId] || '').trim().toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        let visible = 0;
        rows.forEach(function (row) {
            if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                row.style.display = term ? 'none' : '';
                return;
            }
            const match = !term || row.textContent.toLowerCase().indexOf(term) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const count = document.querySelector('[data-directory-count="' + tableId + '"]');
        if (count) count.textContent = visible.toLocaleString();
    }

    function restoreQueueSearch() {
        Object.keys(queueSearchTerms).forEach(function (tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const card = table.closest('.payroll-queue-list');
            const input = card ? card.querySelector('.directory-search input') : null;
            if (input && input.value !== queueSearchTerms[tableId]) {
                input.value = queueSearchTerms[tableId];
            }
            applyQueueSearch(tableId);
        });
    }

    document.addEventListener('input', function (e) {
        const input = e.target.closest('.payroll-queue-list .directory-search input');
        if (!input) return;
        const card = input.closest('.payroll-queue-list');
        const table = card ? card.querySelector('table[id]') : null;
        if (!table) return;
        queueSearchTerms[table.id] = input.value;
        applyQueueSearch(table.id);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-queue-refresh]')) {
            e.preventDefault();
            refreshLiveStatus();
        }
    });

    // Refresh live status via AJAX
    function refreshLiveStatus() {
        fetch(liveStatusUrl, {headers: {Accept: 'application/json'}})
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    for (const [id, html] of Object.entries({
                        servingCountersContainer: data.counters_html,
                        waitingPaidPanelsContainer: data.panels_html,
                        inviteEmployeesContainer: data.invite_employees_html,
                    })) {
                        const container = document.getElementById(id);
                        if (container.dataset.renderedHtml !== html
                            && !container.querySelector(':focus, .dropdown-menu.show')) {
                            container.innerHTML = html;
                            container.dataset.renderedHtml = html;
                        }
                    }
                    
                    if (data.stats) {
                        document.getElementById('statTotalJoined').textContent = data.stats.total_joined + ' នាក់';
                        document.getElementById('statTotalWaiting').textContent = data.stats.total_waiting + ' នាក់';
                        document.getElementById('statTotalServing').textContent = data.stats.total_serving + ' / ' + data.counters.length + ' តុ';
                        document.getElementById('statTotalPaid').textContent = data.stats.total_paid + ' នាក់ ($' + Number(data.stats.total_amount_paid).toFixed(2) + ')';
                    }
                    reInitFeather();
                    restoreQueueSearch();
                }
            })
            .catch(err => console.error('Error refreshing queue:', err));
    }

    // Auto-polling every 4 seconds
    setInterval(refreshLiveStatus, 4000);

    // Manual refresh button
    document.getElementById('btnRefreshStatus')?.addEventListener('click', function() {
        refreshLiveStatus();
    });

    // Copy Join URL button
    document.getElementById('btnCopyJoinUrl')?.addEventListener('click', function() {
        const input = document.getElementById('joinUrlInput');
        input.select();
        document.execCommand('copy');
        alert('បានចម្លងតំណភ្ជាប់ដោយជោគជ័យ!');
    });

    async function postQueueAction(url, body = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(body),
        });
        const data = await response.json().catch(() => null);
        if (!response.ok || !data?.success) {
            throw new Error(data?.message || 'មិនអាចធ្វើបច្ចុប្បន្នភាពជួរបានទេ។');
        }
        return data;
    }

    document.addEventListener('click', async function (e) {
        // Counter setting toggle (1 or more than 1)
        const counterBtn = e.target.closest('.btn-counter-pill, .btn-counter-select');
        if (counterBtn) {
            e.preventDefault();
            const count = parseInt(counterBtn.dataset.counterCount, 10);
            if (!count || count < 1) return;

            counterBtn.disabled = true;

            try {
                const data = await postQueueAction(updateCountersUrl, { max_active_serving: count });

                // Update active buttons
                document.querySelectorAll('.btn-counter-pill').forEach(btn => {
                    const bCount = parseInt(btn.dataset.counterCount, 10);
                    if (bCount === count) {
                        btn.classList.remove('btn-light', 'text-dark');
                        btn.classList.add('btn-primary', 'active', 'fw-bold', 'text-white', 'shadow-sm');
                    } else {
                        btn.classList.remove('btn-primary', 'active', 'fw-bold', 'text-white', 'shadow-sm');
                        btn.classList.add('btn-light', 'text-dark');
                    }
                });

                document.querySelectorAll('.btn-counter-select').forEach(item => {
                    const iCount = parseInt(item.dataset.counterCount, 10);
                    if (iCount === count) {
                        item.classList.add('active', 'fw-bold');
                    } else {
                        item.classList.remove('active', 'fw-bold');
                    }
                });

                const moreLabel = document.getElementById('labelCurrentMoreCount');
                if (moreLabel) {
                    moreLabel.textContent = count > 5 ? (count + ' ' + @json(__('index.counter_unit'))) : '⋯';
                }

                if (data.counters_html) {
                    const container = document.getElementById('servingCountersContainer');
                    container.innerHTML = data.counters_html;
                    container.dataset.renderedHtml = data.counters_html;
                }
                if (data.panels_html) {
                    const pContainer = document.getElementById('waitingPaidPanelsContainer');
                    pContainer.innerHTML = data.panels_html;
                    pContainer.dataset.renderedHtml = data.panels_html;
                }
                if (data.stats && data.counters) {
                    document.getElementById('statTotalServing').textContent = data.stats.total_serving + ' / ' + data.counters.length + ' ' + @json(__('index.counter_unit'));
                }

                reInitFeather();
                restoreQueueSearch();
            } catch (error) {
                alert(error.message);
            } finally {
                counterBtn.disabled = false;
            }
            return;
        }

        const callButton = e.target.closest('.btn-call-next, .btn-call-specific');
        const recallButton = e.target.closest('.btn-recall');
        const skipButton = e.target.closest('.btn-skip');
        const actionButton = callButton || recallButton || skipButton;
        if (actionButton) {
            e.preventDefault();
            if (skipButton && !confirm('តើអ្នកពិតជាចង់រំលងសំបុត្រនេះមែនទេ?')) return;

            const {counter, ticketId} = actionButton.dataset;
            const url = callButton
                ? callNextUrl
                : actionUrls[recallButton ? 'recall' : 'skip'].replace('__ticket__', encodeURIComponent(ticketId));

            try {
                await postQueueAction(url, callButton ? {counter_number: counter, ticket_id: ticketId} : {});
                if (!skipButton) playChime();
            } catch (error) {
                alert(error.message);
            }
            actionButton.blur();
            refreshLiveStatus();
            return;
        }

        // Print Slip
        const btnPrintSlip = e.target.closest('.btn-print-slip');
        if (btnPrintSlip) {
            e.preventDefault();
            const ticketId = btnPrintSlip.dataset.ticketId;
            window.open(paymentUrl('print', ticketId), '_blank', 'width=550,height=750');
            return;
        }

        // Open Disburse Modal
        const btnDisburse = e.target.closest('.btn-disburse');
        if (btnDisburse) {
            e.preventDefault();
            const ticketId = btnDisburse.dataset.ticketId;
            openDisburseModal(ticketId);
            return;
        }
    });

    // Open Disburse Modal & fetch breakdown
    const disburseModalEl = document.getElementById('disburseSalaryModal');
    const disburseModal = new bootstrap.Modal(disburseModalEl);
    const loadingSpinner = document.getElementById('modalLoadingSpinner');
    const contentContainer = document.getElementById('modalContentContainer');
    const btnConfirm = document.getElementById('btnConfirmDisburse');
    const paymentUrls = {
        breakdown: @js(route('admin.payroll-queue.breakdown', ['ticketId' => '__ticket__'])),
        update: @js(route('admin.payroll-queue.quick-update', ['ticketId' => '__ticket__'])),
        disburse: @js(route('admin.payroll-queue.disburse', ['ticketId' => '__ticket__'])),
        print: @js(route('admin.payroll-queue.print-slip', ['ticketId' => '__ticket__'])),
    };
    const paymentUrl = (action, ticketId) => paymentUrls[action].replace('__ticket__', encodeURIComponent(ticketId));

    document.getElementById('btnSendPayrollInvite')?.addEventListener('click', async function () {
        if (!confirm('Send this payroll queue invitation to the selected branch, department, or employee?')) return;
        this.disabled = true;
        try {
            const data = await postQueueAction(actionUrls.invite);
            alert(data.message);
            refreshLiveStatus();
        } catch (error) {
            alert(error.message);
        } finally {
            this.disabled = false;
        }
    });

    function openDisburseModal(ticketId) {
        document.getElementById('modalTicketId').value = ticketId;
        loadingSpinner.style.display = 'block';
        contentContainer.style.display = 'none';
        btnConfirm.disabled = true;
        disburseModal.show();

        return fetch(paymentUrl('breakdown', ticketId), {
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(async response => {
                if (response.redirected || response.status === 401) {
                    throw new Error('សូមចូលគណនីម្តងទៀត រួចបើកសំបុត្រប្រាក់ខែឡើងវិញ។');
                }
                const res = await response.json().catch(() => null);
                if (!response.ok || !res?.success) {
                    throw new Error(res?.message || 'មិនអាចទាញទិន្នន័យប្រាក់ខែបានទេ។');
                }
                const d = res.data;
                window.currentQueuePayslip = d.payslip;
                const emp = d.employee;
                const ticket = d.ticket;
                const session = ticket.session;

                // Profile info
                document.getElementById('modalEmpName').textContent = emp.name || '—';
                document.getElementById('modalEmpCode').textContent = emp.employee_code || emp.username || '—';
                document.getElementById('modalEmpDept').textContent = emp.department ? emp.department.dept_name : '—';
                document.getElementById('modalEmpBranch').textContent = emp.branch ? emp.branch.name : '—';
                document.getElementById('modalTicketNumberBadge').textContent = ticket.ticket_number;
                document.getElementById('modalSalaryMonth').textContent = session ? session.month : '—';
                if (emp.avatar_url) {
                    document.getElementById('modalEmpAvatar').src = emp.avatar_url;
                }

                // Deductions breakdown
                const ded = d.deductions;
                document.getElementById('modalDedLeave').textContent = '-$' + Number(ded.leave).toFixed(2);
                document.getElementById('modalDedLate').textContent = '-$' + Number(ded.late_undertime).toFixed(2);
                document.getElementById('modalDedAdvance').textContent = '-$' + Number(ded.salary_advance).toFixed(2);
                document.getElementById('modalDedInstallment').textContent = '-$' + Number(ded.installment).toFixed(2);
                document.getElementById('modalDedPenalty').textContent = '-$' + Number(ded.penalty).toFixed(2);
                document.getElementById('modalDedOther').textContent = '-$' + Number(ded.other).toFixed(2);
                document.getElementById('modalTotalDeductions').textContent = '-$' + Number(ded.total).toFixed(2);
                document.getElementById('modalTotalDeductionBadge').textContent = '-$' + Number(ded.total).toFixed(2);

                // Earnings breakdown
                const earn = d.earnings;
                document.getElementById('modalEarnBasic').textContent = '$' + Number(earn.basic_salary).toFixed(2);
                document.getElementById('modalEarnSell').textContent = '+$' + Number(earn.sell).toFixed(2);
                document.getElementById('modalEarnIron').textContent = '+$' + Number(earn.iron).toFixed(2);
                document.getElementById('modalEarnMaterial').textContent = '+$' + Number(earn.material).toFixed(2);
                document.getElementById('modalEarnAttendance').textContent = '+$' + Number(earn.attendance_bonus).toFixed(2);
                document.getElementById('modalEarnPunctual').textContent = '+$' + Number(earn.punctuality_bonus).toFixed(2);
                document.getElementById('modalTotalEarnings').textContent = '+$' + Number(earn.gross_total).toFixed(2);
                document.getElementById('modalTotalGrossBadge').textContent = '+$' + Number(earn.gross_total).toFixed(2);

                // Net display & formula
                document.getElementById('modalNetSalaryDisplay').textContent = '$' + Number(d.net_salary).toFixed(2);
                document.getElementById('modalFormulaGross').textContent = '$' + Number(earn.gross_total).toFixed(2);
                document.getElementById('modalFormulaDed').textContent = '$' + Number(ded.total).toFixed(2);
                document.getElementById('modalQuickBonus').value = Number(d.payslip.bonus || 0).toFixed(2);
                document.getElementById('payrollQuickUpdateCard').style.display = d.can_quick_update ? 'block' : 'none';

                // Payment methods populate
                const selectPayment = document.getElementById('modalPaymentMethod');
                selectPayment.innerHTML = '';
                if (d.payment_methods && d.payment_methods.length > 0) {
                    d.payment_methods.forEach(pm => {
                        const opt = document.createElement('option');
                        opt.value = pm.id;
                        opt.textContent = pm.name;
                        selectPayment.appendChild(opt);
                    });
                } else {
                    selectPayment.innerHTML = '<option value="">No active payment methods</option>';
                }

                loadingSpinner.style.display = 'none';
                contentContainer.style.display = 'block';
                btnConfirm.disabled = !d.payment_methods?.length || btnConfirm.dataset.paymentAllowed === '0';
            })
            .catch(err => {
                alert(err.message || 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យ!');
                disburseModal.hide();
            });
    }

    document.getElementById('btnQuickUpdateBonus')?.addEventListener('click', function () {
        const ticketId = document.getElementById('modalTicketId').value;
        const bonus = document.getElementById('modalQuickBonus').value;
        const payslip = window.currentQueuePayslip;
        if (!payslip) return;
        this.disabled = true;
        fetch(paymentUrl('update', ticketId), {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify({bonus, expected_bonus: payslip.bonus ?? 0, expected_net: payslip.net_salary})
        }).then(res => res.json()).then(data => {
            if (!data.success) { alert(data.message || 'Could not update this payroll.'); return; }
            openDisburseModal(ticketId);
        }).catch(() => alert('Could not update this payroll.')).finally(() => { this.disabled = false; });
    });

    // Submit Disbursement
    document.getElementById('disburseSalaryForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (btnConfirm.disabled) return;
        const ticketId = document.getElementById('modalTicketId').value;
        const formData = new FormData(this);
        formData.set('expected_net', window.currentQueuePayslip.net_salary);
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបើកប្រាក់ខែ...';

        fetch(paymentUrl('disburse', ticketId), {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="link-icon me-1" data-feather="check"></i> បញ្ជាក់បើកប្រាក់ខែ';
            
            if (data.success) {
                disburseModal.hide();
                // Open quick print slip
                window.open(paymentUrl('print', ticketId), '_blank', 'width=550,height=750');
                refreshLiveStatus();
            } else {
                alert(data.message || 'មិនអាចបើកប្រាក់ខែបានទេ!');
            }
        })
        .catch(err => {
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="link-icon me-1" data-feather="check"></i> បញ្ជាក់បើកប្រាក់ខែ';
            alert('មានបញ្ហាបច្ចេកទេសក្នុងការបើកប្រាក់ខែ!');
        });
    });
});
</script>
<style>
.calling-pulse {
    animation: pulseBorder 1.5s infinite;
}
@keyframes pulseBorder {
    0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.6); }
    70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
}
.border-dashed {
    border: 2px dashed #dee2e6 !important;
}
</style>
@endsection
