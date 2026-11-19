{{-- Batch Issue: select employees → choose layout → live preview → save & print (Issue Certificate directory) --}}
@extends('layouts.master')

@section('title', __('index.batch_issue_certificate'))

@section('action', __('index.batch_issue_certificate'))

@php
    // Defaults come from the selected Layout Model's complete CertiCraft Studio payload,
    // falling back to the template columns so Batch Issue mirrors the saved model exactly.
    $sd = (isset($selectedTemplate) && method_exists($selectedTemplate, 'toStudioPayload'))
        ? ($selectedTemplate->toStudioPayload() ?? [])
        : [];
    $sdDigitalHr = is_array($sd['digitalHr'] ?? null) ? $sd['digitalHr'] : [];
    $sdCategory = $sd['category'] ?? ($selectedTemplate?->category ?? 'course');
    $sdHours = $sdDigitalHr['trainingHours'] ?? ($selectedTemplate?->training_hours ?? 50);
    $sdKhmerTitle = $sd['khmerTitle'] ?? ($selectedTemplate?->khmer_certificate_title ?? $selectedTemplate?->khmer_title ?? 'វិញ្ញាបនបត្របញ្ចប់ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ');
    $sdTitle = $sd['certificateTitle'] ?? ($selectedTemplate?->certificate_title ?? $selectedTemplate?->title ?? 'CERTIFICATE OF PROFESSIONAL TRAINING COMPLETION');
    $sdKhmerAch = $sd['khmerCourseOrAchievementTitle'] ?? ($selectedTemplate?->khmer_course_or_achievement_title ?? 'វគ្គបណ្តុះបណ្តាលភាពជាអ្នកដឹកនាំ និងប្រតិបត្តិការឌីជីថល HR');
    $sdAch = $sd['courseOrAchievementTitle'] ?? ($selectedTemplate?->course_or_achievement_title ?? 'Advanced KNEAYERNG HRMS Operations & Leadership Development Program');
    $sdKhmerCitation = $sd['khmerCitation'] ?? ($selectedTemplate?->khmer_citation ?? 'បានបំពេញតាមគ្រប់លក្ខខណ្ឌតម្រូវនៃកម្មវិធី និងទទួលបានលទ្ធផលគាប់ប្រសើរ។');
    $sdCitation = $sd['citation'] ?? ($selectedTemplate?->citation ?? 'Having successfully completed all module curricula, practical assessments, and professional milestones.');
    $sdStatus = $sdDigitalHr['complianceStatus'] ?? ($selectedTemplate?->compliance_status ?? 'Certified & Accredited');
    $sdSubtitle = $sd['subtitle'] ?? ($selectedTemplate?->subtitle ?? '');
    $sdKhmerSubtitle = $sd['khmerSubtitle'] ?? ($selectedTemplate?->khmer_subtitle ?? '');
    $sdTrack = $sdDigitalHr['hrCompetencyTrack'] ?? ($selectedTemplate?->hr_competency_track ?? '');

    $sdVisibility = is_array($sd['visibility'] ?? null) ? $sd['visibility'] : [];
    $sdSeal = is_array($sd['seal'] ?? null) ? $sd['seal'] : [];
    $sdStyling = is_array($sd['styling'] ?? null) ? $sd['styling'] : [];
    $sdSignatories = is_array($sd['signatories'] ?? null) ? $sd['signatories'] : [];
    $sdSig1 = $sdSignatories[0] ?? [];
    $sdSig2 = $sdSignatories[1] ?? [];
    $sdSubBanner = $sd['organizationSubBanner'] ?? ($selectedTemplate?->payload_json['organizationSubBanner'] ?? 'KNEAYERNG PHONE SHOP');
    $sdShowSubBanner = $sdVisibility['showOrganizationSubBanner'] ?? true;
    $sdShowDigitalBadge = !empty($sdDigitalHr['showDigitalHoursBadge']);
    $sdHangingStyle = $sd['hangingMedalStyle'] ?? ($selectedTemplate?->layout_template === 'executive-imperial-sash' ? 'gold-sash' : 'navy-sash');
    $sdSealEnabled = !empty($sdSeal['enabled']) && ($sdSeal['style'] ?? '') !== 'none';
    $sdSealStyle = $sdSeal['style'] ?? 'crown-laurel';
    $sdSealRibbon = $sdSeal['ribbonColor'] ?? 'navy';
    $sdShowStorefrontWm = $sdVisibility['showStorefrontWatermark'] ?? ($selectedTemplate?->layout_template === 'executive-imperial-sash');
    $sdShowWatermark = $sdStyling['showWatermark'] ?? true;
    $sdShowQrCode = $sd['showQrCode'] ?? true;
@endphp

