{{--
    Background watermark / guilloche security pattern layer.
    Faithful Blade port of src/components/CertificatePatterns.tsx (WatermarkPatternLayer).
    Place inside .certificate-container (absolute inset-0, z-0).
--}}
@php
    $type = $type ?? 'laurel';
    $safeOpacity = $opacity ?? 0.14;
    $primaryColor = $primaryColor ?? '#092653';
    $accentColor = $accentColor ?? '#D4A32C';
    $issuerName = $issuerName ?? 'KNEAYERNG';
    $issuerDepartment = $issuerDepartment ?? 'PHONE SHOP';
    $showLaurelSides = $showLaurelSides ?? true;
    $diagPatternId = 'diagBrand_' . preg_replace('/[^A-Za-z0-9_]/', '', (string) ($uid ?? uniqid()));
@endphp

@if($type === 'wave-mesh')
    @php
        $strokeCol = $primaryColor ?: '#1D4ED8';
        $wmOpacity = min(0.45, max(0.08, $safeOpacity * 1.4));
    @endphp
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden" style="opacity: {{ $wmOpacity }};">
        <svg width="620" height="340" viewBox="0 0 620 340" fill="none" class="absolute -top-6 -left-6">
            @for($i = 0; $i < 18; $i++)
                <path d="M -20 {{ 260 - $i * 11 }} C {{ 140 + $i * 12 }} {{ 190 - $i * 8 }}, {{ 310 + $i * 16 }} {{ 230 - $i * 13 }}, 610 {{ 18 + $i * 5 }}"
                      stroke="{{ $strokeCol }}" stroke-width="0.9" stroke-opacity="{{ 0.38 + ($i % 3) * 0.12 }}" />
            @endfor
        </svg>
        <svg width="620" height="340" viewBox="0 0 620 340" fill="none" class="absolute -bottom-6 -right-6 transform rotate-180">
            @for($i = 0; $i < 18; $i++)
                <path d="M -20 {{ 260 - $i * 11 }} C {{ 140 + $i * 12 }} {{ 190 - $i * 8 }}, {{ 310 + $i * 16 }} {{ 230 - $i * 13 }}, 610 {{ 18 + $i * 5 }}"
                      stroke="{{ $strokeCol }}" stroke-width="0.9" stroke-opacity="{{ 0.38 + ($i % 3) * 0.12 }}" />
            @endfor
        </svg>
    </div>

@elseif($type === 'guilloche-rosette')
    @php
        $strokeCol = $primaryColor ?: '#1D4ED8';
        $wmOpacity = min(0.35, max(0.06, $safeOpacity * 1.15));
    @endphp
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden" style="opacity: {{ $wmOpacity }};">
        <svg width="560" height="560" viewBox="0 0 300 300" fill="none" stroke="{{ $strokeCol }}">
            <circle cx="150" cy="150" r="142" stroke-width="0.6" stroke-dasharray="3 2" />
            <circle cx="150" cy="150" r="134" stroke-width="0.8" />
            @for($i = 0; $i < 24; $i++)
                <ellipse cx="150" cy="150" rx="132" ry="46" stroke-width="0.65" stroke-opacity="0.6"
                         transform="rotate({{ $i * 7.5 }}, 150, 150)" />
            @endfor
            @for($i = 0; $i < 18; $i++)
                <ellipse cx="150" cy="150" rx="105" ry="58" stroke-width="0.6" stroke-opacity="0.7"
                         transform="rotate({{ $i * 10 }}, 150, 150)" />
            @endfor
            <circle cx="150" cy="150" r="68" fill="#FCFBF7" fill-opacity="0.6" stroke="{{ $strokeCol }}" stroke-width="1.2" />
            <circle cx="150" cy="150" r="62" stroke="{{ $accentColor }}" stroke-width="0.9" />
        </svg>
    </div>

@elseif($type === 'rosette-lace')
    @php
        $strokeCol = $primaryColor ?: '#1D4ED8';
        $wmOpacity = min(0.32, max(0.06, $safeOpacity * 1.1));
    @endphp
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden" style="opacity: {{ $wmOpacity }};">
        <svg width="620" height="620" viewBox="0 0 320 320" fill="none" stroke="{{ $strokeCol }}">
            @for($i = 0; $i < 16; $i++)
                <ellipse cx="160" cy="160" rx="136" ry="52" stroke-width="0.75" stroke-opacity="0.65"
                         transform="rotate({{ $i * 11.25 }}, 160, 160)" />
            @endfor
            @for($i = 0; $i < 8; $i++)
                <rect x="68" y="68" width="184" height="184" rx="42" stroke-width="0.7" stroke-opacity="0.55"
                      transform="rotate({{ $i * 22.5 }}, 160, 160)" />
            @endfor
            <circle cx="160" cy="160" r="56" stroke-width="1" stroke-dasharray="3 2" />
        </svg>
    </div>

