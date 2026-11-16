@php
    $isPreview = $isPreview ?? false;
    $autoPrint = $autoPrint ?? false;
    $plainPreview = $plainPreview ?? ($isPreview && (request()->boolean('plain') || request()->boolean('plain_preview')));
    $credentialId = $certificate['credentialId'] ?? $certificate['id'];
    $currentTmplId = $activeTemplate->id ?? ($certificate['template_id'] ?? null);
@endphp
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $certificate['khmerTitle'] ?? $certificate['certificateTitle'] }} - {{ $certificate['recipientName'] }}</title>

    @include('admin.certificateManagement.partials.certificate-head')

    @if($isPreview)
    <style>
        html, body { background: {{ $plainPreview ? '#ffffff' : '#0f172a' }} !important; margin: 0 !important; }
        body { padding: 0 !important; overflow: hidden; }
        .preview-scale-wrap {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }
        /* Visually center the A4 canvas without affecting layout size */
        .preview-scale-wrap .certificate-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform-origin: center center;
            will-change: transform;
        }

        /* Print the embedded A4 view at full size (no scaling / no dark backdrop) */
        @media print {
            html, body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .preview-scale-wrap {
                position: static !important;
                width: 1080px !important;
                height: 760px !important;
                overflow: visible !important;
            }
            .preview-scale-wrap .certificate-container {
                position: static !important;
                transform: none !important;
                box-shadow: none !important;
                margin: 0 !important;
            }
        }
    </style>
    @endif
