import React from 'react';
import { FieldTypographyKey, FieldTypographyStyle, Signatory } from '../types/certificate';
import { resolveCssFontFamily } from '../utils/typography';

interface SignaturesProps {
  signatories: Signatory[];
  primaryColor: string;
  accentColor: string;
  signatureInkColor?: string;
  isDarkTheme?: boolean;
  compact?: boolean;
  isPortrait?: boolean;
  fieldStyles?: Partial<Record<FieldTypographyKey, FieldTypographyStyle>>;
  onEditSignature?: (index: number) => void;
}

const applyOverride = (
  base: React.CSSProperties,
  ov?: FieldTypographyStyle
): React.CSSProperties => {
  if (!ov) return base;
  return {
    ...base,
    ...(ov.fontFamily ? { fontFamily: resolveCssFontFamily(ov.fontFamily) } : {}),
    ...(typeof ov.fontSize === 'number' ? { fontSize: `${ov.fontSize}px` } : {}),
    ...(ov.fontWeight ? { fontWeight: ov.fontWeight } : {}),
    ...(typeof ov.italic === 'boolean' ? { fontStyle: ov.italic ? 'italic' : 'normal' } : {}),
    ...(ov.color ? { color: ov.color } : {}),
    ...(typeof ov.letterSpacing === 'number' ? { letterSpacing: `${ov.letterSpacing}px` } : {}),
    ...(typeof ov.uppercase === 'boolean' ? { textTransform: ov.uppercase ? 'uppercase' : 'none' } : {}),
  };
};

export const CertificateSignatures: React.FC<SignaturesProps> = ({
  signatories,
  primaryColor,
  accentColor,
  signatureInkColor,
  isDarkTheme = false,
  compact = false,
  isPortrait = false,
  fieldStyles,
  onEditSignature,
}) => {
  const indexedSignatories = (signatories || [])
    .map((sig, originalIndex) => ({ sig, originalIndex }))
    .filter(({ sig }) => !sig.hidden);

  if (indexedSignatories.length === 0) return null;

  const isCrowded = isPortrait || indexedSignatories.length >= 3;
  const inkColor = signatureInkColor || primaryColor;

  return (
    <div
      className={`flex items-end justify-center w-full ${
        compact ? 'pt-1' : 'pt-3'
      } ${isCrowded ? 'gap-3 flex-wrap' : 'gap-6'}`}
    >
      {indexedSignatories.map(({ sig, originalIndex: idx }) => {
        let fontClass = compact ? 'font-signature text-2xl' : 'font-signature text-3xl';
        if (sig.signatureStyle === 'great-vibes') {
          fontClass = compact ? 'font-script text-2xl' : 'font-script text-3xl';
        }
        if (sig.signatureStyle === 'font-signature') {
          fontClass = compact ? 'font-signature text-xl' : 'font-signature text-2xl';
        }

        return (
          <div
            key={sig.id || idx}
            onClick={() => onEditSignature?.(idx)}
            className={`flex flex-col items-center text-center cursor-pointer group ${
              compact ? 'px-1.5 py-0.5' : 'px-2 py-1'
            } transition-all rounded hover:bg-black/5 dark:hover:bg-white/5`}
            title="Click to customize signature style or draw your own"
          >
            {/* Signature rendering */}
            <div
              className={`${
                compact ? 'h-9 mb-0.5' : 'h-12 mb-1'
              } flex items-center justify-center relative w-full`}
            >
              {sig.customSignatureData ? (
                <img
                  src={sig.customSignatureData}
                  alt={sig.name}
                  className={`${compact ? 'max-h-9' : 'max-h-12'} w-auto object-contain filter drop-shadow-sm`}
                />
              ) : (
                <span
                  className={`${fontClass} select-none transition-transform group-hover:scale-105 leading-none`}
                  style={{ color: inkColor }}
                >
                  {sig.name.replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s*/, '')}
                </span>
              )}
            </div>

            {/* Engraved Hairline signature divider with center gold diamond */}
            <div
              className={`${
                isCrowded || compact ? 'w-36' : 'w-44'
              } flex items-center justify-center gap-1.5 ${compact ? 'mb-1' : 'mb-1.5'}`}
            >
              <div
                className="h-[1px] flex-1 opacity-60"
                style={{ backgroundColor: isDarkTheme ? '#94A3B8' : inkColor }}
              />
              <div
                className="w-1.5 h-1.5 rotate-45 shrink-0 opacity-80"
                style={{ backgroundColor: accentColor }}
              />
              <div
                className="h-[1px] flex-1 opacity-60"
                style={{ backgroundColor: isDarkTheme ? '#94A3B8' : inkColor }}
              />
            </div>

            {/* Signatory text */}
            <div className="flex flex-col items-center">
              {sig.khmerName && (
                <div
                  className={`${
                    compact ? 'text-[11px]' : 'text-xs'
                  } font-bold font-khmer-sans leading-tight text-slate-900 dark:text-slate-100`}
                  style={applyOverride({}, fieldStyles?.signatoryName)}
                >
                  {sig.khmerName}
                </div>
              )}
              <div
                className={`${
                  compact ? 'text-[10.5px]' : 'text-[11px]'
                } font-semibold tracking-wider uppercase font-cinzel leading-tight`}
                style={applyOverride(
                  { color: isDarkTheme ? '#F8FAFC' : '#1E293B' },
                  fieldStyles?.signatoryName
                )}
              >
                {sig.name}
              </div>
            </div>

            <div className="flex flex-col items-center mt-0.5">
              {sig.khmerRole && (
                <div
                  className={`${
                    compact ? 'text-[9.5px]' : 'text-[10px]'
                  } font-medium font-khmer-sans text-slate-600 dark:text-slate-400 leading-tight`}
                  style={applyOverride({}, fieldStyles?.signatoryRole)}
                >
                  {sig.khmerRole}
                </div>
              )}
              <div
                className={`${
                  compact ? 'text-[9.5px]' : 'text-[10px]'
                } font-medium opacity-75 leading-tight`}
                style={applyOverride(
                  { color: isDarkTheme ? '#94A3B8' : '#475569' },
                  fieldStyles?.signatoryRole
                )}
              >
                {sig.role}
              </div>
            </div>
            {sig.organization && (
              <div
                className={`${
                  compact ? 'text-[9px]' : 'text-[10px]'
                } opacity-60 tracking-tight leading-tight`}
                style={applyOverride(
                  { color: isDarkTheme ? '#64748B' : '#64748B' },
                  fieldStyles?.signatoryRole
                )}
              >
                {sig.organization}
              </div>
            )}
          </div>
        );
      })}
    </div>
  );
};
