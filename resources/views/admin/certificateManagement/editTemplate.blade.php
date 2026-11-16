@extends('layouts.master')

@section('title', __('index.edit_layout_model') . ' - ' . ($template->khmer_title ?: $template->title))

@section('action', __('index.edit_layout_model'))

@section('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@600;700;800&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,400&family=Great+Vibes&family=Kantumruy+Pro:wght@400;600;700&family=Moul&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
        .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
        .font-cormorant { font-family: 'Cormorant Garamond', serif; }
        .nav-pills .nav-link.active {
            background-color: #092653 !important;
            color: #ffffff !important;
            font-weight: 700;
        }
        .nav-pills .nav-link {
            color: #092653;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 16px;
        }
        .form-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #092653;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 14px;
            margin-top: 8px;
        }
        .color-preview-box {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            border: 2px solid #cbd5e1;
            padding: 2px;
            cursor: pointer;
        }
    </style>
@endsection

@section('main-content')
@php
    $tp = is_array($template->payload_json) ? $template->payload_json : [];
    $styling = is_array($template->styling_json) ? $template->styling_json : ($tp['styling'] ?? []);
    $seal = is_array($template->seal_json) ? $template->seal_json : ($tp['seal'] ?? []);
    $signatories = is_array($template->signatories_json) ? $template->signatories_json : ($tp['signatories'] ?? []);
    $sig1 = $signatories[0] ?? [];
    $sig2 = $signatories[1] ?? [];
@endphp

