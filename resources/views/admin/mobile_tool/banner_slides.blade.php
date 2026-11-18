@extends('layouts.master')
@section('title', 'Banner Slides')
@section('action', 'Manage the mobile home banner slideshow')

@section('styles')
<style>
.mt-page {
    --ink: #172b4d;
    --muted: #64748b;
    --line: #e2e8f0;
    --soft: #f8fafc;
    color: var(--ink);
}
.mt-hero {
    border: 0;
    border-radius: 16px;
    background: linear-gradient(120deg, #1e3a8a, #2563eb, #3b82f6);
    color: #fff;
    box-shadow: 0 10px 26px rgba(30, 58, 138, 0.16);
}
.mt-hero .card-body {
    padding: 24px 30px;
}
.mt-eyebrow {
    font-size: .75rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #bfdbfe;
    font-weight: 700;
}
.mt-hero h2 {
    color: #fff;
    font-size: 1.55rem;
    font-weight: 700;
    margin: .4rem 0;
}
.mt-hero p {
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    max-width: 760px;
}
.mt-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(23, 43, 77, 0.04);
    background: #fff;
}
.mt-preview {
    position: relative;
    width: min(100%, 380px);
    aspect-ratio: 3/1;
    overflow: hidden;
    border-radius: 20px;
    background: #0f172a;
    color: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);
    user-select: none;
}
.mt-preview img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity .3s ease;
}
.mt-preview::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(10, 25, 47, 0.72), rgba(10, 25, 47, 0.35), rgba(10, 25, 47, 0.6));
    pointer-events: none;
}
.mt-preview.mt-animated-border::before,
.phone-banner-box.mt-animated-border::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 3;
    border: 2.5px solid #00e5ff;
    border-radius: inherit;
    pointer-events: none;
    box-shadow: inset 0 0 12px rgba(0, 229, 255, 0.6);
    animation: mt-border-pulse 1.2s ease-in-out infinite alternate;
}
@keyframes mt-border-pulse {
    from { opacity: .3; }
    to { opacity: .95; }
}
@media (prefers-reduced-motion: reduce) {
    .mt-preview.mt-animated-border::before,
    .phone-banner-box.mt-animated-border::before { animation: none; }
}
.mt-preview-copy {
    position: absolute;
    z-index: 2;
    inset: 10px auto 10px 16px;
    width: 55%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    font: 600 11.5px system-ui, sans-serif;
    text-shadow: 0 2px 4px rgba(0,0,0,0.6);
}
.mt-preview-title {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    background: rgba(255, 255, 255, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    padding: 1px 8px;
    font-size: 9.5px;
    font-weight: 800;
    margin-bottom: 2px;
}
.mt-preview-title[hidden] {
    display: none !important;
}
.mt-preview-clock {
    font-size: 28px;
    font-weight: 900;
    letter-spacing: -0.5px;
    line-height: 1;
    margin: 2px 0 4px;
}
.mt-preview-clock small {
    font-size: 15px;
    opacity: 0.9;
}
.mt-preview-dots {
    position: absolute;
    z-index: 3;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 4px;
    padding: 2px 6px;
    border-radius: 10px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
}
.mt-preview-dots[hidden] {
    display: none !important;
}
.mt-preview-dots span {
    width: 5px;
    height: 5px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.45);
    transition: all .2s ease;
}
.mt-preview-dots .active {
    width: 14px;
    background: #00e5ff;
}

/* DataTable Styling */
.table-slides thead th {
    background: var(--soft);
    border-top: 0;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .055em;
    font-size: .72rem;
    font-weight: 700;
    padding: 13px 16px;
    white-space: nowrap;
}
.table-slides td {
    padding: 14px 16px;
    vertical-align: middle;
    border-color: #edf2f7;
}
.slide-thumb {
    width: 105px;
    aspect-ratio: 3/1;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    background: #0f172a;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    display: inline-block;
    vertical-align: middle;
    transition: transform .15s ease, box-shadow .15s ease;
}
.slide-thumb:hover {
    transform: scale(1.04);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}
.slide-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.slide-thumb .fallback-badge {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
}
.mt-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 700;
    font-size: .75rem;
    white-space: nowrap;
}
.mt-status:before {
    content: "";
    width: 6px;
    height: 6px;
    background: currentColor;
    border-radius: 50%;
}
.mt-status-on {
    background: #dcfce7;
    color: #15803d;
}
.mt-status-off {
    background: #f1f5f9;
    color: #64748b;
}
.order-btn {
    padding: 2px 6px;
    line-height: 1;
    border-radius: 4px;
}
.order-btn:disabled {
    opacity: 0.25;
    cursor: not-allowed;
}
.action-btn-group {
    display: inline-flex;
    gap: 4px;
    align-items: center;
}
.action-btn-group form {
    margin: 0;
}
.modal-preview-box {
    background: #0b1120;
    border-radius: 14px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 1px solid #1e293b;
}

/* ======================================================== */
/* REAL MOBILE PHONE SCREEN MOCKUP                          */
/* ======================================================== */
.phone-mockup {
    width: 320px;
    background: #090d16;
    border-radius: 40px;
    padding: 10px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45), 0 0 0 2px #334155, 0 0 0 6px #0f172a;
    position: relative;
    color: #fff;
    user-select: none;
    font-family: system-ui, -apple-system, sans-serif;
}
.phone-notch-island {
    position: absolute;
    top: 7px;
    left: 50%;
    transform: translateX(-50%);
    width: 90px;
    height: 20px;
    background: #000;
    border-radius: 20px;
    z-index: 25;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.notch-sensor {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #0f172a;
}
.notch-lens {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1e293b;
    border: 1px solid #0f172a;
}
.phone-screen {
    background: #0f172a;
    border-radius: 32px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255,255,255,0.08);
}
.phone-status-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px 4px;
    font-size: 11px;
    font-weight: 700;
    color: #f8fafc;
    position: relative;
    z-index: 10;
}
.phone-app-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 14px 10px;
}
.phone-user-badge {
    display: flex;
    align-items: center;
    gap: 8px;
}
.phone-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 12px;
    color: #fff;
    border: 2px solid #1e293b;
}
.phone-user-text {
    line-height: 1.2;
}
.phone-user-name {
    font-size: 11px;
    font-weight: 700;
    color: #fff;
}
.phone-user-role {
    font-size: 9px;
    color: #94a3b8;
}

