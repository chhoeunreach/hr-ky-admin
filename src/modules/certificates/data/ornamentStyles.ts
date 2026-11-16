import {
  BannerStyleOption,
  DividerOrnamentStyle,
  LogoOrnamentStyle,
  SealStyleOption,
  WatermarkStyleOption,
} from '../types/certificate';

export interface LogoOrnamentMeta {
  id: LogoOrnamentStyle;
  label: string;
  khmerLabel: string;
}

export interface DividerOrnamentMeta {
  id: DividerOrnamentStyle;
  label: string;
  khmerLabel: string;
}

export interface BannerStyleMeta {
  id: BannerStyleOption;
  label: string;
  khmerLabel: string;
  previewBg: string;
}

export interface WatermarkStyleMeta {
  id: WatermarkStyleOption;
  label: string;
  khmerLabel: string;
}

export interface SealStyleMeta {
  id: SealStyleOption;
  label: string;
  khmerLabel: string;
}

export const LOGO_ORNAMENT_STYLES: LogoOrnamentMeta[] = [
  { id: 'classic-laurel', label: 'Classic Laurel', khmerLabel: 'មែកឡូរ៉ែលមាស' },
  { id: 'laurel-crossed-wreath', label: 'Crossed Laurel', khmerLabel: 'មែកឡូរ៉ែលខ្វែងមាស' },
  { id: 'crown-laurel-wreath', label: 'Crown & Laurel', khmerLabel: 'មែកឡូរ៉ែល & មកុដ' },
  { id: 'three-star-laurel', label: '3-Star Laurel', khmerLabel: 'មែកឡូរ៉ែល & ផ្កាយ៣' },
  { id: 'khmer-kbach', label: 'Khmer Kbach', khmerLabel: 'ក្បាច់ភ្ញីទេសខ្មែរ' },
  { id: 'rice-wheat-khmer', label: 'Khmer Paddy Wheat', khmerLabel: 'កួរស្រូវមាសខ្មែរ' },
  { id: 'royal-wings', label: 'Imperial Wings', khmerLabel: 'ក្បាច់ស្លាបឥន្ទ្រី' },
  { id: 'floral-baroque', label: 'Baroque Scrolls', khmerLabel: 'ក្បាច់ផ្កាប្រណីត' },
  { id: 'star-halo', label: 'Sunburst & Stars', khmerLabel: 'ពន្លឺរស្មីផ្កាយ' },
  { id: 'diamond-crest', label: 'Geometric Crest', khmerLabel: 'ស៊ុមធរណីមាត្រ' },
  { id: 'none', label: 'None (Clean)', khmerLabel: 'គ្មានក្បាច់' },
];

export const DIVIDER_ORNAMENT_STYLES: DividerOrnamentMeta[] = [
  { id: 'baroque-fleur', label: 'Baroque Fleur', khmerLabel: 'ក្បាច់ភ្ញីវល្លិ៍' },
  { id: 'acanthus-gold-line', label: 'Acanthus Scroll Line', khmerLabel: 'ក្បាច់ភ្ញីមាសបន្ទាត់វែង' },
  { id: 'beaded-diamond-bar', label: 'Beaded Diamond Bar', khmerLabel: 'បន្ទាត់ពេជ្រ & គ្រាប់មាស' },
  { id: 'khmer-lotus', label: 'Khmer Lotus', khmerLabel: 'ក្បាច់ផ្កាឈូកខ្មែរ' },
  { id: 'khmer-angkor-crest', label: 'Khmer Angkor Kbach', khmerLabel: 'ក្បាច់ភ្ញីទេសអង្គរ' },
  { id: 'royal-crown-line', label: 'Royal Crown', khmerLabel: 'ក្បាច់មកុដរាជ្យ' },
  { id: 'royal-plume', label: 'Royal Plume', khmerLabel: 'ក្បាច់ស្លាបហង្ស' },
  { id: 'diamond-stars', label: 'Diamond & Stars', khmerLabel: 'ផ្កាយបី & ពេជ្រ' },
  { id: 'double-scallop', label: 'Leaf Garland', khmerLabel: 'ក្បាច់ស្លឹកឈើ' },
  { id: 'infinity-filigree', label: 'Infinity Filigree', khmerLabel: 'ក្បាច់ខ្សែរមួលប្រណីត' },
  { id: 'minimal-gold-bar', label: 'Executive Line', khmerLabel: 'បន្ទាត់មាសសាមញ្ញ' },
  { id: 'custom', label: 'Custom Upload', khmerLabel: 'បញ្ចូលរូបក្បាច់ផ្ទាល់ខ្លួន' },
  { id: 'none', label: 'None', khmerLabel: 'គ្មាន' },
];

