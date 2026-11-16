import React, { useId } from 'react';
import { BorderStyleOption } from '../types/certificate';

interface BorderProps {
  style: BorderStyleOption;
  primaryColor: string;
  accentColor: string;
  isDarkTheme?: boolean;
  orientation?: 'landscape' | 'portrait';
}

const DEFAULT_NAVY_SET = new Set(['#092653', '#0A224E', '#091E42', '#0D2652']);
const DEFAULT_GOLD_SET = new Set(['#D4A32C', '#D4A13A', '#C89B3C', '#C59B27', '#F4D16B', '#E6BA4E']);

function shadeHex(hex: string, amount: number): string {
  const clean = (hex || '').replace('#', '');
  if (!/^[0-9a-fA-F]{6}$/.test(clean)) return hex;
  const clamp = (n: number) => Math.max(0, Math.min(255, Math.round(n)));
  const r = clamp(parseInt(clean.slice(0, 2), 16) + amount);
  const g = clamp(parseInt(clean.slice(2, 4), 16) + amount);
  const b = clamp(parseInt(clean.slice(4, 6), 16) + amount);
  return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
}

export const CertificateBorders: React.FC<BorderProps> = ({
  style,
  primaryColor,
  accentColor,
  isDarkTheme = false,
  orientation = 'landscape',
}) => {
  const uid = useId().replace(/:/g, '');
  if (style === 'none') return null;

  const isPortrait = orientation === 'portrait';
  const W = isPortrait ? 750 : 1060;
  const H = isPortrait ? 1060 : 750;
  const cx = W / 2;

  const normPrimary = (primaryColor || '#092653').toUpperCase();
  const normAccent = (accentColor || '#D4A32C').toUpperCase();
  const isCustomPrimary = !DEFAULT_NAVY_SET.has(normPrimary);
  const isCustomAccent = !DEFAULT_GOLD_SET.has(normAccent);

  const navyStop0 = isCustomPrimary ? shadeHex(normPrimary, -28) : '#04112A';
  const navyStop55 = isCustomPrimary ? normPrimary : '#081E47';
  const navyStop100 = isCustomPrimary ? shadeHex(normPrimary, 26) : '#0E2E63';

  const goldLight = isCustomAccent ? shadeHex(normAccent, 56) : '#FFF2B2';
  const goldMid = isCustomAccent ? normAccent : '#D4A13A';
  const goldDark = isCustomAccent ? shadeHex(normAccent, -42) : '#9E6E19';

  return (
    <svg
      className="absolute inset-0 w-full h-full pointer-events-none z-0 overflow-visible"
      xmlns="http://www.w3.org/2000/svg"
      preserveAspectRatio="none"
      viewBox={`0 0 ${W} ${H}`}
    >
      <defs>
        {/* Rich Multi-Stop Specular Gold Leaf Foil Gradient */}
        <linearGradient id={`luxuryGoldFoil-${uid}`} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor={isCustomAccent ? shadeHex(normAccent, -16) : '#C59332'} />
          <stop offset="15%" stopColor={isCustomAccent ? shadeHex(normAccent, 24) : '#ECC870'} />
          <stop offset="28%" stopColor={goldLight} />
          <stop offset="42%" stopColor={goldMid} />
          <stop offset="58%" stopColor={goldDark} />
          <stop offset="72%" stopColor={isCustomAccent ? shadeHex(normAccent, 24) : '#ECC870'} />
          <stop offset="85%" stopColor={isCustomAccent ? shadeHex(normAccent, 68) : '#FFF6CE'} />
          <stop offset="100%" stopColor={isCustomAccent ? shadeHex(normAccent, -34) : '#A87720'} />
        </linearGradient>

        {/* Secondary Shimmer Gradient for Hairlines */}
        <linearGradient id={`goldSheenHorizontal-${uid}`} x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stopColor={goldDark} stopOpacity="0.4" />
          <stop offset="20%" stopColor={isCustomAccent ? shadeHex(normAccent, 24) : '#ECC870'} />
          <stop offset="50%" stopColor={goldLight} />
          <stop offset="80%" stopColor={isCustomAccent ? shadeHex(normAccent, 24) : '#ECC870'} />
          <stop offset="100%" stopColor={goldDark} stopOpacity="0.4" />
        </linearGradient>

        {/* Radial Gold Flare for Corner Rosettes */}
        <radialGradient id={`goldRadialBurst-${uid}`} cx="50%" cy="50%" r="50%">
          <stop offset="0%" stopColor="#FFFDF0" />
          <stop offset="35%" stopColor={isCustomAccent ? shadeHex(normAccent, 38) : '#F5D77F'} />
          <stop offset="70%" stopColor={goldMid} />
          <stop offset="100%" stopColor={goldDark} />
        </radialGradient>

        {/* Deep Royal Navy Gradients for Executive Ribbon Borders */}
        <linearGradient id={`navySweepDark-${uid}`} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor={navyStop0} />
          <stop offset="55%" stopColor={navyStop55} />
          <stop offset="100%" stopColor={navyStop100} />
        </linearGradient>

        <linearGradient id={`navySweepMid-${uid}`} x1="0%" y1="100%" x2="100%" y2="0%">
          <stop offset="0%" stopColor={navyStop0} />
          <stop offset="50%" stopColor={navyStop55} />
          <stop offset="100%" stopColor={navyStop100} />
        </linearGradient>

        {/* Deep Emerald Gradients for Emerald Royal Ribbon */}
        <linearGradient id={`emeraldSweepDark-${uid}`} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#022C22" />
          <stop offset="55%" stopColor="#064E3B" />
          <stop offset="100%" stopColor="#065F46" />
        </linearGradient>

        <linearGradient id={`emeraldSweepMid-${uid}`} x1="0%" y1="100%" x2="100%" y2="0%">
          <stop offset="0%" stopColor="#03382B" />
          <stop offset="50%" stopColor="#047857" />
          <stop offset="100%" stopColor="#059669" />
        </linearGradient>

        {/* Deep Crimson Gradients for Crimson Imperial Ribbon */}
        <linearGradient id={`crimsonSweepDark-${uid}`} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#3B0718" />
          <stop offset="55%" stopColor="#5C0B24" />
          <stop offset="100%" stopColor="#7F1032" />
        </linearGradient>

        <linearGradient id={`crimsonSweepMid-${uid}`} x1="0%" y1="100%" x2="100%" y2="0%">
          <stop offset="0%" stopColor="#4A091E" />
          <stop offset="50%" stopColor="#881337" />
          <stop offset="100%" stopColor="#9F1239" />
        </linearGradient>

        {/* Rich Sweeping Metallic Gold Ribbon Foil */}
        <linearGradient id={`goldRibbonSweep-${uid}`} x1="0%" y1="100%" x2="100%" y2="0%">
          <stop offset="0%" stopColor={isCustomAccent ? shadeHex(normAccent, -16) : '#C89328'} />
          <stop offset="22%" stopColor={isCustomAccent ? shadeHex(normAccent, 32) : '#F5D77A'} />
          <stop offset="45%" stopColor={goldLight} />
          <stop offset="68%" stopColor={goldMid} />
          <stop offset="100%" stopColor={goldDark} />
        </linearGradient>

        {/* Banknote / Diploma Guilloche Wave Pattern */}
        <pattern id={`guillocheWeave-${uid}`} width="40" height="20" patternUnits="userSpaceOnUse">
          <path
            d="M 0 10 Q 10 0 20 10 T 40 10 M 0 10 Q 10 20 20 10 T 40 10"
            fill="none"
            stroke={accentColor}
            strokeWidth="0.5"
            strokeOpacity="0.25"
          />
        </pattern>

        {/* Royal Blue Banknote Guilloche Gradients & Patterns */}
        <linearGradient id={`blueGuillocheStroke-${uid}`} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#1E3A8A" />
          <stop offset="35%" stopColor="#2563EB" />
          <stop offset="65%" stopColor="#1D4ED8" />
          <stop offset="100%" stopColor="#1E3A8A" />
        </linearGradient>

        <linearGradient id={`navyNotchedFill-${uid}`} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor={navyStop0} />
          <stop offset="50%" stopColor={navyStop55} />
          <stop offset="100%" stopColor={navyStop100} />
        </linearGradient>

        <pattern id={`blueScallopH-${uid}`} width="32" height="28" patternUnits="userSpaceOnUse">
          <path
            d="M 0 14 Q 8 2 16 14 T 32 14 M 0 14 Q 8 26 16 14 T 32 14"
            fill="none"
            stroke="#1D4ED8"
            strokeWidth="0.95"
            strokeOpacity="0.82"
          />
          <path
            d="M -8 14 Q 0 2 8 14 T 24 14 T 40 14 M -8 14 Q 0 26 8 14 T 24 14 T 40 14"
            fill="none"
            stroke="#3B82F6"
            strokeWidth="0.7"
            strokeOpacity="0.65"
          />
          <path
            d="M 0 14 Q 8 7 16 14 T 32 14 M 0 14 Q 8 21 16 14 T 32 14"
            fill="none"
            stroke="#1E3A8A"
            strokeWidth="0.65"
            strokeOpacity="0.75"
          />
        </pattern>

        <pattern id={`blueScallopV-${uid}`} width="28" height="32" patternUnits="userSpaceOnUse">
          <path
            d="M 14 0 Q 2 8 14 16 T 14 32 M 14 0 Q 26 8 14 16 T 14 32"
            fill="none"
            stroke="#1D4ED8"
            strokeWidth="0.95"
            strokeOpacity="0.82"
          />
          <path
            d="M 14 -8 Q 2 0 14 8 T 14 24 T 14 40 M 14 -8 Q 26 0 14 8 T 14 24 T 14 40"
            fill="none"
            stroke="#3B82F6"
            strokeWidth="0.7"
            strokeOpacity="0.65"
          />
          <path
            d="M 14 0 Q 7 8 14 16 T 14 32 M 14 0 Q 21 8 14 16 T 14 32"
            fill="none"
            stroke="#1E3A8A"
            strokeWidth="0.65"
            strokeOpacity="0.75"
          />
        </pattern>

        {/* Subtle Embossed Shadow for Gold Corner Filigree & Borders */}
        <filter id={`goldEmboss-${uid}`} x="-15%" y="-15%" width="130%" height="130%">
          <feDropShadow dx="0.8" dy="1.2" stdDeviation="1.2" floodColor="#040D21" floodOpacity="0.45" />
        </filter>
        <filter id={`ribbonDrop-${uid}`} x="-10%" y="-10%" width="125%" height="125%">
          <feDropShadow dx="2" dy="3" stdDeviation="4" floodColor="#09152E" floodOpacity="0.28" />
        </filter>
      </defs>

      {/* =========================================================================
          STYLE 0A/0B/0C: EXECUTIVE SWEEPING RIBBON FRAMES (Navy, Emerald, Crimson)
      ========================================================================= */}
      {(style === 'navy-gold-ribbon' || style === 'emerald-royal-ribbon' || style === 'crimson-imperial-ribbon') && (() => {
        const outerStroke =
          style === 'emerald-royal-ribbon'
            ? '#064E3B'
            : style === 'crimson-imperial-ribbon'
            ? '#5C0B24'
            : navyStop55;
        const midFill =
          style === 'emerald-royal-ribbon'
            ? `url(#emeraldSweepMid-${uid})`
            : style === 'crimson-imperial-ribbon'
            ? `url(#crimsonSweepMid-${uid})`
            : `url(#navySweepMid-${uid})`;
        const darkFill =
          style === 'emerald-royal-ribbon'
            ? `url(#emeraldSweepDark-${uid})`
            : style === 'crimson-imperial-ribbon'
            ? `url(#crimsonSweepDark-${uid})`
            : `url(#navySweepDark-${uid})`;

        return (
          <g>
            {/* Outer Crisp Paper Edge Accent Hairline */}
            <rect
              x="10"
              y="10"
              width={W - 20}
              height={H - 20}
              fill="none"
              stroke={outerStroke}
              strokeWidth="2.2"
              strokeOpacity="0.85"
            />

            {/* TOP-LEFT SWEEPING LAYERED RIBBON (under the double gold frame rails like reference) */}
            <g filter={`url(#ribbonDrop-${uid})`}>
              <path d="M 0 0 L 298 0 C 206 42 88 128 0 284 Z" fill={`url(#goldRibbonSweep-${uid})`} />
              <path d="M 0 0 L 266 0 C 178 44 74 120 0 242 Z" fill={midFill} />
              <path d="M 0 0 L 188 0 C 112 40 48 92 0 164 Z" fill={darkFill} />
              <path d="M 188 0 C 114 40 48 90 0 158" fill="none" stroke={`url(#goldRibbonSweep-${uid})`} strokeWidth="1.5" />
              <path d="M 272 0 C 184 44 78 122 0 248" fill="none" stroke="#FFF4BE" strokeWidth="1.2" strokeOpacity="0.85" />
            </g>

            {/* BOTTOM-RIGHT SWEEPING LAYERED RIBBON */}
            <g transform={`translate(${W}, ${H}) scale(-1, -1)`} filter={`url(#ribbonDrop-${uid})`}>
              <path d="M 0 0 L 298 0 C 206 42 88 128 0 284 Z" fill={`url(#goldRibbonSweep-${uid})`} />
              <path d="M 0 0 L 266 0 C 178 44 74 120 0 242 Z" fill={midFill} />
              <path d="M 0 0 L 188 0 C 112 40 48 92 0 164 Z" fill={darkFill} />
              <path d="M 188 0 C 114 40 48 90 0 158" fill="none" stroke={`url(#goldRibbonSweep-${uid})`} strokeWidth="1.5" />
              <path d="M 272 0 C 184 44 78 122 0 248" fill="none" stroke="#FFF4BE" strokeWidth="1.2" strokeOpacity="0.85" />
            </g>

            {/* DOUBLE METALLIC GOLD FRAME RAILS RENDERED ON TOP OF RIBBONS (exact image_33f9eb38 look) */}
            <rect
              x="18"
              y="18"
              width={W - 36}
              height={H - 36}
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="3.8"
            />
            <rect
              x="25.5"
              y="25.5"
              width={W - 51}
              height={H - 51}
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="1.4"
            />

            {/* 4 BAROQUE GOLDEN ACANTHUS CORNER FILIGREE ORNAMENTS (exact match to image_33f9eb38.png) */}
            {[
              'translate(29, 29)',
              `translate(${W - 29}, 29) scale(-1, 1)`,
              `translate(29, ${H - 29}) scale(1, -1)`,
              `translate(${W - 29}, ${H - 29}) scale(-1, -1)`,
            ].map((tf, idx) => (
              <g key={`ribbon-corner-filigree-${idx}`} transform={tf} filter={`url(#goldEmboss-${uid})`}>
                {/* Corner Spearhead Diamond Leaf + Small Edge Leaves */}
                <path d="M 2 2 C 10 3 17 7 21 15 C 13 17 6 12 2 2 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 12 3 C 19 2 26 4 29 8 C 23 10 17 8 12 3 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 3 12 C 2 19 4 26 8 29 C 10 23 8 17 3 12 Z" fill={`url(#luxuryGoldFoil-${uid})`} />

                {/* Upper Lush Swollen Acanthus C-Scroll + Inner Curl */}
                <path
                  d="M 20 14 C 26 4 44 1 56 8 C 66 14 68 28 59 35 C 52 40 43 38 41 31 C 39 25 45 21 50 23 C 53 25 53 29 50 31 C 55 31 59 24 55 16 C 50 7 35 7 25 19 Z"
                  fill={`url(#luxuryGoldFoil-${uid})`}
                />
                {/* Left Lush Swollen Acanthus C-Scroll + Inner Curl */}
                <path
                  d="M 14 20 C 4 26 1 44 8 56 C 14 66 28 68 35 59 C 40 52 38 43 31 41 C 25 39 21 45 23 50 C 25 53 29 53 31 50 C 31 55 24 59 16 55 C 7 50 7 35 19 25 Z"
                  fill={`url(#luxuryGoldFoil-${uid})`}
                />

                {/* Openwork 3-Petal Bloom inside Upper & Left Scrolls */}
                <path d="M 32 18 C 36 13 42 13 45 18 C 40 20 36 20 32 18 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 35 23 C 41 21 46 24 47 29 C 41 29 37 27 35 23 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 18 32 C 13 36 13 42 18 45 C 20 40 20 36 18 32 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 23 35 C 21 41 24 46 29 47 C 29 41 27 37 23 35 Z" fill={`url(#luxuryGoldFoil-${uid})`} />

                {/* Trailing Upper Openwork Finial Scroll & Teardrop Tip */}
                <path
                  d="M 60 11 C 72 3 88 5 94 14 C 98 20 93 27 86 25 C 81 23 81 18 85 16 C 89 15 91 19 88 21 C 92 19 91 12 84 9 C 77 7 68 10 63 16 Z"
                  fill={`url(#luxuryGoldFoil-${uid})`}
                />
                <path d="M 96 12 C 103 10 111 11 115 15 C 109 19 102 18 96 12 Z" fill={`url(#luxuryGoldFoil-${uid})`} />

                {/* Trailing Left Openwork Finial Scroll & Teardrop Tip */}
                <path
                  d="M 11 60 C 3 72 5 88 14 94 C 20 98 27 93 25 86 C 23 81 18 81 16 85 C 15 89 19 91 21 88 C 19 92 12 91 9 84 C 7 77 10 68 16 63 Z"
                  fill={`url(#luxuryGoldFoil-${uid})`}
                />
                <path d="M 12 96 C 10 103 11 111 15 115 C 19 109 18 102 12 96 Z" fill={`url(#luxuryGoldFoil-${uid})`} />

                {/* Diagonal Center Leaf Sprig & Tri-Lobe Fleur */}
                <path d="M 20 20 Q 35 31 50 50 Q 31 35 20 20 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 32 32 C 39 27 48 29 51 35 C 44 37 37 37 32 32 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 32 32 C 27 39 29 48 35 51 C 37 44 37 37 32 32 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
                <path d="M 40 40 C 48 41 55 47 58 58 C 47 55 41 48 40 40 Z" fill={`url(#luxuryGoldFoil-${uid})`} />

                {/* Crisp Gold Pearl Dots Inside Scroll Loops */}
                <circle cx="48" cy="14" r="2" fill={`url(#goldRadialBurst-${uid})`} />
                <circle cx="14" cy="48" r="2" fill={`url(#goldRadialBurst-${uid})`} />
                <circle cx="72" cy="12" r="1.7" fill={`url(#goldRadialBurst-${uid})`} />
                <circle cx="12" cy="72" r="1.7" fill={`url(#goldRadialBurst-${uid})`} />
              </g>
            ))}
          </g>
        );
      })()}

      {/* =========================================================================
          STYLE 0G (CLONED #2): GOLD WAVE & CORNER SCROLL
      ========================================================================= */}
      {style === 'gold-wave-scroll' && (
        <g>
          {/* Faint Golden Diagonal Wave Ribbons */}
          <g
            opacity="0.26"
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            transform={isPortrait ? `scale(${W / 1060}, ${H / 750})` : undefined}
          >
            {Array.from({ length: 11 }).map((_, i) => (
              <path
                key={`gw-tl-${i}`}
                d={`M 24 ${210 - i * 14} C ${150 + i * 16} ${170 - i * 10}, ${260 + i * 22} ${70 + i * 6}, ${460 + i * 14} 24`}
                strokeWidth="0.9"
              />
            ))}
            {Array.from({ length: 11 }).map((_, i) => (
              <path
                key={`gw-br-${i}`}
                d={`M ${560 + i * 14} 726 C ${740 + i * 14} ${650 - i * 8}, ${880 + i * 12} ${610 - i * 10}, 1036 ${530 + i * 14}`}
                strokeWidth="0.9"
              />
            ))}
          </g>

          {/* Crisp Double Metallic Gold Frame */}
          <rect x="18" y="18" width={W - 36} height={H - 36} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="3.6" />
          <rect x="26" y="26" width={W - 52} height={H - 52} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />

          {/* 4 Delicate Classical Corner Scrolls */}
          {[
            'translate(29, 29)',
            `translate(${W - 29}, 29) scale(-1, 1)`,
            `translate(29, ${H - 29}) scale(1, -1)`,
            `translate(${W - 29}, ${H - 29}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`gws-corner-${idx}`} transform={tf}>
              <path d="M 2 2 C 12 4 20 8 24 18 C 14 19 7 13 2 2 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path
                d="M 18 11 C 28 3 44 4 50 13 C 54 20 46 27 39 24 C 34 22 35 16 39 16 C 43 16 44 20 41 21 C 46 21 48 14 43 9 C 37 5 26 7 20 15 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 11 18 C 3 28 4 44 13 50 C 20 54 27 46 24 39 C 22 34 16 35 16 39 C 16 43 20 44 21 41 C 21 46 14 48 9 43 C 5 37 7 26 15 20 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path d="M 52 10 C 66 5 80 7 88 12 C 76 13 64 13 52 10 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path d="M 10 52 C 5 66 7 80 12 88 C 13 76 13 64 10 52 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <circle cx="32" cy="14" r="1.8" fill={`url(#goldRadialBurst-${uid})`} />
              <circle cx="14" cy="32" r="1.8" fill={`url(#goldRadialBurst-${uid})`} />
            </g>
          ))}
        </g>
      )}

      {/* =========================================================================
          STYLE 0H (CLONED #3): NAVY GOLD NOTCHED FRAME
      ========================================================================= */}
      {style === 'navy-gold-notched' && (
        <g>
          {/* Heavy Navy Outer Band */}
          <rect x="14" y="14" width={W - 28} height={H - 28} fill="none" stroke={`url(#navyNotchedFill-${uid})`} strokeWidth="9" />
          {/* Inner Crisp Gold Hairline Rail */}
          <rect x="22" y="22" width={W - 44} height={H - 44} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="2.8" />

          {/* Notched Inner Gold Frame Path (Concave Corner Step) */}
          <path
            d={`M 64 32 L ${W - 64} 32 L ${W - 64} 64 L ${W - 32} 64 L ${W - 32} ${H - 64} L ${W - 64} ${H - 64} L ${W - 64} ${H - 32} L 64 ${H - 32} L 64 ${H - 64} L 32 ${H - 64} L 32 64 L 64 64 Z`}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="2.4"
          />

          {/* 4 Notched Navy & Gold Corner Squares */}
          {[
            'translate(22, 22)',
            `translate(${W - 22}, 22) scale(-1, 1)`,
            `translate(22, ${H - 22}) scale(1, -1)`,
            `translate(${W - 22}, ${H - 22}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`ngn-corner-${idx}`} transform={tf}>
              <rect x="0" y="0" width="42" height="42" fill={`url(#navyNotchedFill-${uid})`} stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="2" />
              <path d="M 8 34 L 8 8 L 34 8" fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="2.4" />
              <rect x="14" y="14" width="12" height="12" fill="none" stroke="#FFF4BE" strokeWidth="1.2" />
              <circle cx="20" cy="20" r="2.5" fill={`url(#luxuryGoldFoil-${uid})`} />
            </g>
          ))}
        </g>
      )}

      {/* =========================================================================
          STYLE 0I (CLONED #4): ROYAL BLUE GUILLOCHE LACE
      ========================================================================= */}
      {style === 'blue-guilloche-lace' && (
        <g>
          {/* Outer Fine Banknote Frame */}
          <rect x="14" y="14" width={W - 28} height={H - 28} rx="14" fill="none" stroke={`url(#blueGuillocheStroke-${uid})`} strokeWidth="1.5" />

          {/* Top & Bottom Scalloped Banknote Guilloche Strips */}
          <rect x="44" y="16" width={W - 88} height="28" fill={`url(#blueScallopH-${uid})`} />
          <rect x="44" y={H - 44} width={W - 88} height="28" fill={`url(#blueScallopH-${uid})`} />

          {/* Left & Right Scalloped Banknote Guilloche Strips */}
          <rect x="16" y="44" width="28" height={H - 88} fill={`url(#blueScallopV-${uid})`} />
          <rect x={W - 44} y="44" width="28" height={H - 88} fill={`url(#blueScallopV-${uid})`} />

          {/* 4 Corner Concentric Spirograph Lace Medallions */}
          {[
            { cx: 34, cy: 34 },
            { cx: W - 34, cy: 34 },
            { cx: 34, cy: H - 34 },
            { cx: W - 34, cy: H - 34 },
          ].map((pt, idx) => (
            <g key={`bgl-corner-${idx}`} transform={`translate(${pt.cx}, ${pt.cy})`}>
              <circle r="19" fill="#FCFBF7" stroke="#1D4ED8" strokeWidth="1.1" />
              {Array.from({ length: 8 }).map((_, j) => (
                <ellipse
                  key={j}
                  rx="16"
                  ry="6.5"
                  fill="none"
                  stroke="#2563EB"
                  strokeWidth="0.75"
                  transform={`rotate(${j * 22.5})`}
                />
              ))}
              <circle r="6" fill="#FCFBF7" stroke="#1E3A8A" strokeWidth="1.1" />
              <circle r="2.6" fill="#1D4ED8" />
            </g>
          ))}

          {/* Double Crisp Royal-Blue Inner Frame Rails */}
          <rect x="45" y="45" width={W - 90} height={H - 90} rx="8" fill="none" stroke="#1E3A8A" strokeWidth="2.2" />
          <rect x="50" y="50" width={W - 100} height={H - 100} rx="6" fill="none" stroke="#3B82F6" strokeWidth="0.9" />
        </g>
      )}

      {/* =========================================================================
          STYLE 0J (CLONED #5): BAROQUE GOLD FLOURISH & WAVE
      ========================================================================= */}
      {style === 'baroque-gold-wave' && (
        <g>
          {/* Faint Golden Wave Curtains */}
          <g
            opacity="0.22"
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            transform={isPortrait ? `scale(${W / 1060}, ${H / 750})` : undefined}
          >
            {Array.from({ length: 13 }).map((_, i) => (
              <path
                key={`bgw-wave-${i}`}
                d={`M 24 ${660 - i * 8} C 280 ${570 + i * 6}, 620 ${710 - i * 9}, 1036 ${590 + i * 7}`}
                strokeWidth="0.85"
              />
            ))}
          </g>

          {/* Heavy Double Metallic Gold Frame */}
          <rect x="17" y="17" width={W - 34} height={H - 34} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="4.6" />
          <rect x="26" y="26" width={W - 52} height={H - 52} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.5" />

          {/* Lush Sculpted Baroque Corner Flourishes */}
          {[
            'translate(29, 29)',
            `translate(${W - 29}, 29) scale(-1, 1)`,
            `translate(29, ${H - 29}) scale(1, -1)`,
            `translate(${W - 29}, ${H - 29}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`bgw-corner-${idx}`} transform={tf} filter={`url(#goldEmboss-${uid})`}>
              <path d="M 2 2 C 16 3 26 10 31 23 C 19 25 8 17 2 2 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path
                d="M 22 14 C 32 1 56 -1 70 9 C 80 17 79 34 67 41 C 57 47 45 43 43 34 C 41 26 48 21 55 24 C 60 27 59 33 54 34 C 61 35 67 26 62 17 C 55 7 37 8 27 21 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 14 22 C 1 32 -1 56 9 70 C 17 80 34 79 41 67 C 47 57 43 45 34 43 C 26 41 21 48 24 55 C 27 60 33 59 34 54 C 35 61 26 67 17 62 C 7 55 8 37 21 27 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 70 11 C 86 2 106 5 114 16 C 118 24 111 32 103 29 C 97 27 97 21 102 18 C 106 17 109 22 105 24 C 110 22 109 13 100 10 C 91 7 80 11 74 18 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 11 70 C 2 86 5 106 16 114 C 24 118 32 111 29 103 C 27 97 21 97 18 102 C 17 106 22 109 24 105 C 22 110 13 109 10 100 C 7 91 11 80 18 74 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path d="M 24 24 Q 44 37 64 64 Q 37 44 24 24 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <circle cx="52" cy="18" r="2.4" fill={`url(#goldRadialBurst-${uid})`} />
              <circle cx="18" cy="52" r="2.4" fill={`url(#goldRadialBurst-${uid})`} />
              <circle cx="88" cy="14" r="2" fill={`url(#goldRadialBurst-${uid})`} />
              <circle cx="14" cy="88" r="2" fill={`url(#goldRadialBurst-${uid})`} />
            </g>
          ))}
        </g>
      )}

      {/* =========================================================================
          STYLE 0K (CLONED #6): BLUE ROSETTE GUILLOCHE
      ========================================================================= */}
      {style === 'blue-rosette-guilloche' && (
        <g>
          {/* Outer Perimeter Fine Rule */}
          <rect x="14" y="14" width={W - 28} height={H - 28} rx="18" fill="none" stroke="#1E3A8A" strokeWidth="1.6" />

          {/* Woven Royal-Blue Banknote Perimeter Strips */}
          <rect x="64" y="17" width={W - 128} height="30" fill={`url(#blueScallopH-${uid})`} />
          <rect x="64" y={H - 47} width={W - 128} height="30" fill={`url(#blueScallopH-${uid})`} />
          <rect x="17" y="64" width="30" height={H - 128} fill={`url(#blueScallopV-${uid})`} />
          <rect x={W - 47} y="64" width="30" height={H - 128} fill={`url(#blueScallopV-${uid})`} />

          {/* Inner Double Banknote Line */}
          <rect x="48" y="48" width={W - 96} height={H - 96} rx="12" fill="none" stroke="#1E40AF" strokeWidth="2.2" />
          <rect x="54" y="54" width={W - 108} height={H - 108} rx="9" fill="none" stroke="#3B82F6" strokeWidth="0.9" />

          {/* 4 Large Ornate Floral Guilloche Rosette Corner Medallions */}
          {[
            { cx: 52, cy: 52 },
            { cx: W - 52, cy: 52 },
            { cx: 52, cy: H - 52 },
            { cx: W - 52, cy: H - 52 },
          ].map((pt, idx) => (
            <g key={`brg-rosette-${idx}`} transform={`translate(${pt.cx}, ${pt.cy})`}>
              <circle r="38" fill="#FCFBF7" stroke="#1E3A8A" strokeWidth="1.4" />
              <circle r="34" fill="none" stroke="#3B82F6" strokeWidth="0.7" strokeDasharray="2 2" />
              {/* 12 Overlapping Guilloche Floral Petals */}
              {Array.from({ length: 12 }).map((_, k) => (
                <ellipse
                  key={`p1-${k}`}
                  rx="31"
                  ry="11"
                  fill="none"
                  stroke="#1D4ED8"
                  strokeWidth="0.85"
                  strokeOpacity="0.8"
                  transform={`rotate(${k * 15})`}
                />
              ))}
              {Array.from({ length: 6 }).map((_, k) => (
                <ellipse
                  key={`p2-${k}`}
                  rx="22"
                  ry="8"
                  fill="none"
                  stroke="#1E3A8A"
                  strokeWidth="0.8"
                  transform={`rotate(${k * 30 + 7.5})`}
                />
              ))}
              <circle r="10" fill="#FCFBF7" stroke="#1E3A8A" strokeWidth="1.2" />
              <circle r="4.5" fill="#1D4ED8" />
            </g>
          ))}
        </g>
      )}

      {/* =========================================================================
          STYLE 0D: ROYAL GEOMETRIC ARCH (Stepped Architectural Chevrons + Gold Bevel)
      ========================================================================= */}
      {style === 'royal-geometric-arch' && (
        <g>
          {/* Outer Heavy Primary Frame */}
          <rect x="14" y="14" width={W - 28} height={H - 28} fill="none" stroke={primaryColor} strokeWidth="6" />
          {/* Inner Bevel Gold Band */}
          <rect x="25" y="25" width={W - 50} height={H - 50} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="2.5" />
          <rect x="34" y="34" width={W - 68} height={H - 68} fill="none" stroke={primaryColor} strokeWidth="1" strokeOpacity="0.55" />

          {/* 4 Stepped Architectural Corner Chevrons */}
          {[
            'translate(14, 14)',
            `translate(${W - 14}, 14) scale(-1, 1)`,
            `translate(14, ${H - 14}) scale(1, -1)`,
            `translate(${W - 14}, ${H - 14}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`arch-corner-${idx}`} transform={tf}>
              <polygon points="0,0 130,0 112,18 18,18 18,112 0,130" fill={primaryColor} />
              <polygon points="0,0 92,0 78,12 12,12 12,78 0,92" fill={`url(#luxuryGoldFoil-${uid})`} />
              <polygon points="24,24 68,24 24,68" fill={primaryColor} fillOpacity="0.14" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
              <rect x="30" y="30" width="12" height="12" transform="rotate(45 36 36)" fill={`url(#luxuryGoldFoil-${uid})`} />
            </g>
          ))}

          {/* Top & Bottom Center Architectural Diamond Crests */}
          <g transform={`translate(${cx}, 25)`}>
            <polygon points="-50,0 0,-10 50,0 0,10" fill={primaryColor} stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.4" />
            <polygon points="-18,0 0,-6 18,0 0,6" fill={`url(#luxuryGoldFoil-${uid})`} />
          </g>
          <g transform={`translate(${cx}, ${H - 25})`}>
            <polygon points="-50,0 0,-10 50,0 0,10" fill={primaryColor} stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.4" />
            <polygon points="-18,0 0,-6 18,0 0,6" fill={`url(#luxuryGoldFoil-${uid})`} />
          </g>
        </g>
      )}

      {/* =========================================================================
          STYLE 0E: KHMER KBACH & LOTUS (Traditional Heritage Gold Arch Frame)
      ========================================================================= */}
      {style === 'khmer-kbach-lotus' && (
        <g>
          {/* Woven Gold Guilloche Border Track */}
          <rect x="18" y="18" width={W - 36} height={H - 36} rx="4" fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="6" />
          <rect x="26" y="26" width={W - 52} height={H - 52} fill="none" stroke={primaryColor} strokeWidth="1.4" strokeOpacity="0.7" />
          <rect x="33" y="33" width={W - 66} height={H - 66} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.8" strokeDasharray="8 3 2 3" />
          <rect x="41" y="41" width={W - 82} height={H - 82} fill="none" stroke={`url(#goldSheenHorizontal-${uid})`} strokeWidth="1.2" />

          {/* 4 Khmer Kbach Flame-Leaf Corner Arches */}
          {[
            'translate(22, 22)',
            `translate(${W - 22}, 22) scale(-1, 1)`,
            `translate(22, ${H - 22}) scale(1, -1)`,
            `translate(${W - 22}, ${H - 22}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`kbach-corner-${idx}`} transform={tf}>
              {/* Corner Backing Shield */}
              <path d="M 0 0 L 96 0 C 78 24 58 34 38 38 C 34 58 24 78 0 96 Z" fill={primaryColor} fillOpacity="0.92" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.5" />
              {/* Sacred Kbach Lotus Flame Petals */}
              <path d="M 8 8 C 30 6 54 12 72 6 C 58 20 42 26 26 26 C 26 42 20 58 6 72 C 12 54 6 30 8 8 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path d="M 14 14 C 28 18 36 28 42 42 C 28 36 18 28 14 14 Z" fill="#FFF7CC" />
              <circle cx="24" cy="24" r="4" fill={primaryColor} stroke="#FFF7CC" strokeWidth="1" />
            </g>
          ))}

          {/* Top & Bottom Center Sacred Lotus Crown */}
          <g transform={`translate(${cx}, 26)`}>
            <path d="M -54 0 C -36 -14 -18 -4 0 -14 C 18 -4 36 -14 54 0 C 32 8 16 4 0 11 C -16 4 -32 8 -54 0 Z" fill={primaryColor} stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
            <path d="M 0 -11 C 6 -3 10 2 0 9 C -10 2 -6 -3 0 -11 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
          </g>
          <g transform={`translate(${cx}, ${H - 26}) scale(1, -1)`}>
            <path d="M -54 0 C -36 -14 -18 -4 0 -14 C 18 -4 36 -14 54 0 C 32 8 16 4 0 11 C -16 4 -32 8 -54 0 Z" fill={primaryColor} stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
            <path d="M 0 -11 C 6 -3 10 2 0 9 C -10 2 -6 -3 0 -11 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
          </g>
        </g>
      )}

      {/* =========================================================================
          STYLE 0F: ART DECO GILDED (1920s Luxury Octagonal & Sunburst Geometry)
      ========================================================================= */}
      {style === 'art-deco-gilded' && (
        <g>
          {/* Outer Framed Tuxedo Lines */}
          <rect x="18" y="18" width={W - 36} height={H - 36} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="3.5" />
          <rect x="28" y="28" width={W - 56} height={H - 56} fill="none" stroke={primaryColor} strokeWidth="2" />
          {/* Inner Chamfered Octagon Gold Frame */}
          <polygon
            points={`72,38 ${W - 72},38 ${W - 38},72 ${W - 38},${H - 72} ${W - 72},${H - 38} 72,${H - 38} 38,${H - 72} 38,72`}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="1.8"
          />

          {/* 4 Art Deco Corner Fan Rays */}
          {[
            'translate(28, 28)',
            `translate(${W - 28}, 28) scale(-1, 1)`,
            `translate(28, ${H - 28}) scale(1, -1)`,
            `translate(${W - 28}, ${H - 28}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`deco-corner-${idx}`} transform={tf} stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.4" fill="none">
              <path d="M 0 0 L 44 44" />
              <path d="M 0 0 L 66 20" />
              <path d="M 0 0 L 20 66" />
              <path d="M 0 36 L 36 0" strokeWidth="2" />
              <path d="M 0 54 L 54 0" stroke={primaryColor} strokeWidth="1.4" />
              <circle cx="14" cy="14" r="5" fill={`url(#goldRadialBurst-${uid})`} stroke="none" />
            </g>
          ))}
        </g>
      )}

      {/* =========================================================================
          STYLE 1: GOLDEN RING FRAME (Custom-engineered for Knea Yoeng Brand)
      ========================================================================= */}
      {(style === 'golden-ring-frame' || !style) && (
        <g>
          {/* Subtle Banknote Guilloche Frieze between borders */}
          <rect
            x="24"
            y="24"
            width={W - 48}
            height={H - 48}
            rx="16"
            fill={`url(#guillocheWeave-${uid})`}
            stroke="none"
            opacity="0.6"
          />

          {/* Outer Heavy Beveled Gold Foil Border */}
          <rect
            x="20"
            y="20"
            width={W - 40}
            height={H - 40}
            rx="16"
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="5.5"
            className="filter drop-shadow-xs"
          />

          {/* Secondary Delicate Hairline Ring */}
          <rect
            x="29"
            y="29"
            width={W - 58}
            height={H - 58}
            rx="11"
            fill="none"
            stroke={primaryColor}
            strokeWidth="1.2"
            strokeOpacity="0.6"
          />

          {/* Third Concentric Inner Gold Hairline Frame */}
          <rect
            x="36"
            y="36"
            width={W - 72}
            height={H - 72}
            rx="8"
            fill="none"
            stroke={`url(#goldSheenHorizontal-${uid})`}
            strokeWidth="1.5"
          />

          {/* Fourth Fine Dotted Academic Security Line */}
          <rect
            x="42"
            y="42"
            width={W - 84}
            height={H - 84}
            rx="6"
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="0.8"
            strokeDasharray="4 3"
            strokeOpacity="0.8"
          />

          {/* 4 Grand Corner Medallion Rosettes */}
          {[
            { cx: 48, cy: 48 },
            { cx: W - 48, cy: 48 },
            { cx: 48, cy: H - 48 },
            { cx: W - 48, cy: H - 48 },
          ].map((pt, idx) => (
            <g key={`corner-medallion-${idx}`}>
              {/* Outer Glow / Drop shadow ring */}
              <circle cx={pt.cx} cy={pt.cy} r="22" fill="#FFFFFF" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="3" />
              {/* Beaded ring */}
              <circle cx={pt.cx} cy={pt.cy} r="17" fill="none" stroke={primaryColor} strokeWidth="1" strokeDasharray="2 2" strokeOpacity="0.7" />
              <circle cx={pt.cx} cy={pt.cy} r="13" fill={`url(#goldRadialBurst-${uid})`} stroke="#FFFFFF" strokeWidth="1" />
              {/* Center 8-point gold facet */}
              <circle cx={pt.cx} cy={pt.cy} r="5" fill="#FFFFFF" />
              <circle cx={pt.cx} cy={pt.cy} r="2.5" fill={primaryColor} />
            </g>
          ))}

          {/* Classical Top & Bottom Center Filigree Crown Insets */}
          <g transform={`translate(${cx}, 24)`}>
            <path
              d="M -60 0 C -30 -10 -15 8 0 0 C 15 8 30 -10 60 0 C 30 -4 15 2 0 -2 C -15 2 -30 -4 -60 0 Z"
              fill={`url(#luxuryGoldFoil-${uid})`}
            />
            <circle cx="0" cy="1" r="3.5" fill={`url(#goldRadialBurst-${uid})`} stroke="#FFFFFF" strokeWidth="0.8" />
          </g>

          <g transform={`translate(${cx}, ${H - 24}) scale(1, -1)`}>
            <path
              d="M -60 0 C -30 -10 -15 8 0 0 C 15 8 30 -10 60 0 C 30 -4 15 2 0 -2 C -15 2 -30 -4 -60 0 Z"
              fill={`url(#luxuryGoldFoil-${uid})`}
            />
            <circle cx="0" cy="1" r="3.5" fill={`url(#goldRadialBurst-${uid})`} stroke="#FFFFFF" strokeWidth="0.8" />
          </g>
        </g>
      )}

      {/* =========================================================================
          STYLE 2: ORNATE GUILLOCHE (Classical Royal Diploma Masterpiece)
      ========================================================================= */}
      {style === 'ornate-guilloche' && (
        <g>
          {/* Outer Heavy Gold Foil Frame */}
          <rect
            x="20"
            y="20"
            width={W - 40}
            height={H - 40}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="5"
          />

          {/* Secondary Fine Rule */}
          <rect
            x="28"
            y="28"
            width={W - 56}
            height={H - 56}
            fill="none"
            stroke={primaryColor}
            strokeWidth="1"
            strokeOpacity="0.7"
          />

          {/* Third Dotted Rule */}
          <rect
            x="34"
            y="34"
            width={W - 68}
            height={H - 68}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="1.5"
            strokeDasharray="5 3"
          />

          {/* Fourth Fine Inner Frame */}
          <rect
            x="42"
            y="42"
            width={W - 84}
            height={H - 84}
            fill="none"
            stroke={`url(#goldSheenHorizontal-${uid})`}
            strokeWidth="1"
          />

          {/* 4 Grand Baroque Guilloche Corner Flourishes */}
          {/* Top-Left */}
          <g transform="translate(20, 20)">
            <path
              d="M 0 0 L 80 0 C 50 25 25 50 0 80 Z"
              fill={`url(#luxuryGoldFoil-${uid})`}
              fillOpacity="0.2"
            />
            <path
              d="M 6 6 L 74 6 C 46 28 28 46 6 74 Z"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="1"
            />
            <circle cx="32" cy="32" r="12" fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
            <circle cx="32" cy="32" r="6" fill={`url(#goldRadialBurst-${uid})`} />
            {/* Fine Engraving Rays */}
            <path
              d="M 12 50 Q 30 30 50 12 M 18 62 Q 38 38 62 18 M 24 74 Q 44 44 74 24"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="0.75"
              strokeOpacity="0.7"
            />
          </g>

          {/* Top-Right */}
          <g transform={`translate(${W - 20}, 20) scale(-1, 1)`}>
            <path
              d="M 0 0 L 80 0 C 50 25 25 50 0 80 Z"
              fill={`url(#luxuryGoldFoil-${uid})`}
              fillOpacity="0.2"
            />
            <path
              d="M 6 6 L 74 6 C 46 28 28 46 6 74 Z"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="1"
            />
            <circle cx="32" cy="32" r="12" fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
            <circle cx="32" cy="32" r="6" fill={`url(#goldRadialBurst-${uid})`} />
            <path
              d="M 12 50 Q 30 30 50 12 M 18 62 Q 38 38 62 18 M 24 74 Q 44 44 74 24"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="0.75"
              strokeOpacity="0.7"
            />
          </g>

          {/* Bottom-Left */}
          <g transform={`translate(20, ${H - 20}) scale(1, -1)`}>
            <path
              d="M 0 0 L 80 0 C 50 25 25 50 0 80 Z"
              fill={`url(#luxuryGoldFoil-${uid})`}
              fillOpacity="0.2"
            />
            <path
              d="M 6 6 L 74 6 C 46 28 28 46 6 74 Z"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="1"
            />
            <circle cx="32" cy="32" r="12" fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
            <circle cx="32" cy="32" r="6" fill={`url(#goldRadialBurst-${uid})`} />
            <path
              d="M 12 50 Q 30 30 50 12 M 18 62 Q 38 38 62 18 M 24 74 Q 44 44 74 24"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="0.75"
              strokeOpacity="0.7"
            />
          </g>

          {/* Bottom-Right */}
          <g transform={`translate(${W - 20}, ${H - 20}) scale(-1, -1)`}>
            <path
              d="M 0 0 L 80 0 C 50 25 25 50 0 80 Z"
              fill={`url(#luxuryGoldFoil-${uid})`}
              fillOpacity="0.2"
            />
            <path
              d="M 6 6 L 74 6 C 46 28 28 46 6 74 Z"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="1"
            />
            <circle cx="32" cy="32" r="12" fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.2" />
            <circle cx="32" cy="32" r="6" fill={`url(#goldRadialBurst-${uid})`} />
            <path
              d="M 12 50 Q 30 30 50 12 M 18 62 Q 38 38 62 18 M 24 74 Q 44 44 74 24"
              fill="none"
              stroke={`url(#luxuryGoldFoil-${uid})`}
              strokeWidth="0.75"
              strokeOpacity="0.7"
            />
          </g>
        </g>
      )}

      {/* =========================================================================
          STYLE 3: CORNER FLOURISH (Victorian / Royal Scrollwork)
      ========================================================================= */}
      {style === 'corner-flourish' && (
        <g>
          <rect
            x="24"
            y="24"
            width={W - 48}
            height={H - 48}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="3.5"
          />
          <rect
            x="34"
            y="34"
            width={W - 68}
            height={H - 68}
            fill="none"
            stroke={primaryColor}
            strokeWidth="1"
            strokeOpacity="0.7"
          />

          {/* Sculpted Victorian Acanthus Scrollwork Corner Ornaments */}
          {[
            'translate(40, 40)',
            `translate(${W - 40}, 40) scale(-1, 1)`,
            `translate(40, ${H - 40}) scale(1, -1)`,
            `translate(${W - 40}, ${H - 40}) scale(-1, -1)`,
          ].map((tf, idx) => (
            <g key={`victorian-corner-${idx}`} transform={tf}>
              <path d="M 0 0 L 22 5 L 13 13 L 5 22 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path
                d="M 13 13 C 22 3 42 1 58 8 C 70 13 75 24 69 33 C 63 41 50 42 44 35 C 39 29 42 21 49 21 C 54 21 55 26 51 28 C 57 29 61 24 58 17 C 52 9 36 8 22 17 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 13 13 C 3 22 1 42 8 58 C 13 70 24 75 33 69 C 41 63 42 50 35 44 C 29 39 21 42 21 49 C 21 54 26 55 28 51 C 29 57 24 61 17 58 C 9 52 8 36 17 22 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 58 10 C 72 2 89 4 98 11 C 104 16 100 24 92 24 C 86 24 84 18 89 15 C 93 14 95 17 93 19 C 96 17 94 11 88 9 C 79 6 69 9 64 14 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path
                d="M 10 58 C 2 72 4 89 11 98 C 16 104 24 100 24 92 C 24 86 18 84 15 89 C 14 93 17 95 19 93 C 17 96 11 94 9 88 C 6 79 9 69 14 64 Z"
                fill={`url(#luxuryGoldFoil-${uid})`}
              />
              <path d="M 17 17 Q 32 28 45 45 Q 28 32 17 17 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path d="M 28 28 C 35 23 44 25 46 30 C 40 33 33 33 28 28 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <path d="M 28 28 C 23 35 25 44 30 46 C 33 40 33 33 28 28 Z" fill={`url(#luxuryGoldFoil-${uid})`} />
              <circle cx="52" cy="17" r="2" fill={`url(#goldRadialBurst-${uid})`} />
              <circle cx="17" cy="52" r="2" fill={`url(#goldRadialBurst-${uid})`} />
            </g>
          ))}
        </g>
      )}

      {/* =========================================================================
          STYLE 4: REGAL RIBBON (Executive Ribbon Frame)
      ========================================================================= */}
      {style === 'regal-ribbon' && (
        <g>
          <rect
            x="20"
            y="20"
            width={W - 40}
            height={H - 40}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="7"
          />
          <rect
            x="32"
            y="32"
            width={W - 64}
            height={H - 64}
            fill="none"
            stroke={primaryColor}
            strokeWidth="1.5"
            strokeOpacity="0.8"
          />

          {/* Corner gold cutouts */}
          <rect x="14" y="14" width="24" height="24" fill={`url(#luxuryGoldFoil-${uid})`} />
          <rect x={W - 38} y="14" width="24" height="24" fill={`url(#luxuryGoldFoil-${uid})`} />
          <rect x="14" y={H - 38} width="24" height="24" fill={`url(#luxuryGoldFoil-${uid})`} />
          <rect x={W - 38} y={H - 38} width="24" height="24" fill={`url(#luxuryGoldFoil-${uid})`} />
        </g>
      )}

      {/* =========================================================================
          STYLE 5: DOUBLE HAIRLINE / MINIMAL
      ========================================================================= */}
      {style === 'double-hairline' && (
        <g>
          <rect
            x="24"
            y="24"
            width={W - 48}
            height={H - 48}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="3.5"
          />
          <rect
            x="34"
            y="34"
            width={W - 68}
            height={H - 68}
            fill="none"
            stroke={primaryColor}
            strokeWidth="1"
          />
          <polygon points="34,48 48,34 34,34" fill={`url(#luxuryGoldFoil-${uid})`} />
          <polygon points={`${W - 34},48 ${W - 48},34 ${W - 34},34`} fill={`url(#luxuryGoldFoil-${uid})`} />
          <polygon points={`34,${H - 48} 48,${H - 34} 34,${H - 34}`} fill={`url(#luxuryGoldFoil-${uid})`} />
          <polygon points={`${W - 34},${H - 48} ${W - 48},${H - 34} ${W - 34},${H - 34}`} fill={`url(#luxuryGoldFoil-${uid})`} />
        </g>
      )}

      {style === 'minimal-box' && (
        <g>
          <rect
            x="30"
            y="30"
            width={W - 60}
            height={H - 60}
            fill="none"
            stroke={isDarkTheme ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.15)'}
            strokeWidth="1.5"
          />
          <g stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="2">
            <path d="M 20 30 L 40 30 M 30 20 L 30 40" />
            <path d={`M ${W - 40} 30 L ${W - 20} 30 M ${W - 30} 20 L ${W - 30} 40`} />
            <path d={`M 20 ${H - 30} L 40 ${H - 30} M 30 ${H - 40} L 30 ${H - 20}`} />
            <path d={`M ${W - 40} ${H - 30} L ${W - 20} ${H - 30} M ${W - 30} ${H - 40} L ${W - 30} ${H - 20}`} />
          </g>
        </g>
      )}

      {/* =========================================================================
          STYLE V6: IMPERIAL GOLD & NAVY WAVE SASH (image_a08e13a4.png)
      ========================================================================= */}
      {style === 'imperial-gold-wave-sash' && (
        <g>
          {/* Crisp Outer Double Metallic Gold Frame */}
          <rect
            x="10"
            y="10"
            width={W - 20}
            height={H - 20}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="2.4"
          />
          <rect
            x="15"
            y="15"
            width={W - 30}
            height={H - 30}
            fill="none"
            stroke={`url(#luxuryGoldFoil-${uid})`}
            strokeWidth="1"
            opacity="0.9"
          />

          {/* Top-Right Delicate Corner Scroll Flourish */}
          <g transform={`translate(${W - 20}, 20) scale(-1, 1)`} fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="1.3">
            <path d="M 0 0 C 12 1 18 7 14 14 C 11 19 5 15 8 10" />
            <path d="M 3 3 C 15 10 10 22 4 24" opacity="0.75" />
          </g>

          {/* Subtle Left & Right Margin Guilloche Wave Threads */}
          <g fill="none" stroke={`url(#luxuryGoldFoil-${uid})`} strokeWidth="0.75" opacity="0.24">
            {Array.from({ length: 8 }).map((_, i) => (
              <path
                key={`v6-lw-${i}`}
                d={`M 0 ${H * 0.72 + i * 7} Q ${W * 0.08} ${H * 0.67 + i * 5}, ${W * 0.16} ${H * 0.77 + i * 5} T ${W * 0.34} ${H * 0.85 + i * 4}`}
              />
            ))}
            {Array.from({ length: 8 }).map((_, i) => (
              <path
                key={`v6-rw-${i}`}
                d={`M ${W} ${H * 0.68 + i * 7} Q ${W * 0.91} ${H * 0.64 + i * 5}, ${W * 0.82} ${H * 0.75 + i * 5} T ${W * 0.66} ${H * 0.86 + i * 4}`}
              />
            ))}
          </g>

          {/* Upper-Left Sweeping Navy + Folded Metallic Gold Ribbon Wave */}
          <g filter={`url(#ribbonDrop-${uid})`}>
            <path
              d={`M 0 0 L ${W * 0.43} 0 C ${W * 0.31} ${H * 0.03}, ${W * 0.14} ${H * 0.11}, 0 ${H * 0.25} Z`}
              fill={`url(#navySweepDark-${uid})`}
            />
            <path
              d={`M 0 ${H * 0.16} C ${W * 0.13} ${H * 0.08}, ${W * 0.28} ${H * 0.02}, ${W * 0.42} 0 L ${W * 0.45} 0 C ${W * 0.30} ${H * 0.04}, ${W * 0.14} ${H * 0.14}, 0 ${H * 0.28} Z`}
              fill={`url(#goldRibbonSweep-${uid})`}
            />
            <path
              d={`M 0 ${H * 0.23} C ${W * 0.10} ${H * 0.15}, ${W * 0.23} ${H * 0.07}, ${W * 0.37} 0 C ${W * 0.26} ${H * 0.06}, ${W * 0.12} ${H * 0.17}, 0 ${H * 0.33} Z`}
              fill={`url(#luxuryGoldFoil-${uid})`}
              opacity="0.92"
            />
            <path
              d={`M 0 ${H * 0.155} C ${W * 0.14} ${H * 0.07}, ${W * 0.29} ${H * 0.015}, ${W * 0.44} 0`}
              fill="none"
              stroke="#FFF3C4"
              strokeWidth="1.5"
              opacity="0.85"
            />
          </g>

          {/* Lower-Left Tapered Metallic Gold Curl Ribbon */}
          <g>
            <path
              d={`M 0 ${H * 0.89} C ${W * 0.14} ${H * 0.90}, ${W * 0.33} ${H * 0.96}, ${W * 0.49} ${H} L ${W * 0.36} ${H} C ${W * 0.21} ${H * 0.97}, ${W * 0.09} ${H * 0.95}, 0 ${H * 0.96} Z`}
              fill={`url(#goldRibbonSweep-${uid})`}
            />
            <path
              d={`M 0 ${H * 0.935} C ${W * 0.14} ${H * 0.945}, ${W * 0.28} ${H * 0.98}, ${W * 0.42} ${H} L 0 ${H} Z`}
              fill={`url(#navySweepDark-${uid})`}
            />
            <path
              d={`M 0 ${H * 0.89} C ${W * 0.14} ${H * 0.90}, ${W * 0.33} ${H * 0.96}, ${W * 0.49} ${H}`}
              fill="none"
              stroke="#FFF3C4"
              strokeWidth="1.4"
            />
          </g>

          {/* Lower-Right Sweeping Navy + Metallic Gold Folded Ribbon */}
          <g filter={`url(#ribbonDrop-${uid})`}>
            <path
              d={`M ${W} ${H * 0.72} C ${W * 0.90} ${H * 0.86}, ${W * 0.73} ${H * 0.95}, ${W * 0.56} ${H} L ${W} ${H} Z`}
              fill={`url(#navySweepDark-${uid})`}
            />
            <path
              d={`M ${W} ${H * 0.69} C ${W * 0.89} ${H * 0.84}, ${W * 0.72} ${H * 0.94}, ${W * 0.52} ${H} L ${W * 0.62} ${H} C ${W * 0.78} ${H * 0.95}, ${W * 0.92} ${H * 0.86}, ${W} ${H * 0.75} Z`}
              fill={`url(#goldRibbonSweep-${uid})`}
            />
            <path
              d={`M ${W} ${H * 0.77} C ${W * 0.92} ${H * 0.89}, ${W * 0.80} ${H * 0.96}, ${W * 0.65} ${H} L ${W * 0.74} ${H} C ${W * 0.86} ${H * 0.97}, ${W * 0.94} ${H * 0.91}, ${W} ${H * 0.83} Z`}
              fill={`url(#luxuryGoldFoil-${uid})`}
              opacity="0.88"
            />
            <path
              d={`M ${W} ${H * 0.69} C ${W * 0.89} ${H * 0.84}, ${W * 0.72} ${H * 0.94}, ${W * 0.52} ${H}`}
              fill="none"
              stroke="#FFF3C4"
              strokeWidth="1.6"
              opacity="0.9"
            />
          </g>
        </g>
      )}
    </svg>
  );
};
