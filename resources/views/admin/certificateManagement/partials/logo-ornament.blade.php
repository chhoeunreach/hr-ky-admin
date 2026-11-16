{{--
    Logo side ornaments — faithful Blade port of the CertiCraft Studio
    LogoFlankingOrnament component (src/components/LogoOrnaments.tsx).

    Usage:
      @include('admin.certificateManagement.partials.logo-ornament', [
          'style' => $logoOrnament,        // styling.logoOrnament
          'accentColor' => $accentGold,
          'primaryColor' => $primaryNavy,
          'uid' => $uid,
          'className' => 'w-[310px] h-[132px] overflow-visible',
      ])
--}}
@php
    $style = $style ?? 'classic-laurel';
    $accentColor = $accentColor ?? '#D4A32C';
    $primaryColor = $primaryColor ?? '#092653';
    $uid = $uid ?? uniqid();
    $className = $className ?? 'w-[280px] h-[135px]';
    $gradId = 'logoOrnGrad_' . preg_replace('/[^A-Za-z0-9_]/', '', (string) $uid);
    $grad = 'url(#' . $gradId . ')';
@endphp

@if($style !== 'none')
    <svg viewBox="0 0 280 135" class="{{ $className }}" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $gradId }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#F5D77A" />
                <stop offset="45%" stop-color="{{ $accentColor }}" />
                <stop offset="100%" stop-color="#9A6914" />
            </linearGradient>
        </defs>

        @include('admin.certificateManagement.partials.logo-ornament-half', [
            'style' => $style,
            'gradId' => $gradId,
            'accentColor' => $accentColor,
            'primaryColor' => $primaryColor,
        ])

        <g transform="translate(280, 0) scale(-1, 1)">
            @include('admin.certificateManagement.partials.logo-ornament-half', [
                'style' => $style,
                'gradId' => $gradId,
                'accentColor' => $accentColor,
                'primaryColor' => $primaryColor,
            ])
        </g>

        {{-- Center top crest: crown (crown-laurel-wreath) or 3 stars (three-star-laurel) --}}
        @if($style === 'crown-laurel-wreath')
            <g transform="translate(140, 12)">
                <path d="M -18 8 L -21 -6 L -10 1 L 0 -10 L 10 1 L 21 -6 L 18 8 Z"
                      fill="{{ $grad }}" stroke="#9A6914" stroke-width="0.7" />
                <rect x="-18" y="9.5" width="36" height="3.2" rx="1.2"
                      fill="{{ $grad }}" stroke="#9A6914" stroke-width="0.5" />
                <circle cx="-21" cy="-7.5" r="2" fill="#FFF7D6" stroke="#9A6914" stroke-width="0.5" />
                <circle cx="0" cy="-11.5" r="2.5" fill="#FFF7D6" stroke="#9A6914" stroke-width="0.5" />
                <circle cx="21" cy="-7.5" r="2" fill="#FFF7D6" stroke="#9A6914" stroke-width="0.5" />
            </g>
        @elseif($style === 'three-star-laurel')
            <g fill="{{ $grad }}" stroke="#9A6914" stroke-width="0.4">
                <polygon transform="translate(120, 15) scale(0.62)"
                         points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                <polygon transform="translate(140, 10) scale(0.88)"
                         points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                <polygon transform="translate(160, 15) scale(0.62)"
                         points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
            </g>
        @endif
    </svg>
@endif
