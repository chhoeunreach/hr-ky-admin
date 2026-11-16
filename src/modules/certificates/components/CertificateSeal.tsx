import React from 'react';
import { CertificateData } from '../types/certificate';

interface SealProps {
  seal: CertificateData['seal'];
  primaryColor: string;
  accentColor: string;
}

export const CertificateSeal: React.FC<SealProps> = ({
  seal,
  primaryColor,
  accentColor,
}) => {
  if (!seal.enabled || seal.style === 'none') return null;

  return (
    <div className="relative inline-flex items-center justify-center select-none group">
      <svg
        width="132"
        height="132"
        viewBox="0 0 160 160"
        className="overflow-visible drop-shadow-xl transition-transform duration-300 group-hover:scale-105"
      >
        <defs>
          {/* Intense Metallic Gold Leaf Radial Gradient */}
          <radialGradient id="seal3DGold" cx="38%" cy="32%" r="65%">
            <stop offset="0%" stopColor="#FFFEE8" />
            <stop offset="25%" stopColor="#FDE68A" />
            <stop offset="55%" stopColor="#D97706" />
            <stop offset="85%" stopColor="#92400E" />
            <stop offset="100%" stopColor="#451A03" />
          </radialGradient>

          {/* Shimmer Gold for Serrated Edge */}
          <linearGradient id="edgeGoldFoil" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stopColor="#FBBF24" />
            <stop offset="30%" stopColor="#FEF3C7" />
            <stop offset="60%" stopColor="#D97706" />
            <stop offset="100%" stopColor="#78350F" />
          </linearGradient>

          {/* Specular Light Curved Sheen */}
          <linearGradient id="specularGleam" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stopColor="#FFFFFF" stopOpacity="0.75" />
            <stop offset="40%" stopColor="#FEF3C7" stopOpacity="0.15" />
            <stop offset="100%" stopColor="#FFFFFF" stopOpacity="0" />
          </linearGradient>

          {/* Satin Ruby Ribbon Tails */}
          <linearGradient id="satinRubyLeft" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#881337" />
            <stop offset="40%" stopColor="#BE123C" />
            <stop offset="80%" stopColor="#9F1239" />
            <stop offset="100%" stopColor="#4C0519" />
          </linearGradient>
          <linearGradient id="satinRubyRight" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#4C0519" />
            <stop offset="25%" stopColor="#BE123C" />
            <stop offset="70%" stopColor="#9F1239" />
            <stop offset="100%" stopColor="#881337" />
          </linearGradient>

          {/* Satin Royal Navy Ribbon Tails */}
          <linearGradient id="satinNavyLeft" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#04112A" />
            <stop offset="42%" stopColor="#0C2A60" />
            <stop offset="78%" stopColor="#081E47" />
            <stop offset="100%" stopColor="#030D21" />
          </linearGradient>
          <linearGradient id="satinNavyRight" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#030D21" />
            <stop offset="28%" stopColor="#0C2A60" />
            <stop offset="72%" stopColor="#081E47" />
            <stop offset="100%" stopColor="#04112A" />
          </linearGradient>

          {/* Satin Emerald Ribbon Tails */}
          <linearGradient id="satinEmeraldLeft" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#064E3B" />
            <stop offset="50%" stopColor="#047857" />
            <stop offset="100%" stopColor="#022C22" />
          </linearGradient>
          <linearGradient id="satinEmeraldRight" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stopColor="#022C22" />
            <stop offset="50%" stopColor="#047857" />
            <stop offset="100%" stopColor="#064E3B" />
          </linearGradient>

          {/* Royal Navy Medallion Radial Gradient */}
          <radialGradient id="navySealRadial" cx="40%" cy="35%" r="65%">
            <stop offset="0%" stopColor="#143D85" />
            <stop offset="50%" stopColor="#092653" />
            <stop offset="100%" stopColor="#030E24" />
          </radialGradient>

          {/* Crimson Red Wax Seal Radial Gradient */}
          <radialGradient id="redWaxRadial" cx="35%" cy="32%" r="68%">
            <stop offset="0%" stopColor="#F87171" />
            <stop offset="28%" stopColor="#DC2626" />
            <stop offset="68%" stopColor="#991B1B" />
            <stop offset="100%" stopColor="#5C0A0A" />
          </radialGradient>

          <path id="sealTextArcTop" d="M 32,80 A 48,48 0 1,1 128,80" />
          <path id="sealTextArcBottom" d="M 128,80 A 48,48 0 0,1 32,80" />
        </defs>

        {/* Satin Ribbon Tails if royal-ribbon, embossed-gold, crown-laurel, navy-k-medal, or gold-k-ribbon-seal */}
        {(seal.style === 'royal-ribbon' ||
          seal.style === 'embossed-gold' ||
          seal.style === 'crown-laurel' ||
          seal.style === 'navy-k-medal' ||
          seal.style === 'gold-k-ribbon-seal') && (
          <g className="filter drop-shadow-md">
            {(() => {
              const color =
                seal.style === 'navy-k-medal' || seal.style === 'gold-k-ribbon-seal'
                  ? seal.ribbonColor || 'navy'
                  : seal.ribbonColor || 'ruby';
              const leftFill =
                color === 'navy'
                  ? 'url(#satinNavyLeft)'
                  : color === 'emerald'
                  ? 'url(#satinEmeraldLeft)'
                  : color === 'gold'
                  ? 'url(#seal3DGold)'
                  : 'url(#satinRubyLeft)';
              const rightFill =
                color === 'navy'
                  ? 'url(#satinNavyRight)'
                  : color === 'emerald'
                  ? 'url(#satinEmeraldRight)'
                  : color === 'gold'
                  ? 'url(#seal3DGold)'
                  : 'url(#satinRubyRight)';
              const trimColor = color === 'gold' ? '#78350F' : '#FDE68A';

              if (seal.style === 'crown-laurel' || seal.style === 'navy-k-medal' || seal.style === 'gold-k-ribbon-seal') {
                return (
                  <>
                    {/* Left Angled Ribbon Tail with Gold Edge Trim */}
                    <polygon points="56,112 30,156 50,148 58,162 74,116" fill={leftFill} />
                    <polyline points="56,112 30,156 50,148 58,162 74,116" fill="none" stroke={trimColor} strokeWidth="1.4" />
                    {/* Right Angled Ribbon Tail with Gold Edge Trim */}
                    <polygon points="86,116 102,162 110,148 130,156 104,112" fill={rightFill} />
                    <polyline points="86,116 102,162 110,148 130,156 104,112" fill="none" stroke={trimColor} strokeWidth="1.4" />
                  </>
                );
              }

              return (
                <>
                  {/* Left Ribbon Tail */}
                  <polygon points="56,105 38,158 55,148 70,158 68,110" fill={leftFill} />
                  <polyline points="38,158 55,148 70,158" fill="none" stroke={trimColor} strokeWidth="1.2" />

                  {/* Right Ribbon Tail */}
                  <polygon points="92,110 90,158 105,148 122,158 104,105" fill={rightFill} />
                  <polyline points="90,158 105,148 122,158" fill="none" stroke={trimColor} strokeWidth="1.2" />
                </>
              );
            })()}
          </g>
        )}

        {/* Scalloped / Serrated / Organic Wax Outer Medallion Edge */}
        <g>
          {seal.style === 'wax-red-k' ? (
            <>
              {/* Organic Crimson Wax Seal Perimeter */}
              <path
                d={Array.from({ length: 18 }).map((_, i) => {
                  const a1 = ((i * 360) / 18) * (Math.PI / 180);
                  const aMid = (((i + 0.5) * 360) / 18) * (Math.PI / 180);
                  const a2 = (((i + 1) * 360) / 18) * (Math.PI / 180);
                  const wobble = (i % 3 === 0 ? 3 : i % 2 === 0 ? -1.5 : 1);
                  const rIn = 66 + wobble;
                  const rOut = 73 + (i % 2 === 0 ? 2 : -1);
                  const x1 = 80 + rIn * Math.cos(a1);
                  const y1 = 80 + rIn * Math.sin(a1);
                  const xm = 80 + rOut * Math.cos(aMid);
                  const ym = 80 + rOut * Math.sin(aMid);
                  const x2 = 80 + rIn * Math.cos(a2);
                  const y2 = 80 + rIn * Math.sin(a2);
                  return `${i === 0 ? `M ${x1} ${y1}` : ''} Q ${xm} ${ym} ${x2} ${y2}`;
                }).join(' ') + ' Z'}
                fill="url(#redWaxRadial)"
                stroke="#450A0A"
                strokeWidth="1.2"
              />
              <circle cx="80" cy="80" r="56" fill="url(#redWaxRadial)" stroke="#FCA5A5" strokeWidth="1" strokeOpacity="0.45" />
              <circle cx="80" cy="80" r="46" fill="#7F1D1D" fillOpacity="0.55" stroke="#B91C1C" strokeWidth="2.4" />
            </>
          ) : seal.style === 'navy-laurel-crest' ? (
            <>
              {/* Navy Scalloped Royal Medallion */}
              <path
                d={Array.from({ length: 24 }).map((_, i) => {
                  const a1 = ((i * 360) / 24) * (Math.PI / 180);
                  const aMid = (((i + 0.5) * 360) / 24) * (Math.PI / 180);
                  const a2 = (((i + 1) * 360) / 24) * (Math.PI / 180);
                  const rIn = 66;
                  const rOut = 74;
                  const x1 = 80 + rIn * Math.cos(a1);
                  const y1 = 80 + rIn * Math.sin(a1);
                  const xm = 80 + rOut * Math.cos(aMid);
                  const ym = 80 + rOut * Math.sin(aMid);
                  const x2 = 80 + rIn * Math.cos(a2);
                  const y2 = 80 + rIn * Math.sin(a2);
                  return `${i === 0 ? `M ${x1} ${y1}` : ''} Q ${xm} ${ym} ${x2} ${y2}`;
                }).join(' ') + ' Z'}
                fill="url(#navySealRadial)"
                stroke="#04112A"
                strokeWidth="1.2"
              />
              <circle cx="80" cy="80" r="59" fill="url(#navySealRadial)" stroke="url(#edgeGoldFoil)" strokeWidth="1.8" />
              <circle cx="80" cy="80" r="52" fill="none" stroke="#FDE68A" strokeWidth="0.9" strokeOpacity="0.7" />
            </>
          ) : seal.style === 'crown-laurel' ||
            seal.style === 'navy-k-medal' ||
            seal.style === 'gold-k-medallion' ||
            seal.style === 'gold-k-ribbon-seal' ? (
            <>
              <path
                d={Array.from({
                  length:
                    seal.style === 'gold-k-medallion' || seal.style === 'gold-k-ribbon-seal'
                      ? 32
                      : 24,
                }).map((_, i) => {
                  const count =
                    seal.style === 'gold-k-medallion' || seal.style === 'gold-k-ribbon-seal'
                      ? 32
                      : 24;
                  const a1 = ((i * 360) / count) * (Math.PI / 180);
                  const aMid = (((i + 0.5) * 360) / count) * (Math.PI / 180);
                  const a2 = (((i + 1) * 360) / count) * (Math.PI / 180);
                  const rIn = 65;
                  const rOut = 74;
                  const x1 = 80 + rIn * Math.cos(a1);
                  const y1 = 80 + rIn * Math.sin(a1);
                  const xm = 80 + rOut * Math.cos(aMid);
                  const ym = 80 + rOut * Math.sin(aMid);
                  const x2 = 80 + rIn * Math.cos(a2);
                  const y2 = 80 + rIn * Math.sin(a2);
                  return `${i === 0 ? `M ${x1} ${y1}` : ''} Q ${xm} ${ym} ${x2} ${y2}`;
                }).join(' ') + ' Z'}
                fill="url(#seal3DGold)"
                stroke="#9A6B16"
                strokeWidth="1"
              />
            </>
          ) : (
            <path
              d={Array.from({ length: 48 }).map((_, i) => {
                const angle = (i * 360) / 48;
                const radOuter = 74;
                const radInner = 68;
                const a1 = (angle * Math.PI) / 180;
                const a2 = ((angle + 3.75) * Math.PI) / 180;
                const x1 = 80 + radOuter * Math.cos(a1);
                const y1 = 80 + radOuter * Math.sin(a1);
                const x2 = 80 + radInner * Math.cos(a2);
                const y2 = 80 + radInner * Math.sin(a2);
                return `${i === 0 ? 'M' : 'L'} ${x1} ${y1} L ${x2} ${y2}`;
              }).join(' ') + ' Z'}
              fill="url(#seal3DGold)"
              stroke="#78350F"
              strokeWidth="0.8"
            />
          )}
        </g>

        {/* Inner Beveled Rings (Skipped for custom Wax/Navy Crest which render their own rings above) */}
        {seal.style !== 'wax-red-k' && seal.style !== 'navy-laurel-crest' && (
          <>
            <circle
              cx="80"
              cy="80"
              r={
                seal.style === 'navy-k-medal'
                  ? 58
                  : seal.style === 'crown-laurel' ||
                    seal.style === 'gold-k-medallion' ||
                    seal.style === 'gold-k-ribbon-seal'
                  ? 60
                  : 63
              }
              fill={seal.style === 'navy-k-medal' ? 'url(#navySealRadial)' : 'url(#seal3DGold)'}
              stroke="#FFF4CC"
              strokeWidth={
                seal.style === 'crown-laurel' ||
                seal.style === 'navy-k-medal' ||
                seal.style === 'gold-k-ribbon-seal'
                  ? 1.8
                  : 1.5
              }
              strokeOpacity="0.9"
            />

            {seal.style === 'navy-k-medal' ? (
              <>
                <circle cx="80" cy="80" r="50" fill="none" stroke="url(#edgeGoldFoil)" strokeWidth="2.4" />
                <circle cx="80" cy="80" r="44" fill="#06193B" stroke="#FDE68A" strokeWidth="0.9" strokeOpacity="0.65" />
              </>
            ) : seal.style === 'gold-k-medallion' || seal.style === 'gold-k-ribbon-seal' ? (
              <>
                <circle cx="80" cy="80" r="53" fill="none" stroke="#92400E" strokeWidth="1.2" strokeOpacity="0.65" />
                <circle cx="80" cy="80" r="46" fill="url(#seal3DGold)" stroke="#FFF4CC" strokeWidth="1.4" />
              </>
            ) : seal.style === 'crown-laurel' ? (
              <>
                <circle cx="80" cy="80" r="54" fill="none" stroke="#855814" strokeWidth="1" strokeOpacity="0.55" />
                <circle cx="80" cy="80" r="46" fill="none" stroke="#FFF4CC" strokeWidth="1.2" strokeOpacity="0.7" />
              </>
            ) : (
              <>
                <circle
                  cx="80"
                  cy="80"
                  r="56"
                  fill="none"
                  stroke="#451A03"
                  strokeWidth="1.2"
                  strokeDasharray="2 2"
                />
                <circle
                  cx="80"
                  cy="80"
                  r="52"
                  fill="none"
                  stroke="#FEF3C7"
                  strokeWidth="0.8"
                />
              </>
            )}

            {seal.style !== 'navy-k-medal' &&
              seal.style !== 'gold-k-medallion' &&
              seal.style !== 'gold-k-ribbon-seal' && (
              <circle
                cx="80"
                cy="80"
                r={seal.style === 'crown-laurel' ? 42 : 36}
                fill={seal.style === 'crown-laurel' ? '#926213' : '#451A03'}
                fillOpacity={seal.style === 'crown-laurel' ? 0.25 : 0.28}
                stroke="#92400E"
                strokeWidth="1.2"
              />
            )}
          </>
        )}

        {/* Center Star / Crown + Laurel / K Monogram Insignia */}
        <g transform="translate(80, 80)">
          {seal.style === 'gold-k-ribbon-seal' ? (
            <g>
              {/* Flanking Gold Laurel Wreath Inside Disc */}
              <g fill="#FFF4B8" stroke="#784C0A" strokeWidth="0.45">
                {[-120, -95, -70, -45, -20, 5].map((_, i) => {
                  const px = -29 + Math.sin((i / 5) * Math.PI) * -4;
                  const py = 20 - i * 6.8;
                  return (
                    <ellipse
                      key={`gkr-l-${i}`}
                      cx={px + (i === 0 ? 11 : i === 5 ? 9 : 0)}
                      cy={py}
                      rx="5.2"
                      ry="2.2"
                      transform={`rotate(${-35 + i * 12}, ${px + (i === 0 ? 11 : i === 5 ? 9 : 0)}, ${py})`}
                    />
                  );
                })}
                {[-120, -95, -70, -45, -20, 5].map((_, i) => {
                  const px = 29 + Math.sin((i / 5) * Math.PI) * 4;
                  const py = 20 - i * 6.8;
                  return (
                    <ellipse
                      key={`gkr-r-${i}`}
                      cx={px - (i === 0 ? 11 : i === 5 ? 9 : 0)}
                      cy={py}
                      rx="5.2"
                      ry="2.2"
                      transform={`rotate(${35 - i * 12}, ${px - (i === 0 ? 11 : i === 5 ? 9 : 0)}, ${py})`}
                    />
                  );
                })}
                <path d="M 6 29 Q -28 24 -30 -12" fill="none" stroke="#FFF4B8" strokeWidth="1.3" />
                <path d="M -6 29 Q 28 24 30 -12" fill="none" stroke="#FFF4B8" strokeWidth="1.3" />
              </g>
              {/* 3 Arched Royal Stars Above K */}
              <g fill="#FFF9D6" stroke="#784C0A" strokeWidth="0.4">
                <polygon transform="translate(-11, -24) scale(0.32)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                <polygon transform="translate(0, -28) scale(0.4)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
                <polygon transform="translate(11, -24) scale(0.32)" points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2" />
              </g>
              {/* Sculpted Center White-Gold K Monogram */}
              <path
                d="M -13 -14 L -4 -14 L -4 -1 L 8 -14 L 18 -14 L 5 -1 L 19 16 L 8 16 L -4 1 L -4 16 L -13 16 Z"
                fill="#FFFBEB"
                stroke="#784C0A"
                strokeWidth="0.9"
                strokeLinejoin="round"
              />
            </g>
          ) : seal.style === 'navy-k-medal' || seal.style === 'gold-k-medallion' || seal.style === 'wax-red-k' ? (
            <g>
              {/* Sculpted Gold K Monogram matching reference */}
              <path
                d="M -17 -22 L -6 -22 L -6 -2 L 10 -22 L 22 -22 L 5 -2 L 23 22 L 10 22 L -6 1 L -6 22 L -17 22 Z"
                fill={seal.style === 'gold-k-medallion' ? '#FFFBEB' : 'url(#edgeGoldFoil)'}
                stroke={seal.style === 'wax-red-k' ? '#450A0A' : '#78350F'}
                strokeWidth="1.2"
                strokeLinejoin="round"
              />
            </g>
          ) : seal.style === 'crown-laurel' || seal.style === 'navy-laurel-crest' ? (
            <g>
              {/* Crossed Laurel Wreaths Circling the Crown */}
              <g
                fill={seal.style === 'navy-laurel-crest' ? 'url(#edgeGoldFoil)' : '#FFF4B8'}
                stroke="#784C0A"
                strokeWidth="0.5"
              >
                {/* Left Laurel Spray */}
                {[-125, -100, -75, -50, -25, 0, 25].map((_, i) => {
                  const px = -29 + Math.sin((i / 6) * Math.PI) * -4;
                  const py = 20 - i * 6.5;
                  return (
                    <ellipse
                      key={`cl-l-${i}`}
                      cx={px + (i === 0 ? 12 : i === 6 ? 10 : 0)}
                      cy={py}
                      rx="5.5"
                      ry="2.3"
                      transform={`rotate(${-35 + i * 11}, ${px + (i === 0 ? 12 : i === 6 ? 10 : 0)}, ${py})`}
                    />
                  );
                })}
                {/* Right Laurel Spray */}
                {[-125, -100, -75, -50, -25, 0, 25].map((_, i) => {
                  const px = 29 + Math.sin((i / 6) * Math.PI) * 4;
                  const py = 20 - i * 6.5;
                  return (
                    <ellipse
                      key={`cl-r-${i}`}
                      cx={px - (i === 0 ? 12 : i === 6 ? 10 : 0)}
                      cy={py}
                      rx="5.5"
                      ry="2.3"
                      transform={`rotate(${35 - i * 11}, ${px - (i === 0 ? 12 : i === 6 ? 10 : 0)}, ${py})`}
                    />
                  );
                })}
                {/* Left & Right Stems Crossing at Bottom */}
                <path
                  d="M 6 29 Q -28 24 -30 -16"
                  fill="none"
                  stroke={seal.style === 'navy-laurel-crest' ? '#FDE68A' : '#FFF4B8'}
                  strokeWidth="1.4"
                />
                <path
                  d="M -6 29 Q 28 24 30 -16"
                  fill="none"
                  stroke={seal.style === 'navy-laurel-crest' ? '#FDE68A' : '#FFF4B8'}
                  strokeWidth="1.4"
                />
              </g>

              {/* 3 Arched Royal Stars Above Crown */}
              <g fill="#FFF9D6" stroke="#784C0A" strokeWidth="0.4">
                <polygon
                  transform="translate(-11, -24) scale(0.32)"
                  points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                />
                <polygon
                  transform="translate(0, -28) scale(0.38)"
                  points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                />
                <polygon
                  transform="translate(11, -24) scale(0.32)"
                  points="0,-10 2.9,-3.2 10,-3.2 4.3,1.2 6.5,8.2 0,4 -6.5,8.2 -4.3,1.2 -10,-3.2 -2.9,-3.2"
                />
              </g>

              {/* Center Royal 3-Point Crown */}
              <g transform="translate(0, -2)">
                <path
                  d="M -15 9 L -18 -8 L -8 0 L 0 -13 L 8 0 L 18 -8 L 15 9 Z"
                  fill={seal.style === 'navy-laurel-crest' ? 'url(#edgeGoldFoil)' : '#FFF7C8'}
                  stroke="#784C0A"
                  strokeWidth="0.9"
                />
                <rect
                  x="-15"
                  y="10.5"
                  width="30"
                  height="3.8"
                  rx="1"
                  fill={seal.style === 'navy-laurel-crest' ? 'url(#edgeGoldFoil)' : '#FFF7C8'}
                  stroke="#784C0A"
                  strokeWidth="0.8"
                />
                <circle cx="-18" cy="-10" r="2.2" fill="#FFFBEB" stroke="#784C0A" strokeWidth="0.6" />
                <circle cx="0" cy="-15" r="2.6" fill="#FFFBEB" stroke="#784C0A" strokeWidth="0.6" />
                <circle cx="18" cy="-10" r="2.2" fill="#FFFBEB" stroke="#784C0A" strokeWidth="0.6" />
              </g>
            </g>
          ) : seal.style === 'certified-stamp' ? (
            <text
              textAnchor="middle"
              y="4"
              fill="#FFFBEB"
              fontSize="11"
              fontWeight="800"
              fontFamily="Cinzel, serif"
              letterSpacing="1.5"
            >
              VERIFIED
            </text>
          ) : (
            <g className="filter drop-shadow-xs">
              {/* 8-Point Raised Star */}
              <polygon
                points="0,-16 5,-5 16,-5 7,3 11,14 0,8 -11,14 -7,3 -16,-5 -5,-5"
                fill="#FFFBEB"
                stroke="#78350F"
                strokeWidth="0.75"
              />
              <circle cx="0" cy="0" r="3.5" fill="#B45309" />
            </g>
          )}

          {seal.year &&
            seal.style !== 'crown-laurel' &&
            seal.style !== 'navy-k-medal' &&
            seal.style !== 'navy-laurel-crest' &&
            seal.style !== 'gold-k-medallion' &&
            seal.style !== 'gold-k-ribbon-seal' &&
            seal.style !== 'wax-red-k' && (
              <text
                textAnchor="middle"
                y="22"
                fill="#FFFBEB"
                fontSize="8"
                fontWeight="800"
                fontFamily="Cinzel, serif"
                letterSpacing="1"
              >
                {seal.year}
              </text>
            )}
        </g>

        {/* Arc Text: Top */}
        {seal.style !== 'navy-k-medal' &&
          seal.style !== 'navy-laurel-crest' &&
          seal.style !== 'gold-k-medallion' &&
          seal.style !== 'gold-k-ribbon-seal' &&
          seal.style !== 'wax-red-k' &&
          (seal.style !== 'crown-laurel' || seal.primaryText) && (
            <text
              fill="#FFFBEB"
              fontSize="6.2"
              fontWeight="800"
              fontFamily="Cinzel, serif"
              letterSpacing="1.2"
              className="drop-shadow-xs"
            >
              <textPath href="#sealTextArcTop" startOffset="50%" textAnchor="middle">
                {seal.primaryText || 'OFFICIAL EXCELLENCE SEAL'}
              </textPath>
            </text>
          )}

        {/* Arc Text: Bottom */}
        {seal.style !== 'navy-k-medal' &&
          seal.style !== 'navy-laurel-crest' &&
          seal.style !== 'gold-k-medallion' &&
          seal.style !== 'gold-k-ribbon-seal' &&
          seal.style !== 'wax-red-k' &&
          (seal.style !== 'crown-laurel' || seal.secondaryText) && (
            <text
              fill="#FDE68A"
              fontSize="5.4"
              fontWeight="700"
              fontFamily="Cinzel, serif"
              letterSpacing="1"
              className="drop-shadow-xs"
            >
              <textPath href="#sealTextArcBottom" startOffset="50%" textAnchor="middle">
                {seal.secondaryText || 'AUTHENTIC MERIT'}
              </textPath>
            </text>
          )}

        {/* Specular Hemisphere Glass/Metallic Lens Curve */}
        <path
          d="M 32 60 A 56 56 0 0 1 128 60 A 56 36 0 0 0 32 60 Z"
          fill="url(#specularGleam)"
          pointerEvents="none"
        />
      </svg>
    </div>
  );
};