@elseif($type === 'diagonal-brand')
    @php
        $strokeCol = $primaryColor ?: '#092653';
        $wmOpacity = min(0.24, max(0.05, $safeOpacity * 0.9));
    @endphp
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden" style="opacity: {{ $wmOpacity }};">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="{{ $diagPatternId }}" width="210" height="130" patternUnits="userSpaceOnUse" patternTransform="rotate(-24)">
                    <polygon points="92,24 100,24 100,48 92,48" fill="{{ $strokeCol }}" />
                    <polygon points="100,35 112,24 120,24 106,36 121,48 113,48" fill="{{ $accentColor }}" />
                    <text x="105" y="68" text-anchor="middle" fill="{{ $strokeCol }}" font-size="13" font-weight="800"
                          font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="1.5">{{ $issuerName }}</text>
                    <text x="105" y="82" text-anchor="middle" fill="{{ $strokeCol }}" font-size="7.5" font-weight="700"
                          font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="2">{{ $issuerDepartment }}</text>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $diagPatternId }})" />
        </svg>
    </div>

@elseif(in_array($type, ['crest', 'academic', 'star'], true))
    @php
        $wmOpacity = min(0.25, max(0.04, $safeOpacity * 0.65));
    @endphp
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden" style="opacity: {{ $wmOpacity }};">
        <svg width="480" height="480" viewBox="0 0 200 200" fill="none" stroke="{{ $accentColor }}">
            <circle cx="100" cy="100" r="90" stroke-width="0.8" stroke-dasharray="3 3" />
            <circle cx="100" cy="100" r="82" stroke-width="1.2" />
            <circle cx="100" cy="100" r="74" stroke-width="0.6" />
            <circle cx="100" cy="100" r="62" stroke-width="1" stroke-dasharray="6 4" />
            <circle cx="100" cy="100" r="50" stroke-width="0.8" />
            @for($i = 0; $i < 16; $i++)
                <ellipse cx="100" cy="100" rx="68" ry="24" stroke-width="0.5"
                         transform="rotate({{ $i * 11.25 }}, 100, 100)" />
            @endfor
            <circle cx="100" cy="100" r="32" stroke-width="1.5" />
        </svg>
    </div>

@else
    {{-- Default 'laurel': faint golden guilloche corner waves + side laurels --}}
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
        <svg width="520" height="260" viewBox="0 0 520 260" fill="none" class="absolute bottom-4 left-4 opacity-20">
            @for($i = 0; $i < 14; $i++)
                <path d="M -20 {{ 120 + $i * 9 }} Q {{ 140 + $i * 8 }} {{ 40 + $i * 6 }}, {{ 340 + $i * 6 }} {{ 230 + $i * 4 }}"
                      stroke="{{ $accentColor }}" stroke-width="0.85" fill="none" />
            @endfor
        </svg>

        <svg width="520" height="260" viewBox="0 0 520 260" fill="none" class="absolute bottom-4 right-4 opacity-20 transform -scale-x-100">
            @for($i = 0; $i < 14; $i++)
                <path d="M -20 {{ 120 + $i * 9 }} Q {{ 140 + $i * 8 }} {{ 40 + $i * 6 }}, {{ 340 + $i * 6 }} {{ 230 + $i * 4 }}"
                      stroke="{{ $accentColor }}" stroke-width="0.85" fill="none" />
            @endfor
        </svg>

        @if($showLaurelSides)
            <div class="absolute inset-0 flex items-center justify-between px-16" style="opacity: {{ $safeOpacity }};">
                <svg width="110" height="280" viewBox="0 0 110 280" fill="none">
                    <path d="M 88 260 C 28 205 22 105 82 20" stroke="{{ $accentColor }}" stroke-width="2" fill="none" />
                    @for($i = 0; $i < 11; $i++)
                        @php
                            $y = 38 + $i * 20;
                            $x = 42 + abs($i - 5) * 4.5;
                        @endphp
                        <g fill="#DFBC68">
                            <ellipse cx="{{ $x - 10 }}" cy="{{ $y }}" rx="14" ry="5.5"
                                     transform="rotate({{ -30 + $i * 4 }} {{ $x - 10 }} {{ $y }})" />
                            <ellipse cx="{{ $x + 12 }}" cy="{{ $y - 6 }}" rx="13" ry="5"
                                     transform="rotate({{ -65 + $i * 4 }} {{ $x + 12 }} {{ $y - 6 }})" />
                        </g>
                    @endfor
                </svg>
                <svg width="110" height="280" viewBox="0 0 110 280" fill="none" class="transform -scale-x-100">
                    <path d="M 88 260 C 28 205 22 105 82 20" stroke="{{ $accentColor }}" stroke-width="2" fill="none" />
                    @for($i = 0; $i < 11; $i++)
                        @php
                            $y = 38 + $i * 20;
                            $x = 42 + abs($i - 5) * 4.5;
                        @endphp
                        <g fill="#DFBC68">
                            <ellipse cx="{{ $x - 10 }}" cy="{{ $y }}" rx="14" ry="5.5"
                                     transform="rotate({{ -30 + $i * 4 }} {{ $x - 10 }} {{ $y }})" />
                            <ellipse cx="{{ $x + 12 }}" cy="{{ $y - 6 }}" rx="13" ry="5"
                                     transform="rotate({{ -65 + $i * 4 }} {{ $x + 12 }} {{ $y - 6 }})" />
                        </g>
                    @endfor
                </svg>
            </div>
        @endif
    </div>
@endif