export const BANNER_RIBBON_STYLES: BannerStyleMeta[] = [
  {
    id: 'gold-swallowtail',
    label: 'Gold Swallowtail',
    khmerLabel: 'បដាមាសប្រណីត',
    previewBg: 'linear-gradient(90deg, #B88116, #F0CB62, #B88116)',
  },
  {
    id: 'navy-gold-ribbon',
    label: 'Navy Folded Ribbon',
    khmerLabel: 'បូខៀវក្រមៅគែមមាស',
    previewBg: 'linear-gradient(90deg, #041536, #0C2F6B, #041536)',
  },
  {
    id: 'crimson-gold-ribbon',
    label: 'Crimson Folded Ribbon',
    khmerLabel: 'បូក្រហមគែមមាស',
    previewBg: 'linear-gradient(90deg, #7F1D1D, #DC2626, #7F1D1D)',
  },
  {
    id: 'royal-blue-arch',
    label: 'Royal Blue Arch',
    khmerLabel: 'បូខៀវរាជ្យកោង',
    previewBg: 'linear-gradient(90deg, #1E3A8A, #2563EB, #1E3A8A)',
  },
];

export const WATERMARK_STYLES: WatermarkStyleMeta[] = [
  { id: 'laurel', label: 'Side Laurels', khmerLabel: 'មែកឡូរ៉ែលសងខាង' },
  { id: 'wave-mesh', label: 'Corner Wave Mesh', khmerLabel: 'រលកសំណាញ់ជ្រុង' },
  { id: 'guilloche-rosette', label: 'Guilloche Rosette', khmerLabel: 'ក្បាច់ឌីផ្លូម៉ាកណ្តាល' },
  { id: 'rosette-lace', label: 'Lace Spirograph', khmerLabel: 'ផ្កាចក្រធរណីមាត្រ' },
  { id: 'diagonal-brand', label: 'Diagonal Brand Grid', khmerLabel: 'អក្សរ & Logo បញ្ឆិត' },
  { id: 'crest', label: 'Heritage Crest', khmerLabel: 'សញ្ញាបុរាណកណ្តាល' },
];

export const SEAL_STYLE_CATALOG: SealStyleMeta[] = [
  { id: 'crown-laurel', label: 'Crown & Laurel', khmerLabel: 'ត្រាមាសមកុដ' },
  { id: 'navy-k-medal', label: 'Navy K Medal', khmerLabel: 'មេដាយខៀវអក្សរ K' },
  { id: 'navy-laurel-crest', label: 'Navy Royal Seal', khmerLabel: 'ត្រាខៀវមកុដ & ផ្កាយ' },
  { id: 'gold-k-medallion', label: 'Gold K Medallion', khmerLabel: 'ត្រាមាសអក្សរ K' },
  { id: 'gold-k-ribbon-seal', label: 'Gold K Ribbon Medal', khmerLabel: 'ត្រាមាសអក្សរ K & បូខៀវ' },
  { id: 'wax-red-k', label: 'Red Wax Seal (K)', khmerLabel: 'ត្រាក្រមួនក្រហម (K)' },
  { id: 'embossed-gold', label: 'Gold Rosette', khmerLabel: 'ត្រាមាសបុរាណ' },
  { id: 'royal-ribbon', label: 'Ribbon Medal', khmerLabel: 'មេដាយបូ' },
  { id: 'laurel-wreath', label: 'Laurel Medal', khmerLabel: 'មេដាយស្លឹកមាស' },
  { id: 'modern-star', label: 'Modern Star', khmerLabel: 'ត្រាផ្កាយទំនើប' },
  { id: 'certified-stamp', label: 'Stamp Mark', khmerLabel: 'ត្រាបញ្ជាក់' },
];
