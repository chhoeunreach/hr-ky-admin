@extends('layouts.master')
@section('title', 'Quotes')
@section('action', 'Manage the quotes shown in the mobile app')
@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Bokor&family=Cinzel:wght@600;800&family=Fasthand&family=Hanuman:wght@400;700;900&family=Inter:wght@400;600;700;800&family=Kantumruy+Pro:ital,wght@0,400;0,600;0,700;0,800;1,400;1,700&family=Koulen&family=Montserrat:wght@400;600;700;800;900&family=Moul&family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Siemreap&display=swap" rel="stylesheet">
<style>
.mt-page{--ink:#172b4d;--muted:#718096;--line:#e8edf3;--soft:#f6f8fb;color:var(--ink)}
.mt-hero{border:0;border-radius:16px;background:linear-gradient(120deg,#1e3a8a,#3b82f6);color:#fff;box-shadow:0 10px 26px rgba(30,58,138,.16)}
.mt-hero .card-body{padding:26px 32px}
.mt-eyebrow{font-size:.75rem;letter-spacing:.12em;text-transform:uppercase;color:#bfdbfe;font-weight:700}
.mt-hero h2{color:#fff;font-size:1.55rem;font-weight:700;margin:.45rem 0}
.mt-hero p{color:rgba(255,255,255,.9);margin:0;max-width:760px}
.mt-card{border:1px solid var(--line);border-radius:14px;box-shadow:0 4px 18px rgba(23,43,77,.04);background:#fff}

/* Banner Canvas */
.qb-preview-wrapper{background:#0b1120;padding:18px;border-radius:20px;border:1px solid #1e293b;position:relative;margin-bottom:20px}
.qb-preview-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;color:#94a3b8;font-size:.82rem}
.qb-banner{
    position:relative;
    width:min(100%,360px);
    aspect-ratio:3/1;
    border-radius:22px;
    overflow:hidden;
    background-color:#0f172a;
    background-size:cover;
    background-position:center;
    box-shadow:0 0 0 2px #00e5ff, 0 10px 30px rgba(0,229,255,0.22), inset 0 0 20px rgba(0,0,0,0.5);
    user-select:none;
}
.qb-banner-overlay{
    position:absolute;
    inset:0;
    background:linear-gradient(90deg, rgba(10,25,47,0.78) 0%, rgba(10,25,47,0.3) 50%, rgba(10,25,47,0.65) 100%);
    pointer-events:none;
}

/* Mock Phone Header on Left */
.qb-mock-header{
    position:absolute;
    left:16px;
    top:12px;
    bottom:12px;
    width:50%;
    display:flex;
    flex-direction:column;
    justify-content:center;
    pointer-events:none;
    z-index:2;
}
.qb-mock-khmer{font-family:'Kantumruy Pro',sans-serif;font-weight:800;font-size:1.15rem;color:#ffffff;line-height:1.2;margin-bottom:2px;text-shadow:0 2px 4px rgba(0,0,0,0.6)}
.qb-mock-sub{font-size:.78rem;font-weight:600;color:rgba(255,255,255,0.9);margin-bottom:6px;text-shadow:0 1px 3px rgba(0,0,0,0.6)}
.qb-mock-clock{font-size:2rem;font-weight:900;color:#ffffff;letter-spacing:-0.03em;line-height:1;margin-bottom:6px;text-shadow:0 2px 6px rgba(0,0,0,0.6)}
.qb-mock-clock span{font-size:1.1rem;opacity:0.9}
.qb-mock-date{font-size:.7rem;font-weight:600;color:rgba(255,255,255,0.9);display:flex;align-items:center;gap:4px;text-shadow:0 1px 3px rgba(0,0,0,0.6)}

/* Draggable Quote Element */
.qb-quote-box{
    position:absolute;
    cursor:grab;
    user-select:none;
    touch-action:none;
    width:50%;
    padding:0;
    border-radius:10px;
    transform:translate(-50%, -50%);
    transition:box-shadow .15s ease;
    z-index:5;
}
.qb-quote-box:hover{
    outline:2px dashed rgba(0,229,255,0.7);
    outline-offset:3px;
    background:rgba(0,0,0,0.18);
}
.qb-quote-box.is-dragging{
    cursor:grabbing;
    outline:2px solid #00e5ff;
    outline-offset:4px;
    background:rgba(0,0,0,0.32);
    box-shadow:0 12px 28px rgba(0,0,0,0.5);
}
.qb-quote-mark{
    font-size:22px;
    font-weight:900;
    line-height:1;
    display:block;
    color:#ffd54f;
}
.qb-quote-text{
    overflow-wrap:anywhere;
    white-space:pre-wrap;
    line-height:1.34;
    display:-webkit-box;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:4;
    overflow:hidden;
}
.qb-quote-author{
    margin-top:2px;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    font-size:.78rem;
    opacity:.85;
    font-weight:600;
}
.qb-drag-badge{
    position:absolute;
    top:-24px;
    left:50%;
    transform:translateX(-50%);
    background:#00e5ff;
    color:#041325;
    font-size:.65rem;
    font-weight:800;
    padding:2px 8px;
    border-radius:10px;
    letter-spacing:.05em;
    text-transform:uppercase;
    pointer-events:none;
    white-space:nowrap;
    opacity:0;
    transition:opacity .15s;
}
.qb-quote-box:hover .qb-drag-badge,
.qb-quote-box.is-dragging .qb-drag-badge{
    opacity:1;
}

/* Control Panels & Tabs */
.qb-nav-pills .nav-link{
    color:#4b5563;
    font-weight:700;
    font-size:.82rem;
    padding:7px 14px;
    border-radius:8px;
    transition:all .15s;
}
.qb-nav-pills .nav-link.active{
    background:#1e3a8a;
    color:#fff;
}
.qb-ctrl-label{
    font-size:.76rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#64748b;
    margin-bottom:5px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.qb-color-chip{
    width:28px;
    height:28px;
    border-radius:6px;
    border:2px solid #e2e8f0;
    cursor:pointer;
    display:inline-block;
    transition:transform .12s, border-color .12s;
}
.qb-color-chip:hover{
    transform:scale(1.15);
    border-color:#1e3a8a;
}
.qb-preset-btn{
    font-size:.75rem;
    font-weight:600;
    padding:5px 10px;
    border-radius:8px;
    border:1px solid #cbd5e1;
    background:#fff;
    cursor:pointer;
    transition:all .15s;
}
.qb-preset-btn:hover{
    background:#eff6ff;
    border-color:#3b82f6;
    color:#1d4ed8;
}

/* Table styles */
.mt-table thead th{background:var(--soft);border-top:0;color:#66768a;text-transform:uppercase;letter-spacing:.055em;font-size:.69rem;font-weight:700;padding:13px 15px;white-space:nowrap}
.mt-table td{padding:14px 15px;vertical-align:middle;border-color:#edf0f4}
.mt-quote-text{font-weight:600;color:#2f2340}
.mt-quote-author{color:var(--muted);font-size:.8rem;margin-top:3px}
.mt-status{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;font-weight:650;font-size:.76rem;white-space:nowrap}
.mt-status:before{content:"";width:6px;height:6px;background:currentColor;border-radius:50%}
.mt-status-on{background:#e7f7ef;color:#198754}
.mt-status-off{background:#f0f2f5;color:#68778b}
.mt-empty{text-align:center;padding:48px 20px}
.mt-actions{display:flex;gap:6px;align-items:center}
.mt-actions form{margin:0}
.qb-style-tag{font-size:.7rem;padding:3px 7px;border-radius:6px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;display:inline-flex;align-items:center;gap:4px}
</style>
@endsection

@section('main-content')
@php
    $homeQuoteId = collect($quotes)->first(fn ($quote) => !empty($quote['enabled']) && !empty($quote['text']))['id'] ?? null;
@endphp
<div class="mt-page">
@include('admin.section.flash_message')

<div class="card mt-hero mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="mt-eyebrow">Mobile Tool</div>
            <h2>Quotes & Motivational Banners</h2>
            <p>Design personalized inspirational quotes with live banner preview, Khmer & modern typography, full color & gradient fills, text-stroke, freehand angle rotation, and interactive drag-and-drop placement.</p>
        </div>
        <a href="#quote-designer" class="btn btn-light text-primary fw-bold shadow-sm">
            <i data-feather="plus-circle" class="me-1"></i> Add / Customize Quote
        </a>
    </div>
</div>

@if(isset($errors) && $errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<!-- ========================================== -->
<!-- 1. LIVE BANNER QUOTE DESIGNER (ADD FORM)    -->
<!-- ========================================== -->
<div class="card mt-card mb-4" id="quote-designer">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark">Add a quote</h5>
                <small class="text-muted">Drag the quote directly on the banner to position it. The preview shows how this quote is laid out on Home. Use the playback settings to rotate enabled quotes. Slideshow images take precedence over quote wallpaper.</small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetQuoteStudio('add')">
                    <i data-feather="rotate-ccw" style="width:14px;height:14px" class="me-1"></i> Reset Style
                </button>
            </div>
        </div>

        <form method="post" action="{{ route('admin.mobile-tools.quotes.store') }}" id="quote-form-add" onsubmit="prepareStylePayload('add')">
            @csrf
            <input type="hidden" name="style" id="style_json_add" value="">

            <div class="row g-4">
                <!-- LEFT COLUMN: LIVE BANNER CANVAS PREVIEW -->
                <div class="col-xl-7">
                    <div class="qb-preview-wrapper shadow">
                        <div class="qb-preview-header">
                            <div><i data-feather="smartphone" style="width:14px;height:14px" class="me-1 text-info"></i> Live Banner Canvas (Mobile layout)</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-dark border border-secondary text-info" id="pos_indicator_add">X: 65% | Y: 30%</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="toggle_mock_add" checked onchange="toggleMockWidgets('add')">
                                    <label class="form-check-label text-light small" for="toggle_mock_add">Show Clock</label>
                                </div>
                            </div>
                        </div>

                        <!-- THE BANNER -->
                        <div class="qb-banner" id="banner_canvas_add" style="background-image: url('{{ asset('assets/images/angkor_wat_banner.png') }}');">
                            <div class="qb-banner-overlay" id="banner_overlay_add"></div>

                            <!-- MOCK CLOCK & GREETING -->
                            <div class="qb-mock-header" id="mock_header_add">
                                <div class="qb-mock-khmer">សូមស្វាគមន៍!</div>
                                <div class="qb-mock-sub" id="mock_greeting_add">Good Morning!</div>
                                <div class="qb-mock-clock" data-quote-clock id="mock_clock_add">08:30 <span id="mock_sec_add">:15</span></div>
                                <div class="qb-mock-date">
                                    <i data-feather="calendar" style="width:11px;height:11px"></i>
                                    <span id="mock_date_add">{{ now()->format('l, F j, Y') }}</span>
                                </div>
                            </div>

                            <!-- DRAGGABLE QUOTE ELEMENT -->
                            <div class="qb-quote-box" id="quote_box_add" style="left:65%; top:30%;">
                                <div class="qb-drag-badge">Drag to move</div>
                                <div>
                                    <span class="qb-quote-mark" id="quote_mark_add">“</span>
                                    <span class="qb-quote-text" id="preview_text_add">ការងារជាក្រុម នាំមកនូវលទ្ធផលល្អ
សាមគ្គីភាពនាំឱ្យមានជ័យជំនះ</span>
                                </div>
                                <div class="qb-quote-author" id="preview_author_add">— Digital HR</div>
                            </div>
                        </div>

                        <!-- QUICK SNAPPING & PLACEMENT BAR -->
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary flex-wrap gap-2">
                            <div class="text-muted small">Quick Position Snapping:</div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('add', 65, 30)">Default Right</button>
                                <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('add', 50, 50)">Center</button>
                                <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('add', 70, 70)">Bottom Right</button>
                                <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('add', 50, 20)">Top Center</button>
                            </div>
                        </div>
                    </div>

                    <!-- 1-CLICK PRESET STYLES -->
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="qb-ctrl-label mb-2">
                            <span>✨ 1-Click Style Templates</span>
                            <small class="text-muted">Apply instantly</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="qb-preset-btn" onclick="applyPresetStyle('add', 'angkor_gold')">🌅 Angkor Sunrise (Gold)</button>
                            <button type="button" class="qb-preset-btn" onclick="applyPresetStyle('add', 'neon_cyber')">⚡ Neon Cyber (Cyan)</button>
                            <button type="button" class="qb-preset-btn" onclick="applyPresetStyle('add', 'royal_moul')">🏛️ Classic Moul (White)</button>
                            <button type="button" class="qb-preset-btn" onclick="applyPresetStyle('add', 'sunset_coral')">🌸 Sunset Coral (Pink)</button>
                            <button type="button" class="qb-preset-btn" onclick="applyPresetStyle('add', 'modern_minimal')">💎 Modern Minimal</button>
                            <button type="button" class="qb-preset-btn" onclick="applyPresetStyle('add', 'playful_italic')">🔥 Slanted Pop (-8°)</button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: FULL CUSTOMIZATION CONTROLS -->
                <div class="col-xl-5">
                    <!-- QUOTE TEXT & AUTHOR INPUTS -->
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="quote_text_add">Quote Content (Khmer / English)</label>
                        <textarea class="form-control" id="quote_text_add" name="text" rows="3" maxlength="500" required placeholder="ការងារជាក្រុម នាំមកនូវលទ្ធផលល្អ&#10;សាមគ្គីភាពនាំឱ្យមានជ័យជំនះ" oninput="updateLiveText('add')">{{ old('text', "ការងារជាក្រុម នាំមកនូវលទ្ធផលល្អ\nសាមគ្គីភាពនាំឱ្យមានជ័យជំនះ") }}</textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-8">
                            <label class="form-label fw-bold" for="quote_author_add">Author / Attribution</label>
                            <input class="form-control" type="text" id="quote_author_add" name="author" maxlength="120" value="{{ old('author', 'Digital HR') }}" placeholder="Steve Jobs / Anonymous" oninput="updateLiveAuthor('add')">
                        </div>
                        <div class="col-sm-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="quote_enabled_add" name="enabled" value="1" @checked(old('enabled', true))>
                                <label class="form-check-label fw-semibold" for="quote_enabled_add">Active</label>
                            </div>
                        </div>
                    </div>

                    <!-- CUSTOMIZATION TABS -->
                    <ul class="nav nav-pills qb-nav-pills mb-3 border-bottom pb-2" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-font-add" type="button">Font & Size</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-color-add" type="button">Color & Stroke</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-grad-add" type="button">Gradient</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-rotate-add" type="button">Rotate & Drag</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-banner-add" type="button">Banner</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: FONT & SIZE -->
                        <div class="tab-pane fade show active" id="tab-font-add">
                            <div class="mb-3">
                                <div class="qb-ctrl-label">Font Family</div>
                                <select class="form-select" id="font_family_add" onchange="applyStyle('add')">
                                    <optgroup label="Khmer Typography (ពុម្ពអក្សរខ្មែរ)">
                                        <option value="Kantumruy Pro" selected>Kantumruy Pro (Modern Sans)</option>
                                        <option value="Battambang">Battambang (Clean Reading)</option>
                                        <option value="Moul">Moul (Royal Traditional Headings)</option>
                                        <option value="Siemreap">Siemreap (Classic Text)</option>
                                        <option value="Bokor">Bokor (Decorative Script)</option>
                                        <option value="Koulen">Koulen (Bold Display)</option>
                                        <option value="Fasthand">Fasthand (Artistic Cursive)</option>
                                        <option value="Hanuman">Hanuman (Serif Traditional)</option>
                                    </optgroup>
                                    <optgroup label="Latin & Modern Display">
                                        <option value="Plus Jakarta Sans">Plus Jakarta Sans</option>
                                        <option value="Inter">Inter</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Playfair Display">Playfair Display (Luxury Serif)</option>
                                        <option value="Cinzel">Cinzel (Roman Elegance)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <div class="qb-ctrl-label">
                                        <span>Font Size</span>
                                        <span id="font_size_val_add" class="text-primary fw-bold">14 px</span>
                                    </div>
                                    <input type="range" class="form-range" id="font_size_add" min="10" max="36" value="14" oninput="applyStyle('add')">
                                </div>
                                <div class="col-4">
                                    <div class="qb-ctrl-label">Weight</div>
                                    <select class="form-select form-select-sm" id="font_weight_add" onchange="applyStyle('add')">
                                        <option value="400">Regular (400)</option>
                                        <option value="600">SemiBold (600)</option>
                                        <option value="700">Bold (700)</option>
                                        <option value="800" selected>ExtraBold (800)</option>
                                        <option value="900">Black (900)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="qb-ctrl-label">Text Alignment</div>
                                    <div class="btn-group w-100 btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="align_add" id="align_left_add" value="left" onchange="applyStyle('add')">
                                        <label class="btn btn-outline-secondary" for="align_left_add">Left</label>

                                        <input type="radio" class="btn-check" name="align_add" id="align_center_add" value="center" onchange="applyStyle('add')">
                                        <label class="btn btn-outline-secondary" for="align_center_add">Center</label>

                                        <input type="radio" class="btn-check" name="align_add" id="align_right_add" value="right" checked onchange="applyStyle('add')">
                                        <label class="btn btn-outline-secondary" for="align_right_add">Right</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="qb-ctrl-label">Font Style</div>
                                    <select class="form-select form-select-sm" id="font_style_add" onchange="applyStyle('add')">
                                        <option value="normal" selected>Normal</option>
                                        <option value="italic">Italic (ទ្រេត)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: COLOR, STROKE & SHADOW -->
                        <div class="tab-pane fade" id="tab-color-add">
                            <div class="mb-3">
                                <div class="qb-ctrl-label">
                                    <span>Text Color</span>
                                    <span id="color_val_add" class="text-primary fw-bold">#FFF7ED</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <input type="color" class="form-control form-control-color" id="text_color_add" value="#fff7ed" oninput="applyStyle('add')">
                                    <div class="d-flex gap-1 flex-wrap">
                                        <span class="qb-color-chip" style="background:#fff7ed" title="Bright Ivory" onclick="pickColor('add', '#fff7ed')"></span>
                                        <span class="qb-color-chip" style="background:#ffd54f" title="Gold" onclick="pickColor('add', '#ffd54f')"></span>
                                        <span class="qb-color-chip" style="background:#00e5ff" title="Neon Cyan" onclick="pickColor('add', '#00e5ff')"></span>
                                        <span class="qb-color-chip" style="background:#f472b6" title="Rose Pink" onclick="pickColor('add', '#f472b6')"></span>
                                        <span class="qb-color-chip" style="background:#4ade80" title="Lime Green" onclick="pickColor('add', '#4ade80')"></span>
                                        <span class="qb-color-chip" style="background:#ffffff" title="Pure White" onclick="pickColor('add', '#ffffff')"></span>
                                        <span class="qb-color-chip" style="background:#fb923c" title="Sunset Amber" onclick="pickColor('add', '#fb923c')"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- TEXT STROKE (OUTLINE) -->
                            <div class="p-2 border rounded-2 bg-light mb-3">
                                <div class="qb-ctrl-label">
                                    <span>Text Stroke / Outline (Stock)</span>
                                    <span id="stroke_val_add" class="text-primary fw-bold">0 px</span>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-8">
                                        <input type="range" class="form-range" id="stroke_width_add" min="0" max="6" step="0.5" value="0" oninput="applyStyle('add')">
                                    </div>
                                    <div class="col-4 d-flex align-items-center gap-2">
                                        <input type="color" class="form-control form-control-color" id="stroke_color_add" value="#000000" oninput="applyStyle('add')">
                                        <small class="text-muted">Outline</small>
                                    </div>
                                </div>
                            </div>

                            <!-- SHADOW & GLOW -->
                            <div class="p-2 border rounded-2 bg-light">
                                <div class="qb-ctrl-label">
                                    <span>Glow & Drop Shadow</span>
                                    <span id="shadow_val_add" class="text-primary fw-bold">5 px</span>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-8">
                                        <input type="range" class="form-range" id="shadow_blur_add" min="0" max="25" value="5" oninput="applyStyle('add')">
                                    </div>
                                    <div class="col-4">
                                        <input type="color" class="form-control form-control-color" id="shadow_color_add" value="#000000" oninput="applyStyle('add')">
                                    </div>
                                </div>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setShadowPreset('add', 'none')">None</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setShadowPreset('add', 'dark')">Deep Dark</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setShadowPreset('add', 'neon')">Cyan Glow</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setShadowPreset('add', 'gold')">Gold Glow</button>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: GRADIENT -->
                        <div class="tab-pane fade" id="tab-grad-add">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="grad_enabled_add" onchange="applyStyle('add')">
                                <label class="form-check-label fw-bold" for="grad_enabled_add">Enable Gradient Fill</label>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="qb-ctrl-label">Start Color</div>
                                    <input type="color" class="form-control form-control-color w-100" id="grad_start_add" value="#ffe082" oninput="applyStyle('add')">
                                </div>
                                <div class="col-6">
                                    <div class="qb-ctrl-label">End Color</div>
                                    <input type="color" class="form-control form-control-color w-100" id="grad_end_add" value="#ff8f00" oninput="applyStyle('add')">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="qb-ctrl-label">
                                    <span>Gradient Direction Angle</span>
                                    <span id="grad_angle_val_add" class="text-primary fw-bold">90°</span>
                                </div>
                                <input type="range" class="form-range" id="grad_angle_add" min="0" max="360" step="15" value="90" oninput="applyStyle('add')">
                            </div>

                            <div class="qb-ctrl-label mb-2">Gradient Swatches</div>
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="qb-preset-btn" onclick="setGradientColors('add', '#ffe082', '#ff8f00', 90)">Golden Dawn</button>
                                <button type="button" class="qb-preset-btn" onclick="setGradientColors('add', '#00f2fe', '#4facfe', 90)">Neon Aurora</button>
                                <button type="button" class="qb-preset-btn" onclick="setGradientColors('add', '#ff758c', '#ff7eb3', 90)">Sunset Coral</button>
                                <button type="button" class="qb-preset-btn" onclick="setGradientColors('add', '#10b981', '#6ee7b7', 90)">Emerald Glow</button>
                                <button type="button" class="qb-preset-btn" onclick="setGradientColors('add', '#c084fc', '#ec4899', 90)">Royal Pink</button>
                            </div>
                        </div>

                        <!-- TAB 4: ROTATE & DRAG POSITION -->
                        <div class="tab-pane fade" id="tab-rotate-add">
                            <!-- ROTATION -->
                            <div class="mb-3">
                                <div class="qb-ctrl-label">
                                    <span>Rotation Angle (Rotate)</span>
                                    <span id="rotation_val_add" class="text-primary fw-bold">0°</span>
                                </div>
                                <input type="range" class="form-range" id="rotation_add" min="-45" max="45" step="1" value="0" oninput="applyStyle('add')">
                                <div class="d-flex gap-1 mt-1 justify-content-between">
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setRotation('add', -15)">-15°</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setRotation('add', -5)">-5°</button>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" onclick="setRotation('add', 0)">0° Straight</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setRotation('add', 5)">+5°</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="setRotation('add', 15)">+15°</button>
                                </div>
                            </div>

                            <!-- DRAGGED COORDINATES -->
                            <div class="p-3 border rounded-2 bg-light mb-2">
                                <div class="qb-ctrl-label mb-2">
                                    <span>Banner Position Coordinates</span>
                                    <span class="badge bg-primary">Drag Canvas or Sliders</span>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1">X-Position (%)</label>
                                        <input type="range" class="form-range" id="pos_x_add" min="15" max="85" value="65" oninput="applyCoordsFromSlider('add')">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1">Y-Position (%)</label>
                                        <input type="range" class="form-range" id="pos_y_add" min="15" max="85" value="30" oninput="applyCoordsFromSlider('add')">
                                    </div>
                                </div>
                                <div class="form-text small text-muted">You can also click and freely drag the quote directly inside the banner preview canvas above!</div>
                            </div>
                        </div>

                        <!-- TAB 5: BANNER BACKGROUND -->
                        <div class="tab-pane fade" id="tab-banner-add">
                            @include('admin.mobile_tool.quote_banner_controls', ['scope' => 'add', 'bannerStyle' => []])
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i data-feather="plus" class="me-1"></i> Save quote
                        </button>
                        <button type="submit" class="btn btn-outline-primary" name="show_on_home" value="1">Save and show on Home</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. QUOTES LIST TABLE                       -->
<!-- ========================================== -->
@include('admin.mobile_tool.quote_playback')
<div class="card mt-card">
    <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 fw-bold">Configured Mobile Quotes ({{ count($quotes) }})</h5>
        <small class="text-muted">Enabled quotes are delivered to mobile employees and dynamically styled.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mt-table mb-0">
                <thead>
                    <tr>
                        <th style="width:40%">Quote & Typography</th>
                        <th>Author</th>
                        <th>Banner Style</th>
                        <th>Status</th>
                        <th style="width:18%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                    @php
                        $style = $quote['style'] ?? [];
                        $font = $style['fontFamily'] ?? 'Kantumruy Pro';
                        $color = $style['color'] ?? '#FFF7ED';
                        $rot = $style['rotation'] ?? 0;
                        $grad = !empty($style['gradientEnabled']);
                        $stroke = ($style['strokeWidth'] ?? 0) > 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="mt-quote-text mb-1" style="font-family:'{{ $font }}',sans-serif; max-width:480px;">
                                “{{ $quote['text'] }}”
                            </div>
                        </td>
                        <td class="text-muted fw-semibold">
                            {{ $quote['author'] ?? '—' }}
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="qb-style-tag" title="Font Family">
                                    <i data-feather="type" style="width:11px;height:11px"></i> {{ $font }}
                                </span>
                                <span class="qb-style-tag" title="Color swatch">
                                    <span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:{{ $color }};border:1px solid #94a3b8"></span>
                                    {{ $color }}
                                </span>
                                @if($rot != 0)
                                <span class="qb-style-tag text-info" title="Rotated">
                                    <i data-feather="rotate-cw" style="width:11px;height:11px"></i> {{ $rot }}°
                                </span>
                                @endif
                                @if($grad)
                                <span class="qb-style-tag text-warning" title="Gradient Fill">
                                    <i data-feather="sun" style="width:11px;height:11px"></i> Gradient
                                </span>
                                @endif
                                @if($stroke)
                                <span class="qb-style-tag text-primary" title="Stroke Outline">
                                    <i data-feather="square" style="width:11px;height:11px"></i> Stroke
                                </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="mt-status {{ !empty($quote['enabled']) ? 'mt-status-on' : 'mt-status-off' }}">
                                {{ $homeQuoteId === $quote['id'] ? 'First on Home' : (!empty($quote['enabled']) ? 'Enabled' : 'Disabled') }}
                            </span>
                        </td>
                        <td>
                            <div class="mt-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Edit with Banner Studio" data-bs-toggle="modal" data-bs-target="#quote-edit-{{ $loop->index }}">
                                    <i data-feather="edit-2" style="width:14px;height:14px"></i>
                                </button>
                                <form method="post" action="{{ route('admin.mobile-tools.quotes.toggle', $quote['id']) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ !empty($quote['enabled']) ? 'warning' : 'success' }}" title="{{ !empty($quote['enabled']) ? 'Disable' : 'Enable' }}">
                                        <i data-feather="{{ !empty($quote['enabled']) ? 'pause' : 'play' }}" style="width:14px;height:14px"></i>
                                    </button>
                                </form>
                                <form method="post" action="{{ route('admin.mobile-tools.quotes.destroy', $quote['id']) }}" onsubmit="return confirm('Delete this quote permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i data-feather="trash-2" style="width:14px;height:14px"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="mt-empty">
                                <h5>No quotes yet</h5>
                                <p class="text-muted mb-0">Use the interactive designer above to create your first customized banner quote.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. EDIT QUOTE MODALS WITH BANNER STUDIO     -->
<!-- ========================================== -->
@foreach($quotes as $quote)
@php
    $idx = $loop->index;
    $st = $quote['style'] ?? [];
    $initialStyle = [
        'fontFamily' => $st['fontFamily'] ?? 'Kantumruy Pro',
        'fontSize' => $st['fontSize'] ?? 14,
        'fontWeight' => $st['fontWeight'] ?? '800',
        'fontStyle' => $st['fontStyle'] ?? 'normal',
        'textAlign' => $st['textAlign'] ?? 'right',
        'color' => $st['color'] ?? '#FFF7ED',
        'opacity' => $st['opacity'] ?? 1.0,
        'rotation' => $st['rotation'] ?? 0,
        'posX' => $st['posX'] ?? 65.0,
        'posY' => $st['posY'] ?? 30.0,
        'strokeWidth' => $st['strokeWidth'] ?? 0,
        'strokeColor' => $st['strokeColor'] ?? '#000000',
        'gradientEnabled' => !empty($st['gradientEnabled']),
        'gradientStart' => $st['gradientStart'] ?? '#FFE082',
        'gradientEnd' => $st['gradientEnd'] ?? '#FF8F00',
        'gradientAngle' => $st['gradientAngle'] ?? 90,
        'shadowBlur' => $st['shadowBlur'] ?? 5,
        'shadowColor' => $st['shadowColor'] ?? 'rgba(0,0,0,0.7)',
        'bannerImage' => $st['bannerImage'] ?? asset('assets/images/angkor_wat_banner.png'),
        'bannerOverlay' => $st['bannerOverlay'] ?? 0.35,
    ];
@endphp
<div class="modal fade" id="quote-edit-{{ $idx }}" tabindex="-1" aria-hidden="true" data-quote-index="{{ $idx }}">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.mobile-tools.quotes.update', $quote['id']) }}" id="quote-form-edit-{{ $idx }}" onsubmit="prepareStylePayload('edit_{{ $idx }}')">
                @csrf
                <input type="hidden" name="style" id="style_json_edit_{{ $idx }}" value="{{ json_encode($initialStyle) }}">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">
                        <i data-feather="edit-3" class="me-1 text-primary"></i> Edit Banner Quote Studio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- BANNER CANVAS IN MODAL -->
                        <div class="col-lg-6">
                            <div class="qb-preview-wrapper shadow">
                                <div class="qb-preview-header">
                                    <div><i data-feather="smartphone" class="me-1 text-info"></i> Banner Preview</div>
                                    <span class="badge bg-dark border border-secondary text-info" id="pos_indicator_edit_{{ $idx }}">
                                        X: {{ round($initialStyle['posX']) }}% | Y: {{ round($initialStyle['posY']) }}%
                                    </span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="toggle_mock_edit_{{ $idx }}" checked onchange="toggleMockWidgets('edit_{{ $idx }}')">
                                        <label class="form-check-label text-light small" for="toggle_mock_edit_{{ $idx }}">Show Clock</label>
                                    </div>
                                </div>

                            @if(!empty($previewSlides))
                            <label for="preview_slide_edit_{{ $idx }}" class="text-light small">Preview background</label>
                            <select class="form-select form-select-sm mb-2" id="preview_slide_edit_{{ $idx }}" onchange="applyStyle('edit_{{ $idx }}')">
                                <option value="">Quote wallpaper</option>
                                @foreach($previewSlides as $previewSlide)
                                <option value="{{ $previewSlide['imageUrl'] }}">{{ ($previewSlide['title'] ?? '') ?: 'Slideshow image '.$loop->iteration }}</option>
                                @endforeach
                            </select>
                            @endif

                                <div class="qb-banner" id="banner_canvas_edit_{{ $idx }}" style="background-image: url('{{ $initialStyle['bannerImage'] }}');">
                                    <div class="qb-banner-overlay" id="banner_overlay_edit_{{ $idx }}"></div>

                                    <div class="qb-mock-header" id="mock_header_edit_{{ $idx }}">
                                <div class="qb-mock-khmer">សូមស្វាគមន៍!</div>
                                <div class="qb-mock-sub" id="mock_greeting_edit_{{ $idx }}">Good Morning!</div>
                                <div class="qb-mock-clock" data-quote-clock id="mock_clock_edit_{{ $idx }}">08:30 <span id="mock_sec_edit_{{ $idx }}">:15</span></div>
                                <div class="qb-mock-date">
                                    <i data-feather="calendar" style="width:11px;height:11px"></i>
                                    <span id="mock_date_edit_{{ $idx }}">{{ now()->format('l, F j, Y') }}</span>
                                </div>
                            </div>

                                    <div class="qb-quote-box" id="quote_box_edit_{{ $idx }}" style="left:{{ $initialStyle['posX'] }}%; top:{{ $initialStyle['posY'] }}%;">
                                        <div class="qb-drag-badge">Drag to move</div>
                                        <div>
                                            <span class="qb-quote-mark" id="quote_mark_edit_{{ $idx }}">“</span>
                                            <span class="qb-quote-text" id="preview_text_edit_{{ $idx }}">{{ $quote['text'] }}</span>
                                                </div>
                                        <div class="qb-quote-author" id="preview_author_edit_{{ $idx }}">
                                            @if(!empty($quote['author']))— {{ $quote['author'] }}@endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary flex-wrap gap-2">
                                    <div class="text-muted small">Snapping:</div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('edit_{{ $idx }}', 65, 30)">Right</button>
                                        <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('edit_{{ $idx }}', 50, 50)">Center</button>
                                        <button type="button" class="btn btn-outline-light" onclick="snapQuotePosition('edit_{{ $idx }}', 70, 70)">Bottom</button>
                                    </div>
                                </div>
                            </div>
                            @include('admin.mobile_tool.quote_banner_controls', ['scope' => 'edit_'.$idx, 'bannerStyle' => $initialStyle])
                        </div>

                        <!-- CONTROLS IN MODAL -->
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quote Content</label>
                                <textarea class="form-control" id="quote_text_edit_{{ $idx }}" name="text" rows="2" maxlength="500" required oninput="updateLiveText('edit_{{ $idx }}')">{{ old("quotes.{$idx}.text", $quote['text']) }}</textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <label class="form-label fw-bold">Author</label>
                                    <input class="form-control form-control-sm" type="text" id="quote_author_edit_{{ $idx }}" name="author" maxlength="120" value="{{ old("quotes.{$idx}.author", $quote['author'] ?? '') }}" oninput="updateLiveAuthor('edit_{{ $idx }}')">
                                </div>
                                <div class="col-4 d-flex align-items-end">
                                    <div class="form-check form-switch mb-1">
                                        <input type="hidden" name="enabled" value="0">
                                        <input class="form-check-input" type="checkbox" id="edit_enabled_{{ $idx }}" name="enabled" value="1" @checked(old("quotes.{$idx}.enabled", !empty($quote['enabled'])))>
                                        <label class="form-check-label small fw-bold" for="edit_enabled_{{ $idx }}">Active</label>
                                    </div>
                                </div>
                            </div>

                            <!-- MINI TABS IN MODAL -->
                            <ul class="nav nav-pills qb-nav-pills mb-2 border-bottom pb-1" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active py-1 px-2" data-bs-toggle="pill" data-bs-target="#mtab-font-{{ $idx }}" type="button">Font</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#mtab-color-{{ $idx }}" type="button">Color/Stroke</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#mtab-grad-{{ $idx }}" type="button">Gradient</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#mtab-rot-{{ $idx }}" type="button">Rotate</button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="mtab-font-{{ $idx }}">
                                    <div class="row g-2 mb-2">
                                        <div class="col-7">
                                            <label class="form-label small text-muted mb-0">Font Family</label>
                                            <select class="form-select form-select-sm" id="font_family_edit_{{ $idx }}" onchange="applyStyle('edit_{{ $idx }}')">
                                                <option value="Kantumruy Pro" @selected($initialStyle['fontFamily'] === 'Kantumruy Pro')>Kantumruy Pro</option>
                                                <option value="Battambang" @selected($initialStyle['fontFamily'] === 'Battambang')>Battambang</option>
                                                <option value="Moul" @selected($initialStyle['fontFamily'] === 'Moul')>Moul</option>
                                                <option value="Siemreap" @selected($initialStyle['fontFamily'] === 'Siemreap')>Siemreap</option>
                                                <option value="Bokor" @selected($initialStyle['fontFamily'] === 'Bokor')>Bokor</option>
                                                <option value="Koulen" @selected($initialStyle['fontFamily'] === 'Koulen')>Koulen</option>
                                                <option value="Fasthand" @selected($initialStyle['fontFamily'] === 'Fasthand')>Fasthand</option>
                                                <option value="Plus Jakarta Sans" @selected($initialStyle['fontFamily'] === 'Plus Jakarta Sans')>Plus Jakarta Sans</option>
                                                <option value="Inter" @selected($initialStyle['fontFamily'] === 'Inter')>Inter</option>
                                                <option value="Playfair Display" @selected($initialStyle['fontFamily'] === 'Playfair Display')>Playfair Display</option>
                                            </select>
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small text-muted mb-0">Weight</label>
                                            <select class="form-select form-select-sm" id="font_weight_edit_{{ $idx }}" onchange="applyStyle('edit_{{ $idx }}')">
                                                <option value="400" @selected($initialStyle['fontWeight'] == '400')>Regular</option>
                                                <option value="600" @selected($initialStyle['fontWeight'] == '600')>SemiBold</option>
                                                <option value="700" @selected($initialStyle['fontWeight'] == '700')>Bold</option>
                                                <option value="800" @selected($initialStyle['fontWeight'] == '800')>ExtraBold</option>
                                                <option value="900" @selected($initialStyle['fontWeight'] == '900')>Black</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small">
                                            <span>Size</span>
                                            <span id="font_size_val_edit_{{ $idx }}" class="text-primary fw-bold">{{ $initialStyle['fontSize'] }} px</span>
                                        </div>
                                        <input type="range" class="form-range" id="font_size_edit_{{ $idx }}" min="10" max="36" value="{{ $initialStyle['fontSize'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="mtab-color-{{ $idx }}">
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-0">Text Color</label>
                                            <input type="color" class="form-control form-control-color w-100" id="text_color_edit_{{ $idx }}" value="{{ $initialStyle['color'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-0">Stroke Color</label>
                                            <input type="color" class="form-control form-control-color w-100" id="stroke_color_edit_{{ $idx }}" value="{{ $initialStyle['strokeColor'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small">
                                            <span>Stroke Width</span>
                                            <span id="stroke_val_edit_{{ $idx }}" class="text-primary fw-bold">{{ $initialStyle['strokeWidth'] }} px</span>
                                        </div>
                                        <input type="range" class="form-range" id="stroke_width_edit_{{ $idx }}" min="0" max="6" step="0.5" value="{{ $initialStyle['strokeWidth'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="mtab-grad-{{ $idx }}">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="grad_enabled_edit_{{ $idx }}" @checked($initialStyle['gradientEnabled']) onchange="applyStyle('edit_{{ $idx }}')">
                                        <label class="form-check-label small fw-bold" for="grad_enabled_edit_{{ $idx }}">Enable Gradient</label>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="color" class="form-control form-control-color w-100" id="grad_start_edit_{{ $idx }}" value="{{ $initialStyle['gradientStart'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                        </div>
                                        <div class="col-6">
                                            <input type="color" class="form-control form-control-color w-100" id="grad_end_edit_{{ $idx }}" value="{{ $initialStyle['gradientEnd'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="mtab-rot-{{ $idx }}">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Rotation Angle</span>
                                        <span id="rotation_val_edit_{{ $idx }}" class="text-primary fw-bold">{{ $initialStyle['rotation'] }}°</span>
                                    </div>
                                    <input type="range" class="form-range" id="rotation_edit_{{ $idx }}" min="-45" max="45" step="1" value="{{ $initialStyle['rotation'] }}" oninput="applyStyle('edit_{{ $idx }}')">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i data-feather="check" class="me-1"></i> Save Changes
                    </button>
                    <button type="submit" class="btn btn-outline-primary" name="show_on_home" value="1">Save and show on Home</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

</div>

<script>
// Global State storage for studio instances (e.g. 'add' or 'edit_0')
window.quoteStudioState = window.quoteStudioState || {};

// Initialize state with defaults
function initStudioState(scope, defaults) {
    window.quoteStudioState[scope] = Object.assign({
        fontFamily: 'Kantumruy Pro',
        fontSize: 14,
        fontWeight: '800',
        fontStyle: 'normal',
        textAlign: 'right',
        color: '#FFF7ED',
        opacity: 1.0,
        rotation: 0,
        posX: 65.0,
        posY: 30.0,
        strokeWidth: 0,
        strokeColor: '#000000',
        gradientEnabled: false,
        gradientStart: '#FFE082',
        gradientEnd: '#FF8F00',
        gradientAngle: 90,
        shadowBlur: 5,
        shadowColor: 'rgba(0,0,0,0.7)',
        bannerImage: '{{ asset("assets/images/angkor_wat_banner.png") }}',
        bannerOverlay: 0.35,
    }, defaults || {});
}

// Live update text
function updateLiveText(scope) {
    const input = document.getElementById(`quote_text_${scope}`);
    const preview = document.getElementById(`preview_text_${scope}`);
    if (input && preview) {
        preview.textContent = (input.value || 'Quote text preview') + ' ”';
    }
}

// Live update author
function updateLiveAuthor(scope) {
    const input = document.getElementById(`quote_author_${scope}`);
    const preview = document.getElementById(`preview_author_${scope}`);
    if (input && preview) {
        preview.textContent = input.value.trim() ? `— ${input.value.trim()}` : '';
    }
}

// Apply Styles
function applyStyle(scope) {
    const s = window.quoteStudioState[scope];
    if (!s) return;

    // Read form values
    const fontEl = document.getElementById(`font_family_${scope}`);
    if (fontEl) s.fontFamily = fontEl.value;

    const sizeEl = document.getElementById(`font_size_${scope}`);
    if (sizeEl) {
        s.fontSize = parseFloat(sizeEl.value);
        const valEl = document.getElementById(`font_size_val_${scope}`);
        if (valEl) valEl.textContent = `${s.fontSize} px`;
    }

    const weightEl = document.getElementById(`font_weight_${scope}`);
    if (weightEl) s.fontWeight = weightEl.value;

    const styleEl = document.getElementById(`font_style_${scope}`);
    if (styleEl) s.fontStyle = styleEl.value;

    const alignChecked = document.querySelector(`input[name="align_${scope}"]:checked`);
    if (alignChecked) s.textAlign = alignChecked.value;

    const colorEl = document.getElementById(`text_color_${scope}`);
    if (colorEl) {
        s.color = colorEl.value;
        const cValEl = document.getElementById(`color_val_${scope}`);
        if (cValEl) cValEl.textContent = s.color;
    }

    const strokeWEl = document.getElementById(`stroke_width_${scope}`);
    if (strokeWEl) {
        s.strokeWidth = parseFloat(strokeWEl.value);
        const stValEl = document.getElementById(`stroke_val_${scope}`);
        if (stValEl) stValEl.textContent = `${s.strokeWidth} px`;
    }

    const strokeCEl = document.getElementById(`stroke_color_${scope}`);
    if (strokeCEl) s.strokeColor = strokeCEl.value;

    const shadowBlurEl = document.getElementById(`shadow_blur_${scope}`);
    if (shadowBlurEl) {
        s.shadowBlur = parseFloat(shadowBlurEl.value);
        const shValEl = document.getElementById(`shadow_val_${scope}`);
        if (shValEl) shValEl.textContent = `${s.shadowBlur} px`;
    }

    const shadowCEl = document.getElementById(`shadow_color_${scope}`);
    if (shadowCEl) s.shadowColor = shadowCEl.value;

    const gradEnEl = document.getElementById(`grad_enabled_${scope}`);
    if (gradEnEl) s.gradientEnabled = gradEnEl.checked;

    const gradSEl = document.getElementById(`grad_start_${scope}`);
    if (gradSEl) s.gradientStart = gradSEl.value;

    const gradEEl = document.getElementById(`grad_end_${scope}`);
    if (gradEEl) s.gradientEnd = gradEEl.value;

    const gradAEl = document.getElementById(`grad_angle_${scope}`);
    if (gradAEl) {
        s.gradientAngle = parseInt(gradAEl.value, 10);
        const aValEl = document.getElementById(`grad_angle_val_${scope}`);
        if (aValEl) aValEl.textContent = `${s.gradientAngle}°`;
    }

    const rotEl = document.getElementById(`rotation_${scope}`);
    if (rotEl) {
        s.rotation = parseFloat(rotEl.value);
        const rValEl = document.getElementById(`rotation_val_${scope}`);
        if (rValEl) rValEl.textContent = `${s.rotation}°`;
    }

    const slide = document.getElementById(`preview_slide_${scope}`)?.value;
    renderQuoteWallpaper(scope, slide || s.bannerImage);

    // Apply to DOM preview box
    const box = document.getElementById(`quote_box_${scope}`);
    const textEl = document.getElementById(`preview_text_${scope}`);
    const authorEl = document.getElementById(`preview_author_${scope}`);
    const markEl = document.getElementById(`quote_mark_${scope}`);

    if (box) {
        box.style.left = `${s.posX}%`;
        box.style.top = `${s.posY}%`;
        box.style.transform = `translate(-50%, -50%) rotate(${s.rotation}deg)`;
        box.style.textAlign = s.textAlign;
    }

    if (textEl) {
        textEl.style.fontFamily = `'${s.fontFamily}', sans-serif`;
        textEl.style.fontSize = `${Math.min(40, Math.max(8, s.fontSize))}px`;
        textEl.style.opacity = s.opacity;
        textEl.style.fontWeight = s.fontWeight;
        textEl.style.fontStyle = s.fontStyle;

        if (s.strokeWidth > 0) {
            textEl.style.webkitTextStroke = `${s.strokeWidth}px ${s.strokeColor}`;
        } else {
            textEl.style.webkitTextStroke = '0px transparent';
        }

        if (s.gradientEnabled) {
            textEl.style.background = `linear-gradient(${s.gradientAngle}deg, ${s.gradientStart}, ${s.gradientEnd})`;
            textEl.style.webkitBackgroundClip = 'text';
            textEl.style.webkitTextFillColor = 'transparent';
        } else {
            textEl.style.background = 'none';
            textEl.style.webkitBackgroundClip = 'initial';
            textEl.style.webkitTextFillColor = 'initial';
            textEl.style.color = s.color;
        }

        if (s.shadowBlur > 0) {
            textEl.style.textShadow = `0px 2px ${s.shadowBlur}px ${s.shadowColor}`;
        } else {
            textEl.style.textShadow = 'none';
        }
    }

    if (authorEl) {
        authorEl.style.fontFamily = `'${s.fontFamily}', sans-serif`;
        authorEl.style.color = s.color;
        authorEl.style.fontSize = `${Math.min(12, Math.max(7.5, Math.min(40, s.fontSize) * .82))}px`;
        authorEl.style.fontStyle = s.fontStyle;
        authorEl.style.opacity = Math.max(.2, s.opacity * .85);
        authorEl.style.textAlign = s.textAlign;
    }

    if (markEl) {
        markEl.style.color = s.gradientEnabled ? s.gradientStart : '#ffd54f';
    }

    // Update coordinates badge
    const overlay = document.getElementById(`banner_overlay_${scope}`);
    if (overlay) {
        const opacity = Math.max(0, Math.min(.8, slide ? .35 : s.bannerOverlay));
        overlay.style.background = `linear-gradient(90deg, rgba(10,25,47,${Math.min(1, opacity + .3)}), rgba(10,25,47,${opacity * .7}), rgba(10,25,47,${Math.min(1, opacity + .2)}))`;
    }
    const overlayValue = document.getElementById(`overlay_val_${scope}`);
    if (overlayValue) {
        const savedOpacity = Math.max(0, Math.min(80, Math.round(s.bannerOverlay * 100)));
        overlayValue.textContent = `${savedOpacity}%`;
    }
    const badge = document.getElementById(`pos_indicator_${scope}`);
    if (badge) {
        badge.textContent = `X: ${Math.round(s.posX)}% | Y: ${Math.round(s.posY)}%`;
    }
}

// Color picker shortcut
function pickColor(scope, hex) {
    const colorEl = document.getElementById(`text_color_${scope}`);
    if (colorEl) {
        colorEl.value = hex;
        applyStyle(scope);
    }
}

// Rotation shortcut
function setRotation(scope, angle) {
    const rotEl = document.getElementById(`rotation_${scope}`);
    if (rotEl) {
        rotEl.value = angle;
        applyStyle(scope);
    }
}

// Shadow preset
function setShadowPreset(scope, type) {
    const blurEl = document.getElementById(`shadow_blur_${scope}`);
    const colEl = document.getElementById(`shadow_color_${scope}`);
    if (!blurEl || !colEl) return;

    if (type === 'none') {
        blurEl.value = 0;
    } else if (type === 'dark') {
        blurEl.value = 6;
        colEl.value = '#000000';
    } else if (type === 'neon') {
        blurEl.value = 12;
        colEl.value = '#00e5ff';
    } else if (type === 'gold') {
        blurEl.value = 10;
        colEl.value = '#f59e0b';
    }
    applyStyle(scope);
}

// Gradient shortcut
function setGradientColors(scope, start, end, angle) {
    const enEl = document.getElementById(`grad_enabled_${scope}`);
    const stEl = document.getElementById(`grad_start_${scope}`);
    const endEl = document.getElementById(`grad_end_${scope}`);
    const aEl = document.getElementById(`grad_angle_${scope}`);
    if (enEl) enEl.checked = true;
    if (stEl) stEl.value = start;
    if (endEl) endEl.value = end;
    if (aEl) aEl.value = angle || 90;
    applyStyle(scope);
}

// Quick Position Snapping
function snapQuotePosition(scope, x, y) {
    const s = window.quoteStudioState[scope];
    if (s) {
        s.posX = x;
        s.posY = y;
        const xEl = document.getElementById(`pos_x_${scope}`);
        const yEl = document.getElementById(`pos_y_${scope}`);
        if (xEl) xEl.value = x;
        if (yEl) yEl.value = y;
        applyStyle(scope);
    }
}

// Coordinate Sliders
function applyCoordsFromSlider(scope) {
    const s = window.quoteStudioState[scope];
    const xEl = document.getElementById(`pos_x_${scope}`);
    const yEl = document.getElementById(`pos_y_${scope}`);
    if (s && xEl && yEl) {
        s.posX = parseFloat(xEl.value);
        s.posY = parseFloat(yEl.value);
        applyStyle(scope);
    }
}

// Banner Background Switcher
function changeBannerBackground(scope) {
    const select = document.getElementById(`banner_bg_${scope}`);
    const canvas = document.getElementById(`banner_canvas_${scope}`);
    if (!select || !canvas) return;

    const s = window.quoteStudioState[scope];
    if (!s) return;
    s.bannerImage = select.value;
    const input = document.getElementById(`banner_custom_url_${scope}`);
    if (input) input.value = '';
    previewQuoteWallpaper(scope);
}

function changeBannerCustomUrl(scope) {
    const input = document.getElementById(`banner_custom_url_${scope}`);
    const s = window.quoteStudioState[scope];
    if (!input || !s) return;
    const select = document.getElementById(`banner_bg_${scope}`);
    s.bannerImage = input.value.trim() || select?.value || '';
    previewQuoteWallpaper(scope);
}

function renderQuoteWallpaper(scope, wallpaper) {
    const canvas = document.getElementById(`banner_canvas_${scope}`);
    if (!canvas) return;
    const gradients = {
        'gradient:royal': 'linear-gradient(135deg, #0b192c, #1e3e62)',
        'gradient:sunset': 'linear-gradient(135deg, #1e1b4b, #831843, #f59e0b)',
        'gradient:emerald': 'linear-gradient(135deg, #064e3b, #047857, #10b981)',
    };
    const fallback = @json(asset('assets/images/angkor_wat_banner.png'));
    if (Object.hasOwn(gradients, wallpaper)) {
        canvas.style.backgroundImage = gradients[wallpaper];
        return;
    }
    let url = fallback;
    try {
        const candidate = new URL(wallpaper || fallback, document.baseURI);
        if (['http:', 'https:'].includes(candidate.protocol)) {
            url = candidate.pathname.endsWith('/assets/images/angkor_wat_banner.jpg')
                ? fallback : candidate.href;
        }
    } catch (_) {}
    canvas.style.backgroundImage = `url(${JSON.stringify(url)})`;
}

function previewQuoteWallpaper(scope) {
    const preview = document.getElementById(`preview_slide_${scope}`);
    if (preview) preview.value = '';
    applyStyle(scope);
}

function applyOverlay(scope) {
    const slider = document.getElementById(`banner_overlay_slider_${scope}`);
    const settings = window.quoteStudioState[scope];
    if (!slider || !settings) return;

    settings.bannerOverlay = Number(slider.value) / 100;
    previewQuoteWallpaper(scope);
}

function toggleMockWidgets(scope) {
    const toggle = document.getElementById(`toggle_mock_${scope}`);
    const header = document.getElementById(`mock_header_${scope}`);
    if (toggle && header) {
        header.style.display = toggle.checked ? 'flex' : 'none';
    }
}

// 1-Click Style Templates
function applyPresetStyle(scope, template) {
    const s = window.quoteStudioState[scope];
    if (!s) return;

    if (template === 'angkor_gold') {
        s.fontFamily = 'Kantumruy Pro';
        s.fontSize = 15;
        s.fontWeight = '800';
        s.gradientEnabled = true;
        s.gradientStart = '#ffe082';
        s.gradientEnd = '#ff8f00';
        s.gradientAngle = 90;
        s.strokeWidth = 0.5;
        s.strokeColor = '#000000';
        s.shadowBlur = 6;
        s.shadowColor = 'rgba(0,0,0,0.8)';
        s.rotation = 0;
        s.posX = 65;
        s.posY = 30;
    } else if (template === 'neon_cyber') {
        s.fontFamily = 'Plus Jakarta Sans';
        s.fontSize = 14;
        s.fontWeight = '800';
        s.gradientEnabled = false;
        s.color = '#00e5ff';
        s.strokeWidth = 0;
        s.shadowBlur = 12;
        s.shadowColor = '#00e5ff';
        s.rotation = 0;
        s.posX = 65;
        s.posY = 30;
    } else if (template === 'royal_moul') {
        s.fontFamily = 'Moul';
        s.fontSize = 13;
        s.fontWeight = '700';
        s.gradientEnabled = false;
        s.color = '#ffffff';
        s.strokeWidth = 1.0;
        s.strokeColor = '#000000';
        s.shadowBlur = 4;
        s.shadowColor = 'rgba(0,0,0,0.8)';
        s.rotation = 0;
        s.posX = 65;
        s.posY = 32;
    } else if (template === 'sunset_coral') {
        s.fontFamily = 'Kantumruy Pro';
        s.fontSize = 14;
        s.fontWeight = '800';
        s.gradientEnabled = true;
        s.gradientStart = '#ff758c';
        s.gradientEnd = '#ff7eb3';
        s.gradientAngle = 90;
        s.strokeWidth = 0;
        s.shadowBlur = 6;
        s.shadowColor = 'rgba(0,0,0,0.7)';
        s.rotation = 0;
        s.posX = 65;
        s.posY = 30;
    } else if (template === 'modern_minimal') {
        s.fontFamily = 'Inter';
        s.fontSize = 13;
        s.fontWeight = '600';
        s.gradientEnabled = false;
        s.color = '#fff7ed';
        s.strokeWidth = 0;
        s.shadowBlur = 4;
        s.shadowColor = 'rgba(0,0,0,0.6)';
        s.rotation = 0;
        s.posX = 65;
        s.posY = 30;
    } else if (template === 'playful_italic') {
        s.fontFamily = 'Fasthand';
        s.fontSize = 18;
        s.fontWeight = '600';
        s.gradientEnabled = false;
        s.color = '#ffd54f';
        s.strokeWidth = 0.5;
        s.strokeColor = '#000000';
        s.shadowBlur = 6;
        s.shadowColor = 'rgba(0,0,0,0.8)';
        s.rotation = -8;
        s.posX = 60;
        s.posY = 35;
    }

    // Sync form controls
    syncFormInputsFromState(scope);
    applyStyle(scope);
}

function syncFormInputsFromState(scope) {
    const s = window.quoteStudioState[scope];
    if (!s) return;

    const fontEl = document.getElementById(`font_family_${scope}`);
    if (fontEl) fontEl.value = s.fontFamily;

    const sizeEl = document.getElementById(`font_size_${scope}`);
    if (sizeEl) sizeEl.value = s.fontSize;

    const weightEl = document.getElementById(`font_weight_${scope}`);
    if (weightEl) weightEl.value = s.fontWeight;

    const colorEl = document.getElementById(`text_color_${scope}`);
    if (colorEl) colorEl.value = s.color;

    const strokeWEl = document.getElementById(`stroke_width_${scope}`);
    if (strokeWEl) strokeWEl.value = s.strokeWidth;

    const strokeCEl = document.getElementById(`stroke_color_${scope}`);
    if (strokeCEl) strokeCEl.value = s.strokeColor;

    const gradEnEl = document.getElementById(`grad_enabled_${scope}`);
    if (gradEnEl) gradEnEl.checked = s.gradientEnabled;

    const gradSEl = document.getElementById(`grad_start_${scope}`);
    if (gradSEl) gradSEl.value = s.gradientStart;

    const gradEEl = document.getElementById(`grad_end_${scope}`);
    if (gradEEl) gradEEl.value = s.gradientEnd;

    const rotEl = document.getElementById(`rotation_${scope}`);
    if (rotEl) rotEl.value = s.rotation;

    const overlayEl = document.getElementById(`banner_overlay_slider_${scope}`);
    if (overlayEl) overlayEl.value = Math.round(s.bannerOverlay * 100);
    const wallpaperEl = document.getElementById(`banner_bg_${scope}`);
    if (wallpaperEl) wallpaperEl.value = s.bannerImage;
    const customUrlEl = document.getElementById(`banner_custom_url_${scope}`);
    if (customUrlEl) customUrlEl.value = wallpaperEl?.value ? '' : s.bannerImage;
}

function resetQuoteStudio(scope) {
    initStudioState(scope);
    syncFormInputsFromState(scope);
    previewQuoteWallpaper(scope);
}

// Drag & Drop Handler
function setupDraggableQuote(scope) {
    const canvas = document.getElementById(`banner_canvas_${scope}`);
    const box = document.getElementById(`quote_box_${scope}`);
    if (!canvas || !box) return;

    let isDragging = false;
    let startX, startY;

    function onPointerDown(e) {
        isDragging = true;
        box.classList.add('is-dragging');
        canvas.setPointerCapture(e.pointerId);
        e.preventDefault();
    }

    function onPointerMove(e) {
        if (!isDragging) return;
        const rect = canvas.getBoundingClientRect();
        const clientX = e.clientX;
        const clientY = e.clientY;

        let posX = ((clientX - rect.left) / rect.width) * 100;
        let posY = ((clientY - rect.top) / rect.height) * 100;

        posX = Math.max(10, Math.min(90, posX));
        posY = Math.max(10, Math.min(90, posY));

        const s = window.quoteStudioState[scope];
        if (s) {
            s.posX = posX;
            s.posY = posY;
            applyStyle(scope);
        }
    }

    function onPointerUp(e) {
        if (!isDragging) return;
        isDragging = false;
        box.classList.remove('is-dragging');
        try {
            canvas.releasePointerCapture(e.pointerId);
        } catch (_) {}
    }

    box.addEventListener('pointerdown', onPointerDown);
    canvas.addEventListener('pointermove', onPointerMove);
    canvas.addEventListener('pointerup', onPointerUp);
    canvas.addEventListener('pointercancel', onPointerUp);
}

// Serialize Style before submitting
function prepareStylePayload(scope) {
    const s = window.quoteStudioState[scope];
    const input = document.getElementById(`style_json_${scope}`);
    if (s && input) {
        input.value = JSON.stringify(s);
    }
}

// Live Clock for Banner Canvas
function startLiveClock() {
    function tick() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        for (const clock of document.querySelectorAll('[data-quote-clock]')) {
            clock.firstChild.textContent = `${h}:${m} `;
            clock.querySelector('span').textContent = `:${s}`;
        }
    }
    tick();
    setInterval(tick, 1000);
}

function setupQuotePlayback() {
    const form = document.getElementById('quote-playback-form');
    if (!form) return;
    const quotes = @js(array_values(array_filter($quotes, fn ($quote) => !empty($quote['enabled']) && !empty($quote['text']))));
    const canvas = document.getElementById('banner_canvas_playback');
    const box = document.getElementById('quote_box_playback');
    const text = document.getElementById('preview_text_playback');
    const author = document.getElementById('preview_author_playback');
    const position = document.getElementById('quote-playback-position');
    const previousButton = document.getElementById('quote-playback-prev');
    const nextButton = document.getElementById('quote-playback-next');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let index = 0;
    let timer;
    let pointerHeld = false;
    let animation;
    let previousBox;
    const option = key => form.elements.namedItem(key).value === '1';

    function schedule() {
        clearTimeout(timer);
        if (quotes.length < 2 || !option('autoplay') || reducedMotion.matches
            || (pointerHeld && option('pauseOnTouch')) || (!option('loop') && index === quotes.length - 1)) return;
        const seconds = Math.min(30, Math.max(2, Number(form.elements.intervalSeconds.value) || 5));
        timer = setTimeout(() => step(1), seconds * 1000);
    }

    function render(animate) {
        const quote = quotes[index];
        const transition = form.elements.transition.value;
        animation?.cancel();
        previousBox?.remove();
        const animateQuote = animate && transition !== 'instant' && !reducedMotion.matches;
        const previous = animateQuote ? box.cloneNode(true) : null;
        if (previous) {
            previous.removeAttribute('id');
            previous.querySelectorAll('[id]').forEach(element => element.removeAttribute('id'));
            previous.setAttribute('aria-hidden', 'true');
            box.before(previous);
            previousBox = previous;
        }
        initStudioState('playback', quote?.style || {});
        text.textContent = quote ? quote.text + ' ”' : '';
        author.textContent = quote?.author ? '— ' + quote.author : '';
        box.hidden = !quote;
        position.textContent = quote ? `Quote ${index + 1} of ${quotes.length}` : 'Enable a quote to preview playback.';
        previousButton.disabled = nextButton.disabled = quotes.length < 2;
        applyStyle('playback');
        if (previous) {
            const transforms = {
                slide: `translateX(${canvas.clientWidth * .08}px)`,
                'slide-up': `translateY(${canvas.clientHeight * .08}px)`,
            };
            const offset = transforms[transition] || '';
            const move = element => transition === 'zoom'
                ? element.style.transform + ' scale(.92)'
                : `${offset} ${element.style.transform}`.trim();
            previous.animate(
                [{opacity: 1, transform: previous.style.transform}, {opacity: 0, transform: move(previous)}],
                {duration: 480, easing: 'cubic-bezier(.55,.055,.675,.19)'}
            ).onfinish = () => previous.remove();
            animation = box.animate(
                [{opacity: 0, transform: move(box)}, {opacity: 1, transform: box.style.transform}],
                {duration: 480, easing: 'cubic-bezier(.215,.61,.355,1)'}
            );
        }
        schedule();
    }

    function step(direction) {
        if (quotes.length < 2) return;
        index = (index + direction + quotes.length) % quotes.length;
        render(true);
    }
    previousButton.onclick = () => step(-1);
    nextButton.onclick = () => step(1);
    form.addEventListener('change', schedule);
    reducedMotion.addEventListener('change', schedule);
    canvas.addEventListener('pointerdown', event => {
        canvas.setPointerCapture(event.pointerId);
        pointerHeld = true;
        schedule();
    });
    for (const event of ['pointerup', 'pointercancel', 'lostpointercapture']) {
        canvas.addEventListener(event, () => {
            pointerHeld = false;
            schedule();
        });
    }
    render(false);
}

// DOM Ready initialization
document.addEventListener('DOMContentLoaded', function() {
    setupQuotePlayback();

    // 1. Initialize Add Form
    initStudioState('add', {
        fontFamily: 'Kantumruy Pro',
        fontSize: 14,
        fontWeight: '800',
        color: '#FFF7ED',
        rotation: 0,
        posX: 65,
        posY: 30,
    });
    setupDraggableQuote('add');
    applyStyle('add');
    updateLiveText('add');
    updateLiveAuthor('add');
    startLiveClock();

    // 2. Initialize Edit Modals
    @foreach($quotes as $quote)
    (function() {
        const idx = {{ $loop->index }};
        const scope = `edit_${idx}`;
        const inputJson = document.getElementById(`style_json_${scope}`);
        let parsed = null;
        if (inputJson && inputJson.value) {
            try { parsed = JSON.parse(inputJson.value); } catch(_) {}
        }
        initStudioState(scope, parsed || {});
        setupDraggableQuote(scope);
        applyStyle(scope);

        // Re-apply when modal is shown to calculate correct geometry
        const modalEl = document.getElementById(`quote-edit-${idx}`);
        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', function() {
                applyStyle(scope);
                updateLiveText(scope);
                updateLiveAuthor(scope);
            });
        }
    })();
    @endforeach

    if (window.feather) {
        feather.replace();
    }
});
</script>
@endsection
