@php
    $previewUrl = route('admin.certificates.templates.preview', ['id' => $tmpl->id, 'embed' => 1, 'plain' => 1]);
    $styling = method_exists($tmpl, 'toStudioPayload') ? ($tmpl->toStudioPayload()['styling'] ?? []) : [];
    $accent = $styling['accentColor'] ?? '#D4A32C';
    $bgColor = $styling['bgColor'] ?? '#FFFDF8';
    $title = $tmpl->template_code ?? ($tmpl->title ?? $tmpl->id);
@endphp

<!-- Live certificate preview (uses the same A4 canvas renderer as full preview / print for Studio-aligned display) -->
<div class="template-canvas-thumb position-relative overflow-hidden rounded-top"
     style="width: 100%; height: 235px; background: {{ $bgColor }}; border: 5px solid {{ $accent }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.06); cursor: pointer;"
     onclick="openFullPreviewModal({{ $tmpl->id }})">
    <iframe
        src="{{ $previewUrl }}"
        title="Layout model preview {{ $title }}"
        loading="lazy"
        scrolling="no"
        tabindex="-1"
        aria-hidden="true"
        style="position:absolute; inset:0; width:100%; height:100%; border:0; pointer-events:none; background: {{ $bgColor }};"></iframe>

    <!-- Hover Overlay: Click to View Full A4 -->
    <div class="template-thumb-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
         style="background: rgba(9, 38, 83, 0.45); opacity: 0; transition: opacity 0.2s ease-in-out; z-index: 20; pointer-events: none;">
        <span class="badge bg-warning text-dark px-3 py-2 fw-bold shadow-lg d-flex align-items-center gap-1" style="font-size: 11px; border: 1.5px solid #fff; border-radius: 20px;">
            🔍 {{ __('index.view_full_a4') }}
        </span>
    </div>
</div>
