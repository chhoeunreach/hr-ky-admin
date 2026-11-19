@php
    $layout = $certificate['layoutTemplate'] ?? 'v1-executive-gold';
    $isImperialSash = in_array($layout, ['executive-imperial-sash', 'v6-sash']);
    $isMedalRibbon = in_array($layout, ['executive-medal-ribbon', 'v1-executive-gold', 'v5-medal']);
    $isNavyRibbon = in_array($layout, ['executive-navy-ribbon', 'v3-ribbon']);
@endphp
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $certificate['khmerTitle'] ?? $certificate['certificateTitle'] ?? 'Certificate of Appreciation' }} — {{ $certificate['recipientName'] ?? 'Recipient' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Battambang:wght@400;700&family=Cinzel:wght@500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Great+Vibes&family=Kantumruy+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Moul&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
        .font-cormorant { font-family: 'Cormorant Garamond', serif; }
        .font-script { font-family: 'Alex Brush', 'Great Vibes', cursive; }

        @page {
            size: A4 landscape;
            margin: 0mm;
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #FFFDF8 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-a4-canvas {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                border-width: 8px !important;
                border-color: #C89B3C !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-900 flex flex-col items-center justify-center min-h-screen p-4 sm:p-6 text-slate-900">

    <!-- Top Action Toolbar (Hidden during print) -->
    <div class="no-print mb-6 max-w-6xl w-full flex flex-wrap items-center justify-between gap-3 bg-slate-800/95 backdrop-blur-md px-6 py-3.5 rounded-2xl border border-slate-700 shadow-xl">
        
        <!-- Left: Layout Models Selector -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <span class="text-xs font-bold text-amber-400 font-kantumruy flex items-center gap-1.5 bg-slate-900/80 px-2.5 py-1.5 rounded-lg border border-amber-500/30">
                <span class="text-amber-400 text-sm">📐</span>
                <span>វិញ្ញាបនបត្រ គំរូ / Layout Model:</span>
            </span>

            <form method="GET" action="" class="inline-flex items-center gap-1.5">
                @if(request()->has('print'))
                    <input type="hidden" name="print" value="{{ request('print') }}">
                @endif
                <select name="template_id" onchange="this.form.submit()" class="text-xs font-semibold font-kantumruy bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-slate-200 focus:ring-2 focus:ring-amber-500 cursor-pointer shadow-sm">
                    @foreach($templates ?? [] as $t)
                        <option value="{{ $t->id }}" {{ (isset($activeTemplate) && $activeTemplate->id == $t->id) || (($certificate['template_id'] ?? null) == $t->id) || (($certificate['layoutTemplate'] ?? '') == $t->layout_template) ? 'selected' : '' }}>
                            {{ $t->template_code }} · {{ $t->title }} ({{ $t->khmer_title }})
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('certificates.studio', ['id' => $certificate['credentialId'] ?? $certificate['id'] ?? '', 'template_id' => $activeTemplate->id ?? ($certificate['template_id'] ?? '')]) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                <span>✏️</span>
                <span>Studio</span>
            </a>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="window.print()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 text-xs cursor-pointer hover:scale-105 active:scale-95">
                <span>🖨️</span>
                <span>Print A4</span>
            </button>

            <a href="{{ route('certificates.downloadPdf', array_merge(['id' => $certificate['credentialId'] ?? $certificate['id'] ?? ''], isset($activeTemplate) ? ['template_id' => $activeTemplate->id] : [])) }}" class="px-3.5 py-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl shadow transition-all flex items-center gap-1.5 text-xs">
                <span>⬇</span>
                <span>Direct PDF</span>
            </a>

            <a href="{{ route('certificates.verify', ['id' => $certificate['credentialId'] ?? $certificate['id'] ?? '']) }}" target="_blank" class="px-3.5 py-2 bg-blue-600/80 hover:bg-blue-600 text-white font-semibold rounded-xl shadow transition-all flex items-center gap-1.5 text-xs">
                <span>🛡️</span>
                <span>Verify</span>
            </a>

            <a href="{{ route('certificates.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl border border-slate-600 transition-all flex items-center gap-1.5 text-xs">
                <span>←</span>
                <span>Registry</span>
            </a>
        </div>
    </div>

    <!-- Certificate Canvas (Exact True A4 Landscape: 1123px x 794px at 96 DPI) -->
    <div class="print-a4-canvas relative w-[1123px] h-[794px] bg-[#FFFDF8] border-8 border-[#C89B3C] shadow-2xl overflow-hidden flex flex-col justify-between px-12 py-8 text-center text-[#2D1328]"
         style="background-image: radial-gradient(ellipse at 50% 35%, #FFFFFF 0%, #FFFDF8 65%, #FBF6EB 100%);">

        <!-- Outer Hairline & Inner Baroque Border Lines -->
        <div class="absolute inset-2.5 border-2 border-[#2D1328] pointer-events-none opacity-40"></div>
        <div class="absolute inset-4.5 border border-[#C89B3C] pointer-events-none"></div>

        <!-- Corner Ornate Brackets -->
        <div class="absolute top-5 left-5 w-12 h-12 border-t-2 border-l-2 border-[#C89B3C] pointer-events-none"></div>
        <div class="absolute top-5 right-5 w-12 h-12 border-t-2 border-r-2 border-[#C89B3C] pointer-events-none"></div>
        <div class="absolute bottom-5 left-5 w-12 h-12 border-b-2 border-l-2 border-[#C89B3C] pointer-events-none"></div>
        <div class="absolute bottom-5 right-5 w-12 h-12 border-b-2 border-r-2 border-[#C89B3C] pointer-events-none"></div>

        @if($isImperialSash)
        <!-- Top-Left SOLID METALLIC GOLD Vertical Hanging Sash (Imperial Sash) -->
        <div class="absolute top-0 left-10 z-20 pointer-events-none drop-shadow-xl">
            <svg width="150" height="276" viewBox="0 0 160 296" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="tmplV6GoldBand" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#B88218" />
                        <stop offset="22%" stop-color="#F5D675" />
                        <stop offset="50%" stop-color="#FFF2B8" />
                        <stop offset="78%" stop-color="#E6B846" />
                        <stop offset="100%" stop-color="#A67214" />
                    </linearGradient>
                    <linearGradient id="tmplV6NavyTail" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0E346E" />
                        <stop offset="50%" stop-color="#092653" />
                        <stop offset="100%" stop-color="#041029" />
                    </linearGradient>
                    <radialGradient id="tmplV6Rosette" cx="38%" cy="32%" r="68%">
                        <stop offset="0%" stop-color="#FFFCE6" />
                        <stop offset="28%" stop-color="#F8DE82" />
                        <stop offset="62%" stop-color="#D4A32C" />
                        <stop offset="88%" stop-color="#A37116" />
                        <stop offset="100%" stop-color="#6E490A" />
                    </radialGradient>
                    <radialGradient id="tmplV6NavyDisc" cx="40%" cy="35%" r="68%">
                        <stop offset="0%" stop-color="#123C7D" />
                        <stop offset="58%" stop-color="#092653" />
                        <stop offset="100%" stop-color="#041129" />
                    </radialGradient>
                    <linearGradient id="tmplV6RailGold" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#FBF0B9" />
                        <stop offset="50%" stop-color="#D4A32C" />
                        <stop offset="100%" stop-color="#8F610E" />
                    </linearGradient>
                </defs>
                <rect x="46" y="0" width="68" height="140" fill="url(#tmplV6GoldBand)" />
                <rect x="49" y="0" width="2" height="140" fill="#FFF7D1" opacity="0.7" />
                <rect x="109" y="0" width="2" height="140" fill="#8C5D0E" opacity="0.45" />
                <polygon points="46,180 72,184 48,272 34,258 18,268" fill="url(#tmplV6NavyTail)" stroke="#D4A32C" stroke-width="1.8" />
                <polygon points="88,184 114,180 142,268 126,258 112,272" fill="url(#tmplV6NavyTail)" stroke="#D4A32C" stroke-width="1.8" />
                <polygon points="62,182 98,182 100,278 80,294 60,278" fill="url(#tmplV6GoldBand)" stroke="#8F6110" stroke-width="1.5" />
                <g transform="translate(80, 142)">
                    <circle r="56" fill="url(#tmplV6Rosette)" stroke="#7A500A" stroke-width="1.8" />
                    <circle r="47" fill="none" stroke="#FFF7CC" stroke-width="1.2" opacity="0.85" />
                    <circle r="42" fill="url(#tmplV6NavyDisc)" stroke="url(#tmplV6RailGold)" stroke-width="2.2" />
                    <text x="0" y="8" text-anchor="middle" fill="url(#tmplV6RailGold)" font-size="28" font-weight="bold" font-family="'Cinzel', serif">K</text>
                    <text x="0" y="24" text-anchor="middle" fill="#F8DE82" font-size="6.5" font-weight="bold" font-family="'Cinzel', serif" letter-spacing="1">KNEA YOENG</text>
                </g>
            </svg>
        </div>
        @elseif($isMedalRibbon)
        <!-- Top-Left Navy & Gold Hanging Ribbon + Scalloped Medal Rosette (Medal Ribbon) -->
        <div class="absolute top-0 left-10 z-20 pointer-events-none drop-shadow-xl">
            <svg width="156" height="344" viewBox="0 0 160 352" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="tmplV5RibbonNavy" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#051636" />
                        <stop offset="28%" stop-color="#0E346E" />
                        <stop offset="52%" stop-color="#092653" />
                        <stop offset="78%" stop-color="#0E346E" />
                        <stop offset="100%" stop-color="#041029" />
                    </linearGradient>
                    <linearGradient id="tmplV5GoldRail" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#C89328" />
                        <stop offset="48%" stop-color="#FDE89D" />
                        <stop offset="100%" stop-color="#A87414" />
                    </linearGradient>
                    <radialGradient id="tmplV5Rosette" cx="38%" cy="32%" r="68%">
                        <stop offset="0%" stop-color="#FFFCE6" />
                        <stop offset="28%" stop-color="#F8DE82" />
                        <stop offset="62%" stop-color="#D4A32C" />
                        <stop offset="88%" stop-color="#A37116" />
                        <stop offset="100%" stop-color="#6E490A" />
                    </radialGradient>
                    <radialGradient id="tmplV5NavyMedallion" cx="40%" cy="35%" r="68%">
                        <stop offset="0%" stop-color="#123C7D" />
                        <stop offset="58%" stop-color="#092653" />
                        <stop offset="100%" stop-color="#041129" />
                    </radialGradient>
                </defs>
                <rect x="44" y="0" width="72" height="175" fill="url(#tmplV5RibbonNavy)" />
                <rect x="44" y="0" width="3.5" height="175" fill="url(#tmplV5GoldRail)" />
                <rect x="112.5" y="0" width="3.5" height="175" fill="url(#tmplV5GoldRail)" />
                <polygon points="48,228 74,234 52,324 38,308 22,318" fill="url(#tmplV5RibbonNavy)" stroke="url(#tmplV5GoldRail)" stroke-width="2" />
                <polygon points="86,234 112,228 138,318 122,308 108,324" fill="url(#tmplV5RibbonNavy)" stroke="url(#tmplV5GoldRail)" stroke-width="2" />
                <polygon points="64,230 96,230 98,322 80,340 62,322" fill="url(#tmplV5Rosette)" stroke="#8F6110" stroke-width="1.5" />
                <g transform="translate(80, 178)">
                    <circle r="56" fill="url(#tmplV5Rosette)" stroke="#8A5D0F" stroke-width="1.4" />
                    <circle r="47" fill="none" stroke="#FFF4B8" stroke-width="1.4" opacity="0.85" />
                    <circle r="42" fill="url(#tmplV5NavyMedallion)" stroke="url(#tmplV5GoldRail)" stroke-width="2.4" />
                    <text x="0" y="8" text-anchor="middle" fill="url(#tmplV5GoldRail)" font-size="28" font-weight="bold" font-family="'Cinzel', serif">K</text>
                    <text x="0" y="24" text-anchor="middle" fill="#F4D16B" font-size="6.5" font-weight="bold" font-family="'Cinzel', serif" letter-spacing="1">EXECUTIVE</text>
                </g>
            </svg>
        </div>
        @endif

        <!-- Optional Recipient Photo Badge (Top Right Corner) -->
        @if(!empty($certificate['recipientPhotoUrl']))
            <div class="absolute top-8 right-12 z-20 flex flex-col items-center">
                <div class="w-24 h-28 rounded-xl overflow-hidden border-2 border-[#C89B3C] bg-white shadow-md p-0.5">
                    <img src="{{ $certificate['recipientPhotoUrl'] }}" alt="Photo" class="w-full h-full object-cover rounded-lg" />
                </div>
                @if(!empty($certificate['digitalHr']['employeeId']))
                    <div class="mt-1 px-2.5 py-0.5 rounded-full bg-[#2D1328] text-[#C89B3C] border border-[#C89B3C] text-[9px] font-mono font-bold shadow">
                        ID: {{ $certificate['digitalHr']['employeeId'] }}
                    </div>
                @endif
            </div>
        @endif

        <!-- TOP ZONE: Insignia & Authority Preamble -->
        <div class="flex flex-col items-center pt-1 relative z-10">
            <!-- Insignia Logo -->
            <div class="w-16 h-16 rounded-full border-2 border-[#C89B3C] overflow-hidden shadow-md mb-1.5 bg-white flex items-center justify-center p-0.5">
                <img src="{{ $certificate['issuer']['logoUrl'] ?? asset('assets/aistudio/unnamed_521b71dc.jpg') }}"
                     alt="Insignia Logo"
                     class="w-full h-full object-cover rounded-full"
                     onerror="this.src='{{ asset('assets/aistudio/unnamed_521b71dc.jpg') }}'" />
            </div>

            <h4 class="font-moul text-sm text-[#2D1328] tracking-wide">
                {{ $certificate['issuer']['khmerName'] ?? 'ស្ថាប័ន គ្នា​យើង (KNEA YOENG)' }}
            </h4>
            <p class="font-kantumruy text-xs text-[#C89B3C] font-semibold tracking-wider mt-0.5">
                {{ $certificate['issuer']['department'] ?? 'គណៈកម្មការអភិវឌ្ឍន៍ភាពជាអ្នកដឹកនាំ និងធនធានមនុស្ស' }}
            </p>

            <!-- Baroque Fleur Divider Ornament -->
            <div class="my-1.5 flex items-center justify-center pointer-events-none">
                <svg width="240" height="22" viewBox="0 0 240 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="10" y1="11" x2="88" y2="11" stroke="#D4A32C" stroke-width="1" stroke-dasharray="2 2" />
                    <path d="M 120 2 C 122 7, 126 10, 132 11 C 126 12, 122 15, 120 20 C 118 15, 114 12, 108 11 C 114 10, 118 7, 120 2 Z" fill="#D4A32C" />
                    <circle cx="102" cy="11" r="2" fill="#D4A32C" />
                    <circle cx="138" cy="11" r="2" fill="#D4A32C" />
                    <line x1="152" y1="11" x2="230" y2="11" stroke="#D4A32C" stroke-width="1" stroke-dasharray="2 2" />
                </svg>
            </div>

            <div class="mt-2 space-y-0.5">
                <p class="font-moul text-xs text-amber-900 tracking-wider">
                    {{ $certificate['khmerPreamble'] ?? 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ' }}
                </p>
                <p class="font-cinzel text-[9px] tracking-widest text-[#C89B3C] font-bold uppercase">
                    {{ $certificate['authorityPreamble'] ?? 'KINGDOM OF CAMBODIA · NATION RELIGION KING' }}
                </p>
            </div>
        </div>

        <!-- TITLE ZONE -->
        <div class="space-y-1 relative z-10 my-1">
            @if(!empty($certificate['khmerTitle']))
                <h1 class="font-moul text-2xl text-amber-950 leading-snug">
                    {{ $certificate['khmerTitle'] }}
                </h1>
            @endif
            <h2 class="font-cinzel text-xl font-bold tracking-wider text-[#2D1328] uppercase">
                {{ $certificate['certificateTitle'] ?? 'CERTIFICATE OF APPRECIATION' }}
            </h2>
            <p class="font-kantumruy text-xs text-slate-700 max-w-2xl mx-auto">
                {{ $certificate['khmerSubtitle'] ?? ($certificate['subtitle'] ?? 'វិញ្ញាបនបត្រនេះត្រូវបានប្រគល់ជូន') }}
            </p>
        </div>

        <!-- RECIPIENT ZONE -->
        <div class="my-1 relative z-10">
            @if(!empty($certificate['khmerRecipientTitle']))
                <p class="font-kantumruy text-xs font-bold text-[#C89B3C] uppercase tracking-wider mb-1">
                    {{ $certificate['khmerRecipientTitle'] }}
                </p>
            @endif

            <div class="py-1">
                @if(!empty($certificate['khmerRecipientName']))
                    <h2 class="font-moul text-3xl text-[#2D1328] leading-tight">
                        {{ $certificate['khmerRecipientName'] }}
                    </h2>
                @endif
                <h3 class="font-cinzel text-2xl font-bold tracking-wide text-[#2D1328] mt-1">
                    {{ $certificate['recipientName'] ?? 'Reach Chhoeun' }}
                </h3>
            </div>

            <div class="w-80 h-[1.5px] bg-gradient-to-r from-transparent via-[#C89B3C] to-transparent mx-auto mt-1"></div>
        </div>

        <!-- KNEAYERNG HRMS & CPD ACCREDITATION PLAQUE -->
        <div class="mx-auto px-6 py-2 border border-[#C89B3C]/70 bg-amber-50/80 rounded-lg flex items-center justify-between gap-8 max-w-xl shadow-sm relative z-10">
            <div class="text-left">
                <div class="font-cinzel text-[8.5px] uppercase tracking-widest text-[#C89B3C] font-extrabold">CPD & KNEAYERNG HRMS ACCREDITED</div>
                <div class="font-cinzel font-bold text-xs text-amber-950">
                    <span>{{ $certificate['digitalHr']['trainingHours'] ?? 60 }}</span>.0 ម៉ោងបណ្តុះបណ្តាលឌីជីថល / KNEAYERNG HRMS HOURS
                </div>
            </div>
            <div class="text-right text-[9.5px] font-mono text-slate-700">
                REG: <span class="font-bold text-slate-900">{{ $certificate['digitalHr']['employeeId'] ?? 'EMP-KY-2026-08' }}</span><br>
                <span class="text-amber-900 font-kantumruy font-bold">{{ $certificate['digitalHr']['complianceStatus'] ?? 'Distinction / Honor Roll' }}</span>
            </div>
        </div>

        <!-- CITATION & ACHIEVEMENT -->
        <div class="max-w-3xl mx-auto my-1 space-y-1 relative z-10">
            @if(!empty($certificate['khmerCourseOrAchievementTitle']))
                <h3 class="font-moul text-sm text-[#2D1328]">
                    {{ $certificate['khmerCourseOrAchievementTitle'] }}
                </h3>
            @endif
            @if(!empty($certificate['khmerCitation']))
                <p class="font-kantumruy text-xs text-slate-700 italic px-6 leading-relaxed">
                    « {{ $certificate['khmerCitation'] }} »
                </p>
            @endif
        </div>

        <!-- FOOTER: QR + Dual Signatories + Official Seal -->
        <div class="flex items-end justify-between pt-3 border-t border-[#C89B3C]/40 mt-1 px-4 relative z-10">
            
            <!-- Left: Verification QR & Credential Metadata -->
            <div class="flex items-center gap-3 text-left text-[10px] font-kantumruy text-slate-600">
                <div class="w-16 h-16 p-1 bg-white border border-[#C89B3C]/50 rounded shadow-sm flex items-center justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('certificates.verify', ['id' => $certificate['credentialId'] ?? 'KY-LDR-2026-001'])) }}"
                         alt="Verification QR"
                         class="w-full h-full object-contain" />
                </div>
                <div>
                    <div><strong>កាលបរិច្ឆេទ / Date:</strong> <span>{{ $certificate['khmerIssueDate'] ?? $certificate['issueDate'] ?? '15 November 2026' }}</span></div>
                    <div class="font-mono font-bold text-slate-900">ID: {{ $certificate['credentialId'] ?? 'KY-LDR-2026-001' }}</div>
                    <div class="text-[9px] text-[#C89B3C] font-semibold">ស្កេនដើម្បីផ្ទៀងផ្ទាត់ / SCAN TO VERIFY</div>
                </div>
            </div>

            <!-- Center: Dual Signatories -->
            <div class="flex gap-14 pb-1">
                @php
                    $sig1 = $certificate['signatories'][0] ?? [
                        'name' => 'Chhoeun Reach',
                        'khmerName' => 'ឈឿន រាជ',
                        'role' => 'Managing Director / IT Manager',
                        'khmerRole' => 'ស្ថាបនិក និងនាយកប្រតិបត្តិ'
                    ];
                    $sig2 = $certificate['signatories'][1] ?? [
                        'name' => 'PET POEUN',
                        'khmerName' => 'ម៉េង សោភា',
                        'role' => 'Chief People Officer',
                        'khmerRole' => 'ប្រធានផ្នែកធនធានមនុស្ស'
                    ];
                @endphp

                <div class="text-center">
                    <div class="w-40 border-b-2 border-[#2D1328] pb-1 mb-1">
                        <span class="font-cormorant italic text-lg font-bold text-[#2D1328] tracking-wider">{{ $sig1['name'] ?? '' }}</span>
                    </div>
                    <div class="font-kantumruy font-bold text-xs text-slate-900">{{ $sig1['khmerName'] ?? '' }}</div>
                    <div class="font-kantumruy text-[9.5px] text-slate-600">{{ $sig1['khmerRole'] ?? ($sig1['role'] ?? '') }}</div>
                </div>

                <div class="text-center">
                    <div class="w-40 border-b-2 border-[#2D1328] pb-1 mb-1">
                        <span class="font-cormorant italic text-lg font-bold text-[#2D1328] tracking-wider">{{ $sig2['name'] ?? '' }}</span>
                    </div>
                    <div class="font-kantumruy font-bold text-xs text-slate-900">{{ $sig2['khmerName'] ?? '' }}</div>
                    <div class="font-kantumruy text-[9.5px] text-slate-600">{{ $sig2['khmerRole'] ?? ($sig2['role'] ?? '') }}</div>
                </div>
            </div>

            <!-- Right: Official Organization Seal -->
            <div class="text-right text-[10px] font-cinzel text-[#C89B3C] font-bold">
                <div class="w-14 h-14 rounded-full border-2 border-dashed border-[#C89B3C] flex items-center justify-center mx-auto mb-1 text-[8px] leading-tight text-center text-amber-900 uppercase font-mono font-bold bg-amber-50 shadow-sm">
                    គ្នា​យើង<br>SEAL
                </div>
                <div>គ្នា​យើង OFFICIAL SEAL</div>
                <div class="text-[8px] text-slate-500 font-mono">SECURE HASH VERIFIED</div>
            </div>

        </div>

    </div>

    @if(!empty($autoPrint))
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 700);
        });
    </script>
    @endif
</body>
</html>
