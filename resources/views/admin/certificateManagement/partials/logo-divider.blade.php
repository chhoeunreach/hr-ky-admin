@php
    // Faithful Blade port of CertiCraft Studio's <LogoDividerOrnament /> (src/components/LogoOrnaments.tsx)
    // Honors styling.dividerOrnament / dividerColor / dividerWidth / dividerScale / dividerOffsetY / dividerOpacity
    $style = $style ?? 'baroque-fleur';
    $accentColor = $accentColor ?? '#D4A13A';
    $primaryColor = $primaryColor ?? '#0A224E';
    $customUrl = $customUrl ?? null;
    $width = $width ?? null;
    $height = $height ?? 26;
    $scale = $scale ?? 1;
    $offsetY = $offsetY ?? 0;
    $opacity = $opacity ?? 1;
    $className = $className ?? 'w-[280px] h-[26px]';
    $uid = $uid ?? ('div_' . uniqid());
    $gradId = 'divGoldGrad_' . $uid;

    // Legacy alias used by the old Blade form dropdown
    if ($style === 'minimal-line') {
        $style = 'minimal-gold-bar';
    }

    $wrapperStyle = 'opacity: ' . $opacity . '; transform: translateY(' . $offsetY . 'px) scale(' . $scale . '); transform-origin: center center;'
        . ($width ? ' width: ' . $width . 'px;' : '');
@endphp

