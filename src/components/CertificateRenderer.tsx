import React, { useEffect, useRef, useState } from 'react';
import { Calendar, Camera, Move, User } from 'lucide-react';
import { CertificateData } from '../types/certificate';
import { CertificateBorders } from './CertificateBorders';
import { CertificateSeal } from './CertificateSeal';
import { IssuerLogo, KMonogramCrest } from './IssuerLogo';
import { LogoDividerOrnament, LogoFlankingOrnament } from './LogoOrnaments';
import { CertificateSignatures } from './CertificateSignatures';
import { PresentationBannerRibbon, WatermarkPatternLayer } from './CertificatePatterns';
import { generateQrDataUrl } from '../utils/qrGenerator';
import { getFieldInlineStyle } from '../utils/typography';

interface CertificateRendererProps {
  data: CertificateData;
  scale?: number;
  enableShimmer?: boolean;
  onEditField?: (fieldKey: string) => void;
  onOpenSignatureModal?: (index: number) => void;
  onOpenVerifyModal?: () => void;
  onUpdateRecipientPhoto?: (dataUrl: string) => void;
  onUpdatePhotoFrameOffset?: (offsetX: number, offsetY: number) => void;
}

export const CertificateRenderer: React.FC<CertificateRendererProps> = ({
  data,
  scale = 1,
  enableShimmer = false,
  onEditField,
  onOpenSignatureModal,
  onOpenVerifyModal,
  onUpdateRecipientPhoto,
  onUpdatePhotoFrameOffset,
}) => {
  const [qrDataUrl, setQrDataUrl] = useState<string>('');
  const [dragPhotoOffset, setDragPhotoOffset] = useState<{ x: number; y: number } | null>(null);
  const didDragPhotoRef = useRef(false);

  useEffect(() => {
    if (data.showQrCode && data.qrVerificationUrl) {
      generateQrDataUrl(
        data.qrVerificationUrl,
        data.theme === 'tech-obsidian'
          ? '#06B6D4'
          : data.layoutTemplate === 'executive-verify' ||
            data.layoutTemplate === 'executive-verify-v2' ||
            data.layoutTemplate === 'executive-medal-ribbon' ||
            data.layoutTemplate === 'executive-imperial-sash'
          ? '#092653'
          : data.theme === 'navy-gold'
          ? '#0A224E'
          : '#2D1328',
        '#00000000'
      ).then(setQrDataUrl);
    }
  }, [data.showQrCode, data.qrVerificationUrl, data.theme, data.layoutTemplate]);

  const isLandscape = data.orientation === 'landscape';
  const isDark = data.theme === 'tech-obsidian';
  const isExecutiveMedalRibbon = data.layoutTemplate === 'executive-medal-ribbon';
  const isExecutiveImperialSash = data.layoutTemplate === 'executive-imperial-sash';
  const isExecutiveVerifyV2 = data.layoutTemplate === 'executive-verify-v2';
  const isExecutiveVerify =
    data.layoutTemplate === 'executive-verify' ||
    isExecutiveVerifyV2 ||
    isExecutiveMedalRibbon ||
    isExecutiveImperialSash;
  const hasStackedDateCertId = isExecutiveVerifyV2 || isExecutiveMedalRibbon || isExecutiveImperialSash;
  const isExecutiveClone = data.layoutTemplate === 'executive-navy-ribbon';

  // Font styling
  let displayFontClass = 'font-cinzel';
  if (data.styling.fontDisplay === 'playfair') displayFontClass = 'font-playfair';
  if (data.styling.fontDisplay === 'cormorant') displayFontClass = 'font-cormorant';
  if (data.styling.fontDisplay === 'sans') displayFontClass = 'font-sans-clean';
  if (data.styling.fontDisplay === 'khmer') displayFontClass = 'font-khmer-moul';

  let bodyFontClass = 'font-cormorant';
  if (data.styling.fontBody === 'playfair') bodyFontClass = 'font-playfair';
  if (data.styling.fontBody === 'sans') bodyFontClass = 'font-sans-clean';
  if (data.styling.fontBody === 'khmer') bodyFontClass = 'font-khmer-sans';

  const vis = data.visibility || {};

  const textColor = isDark
    ? '#F1F5F9'
    : isExecutiveVerify
    ? '#092653'
    : isExecutiveClone
    ? '#0A224E'
    : '#1E1B24';
  const defaultMuted = isDark
    ? '#94A3B8'
    : isExecutiveVerify
    ? '#16305C'
    : isExecutiveClone
    ? '#0F264A'
    : '#4A4154';
  const mutedTextColor = data.styling.secondaryTextColor || defaultMuted;
  const subtextColor = isDark
    ? '#64748B'
    : isExecutiveVerify
    ? '#1B3A6B'
    : isExecutiveClone
    ? '#16305C'
    : '#716580';

  const primaryNavy = data.styling.primaryColor || (isExecutiveVerify ? '#092653' : '#0A224E');
  const signatureInk = data.styling.signatureInkColor || primaryNavy;
  const allSignatories = data.signatories || [];
  const indexedVisibleSignatories =
    vis.showSignatories === false
      ? []
      : allSignatories
          .map((sig, originalIndex) => ({ sig, originalIndex }))
          .filter(({ sig }) => !sig.hidden);
  const primaryVisibleSig = indexedVisibleSignatories[0];

  const showHangingMedal =
    vis.showHangingMedal ?? (isExecutiveMedalRibbon || isExecutiveImperialSash);
  const useGoldHangingSash =
    data.hangingMedalStyle === 'gold-sash'
      ? true
      : data.hangingMedalStyle === 'navy-sash'
      ? false
      : isExecutiveImperialSash;
  const hangingMedalScale = data.hangingMedalScale ?? 1;

  const showTitleLaurels = vis.showTitleLaurels ?? isExecutiveImperialSash;
  const showPreFooterDivider = vis.showPreFooterDivider ?? isExecutiveMedalRibbon;
  const idPrefix = data.employeeIdPrefix ?? 'ID:';

  const hasProfilePhoto =
    data.showRecipientPhoto === true ||
    (Boolean(data.recipientPhotoUrl) && data.showRecipientPhoto !== false);
  const photoPosition = data.recipientPhotoPosition || 'top-right';
  const photoShape = data.recipientPhotoShape || 'circle';
  const photoSize = data.recipientPhotoSize || 86;
  const photoZoom = data.recipientPhotoZoom || 1;
  const photoOffsetX = data.recipientPhotoOffsetX ?? 50;
  const photoOffsetY = data.recipientPhotoOffsetY ?? 25;
  const persistedFrameOffsetX = data.recipientPhotoFrameOffsetX ?? 0;
  const persistedFrameOffsetY = data.recipientPhotoFrameOffsetY ?? 0;
  const activeFrameOffsetX = dragPhotoOffset ? dragPhotoOffset.x : persistedFrameOffsetX;
  const activeFrameOffsetY = dragPhotoOffset ? dragPhotoOffset.y : persistedFrameOffsetY;
  const photoFrameTransformStyle: React.CSSProperties | undefined =
    activeFrameOffsetX !== 0 || activeFrameOffsetY !== 0
      ? { transform: `translate(${activeFrameOffsetX}px, ${activeFrameOffsetY}px)` }
      : undefined;

  const handlePhotoPointerDown = (e: React.PointerEvent<HTMLDivElement>) => {
    if (e.button !== 0 || !onUpdatePhotoFrameOffset) return;
    if ((e.target as HTMLElement)?.tagName === 'INPUT') return;

    const startClientX = e.clientX;
    const startClientY = e.clientY;
    const baseX = persistedFrameOffsetX;
    const baseY = persistedFrameOffsetY;
    const zoomFactor = scale || 1;
    let latestX = baseX;
    let latestY = baseY;
    didDragPhotoRef.current = false;

    const clamp = (val: number, min: number, max: number) => Math.max(min, Math.min(max, val));

    const handlePointerMove = (ev: PointerEvent) => {
      const dist = Math.hypot(ev.clientX - startClientX, ev.clientY - startClientY);
      if (dist > 4) {
        didDragPhotoRef.current = true;
      }
      if (didDragPhotoRef.current) {
        const dx = (ev.clientX - startClientX) / zoomFactor;
        const dy = (ev.clientY - startClientY) / zoomFactor;
        latestX = clamp(Math.round(baseX + dx), -280, 120);
        latestY = clamp(Math.round(baseY + dy), -70, 260);
        setDragPhotoOffset({ x: latestX, y: latestY });
      }
    };

    const handlePointerUp = () => {
      window.removeEventListener('pointermove', handlePointerMove);
      window.removeEventListener('pointerup', handlePointerUp);
      window.removeEventListener('pointercancel', handlePointerUp);
      if (didDragPhotoRef.current) {
        setDragPhotoOffset(null);
        onUpdatePhotoFrameOffset(latestX, latestY);
        setTimeout(() => {
          didDragPhotoRef.current = false;
        }, 0);
      }
    };

    window.addEventListener('pointermove', handlePointerMove);
    window.addEventListener('pointerup', handlePointerUp);
    window.addEventListener('pointercancel', handlePointerUp);
  };

  const handlePhotoClickCapture = (e: React.MouseEvent) => {
    if (didDragPhotoRef.current) {
      e.preventDefault();
      e.stopPropagation();
    }
  };

  // Automatic font-size shrink for long recipient names
  const baseVerifyRecipientPx = data.styling.recipientFontSize || 40;
  const nameLen = (data.recipientName || '').trim().length;
  const autoVerifyRecipientPx =
    nameLen > 28
      ? Math.max(24, Math.round(baseVerifyRecipientPx * 0.68))
      : nameLen > 20
      ? Math.max(28, Math.round(baseVerifyRecipientPx * 0.82))
      : baseVerifyRecipientPx;

  const handleInlinePhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      const result = ev.target?.result as string;
      if (result) {
        onUpdateRecipientPhoto?.(result);
      }
    };
    reader.readAsDataURL(file);
    e.target.value = '';
  };

  const renderProfilePhotoBadge = (compact = false) => {
    const widthPx = compact ? Math.round(photoSize * 0.85) : photoSize;
    const heightPx =
      photoShape === 'rounded-portrait' ? Math.round(widthPx * 1.18) : widthPx;
    const radiusClass =
      photoShape === 'circle'
        ? 'rounded-full'
        : photoShape === 'rounded-portrait'
        ? 'rounded-xl'
        : 'rounded-lg';

    return (
      <div className="flex flex-col items-center gap-1">
        <label
          className={`relative block cursor-pointer group select-none ${radiusClass} p-[3px] shadow-md transition-transform hover:scale-[1.03]`}
          style={{
            width: `${widthPx}px`,
            height: `${heightPx}px`,
            background: `linear-gradient(135deg, #A87518 0%, #F7DE8A 48%, ${
              data.styling.accentColor || '#D4A13A'
            } 100%)`,
          }}
          title="Click to upload or update employee profile photo"
        >
          <input
            type="file"
            accept="image/*"
            onChange={handleInlinePhotoUpload}
            className="hidden"
          />
          <div
            className={`w-full h-full ${radiusClass} overflow-hidden bg-gradient-to-b from-[#FFFDF8] to-[#F3EAD3] border border-white/80 flex flex-col items-center justify-center relative`}
          >
            {data.recipientPhotoUrl ? (
              <img
                src={data.recipientPhotoUrl}
                alt={data.recipientName || 'Employee Profile'}
                className="w-full h-full object-cover"
                style={{
                  objectPosition: `${photoOffsetX}% ${photoOffsetY}%`,
                  transform: photoZoom !== 1 ? `scale(${photoZoom})` : undefined,
                }}
              />
            ) : (
              <div className="flex flex-col items-center justify-center text-center px-1">
                <User
                  className="w-6 h-6 opacity-75"
                  style={{ color: data.styling.primaryColor || '#0A224E' }}
                  strokeWidth={1.7}
                />
                <span
                  className="text-[7px] font-bold uppercase tracking-wider mt-0.5 leading-tight opacity-85"
                  style={{ color: data.styling.primaryColor || '#0A224E' }}
                >
                  {data.languageMode === 'khmer' ? 'រូបបុគ្គលិក' : 'Employee'}
                </span>
              </div>
            )}

            {/* Hover Upload Overlay (hidden when printing/exporting) */}
            <div className="no-print absolute inset-0 bg-slate-950/55 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white">
              <Camera className="w-4 h-4 mb-0.5" />
              <span className="text-[8px] font-bold uppercase tracking-wider leading-none">
                Update
              </span>
            </div>
          </div>
        </label>

        {!compact && vis.showEmployeeIdBadge !== false && data.digitalHr?.employeeId && (
          <div
            onClick={() => onEditField?.('digitalHr')}
            className="px-2 py-0.5 rounded-full bg-white/95 border border-[#D4A13A]/70 shadow-2xs text-[8px] font-mono-code font-bold tracking-wider cursor-pointer hover:border-[#D4A13A] whitespace-nowrap"
            style={{ color: data.styling.primaryColor || '#0A224E' }}
            title="Click to edit Employee Profile ID"
          >
            {idPrefix ? `${idPrefix} ` : ''}{data.digitalHr.employeeId}
          </div>
        )}
      </div>
    );
  };

  const canvasWidth = isLandscape ? 1123 : 794;
  const canvasHeight = isLandscape ? 794 : 1123;
  const supportsCssZoom =
    typeof CSS !== 'undefined' && typeof CSS.supports === 'function' && CSS.supports('zoom', '0.8');

  return (
    <div
      className="certificate-scale-wrapper relative select-none"
      style={
        supportsCssZoom
          ? {
              width: `${canvasWidth}px`,
              height: `${canvasHeight}px`,
              zoom: scale,
            }
          : {
              width: `${Math.round(canvasWidth * scale)}px`,
              height: `${Math.round(canvasHeight * scale)}px`,
            }
      }
    >
      <div
        id="certificate-print-node"
        data-orientation={data.orientation}
        className="certificate-root-element relative shadow-2xl transition-colors duration-300 print-certificate-container flex flex-col justify-between overflow-hidden"
        style={{
          width: `${canvasWidth}px`,
          height: `${canvasHeight}px`,
          ...(supportsCssZoom
            ? {}
            : {
                transform: `scale(${scale})`,
                transformOrigin: 'top left',
              }),
          backgroundColor:
            data.styling.bgColor ||
            (isExecutiveVerify || isExecutiveClone ? '#FCFBF7' : '#FFFDF8'),
          backgroundImage: (() => {
            if (isDark) {
              return 'radial-gradient(ellipse at 50% 40%, #172033 0%, #0B1120 100%)';
            }
            const bg = (data.styling.bgColor || '').toUpperCase();
            const isDefaultPaper = !bg || bg === '#FCFBF7' || bg === '#FFFDF8' || bg === '#FDFCF8';
            if (!isDefaultPaper) {
              return 'radial-gradient(ellipse at 50% 45%, rgba(255,255,255,0.28) 0%, rgba(255,255,255,0) 70%, rgba(15,23,42,0.04) 100%)';
            }
            return isExecutiveVerify || isExecutiveClone
              ? 'radial-gradient(ellipse at 50% 45%, #FFFFFF 0%, #FDFCF8 68%, #F8F4EA 100%)'
              : 'radial-gradient(ellipse at 50% 35%, #FFFFFF 0%, #FFFDF8 65%, #FBF6EB 100%)';
          })(),
          color: textColor,
        }}
      >
        {/* Luxury Gold Shimmer Light Sweep Effect when enabled */}
        {(data.styling.enableShimmer ?? enableShimmer) && (
          <div
            className="shimmer-overlay no-print absolute inset-0 pointer-events-none z-30 opacity-40 mix-blend-overlay"
            style={{
              background: 'linear-gradient(105deg, transparent 20%, rgba(255, 235, 175, 0.6) 45%, rgba(255, 255, 255, 0.8) 50%, rgba(255, 235, 175, 0.6) 55%, transparent 80%)',
              backgroundSize: '200% 200%',
              animation: 'shimmer 4s infinite linear',
            }}
          />
        )}

        {/* Masterwork Vector Certificate Borders */}
        <CertificateBorders
          style={data.styling.borderStyle}
          primaryColor={data.styling.primaryColor}
          accentColor={data.styling.accentColor}
          isDarkTheme={isDark}
          orientation={data.orientation}
        />

        {/* =========================================================================
            BRANCH C: EXECUTIVE VERIFY CERTIFICATE TEMPLATE (1123x794 A4 Landscape)
        ========================================================================= */}
        {isExecutiveVerify ? (
          <>
            {/* Background Watermark & Guilloche Security Pattern */}
            {data.styling.showWatermark && (
              <WatermarkPatternLayer
                type={data.styling.watermarkType || 'laurel'}
                opacity={data.styling.watermarkOpacity || 0.14}
                primaryColor={primaryNavy}
                accentColor={data.styling.accentColor || '#D4A32C'}
                issuerName={data.issuer.name}
                issuerDepartment={data.issuer.department}
              />
            )}

            {/* Top-Left Navy & Gold Hanging Ribbon + Ornate Scalloped Medal Crest */}
            {showHangingMedal && !useGoldHangingSash && (
              <div
                onClick={() => onEditField?.('seal')}
                className={`absolute top-0 z-20 cursor-pointer group transition-transform hover:scale-[1.02] origin-top ${
                  isLandscape ? 'left-[50px]' : 'left-[28px]'
                }`}
                style={hangingMedalScale !== 1 ? { transform: `scale(${hangingMedalScale})` } : undefined}
                title="Click to customize Official Medal & Seal"
              >
                <svg
                  width={isLandscape ? 156 : 114}
                  height={isLandscape ? 344 : 252}
                  viewBox="0 0 160 352"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  className="drop-shadow-xl overflow-visible"
                >
                  <defs>
                    <linearGradient id="v5RibbonNavy" x1="0%" y1="0%" x2="100%" y2="0%">
                      <stop offset="0%" stopColor="#051636" />
                      <stop offset="28%" stopColor="#0E346E" />
                      <stop offset="52%" stopColor="#092653" />
                      <stop offset="78%" stopColor="#0E346E" />
                      <stop offset="100%" stopColor="#041029" />
                    </linearGradient>
                    <linearGradient id="v5GoldRail" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stopColor="#F9DF86" />
                      <stop offset="35%" stopColor="#D4A32C" />
                      <stop offset="70%" stopColor="#FFF0B0" />
                      <stop offset="100%" stopColor="#9C6B14" />
                    </linearGradient>
                    <radialGradient id="v5GoldRosette" cx="38%" cy="32%" r="68%">
                      <stop offset="0%" stopColor="#FFFBE0" />
                      <stop offset="28%" stopColor="#F7DC7C" />
                      <stop offset="62%" stopColor="#D4A32C" />
                      <stop offset="88%" stopColor="#A67418" />
                      <stop offset="100%" stopColor="#724D0C" />
                    </radialGradient>
                    <radialGradient id="v5NavyMedallion" cx="40%" cy="35%" r="68%">
                      <stop offset="0%" stopColor="#123C7D" />
                      <stop offset="55%" stopColor="#092653" />
                      <stop offset="100%" stopColor="#04122B" />
                    </radialGradient>
                    <pattern id="v5RibbonWeave" width="8" height="8" patternUnits="userSpaceOnUse">
                      <path d="M 0 4 L 4 0 L 8 4 L 4 8 Z" fill="none" stroke="#FFFFFF" strokeOpacity="0.06" strokeWidth="0.6" />
                    </pattern>
                  </defs>

                  {/* Top-to-Medallion Vertical Hanging Ribbon Band */}
                  <rect x="44" y="0" width="72" height="175" fill="url(#v5RibbonNavy)" />
                  <rect x="44" y="0" width="72" height="175" fill="url(#v5RibbonWeave)" />
                  {/* Left & Right Metallic Gold Ribbon Borders */}
                  <rect x="42.5" y="0" width="3.5" height="175" fill="url(#v5GoldRail)" />
                  <rect x="48.5" y="0" width="1.4" height="175" fill="#F9DF86" opacity="0.8" />
                  <rect x="114" y="0" width="3.5" height="175" fill="url(#v5GoldRail)" />
                  <rect x="110.1" y="0" width="1.4" height="175" fill="#F9DF86" opacity="0.8" />

                  {/* Bottom 3 Hanging Swallowtail Ribbon Tails */}
                  {/* Left Angled Navy Tail */}
                  <g>
                    <polygon points="48,228 74,234 52,324 38,308 22,318" fill="url(#v5RibbonNavy)" stroke="url(#v5GoldRail)" strokeWidth="2" />
                    <polygon points="48,228 74,234 52,324 38,308 22,318" fill="url(#v5RibbonWeave)" />
                  </g>
                  {/* Right Angled Navy Tail */}
                  <g>
                    <polygon points="86,234 112,228 138,318 122,308 108,324" fill="url(#v5RibbonNavy)" stroke="url(#v5GoldRail)" strokeWidth="2" />
                    <polygon points="86,234 112,228 138,318 122,308 108,324" fill="url(#v5RibbonWeave)" />
                  </g>
                  {/* Center Metallic Gold Pointed Tail */}
                  <polygon
                    points="64,230 96,230 98,322 80,340 62,322"
                    fill="url(#v5GoldRosette)"
                    stroke="#8F6110"
                    strokeWidth="1.5"
                  />
                  <polygon
                    points="69,232 91,232 92,318 80,331 68,318"
                    fill="none"
                    stroke="#FFF7CC"
                    strokeWidth="0.9"
                    opacity="0.7"
                  />

                  {/* Scalloped Metallic Gold Outer Medallion */}
                  <g transform="translate(80, 178)">
                    {/* Outer 24-Lobe Scalloped Burst */}
                    <path
                      d={Array.from({ length: 24 })
                        .map((_, i) => {
                          const a1 = (i / 24) * Math.PI * 2;
                          const aMid = ((i + 0.5) / 24) * Math.PI * 2;
                          const rOut = 72;
                          const rIn = 64.5;
                          const x1 = Math.cos(a1) * rOut;
                          const y1 = Math.sin(a1) * rOut;
                          const x2 = Math.cos(aMid) * rIn;
                          const y2 = Math.sin(aMid) * rIn;
                          return `${i === 0 ? 'M' : 'L'} ${x1.toFixed(1)} ${y1.toFixed(1)} L ${x2.toFixed(1)} ${y2.toFixed(1)}`;
                        })
                        .join(' ') + ' Z'}
                      fill="url(#v5GoldRosette)"
                      stroke="#8A5D0F"
                      strokeWidth="1.4"
                    />
                    {/* Concentric Bezel Rings */}
                    <circle r="58" fill="none" stroke="#FFF4B8" strokeWidth="1.4" opacity="0.85" />
                    <circle r="53" fill="url(#v5NavyMedallion)" stroke="url(#v5GoldRail)" strokeWidth="2.4" />
                    <circle r="48.5" fill="none" stroke="#F9DF86" strokeWidth="0.7" opacity="0.65" />

                    {/* Top Royal 3-Point Crown */}
                    <g transform="translate(0, -33)">
                      <path
                        d="M -10 5 L -12 -5 L -5 0 L 0 -8 L 5 0 L 12 -5 L 10 5 Z"
                        fill="url(#v5GoldRail)"
                        stroke="#784E0A"
                        strokeWidth="0.6"
                      />
                      <rect x="-10" y="6" width="20" height="2.4" rx="0.8" fill="url(#v5GoldRail)" />
                      <circle cx="-12" cy="-6.2" r="1.4" fill="#FFF7C8" />
                      <circle cx="0" cy="-9.5" r="1.7" fill="#FFF7C8" />
                      <circle cx="12" cy="-6.2" r="1.4" fill="#FFF7C8" />
                    </g>

                    {/* Sculpted Center Gold K Monogram */}
                    <text
                      x="0"
                      y="2"
                      textAnchor="middle"
                      fill="url(#v5GoldRail)"
                      fontSize="30"
                      fontWeight="900"
                      fontFamily="'Cinzel', 'Playfair Display', serif"
                      stroke="#784E0A"
                      strokeWidth="0.4"
                    >
                      {(data.issuer.name || 'K').trim().charAt(0).toUpperCase()}
                    </text>

                    {/* Organization Name + Subtext Inside Medallion */}
                    <text
                      x="0"
                      y="15"
                      textAnchor="middle"
                      fill="#FFF6CE"
                      fontSize="7.4"
                      fontWeight="800"
                      fontFamily="'Plus Jakarta Sans', sans-serif"
                      letterSpacing="0.8"
                    >
                      {(data.issuer.name || 'KNEAYERNG').slice(0, 14).toUpperCase()}
                    </text>
                    <text
                      x="0"
                      y="23"
                      textAnchor="middle"
                      fill="#F4D16B"
                      fontSize="5.4"
                      fontWeight="700"
                      fontFamily="'Plus Jakarta Sans', sans-serif"
                      letterSpacing="1.1"
                    >
                      {(data.issuer.department || 'PHONE SHOP').slice(0, 16).toUpperCase()}
                    </text>

                    {/* Flanking Gold Laurels Inside Medallion Rim */}
                    {[-1, 1].map((side) => (
                      <g key={side} transform={`scale(${side}, 1)`} fill="url(#v5GoldRail)">
                        {[0, 1, 2, 3, 4, 5, 6].map((idx) => {
                          const angle = (-15 + idx * 17) * (Math.PI / 180);
                          const lx = -38 * Math.cos(angle * 0.45);
                          const ly = 24 - idx * 7.8;
                          return (
                            <ellipse
                              key={idx}
                              cx={lx}
                              cy={ly}
                              rx="4.2"
                              ry="1.9"
                              transform={`rotate(${-35 + idx * 12}, ${lx}, ${ly})`}
                            />
                          );
                        })}
                      </g>
                    ))}

                    {/* 3 Gold Stars Along Bottom Rim */}
                    <g fill="#FFF2B2" stroke="#8C5E0E" strokeWidth="0.3">
                      <polygon
                        transform="translate(-13, 35) scale(0.36)"
                        points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                      />
                      <polygon
                        transform="translate(0, 38) scale(0.46)"
                        points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                      />
                      <polygon
                        transform="translate(13, 35) scale(0.36)"
                        points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                      />
                    </g>
                  </g>
                </svg>
              </div>
            )}

            {/* Top-Left SOLID GOLD Hanging Ribbon + Navy/Gold K Medal + 3 Tails */}
            {showHangingMedal && useGoldHangingSash && (
              <div
                onClick={() => onEditField?.('seal')}
                className={`absolute top-0 z-20 cursor-pointer group transition-transform hover:scale-[1.02] origin-top ${
                  isLandscape ? 'left-[50px]' : 'left-[28px]'
                }`}
                style={hangingMedalScale !== 1 ? { transform: `scale(${hangingMedalScale})` } : undefined}
                title="Click to customize Official Medal & Seal"
              >
                  <svg
                    width={isLandscape ? 150 : 110}
                    height={isLandscape ? 276 : 204}
                    viewBox="0 0 160 296"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    className="drop-shadow-xl overflow-visible"
                  >
                    <defs>
                      <linearGradient id="v6GoldSashBand" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stopColor="#B88218" />
                        <stop offset="22%" stopColor="#F5D675" />
                        <stop offset="50%" stopColor="#FFF2B8" />
                        <stop offset="78%" stopColor="#E6B846" />
                        <stop offset="100%" stopColor="#A67214" />
                      </linearGradient>
                      <linearGradient id="v6RibbonNavyTail" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stopColor="#0E346E" />
                        <stop offset="50%" stopColor="#092653" />
                        <stop offset="100%" stopColor="#041029" />
                      </linearGradient>
                      <radialGradient id="v6GoldRosette" cx="38%" cy="32%" r="68%">
                        <stop offset="0%" stopColor="#FFFCE6" />
                        <stop offset="28%" stopColor="#F8DE82" />
                        <stop offset="62%" stopColor="#D4A32C" />
                        <stop offset="88%" stopColor="#A37116" />
                        <stop offset="100%" stopColor="#6E490A" />
                      </radialGradient>
                      <radialGradient id="v6NavyDisc" cx="40%" cy="35%" r="68%">
                        <stop offset="0%" stopColor="#123C7D" />
                        <stop offset="58%" stopColor="#092653" />
                        <stop offset="100%" stopColor="#041129" />
                      </radialGradient>
                    </defs>

                    {/* Top-to-Medal SOLID METALLIC GOLD Vertical Hanging Sash */}
                    <rect x="46" y="0" width="68" height="140" fill="url(#v6GoldSashBand)" />
                    <rect x="49" y="0" width="2" height="140" fill="#FFF7D1" opacity="0.7" />
                    <rect x="109" y="0" width="2" height="140" fill="#8C5D0E" opacity="0.45" />

                    {/* 3 Hanging Ribbon Tails Below Medal (2 outer navy tails + 1 centered gold tail) */}
                    <polygon points="48,188 70,192 50,278 36,264 22,273" fill="url(#v6RibbonNavyTail)" stroke="#D4A32C" strokeWidth="1.5" />
                    <polygon points="90,192 112,188 138,273 124,264 110,278" fill="url(#v6RibbonNavyTail)" stroke="#D4A32C" strokeWidth="1.5" />
                    <polygon points="66,192 94,192 96,272 80,289 64,272" fill="url(#v6GoldSashBand)" stroke="#8F6110" strokeWidth="1.2" />

                    {/* Scalloped Metallic Gold Outer Medallion */}
                    <g transform="translate(80, 138)">
                      <path
                        d={Array.from({ length: 24 })
                          .map((_, i) => {
                            const a1 = (i / 24) * Math.PI * 2;
                            const aMid = ((i + 0.5) / 24) * Math.PI * 2;
                            const rOut = 69;
                            const rIn = 62;
                            const x1 = Math.cos(a1) * rOut;
                            const y1 = Math.sin(a1) * rOut;
                            const x2 = Math.cos(aMid) * rIn;
                            const y2 = Math.sin(aMid) * rIn;
                            return `${i === 0 ? 'M' : 'L'} ${x1.toFixed(1)} ${y1.toFixed(1)} L ${x2.toFixed(1)} ${y2.toFixed(1)}`;
                          })
                          .join(' ') + ' Z'}
                        fill="url(#v6GoldRosette)"
                        stroke="#8A5D0F"
                        strokeWidth="1.3"
                      />
                      <circle r="56" fill="none" stroke="#FFF4B8" strokeWidth="1.4" opacity="0.85" />
                      <circle r="51" fill="url(#v6NavyDisc)" stroke="url(#v6GoldSashBand)" strokeWidth="2.4" />
                      <circle r="46.5" fill="none" stroke="#F9DF86" strokeWidth="0.65" opacity="0.6" />

                      {/* Golden 3-Point Crown */}
                      <g transform="translate(0, -29)">
                        <path
                          d="M -11 6 L -14 -6 L -6 0 L 0 -9 L 6 0 L 14 -6 L 11 6 Z"
                          fill="url(#v6GoldSashBand)"
                          stroke="#784E0A"
                          strokeWidth="0.6"
                        />
                        <rect x="-11" y="7" width="22" height="2.6" rx="0.8" fill="url(#v6GoldSashBand)" />
                        <circle cx="-14" cy="-7" r="1.5" fill="#FFF7C8" />
                        <circle cx="0" cy="-10.5" r="1.8" fill="#FFF7C8" />
                        <circle cx="14" cy="-7" r="1.5" fill="#FFF7C8" />
                      </g>

                      {/* Bold Gold K Monogram */}
                      <path
                        d="M -14 -14 L -4 -14 L -4 -1 L 8 -14 L 19 -14 L 5 -1 L 20 17 L 8 17 L -4 2 L -4 17 L -14 17 Z"
                        fill="url(#v6GoldSashBand)"
                        stroke="#784E0A"
                        strokeWidth="0.7"
                        strokeLinejoin="round"
                      />

                      {/* Flanking Gold Laurels Inside Medallion */}
                      {[-1, 1].map((side) => (
                        <g key={side} transform={`scale(${side}, 1)`} fill="url(#v6GoldSashBand)">
                          {[0, 1, 2, 3, 4, 5, 6].map((idx) => {
                            const angle = (-15 + idx * 17) * (Math.PI / 180);
                            const lx = -36 * Math.cos(angle * 0.45);
                            const ly = 23 - idx * 7.4;
                            return (
                              <ellipse
                                key={idx}
                                cx={lx}
                                cy={ly}
                                rx="4.2"
                                ry="1.9"
                                transform={`rotate(${-35 + idx * 12}, ${lx}, ${ly})`}
                              />
                            );
                          })}
                        </g>
                      ))}

                      {/* Bottom Gold Star + Dots */}
                      <polygon
                        transform="translate(0, 34) scale(0.55)"
                        fill="#FFF2B2"
                        stroke="#8C5E0E"
                        strokeWidth="0.4"
                        points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                      />
                      <circle cx="-11" cy="33" r="1.5" fill="#F4D16B" />
                      <circle cx="11" cy="33" r="1.5" fill="#F4D16B" />
                    </g>
                  </svg>
                </div>
            )}

            {/* Subtle Isometric Architectural Storefront Watermark (Right Flank) */}
            {(vis.showStorefrontWatermark ?? isExecutiveImperialSash) && data.styling.showWatermark && (
                  <div
                    className={`pointer-events-none absolute z-[1] ${
                      isLandscape ? 'right-[44px] bottom-[176px]' : 'right-[24px] bottom-[240px]'
                    }`}
                    style={{ opacity: Math.min(0.16, (data.styling.watermarkOpacity || 0.14) * 0.95) }}
                  >
                    <svg
                      width={isLandscape ? 210 : 155}
                      height={isLandscape ? 165 : 122}
                      viewBox="0 0 210 165"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      {/* Perspective Storefront Fascia */}
                      <polygon points="16,46 136,12 198,28 198,144 136,136 16,154" fill={primaryNavy} fillOpacity="0.06" stroke={primaryNavy} strokeWidth="1.2" />
                      {/* Signboard Panel */}
                      <polygon points="20,46 136,14 136,68 20,88" fill="#FFFFFF" fillOpacity="0.55" stroke={primaryNavy} strokeWidth="1.1" />
                      {/* Roof Overhang */}
                      <polygon points="10,44 138,8 204,26 198,32 136,14 16,48" fill={primaryNavy} fillOpacity="0.14" stroke={primaryNavy} strokeWidth="1" />
                      {/* Storefront K Emblem & Sign Text */}
                      <g transform="translate(76, 40) skewY(-14)">
                        <text x="0" y="-4" textAnchor="middle" fill={primaryNavy} fontSize="18" fontWeight="900" fontFamily="'Cinzel', serif">
                          {(data.issuer.name || 'K').trim().charAt(0).toUpperCase()}
                        </text>
                        <text x="0" y="9" textAnchor="middle" fill={primaryNavy} fontSize="9.5" fontWeight="800" fontFamily="'Plus Jakarta Sans', sans-serif" letterSpacing="0.8">
                          {(data.issuer.name || 'KNEAYERNG').slice(0, 12).toUpperCase()}
                        </text>
                        <text x="0" y="17" textAnchor="middle" fill={primaryNavy} fontSize="5.2" fontWeight="700" fontFamily="'Plus Jakarta Sans', sans-serif" letterSpacing="1.2">
                          {(data.issuer.department || 'PHONE SHOP').slice(0, 14).toUpperCase()}
                        </text>
                      </g>
                      {/* Glass Display Windows & Double Entrance Doors */}
                      <polygon points="26,94 60,88 60,148 26,152" fill="#FFFFFF" fillOpacity="0.45" stroke={primaryNavy} strokeWidth="1" />
                      <polygon points="66,87 104,80 104,142 66,147" fill={primaryNavy} fillOpacity="0.08" stroke={primaryNavy} strokeWidth="1.1" />
                      <line x1="85" y1="84" x2="85" y2="144" stroke={primaryNavy} strokeWidth="1" />
                      <polygon points="110,79 134,74 134,136 110,141" fill="#FFFFFF" fillOpacity="0.45" stroke={primaryNavy} strokeWidth="1" />
                      <polygon points="140,74 192,80 192,143 140,136" fill={primaryNavy} fillOpacity="0.06" stroke={primaryNavy} strokeWidth="1" />
                    </svg>
                  </div>
            )}

            {/* Upper-Right Employee ID Portrait Photo + Overlapping Navy ID Ribbon */}
            {hasProfilePhoto && (() => {
              const isCircle = photoShape === 'circle';
              const isSquare = photoShape === 'square';
              const basePx = isLandscape ? (photoSize || 118) : Math.min(photoSize || 100, 100);
              const frameW = isCircle
                ? Math.round(basePx * 1.18)
                : isSquare
                ? Math.round(basePx * 1.15)
                : Math.round(basePx * 1.1);
              const frameH = isCircle || isSquare ? frameW : Math.round(basePx * 1.34);
              const outerRadius = isCircle ? 'rounded-full' : isSquare ? 'rounded-lg' : 'rounded-xl';
              const midRadius = isCircle ? 'rounded-full' : isSquare ? 'rounded-[7px]' : 'rounded-[10px]';
              const innerRadius = isCircle ? 'rounded-full' : isSquare ? 'rounded-[5px]' : 'rounded-[7px]';

              return (
                <div
                  onPointerDown={handlePhotoPointerDown}
                  onClickCapture={handlePhotoClickCapture}
                  style={photoFrameTransformStyle}
                  className={`group/photo touch-none absolute z-20 flex flex-col items-center ${
                    dragPhotoOffset ? 'cursor-grabbing' : 'cursor-grab'
                  } ${
                    isLandscape
                      ? photoPosition === 'top-left'
                        ? 'top-[74px] left-[104px]'
                        : 'top-[74px] right-[104px]'
                      : photoPosition === 'top-left'
                      ? 'top-[68px] left-[76px]'
                      : 'top-[68px] right-[76px]'
                  }`}
                >
                  {/* Floating Drag Hint / Coordinate Pill */}
                  <div
                    className={`no-print pointer-events-none absolute -top-5 left-1/2 -translate-x-1/2 z-40 px-2 py-0.5 rounded-full bg-slate-900/90 text-white text-[9px] font-bold tracking-wide whitespace-nowrap flex items-center gap-1 shadow-sm transition-opacity ${
                      dragPhotoOffset ? 'opacity-100' : 'opacity-0 group-hover/photo:opacity-100'
                    }`}
                  >
                    <Move className="w-2.5 h-2.5 text-amber-400" />
                    <span>
                      {dragPhotoOffset
                        ? `X: ${activeFrameOffsetX > 0 ? '+' : ''}${activeFrameOffsetX}px · Y: ${activeFrameOffsetY > 0 ? '+' : ''}${activeFrameOffsetY}px`
                        : 'Drag to move'}
                    </span>
                  </div>
                  <label
                    className={`relative block cursor-pointer group select-none ${outerRadius} p-[3px] shadow-md transition-transform hover:scale-[1.02]`}
                    style={{
                      width: `${frameW}px`,
                      height: `${frameH}px`,
                      background: `linear-gradient(145deg, #D4A32C 0%, #F9DF86 50%, #B38119 100%)`,
                    }}
                    title="Click to upload or replace employee photo"
                  >
                    <input
                      type="file"
                      accept="image/*"
                      onChange={handleInlinePhotoUpload}
                      className="hidden"
                    />
                    <div className={`w-full h-full ${midRadius} p-[3px] bg-white`}>
                      <div className={`w-full h-full ${innerRadius} overflow-hidden bg-gradient-to-b from-slate-100 to-slate-200 flex flex-col items-center justify-center relative`}>
                        {data.recipientPhotoUrl ? (
                          <img
                            src={data.recipientPhotoUrl}
                            alt={data.recipientName || 'Employee ID Photo'}
                            className="w-full h-full object-cover"
                            style={{
                              objectPosition: `${photoOffsetX}% ${photoOffsetY}%`,
                              transform: photoZoom !== 1 ? `scale(${photoZoom})` : undefined,
                            }}
                          />
                        ) : (
                          <div className="flex flex-col items-center justify-center text-center px-2">
                            <User
                              className="w-9 h-9 opacity-60"
                              style={{ color: primaryNavy }}
                              strokeWidth={1.6}
                            />
                            <span
                              className="text-[8px] font-bold uppercase tracking-wider mt-1 opacity-75"
                              style={{ color: primaryNavy }}
                            >
                              {data.languageMode === 'khmer' ? 'រូបបុគ្គលិក' : 'Upload Photo'}
                            </span>
                          </div>
                        )}

                        {/* Hover Overlay */}
                        <div className="no-print absolute inset-0 bg-slate-950/55 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white">
                          <Camera className="w-5 h-5 mb-0.5" />
                          <span className="text-[8.5px] font-bold uppercase tracking-wider">
                            Update Photo
                          </span>
                        </div>
                      </div>
                    </div>
                  </label>

                  {/* Overlapping Navy ID Ribbon (Pill in V6, Chamfered Hexagonal in V5, Swallowtail in V1/V2) */}
                  {vis.showEmployeeIdBadge !== false && data.digitalHr?.employeeId && (
                    <div
                      onClick={() => onEditField?.('digitalHr')}
                      className={`${
                        isExecutiveImperialSash ? 'mt-1.5' : isCircle ? '-mt-3' : '-mt-2.5'
                      } relative z-30 cursor-pointer hover:scale-[1.03] transition-transform`}
                      title="Click to edit Employee ID"
                    >
                    <svg width={isLandscape ? 166 : 148} height="24" viewBox="0 0 166 24" className="drop-shadow-sm overflow-visible">
                      {isExecutiveImperialSash ? (
                        <rect
                          x="2"
                          y="1.5"
                          width="162"
                          height="21"
                          rx="10.5"
                          fill={primaryNavy}
                          stroke={data.styling.accentColor || '#E8BE54'}
                          strokeWidth="1.4"
                        />
                      ) : (
                        <polygon
                          points={
                            isExecutiveMedalRibbon
                              ? '11,2 155,2 165,12 155,22 11,22 1,12'
                              : '0,2 166,2 160,12 166,22 0,22 6,12'
                          }
                          fill={primaryNavy}
                          stroke={data.styling.accentColor || (isExecutiveMedalRibbon ? '#E8BE54' : '#D4A32C')}
                          strokeWidth={isExecutiveMedalRibbon ? '1.3' : '0.9'}
                        />
                      )}
                      <text
                        x="83"
                        y="15.5"
                        textAnchor="middle"
                        fill="#FFFFFF"
                        fontSize="10"
                        fontWeight="700"
                        fontFamily="'Plus Jakarta Sans', 'JetBrains Mono', sans-serif"
                        letterSpacing="0.5"
                      >
                        {idPrefix ? `${idPrefix} ` : ''}{data.digitalHr.employeeId}
                      </text>
                    </svg>
                  </div>
                )}
                </div>
              );
            })()}

            {/* Main Content Canvas (Balanced Symmetrical A4 1123x794 / 794x1123 Layout) */}
            <div
              className={`relative z-10 w-full h-full flex flex-col justify-between text-center ${
                isLandscape ? 'px-14 pt-7 pb-7' : 'px-10 pt-10 pb-10'
              }`}
            >
              {/* TOP ZONE: Company K Monogram + Flanking Laurels + KNEAYERNG PHONE SHOP */}
              <div
                className="flex flex-col items-center cursor-pointer group"
                onClick={() => onEditField?.('issuer')}
                title="Click to edit company logo & branding"
              >
                <div className="relative inline-flex flex-col items-center justify-center px-16">
                  {/* Flanking Gold Laurels */}
                  {(data.styling.logoOrnament ?? (data.styling.showLaurelWreath === false ? 'none' : 'classic-laurel')) !== 'none' && (
                    <div className="absolute -top-1 left-1/2 -translate-x-1/2 pointer-events-none">
                      <LogoFlankingOrnament
                        style={data.styling.logoOrnament || 'classic-laurel'}
                        accentColor={data.styling.accentColor || '#D4A32C'}
                        primaryColor={primaryNavy}
                        className="w-[310px] h-[132px] overflow-visible"
                      />
                    </div>
                  )}

                  {vis.showLogo !== false && (
                    <IssuerLogo
                      issuer={data.issuer}
                      primaryColor={primaryNavy}
                      accentColor={data.styling.accentColor || '#D4A32C'}
                      scale={data.styling.logoScale || 1}
                      showLaurelWreath={data.styling.showLaurelWreath !== false}
                      logoOrnament={data.styling.logoOrnament}
                      variant="executive-verify"
                    />
                  )}

                  {vis.showIssuerName !== false && (
                    <h4
                      className="mt-1.5 text-[24px] font-extrabold tracking-[0.06em] uppercase font-sans-clean leading-none"
                      style={getFieldInlineStyle(data, 'issuerName', { color: primaryNavy })}
                    >
                      {data.languageMode === 'khmer' && data.issuer.khmerName
                        ? data.issuer.khmerName
                        : data.issuer.name}
                    </h4>
                  )}

                  {vis.showIssuerDepartment !== false && (data.issuer.department || data.issuer.khmerDepartment) && (
                    <div className="mt-1 flex items-center justify-center gap-2.5">
                      {isExecutiveImperialSash && (
                        <div className="w-10 h-[1.3px] bg-gradient-to-r from-transparent to-[#D4A32C]" />
                      )}
                      <p
                        className="text-[12px] font-semibold tracking-[0.26em] uppercase font-sans-clean leading-none"
                        style={getFieldInlineStyle(data, 'issuerDepartment', { color: primaryNavy })}
                      >
                        {data.languageMode === 'khmer' && data.issuer.khmerDepartment
                          ? data.issuer.khmerDepartment
                          : data.issuer.department}
                      </p>
                      {isExecutiveImperialSash && (
                        <div className="w-10 h-[1.3px] bg-gradient-to-l from-transparent to-[#D4A32C]" />
                      )}
                    </div>
                  )}

                  {/* Fully Customizable Ornamental Divider Below Logo (ក្បាច់ខណ្ឌក្រោម Logo) */}
                  {vis.showHeaderDivider !== false &&
                    (data.styling.dividerOrnament || (isExecutiveImperialSash ? 'none' : 'minimal-gold-bar')) !== 'none' && (
                    <div
                      className="mt-2 flex items-center justify-center cursor-pointer hover:opacity-85 transition-opacity"
                      onClick={(e) => {
                        e.stopPropagation();
                        onEditField?.('dividerOrnament');
                      }}
                      title="Click to customize divider ornament (ក្បាច់ខណ្ឌក្រោម Logo)"
                    >
                      <LogoDividerOrnament
                        style={data.styling.dividerOrnament || 'minimal-gold-bar'}
                        accentColor={data.styling.dividerColor || data.styling.accentColor || '#D4A32C'}
                        primaryColor={primaryNavy}
                        customUrl={data.styling.customDividerUrl}
                        width={data.styling.dividerWidth || 240}
                        scale={data.styling.dividerScale ?? 1}
                        offsetY={data.styling.dividerOffsetY ?? 0}
                        opacity={data.styling.dividerOpacity ?? 1}
                        className="w-[240px] h-[26px]"
                      />
                    </div>
                  )}
                </div>
              </div>

              {/* TITLE + GOLD BANNER RIBBON + RECIPIENT ZONE */}
              <div
                className={`relative flex flex-col items-center space-y-2 ${
                  (isExecutiveMedalRibbon || isExecutiveImperialSash) && !isLandscape ? 'px-12' : ''
                }`}
              >
                {/* Upper Flanking Gold Laurels Framing Title & Banner */}
                {showTitleLaurels && isLandscape && (
                  <div className="pointer-events-none absolute -top-3 left-1/2 -translate-x-1/2 w-[660px] flex items-center justify-between opacity-75">
                    <LogoFlankingOrnament
                      style="classic-laurel"
                      accentColor={data.styling.accentColor || '#D4A32C'}
                      primaryColor={primaryNavy}
                      className="w-[660px] h-[112px] overflow-visible"
                    />
                  </div>
                )}

                {/* Khmer + English Titles */}
                {(vis.showKhmerTitle !== false || vis.showEnglishTitle !== false) && (
                  <div className="space-y-1 relative z-10">
                    {vis.showKhmerTitle !== false &&
                      (data.languageMode === 'bilingual' || data.languageMode === 'khmer') &&
                      data.khmerTitle && (
                      <h2
                        onClick={() => onEditField?.('khmerTitle')}
                        className={`font-khmer-moul cursor-pointer hover:opacity-85 leading-snug ${
                          (isExecutiveMedalRibbon || isExecutiveImperialSash) && !isLandscape
                            ? 'text-[19px]'
                            : 'text-[23px]'
                        }`}
                        style={getFieldInlineStyle(data, 'khmerTitle', { color: primaryNavy })}
                        title="Click to edit Khmer title"
                      >
                        {data.khmerTitle}
                      </h2>
                    )}
                    {vis.showEnglishTitle !== false && data.languageMode !== 'khmer' && (
                      <h1
                        onClick={() => onEditField?.('certificateTitle')}
                        className="font-playfair font-bold tracking-[0.04em] uppercase cursor-pointer hover:opacity-85 transition-opacity leading-tight"
                        style={getFieldInlineStyle(data, 'certificateTitle', {
                          color: primaryNavy,
                          fontSize: `${
                            (isExecutiveMedalRibbon || isExecutiveImperialSash) && !isLandscape
                              ? Math.min(data.styling.titleFontSize || 34, 26)
                              : data.styling.titleFontSize || 34
                          }px`,
                        })}
                        title="Click to edit English title"
                      >
                        {data.certificateTitle}
                      </h1>
                    )}
                  </div>
                )}

                {/* V5 Diamond Rule Above Presentation Ribbon */}
                {isExecutiveMedalRibbon && vis.showBannerRibbon !== false && (
                  <div className={`flex items-center justify-center ${isLandscape ? 'w-[440px]' : 'w-[330px]'} -mt-0.5`}>
                    <div className="h-[1.2px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]" />
                    <div className="w-2 h-2 rotate-45 bg-gradient-to-br from-[#F4D16B] to-[#B38119] mx-1.5" />
                    <div className="h-[1.2px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]" />
                  </div>
                )}

                {/* Switchable Presentation Banner Ribbon (4 Cloned Vector Styles) */}
                {vis.showBannerRibbon !== false && (
                  <div
                    onClick={() => onEditField?.('bannerText')}
                    className="relative inline-flex items-center justify-center cursor-pointer group my-0.5"
                    title="Click to edit presentation banner"
                  >
                    {(() => {
                      const bStyle = getFieldInlineStyle(data, 'bannerText', {
                        fontSize: '13.5px',
                        fontWeight: 600,
                        fontFamily: "Cinzel, 'Cormorant Garamond', serif",
                        letterSpacing: '3.2px',
                      });
                      const svgFill = data.styling.fieldStyles?.bannerText?.color || '#FFFFFF';
                      return (
                        <PresentationBannerRibbon
                          style={data.styling.bannerStyle || 'gold-swallowtail'}
                          text={
                            data.languageMode === 'khmer' && data.khmerSubtitle
                              ? data.khmerSubtitle
                              : data.bannerText || 'THIS CERTIFICATE IS PRESENTED TO'
                          }
                          accentColor={data.styling.accentColor || '#D4A32C'}
                          primaryColor={primaryNavy}
                          textColor={svgFill}
                          textStyle={bStyle}
                          width={isLandscape ? 490 : 390}
                          showSpeedLines={(data.styling.bannerStyle || 'gold-swallowtail') === 'gold-swallowtail'}
                        />
                      );
                    })()}
                  </div>
                )}

                {/* Employee Khmer + English Names (Auto-sized for long names) */}
                {(vis.showKhmerRecipientName !== false ||
                  vis.showEnglishRecipientName !== false ||
                  vis.showRecipientDivider !== false) && (
                  <div
                    onClick={() => onEditField?.('recipientName')}
                    className="cursor-pointer group flex flex-col items-center pt-1 max-w-[780px]"
                    title="Click to edit employee name"
                  >
                    {vis.showKhmerRecipientName !== false &&
                      (data.languageMode === 'bilingual' || data.languageMode === 'khmer') &&
                      data.khmerRecipientName && (
                      <h2
                        className="font-khmer-moul text-[28px] leading-tight mb-0.5"
                        style={getFieldInlineStyle(data, 'khmerRecipientName', { color: primaryNavy })}
                      >
                        {data.khmerRecipientName}
                      </h2>
                    )}
                    {vis.showEnglishRecipientName !== false &&
                      data.recipientName &&
                      data.languageMode !== 'khmer' && (
                      <h2
                        className="font-playfair font-bold tracking-[0.04em] uppercase group-hover:scale-[1.01] transition-transform leading-none"
                        style={getFieldInlineStyle(data, 'recipientName', {
                          color: primaryNavy,
                          fontSize: `${autoVerifyRecipientPx}px`,
                        })}
                      >
                        {data.recipientName}
                      </h2>
                    )}

                    {/* Tapered Gold Divider with Centered Diamond */}
                    {vis.showRecipientDivider !== false && (
                      <div className={`flex items-center justify-center ${isLandscape ? 'w-[480px]' : 'w-[380px]'} mt-2.5`}>
                        <div className="h-[1.4px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]" />
                        <div className="w-2.5 h-2.5 rotate-45 bg-gradient-to-br from-[#F4D16B] to-[#B38119] mx-1.5 shadow-2xs" />
                        <div className="h-[1.4px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]" />
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* RECOGNITION MESSAGE PARAGRAPHS (Multiline Khmer & English) */}
              {(vis.showKhmerCitation !== false ||
                vis.showEnglishCitation !== false ||
                vis.showCitationClosing !== false) && (
                <div
                  onClick={() => onEditField?.('citation')}
                  className={`${isLandscape ? 'max-w-[760px]' : 'max-w-[640px]'} mx-auto space-y-1.5 cursor-pointer hover:opacity-85 transition-opacity px-4`}
                  title="Click to edit recognition message"
                >
                  {vis.showKhmerCitation !== false &&
                    (data.languageMode === 'bilingual' || data.languageMode === 'khmer') &&
                    data.khmerCitation && (
                    <p
                      className="font-khmer-sans text-[13.5px] leading-[1.6] whitespace-pre-line font-medium"
                      style={getFieldInlineStyle(data, 'khmerCitation', { color: primaryNavy })}
                    >
                      <span
                        style={
                          isExecutiveMedalRibbon || isExecutiveImperialSash
                            ? { color: data.styling.accentColor || '#D4A32C', fontWeight: 700 }
                            : undefined
                        }
                      >
                        «{' '}
                      </span>
                      {data.khmerCitation}
                      <span
                        style={
                          isExecutiveMedalRibbon || isExecutiveImperialSash
                            ? { color: data.styling.accentColor || '#D4A32C', fontWeight: 700 }
                            : undefined
                        }
                      >
                        {' '}»
                      </span>
                    </p>
                  )}
                  {vis.showEnglishCitation !== false && data.citation && data.languageMode !== 'khmer' && (
                    <p
                      className={`${bodyFontClass} text-[16px] leading-[1.4] italic font-medium whitespace-pre-line`}
                      style={getFieldInlineStyle(data, 'citation', { color: mutedTextColor })}
                    >
                      {data.citation}
                    </p>
                  )}
                  {vis.showCitationClosing !== false && data.citationClosing && data.languageMode !== 'khmer' && (
                    <p
                      className={`${bodyFontClass} text-[16px] leading-[1.35] italic font-medium whitespace-pre-line`}
                      style={getFieldInlineStyle(data, 'citationClosing', { color: mutedTextColor })}
                    >
                      {data.citationClosing}
                    </p>
                  )}
                </div>
              )}

              {/* Pre-Footer Tapered Gold Divider */}
              {showPreFooterDivider && (
                <div className={`flex items-center justify-center mx-auto ${isLandscape ? 'w-[600px]' : 'w-[440px]'} -my-1`}>
                  <div className="h-[1.2px] flex-1 bg-gradient-to-r from-transparent via-[#D4A32C] to-[#F4D16B]" />
                  <div className="w-1.5 h-1.5 rotate-45 bg-[#D4A32C] mx-1.5 opacity-80" />
                  <div className="w-2.5 h-2.5 rotate-45 bg-gradient-to-br from-[#F4D16B] to-[#B38119] mx-1 shadow-2xs" />
                  <div className="w-1.5 h-1.5 rotate-45 bg-[#D4A32C] mx-1.5 opacity-80" />
                  <div className="h-[1.2px] flex-1 bg-gradient-to-l from-transparent via-[#D4A32C] to-[#F4D16B]" />
                </div>
              )}

              {/* 5-ZONE STATIC FOOTER: DATE (+ CERT ID in V2/V5) + SIGNATORY 1 | STATIC STAMP | SIGNATORY 2 + VERIFY QR */}
              {(() => {
                const showDate = vis.showIssueDate !== false;
                const showCertId = vis.showCertificateId ?? hasStackedDateCertId;
                const useStackedFooter = hasStackedDateCertId || vis.showCertificateId === true;
                const qrPx = data.qrSize || 64;
                const qrBadgeText = data.qrLabel === undefined ? 'VERIFY CERTIFICATE' : data.qrLabel;
                const qrBadgeBg = data.qrBadgeColor || primaryNavy;

                const dateBlock = !showDate && !showCertId ? (
                  <div className={`${useStackedFooter ? 'w-[196px]' : 'w-[155px]'} shrink-0`} />
                ) : useStackedFooter ? (
                  <div className="w-[196px] flex items-end gap-3 shrink-0 pb-1.5">
                    <div className="flex-1 min-w-0 flex flex-col gap-2 text-left">
                      {/* DATE Block */}
                      {showDate && (
                        <div
                          onClick={() => onEditField?.('issueDate')}
                          className="cursor-pointer group"
                          title="Click to edit Issue Date"
                        >
                          <div className="flex items-center gap-1.5 mb-0.5">
                            <Calendar className="w-3.5 h-3.5 shrink-0" style={{ color: primaryNavy }} strokeWidth={2.2} />
                            <span
                              className="text-[11px] font-bold tracking-[0.06em] font-sans-clean"
                              style={{ color: primaryNavy }}
                            >
                              {data.languageMode === 'khmer'
                                ? 'កាលបរិច្ឆេទ / DATE'
                                : isExecutiveMedalRibbon || isExecutiveImperialSash
                                ? 'Date of Issue'
                                : 'DATE'}
                            </span>
                          </div>
                          <div className="w-full h-[1.3px]" style={{ backgroundColor: primaryNavy }} />
                          <div className="w-full mt-1">
                            <span
                              className="text-[11.5px] font-semibold font-sans-clean tracking-wide block truncate"
                              style={getFieldInlineStyle(data, 'issueDate', { color: primaryNavy })}
                            >
                              {data.languageMode === 'khmer' && data.khmerIssueDate
                                ? data.khmerIssueDate
                                : data.issueDate || '15 November 2026'}
                            </span>
                          </div>
                        </div>
                      )}

                      {/* CERTIFICATE ID Block */}
                      {showCertId && (
                        <div
                          onClick={() => onEditField?.('credentialId')}
                          className="cursor-pointer group"
                          title="Click to edit Certificate ID"
                        >
                          <div className="flex items-center gap-1.5 mb-0.5">
                            <svg
                              width="14"
                              height="14"
                              viewBox="0 0 16 16"
                              fill="none"
                              className="shrink-0"
                            >
                              <rect
                                x="1.5"
                                y="2.5"
                                width="13"
                                height="11"
                                rx="2"
                                stroke={primaryNavy}
                                strokeWidth="1.5"
                              />
                              <text
                                x="8"
                                y="10.2"
                                textAnchor="middle"
                                fill={primaryNavy}
                                fontSize="6.5"
                                fontWeight="800"
                                fontFamily="sans-serif"
                              >
                                ID
                              </text>
                            </svg>
                            <span
                              className={`text-[10.5px] font-bold tracking-[0.06em] font-sans-clean whitespace-nowrap ${
                                isExecutiveMedalRibbon || isExecutiveImperialSash ? '' : 'uppercase'
                              }`}
                              style={{ color: primaryNavy }}
                            >
                              {isExecutiveMedalRibbon || isExecutiveImperialSash
                                ? 'Certificate ID'
                                : 'CERTIFICATE ID'}
                            </span>
                          </div>
                          <div className="w-full h-[1.3px]" style={{ backgroundColor: primaryNavy }} />
                          <div className="w-full mt-1">
                            <span
                              className="text-[11px] font-mono font-semibold tracking-tight block truncate"
                              style={{ color: primaryNavy }}
                            >
                              {data.credentialId || 'KY-CERT-2026-00045'}
                            </span>
                          </div>
                        </div>
                      )}
                    </div>

                    {/* Vertical Metallic Gold Bar */}
                    <div className="w-[2px] h-[78px] rounded-full bg-gradient-to-b from-[#D4A32C]/40 via-[#D4A32C] to-[#D4A32C]/40 shrink-0" />
                  </div>
                ) : (
                  <div
                    onClick={() => onEditField?.('issueDate')}
                    className="w-[155px] flex flex-col items-start text-left cursor-pointer group pb-2 shrink-0"
                    title="Click to edit Issue Date"
                  >
                    <div className="flex items-center gap-2 mb-1">
                      <Calendar className="w-4 h-4 shrink-0" style={{ color: primaryNavy }} strokeWidth={2.2} />
                      <span
                        className="text-[12.5px] font-bold tracking-[0.12em] uppercase font-sans-clean"
                        style={{ color: primaryNavy }}
                      >
                        {data.languageMode === 'khmer' ? 'កាលបរិច្ឆេទ / DATE' : 'DATE'}
                      </span>
                    </div>
                    <div className="w-full h-[1.4px]" style={{ backgroundColor: primaryNavy }} />
                    <div className="w-full text-center mt-1.5">
                      <span
                        className="text-[13px] font-semibold font-sans-clean tracking-wide"
                        style={getFieldInlineStyle(data, 'issueDate', { color: primaryNavy })}
                      >
                        {data.languageMode === 'khmer' && data.khmerIssueDate
                          ? data.khmerIssueDate
                          : data.issueDate || '15 November 2026'}
                      </span>
                    </div>
                  </div>
                );

                const sigWidthClass =
                  !isLandscape
                    ? 'w-[172px]'
                    : indexedVisibleSignatories.length >= 3
                    ? 'w-[148px]'
                    : useStackedFooter
                    ? 'w-[172px]'
                    : 'w-[184px]';

                const firstVisible = indexedVisibleSignatories[0];
                const sigLineColor =
                  data.styling.signatureInkColor ||
                  (isExecutiveImperialSash ? data.styling.accentColor || '#D4A32C' : primaryNavy);

                const centerTrioBlock = (
                  <div className={`flex items-end justify-center ${useStackedFooter || !isLandscape ? 'gap-3 px-1' : 'gap-4 px-2'}`}>
                    {/* Signatory #1 */}
                    {firstVisible && (
                      <div
                        onClick={() => onOpenSignatureModal?.(firstVisible.originalIndex)}
                        className={`${sigWidthClass} flex flex-col items-center text-center cursor-pointer group pb-1`}
                        title="Click to customize Signatory #1"
                      >
                        <div className="h-12 flex items-end justify-center w-full mb-1">
                          {firstVisible.sig.customSignatureData ? (
                            <img
                              src={firstVisible.sig.customSignatureData}
                              alt={firstVisible.sig.name}
                              className="max-h-12 w-auto object-contain"
                            />
                          ) : (
                            <span
                              className={`${
                                firstVisible.sig.signatureStyle === 'great-vibes' ? 'font-script' : 'font-signature'
                              } text-[29px] leading-none select-none transition-transform group-hover:scale-105`}
                              style={{ color: signatureInk }}
                            >
                              {firstVisible.sig.name.replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/, '')}
                            </span>
                          )}
                        </div>
                        <div className="w-full h-[1.4px]" style={{ backgroundColor: sigLineColor }} />
                        <div
                          className="mt-1.5 text-[13px] font-bold tracking-[0.04em] uppercase font-sans-clean leading-tight"
                          style={getFieldInlineStyle(data, 'signatoryName', { color: primaryNavy })}
                        >
                          {data.languageMode === 'khmer' && firstVisible.sig.khmerName
                            ? firstVisible.sig.khmerName
                            : firstVisible.sig.name}
                        </div>
                        {firstVisible.sig.role && (
                          <div
                            className="mt-0.5 text-[10.5px] font-semibold font-sans-clean leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryRole', { color: primaryNavy })}
                          >
                            {data.languageMode === 'khmer' && firstVisible.sig.khmerRole
                              ? firstVisible.sig.khmerRole
                              : firstVisible.sig.role}
                          </div>
                        )}
                        {firstVisible.sig.organization && (
                          <div
                            className="mt-0.5 text-[9.5px] font-medium font-sans-clean tracking-wide leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryRole', { color: mutedTextColor })}
                          >
                            {firstVisible.sig.organization}
                          </div>
                        )}
                      </div>
                    )}

                    {/* Static Center Seal / Stamp Slot (always 132x132 even if stamp disabled) */}
                    <div
                      className="w-[132px] h-[132px] flex items-center justify-center shrink-0 cursor-pointer group hover:scale-105 transition-transform -mb-2"
                      onClick={() => onEditField?.('seal')}
                      title={
                        data.seal.enabled && data.seal.style !== 'none'
                          ? 'Click to customize official gold seal'
                          : 'Stamp space reserved — click to enable official seal'
                      }
                    >
                      {data.seal.enabled && data.seal.style !== 'none' ? (
                        <CertificateSeal
                          seal={data.seal}
                          primaryColor={primaryNavy}
                          accentColor={data.styling.accentColor || '#D4A32C'}
                        />
                      ) : (
                        <div className="no-print w-[104px] h-[104px] rounded-full border border-dashed border-amber-500/40 flex items-center justify-center text-[9px] font-bold uppercase tracking-wider text-amber-700/70 opacity-0 group-hover:opacity-100 transition-opacity select-none">
                          Stamp Area
                        </div>
                      )}
                    </div>

                    {/* Signatory #2+ (HR Signature) */}
                    {indexedVisibleSignatories.slice(1).map(({ sig, originalIndex }) => (
                      <div
                        key={sig.id || originalIndex}
                        onClick={() => onOpenSignatureModal?.(originalIndex)}
                        className={`${sigWidthClass} flex flex-col items-center text-center cursor-pointer group pb-1`}
                        title={`Click to customize ${sig.role || 'HR Signature'}`}
                      >
                        <div className="h-12 flex items-end justify-center w-full mb-1">
                          {sig.customSignatureData ? (
                            <img
                              src={sig.customSignatureData}
                              alt={sig.name}
                              className="max-h-12 w-auto object-contain"
                            />
                          ) : (
                            <span
                              className={`${
                                sig.signatureStyle === 'great-vibes' ? 'font-script' : 'font-signature'
                              } text-[29px] leading-none select-none transition-transform group-hover:scale-105`}
                              style={{ color: signatureInk }}
                            >
                              {sig.name.replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/, '')}
                            </span>
                          )}
                        </div>
                        <div className="w-full h-[1.4px]" style={{ backgroundColor: sigLineColor }} />
                        <div
                          className="mt-1.5 text-[13px] font-bold tracking-[0.04em] uppercase font-sans-clean leading-tight"
                          style={getFieldInlineStyle(data, 'signatoryName', { color: primaryNavy })}
                        >
                          {data.languageMode === 'khmer' && sig.khmerName ? sig.khmerName : sig.name}
                        </div>
                        {sig.role && (
                          <div
                            className="mt-0.5 text-[10.5px] font-semibold font-sans-clean leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryRole', { color: primaryNavy })}
                          >
                            {data.languageMode === 'khmer' && sig.khmerRole ? sig.khmerRole : sig.role}
                          </div>
                        )}
                        {sig.organization && (
                          <div
                            className="mt-0.5 text-[9.5px] font-medium font-sans-clean tracking-wide leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryRole', { color: mutedTextColor })}
                          >
                            {sig.organization}
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                );

                const qrBlock = (
                  <div className={`${useStackedFooter ? 'w-[138px]' : 'w-[155px]'} flex flex-col items-end justify-end shrink-0 pb-1 pr-2`}>
                    {data.showQrCode ? (
                      <div
                        onClick={() => onOpenVerifyModal?.()}
                        className="flex flex-col items-center cursor-pointer group hover:scale-[1.03] transition-transform"
                        title={`Verify Credential ${data.credentialId}`}
                      >
                        <div
                          className="relative p-1.5 rounded-lg bg-white border-[1.5px] shadow-xs"
                          style={{ borderColor: data.styling.accentColor || '#D4A32C' }}
                        >
                          {qrDataUrl ? (
                            <img
                              src={qrDataUrl}
                              alt="Verify Certificate QR"
                              className="object-contain"
                              style={{ width: `${qrPx}px`, height: `${qrPx}px` }}
                            />
                          ) : (
                            <div
                              className="bg-slate-100 rounded-xs"
                              style={{ width: `${qrPx}px`, height: `${qrPx}px` }}
                            />
                          )}
                          {/* Centered Tiny Navy & Gold K Emblem */}
                          <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div
                              className="w-[18px] h-[18px] rounded-[4px] bg-white border flex items-center justify-center shadow-2xs"
                              style={{ borderColor: data.styling.accentColor || '#D4A32C' }}
                            >
                              <KMonogramCrest
                                primaryColor={primaryNavy}
                                accentColor={data.styling.accentColor || '#D4A32C'}
                                size={12}
                              />
                            </div>
                          </div>
                        </div>
                        {qrBadgeText.trim().length > 0 && (
                          <div
                            className="mt-1 px-2.5 py-[3px] rounded-full text-[8px] font-bold tracking-[0.1em] uppercase font-sans-clean text-white shadow-2xs border whitespace-nowrap"
                            style={{
                              backgroundColor: qrBadgeBg,
                              borderColor: `${data.styling.accentColor || '#D4A32C'}99`,
                            }}
                          >
                            {qrBadgeText}
                          </div>
                        )}
                      </div>
                    ) : (
                      <div className="w-[78px] h-[90px]" />
                    )}
                  </div>
                );

                return isLandscape ? (
                  <div className="flex items-end justify-between w-full px-4 pt-1 pb-1">
                    {dateBlock}
                    {centerTrioBlock}
                    {qrBlock}
                  </div>
                ) : (
                  <div className="flex flex-col items-center w-full gap-4 pt-2 pb-2 px-2">
                    {centerTrioBlock}
                    <div className="flex items-end justify-between w-full px-2 pt-2 border-t border-[#D4A32C]/25">
                      {dateBlock}
                      {qrBlock}
                    </div>
                  </div>
                );
              })()}
            </div>
          </>
        ) : isExecutiveClone ? (
          <>
            {/* Background Watermark & Guilloche Security Pattern */}
            {data.styling.showWatermark && (
              <WatermarkPatternLayer
                type={data.styling.watermarkType || 'laurel'}
                opacity={data.styling.watermarkOpacity || 0.14}
                primaryColor={primaryNavy}
                accentColor={data.styling.accentColor || '#D4A13A'}
                issuerName={data.issuer.name}
                issuerDepartment={data.issuer.department}
              />
            )}

            {/* Optional Top-Left / Top-Right Staff Profile Photo in Executive Ribbon */}
            {hasProfilePhoto && (photoPosition === 'top-right' || photoPosition === 'top-left') && (
              <div
                onPointerDown={handlePhotoPointerDown}
                onClickCapture={handlePhotoClickCapture}
                style={photoFrameTransformStyle}
                className={`group/photo touch-none absolute z-20 ${
                  dragPhotoOffset ? 'cursor-grabbing' : 'cursor-grab'
                } ${
                  isLandscape
                    ? photoPosition === 'top-right'
                      ? 'top-[74px] right-[104px]'
                      : 'top-[74px] left-[104px]'
                    : photoPosition === 'top-right'
                    ? 'top-[68px] right-[74px]'
                    : 'top-[68px] left-[74px]'
                }`}
              >
                <div
                  className={`no-print pointer-events-none absolute -top-5 left-1/2 -translate-x-1/2 z-40 px-2 py-0.5 rounded-full bg-slate-900/90 text-white text-[9px] font-bold tracking-wide whitespace-nowrap flex items-center gap-1 shadow-sm transition-opacity ${
                    dragPhotoOffset ? 'opacity-100' : 'opacity-0 group-hover/photo:opacity-100'
                  }`}
                >
                  <Move className="w-2.5 h-2.5 text-amber-400" />
                  <span>
                    {dragPhotoOffset
                      ? `X: ${activeFrameOffsetX > 0 ? '+' : ''}${activeFrameOffsetX}px · Y: ${activeFrameOffsetY > 0 ? '+' : ''}${activeFrameOffsetY}px`
                      : 'Drag to move'}
                  </span>
                </div>
                {renderProfilePhotoBadge(!isLandscape)}
              </div>
            )}

            {/* Main Cloned Content Canvas (A4 1123x794 / 794x1123) */}
            <div
              className={`relative z-10 w-full h-full flex flex-col justify-between text-center ${
                isLandscape ? 'px-16 pt-7 pb-9' : 'px-10 pt-10 pb-10'
              }`}
            >
              {/* TOP ZONE: Circular Logo with Sweeping Laurels + KNEAYERNG + PHONE SHOP + Ornate Filigree */}
              <div className="flex flex-col items-center">
                {(vis.showLogo !== false || vis.showIssuerName !== false || vis.showIssuerDepartment !== false) && (
                  <div
                    className="cursor-pointer group transition-transform hover:scale-[1.02] flex flex-col items-center"
                    onClick={() => onEditField?.('issuer')}
                    title="Click to edit issuing organization & logo"
                  >
                    {vis.showLogo !== false && (
                      <IssuerLogo
                        issuer={data.issuer}
                        primaryColor={primaryNavy}
                        accentColor={data.styling.accentColor}
                        scale={data.styling.logoScale || 1}
                        showLaurelWreath={data.styling.showLaurelWreath !== false}
                        logoOrnament={data.styling.logoOrnament}
                        variant="executive-navy-ribbon"
                      />
                    )}

                    {vis.showIssuerName !== false && (
                      <h4
                        className="mt-2.5 text-[21px] font-extrabold tracking-[0.06em] uppercase font-sans-clean leading-none"
                        style={getFieldInlineStyle(data, 'issuerName', { color: primaryNavy })}
                      >
                        {data.languageMode === 'khmer' && data.issuer.khmerName ? data.issuer.khmerName : data.issuer.name}
                      </h4>
                    )}

                    {vis.showIssuerDepartment !== false && (data.issuer.department || data.issuer.khmerDepartment) && (
                      <div className="flex items-center justify-center gap-2.5 mt-1">
                        <div className="h-[1.5px] w-7 bg-gradient-to-r from-transparent via-[#C89328] to-[#D4A13A]" />
                        <p
                          className="text-[11px] font-bold tracking-[0.18em] uppercase font-sans-clean leading-none"
                          style={getFieldInlineStyle(data, 'issuerDepartment', { color: primaryNavy })}
                        >
                          {data.languageMode === 'khmer' && data.issuer.khmerDepartment
                            ? data.issuer.khmerDepartment
                            : data.issuer.department}
                        </p>
                        <div className="h-[1.5px] w-7 bg-gradient-to-l from-transparent via-[#C89328] to-[#D4A13A]" />
                      </div>
                    )}
                  </div>
                )}

                {/* Selectable Ornamental Divider Beneath Logo / Organization */}
                {vis.showHeaderDivider !== false && (data.styling.dividerOrnament || 'baroque-fleur') !== 'none' && (
                  <div
                    className="mt-2 flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity"
                    onClick={() => onEditField?.('dividerOrnament')}
                    title="Click to customize divider ornament (ក្បាច់ខណ្ឌក្រោម Logo)"
                  >
                    <LogoDividerOrnament
                      style={data.styling.dividerOrnament || 'baroque-fleur'}
                      accentColor={data.styling.dividerColor || data.styling.accentColor}
                      primaryColor={primaryNavy}
                      customUrl={data.styling.customDividerUrl}
                      width={data.styling.dividerWidth}
                      scale={data.styling.dividerScale ?? 1}
                      offsetY={data.styling.dividerOffsetY ?? 0}
                      opacity={data.styling.dividerOpacity ?? 1}
                      className="w-[280px] h-[26px]"
                    />
                  </div>
                )}
              </div>

              {/* TITLE + GOLD BANNER RIBBON + RECIPIENT ZONE */}
              <div className="relative flex flex-col items-center space-y-2.5 my-1">
                {vis.showTitleLaurels === true && isLandscape && (
                  <div className="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 w-[660px] flex items-center justify-between opacity-75">
                    <LogoFlankingOrnament
                      style="classic-laurel"
                      accentColor={data.styling.accentColor || '#D4A13A'}
                      primaryColor={primaryNavy}
                      className="w-[660px] h-[112px] overflow-visible"
                    />
                  </div>
                )}

                {/* Main Bold Serif Headline */}
                {(vis.showKhmerTitle !== false || vis.showEnglishTitle !== false) && (
                  <div className="space-y-0.5 relative z-10">
                    {vis.showKhmerTitle !== false &&
                      (data.languageMode === 'bilingual' || data.languageMode === 'khmer') &&
                      data.khmerTitle && (
                      <h2
                        onClick={() => onEditField?.('khmerTitle')}
                        className="font-khmer-moul text-xl md:text-2xl cursor-pointer hover:opacity-85 leading-snug"
                        style={getFieldInlineStyle(data, 'khmerTitle', { color: primaryNavy })}
                      >
                        {data.khmerTitle}
                      </h2>
                    )}
                    {vis.showEnglishTitle !== false && data.languageMode !== 'khmer' && (
                      <h1
                        onClick={() => onEditField?.('certificateTitle')}
                        className={`${displayFontClass} font-bold tracking-[0.04em] uppercase cursor-pointer hover:opacity-85 transition-opacity leading-tight`}
                        style={getFieldInlineStyle(data, 'certificateTitle', {
                          color: primaryNavy,
                          fontSize: `${data.styling.titleFontSize || 38}px`,
                        })}
                        title="Click to edit certificate title"
                      >
                        {data.certificateTitle}
                      </h1>
                    )}
                  </div>
                )}

                {/* Switchable Presentation Banner Ribbon: THIS CERTIFICATE IS PRESENTED TO */}
                {vis.showBannerRibbon !== false && (
                  <div
                    onClick={() => onEditField?.('bannerText')}
                    className="relative inline-flex items-center justify-center cursor-pointer group my-0.5"
                    title="Click to edit presentation banner"
                  >
                    {(() => {
                      const bStyle = getFieldInlineStyle(data, 'bannerText', {
                        fontSize: '14.5px',
                        fontWeight: 600,
                        fontFamily: "Cinzel, 'Cormorant Garamond', serif",
                        letterSpacing: '3.2px',
                      });
                      const svgFill = data.styling.fieldStyles?.bannerText?.color || '#FFFFFF';
                      return (
                        <PresentationBannerRibbon
                          style={data.styling.bannerStyle || 'gold-swallowtail'}
                          text={
                            data.languageMode === 'khmer' && data.khmerSubtitle
                              ? data.khmerSubtitle
                              : data.bannerText || 'THIS CERTIFICATE IS PRESENTED TO'
                          }
                          accentColor={data.styling.accentColor || '#D6A232'}
                          primaryColor={primaryNavy}
                          textColor={svgFill}
                          textStyle={bStyle}
                          width={isLandscape ? 530 : 410}
                          showTopDiamond
                        />
                      );
                    })()}
                  </div>
                )}

                {/* Recipient Name Block */}
                {(vis.showKhmerRecipientName !== false ||
                  vis.showEnglishRecipientName !== false ||
                  vis.showRecipientDivider !== false ||
                  vis.showOrganizationSubBanner !== false) && (
                  <div
                    onClick={() => onEditField?.('recipientName')}
                    className="cursor-pointer group flex flex-col items-center pt-1"
                    title="Click to edit recipient name"
                  >
                    <div className="flex items-center justify-center gap-4">
                      {hasProfilePhoto && photoPosition === 'beside-name' && (
                        <div onClick={(e) => e.stopPropagation()}>
                          {renderProfilePhotoBadge(true)}
                        </div>
                      )}
                      <div className="flex flex-col items-center">
                        {vis.showKhmerRecipientName !== false &&
                          (data.languageMode === 'bilingual' || data.languageMode === 'khmer') &&
                          data.khmerRecipientName && (
                          <h2
                            className="font-khmer-moul text-2xl md:text-3xl leading-tight mb-0.5"
                            style={getFieldInlineStyle(data, 'khmerRecipientName', { color: primaryNavy })}
                          >
                            {data.khmerRecipientName}
                          </h2>
                        )}
                        {vis.showEnglishRecipientName !== false &&
                          data.recipientName &&
                          data.languageMode !== 'khmer' && (
                          <h2
                            className="font-playfair font-bold tracking-[0.03em] uppercase group-hover:scale-[1.01] transition-transform leading-none"
                            style={getFieldInlineStyle(data, 'recipientName', {
                              color: primaryNavy,
                              fontSize: `${data.styling.recipientFontSize || 50}px`,
                            })}
                          >
                            {data.recipientName}
                          </h2>
                        )}
                      </div>
                    </div>

                    {/* Tapered Gold Divider with Centered Diamond */}
                    {vis.showRecipientDivider !== false && (
                      <div className={`flex items-center justify-center ${isLandscape ? 'w-[500px]' : 'w-[380px]'} mt-2.5`}>
                        <div className="h-[1.5px] flex-1 bg-gradient-to-r from-transparent via-[#C89328] to-[#D4A13A]" />
                        <div className="w-2.5 h-2.5 rotate-45 bg-gradient-to-br from-[#F7DE8A] to-[#B88218] mx-1 shadow-2xs" />
                        <div className="h-[1.5px] flex-1 bg-gradient-to-l from-transparent via-[#C89328] to-[#D4A13A]" />
                      </div>
                    )}

                    {/* Organization Sub-Banner: ———— KNEAYERNG PHONE SHOP ———— */}
                    {vis.showOrganizationSubBanner !== false && (data.organizationSubBanner || data.recipientTitle) && (
                      <div className={`flex items-center justify-center gap-3.5 ${isLandscape ? 'w-[610px]' : 'w-[460px]'} mt-2`}>
                        <div className="h-[1.6px] flex-1 bg-gradient-to-r from-transparent via-[#C89328] to-[#D4A13A]" />
                        <span
                          className="text-[14.5px] font-bold tracking-[0.22em] uppercase font-sans-clean whitespace-nowrap"
                          style={getFieldInlineStyle(data, 'organizationSubBanner', { color: primaryNavy })}
                        >
                          {data.organizationSubBanner || data.recipientTitle}
                        </span>
                        <div className="h-[1.6px] flex-1 bg-gradient-to-l from-transparent via-[#C89328] to-[#D4A13A]" />
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* OPTIONAL KNEAYERNG HRMS ACCREDITED HOURS BADGE */}
              {data.digitalHr?.showDigitalHoursBadge && (
                <div
                  onClick={() => onEditField?.('digitalHr')}
                  className="mx-auto px-4 py-1 rounded-xs border border-[#C89328]/50 bg-[#FDF8EC] flex items-center gap-4 text-[10px] cursor-pointer hover:border-[#C89328]"
                  style={{ color: primaryNavy }}
                  title="Click to manage KNEAYERNG HRMS Hours"
                >
                  <span className="font-bold tracking-wider uppercase font-cinzel">
                    ⚜ KNEAYERNG HRMS Accredited: {data.digitalHr.trainingHours}.0 Hrs
                  </span>
                  <span className="text-[#C89328]">|</span>
                  <span className="font-mono font-semibold">ID: {data.digitalHr.employeeId}</span>
                </div>
              )}

              {/* CITATION NARRATIVE PARAGRAPHS */}
              {(vis.showKhmerCitation !== false ||
                vis.showEnglishCitation !== false ||
                vis.showCitationClosing !== false) && (
                <div
                  onClick={() => onEditField?.('citation')}
                  className="max-w-[740px] mx-auto space-y-2 cursor-pointer hover:opacity-85 transition-opacity px-4"
                  title="Click to edit citation narrative"
                >
                  {vis.showKhmerCitation !== false &&
                    (data.languageMode === 'bilingual' || data.languageMode === 'khmer') &&
                    data.khmerCitation && (
                    <p
                      className="font-khmer-sans text-[13px] leading-relaxed"
                      style={getFieldInlineStyle(data, 'khmerCitation', { color: mutedTextColor })}
                    >
                      « {data.khmerCitation} »
                    </p>
                  )}
                  {vis.showEnglishCitation !== false && data.citation && data.languageMode !== 'khmer' && (
                    <p
                      className={`${bodyFontClass} text-[16.5px] leading-[1.42] italic font-medium`}
                      style={getFieldInlineStyle(data, 'citation', { color: mutedTextColor })}
                    >
                      {data.citation}
                    </p>
                  )}
                  {vis.showCitationClosing !== false && data.citationClosing && data.languageMode !== 'khmer' && (
                    <p
                      className={`${bodyFontClass} text-[16.5px] leading-[1.38] italic font-medium`}
                      style={getFieldInlineStyle(data, 'citationClosing', { color: mutedTextColor })}
                    >
                      {data.citationClosing}
                    </p>
                  )}
                </div>
              )}

              {/* BOTTOM FOOTER ROW: SINGLE SIGNATORY OR MULTI-SIGNATORY (WITH HR SIGNATURE) */}
              {indexedVisibleSignatories.length <= 1 ? (
                <div className={`flex items-end justify-between w-full ${isLandscape ? 'px-6' : 'px-2'} pt-2 pb-1`}>
                  {/* Left Column: Date Line + Calendar Icon & DATE Label */}
                  <div
                    onClick={() => onEditField?.('issueDate')}
                    className={`${isLandscape ? 'w-[220px]' : 'w-[172px]'} flex flex-col items-start text-left cursor-pointer group pb-3 shrink-0`}
                    title="Click to edit Issue Date"
                  >
                    {data.showQrCode && qrDataUrl && (
                      <div
                        onClick={(e) => {
                          e.stopPropagation();
                          onOpenVerifyModal?.();
                        }}
                        className="mb-1.5 p-1 bg-white border border-[#0A224E]/20 rounded-xs inline-block hover:scale-105 transition-transform"
                        title="Click to verify QR credential"
                      >
                        <img
                          src={qrDataUrl}
                          alt="QR Verification"
                          className="object-contain"
                          style={{
                            width: `${Math.round((data.qrSize || 64) * 0.65)}px`,
                            height: `${Math.round((data.qrSize || 64) * 0.65)}px`,
                          }}
                        />
                      </div>
                    )}
                    {vis.showIssueDate !== false && (
                      <>
                        <div className="w-full min-h-[22px] flex items-end pb-1 pl-1">
                          {data.issueDate && (
                            <span
                              className="text-[13px] font-semibold font-cormorant tracking-wide"
                              style={getFieldInlineStyle(data, 'issueDate', { color: primaryNavy })}
                            >
                              {data.languageMode === 'khmer' && data.khmerIssueDate ? data.khmerIssueDate : data.issueDate}
                            </span>
                          )}
                        </div>
                        <div className="w-full h-[1.4px]" style={{ backgroundColor: primaryNavy }} />
                        <div className="flex items-center gap-2 mt-2">
                          <Calendar className="w-4 h-4 shrink-0" style={{ color: primaryNavy }} strokeWidth={2} />
                          <span
                            className="text-[12.5px] font-bold tracking-[0.12em] uppercase font-cinzel"
                            style={getFieldInlineStyle(data, 'issueDate', { color: primaryNavy })}
                          >
                            {data.languageMode === 'khmer' ? 'កាលបរិច្ឆេទ / DATE' : 'DATE'}
                          </span>
                        </div>
                      </>
                    )}
                    {vis.showCertificateId !== false && data.credentialId && (
                      <div className="mt-1 font-mono-code text-[9px] opacity-75" style={{ color: primaryNavy }}>
                        ID: {data.credentialId}
                      </div>
                    )}
                  </div>

                  {/* Center Column: Flanking Gold Spear Dividers + Embossed Crown-Laurel Seal */}
                  <div className="flex items-center justify-center gap-2 -mb-2 shrink-0">
                    <svg width={isLandscape ? 96 : 44} height="18" viewBox="0 0 96 18" fill="none" className="shrink-0">
                      <defs>
                        <linearGradient id="spearL" x1="0%" y1="50%" x2="100%" y2="50%">
                          <stop offset="0%" stopColor="#D4A13A" stopOpacity="0" />
                          <stop offset="55%" stopColor="#D4A13A" />
                          <stop offset="100%" stopColor="#A87414" />
                        </linearGradient>
                      </defs>
                      <line x1="2" y1="9" x2="72" y2="9" stroke="url(#spearL)" strokeWidth="1.6" />
                      <polygon points="72,4 82,9 72,14 66,9" fill="#E6BA4E" stroke="#9A6B12" strokeWidth="0.6" />
                      <polygon points="84,7 94,9 84,11" fill="#C89328" />
                    </svg>

                    <div
                      className="w-[132px] h-[132px] flex items-center justify-center shrink-0 cursor-pointer group hover:scale-105 transition-transform"
                      onClick={() => onEditField?.('seal')}
                      title={
                        data.seal.enabled && data.seal.style !== 'none'
                          ? 'Click to customize official gold seal'
                          : 'Stamp space reserved — click to enable digital seal'
                      }
                    >
                      <CertificateSeal
                        seal={data.seal}
                        primaryColor={primaryNavy}
                        accentColor={data.styling.accentColor}
                      />
                    </div>

                    <svg width={isLandscape ? 96 : 44} height="18" viewBox="0 0 96 18" fill="none" className="shrink-0 transform -scale-x-100">
                      <line x1="2" y1="9" x2="72" y2="9" stroke="url(#spearL)" strokeWidth="1.6" />
                      <polygon points="72,4 82,9 72,14 66,9" fill="#E6BA4E" stroke="#9A6B12" strokeWidth="0.6" />
                      <polygon points="84,7 94,9 84,11" fill="#C89328" />
                    </svg>
                  </div>

                  {/* Right Column: Single Executive Signatory Block */}
                  <div
                    onClick={() => primaryVisibleSig && onOpenSignatureModal?.(primaryVisibleSig.originalIndex)}
                    className={`${isLandscape ? 'w-[220px]' : 'w-[172px]'} flex flex-col items-center text-center cursor-pointer group pb-1 shrink-0`}
                    title="Click to customize signature & title"
                  >
                    {primaryVisibleSig && (
                      <>
                        <div className="h-12 flex items-end justify-center w-full mb-1">
                          {primaryVisibleSig.sig.customSignatureData ? (
                            <img
                              src={primaryVisibleSig.sig.customSignatureData}
                              alt={primaryVisibleSig.sig.name}
                              className="max-h-12 w-auto object-contain"
                            />
                          ) : (
                            <span
                              className={`${
                                primaryVisibleSig.sig.signatureStyle === 'great-vibes' ? 'font-script' : 'font-signature'
                              } text-[34px] leading-none select-none transition-transform group-hover:scale-105`}
                              style={{ color: signatureInk }}
                            >
                              {primaryVisibleSig.sig.name.replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/, '')}
                            </span>
                          )}
                        </div>

                        <div className="w-full h-[1.4px]" style={{ backgroundColor: signatureInk }} />

                        <div
                          className="mt-1.5 text-[14px] font-bold tracking-[0.06em] uppercase font-sans-clean leading-tight"
                          style={getFieldInlineStyle(data, 'signatoryName', { color: primaryNavy })}
                        >
                          {data.languageMode === 'khmer' && primaryVisibleSig.sig.khmerName
                            ? primaryVisibleSig.sig.khmerName
                            : primaryVisibleSig.sig.name}
                        </div>

                        {primaryVisibleSig.sig.role && (
                          <div
                            className="mt-0.5 text-[11.5px] font-semibold font-cormorant leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryRole', { color: primaryNavy })}
                          >
                            {data.languageMode === 'khmer' && primaryVisibleSig.sig.khmerRole
                              ? primaryVisibleSig.sig.khmerRole
                              : primaryVisibleSig.sig.role}
                          </div>
                        )}

                        {primaryVisibleSig.sig.organization && (
                          <div
                            className="mt-0.5 text-[11px] font-medium font-cormorant tracking-wide leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryRole', { color: mutedTextColor })}
                          >
                            {primaryVisibleSig.sig.organization}
                          </div>
                        )}
                      </>
                    )}
                  </div>
                </div>
              ) : (
                /* MULTI-SIGNATORY FOOTER: Date on far left + Signatory 1, Stamp, Signatory 2 tightly centered */
                (() => {
                  const dateBlock = (
                    <div
                      onClick={() => onEditField?.('issueDate')}
                      className={`${
                        isLandscape && indexedVisibleSignatories.length === 2
                          ? 'absolute left-0 bottom-1 w-[138px]'
                          : isLandscape
                          ? 'w-[120px] shrink-0'
                          : 'w-[180px] shrink-0'
                      } flex flex-col items-start text-left cursor-pointer group pb-2`}
                      title="Click to edit Issue Date"
                    >
                      {data.showQrCode && qrDataUrl && (
                        <div
                          onClick={(e) => {
                            e.stopPropagation();
                            onOpenVerifyModal?.();
                          }}
                          className="mb-1 p-1 bg-white border border-[#0A224E]/20 rounded-xs inline-block hover:scale-105 transition-transform"
                          title="Click to verify QR credential"
                        >
                          <img
                            src={qrDataUrl}
                            alt="QR Verification"
                            className="object-contain"
                            style={{
                              width: `${Math.round((data.qrSize || 64) * 0.56)}px`,
                              height: `${Math.round((data.qrSize || 64) * 0.56)}px`,
                            }}
                          />
                        </div>
                      )}
                      {vis.showIssueDate !== false && (
                        <>
                          <div className="w-full min-h-[20px] flex items-end pb-1 pl-0.5">
                            {data.issueDate && (
                              <span
                                className="text-[12px] font-semibold font-cormorant tracking-wide"
                                style={getFieldInlineStyle(data, 'issueDate', { color: primaryNavy })}
                              >
                                {data.languageMode === 'khmer' && data.khmerIssueDate ? data.khmerIssueDate : data.issueDate}
                              </span>
                            )}
                          </div>
                          <div className="w-full h-[1.4px]" style={{ backgroundColor: primaryNavy }} />
                          <div className="flex items-center gap-1.5 mt-1.5">
                            <Calendar className="w-3.5 h-3.5 shrink-0" style={{ color: primaryNavy }} strokeWidth={2} />
                            <span
                              className="text-[11px] font-bold tracking-[0.12em] uppercase font-cinzel"
                              style={getFieldInlineStyle(data, 'issueDate', { color: primaryNavy })}
                            >
                              {data.languageMode === 'khmer' ? 'កាលបរិច្ឆេទ' : 'DATE'}
                            </span>
                          </div>
                        </>
                      )}
                    </div>
                  );

                  const sigCardClass =
                    !isLandscape
                      ? 'w-[168px]'
                      : indexedVisibleSignatories.length >= 3
                      ? 'w-[154px]'
                      : 'w-[186px]';

                  const signaturesGroup = (
                    <div className="flex items-end justify-center gap-4 px-1">
                      {/* Signatory #1 */}
                      {indexedVisibleSignatories[0] && (
                        <div
                          onClick={() => onOpenSignatureModal?.(indexedVisibleSignatories[0].originalIndex)}
                          className={`${sigCardClass} flex flex-col items-center text-center cursor-pointer group pb-1 px-0.5`}
                          title="Click to edit Signatory #1"
                        >
                          <div className="h-11 flex items-end justify-center w-full mb-1">
                            {indexedVisibleSignatories[0].sig.customSignatureData ? (
                              <img
                                src={indexedVisibleSignatories[0].sig.customSignatureData}
                                alt={indexedVisibleSignatories[0].sig.name}
                                className="max-h-11 w-auto object-contain"
                              />
                            ) : (
                              <span
                                className={`${
                                  indexedVisibleSignatories[0].sig.signatureStyle === 'great-vibes' ? 'font-script' : 'font-signature'
                                } text-[29px] leading-none select-none transition-transform group-hover:scale-105`}
                                style={{ color: signatureInk }}
                              >
                                {indexedVisibleSignatories[0].sig.name.replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/, '')}
                              </span>
                            )}
                          </div>
                          <div className="w-full h-[1.4px]" style={{ backgroundColor: signatureInk }} />
                          <div
                            className="mt-1.5 text-[12.5px] font-bold tracking-[0.04em] uppercase font-sans-clean leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryName', { color: primaryNavy })}
                          >
                            {data.languageMode === 'khmer' && indexedVisibleSignatories[0].sig.khmerName
                              ? indexedVisibleSignatories[0].sig.khmerName
                              : indexedVisibleSignatories[0].sig.name}
                          </div>
                          {indexedVisibleSignatories[0].sig.role && (
                            <div
                              className="mt-0.5 text-[10.5px] font-semibold font-cormorant leading-tight"
                              style={getFieldInlineStyle(data, 'signatoryRole', { color: primaryNavy })}
                            >
                              {data.languageMode === 'khmer' && indexedVisibleSignatories[0].sig.khmerRole
                                ? indexedVisibleSignatories[0].sig.khmerRole
                                : indexedVisibleSignatories[0].sig.role}
                            </div>
                          )}
                          {indexedVisibleSignatories[0].sig.organization && (
                            <div
                              className="mt-0.5 text-[9.5px] font-medium font-cormorant tracking-wide leading-tight"
                              style={getFieldInlineStyle(data, 'signatoryRole', { color: mutedTextColor })}
                            >
                              {indexedVisibleSignatories[0].sig.organization}
                            </div>
                          )}
                        </div>
                      )}

                      {/* Center Official Seal / Stamp (Fixed 132x132 footprint even when disabled) */}
                      <div
                        className="w-[132px] h-[132px] flex items-center justify-center shrink-0 cursor-pointer group hover:scale-105 transition-transform -mb-2 px-0.5"
                        onClick={() => onEditField?.('seal')}
                        title={
                          data.seal.enabled && data.seal.style !== 'none'
                            ? 'Click to customize official gold seal'
                            : 'Stamp space reserved — click to enable digital seal'
                        }
                      >
                        {data.seal.enabled && data.seal.style !== 'none' ? (
                          <CertificateSeal
                            seal={data.seal}
                            primaryColor={primaryNavy}
                            accentColor={data.styling.accentColor}
                          />
                        ) : (
                          <div className="no-print w-[104px] h-[104px] rounded-full border border-dashed border-amber-500/40 flex items-center justify-center text-[9px] font-bold uppercase tracking-wider text-amber-700/70 opacity-0 group-hover:opacity-100 transition-opacity select-none">
                            Stamp Area
                          </div>
                        )}
                      </div>

                      {/* Signatory #2+ (HR Signature & Any Additional Signatories) */}
                      {indexedVisibleSignatories.slice(1).map(({ sig, originalIndex }) => (
                        <div
                          key={sig.id || originalIndex}
                          onClick={() => onOpenSignatureModal?.(originalIndex)}
                          className={`${sigCardClass} flex flex-col items-center text-center cursor-pointer group pb-1 px-0.5`}
                          title={`Click to customize ${sig.role || 'HR Signature'}`}
                        >
                          <div className="h-11 flex items-end justify-center w-full mb-1">
                            {sig.customSignatureData ? (
                              <img
                                src={sig.customSignatureData}
                                alt={sig.name}
                                className="max-h-11 w-auto object-contain"
                              />
                            ) : (
                              <span
                                className={`${
                                  sig.signatureStyle === 'great-vibes' ? 'font-script' : 'font-signature'
                                } text-[29px] leading-none select-none transition-transform group-hover:scale-105`}
                                style={{ color: signatureInk }}
                              >
                                {sig.name.replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/, '')}
                              </span>
                            )}
                          </div>
                          <div className="w-full h-[1.4px]" style={{ backgroundColor: signatureInk }} />
                          <div
                            className="mt-1.5 text-[12.5px] font-bold tracking-[0.04em] uppercase font-sans-clean leading-tight"
                            style={getFieldInlineStyle(data, 'signatoryName', { color: primaryNavy })}
                          >
                            {data.languageMode === 'khmer' && sig.khmerName ? sig.khmerName : sig.name}
                          </div>
                          {sig.role && (
                            <div
                              className="mt-0.5 text-[10.5px] font-semibold font-cormorant leading-tight"
                              style={getFieldInlineStyle(data, 'signatoryRole', { color: primaryNavy })}
                            >
                              {data.languageMode === 'khmer' && sig.khmerRole ? sig.khmerRole : sig.role}
                            </div>
                          )}
                          {sig.organization && (
                            <div
                              className="mt-0.5 text-[9.5px] font-medium font-cormorant tracking-wide leading-tight"
                              style={getFieldInlineStyle(data, 'signatoryRole', { color: mutedTextColor })}
                            >
                              {sig.organization}
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  );

                  return isLandscape ? (
                    <div className={`relative flex items-end ${indexedVisibleSignatories.length === 2 ? 'justify-center' : 'justify-center gap-3'} w-full pt-2 pb-1`}>
                      {dateBlock}
                      {signaturesGroup}
                    </div>
                  ) : (
                    <div className="flex flex-col items-center w-full gap-4 pt-2 pb-1">
                      {signaturesGroup}
                      <div className="w-full flex items-end justify-between px-4 pt-2 border-t border-[#C89328]/25">
                        {dateBlock}
                        {vis.showCertificateId !== false && data.credentialId && (
                          <div className="text-right font-mono-code text-[10px] opacity-80" style={{ color: primaryNavy }}>
                            ID: {data.credentialId}
                          </div>
                        )}
                      </div>
                    </div>
                  );
                })()
              )}
            </div>
          </>
        ) : (
          <>
            {/* =========================================================================
                BRANCH B: CLASSIC INSTITUTIONAL HERITAGE TEMPLATE
            ========================================================================= */}
            {/* Background Watermark & Guilloche Security Pattern */}
            {data.styling.showWatermark && (
              <WatermarkPatternLayer
                type={data.styling.watermarkType || 'crest'}
                opacity={data.styling.watermarkOpacity || 0.08}
                primaryColor={data.styling.primaryColor}
                accentColor={data.styling.accentColor}
                issuerName={data.issuer.name}
                issuerDepartment={data.issuer.department}
              />
            )}

            {/* Optional Top-Left / Top-Right Staff Profile Photo in Classic Layout */}
            {hasProfilePhoto && (photoPosition === 'top-right' || photoPosition === 'top-left') && (
              <div
                onPointerDown={handlePhotoPointerDown}
                onClickCapture={handlePhotoClickCapture}
                style={photoFrameTransformStyle}
                className={`group/photo touch-none absolute z-20 ${
                  dragPhotoOffset ? 'cursor-grabbing' : 'cursor-grab'
                } ${
                  isLandscape
                    ? photoPosition === 'top-right'
                      ? 'top-[74px] right-[104px]'
                      : 'top-[74px] left-[104px]'
                    : photoPosition === 'top-right'
                    ? 'top-[68px] right-[74px]'
                    : 'top-[68px] left-[74px]'
                }`}
              >
                <div
                  className={`no-print pointer-events-none absolute -top-5 left-1/2 -translate-x-1/2 z-40 px-2 py-0.5 rounded-full bg-slate-900/90 text-white text-[9px] font-bold tracking-wide whitespace-nowrap flex items-center gap-1 shadow-sm transition-opacity ${
                    dragPhotoOffset ? 'opacity-100' : 'opacity-0 group-hover/photo:opacity-100'
                  }`}
                >
                  <Move className="w-2.5 h-2.5 text-amber-400" />
                  <span>
                    {dragPhotoOffset
                      ? `X: ${activeFrameOffsetX > 0 ? '+' : ''}${activeFrameOffsetX}px · Y: ${activeFrameOffsetY > 0 ? '+' : ''}${activeFrameOffsetY}px`
                      : 'Drag to move'}
                  </span>
                </div>
                {renderProfilePhotoBadge(!isLandscape)}
              </div>
            )}

            {/* Main Certificate Content Canvas (A4 1123x794 / 794x1123) */}
            <div
              className={`relative z-10 w-full h-full flex flex-col justify-between text-center ${
                isLandscape ? 'px-16 pt-8 pb-7' : 'px-11 pt-10 pb-10'
              }`}
            >
          
          {/* Top Zone: Issuer & Insignia */}
          <div className="flex flex-col items-center pt-0.5">
            {(vis.showLogo !== false || vis.showIssuerName !== false || vis.showIssuerDepartment !== false) && (
              <div
                className="cursor-pointer group transition-transform hover:scale-[1.02]"
                onClick={() => onEditField?.('issuer')}
                title="Click to edit issuing institution and logo"
              >
                {vis.showLogo !== false && (
                  <IssuerLogo
                    issuer={data.issuer}
                    primaryColor={data.styling.primaryColor}
                    accentColor={data.styling.accentColor}
                    scale={(data.styling.logoScale || 1) * (isLandscape ? 0.92 : 1)}
                    showLaurelWreath={data.styling.showLaurelWreath !== false}
                    logoOrnament={data.styling.logoOrnament}
                  />
                )}
                {vis.showIssuerName !== false && (
                  <h4
                    className="mt-1 text-xs md:text-sm font-bold tracking-[0.22em] uppercase font-cinzel leading-tight"
                    style={getFieldInlineStyle(data, 'issuerName', { color: data.styling.primaryColor })}
                  >
                    {data.issuer.name}
                  </h4>
                )}
                {vis.showIssuerDepartment !== false && data.issuer.department && (
                  <p
                    className="text-[10px] font-semibold tracking-[0.14em] uppercase font-sans-clean mt-0.5 opacity-80 leading-tight"
                    style={getFieldInlineStyle(data, 'issuerDepartment', { color: subtextColor })}
                  >
                    {data.issuer.department}
                  </p>
                )}
              </div>
            )}

            {/* Selectable Ornamental Divider Beneath Logo */}
            {vis.showHeaderDivider !== false && (data.styling.dividerOrnament || 'baroque-fleur') !== 'none' && (
              <div
                className="mt-1 flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity"
                onClick={() => onEditField?.('dividerOrnament')}
                title="Click to customize divider ornament (ក្បាច់ខណ្ឌក្រោម Logo)"
              >
                <LogoDividerOrnament
                  style={data.styling.dividerOrnament || 'baroque-fleur'}
                  accentColor={data.styling.dividerColor || data.styling.accentColor}
                  primaryColor={data.styling.primaryColor}
                  customUrl={data.styling.customDividerUrl}
                  width={data.styling.dividerWidth}
                  scale={data.styling.dividerScale ?? 1}
                  offsetY={data.styling.dividerOffsetY ?? 0}
                  opacity={data.styling.dividerOpacity ?? 1}
                  className={isLandscape ? 'w-[240px] h-[20px]' : 'w-[260px] h-[24px]'}
                />
              </div>
            )}
          </div>

          {/* Institutional Authority Preamble Decree (Khmer & English) */}
          {vis.showAuthorityPreamble !== false && (data.khmerPreamble || data.authorityPreamble) && (
            <div
              onClick={() => onEditField?.('authorityPreamble')}
              className="flex flex-col items-center justify-center cursor-pointer group"
              title="Click to edit institutional authority decree"
            >
              {data.khmerPreamble && (
                <div className="flex items-center justify-center gap-2">
                  <div className="h-[1px] w-6 md:w-14 bg-gradient-to-r from-transparent to-amber-500/70" />
                  <span
                    className="text-[10.5px] font-bold font-khmer-moul tracking-wide text-amber-900 dark:text-amber-200 leading-tight"
                    style={getFieldInlineStyle(data, 'authorityPreamble')}
                  >
                    {data.khmerPreamble}
                  </span>
                  <div className="h-[1px] w-6 md:w-14 bg-gradient-to-l from-transparent to-amber-500/70" />
                </div>
              )}
              {data.authorityPreamble && data.languageMode !== 'khmer' && (
                <div
                  className="text-[8.5px] font-bold tracking-[0.22em] uppercase font-cinzel text-amber-800/80 dark:text-amber-300/80 leading-tight"
                  style={getFieldInlineStyle(data, 'authorityPreamble')}
                >
                  {data.authorityPreamble}
                </div>
              )}
            </div>
          )}

          {/* Certificate Title & Subtitle Zone */}
          {(vis.showKhmerTitle !== false || vis.showEnglishTitle !== false || vis.showSubtitle !== false) && (
            <div className="space-y-0.5 relative">
              {showTitleLaurels && isLandscape && (
                <div className="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 w-[560px] flex items-center justify-between opacity-70">
                  <LogoFlankingOrnament
                    style="classic-laurel"
                    accentColor={data.styling.accentColor}
                    primaryColor={data.styling.primaryColor}
                    className="w-[560px] h-[96px] overflow-visible"
                  />
                </div>
              )}

              {/* Royal Khmer Title */}
              {vis.showKhmerTitle !== false && data.khmerTitle && (
                <h2
                  onClick={() => onEditField?.('khmerTitle')}
                  className={`font-khmer-moul ${isLandscape ? 'text-xl' : 'text-2xl'} text-amber-900 dark:text-amber-200 cursor-pointer hover:opacity-85 transition-opacity leading-snug drop-shadow-xs`}
                  style={getFieldInlineStyle(data, 'khmerTitle')}
                  title="Click to edit Khmer certificate title"
                >
                  {data.khmerTitle}
                </h2>
              )}

              {/* English Title */}
              {vis.showEnglishTitle !== false && data.languageMode !== 'khmer' && (
                <h1
                  onClick={() => onEditField?.('certificateTitle')}
                  className={`${displayFontClass} ${isLandscape ? 'text-xl' : 'text-2xl'} font-bold tracking-[0.08em] uppercase cursor-pointer hover:opacity-80 transition-opacity drop-shadow-xs leading-tight`}
                  style={getFieldInlineStyle(data, 'certificateTitle', {
                    color: isDark ? '#38BDF8' : data.styling.primaryColor,
                    fontSize: data.styling.titleFontSize
                      ? `${isLandscape ? Math.min(data.styling.titleFontSize, 26) : data.styling.titleFontSize}px`
                      : undefined,
                  })}
                  title="Click to edit certificate title"
                >
                  {data.certificateTitle}
                </h1>
              )}

              {/* Subtitle Tagline */}
              {vis.showSubtitle !== false && (data.khmerSubtitle || data.subtitle) && (
                <div className="space-y-0.5">
                  {data.khmerSubtitle && (
                    <p
                      onClick={() => onEditField?.('khmerSubtitle')}
                      className="font-khmer-sans text-[12px] text-slate-700 dark:text-slate-300 cursor-pointer hover:opacity-80 leading-snug"
                      style={getFieldInlineStyle(data, 'subtitle')}
                    >
                      {data.khmerSubtitle}
                    </p>
                  )}
                  {data.subtitle && data.languageMode !== 'khmer' && (
                    <p
                      onClick={() => onEditField?.('subtitle')}
                      className={`${bodyFontClass} text-[13px] italic cursor-pointer hover:opacity-80 transition-opacity font-normal tracking-wide leading-snug`}
                      style={getFieldInlineStyle(data, 'subtitle', { color: mutedTextColor })}
                      title="Click to edit subtitle"
                    >
                      {data.subtitle}
                    </p>
                  )}
                </div>
              )}
            </div>
          )}

          {/* Recipient Zone with Classical Calligraphic Flourishes */}
          {(vis.showKhmerRecipientName !== false ||
            vis.showEnglishRecipientName !== false ||
            vis.showRecipientDivider !== false) && (
            <div
              onClick={() => onEditField?.('recipientName')}
              className="cursor-pointer group"
              title="Click to edit recipient name"
            >
              {vis.showOrganizationSubBanner !== false && (data.khmerRecipientTitle || data.recipientTitle) && (
                <div className="space-y-0.5 mb-0.5">
                  {data.khmerRecipientTitle && (
                    <p className="font-khmer-sans text-[10px] font-bold text-amber-800 dark:text-amber-300 leading-tight">
                      {data.khmerRecipientTitle}
                    </p>
                  )}
                  {data.recipientTitle && data.languageMode !== 'khmer' && (
                    <p
                      className="text-[9px] font-bold uppercase tracking-[0.22em] font-sans-clean leading-tight"
                      style={{ color: data.styling.accentColor }}
                    >
                      {data.recipientTitle}
                    </p>
                  )}
                </div>
              )}

              {(vis.showKhmerRecipientName !== false || vis.showEnglishRecipientName !== false) && (
                <div className="relative inline-flex items-center justify-center gap-4 px-8">
                  {/* Left Flourish Ornament */}
                  <span className="text-amber-500/70 font-serif text-lg hidden md:inline select-none">
                    ~ ❧
                  </span>

                  {hasProfilePhoto && photoPosition === 'beside-name' && (
                    <div onClick={(e) => e.stopPropagation()}>
                      {renderProfilePhotoBadge(true)}
                    </div>
                  )}

                  <div className="flex flex-col items-center">
                    {vis.showKhmerRecipientName !== false && data.khmerRecipientName && (
                      <h2
                        className={`font-khmer-moul ${isLandscape ? 'text-2xl' : 'text-3xl'} leading-tight`}
                        style={getFieldInlineStyle(data, 'khmerRecipientName', {
                          color: isDark ? '#FFFFFF' : data.styling.primaryColor,
                        })}
                      >
                        {data.khmerRecipientName}
                      </h2>
                    )}
                    {vis.showEnglishRecipientName !== false && data.recipientName && (
                      <h2
                        className={`${displayFontClass} text-2xl md:text-3xl font-bold tracking-normal group-hover:scale-[1.01] transition-transform leading-none`}
                        style={getFieldInlineStyle(data, 'recipientName', {
                          color: isDark ? '#FFFFFF' : data.styling.primaryColor,
                          textShadow: '0 1px 2px rgba(0,0,0,0.06)',
                          fontSize: data.styling.recipientFontSize
                            ? `${isLandscape ? Math.min(data.styling.recipientFontSize, 34) : data.styling.recipientFontSize}px`
                            : undefined,
                        })}
                      >
                        {data.recipientName}
                      </h2>
                    )}
                  </div>

                  {/* Right Flourish Ornament */}
                  <span className="text-amber-500/70 font-serif text-lg hidden md:inline select-none">
                    ☙ ~
                  </span>
                </div>
              )}

              {/* Dual Multi-stop Gold Underline with Center Diamond */}
              {vis.showRecipientDivider !== false && (
                <div className="flex items-center justify-center gap-2 max-w-md mx-auto mt-1">
                  <div
                    className="h-[1.5px] flex-1 opacity-75"
                    style={{
                      background: `linear-gradient(90deg, transparent, ${data.styling.accentColor}, transparent)`,
                    }}
                  />
                  <div
                    className="w-1.5 h-1.5 rotate-45 shadow-2xs"
                    style={{ backgroundColor: data.styling.accentColor }}
                  />
                  <div
                    className="h-[1.5px] flex-1 opacity-75"
                    style={{
                      background: `linear-gradient(90deg, transparent, ${data.styling.accentColor}, transparent)`,
                    }}
                  />
                </div>
              )}
            </div>
          )}

          {/* Executive Engraved CPD & KNEAYERNG HRMS Accreditation Plaque */}
          {data.digitalHr?.showDigitalHoursBadge && (
            <div
              onClick={() => onEditField?.('digitalHr')}
              className="relative mx-auto px-5 py-1 rounded-xs border-2 border-double border-amber-600/40 bg-gradient-to-b from-amber-500/5 via-amber-500/10 to-amber-500/5 shadow-2xs cursor-pointer hover:border-amber-600/70 hover:scale-[1.01] transition-all group max-w-xl"
              title="Click to manage KNEAYERNG HRMS Accredited Training Hours & Compliance"
            >
              <span className="absolute -top-1.5 -left-1.5 text-amber-600 text-[10px] select-none font-serif">✤</span>
              <span className="absolute -top-1.5 -right-1.5 text-amber-600 text-[10px] select-none font-serif">✤</span>
              <span className="absolute -bottom-1.5 -left-1.5 text-amber-600 text-[10px] select-none font-serif">✤</span>
              <span className="absolute -bottom-1.5 -right-1.5 text-amber-600 text-[10px] select-none font-serif">✤</span>

              <div className="flex items-center justify-between gap-4">
                <div className="flex items-center gap-2.5 text-amber-700 dark:text-amber-400">
                  <span className="text-sm font-serif">⚜</span>
                  <div className="text-left">
                    <div className="text-[8px] font-bold tracking-[0.16em] uppercase font-cinzel text-amber-900/80 dark:text-amber-200/80 leading-none">
                      {data.languageMode === 'khmer' ? 'ការទទួលស្គាល់ការអភិវឌ្ឍវិជ្ជាជីវៈបន្ត (CPD)' : 'Accredited Continuing Professional Development (CPD)'}
                    </div>
                    <div className="text-[11.5px] font-extrabold tracking-wider font-cinzel text-amber-950 dark:text-amber-100 mt-0.5 leading-none">
                      {data.digitalHr.trainingHours}.0 ម៉ោងបណ្តុះបណ្តាលឌីជីថល / KNEAYERNG HRMS HOURS
                    </div>
                  </div>
                </div>

                <div className="h-5 w-[1px] bg-amber-600/30" />

                <div className="text-right text-[9.5px] font-sans-clean">
                  <div className="font-mono-code text-amber-800 dark:text-amber-300 font-semibold leading-tight">
                    REG: {data.digitalHr.employeeId}
                  </div>
                  <div className="font-semibold text-amber-700 dark:text-amber-400 text-[8.5px] uppercase tracking-wider font-khmer-sans leading-tight">
                    {data.digitalHr.complianceStatus}
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Course or Achievement Title & Commendation Citation */}
          {(vis.showAchievementTitle !== false ||
            vis.showKhmerCitation !== false ||
            vis.showEnglishCitation !== false) && (
            <div className="max-w-2xl mx-auto space-y-0.5">
              {vis.showAchievementTitle !== false && data.khmerCourseOrAchievementTitle && (
                <h3
                  onClick={() => onEditField?.('khmerCourseOrAchievementTitle')}
                  className="font-khmer-moul text-[15px] text-amber-900 dark:text-amber-200 cursor-pointer hover:opacity-85 leading-snug"
                  style={getFieldInlineStyle(data, 'courseOrAchievementTitle')}
                >
                  {data.khmerCourseOrAchievementTitle}
                </h3>
              )}
              {vis.showAchievementTitle !== false && data.courseOrAchievementTitle && data.languageMode !== 'khmer' && (
                <h3
                  onClick={() => onEditField?.('courseOrAchievementTitle')}
                  className={`${bodyFontClass} text-[14.5px] font-bold tracking-wide cursor-pointer hover:opacity-80 transition-opacity leading-snug`}
                  style={getFieldInlineStyle(data, 'courseOrAchievementTitle', {
                    color: isDark ? '#38BDF8' : data.styling.primaryColor,
                  })}
                  title="Click to edit course or award title"
                >
                  {data.courseOrAchievementTitle}
                </h3>
              )}

              {vis.showKhmerCitation !== false && data.khmerCitation && (
                <p
                  onClick={() => onEditField?.('khmerCitation')}
                  className={`font-khmer-sans ${isLandscape ? 'text-[11.5px] leading-[1.45]' : 'text-[13px] leading-relaxed'} px-4 cursor-pointer hover:opacity-80 text-slate-800 dark:text-slate-200 font-normal`}
                  style={getFieldInlineStyle(data, 'khmerCitation')}
                >
                  « {data.khmerCitation} »
                </p>
              )}
              {vis.showEnglishCitation !== false && data.citation && data.languageMode !== 'khmer' && (
                <p
                  onClick={() => onEditField?.('citation')}
                  className={`${bodyFontClass} ${isLandscape ? 'text-[12.5px] leading-[1.32]' : 'text-[14px] leading-relaxed'} px-4 cursor-pointer hover:opacity-80 transition-opacity font-normal italic`}
                  style={getFieldInlineStyle(data, 'citation', { color: mutedTextColor })}
                  title="Click to edit citation"
                >
                  "{data.citation}"
                </p>
              )}
            </div>
          )}

          {/* Quantitative Distinction Metrics Badges */}
          {vis.showMetrics !== false && data.metrics && data.metrics.length > 0 && (
            <div className="flex items-center justify-center flex-wrap gap-3">
              {data.metrics.map((m, i) => (
                <div
                  key={m.id || i}
                  onClick={() => onEditField?.('metrics')}
                  className="flex items-center gap-1.5 text-xs cursor-pointer hover:opacity-80 transition-opacity px-2.5 py-0.5 rounded-xs border border-amber-600/25 bg-amber-50/40 dark:bg-amber-950/20 shadow-2xs"
                >
                  <span
                    className="font-bold uppercase tracking-wider text-[8.5px] font-sans-clean"
                    style={{ color: subtextColor }}
                  >
                    {m.label}:
                  </span>
                  <span
                    className="font-bold tabular-nums font-mono-code text-[10px]"
                    style={{ color: isDark ? '#E2E8F0' : data.styling.primaryColor }}
                  >
                    {m.value}
                  </span>
                </div>
              ))}
            </div>
          )}

          {/* Signatures Row */}
          {vis.showSignatories !== false && (
            <div>
              <CertificateSignatures
                signatories={data.signatories}
                primaryColor={data.styling.primaryColor}
                accentColor={data.styling.accentColor}
                signatureInkColor={data.styling.signatureInkColor}
                isDarkTheme={isDark}
                compact={isLandscape}
                isPortrait={!isLandscape}
                fieldStyles={data.styling.fieldStyles}
                onEditSignature={onOpenSignatureModal}
              />
            </div>
          )}

          {/* Bottom Footnote Zone: QR Verification, 3D Gold Seal, Authentication Code */}
          <div className="flex items-end justify-between w-full pt-1.5 border-t border-amber-600/20 dark:border-white/10">
            
            {/* Left: Credential Verification & QR */}
            <div className="flex items-center gap-2.5 text-left min-w-[140px]">
              {data.showQrCode && qrDataUrl && (
                <div
                  onClick={onOpenVerifyModal}
                  className="p-1 rounded-sm bg-white/90 dark:bg-slate-800/90 shadow-2xs border border-amber-500/30 cursor-pointer hover:scale-105 transition-transform"
                  title="Click to verify credential authenticity"
                >
                  <img
                    src={qrDataUrl}
                    alt="Credential Verification QR"
                    className="object-contain"
                    style={{
                      width: `${Math.round((data.qrSize || 64) * 0.65)}px`,
                      height: `${Math.round((data.qrSize || 64) * 0.65)}px`,
                    }}
                  />
                </div>
              )}
              <div className="text-[9px] space-y-0.5 font-sans-clean leading-tight" style={{ color: subtextColor }}>
                {vis.showIssueDate !== false && (
                  <div>
                    <span className="font-bold text-slate-800 dark:text-slate-200">Date Issued:</span> {data.issueDate}
                  </div>
                )}
                {vis.showIssueDate !== false && data.expiryDate && (
                  <div>
                    <span className="font-bold text-slate-800 dark:text-slate-200">Validity:</span> {data.expiryDate}
                  </div>
                )}
                {vis.showCertificateId !== false && (
                  <div className="font-mono-code tabular-nums text-[8px] opacity-80">
                    ID: {data.credentialId}
                  </div>
                )}
              </div>
            </div>

            {/* Center: 3D Embossed Gold Foil Seal (Static slot fitted cleanly within A4 bounds) */}
            <div
              className={`${isLandscape ? 'w-[104px] h-[104px]' : 'w-[132px] h-[132px]'} flex items-center justify-center shrink-0 cursor-pointer group hover:scale-105 transition-transform`}
              onClick={() => onEditField?.('seal')}
              title={
                data.seal.enabled && data.seal.style !== 'none'
                  ? 'Click to customize official 3D seal'
                  : 'Stamp space reserved — click to enable digital seal'
              }
            >
              <div className={isLandscape ? 'scale-[0.78] origin-center' : undefined}>
                <CertificateSeal
                  seal={data.seal}
                  primaryColor={data.styling.primaryColor}
                  accentColor={data.styling.accentColor}
                />
              </div>
            </div>

            {/* Right: Security & Holographic Authenticity Marker */}
            {vis.showSecurityFooter !== false ? (
              <div className="text-right text-[9px] font-sans-clean leading-tight min-w-[140px]" style={{ color: subtextColor }}>
                <div className="font-bold tracking-widest uppercase font-cinzel text-amber-700 dark:text-amber-400">
                  Official Credential
                </div>
                <div className="text-[8px] opacity-80">
                  Cryptographically Authenticated
                </div>
                <div className="font-mono-code text-[8px] opacity-70">
                  HASH: {data.credentialId.replace(/[^A-Z0-9]/g, '').slice(0, 12)}
                </div>
              </div>
            ) : (
              <div className="min-w-[140px]" />
            )}

          </div>

          {/* Microprint Anti-Counterfeit Banknote Security Text Line */}
          {vis.showSecurityFooter !== false && (
            <div className="w-full text-center overflow-hidden pt-1 border-t border-amber-600/10">
              <p className="text-[7px] font-mono-code uppercase tracking-[0.2em] opacity-40 truncate select-none text-slate-700 dark:text-slate-400">
                ★ OFFICIAL ACCREDITED CREDENTIAL · KNEAYERNG HRMS AUDIT REPOSITORY · {data.issuer.name} · VERIFICATION HASH: {data.credentialId.replace(/[^A-Z0-9]/g, '').slice(0, 12)} · TAMPER-EVIDENT PERPETUAL RECORD ★
              </p>
            </div>
          )}

        </div>
          </>
        )}
      </div>
    </div>
  );
};
