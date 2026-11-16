import React, { useId } from 'react';
import { BannerStyleOption, WatermarkStyleOption } from '../types/certificate';

interface BannerRibbonProps {
  style?: BannerStyleOption;
  text: string;
  accentColor?: string;
  primaryColor?: string;
  textColor?: string;
  textStyle?: React.CSSProperties;
  width?: number;
  showSpeedLines?: boolean;
  showTopDiamond?: boolean;
}

/**
 * Editable Vector Presentation Ribbon Banner (4 cloned styles from ornament sheet):
 * - gold-swallowtail
 * - navy-gold-ribbon
 * - crimson-gold-ribbon
 * - royal-blue-arch
 */
export const PresentationBannerRibbon: React.FC<BannerRibbonProps> = ({
  style = 'gold-swallowtail',
  text,
  accentColor = '#D4A32C',
  primaryColor = '#092653',
  textColor = '#FFFFFF',
  textStyle,
  width = 490,
  showSpeedLines = false,
  showTopDiamond = false,
}) => {
  const uid = useId().replace(/:/g, '');
  const gradGoldId = `bnrGold-${uid}`;
  const gradNavyId = `bnrNavy-${uid}`;
  const gradCrimId = `bnrCrim-${uid}`;
  const gradBlueId = `bnrBlue-${uid}`;

  const renderRibbonSvg = () => {
    if (style === 'navy-gold-ribbon') {
      return (
        <svg
          width={width}
          height="38"
          viewBox="0 0 520 40"
          className="drop-shadow-xs overflow-visible"
        >
          <defs>
            <linearGradient id={gradNavyId} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#04122B" />
              <stop offset="25%" stopColor="#0B2A63" />
              <stop offset="50%" stopColor={primaryColor} />
              <stop offset="75%" stopColor="#0B2A63" />
              <stop offset="100%" stopColor="#04122B" />
            </linearGradient>
            <linearGradient id={gradGoldId} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#A97418" />
              <stop offset="30%" stopColor="#F4D16B" />
              <stop offset="50%" stopColor="#FFF0B2" />
              <stop offset="70%" stopColor="#F4D16B" />
              <stop offset="100%" stopColor="#A97418" />
            </linearGradient>
          </defs>
          {/* Folded Back Left & Right Ribbon Tails */}
          <polygon points="0,8 46,8 46,36 0,36 14,22" fill="#030D21" stroke={`url(#${gradGoldId})`} strokeWidth="1.2" />
          <polygon points="520,8 474,8 474,36 520,36 506,22" fill="#030D21" stroke={`url(#${gradGoldId})`} strokeWidth="1.2" />
          <polygon points="34,31 46,36 46,29" fill="#020816" />
          <polygon points="486,31 474,36 474,29" fill="#020816" />
          {/* Main Front Navy Ribbon Panel with Gold Trim */}
          <rect
            x="34"
            y="3"
            width="452"
            height="30"
            rx="2"
            fill={`url(#${gradNavyId})`}
            stroke={`url(#${gradGoldId})`}
            strokeWidth="1.8"
          />
          <line x1="38" y1="6.5" x2="482" y2="6.5" stroke="#FFF4C2" strokeWidth="0.7" strokeOpacity="0.6" />
          <line x1="38" y1="29.5" x2="482" y2="29.5" stroke="#D4A32C" strokeWidth="0.7" strokeOpacity="0.55" />
          <text
            x="260"
            y="22.5"
            textAnchor="middle"
            fill={textColor}
            style={{
              fontSize: '13.5px',
              fontWeight: 600,
              fontFamily: "Cinzel, 'Cormorant Garamond', serif",
              letterSpacing: '3.2px',
              ...textStyle,
              color: undefined,
              fill: textColor,
            }}
          >
            {text}
          </text>
        </svg>
      );
    }

    if (style === 'crimson-gold-ribbon') {
      return (
        <svg
          width={width}
          height="38"
          viewBox="0 0 520 40"
          className="drop-shadow-xs overflow-visible"
        >
          <defs>
            <linearGradient id={gradCrimId} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#690D0D" />
              <stop offset="25%" stopColor="#B91C1C" />
              <stop offset="50%" stopColor="#DC2626" />
              <stop offset="75%" stopColor="#B91C1C" />
              <stop offset="100%" stopColor="#690D0D" />
            </linearGradient>
            <linearGradient id={gradGoldId} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#A97418" />
              <stop offset="30%" stopColor="#F4D16B" />
              <stop offset="50%" stopColor="#FFF0B2" />
              <stop offset="70%" stopColor="#F4D16B" />
              <stop offset="100%" stopColor="#A97418" />
            </linearGradient>
          </defs>
          {/* Folded Back Crimson Swallowtail Ends */}
          <polygon points="0,10 48,10 48,37 0,37 15,23.5" fill="#450A0A" stroke={`url(#${gradGoldId})`} strokeWidth="1.2" />
          <polygon points="520,10 472,10 472,37 520,37 505,23.5" fill="#450A0A" stroke={`url(#${gradGoldId})`} strokeWidth="1.2" />
          <polygon points="34,32 48,37 48,29" fill="#2A0404" />
          <polygon points="486,32 472,37 472,29" fill="#2A0404" />
          {/* Curved Crimson Front Panel */}
          <path
            d="M 34 6 Q 260 0 486 6 L 486 33 Q 260 27 34 33 Z"
            fill={`url(#${gradCrimId})`}
            stroke={`url(#${gradGoldId})`}
            strokeWidth="1.8"
          />
          <path
            d="M 38 9.5 Q 260 4 482 9.5"
            fill="none"
            stroke="#FFF4C2"
            strokeWidth="0.7"
            strokeOpacity="0.65"
          />
          <text
            x="260"
            y="23"
            textAnchor="middle"
            fill={textColor}
            style={{
              fontSize: '13.5px',
              fontWeight: 600,
              fontFamily: "Cinzel, 'Cormorant Garamond', serif",
              letterSpacing: '3.2px',
              ...textStyle,
              color: undefined,
              fill: textColor,
            }}
          >
            {text}
          </text>
        </svg>
      );
    }

    if (style === 'royal-blue-arch') {
      return (
        <svg
          width={width}
          height="40"
          viewBox="0 0 520 42"
          className="drop-shadow-xs overflow-visible"
        >
          <defs>
            <linearGradient id={gradBlueId} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#172554" />
              <stop offset="25%" stopColor="#1D4ED8" />
              <stop offset="50%" stopColor="#2563EB" />
              <stop offset="75%" stopColor="#1D4ED8" />
              <stop offset="100%" stopColor="#172554" />
            </linearGradient>
            <linearGradient id={gradGoldId} x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#A97418" />
              <stop offset="35%" stopColor="#F4D16B" />
              <stop offset="50%" stopColor="#FFF0B2" />
              <stop offset="65%" stopColor="#F4D16B" />
              <stop offset="100%" stopColor="#A97418" />
            </linearGradient>
          </defs>
          {/* Arched Ribbon Back Ends */}
          <polygon points="0,14 48,14 48,39 0,39 14,26.5" fill="#0F172A" stroke={`url(#${gradGoldId})`} strokeWidth="1.1" />
          <polygon points="520,14 472,14 472,39 520,39 506,26.5" fill="#0F172A" stroke={`url(#${gradGoldId})`} strokeWidth="1.1" />
          {/* Grand Arched Blue Banner */}
          <path
            d="M 32 10 Q 260 -3 488 10 L 488 35 Q 260 22 32 35 Z"
            fill={`url(#${gradBlueId})`}
            stroke={`url(#${gradGoldId})`}
            strokeWidth="1.8"
          />
          <path
            d="M 38 13 Q 260 1 482 13"
            fill="none"
            stroke="#FFF4C2"
            strokeWidth="0.7"
            strokeOpacity="0.65"
          />
          <text
            x="260"
            y="24"
            textAnchor="middle"
            fill={textColor}
            style={{
              fontSize: '13.5px',
              fontWeight: 600,
              fontFamily: "Cinzel, 'Cormorant Garamond', serif",
              letterSpacing: '3.2px',
              ...textStyle,
              color: undefined,
              fill: textColor,
            }}
          >
            {text}
          </text>
        </svg>
      );
    }

    // Default: 'gold-swallowtail'
    return (
      <svg
        width={width}
        height="34"
        viewBox="0 0 490 34"
        className="drop-shadow-xs overflow-visible"
      >
        <defs>
          <linearGradient id={gradGoldId} x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#BC8518" />
            <stop offset="22%" stopColor="#E9C253" />
            <stop offset="50%" stopColor={accentColor} />
            <stop offset="78%" stopColor="#F0CB62" />
            <stop offset="100%" stopColor="#B88116" />
          </linearGradient>
        </defs>
        <line x1="36" y1="-2" x2="454" y2="-2" stroke={accentColor} strokeWidth="0.9" strokeOpacity="0.75" />
        <polygon points="0,2 490,2 476,17 490,32 0,32 14,17" fill={`url(#${gradGoldId})`} />
        <line x1="16" y1="5" x2="474" y2="5" stroke="#FFF8DC" strokeWidth="0.8" strokeOpacity="0.65" />
        <line x1="16" y1="29" x2="474" y2="29" stroke="#8C5D0C" strokeWidth="0.8" strokeOpacity="0.4" />
        {showTopDiamond && (
          <polygon points="245,-6 251,-1 245,4 239,-1" fill="#E6BA4E" stroke="#9A6B12" strokeWidth="0.5" />
        )}
        <text
          x="245"
          y="21.5"
          textAnchor="middle"
          fill={textColor}
          style={{
            fontSize: '13.5px',
            fontWeight: 600,
            fontFamily: "Cinzel, 'Cormorant Garamond', serif",
            letterSpacing: '3.2px',
            ...textStyle,
            color: undefined,
            fill: textColor,
          }}
        >
          {text}
        </text>
      </svg>
    );
  };

  return (
    <div className="relative inline-flex items-center justify-center">
      {showSpeedLines && (
        <svg width="78" height="26" viewBox="0 0 78 26" className="hidden sm:block -mr-1">
          <line x1="16" y1="6" x2="76" y2="6" stroke={accentColor} strokeWidth="1" strokeOpacity="0.8" />
          <line x1="2" y1="13" x2="76" y2="13" stroke={accentColor} strokeWidth="1.4" />
          <line x1="22" y1="20" x2="76" y2="20" stroke={accentColor} strokeWidth="1" strokeOpacity="0.8" />
        </svg>
      )}
      {renderRibbonSvg()}
      {showSpeedLines && (
        <svg width="78" height="26" viewBox="0 0 78 26" className="hidden sm:block -ml-1 transform -scale-x-100">
          <line x1="16" y1="6" x2="76" y2="6" stroke={accentColor} strokeWidth="1" strokeOpacity="0.8" />
          <line x1="2" y1="13" x2="76" y2="13" stroke={accentColor} strokeWidth="1.4" />
          <line x1="22" y1="20" x2="76" y2="20" stroke={accentColor} strokeWidth="1" strokeOpacity="0.8" />
        </svg>
      )}
    </div>
  );
};

