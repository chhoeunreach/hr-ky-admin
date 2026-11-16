import React from 'react';
import {
  CertificateData,
  FieldFontFamily,
  FieldTypographyKey,
  FieldTypographyStyle,
} from '../types/certificate';

export interface FontOptionMeta {
  id: FieldFontFamily;
  label: string;
  khmerLabel: string;
  cssFamily: string;
  group: 'serif' | 'sans' | 'script' | 'khmer';
}

export const FONT_FAMILY_OPTIONS: FontOptionMeta[] = [
  { id: 'playfair', label: 'Playfair Display (Royal Serif)', khmerLabel: 'Playfair Serif', cssFamily: "'Playfair Display', serif", group: 'serif' },
  { id: 'cinzel', label: 'Cinzel (Monumental Capitals)', khmerLabel: 'Cinzel Capitals', cssFamily: "'Cinzel', serif", group: 'serif' },
  { id: 'cormorant', label: 'Cormorant Garamond (Editorial)', khmerLabel: 'Cormorant Italic/Serif', cssFamily: "'Cormorant Garamond', serif", group: 'serif' },
  { id: 'montserrat', label: 'Montserrat (Bold Geometric)', khmerLabel: 'Montserrat Sans', cssFamily: "'Montserrat', sans-serif", group: 'sans' },
  { id: 'sans', label: 'Plus Jakarta Sans (Modern Clean)', khmerLabel: 'Jakarta Clean', cssFamily: "'Plus Jakarta Sans', sans-serif", group: 'sans' },
  { id: 'mono', label: 'JetBrains Mono (Credential Code)', khmerLabel: 'Mono Code', cssFamily: "'JetBrains Mono', monospace", group: 'sans' },
  { id: 'alex-brush', label: 'Alex Brush (Calligraphy)', khmerLabel: 'Alex Brush Script', cssFamily: "'Alex Brush', cursive", group: 'script' },
  { id: 'great-vibes', label: 'Great Vibes (Formal Script)', khmerLabel: 'Great Vibes Script', cssFamily: "'Great Vibes', cursive", group: 'script' },
  { id: 'khmer-moul', label: 'Khmer Moul (រាជ្យមូល)', khmerLabel: 'អក្សរមូល (Moul)', cssFamily: "'Moul', cursive, serif", group: 'khmer' },
  { id: 'khmer-sans', label: 'Kantumruy Pro (កន្ទុមរុយ)', khmerLabel: 'កន្ទុមរុយ (Kantumruy Pro)', cssFamily: "'Kantumruy Pro', sans-serif", group: 'khmer' },
  { id: 'khmer-battambang', label: 'Khmer Battambang (បាត់ដំបង)', khmerLabel: 'បាត់ដំបង (Battambang)', cssFamily: "'Battambang', serif", group: 'khmer' },
];

export interface FieldTypographyMeta {
  key: FieldTypographyKey;
  label: string;
  khmerLabel: string;
  defaultSize: number;
  minSize: number;
  maxSize: number;
}