/* Real Phone Banner Carousel */
.phone-banner-box {
    position: relative;
    width: calc(100% - 20px);
    margin: 0 auto 12px;
    aspect-ratio: 3 / 1;
    border-radius: 16px;
    overflow: hidden;
    background: #020617;
    box-shadow: 0 8px 20px rgba(0,0,0,0.35);
}
.phone-banner-box img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity .35s ease;
}
.phone-banner-box::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(10, 25, 47, 0.75), rgba(10, 25, 47, 0.32), rgba(10, 25, 47, 0.65));
    pointer-events: none;
}
.phone-banner-copy {
    position: absolute;
    z-index: 2;
    inset: 8px auto 8px 12px;
    width: 60%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-shadow: 0 2px 4px rgba(0,0,0,0.7);
}
.phone-banner-badge {
    align-self: flex-start;
    background: rgba(255, 255, 255, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 7px;
    padding: 1px 6px;
    font-size: 8.5px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 2px;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.phone-banner-clock {
    font-size: 22px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.5px;
    margin: 2px 0 2px;
    color: #fff;
}
.phone-banner-clock small {
    font-size: 12px;
    opacity: 0.9;
}
.phone-banner-dots {
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
    display: flex;
    gap: 4px;
    padding: 2px 6px;
    border-radius: 10px;
    background: rgba(0,0,0,0.5);
}
.phone-banner-dots span {
    width: 4px;
    height: 4px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.45);
}
.phone-banner-dots span.active {
    width: 11px;
    background: #00e5ff;
}

/* Mock Phone App Bottom Body */
.phone-mock-body {
    padding: 0 10px 14px;
}
.phone-card {
    background: #1e293b;
    border-radius: 12px;
    padding: 10px;
    margin-bottom: 8px;
    border: 1px solid rgba(255,255,255,0.06);
}
.phone-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
    margin-top: 8px;
}
.phone-grid-item {
    background: #1e293b;
    border-radius: 10px;
    padding: 8px 3px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    font-size: 8.5px;
    font-weight: 600;
    color: #cbd5e1;
}
.phone-home-bar {
    width: 100px;
    height: 4px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.35);
    margin: 8px auto 6px;
}
</style>
@endsection

