@extends('layouts.master')

@section('title', __('index.certificate_management'))

@section('action', __('index.certificate_directory'))

@section('button')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.certificates.templates') }}" class="btn btn-outline-primary">
            <i class="link-icon" data-feather="layout"></i> {{ __('index.certificate_templates') }}
        </a>
        @can('certificate_create')
        <a href="{{ route('admin.certificates.batch') }}" class="btn btn-outline-info">
            <i class="link-icon" data-feather="users"></i> {{ __('index.batch_issue_certificate') }}
        </a>
        <a href="{{ route('admin.certificates.create') }}" class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i> {{ __('index.issue_certificate') }}
        </a>
        @endcan
    </div>
@endsection

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <!-- Breadcrumb -->
    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.certificate_management') }}</li>
        </ol>
    </nav>

    <!-- Batch issue result: every saved certificate is already in the list below -->
    @php
        $batchIssuedIds = session('batch_issued_ids', []);
    @endphp
    @if(!empty($batchIssuedIds))
        <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #065f46 0%, #059669 100%);">
            <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="text-white">
                    <div class="fw-bold">
                        <i data-feather="check-circle" style="width:16px;height:16px;"></i>
                        Batch saved — {{ count($batchIssuedIds) }} certificates were issued and listed below.
                    </div>
                    <div class="small text-white-50 mt-1">
                        @foreach($batchIssuedIds as $issuedId)
                            <span class="badge bg-white text-dark font-monospace me-1 mb-1">{{ $issuedId }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light btn-sm fw-bold" onclick="openPrintPreviewModal('{{ route('admin.certificates.batchPrint', ['ids' => $batchIssuedIds]) }}', 'Batch Print Preview ({{ count($batchIssuedIds) }})')">
                        <i data-feather="printer" style="width:14px;height:14px;"></i> Print All ({{ count($batchIssuedIds) }})
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="openPrintPreviewModal('{{ route('admin.certificates.batchPrint', ['ids' => $batchIssuedIds]) }}', 'Batch Print Preview ({{ count($batchIssuedIds) }})')">
                        <i data-feather="eye" style="width:14px;height:14px;"></i> Preview & Customize
                    </button>
                </div>
            </div>
        </div>

        <!-- Ask to print after batch save -->
        <div class="modal fade" id="batchPrintPromptModal" tabindex="-1" aria-labelledby="batchPrintPromptLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold" id="batchPrintPromptLabel"><i data-feather="check-circle" class="me-1" style="width:18px;height:18px;"></i> Certificates Saved</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2 fw-semibold">បានរក្សាទុក និងចេញវិញ្ញាបនបត្រ {{ count($batchIssuedIds) }} សន្លឹកដោយជោគជ័យ។</p>
                        <p class="mb-0 text-muted">Successfully saved and issued {{ count($batchIssuedIds) }} certificate(s). Would you like to print them now?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Not now</button>
                        <button type="button" class="btn btn-primary fw-bold" id="batchPrintPromptYes"
                                data-print-url="{{ route('admin.certificates.batchPrint', ['ids' => $batchIssuedIds]) }}"
                                data-print-title="Batch Print Preview ({{ count($batchIssuedIds) }})">
                            <i data-feather="printer" style="width:14px;height:14px;"></i> Yes, Print / Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Total Certificates</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalCertificates ?? 0 }}</h3>
                        </div>
                        <div class="avatar bg-primary-subtle text-primary p-2 rounded-circle">
                            <i data-feather="award" style="width: 28px; height: 28px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Digital HR Hours</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $totalTrainingHours ?? 0 }} hrs</h3>
                        </div>
                        <div class="avatar bg-success-subtle text-success p-2 rounded-circle">
                            <i data-feather="clock" style="width: 28px; height: 28px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Leadership Honors</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $leaderCount ?? 0 }}</h3>
                        </div>
                        <div class="avatar bg-warning-subtle text-warning p-2 rounded-circle">
                            <i data-feather="star" style="width: 28px; height: 28px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="card shadow-sm border-0 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Service Excellence</p>
                            <h3 class="mb-0 fw-bold text-info">{{ $serviceCount ?? 0 }}</h3>
                        </div>
                        <div class="avatar bg-info-subtle text-info p-2 rounded-circle">
                            <i data-feather="users" style="width: 28px; height: 28px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="card-title mb-0 fw-bold">
                <i class="link-icon text-primary me-1" data-feather="filter"></i> {{ __('index.filter') ?? 'Filter Records' }}
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.certificates.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Search Recipient / ID / Title</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. Reach Chhoeun, KY-LDR..." value="{{ $filterParameters['search'] ?? '' }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <option value="leader" {{ ($filterParameters['category'] ?? '') === 'leader' ? 'selected' : '' }}>Leadership</option>
                            <option value="service" {{ ($filterParameters['category'] ?? '') === 'service' ? 'selected' : '' }}>Customer Service</option>
                            <option value="course" {{ ($filterParameters['category'] ?? '') === 'course' ? 'selected' : '' }}>Training CPD</option>
                            <option value="custom" {{ ($filterParameters['category'] ?? '') === 'custom' ? 'selected' : '' }}>Custom Honor</option>
                        </select>
                    </div>

                    @if(isset($branches) && $branches->count() > 0)
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ ($filterParameters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if(isset($departments) && $departments->count() > 0)
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($filterParameters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->dept_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i data-feather="search" style="width: 14px; height: 14px;"></i> {{ __('index.filter') ?? 'Filter' }}
                        </button>
                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-light btn-sm">
                            <i data-feather="rotate-ccw" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Certificates Data Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold">Issued Certificates Directory</h6>
            <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill">
                Showing {{ $certificates->count() }} of {{ $certificates->total() }} records
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 140px;">Credential ID</th>
                            <th>Recipient (ម្ចាស់ជ័យលាភី)</th>
                            <th>Certificate Title</th>
                            <th>Category</th>
                            <th style="min-width: 250px;">Layout Model / Settings</th>
                            <th>Digital HR</th>
                            <th>Issue Date</th>
                            <th class="text-end pe-3" style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $cert)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary">
                                    <span class="badge bg-light text-dark border border-secondary-subtle">
                                        {{ $cert->credential_id }}
                                    </span>
                                </td>
                                <td>
                                    @if($cert->khmer_recipient_name)
                                        <div class="fw-bold text-dark">{{ $cert->khmer_recipient_name }}</div>
                                    @endif
                                    <div class="text-muted small">{{ $cert->recipient_name }}</div>
                                    @if($cert->employee_id)
                                        <span class="badge bg-secondary-subtle text-secondary small">{{ $cert->employee_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cert->khmer_title)
                                        <div class="fw-semibold text-dark">{{ $cert->khmer_title }}</div>
                                    @endif
                                    <div class="text-muted small text-truncate" style="max-width: 240px;">
                                        {{ $cert->certificate_title }}
                                    </div>
                                </td>
                                <td>
                                    @if($cert->category === 'leader')
                                        <span class="badge bg-warning text-dark">Leadership</span>
                                    @elseif($cert->category === 'service')
                                        <span class="badge bg-info text-dark">Service</span>
                                    @elseif($cert->category === 'course')
                                        <span class="badge bg-success">CPD Training</span>
                                    @else
                                        <span class="badge bg-secondary">Honor</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $layoutModel = $cert->template;
                                        $layoutSettings = $layoutModel ? [
                                            'source' => 'layout_model',
                                            'layout_model' => [
                                                'id' => $layoutModel->id,
                                                'template_code' => $layoutModel->template_code,
                                                'title' => $layoutModel->title,
                                                'khmer_title' => $layoutModel->khmer_title,
                                                'category' => $layoutModel->category,
                                                'layout_template' => $layoutModel->layout_template,
                                                'version_tag' => $layoutModel->version_tag,
                                                'language_mode' => $layoutModel->language_mode,
                                                'theme' => $layoutModel->theme,
                                                'description' => $layoutModel->description,
                                                'khmer_description' => $layoutModel->khmer_description,
                                                'issuer_name' => $layoutModel->issuer_name,
                                                'issuer_khmer_name' => $layoutModel->issuer_khmer_name,
                                                'department' => $layoutModel->department,
                                                'logo_url' => $layoutModel->logo_url,
                                                'certificate_title' => $layoutModel->certificate_title,
                                                'khmer_certificate_title' => $layoutModel->khmer_certificate_title,
                                                'subtitle' => $layoutModel->subtitle,
                                                'khmer_subtitle' => $layoutModel->khmer_subtitle,
                                                'authority_preamble' => $layoutModel->authority_preamble,
                                                'khmer_preamble' => $layoutModel->khmer_preamble,
                                                'course_or_achievement_title' => $layoutModel->course_or_achievement_title,
                                                'khmer_course_or_achievement_title' => $layoutModel->khmer_course_or_achievement_title,
                                                'citation' => $layoutModel->citation,
                                                'khmer_citation' => $layoutModel->khmer_citation,
                                                'citation_closing' => $layoutModel->citation_closing,
                                                'training_hours' => $layoutModel->training_hours,
                                                'hr_competency_track' => $layoutModel->hr_competency_track,
                                                'compliance_status' => $layoutModel->compliance_status,
                                                'signatories' => $layoutModel->signatories_json,
                                                'styling' => $layoutModel->styling_json,
                                                'seal' => $layoutModel->seal_json,
                                                'payload_json' => $layoutModel->payload_json,
                                                'is_default' => $layoutModel->is_default,
                                                'is_active' => $layoutModel->is_active,
                                                'sort_order' => $layoutModel->sort_order,
                                            ],
                                            'certificate_layout' => [
                                                'template_id' => $cert->template_id,
                                                'layout_template' => $cert->layout_template,
                                                'theme' => $cert->theme,
                                                'language_mode' => $cert->language_mode,
                                            ],
                                        ] : [
                                            'source' => 'certificate',
                                            'layout_model' => null,
                                            'certificate_layout' => [
                                                'template_id' => $cert->template_id,
                                                'layout_template' => $cert->layout_template,
                                                'theme' => $cert->theme,
                                                'language_mode' => $cert->language_mode,
                                                'payload_json' => $cert->payload_json,
                                            ],
                                        ];
                                    @endphp
                                    @if($layoutModel)
                                        <div class="fw-semibold text-dark">{{ $layoutModel->khmer_title ?: $layoutModel->title }}</div>
                                        <div class="small text-muted">
                                            <code>{{ $layoutModel->template_code }}</code>
                                            @if($layoutModel->version_tag)
                                                <span class="badge bg-primary-subtle text-primary ms-1">{{ $layoutModel->version_tag }}</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">Theme: {{ $layoutModel->theme }} &middot; {{ $layoutModel->language_mode }}</div>
                                    @else
                                        <div class="fw-semibold text-dark">{{ $cert->layout_template ?: 'N/A' }}</div>
                                        <div class="small text-muted">Theme: {{ $cert->theme ?: 'N/A' }} &middot; {{ $cert->language_mode ?: 'N/A' }}</div>
                                        <span class="badge bg-light text-secondary border">No linked Layout Model</span>
                                    @endif
                                    <div class="mt-1">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm py-0 px-2 js-show-layout-settings"
                                                style="font-size: 11px;"
                                                data-layout-settings='@json($layoutSettings, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)'
                                                data-title="{{ e(($layoutModel?->title ?? $cert->certificate_title) . ' — Layout Settings') }}">
                                            <i data-feather="sliders" style="width:12px;height:12px;"></i> View complete layout settings
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $cert->training_hours ?? 0 }} hrs</div>
                                    <div class="small text-muted">{{ $cert->compliance_status ?? 'Accredited' }}</div>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $cert->issue_date }}</div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-primary" title="Print Preview &amp; Customize" onclick="openPrintPreviewModal('{{ route('admin.certificates.show', $cert->credential_id) }}', 'Print Preview — {{ addslashes($cert->credential_id) }}')">
                                            <i data-feather="printer" style="width: 14px; height: 14px;"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" title="View / Preview Certificate" onclick="openPrintPreviewModal('{{ route('admin.certificates.show', $cert->credential_id) }}', 'Certificate Preview — {{ addslashes($cert->credential_id) }}')">
                                            <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                        </button>
                                        @can('certificate_download')
                                        <a href="{{ route('admin.certificates.downloadPdf', $cert->credential_id) }}" class="btn btn-outline-secondary" title="Download PDF">
                                            <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        @endcan
                                        <a href="{{ route('certificates.verify', $cert->credential_id) }}" target="_blank" class="btn btn-outline-success" title="Verify QR">
                                            <i data-feather="check-circle" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        @can('certificate_edit')
                                        <a href="{{ route('admin.certificates.edit', $cert->id) }}" class="btn btn-outline-info" title="Edit">
                                            <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        @endcan
                                        @can('certificate_delete')
                                        <form action="{{ route('admin.certificates.destroy', $cert->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this certificate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i data-feather="award" class="mb-2" style="width: 40px; height: 40px;"></i>
                                    <p class="mb-0">No certificates found. Click <strong>Issue Certificate</strong> to award your first certificate.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(method_exists($certificates, 'links'))
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $certificates->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
    <!-- Layout Model Complete Settings Modal -->
    <div class="modal fade" id="layoutSettingsModal" tabindex="-1" aria-labelledby="layoutSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color:#fff;">
                    <h5 class="modal-title fw-bold" id="layoutSettingsModalLabel">Complete Layout Settings from Layout Model</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="layoutSettingsMeta" class="mb-3"></div>
                    <div id="layoutSettingsSections"></div>
                    <details class="mt-3">
                        <summary class="fw-semibold" style="cursor: pointer;">Raw JSON (complete)</summary>
                        <pre id="layoutSettingsJson" class="bg-light border rounded p-3 small mt-2 mb-0" style="max-height: 45vh; overflow:auto; white-space: pre-wrap; word-break: break-word;"></pre>
                    </details>
                </div>
