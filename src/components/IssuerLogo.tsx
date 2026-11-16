import React from 'react';
import { CertificateData, LogoOrnamentStyle } from '../types/certificate';
import { LogoFlankingOrnament } from './LogoOrnaments';

interface IssuerLogoProps {
  issuer: CertificateData['issuer'];
  primaryColor: string;
  accentColor: string;
  scale?: number;
  showLaurelWreath?: boolean;
  logoOrnament?: LogoOrnamentStyle;
  variant?: 'default' | 'executive-navy-ribbon' | 'executive-verify';
}

export const KMonogramCrest: React.FC<{
  primaryColor?: string;
  accentColor?: string;
  size?: number;
}> = ({ primaryColor = '#092653', accentColor = '#D4A32C', size = 62 }) => (
  <svg
    width={size}
    height={size}
    viewBox="0 0 80 80"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    className="drop-shadow-xs"
  >
    <defs>
      <linearGradient id="kGoldFacet" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stopColor="#F9DF86" />
        <stop offset="48%" stopColor={accentColor} />
        <stop offset="100%" stopColor="#9E6E14" />
      </linearGradient>
      <linearGradient id="kNavyFacet" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stopColor="#123A78" />
        <stop offset="65%" stopColor={primaryColor} />
        <stop offset="100%" stopColor="#051736" />
      </linearGradient>
    </defs>
    {/* Vertical Left Pillar */}
    <polygon points="15,8 30,8 30,72 15,72" fill="url(#kNavyFacet)" />
    {/* Subtle Gold Accent Edge on Pillar */}
    <polygon points="15,8 17.5,8 17.5,72 15,72" fill="url(#kGoldFacet)" opacity="0.65" />
    {/* Upper Gold-to-Navy Diagonal Arm */}
    <polygon points="48,8 65,8 31,45 31,27" fill="url(#kNavyFacet)" />
    {/* Gold Inner Bevel Fold on Upper Diagonal */}
    <polygon points="48,8 54,8 31,34 31,27" fill="url(#kGoldFacet)" />
    {/* Lower Gold Diagonal Leg */}
    <polygon points="36,39 46,30 66,72 49,72" fill="url(#kNavyFacet)" />
    {/* Gold Highlight Facet on Lower Diagonal */}
    <polygon points="31,45 38,37 52,72 42,72" fill="url(#kGoldFacet)" />
  </svg>
);