@if($style !== 'none')
    @if($style === 'custom')
        @if(!empty($customUrl))
            <div class="flex items-center justify-center overflow-visible" style="{{ $wrapperStyle }}">
                <img src="{{ $customUrl }}" alt="Custom Divider Ornament" class="max-h-[42px] w-full object-contain">
            </div>
        @endif
    @else
        <svg viewBox="0 0 280 26" fill="none" xmlns="http://www.w3.org/2000/svg"
             class="{{ $className }} overflow-visible transition-all"
             style="{{ $wrapperStyle }}{{ $width ? ' height: ' . (int) $height . 'px;' : '' }}"
             aria-hidden="true">
            <defs>
                <linearGradient id="{{ $gradId }}" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="{{ $accentColor }}" stop-opacity="0" />
                    <stop offset="20%" stop-color="{{ $accentColor }}" />
                    <stop offset="50%" stop-color="#F5D77A" />
                    <stop offset="80%" stop-color="{{ $accentColor }}" />
                    <stop offset="100%" stop-color="{{ $accentColor }}" stop-opacity="0" />
                </linearGradient>
            </defs>

            @if($style === 'baroque-fleur')
                {{-- Long Razor-Tapered Outer Gold Spear Rails --}}
                <path d="M 4 13 L 76 11.6 L 76 14.4 Z" fill="url(#{{ $gradId }})" />
                <path d="M 276 13 L 204 11.6 L 204 14.4 Z" fill="url(#{{ $gradId }})" />

                {{-- Outer Gold Beads & Double S-Volute Scrolls --}}
                @foreach([0, 1] as $side)
                    <g {!! $side === 1 ? 'transform="translate(280, 0) scale(-1, 1)"' : '' !!}>
                        <circle cx="80" cy="13" r="2.2" fill="{{ $accentColor }}" />
                        <circle cx="80" cy="12.4" r="0.9" fill="#FFF7D6" fill-opacity="0.8" />

                        <path d="M 85 13 C 89 7 98 7 100 12 C 102 16 97 19 93 17 C 90 15.5 91 12 94 12.5"
                              stroke="{{ $accentColor }}" stroke-width="1.5" stroke-linecap="round" fill="none" />
                        <path d="M 97 13 C 101 18 107 18 110 14"
                              stroke="{{ $accentColor }}" stroke-width="1.2" stroke-linecap="round" fill="none" />

                        <path d="M 136 10.5 C 127 2.5 112 3.5 109 12.5 C 106.5 20 116 23.5 123 19.5 C 128 16.5 127 10.5 121.5 10 C 118 9.6 116.5 12.5 119 14.5 C 120.8 16 123 14.5 122 12.8"
                              stroke="url(#{{ $gradId }})" stroke-width="2.1" stroke-linecap="round" fill="none" />
                        <path d="M 135 16.5 C 129 21.5 121 21 117 17"
                              stroke="{{ $accentColor }}" stroke-width="1.3" stroke-linecap="round" fill="none" />
                        <circle cx="104.5" cy="13" r="1.4" fill="{{ $accentColor }}" />
                    </g>
                @endforeach

                {{-- Central Elongated Two-Pointed Heraldic Diamond Finial --}}
                <path d="M 140 1.5 L 145.5 13 L 140 24.5 L 134.5 13 Z" fill="url(#{{ $gradId }})" stroke="#9A6914" stroke-width="0.4" />
                <path d="M 140 5 L 142.8 13 L 140 21 L 137.2 13 Z" fill="#FFF7D6" fill-opacity="0.45" />
            @endif

            @if($style === 'acanthus-gold-line')
                <path d="M 4 13 L 88 11.7 L 88 14.3 Z" fill="url(#{{ $gradId }})" />
                <path d="M 276 13 L 192 11.7 L 192 14.3 Z" fill="url(#{{ $gradId }})" />
                @foreach([0, 1] as $side)
                    <g {!! $side === 1 ? 'transform="translate(280, 0) scale(-1, 1)"' : '' !!}>
                        <path d="M 88 13 C 96 6 108 6 112 13 C 115 19 107 21 103 16 C 101 13 104 10 107 12"
                              stroke="url(#{{ $gradId }})" stroke-width="1.8" stroke-linecap="round" fill="none" />
                        <path d="M 114 11 C 122 3 132 5 137 13"
                              stroke="url(#{{ $gradId }})" stroke-width="1.9" stroke-linecap="round" fill="none" />
                        <path d="M 116 15 C 123 21 131 21 137 14"
                              stroke="{{ $accentColor }}" stroke-width="1.4" stroke-linecap="round" fill="none" />
                        <path d="M 96 11 C 94 7 98 5 101 8 Z" fill="{{ $accentColor }}" />
                    </g>
                @endforeach
                <path d="M 140 4 L 144 13 L 140 22 L 136 13 Z" fill="url(#{{ $gradId }})" />
            @endif

            @if($style === 'beaded-diamond-bar')
                <path d="M 4 13 L 104 11.4 L 104 14.6 Z" fill="url(#{{ $gradId }})" />
                <path d="M 276 13 L 176 11.4 L 176 14.6 Z" fill="url(#{{ $gradId }})" />
                <circle cx="106" cy="13" r="2.4" fill="url(#{{ $gradId }})" />
                <circle cx="116" cy="13" r="3" fill="url(#{{ $gradId }})" stroke="#9A6914" stroke-width="0.4" />
                <circle cx="126.5" cy="13" r="1.8" fill="{{ $accentColor }}" />
                <circle cx="153.5" cy="13" r="1.8" fill="{{ $accentColor }}" />
                <circle cx="164" cy="13" r="3" fill="url(#{{ $gradId }})" stroke="#9A6914" stroke-width="0.4" />
                <circle cx="174" cy="13" r="2.4" fill="url(#{{ $gradId }})" />
                <polygon points="140,2.5 147.5,13 140,23.5 132.5,13" fill="url(#{{ $gradId }})" stroke="#9A6914" stroke-width="0.5" />
                <polygon points="140,6 143.8,13 140,20 136.2,13" fill="#FFF7D6" fill-opacity="0.6" />
            @endif

            @if($style === 'khmer-lotus')
                <path d="M 14 13 L 96 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <path d="M 184 13 L 266 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <path d="M 140 3 C 143 8 144 13 140 18 C 136 13 137 8 140 3 Z" fill="url(#{{ $gradId }})" />
                <path d="M 136 17 C 130 13 128 8 131 5 C 134 9 136 13 137 17 Z" fill="{{ $accentColor }}" />
                <path d="M 144 17 C 150 13 152 8 149 5 C 146 9 144 13 143 17 Z" fill="{{ $accentColor }}" />
                <path d="M 129 15 C 121 18 113 12 108 16 C 105 18 102 15 98 13"
                      stroke="{{ $accentColor }}" stroke-width="1.4" stroke-linecap="round" fill="none" />
                <path d="M 151 15 C 159 18 167 12 172 16 C 175 18 178 15 182 13"
                      stroke="{{ $accentColor }}" stroke-width="1.4" stroke-linecap="round" fill="none" />
                <ellipse cx="140" cy="20.5" rx="9" ry="1.4" fill="{{ $accentColor }}" />
            @endif

            @if($style === 'khmer-angkor-crest')
                <path d="M 6 14 L 84 13 L 84 15 Z" fill="url(#{{ $gradId }})" />
                <path d="M 274 14 L 196 13 L 196 15 Z" fill="url(#{{ $gradId }})" />
                @foreach([0, 1] as $side)
                    <g {!! $side === 1 ? 'transform="translate(280, 0) scale(-1, 1)"' : '' !!}>
                        <path d="M 86 14 C 95 6 108 6 112 14 C 115 20 107 22 104 17 C 102 14 105 11 108 13"
                              stroke="url(#{{ $gradId }})" stroke-width="1.7" stroke-linecap="round" fill="none" />
                        <path d="M 114 14 C 120 7 129 8 133 14"
                              stroke="{{ $accentColor }}" stroke-width="1.6" stroke-linecap="round" fill="none" />
                        <path d="M 122 8 C 120 3 125 2 127 6 C 125 6 124 8 122 8 Z" fill="{{ $accentColor }}" />
                        <circle cx="88" cy="14" r="1.8" fill="{{ $accentColor }}" />
                    </g>
                @endforeach
                <path d="M 140 1.5 L 143.5 9 L 147 14 L 140 23.5 L 133 14 L 136.5 9 Z"
                      fill="url(#{{ $gradId }})" stroke="#9A6914" stroke-width="0.4" />
                <circle cx="140" cy="13.5" r="2.2" fill="#FFFDF8" fill-opacity="0.7" />
            @endif

            @if($style === 'royal-crown-line')
                <path d="M 18 15 L 114 15" stroke="url(#{{ $gradId }})" stroke-width="1.3" />
                <path d="M 166 15 L 262 15" stroke="url(#{{ $gradId }})" stroke-width="1.3" />
                <path d="M 126 17 L 123 8 L 131 12 L 140 5 L 149 12 L 157 8 L 154 17 Z"
                      fill="url(#{{ $gradId }})" stroke="{{ $primaryColor }}" stroke-width="0.4" />
                <rect x="126" y="18.5" width="28" height="2" rx="1" fill="{{ $accentColor }}" />
                <circle cx="116" cy="15" r="1.6" fill="{{ $accentColor }}" />
                <circle cx="164" cy="15" r="1.6" fill="{{ $accentColor }}" />
            @endif

            @if($style === 'royal-plume')
                <path d="M 12 13 L 88 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <path d="M 192 13 L 268 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                @foreach([0, 1] as $side)
                    <g {!! $side === 1 ? 'transform="translate(280, 0) scale(-1, 1)"' : '' !!}>
                        <path d="M 132 13 C 122 5 106 5 94 11 C 106 11 118 12 130 14 Z" fill="url(#{{ $gradId }})" />
                        <path d="M 130 15 C 118 20 105 20 96 16 C 107 16 119 15 130 15 Z"
                              fill="{{ $accentColor }}" fill-opacity="0.85" />
                        <circle cx="91" cy="13" r="1.7" fill="{{ $accentColor }}" />
                    </g>
                @endforeach
                <polygon points="140,6 146,13 140,20 134,13" fill="url(#{{ $gradId }})" />
                <circle cx="140" cy="13" r="2" fill="#FFFDF8" fill-opacity="0.75" />
            @endif

            @if($style === 'diamond-stars')
                <path d="M 16 13 L 108 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <path d="M 172 13 L 264 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <polygon points="140,5 147,13 140,21 133,13" fill="url(#{{ $gradId }})" />
                <polygon points="140,8.5 143.5,13 140,17.5 136.5,13" fill="#FFFDF8" fill-opacity="0.6" />
                <path d="M 120 7 L 121.5 11.5 L 126 13 L 121.5 14.5 L 120 19 L 118.5 14.5 L 114 13 L 118.5 11.5 Z" fill="{{ $accentColor }}" />
                <path d="M 160 7 L 161.5 11.5 L 166 13 L 161.5 14.5 L 160 19 L 158.5 14.5 L 154 13 L 158.5 11.5 Z" fill="{{ $accentColor }}" />
                <circle cx="109" cy="13" r="1.5" fill="{{ $accentColor }}" />
                <circle cx="171" cy="13" r="1.5" fill="{{ $accentColor }}" />
            @endif

            @if($style === 'double-scallop')
                <path d="M 16 13 L 98 13" stroke="url(#{{ $gradId }})" stroke-width="1.1" />
                <path d="M 182 13 L 264 13" stroke="url(#{{ $gradId }})" stroke-width="1.1" />
                @foreach([108, 118, 128] as $x)
                    <ellipse cx="{{ $x }}" cy="9.5" rx="5.5" ry="2.2" transform="rotate(-20 {{ $x }} 9.5)" fill="{{ $accentColor }}" />
                    <ellipse cx="{{ $x }}" cy="16.5" rx="5.5" ry="2.2" transform="rotate(20 {{ $x }} 16.5)" fill="{{ $accentColor }}" />
                @endforeach
                @foreach([152, 162, 172] as $x)
                    <ellipse cx="{{ $x }}" cy="9.5" rx="5.5" ry="2.2" transform="rotate(20 {{ $x }} 9.5)" fill="{{ $accentColor }}" />
                    <ellipse cx="{{ $x }}" cy="16.5" rx="5.5" ry="2.2" transform="rotate(-20 {{ $x }} 16.5)" fill="{{ $accentColor }}" />
                @endforeach
                <circle cx="140" cy="13" r="3.4" fill="url(#{{ $gradId }})" />
            @endif

            @if($style === 'infinity-filigree')
                <path d="M 10 13 L 94 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <path d="M 186 13 L 270 13" stroke="url(#{{ $gradId }})" stroke-width="1.2" />
                <path d="M 96 13 C 106 4 118 4 128 13 C 138 22 142 22 152 13 C 162 4 174 4 184 13"
                      stroke="url(#{{ $gradId }})" stroke-width="1.7" fill="none" stroke-linecap="round" />
                <path d="M 96 13 C 106 22 118 22 128 13 C 138 4 142 4 152 13 C 162 22 174 22 184 13"
                      stroke="{{ $accentColor }}" stroke-width="1.3" fill="none" stroke-linecap="round" />
                <circle cx="140" cy="13" r="2.6" fill="#FFFDF8" stroke="{{ $accentColor }}" stroke-width="1.4" />
            @endif

            @if($style === 'minimal-gold-bar')
                <path d="M 24 13 L 130 13" stroke="url(#{{ $gradId }})" stroke-width="1.4" />
                <path d="M 150 13 L 256 13" stroke="url(#{{ $gradId }})" stroke-width="1.4" />
                <polygon points="140,8.5 144.5,13 140,17.5 135.5,13" fill="{{ $accentColor }}" />
            @endif
        </svg>
    @endif
@endif
