// Public integration entry point for merging into hr-ky-admin or existing React/Vite/Next.js projects
export { CertificateStudio } from './CertificateStudio';
export type { CertificateStudioProps } from './CertificateStudio';

export { CertificateRenderer } from './components/CertificateRenderer';
export { CertificateSeal } from './components/CertificateSeal';
export { CertificateBorders } from './components/CertificateBorders';
export { CertificateSignatures } from './components/CertificateSignatures';
export { IssuerLogo } from './components/IssuerLogo';

export { CERTIFICATE_PRESETS } from './data/presets';
export { FRAME_STYLES } from './data/frameStyles';
export { LAYOUT_TEMPLATE_VERSIONS, getLayoutMeta } from './data/layoutTemplates';

export {
  mapHrEmployeeToCertificate,
  normalizeHrEmployee,
} from './utils/hrIntegration';
export type {
  HrAdminEmployeeInput,
  NormalizedHrEmployee,
} from './utils/hrIntegration';

export {
  exportCertificateAsImage,
  exportCertificateAsPdf,
  exportCertificateAsJson,
  printCertificate,
} from './utils/exportUtils';

export {
  downloadFullProjectZip,
  downloadModuleMergeZip,
  getBundledProjectFileList,
  getModuleMergeFileList,
} from './utils/projectZipExporter';

export * from './types/certificate';
