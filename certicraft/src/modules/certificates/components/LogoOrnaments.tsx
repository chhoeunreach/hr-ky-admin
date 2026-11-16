import React, { useId } from 'react';
import { DividerOrnamentStyle, LogoOrnamentStyle } from '../types/certificate';

interface LogoFlankingProps {
  style: LogoOrnamentStyle;
  accentColor?: string;
  primaryColor?: string;
  compact?: boolean;
  className?: string;
}

/**
 * Renders symmetrical flanking ornamental motifs (ក្បាច់) around the logo & organization header.
 * Uses viewBox 0 0 280 135 so it scales cleanly in both the full certificate and miniature previews.
 */
export const LogoFlankingOrnament: React.FC<LogoFlankingProps> = ({
  style,
  accentColor = '#D4A130',
  primaryColor = '#0A224E',
  className = 'w-[280px] h-[135px]',
}) => {
  const uid = useId().replace(/:/g, '');
  const gradId = `logoOrnGrad-${uid}`;

  if (style === 'none') return null;

  const renderLeftHalf = () => {
    switch (style) {
      case 'khmer-kbach':
        return (
          <g>
            {/* Outer sweeping Kbach stem */}
            <path
              d="M 60 128 C 36 116 24 90 32 62 C 36 48 44 38 56 32"
              fill="none"
              stroke={`url(#${gradId})`}
              strokeWidth="2"
              strokeLinecap="round"
            />
            {/* Khmer Kbach Flame Petals spiraling along the stem */}
            <path
              d="M 55 34 C 46 28 45 18 54 14 C 50 22 54 28 60 30 C 57 34 56 36 55 34 Z"
              fill={`url(#${gradId})`}
            />
            <path
              d="M 38 52 C 23 48 18 35 28 26 C 26 36 33 42 42 45 Z"
              fill={`url(#${gradId})`}
            />
            <path
              d="M 30 74 C 14 70 11 55 22 47 C 20 57 26 64 35 67 Z"
              fill={`url(#${gradId})`}
            />
            <path
              d="M 31 96 C 14 96 10 80 21 71 C 20 81 26 88 36 90 Z"
              fill={`url(#${gradId})`}
            />
            <path
              d="M 41 116 C 24 120 19 106 28 96 C 28 106 35 110 45 111 Z"
              fill={`url(#${gradId})`}
            />
            {/* Inner curled tendrils */}
            <path
              d="M 42 62 C 50 56 54 64 47 70 M 38 84 C 48 79 51 87 44 93 M 47 104 C 57 101 59 109 52 114"
              fill="none"
              stroke={`url(#${gradId})`}
              strokeWidth="1.5"
              strokeLinecap="round"
            />
            <circle cx="58" cy="128" r="2.6" fill={`url(#${gradId})`} />
            <circle cx="24" cy="60" r="1.8" fill={`url(#${gradId})`} />
          </g>
        );

      case 'rice-wheat-khmer':
        return (
          <g>
            {/* Curved Paddy Stem */}
            <path
              d="M 56 128 C 28 106 26 68 50 38"
              fill="none"
              stroke={`url(#${gradId})`}
              strokeWidth="1.7"
              strokeLinecap="round"
            />
            {/* Paired plump Cambodian rice grains */}
            {[
              { x: 48, y: 40, rot: -55 },
              { x: 40, y: 50, rot: -42 },
              { x: 50, y: 53, rot: -74 },
              { x: 35, y: 63, rot: -26 },
              { x: 46, y: 67, rot: -62 },
              { x: 32, y: 77, rot: -10 },
              { x: 44, y: 81, rot: -46 },
              { x: 33, y: 92, rot: 8 },
              { x: 45, y: 96, rot: -30 },
              { x: 38, y: 106, rot: 22 },
              { x: 50, y: 109, rot: -14 },
              { x: 47, y: 119, rot: 36 },
            ].map((g, idx) => (
              <g key={idx} transform={`translate(${g.x} ${g.y}) rotate(${g.rot})`}>
                <path
                  d="M -7 0 C -3 -3.5 3 -3.5 7 0 C 3 3.5 -3 3.5 -7 0 Z"
                  fill={`url(#${gradId})`}
                />
                {/* Fine rice awn bristle */}
                <line x1="7" y1="0" x2="12" y2="-1" stroke={`url(#${gradId})`} strokeWidth="0.7" />
              </g>
            ))}
          </g>
        );

      case 'royal-wings':
        return (
          <g fill={`url(#${gradId})`}>
            {/* Outer upper wing feathers */}
            <path d="M 78 52 C 54 44 32 34 14 26 C 24 38 44 48 74 56 Z" />
            <path d="M 75 60 C 52 54 32 47 18 42 C 27 52 46 59 73 64 Z" />
            <path d="M 73 69 C 52 65 34 60 23 57 C 32 66 49 71 72 73 Z" />
            <path d="M 72 78 C 53 76 38 73 29 71 C 37 78 52 82 72 82 Z" />
            <path d="M 72 87 C 56 87 43 85 36 84 C 44 90 56 92 73 91 Z" />
            <path d="M 74 96 C 60 97 50 96 44 96 C 51 101 61 102 75 100 Z" />
            {/* Inner shield accent arc */}
            <path
              d="M 78 46 C 66 64 66 90 77 108"
              fill="none"
              stroke={`url(#${gradId})`}
              strokeWidth="1.6"
              strokeLinecap="round"
            />
          </g>
        );

      case 'floral-baroque':
        return (
          <g fill="none" stroke={`url(#${gradId})`} strokeLinecap="round">
            {/* Upper scrolling C-curls */}
            <path d="M 68 42 C 44 26 24 38 30 56 C 35 69 52 63 48 50" strokeWidth="2.1" />
            {/* Main vertical S-scroll */}
            <path d="M 48 64 C 22 68 18 96 40 104 C 54 109 58 94 46 90" strokeWidth="2" />
            {/* Lower base flourish */}
            <path d="M 42 106 C 30 118 42 130 58 124" strokeWidth="1.8" />
            {/* Rosette & leaf buds */}
            <circle cx="32" cy="48" r="3" fill={`url(#${gradId})`} stroke="none" />
            <circle cx="24" cy="82" r="2.6" fill={`url(#${gradId})`} stroke="none" />
            <path d="M 55 122 Q 62 116 66 124 Q 58 128 55 122 Z" fill={`url(#${gradId})`} stroke="none" />
          </g>
        );

      case 'star-halo':
        return (
          <g>
            {/* Radiant sunbeams */}
            {[-48, -32, -16, 0, 16, 32, 48].map((angle, idx) => (
              <line
                key={idx}
                x1="140"
                y1="52"
                x2={140 - Math.cos((angle * Math.PI) / 180) * (idx % 2 === 0 ? 96 : 82)}
                y2={52 - Math.sin((angle * Math.PI) / 180) * (idx % 2 === 0 ? 52 : 42)}
                stroke={`url(#${gradId})`}
                strokeWidth={idx % 2 === 0 ? '1.2' : '0.8'}
                strokeOpacity="0.65"
              />
            ))}
            {/* Three Star Cluster on left */}
            <polygon
              points="46,44 48,50 54,50 49,54 51,60 46,56 41,60 43,54 38,50 44,50"
              fill={`url(#${gradId})`}
            />
            <polygon
              points="34,68 35.5,72 40,72 36.5,75 38,79 34,76.5 30,79 31.5,75 28,72 32.5,72"
              fill={`url(#${gradId})`}
            />
            <polygon
              points="46,90 47.5,93.5 51,93.5 48,96 49.5,99.5 46,97.5 42.5,99.5 44,96 41,93.5 44.5,93.5"
              fill={`url(#${gradId})`}
            />
          </g>
        );

      case 'diamond-crest':
        return (
          <g fill="none" stroke={`url(#${gradId})`}>
            {/* Stepped geometric architectural brackets */}
            <path d="M 74 28 L 42 28 L 32 38 L 32 108 L 44 120 L 66 120" strokeWidth="1.6" />
            <path d="M 70 35 L 47 35 L 39 43 L 39 103 L 49 113 L 62 113" strokeWidth="0.9" strokeOpacity="0.85" />
            {/* Corner diamond jewels */}
            <polygon points="32,73 37,68 42,73 37,78" fill={`url(#${gradId})`} stroke="none" />
            <circle cx="68" cy="28" r="2.2" fill={primaryColor} stroke={`url(#${gradId})`} strokeWidth="1" />
            <circle cx="64" cy="120" r="2" fill={`url(#${gradId})`} stroke="none" />
          </g>
        );

      case 'laurel-crossed-wreath':
      case 'crown-laurel-wreath':
      case 'three-star-laurel':
        return (
          <g>
            {/* Crossed-Stem U-Shaped Gold Laurel Wreath */}
            <path
              d="M 148 131 C 94 128 38 106 34 62 C 32 42 40 28 52 18"
              fill="none"
              stroke={`url(#${gradId})`}
              strokeWidth="1.65"
              strokeLinecap="round"
            />
            {[
              { x: 52, y: 18, rot: -55, s: 0.9 },
              { x: 43, y: 29, rot: -120, s: 0.94 },
              { x: 48, y: 33, rot: -42, s: 0.95 },
              { x: 37, y: 44, rot: -135, s: 1.0 },
              { x: 44, y: 49, rot: -48, s: 1.0 },
              { x: 34, y: 61, rot: -152, s: 1.04 },
              { x: 43, y: 66, rot: -52, s: 1.02 },
              { x: 36, y: 78, rot: -168, s: 1.04 },
              { x: 47, y: 83, rot: -42, s: 1.0 },
              { x: 44, y: 95, rot: 175, s: 1.02 },
              { x: 56, y: 99, rot: -28, s: 0.98 },
              { x: 59, y: 110, rot: 158, s: 0.96 },
              { x: 72, y: 112, rot: -14, s: 0.94 },
              { x: 79, y: 121, rot: 145, s: 0.9 },
              { x: 93, y: 121, rot: -4, s: 0.88 },
              { x: 103, y: 128, rot: 138, s: 0.82 },
              { x: 117, y: 127, rot: 6, s: 0.8 },
            ].map((leaf, i) => (
              <g
                key={`cw-l-${i}`}
                transform={`translate(${leaf.x} ${leaf.y}) rotate(${leaf.rot}) scale(${leaf.s})`}
              >
                <path
                  d="M 0 0 C 4.5 -4.2 12.2 -3.8 18.5 0 C 12.2 3.8 4.5 4.2 0 0 Z"
                  fill={`url(#${gradId})`}
                  stroke="#9A6914"
                  strokeWidth="0.35"
                />
                <path
                  d="M 2 0 Q 9 -1 15.5 0"
                  fill="none"
                  stroke="#FFF7D6"
                  strokeWidth="0.55"
                  strokeOpacity="0.65"
                />
              </g>
            ))}
          </g>
        );

      case 'classic-laurel':
      default:
        return (
          <g>
            {/* Graceful Arched Laurel Stem matching reference */}
            <path
              d="M 59 129 C 29 108 24 66 47 31"
              fill="none"
              stroke={`url(#${gradId})`}
              strokeWidth="1.5"
              strokeLinecap="round"
            />
            {[
              { x: 47, y: 31, rot: -52, s: 0.92 },
              { x: 41, y: 41, rot: -115, s: 0.95 },
              { x: 44, y: 45, rot: -38, s: 0.96 },
              { x: 36, y: 55, rot: -130, s: 1.0 },
              { x: 41, y: 59, rot: -45, s: 1.0 },
              { x: 33, y: 70, rot: -145, s: 1.04 },
              { x: 39, y: 75, rot: -52, s: 1.02 },
              { x: 32, y: 86, rot: -162, s: 1.04 },
              { x: 40, y: 90, rot: -46, s: 1.0 },
              { x: 35, y: 101, rot: -178, s: 1.02 },
              { x: 44, y: 105, rot: -32, s: 0.98 },
              { x: 43, y: 115, rot: 162, s: 0.98 },
              { x: 52, y: 118, rot: -14, s: 0.95 },
              { x: 51, y: 126, rot: 145, s: 0.9 },
            ].map((leaf, i) => (
              <g
                key={`cl-l-${i}`}
                transform={`translate(${leaf.x} ${leaf.y}) rotate(${leaf.rot}) scale(${leaf.s})`}
              >
                {/* Pointed Lanceolate Gold Leaf */}
                <path
                  d="M 0 0 C 4.5 -4.2 12.2 -3.8 18.5 0 C 12.2 3.8 4.5 4.2 0 0 Z"
                  fill={`url(#${gradId})`}
                  stroke="#9A6914"
                  strokeWidth="0.35"
                />
                {/* Fine Specular Leaf Vein Highlight */}
                <path
                  d="M 2 0 Q 9 -1 15.5 0"
                  fill="none"
                  stroke="#FFF7D6"
                  strokeWidth="0.55"
                  strokeOpacity="0.65"
                />
              </g>
            ))}
          </g>
        );
    }
  };

  const renderCenterTop = () => {
    if (style === 'crown-laurel-wreath') {
      return (
        <g transform="translate(140, 12)">
          <path
            d="M -18 8 L -21 -6 L -10 1 L 0 -10 L 10 1 L 21 -6 L 18 8 Z"
            fill={`url(#${gradId})`}
            stroke="#9A6914"
            strokeWidth="0.7"
          />
          <rect x="-18" y="9.5" width="36" height="3.2" rx="1.2" fill={`url(#${gradId})`} stroke="#9A6914" strokeWidth="0.5" />
          <circle cx="-21" cy="-7.5" r="2" fill="#FFF7D6" stroke="#9A6914" strokeWidth="0.5" />
          <circle cx="0" cy="-11.5" r="2.5" fill="#FFF7D6" stroke="#9A6914" strokeWidth="0.5" />
          <circle cx="21" cy="-7.5" r="2" fill="#FFF7D6" stroke="#9A6914" strokeWidth="0.5" />
        </g>
      );
    }
    if (style === 'three-star-laurel') {
      return (
        <g fill={`url(#${gradId})`} stroke="#9A6914" strokeWidth="0.4">
          <polygon
            transform="translate(120, 15) scale(0.62)"
            points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
          />
          <polygon
            transform="translate(140, 10) scale(0.88)"
            points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
          />
          <polygon
            transform="translate(160, 15) scale(0.62)"
            points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
          />
        </g>
      );
    }
    return null;
  };

  return (
    <svg
      viewBox="0 0 280 135"
      className={className}
      aria-hidden="true"
    >
      <defs>
        <linearGradient id={gradId} x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#F5D77A" />
          <stop offset="45%" stopColor={accentColor} />
          <stop offset="100%" stopColor="#9A6914" />
        </linearGradient>
      </defs>
      {renderLeftHalf()}
      <g transform="translate(280, 0) scale(-1, 1)">{renderLeftHalf()}</g>
      {renderCenterTop()}
    </svg>
  );
};

