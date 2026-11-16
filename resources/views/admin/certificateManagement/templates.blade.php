@extends('layouts.master')

@section('title', __('index.certificate_templates'))

@section('action', __('index.certificate_templates'))

@section('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@600;700;800&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,400&family=Great+Vibes&family=Kantumruy+Pro:wght@400;600;700&family=Moul&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
        .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
        .font-cormorant { font-family: 'Cormorant Garamond', serif; }
        .template-card {
            transition: all 0.25s ease-in-out;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.15) !important;
            border-color: #cbd5e1;
        }
        .template-canvas-thumb {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .template-canvas-thumb:hover .template-thumb-overlay {
            opacity: 1 !important;
        }
        .template-canvas-thumb .logo-thumb-ornament {
            width: 112px;
            height: 54px;
            overflow: visible;
        }
    </style>
@endsection

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">{{ __('index.certificate_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.certificate_templates') }}</li>
        </ol>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.add_layout_model') }}
            </button>
            <a href="{{ route('admin.certificates.studio') }}" class="btn btn-primary btn-sm">
                <i class="link-icon" data-feather="layout"></i> {{ __('index.certificate_studio') }}
            </a>
            <a href="{{ route('admin.certificates.create') }}" class="btn btn-outline-success btn-sm">
                <i class="link-icon" data-feather="plus"></i> {{ __('index.issue_certificate') }}
            </a>
            <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">
                <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.certificate_directory') }}
            </a>
        </div>
    </nav>

    <!-- Header Description Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-radius: 12px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold mb-2">CertiCraft Studio — Layout Model Center</span>
                    <h4 class="fw-bold mb-1 text-white">{{ __('index.certificate_templates') }} (Certificate Layout Models)</h4>
                    <p class="mb-0 text-white-50 small">
                        គ្រប់គ្រង <strong>វិញ្ញាបនបត្រ គំរូ (Layout Models)</strong> បន្ថែមថ្មី (Add), កែសម្រួល (Update), និងលុប (Delete) ដោយងាយស្រួល ព្រមទាំងអាចបើកកែផ្ទាល់ភ្នែកក្នុង CertiCraft Studio 🎨 គ្រប់ពេលវេលា។
                    </p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
                    <button type="button" class="btn btn-light text-primary fw-bold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                        <i class="link-icon" data-feather="plus-circle"></i> + {{ __('index.add_layout_model') }}
                    </button>
                    <a href="{{ route('admin.certificates.studio') }}" class="btn btn-warning text-dark fw-bold px-3 py-2 shadow">
                        <i class="link-icon" data-feather="edit-3"></i> {{ __('index.certificate_studio') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill flex-wrap gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ empty($category) || $category === 'all' ? 'active bg-primary text-white' : 'text-muted' }}" 
                       href="{{ route('admin.certificates.templates') }}">
                        ទាំងអស់ (All) <span class="badge bg-light text-dark ms-1">{{ $counts['all'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $category === 'leader' ? 'active bg-primary text-white' : 'text-muted' }}" 
                       href="{{ route('admin.certificates.templates', ['category' => 'leader']) }}">
                        👑 មេដឹកនាំឆ្នើម (Leadership) <span class="badge bg-light text-dark ms-1">{{ $counts['leader'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $category === 'staff' ? 'active bg-primary text-white' : 'text-muted' }}" 
                       href="{{ route('admin.certificates.templates', ['category' => 'staff']) }}">
                        ⭐ បុគ្គលិកឆ្នើម (Staff) <span class="badge bg-light text-dark ms-1">{{ $counts['staff'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $category === 'service' ? 'active bg-primary text-white' : 'text-muted' }}" 
                       href="{{ route('admin.certificates.templates', ['category' => 'service']) }}">
                        🤝 សេវាកម្មឆ្នើម (Service) <span class="badge bg-light text-dark ms-1">{{ $counts['service'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $category === 'course' ? 'active bg-primary text-white' : 'text-muted' }}" 
                       href="{{ route('admin.certificates.templates', ['category' => 'course']) }}">
                        🎓 វគ្គសិក្សា CPD (Training) <span class="badge bg-light text-dark ms-1">{{ $counts['course'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $category === 'custom' ? 'active bg-primary text-white' : 'text-muted' }}" 
                       href="{{ route('admin.certificates.templates', ['category' => 'custom']) }}">
                        🎨 គំរូផ្ទាល់ខ្លួន (Custom) <span class="badge bg-light text-dark ms-1">{{ $counts['custom'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Visual Certificate Image Grid -->
    <div class="row g-4">
        @forelse($templates as $tmpl)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm template-card {{ $tmpl->is_default ? 'border-warning border-2' : '' }}">
                    
                    <!-- Card Header / Meta Top Banner -->
                    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-white border-bottom">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge bg-dark text-warning font-monospace fw-bold px-2 py-0.5" style="font-size: 11px;">
                                {{ $tmpl->version_tag }}
                            </span>
                            <span class="badge 
                                @if($tmpl->category === 'leader') bg-warning text-dark
                                @elseif($tmpl->category === 'service') bg-info text-dark
                                @elseif($tmpl->category === 'course') bg-success text-white
                                @elseif($tmpl->category === 'staff') bg-primary text-white
                                @else bg-secondary text-white @endif" style="font-size: 10px;">
                                {{ strtoupper($tmpl->category) }}
                            </span>
                        </div>
                        @if($tmpl->is_default)
                            <span class="badge bg-warning text-dark fw-bold" style="font-size: 10px;">
                                ★ គំរូលំនាំដើម (Default)
                            </span>
                        @else
                            <form action="{{ route('admin.certificates.templates.setDefault', $tmpl->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link btn-xs p-0 text-muted text-decoration-none" title="កំណត់ជាគំរូលំនាំដើម">
                                    <small>កំណត់ជា Default</small>
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Visual Certificate Image Canvas -->
                    @include('admin.certificateManagement.partials.template-card-preview', ['tmpl' => $tmpl])

                    <!-- Card Body -->
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0 text-primary">{{ $tmpl->khmer_title ?: $tmpl->title }}</h6>
                                <span class="badge bg-light text-secondary font-monospace" style="font-size: 9.5px;">
                                    {{ $tmpl->layout_template }}
                                </span>
                            </div>
                            <div class="small text-muted mb-2 font-monospace" style="font-size: 11px;">{{ $tmpl->title }} ({{ $tmpl->template_code }})</div>

                            <p class="text-secondary small mb-3" style="min-height: 38px; line-height: 1.4;">
                                {{ $tmpl->khmer_description ?: $tmpl->description }}
                            </p>

                            <div class="p-2 rounded bg-light small mb-3 border">
                                <div class="text-muted d-flex justify-content-between align-items-center" style="font-size: 11px;">
                                    <span><strong>ស្ថាប័ន:</strong> {{ $tmpl->issuer_name }}</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">🕒 {{ $tmpl->training_hours }}h Digital HR</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Buttons (Add/Update/Delete Enabled) -->
                        <div class="d-grid gap-2 pt-2 border-top">
                            <!-- Management Action Row -->
                            <div class="d-flex gap-1.5 flex-wrap">
                                <!-- View Full A4 -->
                                <button type="button" class="btn btn-outline-info btn-sm fw-bold flex-grow-1" 
                                        onclick="openFullPreviewModal({{ $tmpl->id }})" title="មើលទម្រង់ពេញ A4 (Full View Like A4)">
                                    <i class="link-icon" data-feather="eye"></i> {{ __('index.view_layout_model') }}
                                </button>

                                <!-- Open real settings in CertiCraft Studio (content-only preview) -->
                                <a href="{{ route('admin.certificates.studio', ['template_id' => $tmpl->id, 'embed' => 1]) }}"
                                   target="_blank" class="btn btn-outline-secondary btn-sm px-2" title="{{ __('index.open_in_new_tab') }} — Studio (content only)">
                                    <i class="link-icon" data-feather="external-link"></i>
                                </a>

                                <!-- Full Edit in CertiCraft Studio -->
                                <a href="{{ route('admin.certificates.studio', ['template_id' => $tmpl->id]) }}" class="btn btn-outline-warning text-dark btn-sm fw-bold"
                                   title="{{ __('index.edit_layout_model') }} — CertiCraft Studio 🎨">
                                    <i class="link-icon" data-feather="edit-3"></i> Edit in Studio 🎨
                                </a>
                                
                                <!-- Reset to Default -->
                                <form action="{{ route('admin.certificates.templates.reset', $tmpl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់កំណត់គំរូ [{{ $tmpl->khmer_title ?: $tmpl->title }}] ឡើងវិញទៅលំនាំដើមមែនទេ?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm px-2" title="{{ __('index.reset_to_default') }}">
                                        <i class="link-icon" data-feather="rotate-ccw"></i>
                                    </button>
                                </form>

                                <!-- Delete Template -->
                                <form action="{{ route('admin.certificates.templates.destroy', $tmpl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបគំរូ [{{ $tmpl->khmer_title ?: $tmpl->title }}] នេះមែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="{{ __('index.delete_layout_model') }}">
                                        <i class="link-icon" data-feather="trash-2"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Issue Certificate Action Row -->
                            <div class="btn-group btn-group-sm w-100">
                                <a href="{{ route('admin.certificates.create', ['template_id' => $tmpl->id]) }}" 
                                   class="btn btn-primary" title="ចេញវិញ្ញាបនបត្រជូនបុគ្គលិក">
                                    <i class="link-icon" data-feather="plus"></i> {{ __('index.issue_certificate') }}
                                </a>
                                <a href="{{ route('admin.certificates.batch', ['template_id' => $tmpl->id]) }}" 
                                   class="btn btn-outline-secondary" title="ចេញជាក្រុម">
                                    <i class="link-icon" data-feather="users"></i> {{ __('index.batch_issue_certificate') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i data-feather="layout" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                <h5 class="text-muted">មិនមានគំរូ layout ក្នុងជម្រើសនេះទេ</h5>
                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                    <i class="link-icon" data-feather="plus-circle"></i> + {{ __('index.add_layout_model') }}
                </button>
            </div>
        @endforelse
    </div>

    <!-- Modal 1: Create New Layout Model -->
    <div class="modal fade" id="createTemplateModal" tabindex="-1" aria-labelledby="createTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="link-icon" data-feather="plus-circle"></i>
                        <h5 class="modal-title fw-bold text-white mb-0" id="createTemplateModalLabel">{{ __('index.add_layout_model') }}</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.certificates.templates.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ឈ្មោះគំរូជាភាសាខ្មែរ (Khmer Title) <span class="text-danger">*</span></label>
                                <input type="text" name="khmer_title" class="form-control" required placeholder="ឧទាហរណ៍៖ មេដឹកនាំឆ្នើម ឬ គំរូពិសេស">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ឈ្មោះគំរូជាភាសាអង់គ្លេស (English Title)</label>
                                <input type="text" name="title" class="form-control" placeholder="ឧទាហរណ៍៖ Executive Leadership Model">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ប្រភេទទម្រង់ (Category) <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="leader">👑 មេដឹកនាំឆ្នើម (Leadership)</option>
                                    <option value="staff">⭐ បុគ្គលិកឆ្នើម (Staff Recognition)</option>
                                    <option value="service">🤝 សេវាកម្មឆ្នើម (Customer Service)</option>
                                    <option value="course">🎓 វគ្គសិក្សា CPD (Training Course)</option>
                                    <option value="custom" selected>🎨 គំរូផ្ទាល់ខ្លួន (Custom Layout)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">រចនាបថប្លង់គោល (Layout Template Base) <span class="text-danger">*</span></label>
                                <select name="layout_template" class="form-select" required>
                                    <option value="executive-medal-ribbon" selected>🎖️ Executive Medal Ribbon (V5 - ខ្សែបូរមេដាយកិត្តិយស)</option>
                                    <option value="executive-imperial-sash">🏅 Executive Imperial Sash (V6 - សង្វារមាសរាជវង្ស)</option>
                                    <option value="executive-navy-ribbon">🎗️ Executive Navy Ribbons (V3 - ខ្សែបូរជ្រុង Navy-Gold)</option>
                                    <option value="classic">🏛️ Royal Heritage (V4 - បុរាណក្បាច់រាជធានី)</option>
                                    <option value="executive-verify-v2">🆔 Staff ID Photo (V2 - រូបថតមូលបុគ្គលិក)</option>
                                    <option value="executive-verify">⭐ Verify Classic Gold (V1 - មាសបុរាណ)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ចំណងជើងវិញ្ញាបនបត្រខ្មែរ (Khmer Certificate Title)</label>
                                <input type="text" name="khmer_certificate_title" class="form-control" value="វិញ្ញាបនបត្រកោតសរសើរ និងផ្តល់ការតែងតាំង">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ចំណងជើងវិញ្ញាបនបត្រអង់គ្លេស (Certificate Title)</label>
                                <input type="text" name="certificate_title" class="form-control" value="CERTIFICATE OF APPRECIATION">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ម៉ោងបណ្តុះបណ្តាល (CPD Digital HR Hours)</label>
                                <input type="number" name="training_hours" class="form-control" value="45" min="0" max="999">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ស្លាកជំនាន់ (Version Tag)</label>
                                <input type="text" name="version_tag" class="form-control" value="CUSTOM" placeholder="ឧទាហរណ៍៖ V5, V6, CUSTOM">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">ការពិពណ៌នាជាភាសាខ្មែរ (Khmer Description)</label>
                                <textarea name="khmer_description" rows="2" class="form-control" placeholder="ពិពណ៌នាអំពីលក្ខណៈពិសេសនៃគំរូនេះ..."></textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="create_is_default">
                                    <label class="form-check-label fw-bold text-dark" for="create_is_default">
                                        ★ កំណត់ជាគំរូលំនាំដើម (Set as Default Layout Model)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-4 bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">
                            <i class="link-icon" data-feather="check"></i> រក្សាទុកគំរូថ្មី (Save Layout Model)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 2: Edit Layout Model (Full Feature Editor: Layout, Content, Style, Text, Signatories & Seal) -->
    <div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="link-icon" data-feather="edit-2"></i>
                        <h5 class="modal-title fw-bold text-white mb-0" id="editTemplateModalLabel">{{ __('index.edit_layout_model') }}</h5>
                        <span class="badge bg-warning text-dark font-monospace fw-bold ms-2" id="edit_modal_version_badge">V5</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="modalEditFullPageLink" class="btn btn-outline-light btn-sm fw-bold" target="_blank" title="បើកកែក្នុងទំព័រធំពេញលេញ">
                            <i class="link-icon" data-feather="external-link"></i> ↗ បើកទំព័រធំ (Full Page)
                        </a>
                        <a href="#" id="modalEditStudioLink" class="btn btn-warning btn-sm text-dark fw-bold" target="_blank" title="បើកកែក្នុង CertiCraft Studio">
                            <i class="link-icon" data-feather="layout"></i> Studio 🎨
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                
                <form id="editTemplateForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <!-- Tab Navigation Header -->
                    <div class="bg-light border-bottom p-2 px-3">
                        <ul class="nav nav-pills nav-fill gap-1" id="editModalTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-2 fw-bold" id="modal-tab-general-btn" data-bs-toggle="pill" data-bs-target="#modal-tab-general" type="button" role="tab">
                                    📋 ១. មូលដ្ឋាន & ប្លង់ (Layout)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-bold" id="modal-tab-content-btn" data-bs-toggle="pill" data-bs-target="#modal-tab-content" type="button" role="tab">
                                    ✍️ ២. មាតិកា & អត្ថបទ (Content)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-bold" id="modal-tab-style-btn" data-bs-toggle="pill" data-bs-target="#modal-tab-style" type="button" role="tab">
                                    🎨 ៣. រចនាប័ទ្ម & ពណ៌ (Style)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-bold" id="modal-tab-signatories-btn" data-bs-toggle="pill" data-bs-target="#modal-tab-signatories" type="button" role="tab">
                                    🖋️ ៤. ហត្ថលេខី & ត្រា (Signatories)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="modal-body p-4" style="max-height: 72vh; overflow-y: auto;">
                        <div class="tab-content" id="editModalTabContent">
                            
                            <!-- TAB 1: GENERAL & LAYOUT -->
                            <div class="tab-pane fade show active" id="modal-tab-general" role="tabpanel">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">📋 ព័ត៌មានអត្តសញ្ញាណគំរូ & រចនាបថគោល</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះគំរូជាភាសាខ្មែរ (Khmer Title) <span class="text-danger">*</span></label>
                                        <input type="text" name="khmer_title" id="edit_khmer_title" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះគំរូជាភាសាអង់គ្លេស (English Title)</label>
                                        <input type="text" name="title" id="edit_title" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ប្រភេទទម្រង់ (Category) <span class="text-danger">*</span></label>
                                        <select name="category" id="edit_category" class="form-select" required>
                                            <option value="leader">👑 មេដឹកនាំឆ្នើម (Leadership)</option>
                                            <option value="staff">⭐ បុគ្គលិកឆ្នើម (Staff Recognition)</option>
                                            <option value="service">🤝 សេវាកម្មឆ្នើម (Customer Service)</option>
                                            <option value="course">🎓 វគ្គសិក្សា CPD (Training Course)</option>
                                            <option value="custom">🎨 គំរូផ្ទាល់ខ្លួន (Custom Layout)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">រចនាបថប្លង់គោល (Layout Template Base) <span class="text-danger">*</span></label>
                                        <select name="layout_template" id="edit_layout_template" class="form-select" required>
                                            <option value="executive-medal-ribbon">🎖️ Executive Medal Ribbon (V5 - ខ្សែបូរមេដាយកិត្តិយស)</option>
                                            <option value="executive-imperial-sash">🏅 Executive Imperial Sash (V6 - សង្វារមាសរាជវង្ស)</option>
                                            <option value="executive-navy-ribbon">🎗️ Executive Navy Ribbons (V3 - ខ្សែបូរជ្រុង Navy-Gold)</option>
                                            <option value="classic">🏛️ Royal Heritage (V4 - បុរាណក្បាច់រាជធានី)</option>
                                            <option value="executive-verify-v2">🆔 Staff ID Photo (V2 - រូបថតមូលបុគ្គលិក)</option>
                                            <option value="executive-verify">⭐ Verify Classic Gold (V1 - មាសបុរាណ)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ស្លាកជំនាន់ (Version Tag)</label>
                                        <input type="text" name="version_tag" id="edit_version_tag" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ស្បែកពណ៌ (Theme)</label>
                                        <select name="theme" id="edit_theme" class="form-select">
                                            <option value="navy-gold">Navy & Gold (លំនាំដើម)</option>
                                            <option value="knea-yoeng-gold">Knea Yoeng Royal Gold</option>
                                            <option value="tech-obsidian">Tech Obsidian (ងងឹត)</option>
                                            <option value="royal-burgundy">Royal Burgundy (ក្រហមឈាមជ្រូក)</option>
                                            <option value="emerald-prestige">Emerald Prestige (បៃតងត្បូង)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ភាសាបង្ហាញ (Language Mode)</label>
                                        <select name="language_mode" id="edit_language_mode" class="form-select">
                                            <option value="bilingual">Bilingual (ខ្មែរ + English)</option>
                                            <option value="khmer-only">ភាសាខ្មែរសុទ្ធ (Khmer Only)</option>
                                            <option value="english-only">English Only</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ម៉ោងបណ្តុះបណ្តាល (CPD Hours)</label>
                                        <input type="number" name="training_hours" id="edit_training_hours" class="form-control" min="0" max="999">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ជំនាញបណ្តុះបណ្តាល (Track)</label>
                                        <input type="text" name="hr_competency_track" id="edit_hr_competency_track" class="form-control" placeholder="e.g. Leadership Development">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ស្ថានភាពវិញ្ញាបនបត្រ</label>
                                        <input type="text" name="compliance_status" id="edit_compliance_status" class="form-control" placeholder="e.g. Verified & Accredited">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ការពិពណ៌នាជាភាសាខ្មែរ (Khmer Description)</label>
                                        <textarea name="khmer_description" id="edit_khmer_description" rows="2" class="form-control"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ការពិពណ៌នាជាភាសាអង់គ្លេស (English Description)</label>
                                        <textarea name="description" id="edit_description" rows="2" class="form-control"></textarea>
                                    </div>

                                    <div class="col-12 d-flex gap-4 p-3 bg-light rounded border mt-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="edit_is_default">
                                            <label class="form-check-label fw-bold text-dark" for="edit_is_default">★ គំរូលំនាំដើម (Default Model)</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                                            <label class="form-check-label fw-bold text-dark" for="edit_is_active">បើកដំណើរការ (Active)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: CONTENT & TEXT -->
                            <div class="tab-pane fade" id="modal-tab-content" role="tabpanel">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">✍️ ស្ថាប័នចេញវិញ្ញាបនបត្រ & ស្លាកសញ្ញា</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះស្ថាប័នជាភាសាខ្មែរ</label>
                                        <input type="text" name="issuer_khmer_name" id="edit_issuer_khmer_name" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះស្ថាប័នជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="issuer_name" id="edit_issuer_name" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះផ្នែក / សាខា (Department)</label>
                                        <input type="text" name="department" id="edit_department" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">តំណភ្ជាប់ Logo (Logo URL)</label>
                                        <input type="text" name="logo_url" id="edit_logo_url" class="form-control">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">📜 បាលីជាតិ & ចំណងជើងវិញ្ញាបនបត្រ</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">បាលីជាតិខ្មែរ (Khmer Preamble)</label>
                                        <input type="text" name="khmer_preamble" id="edit_khmer_preamble" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">បាលីជាតិអង់គ្លេស (English Preamble)</label>
                                        <input type="text" name="authority_preamble" id="edit_authority_preamble" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ចំណងជើងវិញ្ញាបនបត្រខ្មែរ</label>
                                        <input type="text" name="khmer_certificate_title" id="edit_khmer_certificate_title" class="form-control font-moul" style="font-size: 13px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ចំណងជើងវិញ្ញាបនបត្រអង់គ្លេស</label>
                                        <input type="text" name="certificate_title" id="edit_certificate_title" class="form-control font-cinzel" style="font-size: 13px;">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">អត្ថបទបូស្លាបព្រាបមាស (Presentation Ribbon Banner)</label>
                                        <input type="text" name="banner_text" id="edit_banner_text" class="form-control font-cinzel fw-bold text-primary">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🎖️ សមិទ្ធផល និងសេចក្តីថ្លែងកោតសរសើរ</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះវគ្គសិក្សា / សមិទ្ធផលខ្មែរ</label>
                                        <input type="text" name="khmer_course_or_achievement_title" id="edit_khmer_course_or_achievement_title" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">ឈ្មោះវគ្គសិក្សា / សមិទ្ធផលអង់គ្លេស</label>
                                        <input type="text" name="course_or_achievement_title" id="edit_course_or_achievement_title" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">អត្ថបទបន្ទាប់ពីចំណងជើងខ្មែរ (Subtitle)</label>
                                        <input type="text" name="khmer_subtitle" id="edit_khmer_subtitle" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">អត្ថបទបន្ទាប់ពីចំណងជើងអង់គ្លេស (Subtitle)</label>
                                        <input type="text" name="subtitle" id="edit_subtitle" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">សេចក្តីកោតសរសើរជាភាសាខ្មែរ (Khmer Citation)</label>
                                        <textarea name="khmer_citation" id="edit_khmer_citation" rows="2" class="form-control font-kantumruy"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">សេចក្តីកោតសរសើរជាភាសាអង់គ្លេស (English Citation)</label>
                                        <textarea name="citation" id="edit_citation" rows="2" class="form-control font-cormorant"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">ប្រយោគអបអរសាទរបញ្ចប់ (Closing Citation Sentence)</label>
                                        <input type="text" name="citation_closing" id="edit_citation_closing" class="form-control font-cormorant">
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: STYLE & APPEARANCE -->
                            <div class="tab-pane fade" id="modal-tab-style" role="tabpanel">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🎨 ពណ៌គោល និងផ្ទៃក្រដាស (Palette)</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌ខៀវគោល (Primary Color)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="color-preview-box" id="edit_picker_primary_color" onchange="document.getElementById('edit_primary_color').value=this.value">
                                            <input type="text" name="primary_color" id="edit_primary_color" class="form-control font-monospace">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌មាសរំលេច (Accent Gold)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="color-preview-box" id="edit_picker_accent_color" onchange="document.getElementById('edit_accent_color').value=this.value">
                                            <input type="text" name="accent_color" id="edit_accent_color" class="form-control font-monospace">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌ផ្ទៃក្រដាស (Background Tint)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="color" class="color-preview-box" id="edit_picker_bg_color" onchange="document.getElementById('edit_bg_color').value=this.value">
                                            <input type="text" name="bg_color" id="edit_bg_color" class="form-control font-monospace">
                                        </div>
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🖼️ រចនាប័ទ្មស៊ុម និងបន្ទាត់ផ្កា</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">រចនាប័ទ្មស៊ុម (Border Style)</label>
                                        <select name="border_style" id="edit_border_style" class="form-select">
                                            <option value="navy-gold-ribbon">Navy & Gold Ribbon + 4 Corner Filigrees (ប្រណិតលំនាំដើម)</option>
                                            <option value="double-gold-rails">Double Gold Metallic Rails (ស៊ុមមាសទ្វេ)</option>
                                            <option value="classic-ornate">Classic Royal Ornate (ក្បាច់បុរាណ)</option>
                                            <option value="modern-minimal">Modern Clean Minimal (សាមញ្ញទាន់សម័យ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ក្បាច់បន្ទាត់ផ្កាកណ្តាល (Divider Ornament)</label>
                                        <select name="divider_ornament" id="edit_divider_ornament" class="form-select">
                                            <option value="baroque-fleur">Baroque Fleur-de-lis (ផ្កាភ្ញីទេស)</option>
                                            <option value="diamond-stars">Diamond Stars (គ្រាប់ពេជ្រ & ផ្កាយ)</option>
                                            <option value="minimal-line">Minimal Clean Line (បន្ទាត់សាមញ្ញ)</option>
                                            <option value="none">None (គ្មាន)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">ប្រវែងបន្ទាត់ (px)</label>
                                        <input type="number" name="divider_width" id="edit_divider_width" class="form-control" min="80" max="600">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">👤 ប្រអប់រូបថតបុគ្គលិក & Watermark</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="show_recipient_photo" value="1" id="edit_show_recipient_photo">
                                            <label class="form-check-label fw-bold" for="edit_show_recipient_photo">បង្ហាញប្រអប់រូបថត (Photo Box)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ទម្រង់ប្រអប់រូបថត (Photo Shape)</label>
                                        <select name="recipient_photo_shape" id="edit_recipient_photo_shape" class="form-select">
                                            <option value="rounded-portrait">Rounded Portrait (ចតុកោណកែងបញ្ឈរ)</option>
                                            <option value="circle">Circle (រង្វង់មូល)</option>
                                            <option value="square">Square (ការេ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="show_watermark" value="1" id="edit_show_watermark">
                                            <label class="form-check-label fw-bold" for="edit_show_watermark">Watermark</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold">ភាពព្រាល (Opacity)</label>
                                        <input type="number" step="0.01" name="watermark_opacity" id="edit_watermark_opacity" class="form-control" min="0" max="0.5">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🔤 ពុម្ពអក្សរ (Typography)</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពុម្ពអក្សរអង់គ្លេសធំ (Display Font)</label>
                                        <select name="font_english_title" id="edit_font_english_title" class="form-select">
                                            <option value="cinzel">Cinzel (រាជវង្សអង់គ្លេស)</option>
                                            <option value="playfair">Playfair Display (បុរាណប្រណិត)</option>
                                            <option value="cormorant">Cormorant Garamond</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពុម្ពអក្សរអត្ថបទ (Body Font)</label>
                                        <select name="font_body" id="edit_font_body" class="form-select">
                                            <option value="cormorant">Cormorant Garamond (Italic Serifs)</option>
                                            <option value="plus-jakarta-sans">Plus Jakarta Sans (ទំនើបស្អាត)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពុម្ពអក្សរហត្ថលេខា (Signature)</label>
                                        <select name="font_signature" id="edit_font_signature" class="form-select">
                                            <option value="alex-brush">Alex Brush (រលូនប្រណិត)</option>
                                            <option value="great-vibes">Great Vibes (ទន់ភ្លន់)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: SIGNATORIES & SEAL -->
                            <div class="tab-pane fade" id="modal-tab-signatories" role="tabpanel">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🖋️ ហត្ថលេខីទី១ - ខាងឆ្វេង (Signatory #1 - Left)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig1_name" id="edit_sig1_name" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig1_khmer_name" id="edit_sig1_khmer_name" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">រចនាប័ទ្មហត្ថលេខា</label>
                                        <select name="sig1_style" id="edit_sig1_style" class="form-select">
                                            <option value="alex-brush">Alex Brush (Cursive Script)</option>
                                            <option value="great-vibes">Great Vibes (Calligraphy)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig1_role" id="edit_sig1_role" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig1_khmer_role" id="edit_sig1_khmer_role" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ផ្នែក / ស្ថាប័ន</label>
                                        <input type="text" name="sig1_organization" id="edit_sig1_organization" class="form-control">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🖋️ ហត្ថលេខីទី២ - ខាងស្តាំ (Signatory #2 - Right)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig2_name" id="edit_sig2_name" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ឈ្មោះជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig2_khmer_name" id="edit_sig2_khmer_name" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">រចនាប័ទ្មហត្ថលេខា</label>
                                        <select name="sig2_style" id="edit_sig2_style" class="form-select">
                                            <option value="great-vibes">Great Vibes (Calligraphy)</option>
                                            <option value="alex-brush">Alex Brush (Cursive Script)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាអង់គ្លេស</label>
                                        <input type="text" name="sig2_role" id="edit_sig2_role" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">តួនាទីជាភាសាខ្មែរ</label>
                                        <input type="text" name="sig2_khmer_role" id="edit_sig2_khmer_role" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ផ្នែក / ស្ថាប័ន</label>
                                        <input type="text" name="sig2_organization" id="edit_sig2_organization" class="form-control">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">🎖️ ត្រាមាសផ្លូវការ (Official Center Seal)</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">រចនាប័ទ្មត្រា (Seal Style)</label>
                                        <select name="seal_style" id="edit_seal_style" class="form-select">
                                            <option value="crown-laurel">3D Embossed Gold Seal with Crown & Laurels (ត្រាមាស 3D)</option>
                                            <option value="classic-round">Classic Round Seal (រង្វង់បុរាណ)</option>
                                            <option value="crimson-wax">Crimson Wax (ជ័រក្រហម)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">ពណ៌កន្ទុយបូត្រា (Ribbon Tails Color)</label>
                                        <select name="seal_ribbon_color" id="edit_seal_ribbon_color" class="form-select">
                                            <option value="navy">Navy Blue (ខៀវចាស់កាត់មាស)</option>
                                            <option value="gold">Gold (មាសសុទ្ធ)</option>
                                            <option value="none">None (គ្មានកន្ទុយបូ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">អត្ថបទត្រាជួរលើ (Primary Text)</label>
                                        <input type="text" name="seal_primary_text" id="edit_seal_primary_text" class="form-control">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer py-2.5 px-4 bg-light d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">
                            <i class="link-icon" data-feather="check"></i> រក្សាទុកការកែសម្រួលគំរូ (Save All Changes)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal 3: Fullscreen High-Res Template Preview Modal (A4 Landscape) -->
    <div class="modal fade" id="fullPreviewModal" tabindex="-1" aria-labelledby="fullPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered" style="max-width: 1240px;">
            <div class="modal-content border-0 shadow-2xl" style="border-radius: 16px; overflow: hidden; background: #071026;">
                <div class="modal-header py-2.5 px-4" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-bottom: 1px solid rgba(212,163,44,0.3);">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark font-monospace fw-bold" id="modalTemplateTag">V6</span>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0" id="modalTemplateTitle">Template Preview</h6>
                            <small class="text-warning font-monospace" style="font-size: 11px;">A4 Landscape · 1080px × 760px (Ratio 1.414:1)</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-light btn-sm text-dark fw-bold" onclick="printModalPreview()" title="បោះពុម្ព A4 ភ្លាមៗ">
                            <i class="link-icon" data-feather="printer"></i> 🖨️ {{ __('index.print_a4') }}
                        </button>
                        <a href="#" id="modalOpenTabBtn" class="btn btn-outline-light btn-sm fw-bold" target="_blank" title="បើកមើលទម្រង់ A4 ពេញផ្ទាំងក្នុង Tab ថ្មី">
                            <i class="link-icon" data-feather="external-link"></i> ↗ {{ __('index.open_in_new_tab') }}
                        </a>
                        <a href="#" id="modalStudioBtn" class="btn btn-warning btn-sm text-dark fw-bold" target="_blank">
                            <i class="link-icon" data-feather="layout"></i> Studio 🎨
                        </a>
                        <a href="#" id="modalIssueBtn" class="btn btn-success btn-sm fw-bold">
                            <i class="link-icon" data-feather="plus"></i> {{ __('index.issue_certificate') }}
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0 d-flex flex-column align-items-center justify-content-center" style="background: radial-gradient(circle at 50% 50%, #121e3d 0%, #060e22 100%); min-height: 640px; height: 82vh; max-height: 840px;">
                    <div class="w-100 h-100 p-2 d-flex align-items-center justify-content-center position-relative">
                        <div id="modalLoadingSpinner" class="position-absolute d-flex flex-column align-items-center text-white" style="z-index: 10;">
                            <div class="spinner-border text-warning mb-2" role="status"></div>
                            <span class="small font-kantumruy">កំពុងដំណើរការបង្ហាញគំរូ A4...</span>
                        </div>
                        <iframe id="modalPreviewFrame" src="about:blank" class="w-100 h-100 border-0 rounded shadow-2xl" 
                                style="background: transparent; z-index: 20;"
                                onload="var s = document.getElementById('modalLoadingSpinner'); if(s) s.style.display='none';"></iframe>
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 d-flex justify-content-between align-items-center" style="background: #091a38; border-top: 1px solid rgba(255,255,255,0.08);">
                    <div class="small text-white-50 font-kantumruy">
                        ℹ️ បង្ហាញទម្រង់វិញ្ញាបនបត្រទំហំ A4 Landscape (297mm × 210mm) សមាមាត្រតាមស្តង់ដារបោះពុម្ព។
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ (Close)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    var previewBaseUrl = @json(route('admin.certificates.batchPreview'));
    var templatePreviewBaseUrl = "{{ url('admin/certificates/templates') }}";
    var studioBaseUrl = @json(route('admin.certificates.studio'));
    var a4PreviewBaseUrl = @json(url('admin/certificates/templates'));
    var issueBaseUrl = @json(route('admin.certificates.create'));
    var templatesData = @json($templates->keyBy('id'));

    function openFullPreviewModal(tmplId) {
        var tmpl = templatesData[tmplId];
        if (!tmpl) return;

        document.getElementById('modalTemplateTag').innerText = tmpl.version_tag || 'TMPL';
        document.getElementById('modalTemplateTitle').innerText = (tmpl.khmer_title ? tmpl.khmer_title + ' · ' : '') + tmpl.title;

        // Content-only A4 certificate preview (no admin sidebar / top navbar)
        var a4Url = a4PreviewBaseUrl + '/' + tmpl.id + '/preview?embed=1';
        var studioUrl = studioBaseUrl + '?template_id=' + tmpl.id;
        document.getElementById('modalStudioBtn').href = studioUrl;
        document.getElementById('modalIssueBtn').href = issueBaseUrl + '?template_id=' + tmpl.id;
        document.getElementById('modalOpenTabBtn').href = a4Url;

        var spinner = document.getElementById('modalLoadingSpinner');
        if (spinner) spinner.style.display = 'flex';

        var iframe = document.getElementById('modalPreviewFrame');
        iframe.src = a4Url + '&_ts=' + Date.now();

        var modalEl = document.getElementById('fullPreviewModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }

    function printModalPreview() {
        var iframe = document.getElementById('modalPreviewFrame');
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }
    }

    function openEditTemplateModal(tmpl) {
        if (!tmpl) return;

        var form = document.getElementById('editTemplateForm');
        form.action = "{{ url('admin/certificates/templates') }}/" + tmpl.id;

        document.getElementById('edit_modal_version_badge').innerText = tmpl.version_tag || 'TMPL';
        document.getElementById('modalEditFullPageLink').href = "{{ url('admin/certificates/templates') }}/" + tmpl.id + "/edit";
        document.getElementById('modalEditStudioLink').href = studioBaseUrl + '?template_id=' + tmpl.id;

        var tp = (tmpl.payload_json && typeof tmpl.payload_json === 'object') ? tmpl.payload_json : {};
        var styling = (tmpl.styling_json && typeof tmpl.styling_json === 'object') ? tmpl.styling_json : (tp.styling || {});
        var seal = (tmpl.seal_json && typeof tmpl.seal_json === 'object') ? tmpl.seal_json : (tp.seal || {});
        var sigs = (tmpl.signatories_json && Array.isArray(tmpl.signatories_json)) ? tmpl.signatories_json : (tp.signatories || []);
        var sig1 = sigs[0] || {};
        var sig2 = sigs[1] || {};

        // Tab 1: General & Layout
        document.getElementById('edit_khmer_title').value = tmpl.khmer_title || '';
        document.getElementById('edit_title').value = tmpl.title || '';
        document.getElementById('edit_category').value = tmpl.category || 'custom';
        document.getElementById('edit_layout_template').value = tmpl.layout_template || 'executive-medal-ribbon';
        document.getElementById('edit_version_tag').value = tmpl.version_tag || 'CUSTOM';
        document.getElementById('edit_theme').value = tmpl.theme || 'navy-gold';
        document.getElementById('edit_language_mode').value = tmpl.language_mode || 'bilingual';
        document.getElementById('edit_training_hours').value = tmpl.training_hours || 45;
        document.getElementById('edit_hr_competency_track').value = tmpl.hr_competency_track || '';
        document.getElementById('edit_compliance_status').value = tmpl.compliance_status || '';
        document.getElementById('edit_khmer_description').value = tmpl.khmer_description || '';
        document.getElementById('edit_description').value = tmpl.description || '';
        document.getElementById('edit_is_default').checked = !!tmpl.is_default;
        document.getElementById('edit_is_active').checked = tmpl.is_active !== undefined ? !!tmpl.is_active : true;

        // Tab 2: Content & Text
        document.getElementById('edit_issuer_khmer_name').value = tmpl.issuer_khmer_name || (tp.issuer && tp.issuer.khmerName) || 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)';
        document.getElementById('edit_issuer_name').value = tmpl.issuer_name || (tp.issuer && tp.issuer.name) || 'KNEAYERNG';
        document.getElementById('edit_department').value = tmpl.department || (tp.issuer && tp.issuer.department) || 'PHONE SHOP';
        document.getElementById('edit_logo_url').value = tmpl.logo_url || (tp.issuer && tp.issuer.logoUrl) || '';
        document.getElementById('edit_khmer_preamble').value = tmpl.khmer_preamble || tp.khmerPreamble || 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ';
        document.getElementById('edit_authority_preamble').value = tmpl.authority_preamble || tp.authorityPreamble || 'KINGDOM OF CAMBODIA · NATION RELIGION KING';
        document.getElementById('edit_khmer_certificate_title').value = tmpl.khmer_certificate_title || tp.khmerTitle || '';
        document.getElementById('edit_certificate_title').value = tmpl.certificate_title || tp.certificateTitle || '';
        document.getElementById('edit_banner_text').value = tp.bannerText || 'THIS CERTIFICATE IS PRESENTED TO';
        document.getElementById('edit_khmer_course_or_achievement_title').value = tmpl.khmer_course_or_achievement_title || tp.khmerCourseOrAchievementTitle || '';
        document.getElementById('edit_course_or_achievement_title').value = tmpl.course_or_achievement_title || tp.courseOrAchievementTitle || '';
        document.getElementById('edit_khmer_subtitle').value = tmpl.khmer_subtitle || tp.khmerSubtitle || '';
        document.getElementById('edit_subtitle').value = tmpl.subtitle || tp.subtitle || '';
        document.getElementById('edit_khmer_citation').value = tmpl.khmer_citation || tp.khmerCitation || '';
        document.getElementById('edit_citation').value = tmpl.citation || tp.citation || '';
        document.getElementById('edit_citation_closing').value = tmpl.citation_closing || tp.citationClosing || 'Congratulations on this well-deserved achievement, and we wish you continued success in your new role.';

        // Tab 3: Style & Appearance
        var prim = styling.primaryColor || '#092653';
        var acc = styling.accentColor || '#D4A32C';
        var bg = styling.bgColor || '#FFFDF8';
        document.getElementById('edit_primary_color').value = prim;
        document.getElementById('edit_picker_primary_color').value = prim.startsWith('#') && prim.length === 7 ? prim : '#092653';
        document.getElementById('edit_accent_color').value = acc;
        document.getElementById('edit_picker_accent_color').value = acc.startsWith('#') && acc.length === 7 ? acc : '#D4A32C';
        document.getElementById('edit_bg_color').value = bg;
        document.getElementById('edit_picker_bg_color').value = bg.startsWith('#') && bg.length === 7 ? bg : '#FFFDF8';
        document.getElementById('edit_border_style').value = styling.borderStyle || 'navy-gold-ribbon';
        document.getElementById('edit_divider_ornament').value = styling.dividerOrnament || 'baroque-fleur';
        document.getElementById('edit_divider_width').value = styling.dividerWidth || 240;
        document.getElementById('edit_show_recipient_photo').checked = tp.showRecipientPhoto !== undefined ? !!tp.showRecipientPhoto : true;
        document.getElementById('edit_recipient_photo_shape').value = tp.recipientPhotoShape || 'rounded-portrait';
        document.getElementById('edit_show_watermark').checked = styling.showWatermark !== undefined ? !!styling.showWatermark : true;
        document.getElementById('edit_watermark_opacity').value = styling.watermarkOpacity !== undefined ? styling.watermarkOpacity : 0.10;
        document.getElementById('edit_font_english_title').value = styling.fontDisplay || 'playfair';
        document.getElementById('edit_font_body').value = styling.fontBody || 'cormorant';
        document.getElementById('edit_font_signature').value = styling.fontSignature || 'alex-brush';

        // Tab 4: Signatories & Seal
        document.getElementById('edit_sig1_name').value = sig1.name || 'CHHOEUN REACH';
        document.getElementById('edit_sig1_khmer_name').value = sig1.khmerName || 'ឈឿន រាជ';
        document.getElementById('edit_sig1_role').value = sig1.role || 'IT Manager / Branch Manager';
        document.getElementById('edit_sig1_khmer_role').value = sig1.khmerRole || 'ប្រធានផ្នែកព័ត៌មានវិទ្យា / ប្រធានសាខា';
        document.getElementById('edit_sig1_organization').value = sig1.organization || 'Admin • HR • System • Stock';
        document.getElementById('edit_sig1_style').value = sig1.signatureStyle || 'alex-brush';

        document.getElementById('edit_sig2_name').value = sig2.name || 'PET POEUN';
        document.getElementById('edit_sig2_khmer_name').value = sig2.khmerName || 'ម៉េង សោភា';
        document.getElementById('edit_sig2_role').value = sig2.role || 'HR Manager / HR Supervisor';
        document.getElementById('edit_sig2_khmer_role').value = sig2.khmerRole || 'ប្រធានផ្នែកធនធានមនុស្ស';
        document.getElementById('edit_sig2_organization').value = sig2.organization || 'Human Resource Department';
        document.getElementById('edit_sig2_style').value = sig2.signatureStyle || 'great-vibes';

        document.getElementById('edit_seal_style').value = seal.style || 'crown-laurel';
        document.getElementById('edit_seal_ribbon_color').value = seal.ribbonColor || 'navy';
        document.getElementById('edit_seal_primary_text').value = seal.primaryText || 'KNEAYERNG';

        // Reset tab to Tab 1
        var tab1Trigger = document.getElementById('modal-tab-general-btn');
        if (tab1Trigger) {
            var tab1 = new bootstrap.Tab(tab1Trigger);
            tab1.show();
        }

        var modalEl = document.getElementById('editTemplateModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endsection