@section('main-content')
<div class="mt-page">
    @include('admin.section.flash_message')

    <!-- HERO HEADER -->
    <div class="card mt-hero mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="mt-eyebrow"><i data-feather="smartphone" style="width:14px;height:14px;" class="me-1"></i> Mobile Tool</div>
                <h2>Banner Slides Management</h2>
                <p>Manage the dynamic banner image carousel displayed on the mobile app home screen. Configure individual slides, direct link actions, and live playback timing.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-light text-primary fw-bold shadow-sm" onclick="openRealPreviewModal(0)">
                    <i data-feather="smartphone" class="me-1" style="width:16px;height:16px"></i> Real Mobile Preview
                </button>
                <button type="button" class="btn btn-light text-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                    <i data-feather="plus-circle" class="me-1" style="width:16px;height:16px"></i> Add Slide
                </button>
                <button type="button" class="btn btn-outline-light fw-bold" data-bs-toggle="modal" data-bs-target="#playbackModal">
                    <i data-feather="settings" class="me-1" style="width:16px;height:16px"></i> Playback Settings
                </button>
                <button type="button" class="btn btn-outline-light fw-bold" data-bs-toggle="modal" data-bs-target="#resetModal">
                    <i data-feather="rotate-ccw" class="me-1" style="width:16px;height:16px"></i> Reset
                </button>
            </div>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i data-feather="alert-circle" class="me-1"></i> Validation Notice:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- LIVE MOBILE PREVIEW CARD -->
    <div class="card mt-card mb-4" id="preview-section">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i data-feather="eye" class="text-primary" style="width:18px;height:18px"></i>
                <h6 class="mb-0 fw-bold">Live Mobile Carousel Preview</h6>
                <span class="badge bg-light text-secondary border ms-1" id="previewSlideCounter">Slide 1 of {{ max(1, count($config['slides'])) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-info fw-semibold" onclick="openRealPreviewModal(activeSlideIdx)">
                    <i data-feather="maximize-2" style="width:13px;height:13px" class="me-1"></i> Open in Phone Screen
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="preview-prev" title="Previous Slide">
                    <i data-feather="chevron-left" style="width:14px;height:14px"></i> Prev
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="preview-play" title="Toggle Play / Pause">
                    <i data-feather="pause" style="width:14px;height:14px" id="preview-play-icon"></i> <span id="preview-play-text">Pause</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="preview-next" title="Next Slide">
                    Next <i data-feather="chevron-right" style="width:14px;height:14px"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-4 bg-light d-flex flex-column align-items-center justify-content-center">
            <div class="mt-preview {{ !empty($config['animateBorder']) ? 'mt-animated-border' : '' }}" id="liveMobilePreview" aria-label="Mobile Home banner preview">
                <img src="{{ asset('assets/images/angkor_wat_banner.png') }}"
                     id="livePreviewImg"
                     data-fallback="{{ asset('assets/images/angkor_wat_banner.png') }}"
                     onerror="if (this.getAttribute('src') !== this.dataset.fallback) this.src = this.dataset.fallback;" alt="Banner Slide">
                <div class="mt-preview-copy">
                    <span class="mt-preview-title" id="livePreviewTitle" hidden></span>
                    <strong style="font-size:16px">សូមស្វាគមន៍!</strong>
                    <span>Good Morning!</span>
                    <span class="mt-preview-clock">08:30<small>:15</small></span>
                    <span style="font-size:10px">{{ now()->format('l, F j, Y') }}</span>
                </div>
                <div class="mt-preview-dots" id="livePreviewDots" {{ empty($config['showIndicators']) || count($config['slides']) < 2 ? 'hidden' : '' }}></div>
            </div>
            <div class="mt-2 text-muted small text-center" id="livePreviewInfo">
                <i data-feather="info" style="width:12px;height:12px" class="me-1"></i>
                <span id="previewInfoText">Rotating every {{ $config['intervalSeconds'] }}s • {{ ucfirst($config['transition']) }} transition • Autoplay {{ !empty($config['autoplay']) ? 'ON' : 'OFF' }}</span>
            </div>
        </div>
    </div>

    <!-- DATATABLE, TOOLBAR & FILTERS -->
    <div class="card mt-card mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Search & Filters Toolbar -->
                <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i data-feather="search" style="width:14px;height:14px"></i></span>
                        <input type="text" id="slideSearchInput" class="form-control border-start-0" placeholder="Search slides by title, link...">
                    </div>

                    <div style="min-width: 140px;">
                        <select id="statusFilter" class="form-select form-select-sm">
                            <option value="all">All Statuses</option>
                            <option value="active">Active (Visible)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                    </div>

                    <div style="min-width: 155px;">
                        <select id="imageTypeFilter" class="form-select form-select-sm">
                            <option value="all">All Image Sources</option>
                            <option value="custom">Custom Image</option>
                            <option value="default">Wallpaper Fallback</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFiltersBtn" title="Reset Filters">
                        <i data-feather="rotate-ccw" style="width:12px;height:12px"></i> Clear
                    </button>
                </div>

                <!-- Right Toolbar Actions -->
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2 py-1" id="slideShowingBadge">
                        Showing <span id="filteredCount">{{ count($config['slides']) }}</span> of {{ count($config['slides']) }} slides
                    </span>
                    <button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                        <i data-feather="plus" style="width:14px;height:14px" class="me-1"></i> Add Slide
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-slides mb-0 align-middle" id="slidesDataTable">
                <thead>
                    <tr>
                        <th style="width: 85px;"># Order</th>
                        <th style="width: 140px;">Preview</th>
                        <th>Title & Caption</th>
                        <th>Target Link</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 160px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="slidesTableBody">
                    @forelse($config['slides'] as $index => $slide)
                    @php
                        $slideId = $slide['id'] ?? ('slide_'.$index);
                        $hasCustomImage = !empty($slide['imageUrl']);
                        $imgSource = $slide['imageUrl'] ?? asset('assets/images/angkor_wat_banner.png');
                        $isActive = !empty($slide['enabled']);
                    @endphp
                    <tr class="slide-row"
                        id="slide-row-{{ $slideId }}"
                        data-id="{{ $slideId }}"
                        data-index="{{ $index }}"
                        data-title="{{ strtolower($slide['title'] ?? '') }}"
                        data-link="{{ strtolower($slide['linkUrl'] ?? '') }}"
                        data-status="{{ $isActive ? 'active' : 'inactive' }}"
                        data-image-type="{{ $hasCustomImage ? 'custom' : 'default' }}"
                        data-image-url="{{ $imgSource }}"
                        data-display-title="{{ $slide['title'] ?? '' }}">
                        <!-- Order & Reorder Arrows -->
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-light text-dark border fw-bold">{{ $index + 1 }}</span>
                                <div class="d-flex flex-column gap-1 ms-1">
                                    <form method="post" action="{{ route('admin.mobile-tools.banner-slides.reorder', $slideId) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="btn btn-xs btn-outline-secondary order-btn p-0 px-1" title="Move Up" {{ $index === 0 ? 'disabled' : '' }}>
                                            <i data-feather="chevron-up" style="width:12px;height:12px"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.mobile-tools.banner-slides.reorder', $slideId) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="btn btn-xs btn-outline-secondary order-btn p-0 px-1" title="Move Down" {{ $loop->last ? 'disabled' : '' }}>
                                            <i data-feather="chevron-down" style="width:12px;height:12px"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>

                        <!-- Thumbnail Preview with Click-to-Preview Modal -->
                        <td>
                            <div class="slide-thumb" role="button" onclick="openRealPreviewModal({{ $index }})" style="cursor:pointer;" title="Click to open Real Mobile Preview">
                                <img src="{{ $imgSource }}" alt="Slide {{ $index + 1 }}" onerror="this.src='{{ asset('assets/images/angkor_wat_banner.png') }}'">
                                @if(!$hasCustomImage)
                                    <div class="fallback-badge">Wallpaper</div>
                                @endif
                            </div>
                        </td>

                        <!-- Title & Info -->
                        <td>
                            <div class="fw-bold text-dark mb-1">
                                {{ $slide['title'] ?? '— (No Title)' }}
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: .68rem;">ID: {{ $slideId }}</span>
                                @if($hasCustomImage)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: .68rem;">
                                        <i data-feather="image" style="width:10px;height:10px"></i> Custom Image
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: .68rem;">
                                        <i data-feather="layout" style="width:10px;height:10px"></i> Home Wallpaper
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Link Target -->
                        <td>
                            @if(!empty($slide['linkUrl']))
                                <a href="{{ $slide['linkUrl'] }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 220px;" title="{{ $slide['linkUrl'] }}">
                                    <i data-feather="external-link" style="width:12px;height:12px;flex-shrink:0;"></i>
                                    <span class="text-truncate">{{ $slide['linkUrl'] }}</span>
                                </a>
                            @else
                                <span class="text-muted small">None (Display only)</span>
                            @endif
                        </td>

                        <!-- Status & Toggle -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="mt-status {{ $isActive ? 'mt-status-on' : 'mt-status-off' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                                <form method="post" action="{{ route('admin.mobile-tools.banner-slides.toggle', $slideId) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $isActive ? 'secondary' : 'success' }} py-0 px-2" title="{{ $isActive ? 'Deactivate' : 'Activate' }}" style="font-size:.75rem">
                                        {{ $isActive ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="text-end">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-outline-info" title="View Real Mobile Preview" onclick="openRealPreviewModal({{ $index }})">
                                    <i data-feather="eye" style="width:14px;height:14px"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Edit Slide" data-bs-toggle="modal" data-bs-target="#editSlideModal-{{ $slideId }}">
                                    <i data-feather="edit-2" style="width:14px;height:14px"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Slide" data-bs-toggle="modal" data-bs-target="#deleteSlideModal-{{ $slideId }}">
                                    <i data-feather="trash-2" style="width:14px;height:14px"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-row">
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i data-feather="image" style="width:40px;height:40px" class="mb-2 text-secondary"></i>
                                <h5>No banner slides found</h5>
                                <p class="mb-3 small">Add your first slide to customize the mobile home screen carousel.</p>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                                    <i data-feather="plus" class="me-1"></i> Add First Slide
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    <tr id="no-filter-match-row" style="display:none;">
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i data-feather="search" style="width:28px;height:28px" class="mb-2"></i>
                            <div class="fw-semibold">No slides match your search / filter criteria.</div>
                            <small>Try clearing your search terms or resetting filters.</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: REAL MOBILE SCREEN PREVIEW                        -->
<!-- ======================================================== -->
<div class="modal fade" id="realPreviewModal" tabindex="-1" aria-labelledby="realPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-bottom border-secondary py-3">
                <div class="d-flex align-items-center gap-2">
                    <i data-feather="smartphone" class="text-info" style="width:22px;height:22px"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="realPreviewModalLabel">Real Mobile Screen Preview</h5>
                        <small class="text-muted" id="realPreviewSubTitle">Actual 1:1 view as displayed on employee mobile phone</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- Slide switcher in header -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-light" id="modalNavPrev" onclick="stepModalSlide(-1)" title="Previous Slide">
                            <i data-feather="chevron-left" style="width:14px;height:14px"></i> Prev
                        </button>
                        <button type="button" class="btn btn-outline-light disabled text-info fw-bold" id="modalNavCounter">
                            Slide 1 of 1
                        </button>
                        <button type="button" class="btn btn-outline-light" id="modalNavNext" onclick="stepModalSlide(1)" title="Next Slide">
                            Next <i data-feather="chevron-right" style="width:14px;height:14px"></i>
                        </button>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="row g-4 align-items-center justify-content-center">
                    <!-- LEFT COLUMN: REALISTIC PHONE FRAME -->
                    <div class="col-lg-5 d-flex justify-content-center">
                        <div class="phone-mockup">
                            <!-- Dynamic Island / Speaker Notch -->
                            <div class="phone-notch-island">
                                <div class="notch-sensor"></div>
                                <div class="notch-lens"></div>
                            </div>

                            <!-- Phone Screen -->
                            <div class="phone-screen">
                                <!-- Status Bar (Live Clock, 5G, Wi-Fi, Battery) -->
                                <div class="phone-status-bar">
                                    <span class="phone-real-time" id="phoneStatusTime">09:41</span>
                                    <div class="d-flex align-items-center gap-2 text-white" style="font-size: 11px;">
                                        <i data-feather="bar-chart-2" style="width:12px;height:12px"></i>
                                        <i data-feather="wifi" style="width:12px;height:12px"></i>
                                        <i data-feather="battery" style="width:14px;height:14px"></i>
                                    </div>
                                </div>

                                <!-- App Header Profile Bar -->
                                <div class="phone-app-topbar">
                                    <div class="phone-user-badge">
                                        <div class="phone-avatar">CR</div>
                                        <div class="phone-user-text">
                                            <div class="phone-user-name">CHHOEUN REACH</div>
                                            <div class="phone-user-role">IT & Branch Manager</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-danger rounded-pill p-1" style="font-size: 9px;"><i data-feather="bell" style="width:11px;height:11px;color:#fff"></i></span>
                                    </div>
                                </div>

                                <!-- REAL BANNER SLIDE CANVAS -->
                                <div class="phone-banner-box {{ !empty($config['animateBorder']) ? 'mt-animated-border' : '' }}" id="modalPhoneBannerBox">
                                    <img src="{{ asset('assets/images/angkor_wat_banner.png') }}"
                                         id="modalPhoneBannerImg"
                                         data-fallback="{{ asset('assets/images/angkor_wat_banner.png') }}"
                                         alt="Banner">
                                    <div class="phone-banner-copy">
                                        <span class="phone-banner-badge" id="modalPhoneBannerTitle" hidden></span>
                                        <strong style="font-size:13px;color:#fff;">សូមស្វាគមន៍!</strong>
                                        <span style="font-size:9.5px;color:rgba(255,255,255,0.9);">Good Morning!</span>
                                        <span class="phone-banner-clock" id="modalPhoneLiveClock">08:30<small>:15</small></span>
                                        <span style="font-size:8px;color:rgba(255,255,255,0.85);" id="modalPhoneLiveDate">{{ now()->format('l, F j, Y') }}</span>
                                    </div>
                                    <div class="phone-banner-dots" id="modalPhoneDots"></div>
                                </div>

                                <!-- MOCK APP SCREEN BODY (ATTENDANCE & ACTIONS) -->
                                <div class="phone-mock-body">
                                    <!-- Quick Attendance Action Card -->
                                    <div class="phone-card">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="font-size:10px;font-weight:700;color:#e2e8f0;">វត្តមានថ្ងៃនេះ • Attendance</span>
                                            <span class="badge bg-success" style="font-size:8.5px;">On Time</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded-3 border border-secondary" style="font-size:10px;">
                                            <div>
                                                <small class="text-muted d-block" style="font-size:8px;">Check In</small>
                                                <strong class="text-success">08:00 AM</strong>
                                            </div>
                                            <div class="vr bg-secondary"></div>
                                            <div>
                                                <small class="text-muted d-block" style="font-size:8px;">Check Out</small>
                                                <strong class="text-warning">--:-- --</strong>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm py-0 px-2 fw-bold" style="font-size:9.5px;">Check Out</button>
                                        </div>
                                    </div>

                                    <!-- Quick Feature Grid -->
                                    <div class="phone-grid">
                                        <div class="phone-grid-item">
                                            <i data-feather="calendar" style="width:13px;height:13px;color:#38bdf8;"></i>
                                            <span>Leave</span>
                                        </div>
                                        <div class="phone-grid-item">
                                            <i data-feather="dollar-sign" style="width:13px;height:13px;color:#4ade80;"></i>
                                            <span>Payroll</span>
                                        </div>
                                        <div class="phone-grid-item">
                                            <i data-feather="award" style="width:13px;height:13px;color:#facc15;"></i>
                                            <span>Certificates</span>
                                        </div>
                                        <div class="phone-grid-item">
                                            <i data-feather="message-circle" style="width:13px;height:13px;color:#f472b6;"></i>
                                            <span>Quotes</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Home Bottom Bar Indicator -->
                                <div class="phone-home-bar"></div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: SLIDE DETAILS, ACTIONS & CONTROLS -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold mb-1" id="modalSlideOrderBadge">Slide 1</span>
                                        <h4 class="fw-bold text-dark mb-0" id="modalDetailTitle">Slide Title</h4>
                                        <span class="font-monospace text-muted small" id="modalDetailId">ID: slide_xxx</span>
                                    </div>
                                    <span class="mt-status mt-status-on" id="modalDetailStatusBadge">Active</span>
                                </div>

                                <div class="list-group list-group-flush mb-4 rounded-3 border">
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span class="text-muted small fw-semibold"><i data-feather="image" style="width:14px;height:14px" class="me-1"></i> Image Source:</span>
                                        <span class="badge bg-light text-dark border" id="modalDetailImgSource">Custom Image</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span class="text-muted small fw-semibold"><i data-feather="link" style="width:14px;height:14px" class="me-1"></i> Target Link:</span>
                                        <div id="modalDetailLinkWrapper">
                                            <a href="#" target="_blank" class="text-decoration-none fw-semibold" id="modalDetailLink">None</a>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span class="text-muted small fw-semibold"><i data-feather="clock" style="width:14px;height:14px" class="me-1"></i> Playback Timing:</span>
                                        <span class="fw-semibold text-secondary small">{{ $config['intervalSeconds'] }} seconds ({{ ucfirst($config['transition']) }})</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span class="text-muted small fw-semibold"><i data-feather="zap" style="width:14px;height:14px" class="me-1"></i> Border Animation:</span>
                                        <span class="badge {{ !empty($config['animateBorder']) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ !empty($config['animateBorder']) ? 'Neon Glow Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Slide Selector Pills -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Jump to Slide</label>
                                    <div class="d-flex flex-wrap gap-2" id="modalSlidePills">
                                        <!-- Populated dynamically via JS -->
                                    </div>
                                </div>

                                <!-- Action Buttons in Modal -->
                                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                    <button type="button" class="btn btn-primary fw-bold" id="modalEditSlideBtn">
                                        <i data-feather="edit-2" class="me-1" style="width:14px;height:14px"></i> Edit This Slide
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary fw-bold" id="modalToggleSlideBtn">
                                        <i data-feather="power" class="me-1" style="width:14px;height:14px"></i> Toggle Status
                                    </button>
                                    <button type="button" class="btn btn-outline-light text-dark border fw-bold" data-bs-dismiss="modal">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2">
                <small class="text-muted me-auto">💡 This preview shows how the banner slide integrates directly with the live mobile home screen.</small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: ADD SLIDE                                         -->
<!-- ======================================================== -->
<div class="modal fade" id="addSlideModal" tabindex="-1" aria-labelledby="addSlideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.mobile-tools.banner-slides.store') }}" enctype="multipart/form-data" id="addSlideForm">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="addSlideModalLabel">
                        <i data-feather="plus-circle" class="me-1 text-primary"></i> Add New Banner Slide
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Inputs -->
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="add_title">Slide Title / Caption</label>
                                <input type="text" class="form-control" id="add_title" name="title" maxlength="180" placeholder="e.g. Happy Khmer New Year! ឬ ប្រូម៉ូសិនពិសេស" oninput="updateAddModalPreview()">
                                <small class="text-muted">Displays as a subtle glass badge on the banner.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="add_image">Upload Banner Image</label>
                                <input type="file" class="form-control" id="add_image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewAddFile(this)">
                                <small class="text-muted">Recommended: 1080×360 px (3:1 aspect ratio), JPG/PNG/WebP, max 10MB.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="add_image_url">…or Image URL</label>
                                <input type="url" class="form-control" id="add_image_url" name="imageUrl" maxlength="2048" placeholder="https://example.com/banner.jpg" oninput="updateAddModalPreview()">
                                <small class="text-muted">If both file and URL are given, the uploaded file takes precedence.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="add_link">Target Link URL (optional)</label>
                                <input type="url" class="form-control" id="add_link" name="linkUrl" maxlength="2048" placeholder="https://… (e.g. https://kneayerng.com/promo)">
                                <small class="text-muted">Tapping the banner on mobile will open this URL.</small>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Position</label>
                                    <select class="form-select" name="position">
                                        <option value="last" selected>Add to End</option>
                                        <option value="first">Add to Beginning</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 d-flex align-items-center pt-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="add_enabled" checked>
                                        <label class="form-check-label fw-semibold" for="add_enabled">Visible on Mobile</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Live Modal Preview -->
                        <div class="col-lg-5">
                            <label class="form-label fw-semibold">Live Banner Preview</label>
                            <div class="modal-preview-box">
                                <div class="mt-preview" style="width: 100%;">
                                    <img src="{{ asset('assets/images/angkor_wat_banner.png') }}"
                                         id="addPreviewImg"
                                         data-fallback="{{ asset('assets/images/angkor_wat_banner.png') }}"
                                         alt="Preview">
                                    <div class="mt-preview-copy">
                                        <span class="mt-preview-title" id="addPreviewTitle" hidden></span>
                                        <strong style="font-size:14px">សូមស្វាគមន៍!</strong>
                                        <span style="font-size:10px">Good Morning!</span>
                                        <span class="mt-preview-clock" style="font-size:22px">08:30<small>:15</small></span>
                                    </div>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary border mt-2" id="addPreviewBadge">Using Home Wallpaper</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i data-feather="save" class="me-1"></i> Save Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODALS: EDIT SLIDE (One per slide)                       -->
<!-- ======================================================== -->
@foreach($config['slides'] as $index => $slide)
@php
    $slideId = $slide['id'] ?? ('slide_'.$index);
    $hasCustomImage = !empty($slide['imageUrl']);
    $currentImg = $slide['imageUrl'] ?? asset('assets/images/angkor_wat_banner.png');
    $isActive = !empty($slide['enabled']);
@endphp
<div class="modal fade" id="editSlideModal-{{ $slideId }}" tabindex="-1" aria-labelledby="editSlideModalLabel-{{ $slideId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.mobile-tools.banner-slides.update-slide', $slideId) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="editSlideModalLabel-{{ $slideId }}">
                        <i data-feather="edit-2" class="me-1 text-primary"></i> Edit Slide #{{ $index + 1 }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Inputs -->
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="edit_title_{{ $slideId }}">Slide Title / Caption</label>
                                <input type="text" class="form-control" id="edit_title_{{ $slideId }}" name="title" maxlength="180"
                                       value="{{ $slide['title'] ?? '' }}" placeholder="Optional caption"
                                       oninput="updateEditModalPreview('{{ $slideId }}')">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="edit_image_{{ $slideId }}">Replace Banner Image</label>
                                <input type="file" class="form-control" id="edit_image_{{ $slideId }}" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                                       onchange="previewEditFile(this, '{{ $slideId }}')">
                                <small class="text-muted">Upload a new image to replace the current one.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="edit_image_url_{{ $slideId }}">…or Image URL</label>
                                <input type="url" class="form-control" id="edit_image_url_{{ $slideId }}" name="imageUrl" maxlength="2048"
                                       value="{{ $slide['imageUrl'] ?? '' }}" placeholder="https://example.com/banner.jpg"
                                       oninput="updateEditModalPreview('{{ $slideId }}')">
                            </div>

                            @if($hasCustomImage)
                            <div class="form-check mb-3 p-2 bg-light rounded border">
                                <input class="form-check-input ms-1" type="checkbox" name="remove_image" value="1" id="remove_image_{{ $slideId }}"
                                       onchange="toggleRemoveImage('{{ $slideId }}')">
                                <label class="form-check-label ms-2 text-danger fw-semibold" for="remove_image_{{ $slideId }}">
                                    Remove custom image (Revert to default Home Wallpaper)
                                </label>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="edit_link_{{ $slideId }}">Target Link URL</label>
                                <input type="url" class="form-control" id="edit_link_{{ $slideId }}" name="linkUrl" maxlength="2048"
                                       value="{{ $slide['linkUrl'] ?? '' }}" placeholder="https://…">
                            </div>

                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1" id="edit_enabled_{{ $slideId }}" @checked($isActive)>
                                <label class="form-check-label fw-semibold" for="edit_enabled_{{ $slideId }}">Visible on Mobile (Enabled)</label>
                            </div>
                        </div>

                        <!-- Right Column: Preview -->
                        <div class="col-lg-5">
                            <label class="form-label fw-semibold">Slide Preview</label>
                            <div class="modal-preview-box">
                                <div class="mt-preview" style="width: 100%;">
                                    <img src="{{ $currentImg }}"
                                         id="editPreviewImg_{{ $slideId }}"
                                         data-initial="{{ $currentImg }}"
                                         data-fallback="{{ asset('assets/images/angkor_wat_banner.png') }}"
                                         alt="Slide preview">
                                    <div class="mt-preview-copy">
                                        <span class="mt-preview-title" id="editPreviewTitle_{{ $slideId }}" {{ empty($slide['title']) ? 'hidden' : '' }}>
                                            {{ $slide['title'] ?? '' }}
                                        </span>
                                        <strong style="font-size:14px">សូមស្វាគមន៍!</strong>
                                        <span style="font-size:10px">Good Morning!</span>
                                        <span class="mt-preview-clock" style="font-size:22px">08:30<small>:15</small></span>
                                    </div>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary border mt-2" id="editPreviewBadge_{{ $slideId }}">
                                    {{ $hasCustomImage ? 'Custom Image Active' : 'Using Home Wallpaper' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i data-feather="check" class="me-1"></i> Update Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: DELETE SLIDE -->
<div class="modal fade" id="deleteSlideModal-{{ $slideId }}" tabindex="-1" aria-labelledby="deleteSlideModalLabel-{{ $slideId }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.mobile-tools.banner-slides.destroy', $slideId) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="deleteSlideModalLabel-{{ $slideId }}">
                        <i data-feather="alert-triangle" class="me-1"></i> Delete Slide #{{ $index + 1 }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="slide-thumb mb-3" style="width: 180px;">
                        <img src="{{ $currentImg }}" alt="Slide" onerror="this.src='{{ asset('assets/images/angkor_wat_banner.png') }}'">
                    </div>
                    <h5 class="fw-bold mb-2">{{ $slide['title'] ?: 'Slide #'.($index + 1) }}</h5>
                    <p class="text-muted mb-0">Are you sure you want to delete this banner slide? Any uploaded image file will be permanently removed.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i data-feather="trash-2" class="me-1"></i> Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- ======================================================== -->
<!-- MODAL: PLAYBACK SETTINGS                                 -->
<!-- ======================================================== -->
<div class="modal fade" id="playbackModal" tabindex="-1" aria-labelledby="playbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.mobile-tools.banner-slides.playback') }}">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="playbackModalLabel">
                        <i data-feather="settings" class="me-1 text-primary"></i> Slideshow Playback Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Controls rotation speed, transitions, and indicators on employee phones.</p>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="intervalSeconds">Interval (seconds)</label>
                            <input class="form-control" type="number" id="intervalSeconds" name="intervalSeconds" min="2" max="30" required value="{{ old('intervalSeconds', $config['intervalSeconds']) }}">
                            <small class="text-muted">Between 2 and 30 seconds</small>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="transition">Transition Effect</label>
                            <select class="form-select" id="transition" name="transition">
                                @foreach(\App\Support\BannerSlideshowTransitions::OPTIONS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('transition', $config['transition']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Autoplay</label>
                            <select class="form-select" name="autoplay">
                                <option value="1" @selected((bool) old('autoplay', $config['autoplay']))>On (Rotate automatically)</option>
                                <option value="0" @selected(old('autoplay', $config['autoplay']) !== null && !(bool) old('autoplay', $config['autoplay']))>Off (Manual swipe only)</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Indicator Dots</label>
                            <select class="form-select" name="showIndicators">
                                <option value="1" @selected((bool) old('showIndicators', $config['showIndicators']))>Shown</option>
                                <option value="0" @selected(old('showIndicators', $config['showIndicators']) !== null && !(bool) old('showIndicators', $config['showIndicators']))>Hidden</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Pause on Touch</label>
                            <select class="form-select" name="pauseOnTouch">
                                <option value="1" @selected((bool) old('pauseOnTouch', $config['pauseOnTouch']))>Yes (Pause when touched)</option>
                                <option value="0" @selected(old('pauseOnTouch', $config['pauseOnTouch']) !== null && !(bool) old('pauseOnTouch', $config['pauseOnTouch']))>No</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Loop</label>
                            <select class="form-select" name="loop">
                                <option value="1" @selected((bool) old('loop', $config['loop']))>Yes (Infinite carousel)</option>
                                <option value="0" @selected(old('loop', $config['loop']) !== null && !(bool) old('loop', $config['loop']))>No (Stop at last slide)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Animated Border</label>
                            <select class="form-select" name="animateBorder">
                                <option value="0" @selected(!(bool) old('animateBorder', $config['animateBorder']))>Off (Standard clean border)</option>
                                <option value="1" @selected((bool) old('animateBorder', $config['animateBorder']))>On (Pulsing neon glow border)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i data-feather="check" class="me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: RESET SLIDESHOW                                   -->
<!-- ======================================================== -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.mobile-tools.banner-slides.reset') }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="resetModalLabel">
                        <i data-feather="alert-triangle" class="me-1"></i> Reset Banner Slideshow
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i data-feather="rotate-ccw" style="width:48px;height:48px" class="text-danger mb-3"></i>
                    <h5 class="fw-bold">Reset slideshow to default?</h5>
                    <p class="text-muted mb-0">This will delete all custom slides, remove any uploaded slide images from storage, and restore default settings.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i data-feather="rotate-ccw" class="me-1"></i> Reset Everything
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==========================================
// DATA & LIVE PLAYBACK PREVIEW LOGIC
// ==========================================
const slidesData = @json($config['slides']);
const playbackConfig = {
    intervalSeconds: {{ (int) $config['intervalSeconds'] }},
    autoplay: {{ !empty($config['autoplay']) ? 'true' : 'false' }},
    showIndicators: {{ !empty($config['showIndicators']) ? 'true' : 'false' }},
    animateBorder: {{ !empty($config['animateBorder']) ? 'true' : 'false' }},
    pauseOnTouch: {{ !empty($config['pauseOnTouch']) ? 'true' : 'false' }},
    loop: {{ !empty($config['loop']) ? 'true' : 'false' }},
    transition: '{{ $config['transition'] }}'
};

const defaultWallpaper = "{{ asset('assets/images/angkor_wat_banner.png') }}";
const livePreview = document.getElementById('liveMobilePreview');
const liveImg = document.getElementById('livePreviewImg');
const liveTitle = document.getElementById('livePreviewTitle');
const liveDots = document.getElementById('livePreviewDots');
const playBtn = document.getElementById('preview-play');
const playIcon = document.getElementById('preview-play-icon');
const playText = document.getElementById('preview-play-text');
const counterBadge = document.getElementById('previewSlideCounter');

let activeSlideIdx = 0;
let playbackTimer = null;
let isPaused = !playbackConfig.autoplay;
let pointerHeld = false;
let outgoingNode = null;
let animationRef = null;

const transitionStyles = {
    fade: 'none',
    slide: 'translateX(8%)',
    'slide-up': 'translateY(8%)',
    zoom: 'scale(.92)',
    instant: 'none'
};

// Filter enabled slides for playback
function getPlayableSlides() {
    const active = slidesData.filter(s => s && s.enabled);
    return active.length > 0 ? active : slidesData;
}

function renderPlayback() {
    clearTimeout(playbackTimer);
    const playable = getPlayableSlides();
    if (!playable.length) {
        liveImg.src = defaultWallpaper;
        liveTitle.hidden = true;
        counterBadge.textContent = 'No slides';
        liveDots.hidden = true;
        return;
    }

    activeSlideIdx = (activeSlideIdx + playable.length) % playable.length;
    const currentSlide = playable[activeSlideIdx];
    const newSrc = currentSlide.imageUrl || defaultWallpaper;
    const title = (currentSlide.title || '').trim();

    // Title overlay
    if (title) {
        liveTitle.textContent = title;
        liveTitle.hidden = false;
    } else {
        liveTitle.hidden = true;
    }

    // Image transition
    if (liveImg.getAttribute('src') !== newSrc) {
        animationRef?.cancel();
        outgoingNode?.remove();

        if (playbackConfig.transition !== 'instant' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const clone = liveImg.cloneNode();
            liveImg.before(clone);
            outgoingNode = clone;

            const start = transitionStyles[playbackConfig.transition] || 'none';
            const hidden = { opacity: 0, transform: start };
            const shown = { opacity: 1, transform: 'none' };

            animationRef = liveImg.animate([hidden, shown], { duration: 420, easing: 'cubic-bezier(.215,.61,.355,1)' });
            clone.animate([shown, hidden], { duration: 420, easing: 'bezier(.55,.055,.675,.19)' })
                 .onfinish = () => clone.remove();
        }
        liveImg.src = newSrc;
    }

    // Dots
    if (playbackConfig.showIndicators && playable.length > 1) {
        liveDots.hidden = false;
        liveDots.replaceChildren(...playable.map((_, i) => {
            const dot = document.createElement('span');
            dot.className = (i === activeSlideIdx) ? 'active' : '';
            return dot;
        }));
    } else {
        liveDots.hidden = true;
    }

    // Counter
    counterBadge.textContent = `Slide ${activeSlideIdx + 1} of ${playable.length}`;

    // Play/Pause button state
    const hasNext = playbackConfig.loop || activeSlideIdx < playable.length - 1;
    if (isPaused || !hasNext) {
        playText.textContent = hasNext ? 'Play' : 'Replay';
        if (playIcon) playIcon.setAttribute('data-feather', 'play');
    } else {
        playText.textContent = 'Pause';
        if (playIcon) playIcon.setAttribute('data-feather', 'pause');
    }
    if (window.feather) feather.replace();

    // Auto rotate timer
    if (!isPaused && playable.length > 1 && !pointerHeld && hasNext) {
        playbackTimer = setTimeout(() => {
            activeSlideIdx++;
            renderPlayback();
        }, Math.max(2, playbackConfig.intervalSeconds) * 1000);
    }
}

// Preview navigation buttons
document.getElementById('preview-prev').onclick = () => {
    activeSlideIdx--;
    renderPlayback();
};
document.getElementById('preview-next').onclick = () => {
    activeSlideIdx++;
    renderPlayback();
};
playBtn.onclick = () => {
    const playable = getPlayableSlides();
    const isAtEnd = !playbackConfig.loop && activeSlideIdx === playable.length - 1;
    if (isAtEnd) {
        activeSlideIdx = 0;
        isPaused = false;
    } else {
        isPaused = !isPaused;
    }
    renderPlayback();
};

// Pause on touch
if (playbackConfig.pauseOnTouch && livePreview) {
    livePreview.addEventListener('pointerdown', (e) => {
        livePreview.setPointerCapture(e.pointerId);
        pointerHeld = true;
        clearTimeout(playbackTimer);
    });
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(ev => {
        livePreview.addEventListener(ev, () => {
            pointerHeld = false;
            renderPlayback();
        });
    });
}

// ==========================================
// REAL MOBILE SCREEN PREVIEW MODAL LOGIC
// ==========================================
let currentModalSlideIdx = 0;
let realPhoneClockInterval = null;

function updateRealPhoneClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    // Status bar clock
    const statusTime = document.getElementById('phoneStatusTime');
    if (statusTime) statusTime.textContent = `${hours}:${minutes}`;

    // Banner Clock
    const bannerClock = document.getElementById('modalPhoneLiveClock');
    if (bannerClock) bannerClock.innerHTML = `${hours}:${minutes}<small>:${seconds}</small>`;
}

function openRealPreviewModal(index) {
    if (!slidesData || !slidesData.length) return;
    currentModalSlideIdx = (index >= 0 && index < slidesData.length) ? index : 0;
    renderRealModalContent();

    // Show modal
    const modalEl = document.getElementById('realPreviewModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    // Start ticking clock
    clearInterval(realPhoneClockInterval);
    updateRealPhoneClock();
    realPhoneClockInterval = setInterval(updateRealPhoneClock, 1000);
}

function stepModalSlide(direction) {
    if (!slidesData || !slidesData.length) return;
    currentModalSlideIdx = (currentModalSlideIdx + direction + slidesData.length) % slidesData.length;
    renderRealModalContent();
}

function renderRealModalContent() {
    const slide = slidesData[currentModalSlideIdx];
    if (!slide) return;

    const count = slidesData.length;
    const imgUrl = slide.imageUrl || defaultWallpaper;
    const hasCustomImg = !!slide.imageUrl;
    const title = (slide.title || '').trim();
    const slideId = slide.id || ('slide_' + currentModalSlideIdx);
    const isEnabled = !!slide.enabled;

    // Header counter & title
    const navCounter = document.getElementById('modalNavCounter');
    if (navCounter) navCounter.textContent = `Slide ${currentModalSlideIdx + 1} of ${count}`;

    const subTitle = document.getElementById('realPreviewSubTitle');
    if (subTitle) subTitle.textContent = `Viewing Slide #${currentModalSlideIdx + 1}: ${title || 'No Title Set'}`;

    // Phone Mockup elements
    const phoneImg = document.getElementById('modalPhoneBannerImg');
    if (phoneImg) phoneImg.src = imgUrl;

    const phoneTitle = document.getElementById('modalPhoneBannerTitle');
    if (phoneTitle) {
        phoneTitle.textContent = title;
        phoneTitle.hidden = !title;
    }

    // Dots in phone banner
    const phoneDots = document.getElementById('modalPhoneDots');
    if (phoneDots) {
        if (playbackConfig.showIndicators && count > 1) {
            phoneDots.hidden = false;
            phoneDots.replaceChildren(...slidesData.map((_, i) => {
                const s = document.createElement('span');
                if (i === currentModalSlideIdx) s.className = 'active';
                return s;
            }));
        } else {
            phoneDots.hidden = true;
        }
    }

    // Details Column
    const orderBadge = document.getElementById('modalSlideOrderBadge');
    if (orderBadge) orderBadge.textContent = `Slide #${currentModalSlideIdx + 1}`;

    const detailTitle = document.getElementById('modalDetailTitle');
    if (detailTitle) detailTitle.textContent = title || '— (No Title Set)';

    const detailId = document.getElementById('modalDetailId');
    if (detailId) detailId.textContent = `ID: ${slideId}`;

    const statusBadge = document.getElementById('modalDetailStatusBadge');
    if (statusBadge) {
        statusBadge.className = isEnabled ? 'mt-status mt-status-on' : 'mt-status mt-status-off';
        statusBadge.textContent = isEnabled ? 'Active (Visible on Mobile)' : 'Inactive (Hidden)';
    }

    const sourceBadge = document.getElementById('modalDetailImgSource');
    if (sourceBadge) {
        sourceBadge.textContent = hasCustomImg ? 'Custom Image Uploaded' : 'Default Home Wallpaper';
        sourceBadge.className = hasCustomImg ? 'badge bg-primary-subtle text-primary border' : 'badge bg-secondary-subtle text-secondary border';
    }

    const linkWrapper = document.getElementById('modalDetailLinkWrapper');
    if (linkWrapper) {
        if (slide.linkUrl) {
            linkWrapper.innerHTML = `<a href="${slide.linkUrl}" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold text-primary d-inline-flex align-items-center gap-1 text-truncate" style="max-width:320px;" title="${slide.linkUrl}">
                <i data-feather="external-link" style="width:12px;height:12px;flex-shrink:0;"></i> <span class="text-truncate">${slide.linkUrl}</span>
            </a>`;
        } else {
            linkWrapper.innerHTML = `<span class="text-muted small">None (Display banner only)</span>`;
        }
    }

    // Slide Pills Selector
    const pillsContainer = document.getElementById('modalSlidePills');
    if (pillsContainer) {
        pillsContainer.innerHTML = '';
        slidesData.forEach((s, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm ${idx === currentModalSlideIdx ? 'btn-primary fw-bold' : 'btn-outline-secondary'}`;
            btn.textContent = `#${idx + 1} ${s.title ? '(' + s.title.slice(0, 16) + '...)' : ''}`;
            btn.onclick = () => {
                currentModalSlideIdx = idx;
                renderRealModalContent();
            };
            pillsContainer.appendChild(btn);
        });
    }

    // Edit button in modal: Closes preview modal and opens the edit modal for this slide
    const editBtn = document.getElementById('modalEditSlideBtn');
    if (editBtn) {
        editBtn.onclick = () => {
            const previewModal = bootstrap.Modal.getInstance(document.getElementById('realPreviewModal'));
            if (previewModal) previewModal.hide();
            setTimeout(() => {
                const editModalEl = document.getElementById(`editSlideModal-${slideId}`);
                if (editModalEl) {
                    bootstrap.Modal.getOrCreateInstance(editModalEl).show();
                }
            }, 350);
        };
    }

    // Toggle button in modal: submits toggle form
    const toggleBtn = document.getElementById('modalToggleSlideBtn');
    if (toggleBtn) {
        toggleBtn.innerHTML = isEnabled ?
            '<i data-feather="pause" class="me-1" style="width:14px;height:14px"></i> Set Inactive' :
            '<i data-feather="play" class="me-1" style="width:14px;height:14px"></i> Set Active';
        toggleBtn.className = isEnabled ? 'btn btn-outline-warning fw-bold' : 'btn btn-outline-success fw-bold';
        toggleBtn.onclick = () => {
            const toggleForm = document.querySelector(`#slide-row-${slideId} form[action*="toggle"]`);
            if (toggleForm) toggleForm.submit();
        };
    }

    if (window.feather) feather.replace();
}

const realPreviewModalEl = document.getElementById('realPreviewModal');
if (realPreviewModalEl) {
    realPreviewModalEl.addEventListener('hidden.bs.modal', () => {
        clearInterval(realPhoneClockInterval);
    });
}

// ==========================================
// DATATABLE SEARCH & FILTER LOGIC
// ==========================================
const searchInput = document.getElementById('slideSearchInput');
const statusFilter = document.getElementById('statusFilter');
const imageTypeFilter = document.getElementById('imageTypeFilter');
const resetFiltersBtn = document.getElementById('resetFiltersBtn');
const filteredCountSpan = document.getElementById('filteredCount');
const noFilterMatchRow = document.getElementById('no-filter-match-row');

function filterTableRows() {
    const query = (searchInput.value || '').trim().toLowerCase();
    const statusVal = statusFilter.value;
    const imgTypeVal = imageTypeFilter.value;

    const rows = document.querySelectorAll('.slide-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const title = row.dataset.title || '';
        const link = row.dataset.link || '';
        const id = row.dataset.id || '';
        const status = row.dataset.status;
        const imgType = row.dataset.imageType;

        const matchesQuery = !query || title.includes(query) || link.includes(query) || id.includes(query);
        const matchesStatus = (statusVal === 'all') || (status === statusVal);
        const matchesImgType = (imgTypeVal === 'all') || (imgType === imgTypeVal);

        if (matchesQuery && matchesStatus && matchesImgType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (filteredCountSpan) filteredCountSpan.textContent = visibleCount;

    if (noFilterMatchRow) {
        noFilterMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
}

if (searchInput) searchInput.addEventListener('input', filterTableRows);
if (statusFilter) statusFilter.addEventListener('change', filterTableRows);
if (imageTypeFilter) imageTypeFilter.addEventListener('change', filterTableRows);

if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        if (imageTypeFilter) imageTypeFilter.value = 'all';
        filterTableRows();
    });
}

// ==========================================
// MODAL LIVE PREVIEWS (ADD & EDIT)
// ==========================================
function updateAddModalPreview() {
    const titleVal = (document.getElementById('add_title').value || '').trim();
    const urlVal = (document.getElementById('add_image_url').value || '').trim();
    const imgElement = document.getElementById('addPreviewImg');
    const titleElement = document.getElementById('addPreviewTitle');
    const badgeElement = document.getElementById('addPreviewBadge');
    const fileInput = document.getElementById('add_image');

    if (titleVal) {
        titleElement.textContent = titleVal;
        titleElement.hidden = false;
    } else {
        titleElement.hidden = true;
    }

    if (!fileInput.files.length) {
        if (urlVal) {
            imgElement.src = urlVal;
            badgeElement.textContent = 'Using Image URL';
            badgeElement.className = 'badge bg-primary-subtle text-primary border mt-2';
        } else {
            imgElement.src = imgElement.dataset.fallback;
            badgeElement.textContent = 'Using Home Wallpaper';
            badgeElement.className = 'badge bg-secondary-subtle text-secondary border mt-2';
        }
    }
}

function previewAddFile(input) {
    const imgElement = document.getElementById('addPreviewImg');
    const badgeElement = document.getElementById('addPreviewBadge');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imgElement.src = e.target.result;
            badgeElement.textContent = 'Using Uploaded File';
            badgeElement.className = 'badge bg-success-subtle text-success border mt-2';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        updateAddModalPreview();
    }
}

function updateEditModalPreview(slideId) {
    const titleInput = document.getElementById(`edit_title_${slideId}`);
    const urlInput = document.getElementById(`edit_image_url_${slideId}`);
    const imgElement = document.getElementById(`editPreviewImg_${slideId}`);
    const titleElement = document.getElementById(`editPreviewTitle_${slideId}`);
    const badgeElement = document.getElementById(`editPreviewBadge_${slideId}`);
    const fileInput = document.getElementById(`edit_image_${slideId}`);
    const removeCheck = document.getElementById(`remove_image_${slideId}`);

    if (titleInput && titleElement) {
        const titleVal = titleInput.value.trim();
        if (titleVal) {
            titleElement.textContent = titleVal;
            titleElement.hidden = false;
        } else {
            titleElement.hidden = true;
        }
    }

    if (removeCheck && removeCheck.checked) {
        imgElement.src = imgElement.dataset.fallback;
        badgeElement.textContent = 'Reverting to Home Wallpaper';
        badgeElement.className = 'badge bg-danger-subtle text-danger border mt-2';
        return;
    }

    if (fileInput && fileInput.files && fileInput.files.length) {
        return;
    }

    if (urlInput && urlInput.value.trim()) {
        imgElement.src = urlInput.value.trim();
        badgeElement.textContent = 'Using Image URL';
        badgeElement.className = 'badge bg-primary-subtle text-primary border mt-2';
    } else {
        imgElement.src = imgElement.dataset.initial || imgElement.dataset.fallback;
        badgeElement.textContent = imgElement.dataset.initial ? 'Custom Image Active' : 'Using Home Wallpaper';
        badgeElement.className = 'badge bg-secondary-subtle text-secondary border mt-2';
    }
}

function previewEditFile(input, slideId) {
    const imgElement = document.getElementById(`editPreviewImg_${slideId}`);
    const badgeElement = document.getElementById(`editPreviewBadge_${slideId}`);
    const removeCheck = document.getElementById(`remove_image_${slideId}`);

    if (removeCheck) removeCheck.checked = false;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imgElement.src = e.target.result;
            badgeElement.textContent = 'Using Uploaded File';
            badgeElement.className = 'badge bg-success-subtle text-success border mt-2';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        updateEditModalPreview(slideId);
    }
}

function toggleRemoveImage(slideId) {
    const removeCheck = document.getElementById(`remove_image_${slideId}`);
    const fileInput = document.getElementById(`edit_image_${slideId}`);
    const urlInput = document.getElementById(`edit_image_url_${slideId}`);

    if (removeCheck && removeCheck.checked) {
        if (fileInput) fileInput.value = '';
        if (urlInput) urlInput.value = '';
    }
    updateEditModalPreview(slideId);
}

// Initial initialization
document.addEventListener('DOMContentLoaded', () => {
    if (window.feather) feather.replace();
    renderPlayback();
});
</script>
@endsection