@section('styles')
<style>
    /* CertiCraft Studio-style Layout Model Gallery */
    .batch-layout-thumb {
        position: relative;
        width: 172px;
        height: 121px;
        overflow: hidden;
        border-radius: 10px;
        background: #0b1730;
        border: 1.5px solid rgba(212, 163, 44, 0.55);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.28);
        flex: 0 0 auto;
    }
    .batch-layout-thumb iframe {
        border: 0;
        width: 1080px;
        height: 760px;
        transform: scale(0.1593);
        transform-origin: top left;
        pointer-events: none;
        background: #fff;
    }
    .batch-layout-card .template-canvas-thumb { cursor: pointer; }
    .batch-layout-card .template-canvas-thumb:hover .template-thumb-overlay { opacity: 1 !important; }
    .batch-layout-card .card {
        border-radius: 14px;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        border: 1px solid #e2e8f0;
    }
    .batch-layout-card:hover .card {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -10px rgba(0, 0, 0, .18) !important;
    }
    .batch-layout-card.is-selected .card {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, .25), 0 16px 32px -12px rgba(0, 0, 0, .18) !important;
    }
    .batch-gallery-chip {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 999px;
        cursor: pointer;
        transition: all .15s ease;
        white-space: nowrap;
    }
    .batch-gallery-chip:hover { border-color: #f59e0b; color: #92400e; }
    .batch-gallery-chip.active {
        background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%);
        border-color: #092653;
        color: #fff;
        box-shadow: 0 4px 10px rgba(9, 38, 83, .25);
    }
    .batch-layout-card .logo-thumb-ornament {
        width: 112px;
        height: 54px;
        overflow: visible;
    }

    /* Real A4 render layered over each gallery card (lazy-loaded when the gallery opens) */
    .batch-gallery-preview {
        position: relative;
        height: 235px;
        overflow: hidden;
    }
    .batch-gallery-a4-frame {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        pointer-events: none;
        background: #ffffff;
        opacity: 0;
        transition: opacity .25s ease;
        z-index: 20;
    }
    .batch-gallery-a4-frame.is-loaded { opacity: 1; }
    .batch-staff-list { max-height: 560px; overflow-y: auto; }
    .batch-staff-item {
        align-items: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
        cursor: pointer; display: grid; gap: 10px; grid-template-columns: 18px 42px minmax(0, 1fr);
        margin-bottom: 7px; padding: 8px; transition: background .15s ease, border-color .15s ease;
    }
    .batch-staff-item:hover { border-color: #93c5fd; }
    .batch-staff-item.is-selected { background: #eff6ff; border-color: #3b82f6; }
    .batch-staff-item .staff-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #f1f5f9; }
    .batch-staff-item .staff-name { display: block; }
    .batch-staff-item .staff-meta { color: #64748b; display: block; font-size: 11px; line-height: 1.45; }
    .batch-staff-pager { align-items: center; display: flex; gap: 8px; justify-content: space-between; margin-top: 10px; }
    .batch-staff-pager button { background: #fff; border: 1px solid #dbe3ee; border-radius: 7px; color: #334155; font-size: 12px; font-weight: 700; min-height: 30px; padding: 4px 9px; }
    .batch-staff-pager button:disabled { cursor: not-allowed; opacity: .45; }
    .batch-staff-pager span { color: #64748b; font-size: 11px; text-align: center; }
    .batch-staff-filter-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: grid; gap: 8px; padding: 10px; }
</style>
@endsection

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">{{ __('index.certificate_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.batch_issue_certificate') }}</li>
        </ol>
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">
            <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back_to_directory') }}
        </a>
    </nav>

    <!-- CertiCraft Studio 🎨 Style Layout Model Selector -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-radius: 12px; overflow: hidden;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <!-- Live mini preview of the currently selected Layout Model -->
                    <div class="batch-layout-thumb">
                        <iframe id="batch_layout_thumb" title="Selected Layout Model Preview"
                                src="{{ isset($selectedTemplate) ? route('admin.certificates.templates.preview', ['id' => $selectedTemplate->id, 'embed' => 1]) : 'about:blank' }}"></iframe>
                    </div>
                    <div>
                        <span class="badge bg-warning text-dark px-2.5 py-0.5 fw-bold mb-1">CertiCraft Studio 🎨 · Layout Model</span>
                        <h6 class="mb-1 text-white fw-bold" id="batch_layout_name">
                            {{ isset($selectedTemplate) ? ($selectedTemplate->khmer_title ?: $selectedTemplate->title) : 'Choose a Layout Model' }}
                        </h6>
                        <div class="small text-white-50" id="batch_layout_meta">
                            @if(isset($selectedTemplate))
                                <span class="font-monospace">{{ $selectedTemplate->template_code }}</span>
                                · {{ $selectedTemplate->layout_template }}
                                · {{ ucfirst($selectedTemplate->category) }}
                                · {{ $selectedTemplate->training_hours }}h
                                @if($selectedTemplate->is_default) · ★ Default @endif
                            @else
                                Pick a layout model to preview and issue certificates.
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-warning btn-sm text-dark fw-bold text-nowrap" onclick="openBatchLayoutGallery()">
                        <i class="link-icon" data-feather="grid"></i> Choose Layout Model 🎨
                    </button>
                    <a href="{{ route('admin.certificates.studio', ['template_id' => $selectedTemplate->id ?? 1]) }}"
                       id="batch_edit_in_studio_btn"
                       class="btn btn-outline-light btn-sm fw-bold text-nowrap"
                       target="_blank"
                       title="បើកគំរូនេះក្នុង CertiCraft Studio">
                        <i class="link-icon" data-feather="layout"></i> Edit in Studio
                    </a>
                </div>
            </div>
        </div>

        <!-- Hidden native select preserved for the existing template-apply logic -->
        <div class="d-none">
            <select id="batch_template_picker" class="form-select form-select-sm fw-bold" onchange="onBatchTemplateChange(this)">
                @foreach($templates ?? [] as $tmpl)
                    @php
                        $displayKhmer = $tmpl->khmer_title ?: $tmpl->khmer_certificate_title;
                        $displayEng = $tmpl->title ?: $tmpl->certificate_title;
                        $label = '[' . $tmpl->version_tag . '] ' . ($displayKhmer ? $displayKhmer . ($displayEng && $displayEng !== $displayKhmer ? ' · ' . $displayEng : '') : $displayEng) . ' (' . $tmpl->template_code . ')';
                        $tp = method_exists($tmpl, 'toStudioPayload') ? ($tmpl->toStudioPayload() ?? []) : [];
                        $tpDigitalHr = is_array($tp['digitalHr'] ?? null) ? $tp['digitalHr'] : [];
                        $tpVisibility = is_array($tp['visibility'] ?? null) ? $tp['visibility'] : [];
                        $tpSeal = is_array($tp['seal'] ?? null) ? $tp['seal'] : [];
                        $tpStyling = is_array($tp['styling'] ?? null) ? $tp['styling'] : [];
                        $tpSignatories = is_array($tp['signatories'] ?? null) ? $tp['signatories'] : [];
                        $tpSig1 = $tpSignatories[0] ?? [];
                        $tpSig2 = $tpSignatories[1] ?? [];
                        $tpHanging = $tp['hangingMedalStyle'] ?? ($tmpl->layout_template === 'executive-imperial-sash' ? 'gold-sash' : 'navy-sash');
                    @endphp
                    <option value="{{ $tmpl->id }}" {{ (isset($selectedTemplate) && $selectedTemplate->id == $tmpl->id) ? 'selected' : '' }}
                        data-id="{{ $tmpl->id }}"
                        data-code="{{ $tmpl->template_code }}"
                        data-title="{{ $tp['certificateTitle'] ?? ($tmpl->certificate_title ?: $tmpl->title) }}"
                        data-khmer-title="{{ $tp['khmerTitle'] ?? ($tmpl->khmer_certificate_title ?: ($tmpl->khmer_title ?: $tmpl->title)) }}"
                        data-cat="{{ $tp['category'] ?? $tmpl->category }}"
                        data-layout="{{ $tp['layoutTemplate'] ?? $tmpl->layout_template }}"
                        data-hours="{{ $tpDigitalHr['trainingHours'] ?? $tmpl->training_hours }}"
                        data-status="{{ $tpDigitalHr['complianceStatus'] ?? $tmpl->compliance_status }}"
                        data-track="{{ $tpDigitalHr['hrCompetencyTrack'] ?? $tmpl->hr_competency_track }}"
                        data-sub="{{ $tp['subtitle'] ?? $tmpl->subtitle }}"
                        data-khmer-sub="{{ $tp['khmerSubtitle'] ?? $tmpl->khmer_subtitle }}"
                        data-ach="{{ $tp['courseOrAchievementTitle'] ?? $tmpl->course_or_achievement_title }}"
                        data-khmer-ach="{{ $tp['khmerCourseOrAchievementTitle'] ?? $tmpl->khmer_course_or_achievement_title }}"
                        data-cit="{{ $tp['citation'] ?? $tmpl->citation }}"
                        data-khmer-cit="{{ $tp['khmerCitation'] ?? $tmpl->khmer_citation }}"
                        data-sub-banner="{{ $tp['organizationSubBanner'] ?? ($tmpl->payload_json['organizationSubBanner'] ?? 'KNEAYERNG PHONE SHOP') }}"
                        data-show-sub-banner="{{ ($tpVisibility['showOrganizationSubBanner'] ?? true) ? '1' : '0' }}"
                        data-hanging-medal-style="{{ $tpHanging }}"
                        data-seal-enabled="{{ (!empty($tpSeal['enabled']) && ($tpSeal['style'] ?? '') !== 'none') ? '1' : '0' }}"
                        data-seal-style="{{ $tpSeal['style'] ?? 'crown-laurel' }}"
                        data-seal-ribbon-color="{{ $tpSeal['ribbonColor'] ?? 'navy' }}"
                        data-show-digital-hours-badge="{{ (!empty($tpDigitalHr['showDigitalHoursBadge'])) ? '1' : '0' }}"
                        data-show-storefront-watermark="{{ ($tpVisibility['showStorefrontWatermark'] ?? ($tmpl->layout_template === 'executive-imperial-sash')) ? '1' : '0' }}"
                        data-show-watermark="{{ ($tpStyling['showWatermark'] ?? true) ? '1' : '0' }}"
                        data-show-qr-code="{{ ($tp['showQrCode'] ?? true) ? '1' : '0' }}"
                        data-sig1-name="{{ $tpSig1['name'] ?? 'CHHOEUN REACH' }}"
                        data-sig1-khmer-name="{{ $tpSig1['khmerName'] ?? 'ឈឿន រាជ' }}"
                        data-sig1-role="{{ $tpSig1['role'] ?? 'IT Manager / Branch Manager' }}"
                        data-sig1-khmer-role="{{ $tpSig1['khmerRole'] ?? 'ប្រធានផ្នែកព័ត៌មានវិទ្យា / ប្រធានសាខា' }}"
                        data-sig1-organization="{{ $tpSig1['organization'] ?? 'Admin • HR • System • Stock' }}"
                        data-sig1-style="{{ $tpSig1['signatureStyle'] ?? 'alex-brush' }}"
                        data-sig2-name="{{ $tpSig2['name'] ?? 'PET POEUN' }}"
                        data-sig2-khmer-name="{{ $tpSig2['khmerName'] ?? 'ម៉េង សោភា' }}"
                        data-sig2-role="{{ $tpSig2['role'] ?? 'HR Manager / HR Supervisor' }}"
                        data-sig2-khmer-role="{{ $tpSig2['khmerRole'] ?? 'ប្រធានផ្នែកធនធានមនុស្ស' }}"
                        data-sig2-organization="{{ $tpSig2['organization'] ?? 'Human Resource Department' }}"
                        data-sig2-style="{{ $tpSig2['signatureStyle'] ?? 'great-vibes' }}">
                        {{ $label }} {{ $tmpl->is_default ? '★' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <form action="{{ route('admin.certificates.batchStore') }}" method="POST" id="batchIssueForm">
        @csrf
        <input type="hidden" name="template_id" id="batch_template_id" value="{{ $selectedTemplate?->id ?? '' }}">
        <input type="hidden" name="layout_template" id="batch_layout_template" value="{{ $sd['layoutTemplate'] ?? ($selectedTemplate?->layout_template ?? 'executive-verify') }}">
        <input type="hidden" name="subtitle" id="subtitle" value="{{ $sdSubtitle }}">
        <input type="hidden" name="khmer_subtitle" id="khmer_subtitle" value="{{ $sdKhmerSubtitle }}">
        <input type="hidden" name="hr_competency_track" id="hr_competency_track" value="{{ $sdTrack }}">

        <div class="row g-4">
            <!-- Left: Select Employees -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100 mb-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="users" class="me-1"></i> ១. Select Employees
                        </h6>
                        <span class="badge bg-primary-subtle text-primary fw-bold"><span id="selectedCount">0</span> selected</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <input type="text" id="staff_search" class="form-control form-control-sm"
                                   placeholder="🔍 Search name, ID, branch or department..."
                                   oninput="staffPickerPage = 1; applyStaffFilters()">
                        </div>
                        <div class="batch-staff-filter-panel mb-3">
                            <select id="branch_filter" class="form-select form-select-sm" onchange="staffPickerPage = 1; applyStaffFilters()">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <select id="dept_filter" class="form-select form-select-sm" onchange="staffPickerPage = 1; applyStaffFilters()">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearStaffFilters()">Clear filters</button>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAllStaff()">Select all</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleSelectVisible()">Select visible</button>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">Clear selection</button>
                        </div>

                        <div class="staff-list batch-staff-list">
                            @forelse($users as $user)
                                @php
                                    $searchText = trim(($user->name ?? '') . ' ' . ($user->english_name ?? '') . ' ' . ($user->employee_code ?? '') . ' ' . ($user->branch?->name ?? '') . ' ' . ($user->department?->dept_name ?? ''));
                                @endphp
                                <label class="batch-staff-item staff-item"
                                     data-dept="{{ $user->department_id }}"
                                     data-branch="{{ $user->branch_id }}"
                                     data-search="{{ \Illuminate\Support\Str::lower($searchText) }}">
                                    <input class="form-check-input staff-checkbox" type="checkbox" name="employee_ids[]"
                                           value="{{ $user->id }}" id="emp_{{ $user->id }}" onchange="onStaffSelectionChange()">
                                    <img class="staff-avatar" src="{{ $employeePhotos[$user->id] ?? $user->avatar_url }}" alt="" loading="lazy" onerror="this.src='{{ asset('assets/images/img.png') }}'">
                                    <span class="min-w-0">
                                        <strong class="staff-name fw-bold text-dark">{{ $user->name }}</strong>
                                        <small class="staff-meta">{{ $user->english_name ?: 'No Latin Name' }}</small>
                                        <small class="staff-meta"><span class="font-monospace">{{ $user->employee_code ?: '—' }}</span> · {{ $user->department?->dept_name ?: 'No department' }} · {{ $user->branch?->name ?: 'No branch' }}</small>
                                    </span>
                                </label>
                            @empty
                                <div class="text-center text-muted py-4 small">No employees found.</div>
                            @endforelse
                        </div>
                        <div id="staffPickerPager" class="batch-staff-pager"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Live Preview + Shared Content -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="card-title mb-0 fw-bold text-primary">
                                <i data-feather="eye" class="me-1"></i> ២. Preview (A4 Landscape)
                            </h6>
                            <small class="text-muted" id="preview_employee_label">Select an employee to preview.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshBatchPreview(true)">
                                <i data-feather="refresh-cw" style="width:14px;height:14px;"></i> Refresh Preview
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold" id="batchSubmitBtn" onclick="return confirmBatchSubmit(event)">
                                <i data-feather="check-circle" style="width:14px;height:14px;"></i>
                                <span id="batchSubmitLabel">Save &amp; Issue Certificates</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-2 bg-light position-relative" style="border-radius: 0 0 12px 12px;">
                        <div id="preview_placeholder" class="d-flex flex-column align-items-center justify-content-center text-muted" style="height: 560px;">
                            <i data-feather="award" style="width: 48px; height: 48px;"></i>
                            <p class="mb-0 mt-2 fw-semibold">ជ្រើសរើសបុគ្គលិកយ៉ាងតិច ១ នាក់ ដើម្បីមើល Preview</p>
                            <small>Pick at least one employee to see the live certificate preview.</small>
                        </div>
                        <iframe id="batch_preview_frame" title="Certificate Preview"
                                class="w-100 border-0 rounded d-none"
                                style="height: 560px; background:#fff;"></iframe>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="edit-3" class="me-1"></i> ៣. Shared Certificate Content
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleBatchSettings()" id="batch_settings_toggle">
                            Show / Hide
                        </button>
                    </div>
                    <div class="card-body d-none" id="batch_settings_body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select" required onchange="refreshBatchPreview()">
                                    <option value="course" {{ $sdCategory === 'course' ? 'selected' : '' }}>Professional Training / CPD (វគ្គបណ្តុះបណ្តាល)</option>
                                    <option value="leader" {{ $sdCategory === 'leader' ? 'selected' : '' }}>Leadership Excellence (មេដឹកនាំឆ្នើម)</option>
                                    <option value="staff" {{ $sdCategory === 'staff' ? 'selected' : '' }}>Staff Excellence (បុគ្គលិកឆ្នើម)</option>
                                    <option value="service" {{ $sdCategory === 'service' ? 'selected' : '' }}>Customer Service Excellence (សេវាកម្មឆ្នើម)</option>
                                    <option value="custom" {{ $sdCategory === 'custom' ? 'selected' : '' }}>Honor & Merit Recognition (កិត្តិយសពិសេស)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Training Hours (ម៉ោង)</label>
                                <input type="number" step="0.5" name="training_hours" id="training_hours" class="form-control" value="{{ $sdHours }}" oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Title</label>
                                <input type="text" name="khmer_title" id="khmer_title" class="form-control" value="{{ $sdKhmerTitle }}" oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Title <span class="text-danger">*</span></label>
                                <input type="text" name="certificate_title" id="certificate_title" class="form-control" value="{{ $sdTitle }}" required oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Khmer Achievement/Course Title</label>
                                <input type="text" name="khmer_course_or_achievement_title" id="khmer_course_or_achievement_title" class="form-control" value="{{ $sdKhmerAch }}" oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">English Achievement/Course Title <span class="text-danger">*</span></label>
                                <input type="text" name="course_or_achievement_title" id="course_or_achievement_title" class="form-control" value="{{ $sdAch }}" required oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Khmer Citation</label>
                                <textarea name="khmer_citation" id="khmer_citation" rows="2" class="form-control" oninput="refreshBatchPreview()">{{ $sdKhmerCitation }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">English Citation</label>
                                <textarea name="citation" id="citation" rows="2" class="form-control" oninput="refreshBatchPreview()">{{ $sdCitation }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Accreditation Status</label>
                                <input type="text" name="compliance_status" id="compliance_status" class="form-control" value="{{ $sdStatus }}" oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Issue Date (English) <span class="text-danger">*</span></label>
                                <input type="text" name="issue_date" id="issue_date" class="form-control" value="{{ date('F d, Y') }}" required oninput="refreshBatchPreview()">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Issue Date (Khmer)</label>
                                <input type="text" name="khmer_issue_date" id="khmer_issue_date" class="form-control" value="ថ្ងៃទី {{ date('d') }} ខែ {{ date('m') }} ឆ្នាំ {{ date('Y') }}" oninput="refreshBatchPreview()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- CertiCraft Studio 🎨 Layout Model Gallery (full-screen visual picker) -->
    <div class="modal fade" id="batchLayoutGalleryModal" tabindex="-1" aria-labelledby="batchLayoutGalleryLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0" style="background: #f1f5f9;">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-bottom: 1px solid rgba(212, 163, 44, .35);">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="batchLayoutGalleryLabel">
                            🎨 CertiCraft Studio — Choose Layout Model
                        </h5>
                        <small class="text-warning">ជ្រើសរើសគំរូវិញ្ញាបនបត្រ (Layout Model) សម្រាប់ Batch Cohort — ចុច “Use this Layout” ដើម្បីអនុវត្ត</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.certificates.templates') }}" target="_blank"
                           class="btn btn-outline-light btn-sm fw-bold text-nowrap">
                            <i class="link-icon" data-feather="layout"></i> Layout Models
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2" id="batchGalleryCategoryChips">
                            <button type="button" class="batch-gallery-chip active" data-category="all" onclick="setBatchGalleryCategory('all', this)">All Models</button>
                            <button type="button" class="batch-gallery-chip" data-category="staff" onclick="setBatchGalleryCategory('staff', this)">★ Staff</button>
                            <button type="button" class="batch-gallery-chip" data-category="leader" onclick="setBatchGalleryCategory('leader', this)">Leadership</button>
                            <button type="button" class="batch-gallery-chip" data-category="service" onclick="setBatchGalleryCategory('service', this)">Customer Service</button>
                            <button type="button" class="batch-gallery-chip" data-category="course" onclick="setBatchGalleryCategory('course', this)">Training / CPD</button>
                            <button type="button" class="batch-gallery-chip" data-category="custom" onclick="setBatchGalleryCategory('custom', this)">Honor & Merit</button>
                        </div>
                        <div class="position-relative" style="min-width: 280px; max-width: 380px; flex: 1 1 280px;">
                            <input type="text" id="batchGallerySearch" class="form-control form-control-sm ps-4"
                                   placeholder="🔍 Search layout name, code or layout type..."
                                   oninput="filterBatchLayoutGallery()">
                            <span class="position-absolute" style="left: 12px; top: 6px; font-size: 12px; opacity: .55;">🔍</span>
                        </div>
                    </div>

                    <div class="row g-3" id="batchLayoutGalleryGrid">
                        @forelse($templates ?? [] as $tmpl)
                            @php
                                $gallerySearch = \Illuminate\Support\Str::lower(trim(
                                    ($tmpl->khmer_title ?? '') . ' ' . ($tmpl->title ?? '') . ' ' .
                                    ($tmpl->khmer_certificate_title ?? '') . ' ' . ($tmpl->certificate_title ?? '') . ' ' .
                                    ($tmpl->template_code ?? '') . ' ' . ($tmpl->layout_template ?? '') . ' ' . ($tmpl->category ?? '')
                                ));
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4 batch-layout-card {{ (isset($selectedTemplate) && $selectedTemplate->id == $tmpl->id) ? 'is-selected' : '' }}"
                                 data-template-id="{{ $tmpl->id }}"
                                 data-category="{{ $tmpl->category }}"
                                 data-search="{{ $gallerySearch }}">
                                <div class="card h-100 shadow-sm">
                                    <div class="position-relative batch-gallery-preview">
                                        @include('admin.certificateManagement.partials.template-card-preview', ['tmpl' => $tmpl])
                                        <iframe class="batch-gallery-a4-frame"
                                                data-src="{{ route('admin.certificates.templates.preview', ['id' => $tmpl->id, 'embed' => 1, 'plain' => 1]) }}"
                                                title="Full A4 preview of {{ $tmpl->template_code }}"
                                                loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
                                        <span class="badge bg-success position-absolute shadow-sm batch-layout-selected-badge {{ (isset($selectedTemplate) && $selectedTemplate->id == $tmpl->id) ? '' : 'd-none' }}"
                                              style="top: 10px; left: 12px; z-index: 25; font-size: 10px;">
                                            ✓ Selected
                                        </span>
                                    </div>
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark text-truncate">{{ $tmpl->khmer_title ?: $tmpl->title }}</div>
                                                <div class="small text-muted font-monospace text-truncate" style="font-size: 11px;">
                                                    {{ $tmpl->template_code }} · {{ $tmpl->layout_template }}
                                                </div>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase"
                                                  style="font-size: 9.5px;">{{ $tmpl->category }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mt-1.5 flex-wrap">
                                            <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $tmpl->version_tag }}</span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">🕒 {{ $tmpl->training_hours }}h</span>
                                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">{{ $tmpl->compliance_status }}</span>
                                            @if($tmpl->is_default)
                                                <span class="badge bg-warning text-dark" style="font-size: 10px;">★ Default</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0 px-3 pb-3 d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm flex-grow-1 fw-bold"
                                                onclick="selectBatchLayout({{ $tmpl->id }})">
                                            <i class="link-icon" data-feather="check-circle" style="width:13px;height:13px;"></i>
                                            Use this Layout
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                title="Preview this Layout Model in full A4"
                                                onclick="openFullPreviewModal({{ $tmpl->id }})">
                                            <i class="link-icon" data-feather="eye" style="width:13px;height:13px;"></i> A4
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    No Layout Models found. Create one in
                                    <a href="{{ route('admin.certificates.studio') }}" class="fw-bold">CertiCraft Studio 🎨</a>.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div id="batchGalleryEmpty" class="text-center text-muted py-5 d-none">
                        <div style="font-size: 26px;">🔍</div>
                        <div class="fw-semibold mt-1">No layout models match your search.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full A4 preview of a Layout Model (content-only, no sidebar) -->
    <div class="modal fade" id="batchA4PreviewModal" tabindex="-1" aria-labelledby="batchA4PreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1220px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: #071026;">
                <div class="modal-header py-2.5 px-4" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-bottom: 1px solid rgba(212, 163, 44, .3);">
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="batchA4PreviewLabel">Layout Model A4 Preview</h6>
                        <small class="text-warning font-monospace" style="font-size: 11px;">A4 Landscape · real saved Studio settings</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="batchA4OpenTab" class="btn btn-outline-light btn-sm" target="_blank">
                            <i class="link-icon" data-feather="external-link" style="width:13px;height:13px;"></i> Open Tab
                        </a>
                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold" id="batchA4UseBtn" onclick="useBatchA4PreviewLayout()">
                            <i class="link-icon" data-feather="check-circle" style="width:13px;height:13px;"></i> Use this Layout
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="background: radial-gradient(circle at 50% 50%, #121e3d 0%, #060e22 100%); height: 76vh; max-height: 800px;">
                    <iframe id="batchA4PreviewFrame" class="w-100 h-100 border-0" src="about:blank" title="Layout Model A4 Preview"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var batchPreviewTimer = null;
    var batchPreviewUrl = @json(route('admin.certificates.batchPreview'));
    var batchA4PreviewBaseUrl = @json(url('admin/certificates/templates'));
    var batchGalleryCategory = 'all';
    var staffPickerPage = 1;
    var staffPickerPageSize = 20;

    /* ---------------- CertiCraft Studio-style Layout Model gallery ---------------- */

    function getBatchSelectedOption() {
        var select = document.getElementById('batch_template_picker');
        if (!select || select.selectedIndex < 0) return null;
        return select.options[select.selectedIndex] || null;
    }

    function updateBatchLayoutSelectorCard() {
        var opt = getBatchSelectedOption();
        var nameEl = document.getElementById('batch_layout_name');
        var metaEl = document.getElementById('batch_layout_meta');
        var thumb = document.getElementById('batch_layout_thumb');
        var editBtn = document.getElementById('batch_edit_in_studio_btn');
        var select = document.getElementById('batch_template_picker');
        if (!opt) return;

        var id = opt.value;
        var khmer = opt.getAttribute('data-khmer-title') || opt.getAttribute('data-title') || '';
        if (nameEl) nameEl.innerText = khmer;

        if (metaEl) {
            var parts = [];
            if (opt.getAttribute('data-code')) parts.push('<span class="font-monospace">' + opt.getAttribute('data-code') + '</span>');
            if (opt.getAttribute('data-layout')) parts.push(opt.getAttribute('data-layout'));
            if (opt.getAttribute('data-cat')) parts.push(opt.getAttribute('data-cat').charAt(0).toUpperCase() + opt.getAttribute('data-cat').slice(1));
            if (opt.getAttribute('data-hours')) parts.push(opt.getAttribute('data-hours') + 'h');
            metaEl.innerHTML = parts.join(' · ');
        }

        if (thumb) {
            thumb.src = batchA4PreviewBaseUrl + '/' + id + '/preview?embed=1&_ts=' + Date.now();
        }
        if (editBtn) {
            editBtn.href = @json(route('admin.certificates.studio')) + '?template_id=' + id;
        }

        // Highlight the selected card inside the gallery
        document.querySelectorAll('#batchLayoutGalleryGrid .batch-layout-card').forEach(function (card) {
            var isSel = String(card.getAttribute('data-template-id')) === String(id);
            card.classList.toggle('is-selected', isSel);
            var badge = card.querySelector('.batch-layout-selected-badge');
            if (badge) badge.classList.toggle('d-none', !isSel);
        });
    }

    function openBatchLayoutGallery() {
        filterBatchLayoutGallery();
        var modalEl = document.getElementById('batchLayoutGalleryModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            if (typeof feather !== 'undefined') { try { feather.replace(); } catch (e) {} }
        }
    }

    function setBatchGalleryCategory(category, btn) {
        batchGalleryCategory = category || 'all';
        document.querySelectorAll('#batchGalleryCategoryChips .batch-gallery-chip').forEach(function (chip) {
            chip.classList.toggle('active', chip === btn);
        });
        filterBatchLayoutGallery();
    }

    function filterBatchLayoutGallery() {
        var term = (document.getElementById('batchGallerySearch')?.value || '').trim().toLowerCase();
        var visible = 0;
        document.querySelectorAll('#batchLayoutGalleryGrid .batch-layout-card').forEach(function (card) {
            var matchesCat = batchGalleryCategory === 'all' || card.getAttribute('data-category') === batchGalleryCategory;
            var matchesTerm = !term || (card.getAttribute('data-search') || '').indexOf(term) !== -1;
            var show = matchesCat && matchesTerm;
            card.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        var empty = document.getElementById('batchGalleryEmpty');
        if (empty) empty.classList.toggle('d-none', visible !== 0);

        // Lazily render the real full A4 for the cards that are currently visible.
        loadBatchGalleryFrames();
    }

    /* Load real A4 Layout Model renders over the gallery cards, only when visible. */
    function loadBatchGalleryFrames() {
        document.querySelectorAll('#batchLayoutGalleryGrid .batch-gallery-a4-frame').forEach(function (frame) {
            var card = frame.closest('.batch-layout-card');
            if (card && card.classList.contains('d-none')) return;
            if (frame.getAttribute('src') || !frame.getAttribute('data-src')) return;
            frame.addEventListener('load', function () { frame.classList.add('is-loaded'); }, { once: true });
            frame.setAttribute('src', frame.getAttribute('data-src'));
        });
    }

    function selectBatchLayout(id) {
        var select = document.getElementById('batch_template_picker');
        if (!select) return;

        // Match by value, with a numeric fallback
        select.value = id;
        if (String(select.value) !== String(id)) {
            for (var i = 0; i < select.options.length; i++) {
                if (String(select.options[i].value) === String(id)) { select.selectedIndex = i; break; }
            }
        }

        onBatchTemplateChange(select);
        updateBatchLayoutSelectorCard();

        var galleryEl = document.getElementById('batchLayoutGalleryModal');
        if (galleryEl && window.bootstrap) {
            var inst = bootstrap.Modal.getInstance(galleryEl);
            if (inst) inst.hide();
        }
        if (typeof refreshBatchPreview === 'function') refreshBatchPreview(true);
    }

    function openFullPreviewModal(id) {
        var opt = null;
        var select = document.getElementById('batch_template_picker');
        if (select) {
            for (var i = 0; i < select.options.length; i++) {
                if (String(select.options[i].value) === String(id)) { opt = select.options[i]; break; }
            }
        }

        var url = batchA4PreviewBaseUrl + '/' + id + '/preview?embed=1';
        var frame = document.getElementById('batchA4PreviewFrame');
        var openTab = document.getElementById('batchA4OpenTab');
        var useBtn = document.getElementById('batchA4UseBtn');
        var titleEl = document.getElementById('batchA4PreviewLabel');

        if (frame) frame.src = url + '&_ts=' + Date.now();
        if (openTab) openTab.href = url;
        if (useBtn) useBtn.setAttribute('data-template-id', id);
        if (titleEl) {
            var label = opt ? (opt.getAttribute('data-khmer-title') || opt.getAttribute('data-title')) : '';
            titleEl.innerText = label ? (label + ' — A4 Preview') : 'Layout Model A4 Preview';
        }

        // Close the gallery first (avoid stacked modals), then open the A4 preview
        var galleryEl = document.getElementById('batchLayoutGalleryModal');
        var previewEl = document.getElementById('batchA4PreviewModal');
        if (!previewEl || !window.bootstrap) return;

        var showPreview = function () {
            bootstrap.Modal.getOrCreateInstance(previewEl).show();
        };

        if (galleryEl) {
            var galleryInst = bootstrap.Modal.getInstance(galleryEl);
            if (galleryInst) {
                galleryEl.addEventListener('hidden.bs.modal', showPreview, { once: true });
                galleryInst.hide();
                return;
            }
        }
        showPreview();
    }

    function useBatchA4PreviewLayout() {
        var useBtn = document.getElementById('batchA4UseBtn');
        var id = useBtn ? useBtn.getAttribute('data-template-id') : null;
        var previewEl = document.getElementById('batchA4PreviewModal');
        if (previewEl && window.bootstrap) {
            var inst = bootstrap.Modal.getInstance(previewEl);
            if (inst) inst.hide();
        }
        if (id) selectBatchLayout(id);
    }

    function updateSelectedCount() {
        var checked = document.querySelectorAll('.staff-checkbox:checked').length;
        var counter = document.getElementById('selectedCount');
        if (counter) counter.innerText = checked;
        var btnLabel = document.getElementById('batchSubmitLabel');
        if (btnLabel) {
            btnLabel.innerText = checked > 0
                ? 'Save & Issue ' + checked + ' Certificate' + (checked > 1 ? 's' : '')
                : 'Save & Issue Certificates';
        }
        return checked;
    }

    function getFirstSelectedEmployee() {
        var cb = document.querySelector('.staff-checkbox:checked');
        return cb ? cb.value : '';
    }

    function applyStaffFilters() {
        var search = (document.getElementById('staff_search')?.value || '').trim().toLowerCase();
        var deptId = document.getElementById('dept_filter')?.value || '';
        var branchId = document.getElementById('branch_filter')?.value || '';
        var items = Array.from(document.querySelectorAll('.staff-item'));
        var matching = items.filter(function (item) {
            var matchesDept = !deptId || item.getAttribute('data-dept') === deptId;
            var matchesBranch = !branchId || item.getAttribute('data-branch') === branchId;
            var matchesSearch = !search || (item.getAttribute('data-search') || '').indexOf(search) !== -1;
            return matchesDept && matchesBranch && matchesSearch;
        });
        var totalPages = Math.max(1, Math.ceil(matching.length / staffPickerPageSize));
        staffPickerPage = Math.min(Math.max(staffPickerPage, 1), totalPages);
        var start = (staffPickerPage - 1) * staffPickerPageSize;
        var visible = new Set(matching.slice(start, start + staffPickerPageSize));
        items.forEach(function (item) { item.style.display = visible.has(item) ? 'grid' : 'none'; });

        var pager = document.getElementById('staffPickerPager');
        if (pager) {
            var end = Math.min(start + staffPickerPageSize, matching.length);
            pager.innerHTML = '<button type="button" onclick="changeStaffPickerPage(-1)" ' + (staffPickerPage <= 1 ? 'disabled' : '') + '>Previous</button>' +
                '<span>' + (matching.length ? (start + 1) + '–' + end + ' of ' + matching.length : '0 employees') + ' · Page ' + staffPickerPage + '/' + totalPages + '</span>' +
                '<button type="button" onclick="changeStaffPickerPage(1)" ' + (staffPickerPage >= totalPages ? 'disabled' : '') + '>Next</button>';
        }
    }

    function changeStaffPickerPage(direction) {
        staffPickerPage += direction;
        applyStaffFilters();
    }

    function clearStaffFilters() {
        ['staff_search', 'branch_filter', 'dept_filter'].forEach(function (id) {
            var field = document.getElementById(id);
            if (field) field.value = '';
        });
        staffPickerPage = 1;
        applyStaffFilters();
    }

    function syncStaffOptionStyles() {
        document.querySelectorAll('.staff-item').forEach(function (item) {
            item.classList.toggle('is-selected', Boolean(item.querySelector('.staff-checkbox')?.checked));
        });
    }

    function selectAllStaff() {
        document.querySelectorAll('.staff-checkbox').forEach(function (cb) { cb.checked = true; });
        onStaffSelectionChange();
    }

    function toggleSelectVisible() {
        var checkboxes = document.querySelectorAll('.staff-item:not([style*="display: none"]) .staff-checkbox');
        var allChecked = true;
        checkboxes.forEach(function (cb) { if (!cb.checked) allChecked = false; });
        checkboxes.forEach(function (cb) { cb.checked = !allChecked; });
        onStaffSelectionChange();
    }

    function clearSelection() {
        document.querySelectorAll('.staff-checkbox').forEach(function (cb) { cb.checked = false; });
        onStaffSelectionChange();
    }

    function onStaffSelectionChange() {
        var count = updateSelectedCount();
        syncStaffOptionStyles();
        var label = document.getElementById('preview_employee_label');
        var selected = document.querySelector('.staff-checkbox:checked');
        if (label) {
            if (selected) {
                var row = selected.closest('.staff-item');
                var name = row ? row.querySelector('.fw-bold')?.innerText : '';
                label.innerText = 'Previewing: ' + (name || 'Selected employee') + (count > 1 ? ' (first of ' + count + ')' : '');
            } else {
                label.innerText = 'Previewing: Template Sample (KHUN SREYNI)';
            }
        }
        refreshBatchPreview(true);
    }

    function buildPreviewUrl() {
        var employeeId = getFirstSelectedEmployee();

        var url = new URL(batchPreviewUrl, window.location.origin);
        if (employeeId) {
            url.searchParams.set('employee_id', employeeId);
        }

        var tmplId = document.getElementById('batch_template_id')?.value;
        if (tmplId) url.searchParams.set('template_id', tmplId);

        var layoutTmpl = document.getElementById('batch_layout_template')?.value;
        if (layoutTmpl) url.searchParams.set('layout_template', layoutTmpl);

        var fields = [
            'category', 'certificate_title', 'khmer_title',
            'course_or_achievement_title', 'khmer_course_or_achievement_title',
            'citation', 'khmer_citation', 'training_hours', 'compliance_status',
            'hr_competency_track', 'issue_date', 'khmer_issue_date',
            'subtitle', 'khmer_subtitle'
        ];

        fields.forEach(function (field) {
            var el = document.getElementById(field);
            if (el && el.value !== undefined && el.value !== null && String(el.value).length > 0) {
                url.searchParams.set(field, el.value);
            }
        });

        // Bust the iframe cache so the preview always reflects the latest input.
        url.searchParams.set('_ts', Date.now());
        return url.toString();
    }

    function refreshBatchPreview(immediate) {
        if (batchPreviewTimer) {
            clearTimeout(batchPreviewTimer);
            batchPreviewTimer = null;
        }

        var run = function () {
            var frame = document.getElementById('batch_preview_frame');
            var placeholder = document.getElementById('preview_placeholder');
            if (!frame || !placeholder) return;

            var url = buildPreviewUrl();
            if (!url) {
                frame.classList.add('d-none');
                placeholder.classList.remove('d-none');
                return;
            }

            frame.src = url;
            placeholder.classList.add('d-none');
            frame.classList.remove('d-none');
        };

        if (immediate) {
            run();
        } else {
            batchPreviewTimer = setTimeout(run, 400);
        }
    }

    function toggleBatchSettings() {
        var body = document.getElementById('batch_settings_body');
        if (body) body.classList.toggle('d-none');
    }

    function prepareTemplateForPreviewAndForm(select) {
        var opt = select.options[select.selectedIndex];
        if (!opt) return;

        var map = {
            id: 'batch_template_id',
            layout: 'batch_layout_template',
            cat: 'category',
            title: 'certificate_title',
            'khmer-title': 'khmer_title',
            sub: 'subtitle',
            'khmer-sub': 'khmer_subtitle',
            ach: 'course_or_achievement_title',
            'khmer-ach': 'khmer_course_or_achievement_title',
            cit: 'citation',
            'khmer-cit': 'khmer_citation',
            hours: 'training_hours',
            status: 'compliance_status',
            track: 'hr_competency_track'
        };

        Object.keys(map).forEach(function (dataKey) {
            var value = opt.getAttribute('data-' + dataKey);
            var el = document.getElementById(map[dataKey]);
            if (!el) return;
            // Always apply template's values on template change so the selected layout model is used correctly
            if (value === null || value === undefined) value = '';
            if (el.tagName === 'SELECT') {
                if (value === '') return;
                el.value = value;
                if (el.value !== String(value)) {
                    // fallback: try to match by option existence
                    var found = false;
                    for (var i = 0; i < el.options.length; i++) {
                        if (el.options[i].value == value) { el.selectedIndex = i; found = true; break; }
                    }
                }
            } else {
                el.value = value;
            }
        });

        var editBtn = document.getElementById('batch_edit_in_studio_btn');
        var id = opt.getAttribute('data-id');
        if (editBtn && id) {
            editBtn.href = @json(route('admin.certificates.studio')) + '?template_id=' + id;
        }
    }

    function onBatchTemplateChange(select) {
        prepareTemplateForPreviewAndForm(select);
        updateBatchLayoutSelectorCard();
        refreshBatchPreview(true);
    }

    var confirmBatchSubmit = function (event) {
        var count = updateSelectedCount();
        if (count < 1) {
            event.preventDefault();
            alert('សូមជ្រើសរើសបុគ្គលិកយ៉ាងតិច ១ នាក់ មុននឹងចេញវិញ្ញាបនបត្រ។\nPlease select at least one employee before issuing certificates.');
            return false;
        }

        var label = document.getElementById('batchSubmitLabel');
        if (label) label.innerText = 'Saving & issuing...';

        return confirm('ចេញវិញ្ញាបនបត្រ ' + count + ' សម្រេច?\nIssue ' + count + ' certificate(s) now? You can print them from the Issue Certificate list afterwards.');
    };

    document.addEventListener('DOMContentLoaded', function () {
        ['category', 'certificate_title', 'khmer_title', 'course_or_achievement_title',
         'khmer_course_or_achievement_title', 'citation', 'khmer_citation', 'training_hours',
         'compliance_status', 'issue_date', 'khmer_issue_date'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () { refreshBatchPreview(); });
                el.addEventListener('change', function () { refreshBatchPreview(); });
            }
        });

        updateSelectedCount();
        applyStaffFilters();
        var picker = document.getElementById('batch_template_picker');
        if (picker) {
            prepareTemplateForPreviewAndForm(picker);
            updateBatchLayoutSelectorCard();
        }

        var galleryModal = document.getElementById('batchLayoutGalleryModal');
        if (galleryModal) {
            galleryModal.addEventListener('shown.bs.modal', loadBatchGalleryFrames);
        }

        onStaffSelectionChange();
    });
</script>
@endsection