</head>
<body class="{{ $isPreview ? 'bg-slate-900 min-h-screen overflow-hidden' : 'bg-slate-200 flex flex-col items-center justify-center min-h-screen p-4 sm:p-8' }}">

    @if(!$isPreview)
    <!-- Print / Export & Layout Models Toolbar -->
    <div class="no-print mb-4 max-w-6xl w-full flex flex-wrap items-center justify-between gap-3 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl border border-slate-200">
        
        <!-- Left: Layout Models (វិញ្ញាបនបត្រ គំរូ) Selector -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800 font-kantumruy flex items-center gap-1.5 bg-amber-50 px-2.5 py-1.5 rounded-lg border border-amber-300">
                    <span class="text-amber-600 text-sm">📐</span>
                    <span>វិញ្ញាបនបត្រ គំរូ / Layout Model:</span>
                </span>
                
                @if(!empty($isTemplatePreview))
                    <select id="template_id_select" onchange="window.location.href='{{ url('admin/certificates/templates') }}/' + this.value + '/preview'" class="text-xs font-semibold font-kantumruy bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-slate-800 focus:ring-2 focus:ring-amber-500 cursor-pointer shadow-sm">
                        @foreach($templates ?? [] as $t)
                            <option value="{{ $t->id }}" {{ (isset($activeTemplate) && $activeTemplate->id == $t->id) ? 'selected' : '' }}>
                                [{{ $t->version_tag }}] {{ $t->template_code }} · {{ $t->khmer_title ?: $t->title }} ({{ $t->title }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <form id="layoutModelForm" method="GET" action="{{ route('admin.certificates.show', $credentialId) }}" class="inline-flex items-center gap-1.5">
                        @if(request()->has('print'))
                            <input type="hidden" name="print" value="{{ request('print') }}">
                        @endif
                        <select name="template_id" id="template_id_select" onchange="this.form.submit()" class="text-xs font-semibold font-kantumruy bg-slate-50 border border-slate-300 rounded-lg px-3 py-1.5 text-slate-800 focus:ring-2 focus:ring-amber-500 cursor-pointer shadow-sm">
                            @foreach($templates ?? [] as $t)
                                <option value="{{ $t->id }}" {{ (isset($activeTemplate) && $activeTemplate->id == $t->id) || ($currentTmplId == $t->id) || (($certificate['layoutTemplate'] ?? '') == $t->layout_template) ? 'selected' : '' }}>
                                    {{ $t->template_code }} · {{ $t->title }} ({{ $t->khmer_title }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            @if(!empty($isTemplatePreview))
                <a href="{{ route('admin.certificates.create', ['template_id' => $activeTemplate->id]) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    ➕ <span>{{ __('index.issue_certificate') }}</span>
                </a>
                <a href="{{ route('admin.certificates.batch', ['template_id' => $activeTemplate->id]) }}" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    👥 <span>{{ __('index.batch_issue_certificate') }}</span>
                </a>
                <a href="{{ route('admin.certificates.studio', ['template_id' => $activeTemplate->id]) }}" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-900 text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    ✏️ <span>Studio 🎨</span>
                </a>
            @else
                <!-- Save as default model for this certificate button -->
                @if(isset($activeTemplate))
                <form method="POST" action="{{ route('admin.certificates.applyTemplate', $credentialId) }}" class="inline">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $activeTemplate->id }}">
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-amber-100 hover:text-amber-800 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition flex items-center gap-1 cursor-pointer" title="Save this Layout Model as the permanent setting for this certificate">
                        💾 <span>រក្សាទុកគំរូ / Save Model</span>
                    </button>
                </form>
                @endif

                <!-- Edit Layout Model in CertiCraft Studio -->
                <a href="{{ route('certificates.studio', ['id' => $credentialId, 'template_id' => $activeTemplate->id ?? ($currentTmplId ?? '')]) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    ✏️ <span>កែសម្រួលក្នុង Studio / Layout Studio</span>
                </a>
            @endif
        </div>

        <!-- Right: Print / Vector PDF / Verify Actions -->
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="window.print()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md cursor-pointer transition flex items-center gap-1.5 text-xs">
                🖨️ <span>បោះពុម្ព A4 / Print</span>
            </button>
            @if(!empty($isTemplatePreview))
                <a href="{{ route('admin.certificates.templates') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition flex items-center gap-1.5 text-xs">
                    ← <span>ត្រឡប់ទៅ វិញ្ញាបនបត្រ គំរូ</span>
                </a>
            @else
                <a href="{{ route('admin.certificates.downloadPdf', array_merge(['id' => $credentialId], isset($activeTemplate) ? ['template_id' => $activeTemplate->id] : [])) }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5 text-xs">
                    📄 <span>PDF</span>
                </a>
                <a href="{{ route('certificates.verify', $credentialId) }}" target="_blank" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-xl border border-slate-300 transition flex items-center gap-1.5 text-xs">
                    🔍 <span>Verify</span>
                </a>
                <a href="{{ route('admin.certificates.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition flex items-center gap-1 text-xs">
                    ← <span>បញ្ជី / Directory</span>
                </a>
            @endif
        </div>
    </div>

    @include('admin.certificateManagement.partials.print-customizer', ['mode' => 'single'])
    @endif

    @if($isPreview)
        <div class="preview-scale-wrap" id="previewScaler">
            @include('admin.certificateManagement.partials.certificate-canvas', ['certificate' => $certificate])
        </div>
        <script>
            (function () {
                var wrap = document.getElementById('previewScaler');
                var canvas = wrap ? wrap.querySelector('.certificate-container') : null;
                if (!wrap || !canvas) return;

                function fit() {
                    var padX = 16;
                    var padY = 16;
                    var availW = Math.max(100, window.innerWidth - padX);
                    var availH = Math.max(100, window.innerHeight - padY);
                    var scaleX = availW / 1080;
                    var scaleY = availH / 760;
                    var scale = Math.min(scaleX, scaleY);
                    if (scale <= 0) scale = 0.1;
                    canvas.style.transform = 'translate(-50%, -50%) scale(' + scale + ')';
                }

                window.addEventListener('resize', fit);
                window.addEventListener('load', fit);
                fit();
            })();
        </script>
    @else
        @include('admin.certificateManagement.partials.certificate-canvas', ['certificate' => $certificate])
    @endif

    @if($autoPrint)
    <script>
        window.addEventListener('load', function () {
            // Let webfonts and the QR image settle before opening the print dialog.
            setTimeout(function () { window.print(); }, 700);
        });
    </script>
    @endif
</body>
</html>
