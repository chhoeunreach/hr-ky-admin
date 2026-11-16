@php
    $styling = $certificate['styling'] ?? [];
    $layout = $certificate['layoutTemplate'] ?? 'executive-medal-ribbon';
    $hangingStyle = $certificate['hangingMedalStyle'] ?? ($certificate['payload_json']['hangingMedalStyle'] ?? null);

    $isImperialSash = ($hangingStyle === 'gold-sash') || in_array($layout, ['executive-imperial-sash', 'v6-sash']);
    $isMedalRibbon = ($hangingStyle === 'navy-sash') || in_array($layout, ['executive-medal-ribbon', 'v5-medal']) || (!$isImperialSash && $hangingStyle !== 'none' && !in_array($layout, ['classic', 'v4-royal', 'executive-verify-v2', 'v2-id', 'executive-navy-ribbon', 'v3-ribbon', 'executive-verify', 'v1-executive-gold']));
    if ($hangingStyle === 'none') {
        $isImperialSash = false;
        $isMedalRibbon = false;
    }
    $isNavyRibbon = in_array($layout, ['executive-navy-ribbon', 'v3-ribbon']);
    $isClassic = in_array($layout, ['classic', 'v4-royal']);
    $isVerifyV2 = in_array($layout, ['executive-verify-v2', 'v2-id']);

    $isDark = ($certificate['theme'] ?? '') === 'tech-obsidian';
    $primaryNavy = $styling['primaryColor'] ?? ($isDark ? '#F1F5F9' : ($isClassic ? '#7B1113' : '#092653'));
    $accentGold = $styling['accentColor'] ?? '#D4A32C';
    $bgColor = $styling['bgColor'] ?? ($isClassic ? '#FAF6ED' : '#FFFDF8');
    $borderStyle = $styling['borderStyle'] ?? 'navy-gold-ribbon';
    $dividerOrnament = $styling['dividerOrnament'] ?? 'baroque-fleur';
    $dividerWidth = (int) ($styling['dividerWidth'] ?? 240);
    $dividerColor = $styling['dividerColor'] ?? ($styling['accentColor'] ?? $accentGold);
    $dividerScale = (float) ($styling['dividerScale'] ?? 1);
    $dividerOffsetY = (float) ($styling['dividerOffsetY'] ?? 0);
    $dividerOpacity = (float) ($styling['dividerOpacity'] ?? 1);
    $customDividerUrl = $styling['customDividerUrl'] ?? null;
    $visibility = $certificate['visibility'] ?? ($styling['visibility'] ?? []);
    $showHeaderDivider = $visibility['showHeaderDivider'] ?? true;
    $showOrganizationSubBanner = $visibility['showOrganizationSubBanner'] ?? true;
    $showQrCode = $certificate['showQrCode'] ?? true;
    $organizationSubBanner = $certificate['organizationSubBanner'] ?? ($certificate['recipientTitle'] ?? '');
    $digitalHr = is_array($certificate['digitalHr'] ?? null) ? $certificate['digitalHr'] : [];
    $showDigitalHoursBadge = !empty($digitalHr['showDigitalHoursBadge']);
    // Honour the saved logo side-ornament (CertiCraft Studio → Styling → Logo Ornament)
    $logoOrnament = $styling['logoOrnament'] ?? ((($styling['showLaurelWreath'] ?? true) === false) ? 'none' : 'classic-laurel');
    $showWatermark = $styling['showWatermark'] ?? true;
    $watermarkType = $styling['watermarkType'] ?? 'laurel';
    $watermarkOpacity = $styling['watermarkOpacity'] ?? 0.14;
    $sealData = $certificate['seal'] ?? [];

    $fieldStyles = $styling['fieldStyles'] ?? [];
    $khmerTitleFont = $fieldStyles['khmerTitle']['fontFamily'] ?? 'khmer-moul';
    $khmerRecipientFont = $fieldStyles['khmerRecipientName']['fontFamily'] ?? 'khmer-moul';

    $photoUrl = $certificate['recipientPhotoUrl'] ?? null;
    $hasPhoto = !empty($photoUrl) && !str_contains($photoUrl, 'khun_sreyni_portrait.jpg');
    $showPhoto = ($certificate['showRecipientPhoto'] ?? true);
    $photoShape = $certificate['recipientPhotoShape'] ?? ($isVerifyV2 ? 'circle' : 'rounded-portrait');
    // Studio photo settings (src/components/CertificateRenderer.tsx)
    $photoPosition = $certificate['recipientPhotoPosition'] ?? 'top-right';
    $photoSize = (int) ($certificate['recipientPhotoSize'] ?? 86);
    $photoZoom = (float) ($certificate['recipientPhotoZoom'] ?? 1);
    $photoOffsetX = $certificate['recipientPhotoOffsetX'] ?? 50;
    $photoOffsetY = $certificate['recipientPhotoOffsetY'] ?? 25;
    $photoFrameOffsetX = (float) ($certificate['recipientPhotoFrameOffsetX'] ?? 0);
    $photoFrameOffsetY = (float) ($certificate['recipientPhotoFrameOffsetY'] ?? 0);

    $photoIsCircle = $photoShape === 'circle';
    $photoIsSquare = $photoShape === 'square';
    $photoBase = $photoSize ?: 86;
    $photoFrameW = $photoIsCircle
        ? (int) round($photoBase * 1.18)
        : ($photoIsSquare ? (int) round($photoBase * 1.15) : (int) round($photoBase * 1.1));
    $photoFrameH = ($photoIsCircle || $photoIsSquare) ? $photoFrameW : (int) round($photoBase * 1.34);
    $photoRadiusOuter = $photoIsCircle ? '9999px' : ($photoIsSquare ? '8px' : '12px');
    $photoRadiusMid = $photoIsCircle ? '9999px' : ($photoIsSquare ? '7px' : '10px');
    $photoRadiusInner = $photoIsCircle ? '9999px' : ($photoIsSquare ? '5px' : '7px');
    $photoFrameTransform = ($photoFrameOffsetX != 0 || $photoFrameOffsetY != 0)
        ? 'transform: translate(' . $photoFrameOffsetX . 'px, ' . $photoFrameOffsetY . 'px);'
        : '';
    $photoAnchorStyle = $photoPosition === 'top-left'
        ? 'top: 74px; left: 104px;'
        : 'top: 74px; right: 104px;';
    $photoImgStyle = 'object-position: ' . $photoOffsetX . '% ' . $photoOffsetY . '%;'
        . ($photoZoom != 1 ? ' transform: scale(' . $photoZoom . ');' : '');
    $photoIdStyle = 'background-color: ' . $primaryNavy . '; border-color: ' . $accentGold . ';';
    $uid = 'canvas_' . ($certificate['credentialId'] ?? ($certificate['id'] ?? uniqid()));
@endphp

