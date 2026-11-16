import { LayoutTemplateOption } from '../types/certificate';

export interface LayoutTemplateMeta {
  id: LayoutTemplateOption;
  versionTag: 'V1' | 'V2' | 'V3' | 'V4' | 'V5' | 'V6';
  label: string;
  khmerLabel: string;
  shortDesc: string;
  khmerShortDesc: string;
  defaultPresetId: string;
}

export const LAYOUT_TEMPLATE_VERSIONS: LayoutTemplateMeta[] = [
  {
    id: 'executive-verify',
    versionTag: 'V1',
    label: 'Verify Classic',
    khmerLabel: 'ទម្រង់ V1 · Verify QR',
    shortDesc: 'Portrait ID card, Date footer, Dual Signatory & Verify QR',
    khmerShortDesc: 'រូបបុគ្គលិកបញ្ឈរ · កាលបរិច្ឆេទ · ហត្ថលេខា ២ · QR Verify',
    defaultPresetId: 'kneayerng-verify-executive',
  },
  {
    id: 'executive-verify-v2',
    versionTag: 'V2',
    label: 'Verify + Cert ID',
    khmerLabel: 'ទម្រង់ V2 · Verify + ID',
    shortDesc: 'Circle ID photo, Stacked Date & Certificate ID + Gold Bar & QR',
    khmerShortDesc: 'រូបបុគ្គលិករង្វង់ · កាលបរិច្ឆេទ & លេខសម្គាល់ · QR Verify',
    defaultPresetId: 'kneayerng-verify-v2-idcard',
  },
  {
    id: 'executive-navy-ribbon',
    versionTag: 'V3',
    label: 'Navy Ribbon',
    khmerLabel: 'ទម្រង់ V3 · បូប្រណិត',
    shortDesc: 'Sweeping Navy-Gold corner ribbons, Org Sub-banner & Flexible HR',
    khmerShortDesc: 'ស៊ុមបូខៀវ-មាស · ឈ្មោះស្ថាប័ន · ហត្ថលេខា HR ឬ ទោល',
    defaultPresetId: 'kneayerng-appreciation-leader-hr-profile',
  },
  {
    id: 'classic',
    versionTag: 'V4',
    label: 'Royal Heritage',
    khmerLabel: 'ទម្រង់ V4 · បុរាណប្រណីត',
    shortDesc: 'Guilloche crest watermark, Decree header, CPD plaque & Metrics',
    khmerShortDesc: 'ក្បាច់បុរាណ · សាររដ្ឋបាល · តារាងម៉ោង HR & ពិន្ទុ',
    defaultPresetId: 'knea-yoeng-best-staff',
  },
  {
    id: 'executive-medal-ribbon',
    versionTag: 'V5',
    label: 'Medal Ribbon',
    khmerLabel: 'ទម្រង់ V5 · មេដាយប្រណិត',
    shortDesc: 'Left hanging Navy-Gold medal sash, Photo ID, Date + Cert ID & QR',
    khmerShortDesc: 'បូមេដាយខៀវ-មាសផ្នែកឆ្វេង · រូបបុគ្គលិក · លេខសម្គាល់ · QR',
    defaultPresetId: 'kneayerng-v5-medal-appreciation',
  },
  {
    id: 'executive-imperial-sash',
    versionTag: 'V6',
    label: 'Imperial Sash',
    khmerLabel: 'ទម្រង់ V6 · រលកមាស-បូខៀវ',
    shortDesc: 'Gold-sash medal, K crest, storefront watermark, laurels & QR',
    khmerShortDesc: 'មេដាយបូមាសផ្នែកឆ្វេង · រូបបុគ្គលិក · ហាងគ្នាយើងស្រាល · QR',
    defaultPresetId: 'kneayerng-v6-imperial-sash',
  },
];

export function getLayoutMeta(layout?: LayoutTemplateOption): LayoutTemplateMeta {
  return (
    LAYOUT_TEMPLATE_VERSIONS.find((lt) => lt.id === layout) ||
    LAYOUT_TEMPLATE_VERSIONS[3]
  );
}