interface LogoDividerProps {
  style: DividerOrnamentStyle;
  accentColor?: string;
  primaryColor?: string;
  customUrl?: string;
  width?: number;
  scale?: number;
  offsetY?: number;
  opacity?: number;
  className?: string;
}

/**
 * Renders the ornamental divider (ក្បាច់ខណ្ឌ) directly below the brand/shop name.
 * Fully customizable in style, color, width, scale, vertical offset, opacity, or custom image.
 */
export const LogoDividerOrnament: React.FC<LogoDividerProps> = ({
  style,
  accentColor = '#D4A13A',
  primaryColor = '#0A224E',
  customUrl,
  width,
  scale = 1,
  offsetY = 0,
  opacity = 1,
  className = 'w-[280px] h-[26px]',
}) => {
  const uid = useId().replace(/:/g, '');
  const gradId = `divGoldGrad-${uid}`;

  if (style === 'none') return null;

  const wrapperStyle: React.CSSProperties = {
    opacity,
    transform: `translateY(${offsetY}px) scale(${scale})`,
    transformOrigin: 'center center',
    ...(width ? { width: `${width}px` } : {}),
  };

  if (style === 'custom') {
    if (!customUrl) return null;
    return (
      <div
        className={`flex items-center justify-center transition-all ${width ? '' : className}`}
        style={wrapperStyle}
      >
        <img
          src={customUrl}
          alt="Custom Divider Ornament"
          className="max-h-[42px] w-full object-contain"
        />
      </div>
    );
  }

  return (
    <svg
      viewBox="0 0 280 26"
      fill="none"
      className={width ? 'h-[26px] transition-all overflow-visible' : `${className} transition-all overflow-visible`}
      style={wrapperStyle}
      aria-hidden="true"
    >
      <defs>
        <linearGradient id={gradId} x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stopColor={accentColor} stopOpacity="0" />
          <stop offset="20%" stopColor={accentColor} />
          <stop offset="50%" stopColor="#F5D77A" />
          <stop offset="80%" stopColor={accentColor} />
          <stop offset="100%" stopColor={accentColor} stopOpacity="0" />
        </linearGradient>
      </defs>

      {style === 'baroque-fleur' && (
        <>
          {/* Long Razor-Tapered Outer Gold Spear Rails */}
          <path d="M 4 13 L 76 11.6 L 76 14.4 Z" fill={`url(#${gradId})`} />
          <path d="M 276 13 L 204 11.6 L 204 14.4 Z" fill={`url(#${gradId})`} />

          {/* Outer Gold Beads & Double S-Volute Scrolls */}
          {[0, 1].map((side) => (
            <g
              key={`baroque-side-${side}`}
              transform={side === 1 ? 'translate(280, 0) scale(-1, 1)' : undefined}
            >
              {/* Faceted Bead */}
              <circle cx="80" cy="13" r="2.2" fill={accentColor} />
              <circle cx="80" cy="12.4" r="0.9" fill="#FFF7D6" fillOpacity="0.8" />

              {/* Small Outer Spiral Curl */}
              <path
                d="M 85 13 C 89 7 98 7 100 12 C 102 16 97 19 93 17 C 90 15.5 91 12 94 12.5"
                stroke={accentColor}
                strokeWidth="1.5"
                strokeLinecap="round"
                fill="none"
              />
              <path
                d="M 97 13 C 101 18 107 18 110 14"
                stroke={accentColor}
                strokeWidth="1.2"
                strokeLinecap="round"
                fill="none"
              />

              {/* Sculptural Main C-Scroll Arch */}
              <path
                d="M 136 10.5 C 127 2.5 112 3.5 109 12.5 C 106.5 20 116 23.5 123 19.5 C 128 16.5 127 10.5 121.5 10 C 118 9.6 116.5 12.5 119 14.5 C 120.8 16 123 14.5 122 12.8"
                stroke={`url(#${gradId})`}
                strokeWidth="2.1"
                strokeLinecap="round"
                fill="none"
              />
              {/* Inner Counter-Volute */}
              <path
                d="M 135 16.5 C 129 21.5 121 21 117 17"
                stroke={accentColor}
                strokeWidth="1.3"
                strokeLinecap="round"
                fill="none"
              />
              <circle cx="104.5" cy="13" r="1.4" fill={accentColor} />
            </g>
          ))}

          {/* Central Elongated Two-Pointed Heraldic Diamond Finial */}
          <path
            d="M 140 1.5 L 145.5 13 L 140 24.5 L 134.5 13 Z"
            fill={`url(#${gradId})`}
            stroke="#9A6914"
            strokeWidth="0.4"
          />
          <path
            d="M 140 5 L 142.8 13 L 140 21 L 137.2 13 Z"
            fill="#FFF7D6"
            fillOpacity="0.45"
          />
        </>
      )}

      {style === 'acanthus-gold-line' && (
        <>
          <path d="M 4 13 L 88 11.7 L 88 14.3 Z" fill={`url(#${gradId})`} />
          <path d="M 276 13 L 192 11.7 L 192 14.3 Z" fill={`url(#${gradId})`} />
          {[0, 1].map((side) => (
            <g
              key={`ac-side-${side}`}
              transform={side === 1 ? 'translate(280, 0) scale(-1, 1)' : undefined}
            >
              <path
                d="M 88 13 C 96 6 108 6 112 13 C 115 19 107 21 103 16 C 101 13 104 10 107 12"
                stroke={`url(#${gradId})`}
                strokeWidth="1.8"
                strokeLinecap="round"
                fill="none"
              />
              <path
                d="M 114 11 C 122 3 132 5 137 13"
                stroke={`url(#${gradId})`}
                strokeWidth="1.9"
                strokeLinecap="round"
                fill="none"
              />
              <path
                d="M 116 15 C 123 21 131 21 137 14"
                stroke={accentColor}
                strokeWidth="1.4"
                strokeLinecap="round"
                fill="none"
              />
              <path d="M 96 11 C 94 7 98 5 101 8 Z" fill={accentColor} />
            </g>
          ))}
          <path d="M 140 4 L 144 13 L 140 22 L 136 13 Z" fill={`url(#${gradId})`} />
        </>
      )}

      {style === 'beaded-diamond-bar' && (
        <>
          <path d="M 4 13 L 104 11.4 L 104 14.6 Z" fill={`url(#${gradId})`} />
          <path d="M 276 13 L 176 11.4 L 176 14.6 Z" fill={`url(#${gradId})`} />
          <circle cx="106" cy="13" r="2.4" fill={`url(#${gradId})`} />
          <circle cx="116" cy="13" r="3" fill={`url(#${gradId})`} stroke="#9A6914" strokeWidth="0.4" />
          <circle cx="126.5" cy="13" r="1.8" fill={accentColor} />
          <circle cx="153.5" cy="13" r="1.8" fill={accentColor} />
          <circle cx="164" cy="13" r="3" fill={`url(#${gradId})`} stroke="#9A6914" strokeWidth="0.4" />
          <circle cx="174" cy="13" r="2.4" fill={`url(#${gradId})`} />
          <polygon
            points="140,2.5 147.5,13 140,23.5 132.5,13"
            fill={`url(#${gradId})`}
            stroke="#9A6914"
            strokeWidth="0.5"
          />
          <polygon points="140,6 143.8,13 140,20 136.2,13" fill="#FFF7D6" fillOpacity="0.6" />
        </>
      )}

      {style === 'khmer-lotus' && (
        <>
          <path d="M 14 13 L 96 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          <path d="M 184 13 L 266 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          {/* Khmer Angkor Lotus Flower Centerpiece */}
          <path
            d="M 140 3 C 143 8 144 13 140 18 C 136 13 137 8 140 3 Z"
            fill={`url(#${gradId})`}
          />
          <path
            d="M 136 17 C 130 13 128 8 131 5 C 134 9 136 13 137 17 Z"
            fill={accentColor}
          />
          <path
            d="M 144 17 C 150 13 152 8 149 5 C 146 9 144 13 143 17 Z"
            fill={accentColor}
          />
          {/* Flanking Kbach Flame Tails */}
          <path
            d="M 129 15 C 121 18 113 12 108 16 C 105 18 102 15 98 13"
            stroke={accentColor}
            strokeWidth="1.4"
            strokeLinecap="round"
            fill="none"
          />
          <path
            d="M 151 15 C 159 18 167 12 172 16 C 175 18 178 15 182 13"
            stroke={accentColor}
            strokeWidth="1.4"
            strokeLinecap="round"
            fill="none"
          />
          <ellipse cx="140" cy="20.5" rx="9" ry="1.4" fill={accentColor} />
        </>
      )}

      {style === 'khmer-angkor-crest' && (
        <>
          {/* Tapered Angkor Horizon Bars */}
          <path d="M 6 14 L 84 13 L 84 15 Z" fill={`url(#${gradId})`} />
          <path d="M 274 14 L 196 13 L 196 15 Z" fill={`url(#${gradId})`} />
          {/* Symmetrical Khmer Kbach Flame Spirals */}
          {[0, 1].map((side) => (
            <g
              key={`angkor-side-${side}`}
              transform={side === 1 ? 'translate(280, 0) scale(-1, 1)' : undefined}
            >
              <path
                d="M 86 14 C 95 6 108 6 112 14 C 115 20 107 22 104 17 C 102 14 105 11 108 13"
                stroke={`url(#${gradId})`}
                strokeWidth="1.7"
                strokeLinecap="round"
                fill="none"
              />
              <path
                d="M 114 14 C 120 7 129 8 133 14"
                stroke={accentColor}
                strokeWidth="1.6"
                strokeLinecap="round"
                fill="none"
              />
              {/* Flame Tongue Leaves */}
              <path
                d="M 122 8 C 120 3 125 2 127 6 C 125 6 124 8 122 8 Z"
                fill={accentColor}
              />
              <circle cx="88" cy="14" r="1.8" fill={accentColor} />
            </g>
          ))}
          {/* Center Khmer Temple Spire (Prasat Peak) */}
          <path
            d="M 140 1.5 L 143.5 9 L 147 14 L 140 23.5 L 133 14 L 136.5 9 Z"
            fill={`url(#${gradId})`}
            stroke="#9A6914"
            strokeWidth="0.4"
          />
          <circle cx="140" cy="13.5" r="2.2" fill="#FFFDF8" fillOpacity="0.7" />
        </>
      )}

      {style === 'royal-crown-line' && (
        <>
          <path d="M 18 15 L 114 15" stroke={`url(#${gradId})`} strokeWidth="1.3" />
          <path d="M 166 15 L 262 15" stroke={`url(#${gradId})`} strokeWidth="1.3" />
          {/* Regal 5-Point Crown */}
          <path
            d="M 126 17 L 123 8 L 131 12 L 140 5 L 149 12 L 157 8 L 154 17 Z"
            fill={`url(#${gradId})`}
            stroke={primaryColor}
            strokeWidth="0.4"
          />
          <rect x="126" y="18.5" width="28" height="2" rx="1" fill={accentColor} />
          <circle cx="116" cy="15" r="1.6" fill={accentColor} />
          <circle cx="164" cy="15" r="1.6" fill={accentColor} />
        </>
      )}

      {style === 'royal-plume' && (
        <>
          <path d="M 12 13 L 88 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          <path d="M 192 13 L 268 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          {[0, 1].map((side) => (
            <g
              key={`plume-side-${side}`}
              transform={side === 1 ? 'translate(280, 0) scale(-1, 1)' : undefined}
            >
              {/* Layered Royal Swan/Peacock Feather Plumes */}
              <path
                d="M 132 13 C 122 5 106 5 94 11 C 106 11 118 12 130 14 Z"
                fill={`url(#${gradId})`}
              />
              <path
                d="M 130 15 C 118 20 105 20 96 16 C 107 16 119 15 130 15 Z"
                fill={accentColor}
                fillOpacity="0.85"
              />
              <circle cx="91" cy="13" r="1.7" fill={accentColor} />
            </g>
          ))}
          <polygon points="140,6 146,13 140,20 134,13" fill={`url(#${gradId})`} />
          <circle cx="140" cy="13" r="2" fill="#FFFDF8" fillOpacity="0.75" />
        </>
      )}

      {style === 'diamond-stars' && (
        <>
          <path d="M 16 13 L 108 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          <path d="M 172 13 L 264 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          {/* Center Diamond */}
          <polygon points="140,5 147,13 140,21 133,13" fill={`url(#${gradId})`} />
          <polygon points="140,8.5 143.5,13 140,17.5 136.5,13" fill="#FFFDF8" fillOpacity="0.6" />
          {/* Left & Right 4-Point Sparkle Stars */}
          <path d="M 120 7 L 121.5 11.5 L 126 13 L 121.5 14.5 L 120 19 L 118.5 14.5 L 114 13 L 118.5 11.5 Z" fill={accentColor} />
          <path d="M 160 7 L 161.5 11.5 L 166 13 L 161.5 14.5 L 160 19 L 158.5 14.5 L 154 13 L 158.5 11.5 Z" fill={accentColor} />
          <circle cx="109" cy="13" r="1.5" fill={accentColor} />
          <circle cx="171" cy="13" r="1.5" fill={accentColor} />
        </>
      )}

      {style === 'double-scallop' && (
        <>
          <path d="M 16 13 L 98 13" stroke={`url(#${gradId})`} strokeWidth="1.1" />
          <path d="M 182 13 L 264 13" stroke={`url(#${gradId})`} strokeWidth="1.1" />
          {/* Horizontal Olive Leaf Garland */}
          {[108, 118, 128].map((x, idx) => (
            <g key={`dl-${idx}`}>
              <ellipse cx={x} cy="9.5" rx="5.5" ry="2.2" transform={`rotate(-20 ${x} 9.5)`} fill={accentColor} />
              <ellipse cx={x} cy="16.5" rx="5.5" ry="2.2" transform={`rotate(20 ${x} 16.5)`} fill={accentColor} />
            </g>
          ))}
          {[152, 162, 172].map((x, idx) => (
            <g key={`dr-${idx}`}>
              <ellipse cx={x} cy="9.5" rx="5.5" ry="2.2" transform={`rotate(20 ${x} 9.5)`} fill={accentColor} />
              <ellipse cx={x} cy="16.5" rx="5.5" ry="2.2" transform={`rotate(-20 ${x} 16.5)`} fill={accentColor} />
            </g>
          ))}
          <circle cx="140" cy="13" r="3.4" fill={`url(#${gradId})`} />
        </>
      )}

      {style === 'infinity-filigree' && (
        <>
          <path d="M 10 13 L 94 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          <path d="M 186 13 L 270 13" stroke={`url(#${gradId})`} strokeWidth="1.2" />
          {/* Braided Infinity Wave Loops */}
          <path
            d="M 96 13 C 106 4 118 4 128 13 C 138 22 142 22 152 13 C 162 4 174 4 184 13"
            stroke={`url(#${gradId})`}
            strokeWidth="1.7"
            fill="none"
            strokeLinecap="round"
          />
          <path
            d="M 96 13 C 106 22 118 22 128 13 C 138 4 142 4 152 13 C 162 22 174 22 184 13"
            stroke={accentColor}
            strokeWidth="1.3"
            fill="none"
            strokeLinecap="round"
          />
          <circle cx="140" cy="13" r="2.6" fill="#FFFDF8" stroke={accentColor} strokeWidth="1.4" />
        </>
      )}

      {style === 'minimal-gold-bar' && (
        <>
          <path d="M 24 13 L 130 13" stroke={`url(#${gradId})`} strokeWidth="1.4" />
          <path d="M 150 13 L 256 13" stroke={`url(#${gradId})`} strokeWidth="1.4" />
          <polygon points="140,8.5 144.5,13 140,17.5 135.5,13" fill={accentColor} />
        </>
      )}
    </svg>
  );
};