<section class="content">
    @include('admin.section.flash_message')

    <!-- Breadcrumb & Action Toolbar -->
    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">{{ __('index.certificate_management') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.templates') }}">{{ __('index.certificate_templates') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $template->khmer_title ?: $template->title }}</li>
        </ol>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.certificates.templates.preview', $template->id) }}" target="_blank" class="btn btn-outline-info btn-sm fw-bold">
                <i class="link-icon" data-feather="eye"></i> {{ __('index.view_layout_model') }}
            </a>
            <a href="{{ route('admin.certificates.studio', ['template_id' => $template->id]) }}" class="btn btn-warning btn-sm text-dark fw-bold">
                <i class="link-icon" data-feather="layout"></i> Studio 🎨
            </a>
            <a href="{{ route('admin.certificates.templates') }}" class="btn btn-secondary btn-sm">
                <i class="link-icon" data-feather="arrow-left"></i> ត្រឡប់ក្រោយ
            </a>
        </div>
    </nav>

    <!-- Header Hero Banner -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-radius: 12px;">
        <div class="card-body p-3.5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark font-monospace fw-bold">{{ $template->version_tag }}</span>
                        <span class="badge bg-info text-dark text-uppercase fw-bold">{{ $template->category }}</span>
                        @if($template->is_default)
                            <span class="badge bg-warning text-dark fw-bold">★ Default Model</span>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-0 text-white">
                        កែសម្រួលគំរូវិញ្ញាបនបត្រពេញលេញ៖ {{ $template->khmer_title ?: $template->title }} ({{ $template->template_code }})
                    </h5>
                    <small class="text-white-50">កែប្រែប្លង់ (Layout), មាតិកា (Content), អត្ថបទ (Text), ពណ៌ & រចនាប័ទ្ម (Style) និងហត្ថលេខី/ត្រា (Signatories & Seal)</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" form="editFullTemplateForm" class="btn btn-success fw-bold px-3 shadow">
                        <i class="link-icon" data-feather="check-circle"></i> រក្សាទុកការកែសម្រួលទាំងអស់ (Save All)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="editFullTemplateForm" method="POST" action="{{ route('admin.certificates.templates.update', $template->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left: Tabbed Form Editor (col-lg-7) -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom p-2">
                        <ul class="nav nav-pills nav-fill gap-1" id="templateEditTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-general-tab" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab">
                                    📋 ១. មូលដ្ឋាន & ប្លង់
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-content-tab" data-bs-toggle="pill" data-bs-target="#tab-content" type="button" role="tab">
                                    ✍️ ២. មាតិកា & អត្ថបទ
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-style-tab" data-bs-toggle="pill" data-bs-target="#tab-style" type="button" role="tab">
                                    🎨 ៣. រចនាប័ទ្ម & ពណ៌
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-signatories-tab" data-bs-toggle="pill" data-bs-target="#tab-signatories" type="button" role="tab">
                                    🖋️ ៤. ហត្ថលេខី & ត្រា
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content" id="templateEditTabContent">
                            
                            <!-- TAB 1: GENERAL & LAYOUT -->
                            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                                <div class="form-section-title">ព័ត៌មានអត្តសញ្ញាណគំរូ (Template Identity)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះគំរូជាភាសាខ្មែរ (Khmer Title) <span class="text-danger">*</span></label>
                                        <input type="text" name="khmer_title" id="khmer_title" class="form-control" value="{{ old('khmer_title', $template->khmer_title) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះគំរូជាភាសាអង់គ្លេស (English Title)</label>
                                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $template->title) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ប្រភេទទម្រង់ (Category) <span class="text-danger">*</span></label>
                                        <select name="category" id="category" class="form-select" required>
                                            <option value="leader" {{ $template->category === 'leader' ? 'selected' : '' }}>👑 មេដឹកនាំឆ្នើម (Leadership)</option>
                                            <option value="staff" {{ $template->category === 'staff' ? 'selected' : '' }}>⭐ បុគ្គលិកឆ្នើម (Staff Recognition)</option>
                                            <option value="service" {{ $template->category === 'service' ? 'selected' : '' }}>🤝 សេវាកម្មឆ្នើម (Customer Service)</option>
                                            <option value="course" {{ $template->category === 'course' ? 'selected' : '' }}>🎓 វគ្គសិក្សា CPD (Training Course)</option>
                                            <option value="custom" {{ $template->category === 'custom' ? 'selected' : '' }}>🎨 គំរូផ្ទាល់ខ្លួន (Custom Layout)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">រចនាបថប្លង់គោល (Layout Template Base) <span class="text-danger">*</span></label>
                                        <select name="layout_template" id="layout_template" class="form-select" required>
                                            <option value="executive-medal-ribbon" {{ $template->layout_template === 'executive-medal-ribbon' ? 'selected' : '' }}>🎖️ Executive Medal Ribbon (V5 - ខ្សែបូរមេដាយកិត្តិយស)</option>
                                            <option value="executive-imperial-sash" {{ $template->layout_template === 'executive-imperial-sash' ? 'selected' : '' }}>🏅 Executive Imperial Sash (V6 - សង្វារមាសរាជវង្ស)</option>
                                            <option value="executive-navy-ribbon" {{ $template->layout_template === 'executive-navy-ribbon' ? 'selected' : '' }}>🎗️ Executive Navy Ribbons (V3 - ខ្សែបូរជ្រុង Navy-Gold)</option>
                                            <option value="classic" {{ $template->layout_template === 'classic' ? 'selected' : '' }}>🏛️ Royal Heritage (V4 - បុរាណក្បាច់រាជធានី)</option>
                                            <option value="executive-verify-v2" {{ $template->layout_template === 'executive-verify-v2' ? 'selected' : '' }}>🆔 Staff ID Photo (V2 - រូបថតមូលបុគ្គលិក)</option>
                                            <option value="executive-verify" {{ $template->layout_template === 'executive-verify' ? 'selected' : '' }}>⭐ Verify Classic Gold (V1 - មាសបុរាណ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ស្លាកជំនាន់ (Version Tag)</label>
                                        <input type="text" name="version_tag" id="version_tag" class="form-control" value="{{ old('version_tag', $template->version_tag) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ស្បែកពណ៌ (Theme)</label>
                                        <select name="theme" id="theme" class="form-select">
                                            <option value="navy-gold" {{ $template->theme === 'navy-gold' ? 'selected' : '' }}>Navy & Gold (លំនាំដើម)</option>
                                            <option value="knea-yoeng-gold" {{ $template->theme === 'knea-yoeng-gold' ? 'selected' : '' }}>Knea Yoeng Royal Gold</option>
                                            <option value="tech-obsidian" {{ $template->theme === 'tech-obsidian' ? 'selected' : '' }}>Tech Obsidian (ងងឹត)</option>
                                            <option value="royal-burgundy" {{ $template->theme === 'royal-burgundy' ? 'selected' : '' }}>Royal Burgundy (ក្រហមឈាមជ្រូក)</option>
                                            <option value="emerald-prestige" {{ $template->theme === 'emerald-prestige' ? 'selected' : '' }}>Emerald Prestige (បៃតងត្បូង)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ភាសាបង្ហាញ (Language Mode)</label>
                                        <select name="language_mode" id="language_mode" class="form-select">
                                            <option value="bilingual" {{ $template->language_mode === 'bilingual' ? 'selected' : '' }}>Bilingual (ខ្មែរ + English)</option>
                                            <option value="khmer-only" {{ $template->language_mode === 'khmer-only' ? 'selected' : '' }}>ភាសាខ្មែរសុទ្ធ (Khmer Only)</option>
                                            <option value="english-only" {{ $template->language_mode === 'english-only' ? 'selected' : '' }}>English Only</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-section-title">ម៉ោងបណ្តុះបណ្តាល និងសុពលភាព (CPD & Compliance)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ម៉ោងបណ្តុះបណ្តាល (CPD Hours)</label>
                                        <input type="number" name="training_hours" id="training_hours" class="form-control" value="{{ old('training_hours', $template->training_hours) }}" min="0" max="999">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ជំនាញបណ្តុះបណ្តាល (Track)</label>
                                        <input type="text" name="hr_competency_track" id="hr_competency_track" class="form-control" value="{{ old('hr_competency_track', $template->hr_competency_track) }}" placeholder="e.g. Leadership Development">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ស្ថានភាពវិញ្ញាបនបត្រ</label>
                                        <input type="text" name="compliance_status" id="compliance_status" class="form-control" value="{{ old('compliance_status', $template->compliance_status) }}" placeholder="e.g. Verified & Accredited">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ការពិពណ៌នាជាភាសាខ្មែរ (Khmer Description)</label>
                                        <textarea name="khmer_description" id="khmer_description" rows="2" class="form-control">{{ old('khmer_description', $template->khmer_description) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ការពិពណ៌នាជាភាសាអង់គ្លេស (English Description)</label>
                                        <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $template->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex gap-4 p-3 bg-light rounded border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" {{ $template->is_default ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="is_default">★ កំណត់ជាគំរូលំនាំដើម (Default Layout Model)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ $template->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="is_active">បើកដំណើរការ (Active)</label>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: CONTENT & TEXT -->
                            <div class="tab-pane fade" id="tab-content" role="tabpanel">
                                <div class="form-section-title">ស្ថាប័នចេញវិញ្ញាបនបត្រ (Issuer & Organization)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះស្ថាប័នជាភាសាខ្មែរ</label>
                                        <input type="text" name="issuer_khmer_name" class="form-control" value="{{ old('issuer_khmer_name', $template->issuer_khmer_name ?: 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះស្ថាប័នជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="issuer_name" class="form-control" value="{{ old('issuer_name', $template->issuer_name ?: 'KNEAYERNG') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះផ្នែក / សាខា (Department / Sub-title)</label>
                                        <input type="text" name="department" class="form-control" value="{{ old('department', $template->department ?: 'PHONE SHOP') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">តំណភ្ជាប់ Logo (Logo URL)</label>
                                        <input type="text" name="logo_url" class="form-control" value="{{ old('logo_url', $template->logo_url) }}">
                                    </div>
                                </div>

                                <div class="form-section-title">បាលីជាតិ និងចំណងជើងធំ (Preambles & Certificate Titles)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">បាលីជាតិខ្មែរ (Khmer Preamble)</label>
                                        <input type="text" name="khmer_preamble" class="form-control" value="{{ old('khmer_preamble', $template->khmer_preamble ?: 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">បាលីជាតិអង់គ្លេស (English Preamble)</label>
                                        <input type="text" name="authority_preamble" class="form-control" value="{{ old('authority_preamble', $template->authority_preamble ?: 'KINGDOM OF CAMBODIA · NATION RELIGION KING') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ចំណងជើងវិញ្ញាបនបត្រខ្មែរ <span class="text-danger">*</span></label>
                                        <input type="text" name="khmer_certificate_title" class="form-control font-moul" style="font-size: 13px;" value="{{ old('khmer_certificate_title', $template->khmer_certificate_title ?: 'វិញ្ញាបនបត្រកោតសរសើរ និងផ្ដល់ការតែងតាំង') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ចំណងជើងវិញ្ញាបនបត្រអង់គ្លេស <span class="text-danger">*</span></label>
                                        <input type="text" name="certificate_title" class="form-control font-cinzel" style="font-size: 13px;" value="{{ old('certificate_title', $template->certificate_title ?: 'CERTIFICATE OF APPRECIATION') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">អត្ថបទបូស្លាបព្រាបមាស (Presentation Ribbon Banner Text)</label>
                                        <input type="text" name="banner_text" class="form-control font-cinzel fw-bold text-primary" value="{{ old('banner_text', $tp['bannerText'] ?? 'THIS CERTIFICATE IS PRESENTED TO') }}">
                                    </div>
                                </div>

                                <div class="form-section-title">សមិទ្ធផល និងសេចក្តីថ្លែងកោតសរសើរ (Achievement & Recognition Citations)</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះវគ្គសិក្សា / សមិទ្ធផលខ្មែរ</label>
                                        <input type="text" name="khmer_course_or_achievement_title" class="form-control" value="{{ old('khmer_course_or_achievement_title', $template->khmer_course_or_achievement_title) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះវគ្គសិក្សា / សមិទ្ធផលអង់គ្លេស</label>
                                        <input type="text" name="course_or_achievement_title" class="form-control" value="{{ old('course_or_achievement_title', $template->course_or_achievement_title) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">អត្ថបទបន្ទាប់ពីចំណងជើងខ្មែរ (Subtitle)</label>
                                        <input type="text" name="khmer_subtitle" class="form-control" value="{{ old('khmer_subtitle', $template->khmer_subtitle) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">អត្ថបទបន្ទាប់ពីចំណងជើងអង់គ្លេស (Subtitle)</label>
                                        <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $template->subtitle) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">សេចក្តីកោតសរសើរជាភាសាខ្មែរ (Khmer Citation)</label>
                                        <textarea name="khmer_citation" rows="2" class="form-control font-kantumruy">{{ old('khmer_citation', $template->khmer_citation) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">សេចក្តីកោតសរសើរជាភាសាអង់គ្លេស (English Citation)</label>
                                        <textarea name="citation" rows="2" class="form-control font-cormorant">{{ old('citation', $template->citation) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">ប្រយោគអបអរសាទរបញ្ចប់ (Closing Citation Sentence)</label>
                                        <input type="text" name="citation_closing" class="form-control font-cormorant" value="{{ old('citation_closing', $template->citation_closing ?: ($tp['citationClosing'] ?? 'Congratulations on this well-deserved achievement, and we wish you continued success in your new role.')) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: STYLE & APPEARANCE -->
                            <div class="tab-pane fade" id="tab-style" role="tabpanel">
                                <div class="form-section-title">ពណ៌គោល និងផ្ទៃខាងក្រោយ (Palette & Background)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌ខៀវគោល (Primary Color)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="color-preview-box" id="picker_primary_color" value="{{ $styling['primaryColor'] ?? '#092653' }}" onchange="document.getElementById('primary_color').value=this.value">
                                            <input type="text" name="primary_color" id="primary_color" class="form-control font-monospace" value="{{ old('primary_color', $styling['primaryColor'] ?? '#092653') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌មាសរំលេច (Accent Gold)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="color-preview-box" id="picker_accent_color" value="{{ $styling['accentColor'] ?? '#D4A32C' }}" onchange="document.getElementById('accent_color').value=this.value">
                                            <input type="text" name="accent_color" id="accent_color" class="form-control font-monospace" value="{{ old('accent_color', $styling['accentColor'] ?? '#D4A32C') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌ផ្ទៃក្រដាស (Background Tint)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="color-preview-box" id="picker_bg_color" value="{{ $styling['bgColor'] ?? '#FFFDF8' }}" onchange="document.getElementById('bg_color').value=this.value">
                                            <input type="text" name="bg_color" id="bg_color" class="form-control font-monospace" value="{{ old('bg_color', $styling['bgColor'] ?? '#FFFDF8') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section-title">រចនាប័ទ្មស៊ុម និងក្បាច់ផ្កា (Borders & Dividers)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">រចនាប័ទ្មស៊ុម (Border Style)</label>
                                        <select name="border_style" class="form-select">
                                            <option value="navy-gold-ribbon" {{ ($styling['borderStyle'] ?? 'navy-gold-ribbon') === 'navy-gold-ribbon' ? 'selected' : '' }}>Navy & Gold Ribbon + 4 Corner Filigrees (ប្រណិតលំនាំដើម)</option>
                                            <option value="double-gold-rails" {{ ($styling['borderStyle'] ?? '') === 'double-gold-rails' ? 'selected' : '' }}>Double Gold Metallic Rails (ស៊ុមមាសទ្វេ)</option>
                                            <option value="classic-ornate" {{ ($styling['borderStyle'] ?? '') === 'classic-ornate' ? 'selected' : '' }}>Classic Royal Ornate (ក្បាច់បុរាណ)</option>
                                            <option value="modern-minimal" {{ ($styling['borderStyle'] ?? '') === 'modern-minimal' ? 'selected' : '' }}>Modern Clean Minimal (សាមញ្ញទាន់សម័យ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ក្បាច់បន្ទាត់ផ្កាកណ្តាល (Divider Ornament)</label>
                                        <select name="divider_ornament" class="form-select">
                                            <option value="baroque-fleur" {{ ($styling['dividerOrnament'] ?? 'baroque-fleur') === 'baroque-fleur' ? 'selected' : '' }}>Baroque Fleur-de-lis (ផ្កាភ្ញីទេស)</option>
                                            <option value="diamond-stars" {{ ($styling['dividerOrnament'] ?? '') === 'diamond-stars' ? 'selected' : '' }}>Diamond Stars (គ្រាប់ពេជ្រ & ផ្កាយ)</option>
                                            <option value="minimal-line" {{ ($styling['dividerOrnament'] ?? '') === 'minimal-line' ? 'selected' : '' }}>Minimal Clean Line (បន្ទាត់សាមញ្ញ)</option>
                                            <option value="none" {{ ($styling['dividerOrnament'] ?? '') === 'none' ? 'selected' : '' }}>None (គ្មាន)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">ប្រវែងបន្ទាត់ (px)</label>
                                        <input type="number" name="divider_width" class="form-control" value="{{ $styling['dividerWidth'] ?? 240 }}" min="80" max="600">
                                    </div>
                                </div>

                                <div class="form-section-title">ប្រអប់រូបថតបុគ្គលិក & Watermark (Photo & Watermark)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="show_recipient_photo" value="1" id="show_recipient_photo" {{ ($tp['showRecipientPhoto'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="show_recipient_photo">បង្ហាញប្រអប់រូបថតបុគ្គលិក (Photo Box)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ទម្រង់ប្រអប់រូបថត (Photo Shape)</label>
                                        <select name="recipient_photo_shape" class="form-select">
                                            <option value="rounded-portrait" {{ ($tp['recipientPhotoShape'] ?? 'rounded-portrait') === 'rounded-portrait' ? 'selected' : '' }}>Rounded Portrait (ចតុកោណកែងបញ្ឈរ)</option>
                                            <option value="circle" {{ ($tp['recipientPhotoShape'] ?? '') === 'circle' ? 'selected' : '' }}>Circle (រង្វង់មូល)</option>
                                            <option value="square" {{ ($tp['recipientPhotoShape'] ?? '') === 'square' ? 'selected' : '' }}>Square (ការេ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="show_watermark" value="1" id="show_watermark" {{ ($styling['showWatermark'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="show_watermark">Watermark</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">ភាពព្រាល (Opacity)</label>
                                        <input type="number" step="0.01" name="watermark_opacity" class="form-control" value="{{ $styling['watermarkOpacity'] ?? 0.10 }}" min="0" max="0.5">
                                    </div>
                                </div>

                                <div class="form-section-title">ពុម្ពអក្សរ (Typography Fonts)</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពុម្ពអក្សរអង់គ្លេសធំ (Display Font)</label>
                                        <select name="font_english_title" class="form-select">
                                            <option value="cinzel" {{ ($styling['fontDisplay'] ?? 'playfair') === 'cinzel' ? 'selected' : '' }}>Cinzel (រាជវង្សអង់គ្លេស)</option>
                                            <option value="playfair" {{ ($styling['fontDisplay'] ?? 'playfair') === 'playfair' ? 'selected' : '' }}>Playfair Display (បុរាណប្រណិត)</option>
                                            <option value="cormorant" {{ ($styling['fontDisplay'] ?? '') === 'cormorant' ? 'selected' : '' }}>Cormorant Garamond</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពុម្ពអក្សរអត្ថបទ (Body Font)</label>
                                        <select name="font_body" class="form-select">
                                            <option value="cormorant" {{ ($styling['fontBody'] ?? 'cormorant') === 'cormorant' ? 'selected' : '' }}>Cormorant Garamond (Italic Serifs)</option>
                                            <option value="plus-jakarta-sans" {{ ($styling['fontBody'] ?? '') === 'plus-jakarta-sans' ? 'selected' : '' }}>Plus Jakarta Sans (ទំនើបស្អាត)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពុម្ពអក្សរហត្ថលេខា (Signature)</label>
                                        <select name="font_signature" class="form-select">
                                            <option value="alex-brush" {{ ($styling['fontSignature'] ?? 'alex-brush') === 'alex-brush' ? 'selected' : '' }}>Alex Brush (រលូនប្រណិត)</option>
                                            <option value="great-vibes" {{ ($styling['fontSignature'] ?? '') === 'great-vibes' ? 'selected' : '' }}>Great Vibes (ទន់ភ្លន់)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: SIGNATORIES & SEAL -->
                            <div class="tab-pane fade" id="tab-signatories" role="tabpanel">
                                <!-- Signatory 1 -->
                                <div class="form-section-title">ហត្ថលេខីទី១ - ខាងឆ្វេង (Signatory #1 - Left)</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig1_name" class="form-control" value="{{ old('sig1_name', $sig1['name'] ?? 'CHHOEUN REACH') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig1_khmer_name" class="form-control" value="{{ old('sig1_khmer_name', $sig1['khmerName'] ?? 'ឈឿន រាជ') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">រចនាប័ទ្មហត្ថលេខា</label>
                                        <select name="sig1_style" class="form-select">
                                            <option value="alex-brush" {{ ($sig1['signatureStyle'] ?? 'alex-brush') === 'alex-brush' ? 'selected' : '' }}>Alex Brush (Cursive Script)</option>
                                            <option value="great-vibes" {{ ($sig1['signatureStyle'] ?? '') === 'great-vibes' ? 'selected' : '' }}>Great Vibes (Calligraphy)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig1_role" class="form-control" value="{{ old('sig1_role', $sig1['role'] ?? 'IT Manager / Branch Manager') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig1_khmer_role" class="form-control" value="{{ old('sig1_khmer_role', $sig1['khmerRole'] ?? 'ប្រធានផ្នែកព័ត៌មានវិទ្យា / ប្រធានសាខា') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ផ្នែក / ស្ថាប័ន (Organization / Dept)</label>
                                        <input type="text" name="sig1_organization" class="form-control" value="{{ old('sig1_organization', $sig1['organization'] ?? 'Admin • HR • System • Stock') }}">
                                    </div>
                                </div>

                                <!-- Signatory 2 -->
                                <div class="form-section-title">ហត្ថលេខីទី២ - ខាងស្តាំ (Signatory #2 - Right)</div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig2_name" class="form-control" value="{{ old('sig2_name', $sig2['name'] ?? 'SOPHEA MENG') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig2_khmer_name" class="form-control" value="{{ old('sig2_khmer_name', $sig2['khmerName'] ?? 'ម៉េង សោភា') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">រចនាប័ទ្មហត្ថលេខា</label>
                                        <select name="sig2_style" class="form-select">
                                            <option value="great-vibes" {{ ($sig2['signatureStyle'] ?? 'great-vibes') === 'great-vibes' ? 'selected' : '' }}>Great Vibes (Calligraphy)</option>
                                            <option value="alex-brush" {{ ($sig2['signatureStyle'] ?? '') === 'alex-brush' ? 'selected' : '' }}>Alex Brush (Cursive Script)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig2_role" class="form-control" value="{{ old('sig2_role', $sig2['role'] ?? 'HR Manager / HR Supervisor') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig2_khmer_role" class="form-control" value="{{ old('sig2_khmer_role', $sig2['khmerRole'] ?? 'ប្រធានផ្នែកធនធានមនុស្ស') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ផ្នែក / ស្ថាប័ន (Organization / Dept)</label>
                                        <input type="text" name="sig2_organization" class="form-control" value="{{ old('sig2_organization', $sig2['organization'] ?? 'Human Resource Department') }}">
                                    </div>
                                </div>

                                <!-- Official Seal -->
                                <div class="form-section-title">ត្រាមាសផ្លូវការ (Official Center Seal)</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">រចនាប័ទ្មត្រា (Seal Style)</label>
                                        <select name="seal_style" class="form-select">
                                            <option value="crown-laurel" {{ ($seal['style'] ?? 'crown-laurel') === 'crown-laurel' ? 'selected' : '' }}>3D Embossed Gold Seal with Crown & Laurels (ត្រាមាស 3D)</option>
                                            <option value="classic-round" {{ ($seal['style'] ?? '') === 'classic-round' ? 'selected' : '' }}>Classic Round Seal (រង្វង់បុរាណ)</option>
                                            <option value="crimson-wax" {{ ($seal['style'] ?? '') === 'crimson-wax' ? 'selected' : '' }}>Crimson Wax (ជ័រក្រហម)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌កន្ទុយបូត្រា (Ribbon Tails Color)</label>
                                        <select name="seal_ribbon_color" class="form-select">
                                            <option value="navy" {{ ($seal['ribbonColor'] ?? 'navy') === 'navy' ? 'selected' : '' }}>Navy Blue (ខៀវចាស់កាត់មាស)</option>
                                            <option value="gold" {{ ($seal['ribbonColor'] ?? '') === 'gold' ? 'selected' : '' }}>Gold (មាសសុទ្ធ)</option>
                                            <option value="none" {{ ($seal['ribbonColor'] ?? '') === 'none' ? 'selected' : '' }}>None (គ្មានកន្ទុយបូ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">អត្ថបទត្រាជួរលើ (Primary Text)</label>
                                        <input type="text" name="seal_primary_text" class="form-control" value="{{ old('seal_primary_text', $seal['primaryText'] ?? 'KNEAYERNG') }}">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Footer Action Toolbar -->
                    <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.certificates.templates') }}" class="btn btn-secondary btn-sm">
                                បោះបង់ (Cancel)
                            </a>
                            <a href="{{ route('admin.certificates.studio', ['template_id' => $template->id]) }}" class="btn btn-outline-warning text-dark btn-sm fw-bold">
                                <i class="link-icon" data-feather="layout"></i> បើកកែផ្ទាល់ភ្នែកក្នុង Studio 🎨
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="link-icon" data-feather="check"></i> រក្សាទុកការកែសម្រួលគំរូ (Save Layout Model)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Real-time Live A4 Preview Sticky Card (col-lg-5) -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 sticky-top" style="top: 24px; border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-dark text-white py-2.5 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark font-monospace fw-bold">A4 PREVIEW</span>
                            <small class="text-white-50">ការមើលជាមុនផ្ទាល់ភ្នែក</small>
                        </div>
                        <div class="d-flex gap-1.5">
                            <button type="button" class="btn btn-xs btn-outline-light" onclick="refreshEditPreview()" title="ផ្ទុកគំរូឡើងវិញ">
                                🔄 Refresh
                            </button>
                            <a href="{{ route('admin.certificates.templates.preview', $template->id) }}" target="_blank" class="btn btn-xs btn-warning text-dark fw-bold">
                                ↗ ពេញអេក្រង់
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0" style="background: radial-gradient(circle at 50% 50%, #101c38 0%, #060e22 100%); min-height: 480px; height: 68vh;">
                        <iframe id="editLivePreviewFrame" 
                                src="{{ route('admin.certificates.batchPreview', ['template_id' => $template->id]) }}" 
                                class="w-100 h-100 border-0" 
                                style="background: transparent;"></iframe>
                    </div>
                    <div class="card-footer bg-light py-2 px-3 text-center small text-muted font-kantumruy">
                        💡 ចុច <strong>រក្សាទុកការកែសម្រួលគំរូ</strong> ដើម្បី Save ទិន្នន័យទាំងអស់ចូលក្នុងប្រព័ន្ធ។
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('scripts')
<script>
    function refreshEditPreview() {
        var frame = document.getElementById('editLivePreviewFrame');
        if (frame) {
            frame.src = @json(route('admin.certificates.batchPreview')) + '?template_id={{ $template->id }}&_ts=' + Date.now();
        }
    }
</script>
@endsection