<div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Print Preview Studio Modal (CertiCraft-style customization) -->
    <div class="modal fade" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 99vw; width: 99vw; margin: 0.25rem auto;">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="height: 98vh; border-radius: 14px; background:#0f172a;">
                <div class="modal-header py-2 px-3 border-0" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-warning text-dark fw-bold">🎨 CertiCraft Print Studio</span>
                        <h6 class="modal-title fw-bold text-white mb-0" id="printPreviewModalLabel">Certificate Print Preview</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="#" id="printPreviewStudioBtn" target="_blank" class="btn btn-warning btn-sm text-dark fw-bold">
                            <i data-feather="layout" style="width:14px;height:14px;"></i> Open in CertiCraft Studio 🎨
                        </a>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="studioPrintReset()">
                            <i data-feather="rotate-ccw" style="width:14px;height:14px;"></i> Reset
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="reloadPrintPreviewFrame()">
                            <i data-feather="refresh-cw" style="width:14px;height:14px;"></i> Reload
                        </button>
                        <button type="button" class="btn btn-light btn-sm fw-bold" onclick="printPreviewFrame()">
                            <i data-feather="printer" style="width:14px;height:14px;"></i> Print
                        </button>
                        <a href="#" id="printPreviewOpenNewTab" target="_blank" class="btn btn-outline-light btn-sm">
                            <i data-feather="external-link" style="width:14px;height:14px;"></i> Open Tab
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 d-flex" style="height: calc(98vh - 54px); background:#1e293b;">
                    <!-- Left: Studio-style customization panel -->
                    <div class="no-print bg-white border-end" style="width: 340px; min-width: 300px; overflow-y: auto;">
                        <div class="p-3">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <span style="font-size:20px;">⚙️</span>
                                <div>
                                    <div class="fw-bold text-dark">Customize Before Print</div>
                                    <div class="small text-muted">កំណត់មុនបោះពុម្ព · Studio-style controls</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold text-primary small text-uppercase mb-2">Layout & Canvas</div>
                                <label class="form-label small mb-1 d-flex justify-content-between"><span>Scale</span><span id="sp_scale_val" class="font-monospace">100%</span></label>
                                <input id="sp_scale" type="range" min="40" max="120" value="100" class="form-range" oninput="applyStudioPrintSettings()">
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Page margin (mm)</label>
                                        <input id="sp_margin" type="number" min="0" max="30" value="0" class="form-control form-control-sm" oninput="applyStudioPrintSettings()">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Gap (mm)</label>
                                        <input id="sp_gap" type="number" min="0" max="40" value="4" class="form-control form-control-sm" oninput="applyStudioPrintSettings()">
                                    </div>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="sp_center" checked onchange="applyStudioPrintSettings()">
                                    <label class="form-check-label small" for="sp_center">Center certificate on page</label>
                                </div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" id="sp_bg" checked onchange="applyStudioPrintSettings()">
                                    <label class="form-check-label small" for="sp_bg">Keep background colors</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold text-primary small text-uppercase mb-2">Design Elements</div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_border" data-sel=".cert-border-svg" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_border">Border & decorative frame</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_ribbon" data-sel=".cert-ribbon-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_ribbon">Hanging ribbon / medallion</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_header" data-sel=".cert-header-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_header">Header logo & issuer</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_banner" data-sel=".cert-banner-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_banner">Presentation banner</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_citation" data-sel=".cert-citation-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_citation">Citation / recognition text</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_photo" data-sel=".cert-photo-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_photo">Recipient photo & ID box</label></div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold text-primary small text-uppercase mb-2">Footer & Verification</div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_credential" data-sel=".cert-credential-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_credential">Date & certificate ID</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_signatures" data-sel=".cert-signature-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_signatures">Signatures</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_seal" data-sel=".cert-seal-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_seal">Gold seal</label></div>
                                <div class="form-check"><input class="form-check-input sp-toggle" type="checkbox" id="sp_qr" data-sel=".cert-qr-zone" checked onchange="applyStudioPrintSettings()"><label class="form-check-label small" for="sp_qr">QR & verify badge</label></div>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-2">
                                <strong>Tip:</strong> For full layout/content/template design (like CertiCraft Studio), use <em>Open in CertiCraft Studio 🎨</em>. Here you can fine-tune print output before printing.
                            </div>
                            <div class="text-muted small">Enable <strong>Background graphics</strong> in the browser print dialog for best color fidelity.</div>
                        </div>
                    </div>

                    <!-- Right: Live preview -->
                    <div class="flex-grow-1 position-relative" style="background: #334155;">
                        <iframe id="printPreviewFrame" title="Certificate Print Preview" class="w-100 h-100 border-0" style="background:#cbd5e1;"></iframe>
                        <div id="printPreviewLoading" class="position-absolute top-50 start-50 translate-middle text-white text-center" style="pointer-events:none;">
                            <div class="spinner-border text-light mb-2" role="status"></div>
                            <div class="small">Loading preview…</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    var _printPreviewUrl = '';
    var _studioBaseUrl = @json(route('certificates.studio'));

    function extractCredentialFromUrl(url) {
        try {
            // admin show: /.../certificates/{credential} or certificates/{id}
            var m = url.match(/\/certificates\/([^\/?]+)(?:\?|$)/i);
            if (m && m[1] && m[1].toLowerCase() !== 'batch' && m[1].toLowerCase() !== 'batchprint' && m[1].toLowerCase() !== 'batch-preview') {
                return decodeURIComponent(m[1]);
            }
        } catch(e) {}
        return null;
    }

    function openPrintPreviewModal(url, title) {
        _printPreviewUrl = url;
        var modalEl = document.getElementById('printPreviewModal');
        var frame = document.getElementById('printPreviewFrame');
        var label = document.getElementById('printPreviewModalLabel');
        var openTab = document.getElementById('printPreviewOpenNewTab');
        var studioBtn = document.getElementById('printPreviewStudioBtn');
        var loading = document.getElementById('printPreviewLoading');

        if (label) label.textContent = title || 'Certificate Print Preview';
        if (openTab) openTab.href = url;
        if (studioBtn) {
            var cred = extractCredentialFromUrl(url);
            if (cred) {
                studioBtn.href = _studioBaseUrl + '?id=' + encodeURIComponent(cred);
                studioBtn.classList.remove('disabled');
                studioBtn.style.pointerEvents = '';
                studioBtn.style.opacity = '';
            } else {
                studioBtn.href = _studioBaseUrl;
                studioBtn.classList.remove('disabled');
            }
        }
        if (loading) loading.style.display = 'block';
        if (frame) frame.src = url;
        if (modalEl) {
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else if (window.jQuery && jQuery(modalEl).modal) {
                jQuery(modalEl).modal('show');
            } else {
                // fallback: basic display
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                modalEl.removeAttribute('aria-hidden');
                document.body.classList.add('modal-open');
            }
        }
        setTimeout(function(){ if (window.feather) { try { feather.replace(); } catch(e) {} } }, 50);
    }

    function getPreviewDoc() {
        var frame = document.getElementById('printPreviewFrame');
        try { return frame && frame.contentDocument ? frame.contentDocument : null; } catch(e) { return null; }
    }

    function applyStudioPrintSettings() {
        var doc = getPreviewDoc();
        if (!doc) return;
        var scale = parseInt(document.getElementById('sp_scale').value || '100', 10) / 100;
        var margin = parseFloat(document.getElementById('sp_margin').value || '0');
        var gap = parseFloat(document.getElementById('sp_gap').value || '0');
        var center = document.getElementById('sp_center').checked;
        var keepBg = document.getElementById('sp_bg').checked;
        var scaleVal = document.getElementById('sp_scale_val');
        if (scaleVal) scaleVal.textContent = Math.round(scale * 100) + '%';

        doc.querySelectorAll('.sp-toggle'); // noop guard
        document.querySelectorAll('#printPreviewModal .sp-toggle').forEach(function (cb) {
            var sel = cb.getAttribute('data-sel');
            if (!sel) return;
            doc.querySelectorAll(sel).forEach(function (el) {
                el.style.display = cb.checked ? '' : 'none';
            });
        });

        var containers = doc.querySelectorAll('.certificate-container');
        containers.forEach(function (el) {
            el.style.transformOrigin = center ? 'top center' : 'top left';
            el.style.transform = scale === 1 ? '' : 'scale(' + scale + ')';
        });

        var pages = doc.querySelectorAll('.batch-page');
        pages.forEach(function (p) {
            p.style.marginBottom = gap + 'mm';
            if (scale !== 1) {
                p.style.height = (760 * scale) + 'px';
                p.style.width = (1080 * scale) + 'px';
                p.style.overflow = 'visible';
            } else {
                p.style.height = '';
                p.style.width = '';
            }
        });

        // inject/update dynamic print style in iframe
        var style = doc.getElementById('studioModalPrintStyle');
        if (!style) {
            style = doc.createElement('style');
            style.id = 'studioModalPrintStyle';
            doc.head.appendChild(style);
        }
        var css = '@page { size: A4 landscape; margin: ' + margin + 'mm; }';
        css += '@media print {';
        css += ' .certificate-container { transform-origin: top center !important; transform: ' + (scale === 1 ? 'none' : 'scale(' + scale + ')') + ' !important; }';
        css += ' .batch-page { margin-bottom: 0 !important; page-break-after: always; break-after: page; height: auto !important; width: auto !important; }';
        css += ' .batch-page:last-of-type { page-break-after: auto; break-after: auto; }';
        if (!keepBg) {
            css += ' body, .certificate-container { background: #fff !important; background-image: none !important; -webkit-print-color-adjust: economy !important; print-color-adjust: economy !important; }';
        } else {
            css += ' body, .certificate-container { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }';
        }
        css += ' }';
        style.textContent = css;

        // also try iframe's own apply if present (for standalone consistency)
        try { if (frameHasFn('certPrintApply')) { /* parent-driven; avoid double */ } } catch(e) {}
    }

    function frameHasFn(name) {
        var frame = document.getElementById('printPreviewFrame');
        try { return frame && frame.contentWindow && typeof frame.contentWindow[name] === 'function'; } catch(e) { return false; }
    }

    function studioPrintReset() {
        var ids = ['sp_scale','sp_margin','sp_gap'];
        var def = { sp_scale: 100, sp_margin: 0, sp_gap: 4 };
        Object.keys(def).forEach(function(id){ var el=document.getElementById(id); if(el) el.value = def[id]; });
        document.getElementById('sp_center').checked = true;
        document.getElementById('sp_bg').checked = true;
        document.querySelectorAll('#printPreviewModal .sp-toggle').forEach(function(cb){ cb.checked = true; });
        applyStudioPrintSettings();
    }

    function reloadPrintPreviewFrame() {
        var frame = document.getElementById('printPreviewFrame');
        var loading = document.getElementById('printPreviewLoading');
        if (frame && _printPreviewUrl) {
            if (loading) loading.style.display = 'block';
            frame.src = _printPreviewUrl + (_printPreviewUrl.indexOf('?') === -1 ? '?' : '&') + '_ts=' + Date.now();
        }
    }

    function printPreviewFrame() {
        applyStudioPrintSettings();
        var frame = document.getElementById('printPreviewFrame');
        if (frame && frame.contentWindow) {
            try { frame.contentWindow.focus(); frame.contentWindow.print(); } catch(e) { window.open(_printPreviewUrl, '_blank'); }
        }
    }

    document.getElementById('printPreviewFrame')?.addEventListener('load', function () {
        var loading = document.getElementById('printPreviewLoading');
        if (loading) loading.style.display = 'none';
        // Hide inner customizer inside iframe when using studio modal to avoid duplicate panels
        var doc = getPreviewDoc();
        if (doc) {
            var inner = doc.getElementById('printCustomizer');
            if (inner) inner.style.display = 'none';
        }
        setTimeout(applyStudioPrintSettings, 80);
    });

    document.getElementById('printPreviewModal')?.addEventListener('hidden.bs.modal', function () {
        var frame = document.getElementById('printPreviewFrame');
        if (frame) frame.src = 'about:blank';
        var loading = document.getElementById('printPreviewLoading');
        if (loading) loading.style.display = 'none';
    });
