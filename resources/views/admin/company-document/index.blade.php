@extends('layouts.master')

@section('title', __('index.company_documents'))
@section('action', __('index.lists') ?? 'Lists')

@section('styles')
<style>
    .metric-card {
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    }
    .category-nav-pills .nav-link {
        font-weight: 600;
        font-size: 13px;
        color: #475569;
        border-radius: 8px;
        padding: 8px 16px;
        margin-right: 6px;
        margin-bottom: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .category-nav-pills .nav-link:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .category-nav-pills .nav-link.active {
        background: #0f766e !important;
        color: #ffffff !important;
        border-color: #0f766e !important;
        box-shadow: 0 2px 8px rgba(15, 118, 110, 0.3);
    }
    .file-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
    }
    .file-pdf { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
    .file-image { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .file-word { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
    .file-excel { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .file-other { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .countdown-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
    }
    .countdown-safe { background: #dcfce7; color: #15803d; }
    .countdown-warning { background: #fef3c7; color: #b45309; animation: pulse 2s infinite; }
    .countdown-danger { background: #fee2e2; color: #b91c1c; }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
</style>
@endsection

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <!-- Breadcrumb & Page Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin mb-3">
        <div>
            <h4 class="mb-1 mb-md-0 fw-bold" style="color: #0f172a;">
                <i class="link-icon me-2 text-primary" data-feather="folder"></i>
                {{ __('index.enterprise_document_hub') }}
            </h4>
            <p class="text-muted small mb-0">
                {{ __('index.enterprise_document_hub_desc') }}
            </p>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap gap-2 mt-2 mt-md-0">
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                <i class="link-icon me-1" data-feather="plus-circle"></i> + {{ __('index.add_document') }}
            </button>
        </div>
    </div>

    <!-- 4 Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Documents -->
        <div class="col-xl-3 col-sm-6">
            <div class="card metric-card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">{{ __('index.total_documents') }}</span>
                        <h3 class="fw-bold mb-0 mt-1 text-primary">{{ number_format($totalCount) }}</h3>
                        <small class="text-muted" style="font-size: 11px;">{{ __('index.five_major_categories') }}</small>
                    </div>
                    <div class="p-3 bg-light-primary rounded-circle">
                        <i class="link-icon text-primary" data-feather="layers" style="width: 26px; height: 26px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valid & Active -->
        <div class="col-xl-3 col-sm-6">
            <div class="card metric-card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase">{{ __('index.active_documents') }}</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">
                            {{ number_format(max(0, $totalCount - $expiredCount - $expiringSoonCount)) }}
                        </h3>
                        <small class="text-success" style="font-size: 11px;">{{ __('index.legally_compliant') }}</small>
                    </div>
                    <div class="p-3 bg-light-success rounded-circle">
                        <i class="link-icon text-success" data-feather="check-circle" style="width: 26px; height: 26px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Soon (< 30 days) -->
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('admin.company-documents.index', ['status' => 'expiring_soon']) }}" class="text-decoration-none">
                <div class="card metric-card border-start border-warning border-4 shadow-sm h-100 {{ $expiringSoonCount > 0 ? 'bg-light-warning' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-warning-dark small fw-bold text-uppercase">{{ __('index.expiring_soon_count') }}</span>
                            <h3 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($expiringSoonCount) }}</h3>
                            <small class="text-muted" style="font-size: 11px;">{{ __('index.alert_within_30_days') }}</small>
                        </div>
                        <div class="p-3 bg-light-warning rounded-circle">
                            <i class="link-icon text-warning" data-feather="alert-triangle" style="width: 26px; height: 26px;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Expired / Overdue -->
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('admin.company-documents.index', ['status' => 'expired']) }}" class="text-decoration-none">
                <div class="card metric-card border-start border-danger border-4 shadow-sm h-100 {{ $expiredCount > 0 ? 'bg-light-danger' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-danger small fw-bold text-uppercase">{{ __('index.expired_count') }}</span>
                            <h3 class="fw-bold mb-0 mt-1 text-danger">{{ number_format($expiredCount) }}</h3>
                            <small class="text-danger" style="font-size: 11px;">{{ __('index.urgent_renewal_needed') }}</small>
                        </div>
                        <div class="p-3 bg-light-danger rounded-circle">
                            <i class="link-icon text-danger" data-feather="x-circle" style="width: 26px; height: 26px;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Category Tabs Navigation -->
    <div class="category-nav-pills d-flex flex-wrap mb-2">
        <a href="{{ route('admin.company-documents.index') }}"
           class="nav-link {{ !request('category') ? 'active' : '' }}">
            <i class="link-icon me-1" data-feather="folder"></i> {{ __('index.all_documents') }}
            <span class="badge rounded-pill bg-secondary ms-1">{{ $totalCount }}</span>
        </a>
        <a href="{{ route('admin.company-documents.index', ['category' => 'master']) }}"
           class="nav-link {{ request('category') === 'master' ? 'active' : '' }}">
            <i class="link-icon me-1 text-primary" data-feather="user-check"></i> 🪪 {{ __('index.doc_master') }}
            <span class="badge rounded-pill bg-primary ms-1">{{ $masterCount }}</span>
        </a>
        <a href="{{ route('admin.company-documents.index', ['category' => 'tax']) }}"
           class="nav-link {{ request('category') === 'tax' ? 'active' : '' }}">
            <i class="link-icon me-1 text-warning" data-feather="dollar-sign"></i> 📑 {{ __('index.doc_tax') }}
            <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $taxCount }}</span>
        </a>
        <a href="{{ route('admin.company-documents.index', ['category' => 'business_license']) }}"
           class="nav-link {{ request('category') === 'business_license' ? 'active' : '' }}">
            <i class="link-icon me-1 text-success" data-feather="briefcase"></i> 🏪 {{ __('index.doc_business_license') }}
            <span class="badge rounded-pill bg-success ms-1">{{ $businessCount }}</span>
        </a>
        <a href="{{ route('admin.company-documents.index', ['category' => 'nssf']) }}"
           class="nav-link {{ request('category') === 'nssf' ? 'active' : '' }}">
            <i class="link-icon me-1 text-danger" data-feather="shield"></i> 🏥 {{ __('index.doc_nssf') }}
            <span class="badge rounded-pill bg-danger ms-1">{{ $nssfCount }}</span>
        </a>
        <a href="{{ route('admin.company-documents.index', ['category' => 'contract']) }}"
           class="nav-link {{ request('category') === 'contract' ? 'active' : '' }}">
            <i class="link-icon me-1 text-info" data-feather="file-text"></i> ✍️ {{ __('index.doc_contract') }}
            <span class="badge rounded-pill bg-info text-dark ms-1">{{ $contractCount }}</span>
        </a>
        <a href="{{ route('admin.company-documents.index', ['category' => 'utility']) }}"
           class="nav-link {{ request('category') === 'utility' ? 'active' : '' }}">
            <i class="link-icon me-1 text-primary" data-feather="zap"></i> ⚡ {{ __('index.doc_utility') }}
            <span class="badge rounded-pill bg-primary ms-1">{{ $utilityCount ?? 0 }}</span>
        </a>
    </div>

    <!-- Sub-Type Filter Pills (When 'master' Category is Active: ID, Passport, Family/Residence Book) -->
    @if(request('category') === 'master')
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3 p-2 bg-light rounded border">
            <span class="small fw-bold text-muted me-1">📂 {{ __('index.doc_sub_type') }}:</span>
            <a href="{{ route('admin.company-documents.index', ['category' => 'master']) }}"
               class="badge {{ !request('doc_sub_type') ? 'bg-primary text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                {{ __('index.all_documents') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'master', 'doc_sub_type' => 'national_id']) }}"
               class="badge {{ request('doc_sub_type') === 'national_id' ? 'bg-primary text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                🆔 {{ __('index.doc_type_national_id') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'master', 'doc_sub_type' => 'passport']) }}"
               class="badge {{ request('doc_sub_type') === 'passport' ? 'bg-info text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                🛂 {{ __('index.doc_type_passport') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'master', 'doc_sub_type' => 'family_book']) }}"
               class="badge {{ request('doc_sub_type') === 'family_book' ? 'bg-success text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                📘 {{ __('index.doc_type_family_book') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'master', 'doc_sub_type' => 'residence_book']) }}"
               class="badge {{ request('doc_sub_type') === 'residence_book' ? 'bg-warning text-dark' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                📗 {{ __('index.doc_type_residence_book') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'master', 'doc_sub_type' => 'other_master']) }}"
               class="badge {{ request('doc_sub_type') === 'other_master' ? 'bg-secondary text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                📑 {{ __('index.doc_type_other_master') }}
            </a>
        </div>
    @endif

    <!-- Sub-Type Filter Pills (When 'utility' Category is Active: Rent, EDC, PPWSA, Internet, Waste) -->
    @if(request('category') === 'utility')
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3 p-2 bg-light rounded border border-info">
            <span class="small fw-bold text-info me-1">📂 {{ __('index.doc_sub_type') }}:</span>
            <a href="{{ route('admin.company-documents.index', ['category' => 'utility']) }}"
               class="badge {{ !request('doc_sub_type') ? 'bg-primary text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                {{ __('index.all_documents') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'utility', 'doc_sub_type' => 'rent']) }}"
               class="badge {{ request('doc_sub_type') === 'rent' ? 'bg-primary text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                🏠 {{ __('index.doc_type_rent') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'utility', 'doc_sub_type' => 'electricity']) }}"
               class="badge {{ request('doc_sub_type') === 'electricity' ? 'bg-warning text-dark' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                ⚡ {{ __('index.doc_type_electricity') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'utility', 'doc_sub_type' => 'water']) }}"
               class="badge {{ request('doc_sub_type') === 'water' ? 'bg-info text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                💧 {{ __('index.doc_type_water') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'utility', 'doc_sub_type' => 'internet']) }}"
               class="badge {{ request('doc_sub_type') === 'internet' ? 'bg-success text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                🌐 {{ __('index.doc_type_internet') }}
            </a>
            <a href="{{ route('admin.company-documents.index', ['category' => 'utility', 'doc_sub_type' => 'waste']) }}"
               class="badge {{ request('doc_sub_type') === 'waste' ? 'bg-secondary text-white' : 'bg-white text-dark border' }} text-decoration-none py-2 px-3">
                🗑️ {{ __('index.doc_type_waste') }}
            </a>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.company-documents.index') }}" method="GET" class="row g-2 align-items-center">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('doc_sub_type'))
                    <input type="hidden" name="doc_sub_type" value="{{ request('doc_sub_type') }}">
                @endif

                <!-- Search -->
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="link-icon" data-feather="search"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0" name="search"
                               value="{{ request('search') }}" placeholder="{{ __('index.search_document_placeholder') }}">
                    </div>
                </div>

                <!-- Branch Filter -->
                <div class="col-lg-2 col-md-4">
                    <select class="form-select form-select-sm" name="branch_id">
                        <option value="">-- {{ __('index.all_branches') }} --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string) request('branch_id') === (string) $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-2 col-md-4">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">-- {{ __('index.all_status') }} --</option>
                        <option value="valid" {{ request('status') === 'valid' ? 'selected' : '' }}>🟢 {{ __('index.status_valid') }}</option>
                        <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>🟡 {{ __('index.status_expiring_soon') }}</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>🔴 {{ __('index.status_expired') }}</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div class="col-lg-2 col-md-4">
                    <input type="number" min="2018" max="2035" class="form-control form-control-sm" name="year"
                           value="{{ request('year') }}" placeholder="{{ __('index.year') }} ({{ date('Y') }})">
                </div>

                <!-- Filter Actions -->
                <div class="col-lg-3 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="link-icon me-1" data-feather="filter"></i> {{ __('index.filter') }}
                    </button>
                    <a href="{{ route('admin.company-documents.index', request('category') ? ['category' => request('category')] : []) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="link-icon me-1" data-feather="refresh-cw"></i> {{ __('index.reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Document Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="min-width: 260px;">{{ __('index.document_title') }} & {{ __('index.document_category') }}</th>
                            <th style="min-width: 130px;">{{ __('index.branch') }}</th>
                            <th style="min-width: 150px;">{{ __('index.issuing_authority') }}</th>
                            <th style="min-width: 110px;">{{ __('index.issue_date') }}</th>
                            <th style="min-width: 140px;">{{ __('index.expiry_date') }}</th>
                            <th style="min-width: 100px;">{{ __('index.cost_fee') }}</th>
                            <th class="text-center" style="min-width: 110px;">{{ __('index.status') }}</th>
                            <th class="text-center" style="min-width: 130px;">{{ __('index.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            @php
                                $days = $doc->days_remaining;
                                $status = $doc->calculated_status;
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">
                                    {{ ($documents->firstItem() ?? 1) + $loop->index }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- File format icon badge -->
                                        @if($doc->is_pdf)
                                            <span class="file-icon-badge file-pdf" title="PDF Document">PDF</span>
                                        @elseif($doc->is_image)
                                            <span class="file-icon-badge file-image" title="Image / Scan">IMG</span>
                                        @elseif($doc->file_type === 'word')
                                            <span class="file-icon-badge file-word" title="Word Document">DOC</span>
                                        @elseif($doc->file_type === 'excel')
                                            <span class="file-icon-badge file-excel" title="Excel Sheet">XLS</span>
                                        @else
                                            <span class="file-icon-badge file-other" title="File">FILE</span>
                                        @endif

                                        <div>
                                            <a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none btn-preview-doc" data-id="{{ $doc->id }}">
                                                {{ $doc->title }}
                                            </a>

                                            <!-- Document Holder / Owner Name (if specified) -->
                                            @if($doc->holder_name)
                                                <div class="small fw-semibold text-primary mt-0">
                                                    <i class="link-icon" data-feather="user" style="width: 12px; height: 12px;"></i> {{ $doc->holder_name }}
                                                </div>
                                            @endif

                                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                <!-- Master Sub-Type Badge (ID, Passport, Books) -->
                                                @if($doc->doc_sub_type_label)
                                                    <span class="badge bg-light text-primary border" style="font-size: 10px;">
                                                        {{ $doc->doc_sub_type_label }}
                                                    </span>
                                                @endif

                                                @if($doc->doc_number)
                                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">
                                                        #{{ $doc->doc_number }}
                                                    </span>
                                                @endif
                                                <span class="badge" style="background-color: {{ $doc->category?->color ?? '#64748b' }}; font-size: 10px;">
                                                    {{ $doc->category?->name ?? ucfirst($doc->category_code) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($doc->branch)
                                        <span class="fw-semibold text-dark">{{ $doc->branch->name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">🏢 {{ __('index.head_office_all_branches') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small">{{ $doc->issuing_authority ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="small">{{ $doc->issue_date ? $doc->issue_date->format('d-M-Y') : '—' }}</span>
                                </td>
                                <td>
                                    @if($doc->expiry_date)
                                        <div class="fw-bold small">{{ $doc->expiry_date->format('d-M-Y') }}</div>
                                        @if($days < 0)
                                            <span class="countdown-pill countdown-danger">⚠️ {{ __('index.days_overdue_count', ['count' => abs($days)]) }}</span>
                                        @elseif($days <= ($doc->alert_before_days ?: 30))
                                            <span class="countdown-pill countdown-warning">⏳ {{ __('index.days_remaining_count', ['count' => $days]) }}</span>
                                        @else
                                            <span class="countdown-pill countdown-safe">{{ __('index.days_remaining_count', ['count' => $days]) }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted border">{{ __('index.permanent_no_expiry') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($doc->cost_amount)
                                        <span class="fw-bold text-dark">{{ $doc->formatted_cost }}</span>
                                        @if($doc->receipt_number)
                                            <div class="text-muted" style="font-size: 10px;">{{ $doc->receipt_number }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($status === 'expired')
                                        <span class="badge bg-danger">🔴 {{ __('index.status_expired') }}</span>
                                    @elseif($status === 'expiring_soon')
                                        <span class="badge bg-warning text-dark">🟡 {{ __('index.status_expiring_soon') }}</span>
                                    @elseif($status === 'archived')
                                        <span class="badge bg-secondary">⚪ {{ __('index.status_archived') }}</span>
                                    @else
                                        <span class="badge bg-success">🟢 {{ __('index.status_valid') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-xs" role="group">
                                        <!-- Preview -->
                                        <button type="button" class="btn btn-outline-primary btn-preview-doc" data-id="{{ $doc->id }}" title="{{ __('index.document_preview') }}">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </button>
                                        <!-- Download -->
                                        <a href="{{ route('admin.company-documents.download', $doc->id) }}" class="btn btn-outline-secondary" title="{{ __('index.download') }}">
                                            <i class="link-icon" data-feather="download"></i>
                                        </a>
                                        <!-- Edit -->
                                        <button type="button" class="btn btn-outline-info btn-edit-doc" data-id="{{ $doc->id }}" title="{{ __('index.edit') }}">
                                            <i class="link-icon" data-feather="edit-2"></i>
                                        </button>
                                        <!-- Delete -->
                                        <button type="button" class="btn btn-outline-danger btn-delete-doc" data-id="{{ $doc->id }}" data-title="{{ $doc->title }}" title="{{ __('index.delete') }}">
                                            <i class="link-icon" data-feather="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="link-icon mb-2" data-feather="inbox" style="width: 48px; height: 48px;"></i>
                                        <p class="mb-2 fw-bold">{{ __('index.no_documents_found') }}</p>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
                                            + {{ __('index.add_first_document') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($documents->hasPages())
            <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    {{ __('index.showing_documents_range', ['from' => $documents->firstItem() ?? 0, 'to' => $documents->lastItem() ?? 0, 'total' => $documents->total() ?? 0]) }}
                </span>
                <div>
                    {{ $documents->links() }}
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Modals -->
@include('admin.company-document.partials.create-modal')
@include('admin.company-document.partials.edit-modal')
@include('admin.company-document.partials.preview-modal')

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toggle presets visibility based on category
        function checkCategoryState() {
            const cat = $('#create_category_code').val();
            if (cat === 'master') {
                $('#create_master_presets').slideDown(200);
                $('#create_utility_presets').slideUp(200);
            } else if (cat === 'utility') {
                $('#create_master_presets').slideUp(200);
                $('#create_utility_presets').slideDown(200);
            } else {
                $('#create_master_presets').slideUp(200);
                $('#create_utility_presets').slideUp(200);
            }
        }
        $('#create_category_code').on('change', checkCategoryState);
        checkCategoryState();

        // Preset sub-type click handler
        $(document).on('click', '.btn-preset-sub', function () {
            const cat = $(this).data('category') || 'master';
            const subType = $(this).data('type');
            const titlePrefix = $(this).data('title');
            $('#create_category_code').val(cat);
            checkCategoryState();
            $('#create_doc_sub_type').val(subType);
            
            const currentTitle = $('#create_title').val();
            if (!currentTitle || currentTitle.includes('អត្តសញ្ញាណប័ណ្ណ') || currentTitle.includes('លិខិតឆ្លងដែន') || currentTitle.includes('សៀវភៅ') || currentTitle.includes('វិក្កយបត្រ') || currentTitle.includes('ថ្លៃ')) {
                $('#create_title').val(titlePrefix + ' - ');
            }
            $('#create_holder_name').focus();
        });

        // 1. Preview Document Modal Handler
        $(document).on('click', '.btn-preview-doc', function () {
            const docId = $(this).data('id');
            const previewModal = new bootstrap.Modal(document.getElementById('previewDocumentModal'));
            
            // Reset modal states
            $('#previewLoading').show();
            $('#previewPdfFrame').hide().attr('src', '');
            $('#previewImageWrapper').hide();
            $('#previewImage').attr('src', '');
            $('#previewUnsupported').hide();
            
            previewModal.show();

            $.get(`{{ url('admin/company-documents') }}/${docId}`, function (res) {
                $('#previewLoading').hide();
                if (res.success && res.data) {
                    const doc = res.data;
                    let titleText = doc.title;
                    if (doc.holder_name) {
                        titleText += ' (' + doc.holder_name + ')';
                    }
                    if (doc.doc_number) {
                        titleText += ' [#' + doc.doc_number + ']';
                    }

                    $('#previewDocumentModalLabel').text(titleText);
                    $('#previewCategoryBadge').text(res.category_name || (doc.category ? doc.category.name : doc.category_code));
                    $('#previewDownloadBtn').attr('href', `{{ url('admin/company-documents') }}/${docId}/download`);
                    $('#previewFallbackDownloadBtn').attr('href', `{{ url('admin/company-documents') }}/${docId}/download`);
                    
                    const fileUrl = `{{ url('admin/company-documents') }}/${docId}/preview`;

                    if (res.is_pdf) {
                        $('#previewPdfFrame').attr('src', fileUrl).show();
                    } else if (res.is_image) {
                        $('#previewImage').attr('src', fileUrl);
                        $('#previewImageWrapper').show();
                    } else {
                        $('#previewUnsupportedName').text(doc.file_name || '{{ __("index.attach_document") }}');
                        $('#previewUnsupported').show();
                    }
                }
            }).fail(function () {
                $('#previewLoading').hide();
                Swal.fire('Error', '{{ __("index.failed_to_load_preview") }}', 'error');
            });
        });

        // 2. Edit Document Handler
        $(document).on('click', '.btn-edit-doc', function () {
            const docId = $(this).data('id');
            const editModal = new bootstrap.Modal(document.getElementById('editDocumentModal'));

            $.get(`{{ url('admin/company-documents') }}/${docId}`, function (res) {
                if (res.success && res.data) {
                    const doc = res.data;
                    $('#editDocumentForm').attr('action', `{{ url('admin/company-documents') }}/${docId}`);
                    $('#edit_doc_id').val(doc.id);
                    $('#edit_category_code').val(doc.category_code);
                    $('#edit_doc_sub_type').val(doc.doc_sub_type || '');
                    $('#edit_holder_name').val(doc.holder_name || '');
                    $('#edit_branch_id').val(doc.branch_id || '');
                    $('#edit_title').val(doc.title);
                    $('#edit_doc_number').val(doc.doc_number || '');
                    $('#edit_issuing_authority').val(doc.issuing_authority || '');
                    $('#edit_confidentiality_level').val(doc.confidentiality_level || 'internal');
                    $('#edit_issue_date').val(doc.issue_date ? doc.issue_date.substring(0, 10) : '');
                    $('#edit_expiry_date').val(doc.expiry_date ? doc.expiry_date.substring(0, 10) : '');
                    $('#edit_alert_before_days').val(doc.alert_before_days || 30);
                    $('#edit_cost_amount').val(doc.cost_amount || '');
                    $('#edit_currency').val(doc.currency || 'USD');
                    $('#edit_receipt_number').val(doc.receipt_number || '');
                    $('#edit_description').val(doc.description || '');

                    if (doc.file_name) {
                        $('#currentFileDisplay').html(`{{ __("index.current_file") }}: <a href="{{ url('admin/company-documents') }}/${docId}/download" class="fw-bold" target="_blank">${doc.file_name}</a>`);
                    } else {
                        $('#currentFileDisplay').empty();
                    }

                    editModal.show();
                }
            }).fail(function () {
                Swal.fire('Error', '{{ __("index.failed_to_fetch_document") }}', 'error');
            });
        });

        // 3. Delete Document Handler
        $(document).on('click', '.btn-delete-doc', function () {
            const docId = $(this).data('id');
            const docTitle = $(this).data('title');

            Swal.fire({
                title: '{{ __("index.delete_document_confirmation") }}',
                text: `${docTitle} - {{ __("index.delete_document_warning") }}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '{{ __("index.confirm_delete_button") }}',
                cancelButtonText: '{{ __("index.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('admin/company-documents') }}/${docId}`,
                        type: 'DELETE',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    title: '{{ __("index.success") ?? "Success" }}',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || '{{ __("index.failed_to_delete_document") }}', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