export const IssuerLogo: React.FC<IssuerLogoProps> = ({
  issuer,
  primaryColor,
  accentColor,
  scale = 1,
  showLaurelWreath = true,
  logoOrnament,
  variant = 'default',
}) => {
  const isKMonogram =
    issuer.logoType === 'k-monogram' && !issuer.customLogoUrl;
  const isKneaYoeng =
    !isKMonogram &&
    (issuer.logoType === 'knea-yoeng' ||
      (!issuer.logoType && !issuer.customLogoUrl) ||
      issuer.customLogoUrl?.includes('unnamed_521b71dc'));
  const logoSrc = isKMonogram
    ? ''
    : issuer.customLogoUrl || (isKneaYoeng ? '/assets/aistudio/unnamed_521b71dc.jpg' : '');

  const effectiveOrnament: LogoOrnamentStyle =
    logoOrnament ?? (showLaurelWreath === false ? 'none' : 'classic-laurel');

  // Executive Verify Template Header (Geometric K Crest or Uploaded Logo flanked by Gold Laurels)
  if (variant === 'executive-verify') {
    return (
      <div
        className="relative mx-auto inline-flex flex-col items-center justify-center select-none transition-transform"
        style={{ transform: `scale(${scale})` }}
      >
        <div className="relative z-10 flex items-center justify-center">
          {issuer.customLogoUrl && issuer.logoType === 'custom' ? (
            <div className="w-[74px] h-[74px] rounded-full p-[2.5px] bg-gradient-to-tr from-[#A87518] via-[#F4D16B] to-[#D4A32C] shadow-sm">
              <div className="w-full h-full rounded-full overflow-hidden bg-white flex items-center justify-center">
                <img
                  src={issuer.customLogoUrl}
                  alt={issuer.name || 'KNEAYERNG Logo'}
                  className="w-full h-full object-cover rounded-full"
                  referrerPolicy="no-referrer"
                />
              </div>
            </div>
          ) : isKMonogram || !logoSrc ? (
            <KMonogramCrest primaryColor={primaryColor} accentColor={accentColor} size={62} />
          ) : (
            <div className="w-[74px] h-[74px] rounded-full p-[2.5px] bg-gradient-to-tr from-[#A87518] via-[#F4D16B] to-[#D4A32C] shadow-sm">
              <div className="w-full h-full rounded-full overflow-hidden bg-white flex items-center justify-center">
                <img
                  src={logoSrc}
                  alt={issuer.name || 'គ្នា​យើង Logo'}
                  className="w-full h-full object-cover rounded-full"
                  referrerPolicy="no-referrer"
                />
              </div>
            </div>
          )}
        </div>
      </div>
    );
  }

  // Executive Navy Ribbon Header Logo Presentation
  if (logoSrc && variant === 'executive-navy-ribbon') {
    return (
      <div
        className="relative mx-auto inline-flex flex-col items-center justify-center select-none transition-transform"
        style={{ transform: `scale(${scale})` }}
      >
        {/* Flanking Ornamental Motif Spanning Logo + Brand Name */}
        {effectiveOrnament !== 'none' && (
          <div className="absolute -top-2 left-1/2 -translate-x-1/2 pointer-events-none">
            <LogoFlankingOrnament
              style={effectiveOrnament}
              accentColor={accentColor}
              primaryColor={primaryColor}
              className="w-[280px] h-[135px] overflow-visible"
            />
          </div>
        )}

        {/* Crisp Circular Logo Container */}
        <div className="relative z-10 w-[86px] h-[86px] rounded-full p-[2.5px] bg-gradient-to-tr from-[#A87518] via-[#F2D678] to-[#C89328] shadow-sm">
          <div className="w-full h-full rounded-full overflow-hidden bg-white flex items-center justify-center">
            <img
              src={logoSrc}
              alt={issuer.name || 'គ្នា​យើង Logo'}
              className="w-full h-full object-cover rounded-full"
              referrerPolicy="no-referrer"
            />
          </div>
        </div>
      </div>
    );
  }

  // Regal Insignia Presentation for Brand Logo in Classic / Institutional Layouts
  if (logoSrc) {
    return (
      <div
        className="relative mx-auto inline-flex items-center justify-center select-none py-1 transition-transform"
        style={{ transform: `scale(${scale})` }}
      >
        {effectiveOrnament !== 'none' && (
          <div className="absolute -top-3 left-1/2 -translate-x-1/2 pointer-events-none">
            <LogoFlankingOrnament
              style={effectiveOrnament}
              accentColor={accentColor}
              primaryColor={primaryColor}
              className="w-[215px] h-[104px] overflow-visible"
            />
          </div>
        )}

        <svg
          width="136"
          height="88"
          viewBox="0 0 160 100"
          className="absolute inset-0 w-full h-full pointer-events-none overflow-visible"
        >
          <defs>
            <linearGradient id="logoGoldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stopColor="#DFB343" />
              <stop offset="30%" stopColor="#FFF2B2" />
              <stop offset="60%" stopColor="#C89B3C" />
              <stop offset="100%" stopColor="#8A6218" />
            </linearGradient>
            <radialGradient id="sunburstHalo" cx="50%" cy="50%" r="50%">
              <stop offset="0%" stopColor="#FFF9E0" stopOpacity="0.8" />
              <stop offset="70%" stopColor="#E5BE5E" stopOpacity="0.15" />
              <stop offset="100%" stopColor="#C89B3C" stopOpacity="0" />
            </radialGradient>
          </defs>

          {/* Golden Sunburst Halo behind circular logo */}
          <circle cx="80" cy="50" r="46" fill="url(#sunburstHalo)" />

          {/* Top Imperial Distinction Star */}
          <polygon
            points="80,4 82,10 88,10 83,14 85,20 80,16 75,20 77,14 72,10 78,10"
            fill="url(#logoGoldGradient)"
            stroke="#8A6218"
            strokeWidth="0.5"
          />
        </svg>

        {/* Circular Logo Container with Beveled Gold Frame */}
        <div className="relative z-10 w-[72px] h-[72px] rounded-full p-[3px] bg-gradient-to-tr from-amber-600 via-amber-200 to-amber-500 shadow-lg">
          <div className="w-full h-full rounded-full overflow-hidden bg-white border border-amber-300 flex items-center justify-center">
            <img
              src={logoSrc}
              alt={issuer.name || 'គ្នា​យើង Logo'}
              className="w-full h-full object-cover rounded-full"
              referrerPolicy="no-referrer"
            />
          </div>
        </div>
      </div>
    );
  }

  // Pure Scalable Vector Logos (if other logos chosen)
  if (issuer.logoType === 'academic') {
    return (
      <svg width="64" height="64" viewBox="0 0 64 64" className="mx-auto drop-shadow-md">
        <circle cx="32" cy="32" r="30" fill="none" stroke={primaryColor} strokeWidth="2.5" />
        <circle cx="32" cy="32" r="26" fill="none" stroke={accentColor} strokeWidth="1" strokeDasharray="3 2" />
        <path d="M 18 24 L 32 15 L 46 24 Z" fill={primaryColor} />
        <rect x="21" y="26" width="3" height="16" fill={accentColor} />
        <rect x="27" y="26" width="3" height="16" fill={accentColor} />
        <rect x="34" y="26" width="3" height="16" fill={accentColor} />
        <rect x="40" y="26" width="3" height="16" fill={accentColor} />
        <rect x="18" y="43" width="28" height="3" fill={primaryColor} />
        <rect x="15" y="47" width="34" height="2" fill={accentColor} />
      </svg>
    );
  }

  // Default: 'crest'
  return (
    <svg width="64" height="64" viewBox="0 0 64 64" className="mx-auto drop-shadow-md">
      <circle cx="32" cy="32" r="29" fill="none" stroke={primaryColor} strokeWidth="2.5" />
      <circle cx="32" cy="32" r="25" fill="none" stroke={accentColor} strokeWidth="1" strokeDasharray="4 2" />
      <path
        d="M 22 20 L 42 20 C 42 34 32 44 32 44 C 32 44 22 34 22 20 Z"
        fill={accentColor}
        fillOpacity="0.3"
        stroke={primaryColor}
        strokeWidth="1.5"
      />
      <line x1="32" y1="20" x2="32" y2="44" stroke={primaryColor} strokeWidth="1.2" />
      <line x1="22" y1="30" x2="42" y2="30" stroke={primaryColor} strokeWidth="1.2" />
      <circle cx="27" cy="25" r="2.5" fill={primaryColor} />
      <circle cx="37" cy="37" r="2.5" fill={primaryColor} />
    </svg>
  );
};
