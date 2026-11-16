import React, { useState } from 'react';
import { Type, RotateCcw, ChevronDown } from 'lucide-react';
import { FieldFontFamily, FieldTypographyKey, FieldTypographyStyle } from '../types/certificate';
import { FIELD_TYPOGRAPHY_CATALOG, FONT_FAMILY_OPTIONS } from '../utils/typography';

interface FieldTypographyControlProps {
  fieldKey: FieldTypographyKey;
  value?: FieldTypographyStyle;
  onChange: (updated: FieldTypographyStyle | undefined) => void;
  compact?: boolean;
  defaultOpen?: boolean;
  uiLang?: 'en' | 'km';
}

export const FieldTypographyControl: React.FC<FieldTypographyControlProps> = ({
  fieldKey,
  value,
  onChange,
  compact = true,
  defaultOpen = false,
  uiLang = 'en',
}) => {
  const [open, setOpen] = useState(defaultOpen);
  const meta = FIELD_TYPOGRAPHY_CATALOG.find((m) => m.key === fieldKey) || {
    key: fieldKey,
    label: fieldKey,
    khmerLabel: fieldKey,
    defaultSize: 16,
    minSize: 8,
    maxSize: 64,
  };

  const currentSize = value?.fontSize ?? meta.defaultSize;
  const currentFontFamily = value?.fontFamily ?? meta.defaultFontFamily;
  const currentFontMeta = FONT_FAMILY_OPTIONS.find((f) => f.id === currentFontFamily);

  const hasCustom = Boolean(
    value &&
      (value.fontFamily ||
        typeof value.fontSize === 'number' ||
        value.fontWeight ||
        typeof value.italic === 'boolean' ||
        value.color ||
        typeof value.letterSpacing === 'number' ||
        typeof value.uppercase === 'boolean')
  );

  const patch = (partial: Partial<FieldTypographyStyle>) => {
    onChange({
      ...(value || {}),
      ...partial,
    });
  };

  if (compact && !open) {
    return (
      <button
        type="button"
        onClick={() => setOpen(true)}
        className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold transition-colors cursor-pointer ${
          hasCustom
            ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-amber-700 border border-slate-200 dark:border-slate-700'
        }`}
        title={uiLang === 'km' ? 'កែពុម្ពអក្សរ និងទំហំ' : 'Customize font & size for this field'}
      >
        <Type className="w-2.5 h-2.5" />
        <span className="font-khmer-sans">
          {currentFontMeta
            ? (uiLang === 'km' ? currentFontMeta.khmerLabel : currentFontMeta.label.split(' ')[0])
            : 'Font'}
        </span>
        <span>·</span>
        <span className="font-mono">{currentSize}px</span>
        <ChevronDown className="w-2.5 h-2.5 opacity-70" />
      </button>
    );
  }

  return (
    <div className="p-2.5 rounded-lg bg-amber-50/70 dark:bg-slate-800/90 border border-amber-200/80 dark:border-slate-700 space-y-2 mt-1 mb-1.5">
      <div className="flex items-center justify-between gap-2">
        <span className="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 flex items-center gap-1">
          <Type className="w-3 h-3" />
          <span>{uiLang === 'km' ? meta.khmerLabel : meta.label}</span>
        </span>
        <div className="flex items-center gap-1.5">
          {hasCustom && (
            <button
              type="button"
              onClick={() => onChange(undefined)}
              className="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 hover:text-rose-600 rounded cursor-pointer"
              title="Reset to template default"
            >
              <RotateCcw className="w-2.5 h-2.5" />
              <span>Reset</span>
            </button>
          )}
          {compact && (
            <button
              type="button"
              onClick={() => setOpen(false)}
              className="px-1.5 py-0.5 text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 cursor-pointer"
            >
              Done
            </button>
          )}
        </div>
      </div>

      {/* Font Family Select */}
      <div>
        <select
          value={value?.fontFamily || ''}
          onChange={(e) =>
            patch({
              fontFamily: (e.target.value || undefined) as FieldFontFamily | undefined,
            })
          }
          className="w-full px-2 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md text-slate-900 dark:text-slate-100 cursor-pointer"
          style={{
            fontFamily: currentFontFamily
              ? FONT_FAMILY_OPTIONS.find((f) => f.id === currentFontFamily)?.cssFamily
              : undefined,
          }}
        >
          <option value="">
            {uiLang === 'km'
              ? `ពុម្ពអក្សរលំនាំដើម (${currentFontMeta?.khmerLabel || 'លំនាំដើម'})`
              : `Template Default (${currentFontMeta?.label.split(' ')[0] || 'Default'})`}
          </option>
          <optgroup label="Classical & Editorial Serif">
            {FONT_FAMILY_OPTIONS.filter((f) => f.group === 'serif').map((f) => (
              <option key={f.id} value={f.id}>
                {f.label}
              </option>
            ))}
          </optgroup>
          <optgroup label="Modern Sans & Mono">
            {FONT_FAMILY_OPTIONS.filter((f) => f.group === 'sans').map((f) => (
              <option key={f.id} value={f.id}>
                {f.label}
              </option>
            ))}
          </optgroup>
          <optgroup label="Calligraphy & Script">
            {FONT_FAMILY_OPTIONS.filter((f) => f.group === 'script').map((f) => (
              <option key={f.id} value={f.id}>
                {f.label}
              </option>
            ))}
          </optgroup>
          <optgroup label="Khmer Fonts (ខ្មែរ)">
            {FONT_FAMILY_OPTIONS.filter((f) => f.group === 'khmer').map((f) => (
              <option key={f.id} value={f.id}>
                {f.label}
              </option>
            ))}
          </optgroup>
        </select>
      </div>

      {/* Font Size Slider + Direct Stepper */}
      <div className="flex items-center gap-2">
        <span className="text-[10px] font-semibold text-slate-500 w-8">Size</span>
        <button
          type="button"
          onClick={() => patch({ fontSize: Math.max(meta.minSize, currentSize - 1) })}
          className="w-6 h-6 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-bold text-slate-700 dark:text-slate-200 cursor-pointer"
        >
          -
        </button>
        <input
          type="range"
          min={meta.minSize}
          max={meta.maxSize}
          value={currentSize}
          onChange={(e) => patch({ fontSize: parseInt(e.target.value, 10) })}
          className="flex-1 accent-amber-600 cursor-pointer"
        />
        <button
          type="button"
          onClick={() => patch({ fontSize: Math.min(meta.maxSize, currentSize + 1) })}
          className="w-6 h-6 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-bold text-slate-700 dark:text-slate-200 cursor-pointer"
        >
          +
        </button>
        <span className="font-mono text-[11px] font-bold text-amber-700 dark:text-amber-300 w-10 text-right tabular-nums">
          {currentSize}px
        </span>
      </div>

      {/* Style Toggles: Weight, Italic, Uppercase, Letter Spacing, Custom Color */}
      <div className="flex items-center justify-between gap-1.5 pt-0.5 flex-wrap">
        <div className="flex items-center gap-1">
          <button
            type="button"
            onClick={() =>
              patch({ fontWeight: value?.fontWeight === 'bold' ? 'normal' : 'bold' })
            }
            className={`px-2 py-1 text-[10px] font-bold rounded border cursor-pointer ${
              value?.fontWeight === 'bold' || value?.fontWeight === 'extrabold'
                ? 'bg-amber-600 text-white border-amber-600'
                : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'
            }`}
            title="Toggle Bold"
          >
            B
          </button>
          <button
            type="button"
            onClick={() => patch({ italic: !value?.italic })}
            className={`px-2 py-1 text-[10px] italic font-serif rounded border cursor-pointer ${
              value?.italic
                ? 'bg-amber-600 text-white border-amber-600'
                : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'
            }`}
            title="Toggle Italic"
          >
            I
          </button>
          <button
            type="button"
            onClick={() => patch({ uppercase: !value?.uppercase })}
            className={`px-1.5 py-1 text-[10px] font-semibold rounded border cursor-pointer ${
              value?.uppercase
                ? 'bg-amber-600 text-white border-amber-600'
                : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'
            }`}
            title="Toggle UPPERCASE"
          >
            AA
          </button>
        </div>

        {/* Letter Spacing mini control */}
        <div className="flex items-center gap-1 text-[10px] text-slate-500">
          <span>Space</span>
          <input
            type="number"
            min={-2}
            max={12}
            step={0.5}
            value={value?.letterSpacing ?? 0}
            onChange={(e) => patch({ letterSpacing: parseFloat(e.target.value) || 0 })}
            className="w-12 px-1 py-0.5 text-[10px] font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-center"
          />
        </div>

        {/* Field Ink Color Override */}
        <div className="flex items-center gap-1">
          <input
            type="color"
            value={value?.color || '#0A224E'}
            onChange={(e) => patch({ color: e.target.value })}
            className="w-6 h-6 rounded border border-slate-300 cursor-pointer"
            title="Custom Ink Color for this field"
          />
          {value?.color && (
            <button
              type="button"
              onClick={() => patch({ color: undefined })}
              className="text-[9.5px] text-slate-500 hover:text-rose-600 cursor-pointer"
              title="Clear field color override"
            >
              ×
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
