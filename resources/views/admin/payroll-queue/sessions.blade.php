@extends('layouts.master')

@section('title', __('index.payroll_queue_system'))
@section('action', __('index.lists'))

@section('styles')
    <style>
        .payroll-session-table td {
            vertical-align: middle;
            font-size: 13px;
        }
        .payroll-session-table th {
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
@endsection

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    @include('admin.payroll-queue.common.breadcrumb')

    @php
        $defaultPageSize = (int) ($defaultPageSize ?? getRecordPerPage());
        $sessionPageSizes = array_values(array_unique(array_merge([10, 15, 25, 50, 100, 200], [$defaultPageSize])));
        sort($sessionPageSizes);
        $sessionPageSizes[] = 'all';
    @endphp

    {{-- Filter Panel --}}
    <div class="directory-filter-panel mb-4">
        @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'payrollQueueFilterCollapse', 'directoryExpanded' => false])
        <div id="payrollQueueFilterCollapse" class="collapse">
            <form class="forms-sample directory-filter-form" action="{{ route('admin.payroll-queue.sessions.index') }}" id="payrollQueueFilterForm" method="get">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-6">
                        <label for="filter_month" class="form-label">{{ __('index.month') ?? 'Month' }}</label>
                        <input type="month" class="form-control" id="filter_month" name="month" value="{{ $filterParameters['month'] ?? '' }}">
                    </div>

                    @if($canSelectAllBranches || $branches->count() > 1)
                        <div class="col-xl-3 col-md-6">
                            <label for="filter_branch_id" class="form-label">{{ __('index.branch') }}</label>
                            <select class="form-control" id="filter_branch_id" name="branch_id">
                                <option value="">{{ __('index.select_branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ (isset($filterParameters['branch_id']) && (string)$filterParameters['branch_id'] === (string)$branch->id) ? 'selected' : '' }}>
                                        {{ ucfirst($branch->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-xl-3 col-md-6">
                        <label for="filter_department_id" class="form-label">{{ __('index.department') }}</label>
                        <select class="form-control" id="filter_department_id" name="department_id">
                            <option value="">{{ __('index.select_department') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" data-branch="{{ $department->branch_id }}" {{ (isset($filterParameters['department_id']) && (string)$filterParameters['department_id'] === (string)$department->id) ? 'selected' : '' }}>
                                    {{ $department->dept_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label for="filter_status" class="form-label">{{ __('index.status') }}</label>
                        <select class="form-control" id="filter_status" name="status">
                            <option value="">{{ __('index.all') ?? 'All' }}</option>
                            <option value="open" {{ (isset($filterParameters['status']) && $filterParameters['status'] === 'open') ? 'selected' : '' }}>{{ __('index.open_active') }}</option>
                            <option value="paused" {{ (isset($filterParameters['status']) && $filterParameters['status'] === 'paused') ? 'selected' : '' }}>{{ __('index.paused') }}</option>
                            <option value="closed" {{ (isset($filterParameters['status']) && $filterParameters['status'] === 'closed') ? 'selected' : '' }}>{{ __('index.closed') }}</option>
                        </select>
                    </div>
                </div>
                @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.payroll-queue.sessions.index')])
            </form>
        </div>
    </div>

    {{-- Sessions Table Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
        @include('admin.section.directory-toolbar', [
            'directoryTable' => 'payrollQueueTable',
            'directoryForm' => 'payrollQueueFilterForm',
            'directorySearch' => 'payrollQueueListSearch',
            'directoryRecords' => $sessions,
            'directoryTitle' => __('index.payroll_queue_lists'),
            'directoryIcon' => 'users',
            'directoryCanAdd' => true,
            'directoryAddModal' => 'createSessionModal',
            'directoryAddLabel' => __('index.create_payday_queue'),
            'directoryDefaultSize' => (string) $defaultPageSize,
            'directoryPageSizes' => $sessionPageSizes,
            'directorySearchKey' => 'search',
            'directorySearchInForm' => true,
            'directoryLive' => false,
        ])

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="payrollQueueTable" class="table payroll-session-table mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>{{ __('index.session_code') }} &amp; {{ __('index.title') }}</th>
                        <th class="text-center">{{ __('index.month') ?? 'Month' }}</th>
                        <th>{{ __('index.branch') }} &amp; {{ __('index.department') }}</th>
                        <th>{{ __('index.target_audience') }}</th>
                        <th class="text-center">{{ __('index.tickets_and_progress') }}</th>
                        <th class="text-center">{{ __('index.status') }}</th>
                        <th class="text-center no-sort" data-directory-actions style="width: 180px;">{{ __('index.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($sessions as $key => $session)
                        <tr>
                            <td>{{ $sessions->firstItem() ? ($sessions->firstItem() + $key) : ($key + 1) }}</td>
                            <td>
                                <div class="fw-bold text-primary">{{ $session->session_code }}</div>
                                <div class="text-muted small">{{ $session->title }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $session->month }}</span>
                            </td>
                            <td>
                                <div><i class="link-icon me-1 text-muted" data-feather="home" style="width: 14px; height: 14px;"></i> {{ $session->branch?->name ?? __('index.all_branches') }}</div>
                                @if($session->department)
                                    <div class="text-muted small"><i class="link-icon me-1" data-feather="briefcase" style="width: 12px; height: 12px;"></i> {{ $session->department->dept_name }}</div>
                                @endif
                            </td>
                            <td>
                                @if($session->targetEmployee)
                                    <span class="badge bg-info text-white">
                                        <i class="link-icon me-1" data-feather="user" style="width: 12px; height: 12px;"></i>
                                        {{ $session->targetEmployee->english_name ?: $session->targetEmployee->name }}
                                        ({{ $session->targetEmployee->employee_code ?: $session->targetEmployee->username }})
                                    </span>
                                @elseif($session->department)
                                    <span class="badge bg-secondary">
                                        {{ $session->department->dept_name }} {{ __('index.all_employees') }}
                                    </span>
                                @elseif($session->branch)
                                    <span class="badge bg-light text-dark border">
                                        {{ $session->branch->name }} {{ __('index.all_employees') }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ __('index.all_employees') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                    <span class="stat-pill bg-warning-subtle text-warning border border-warning" title="{{ __('index.waiting') }}">
                                        ⏳ {{ $session->waiting_count }}
                                    </span>
                                    <span class="stat-pill bg-info-subtle text-info border border-info" title="{{ __('index.serving') }}">
                                        ⚡ {{ $session->serving_count }}
                                    </span>
                                    <span class="stat-pill bg-success-subtle text-success border border-success" title="{{ __('index.paid') }}">
                                        ✓ {{ $session->paid_count }}
                                    </span>
                                    <span class="stat-pill bg-secondary-subtle text-secondary border border-secondary" title="{{ __('index.all') }}">
                                        Σ {{ $session->tickets_count }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($session->status === 'open')
                                    <span class="badge bg-success">{{ __('index.open_active') }}</span>
                                @elseif($session->status === 'paused')
                                    <span class="badge bg-warning text-dark">{{ __('index.paused') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('index.closed') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <a href="{{ route('admin.payroll-queue.index', ['sessionId' => $session->id]) }}"
                                       class="btn btn-xs btn-outline-primary"
                                       title="{{ __('index.view_live_queue') }}">
                                        <i data-feather="eye" style="width: 14px; height: 14px;"></i> {{ __('index.view') ?? 'View' }}
                                    </a>
                                    <button type="button"
                                            class="btn btn-xs btn-outline-info showSessionDetailsBtn"
                                            data-detail-url="{{ route('admin.payroll-queue.sessions.show', $session->id) }}"
                                            title="{{ __('index.session_details') }}">
                                        <i data-feather="grid" style="width: 14px; height: 14px;"></i>
                                    </button>
                                    <a href="{{ route('admin.payroll-queue.display', $session->id) }}"
                                       target="_blank"
                                       class="btn btn-xs btn-outline-dark"
                                       title="{{ __('index.open_tv_display') }}">
                                        <i data-feather="tv" style="width: 14px; height: 14px;"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-xs btn-outline-danger delete-session-btn"
                                            data-href="{{ route('admin.payroll-queue.sessions.delete', $session->id) }}"
                                            title="{{ __('index.delete') }}">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="link-icon mb-2" data-feather="inbox" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                                <div>{{ __('index.no_records_found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($sessions->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Create Session Modal --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('admin.payroll-queue.sessions.store') }}" method="POST" id="createSessionForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="createSessionModalLabel">
                        <i class="link-icon me-1" data-feather="plus-circle"></i> {{ __('index.create_payday_queue') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($errors->createPaydayQueue->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->createPaydayQueue->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('index.month') ?? 'Payroll Month' }} <span class="text-danger">*</span></label>
                        <input type="month" name="month" class="form-control" value="{{ old('month', now()->format('Y-m')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('index.title') }} <span class="text-muted small">({{ __('index.optional') ?? 'Optional' }})</span></label>
                        <input type="text" name="title" class="form-control" maxlength="255" value="{{ old('title') }}" placeholder="ឧ. បើកប្រាក់ខែប្រចាំខែ (Payday Queue)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('index.branch') }}</label>
                        <select name="branch_id" id="modal_branch_id" class="form-select">
                            @if($canSelectAllBranches)
                                <option value="">{{ __('index.all_branches') }}</option>
                            @endif
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected((string) old('branch_id', $canSelectAllBranches ? '' : auth()->user()?->branch_id) === (string) $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('index.department') }}</label>
                        <select name="department_id" id="modal_department_id" class="form-select">
                            <option value="">{{ __('index.all_departments') }}</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" data-branch="{{ $dept->branch_id }}" @selected((string) old('department_id') === (string) $dept->id)>{{ $dept->dept_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('index.target_employee_code') }} <span class="text-muted small">({{ __('index.optional') ?? 'Optional' }})</span></label>
                        <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code') }}" placeholder="ឧ. EMP001">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">{{ __('index.max_active_serving') }}</label>
                            <input type="number" name="max_active_serving" class="form-control" min="1" max="10" value="{{ old('max_active_serving', 5) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">{{ __('index.status') }}</label>
                            <select name="status" class="form-select">
                                <option value="open" @selected(old('status', 'open') === 'open')>{{ __('index.open_active') }}</option>
                                <option value="paused" @selected(old('status') === 'paused')>{{ __('index.paused') }}</option>
                                <option value="closed" @selected(old('status') === 'closed')>{{ __('index.closed') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('index.cancel') ?? 'Cancel' }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="link-icon me-1" data-feather="check"></i> {{ __('index.create') ?? 'Create Session' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Session Details & QR Inspection Modal --}}
<div class="modal fade" id="sessionDetailModal" tabindex="-1" aria-labelledby="sessionDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="sessionDetailModalLabel">
                    <i class="link-icon me-1" data-feather="grid"></i> {{ __('index.payday_queue_session_details') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="sessionDetailSpinner" class="py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="sessionDetailError" class="alert alert-danger d-none" role="alert">
                    {{ __('index.payday_queue_details_failed') }}
                </div>

                <div id="sessionDetailContent" class="d-none">
                    <h5 class="fw-bold mb-1" id="modalSessionTitle"></h5>
                    <p class="text-muted small mb-3">{{ __('index.session_code') }}: <code id="modalSessionCode"></code></p>

                    <div id="modalQrSvgContainer" class="p-3 bg-white rounded border d-inline-block shadow-sm mb-3">
                        {{-- Injected SVG --}}
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" id="modalJoinUrl" class="form-control form-control-sm text-center" readonly>
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="copyJoinUrlBtn">
                            <i class="link-icon me-1" data-feather="copy" style="width: 14px; height: 14px;"></i> {{ __('index.copy_url') }}
                        </button>
                    </div>

                    <div class="row g-2 mb-3 text-start small border rounded p-2 bg-light">
                        <div class="col-6"><strong>{{ __('index.month') ?? 'Month' }}:</strong> <span id="modalMonth"></span></div>
                        <div class="col-6"><strong>{{ __('index.branch') }}:</strong> <span id="modalBranch"></span></div>
                        <div class="col-6"><strong>{{ __('index.waiting') }}:</strong> <span id="modalWaiting" class="text-warning fw-bold"></span></div>
                        <div class="col-6"><strong>{{ __('index.paid') }}:</strong> <span id="modalPaid" class="text-success fw-bold"></span></div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" id="modalDashboardLink" class="btn btn-primary btn-sm">
                            <i class="link-icon me-1" data-feather="activity"></i> {{ __('index.open_dashboard') }}
                        </a>
                        <a href="#" id="modalTvLink" target="_blank" class="btn btn-dark btn-sm">
                            <i class="link-icon me-1" data-feather="tv"></i> {{ __('index.open_tv_display') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('index.close') ?? 'Close' }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        for (const prefix of ['filter', 'modal']) {
            const branch = document.getElementById(prefix + '_branch_id');
            const department = document.getElementById(prefix + '_department_id');
            if (!branch || !department) continue;

            const filterDepartments = () => {
                for (const option of department.options) {
                    option.hidden = option.disabled = Boolean(
                        branch.value && option.value && option.dataset.branch !== branch.value
                    );
                }
                if (department.selectedOptions[0]?.disabled) department.value = '';
            };
            branch.addEventListener('change', filterDepartments);
            filterDepartments();
        }

        @if($errors->createPaydayQueue->any())
            bootstrap.Modal.getOrCreateInstance(document.getElementById('createSessionModal')).show();
        @endif

        // Delete Session with SweetAlert confirmation
        document.querySelectorAll('.delete-session-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const deleteUrl = this.dataset.href;
                Swal.fire({
                    title: `{{ __('index.delete_payroll_queue_session_confirm') }}`,
                    text: `{{ __('index.delete_payroll_queue_session_warning') }}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `{{ __('index.yes_delete') ?? 'Yes, delete it!' }}`,
                    cancelButtonText: `{{ __('index.no_cancel') ?? 'Cancel' }}`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });
        });

        // Show session details modal via AJAX
        const detailModalEl = document.getElementById('sessionDetailModal');
        const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalEl);
        let detailRequest;
        detailModalEl.addEventListener('hide.bs.modal', () => detailRequest?.abort());

        document.querySelectorAll('.showSessionDetailsBtn').forEach(btn => {
            btn.addEventListener('click', function () {
                detailRequest?.abort();
                const request = new AbortController();
                detailRequest = request;
                const error = document.getElementById('sessionDetailError');
                const spinner = document.getElementById('sessionDetailSpinner');
                const content = document.getElementById('sessionDetailContent');
                spinner.classList.remove('d-none');
                content.classList.add('d-none');
                error.classList.add('d-none');
                detailModal.show();

                fetch(this.dataset.detailUrl, {
                    headers: { 'Accept': 'application/json' },
                    signal: request.signal,
                })
                    .then(response => {
                        if (!response.ok || response.redirected) throw new Error();
                        return response.json();
                    })
                    .then(data => {
                        if (request.signal.aborted) return;
                        if (!data.success) throw new Error();

                        const s = data.session;
                        document.getElementById('modalSessionTitle').textContent = s.title;
                        document.getElementById('modalSessionCode').textContent = s.session_code;
                        document.getElementById('modalQrSvgContainer').innerHTML = data.qr_svg;
                        document.getElementById('modalJoinUrl').value = data.join_url;
                        document.getElementById('modalMonth').textContent = s.month;
                        document.getElementById('modalBranch').textContent = s.branch?.name ?? @js(__('index.all_branches'));
                        document.getElementById('modalWaiting').textContent = s.waiting_count ?? 0;
                        document.getElementById('modalPaid').textContent = s.paid_count ?? 0;
                        document.getElementById('modalDashboardLink').href = data.dashboard_url;
                        document.getElementById('modalTvLink').href = data.display_url;

                        content.classList.remove('d-none');
                        if (window.feather) feather.replace();
                    })
                    .catch(() => {
                        if (!request.signal.aborted) error.classList.remove('d-none');
                    })
                    .finally(() => {
                        if (!request.signal.aborted) spinner.classList.add('d-none');
                    });
            });
        });

        // Copy join URL button
        const copyBtn = document.getElementById('copyJoinUrlBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const urlInput = document.getElementById('modalJoinUrl');
                if (urlInput) {
                    urlInput.select();
                    navigator.clipboard.writeText(urlInput.value).then(() => {
                        this.innerHTML = `<i class="link-icon me-1" data-feather="check"></i> ${@json(__('index.copied'))}`;
                        if (window.feather) feather.replace();
                        setTimeout(() => {
                            this.innerHTML = `<i class="link-icon me-1" data-feather="copy"></i> ${@json(__('index.copy_url'))}`;
                            if (window.feather) feather.replace();
                        }, 2000);
                    });
                }
            });
        }
    })();
</script>
@endsection
