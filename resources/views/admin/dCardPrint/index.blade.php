@extends('layouts.master')

@section('title', __('index.d_card_designer'))

@section('styles')
    <style>
        .page-content:has(.d-card-studio) {
            background: #f8fafc;
            padding-top: 0;
        }

        .page-content:has(.d-card-studio) > .grid-margin {
            display: none !important;
        }

        .content.d-card-studio {
            margin-top: 0;
        }
    </style>
@endsection

@section('main-content')
    <section class="content d-card-studio">
        @include('admin.section.flash_message')
        @php
            $formatBranchName = static function ($branch, $fallback = 'គ្នាយើង') {
                $label = trim((string) ($branch ?: $fallback));

                if ($label === '') {
                    return '';
                }

                $label = preg_replace('/^គ្នាយើង\s*[-–—]?\s*/u', '', $label);

                return $label === '' ? 'គ្នាយើង' : 'គ្នាយើង-' . $label;
            };

            $formatDepartmentBranch = static function ($department, $branch) use ($formatBranchName) {
                return collect([
                    trim((string) $department),
                    !empty($branch) ? $formatBranchName($branch, '') : '',
                ])->filter()->implode(' ');
            };
        @endphp

        <header class="studio-topbar no-print">
            <div class="studio-brand">
                <div class="studio-logo">
                    @if(!empty($companyLogo))
                        <img src="{{ $companyLogo }}" alt="">
                    @else
                        KY
                    @endif
                </div>
                <div>
                    <div class="studio-title">{{ $company?->name ?? 'គ្នាយើង' }} <span>គ្នាយើង</span></div>
                    <div class="studio-subtitle">D Card Print Studio · Employee card designer and A4 batch printing</div>
                </div>
            </div>
            <nav class="studio-main-tabs">
                <button type="button" class="active" data-studio-tab="editor">
                    <i class="link-icon" data-feather="credit-card"></i> Template Designer
                </button>
                <button type="button" data-studio-tab="batch">
                    <i class="link-icon" data-feather="users"></i> Employee Batch Roster
                    <strong><span id="selectedCount">0</span>/<span id="totalCount">0</span></strong>
                </button>
                <button type="button" data-studio-tab="a4print">
                    <i class="link-icon" data-feather="printer"></i> A4 Print Studio
                </button>
            </nav>
            <button type="button" class="studio-print-btn" id="printCards">
                <i class="link-icon" data-feather="printer"></i> Quick Print A4
            </button>
        </header>

        <div class="studio-pane no-print active" id="studio-editor">
            <div class="row g-3">
                <div class="col-xl-4 col-lg-5">
                    <div class="studio-panel-head">
                        <div>
                            <h5><i class="link-icon" data-feather="sliders"></i> ID Card Template Studio</h5>
                            <p>Customize layout, templates, photo styles & 300 DPI guides</p>
                        </div>
                        <button type="button" class="btn btn-link" id="resetStudio">Reset</button>
                    </div>

                    <div class="studio-subtabs">
                        <button type="button" class="active" data-editor-section="templates"><i data-feather="grid"></i> Templates</button>
                        <button type="button" data-editor-section="orientation"><i data-feather="crop"></i> Orientation</button>
                        <button type="button" data-editor-section="branding"><i data-feather="droplet"></i> Branding</button>
                        <button type="button" data-editor-section="front"><i data-feather="credit-card"></i> Front Layout</button>
                        <button type="button" data-editor-section="back"><i data-feather="grid"></i> Back Side</button>
                        <button type="button" data-editor-section="guides"><i data-feather="crop"></i> Guides</button>
                    </div>

                    <div class="card mb-3 editor-section active" data-editor-content="templates">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Templates</h6>
                        </div>
                        <div class="card-body">
                            <p class="template-intro">Choose from 8 high-resolution ID card template styles tailored for គ្នាយើង, corporate, academic, and medical sectors:</p>
                            <div class="template-color-tool">
                                    <input type="hidden" id="templateStyle" value="kneayerng_gold">
                                <label>
                                    <span>Card Color</span>
                                    <input type="color" id="templateCardColor" value="#0B172A">
                                </label>
                                <label>
                                    <span>Accent Color</span>
                                    <input type="color" id="templateAccentColor" value="#C59B27">
                                </label>
                            </div>
                            <div class="template-grid">
                                <button type="button" class="template-preset active" data-template="kneayerng_gold" data-card="#0B172A" data-accent="#C59B27" data-photo-width="34" data-photo-height="33.5" data-width="71" data-height="103">
                                    <span class="template-icon" style="background:#0B172A"><i data-feather="award"></i></span>
                                    <span class="template-copy"><strong>Employee ID Card <em>Kneayerng Gold Signature</em></strong><small>Full ZIP template with navy curved ribbon, gold accents, dual QR, barcode, and modern back side</small><span class="swatches"><b style="background:#0B172A"></b><b style="background:#C59B27"></b><b style="background:#FAF8F5"></b></span></span>
                                </button>
                                <button type="button" class="template-preset" data-template="khmer_gold" data-card="#c9aa28" data-accent="#1f2937" data-photo-width="52.5" data-photo-height="69" data-width="71" data-height="103">
                                    <span class="template-icon" style="background:#d97706"><i data-feather="home"></i></span>
                                    <span class="template-copy"><strong>Khmer Amber Gold <em>Store / Retail Staff</em></strong><small>Traditional Khmer side banner ribbon with Amber Gold gradient and KHQR back</small><span class="swatches"><b style="background:#d97706"></b><b style="background:#dc2626"></b><b style="background:#111827"></b></span></span>
                                    <i class="template-check" data-feather="check"></i>
                                </button>
                                <button type="button" class="template-preset" data-template="modern_corporate" data-card="#0284c7" data-accent="#0f172a">
                                    <span class="template-icon" style="background:#0284c7"><i data-feather="briefcase"></i></span>
                                    <span class="template-copy"><strong>Modern Corporate & Tech <em>Corporate Office</em></strong><small>Clean header bar with top badge, crisp grid, and professional corporate styling</small><span class="swatches"><b style="background:#0284c7"></b><b style="background:#2563eb"></b><b style="background:#0f172a"></b></span></span>
                                </button>
                                <button type="button" class="template-preset" data-template="minimal_clean" data-card="#475569" data-accent="#0ea5e9">
                                    <span class="template-icon" style="background:#475569"><i data-feather="star"></i></span>
                                    <span class="template-copy"><strong>Minimalist Clean Badge <em>Universal / Event</em></strong><small>Light contrast canvas with refined typography and subtle border accents</small><span class="swatches"><b style="background:#475569"></b><b style="background:#0ea5e9"></b><b style="background:#0f172a"></b></span></span>
                                </button>
                                <button type="button" class="template-preset" data-template="executive_dark" data-card="#111827" data-accent="#d97706">
                                    <span class="template-icon" style="background:#111827"><i data-feather="shield"></i></span>
                                    <span class="template-copy"><strong>Executive Dark Gold <em>Executive / Luxury</em></strong><small>Deep navy background with gold border lines and metallic emblem styling</small><span class="swatches"><b style="background:#111827"></b><b style="background:#d97706"></b><b style="background:#e2e8f0"></b></span></span>
                                </button>
                                <button type="button" class="template-preset" data-template="university_student" data-card="#1d4ed8" data-accent="#0f172a">
                                    <span class="template-icon" style="background:#1d4ed8"><i data-feather="award"></i></span>
                                    <span class="template-copy"><strong>University Student ID <em>Academic / School</em></strong><small>Institution header banner, student photo slot, matriculation & library barcode</small><span class="swatches"><b style="background:#1d4ed8"></b><b style="background:#d97706"></b><b style="background:#0f172a"></b></span></span>
                                </button>
                                <button type="button" class="template-preset" data-template="medical_health" data-card="#0f766e" data-accent="#dc2626">
                                    <span class="template-icon" style="background:#0f766e"><i data-feather="activity"></i></span>
                                    <span class="template-copy"><strong>Hospital / Medical Staff <em>Healthcare</em></strong><small>Cyan header with doctor/nurse photo frame, blood type badge, and medical cross</small><span class="swatches"><b style="background:#0f766e"></b><b style="background:#dc2626"></b><b style="background:#134e4a"></b></span></span>
                                </button>
                                <button type="button" class="template-preset" data-template="retail_merchant" data-card="#b45309" data-accent="#dc2626">
                                    <span class="template-icon" style="background:#b45309"><i data-feather="credit-card"></i></span>
                                    <span class="template-copy"><strong>Retail KHQR Merchant <em>Merchant Payment</em></strong><small>High-visibility merchant card optimized for KHQR cashier identification</small><span class="swatches"><b style="background:#b45309"></b><b style="background:#dc2626"></b><b style="background:#0f172a"></b></span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3 editor-section" data-editor-content="orientation">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Orientation & CR80 Calibration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Card Orientation</label>
                                    <select class="form-select designer-control" id="cardOrientation">
                                        <option value="portrait" selected>Portrait 71 x 103mm</option>
                                        <option value="landscape">Landscape 103 x 71mm</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Scale %</label>
                                    <input type="number" class="form-control designer-control" id="cardScale" value="100" min="80" max="120">
                                </div>
                                <div class="col-md-4 col-6 mb-3">
                                    <label class="form-label">Width mm</label>
                                    <input type="number" class="form-control designer-control" id="cardWidthMm" value="71" step="0.01">
                                </div>
                                <div class="col-md-4 col-6 mb-3">
                                    <label class="form-label">Height mm</label>
                                    <input type="number" class="form-control designer-control" id="cardHeightMm" value="103" step="0.01">
                                </div>
                                <div class="col-md-4 col-6 mb-3">
                                    <label class="form-label">Bleed mm</label>
                                    <input type="number" class="form-control designer-control" id="bleedMm" value="1.5" step="0.1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3 editor-section" data-editor-content="branding">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Branding & Layout</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Khmer</label>
                                    <input type="text" class="form-control designer-control" id="companyKhmer" value="{{ $company?->name ?? 'KNEA YERNG' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company English</label>
                                    <input type="text" class="form-control designer-control" id="companyEnglish" value="{{ $company?->name ?? 'KNEA YERNG PHONE SHOP' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Side Banner</label>
                                    <input type="text" class="form-control designer-control" id="sideBannerText" value="{{ $company?->name ?? 'KNEA YERNG' }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Back Tagline</label>
                                    <input type="text" class="form-control designer-control" id="backTagline" value="Scan. Pay. Done.">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('index.card_color') }}</label>
                                    <input type="color" class="form-control form-control-color w-100 color-sync" id="brandCardColor" data-sync-target="#cardColor" value="#0B172A">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('index.accent_color') }}</label>
                                    <input type="color" class="form-control form-control-color w-100 color-sync" id="brandAccentColor" data-sync-target="#accentColor" value="#C59B27">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card editor-section" data-editor-content="front">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Photo, Fields & Guides</h6>
                        </div>
                        <div class="card-body">
                            <div class="customize-block">
                                <div class="customize-block-title">
                                    <i data-feather="image"></i>
                                    <span>Customize Front Card</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Photo Size</label>
                                        <select class="form-select designer-control" id="photoPreset">
                                            <option value="auto">Auto Flexible</option>
                                            <option value="passport">Passport 3x4cm</option>
                                            <option value="square">Square 1:1</option>
                                            <option value="hero">Large Hero</option>
                                            <option value="custom">Custom mm</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Photo Shape</label>
                                        <select class="form-select designer-control" id="photoShape">
                                            <option value="rectangle">Rectangle</option>
                                            <option value="rounded">Rounded</option>
                                            <option value="square">Square</option>
                                            <option value="circle">Circle</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Photo Fit</label>
                                        <select class="form-select designer-control" id="photoFit">
                                            <option value="cover">Cover</option>
                                            <option value="contain">Contain</option>
                                            <option value="fill">Fill</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Photo Zoom %</label>
                                        <input type="number" class="form-control designer-control" id="photoZoom" value="100" min="80" max="140">
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Photo W mm</label>
                                        <input type="number" class="form-control designer-control" id="photoWidthMm" value="34" step="0.1">
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Photo H mm</label>
                                        <input type="number" class="form-control designer-control" id="photoHeightMm" value="33.5" step="0.1">
                                    </div>
                                    <div class="col-md-4 col-6 mb-0">
                                        <label class="form-label">Corner mm</label>
                                        <input type="number" class="form-control designer-control" id="photoRadiusMm" value="3" min="0" step="0.1">
                                    </div>
                                    <div class="col-md-4 col-6 mb-0">
                                        <label class="form-label">Text Scale %</label>
                                        <input type="number" class="form-control designer-control" id="frontTextScale" value="100" min="70" max="140">
                                    </div>
                                    <div class="col-md-6 col-12 mb-3">
                                        <label class="form-label">Khmer Name Font</label>
                                        <select class="form-select designer-control" id="khmerNameFont">
                                            <option value='"Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif'>Khmer OS Muol / Moul</option>
                                            <option value='"Battambang", "Khmer OS Battambang", "Noto Sans Khmer", "Kantumruy Pro", Arial, sans-serif'>Battambang</option>
                                            <option value='"Kantumruy Pro", "Noto Sans Khmer", Arial, sans-serif'>Kantumruy Pro</option>
                                            <option value='"Noto Sans Khmer", "Kantumruy Pro", Arial, sans-serif'>Noto Sans Khmer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="form-label">Khmer Size px</label>
                                        <input type="number" class="form-control designer-control" id="khmerNameSize" value="12" min="8" max="34" step="0.5">
                                    </div>
                                    <div class="col-md-6 col-12 mb-0">
                                        <label class="form-label">English Name Font</label>
                                        <select class="form-select designer-control" id="englishNameFont">
                                            <option value='Arial, "Poppins", sans-serif'>Arial / Poppins</option>
                                            <option value='"Poppins", Arial, sans-serif'>Poppins</option>
                                            <option value='Montserrat, "Poppins", Arial, sans-serif'>Montserrat</option>
                                            <option value='"Kantumruy Pro", Arial, sans-serif'>Kantumruy Pro</option>
                                            <option value='"Times New Roman", serif'>Times New Roman</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6 mb-0">
                                        <label class="form-label">English Size px</label>
                                        <input type="number" class="form-control designer-control" id="englishNameSize" value="8" min="6" max="22" step="0.5">
                                    </div>
                                </div>
                            </div>

                            <div class="customize-block mt-3">
                                <div class="customize-block-title">
                                    <i data-feather="list"></i>
                                    <span>Front Display Rows</span>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="front-field-toggles">
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showPhoto" checked>
                                                <span class="form-check-label">Profile photo</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showSideBrand" checked>
                                                <span class="form-check-label">Branch logo rail</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showKhmerName" checked>
                                                <span class="form-check-label">Khmer name</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showEnglishName" checked>
                                                <span class="form-check-label">English name</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showPosition" checked>
                                                <span class="form-check-label">Position</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showDepartment" checked>
                                                <span class="form-check-label">Department</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showBranch" checked>
                                                <span class="form-check-label">Branch</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showPhone" checked>
                                                <span class="form-check-label">Phone</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showEmployeeCode">
                                                <span class="form-check-label">Code row</span>
                                            </label>
                                            <label class="form-check form-switch">
                                                <input class="form-check-input designer-control" type="checkbox" id="showBarcode" checked>
                                                <span class="form-check-label">Barcode footer</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card editor-section" data-editor-content="back">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Back Side & KHQR Payments</h6>
                        </div>
                        <div class="card-body">
                            <div class="customize-block">
                                <div class="customize-block-title">
                                    <i data-feather="credit-card"></i>
                                    <span>Customize Back Card</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Store Subtitle</label>
                                        <input type="text" class="form-control designer-control" id="shopSubtitle" value="{{ $company?->name ?? 'KNEA YERNG PHONE SHOP 3' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Merchant Name</label>
                                        <input type="text" class="form-control designer-control" id="merchantName" value="{{ $company?->name ?? 'HAV MAO' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Merchant ID</label>
                                        <input type="text" class="form-control designer-control" id="merchantId" value="MID: 125080609411765">
                                    </div>
                                    <div class="col-md-6 mb-0">
                                        <label class="form-label">ABA Account</label>
                                        <input type="text" class="form-control designer-control" id="bankAccount1" value="000460226">
                                    </div>
                                    <div class="col-md-6 mb-0">
                                        <label class="form-label">WING Account</label>
                                        <input type="text" class="form-control designer-control" id="bankAccount2" value="070923681">
                                    </div>
                                </div>
                            </div>

                            <div class="customize-block mt-3">
                                <div class="customize-block-title">
                                    <i data-feather="maximize"></i>
                                    <span>Customize Payment QR</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Payment QR mm</label>
                                        <input type="number" class="form-control designer-control" id="paymentQrSizeMm" value="27.3" min="12" max="40" step="0.5">
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Payment Padding mm</label>
                                        <input type="number" class="form-control designer-control" id="paymentPaddingMm" value="2" min="0" max="6" step="0.1">
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <label class="form-label">Payment Crop</label>
                                        <select class="form-select designer-control" id="paymentQrFit">
                                            <option value="contain">Contain</option>
                                            <option value="cover">Crop</option>
                                            <option value="fill">Stretch</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-check form-switch mb-2">
                                            <input class="form-check-input designer-control" type="checkbox" id="showPaymentQr" checked>
                                            <span class="form-check-label">Show generated KHQR / branch payment QR</span>
                                        </label>
                                        <label class="form-check form-switch mb-0">
                                            <input class="form-check-input designer-control" type="checkbox" id="showSignatureLine" checked>
                                            <span class="form-check-label">Show authorized signature line</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card editor-section" data-editor-content="guides">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Bleed, Trim & Safe Guides</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-check form-switch mb-2">
                                <input class="form-check-input designer-control" type="checkbox" id="showBleedGuide" checked>
                                <span class="form-check-label">Show bleed border</span>
                            </label>
                            <label class="form-check form-switch mb-2">
                                <input class="form-check-input designer-control" type="checkbox" id="showCutLines" checked>
                                <span class="form-check-label">Show CR80 cut lines</span>
                            </label>
                            <label class="form-check form-switch mb-0">
                                <input class="form-check-input designer-control" type="checkbox" id="showSafeZone">
                                <span class="form-check-label">Show safe zone</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-7">
                    <div class="editor-stage">
                        <div class="editor-export-toolbar">
                            <button type="button" id="zoomOut"><i data-feather="zoom-out"></i></button>
                            <strong><span id="zoomValue">120</span>%</strong>
                            <button type="button" id="zoomIn"><i data-feather="zoom-in"></i></button>
                            <button type="button" class="export-btn" id="exportPng"><i data-feather="download"></i> Export 300DPI PNG</button>
                            <button type="button" class="export-btn" id="exportPdf"><i data-feather="file-text"></i> Export PDF</button>
                        </div>
                        @php
                            $previewEmployee = collect($employees)->first() ?? [
                                'name' => 'នេម ភីនី',
                                'english_name' => 'NEM PINI',
                                'employee_code' => 'KY0540',
                                'position_khmer' => 'អ្នកលក់',
                                'post' => 'អ្នកលក់',
                                'department' => 'កម្មដា',
                                'branch' => $company?->name ?? 'គ្នាយើង',
                                'photo_url' => asset('assets/images/img.png'),
                                'branch_logo_url' => $companyLogo,
                                'khqr_account_id' => 'KY0540',
                            ];
                        @endphp
                        <div id="editorPreview">
                            <div class="preview-card-column">
                                <div class="preview-label"><i data-feather="credit-card"></i> FRONT SIDE PREVIEW</div>
                                <div class="id-card-wrap">
                                    <div class="id-card id-card-front" style="width:71mm;height:103mm;">
                                        <div class="id-side" style="background:#f59e0b">
                                            <span class="side-logo-badge">
                                                @if(!empty($previewEmployee['branch_logo_url']))
                                                    <img src="{{ $previewEmployee['branch_logo_url'] }}" alt="">
                                                @else
                                                    <span>DHR</span>
                                                @endif
                                            </span>
                                            <span class="side-branch-name">{{ $formatBranchName($previewEmployee['branch'] ?? '') }}</span>
                                            <span class="side-logo-badge side-logo-badge-bottom">
                                                @if(!empty($previewEmployee['branch_logo_url']))
                                                    <img src="{{ $previewEmployee['branch_logo_url'] }}" alt="">
                                                @else
                                                    <span>DHR</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="id-body">
                                            <img class="id-photo" src="{{ $previewEmployee['photo_url'] ?? asset('assets/images/img.png') }}" alt="">
                                            <div class="id-details id-details-khmer">
                                                <div>
                                                    <span>ឈ្មោះ:</span>
                                                    <strong>
                                                        {{ $previewEmployee['name'] ?? 'នេម ភីនី' }}
                                                        @if(!empty($previewEmployee['english_name']))
                                                            <small>{{ $previewEmployee['english_name'] }}</small>
                                                        @endif
                                                    </strong>
                                                </div>
                                                <div><span>មុខតំណែង:</span><strong>{{ $previewEmployee['position_khmer'] ?? ($previewEmployee['post'] ?? '') }}</strong></div>
                                                <div><span>សាខា:</span><strong>{{ $formatBranchName($previewEmployee['branch'] ?? '') }}</strong></div>
                                                <div><span>ផ្នែក:</span><strong>{{ $previewEmployee['department'] ?? '' }}</strong></div>
                                            </div>
                                            <div class="id-code">
                                                <div class="barcode-visual barcode-fallback" aria-hidden="true"></div>
                                                <small>ID No : <b>{{ $previewEmployee['employee_code'] ?? 'KY0540' }}</b></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-card-column">
                                <div class="preview-label"><i data-feather="grid"></i> BACK SIDE PREVIEW</div>
                                <div class="id-card-wrap">
                                    <div class="id-card id-card-back template-khmer_gold template-kneayerng-amber-back" style="width:71mm;height:103mm;">
                                        <div class="back-brand-row {{ !empty($previewEmployee['branch_logo_url']) ? '' : 'no-logo' }}">
                                            @if(!empty($previewEmployee['branch_logo_url']))
                                                <img class="id-logo back-branch-logo" src="{{ $previewEmployee['branch_logo_url'] }}" alt="">
                                            @endif
                                            <div class="back-brand-copy">
                                                <div class="back-title" style="color:#f59e0b">{{ $formatBranchName($previewEmployee['branch'] ?? ($company?->name ?? 'គ្នាយើង')) }}</div>
                                                <div class="text-muted small">Scan. Pay. Done.</div>
                                            </div>
                                        </div>
                                        <div class="payment-grid">
                                            <div class="payment-box">
                                                <div class="payment-qr-frame"><div class="text-muted small">{{ $previewEmployee['khqr_account_id'] ?? ($previewEmployee['employee_code'] ?? 'KY0540') }}</div></div>
                                                <strong>KHQR</strong>
                                            </div>
                                            <div class="payment-box telegram-payment-box">
                                                <div class="payment-qr-frame"><div class="khqr-generated" data-value="https://t.me/kneayerngofficialbot" data-qr-px="103"></div></div>
                                                <strong>TELEGRAM QR</strong>
                                            </div>
                                        </div>
                                        <div class="back-contact">
                                            <strong class="back-contact-title">Contact Us:</strong>
                                            <div class="back-contact-row" style="--contact-color:#10b981"><span class="back-contact-icon">W</span><span>Website: <a href="http://kneayerng.com">http://kneayerng.com</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook: <a href="https://www.facebook.com/kystorecambodia">https://www.facebook.com/kystorecambodia</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook (Official): <a href="https://www.facebook.com/Knea">https://www.facebook.com/Knea</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#ec4899"><span class="back-contact-icon">IG</span><span>Instagram: <a href="https://www.instagram.com/kneayerngvip.official/">https://www.instagram.com/kneayerngvip.official/</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#0ea5e9"><span class="back-contact-icon">T</span><span>Telegram: <a href="https://t.me/kneayerngofficialbot">https://t.me/kneayerngofficialbot</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#14b8a6"><span class="back-contact-icon">P</span><span>Phone: <b>16910505</b></span></div>
                                        </div>
                                        <div class="back-note">If found, please return this card to គ្នាយើង.<br>Holder: {{ $previewEmployee['name'] ?? 'Employee' }}</div>
                                        <div class="signature-line">AUTHORIZED STAMP & SIGNATURE</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="studio-pane no-print" id="studio-batch">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="card-title mb-0">Employee Batch Roster</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="batchSelectAll">Select All</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="batchDeselectAll">Deselect All</button>
                        <button type="button" class="btn btn-primary btn-sm" id="addLocalEmployee">Add Local Row</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="table-responsive batch-table-wrap">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>Print</th>
                                        <th>Photo URL</th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Position KH / EN</th>
                                        <th>Department / Branch</th>
                                        <th>Emergency / Blood</th>
                                        <th>KHQR</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="batchEmployeeRows"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label">Paste CSV / Excel Rows</label>
                            <textarea class="form-control font-monospace" id="csvImportText" rows="12" placeholder="KY0541, Name Khmer, Name English, Position Khmer, Branch, Phone, Photo URL, KHQR Account ID"></textarea>
                            <button type="button" class="btn btn-success mt-2" id="importCsvRows">
                                <i class="link-icon" data-feather="upload"></i> Import Rows
                            </button>
                            <p class="text-muted mt-2 mb-0">Format: ID, Khmer Name, English Name, Position Khmer, Branch, Phone, Photo URL, KHQR Account ID.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="studio-pane no-print" id="studio-a4print">
        <div class="row g-3">
            <div class="col-xl-3 col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="card-title mb-0">{{ __('index.select_employees') }}</h6>
                        <div>
                            <button type="button" class="btn btn-outline-info btn-xs" data-bs-toggle="collapse" data-bs-target="#employeeFilterCollapse" aria-expanded="false" aria-controls="employeeFilterCollapse">Filter</button>
                            <button type="button" class="btn btn-outline-primary btn-xs" id="selectAllEmployees">{{ __('index.select_all') }}</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs" id="clearEmployees">{{ __('index.clear') }}</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control mb-3" id="employeeSearch" placeholder="{{ __('index.search') }}">
                        <div class="collapse mb-3" id="employeeFilterCollapse">
                            <div class="employee-filter-panel">
                                <select class="form-select form-select-sm" id="employeeBranchFilter">
                                    <option value="">All Branches</option>
                                </select>
                                <select class="form-select form-select-sm" id="employeeDepartmentFilter">
                                    <option value="">All Departments</option>
                                </select>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearEmployeeFilters">{{ __('index.clear') }}</button>
                            </div>
                        </div>
                        <div class="employee-picker"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-9 col-lg-8">
                <div class="a4-studio-board">
                    <div class="a4-studio-head">
                        <div class="a4-studio-title">
                            <span class="a4-studio-icon"><i data-feather="layout"></i></span>
                            <div>
                                <h4>A4 Print Studio Preview <strong><span id="a4SelectedBadge">{{ count($employees) }}</span> Cards Selected</strong></h4>
                                <p>Calibrated A4 layout grid matching exact ID card size, print margins & crop guides.</p>
                            </div>
                        </div>
                    </div>

                    <div class="a4-toolbar-row">
                        <div class="a4-segmented">
                            <button type="button" class="active" data-a4-mode="front_back" data-a4-pair="top_bottom">Stacked Pairs (2×2)</button>
                            <button type="button" data-a4-mode="front_back" data-a4-pair="side_by_side">Side-by-Side</button>
                            <button type="button" data-a4-mode="duplex">Duplex 2-Page</button>
                            <button type="button" data-a4-mode="front">Fronts Only</button>
                        </div>

                        <select class="form-select a4-grid-select" id="cardsPerPage">
                            <option value="4" selected>2 × 2 Grid (4 Cards / A4 Sheet)</option>
                            <option value="6">2 × 3 Grid (6 Cards / A4 Sheet)</option>
                        </select>

                        <button type="button" class="a4-toggle active" id="toggleMarginIndex"><i data-feather="list"></i> Margin List ON</button>
                        <button type="button" class="a4-toggle active" id="toggleCutMarks"><i data-feather="check-circle"></i> Cut Marks ON</button>
                        <button type="button" class="a4-download-btn" id="downloadA4Pdf"><i data-feather="download"></i> Download 300DPI PDF</button>
                        <button type="button" class="a4-print-now" id="printA4Now"><i data-feather="printer"></i> Print A4 Now (Ctrl+P)</button>
                    </div>

                    <div class="a4-calibration-row">
                        <label>Card Print Scale:</label>
                        <input type="range" id="a4CardScale" min="80" max="120" value="100">
                        <strong><span id="a4ScaleValue">100</span>%</strong>
                        <label>Width:</label>
                        <input type="number" class="form-control print-layout-control" id="a4CardWidthMm" value="71" min="20" max="210" step="0.01">
                        <span>mm</span>
                        <label>Height:</label>
                        <input type="number" class="form-control print-layout-control" id="a4CardHeightMm" value="103" min="20" max="297" step="0.01">
                        <span>mm</span>
                        <label>Top Margin:</label>
                        <input type="number" class="form-control print-layout-control" id="marginTopMm" value="12">
                        <span>mm</span>
                        <label>Left Margin:</label>
                        <input type="number" class="form-control print-layout-control" id="marginLeftMm" value="16">
                        <span>mm</span>
                        <label>Col Gap:</label>
                        <input type="number" class="form-control print-layout-control" id="gapXMm" value="10">
                        <span>mm</span>
                        <label>Row Gap:</label>
                        <input type="number" class="form-control print-layout-control" id="gapYMm" value="10">
                        <span>mm</span>
                        <span class="a4-spec">Default: 71mm × 103mm | Bleed: 1.5mm</span>
                    </div>

                    <select class="d-none" id="printMode">
                        <option value="front_back" selected>{{ __('index.front_back') }}</option>
                        <option value="front">{{ __('index.front_only') }}</option>
                        <option value="back">{{ __('index.back_only') }}</option>
                        <option value="duplex">Duplex 2-Page</option>
                    </select>
                    <select class="d-none print-layout-control" id="pairMode">
                        <option value="top_bottom" selected>Top-Bottom Stacked Pairs</option>
                        <option value="side_by_side">Side By Side</option>
                    </select>
                    <input type="color" class="d-none" id="cardColor" value="#0B172A">
                    <input type="color" class="d-none" id="accentColor" value="#C59B27">
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ __('index.a4_print_preview') }}</h6>
                    </div>
                    <div class="card-body preview-shell">
                        <div id="printArea">
                            @foreach(collect($employees)->chunk(2) as $pageEmployees)
                                <div class="a4-page">
                                    <div class="a4-index">
                                        <strong>ID</strong>
                                        @foreach($pageEmployees as $employee)
                                            <div>{{ $employee['employee_code'] ?? '' }}</div>
                                        @endforeach
                                    </div>
                                    <div class="a4-grid" style="margin-top:10mm;margin-left:16mm;column-gap:10mm;row-gap:10mm;">
                                        @foreach($pageEmployees as $employee)
                                            <div class="id-card-wrap">
                                                <div class="id-card id-card-front" style="width:71mm;height:103mm;">
                                                    <div class="id-side" style="background:#f59e0b">
                                                        <span class="side-logo-badge">
                                                            @if(!empty($employee['branch_logo_url']))
                                                                <img src="{{ $employee['branch_logo_url'] }}" alt="">
                                                            @else
                                                                <span>DHR</span>
                                                            @endif
                                                        </span>
                                                        <span class="side-branch-name">{{ $formatBranchName($employee['branch'] ?? '') }}</span>
                                                        <span class="side-logo-badge side-logo-badge-bottom">
                                                            @if(!empty($employee['branch_logo_url']))
                                                                <img src="{{ $employee['branch_logo_url'] }}" alt="">
                                                            @else
                                                                <span>DHR</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="id-body">
                                                        <img class="id-photo" src="{{ $employee['photo_url'] ?? asset('assets/images/img.png') }}" alt="">
                                                        <div class="id-details id-details-khmer">
                                                            <div>
                                                                <span>ឈ្មោះ:</span>
                                                                <strong>
                                                                    {{ $employee['name'] ?? 'Employee' }}
                                                                    @if(!empty($employee['english_name']))
                                                                        <small>{{ $employee['english_name'] }}</small>
                                                                    @endif
                                                                </strong>
                                                            </div>
                                                            <div><span>មុខតំណែង:</span><strong>{{ $employee['position_khmer'] ?? ($employee['post'] ?? '') }}</strong></div>
                                                            <div><span>សាខា:</span><strong>{{ $formatBranchName($employee['branch'] ?? '') }}</strong></div>
                                                            <div><span>ផ្នែក:</span><strong>{{ $employee['department'] ?? '' }}</strong></div>
                                                        </div>
                                                        <div class="id-code">
                                                            <div class="barcode-visual"><svg class="barcode-target" data-value="{{ $employee['employee_code'] ?? '' }}"></svg></div>
                                                            <small>ID No : <b>{{ $employee['employee_code'] ?? '' }}</b></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @foreach($pageEmployees as $employee)
                                            <div class="id-card-wrap">
                                                <div class="id-card id-card-back template-khmer_gold template-kneayerng-amber-back" style="width:71mm;height:103mm;">
                                                    <div class="back-brand-row {{ !empty($employee['branch_logo_url']) ? '' : 'no-logo' }}">
                                                        @if(!empty($employee['branch_logo_url']))
                                                            <img class="id-logo back-branch-logo" src="{{ $employee['branch_logo_url'] }}" alt="">
                                                        @endif
                                                        <div class="back-brand-copy">
                                                            <div class="back-title" style="color:#f59e0b">{{ $formatBranchName($employee['branch'] ?? '') }}</div>
                                                            <div class="text-muted small">Scan. Pay. Done.</div>
                                                        </div>
                                                    </div>
                                                    <div class="payment-grid">
                                                        <div class="payment-box">
                                                            <div class="payment-qr-frame">
                                                                <div class="text-muted small">{{ $employee['khqr_account_id'] ?? ($employee['employee_code'] ?? '') }}</div>
                                                            </div>
                                                            <strong>KHQR</strong>
                                                        </div>
                                                        <div class="payment-box telegram-payment-box">
                                                            <div class="payment-qr-frame"><div class="khqr-generated" data-value="https://t.me/kneayerngofficialbot" data-qr-px="103"></div></div>
                                                            <strong>TELEGRAM QR</strong>
                                                        </div>
                                                    </div>
                                                    <div class="back-contact">
                                                        <strong class="back-contact-title">Contact Us:</strong>
                                                        <div class="back-contact-row" style="--contact-color:#10b981"><span class="back-contact-icon">W</span><span>Website: <a href="http://kneayerng.com">http://kneayerng.com</a></span></div>
                                                        <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook: <a href="https://www.facebook.com/kystorecambodia">https://www.facebook.com/kystorecambodia</a></span></div>
                                                        <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook (Official): <a href="https://www.facebook.com/Knea">https://www.facebook.com/Knea</a></span></div>
                                                        <div class="back-contact-row" style="--contact-color:#ec4899"><span class="back-contact-icon">IG</span><span>Instagram: <a href="https://www.instagram.com/kneayerngvip.official/">https://www.instagram.com/kneayerngvip.official/</a></span></div>
                                                        <div class="back-contact-row" style="--contact-color:#0ea5e9"><span class="back-contact-icon">T</span><span>Telegram: <a href="https://t.me/kneayerngofficialbot">https://t.me/kneayerngofficialbot</a></span></div>
                                                        <div class="back-contact-row" style="--contact-color:#14b8a6"><span class="back-contact-icon">P</span><span>Phone: <b>16910505</b></span></div>
                                                    </div>
                                                    <div class="back-note">If found, please return this card to គ្នាយើង.<br>Holder: {{ $employee['name'] ?? 'Employee' }}</div>
                                                    <div class="signature-line">AUTHORIZED STAMP & SIGNATURE</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="studio-pane no-print" id="studio-laravel">
            <div class="code-export-shell">
                <div class="code-export-head">
                    <div>
                        <h5><i data-feather="code"></i> គ្នាយើង Laravel Code Export</h5>
                        <p>Direct copy-paste Blade views, Controllers, Routes & CSS for your Laravel HR Management system.</p>
                    </div>
                    <button type="button" class="copy-code-btn" id="copyCodeSnippet">
                        <i data-feather="copy"></i> Copy Code Snippet
                    </button>
                </div>
                <div class="code-tabs">
                    <button type="button" class="active" data-code-tab="blade"><i data-feather="file-text"></i> id_cards/print_a4.blade.php</button>
                    <button type="button" data-code-tab="controller"><i data-feather="terminal"></i> IDCardController.php</button>
                    <button type="button" data-code-tab="route"><i data-feather="layers"></i> routes/web.php</button>
                    <button type="button" data-code-tab="css"><i data-feather="layout"></i> print.css</button>
                </div>
                <pre class="code-panel"><code id="laravelCodeOutput"></code></pre>
            </div>
        </div>

        <div class="print-only">
            <div id="printAreaForPaper">
                @foreach(collect($employees)->chunk(2) as $pageEmployees)
                    <div class="a4-page">
                        <div class="a4-index">
                            <strong>ID</strong>
                            @foreach($pageEmployees as $employee)
                                <div>{{ $employee['employee_code'] ?? '' }}</div>
                            @endforeach
                        </div>
                        <div class="a4-grid" style="margin-top:10mm;margin-left:16mm;column-gap:10mm;row-gap:10mm;">
                            @foreach($pageEmployees as $employee)
                                <div class="id-card-wrap">
                                    <div class="id-card id-card-front" style="width:71mm;height:103mm;">
                                        <div class="id-side" style="background:#f59e0b">
                                            <span class="side-logo-badge">
                                                @if(!empty($employee['branch_logo_url']))
                                                    <img src="{{ $employee['branch_logo_url'] }}" alt="">
                                                @else
                                                    <span>DHR</span>
                                                @endif
                                            </span>
                                            <span class="side-branch-name">{{ $formatBranchName($employee['branch'] ?? '') }}</span>
                                            <span class="side-logo-badge side-logo-badge-bottom">
                                                @if(!empty($employee['branch_logo_url']))
                                                    <img src="{{ $employee['branch_logo_url'] }}" alt="">
                                                @else
                                                    <span>DHR</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="id-body">
                                            <img class="id-photo" src="{{ $employee['photo_url'] ?? asset('assets/images/img.png') }}" alt="">
                                            <div class="id-details id-details-khmer">
                                                <div>
                                                    <span>ឈ្មោះ:</span>
                                                    <strong>
                                                        {{ $employee['name'] ?? 'Employee' }}
                                                        @if(!empty($employee['english_name']))
                                                            <small>{{ $employee['english_name'] }}</small>
                                                        @endif
                                                    </strong>
                                                </div>
                                                <div><span>មុខតំណែង:</span><strong>{{ $employee['position_khmer'] ?? ($employee['post'] ?? '') }}</strong></div>
                                                <div><span>សាខា:</span><strong>{{ $formatBranchName($employee['branch'] ?? '') }}</strong></div>
                                                <div><span>ផ្នែក:</span><strong>{{ $employee['department'] ?? '' }}</strong></div>
                                            </div>
                                            <div class="id-code">
                                                <div class="barcode-visual"><svg class="barcode-target" data-value="{{ $employee['employee_code'] ?? '' }}"></svg></div>
                                                <small>ID No : <b>{{ $employee['employee_code'] ?? '' }}</b></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @foreach($pageEmployees as $employee)
                                <div class="id-card-wrap">
                                    <div class="id-card id-card-back template-khmer_gold template-kneayerng-amber-back" style="width:71mm;height:103mm;">
                                        <div class="back-brand-row {{ !empty($employee['branch_logo_url']) ? '' : 'no-logo' }}">
                                            @if(!empty($employee['branch_logo_url']))
                                                <img class="id-logo back-branch-logo" src="{{ $employee['branch_logo_url'] }}" alt="">
                                            @endif
                                            <div class="back-brand-copy">
                                                <div class="back-title" style="color:#f59e0b">{{ $formatBranchName($employee['branch'] ?? '') }}</div>
                                                <div class="text-muted small">Scan. Pay. Done.</div>
                                            </div>
                                        </div>
                                        <div class="payment-grid">
                                            <div class="payment-box">
                                                <div class="payment-qr-frame"><div class="text-muted small">{{ $employee['khqr_account_id'] ?? ($employee['employee_code'] ?? '') }}</div></div>
                                                <strong>KHQR</strong>
                                            </div>
                                            <div class="payment-box telegram-payment-box">
                                                <div class="payment-qr-frame"><div class="khqr-generated" data-value="https://t.me/kneayerngofficialbot" data-qr-px="103"></div></div>
                                                <strong>TELEGRAM QR</strong>
                                            </div>
                                        </div>
                                        <div class="back-contact">
                                            <strong class="back-contact-title">Contact Us:</strong>
                                            <div class="back-contact-row" style="--contact-color:#10b981"><span class="back-contact-icon">W</span><span>Website: <a href="http://kneayerng.com">http://kneayerng.com</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook: <a href="https://www.facebook.com/kystorecambodia">https://www.facebook.com/kystorecambodia</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook (Official): <a href="https://www.facebook.com/Knea">https://www.facebook.com/Knea</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#ec4899"><span class="back-contact-icon">IG</span><span>Instagram: <a href="https://www.instagram.com/kneayerngvip.official/">https://www.instagram.com/kneayerngvip.official/</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#0ea5e9"><span class="back-contact-icon">T</span><span>Telegram: <a href="https://t.me/kneayerngofficialbot">https://t.me/kneayerngofficialbot</a></span></div>
                                            <div class="back-contact-row" style="--contact-color:#14b8a6"><span class="back-contact-icon">P</span><span>Phone: <b>16910505</b></span></div>
                                        </div>
                                        <div class="back-note">If found, please return this card to គ្នាយើង.<br>Holder: {{ $employee['name'] ?? 'Employee' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        window.digitalHrsEmployees = @json($employees);
        window.digitalHrsDefaultPhoto = '{{ asset('assets/images/img.png') }}';
        window.digitalHrsEmployeeStoreUrl = '{{ route('admin.d-card-print.employees.store') }}';
        window.digitalHrsEmployeeBaseUrl = '{{ url('admin/d-card-print/employees') }}';
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

    <style>
        .d-card-studio {
            --d-card-brand: var(--primary-color, #0F766E);
            --d-card-brand-hover: var(--hover-color, #115E59);
            --d-card-brand-soft: rgba(15, 118, 110, .10);
            --d-card-card-color: var(--primary-color, #0F766E);
            --d-card-accent-color: #1f2937;
            min-height: calc(100vh - 170px);
            background: #f8fafc;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .studio-topbar {
            min-height: 58px;
            background: #fff;
            color: #1f2937;
            display: grid;
            grid-template-columns: minmax(280px, 1fr) auto auto;
            gap: 12px;
            align-items: center;
            padding: 8px 14px;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            position: relative;
            top: auto;
            z-index: 50;
        }

        .studio-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .studio-logo {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: var(--d-card-brand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: #fff;
            box-shadow: inset 0 -3px 0 rgba(15, 23, 42, .12);
            overflow: hidden;
            flex: 0 0 auto;
        }

        .studio-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #fff;
            padding: 4px;
        }

        .studio-title {
            font-size: 16px;
            font-weight: 900;
            line-height: 1.2;
            white-space: nowrap;
        }

        .studio-title span {
            margin-left: 8px;
            font-size: 11px;
            color: var(--d-card-brand);
            border: 1px solid rgba(15, 118, 110, .25);
            border-radius: 6px;
            padding: 2px 7px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            background: var(--d-card-brand-soft);
        }

        .studio-subtitle {
            color: #64748b;
            font-size: 12px;
            margin-top: 2px;
        }

        .studio-main-tabs {
            display: flex;
            gap: 6px;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px;
        }

        .studio-main-tabs button {
            border: 0;
            background: transparent;
            color: #475569;
            min-height: 34px;
            border-radius: 6px;
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .studio-main-tabs button.active {
            background: var(--d-card-brand);
            color: #fff;
            box-shadow: 0 4px 10px rgba(15, 118, 110, .22);
        }

        .studio-main-tabs strong {
            background: rgba(255, 255, 255, .18);
            color: #fff;
            border-radius: 999px;
            padding: 2px 8px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
        }

        .studio-print-btn {
            border: 0;
            border-radius: 8px;
            background: var(--d-card-brand);
            color: #fff;
            height: 36px;
            padding: 0 13px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            box-shadow: 0 6px 12px rgba(15, 118, 110, .18);
            white-space: nowrap;
        }

        .studio-print-btn:hover {
            background: var(--d-card-brand-hover);
        }

        .studio-panel-head {
            height: 86px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }

        .studio-panel-head h5 {
            font-size: 20px;
            font-weight: 900;
            margin: 0 0 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .studio-panel-head h5 svg {
            color: var(--d-card-brand);
        }

        .studio-panel-head p {
            margin: 0;
            color: #64748b;
        }

        .studio-subtabs {
            height: 62px;
            display: flex;
            overflow-x: auto;
            gap: 4px;
            align-items: end;
            padding: 0 12px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }

        .studio-subtabs button {
            border: 0;
            background: transparent;
            padding: 16px 12px;
            border-bottom: 3px solid transparent;
            color: #475569;
            font-weight: 800;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .studio-subtabs button.active {
            color: var(--d-card-brand);
            border-bottom-color: var(--d-card-brand);
        }

        #studio-editor > .row,
        #studio-batch,
        #studio-a4print,
        #studio-laravel {
            margin: 0;
        }

        #studio-editor > .row > .col-xl-4 {
            background: #fff;
            min-height: calc(100vh - 230px);
            padding: 0;
            border-right: 1px solid #dbe3ee;
            overflow-y: auto;
            max-height: calc(100vh - 230px);
        }

        #studio-editor > .row > .col-xl-8 {
            background: #f1f5f9;
            min-height: calc(100vh - 230px);
            padding: 24px;
        }

        #studio-editor .card {
            border: 0;
            box-shadow: none;
            border-radius: 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 0 !important;
        }

        #studio-editor .card-header {
            background: #fff;
            border-bottom: 0;
            padding: 18px 18px 6px;
        }

        #studio-editor .card-body {
            padding: 12px 18px 18px;
        }

        .customize-block {
            border: 1px solid #dbe3ee;
            border-radius: 8px;
            background: #f8fafc;
            padding: 14px;
        }

        .customize-block-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1f2937;
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .customize-block-title svg {
            width: 16px;
            height: 16px;
            color: var(--d-card-brand);
        }

        .d-card-tabs {
            gap: 8px;
        }

        .d-card-tabs .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 6px;
        }

        .studio-pane {
            display: none;
        }

        .studio-pane.active {
            display: block;
        }

        .editor-section {
            display: none;
        }

        .editor-section.active {
            display: block;
        }

        .template-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .template-intro {
            color: #475569;
            font-size: 15px;
            line-height: 1.45;
            margin: 0 0 16px;
        }

        .template-color-tool {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .template-color-tool label {
            min-height: 52px;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            background: #f8fafc;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #334155;
            font-size: 12px;
            font-weight: 900;
        }

        .template-color-tool input {
            width: 44px;
            height: 34px;
            padding: 2px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            cursor: pointer;
        }

        .front-field-toggles {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 12px;
            padding: 12px;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            background: #f8fafc;
        }

        .front-field-toggles .form-check {
            min-height: 26px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: #334155;
        }

        .template-preset {
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 14px;
            padding: 18px;
            font-weight: 700;
            color: #334155;
            min-height: 114px;
            text-align: left;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
            display: grid;
            grid-template-columns: 48px 1fr 18px;
            gap: 14px;
            align-items: center;
            width: 100%;
        }

        .template-preset:hover,
        .template-preset:focus-visible {
            border-color: var(--d-card-brand);
            box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .25), 0 3px 10px rgba(15, 23, 42, .08);
        }

        .template-preset.active {
            border-color: var(--d-card-brand);
            background: var(--d-card-brand-soft);
            color: var(--d-card-brand-hover);
            box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .28);
        }

        .template-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(15, 23, 42, .18);
        }

        .template-copy strong {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #1f2937;
            font-size: 16px;
            line-height: 1.2;
        }

        .template-copy strong em {
            font-style: normal;
            font-size: 11px;
            color: #64748b;
            background: #eef2f7;
            border-radius: 5px;
            padding: 4px 8px;
        }

        .template-copy small {
            display: block;
            color: #64748b;
            font-weight: 500;
            margin-top: 5px;
        }

        .swatches {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .swatches b {
            width: 13px;
            height: 13px;
            border-radius: 50%;
            border: 1px solid rgba(15, 23, 42, .15);
        }

        .template-check {
            color: var(--d-card-brand);
            opacity: 0;
        }

        .template-preset.active .template-check {
            opacity: 1;
        }

        .editor-stage {
            min-height: calc(100vh - 278px);
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            border: 0;
            position: relative;
            padding: 110px 28px 40px;
        }

        #editorPreview {
            display: flex;
            gap: 48px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            min-height: 100mm;
            transform-origin: center center;
        }

        .preview-card-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .preview-label {
            background: #fff;
            color: #334155;
            border-radius: 9px;
            padding: 8px 18px;
            font-weight: 900;
            letter-spacing: .06em;
            box-shadow: 0 2px 5px rgba(15, 23, 42, .08);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .preview-label svg {
            color: var(--d-card-brand);
            width: 18px;
            height: 18px;
        }

        .editor-export-toolbar {
            position: absolute;
            top: 24px;
            right: 24px;
            min-height: 60px;
            background: #fff;
            border: 1px solid #dbe3ee;
            border-radius: 14px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .12);
        }

        .editor-export-toolbar button {
            border: 0;
            background: transparent;
            color: #334155;
            font-weight: 900;
        }

        .editor-export-toolbar strong {
            color: #334155;
            min-width: 52px;
            text-align: center;
        }

        .editor-export-toolbar .export-btn {
            background: var(--d-card-brand);
            color: #fff;
            border-radius: 11px;
            padding: 11px 18px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #editorPreview .id-card-wrap::before,
        #editorPreview .id-card-wrap::after {
            display: none;
        }

        #editorPreview .id-card-wrap {
            padding: 0;
        }

        #editorPreview .id-card-wrap::before {
            content: "FRONT SIDE PREVIEW";
        }

        @media (max-width: 1400px) {
            .studio-topbar {
                grid-template-columns: 1fr;
                gap: 12px;
                position: relative;
            }

            .studio-main-tabs {
                overflow-x: auto;
            }
        }

        @media (max-width: 1199px) {
            .studio-topbar {
                padding: 8px 14px;
            }

            .studio-title {
                white-space: normal;
                font-size: 16px;
            }

            #studio-editor > .row > .col-xl-4,
            #studio-editor > .row > .col-xl-8 {
                min-height: auto;
                max-height: none;
            }

            #studio-editor > .row > .col-xl-4 {
                border-right: 0;
                border-bottom: 1px solid #dbe3ee;
            }

            .editor-stage {
                min-height: 620px;
                padding: 96px 16px 32px;
            }

            #editorPreview {
                gap: 28px;
            }
        }

        @media (max-width: 767px) {
            .studio-brand {
                align-items: flex-start;
            }

            .studio-logo {
                width: 38px;
                height: 38px;
                border-radius: 8px;
                font-size: 12px;
                flex: 0 0 auto;
            }

            .studio-title {
                font-size: 16px;
            }

            .studio-title span {
                display: inline-block;
                margin: 6px 0 0;
            }

            .studio-subtitle,
            .studio-panel-head p {
                font-size: 12px;
            }

            .studio-main-tabs,
            .studio-subtabs {
                scrollbar-width: thin;
            }

            .studio-main-tabs button {
                min-height: 36px;
                padding: 0 10px;
                font-size: 13px;
            }

            .studio-print-btn {
                width: 100%;
                justify-content: center;
            }

            .studio-panel-head {
                height: auto;
                min-height: 74px;
                gap: 10px;
                padding: 12px 14px;
            }

            .studio-panel-head h5 {
                font-size: 17px;
            }

            .template-preset {
                min-height: 96px;
                grid-template-columns: 40px minmax(0, 1fr) 16px;
                gap: 10px;
                padding: 13px;
            }

            .template-color-tool {
                grid-template-columns: 1fr;
            }

            .front-field-toggles {
                grid-template-columns: 1fr;
            }

            .template-icon {
                width: 40px;
                height: 40px;
                border-radius: 8px;
            }

            .template-copy strong {
                font-size: 14px;
            }

            .template-copy small {
                font-size: 12px;
            }

            .editor-export-toolbar {
                position: sticky;
                top: 8px;
                right: auto;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                min-height: auto;
                margin-bottom: 16px;
            }

            .editor-stage {
                display: block;
                min-height: auto;
                padding: 12px;
            }

            #editorPreview {
                transform: none !important;
                min-height: 0;
                gap: 20px;
            }

            .preview-card-column {
                width: 100%;
            }

            .preview-label {
                max-width: 100%;
                font-size: 12px;
                padding: 7px 12px;
            }
        }

        .batch-table-wrap {
            max-height: 650px;
            overflow: auto;
        }

        .batch-edit {
            min-width: 120px;
        }

        .batch-photo {
            width: 36px;
            height: 42px;
            object-fit: cover;
            border-radius: 4px;
            background: #f3f4f6;
        }

        .a4-studio-board {
            background: #fff;
            border-bottom: 1px solid #dbe3ee;
            margin-bottom: 16px;
        }

        .a4-studio-head {
            padding: 18px 0 12px;
        }

        .a4-studio-title {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .a4-studio-icon {
            width: 56px;
            height: 56px;
            border: 1px solid rgba(15, 118, 110, .25);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--d-card-brand);
            background: var(--d-card-brand-soft);
        }

        .a4-studio-icon svg {
            width: 26px;
            height: 26px;
        }

        .a4-studio-title h4 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            color: #172033;
        }

        .a4-studio-title h4 strong {
            margin-left: 14px;
            background: var(--d-card-brand-soft);
            color: var(--d-card-brand-hover);
            border-radius: 10px;
            padding: 6px 14px;
            font-size: 16px;
            vertical-align: middle;
        }

        .a4-studio-title p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 15px;
        }

        .a4-toolbar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0 18px;
            flex-wrap: wrap;
        }

        .a4-segmented {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            background: #eef2f7;
            border: 1px solid #d7e0ea;
            border-radius: 9px;
            padding: 6px;
        }

        .a4-segmented button,
        .a4-toggle,
        .a4-download-btn,
        .a4-print-now {
            min-height: 44px;
            border-radius: 9px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .a4-segmented button {
            border: 1px solid transparent;
            background: transparent;
            color: #334155;
            padding: 0 16px;
        }

        .a4-segmented button.active {
            background: #fff;
            color: var(--d-card-brand-hover);
            border-color: rgba(15, 118, 110, .28);
            box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
        }

        .a4-grid-select {
            width: 330px;
            min-height: 52px;
            border-radius: 9px;
            border-color: #cbd5e1;
            font-weight: 800;
            color: #334155;
        }

        .a4-toggle {
            border: 1px solid rgba(15, 118, 110, .32);
            background: #fff;
            color: var(--d-card-brand-hover);
            padding: 0 16px;
        }

        .a4-toggle:not(.active) {
            border-color: #cbd5e1;
            color: #64748b;
            background: #f8fafc;
        }

        .a4-download-btn {
            border: 0;
            background: #1f2937;
            color: #fff;
            padding: 0 18px;
        }

        .a4-print-now {
            border: 0;
            background: var(--d-card-brand);
            color: #fff;
            padding: 0 22px;
        }

        .a4-calibration-row {
            min-height: 56px;
            margin: 0 -24px;
            padding: 12px 24px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #dbe3ee;
            background: #f8fafc;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            color: #475569;
            font-weight: 700;
        }

        .a4-calibration-row input[type="range"] {
            width: 140px;
            accent-color: var(--d-card-brand);
        }

        .a4-calibration-row input[type="number"] {
            width: 74px;
            min-height: 34px;
            padding: 4px 10px;
            border-radius: 7px;
            font-weight: 700;
            text-align: center;
        }

        .a4-calibration-row strong {
            color: var(--d-card-brand-hover);
            min-width: 50px;
        }

        .a4-spec {
            margin-left: auto;
            color: #64748b;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 13px;
        }

        @media (max-width: 1200px) {
            .a4-grid-select {
                width: 100%;
            }

            .a4-spec {
                margin-left: 0;
                width: 100%;
            }
        }

        .code-export-shell {
            margin: 24px;
            background: #fff;
            border: 1px solid #dbe3ee;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .code-export-head {
            min-height: 86px;
            padding: 18px 22px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .code-export-head h5 {
            margin: 0 0 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            color: #1f2937;
        }

        .code-export-head h5 svg {
            color: var(--d-card-brand);
        }

        .code-export-head p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
        }

        .copy-code-btn {
            border: 0;
            background: var(--d-card-brand);
            color: #fff;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .code-tabs {
            display: flex;
            gap: 8px;
            padding: 10px 16px 0;
            background: #eef2f7;
            border-bottom: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .code-tabs button {
            border: 0;
            background: transparent;
            color: #475569;
            padding: 12px 14px;
            border-radius: 9px 9px 0 0;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .code-tabs button.active {
            background: #fff;
            color: var(--d-card-brand-hover);
            box-shadow: inset 0 3px 0 var(--d-card-brand);
        }

        .code-panel {
            min-height: calc(100vh - 270px);
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 0;
            padding: 22px;
            white-space: pre-wrap;
            margin: 0;
            overflow: auto;
            font-size: 12px;
            line-height: 1.6;
        }

        .safe-zone-guide {
            position: absolute;
            inset: 4mm;
            border: 1px dashed #10b981;
            pointer-events: none;
            z-index: 4;
        }

        .bleed-guide {
            position: absolute;
            inset: 0.75mm;
            border: 1px dashed rgba(220, 38, 38, .75);
            pointer-events: none;
            z-index: 6;
        }

        .cut-line-guide {
            position: absolute;
            inset: 1.5mm;
            border: 1px solid rgba(100, 116, 139, .55);
            pointer-events: none;
            z-index: 5;
        }

        .d-card-studio .employee-picker {
            max-height: 620px;
            overflow-y: auto;
        }

        .d-card-studio .employee-filter-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            display: grid;
            gap: 8px;
            padding: 10px;
        }

        .d-card-studio .employee-option {
            display: grid;
            grid-template-columns: 18px 42px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 9px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: pointer;
            background: #fff;
            position: relative;
        }

        .d-card-studio .employee-option.is-selected {
            border-color: var(--d-card-brand);
            background: var(--d-card-brand-soft);
        }

        .d-card-studio .employee-option img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            background: #f3f4f6;
        }

        .d-card-studio .employee-photo-thumb {
            border-radius: 50%;
            height: 42px;
            position: relative;
            width: 42px;
        }

        .d-card-studio .employee-photo-thumb img {
            display: block;
        }

        .d-card-studio .employee-photo-hover {
            align-items: center;
            background: rgba(15, 23, 42, .68);
            border: 0;
            border-radius: 50%;
            bottom: 0;
            color: #fff;
            cursor: pointer;
            display: flex;
            font-size: 10px;
            font-weight: 800;
            justify-content: center;
            left: 0;
            opacity: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: opacity .15s ease;
        }

        .d-card-studio .employee-option:hover .employee-photo-hover,
        .d-card-studio .employee-option:focus-within .employee-photo-hover {
            opacity: 1;
        }

        .d-card-studio .employee-option-main {
            min-width: 0;
        }

        .d-card-studio .employee-option strong,
        .d-card-studio .employee-option small {
            display: block;
            line-height: 1.2;
        }

        .d-card-studio .employee-option small {
            color: #6b7280;
            margin-top: 3px;
        }

        .d-card-studio .employee-photo-quick {
            background: #fff;
            border: 1px solid #dbe3ee;
            border-radius: 7px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .14);
            display: flex;
            gap: 6px;
            left: 48px;
            opacity: 0;
            padding: 6px;
            pointer-events: none;
            position: absolute;
            top: 50%;
            transition: opacity .16s ease, transform .16s ease;
            transform: translateY(-50%) translateX(-4px);
            white-space: nowrap;
            z-index: 20;
        }

        .d-card-studio .employee-option:hover .employee-photo-quick,
        .d-card-studio .employee-option:focus-within .employee-photo-quick {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(-50%) translateX(0);
        }

        .d-card-studio .employee-photo-quick .btn {
            align-items: center;
            display: inline-flex;
            justify-content: center;
            min-height: 31px;
            padding: 4px 8px;
        }

        .d-card-studio .quick-photo-status {
            background: var(--d-card-brand);
            border-radius: 7px;
            color: #fff;
            display: none;
            font-size: 11px;
            font-weight: 800;
            padding: 6px 8px;
        }

        .preview-shell {
            background: #eef1f5;
            overflow: auto;
            max-height: 760px;
        }

        .a4-page {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            margin: 0 auto 18px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }

        .a4-grid {
            display: grid;
            grid-template-columns: repeat(2, max-content);
            align-content: start;
            justify-content: start;
            justify-items: center;
        }

        .a4-index {
            position: absolute;
            top: 10mm;
            left: 3mm;
            width: 11mm;
            font-size: 7px;
            line-height: 1.4;
            color: #334155;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            z-index: 2;
        }

        .a4-index strong {
            display: block;
            border-bottom: 1px solid #cbd5e1;
            margin-bottom: 2mm;
        }

        .a4-page.no-index .a4-index {
            display: none;
        }

        .a4-page.no-cut .id-card-wrap::before,
        .a4-page.no-cut .id-card-wrap::after {
            display: none;
        }

        .id-card-wrap {
            position: relative;
            padding: 1mm;
        }

        .id-card-wrap::before,
        .id-card-wrap::after {
            content: "";
            position: absolute;
            width: 6mm;
            height: 6mm;
            pointer-events: none;
        }

        .id-card-wrap::before {
            top: 0;
            left: 0;
            border-top: 1px solid #9ca3af;
            border-left: 1px solid #9ca3af;
        }

        .id-card-wrap::after {
            right: 0;
            bottom: 0;
            border-right: 1px solid #9ca3af;
            border-bottom: 1px solid #9ca3af;
        }

        .id-card {
            --card-text-scale: 1;
            width: 54mm;
            height: 86mm;
            overflow: hidden;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, .96) 0%, rgba(255, 250, 237, .92) 42%, rgba(239, 246, 255, .94) 100%),
                radial-gradient(circle at 86% 12%, rgba(245, 158, 11, .22), transparent 30%),
                radial-gradient(circle at 10% 92%, rgba(14, 165, 233, .16), transparent 34%);
            border: 1px solid #d9e0ea;
            border-radius: 3mm;
            position: relative;
            color: #111827;
            font-family: "Kantumruy Pro", "Noto Sans Khmer", Arial, sans-serif;
            box-shadow: 0 3px 10px rgba(15, 23, 42, .10);
        }

        .id-card::before,
        .id-card::after {
            content: "";
            inset: 0;
            pointer-events: none;
            position: absolute;
            z-index: 0;
        }

        .id-card::before {
            background:
                linear-gradient(110deg, rgba(255, 255, 255, .72) 0 18%, transparent 18% 100%),
                repeating-linear-gradient(135deg, rgba(255, 255, 255, .28) 0 1px, transparent 1px 7px);
            opacity: .5;
        }

        .id-card::after {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .28), transparent 42%),
                linear-gradient(90deg, transparent, rgba(15, 23, 42, .035));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .46);
        }

        .id-card > * {
            position: relative;
            z-index: 1;
        }

        .id-card.landscape {
            width: 86mm;
            height: 54mm;
        }

        .id-card-front {
            background:
                linear-gradient(145deg, #fffaf0 0%, #ffffff 46%, #eef7ff 100%),
                radial-gradient(circle at 86% 10%, rgba(31, 41, 55, .12), transparent 30%),
                radial-gradient(circle at 12% 92%, rgba(245, 158, 11, .20), transparent 36%);
            display: grid;
            grid-template-columns: 11.5mm 1fr;
        }

        .id-card-front.no-side-brand {
            grid-template-columns: 0 1fr;
        }

        .template-kneayerng_gold {
            --ky-navy: #0b172a;
            --ky-gold: #c59b27;
            --ky-light-gold: #f3d36b;
            background: var(--ky-navy);
            border: 0;
            border-radius: 16px;
            box-shadow: 0 12px 30px -10px rgba(0, 0, 0, .38);
            font-family: "Kantumruy Pro", "Noto Sans Khmer", Arial, sans-serif;
        }

        .template-kneayerng_gold.id-card-front {
            display: block;
        }

        .template-kneayerng_gold.id-card::before,
        .template-kneayerng_gold.id-card::after {
            display: none;
        }

        .template-kneayerng_gold .ky-card-art {
            display: block;
            inset: 0;
            height: 100%;
            pointer-events: none;
            position: absolute;
            width: 100%;
            z-index: 1;
        }

        .template-kneayerng_gold .ky-left-panel {
            align-items: center;
            bottom: 0;
            display: flex;
            flex-direction: column;
            left: 0;
            padding-top: var(--ky-left-pad-top, 3mm);
            pointer-events: none;
            position: absolute;
            top: 0;
            width: var(--ky-left-width, 14.2mm);
            z-index: 3;
        }

        .template-kneayerng_gold .ky-logo-circle {
            align-items: center;
            background: #fff;
            border: var(--ky-logo-border, .6mm) solid var(--ky-gold);
            border-radius: 50%;
            box-shadow: 0 var(--ky-shadow-y, 1.2mm) var(--ky-shadow-blur, 4mm) rgba(0, 0, 0, .28);
            color: var(--ky-light-gold);
            display: flex;
            flex-direction: column;
            height: var(--ky-logo-size, 11.2mm);
            justify-content: center;
            line-height: 1;
            overflow: hidden;
            width: var(--ky-logo-size, 11.2mm);
        }

        .template-kneayerng_gold .ky-logo-circle img {
            background: #fff;
            box-sizing: border-box;
            height: 100%;
            object-fit: contain;
            padding: .8mm;
            width: 100%;
        }

        .template-kneayerng_gold .ky-logo-circle b {
            font-family: "Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(5.5px * var(--card-text-scale));
            font-weight: 900;
        }

        .template-kneayerng_gold .ky-side-brand {
            align-items: center;
            display: flex;
            flex-direction: column;
            margin-top: 1mm;
            text-align: center;
        }

        .template-kneayerng_gold .ky-side-brand strong {
            color: var(--ky-light-gold);
            font-family: "Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(10.5px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.05;
        }

        .template-kneayerng_gold .ky-side-brand span {
            color: var(--ky-light-gold);
            font-family: Arial, sans-serif;
            font-size: calc(5.8px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .05em;
            margin-top: .6mm;
        }

        .template-kneayerng_gold .ky-side-brand small {
            border-top: .35mm solid rgba(245, 198, 67, .75);
            color: #e2e8f0;
            font-family: Arial, sans-serif;
            font-size: calc(4.8px * var(--card-text-scale));
            font-weight: 800;
            letter-spacing: .12em;
            margin-top: .6mm;
            padding-top: .5mm;
        }

        .template-kneayerng_gold .ky-side-vertical {
            color: var(--ky-light-gold);
            font-family: Arial, sans-serif;
            font-size: calc(8.2px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .18em;
            margin-top: var(--ky-side-vertical-top, 23mm);
            text-transform: uppercase;
            transform: rotate(-90deg);
            white-space: nowrap;
        }

        .template-kneayerng_gold .ky-dot-matrix {
            display: grid;
            gap: var(--ky-dot-gap, 1.3mm);
            grid-template-columns: repeat(4, var(--ky-dot-size, 1.2mm));
            margin-bottom: var(--ky-dot-bottom, 18mm);
            margin-top: auto;
            opacity: .32;
        }

        .template-kneayerng_gold .ky-dot-matrix span,
        .template-kneayerng_gold .ky-right-dots span {
            background: #cbd5e1;
            border-radius: 50%;
            display: block;
            height: var(--ky-dot-size, 1.2mm);
            width: var(--ky-dot-size, 1.2mm);
        }

        .template-kneayerng_gold .ky-right-dots {
            display: grid;
            gap: var(--ky-right-dot-gap, 1.5mm);
            grid-template-columns: repeat(6, var(--ky-dot-size, 1.2mm));
            opacity: .26;
            position: absolute;
            right: var(--ky-right-dots-right, 4mm);
            top: var(--ky-right-dots-top, 13.6mm);
            z-index: 2;
        }

        .template-kneayerng_gold .ky-front-content {
            align-items: center;
            bottom: 0;
            display: flex;
            flex-direction: column;
            left: var(--ky-left-width, 14.2mm);
            padding: var(--ky-front-pad-top, 2.8mm) var(--ky-front-pad-x, 2.3mm) var(--ky-front-pad-bottom, 1.5mm);
            position: absolute;
            right: var(--ky-front-right, 1.8mm);
            top: 0;
            z-index: 3;
        }

        .template-kneayerng_gold .ky-photo-frame {
            background: #e2e8f0;
            border: var(--ky-photo-border, .65mm) solid var(--ky-gold);
            border-radius: var(--ky-photo-radius, 4mm);
            box-shadow: 0 var(--ky-photo-shadow-y, 2mm) var(--ky-photo-shadow-blur, 5mm) rgba(15, 23, 42, .24);
            height: var(--ky-photo-height, 33.5mm);
            overflow: hidden;
            width: var(--ky-photo-width, 34mm);
        }

        .template-kneayerng_gold .ky-photo-frame img {
            border: 0;
            border-radius: 0;
            box-shadow: none;
            display: block;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            transform: none;
            width: 100%;
        }

        .template-kneayerng_gold .ky-name-block {
            margin-top: var(--ky-name-top, 1.25mm);
            text-align: center;
            width: 100%;
        }

        .template-kneayerng_gold .ky-name-block strong {
            color: var(--ky-navy);
            display: block;
            font-family: var(--employee-khmer-font, "Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif);
            font-size: calc(var(--employee-khmer-size, 18px) * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.05;
        }

        .template-kneayerng_gold .ky-name-block small {
            color: var(--ky-gold);
            display: block;
            font-family: var(--employee-english-font, Arial, sans-serif);
            font-size: calc(var(--employee-english-size, 8.8px) * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .08em;
            margin-top: var(--ky-english-top, .5mm);
            text-transform: uppercase;
        }

        .template-kneayerng_gold .ky-position-pill {
            align-items: center;
            background: var(--ky-navy);
            border: .35mm solid var(--ky-gold);
            border-radius: 999px;
            box-shadow: 0 1mm 2mm rgba(15, 23, 42, .16);
            color: var(--ky-light-gold);
            display: flex;
            gap: var(--ky-pill-gap, 1.7mm);
            justify-content: center;
            margin-top: var(--ky-pill-top, 1.05mm);
            min-height: var(--ky-pill-height, 4.1mm);
            padding: var(--ky-pill-pad-y, .35mm) var(--ky-pill-pad-x, 2.2mm);
            width: var(--ky-pill-width, 34mm);
        }

        .template-kneayerng_gold .ky-position-pill span {
            font-family: "Battambang", "Noto Sans Khmer", Arial, sans-serif;
            font-size: calc(7.4px * var(--card-text-scale));
            font-weight: 900;
        }

        .template-kneayerng_gold .ky-position-pill b {
            border-left: 1px solid var(--ky-gold);
            color: var(--ky-light-gold);
            font-family: Arial, sans-serif;
            font-size: calc(7.8px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .06em;
            padding-left: var(--ky-pill-divider-pad, 2mm);
            text-transform: uppercase;
        }

        .template-kneayerng_gold .ky-info-list {
            color: #0f172a;
            display: flex;
            flex-direction: column;
            gap: var(--ky-info-gap, .65mm);
            margin-top: var(--ky-info-top, 1.05mm);
            width: var(--ky-info-width, 36mm);
        }

        .template-kneayerng_gold .ky-info-row {
            align-items: center;
            border-bottom: 1px solid rgba(197, 155, 39, .55);
            display: grid;
            gap: var(--ky-info-row-gap, 1mm);
            grid-template-columns: var(--ky-info-icon-col, 4.2mm) var(--ky-info-label-col, 11mm) minmax(0, 1fr);
            min-height: var(--ky-info-row-height, 4.05mm);
            padding-bottom: var(--ky-info-row-pad-bottom, .45mm);
        }

        .template-kneayerng_gold .ky-info-icon {
            align-items: center;
            background: var(--ky-navy);
            border-radius: 50%;
            color: #fff;
            display: flex;
            height: var(--ky-info-icon-size, 3.6mm);
            justify-content: center;
            width: var(--ky-info-icon-size, 3.6mm);
        }

        .template-kneayerng_gold .ky-info-icon i {
            font-size: calc(7px * var(--card-text-scale));
            line-height: 1;
        }

        .template-kneayerng_gold .ky-info-icon svg {
            display: block;
            height: var(--ky-info-svg-size, 2.6mm);
            stroke: currentColor;
            stroke-width: 2.35;
            width: var(--ky-info-svg-size, 2.6mm);
        }

        .template-kneayerng_gold .ky-info-row span {
            color: #0f172a;
            font-family: "Battambang", "Noto Sans Khmer", Arial, sans-serif;
            font-size: calc(7px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.1;
        }

        .template-kneayerng_gold .ky-info-row strong {
            border-left: 1px solid var(--ky-gold);
            color: #111827;
            font-family: "Battambang", "Noto Sans Khmer", Arial, sans-serif;
            font-size: calc(6.6px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.1;
            min-width: 0;
            overflow-wrap: anywhere;
            padding-left: var(--ky-info-value-pad, 1.2mm);
            text-align: right;
        }

        .template-kneayerng_gold .ky-front-footer {
            align-items: center;
            bottom: var(--ky-footer-bottom, 1.2mm);
            display: block;
            height: var(--ky-footer-height, 19.5mm);
            left: 0;
            margin-top: 0;
            position: absolute;
            right: 0;
            width: auto;
        }

        .template-kneayerng_gold .ky-qr-pair {
            bottom: var(--ky-qr-bottom, 1.6mm);
            display: flex;
            gap: var(--ky-qr-gap, 3.4mm);
            justify-content: flex-start;
            left: var(--ky-qr-left, -11.4mm);
            padding-right: 0;
            position: absolute;
            right: auto;
            top: auto;
        }

        .template-kneayerng_gold .ky-qr-item {
            align-items: center;
            display: flex;
            flex-direction: column;
            gap: var(--ky-qr-label-gap, .6mm);
            line-height: 1;
        }

        .template-kneayerng_gold .ky-qr-box {
            align-items: center;
            background: #fff;
            border: .3mm solid var(--ky-light-gold);
            border-radius: var(--ky-qr-radius, 1.4mm);
            display: flex;
            height: var(--ky-qr-box, 13.8mm);
            justify-content: center;
            padding: var(--ky-qr-pad, .75mm);
            position: relative;
            width: var(--ky-qr-box, 13.8mm);
        }

        .template-kneayerng_gold .ky-qr-box.telegram {
            border-color: #38bdf8;
        }

        .template-kneayerng_gold .ky-send-mark {
            align-items: center;
            background: #229ed9;
            border-radius: 50%;
            color: #fff;
            display: flex;
            font-size: calc(7px * var(--card-text-scale));
            height: var(--ky-send-size, 3mm);
            justify-content: center;
            position: absolute;
            right: var(--ky-send-offset, -1.1mm);
            top: var(--ky-send-offset, -1.1mm);
            width: var(--ky-send-size, 3mm);
        }

        .template-kneayerng_gold .ky-qr-code,
        .template-kneayerng_gold .ky-qr-code canvas,
        .template-kneayerng_gold .ky-qr-code img {
            display: block;
            height: var(--ky-qr-code, 11.8mm) !important;
            width: var(--ky-qr-code, 11.8mm) !important;
        }

        .template-kneayerng_gold .ky-qr-item span {
            color: var(--ky-light-gold);
            font-family: Arial, sans-serif;
            font-size: calc(4.25px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .05em;
            line-height: 1;
            white-space: nowrap;
        }

        .template-kneayerng_gold .ky-qr-item.telegram span {
            color: #7dd3fc;
        }

        .template-kneayerng_gold .ky-id-no {
            bottom: var(--ky-id-bottom, 8.2mm);
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: calc(7.2px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .08em;
            left: var(--ky-id-left, 16.6mm);
            margin-top: 0;
            position: absolute;
            right: var(--ky-id-right, 1mm);
            text-align: left;
        }

        .template-kneayerng_gold .ky-id-no b {
            color: var(--ky-light-gold);
        }

        .template-kneayerng_gold .ky-website-pill {
            align-items: center;
            background: var(--ky-gold);
            border-radius: 999px;
            box-shadow: 0 1mm 2mm rgba(0, 0, 0, .16);
            color: #020617;
            display: flex;
            font-family: Arial, sans-serif;
            font-size: calc(5.7px * var(--card-text-scale));
            font-weight: 900;
            gap: var(--ky-website-gap, 1.5mm);
            justify-content: center;
            margin: 0 auto;
            min-height: var(--ky-website-height, 3.5mm);
            position: absolute;
            bottom: var(--ky-website-bottom, 3.3mm);
            left: var(--ky-website-left, 16.6mm);
            right: var(--ky-website-right, 1mm);
            width: auto;
        }

        .template-kneayerng_gold.id-card-back {
            background:
                radial-gradient(circle at 15% 8%, rgba(245, 198, 67, .18), transparent 26%),
                radial-gradient(circle at 85% 86%, rgba(245, 198, 67, .12), transparent 28%),
                linear-gradient(145deg, #0b172a, #07111f);
            border: 0;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 8mm 4mm 4mm;
        }

        .template-kneayerng_gold .ky-back-header {
            border-bottom: 1px solid rgba(255, 255, 255, .18);
            padding-bottom: 2mm;
            text-align: center;
        }

        .template-kneayerng_gold .ky-back-header strong {
            color: var(--ky-light-gold);
            display: block;
            font-family: "Khmer OS Muol Light", "Moul", "Noto Serif Khmer", Arial, sans-serif;
            font-size: calc(12px * var(--card-text-scale));
            line-height: 1.1;
        }

        .template-kneayerng_gold .ky-back-header span {
            color: #cbd5e1;
            display: block;
            font-family: Arial, sans-serif;
            font-size: calc(6.8px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .12em;
            margin-top: .8mm;
        }

        .template-kneayerng_gold .ky-back-copy {
            color: #e2e8f0;
            flex: 1;
            font-size: calc(7.4px * var(--card-text-scale));
            line-height: 1.35;
            margin-top: 3mm;
        }

        .template-kneayerng_gold .ky-back-copy p:first-child {
            color: var(--ky-light-gold);
            font-family: "Battambang", "Noto Sans Khmer", Arial, sans-serif;
            font-weight: 900;
        }

        .template-kneayerng_gold .ky-rules {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 2mm;
            display: flex;
            flex-direction: column;
            gap: 1.2mm;
            margin-top: 2mm;
            padding: 2mm;
        }

        .template-kneayerng_gold .ky-rules div {
            color: #e2e8f0;
            display: grid;
            font-family: "Battambang", "Noto Sans Khmer", Arial, sans-serif;
            gap: 1.2mm;
            grid-template-columns: 3mm 1fr;
        }

        .template-kneayerng_gold .ky-rules b {
            color: var(--ky-light-gold);
        }

        .template-kneayerng_gold .ky-back-footer {
            border-top: 1px solid rgba(255, 255, 255, .18);
            color: #cbd5e1;
            display: grid;
            gap: 2mm;
            grid-template-columns: 1fr 20mm;
            padding-top: 2mm;
        }

        .template-kneayerng_gold .ky-back-footer small {
            color: var(--ky-light-gold);
            display: block;
            font-family: Arial, sans-serif;
            font-size: calc(6.2px * var(--card-text-scale));
            font-weight: 800;
            line-height: 1.35;
        }

        .template-kneayerng_gold .ky-stamp {
            border-bottom: 1px dashed rgba(245, 198, 67, .65);
            color: #94a3b8;
            font-family: Arial, sans-serif;
            font-size: calc(5.5px * var(--card-text-scale));
            height: 8mm;
            text-align: center;
        }

        .template-kneayerng_gold .ky-back-barcode {
            background: #fff;
            border-radius: 1mm;
            margin-top: 2mm;
            padding: 1mm;
        }

        .template-kneayerng_gold .ky-back-barcode .barcode-target {
            height: 5mm;
            width: 38mm;
        }

        .template-khmer_gold.id-card-front {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .12) 0 67%, #fff 67% 100%),
                radial-gradient(circle at 84% 9%, rgba(14, 165, 233, .45), transparent 8%),
                radial-gradient(circle at 88% 39%, rgba(236, 72, 153, .38), transparent 9%),
                radial-gradient(circle at 12% 47%, rgba(249, 115, 22, .35), transparent 10%),
                linear-gradient(145deg, #f8fafc 0%, #ffffff 45%, #eef2f7 100%);
            border-color: #9ca3af;
            border-radius: 0;
            grid-template-columns: 17.8mm 1fr;
        }

        .template-khmer_gold.id-card-front::before {
            background:
                linear-gradient(154deg, transparent 0 18%, rgba(15, 23, 42, .08) 18% 18.4%, transparent 18.4% 100%),
                linear-gradient(26deg, transparent 0 76%, rgba(15, 23, 42, .88) 76% 93%, transparent 93%),
                radial-gradient(circle at 75% 7%, #fdba74 0 1.2mm, transparent 1.3mm),
                repeating-linear-gradient(135deg, rgba(148, 163, 184, .20) 0 1px, transparent 1px 8px);
            opacity: .85;
        }

        .template-khmer_gold.id-card-front::after {
            background:
                linear-gradient(90deg, transparent 0 17.8mm, rgba(156, 163, 175, .9) 17.8mm 18mm, transparent 18mm 100%),
                linear-gradient(180deg, transparent 0 68%, rgba(156, 163, 175, .9) 68% calc(68% + 1px), transparent calc(68% + 1px) 100%);
            box-shadow: inset 0 0 0 1px #9ca3af;
        }

        .template-khmer_gold .id-side {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, 0) 36%),
                repeating-linear-gradient(135deg, rgba(255, 255, 255, .10) 0 1px, transparent 1px 5px),
                linear-gradient(180deg, #c8ab29 0%, #bfa321 89%, #ff9800 89%, #ff9800 100%) !important;
            justify-content: flex-end;
            padding: 4mm 1.2mm 12mm;
            position: relative;
            z-index: 2;
        }

        .template-khmer_gold .side-logo-badge:not(.side-logo-badge-bottom) {
            display: none;
        }

        .template-khmer_gold .side-logo-badge-bottom {
            background: #fff;
            border: .8mm solid rgba(255, 255, 255, .72);
            box-shadow: 0 2mm 5mm rgba(120, 53, 15, .20);
            height: 13mm;
            order: 2;
            width: 13mm;
        }

        .template-khmer_gold .side-branch-name {
            color: #fff;
            flex: 0 1 auto;
            font-family: "Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(17px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.05;
            max-height: 70mm;
            order: 1;
            padding: 0 0 5mm;
            text-shadow: 0 1px 2px rgba(120, 53, 15, .18);
        }

        .template-khmer_gold .id-body {
            align-items: stretch;
            padding: 0;
        }

        .template-khmer_gold .front-photo-drag {
            display: block;
            height: 69mm;
            overflow: hidden;
            width: 100%;
        }

        .template-khmer_gold .front-photo-drag .id-photo,
        .template-khmer_gold .id-photo {
            background:
                radial-gradient(circle at 84% 12%, rgba(14, 165, 233, .35), transparent 11%),
                linear-gradient(145deg, #f8fafc, #ffffff);
            border: 0;
            border-radius: 0;
            box-shadow: none;
            display: block;
            max-width: none;
        }

        .template-khmer_gold .id-details-khmer {
            color: #000;
            font-family: "Khmer OS Battambang", "Battambang", "Noto Sans Khmer", "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(13px * var(--card-text-scale));
            gap: calc(2.3mm * var(--card-text-scale));
            line-height: 1.15;
            margin: 3.8mm auto 0;
            width: 34mm;
        }

        .template-khmer_gold .id-details-khmer div {
            gap: 4mm;
            grid-template-columns: 12mm minmax(0, 1fr);
        }

        .template-khmer_gold .id-details-khmer span,
        .template-khmer_gold .id-details-khmer strong {
            color: #000;
            font-family: "Khmer OS Battambang", "Battambang", "Noto Sans Khmer", "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(13px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.18;
        }

        .template-khmer_gold .id-details-khmer .employee-name-row strong {
            font-family: var(--employee-khmer-font, "Khmer OS Battambang", "Battambang", "Noto Sans Khmer", "Kantumruy Pro", Arial, sans-serif);
            font-size: calc(var(--employee-khmer-size, 13px) * var(--card-text-scale));
        }

        .template-khmer_gold .id-details-khmer small {
            color: #6b7280;
            font-size: calc(8px * var(--card-text-scale));
            letter-spacing: 0;
        }

        .template-khmer_gold .id-details-khmer .employee-name-row small {
            font-family: var(--employee-english-font, Arial, sans-serif);
            font-size: calc(var(--employee-english-size, 8px) * var(--card-text-scale));
        }

        .template-khmer_gold .id-code {
            border-top: 0;
            margin: auto auto 7mm;
            padding-top: 0;
            width: 33mm;
        }

        .template-khmer_gold .barcode-visual,
        .template-khmer_gold .barcode-target {
            height: 7.8mm;
            width: 29mm;
        }

        .template-khmer_gold .barcode-visual {
            opacity: .36;
        }

        .template-khmer_gold .id-code small {
            color: #8b95a1;
            display: grid;
            font-family: Arial, sans-serif;
            font-size: calc(8.2px * var(--card-text-scale));
            font-weight: 500;
            grid-template-columns: 1fr 1fr;
            letter-spacing: 0;
            margin-top: 1.6mm;
            text-align: left;
        }

        .template-khmer_gold .id-code b {
            color: #8b95a1;
            font-weight: 500;
        }

        .id-side {
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            font-size: calc(10px * var(--card-text-scale));
            padding: 3mm 0 3mm;
            text-align: center;
        }

        .id-side-empty {
            background: transparent !important;
        }

        .side-branch-name {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5mm 0;
            color: #fff;
            font-family: "Moul", "Kantumruy Pro", "Noto Sans Khmer", Arial, sans-serif;
            font-size: calc(9.5px * var(--card-text-scale));
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: 0;
            max-height: 58mm;
            overflow: hidden;
        }

        .side-logo-badge {
            width: 7.2mm;
            height: 7.2mm;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .72);
            background: rgba(255, 255, 255, .16);
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #fff7ed;
            font-size: calc(4.5px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1;
        }

        .side-logo-badge img {
            background: #fff;
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: .45mm;
            border-radius: 50%;
        }

        .id-body {
            padding: 3.2mm 3mm 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        .id-logo {
            max-width: 28mm;
            max-height: 10mm;
            object-fit: contain;
        }

        .back-branch-logo {
            background: rgba(255, 255, 255, .94);
            border: 1px solid #e2e8f0;
            border-radius: 2mm;
            height: 13.2mm;
            max-height: none;
            max-width: none;
            object-fit: contain;
            padding: 1mm;
            position: static;
            width: 13.2mm;
        }

        .back-brand-row {
            align-items: start;
            display: grid;
            gap: 2mm;
            grid-template-columns: 13.2mm minmax(0, 1fr);
            min-height: 13.2mm;
            width: 100%;
        }

        .back-brand-row.no-logo {
            grid-template-columns: 1fr;
        }

        .back-brand-copy {
            min-width: 0;
            text-align: left;
        }

        .back-brand-copy .back-title {
            text-align: left;
        }

        .back-brand-copy .text-muted {
            display: block;
            line-height: 1.2;
            margin-top: .45mm;
        }

        .id-photo {
            width: 27mm;
            height: 36mm;
            object-fit: cover;
            border: 1.2mm solid currentColor;
            border-radius: 2.6mm;
            background: #f3f4f6;
            box-shadow: 0 2px 5px rgba(15, 23, 42, .14);
        }

        .id-photo.rounded {
            border-radius: 6mm;
        }

        .id-photo.square {
            border-radius: 0;
            width: 30mm;
            height: 30mm;
        }

        .id-photo.circle {
            border-radius: 50%;
            width: 30mm;
            height: 30mm;
        }

        .drag-size-frame {
            display: inline-block;
            line-height: 0;
            position: relative;
        }

        .drag-size-handle {
            align-items: center;
            background: #0f766e;
            border: 1px solid #fff;
            border-radius: 50%;
            bottom: -4px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, .24);
            color: #fff;
            cursor: nwse-resize;
            display: flex;
            height: 12px;
            justify-content: center;
            position: absolute;
            right: -4px;
            touch-action: none;
            width: 12px;
            z-index: 5;
        }

        .drag-size-handle::before {
            content: "";
            border-bottom: 4px solid currentColor;
            border-left: 4px solid transparent;
            height: 0;
            width: 0;
        }

        .digital-hrs-print-studio .drag-size-handle {
            display: none;
        }

        #editorPreview .drag-size-handle {
            display: flex;
        }

        .id-name {
            width: 100%;
            text-align: center;
            font-size: calc(13px * var(--card-text-scale));
            font-weight: 800;
            line-height: 1.15;
        }

        .id-english {
            font-size: calc(9px * var(--card-text-scale));
            text-transform: uppercase;
            color: #4b5563;
            margin-top: 1mm;
        }

        .id-details {
            width: 100%;
            font-size: calc(8px * var(--card-text-scale));
            line-height: 1.35;
            margin-top: 1mm;
        }

        .id-details div {
            display: grid;
            grid-template-columns: 17mm 1fr;
            gap: 2mm;
        }

        .id-details-khmer {
            width: 36mm;
            margin-top: calc(9mm * var(--card-text-scale));
            display: flex;
            flex-direction: column;
            gap: calc(1.55mm * var(--card-text-scale));
            color: #475569;
            font-family: "Kantumruy Pro", "Noto Sans Khmer", Arial, sans-serif;
            font-size: calc(8.2px * var(--card-text-scale));
            font-weight: 800;
            line-height: 1.2;
        }

        .id-body.no-photo .id-details-khmer {
            margin-top: calc(4mm * var(--card-text-scale));
        }

        .id-details-khmer div {
            grid-template-columns: 12mm minmax(0, 1fr);
            align-items: start;
            gap: 2.6mm;
        }

        .id-details-khmer span {
            color: #475569;
            font-weight: 800;
            font-size: calc(8.2px * var(--card-text-scale));
            line-height: 1.25;
            white-space: nowrap;
        }

        .id-details-khmer strong {
            color: #111827;
            font-family: "Kantumruy Pro", "Noto Sans Khmer", "Khmer OS Battambang", Arial, sans-serif;
            font-size: calc(9.6px * var(--card-text-scale));
            font-weight: 900;
            line-height: 1.22;
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .id-details-khmer .employee-name-row strong {
            font-family: var(--employee-khmer-font, "Kantumruy Pro", "Noto Sans Khmer", "Khmer OS Battambang", Arial, sans-serif);
            font-size: calc(var(--employee-khmer-size, 9.6px) * var(--card-text-scale));
        }

        .id-details-khmer small {
            display: block;
            margin-top: .55mm;
            color: #64748b;
            font-family: "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(6.7px * var(--card-text-scale));
            font-weight: 800;
            letter-spacing: .035em;
            text-transform: uppercase;
        }

        .id-details-khmer .employee-name-row small {
            font-family: var(--employee-english-font, "Kantumruy Pro", Arial, sans-serif);
            font-size: calc(var(--employee-english-size, 6.7px) * var(--card-text-scale));
        }

        .id-code {
            margin-top: auto;
            width: 38mm;
            text-align: center;
            border-top: 1px solid #e1e7ef;
            padding-top: 1.45mm;
        }

        .barcode-visual {
            width: 30mm;
            height: 7.2mm;
            max-width: 100%;
            margin: 0 auto;
            background: repeating-linear-gradient(
                90deg,
                #111827 0 1px,
                transparent 1px 2px,
                #111827 2px 3px,
                transparent 3px 5px,
                #111827 5px 7px,
                transparent 7px 9px
            );
            position: relative;
        }

        .barcode-target {
            height: 7.2mm;
            width: 30mm;
            max-width: 100%;
            border: 0;
            display: block;
            background: transparent;
        }

        .barcode-fallback {
            width: 30mm;
            height: 8mm;
            margin: 0 auto;
            background: repeating-linear-gradient(
                90deg,
                #111827 0 1px,
                transparent 1px 2px,
                #111827 2px 4px,
                transparent 4px 6px,
                #111827 6px 7px,
                transparent 7px 10px
            );
        }

        .id-code small {
            display: block;
            margin-top: 1mm;
            color: #111827;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: calc(6.8px * var(--card-text-scale));
            font-weight: 900;
            letter-spacing: .04em;
        }

        .id-code b {
            color: var(--card-accent-color, #b45309);
            font-weight: 900;
        }

        .khqr-generated {
            width: 20mm;
            height: 20mm;
            margin: 0 auto 1mm;
        }

        .khqr-generated canvas,
        .khqr-generated img {
            width: 100% !important;
            height: 100% !important;
            object-fit: inherit;
            display: block;
        }

        .id-card-back {
            padding: .9mm 3.2mm 2.4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.35mm;
            background:
                linear-gradient(160deg, #ffffff 0%, #fff7ed 46%, #edf7ff 100%),
                radial-gradient(circle at 16% 12%, rgba(245, 158, 11, .24), transparent 31%),
                radial-gradient(circle at 92% 84%, rgba(37, 99, 235, .14), transparent 34%);
            position: relative;
        }

        .back-title {
            font-weight: 900;
            text-align: center;
            font-size: 12.5px;
            line-height: 1.05;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2mm;
            width: 100%;
            margin-top: 1.2mm;
        }

        .payment-box {
            text-align: center;
        }

        .payment-qr-frame {
            align-items: center;
            background: rgba(255, 255, 255, .92);
            border: 1px solid #dfe5ed;
            border-radius: 2.2mm;
            display: flex;
            justify-content: center;
            min-height: 30mm;
            overflow: hidden;
            padding: 1.4mm;
            width: 100%;
        }

        .payment-qr-frame img,
        .payment-qr-frame .khqr-generated {
            width: 27.3mm;
            height: 27.3mm;
            object-fit: contain;
            display: block;
            margin: 0;
        }

        .payment-box strong {
            display: block;
            font-size: 8px;
            line-height: 1.15;
            color: #111827;
            margin-top: 1mm;
            overflow-wrap: anywhere;
        }

        .payment-box.telegram-payment-box strong {
            color: #0ea5e9;
        }

        .back-contact {
            color: #0f172a;
            font-family: "Kantumruy Pro", Arial, sans-serif;
            font-size: calc(6.25px * var(--card-text-scale));
            line-height: 1.22;
            margin-top: .9mm;
            width: 100%;
        }

        .back-contact-title {
            display: block;
            font-size: calc(9px * var(--card-text-scale));
            font-weight: 900;
            margin-bottom: 1.2mm;
        }

        .back-contact-row {
            align-items: start;
            display: grid;
            gap: 1.35mm;
            grid-template-columns: 3.9mm minmax(0, 1fr);
            margin-bottom: .75mm;
        }

        .back-contact-icon,
        .back-contact-row .contact-mark {
            align-items: center;
            background: rgba(255, 255, 255, .92);
            border: .25mm solid currentColor;
            border-radius: 50%;
            color: var(--contact-color, #0ea5e9);
            display: flex;
            font-size: calc(8.6px * var(--card-text-scale));
            font-style: normal;
            font-weight: 900;
            height: 3.7mm;
            justify-content: center;
            line-height: 1;
            width: 3.7mm;
        }

        .back-contact-row span:last-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .back-contact-row a {
            color: #0f172a;
            text-decoration: underline;
        }

        .back-contact-row b {
            font-weight: 900;
        }

        .back-note {
            margin-top: auto;
            border-top: 1px solid #e1e7ef;
            padding-top: 2mm;
            font-size: 7px;
            color: #4b5563;
            line-height: 1.35;
            width: 100%;
            text-align: center;
        }

        .bank-strip {
            width: 100%;
            display: none;
            grid-template-columns: 1fr 1fr;
            gap: 1mm;
            font-size: 6px;
            color: #334155;
        }

        .bank-strip span {
            border: 1px solid #e5e7eb;
            border-radius: 1.5mm;
            padding: 1mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .bank-strip b {
            color: #0369a1;
        }

        .signature-line {
            width: 100%;
            border-top: 1px dashed #94a3b8;
            color: #64748b;
            font-size: 6px;
            text-align: right;
            padding-top: 1mm;
        }

        .print-only {
            display: none;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body * {
                visibility: hidden;
            }

            .print-only,
            .print-only * {
                visibility: visible;
            }

            .print-only {
                display: block;
                position: absolute;
                inset: 0;
            }

            .a4-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }

            .a4-page:last-child {
                page-break-after: auto;
            }

            .id-card {
                box-shadow: none;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>

    <script>
        $(document).ready(function () {
            let employees = window.digitalHrsEmployees || [];
            const company = @json($company);
            const companyLogo = @json($companyLogo);
            let previewIndex = 0;
            let previewZoom = 120;
            let activeCodeTab = 'blade';

            employees = employees.map((employee) => ({
                ...employee,
                selected: true,
            }));

            const escapeHtml = (value) => $('<div>').text(value || '').html();
            const companyValue = (field) => company && company[field] ? company[field] : '';

            const selectedEmployees = () => employees.filter((employee) => employee.selected !== false);

            const activeEmployee = () => employees[previewIndex] || employees[0] || null;

            const uniqueEmployeeValues = (field, predicate = () => true) => [...new Set(employees
                .filter(predicate)
                .map((employee) => String(employee[field] || '').trim())
                .filter(Boolean))]
                .sort((a, b) => a.localeCompare(b));

            const renderEmployeeFilters = () => {
                const branchValue = $('#employeeBranchFilter').val() || '';
                const departmentValue = $('#employeeDepartmentFilter').val() || '';
                const option = (value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`;
                const departments = uniqueEmployeeValues('department', (employee) => !branchValue || String(employee.branch || '') === branchValue);
                const nextDepartmentValue = departments.includes(departmentValue) ? departmentValue : '';

                $('#employeeBranchFilter').html('<option value="">All Branches</option>' + uniqueEmployeeValues('branch').map(option).join('')).val(branchValue);
                $('#employeeDepartmentFilter').html('<option value="">All Departments</option>' + departments.map(option).join('')).val(nextDepartmentValue);
            };

            const readDimension = (selector, fallback) => {
                const value = parseFloat($(selector).val());
                return Number.isFinite(value) && value > 0 ? value : fallback;
            };

            const design = () => ({
                orientation: $('#cardOrientation').val(),
                cardColor: $('#cardColor').val(),
                accentColor: $('#accentColor').val(),
                companyKhmer: $('#companyKhmer').val(),
                companyEnglish: $('#companyEnglish').val(),
                sideBannerText: $('#sideBannerText').val(),
                backTagline: $('#backTagline').val(),
                shopSubtitle: $('#shopSubtitle').val(),
                merchantName: $('#merchantName').val(),
                merchantId: $('#merchantId').val(),
                bankAccount1: $('#bankAccount1').val(),
                bankAccount2: $('#bankAccount2').val(),
                templateStyle: $('#templateStyle').val() || 'khmer_gold',
                width: readDimension('#a4CardWidthMm', readDimension('#cardWidthMm', 71)),
                height: readDimension('#a4CardHeightMm', readDimension('#cardHeightMm', 103)),
                bleed: parseFloat($('#bleedMm').val()) || 1.5,
                scale: Math.max(80, Math.min(120, parseInt($('#cardScale').val(), 10) || 100)),
                photoShape: $('#photoShape').val(),
                photoFit: $('#photoFit').val(),
                photoZoom: Math.max(80, Math.min(140, parseInt($('#photoZoom').val(), 10) || 100)),
                frontTextScale: Math.max(70, Math.min(140, parseInt($('#frontTextScale').val(), 10) || 100)),
                showPhoto: $('#showPhoto').is(':checked'),
                showSideBrand: $('#showSideBrand').is(':checked'),
                showKhmerName: $('#showKhmerName').is(':checked'),
                showEnglishName: $('#showEnglishName').is(':checked'),
                showPosition: $('#showPosition').is(':checked'),
                showBarcode: $('#showBarcode').is(':checked'),
                showDepartment: $('#showDepartment').is(':checked'),
                showBranch: $('#showBranch').is(':checked'),
                showPhone: $('#showPhone').is(':checked'),
                showEmployeeCode: $('#showEmployeeCode').is(':checked'),
                showPaymentQr: $('#showPaymentQr').is(':checked'),
                showCutLines: $('#showCutLines').is(':checked'),
                showBleedGuide: $('#showBleedGuide').is(':checked'),
                showSafeZone: $('#showSafeZone').is(':checked'),
                showSignatureLine: $('#showSignatureLine').is(':checked'),
                paymentQrSize: Math.max(12, Math.min(40, parseFloat($('#paymentQrSizeMm').val()) || 27.3)),
                paymentPadding: Math.max(0, Math.min(6, parseFloat($('#paymentPaddingMm').val()) || 2)),
                paymentQrFit: $('#paymentQrFit').val() || 'contain',
                photoPreset: $('#photoPreset').val(),
                photoWidth: Math.max(12, parseFloat($('#photoWidthMm').val()) || 27),
                photoHeight: Math.max(12, parseFloat($('#photoHeightMm').val()) || 36),
                photoRadius: Math.max(0, parseFloat($('#photoRadiusMm').val()) || 0),
                khmerNameFont: $('#khmerNameFont').val() || '"Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif',
                khmerNameSize: Math.max(8, Math.min(34, parseFloat($('#khmerNameSize').val()) || 12)),
                englishNameFont: $('#englishNameFont').val() || 'Arial, "Poppins", sans-serif',
                englishNameSize: Math.max(6, Math.min(22, parseFloat($('#englishNameSize').val()) || 8)),
            });

            const printLayout = () => ({
                marginTop: parseFloat($('#marginTopMm').val()) || 12,
                marginLeft: parseFloat($('#marginLeftMm').val()) || 16,
                gapX: parseFloat($('#gapXMm').val()) || 10,
                gapY: parseFloat($('#gapYMm').val()) || 10,
                pairMode: $('#pairMode').val(),
                showIndex: $('#toggleMarginIndex').hasClass('active'),
                showCutMarks: $('#toggleCutMarks').hasClass('active'),
            });

            const cssFontValue = (value) => String(value || '').split('"').join("'");

            const cardDimensionStyle = () => {
                const config = design();
                const scale = config.scale / 100;
                const baseWidth = 71;
                const baseHeight = 103;
                const widthFactor = config.width / baseWidth;
                const heightFactor = config.height / baseHeight;
                const sizeFactor = Math.min(widthFactor, heightFactor);
                const layoutScale = Math.max(0.72, Math.min(1.32, sizeFactor * scale));
                const autoTextScale = Math.max(0.68, Math.min(1.35, layoutScale * (config.frontTextScale / 100)));
                const mm = (value) => `${(value * layoutScale).toFixed(3)}mm`;
                const cardWidth = config.width * scale;
                const cardHeight = config.height * scale;
                const usableWidth = Math.max(28, cardWidth - (14.2 * layoutScale) - (4.6 * layoutScale));
                const footerReserve = Math.max(18, 19.5 * layoutScale);
                const employeePhotoWidth = Math.max(24, Math.min(usableWidth, cardWidth * 0.48));
                const detailsReserve = 34 * layoutScale;
                const employeePhotoHeight = Math.max(26, Math.min(cardHeight - footerReserve - detailsReserve, (cardHeight * 0.39) + 5));
                const infoWidth = Math.max(28, Math.min(usableWidth, 36 * layoutScale));
                const pillWidth = Math.max(27, Math.min(usableWidth, 34 * layoutScale));
                return [
                    `width:${cardWidth}mm`,
                    `height:${cardHeight}mm`,
                    `--card-text-scale:${autoTextScale}`,
                    `--card-accent-color:${config.accentColor}`,
                    `--ky-left-width:${mm(14.2)}`,
                    `--ky-left-pad-top:${mm(3)}`,
                    `--ky-logo-size:${mm(11.2)}`,
                    `--ky-logo-border:${mm(.6)}`,
                    `--ky-shadow-y:${mm(1.2)}`,
                    `--ky-shadow-blur:${mm(4)}`,
                    `--ky-side-vertical-top:${mm(23)}`,
                    `--ky-dot-size:${mm(1.2)}`,
                    `--ky-dot-gap:${mm(1.3)}`,
                    `--ky-dot-bottom:${mm(18)}`,
                    `--ky-right-dot-gap:${mm(1.5)}`,
                    `--ky-right-dots-top:${mm(13.6)}`,
                    `--ky-right-dots-right:${mm(4)}`,
                    `--ky-front-pad-top:${mm(2.8)}`,
                    `--ky-front-pad-x:${mm(2.3)}`,
                    `--ky-front-pad-bottom:${mm(1.5)}`,
                    `--ky-front-right:${mm(1.8)}`,
                    `--ky-photo-width:${employeePhotoWidth.toFixed(3)}mm`,
                    `--ky-photo-height:${employeePhotoHeight.toFixed(3)}mm`,
                    `--ky-photo-border:${mm(.65)}`,
                    `--ky-photo-radius:${mm(4)}`,
                    `--ky-photo-shadow-y:${mm(2)}`,
                    `--ky-photo-shadow-blur:${mm(5)}`,
                    `--ky-name-top:${mm(1.25)}`,
                    `--ky-english-top:${mm(.5)}`,
                    `--ky-pill-width:${pillWidth.toFixed(3)}mm`,
                    `--ky-pill-gap:${mm(1.7)}`,
                    `--ky-pill-top:${mm(1.05)}`,
                    `--ky-pill-height:${mm(4.1)}`,
                    `--ky-pill-pad-y:${mm(.35)}`,
                    `--ky-pill-pad-x:${mm(2.2)}`,
                    `--ky-pill-divider-pad:${mm(2)}`,
                    `--ky-info-width:${infoWidth.toFixed(3)}mm`,
                    `--ky-info-gap:${mm(.65)}`,
                    `--ky-info-top:${mm(1.05)}`,
                    `--ky-info-row-gap:${mm(1)}`,
                    `--ky-info-icon-col:${mm(4.2)}`,
                    `--ky-info-label-col:${mm(11)}`,
                    `--ky-info-row-height:${mm(4.05)}`,
                    `--ky-info-row-pad-bottom:${mm(.45)}`,
                    `--ky-info-icon-size:${mm(3.6)}`,
                    `--ky-info-svg-size:${mm(2.6)}`,
                    `--ky-info-value-pad:${mm(1.2)}`,
                    `--ky-footer-bottom:${mm(.8)}`,
                    `--ky-footer-height:${mm(24)}`,
                    `--ky-qr-left:${mm(-11.4)}`,
                    `--ky-qr-bottom:${mm(7.35)}`,
                    `--ky-qr-gap:${mm(2.8)}`,
                    `--ky-qr-label-gap:${mm(.38)}`,
                    `--ky-qr-box:${mm(13.8)}`,
                    `--ky-qr-pad:${mm(.75)}`,
                    `--ky-qr-radius:${mm(1.4)}`,
                    `--ky-qr-code:${mm(11.8)}`,
                    `--ky-send-size:${mm(3)}`,
                    `--ky-send-offset:${mm(-1.1)}`,
                    `--ky-id-bottom:${mm(8.2)}`,
                    `--ky-id-left:${mm(20.2)}`,
                    `--ky-id-right:${mm(1)}`,
                    `--ky-website-bottom:${mm(3.3)}`,
                    `--ky-website-left:${mm(20.2)}`,
                    `--ky-website-right:${mm(1)}`,
                    `--ky-website-height:${mm(3.5)}`,
                    `--ky-website-gap:${mm(1.5)}`,
                    `--employee-khmer-font:${cssFontValue(config.khmerNameFont)}`,
                    `--employee-khmer-size:${config.khmerNameSize}px`,
                    `--employee-english-font:${cssFontValue(config.englishNameFont)}`,
                    `--employee-english-size:${config.englishNameSize}px`,
                ].join(';');
            };

            const templateClass = () => `template-${design().templateStyle}`;

            const chunk = (items, size) => {
                const pages = [];
                for (let i = 0; i < items.length; i += size) {
                    pages.push(items.slice(i, i + size));
                }
                return pages.length ? pages : [[]];
            };

            const cardLogo = (employee) => employee.branch_logo_url || companyLogo || '';

            const formatBranchLabel = (value, fallback = 'គ្នាយើង') => {
                let label = String(value || fallback || '').trim();
                if (!label) return '';
                label = label.replace(/^គ្នាយើង\s*[-–—]?\s*/u, '').trim();
                return label ? `គ្នាយើង-${label}` : 'គ្នាយើង';
            };

            const branchLabel = (employee) => formatBranchLabel(
                employee.branch
                || design().sideBannerText
                || employee.company
                || companyValue('name')
                || 'គ្នាយើង'
            );

            const employeeBranchLabel = (employee) => employee.branch ? formatBranchLabel(employee.branch, '') : '';

            const sideLogoBadge = (employee, extraClass = '') => {
                const logo = cardLogo(employee);
                const className = `side-logo-badge${extraClass ? ` ${extraClass}` : ''}`;
                const safeLogo = escapeHtml(logo);
                return `<span class="${className}">${logo
                    ? `<img src="${safeLogo}" alt="${escapeHtml(branchLabel(employee))}">`
                    : '<span>DHR</span>'}</span>`;
            };

            const sideBrandRail = (employee) => `
                <div class="id-side" style="background:${design().cardColor}">
                    ${sideLogoBadge(employee)}
                    <span class="side-branch-name">${escapeHtml(branchLabel(employee))}</span>
                    ${sideLogoBadge(employee, 'side-logo-badge-bottom')}
                </div>
            `;

            const photoStyle = () => {
                const config = design();
                const autoSize = config.templateStyle === 'khmer_gold'
                    ? { width: 52.5, height: 69, radius: 0 }
                    : { width: 27, height: 36, radius: config.photoRadius };
                const presets = {
                    auto: autoSize,
                    passport: { width: 30, height: 40, radius: config.photoRadius },
                    square: { width: 30, height: 30, radius: config.photoRadius },
                    hero: { width: 34, height: 42, radius: config.photoRadius },
                    custom: { width: config.photoWidth, height: config.photoHeight, radius: config.photoRadius },
                };
                const size = presets[config.photoPreset] || presets.auto;
                const radius = config.photoShape === 'circle'
                    ? '50%'
                    : `${config.photoShape === 'square' ? 0 : size.radius}mm`;

                return [
                    `width:${size.width}mm`,
                    `height:${size.height}mm`,
                    `border-radius:${radius}`,
                    `color:${config.cardColor}`,
                    `object-fit:${config.photoFit}`,
                    `transform:scale(${config.photoZoom / 100})`,
                ].join(';');
            };

            const paymentBoxStyle = () => {
                const config = design();
                const minHeight = Math.max(30, config.paymentQrSize + (config.paymentPadding * 2));

                return [
                    `padding:${config.paymentPadding}mm ${Math.max(1, config.paymentPadding * 0.75)}mm`,
                    `min-height:${minHeight}mm`,
                ].join(';');
            };

            const paymentQrStyle = () => {
                const config = design();

                return [
                    `width:${config.paymentQrSize}mm`,
                    `height:${config.paymentQrSize}mm`,
                    `object-fit:${config.paymentQrFit}`,
                ].join(';');
            };

            const paymentQrPx = () => Math.round(design().paymentQrSize * 3.78);

            const dragHandle = (target) => `<span class="drag-size-handle" data-resize-target="${target}" title="Drag to resize"></span>`;

            const dotMatrix = (count = 20) => Array.from({ length: count }, () => '<span></span>').join('');

            const backContactBlock = () => `
                <div class="back-contact">
                    <strong class="back-contact-title">Contact Us:</strong>
                    <div class="back-contact-row" style="--contact-color:#10b981"><span class="back-contact-icon">W</span><span>Website: <a href="http://kneayerng.com">http://kneayerng.com</a></span></div>
                    <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook: <a href="https://www.facebook.com/kystorecambodia">https://www.facebook.com/kystorecambodia</a></span></div>
                    <div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook (Official): <a href="https://www.facebook.com/Knea">https://www.facebook.com/Knea</a></span></div>
                    <div class="back-contact-row" style="--contact-color:#ec4899"><span class="back-contact-icon">IG</span><span>Instagram: <a href="https://www.instagram.com/kneayerngvip.official/">https://www.instagram.com/kneayerngvip.official/</a></span></div>
                    <div class="back-contact-row" style="--contact-color:#0ea5e9"><span class="back-contact-icon">T</span><span>Telegram: <a href="https://t.me/kneayerngofficialbot">https://t.me/kneayerngofficialbot</a></span></div>
                    <div class="back-contact-row" style="--contact-color:#14b8a6"><span class="back-contact-icon">P</span><span>Phone: <b>16910505</b></span></div>
                </div>
            `;

            const verifyQrValue = (employee) => {
                const code = employee.employee_code || '';
                return employee.qr_data || `https://kneayerng.com/verify/${encodeURIComponent(code)}`;
            };

            const telegramQrValue = (employee) => employee.telegram_qr_url || 'https://t.me/kneayerngofficialbot';

            const kyInfoIcon = (name) => {
                const icons = {
                    user: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>',
                    building: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1M9 13h1M9 17h1M16 13h1M16 17h1"/></svg>',
                    card: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h4"/></svg>',
                    phone: '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2.1z"/></svg>',
                };

                return icons[name] || icons.card;
            };

            const kneayerngArt = () => `
                <svg class="ky-card-art" viewBox="0 0 340 540" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="340" height="540" fill="#FAF8F5"></rect>
                    <path d="M 0,0 L 102,0 C 102,100 68,180 68,260 C 68,340 98,404 98,447 L 0,540 Z" fill="#0B172A"></path>
                    <path d="M 102,0 C 102,100 68,180 68,260 C 68,340 98,404 98,447" stroke="#C59B27" stroke-width="3.5"></path>
                    <path d="M 107,0 C 107,95 73,175 73,255 C 73,335 103,399 103,442" stroke="#F3E5AB" stroke-width="1" opacity=".6"></path>
                    <path d="M 0,452 C 64,430 276,430 340,452 L 340,540 L 0,540 Z" fill="#0B172A"></path>
                    <path d="M 0,452 C 64,430 276,430 340,452" stroke="#C59B27" stroke-width="3.5"></path>
                </svg>
            `;

            const renderKneayerngFront = (employee) => {
                const code = employee.employee_code || '';
                const logo = cardLogo(employee);
                const positionKhmer = employee.position_khmer || employee.post || employee.position_english || '';
                const positionEnglish = employee.position_english || employee.post || employee.position_khmer || '';
                const branch = employeeBranchLabel(employee) || branchLabel(employee);

                return `
                    <div class="id-card-wrap">
                        <div class="id-card id-card-front template-kneayerng_gold" style="${cardDimensionStyle()}">
                            ${design().showBleedGuide ? '<div class="bleed-guide"></div>' : ''}
                            ${design().showCutLines ? '<div class="cut-line-guide"></div>' : ''}
                            ${design().showSafeZone ? '<div class="safe-zone-guide"></div>' : ''}
                            ${kneayerngArt()}
                            <aside class="ky-left-panel">
                                <div class="ky-logo-circle">${logo ? `<img src="${escapeHtml(logo)}" alt="Kneayerng">` : '<b>គ្នាយើង</b>'}</div>
                                <div class="ky-side-brand">
                                    <strong>គ្នាយើង</strong>
                                    <span>KNEAYERNG</span>
                                    <small>PHONE SHOP</small>
                                </div>
                                <div class="ky-side-vertical">KNEAYERNG PHONE SHOP</div>
                                <div class="ky-dot-matrix">${dotMatrix(20)}</div>
                            </aside>
                            <div class="ky-right-dots">${dotMatrix(30)}</div>
                            <main class="ky-front-content">
                                ${design().showPhoto ? `<div class="ky-photo-frame"><img src="${escapeHtml(employee.photo_url)}" alt="${escapeHtml(employee.name || '')}"></div>` : ''}
                                <div class="ky-name-block">
                                    ${design().showKhmerName ? `<strong>${escapeHtml(employee.name || '')}</strong>` : ''}
                                    ${design().showEnglishName && employee.english_name ? `<small>${escapeHtml(employee.english_name)}</small>` : ''}
                                </div>
                                ${design().showPosition ? `<div class="ky-position-pill"><span>មុខតំណែង:</span><b>${escapeHtml(positionEnglish)}</b></div>` : ''}
                                <div class="ky-info-list">
                                    ${design().showBranch ? `<div class="ky-info-row"><span class="ky-info-icon">${kyInfoIcon('building')}</span><span>សាខា</span><strong>${escapeHtml(branch)}</strong></div>` : ''}
                                    ${design().showDepartment ? `<div class="ky-info-row"><span class="ky-info-icon">${kyInfoIcon('card')}</span><span>ផ្នែក</span><strong>${escapeHtml(employee.department || '')}</strong></div>` : ''}
                                    ${design().showPhone ? `<div class="ky-info-row"><span class="ky-info-icon">${kyInfoIcon('phone')}</span><span>ទូរស័ព្ទ</span><strong>${escapeHtml(employee.phone || '')}</strong></div>` : ''}
                                </div>
                                ${design().showBarcode ? `
                                    <div class="ky-front-footer">
                                        <div class="ky-qr-pair">
                                            <div class="ky-qr-item">
                                                <div class="ky-qr-box"><div class="khqr-generated ky-qr-code" data-value="${escapeHtml(verifyQrValue(employee))}" data-qr-px="48"></div></div>
                                                <span>WEBSITE QR</span>
                                            </div>
                                            <div class="ky-qr-item telegram">
                                                <div class="ky-qr-box telegram"><div class="khqr-generated ky-qr-code" data-value="${escapeHtml(telegramQrValue(employee))}" data-qr-px="48"></div><b class="ky-send-mark">↗</b></div>
                                                <span>TELEGRAM QR</span>
                                            </div>
                                        </div>
                                        <div class="ky-id-no">ID NO : <b>${escapeHtml(code)}</b></div>
                                        <div class="ky-website-pill"><span>◎</span><b>www.kneayerng.com</b></div>
                                    </div>
                                ` : ''}
                            </main>
                        </div>
                    </div>
                `;
            };

            const renderAmberGoldBack = (employee, cardClass = templateClass(), titleColor = design().cardColor) => {
                const qrCodes = [
                    employee.khqr_account_id ? { payment_name: 'Employee KHQR', khqr_value: employee.khqr_account_id } : null,
                    ...(employee.payment_qr_codes || []),
                ].filter(Boolean);
                const payments = qrCodes.length ? qrCodes.slice(0, 4).map((qrCode) => `
                    <div class="payment-box">
                        <div class="payment-qr-frame" style="${paymentBoxStyle()}">
                            ${qrCode.khqr_value
                                ? `<span class="drag-size-frame payment-qr-drag"><div class="khqr-generated" data-value="${escapeHtml(qrCode.khqr_value)}" data-qr-px="${paymentQrPx()}" style="${paymentQrStyle()}"></div>${dragHandle('payment-qr')}</span>`
                                : `<span class="drag-size-frame payment-qr-drag"><img src="${qrCode.qr_code_url}" alt="${escapeHtml(qrCode.payment_name)}" style="${paymentQrStyle()}">${dragHandle('payment-qr')}</span>`}
                        </div>
                        <strong>${escapeHtml(qrCode.payment_name)}</strong>
                    </div>
                `).join('') : `
                    <div class="payment-box">
                        <div class="payment-qr-frame" style="${paymentBoxStyle()}">
                            <strong>{{ __('index.payment_qr_codes') }}</strong>
                        </div>
                        <div class="text-muted mt-2">{{ __('index.not_available') }}</div>
                    </div>
                `;

                const telegramBox = `
                    <div class="payment-box telegram-payment-box">
                        <div class="payment-qr-frame" style="${paymentBoxStyle()}">
                            <span class="drag-size-frame payment-qr-drag"><div class="khqr-generated" data-value="${escapeHtml(telegramQrValue(employee))}" data-qr-px="${paymentQrPx()}" style="${paymentQrStyle()}"></div>${dragHandle('payment-qr')}</span>
                        </div>
                        <strong>TELEGRAM QR</strong>
                    </div>
                `;

                return `
                    <div class="id-card-wrap">
                        <div class="id-card id-card-back ${cardClass}" style="${cardDimensionStyle()}">
                            ${design().showBleedGuide ? '<div class="bleed-guide"></div>' : ''}
                            ${design().showCutLines ? '<div class="cut-line-guide"></div>' : ''}
                            ${design().showSafeZone ? '<div class="safe-zone-guide"></div>' : ''}
                            <div class="back-brand-row ${cardLogo(employee) ? '' : 'no-logo'}">
                                ${cardLogo(employee) ? `<img class="id-logo back-branch-logo" src="${escapeHtml(cardLogo(employee))}" alt="${escapeHtml(branchLabel(employee))}">` : ''}
                                <div class="back-brand-copy">
                                    <div class="back-title" style="color:${titleColor}">${escapeHtml(branchLabel(employee))}</div>
                                    <div class="text-muted small">${escapeHtml(design().backTagline)}</div>
                                </div>
                            </div>
                            ${design().showPaymentQr ? `<div class="payment-grid">${payments}${telegramBox}</div>` : ''}
                            ${backContactBlock()}
                            <div class="bank-strip">
                                <span><b>ABA</b> ${escapeHtml(design().bankAccount1 || '')}</span>
                                <span><b>WING</b> ${escapeHtml(design().bankAccount2 || '')}</span>
                            </div>
                            <div class="back-note">
                                If found, please return this card to គ្នាយើង.<br>
                                Holder: ${escapeHtml(employee.name || '')}
                            </div>
                            ${design().showSignatureLine ? '<div class="signature-line">AUTHORIZED STAMP & SIGNATURE</div>' : ''}
                        </div>
                    </div>
                `;
            };

            const renderKneayerngBack = (employee) => {
                return renderAmberGoldBack(employee, 'template-khmer_gold template-kneayerng-amber-back', '#c9aa28');
            };

            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const employeePayload = (employee) => ({
                employee_code: employee.employee_code || '',
                name_khmer: employee.name || '',
                name_english: employee.english_name || '',
                position_khmer: employee.position_khmer || employee.post || '',
                position_english: employee.position_english || '',
                department: employee.department || '',
                branch: employee.branch || '',
                joining_date: employee.joining_date || '',
                emergency_contact: employee.emergency_contact || '',
                blood_type: employee.blood_type || '',
                khqr_account_id: employee.khqr_account_id || '',
                profile_photo_url: employee.photo_url || '',
                phone: employee.phone || '',
                email: employee.email || '',
            });

            const replaceFeatherIcons = (scope = document) => {
                if (typeof feather === 'undefined' || !feather.icons) {
                    return;
                }

                const root = typeof scope === 'string' ? document.querySelector(scope) : scope;
                if (!root) {
                    return;
                }

                const $icons = $(root).is('[data-feather]')
                    ? $(root)
                    : $(root).find('[data-feather]');

                $icons.each(function () {
                    const icon = feather.icons[$(this).data('feather')];
                    if (!icon) {
                        return;
                    }

                    const className = this.getAttribute('class');
                    this.outerHTML = icon.toSvg(className ? { class: className } : {});
                });
            };

            const enhanceCodes = (scope = document) => {
                const $scope = $(scope);

                if (window.JsBarcode) {
                    const $barcodeTargets = $scope.is('.barcode-target') ? $scope : $scope.find('.barcode-target');
                    $barcodeTargets.each(function () {
                        const value = $(this).data('value') || '';
                        if (!value) return;
                        try {
                            JsBarcode(this, value, {
                                format: 'CODE128',
                                displayValue: false,
                                margin: 0,
                                height: 26,
                                width: 1.15,
                            });
                        } catch (error) {
                            $(this).replaceWith(`<small>${escapeHtml(value)}</small>`);
                        }
                    });
                }

                if (window.QRCode) {
                    const $qrTargets = $scope.is('.khqr-generated') ? $scope : $scope.find('.khqr-generated');
                    $qrTargets.each(function () {
                        const value = $(this).data('value') || '';
                        if (!value || $(this).children().length) return;
                        const size = parseInt($(this).data('qr-px'), 10) || 76;
                        try {
                            new QRCode(this, {
                                text: value,
                                width: size,
                                height: size,
                                correctLevel: window.QRCode.CorrectLevel ? window.QRCode.CorrectLevel.M : 0,
                            });
                        } catch (error) {
                            $(this).html(`<small>${escapeHtml(value)}</small>`);
                        }
                    });
                }
            };

            const frontDetailRows = (employee) => {
                const config = design();
                const rows = [];
                const nameValue = [
                    config.showKhmerName ? escapeHtml(employee.name || '') : '',
                    config.showEnglishName && employee.english_name ? `<small>${escapeHtml(employee.english_name)}</small>` : '',
                ].join('');

                if (nameValue) {
                    rows.push(`<div class="employee-name-row"><span>ឈ្មោះ:</span><strong>${nameValue}</strong></div>`);
                }
                if (config.showEmployeeCode) {
                    rows.push(`<div><span>លេខកូដ:</span><strong>${escapeHtml(employee.employee_code || '')}</strong></div>`);
                }
                if (config.showPosition) {
                    rows.push(`<div><span>មុខតំណែង:</span><strong>${escapeHtml(employee.position_khmer || employee.post || employee.position_english || '')}</strong></div>`);
                }
                if (config.showBranch) {
                    rows.push(`<div><span>សាខា:</span><strong>${escapeHtml(employeeBranchLabel(employee))}</strong></div>`);
                }
                if (config.showDepartment) {
                    rows.push(`<div><span>ផ្នែក:</span><strong>${escapeHtml(employee.department || '')}</strong></div>`);
                }
                if (config.showPhone) {
                    rows.push(`<div><span>ទូរស័ព្ទ:</span><strong>${escapeHtml(employee.phone || '')}</strong></div>`);
                }

                return rows.join('');
            };

            const frontDetailBlock = (employee) => {
                const rows = frontDetailRows(employee);
                return rows ? `<div class="id-details id-details-khmer">${rows}</div>` : '';
            };

            const renderFront = (employee) => {
                if (design().templateStyle === 'kneayerng_gold') {
                    return renderKneayerngFront(employee);
                }

                return `
                    <div class="id-card-wrap">
                        <div class="id-card id-card-front ${templateClass()} ${design().showSideBrand ? '' : 'no-side-brand'}" style="${cardDimensionStyle()}">
                            ${design().showBleedGuide ? '<div class="bleed-guide"></div>' : ''}
                            ${design().showCutLines ? '<div class="cut-line-guide"></div>' : ''}
                            ${design().showSafeZone ? '<div class="safe-zone-guide"></div>' : ''}
                            ${design().showSideBrand ? sideBrandRail(employee) : '<div class="id-side id-side-empty"></div>'}
                            <div class="id-body ${design().showPhoto ? '' : 'no-photo'}">
                                ${design().showPhoto ? `<span class="drag-size-frame front-photo-drag"><img class="id-photo ${design().photoShape}" src="${employee.photo_url}" alt="${escapeHtml(employee.name)}" style="${photoStyle()}">${dragHandle('front-photo')}</span>` : ''}
                                ${frontDetailBlock(employee)}
                                ${design().showBarcode ? `<div class="id-code">
                                    <div class="barcode-visual"><svg class="barcode-target" data-value="${escapeHtml(employee.employee_code)}"></svg></div>
                                    <small>ID No : <b>${escapeHtml(employee.employee_code)}</b></small>
                                </div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            };

            const renderBack = (employee) => {
                if (design().templateStyle === 'kneayerng_gold') {
                    return renderKneayerngBack(employee);
                }

                return renderAmberGoldBack(employee);
            };

            const renderEmployeePicker = () => {
                const keyword = ($('#employeeSearch').val() || '').toLowerCase();
                const branchFilter = $('#employeeBranchFilter').val() || '';
                const departmentFilter = $('#employeeDepartmentFilter').val() || '';
                const html = employees.map((employee, index) => {
                    const search = `${employee.employee_code || ''} ${employee.name || ''} ${employee.english_name || ''} ${employee.branch || ''} ${employee.department || ''}`.toLowerCase();
                    const matchesKeyword = !keyword || search.includes(keyword);
                    const matchesBranch = !branchFilter || String(employee.branch || '') === branchFilter;
                    const matchesDepartment = !departmentFilter || String(employee.department || '') === departmentFilter;
                    const hidden = matchesKeyword && matchesBranch && matchesDepartment ? '' : 'display:none';

                    return `
                        <div class="employee-option ${employee.selected !== false ? 'is-selected' : ''}" style="${hidden}" data-index="${index}">
                            <input type="checkbox" class="employee-check" data-index="${index}" ${employee.selected !== false ? 'checked' : ''}>
                            <div class="employee-photo-thumb">
                                <img src="${employee.photo_url}" alt="${escapeHtml(employee.name)}">
                                <span class="employee-photo-hover" title="Quick update photo">Update</span>
                                <div class="employee-photo-quick">
                                    <label class="btn btn-outline-secondary btn-sm mb-0" title="Upload photo">
                                        Upload
                                        <input type="file" class="d-none quick-photo-file" data-index="${index}" accept="image/*">
                                    </label>
                                    <span class="quick-photo-status">Saving...</span>
                                </div>
                            </div>
                            <span class="employee-option-main">
                                <strong>${escapeHtml(employee.name)}</strong>
                                <small>${escapeHtml(employee.employee_code)} · ${escapeHtml(employee.post || employee.department || '{{ __('index.not_available') }}')}</small>
                            </span>
                        </div>
                    `;
                }).join('');

                $('.employee-picker').html(html);
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            };

            const renderBatchRows = () => {
                const html = employees.map((employee, index) => `
                    <tr>
                        <td><input type="checkbox" class="batch-selected" data-index="${index}" ${employee.selected !== false ? 'checked' : ''}></td>
                        <td>
                            <img class="batch-photo mb-1" src="${employee.photo_url}" alt="">
                            <input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="photo_url" value="${escapeHtml(employee.photo_url || '')}" placeholder="Photo URL">
                        </td>
                        <td><input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="employee_code" value="${escapeHtml(employee.employee_code)}"></td>
                        <td>
                            <input class="form-control form-control-sm batch-edit mb-1" data-index="${index}" data-field="name" value="${escapeHtml(employee.name)}">
                            <input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="english_name" value="${escapeHtml(employee.english_name)}" placeholder="English">
                        </td>
                        <td>
                            <input class="form-control form-control-sm batch-edit mb-1" data-index="${index}" data-field="position_khmer" value="${escapeHtml(employee.position_khmer || employee.post || '')}" placeholder="Position Khmer">
                            <input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="position_english" value="${escapeHtml(employee.position_english || '')}" placeholder="Position EN">
                        </td>
                        <td>
                            <input class="form-control form-control-sm batch-edit mb-1" data-index="${index}" data-field="department" value="${escapeHtml(employee.department || '')}" placeholder="Department">
                            <input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="branch" value="${escapeHtml(employee.branch || '')}" placeholder="Branch">
                            <input class="form-control form-control-sm batch-edit mt-1" type="date" data-index="${index}" data-field="joining_date" value="${escapeHtml(employee.joining_date || '')}">
                        </td>
                        <td>
                            <input class="form-control form-control-sm batch-edit mb-1" data-index="${index}" data-field="emergency_contact" value="${escapeHtml(employee.emergency_contact || '')}" placeholder="Emergency">
                            <input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="blood_type" value="${escapeHtml(employee.blood_type || '')}" placeholder="Blood">
                        </td>
                        <td><input class="form-control form-control-sm batch-edit" data-index="${index}" data-field="khqr_account_id" value="${escapeHtml(employee.khqr_account_id || '')}" placeholder="KHQR"></td>
                        <td class="text-nowrap">
                            <button type="button" class="btn btn-outline-success btn-xs batch-save" data-index="${index}"><i class="link-icon" data-feather="save"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-xs batch-delete" data-index="${index}"><i class="link-icon" data-feather="trash-2"></i></button>
                        </td>
                    </tr>
                `).join('');

                $('#batchEmployeeRows').html(html);

                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            };

            const renderEditorPreview = () => {
                const employee = activeEmployee();
                $('#zoomValue').text(previewZoom);
                $('#editorPreview').css('transform', `scale(${previewZoom / 120})`);
                $('#editorPreview').html(employee ? `
                    <div class="preview-card-column">
                        <div class="preview-label"><i data-feather="credit-card"></i> FRONT SIDE PREVIEW</div>
                        ${renderFront(employee)}
                    </div>
                    <div class="preview-card-column">
                        <div class="preview-label"><i data-feather="grid"></i> BACK SIDE PREVIEW</div>
                        ${renderBack(employee)}
                    </div>
                ` : '<p class="text-muted">No employee selected.</p>');

                replaceFeatherIcons('#editorPreview');
                enhanceCodes('#editorPreview');
            };

            const renderCardItems = (employee) => {
                const mode = $('#printMode').val();
                if (mode === 'front') return renderFront(employee);
                if (mode === 'back') return renderBack(employee);
                return renderFront(employee) + renderBack(employee);
            };

            const updateEmployeeCounts = () => {
                const selected = selectedEmployees();
                $('#selectedCount').text(selected.length);
                $('#totalCount').text(employees.length);
                $('#a4SelectedBadge').text(selected.length);
                return selected;
            };

            const renderPages = () => {
                const selected = updateEmployeeCounts();
                const cardsPerPage = parseInt($('#cardsPerPage').val(), 10);
                const mode = $('#printMode').val();
                const employeesPerPage = mode === 'front_back' ? Math.max(1, Math.floor(cardsPerPage / 2)) : cardsPerPage;
                const pages = chunk(selected, employeesPerPage);
                const layout = printLayout();
                const pageClasses = `${layout.showIndex ? '' : ' no-index'}${layout.showCutMarks ? '' : ' no-cut'}`;

                const html = pages.map((pageEmployees) => {
                    let items = '';

                    if (mode === 'front_back' && layout.pairMode === 'top_bottom') {
                        items = chunk(pageEmployees, 2)
                            .map((rowEmployees) => rowEmployees.map(renderFront).join('') + rowEmployees.map(renderBack).join(''))
                            .join('');
                    } else {
                        items = pageEmployees.map(renderCardItems).join('');
                    }

                    return `
                    <div class="a4-page${pageClasses}">
                        <div class="a4-index">
                            <strong>ID</strong>
                            ${pageEmployees.map((employee) => `<div>${escapeHtml(employee.employee_code)}</div>`).join('')}
                        </div>
                        <div class="a4-grid" style="margin-top:${layout.marginTop}mm;margin-left:${layout.marginLeft}mm;column-gap:${layout.gapX}mm;row-gap:${layout.gapY}mm;">
                            ${items}
                        </div>
                    </div>
                `;
                }).join('');

                $('#printArea').html(html);
                $('#printAreaForPaper').html(html);
                enhanceCodes('#printArea, #printAreaForPaper');
            };

            const phpOpen = '<' + '?php';

            const codeSnippets = () => {
                const config = design();
                const layout = printLayout();
                const exportedPhotoRadius = config.photoShape === 'circle' ? '50%' : `${config.photoRadius}mm`;

                return {
                    blade: `resources/views/admin/dCardPrint/print_a4.blade.php
@@extends('layouts.master')

@@section('title', 'គ្នាយើង - A4 ID Cards Print Studio')

@@section('main-content')
<div class="digital-hrs-print-studio">
    <div class="no-print print-toolbar">
        <div>
            <h2>គ្នាយើង - Laravel ID Card Print Studio</h2>
            <p>Calibrated CR80 A4 Layout with Stacked Pairs & Flexible Photos</p>
        </div>
        <button onclick="window.print()">Print A4 Sheet</button>
    </div>

    @@foreach($employees->chunk(2) as $employeePair)
        <section class="a4-page">
            <aside class="margin-index no-print">
                <strong>ID</strong>
                @@foreach($employeePair as $employee)
                    <div>@{{ $employee->employee_code }}</div>
                @@endforeach
            </aside>

            <!-- Row 1: Front cards -->
            @@foreach($employeePair as $employee)
                <article class="id-card id-card-front">
                    <div class="id-side">
                        <span class="side-logo-badge">
                            @@if(!empty($employee->branch_logo_url))
                                <img src="@{{ $employee->branch_logo_url }}" alt="">
                            @@else
                                <span>DHR</span>
                            @@endif
                        </span>
                        <span class="side-branch-name">@{{ $employee->branch ?? $settings['side_banner_text'] }}</span>
                        <span class="side-logo-badge side-logo-badge-bottom">
                            @@if(!empty($employee->branch_logo_url))
                                <img src="@{{ $employee->branch_logo_url }}" alt="">
                            @@else
                                <span>DHR</span>
                            @@endif
                        </span>
                    </div>
                    <div class="id-body">
                        <img class="id-photo" src="@{{ $employee->profile_photo_url ?? asset('assets/images/img.png') }}" alt="">
                        <h3>@{{ $employee->name_khmer }}</h3>
                        <p>@{{ $employee->name_english }}</p>
                        <dl>
                            <dt>Code</dt><dd>@{{ $employee->employee_code }}</dd>
                            <dt>Position</dt><dd>@{{ $employee->position_khmer }}</dd>
                            <dt>Branch</dt><dd>@{{ $employee->branch }}</dd>
                        </dl>
                        <div class="barcode-visual"><svg class="barcode-target" data-value="@{{ $employee->employee_code }}"></svg></div>
                    </div>
                </article>
            @@endforeach

            <!-- Row 2: Back cards -->
            @@foreach($employeePair as $employee)
                <article class="id-card id-card-back">
                    <h3>@{{ $settings['company_english'] }}</h3>
                    <p>@{{ $settings['back_tagline'] }}</p>
                    <div class="payment-grid">
                        <div class="khqr-generated" data-value="@{{ $employee->khqr_account_id }}"></div>
                    </div>
                    <strong>@{{ $settings['shop_subtitle'] }}</strong>
                    <small>@{{ $settings['merchant_id'] }}</small>
                    <p class="return-note">@{{ $settings['return_note'] }}</p>
                </article>
            @@endforeach
        </section>
    @@endforeach
</div>
@@endsection`,
                    controller: `${phpOpen}

namespace App\\Http\\Controllers\\Web;

use App\\Http\\Controllers\\Controller;
use App\\Models\\DCardEmployee;
use App\\Models\\User;
use Illuminate\\Http\\Request;

class DCardPrintController extends Controller
{
    public function printA4(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->filter()
            ->values();

        $employees = DCardEmployee::query()
            ->when($ids->isNotEmpty(), fn ($query) => $query->whereIn('id', $ids))
            ->orderBy('employee_code')
            ->get();

        $settings = [
            'company_english' => '${config.companyEnglish || 'គ្នាយើង'}',
            'side_banner_text' => '${config.sideBannerText || 'គ្នាយើង'}',
            'back_tagline' => '${config.backTagline || 'Scan. Pay. Done.'}',
            'shop_subtitle' => '${config.shopSubtitle || 'KNEA YERNG PHONE SHOP'}',
            'merchant_id' => '${config.merchantId || 'MID: 125080609411765'}',
            'return_note' => 'If found, please return to the company.',
        ];

        return view('admin.dCardPrint.print_a4', compact('employees', 'settings'));
    }
}`,
                    route: `// routes/web.php

use App\\Http\\Controllers\\Web\\DCardPrintController;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin.auth', 'permission']], function () {
    Route::get('d-card-print', [DCardPrintController::class, 'index'])->name('d-card-print.index');
    Route::get('d-card-print/print-a4', [DCardPrintController::class, 'printA4'])->name('d-card-print.print-a4');
    Route::post('d-card-print/employees', [DCardPrintController::class, 'store'])->name('d-card-print.employees.store');
    Route::put('d-card-print/employees/{employee}', [DCardPrintController::class, 'update'])->name('d-card-print.employees.update');
    Route::delete('d-card-print/employees/{employee}', [DCardPrintController::class, 'destroy'])->name('d-card-print.employees.destroy');
});`,
                    css: `@page { size: A4 portrait; margin: 0; }

.digital-hrs-print-studio {
    font-family: "Kantumruy Pro", Arial, sans-serif;
    background: #f8fafc;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.a4-page {
    width: 210mm;
    height: 297mm;
    position: relative;
    background: #fff;
    page-break-after: always;
    overflow: hidden;
}

.a4-page .id-card {
    width: ${config.width}mm;
    height: ${config.height}mm;
}

.id-card-front {
    display: grid;
    grid-template-columns: 13mm 1fr;
}

.id-side {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    padding: 3mm 0 3.2mm;
    color: #fff;
}

.side-logo-badge {
    width: 8.5mm;
    height: 8.5mm;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.72);
}

.side-logo-badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.side-branch-name {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    font-family: "Moul", "Kantumruy Pro", Arial, sans-serif;
    font-size: 10.5px;
    font-weight: 800;
}

.id-body {
    padding: 4mm 3mm 3mm;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.a4-grid {
    display: grid;
    grid-template-columns: repeat(2, max-content);
    margin-top: ${layout.marginTop}mm;
    margin-left: ${layout.marginLeft}mm;
    column-gap: ${layout.gapX}mm;
    row-gap: ${layout.gapY}mm;
}

.id-photo {
    width: ${config.photoWidth}mm;
    height: ${config.photoHeight}mm;
    object-fit: ${config.photoFit};
    border-radius: ${exportedPhotoRadius};
}

.id-details-khmer {
    width: 36mm;
    margin-top: 9mm;
    display: flex;
    flex-direction: column;
    gap: 1.55mm;
    font-size: 8.2px;
    font-weight: 800;
}

.id-details-khmer div {
    display: grid;
    grid-template-columns: 12mm minmax(0, 1fr);
    gap: 2.6mm;
}

.id-details-khmer strong {
    font-family: "Kantumruy Pro", "Noto Sans Khmer", Arial, sans-serif;
    font-size: 9.6px;
    line-height: 1.22;
    overflow-wrap: anywhere;
}

.id-code {
    margin-top: auto;
    width: 38mm;
    text-align: center;
    border-top: 1px solid #e1e7ef;
    padding-top: 1.45mm;
}

.barcode-visual {
    width: 30mm;
    height: 7.2mm;
    margin: 0 auto;
    background: repeating-linear-gradient(90deg, #111827 0 1px, transparent 1px 2px, #111827 2px 3px, transparent 3px 5px, #111827 5px 7px, transparent 7px 9px);
}

.margin-index {
    position: absolute;
    top: 10mm;
    left: 3mm;
    width: 11mm;
    font-size: 7px;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
}

@media print {
    .no-print { display: none !important; }
    body * { visibility: hidden; }
    .digital-hrs-print-studio,
    .digital-hrs-print-studio * { visibility: visible; }
    .a4-page { box-shadow: none !important; margin: 0 !important; }
}`
                };
            };

            const renderLaravelCode = () => {
                const snippets = codeSnippets();
                $('#laravelCodeOutput').text(snippets[activeCodeTab] || snippets.blade);
            };

            let previewRenderFrame = null;
            let pagesRenderTimer = null;
            let codeRenderTimer = null;

            const scheduleEditorPreview = () => {
                if (previewRenderFrame) {
                    return;
                }

                previewRenderFrame = window.requestAnimationFrame(() => {
                    previewRenderFrame = null;
                    renderEditorPreview();
                });
            };

            const schedulePagesRender = (delay = 180) => {
                window.clearTimeout(pagesRenderTimer);
                pagesRenderTimer = window.setTimeout(() => {
                    pagesRenderTimer = null;
                    renderPages();
                }, delay);
            };

            const scheduleLaravelCodeRender = (delay = 240) => {
                window.clearTimeout(codeRenderTimer);
                codeRenderTimer = window.setTimeout(() => {
                    codeRenderTimer = null;
                    renderLaravelCode();
                }, delay);
            };

            const scheduleDesignRender = () => {
                scheduleEditorPreview();
                if ($('#studio-a4print').hasClass('active')) {
                    schedulePagesRender();
                }
                if ($('#studio-laravel').hasClass('active') || $('#laravelCodeOutput').is(':visible')) {
                    scheduleLaravelCodeRender();
                }
            };

            const refreshStudio = () => {
                renderEmployeeFilters();
                renderEmployeePicker();
                renderBatchRows();
                renderEditorPreview();
                if ($('#studio-a4print').hasClass('active')) {
                    renderPages();
                } else {
                    updateEmployeeCounts();
                }
                renderLaravelCode();
            };

            const setTemplateColors = (cardColor, accentColor, templateButton = null) => {
                const safeCardColor = cardColor || '#f59e0b';
                const safeAccentColor = accentColor || '#1f2937';
                const templateStyle = templateButton ? ($(templateButton).data('template') || 'khmer_gold') : 'custom';

                $('#cardColor, #brandCardColor, #templateCardColor').val(safeCardColor);
                $('#accentColor, #brandAccentColor, #templateAccentColor').val(safeAccentColor);
                $('#templateStyle').val(templateStyle);

                if (templateButton) {
                    $('.template-preset').removeClass('active');
                    $(templateButton).addClass('active');
                } else {
                    $('.template-preset').removeClass('active');
                    const match = $('.template-preset').filter(function () {
                        return String($(this).data('card')).toLowerCase() === String(safeCardColor).toLowerCase()
                            && String($(this).data('accent')).toLowerCase() === String(safeAccentColor).toLowerCase();
                    }).first();
                    if (match.length) match.addClass('active');
                }

                scheduleDesignRender();
            };

            $('#photoPreset').on('change', function () {
                const preset = $(this).val();
                const isEmployeeTemplate = ($('#templateStyle').val() || '') === 'kneayerng_gold';
                const sizes = {
                    auto: isEmployeeTemplate ? [34, 33.5] : [27, 36],
                    passport: [30, 40],
                    square: [30, 30],
                    hero: [34, 42],
                };

                if (sizes[preset]) {
                    $('#photoWidthMm').val(sizes[preset][0]);
                    $('#photoHeightMm').val(sizes[preset][1]);
                }
            });

            $('#photoWidthMm, #photoHeightMm, #photoRadiusMm').on('input change', function () {
                $('#photoPreset').val('custom');
            });

            $('.color-sync').on('change input', function () {
                $($(this).data('sync-target')).val($(this).val());
                setTemplateColors($('#cardColor').val(), $('#accentColor').val());
            });

            $('#cardColor').on('change input', function () {
                setTemplateColors($(this).val(), $('#accentColor').val());
            });

            $('#accentColor').on('change input', function () {
                setTemplateColors($('#cardColor').val(), $(this).val());
            });

            $('#templateCardColor').on('change input', function () {
                setTemplateColors($(this).val(), $('#templateAccentColor').val());
            });

            $('#templateAccentColor').on('change input', function () {
                setTemplateColors($('#templateCardColor').val(), $(this).val());
            });

            const syncA4CardDimensions = (width, height) => {
                $('#cardWidthMm, #a4CardWidthMm').val(width);
                $('#cardHeightMm, #a4CardHeightMm').val(height);
            };

            const syncA4CardScale = (scale) => {
                $('#cardScale, #a4CardScale').val(scale);
                $('#a4ScaleValue').text(scale);
            };

            syncA4CardDimensions($('#cardWidthMm').val(), $('#cardHeightMm').val());
            syncA4CardScale($('#a4CardScale').val());

            $('#cardOrientation').on('change', function () {
                if ($(this).val() === 'landscape') {
                    syncA4CardDimensions('103', '71');
                } else {
                    syncA4CardDimensions('71', '103');
                }
            });

            $('[data-a4-mode]').on('click', function () {
                $('[data-a4-mode]').removeClass('active');
                $(this).addClass('active');
                $('#printMode').val($(this).data('a4-mode'));
                if ($(this).data('a4-pair')) {
                    $('#pairMode').val($(this).data('a4-pair'));
                }
                renderPages();
                renderLaravelCode();
            });

            $('#toggleMarginIndex, #toggleCutMarks').on('click', function () {
                $(this).toggleClass('active');
                if (this.id === 'toggleMarginIndex') {
                    $(this).html($(this).hasClass('active') ? '<i data-feather="list"></i> Margin List ON' : '<i data-feather="list"></i> Margin List OFF');
                } else {
                    $(this).html($(this).hasClass('active') ? '<i data-feather="check-circle"></i> Cut Marks ON' : '<i data-feather="circle"></i> Cut Marks OFF');
                }
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
                renderPages();
                renderLaravelCode();
            });

            $('#a4CardScale').on('input change', function () {
                syncA4CardScale($(this).val());
                scheduleDesignRender();
            });

            $('#cardScale').on('input change', function () {
                syncA4CardScale($(this).val());
            });

            $('#a4CardWidthMm, #a4CardHeightMm').on('input change', function () {
                $('#cardWidthMm').val($('#a4CardWidthMm').val());
                $('#cardHeightMm').val($('#a4CardHeightMm').val());
            });

            $('#cardWidthMm, #cardHeightMm').on('input change', function () {
                $('#a4CardWidthMm').val($('#cardWidthMm').val());
                $('#a4CardHeightMm').val($('#cardHeightMm').val());
            });

            $('#downloadA4Pdf').on('click', function () {
                $('#exportPdf').trigger('click');
            });

            $('#printA4Now').on('click', function () {
                renderPages();
                window.print();
            });

            $('.designer-control, .print-layout-control, #printMode, #cardsPerPage, #cardColor, #accentColor').on('change input', function () {
                scheduleDesignRender();
            });

            $('body').on('pointerdown', '.drag-size-handle', function (event) {
                if (!$(this).closest('#editorPreview').length) {
                    return;
                }

                event.preventDefault();

                const target = $(this).data('resize-target');
                const frame = this.closest('.drag-size-frame');
                const card = this.closest('.id-card');

                if (!frame || !card) {
                    return;
                }

                const item = target === 'front-photo'
                    ? frame.querySelector('.id-photo')
                    : frame.querySelector('.khqr-generated, img');

                if (!item) {
                    return;
                }

                const cardRect = card.getBoundingClientRect();
                const itemRect = item.getBoundingClientRect();
                const config = design();
                const renderedCardWidthMm = config.width * (config.scale / 100);
                const pxPerMm = cardRect.width / renderedCardWidthMm;
                const startX = event.clientX;
                const startY = event.clientY;
                const startWidthMm = itemRect.width / pxPerMm;
                const startHeightMm = itemRect.height / pxPerMm;
                const pointerId = event.originalEvent && event.originalEvent.pointerId;

                if (typeof this.setPointerCapture === 'function' && pointerId !== undefined) {
                    this.setPointerCapture(pointerId);
                }

                const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
                const updatePaymentBox = () => {
                    const qrSize = clamp(parseFloat($('#paymentQrSizeMm').val()) || 27.3, 12, 40);
                    const padding = clamp(parseFloat($('#paymentPaddingMm').val()) || 2, 0, 6);
                    const minHeight = Math.max(30, qrSize + (padding * 2));
                    $('#editorPreview .payment-qr-frame').css({
                        minHeight: `${minHeight}mm`,
                    });
                };

                $(document).on('pointermove.dragSize', (moveEvent) => {
                    const pointerEvent = moveEvent.originalEvent || moveEvent;
                    const deltaX = (pointerEvent.clientX - startX) / pxPerMm;
                    const deltaY = (pointerEvent.clientY - startY) / pxPerMm;

                    if (target === 'front-photo') {
                        const nextWidth = clamp(startWidthMm + deltaX, 12, 45);
                        const nextHeight = clamp(startHeightMm + deltaY, 12, 52);

                        $('#photoPreset').val('custom');
                        $('#photoWidthMm').val(nextWidth.toFixed(1));
                        $('#photoHeightMm').val(nextHeight.toFixed(1));
                        $('#editorPreview .front-photo-drag .id-photo').css({
                            width: `${nextWidth}mm`,
                            height: `${nextHeight}mm`,
                        });
                    } else if (target === 'payment-qr') {
                        const nextSize = clamp(Math.max(startWidthMm + deltaX, startHeightMm + deltaY), 12, 40);

                        $('#paymentQrSizeMm').val(nextSize.toFixed(1));
                        $('#editorPreview .payment-qr-drag .khqr-generated, #editorPreview .payment-qr-drag img').css({
                            width: `${nextSize}mm`,
                            height: `${nextSize}mm`,
                        });
                        updatePaymentBox();
                    }
                });

                $(document).one('pointerup.dragSize pointercancel.dragSize', () => {
                    $(document).off('pointermove.dragSize');
                    renderEditorPreview();
                    renderPages();
                    renderLaravelCode();
                });
            });

            $('body').on('change', '.employee-check', function () {
                employees[$(this).data('index')].selected = $(this).is(':checked');
                $(this).closest('.employee-option').toggleClass('is-selected', $(this).is(':checked'));
                renderBatchRows();
                renderPages();
            });

            $('body').on('click', '.employee-option', function (event) {
                if ($(event.target).closest('input, button, label, a').length) {
                    return;
                }

                const checkbox = $(this).find('.employee-check');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            $('body').on('change', '.batch-selected', function () {
                employees[$(this).data('index')].selected = $(this).is(':checked');
                renderEmployeePicker();
                renderPages();
            });

            $('body').on('input', '.batch-edit', function () {
                employees[$(this).data('index')][$(this).data('field')] = $(this).val();
                renderEmployeePicker();
                renderEditorPreview();
                renderPages();
            });

            const autoSaveQuickPhoto = (index, file) => {
                const employee = employees[index];

                if (!(file instanceof File)) {
                    return;
                }

                const isExisting = employee.source === 'dcard' && employee.record_id;
                const payload = new FormData();

                Object.entries(employeePayload(employee)).forEach(([key, value]) => {
                    if (key === 'profile_photo_url') {
                        value = '';
                    }
                    payload.append(key, value || '');
                });
                payload.append('profile_photo', file);
                if (isExisting) {
                    payload.append('_method', 'PUT');
                }

                const status = $(`.quick-photo-file[data-index="${index}"]`).closest('.employee-photo-quick').find('.quick-photo-status');
                status.text('Saving...').show();

                $.ajax({
                    url: isExisting
                        ? `{{ url('admin/d-card-print/employees') }}/${employee.record_id}`
                        : `{{ route('admin.d-card-print.employees.store') }}`,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: payload,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        employees[index] = {
                            ...employee,
                            ...response.employee,
                            branch_logo_url: response.employee.branch_logo_url || employee.branch_logo_url,
                            company: response.employee.company || employee.company,
                            company_address: response.employee.company_address || employee.company_address,
                            company_phone: response.employee.company_phone || employee.company_phone,
                            payment_qr_codes: (response.employee.payment_qr_codes || []).length
                                ? response.employee.payment_qr_codes
                                : (employee.payment_qr_codes || []),
                            selected: employee.selected !== false,
                        };
                        refreshStudio();
                    },
                    error: function (xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not save uploaded photo.');
                    },
                    complete: function () {
                        status.hide();
                    },
                });
            };

            $('body').on('change', '.quick-photo-file', function () {
                const index = $(this).data('index');
                const file = this.files && this.files[0];
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    employees[index].photo_url = event.target.result;
                    employees[index].quick_photo_file = file;
                    renderEmployeePicker();
                    renderBatchRows();
                    renderEditorPreview();
                    renderPages();
                    autoSaveQuickPhoto(index, file);
                };
                reader.readAsDataURL(file);
            });

            $('body').on('click', '.batch-delete', function () {
                const index = $(this).data('index');
                const employee = employees[index];

                if (employee.source === 'dcard' && employee.record_id) {
                    $.ajax({
                        url: `{{ url('admin/d-card-print/employees') }}/${employee.record_id}`,
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                    });
                }

                employees.splice(index, 1);
                previewIndex = Math.min(previewIndex, employees.length - 1);
                refreshStudio();
            });

            $('body').on('click', '.batch-save', function () {
                const index = $(this).data('index');
                const employee = employees[index];
                const isExisting = employee.source === 'dcard' && employee.record_id;

                $.ajax({
                    url: isExisting
                        ? `{{ url('admin/d-card-print/employees') }}/${employee.record_id}`
                        : `{{ route('admin.d-card-print.employees.store') }}`,
                    method: isExisting ? 'PUT' : 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: employeePayload(employee),
                    success: function (response) {
                        employees[index] = { ...response.employee, selected: employee.selected !== false };
                        refreshStudio();
                    },
                    error: function (xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not save employee row.');
                    },
                });
            });

            $('#selectAllEmployees').on('click', function () {
                employees = employees.map((employee) => ({ ...employee, selected: true }));
                refreshStudio();
            });

            $('#clearEmployees').on('click', function () {
                employees = employees.map((employee) => ({ ...employee, selected: false }));
                refreshStudio();
            });

            $('#batchSelectAll').on('click', function () {
                employees = employees.map((employee) => ({ ...employee, selected: true }));
                refreshStudio();
            });

            $('#batchDeselectAll').on('click', function () {
                employees = employees.map((employee) => ({ ...employee, selected: false }));
                refreshStudio();
            });

            $('#addLocalEmployee').on('click', function () {
                employees.unshift({
                    id: `local-${Date.now()}`,
                    employee_code: `KY${String(employees.length + 1).padStart(4, '0')}`,
                    source: 'local',
                    record_id: null,
                    name: 'New Employee',
                    english_name: '',
                    position_khmer: '',
                    position_english: '',
                    phone: '',
                    email: '',
                    photo_url: '{{ asset('assets/images/img.png') }}',
                    branch: '',
                    branch_logo_url: null,
                    department: '',
                    post: '',
                    joining_date: '',
                    emergency_contact: '',
                    blood_type: '',
                    khqr_account_id: '',
                    company: companyValue('name') || '',
                    company_address: companyValue('address') || '',
                    company_phone: companyValue('phone') || '',
                    payment_qr_codes: [],
                    selected: true,
                });
                previewIndex = 0;
                refreshStudio();
            });

            $('#importCsvRows').on('click', function () {
                const rows = ($('#csvImportText').val() || '').split(/\r?\n/).map((row) => row.trim()).filter(Boolean);
                rows.forEach((row, offset) => {
                    const parts = row.split(',').map((part) => part.trim());
                    employees.push({
                        id: `import-${Date.now()}-${offset}`,
                        source: 'local',
                        record_id: null,
                        employee_code: parts[0] || `KY${String(employees.length + 1).padStart(4, '0')}`,
                        name: parts[1] || '',
                        english_name: parts[2] || '',
                        position_khmer: parts[3] || '',
                        position_english: '',
                        post: parts[3] || '',
                        branch: parts[4] || '',
                        phone: parts[5] || '',
                        photo_url: parts[6] || '{{ asset('assets/images/img.png') }}',
                        khqr_account_id: parts[7] || '',
                        branch_logo_url: null,
                        department: parts[3] || '',
                        joining_date: '',
                        emergency_contact: '',
                        blood_type: '',
                        email: '',
                        company: companyValue('name') || '',
                        company_address: companyValue('address') || '',
                        company_phone: companyValue('phone') || '',
                        payment_qr_codes: [],
                        selected: true,
                    });
                });
                $('#csvImportText').val('');
                refreshStudio();
            });

            $('#employeeSearch').on('input', function () {
                renderEmployeePicker();
            });

            $('#employeeBranchFilter').on('change', function () {
                renderEmployeeFilters();
                renderEmployeePicker();
            });

            $('#employeeDepartmentFilter').on('change', function () {
                renderEmployeePicker();
            });

            $('#clearEmployeeFilters').on('click', function () {
                $('#employeeBranchFilter, #employeeDepartmentFilter').val('');
                renderEmployeePicker();
            });

            $('.template-preset').on('click', function () {
                if ($(this).data('width') && $(this).data('height')) {
                    $('#cardOrientation').val('portrait');
                    syncA4CardDimensions(String($(this).data('width')), String($(this).data('height')));
                }
                if ($(this).data('photo-width') && $(this).data('photo-height')) {
                    $('#photoPreset').val('custom');
                    $('#photoWidthMm').val($(this).data('photo-width'));
                    $('#photoHeightMm').val($(this).data('photo-height'));
                    $('#photoShape').val('rounded');
                    $('#photoFit').val('cover');
                }
                setTemplateColors($(this).data('card'), $(this).data('accent'), this);
            });

            $('[data-editor-section]').on('click', function () {
                const section = $(this).data('editor-section');
                $('[data-editor-section]').removeClass('active');
                $(this).addClass('active');
                $('[data-editor-content]').removeClass('active');
                $(`[data-editor-content="${section}"]`).addClass('active');
            });

            $('#resetStudio').on('click', function () {
                $('#cardOrientation').val('portrait');
                syncA4CardDimensions('71', '103');
                $('#bleedMm').val('1.5');
                syncA4CardScale('100');
                $('#marginTopMm').val('12');
                $('#marginLeftMm').val('16');
                $('#gapXMm').val('10');
                $('#gapYMm').val('10');
                $('#photoShape').val('rectangle');
                $('#photoPreset').val('auto');
                $('#photoFit').val('cover');
                $('#photoZoom').val('100');
                $('#photoWidthMm').val('34');
                $('#photoHeightMm').val('33.5');
                $('#photoRadiusMm').val('3');
                $('#frontTextScale').val('100');
                $('#khmerNameFont').val('"Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif');
                $('#khmerNameSize').val('12');
                $('#englishNameFont').val('Arial, "Poppins", sans-serif');
                $('#englishNameSize').val('8');
                $('#paymentQrSizeMm').val('27.3');
                $('#paymentPaddingMm').val('2');
                $('#paymentQrFit').val('contain');
                $('#showPhoto, #showSideBrand, #showKhmerName, #showEnglishName, #showPosition, #showDepartment, #showBranch, #showPhone, #showBarcode, #showPaymentQr, #showCutLines, #showBleedGuide, #showSignatureLine').prop('checked', true);
                $('#showEmployeeCode').prop('checked', false);
                $('#showSafeZone').prop('checked', false);
                setTemplateColors('#0B172A', '#C59B27', $('.template-preset').first());
            });

            $('[data-studio-tab]').on('click', function () {
                const tab = $(this).data('studio-tab');
                $('[data-studio-tab]').removeClass('active');
                $(this).addClass('active');
                $('.studio-pane').removeClass('active');
                $(`#studio-${tab}`).addClass('active');
                renderPages();
            });

            $('[data-code-tab]').on('click', function () {
                activeCodeTab = $(this).data('code-tab');
                $('[data-code-tab]').removeClass('active');
                $(this).addClass('active');
                renderLaravelCode();
            });

            $('#copyCodeSnippet').on('click', function () {
                const text = $('#laravelCodeOutput').text();
                const button = $(this);
                const original = button.html();
                const done = () => {
                    button.html('<i data-feather="check"></i> Copied to Clipboard!');
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                    setTimeout(() => {
                        button.html(original);
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    }, 1600);
                };

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(done).catch(() => alert('Could not copy code.'));
                    return;
                }

                const textarea = $('<textarea>').val(text).appendTo('body').select();
                document.execCommand('copy');
                textarea.remove();
                done();
            });

            $('#previewPrev').on('click', function () {
                previewIndex = employees.length ? (previewIndex - 1 + employees.length) % employees.length : 0;
                renderEditorPreview();
            });

            $('#previewNext').on('click', function () {
                previewIndex = employees.length ? (previewIndex + 1) % employees.length : 0;
                renderEditorPreview();
            });

            $('#zoomOut').on('click', function () {
                previewZoom = Math.max(80, previewZoom - 10);
                renderEditorPreview();
            });

            $('#zoomIn').on('click', function () {
                previewZoom = Math.min(160, previewZoom + 10);
                renderEditorPreview();
            });

            $('#exportPng').on('click', function () {
                if (!window.html2canvas) {
                    alert('PNG export library is not loaded. Please use browser print.');
                    return;
                }

                html2canvas(document.querySelector('#editorPreview'), {
                    scale: 4,
                    useCORS: true,
                    backgroundColor: '#f8fafc',
                }).then((canvas) => {
                    const link = document.createElement('a');
                    link.download = `Knea_Yerng_ID_Card_${Date.now()}_300DPI.png`;
                    link.href = canvas.toDataURL('image/png', 1.0);
                    link.click();
                }).catch(() => alert('Could not export PNG. Please use browser print.'));
            });

            $('#exportPdf').on('click', async function () {
                renderPages();

                if (!window.html2canvas || !window.jspdf || !window.jspdf.jsPDF) {
                    window.print();
                    return;
                }

                const captureRoot = document.createElement('div');
                captureRoot.style.position = 'fixed';
                captureRoot.style.left = '-9999px';
                captureRoot.style.top = '0';
                captureRoot.style.background = '#ffffff';
                captureRoot.innerHTML = $('#printAreaForPaper').html();
                document.body.appendChild(captureRoot);

                try {
                    const pdf = new window.jspdf.jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
                    const pages = captureRoot.querySelectorAll('.a4-page');

                    for (let index = 0; index < pages.length; index++) {
                        const canvas = await html2canvas(pages[index], {
                            scale: 3,
                            useCORS: true,
                            backgroundColor: '#ffffff',
                        });
                        const image = canvas.toDataURL('image/png', 1.0);
                        if (index > 0) {
                            pdf.addPage('a4', 'portrait');
                        }
                        pdf.addImage(image, 'PNG', 0, 0, 210, 297);
                    }

                    pdf.save(`Knea_Yerng_A4_ID_Cards_${Date.now()}.pdf`);
                } catch (error) {
                    alert('Could not export PDF. Please use Quick Print A4.');
                } finally {
                    captureRoot.remove();
                }
            });

            $('#printCards').on('click', function () {
                renderPages();
                window.print();
            });

            refreshStudio();
            window.digitalHrsMainReady = true;
        });
    </script>
    <script>
        (function () {
            function ready(fn) {
                if (document.readyState !== 'loading') {
                    fn();
                    return;
                }
                document.addEventListener('DOMContentLoaded', fn);
            }

            function each(selector, callback) {
                var items = document.querySelectorAll(selector);
                for (var i = 0; i < items.length; i += 1) {
                    callback(items[i], i);
                }
            }

            function showStudioTab(tab) {
                each('[data-studio-tab]', function (button) {
                    button.classList.toggle('active', button.getAttribute('data-studio-tab') === tab);
                });
                each('.studio-pane', function (pane) {
                    pane.classList.remove('active');
                });
                var pane = document.getElementById('studio-' + tab);
                if (pane) {
                    pane.classList.add('active');
                }
            }

            function showEditorSection(section) {
                each('[data-editor-section]', function (button) {
                    button.classList.toggle('active', button.getAttribute('data-editor-section') === section);
                });
                each('[data-editor-content]', function (panel) {
                    panel.classList.toggle('active', panel.getAttribute('data-editor-content') === section);
                });
            }

            function showCodeTab(tab) {
                each('[data-code-tab]', function (button) {
                    button.classList.toggle('active', button.getAttribute('data-code-tab') === tab);
                });

                var output = document.getElementById('laravelCodeOutput');
                if (!output || output.textContent.trim()) {
                    return;
                }

                output.textContent = [
                    'គ្នាយើង Laravel Code Export',
                    '',
                    'Route: /admin/d-card-print',
                    'Controller: App\\Http\\Controllers\\Web\\DCardPrintController',
                    'View: resources/views/admin/dCardPrint/index.blade.php',
                    '',
                    'Use Template Designer, Employee Batch Roster, and A4 Print Studio tabs to configure output.'
                ].join('\n');
            }

            function setFallbackTemplateColors(cardColor, accentColor, activeButton) {
                var safeCardColor = cardColor || '#f59e0b';
                var safeAccentColor = accentColor || '#1f2937';
                var templateStyle = activeButton ? (activeButton.getAttribute('data-template') || 'khmer_gold') : 'custom';
                var templateInput = document.getElementById('templateStyle');
                var studio = document.querySelector('.d-card-studio');

                if (templateInput) {
                    templateInput.value = templateStyle;
                    templateInput.dispatchEvent(new Event('change'));
                }

                ['cardColor', 'brandCardColor', 'templateCardColor'].forEach(function (id) {
                    var input = document.getElementById(id);
                    if (input) input.value = safeCardColor;
                });
                ['accentColor', 'brandAccentColor', 'templateAccentColor'].forEach(function (id) {
                    var input = document.getElementById(id);
                    if (input) input.value = safeAccentColor;
                });

                if (studio) {
                    studio.style.setProperty('--d-card-card-color', safeCardColor);
                    studio.style.setProperty('--d-card-accent-color', safeAccentColor);
                }

                each('.template-preset', function (button) {
                    button.classList.toggle('active', button === activeButton);
                });
                each('.id-side', function (side) {
                    side.style.background = safeCardColor;
                });
                each('.id-photo', function (photo) {
                    photo.style.color = safeCardColor;
                });
                each('.back-title', function (title) {
                    if (title.style.color) title.style.color = safeCardColor;
                });
                each('.id-code b', function (code) {
                    code.style.color = safeAccentColor;
                });
            }

            ready(function () {
                each('[data-studio-tab]', function (button) {
                    button.addEventListener('click', function () {
                        showStudioTab(button.getAttribute('data-studio-tab'));
                    });
                });

                each('[data-editor-section]', function (button) {
                    button.addEventListener('click', function () {
                        showEditorSection(button.getAttribute('data-editor-section'));
                    });
                });

                each('[data-code-tab]', function (button) {
                    button.addEventListener('click', function () {
                        showCodeTab(button.getAttribute('data-code-tab'));
                    });
                });

                each('.template-preset', function (button) {
                    button.addEventListener('click', function () {
                        if (button.getAttribute('data-width') && button.getAttribute('data-height')) {
                            ['cardWidthMm', 'a4CardWidthMm'].forEach(function (id) {
                                var input = document.getElementById(id);
                                if (input) input.value = button.getAttribute('data-width');
                            });
                            ['cardHeightMm', 'a4CardHeightMm'].forEach(function (id) {
                                var input = document.getElementById(id);
                                if (input) input.value = button.getAttribute('data-height');
                            });
                        }
                        if (button.getAttribute('data-photo-width') && button.getAttribute('data-photo-height')) {
                            var preset = document.getElementById('photoPreset');
                            var photoWidth = document.getElementById('photoWidthMm');
                            var photoHeight = document.getElementById('photoHeightMm');
                            var photoShape = document.getElementById('photoShape');
                            var photoFit = document.getElementById('photoFit');
                            if (preset) preset.value = 'custom';
                            if (photoWidth) photoWidth.value = button.getAttribute('data-photo-width');
                            if (photoHeight) photoHeight.value = button.getAttribute('data-photo-height');
                            if (photoShape) photoShape.value = 'rounded';
                            if (photoFit) photoFit.value = 'cover';
                        }
                        setFallbackTemplateColors(button.getAttribute('data-card'), button.getAttribute('data-accent'), button);
                    });
                });

                var templateCardColor = document.getElementById('templateCardColor');
                var templateAccentColor = document.getElementById('templateAccentColor');
                var brandCardColor = document.getElementById('brandCardColor');
                var brandAccentColor = document.getElementById('brandAccentColor');

                [templateCardColor, brandCardColor].forEach(function (input) {
                    if (!input) return;
                    input.addEventListener('input', function () {
                        setFallbackTemplateColors(input.value, (templateAccentColor || brandAccentColor || {}).value || '#1f2937', null);
                    });
                });

                [templateAccentColor, brandAccentColor].forEach(function (input) {
                    if (!input) return;
                    input.addEventListener('input', function () {
                        setFallbackTemplateColors((templateCardColor || brandCardColor || {}).value || '#f59e0b', input.value, null);
                    });
                });

                var printButton = document.getElementById('printCards');
                if (printButton) {
                    printButton.addEventListener('click', function () {
                        window.print();
                    });
                }

                var copyButton = document.getElementById('copyCodeSnippet');
                if (copyButton) {
                    copyButton.addEventListener('click', function () {
                        var output = document.getElementById('laravelCodeOutput');
                        var text = output ? output.textContent : '';
                        if (navigator.clipboard && text) {
                            navigator.clipboard.writeText(text);
                        }
                        copyButton.textContent = 'Copied to Clipboard!';
                        setTimeout(function () {
                            copyButton.textContent = 'Copy Code Snippet';
                        }, 1600);
                    });
                }

                showStudioTab(document.querySelector('[data-studio-tab].active') ? document.querySelector('[data-studio-tab].active').getAttribute('data-studio-tab') : 'editor');
                showEditorSection(document.querySelector('[data-editor-section].active') ? document.querySelector('[data-editor-section].active').getAttribute('data-editor-section') : 'templates');
                showCodeTab('blade');

                setTimeout(function () {
                    var batchRows = document.getElementById('batchEmployeeRows');
                    if (!window.digitalHrsMainReady || (batchRows && !batchRows.children.length)) {
                        bootBatchRosterFallback();
                    }
                    var printArea = document.getElementById('printArea');
                    if (!window.digitalHrsMainReady || (printArea && !printArea.children.length)) {
                        bootA4PrintFallback();
                    }
                }, 80);
            });

            function escapeText(value) {
                var div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function batchEmployeePayload(employee) {
                return {
                    employee_code: employee.employee_code || '',
                    name_khmer: employee.name || '',
                    name_english: employee.english_name || '',
                    position_khmer: employee.position_khmer || employee.post || '',
                    position_english: employee.position_english || '',
                    department: employee.department || '',
                    branch: employee.branch || '',
                    joining_date: employee.joining_date || '',
                    emergency_contact: employee.emergency_contact || '',
                    blood_type: employee.blood_type || '',
                    khqr_account_id: employee.khqr_account_id || '',
                    profile_photo_url: employee.photo_url || '',
                    phone: employee.phone || '',
                    email: employee.email || ''
                };
            }

            function formEncode(data) {
                var pairs = [];
                Object.keys(data).forEach(function (key) {
                    pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key] == null ? '' : data[key]));
                });
                return pairs.join('&');
            }

            function nextEmployeeCode(count) {
                var number = String(count + 1);
                while (number.length < 4) {
                    number = '0' + number;
                }
                return 'KY' + number;
            }

            function requestJson(method, url, data, callback) {
                var xhr = new XMLHttpRequest();
                xhr.open(method, url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                var csrf = document.querySelector('meta[name="csrf-token"]');
                if (csrf) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf.getAttribute('content'));
                }
                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4) return;
                    var response = {};
                    try {
                        response = xhr.responseText ? JSON.parse(xhr.responseText) : {};
                    } catch (error) {}
                    callback(xhr.status >= 200 && xhr.status < 300, response);
                };
                xhr.send(data ? formEncode(data) : null);
            }

            function bootBatchRosterFallback() {
                var rows = document.getElementById('batchEmployeeRows');
                if (!rows) return;

                var employees = (window.digitalHrsEmployees || []).map(function (employee) {
                    employee.selected = employee.selected !== false;
                    return employee;
                });

                function refreshCounts() {
                    var total = document.getElementById('totalCount');
                    var selected = document.getElementById('selectedCount');
                    var selectedCount = employees.filter(function (employee) {
                        return employee.selected !== false;
                    }).length;
                    if (total) total.textContent = employees.length;
                    if (selected) selected.textContent = selectedCount;
                }

                function renderRows() {
                    rows.innerHTML = employees.map(function (employee, index) {
                        return [
                            '<tr>',
                            '<td><input type="checkbox" class="batch-fallback-selected" data-index="' + index + '"' + (employee.selected !== false ? ' checked' : '') + '></td>',
                            '<td><img class="batch-photo mb-1" src="' + escapeText(employee.photo_url || window.digitalHrsDefaultPhoto) + '" alt=""><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="photo_url" value="' + escapeText(employee.photo_url || '') + '" placeholder="Photo URL"></td>',
                            '<td><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="employee_code" value="' + escapeText(employee.employee_code || '') + '"></td>',
                            '<td><input class="form-control form-control-sm batch-fallback-edit mb-1" data-index="' + index + '" data-field="name" value="' + escapeText(employee.name || '') + '"><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="english_name" value="' + escapeText(employee.english_name || '') + '" placeholder="English"></td>',
                            '<td><input class="form-control form-control-sm batch-fallback-edit mb-1" data-index="' + index + '" data-field="position_khmer" value="' + escapeText(employee.position_khmer || employee.post || '') + '" placeholder="Position Khmer"><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="position_english" value="' + escapeText(employee.position_english || '') + '" placeholder="Position EN"></td>',
                            '<td><input class="form-control form-control-sm batch-fallback-edit mb-1" data-index="' + index + '" data-field="department" value="' + escapeText(employee.department || '') + '" placeholder="Department"><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="branch" value="' + escapeText(employee.branch || '') + '" placeholder="Branch"><input class="form-control form-control-sm batch-fallback-edit mt-1" type="date" data-index="' + index + '" data-field="joining_date" value="' + escapeText(employee.joining_date || '') + '"></td>',
                            '<td><input class="form-control form-control-sm batch-fallback-edit mb-1" data-index="' + index + '" data-field="emergency_contact" value="' + escapeText(employee.emergency_contact || '') + '" placeholder="Emergency"><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="blood_type" value="' + escapeText(employee.blood_type || '') + '" placeholder="Blood"></td>',
                            '<td><input class="form-control form-control-sm batch-fallback-edit" data-index="' + index + '" data-field="khqr_account_id" value="' + escapeText(employee.khqr_account_id || '') + '" placeholder="KHQR"></td>',
                            '<td class="text-nowrap"><button type="button" class="btn btn-outline-success btn-xs batch-fallback-save" data-index="' + index + '">Save</button> <button type="button" class="btn btn-outline-danger btn-xs batch-fallback-delete" data-index="' + index + '">Delete</button></td>',
                            '</tr>'
                        ].join('');
                    }).join('');
                    refreshCounts();
                }

                function addEmployee(employee) {
                    employees.unshift(employee);
                    window.digitalHrsEmployees = employees;
                    renderRows();
                }

                rows.addEventListener('change', function (event) {
                    if (event.target.classList.contains('batch-fallback-selected')) {
                        employees[event.target.getAttribute('data-index')].selected = event.target.checked;
                        refreshCounts();
                    }
                });

                rows.addEventListener('input', function (event) {
                    if (event.target.classList.contains('batch-fallback-edit')) {
                        var employee = employees[event.target.getAttribute('data-index')];
                        employee[event.target.getAttribute('data-field')] = event.target.value;
                    }
                });

                rows.addEventListener('click', function (event) {
                    if (event.target.classList.contains('batch-fallback-delete')) {
                        var deleteIndex = parseInt(event.target.getAttribute('data-index'), 10);
                        var deleteEmployee = employees[deleteIndex];
                        if (deleteEmployee.source === 'dcard' && deleteEmployee.record_id) {
                            requestJson('DELETE', window.digitalHrsEmployeeBaseUrl + '/' + deleteEmployee.record_id, null, function () {});
                        }
                        employees.splice(deleteIndex, 1);
                        renderRows();
                    }

                    if (event.target.classList.contains('batch-fallback-save')) {
                        var saveIndex = parseInt(event.target.getAttribute('data-index'), 10);
                        var saveEmployee = employees[saveIndex];
                        var existing = saveEmployee.source === 'dcard' && saveEmployee.record_id;
                        requestJson(existing ? 'PUT' : 'POST', existing ? window.digitalHrsEmployeeBaseUrl + '/' + saveEmployee.record_id : window.digitalHrsEmployeeStoreUrl, batchEmployeePayload(saveEmployee), function (ok, response) {
                            if (!ok) {
                                alert(response.message || 'Could not save employee row.');
                                return;
                            }
                            employees[saveIndex] = response.employee || saveEmployee;
                            employees[saveIndex].selected = saveEmployee.selected !== false;
                            renderRows();
                        });
                    }
                });

                var selectAll = document.getElementById('batchSelectAll');
                if (selectAll) {
                    selectAll.addEventListener('click', function () {
                        employees.forEach(function (employee) { employee.selected = true; });
                        renderRows();
                    });
                }

                var deselectAll = document.getElementById('batchDeselectAll');
                if (deselectAll) {
                    deselectAll.addEventListener('click', function () {
                        employees.forEach(function (employee) { employee.selected = false; });
                        renderRows();
                    });
                }

                var addLocal = document.getElementById('addLocalEmployee');
                if (addLocal) {
                    addLocal.addEventListener('click', function () {
                        addEmployee({
                            id: 'local-' + Date.now(),
                            source: 'local',
                            record_id: null,
                            employee_code: nextEmployeeCode(employees.length),
                            name: 'New Employee',
                            english_name: '',
                            position_khmer: '',
                            position_english: '',
                            department: '',
                            branch: '',
                            joining_date: '',
                            emergency_contact: '',
                            blood_type: '',
                            khqr_account_id: '',
                            phone: '',
                            email: '',
                            photo_url: window.digitalHrsDefaultPhoto,
                            selected: true
                        });
                    });
                }

                var importRows = document.getElementById('importCsvRows');
                if (importRows) {
                    importRows.addEventListener('click', function () {
                        var textarea = document.getElementById('csvImportText');
                        var text = textarea ? textarea.value : '';
                        text.split(/\r?\n/).forEach(function (line, offset) {
                            if (!line.trim()) return;
                            var parts = line.split(',').map(function (part) { return part.trim(); });
                            employees.push({
                                id: 'import-' + Date.now() + '-' + offset,
                                source: 'local',
                                record_id: null,
                                employee_code: parts[0] || nextEmployeeCode(employees.length),
                                name: parts[1] || '',
                                english_name: parts[2] || '',
                                position_khmer: parts[3] || '',
                                position_english: '',
                                department: parts[3] || '',
                                branch: parts[4] || '',
                                phone: parts[5] || '',
                                photo_url: parts[6] || window.digitalHrsDefaultPhoto,
                                khqr_account_id: parts[7] || '',
                                joining_date: '',
                                emergency_contact: '',
                                blood_type: '',
                                selected: true
                            });
                        });
                        if (textarea) textarea.value = '';
                        renderRows();
                    });
                }

                renderRows();
            }

            function getSelectedFallbackEmployees(employees) {
                return employees.filter(function (employee) {
                    return employee.selected !== false;
                });
            }

            function chunkFallback(items, size) {
                var chunks = [];
                for (var i = 0; i < items.length; i += size) {
                    chunks.push(items.slice(i, i + size));
                }
                return chunks.length ? chunks : [[]];
            }

            function readNumber(id, fallback) {
                var element = document.getElementById(id);
                var value = element ? parseFloat(element.value) : NaN;
                return isNaN(value) ? fallback : value;
            }

            function readValue(id, fallback) {
                var element = document.getElementById(id);
                return element && element.value ? element.value : fallback;
            }

            function readChecked(id, fallback) {
                var element = document.getElementById(id);
                return element ? element.checked : fallback;
            }

            function fallbackFormatBranchLabel(value, fallback) {
                var label = String(value || fallback || '').trim();
                if (!label) return '';
                label = label.replace(/^គ្នាយើង\s*[-–—]?\s*/u, '').trim();
                return label ? 'គ្នាយើង-' + label : 'គ្នាយើង';
            }

            function fallbackBranchRail(employee, cardColor) {
                var branch = fallbackFormatBranchLabel(employee.branch || readValue('sideBannerText', 'គ្នាយើង') || employee.company || 'គ្នាយើង', 'គ្នាយើង');
                var logo = employee.branch_logo_url || '';
                var badge = logo
                    ? '<span class="side-logo-badge"><img src="' + escapeText(logo) + '" alt="' + escapeText(branch) + '"></span>'
                    : '<span class="side-logo-badge"><span>DHR</span></span>';
                var bottomBadge = badge.replace('side-logo-badge"', 'side-logo-badge side-logo-badge-bottom"');

                return [
                    '<div class="id-side" style="background:' + cardColor + '">',
                    badge,
                    '<span class="side-branch-name">' + escapeText(branch) + '</span>',
                    bottomBadge,
                    '</div>'
                ].join('');
            }

            function fallbackFrontDetailRows(employee) {
                var rows = [];
                var name = employee.name || 'Employee';
                var english = employee.english_name || '';
                var post = employee.position_khmer || employee.post || employee.position_english || '';
                var branch = employee.branch ? fallbackFormatBranchLabel(employee.branch, '') : '';
                var department = employee.department || '';
                var phone = employee.phone || '';
                var code = employee.employee_code || '';
                var nameValue = (readChecked('showKhmerName', true) ? escapeText(name) : '')
                    + (readChecked('showEnglishName', true) && english ? '<small>' + escapeText(english) + '</small>' : '');

                if (nameValue) rows.push('<div class="employee-name-row"><span>ឈ្មោះ:</span><strong>' + nameValue + '</strong></div>');
                if (readChecked('showEmployeeCode', false)) rows.push('<div><span>លេខកូដ:</span><strong>' + escapeText(code) + '</strong></div>');
                if (readChecked('showPosition', true)) rows.push('<div><span>មុខតំណែង:</span><strong>' + escapeText(post) + '</strong></div>');
                if (readChecked('showBranch', true)) {
                    rows.push('<div><span>សាខា:</span><strong>' + escapeText(branch) + '</strong></div>');
                }
                if (readChecked('showDepartment', true)) {
                    rows.push('<div><span>ផ្នែក:</span><strong>' + escapeText(department) + '</strong></div>');
                }
                if (readChecked('showPhone', true)) rows.push('<div><span>ទូរស័ព្ទ:</span><strong>' + escapeText(phone) + '</strong></div>');

                return rows.join('');
            }

            function fallbackFrontDetailBlock(employee) {
                var rows = fallbackFrontDetailRows(employee);
                return rows ? '<div class="id-details id-details-khmer">' + rows + '</div>' : '';
            }

            function fallbackA4CardSize() {
                var scale = readNumber('a4CardScale', 100) / 100;
                return {
                    scale: scale,
                    width: readNumber('a4CardWidthMm', readNumber('cardWidthMm', 71)) * scale,
                    height: readNumber('a4CardHeightMm', readNumber('cardHeightMm', 103)) * scale
                };
            }

            function fallbackTemplateClass() {
                return 'template-' + readValue('templateStyle', 'khmer_gold');
            }

            function fallbackPhotoStyle(cardColor) {
                var preset = readValue('photoPreset', 'auto');
                var radius = readNumber('photoRadiusMm', 3);
                var shape = readValue('photoShape', 'rectangle');
                var templateStyle = readValue('templateStyle', 'khmer_gold');
                var autoSize = templateStyle === 'khmer_gold'
                    ? [52.5, 69]
                    : (templateStyle === 'kneayerng_gold' ? [34, 33.5] : [27, 36]);
                var sizes = {
                    auto: autoSize,
                    passport: [30, 40],
                    square: [30, 30],
                    hero: [34, 42],
                    custom: [readNumber('photoWidthMm', 27), readNumber('photoHeightMm', 36)]
                };
                var size = sizes[preset] || sizes.auto;
                var borderRadius = shape === 'circle'
                    ? '50%'
                    : (shape === 'square' ? '0mm' : radius + 'mm');

                return [
                    'width:' + size[0] + 'mm',
                    'height:' + size[1] + 'mm',
                    'border-radius:' + borderRadius,
                    'color:' + cardColor,
                    'object-fit:' + readValue('photoFit', 'cover'),
                    'transform:scale(' + (readNumber('photoZoom', 100) / 100) + ')'
                ].join(';');
            }

            function fallbackPaymentBoxStyle() {
                var qrSize = Math.max(12, Math.min(40, readNumber('paymentQrSizeMm', 27.3)));
                var padding = Math.max(0, Math.min(6, readNumber('paymentPaddingMm', 2)));
                var minHeight = Math.max(30, qrSize + (padding * 2));

                return 'padding:' + padding + 'mm ' + Math.max(1, padding * 0.75) + 'mm;min-height:' + minHeight + 'mm;';
            }

            function fallbackPaymentQrStyle() {
                var qrSize = Math.max(12, Math.min(40, readNumber('paymentQrSizeMm', 27.3)));

                return [
                    'width:' + qrSize + 'mm',
                    'height:' + qrSize + 'mm',
                    'object-fit:' + readValue('paymentQrFit', 'contain')
                ].join(';');
            }

            function fallbackPaymentQrPx() {
                return Math.round(Math.max(12, Math.min(40, readNumber('paymentQrSizeMm', 27.3))) * 3.78);
            }

            function fallbackBackContactBlock() {
                return [
                    '<div class="back-contact">',
                    '<strong class="back-contact-title">Contact Us:</strong>',
                    '<div class="back-contact-row" style="--contact-color:#10b981"><span class="back-contact-icon">W</span><span>Website: <a href="http://kneayerng.com">http://kneayerng.com</a></span></div>',
                    '<div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook: <a href="https://www.facebook.com/kystorecambodia">https://www.facebook.com/kystorecambodia</a></span></div>',
                    '<div class="back-contact-row" style="--contact-color:#2563eb"><span class="back-contact-icon">f</span><span>Facebook (Official): <a href="https://www.facebook.com/Knea">https://www.facebook.com/Knea</a></span></div>',
                    '<div class="back-contact-row" style="--contact-color:#ec4899"><span class="back-contact-icon">IG</span><span>Instagram: <a href="https://www.instagram.com/kneayerngvip.official/">https://www.instagram.com/kneayerngvip.official/</a></span></div>',
                    '<div class="back-contact-row" style="--contact-color:#0ea5e9"><span class="back-contact-icon">T</span><span>Telegram: <a href="https://t.me/kneayerngofficialbot">https://t.me/kneayerngofficialbot</a></span></div>',
                    '<div class="back-contact-row" style="--contact-color:#14b8a6"><span class="back-contact-icon">P</span><span>Phone: <b>16910505</b></span></div>',
                    '</div>'
                ].join('');
            }

            function fallbackFrontCard(employee) {
                var cardColor = readValue('cardColor', '#f59e0b');
                var accentColor = readValue('accentColor', '#1f2937');
                var cardSize = fallbackA4CardSize();
                var widthFactor = cardSize.width / 71;
                var heightFactor = cardSize.height / 103;
                var textScale = Math.max(0.72, Math.min(1.35, Math.min(widthFactor, heightFactor) * (readNumber('frontTextScale', 100) / 100)));
                var photo = employee.photo_url || window.digitalHrsDefaultPhoto;
                var name = employee.name || 'Employee';
                var code = employee.employee_code || '';
                var nameFontStyle = [
                    '--employee-khmer-font:' + readValue('khmerNameFont', '"Khmer OS Muol Light", "Moul", "Noto Serif Khmer", "Kantumruy Pro", Arial, sans-serif').split('"').join("'"),
                    '--employee-khmer-size:' + readNumber('khmerNameSize', 12) + 'px',
                    '--employee-english-font:' + readValue('englishNameFont', 'Arial, "Poppins", sans-serif').split('"').join("'"),
                    '--employee-english-size:' + readNumber('englishNameSize', 8) + 'px'
                ].join(';');

                return [
                    '<div class="id-card-wrap">',
                    '<div class="id-card id-card-front ' + fallbackTemplateClass() + (readChecked('showSideBrand', true) ? '' : ' no-side-brand') + '" style="width:' + cardSize.width + 'mm;height:' + cardSize.height + 'mm;--card-text-scale:' + textScale + ';--card-accent-color:' + accentColor + ';' + nameFontStyle + ';">',
                    readChecked('showSideBrand', true) ? fallbackBranchRail(employee, cardColor) : '<div class="id-side id-side-empty"></div>',
                    '<div class="id-body' + (readChecked('showPhoto', true) ? '' : ' no-photo') + '">',
                    readChecked('showPhoto', true) ? '<img class="id-photo" src="' + escapeText(photo) + '" alt="' + escapeText(name) + '" style="' + fallbackPhotoStyle(cardColor) + '">' : '',
                    fallbackFrontDetailBlock(employee),
                    readChecked('showBarcode', true) ? '<div class="id-code"><div class="barcode-visual"><svg class="barcode-target" data-value="' + escapeText(code) + '"></svg></div><small>ID No : <b>' + escapeText(code) + '</b></small></div>' : '',
                    '</div>',
                    '</div>',
                    '</div>'
                ].join('');
            }

            function fallbackBackCard(employee) {
                var cardColor = readValue('cardColor', '#f59e0b');
                var backClass = fallbackTemplateClass();
                var cardSize = fallbackA4CardSize();
                var name = employee.name || 'Employee';
                var code = employee.employee_code || '';
                var khqr = employee.khqr_account_id || '';
                var logo = employee.branch_logo_url || '';
                var branchTitle = fallbackFormatBranchLabel(employee.branch || readValue('sideBannerText', 'គ្នាយើង') || employee.company || 'គ្នាយើង', 'គ្នាយើង');

                if (readValue('templateStyle', 'khmer_gold') === 'kneayerng_gold') {
                    backClass = 'template-khmer_gold template-kneayerng-amber-back';
                    cardColor = '#c9aa28';
                }

                var qrCodes = []
                    .concat(khqr ? [{payment_name: 'Employee KHQR', khqr_value: khqr}] : [])
                    .concat(employee.payment_qr_codes || []);
                var payments = qrCodes.length ? qrCodes.slice(0, 4).map(function (qrCode) {
                    return [
                        '<div class="payment-box">',
                        '<div class="payment-qr-frame" style="' + fallbackPaymentBoxStyle() + '">',
                        qrCode.khqr_value
                            ? '<div class="khqr-generated" data-value="' + escapeText(qrCode.khqr_value) + '" data-qr-px="' + fallbackPaymentQrPx() + '" style="' + fallbackPaymentQrStyle() + '"></div>'
                            : '<img src="' + escapeText(qrCode.qr_code_url || '') + '" alt="' + escapeText(qrCode.payment_name || '') + '" style="' + fallbackPaymentQrStyle() + '">',
                        '</div>',
                        '<strong>' + escapeText(qrCode.payment_name || 'Payment') + '</strong>',
                        '</div>'
                    ].join('');
                }).join('') : '<div class="payment-box"><div class="payment-qr-frame" style="' + fallbackPaymentBoxStyle() + '"><strong>KHQR</strong></div><div class="text-muted mt-2">' + escapeText(khqr || code) + '</div></div>';

                return [
                    '<div class="id-card-wrap">',
                    '<div class="id-card id-card-back ' + backClass + '" style="width:' + cardSize.width + 'mm;height:' + cardSize.height + 'mm;">',
                    '<div class="back-brand-row' + (logo ? '' : ' no-logo') + '">',
                    logo ? '<img class="id-logo back-branch-logo" src="' + escapeText(logo) + '" alt="' + escapeText(branchTitle) + '">' : '',
                    '<div class="back-brand-copy">',
                    '<div class="back-title" style="color:' + cardColor + '">' + escapeText(branchTitle) + '</div>',
                    '<div class="text-muted small">Scan. Pay. Done.</div>',
                    '</div>',
                    '</div>',
                    '<div class="payment-grid">',
                    payments,
                    '</div>',
                    fallbackBackContactBlock(),
                    '<div class="back-note">If found, please return this card to គ្នាយើង.<br>Holder: ' + escapeText(name) + '</div>',
                    '<div class="signature-line">AUTHORIZED STAMP & SIGNATURE</div>',
                    '</div>',
                    '</div>'
                ].join('');
            }

            function renderFallbackPicker(employees, renderA4) {
                var picker = document.querySelector('.employee-picker');
                if (!picker) return;
                var keywordInput = document.getElementById('employeeSearch');
                var keyword = keywordInput ? keywordInput.value.toLowerCase() : '';
                var branchInput = document.getElementById('employeeBranchFilter');
                var departmentInput = document.getElementById('employeeDepartmentFilter');
                var branchFilter = branchInput ? branchInput.value : '';
                var departmentFilter = departmentInput ? departmentInput.value : '';

                picker.innerHTML = employees.map(function (employee, index) {
                    var search = [
                        employee.employee_code || '',
                        employee.name || '',
                        employee.english_name || '',
                        employee.branch || '',
                        employee.department || ''
                    ].join(' ').toLowerCase();

                    if ((keyword && search.indexOf(keyword) === -1)
                        || (branchFilter && String(employee.branch || '') !== branchFilter)
                        || (departmentFilter && String(employee.department || '') !== departmentFilter)) {
                        return '';
                    }

                    return [
                        '<div class="employee-option' + (employee.selected !== false ? ' is-selected' : '') + '" data-index="' + index + '">',
                        '<input type="checkbox" class="a4-fallback-check" data-index="' + index + '"' + (employee.selected !== false ? ' checked' : '') + '>',
                        '<div class="employee-photo-thumb">',
                        '<img src="' + escapeText(employee.photo_url || window.digitalHrsDefaultPhoto) + '" alt="">',
                        '<span class="employee-photo-hover">Update</span>',
                        '<div class="employee-photo-quick">',
                        '<label class="btn btn-outline-secondary btn-sm mb-0">Upload<input type="file" class="d-none a4-fallback-photo-file" data-index="' + index + '" accept="image/*"></label>',
                        '</div>',
                        '</div>',
                        '<span class="employee-option-main"><strong>' + escapeText(employee.name || 'Employee') + '</strong><small>' + escapeText(employee.employee_code || '') + ' - ' + escapeText(employee.branch || employee.department || '') + '</small></span>',
                        '</div>'
                    ].join('');
                }).join('');

                each('.a4-fallback-check', function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        employees[checkbox.getAttribute('data-index')].selected = checkbox.checked;
                        checkbox.closest('.employee-option').classList.toggle('is-selected', checkbox.checked);
                        renderA4();
                    });
                });

                each('.a4-fallback-photo-file', function (input) {
                    input.addEventListener('change', function () {
                        var file = input.files && input.files[0];
                        if (!file) return;
                        var reader = new FileReader();
                        reader.onload = function (event) {
                            employees[input.getAttribute('data-index')].photo_url = event.target.result;
                            employees[input.getAttribute('data-index')].quick_photo_file = file;
                            renderFallbackPicker(employees, renderA4);
                            renderA4();
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }

            function bootA4PrintFallback() {
                var printArea = document.getElementById('printArea');
                var paperArea = document.getElementById('printAreaForPaper');
                if (!printArea || !paperArea) return;

                var employees = window.digitalHrsEmployees || [];
                employees.forEach(function (employee) {
                    employee.selected = employee.selected !== false;
                });

                function populateFallbackEmployeeFilter(id, field, defaultLabel, branchName) {
                    var select = document.getElementById(id);
                    if (!select) return;
                    var selected = select.value || '';
                    var values = [];
                    employees.forEach(function (employee) {
                        if (branchName && String(employee.branch || '') !== branchName) return;
                        var value = String(employee[field] || '').trim();
                        if (value && values.indexOf(value) === -1) values.push(value);
                    });
                    values.sort();
                    select.innerHTML = '<option value="">' + defaultLabel + '</option>' + values.map(function (value) {
                        return '<option value="' + escapeText(value) + '">' + escapeText(value) + '</option>';
                    }).join('');
                    select.value = values.indexOf(selected) !== -1 ? selected : '';
                }

                populateFallbackEmployeeFilter('employeeBranchFilter', 'branch', 'All Branches');
                populateFallbackEmployeeFilter('employeeDepartmentFilter', 'department', 'All Departments', readValue('employeeBranchFilter', ''));

                function renderA4() {
                    var selected = getSelectedFallbackEmployees(employees);
                    var mode = readValue('printMode', 'front_back');
                    var cardsPerPage = parseInt(readValue('cardsPerPage', '4'), 10) || 4;
                    var employeesPerPage = mode === 'front_back' ? Math.max(1, Math.floor(cardsPerPage / 2)) : cardsPerPage;
                    var pages = chunkFallback(selected, employeesPerPage);
                    var marginTop = readNumber('marginTopMm', 12);
                    var marginLeft = readNumber('marginLeftMm', 16);
                    var gapX = readNumber('gapXMm', 10);
                    var gapY = readNumber('gapYMm', 10);
                    var pairMode = readValue('pairMode', 'top_bottom');
                    var showIndex = document.getElementById('toggleMarginIndex') ? document.getElementById('toggleMarginIndex').classList.contains('active') : true;
                    var showCut = document.getElementById('toggleCutMarks') ? document.getElementById('toggleCutMarks').classList.contains('active') : true;
                    var pageClass = (showIndex ? '' : ' no-index') + (showCut ? '' : ' no-cut');

                    var html = pages.map(function (pageEmployees) {
                        var cards = '';
                        if (mode === 'front') {
                            cards = pageEmployees.map(fallbackFrontCard).join('');
                        } else if (mode === 'back') {
                            cards = pageEmployees.map(fallbackBackCard).join('');
                        } else if (pairMode === 'top_bottom') {
                            cards = chunkFallback(pageEmployees, 2).map(function (pair) {
                                return pair.map(fallbackFrontCard).join('') + pair.map(fallbackBackCard).join('');
                            }).join('');
                        } else {
                            cards = pageEmployees.map(function (employee) {
                                return fallbackFrontCard(employee) + fallbackBackCard(employee);
                            }).join('');
                        }

                        return [
                            '<div class="a4-page' + pageClass + '">',
                            '<div class="a4-index"><strong>ID</strong>' + pageEmployees.map(function (employee) {
                                return '<div>' + escapeText(employee.employee_code || '') + '</div>';
                            }).join('') + '</div>',
                            '<div class="a4-grid" style="margin-top:' + marginTop + 'mm;margin-left:' + marginLeft + 'mm;column-gap:' + gapX + 'mm;row-gap:' + gapY + 'mm;">',
                            cards || '<div class="text-muted p-4">No employees selected.</div>',
                            '</div>',
                            '</div>'
                        ].join('');
                    }).join('');

                    printArea.innerHTML = html;
                    paperArea.innerHTML = html;

                    if (window.QRCode) {
                        each('.khqr-generated', function (qr) {
                            var value = qr.getAttribute('data-value') || '';
                            if (!value || qr.children.length) return;
                            new QRCode(qr, {
                                text: value,
                                width: parseInt(qr.getAttribute('data-qr-px') || '76', 10),
                                height: parseInt(qr.getAttribute('data-qr-px') || '76', 10)
                            });
                        });
                    }

                    var total = document.getElementById('totalCount');
                    var count = document.getElementById('selectedCount');
                    var badge = document.getElementById('a4SelectedBadge');
                    if (total) total.textContent = employees.length;
                    if (count) count.textContent = selected.length;
                    if (badge) badge.textContent = selected.length;
                }

                renderFallbackPicker(employees, renderA4);
                renderA4();

                var search = document.getElementById('employeeSearch');
                if (search) {
                    search.addEventListener('input', function () {
                        renderFallbackPicker(employees, renderA4);
                    });
                }

                var branchFilter = document.getElementById('employeeBranchFilter');
                if (branchFilter) {
                    branchFilter.addEventListener('change', function () {
                        populateFallbackEmployeeFilter('employeeDepartmentFilter', 'department', 'All Departments', branchFilter.value || '');
                        renderFallbackPicker(employees, renderA4);
                    });
                }

                var departmentFilter = document.getElementById('employeeDepartmentFilter');
                if (departmentFilter) {
                    departmentFilter.addEventListener('change', function () {
                        renderFallbackPicker(employees, renderA4);
                    });
                }

                var clearFilters = document.getElementById('clearEmployeeFilters');
                if (clearFilters) {
                    clearFilters.addEventListener('click', function () {
                        if (branchFilter) branchFilter.value = '';
                        if (departmentFilter) departmentFilter.value = '';
                        renderFallbackPicker(employees, renderA4);
                    });
                }

                var selectAll = document.getElementById('selectAllEmployees');
                if (selectAll) {
                    selectAll.addEventListener('click', function () {
                        employees.forEach(function (employee) { employee.selected = true; });
                        renderFallbackPicker(employees, renderA4);
                        renderA4();
                    });
                }

                var clear = document.getElementById('clearEmployees');
                if (clear) {
                    clear.addEventListener('click', function () {
                        employees.forEach(function (employee) { employee.selected = false; });
                        renderFallbackPicker(employees, renderA4);
                        renderA4();
                    });
                }

                [
                    'printMode', 'cardsPerPage', 'cardColor', 'accentColor', 'templateStyle', 'a4CardWidthMm', 'a4CardHeightMm', 'marginTopMm', 'marginLeftMm', 'gapXMm', 'gapYMm', 'pairMode',
                    'frontTextScale', 'khmerNameFont', 'khmerNameSize', 'englishNameFont', 'englishNameSize', 'photoPreset', 'photoWidthMm', 'photoHeightMm', 'photoRadiusMm', 'photoShape', 'photoFit', 'photoZoom', 'paymentQrSizeMm', 'paymentPaddingMm', 'paymentQrFit',
                    'showPhoto', 'showSideBrand', 'showKhmerName', 'showEnglishName', 'showPosition', 'showDepartment', 'showBranch', 'showPhone', 'showEmployeeCode', 'showBarcode'
                ].forEach(function (id) {
                    var element = document.getElementById(id);
                    if (element) {
                        element.addEventListener('change', renderA4);
                        element.addEventListener('input', renderA4);
                    }
                });

                each('[data-a4-mode]', function (button) {
                    button.addEventListener('click', function () {
                        each('[data-a4-mode]', function (modeButton) {
                            modeButton.classList.remove('active');
                        });
                        button.classList.add('active');
                        var printMode = document.getElementById('printMode');
                        var pairMode = document.getElementById('pairMode');
                        if (printMode) printMode.value = button.getAttribute('data-a4-mode');
                        if (pairMode && button.getAttribute('data-a4-pair')) pairMode.value = button.getAttribute('data-a4-pair');
                        renderA4();
                    });
                });

                ['toggleMarginIndex', 'toggleCutMarks'].forEach(function (id) {
                    var toggle = document.getElementById(id);
                    if (toggle) {
                        toggle.addEventListener('click', function () {
                            toggle.classList.toggle('active');
                            toggle.textContent = id === 'toggleMarginIndex'
                                ? (toggle.classList.contains('active') ? 'Margin List ON' : 'Margin List OFF')
                                : (toggle.classList.contains('active') ? 'Cut Marks ON' : 'Cut Marks OFF');
                            renderA4();
                        });
                    }
                });

                var scaleInput = document.getElementById('a4CardScale');
                if (scaleInput) {
                    scaleInput.addEventListener('input', function () {
                        var value = document.getElementById('a4ScaleValue');
                        if (value) value.textContent = scaleInput.value;
                        renderA4();
                    });
                }

                var a4WidthInput = document.getElementById('a4CardWidthMm');
                var a4HeightInput = document.getElementById('a4CardHeightMm');
                var designerWidthInput = document.getElementById('cardWidthMm');
                var designerHeightInput = document.getElementById('cardHeightMm');
                if (a4WidthInput && designerWidthInput) {
                    a4WidthInput.addEventListener('input', function () {
                        designerWidthInput.value = a4WidthInput.value;
                    });
                }
                if (a4HeightInput && designerHeightInput) {
                    a4HeightInput.addEventListener('input', function () {
                        designerHeightInput.value = a4HeightInput.value;
                    });
                }

                var printNow = document.getElementById('printA4Now');
                if (printNow) {
                    printNow.addEventListener('click', function () {
                        renderA4();
                        window.print();
                    });
                }

                var download = document.getElementById('downloadA4Pdf');
                if (download) {
                    download.addEventListener('click', function () {
                        renderA4();
                        window.print();
                    });
                }
            }
        })();
    </script>
@endsection