<!-- Certificate Canvas (1080px x 760px A4 Landscape) -->
<div class="certificate-container relative w-[1080px] h-[760px] shadow-2xl overflow-hidden flex flex-col justify-between text-center select-none"
     style="background-color: {{ $bgColor }}; background-image: radial-gradient(ellipse at 50% 35%, #FFFFFF 0%, {{ $bgColor }} 65%, #F7EFE1 100%); border: 8px solid {{ $accentGold }};">

    <!-- Vector Masterwork Certificate Border (Navy & Gold Ribbons + Acanthus Filigrees) -->
    <svg class="cert-border-svg absolute top-0 left-0 w-full h-full pointer-events-none z-0" viewBox="0 0 1080 760" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="luxuryGoldFoil_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#C69226" />
                <stop offset="25%" stop-color="#FCE392" />
                <stop offset="48%" stop-color="#E1B347" />
                <stop offset="72%" stop-color="#FFF4B8" />
                <stop offset="100%" stop-color="#9C6B14" />
            </linearGradient>
            <linearGradient id="navySweepMid_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#0E346E" />
                <stop offset="50%" stop-color="#092653" />
                <stop offset="100%" stop-color="#041029" />
            </linearGradient>
            <linearGradient id="navySweepDark_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#071E42" />
                <stop offset="50%" stop-color="#041029" />
                <stop offset="100%" stop-color="#020817" />
            </linearGradient>
            <linearGradient id="goldRibbonSweep_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#C69226" />
                <stop offset="35%" stop-color="#FFF0B0" />
                <stop offset="70%" stop-color="#D4A32C" />
                <stop offset="100%" stop-color="#8C5D0E" />
            </linearGradient>
            <filter id="ribbonDrop_{{ $uid }}" x="-10%" y="-10%" width="130%" height="130%">
                <feDropShadow dx="3" dy="4" stdDeviation="4" flood-color="#000000" flood-opacity="0.25" />
            </filter>
        </defs>

        @if($borderStyle === 'navy-gold-ribbon' || $isMedalRibbon || $isImperialSash || $isNavyRibbon)
            <!-- TOP-LEFT SWEEPING LAYERED CORNER RIBBON -->
            <g filter="url(#ribbonDrop_{{ $uid }})">
                <path d="M 0 0 L 298 0 C 206 42 88 128 0 284 Z" fill="url(#goldRibbonSweep_{{ $uid }})" />
                <path d="M 0 0 L 266 0 C 178 44 74 120 0 242 Z" fill="url(#navySweepMid_{{ $uid }})" />
                <path d="M 0 0 L 188 0 C 112 40 48 92 0 164 Z" fill="url(#navySweepDark_{{ $uid }})" />
                <path d="M 188 0 C 114 40 48 90 0 158" fill="none" stroke="url(#goldRibbonSweep_{{ $uid }})" stroke-width="1.5" />
                <path d="M 272 0 C 184 44 78 122 0 248" fill="none" stroke="#FFF4BE" stroke-width="1.2" stroke-opacity="0.85" />
            </g>

            <!-- BOTTOM-RIGHT SWEEPING LAYERED CORNER RIBBON -->
            <g transform="translate(1080, 760) scale(-1, -1)" filter="url(#ribbonDrop_{{ $uid }})">
                <path d="M 0 0 L 298 0 C 206 42 88 128 0 284 Z" fill="url(#goldRibbonSweep_{{ $uid }})" />
                <path d="M 0 0 L 266 0 C 178 44 74 120 0 242 Z" fill="url(#navySweepMid_{{ $uid }})" />
                <path d="M 0 0 L 188 0 C 112 40 48 92 0 164 Z" fill="url(#navySweepDark_{{ $uid }})" />
                <path d="M 188 0 C 114 40 48 90 0 158" fill="none" stroke="url(#goldRibbonSweep_{{ $uid }})" stroke-width="1.5" />
                <path d="M 272 0 C 184 44 78 122 0 248" fill="none" stroke="#FFF4BE" stroke-width="1.2" stroke-opacity="0.85" />
            </g>

            <!-- DOUBLE METALLIC GOLD FRAME RAILS (Overlaid on Top of Corner Ribbons) -->
            <rect x="18" y="18" width="1044" height="724" fill="none" stroke="url(#luxuryGoldFoil_{{ $uid }})" stroke-width="3.8" />
            <rect x="25.5" y="25.5" width="1029" height="709" fill="none" stroke="url(#luxuryGoldFoil_{{ $uid }})" stroke-width="1.4" />

            <!-- 4 BAROQUE GOLDEN ACANTHUS CORNER FILIGREE SCROLLS -->
            @foreach([
                'translate(29, 29)',
                'translate(1051, 29) scale(-1, 1)',
                'translate(29, 731) scale(1, -1)',
                'translate(1051, 731) scale(-1, -1)'
            ] as $tf)
                <g transform="{{ $tf }}">
                    <path d="M 2 2 C 10 3 17 7 21 15 C 13 17 6 12 2 2 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                    <path d="M 12 3 C 19 2 26 4 29 8 C 23 10 17 8 12 3 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                    <path d="M 3 12 C 2 19 4 26 8 29 C 10 23 8 17 3 12 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                    <path d="M 20 14 C 26 4 44 1 56 8 C 66 14 68 28 59 35 C 52 40 43 38 41 31 C 39 25 45 21 50 23 C 53 25 53 29 50 31 C 55 31 59 24 55 16 C 50 7 35 7 25 19 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                    <path d="M 14 20 C 4 26 1 44 8 56 C 14 66 28 68 35 59 C 40 52 38 43 31 41 C 25 39 21 45 23 50 C 25 53 29 53 31 50 C 31 55 24 59 16 55 C 7 50 7 35 19 25 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                    <path d="M 60 11 C 72 3 88 5 94 14 C 98 20 93 27 86 25 C 81 23 81 18 85 16 C 89 15 91 19 88 21 C 92 19 91 12 84 9 C 77 7 68 10 63 16 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                    <path d="M 11 60 C 3 72 5 88 14 94 C 20 98 27 93 25 86 C 23 81 18 81 16 85 C 15 89 19 91 21 88 C 19 92 12 91 9 84 C 7 77 10 68 16 63 Z" fill="url(#luxuryGoldFoil_{{ $uid }})" />
                </g>
            @endforeach
        @else
            <!-- Standard Ornate Inner Hairline Border Frame -->
            <rect x="18" y="18" width="1044" height="724" fill="none" stroke="{{ $primaryNavy }}" stroke-width="2.5" stroke-opacity="0.4" />
            <rect x="25.5" y="25.5" width="1029" height="709" fill="none" stroke="{{ $accentGold }}" stroke-width="1.2" />
        @endif
    </svg>

    <!-- Background Watermark & Guilloche Security Pattern (matches Studio setting) -->
    @if($showWatermark)
        @include('admin.certificateManagement.partials.watermark', [
            'type' => $watermarkType,
            'opacity' => $watermarkOpacity,
            'primaryColor' => $primaryNavy,
            'accentColor' => $accentGold,
            'issuerName' => $certificate['issuer']['name'] ?? 'KNEAYERNG',
            'issuerDepartment' => $certificate['issuer']['department'] ?? 'PHONE SHOP',
            'uid' => $uid,
        ])
    @endif

    <!-- Subtle Isometric Architectural Storefront Watermark (Right Flank) matching CertiCraft Studio -->
    @if(($visibility['showStorefrontWatermark'] ?? $isImperialSash) && $showWatermark)
        <div class="pointer-events-none absolute z-[1] right-[44px] bottom-[176px]"
             style="opacity: {{ min(0.16, ($watermarkOpacity ?? 0.14) * 0.95) }};">
            <svg width="210" height="165" viewBox="0 0 210 165" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Perspective Storefront Fascia -->
                <polygon points="16,46 136,12 198,28 198,144 136,136 16,154" fill="{{ $primaryNavy }}" fill-opacity="0.06" stroke="{{ $primaryNavy }}" stroke-width="1.2" />
                <!-- Signboard Panel -->
                <polygon points="20,46 136,14 136,68 20,88" fill="#FFFFFF" fill-opacity="0.55" stroke="{{ $primaryNavy }}" stroke-width="1.1" />
                <!-- Roof Overhang -->
                <polygon points="10,44 138,8 204,26 198,32 136,14 16,48" fill="{{ $primaryNavy }}" fill-opacity="0.14" stroke="{{ $primaryNavy }}" stroke-width="1" />
                <!-- Storefront K Emblem & Sign Text -->
                <g transform="translate(76, 40) skewY(-14)">
                    <text x="0" y="-4" text-anchor="middle" fill="{{ $primaryNavy }}" font-size="18" font-weight="900" font-family="'Cinzel', serif">
                        {{ strtoupper(substr(trim($certificate['issuer']['name'] ?? 'K'), 0, 1)) }}
                    </text>
                    <text x="0" y="9" text-anchor="middle" fill="{{ $primaryNavy }}" font-size="9.5" font-weight="800" font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="0.8">
                        {{ strtoupper(substr($certificate['issuer']['name'] ?? 'KNEAYERNG', 0, 12)) }}
                    </text>
                    <text x="0" y="17" text-anchor="middle" fill="{{ $primaryNavy }}" font-size="5.2" font-weight="700" font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="1.2">
                        {{ strtoupper(substr($certificate['issuer']['department'] ?? 'PHONE SHOP', 0, 14)) }}
                    </text>
                </g>
                <!-- Glass Display Windows & Double Entrance Doors -->
                <polygon points="26,94 60,88 60,148 26,152" fill="#FFFFFF" fill-opacity="0.45" stroke="{{ $primaryNavy }}" stroke-width="1" />
                <polygon points="66,87 104,80 104,142 66,147" fill="{{ $primaryNavy }}" fill-opacity="0.08" stroke="{{ $primaryNavy }}" stroke-width="1.1" />
                <line x1="85" y1="84" x2="85" y2="144" stroke="{{ $primaryNavy }}" stroke-width="1" />
                <polygon points="110,79 134,74 134,136 110,141" fill="#FFFFFF" fill-opacity="0.45" stroke="{{ $primaryNavy }}" stroke-width="1" />
                <polygon points="140,74 192,80 192,143 140,136" fill="{{ $primaryNavy }}" fill-opacity="0.06" stroke="{{ $primaryNavy }}" stroke-width="1" />
            </svg>
        </div>
    @endif

    <!-- Top-Left Vertical Hanging Ribbon & Crest Medallion -->
    @if($isImperialSash)
        <!-- Solid Metallic Gold Hanging Sash (V6) with Scalloped Rosette Crest matching CertiCraft Studio -->
        <div class="cert-ribbon-zone absolute top-0 left-12 z-20 pointer-events-none drop-shadow-xl">
            <svg width="156" height="344" viewBox="0 0 160 352" fill="none" xmlns="http://www.w3.org/2000/svg" class="overflow-visible">
                <defs>
                    <linearGradient id="v6GSash_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#B88218" />
                        <stop offset="22%" stop-color="#F5D675" />
                        <stop offset="50%" stop-color="#FFF2B8" />
                        <stop offset="78%" stop-color="#E6B846" />
                        <stop offset="100%" stop-color="#A67214" />
                    </linearGradient>
                    <linearGradient id="v6NTail_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0E346E" />
                        <stop offset="50%" stop-color="#092653" />
                        <stop offset="100%" stop-color="#041029" />
                    </linearGradient>
                    <linearGradient id="v6GoldRail_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#F9DF86" />
                        <stop offset="35%" stop-color="#D4A32C" />
                        <stop offset="70%" stop-color="#FFF0B0" />
                        <stop offset="100%" stop-color="#9C6B14" />
                    </linearGradient>
                    <radialGradient id="v6GRos_{{ $uid }}" cx="38%" cy="32%" r="68%">
                        <stop offset="0%" stop-color="#FFFCE6" />
                        <stop offset="28%" stop-color="#F8DE82" />
                        <stop offset="62%" stop-color="#D4A32C" />
                        <stop offset="88%" stop-color="#A37116" />
                        <stop offset="100%" stop-color="#6E490A" />
                    </radialGradient>
                    <radialGradient id="v6NavyMed_{{ $uid }}" cx="40%" cy="35%" r="68%">
                        <stop offset="0%" stop-color="#123C7D" />
                        <stop offset="55%" stop-color="#092653" />
                        <stop offset="100%" stop-color="#04122B" />
                    </radialGradient>
                </defs>
                <!-- Gold Vertical Band -->
                <rect x="46" y="0" width="68" height="175" fill="url(#v6GSash_{{ $uid }})" />
                <rect x="49" y="0" width="2" height="175" fill="#FFF7D1" opacity="0.7" />
                <rect x="109" y="0" width="2" height="175" fill="#8C5D0E" opacity="0.45" />

                <!-- Bottom 3 Hanging Swallowtail Tails -->
                <polygon points="48,228 74,234 52,324 38,308 22,318" fill="url(#v6NTail_{{ $uid }})" stroke="url(#v6GoldRail_{{ $uid }})" stroke-width="2" />
                <polygon points="86,234 112,228 138,318 122,308 108,324" fill="url(#v6NTail_{{ $uid }})" stroke="url(#v6GoldRail_{{ $uid }})" stroke-width="2" />
                <polygon points="64,230 96,230 98,322 80,340 62,322" fill="url(#v6GRos_{{ $uid }})" stroke="#8F6110" stroke-width="1.5" />
                <polygon points="69,232 91,232 92,318 80,331 68,318" fill="none" stroke="#FFF7CC" stroke-width="0.9" opacity="0.7" />

                <!-- Scalloped Metallic Gold Rosette Medallion matching CertiCraft Studio -->
                <g transform="translate(80, 178)">
                    @php
                        $medallionScallopsV6 = '';
                        for ($i = 0; $i < 24; $i++) {
                            $a1 = ($i / 24) * M_PI * 2;
                            $aMid = (($i + 0.5) / 24) * M_PI * 2;
                            $rOut = 72;
                            $rIn = 64.5;
                            $x1 = cos($a1) * $rOut;
                            $y1 = sin($a1) * $rOut;
                            $x2 = cos($aMid) * $rIn;
                            $y2 = sin($aMid) * $rIn;
                            $medallionScallopsV6 .= ($i === 0 ? 'M' : 'L') . " {$x1} {$y1} L {$x2} {$y2} ";
                        }
                        $medallionScallopsV6 .= 'Z';
                    @endphp
                    <path d="{{ $medallionScallopsV6 }}" fill="url(#v6GRos_{{ $uid }})" stroke="#8A5D0F" stroke-width="1.4" />
                    <circle r="58" fill="none" stroke="#FFF4B8" stroke-width="1.4" opacity="0.85" />
                    <circle r="53" fill="url(#v6NavyMed_{{ $uid }})" stroke="url(#v6GoldRail_{{ $uid }})" stroke-width="2.4" />
                    <circle r="48.5" fill="none" stroke="#F9DF86" stroke-width="0.7" opacity="0.65" />

                    <!-- Royal 3-Point Crown -->
                    <g transform="translate(0, -33)">
                        <path d="M -10 5 L -12 -5 L -5 0 L 0 -8 L 5 0 L 12 -5 L 10 5 Z" fill="url(#v6GoldRail_{{ $uid }})" stroke="#784E0A" stroke-width="0.6" />
                        <rect x="-10" y="6" width="20" height="2.4" rx="0.8" fill="url(#v6GoldRail_{{ $uid }})" />
                        <circle cx="-12" cy="-6.2" r="1.4" fill="#FFF7C8" />
                        <circle cx="0" cy="-9.5" r="1.7" fill="#FFF7C8" />
                        <circle cx="12" cy="-6.2" r="1.4" fill="#FFF7C8" />
                    </g>

                    <!-- Center Gold K Monogram -->
                    <text x="0" y="2" text-anchor="middle" fill="url(#v6GoldRail_{{ $uid }})" font-size="30" font-weight="900" font-family="'Cinzel', serif" stroke="#784E0A" stroke-width="0.4">K</text>

                    <!-- Text Inside Medallion -->
                    <text x="0" y="15" text-anchor="middle" fill="#FFF6CE" font-size="7.4" font-weight="800" font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="0.8">KNEAYERNG</text>
                    <text x="0" y="23" text-anchor="middle" fill="#F4D16B" font-size="5.4" font-weight="700" font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="1.1">PHONE SHOP</text>

                    <!-- Flanking Gold Laurels -->
                    @foreach([-1, 1] as $side)
                        <g transform="scale({{ $side }}, 1)" fill="url(#v6GoldRail_{{ $uid }})">
                            @for($idx = 0; $idx < 7; $idx++)
                                @php
                                    $angle = (-15 + $idx * 17) * (M_PI / 180);
                                    $lx = -38 * cos($angle * 0.45);
                                    $ly = 24 - $idx * 7.8;
                                    $rot = -35 + $idx * 12;
                                @endphp
                                <ellipse cx="{{ $lx }}" cy="{{ $ly }}" rx="4.2" ry="1.9" transform="rotate({{ $rot }}, {{ $lx }}, {{ $ly }})" />
                            @endfor
                        </g>
                    @endforeach

                    <!-- 3 Gold Stars -->
                    <g fill="#FFF2B2" stroke="#8C5E0E" stroke-width="0.3">
                        <polygon transform="translate(-13, 35) scale(0.36)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                        <polygon transform="translate(0, 38) scale(0.46)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                        <polygon transform="translate(13, 35) scale(0.36)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                    </g>
                </g>
            </svg>
        </div>
    @elseif($isMedalRibbon)
        <!-- Navy Vertical Ribbon + Scalloped 24-point Rosette Medallion (V5 Medal Ribbon / CertiCraft Studio Reference) -->
        <div class="cert-ribbon-zone absolute top-0 left-12 z-20 pointer-events-none drop-shadow-xl">
            <svg width="156" height="344" viewBox="0 0 160 352" fill="none" xmlns="http://www.w3.org/2000/svg" class="overflow-visible">
                <defs>
                    <linearGradient id="v5Nav_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#051636" />
                        <stop offset="28%" stop-color="#0E346E" />
                        <stop offset="52%" stop-color="#092653" />
                        <stop offset="78%" stop-color="#0E346E" />
                        <stop offset="100%" stop-color="#041029" />
                    </linearGradient>
                    <linearGradient id="v5GoldRail_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#F9DF86" />
                        <stop offset="35%" stop-color="#D4A32C" />
                        <stop offset="70%" stop-color="#FFF0B0" />
                        <stop offset="100%" stop-color="#9C6B14" />
                    </linearGradient>
                    <radialGradient id="v5GoldRos_{{ $uid }}" cx="38%" cy="32%" r="68%">
                        <stop offset="0%" stop-color="#FFFBE0" />
                        <stop offset="28%" stop-color="#F7DC7C" />
                        <stop offset="62%" stop-color="#D4A32C" />
                        <stop offset="88%" stop-color="#A67418" />
                        <stop offset="100%" stop-color="#724D0C" />
                    </radialGradient>
                    <radialGradient id="v5NavyMed_{{ $uid }}" cx="40%" cy="35%" r="68%">
                        <stop offset="0%" stop-color="#123C7D" />
                        <stop offset="55%" stop-color="#092653" />
                        <stop offset="100%" stop-color="#04122B" />
                    </radialGradient>
                </defs>

                <!-- Vertical Navy Band with Gold Rails -->
                <rect x="44" y="0" width="72" height="175" fill="url(#v5Nav_{{ $uid }})" />
                <rect x="42.5" y="0" width="3.5" height="175" fill="url(#v5GoldRail_{{ $uid }})" />
                <rect x="48.5" y="0" width="1.4" height="175" fill="#F9DF86" opacity="0.8" />
                <rect x="114" y="0" width="3.5" height="175" fill="url(#v5GoldRail_{{ $uid }})" />
                <rect x="110.1" y="0" width="1.4" height="175" fill="#F9DF86" opacity="0.8" />

                <!-- Bottom 3 Hanging Swallowtail Tails -->
                <polygon points="48,228 74,234 52,324 38,308 22,318" fill="url(#v5Nav_{{ $uid }})" stroke="url(#v5GoldRail_{{ $uid }})" stroke-width="2" />
                <polygon points="86,234 112,228 138,318 122,308 108,324" fill="url(#v5Nav_{{ $uid }})" stroke="url(#v5GoldRail_{{ $uid }})" stroke-width="2" />
                <polygon points="64,230 96,230 98,322 80,340 62,322" fill="url(#v5GoldRos_{{ $uid }})" stroke="#8F6110" stroke-width="1.5" />
                <polygon points="69,232 91,232 92,318 80,331 68,318" fill="none" stroke="#FFF7CC" stroke-width="0.9" opacity="0.7" />

                <!-- Scalloped Metallic Gold Rosette Medallion -->
                <g transform="translate(80, 178)">
                    @php
                        $medallionScallops = '';
                        for ($i = 0; $i < 24; $i++) {
                            $a1 = ($i / 24) * M_PI * 2;
                            $aMid = (($i + 0.5) / 24) * M_PI * 2;
                            $rOut = 72;
                            $rIn = 64.5;
                            $x1 = cos($a1) * $rOut;
                            $y1 = sin($a1) * $rOut;
                            $x2 = cos($aMid) * $rIn;
                            $y2 = sin($aMid) * $rIn;
                            $medallionScallops .= ($i === 0 ? 'M' : 'L') . " {$x1} {$y1} L {$x2} {$y2} ";
                        }
                        $medallionScallops .= 'Z';
                    @endphp
                    <path d="{{ $medallionScallops }}" fill="url(#v5GoldRos_{{ $uid }})" stroke="#8A5D0F" stroke-width="1.4" />
                    <circle r="58" fill="none" stroke="#FFF4B8" stroke-width="1.4" opacity="0.85" />
                    <circle r="53" fill="url(#v5NavyMed_{{ $uid }})" stroke="url(#v5GoldRail_{{ $uid }})" stroke-width="2.4" />
                    <circle r="48.5" fill="none" stroke="#F9DF86" stroke-width="0.7" opacity="0.65" />

                    <!-- Royal 3-Point Crown -->
                    <g transform="translate(0, -33)">
                        <path d="M -10 5 L -12 -5 L -5 0 L 0 -8 L 5 0 L 12 -5 L 10 5 Z" fill="url(#v5GoldRail_{{ $uid }})" stroke="#784E0A" stroke-width="0.6" />
                        <rect x="-10" y="6" width="20" height="2.4" rx="0.8" fill="url(#v5GoldRail_{{ $uid }})" />
                        <circle cx="-12" cy="-6.2" r="1.4" fill="#FFF7C8" />
                        <circle cx="0" cy="-9.5" r="1.7" fill="#FFF7C8" />
                        <circle cx="12" cy="-6.2" r="1.4" fill="#FFF7C8" />
                    </g>

                    <!-- Center Gold K Monogram -->
                    <text x="0" y="2" text-anchor="middle" fill="url(#v5GoldRail_{{ $uid }})" font-size="30" font-weight="900" font-family="'Cinzel', serif" stroke="#784E0A" stroke-width="0.4">K</text>

                    <!-- Text Inside Medallion -->
                    <text x="0" y="15" text-anchor="middle" fill="#FFF6CE" font-size="7.4" font-weight="800" font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="0.8">KNEAYERNG</text>
                    <text x="0" y="23" text-anchor="middle" fill="#F4D16B" font-size="5.4" font-weight="700" font-family="'Plus Jakarta Sans', sans-serif" letter-spacing="1.1">PHONE SHOP</text>

                    <!-- Flanking Gold Laurels -->
                    @foreach([-1, 1] as $side)
                        <g transform="scale({{ $side }}, 1)" fill="url(#v5GoldRail_{{ $uid }})">
                            @for($idx = 0; $idx < 7; $idx++)
                                @php
                                    $angle = (-15 + $idx * 17) * (M_PI / 180);
                                    $lx = -38 * cos($angle * 0.45);
                                    $ly = 24 - $idx * 7.8;
                                    $rot = -35 + $idx * 12;
                                @endphp
                                <ellipse cx="{{ $lx }}" cy="{{ $ly }}" rx="4.2" ry="1.9" transform="rotate({{ $rot }}, {{ $lx }}, {{ $ly }})" />
                            @endfor
                        </g>
                    @endforeach

                    <!-- 3 Gold Stars -->
                    <g fill="#FFF2B2" stroke="#8C5E0E" stroke-width="0.3">
                        <polygon transform="translate(-13, 35) scale(0.36)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                        <polygon transform="translate(0, 38) scale(0.46)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                        <polygon transform="translate(13, 35) scale(0.36)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                    </g>
                </g>
            </svg>
        </div>
    @endif

    <!-- Employee ID Portrait (position / shape / size / zoom / offsets match CertiCraft Studio) -->
    @if($showPhoto && in_array($photoPosition, ['top-right', 'top-left']))
        <div class="cert-photo-zone absolute z-20 flex flex-col items-center pointer-events-auto"
             style="{{ $photoAnchorStyle }} {{ $photoFrameTransform }}">
            @include('admin.certificateManagement.partials.recipient-photo-badge', ['compact' => false])
        </div>
    @endif

    <!-- Main Certificate Content (Centered Balanced Layout) -->
    <div class="relative z-10 w-full h-full flex flex-col justify-between pt-7 pb-4 px-12">
        
        <!-- TOP ZONE: Logo + Flanking Laurels + KNEAYERNG PHONE SHOP + Baroque Fleur Divider -->
        <div class="cert-header-zone flex flex-col items-center">
            <div class="relative inline-flex flex-col items-center justify-center">
                <!-- Flanking Logo Side Ornaments (matches saved styling.logoOrnament) -->
                @if($logoOrnament !== 'none')
                    <div class="absolute -top-1 left-1/2 -translate-x-1/2 pointer-events-none">
                        @include('admin.certificateManagement.partials.logo-ornament', [
                            'style' => $logoOrnament,
                            'accentColor' => $accentGold,
                            'primaryColor' => $primaryNavy,
                            'uid' => $uid,
                            'className' => 'w-[310px] h-[132px] overflow-visible',
                        ])
                    </div>
                @endif

                <!-- Circular Issuer Logo -->
                <div class="w-16 h-16 rounded-full border-2 border-[#D4A32C] overflow-hidden shadow-md bg-white p-0.5 relative z-10">
                    <img src="{{ $certificate['issuer']['logoUrl'] ?? asset('assets/aistudio/unnamed_521b71dc.jpg') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                </div>

                <!-- Issuer Name & Dept -->
                <h4 class="mt-2 text-[21px] font-extrabold tracking-[0.06em] uppercase font-sans-clean leading-none" style="color: {{ $primaryNavy }};">
                    {{ $certificate['issuer']['name'] ?? 'KNEAYERNG' }}
                </h4>
                <div class="flex items-center justify-center gap-2.5 mt-1">
                    <div class="h-[1.5px] w-7 bg-gradient-to-r from-transparent via-[#C89328] to-[#D4A13A]"></div>
                    <p class="text-[11px] font-bold tracking-[0.18em] uppercase font-sans-clean leading-none" style="color: {{ $primaryNavy }};">
                        {{ $certificate['issuer']['department'] ?? 'PHONE SHOP' }}
                    </p>
                    <div class="h-[1.5px] w-7 bg-gradient-to-l from-transparent via-[#C89328] to-[#D4A13A]"></div>
                </div>

                <!-- Ornamental Divider Below Logo (matches saved Studio dividerOrnament style/width/color) -->
                @if($showHeaderDivider && $dividerOrnament !== 'none')
                    <div class="mt-2 mb-1 flex items-center justify-center overflow-visible">
                        @include('admin.certificateManagement.partials.logo-divider', [
                            'style' => $dividerOrnament,
                            'accentColor' => $dividerColor,
                            'primaryColor' => $primaryNavy,
                            'customUrl' => $customDividerUrl,
                            'width' => $dividerWidth ?: 280,
                            'height' => 26,
                            'scale' => $dividerScale,
                            'offsetY' => $dividerOffsetY,
                            'opacity' => $dividerOpacity,
                            'uid' => $uid,
                            'className' => 'w-[280px] h-[26px]',
                        ])
                    </div>
                @endif
            </div>
        </div>

        <!-- TITLE ZONE: Khmer Moul + Cinzel English + Diamond Rule + Swallowtail Presentation Banner -->
        <div class="flex flex-col items-center mt-0.5 space-y-1">
            <h2 class="font-moul text-[22px] text-[#092653] leading-snug">
                {{ $certificate['khmerTitle'] }}
            </h2>
            <h1 class="font-cinzel text-[22px] font-bold tracking-[0.06em] text-[#092653] uppercase leading-tight -mt-0.5">
                {{ $certificate['certificateTitle'] }}
            </h1>

            <!-- Diamond Rule Above Presentation Ribbon -->
            <div class="flex items-center justify-center w-[440px] mx-auto my-0.5">
                <div class="h-[1.2px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
                <div class="w-2 h-2 rotate-45 bg-gradient-to-br from-[#F4D16B] to-[#B38119] mx-1.5"></div>
                <div class="h-[1.2px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
            </div>

            <!-- Horizontal Gold Swallowtail Presentation Banner Ribbon -->
            <div class="cert-banner-zone relative inline-flex items-center justify-center my-0.5">
                <svg width="490" height="34" viewBox="0 0 490 34" class="overflow-visible drop-shadow-xs">
                    <defs>
                        <linearGradient id="bannerGoldGrad_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#BC8518" />
                            <stop offset="22%" stop-color="#E9C253" />
                            <stop offset="50%" stop-color="#D4A32C" />
                            <stop offset="78%" stop-color="#F0CB62" />
                            <stop offset="100%" stop-color="#B88116" />
                        </linearGradient>
                    </defs>
                    <line x1="36" y1="-2" x2="454" y2="-2" stroke="#D4A32C" stroke-width="0.9" stroke-opacity="0.75" />
                    <polygon points="0,2 490,2 476,17 490,32 0,32 14,17" fill="url(#bannerGoldGrad_{{ $uid }})" />
                    <line x1="16" y1="5" x2="474" y2="5" stroke="#FFF8DC" stroke-width="0.8" stroke-opacity="0.65" />
                    <line x1="16" y1="29" x2="474" y2="29" stroke="#8C5D0C" stroke-width="0.8" stroke-opacity="0.4" />
                    <text x="245" y="21.5" text-anchor="middle" fill="#FFFFFF" font-family="'Cinzel', serif" font-size="13.5px" font-weight="600" letter-spacing="3.2px">
                        {{ $certificate['bannerText'] ?? 'THIS CERTIFICATE IS PRESENTED TO' }}
                    </text>
                </svg>
            </div>
        </div>

        <!-- RECIPIENT ZONE: Khmer Moul + Cinzel English + Tapered Gold Diamond Divider -->
        <div class="flex flex-col items-center -mt-1">
            <div class="flex items-center justify-center gap-4">
                @if($showPhoto && $photoPosition === 'beside-name')
                    <div class="cert-photo-zone flex items-center justify-center">
                        @include('admin.certificateManagement.partials.recipient-photo-badge', ['compact' => true])
                    </div>
                @endif
                <div class="flex flex-col items-center">
                    <h2 class="font-moul text-[27px] text-[#092653] leading-tight mb-0.5">
                        {{ $certificate['khmerRecipientName'] }}
                    </h2>
                    <h2 class="font-cinzel text-[23px] font-bold tracking-[0.05em] uppercase text-[#092653] leading-none">
                        {{ $certificate['recipientName'] }}
                    </h2>
                </div>
            </div>

            <!-- Tapered Gold Divider with Centered Diamond -->
            <div class="flex items-center justify-center w-[460px] mx-auto mt-2">
                <div class="h-[1.4px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
                <div class="w-2.5 h-2.5 rotate-45 bg-gradient-to-br from-[#F4D16B] to-[#B38119] mx-1.5 shadow-xs"></div>
                <div class="h-[1.4px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
            </div>

            <!-- Organization Sub-Banner (Layout Model setting: organizationSubBanner / recipientTitle) -->
            @if($showOrganizationSubBanner && !empty($organizationSubBanner))
                <div class="flex items-center justify-center gap-3.5 w-[500px] mt-1.5">
                    <div class="h-[1.4px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
                    <span class="text-[11.5px] font-bold tracking-[0.2em] uppercase font-sans-clean whitespace-nowrap" style="color: {{ $primaryNavy }};">
                        {{ $organizationSubBanner }}
                    </span>
                    <div class="h-[1.4px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
                </div>
            @endif
        </div>

        <!-- OPTIONAL DIGITAL HR ACCREDITED HOURS BADGE (Layout Model setting) -->
        @if($showDigitalHoursBadge)
            <div class="mx-auto mt-1 px-4 py-0.5 rounded-xs border border-[#C89328]/50 bg-[#FDF8EC] flex items-center gap-3 text-[9.5px] leading-none"
                 style="color: {{ $primaryNavy }};">
                <span class="font-bold tracking-wider uppercase font-cinzel">
                    ⚜ Digital HR Accredited: {{ $digitalHr['trainingHours'] ?? $certificate['training_hours'] ?? 0 }}.0 Hrs
                </span>
                @if(!empty($digitalHr['employeeId']))
                    <span class="text-[#C89328]">|</span>
                    <span class="font-mono font-semibold">ID: {{ $digitalHr['employeeId'] }}</span>
                @endif
            </div>
        @endif

        <!-- RECOGNITION CITATIONS (Multiline Khmer & English) -->
        <div class="cert-citation-zone max-w-[760px] mx-auto space-y-1 px-4 -mt-1">
            @if(($visibility['showKhmerCitation'] ?? true) && !empty($certificate['khmerCitation']))
                <p class="font-kantumruy text-[12.5px] leading-[1.6] text-[#092653] font-medium whitespace-pre-line">
                    <span class="text-[#D4A32C] font-bold">« </span>{{ $certificate['khmerCitation'] }}<span class="text-[#D4A32C] font-bold"> »</span>
                </p>
            @endif
            @if(($visibility['showEnglishCitation'] ?? true) && !empty($certificate['citation']))
                <p class="font-cormorant text-[14px] leading-[1.35] italic text-slate-700 font-medium whitespace-pre-line">
                    {{ $certificate['citation'] }}
                </p>
            @endif
            @if(($visibility['showCitationClosing'] ?? true) && !empty($certificate['citationClosing']))
                <p class="font-cormorant text-[13.5px] leading-[1.3] italic text-slate-600 font-medium whitespace-pre-line">
                    {{ $certificate['citationClosing'] }}
                </p>
            @endif
        </div>

        <!-- Pre-Footer Diamond Divider -->
        <div class="flex items-center justify-center w-[540px] mx-auto -my-1">
            <div class="h-[1.2px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
            <div class="w-1.5 h-1.5 rotate-45 bg-[#D4A32C] mx-1 opacity-80"></div>
            <div class="w-2.5 h-2.5 rotate-45 bg-gradient-to-br from-[#F4D16B] to-[#B38119] mx-1 shadow-xs"></div>
            <div class="w-1.5 h-1.5 rotate-45 bg-[#D4A32C] mx-1 opacity-80"></div>
            <div class="h-[1.2px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]"></div>
        </div>

        <!-- 5-ZONE STATIC FOOTER: Date/ID | Signatory 1 | 3D Embossed Gold Seal | Signatory 2 | Verify QR -->
        <div class="w-full flex items-end justify-between px-6 pb-2 pt-0.5 relative z-10" style="min-height: 110px;">
            
            <!-- Zone 1: Date of Issue & Certificate ID Stacked -->
            <div class="cert-credential-zone w-[190px] flex items-end gap-3 shrink-0 pb-1">
                <div class="flex-1 min-w-0 flex flex-col gap-2 text-left">
                    <!-- Date -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <svg class="w-3.5 h-3.5 text-[#092653]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            <span class="text-[11px] font-bold tracking-[0.06em] font-sans-clean text-[#092653]">Date of Issue</span>
                        </div>
                        <div class="w-full h-[1.3px] bg-[#092653]"></div>
                        <div class="w-full mt-1">
                            <span class="text-[11.5px] font-semibold font-sans-clean tracking-wide block truncate text-[#092653]">
                                {{ $certificate['issueDate'] ?? '15 November 2026' }}
                            </span>
                        </div>
                    </div>

                    <!-- Certificate ID -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <svg class="w-3.5 h-3.5 text-[#092653]" viewBox="0 0 16 16" fill="none">
                                <rect x="1.5" y="2.5" width="13" height="11" rx="2" stroke="#092653" stroke-width="1.5" />
                                <text x="8" y="10.2" text-anchor="middle" fill="#092653" font-size="6.5" font-weight="800" font-family="sans-serif">ID</text>
                            </svg>
                            <span class="text-[10.5px] font-bold tracking-[0.06em] font-sans-clean text-[#092653]">Certificate ID</span>
                        </div>
                        <div class="w-full h-[1.3px] bg-[#092653]"></div>
                        <div class="w-full mt-1">
                            <span class="text-[11px] font-mono font-semibold tracking-tight block truncate text-[#092653]">
                                {{ $certificate['credentialId'] ?? 'KY-PS-VFY-2026-002' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Vertical Metallic Gold Bar -->
                <div class="w-[2px] h-[78px] rounded-full bg-gradient-to-b from-[#D4A32C]/40 via-[#D4A32C] to-[#D4A32C]/40 shrink-0"></div>
            </div>

            <!-- Zone 2: Signatory #1 (CHHOEUN REACH) -->
            @php
                $signatoriesList = array_values($certificate['signatories'] ?? []);
                $sigCount = count($signatoriesList);
                $sig1 = $signatoriesList[0] ?? [
                    'name' => 'CHHOEUN REACH',
                    'role' => 'IT Manager / Branch Manager',
                    'organization' => 'Admin • HR • System • Stock',
                    'signatureStyle' => 'alex-brush',
                ];
                $hasSig2 = $sigCount >= 2;
                $sig2 = $hasSig2 ? $signatoriesList[1] : null;
                $sealEnabled = !empty($sealData['enabled']) && ($sealData['style'] ?? '') !== 'none';
            @endphp
            <div class="cert-signature-zone cert-signature-left w-[180px] flex flex-col items-center text-center pb-1">
                <div class="h-12 flex items-end justify-center w-full mb-1">
                    @if(!empty($sig1['customSignatureData']))
                        <img src="{{ $sig1['customSignatureData'] }}" alt="Signature" class="max-h-12 object-contain" />
                    @else
                        <span class="font-signature text-[29px] leading-none select-none text-[#092653]">
                            {{ preg_replace('/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/', '', $sig1['name'] ?? 'CHHOEUN REACH') }}
                        </span>
                    @endif
                </div>
                <div class="w-full h-[1.4px] bg-[#092653]"></div>
                <div class="mt-1.5 text-[12.5px] font-bold tracking-[0.04em] uppercase font-sans-clean text-[#092653] leading-tight">
                    {{ $sig1['name'] ?? 'CHHOEUN REACH' }}
                </div>
                <div class="mt-0.5 text-[10.5px] font-semibold font-sans-clean text-[#092653] leading-tight">
                    {{ $sig1['role'] ?? 'IT Manager / Branch Manager' }}
                </div>
                @if(!empty($sig1['organization']))
                <div class="mt-0.5 text-[9px] font-medium font-sans-clean text-slate-500 tracking-wide leading-tight">
                    {{ $sig1['organization'] }}
                </div>
                @endif
            </div>

            <!-- Zone 3: Center 3D Embossed Metallic Gold Seal -->
            @if($sealEnabled)
            <div class="cert-seal-zone w-[130px] h-[130px] flex items-center justify-center shrink-0 -mb-2">
                <svg width="128" height="128" viewBox="0 0 160 160" class="overflow-visible drop-shadow-xl">
                    <defs>
                        <radialGradient id="seal3DGold_{{ $uid }}" cx="38%" cy="32%" r="65%">
                            <stop offset="0%" stop-color="#FFFEE8" />
                            <stop offset="25%" stop-color="#FDE68A" />
                            <stop offset="55%" stop-color="#D97706" />
                            <stop offset="85%" stop-color="#92400E" />
                            <stop offset="100%" stop-color="#451A03" />
                        </radialGradient>
                        <linearGradient id="satinNavyLeft_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#04112A" />
                            <stop offset="42%" stop-color="#0C2A60" />
                            <stop offset="78%" stop-color="#081E47" />
                            <stop offset="100%" stop-color="#030D21" />
                        </linearGradient>
                        <linearGradient id="satinNavyRight_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#030D21" />
                            <stop offset="28%" stop-color="#0C2A60" />
                            <stop offset="72%" stop-color="#081E47" />
                            <stop offset="100%" stop-color="#04112A" />
                        </linearGradient>
                    </defs>
                    <!-- Satin Ribbon Tails -->
                    <polygon points="56,112 30,156 50,148 58,162 74,116" fill="url(#satinNavyLeft_{{ $uid }})" stroke="#FDE68A" stroke-width="1.4" />
                    <polygon points="86,116 102,162 110,148 130,156 104,112" fill="url(#satinNavyRight_{{ $uid }})" stroke="#FDE68A" stroke-width="1.4" />
                    <!-- 24-point Scalloped Gold Burst Rim -->
                    @php
                        $burstPath = '';
                        for ($i = 0; $i < 24; $i++) {
                            $a1 = ($i * 360 / 24) * (M_PI / 180);
                            $aMid = (($i + 0.5) * 360 / 24) * (M_PI / 180);
                            $a2 = (($i + 1) * 360 / 24) * (M_PI / 180);
                            $rIn = 65;
                            $rOut = 74;
                            $x1 = 80 + $rIn * cos($a1);
                            $y1 = 80 + $rIn * sin($a1);
                            $xm = 80 + $rOut * cos($aMid);
                            $ym = 80 + $rOut * sin($aMid);
                            $x2 = 80 + $rIn * cos($a2);
                            $y2 = 80 + $rIn * sin($a2);
                            $burstPath .= ($i === 0 ? "M $x1 $y1" : "") . " Q $xm $ym $x2 $y2 ";
                        }
                        $burstPath .= 'Z';
                    @endphp
                    <path d="{{ $burstPath }}" fill="url(#seal3DGold_{{ $uid }})" stroke="#9A6B16" stroke-width="1" />
                    <circle cx="80" cy="80" r="60" fill="url(#seal3DGold_{{ $uid }})" stroke="#FFF4CC" stroke-width="1.8" stroke-opacity="0.9" />
                    <circle cx="80" cy="80" r="54" fill="none" stroke="#855814" stroke-width="1" stroke-opacity="0.55" />
                    <circle cx="80" cy="80" r="46" fill="none" stroke="#FFF4CC" stroke-width="1.2" stroke-opacity="0.7" />
                    <circle cx="80" cy="80" r="42" fill="#926213" fill-opacity="0.25" stroke="#92400E" stroke-width="1.2" />

                    <!-- Crossed Laurel Wreath -->
                    <g transform="translate(80, 80)" fill="#FFF4B8" stroke="#784C0A" stroke-width="0.5">
                        @for($li = 0; $li < 7; $li++)
                            @php
                                $py = 20 - $li * 6.5;
                                $pxL = -29 + sin(($li / 6) * M_PI) * -4 + ($li === 0 ? 12 : ($li === 6 ? 10 : 0));
                                $pxR = 29 + sin(($li / 6) * M_PI) * 4 - ($li === 0 ? 12 : ($li === 6 ? 10 : 0));
                                $rotL = -35 + $li * 11;
                                $rotR = 35 - $li * 11;
                            @endphp
                            <ellipse cx="{{ $pxL }}" cy="{{ $py }}" rx="5.5" ry="2.3" transform="rotate({{ $rotL }}, {{ $pxL }}, {{ $py }})" />
                            <ellipse cx="{{ $pxR }}" cy="{{ $py }}" rx="5.5" ry="2.3" transform="rotate({{ $rotR }}, {{ $pxR }}, {{ $py }})" />
                        @endfor
                        <path d="M 6 29 Q -28 24 -30 -16" fill="none" stroke="#FFF4B8" stroke-width="1.4" />
                        <path d="M -6 29 Q 28 24 30 -16" fill="none" stroke="#FFF4B8" stroke-width="1.4" />
                        <!-- 3 Arched Stars Above Crown -->
                        <polygon transform="translate(-11, -24) scale(0.32)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" fill="#FFF9D6" stroke="#784C0A" stroke-width="0.4" />
                        <polygon transform="translate(0, -28) scale(0.38)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" fill="#FFF9D6" stroke="#784C0A" stroke-width="0.4" />
                        <polygon transform="translate(11, -24) scale(0.32)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" fill="#FFF9D6" stroke="#784C0A" stroke-width="0.4" />
                        <!-- Royal 3-Point Crown -->
                        <g transform="translate(0, -2)">
                            <path d="M -15 9 L -18 -8 L -8 0 L 0 -13 L 8 0 L 18 -8 L 15 9 Z" fill="#FFF7C8" stroke="#784C0A" stroke-width="0.9" />
                            <rect x="-15" y="10.5" width="30" height="3.8" rx="1" fill="#FFF7C8" stroke="#784C0A" stroke-width="0.8" />
                            <circle cx="-18" cy="-10" r="2.2" fill="#FFFBEB" stroke="#784C0A" stroke-width="0.6" />
                            <circle cx="0" cy="-15" r="2.6" fill="#FFFBEB" stroke="#784C0A" stroke-width="0.6" />
                            <circle cx="18" cy="-10" r="2.2" fill="#FFFBEB" stroke="#784C0A" stroke-width="0.6" />
                        </g>
                    </g>
                </svg>
            </div>
            @else
            <!-- Seal hidden: spacer keeps balanced alignment between signatories -->
            <div class="cert-seal-spacer w-[60px] shrink-0"></div>
            @endif

            <!-- Zone 4: Signatory #2 (SOPHEA MENG) -->
            @if($hasSig2 && $sig2)
            <div class="cert-signature-zone cert-signature-right w-[180px] flex flex-col items-center text-center pb-1">
                <div class="h-12 flex items-end justify-center w-full mb-1">
                    @if(!empty($sig2['customSignatureData']))
                        <img src="{{ $sig2['customSignatureData'] }}" alt="Signature" class="max-h-12 object-contain" />
                    @else
                        <span class="font-script text-[29px] leading-none select-none text-[#092653]">
                            {{ preg_replace('/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/', '', $sig2['name'] ?? 'SOPHEA MENG') }}
                        </span>
                    @endif
                </div>
                <div class="w-full h-[1.4px] bg-[#092653]"></div>
                <div class="mt-1.5 text-[12.5px] font-bold tracking-[0.04em] uppercase font-sans-clean text-[#092653] leading-tight">
                    {{ $sig2['name'] ?? 'SOPHEA MENG' }}
                </div>
                <div class="mt-0.5 text-[10.5px] font-semibold font-sans-clean text-[#092653] leading-tight">
                    {{ $sig2['role'] ?? 'HR Manager / HR Supervisor' }}
                </div>
                @if(!empty($sig2['organization']))
                <div class="mt-0.5 text-[9px] font-medium font-sans-clean text-slate-500 tracking-wide leading-tight">
                    {{ $sig2['organization'] }}
                </div>
                @endif
            </div>
            @endif

            <!-- Zone 5: Verify QR Code + Blue Ribbon Badge -->
            @if($showQrCode)
            <div class="cert-qr-zone w-[130px] flex flex-col items-end justify-end shrink-0 pb-1">
                <div class="flex flex-col items-center">
                    <div class="p-1 rounded-lg bg-white border-[1.5px] border-[#D4A32C] shadow-xs" style="width: 62px; height: 62px;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(route('certificates.verify', $certificate['credentialId'] ?? $certificate['id'])) }}" alt="Verify QR" class="w-full h-full object-contain">
                    </div>
                    <div class="mt-1 px-2 py-0.5 rounded-full text-[8px] font-bold tracking-[0.08em] uppercase font-sans-clean text-white shadow-xs border border-[#D4A32C] whitespace-nowrap bg-[#092653]">
                        VERIFY CERTIFICATE
                    </div>
                </div>
            </div>
            @else
            <div class="cert-qr-spacer w-[60px] shrink-0"></div>
            @endif
        </div>

    </div>
</div>