</script>
<script>
(function () {
    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function isPlainObject(value) {
        return value !== null && typeof value === 'object' && !Array.isArray(value);
    }

    function renderKv(title, obj, skip) {
        skip = skip || [];
        var rows = '';
        Object.keys(obj || {}).forEach(function (key) {
            if (skip.indexOf(key) !== -1) return;
            var value = obj[key];
            if (value === null || value === undefined || value === '' || typeof value === 'object') return;
            rows += '<tr><th class="fw-normal text-muted small" style="width: 34%;">' + esc(key) + '</th>'
                + '<td class="small">' + esc(value) + '</td></tr>';
        });
        if (!rows) return '';
        return '<h6 class="fw-bold border-bottom pb-2 mb-2 mt-3">' + esc(title) + '</h6>'
            + '<table class="table table-sm table-bordered align-middle mb-0"><tbody>' + rows + '</tbody></table>';
    }

    function renderBlock(title, value) {
        if (value === null || value === undefined) return '';
        if (Array.isArray(value) && value.length === 0) return '';
        if (isPlainObject(value) && Object.keys(value).length === 0) return '';
        return '<h6 class="fw-bold border-bottom pb-2 mb-2 mt-3">' + esc(title) + '</h6>'
            + '<pre class="bg-light border rounded p-2 small mb-0" style="max-height: 32vh; overflow: auto; white-space: pre-wrap; word-break: break-word;">'
            + esc(JSON.stringify(value, null, 2)) + '</pre>';
    }

    window.showLayoutSettings = function (data, title) {
        data = data || {};
        var lm = data.layout_model || {};
        var cl = data.certificate_layout || {};
        var isModel = data.source === 'layout_model' && lm && lm.template_code;

        var titleEl = document.getElementById('layoutSettingsModalLabel');
        if (titleEl) titleEl.textContent = title || 'Complete Layout Settings from Layout Model';

        var metaEl = document.getElementById('layoutSettingsMeta');
        if (metaEl) {
            var meta = isModel
                ? '<div class="d-flex flex-wrap align-items-center gap-2 mb-2">'
                    + '<span class="badge bg-success">Source: Layout Model</span>'
                    + '<span class="badge bg-light text-dark border">' + esc(lm.template_code) + '</span>'
                    + (lm.version_tag ? '<span class="badge bg-primary-subtle text-primary">' + esc(lm.version_tag) + '</span>' : '')
                    + '</div>'
                : '<div class="alert alert-warning mb-2"><strong>Source:</strong> Certificate layout &mdash; no linked Layout Model found. Showing the layout settings stored on the certificate.</div>';
            metaEl.innerHTML = meta;
        }

        var sections = document.getElementById('layoutSettingsSections');
        if (sections) {
            var html = '';
            if (isModel) {
                html += renderKv('Layout model', lm, ['signatories', 'styling', 'seal', 'payload_json']);
                html += renderBlock('Signatories', lm.signatories);
                html += renderBlock('Styling', lm.styling);
                html += renderBlock('Seal', lm.seal);
                html += renderBlock('Payload', lm.payload_json);
            } else {
                html += renderKv('Certificate layout', cl, []);
                html += renderBlock('Certificate payload', cl.payload_json);
            }
            sections.innerHTML = html;
        }

        var pre = document.getElementById('layoutSettingsJson');
        if (pre) pre.textContent = JSON.stringify(data, null, 2);

        var modalEl = document.getElementById('layoutSettingsModal');
        if (modalEl && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    function bind() {
        document.querySelectorAll('.js-show-layout-settings').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var raw = btn.getAttribute('data-layout-settings') || '{}';
                var data;
                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    data = { error: 'Unable to parse layout settings', raw: raw };
                }
                window.showLayoutSettings(data, btn.getAttribute('data-title') || '');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
@endsection