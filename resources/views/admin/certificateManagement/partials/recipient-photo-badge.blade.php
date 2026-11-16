@php
    // Faithful Blade port of CertiCraft Studio's renderProfilePhotoBadge() (src/components/CertificateRenderer.tsx:232-311)
    $compact = $compact ?? false;
    $photoSize = (int) ($certificate['recipientPhotoSize'] ?? 86);
    if ($photoSize <= 0) $photoSize = 86;

    $photoShape = $certificate['recipientPhotoShape'] ?? ($isVerifyV2 ?? false ? 'circle' : 'rounded-portrait');
    $widthPx = $compact ? (int) round($photoSize * 0.85) : $photoSize;
    $heightPx = ($photoShape === 'rounded-portrait') ? (int) round($widthPx * 1.18) : $widthPx;

    $radiusOuter = $photoShape === 'circle' ? '9999px' : ($photoShape === 'rounded-portrait' ? '12px' : '8px');
    $radiusInner = $photoShape === 'circle' ? '9999px' : ($photoShape === 'rounded-portrait' ? '10px' : '6px');

    $photoZoom = (float) ($certificate['recipientPhotoZoom'] ?? 1);
    $photoOffsetX = $certificate['recipientPhotoOffsetX'] ?? 50;
    $photoOffsetY = $certificate['recipientPhotoOffsetY'] ?? 25;

    $badgeImgStyle = 'object-position: ' . $photoOffsetX . '% ' . $photoOffsetY . '%;'
        . ($photoZoom != 1 ? ' transform: scale(' . $photoZoom . ');' : '');

    $badgePrimary = $primaryNavy ?? ($styling['primaryColor'] ?? '#092653');
    $badgeAccent = $accentGold ?? ($styling['accentColor'] ?? '#D4A32C');
    $badgeLanguage = $certificate['languageMode'] ?? 'bilingual';

    $photoUrl = $certificate['recipientPhotoUrl'] ?? null;
    $hasPhoto = !empty($photoUrl) && !str_contains($photoUrl, 'khun_sreyni_portrait.jpg');

    $vis = $visibility ?? ($certificate['visibility'] ?? []);
    $employeeId = $certificate['digitalHr']['employeeId'] ?? ($certificate['employeeId'] ?? 'EMP-KY-TL-2026-02');
    $idPrefix = $certificate['employeeIdPrefix'] ?? 'ID:';
@endphp

<div class="flex flex-col items-center gap-1 select-none pointer-events-auto">
    <!-- Golden Gradient Framed Photo Card -->
    <div class="relative block shadow-md transition-transform"
         style="width: {{ $widthPx }}px; height: {{ $heightPx }}px; border-radius: {{ $radiusOuter }}; padding: 3px; background: linear-gradient(135deg, #A87518 0%, #F7DE8A 48%, {{ $badgeAccent }} 100%);">
        <div class="w-full h-full overflow-hidden bg-gradient-to-b from-[#FFFDF8] to-[#F3EAD3] border border-white/80 flex flex-col items-center justify-center relative"
             style="border-radius: {{ $radiusInner }};">
            @if($hasPhoto)
                <img src="{{ $photoUrl }}" alt="{{ $certificate['recipientName'] ?? 'Employee Profile' }}"
                     class="w-full h-full object-cover" style="{{ $badgeImgStyle }}"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="flex-col items-center justify-center text-center px-1 leading-none" style="display: none;">
                    <svg class="{{ $compact ? 'w-5 h-5' : 'w-6 h-6' }} opacity-75 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="{{ $badgePrimary }}" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="{{ $compact ? 'text-[6px]' : 'text-[7px]' }} font-bold uppercase tracking-wider leading-tight opacity-85 font-sans-clean"
                          style="color: {{ $badgePrimary }};">
                        {{ $badgeLanguage === 'khmer' ? 'រូបបុគ្គលិក' : 'Employee' }}
                    </span>
                </div>
            @else
                <div class="flex flex-col items-center justify-center text-center px-1 leading-none">
                    <svg class="{{ $compact ? 'w-5 h-5' : 'w-6 h-6' }} opacity-75 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="{{ $badgePrimary }}" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="{{ $compact ? 'text-[6px]' : 'text-[7px]' }} font-bold uppercase tracking-wider leading-tight opacity-85 font-sans-clean"
                          style="color: {{ $badgePrimary }};">
                        {{ $badgeLanguage === 'khmer' ? 'រូបបុគ្គលិក' : 'UPLOAD PHOTO' }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Employee Profile ID Pill (Exact Studio renderProfilePhotoBadge styling) -->
    @if(!$compact && ($vis['showEmployeeIdBadge'] ?? true) !== false && !empty($employeeId))
        <div class="px-2 py-0.5 rounded-full bg-white/95 border shadow-xs text-[8px] font-mono font-bold tracking-wider whitespace-nowrap"
             style="color: {{ $badgePrimary }}; border-color: rgba(212, 161, 58, 0.75);">
            {{ $idPrefix ? $idPrefix . ' ' : '' }}{{ $employeeId }}
        </div>
    @endif
</div>