interface WatermarkLayerProps {
  type: WatermarkStyleOption;
  opacity: number;
  primaryColor?: string;
  accentColor?: string;
  issuerName?: string;
  issuerDepartment?: string;
  showLaurelSides?: boolean;
}

/**
 * Watermark & Guilloche Security Pattern Layer (6 distinct patterns cloned from ornament sheet):
 * - laurel (side golden laurels + soft corner waves)
 * - wave-mesh (sweeping royal wave mesh corners)
 * - guilloche-rosette (centered banknote spirograph rosette seal)
 * - rosette-lace (overlapping geometric spirograph lace)
 * - diagonal-brand (anti-counterfeit diagonal tiled issuer pattern)
 * - crest / academic / star (classic concentric heritage guilloche)
 */
export const WatermarkPatternLayer: React.FC<WatermarkLayerProps> = ({
  type,
  opacity,
  primaryColor = '#092653',
  accentColor = '#D4A32C',
  issuerName = 'KNEAYERNG',
  issuerDepartment = 'PHONE SHOP',
  showLaurelSides = true,
}) => {
  const safeOpacity = opacity ?? 0.14;
  const uid = useId().replace(/:/g, '');
  const diagPatternId = `diagBrand-${uid}`;

  if (type === 'wave-mesh') {
    const strokeCol = primaryColor || '#1D4ED8';
    return (
      <div
        className="absolute inset-0 pointer-events-none z-0 overflow-hidden"
        style={{ opacity: Math.min(0.45, Math.max(0.08, safeOpacity * 1.4)) }}
      >
        {/* Top-Left Sweeping Wave Mesh */}
        <svg width="620" height="340" viewBox="0 0 620 340" fill="none" className="absolute -top-6 -left-6">
          {Array.from({ length: 18 }).map((_, i) => (
            <path
              key={`wm-tl-${i}`}
              d={`M -20 ${260 - i * 11} C ${140 + i * 12} ${190 - i * 8}, ${310 + i * 16} ${230 - i * 13}, 610 ${18 + i * 5}`}
              stroke={strokeCol}
              strokeWidth="0.9"
              strokeOpacity={0.38 + (i % 3) * 0.12}
            />
          ))}
        </svg>

        {/* Bottom-Right Sweeping Wave Mesh */}
        <svg width="620" height="340" viewBox="0 0 620 340" fill="none" className="absolute -bottom-6 -right-6 transform rotate-180">
          {Array.from({ length: 18 }).map((_, i) => (
            <path
              key={`wm-br-${i}`}
              d={`M -20 ${260 - i * 11} C ${140 + i * 12} ${190 - i * 8}, ${310 + i * 16} ${230 - i * 13}, 610 ${18 + i * 5}`}
              stroke={strokeCol}
              strokeWidth="0.9"
              strokeOpacity={0.38 + (i % 3) * 0.12}
            />
          ))}
        </svg>
      </div>
    );
  }

  if (type === 'guilloche-rosette') {
    const strokeCol = primaryColor || '#1D4ED8';
    return (
      <div
        className="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden"
        style={{ opacity: Math.min(0.35, Math.max(0.06, safeOpacity * 1.15)) }}
      >
        <svg width="560" height="560" viewBox="0 0 300 300" fill="none" stroke={strokeCol}>
          <circle cx="150" cy="150" r="142" strokeWidth="0.6" strokeDasharray="3 2" />
          <circle cx="150" cy="150" r="134" strokeWidth="0.8" />
          {/* Outer Banknote Wave Petals */}
          {Array.from({ length: 24 }).map((_, i) => (
            <ellipse
              key={`gr-out-${i}`}
              cx="150"
              cy="150"
              rx="132"
              ry="46"
              strokeWidth="0.65"
              strokeOpacity="0.6"
              transform={`rotate(${i * 7.5}, 150, 150)`}
            />
          ))}
          {/* Mid Spirograph Rings */}
          {Array.from({ length: 18 }).map((_, i) => (
            <ellipse
              key={`gr-mid-${i}`}
              cx="150"
              cy="150"
              rx="105"
              ry="58"
              strokeWidth="0.6"
              strokeOpacity="0.7"
              transform={`rotate(${i * 10}, 150, 150)`}
            />
          ))}
          <circle cx="150" cy="150" r="68" fill="#FCFBF7" fillOpacity="0.6" stroke={strokeCol} strokeWidth="1.2" />
          <circle cx="150" cy="150" r="62" stroke={accentColor} strokeWidth="0.9" />
        </svg>
      </div>
    );
  }

  if (type === 'rosette-lace') {
    const strokeCol = primaryColor || '#1D4ED8';
    return (
      <div
        className="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden"
        style={{ opacity: Math.min(0.32, Math.max(0.06, safeOpacity * 1.1)) }}
      >
        <svg width="620" height="620" viewBox="0 0 320 320" fill="none" stroke={strokeCol}>
          {/* 8-Lobe Geometric Spirograph Flower */}
          {Array.from({ length: 16 }).map((_, i) => (
            <ellipse
              key={`rl-1-${i}`}
              cx="160"
              cy="160"
              rx="136"
              ry="52"
              strokeWidth="0.75"
              strokeOpacity="0.65"
              transform={`rotate(${i * 11.25}, 160, 160)`}
            />
          ))}
          {Array.from({ length: 8 }).map((_, i) => (
            <rect
              key={`rl-2-${i}`}
              x="68"
              y="68"
              width="184"
              height="184"
              rx="42"
              strokeWidth="0.7"
              strokeOpacity="0.55"
              transform={`rotate(${i * 22.5}, 160, 160)`}
            />
          ))}
          <circle cx="160" cy="160" r="56" strokeWidth="1" strokeDasharray="3 2" />
        </svg>
      </div>
    );
  }

  if (type === 'diagonal-brand') {
    const strokeCol = primaryColor || '#092653';
    return (
      <div
        className="absolute inset-0 pointer-events-none z-0 overflow-hidden"
        style={{ opacity: Math.min(0.24, Math.max(0.05, safeOpacity * 0.9)) }}
      >
        <svg className="w-full h-full" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern
              id={diagPatternId}
              width="210"
              height="130"
              patternUnits="userSpaceOnUse"
              patternTransform="rotate(-24)"
            >
              {/* Stylized K + Brand Title Stamp */}
              <polygon points="92,24 100,24 100,48 92,48" fill={strokeCol} />
              <polygon points="100,35 112,24 120,24 106,36 121,48 113,48" fill={accentColor} />
              <text
                x="105"
                y="68"
                textAnchor="middle"
                fill={strokeCol}
                fontSize="13"
                fontWeight="800"
                fontFamily="'Plus Jakarta Sans', sans-serif"
                letterSpacing="1.5"
              >
                {issuerName}
              </text>
              <text
                x="105"
                y="82"
                textAnchor="middle"
                fill={strokeCol}
                fontSize="7.5"
                fontWeight="700"
                fontFamily="'Plus Jakarta Sans', sans-serif"
                letterSpacing="2"
              >
                {issuerDepartment}
              </text>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill={`url(#${diagPatternId})`} />
        </svg>
      </div>
    );
  }

  if (type === 'crest' || type === 'academic' || type === 'star') {
    return (
      <div
        className="absolute inset-0 flex items-center justify-center pointer-events-none z-0 overflow-hidden"
        style={{ opacity: Math.min(0.25, Math.max(0.04, safeOpacity * 0.65)) }}
      >
        <svg width="480" height="480" viewBox="0 0 200 200" fill="none" stroke={accentColor}>
          <circle cx="100" cy="100" r="90" strokeWidth="0.8" strokeDasharray="3 3" />
          <circle cx="100" cy="100" r="82" strokeWidth="1.2" />
          <circle cx="100" cy="100" r="74" strokeWidth="0.6" />
          <circle cx="100" cy="100" r="62" strokeWidth="1" strokeDasharray="6 4" />
          <circle cx="100" cy="100" r="50" strokeWidth="0.8" />
          {Array.from({ length: 16 }).map((_, i) => (
            <ellipse
              key={i}
              cx="100"
              cy="100"
              rx="68"
              ry="24"
              strokeWidth="0.5"
              transform={`rotate(${i * 11.25}, 100, 100)`}
            />
          ))}
          <circle cx="100" cy="100" r="32" strokeWidth="1.5" />
        </svg>
      </div>
    );
  }

  // Default 'laurel': Faint Golden Guilloche Corner Waves + Side Laurels
  return (
    <div className="absolute inset-0 pointer-events-none z-0 overflow-hidden">
      <svg width="520" height="260" viewBox="0 0 520 260" fill="none" className="absolute bottom-4 left-4 opacity-20">
        {Array.from({ length: 14 }).map((_, i) => (
          <path
            key={`gl-wave-l-${i}`}
            d={`M -20 ${120 + i * 9} Q ${140 + i * 8} ${40 + i * 6}, ${340 + i * 6} ${230 + i * 4}`}
            stroke={accentColor}
            strokeWidth="0.85"
            fill="none"
          />
        ))}
      </svg>

      <svg width="520" height="260" viewBox="0 0 520 260" fill="none" className="absolute bottom-4 right-4 opacity-20 transform -scale-x-100">
        {Array.from({ length: 14 }).map((_, i) => (
          <path
            key={`gl-wave-r-${i}`}
            d={`M -20 ${120 + i * 9} Q ${140 + i * 8} ${40 + i * 6}, ${340 + i * 6} ${230 + i * 4}`}
            stroke={accentColor}
            strokeWidth="0.85"
            fill="none"
          />
        ))}
      </svg>

      {showLaurelSides && (
        <div
          className="absolute inset-0 flex items-center justify-between px-16"
          style={{ opacity: safeOpacity }}
        >
          <svg width="110" height="280" viewBox="0 0 110 280" fill="none">
            <path d="M 88 260 C 28 205 22 105 82 20" stroke={accentColor} strokeWidth="2" fill="none" />
            {Array.from({ length: 11 }).map((_, i) => {
              const y = 38 + i * 20;
              const x = 42 + Math.abs(i - 5) * 4.5;
              return (
                <g key={`vwm-l-${i}`} fill="#DFBC68">
                  <ellipse cx={x - 10} cy={y} rx="14" ry="5.5" transform={`rotate(${-30 + i * 4} ${x - 10} ${y})`} />
                  <ellipse cx={x + 12} cy={y - 6} rx="13" ry="5" transform={`rotate(${-65 + i * 4} ${x + 12} ${y - 6})`} />
                </g>
              );
            })}
          </svg>
          <svg width="110" height="280" viewBox="0 0 110 280" fill="none" className="transform -scale-x-100">
            <path d="M 88 260 C 28 205 22 105 82 20" stroke={accentColor} strokeWidth="2" fill="none" />
            {Array.from({ length: 11 }).map((_, i) => {
              const y = 38 + i * 20;
              const x = 42 + Math.abs(i - 5) * 4.5;
              return (
                <g key={`vwm-r-${i}`} fill="#DFBC68">
                  <ellipse cx={x - 10} cy={y} rx="14" ry="5.5" transform={`rotate(${-30 + i * 4} ${x - 10} ${y})`} />
                  <ellipse cx={x + 12} cy={y - 6} rx="13" ry="5" transform={`rotate(${-65 + i * 4} ${x + 12} ${y - 6})`} />
                </g>
              );
            })}
          </svg>
        </div>
      )}
    </div>
  );
};