export const FIELD_TYPOGRAPHY_CATALOG: FieldTypographyMeta[] = [
  { key: 'issuerName', label: 'Organization Name', khmerLabel: 'ឈ្មោះស្ថាប័ន / ហាង', defaultSize: 21, minSize: 12, maxSize: 36 },
  { key: 'issuerDepartment', label: 'Department / Sub-Brand', khmerLabel: 'ផ្នែក / អនុស្ថាប័ន', defaultSize: 11, minSize: 8, maxSize: 24 },
  { key: 'authorityPreamble', label: 'Authority Preamble', khmerLabel: 'សេចក្តីប្រកាសស្ថាប័ន', defaultSize: 10, minSize: 8, maxSize: 20 },
  { key: 'khmerTitle', label: 'Khmer Certificate Title', khmerLabel: 'ចំណងជើងខ្មែរ', defaultSize: 24, minSize: 16, maxSize: 44 },
  { key: 'certificateTitle', label: 'Main Certificate Title', khmerLabel: 'ចំណងជើងវិញ្ញាបនបត្រ', defaultSize: 38, minSize: 18, maxSize: 56 },
  { key: 'bannerText', label: 'Gold Banner Ribbon Text', khmerLabel: 'ពាក្យលើបដាមាស', defaultSize: 14, minSize: 9, maxSize: 24 },
  { key: 'subtitle', label: 'Subtitle / Tagline', khmerLabel: 'អនុចំណងជើង', defaultSize: 13, minSize: 9, maxSize: 26 },
  { key: 'khmerRecipientName', label: 'Recipient Name (Khmer)', khmerLabel: 'ឈ្មោះអ្នកទទួល (ខ្មែរ)', defaultSize: 30, minSize: 18, maxSize: 54 },
  { key: 'recipientName', label: 'Recipient Name', khmerLabel: 'ឈ្មោះអ្នកទទួល', defaultSize: 50, minSize: 22, maxSize: 72 },
  { key: 'organizationSubBanner', label: 'Sub-Banner Below Name', khmerLabel: 'ឈ្មោះសាខាក្រោមឈ្មោះ', defaultSize: 14, minSize: 9, maxSize: 28 },
  { key: 'courseOrAchievementTitle', label: 'Course / Award Subject', khmerLabel: 'ប្រធានបទ / សមិទ្ធផល', defaultSize: 16, minSize: 11, maxSize: 30 },
  { key: 'citation', label: 'Main Citation Body', khmerLabel: 'អត្ថន័យសរសើរគោល', defaultSize: 16, minSize: 10, maxSize: 26 },
  { key: 'citationClosing', label: 'Closing Congratulations', khmerLabel: 'ប្រយោគបញ្ចប់ជូនពរ', defaultSize: 16, minSize: 10, maxSize: 26 },
  { key: 'khmerCitation', label: 'Khmer Citation Body', khmerLabel: 'អត្ថន័យសរសើរខ្មែរ', defaultSize: 13, minSize: 10, maxSize: 24 },
  { key: 'issueDate', label: 'Date Text & Label', khmerLabel: 'កាលបរិច្ឆេទ', defaultSize: 13, minSize: 9, maxSize: 22 },
  { key: 'signatoryName', label: 'Signatory Name', khmerLabel: 'ឈ្មោះអ្នកចុះហត្ថលេខា', defaultSize: 14, minSize: 10, maxSize: 24 },
  { key: 'signatoryRole', label: 'Signatory Role & Tags', khmerLabel: 'តួនាទីអ្នកចុះហត្ថលេខា', defaultSize: 11, minSize: 8, maxSize: 20 },
];

export function resolveCssFontFamily(fontFamily?: FieldFontFamily): string | undefined {
  if (!fontFamily) return undefined;
  return FONT_FAMILY_OPTIONS.find((f) => f.id === fontFamily)?.cssFamily;
}

const FONT_WEIGHT_MAP: Record<NonNullable<FieldTypographyStyle['fontWeight']>, number> = {
  normal: 400,
  medium: 500,
  semibold: 600,
  bold: 700,
  extrabold: 800,
};

export function getFieldInlineStyle(
  data: CertificateData,
  key: FieldTypographyKey,
  baseStyle?: React.CSSProperties
): React.CSSProperties {
  const override = data.styling.fieldStyles?.[key];
  if (!override) return baseStyle || {};

  const resolved: React.CSSProperties = { ...(baseStyle || {}) };
  if (override.fontFamily) {
    resolved.fontFamily = resolveCssFontFamily(override.fontFamily);
  }
  if (typeof override.fontSize === 'number') {
    resolved.fontSize = `${override.fontSize}px`;
  }
  if (override.fontWeight) {
    resolved.fontWeight = FONT_WEIGHT_MAP[override.fontWeight];
  }
  if (typeof override.italic === 'boolean') {
    resolved.fontStyle = override.italic ? 'italic' : 'normal';
  }
  if (override.color) {
    resolved.color = override.color;
  }
  if (typeof override.letterSpacing === 'number') {
    resolved.letterSpacing = `${override.letterSpacing}px`;
  }
  if (typeof override.uppercase === 'boolean') {
    resolved.textTransform = override.uppercase ? 'uppercase' : 'none';
  }

  return resolved;
}
