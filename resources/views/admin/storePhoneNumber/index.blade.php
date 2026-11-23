@extends('layouts.master')

@section('title', __('index.store_phone_numbers'))

@section('action', __('index.lists'))

@section('button')
    <div class="float-md-end d-flex align-items-center gap-1.5 justify-content-center">
        <a href="{{ route('admin.store-phone-numbers.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 rounded-2 px-3 py-1.5" style="font-size: 0.8125rem;">
            <i class="link-icon" data-feather="plus" style="width: 14px; height: 14px;"></i> {{ __('index.add_new_phone_number') }}
        </a>
    </div>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.storePhoneNumber.common.breadcrumb')

        <!-- Top Interactive KPI Metric Cards -->
        <div class="row g-2 mb-2.5 employee-kpi-container">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByStatus('')"
                     title="{{ __('index.all') }}"
                     style="border-radius: 10px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 3.5px solid #3b82f6 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">{{ __('index.total_phone_numbers') }}</span>
                            <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem; line-height: 1.2;">{{ $stats['total'] ?? $phoneNumbers->total() }}</h4>
                        </div>
                        <div class="rounded-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="link-icon" data-feather="phone" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByStatus('active')"
                     title="{{ __('index.active') }}"
                     style="border-radius: 10px; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 3.5px solid #10b981 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">{{ __('index.active_lines') }}</span>
                            <h4 class="fw-bold mb-0 text-success" style="font-size: 1.25rem; line-height: 1.2;">{{ $stats['active'] ?? 0 }}</h4>
                        </div>
                        <div class="rounded-2 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="link-icon" data-feather="check-circle" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByAssigned('1')"
                     title="{{ __('index.assigned_custodians') }}"
                     style="border-radius: 10px; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%); border-left: 3.5px solid #f97316 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">{{ __('index.assigned_custodians') }}</span>
                            <h4 class="fw-bold mb-0" style="color: #ea580c; font-size: 1.25rem; line-height: 1.2;">{{ $stats['assigned'] ?? 0 }}</h4>
                        </div>
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(249, 115, 22, 0.1); color: #f97316;">
                            <i class="link-icon" data-feather="user-check" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100 kpi-card-interactive"
                     onclick="filterByStandalone('1')"
                     title="{{ __('index.standalone_sims') }}"
                     style="border-radius: 10px; background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%); border-left: 3.5px solid #8b5cf6 !important; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted d-block" style="font-size: 11px; font-weight: 500; line-height: 1.2;">{{ __('index.standalone_sims') }}</span>
                            <h4 class="fw-bold mb-0" style="color: #7c3aed; font-size: 1.25rem; line-height: 1.2;">{{ $stats['standalone'] ?? 0 }}</h4>
                        </div>
                        <div class="rounded-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(139, 92, 246, 0.1); color: #7c3aed;">
                            <i class="link-icon" data-feather="layers" style="width: 16px; height: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $hasPhoneFilters = collect($filterParameters ?? [])
                ->except(['search', 'per_page', 'page', 'sort', 'direction'])
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->isNotEmpty();
        @endphp

        <!-- Collapsible Filter Panel (Like Employees) -->
        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'phoneFilterCollapse', 'directoryExpanded' => $hasPhoneFilters])
            <div id="phoneFilterCollapse" class="collapse{{ $hasPhoneFilters ? ' show' : '' }}">
                <form class="forms-sample directory-filter-form" action="{{ route('admin.store-phone-numbers.index') }}" id="phoneFilterForm" method="get">
                    <input type="hidden" id="search" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                    <input type="hidden" id="filter_is_assigned" name="is_assigned" value="{{ $filterParameters['is_assigned'] ?? '' }}">
                    <input type="hidden" id="filter_is_standalone" name="is_standalone" value="{{ $filterParameters['is_standalone'] ?? '' }}">

                    <div class="row g-3 align-items-end">
                        <div class="col-xl-3 col-md-6">
                            <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                            <select class="form-select form-select-sm" id="branch_id" name="branch_id">
                                <option value="">{{ __('index.all_branches') }}</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(($filterParameters['branch_id'] ?? '') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label for="operator" class="form-label">{{ __('index.operator') }}</label>
                            <select class="form-select form-select-sm" id="operator" name="operator">
                                <option value="">{{ __('index.all_operators') }}</option>
                                @foreach($operators as $key => $label)
                                    <option value="{{ $key }}" @selected(($filterParameters['operator'] ?? '') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label for="sim_slot" class="form-label">{{ __('index.sim_slot') }}</label>
                            <select class="form-select form-select-sm" id="sim_slot" name="sim_slot">
                                <option value="">{{ __('index.all_sim_slots') }}</option>
                                @foreach($simSlots as $key => $label)
                                    <option value="{{ $key }}" @selected(($filterParameters['sim_slot'] ?? '') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label for="purpose" class="form-label">{{ __('index.purpose') }}</label>
                            <select class="form-select form-select-sm" id="purpose" name="purpose">
                                <option value="">{{ __('index.all_purposes') }}</option>
                                @foreach($purposes as $key => $label)
                                    <option value="{{ $key }}" @selected(($filterParameters['purpose'] ?? '') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="assigned_to" class="form-label">{{ __('index.custodian_assigned_staff') }}</label>
                            <select class="form-select form-select-sm" id="assigned_to" name="assigned_to">
                                <option value="">{{ __('index.all_custodians') }}</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" @selected(($filterParameters['assigned_to'] ?? '') == $emp->id)>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label for="asset_id" class="form-label">{{ __('index.assigned_device') }}</label>
                            <select class="form-select form-select-sm" id="asset_id" name="asset_id">
                                <option value="">{{ __('index.all_devices') }}</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" @selected(($filterParameters['asset_id'] ?? '') == $asset->id)>{{ $asset->name }} ({{ $asset->asset_code ?: __('index.no_code') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6">
                            <label for="filter_status" class="form-label">{{ __('index.status') }}</label>
                            <select class="form-select form-select-sm" id="filter_status" name="status">
                                <option value="">{{ __('index.all_status') }}</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected(($filterParameters['status'] ?? '') == $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.store-phone-numbers.index')])
                </form>
            </div>
        </div>

        <!-- Main Datatable Card with Directory Toolbar -->
        <div class="card border-0 shadow-sm employee-datatable-card" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
            @include('admin.section.directory-toolbar', [
                'directoryTable' => 'storePhoneTable',
                'directoryForm' => 'phoneFilterForm',
                'directorySearch' => 'storePhoneSearch',
                'directoryRecords' => $phoneNumbers,
                'directoryTitle' => __('index.store_phone_numbers'),
                'directoryIcon' => 'phone',
                'directoryCanAdd' => true,
                'directoryAddUrl' => route('admin.store-phone-numbers.create'),
                'directoryDefaultSize' => '25',
                'directorySearchKey' => 'search',
                'directoryLive' => true
            ])

            <div class="card-body p-0">
                <style>
                    .phone-table th {
                        font-size: 0.75rem;
                        text-transform: uppercase;
                        letter-spacing: 0.04em;
                        font-weight: 700;
                        color: #475569;
                        background: #f8fafc;
                        border-bottom: 1px solid #e2e8f0;
                        padding: 10px 14px;
                        white-space: nowrap;
                    }
                    .phone-table td {
                        padding: 11px 14px;
                        vertical-align: middle;
                        font-size: 0.8125rem;
                        border-bottom: 1px solid #f1f5f9;
                    }
                    .phone-table tbody tr:hover td {
                        background-color: #f8fbff;
                    }
                    .phone-action-btn {
                        width: 28px;
                        height: 28px;
                        border-radius: 6px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        border: 1px solid #e2e8f0;
                        background: #ffffff;
                        color: #475569;
                        transition: all 0.15s ease;
                    }
                    .phone-action-btn:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
                    }
                    .phone-action-btn.btn-edit:hover {
                        background: #f0fdf4;
                        color: #16a34a;
                        border-color: #bbf7d0;
                    }
                    .phone-action-btn.btn-delete:hover {
                        background: #fef2f2;
                        color: #dc2626;
                        border-color: #fecaca;
                    }
                </style>

                <div class="table-responsive">
                    <table id="storePhoneTable" class="table phone-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">#</th>
                                <th style="min-width: 170px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="phone" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.phone_number') }}
                                    </span>
                                </th>
                                <th style="min-width: 110px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="radio" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.operator') }}
                                    </span>
                                </th>
                                <th style="min-width: 100px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="layers" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.sim_slot') }}
                                    </span>
                                </th>
                                <th style="min-width: 140px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="tag" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.purpose') }}
                                    </span>
                                </th>
                                <th style="min-width: 160px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="smartphone" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.assigned_device') }}
                                    </span>
                                </th>
                                <th style="min-width: 130px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="map-pin" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.branch') }}
                                    </span>
                                </th>
                                <th style="min-width: 180px;">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="link-icon text-muted" data-feather="user" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.custodian_assigned_staff') }}
                                    </span>
                                </th>
                                <th class="text-center" style="width: 90px;">
                                    <span class="d-inline-flex align-items-center justify-content-center gap-1">
                                        <i class="link-icon text-muted" data-feather="activity" style="width: 12px; height: 12px;"></i>
                                        {{ __('index.status') }}
                                    </span>
                                </th>
                                <th class="text-end pe-3" style="width: 80px;" data-directory-actions>{{ __('index.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($phoneNumbers as $key => $phone)
                                <tr>
                                    <td class="text-center text-muted fw-semibold" style="font-size: 11px;">
                                        {{ method_exists($phoneNumbers, 'firstItem') && $phoneNumbers->firstItem() ? $phoneNumbers->firstItem() + $key : $key + 1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; min-width: 28px;">
                                                <i data-feather="phone" style="width: 13px; height: 13px;"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark" style="font-size: 0.875rem;">{{ $phone->phone_number }}</strong>
                                                @if($phone->serial_number)
                                                    <span class="text-muted d-block font-monospace" style="font-size: 10px;">ICCID: {{ $phone->serial_number }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $opBadges = [
                                                'smart' => ['bg' => '#ecfdf5', 'color' => '#059669', 'border' => '#a7f3d0'],
                                                'cellcard' => ['bg' => '#fffbeb', 'color' => '#d97706', 'border' => '#fde68a'],
                                                'metfone' => ['bg' => '#fef2f2', 'color' => '#dc2626', 'border' => '#fecaca'],
                                                'seatel' => ['bg' => '#f0fdfa', 'color' => '#0d9488', 'border' => '#99f6e4'],
                                            ];
                                            $b = $opBadges[$phone->operator] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1'];
                                        @endphp
                                        <span class="badge" style="background: {{ $b['bg'] }}; color: {{ $b['color'] }}; border: 1px solid {{ $b['border'] }}; font-weight: 600;">
                                            {{ $operators[$phone->operator] ?? ucfirst($phone->operator) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border" style="font-weight: 500;">
                                            {{ $simSlots[$phone->sim_slot] ?? strtoupper($phone->sim_slot) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($phone->purpose)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-weight: 500;">
                                                {{ $purposes[$phone->purpose] ?? ucfirst($phone->purpose) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($phone->asset)
                                            <div class="fw-semibold text-dark">{{ $phone->asset->name }}</div>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">{{ $phone->asset->asset_code ?: __('index.no_code') }}</small>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 11px;">{{ __('index.standalone') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted"><i data-feather="map-pin" style="width: 12px; height: 12px;" class="me-1"></i>{{ $phone->branch?->name ?: '—' }}</span>
                                    </td>
                                    <td>
                                        @if($phone->employee)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; min-width: 26px;">
                                                    <i data-feather="user" style="width: 12px; height: 12px;"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark d-block">{{ $phone->employee->name }}</span>
                                                    @if($phone->assigned_date)
                                                        <span class="text-muted d-block" style="font-size: 10px;">{{ $phone->assigned_date->format('d M Y') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle" style="font-size: 11px;">{{ __('index.unassigned') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($phone->status === 'active')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle" style="font-weight: 600;">{{ __('index.active') }}</span>
                                        @elseif($phone->status === 'reserved')
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle" style="font-weight: 600;">{{ __('index.reserved') }}</span>
                                        @elseif($phone->status === 'inactive')
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-weight: 600;">{{ __('index.inactive') }}</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle" style="font-weight: 600;">{{ __('index.lost_damaged') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.store-phone-numbers.edit', $phone->id) }}" class="phone-action-btn btn-edit" title="{{ __('index.edit') }}">
                                                <i data-feather="edit-2" style="width: 12px; height: 12px;"></i>
                                            </a>
                                            <form action="{{ route('admin.store-phone-numbers.destroy', $phone->id) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirm('{{ __('index.confirm_delete_phone_number') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="phone-action-btn btn-delete" title="{{ __('index.delete') }}">
                                                    <i data-feather="trash-2" style="width: 12px; height: 12px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center py-5 text-muted">
                                        <i data-feather="phone-off" class="mb-2 text-muted" style="width: 36px; height: 36px;"></i>
                                        <div class="fw-semibold">{{ __('index.no_records_found') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted small">
                        @if($phoneNumbers->total() > 0)
                            {{ __('index.showing') }} {{ $phoneNumbers->firstItem() }} {{ __('index.to') }} {{ $phoneNumbers->lastItem() }} {{ __('index.of') }} {{ $phoneNumbers->total() }} {{ __('index.entries') }}
                        @endif
                    </div>
                    <div>
                        {{ $phoneNumbers->appends($_GET)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    function filterByStatus(status) {
        document.getElementById('filter_status').value = status;
        document.getElementById('filter_is_assigned').value = '';
        document.getElementById('filter_is_standalone').value = '';
        document.getElementById('phoneFilterForm').submit();
    }

    function filterByAssigned(isAssigned) {
        document.getElementById('filter_is_assigned').value = isAssigned;
        document.getElementById('filter_is_standalone').value = '';
        document.getElementById('filter_status').value = '';
        document.getElementById('phoneFilterForm').submit();
    }

    function filterByStandalone(isStandalone) {
        document.getElementById('filter_is_standalone').value = isStandalone;
        document.getElementById('filter_is_assigned').value = '';
        document.getElementById('filter_status').value = '';
        document.getElementById('phoneFilterForm').submit();
    }
</script>
@endsection
