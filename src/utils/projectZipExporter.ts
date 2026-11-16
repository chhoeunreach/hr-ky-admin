import JSZip from 'jszip';
import { RAW_PROJECT_FILES } from './rawProjectFiles';

const SELF_RAW_FILE_CONTENT = `export const RAW_PROJECT_FILES = import.meta.glob(
  [
    '/index.html',
    '/vite.config.ts',
    '/tsconfig.json',
    '/package.json',
    '/.env.example',
    '/.gitignore',
    '/metadata.json',
    '/MERGE_GUIDE.md',
    '/INSTALL_WINDOWS.bat',
    '/RUN_WINDOWS.bat',
    '/BUILD_WINDOWS.bat',
    '/README_WINDOWS.txt',
    '/laravel/**/*',
    '/src/**/*.ts',
    '/src/**/*.tsx',
    '/src/**/*.css',
    '!/src/utils/rawProjectFiles.ts',
  ],
  { query: '?raw', import: 'default', eager: true }
) as Record<string, string>;
`;

const PUBLIC_BINARY_ASSETS = [
  '/assets/aistudio/unnamed_521b71dc.jpg',
  '/assets/aistudio/image_443f7858.png',
];

export interface ProjectExportSummary {
  fileCount: number;
  filePaths: string[];
  archiveName: string;
}

export function getBundledProjectFileList(): string[] {
  const paths = Object.keys(RAW_PROJECT_FILES).map((p) => (p.startsWith('/') ? p.slice(1) : p));
  paths.push('src/utils/rawProjectFiles.ts');
  for (const asset of PUBLIC_BINARY_ASSETS) {
    paths.push(`public${asset}`);
  }
  return paths.sort();
}

export function getModuleMergeFileList(): string[] {
  const paths = Object.keys(RAW_PROJECT_FILES)
    .map((p) => (p.startsWith('/') ? p.slice(1) : p))
    .filter(
      (p) =>
        p.startsWith('src/') &&
        p !== 'src/main.tsx' &&
        p !== 'src/App.tsx'
    )
    .map((p) => `src/modules/certificates/${p.slice('src/'.length)}`);

  paths.push('src/modules/certificates/utils/rawProjectFiles.ts');
  paths.push('MERGE_GUIDE.md');
  paths.push('INSTALL_DEPS.txt');
  for (const asset of PUBLIC_BINARY_ASSETS) {
    paths.push(`public${asset}`);
  }
  return paths.sort();
}

function triggerZipDownload(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Downloads the complete standalone runnable project (including package.json, Vite config,
 * src/, public/, Windows .bat launchers, Laravel files, and MERGE_GUIDE.md).
 */
export async function downloadFullProjectZip(): Promise<ProjectExportSummary> {
  const zip = new JSZip();
  const includedPaths: string[] = [];

  for (const [rawPath, content] of Object.entries(RAW_PROJECT_FILES)) {
    const cleanPath = rawPath.startsWith('/') ? rawPath.slice(1) : rawPath;
    if (typeof content === 'string') {
      zip.file(cleanPath, content);
      includedPaths.push(cleanPath);
    }
  }

  zip.file('src/utils/rawProjectFiles.ts', SELF_RAW_FILE_CONTENT);
  includedPaths.push('src/utils/rawProjectFiles.ts');

  for (const assetPath of PUBLIC_BINARY_ASSETS) {
    try {
      const res = await fetch(assetPath);
      if (res.ok) {
        const buffer = await res.arrayBuffer();
        const destPath = `public${assetPath}`;
        zip.file(destPath, buffer);
        includedPaths.push(destPath);
      }
    } catch (err) {
      console.warn(`Could not bundle binary asset ${assetPath}:`, err);
    }
  }

  const archiveName = 'CertiCraft-Studio-Full-Project.zip';
  const blob = await zip.generateAsync({
    type: 'blob',
    compression: 'DEFLATE',
    compressionOptions: { level: 6 },
  });

  triggerZipDownload(blob, archiveName);

  return {
    fileCount: includedPaths.length,
    filePaths: includedPaths.sort(),
    archiveName,
  };
}

/**
 * Downloads an embeddable drop-in module pack specifically organized to merge into
 * hr-ky-admin or any existing React/Vite/Next.js/Laravel repository without overwriting root files.
 */
export async function downloadModuleMergeZip(): Promise<ProjectExportSummary> {
  const zip = new JSZip();
  const includedPaths: string[] = [];

  for (const [rawPath, content] of Object.entries(RAW_PROJECT_FILES)) {
    const cleanPath = rawPath.startsWith('/') ? rawPath.slice(1) : rawPath;
    if (typeof content !== 'string') continue;

    if (cleanPath === 'MERGE_GUIDE.md') {
      zip.file('MERGE_GUIDE.md', content);
      includedPaths.push('MERGE_GUIDE.md');
    } else if (cleanPath.startsWith('laravel/')) {
      zip.file(cleanPath, content);
      includedPaths.push(cleanPath);
    } else if (
      cleanPath.startsWith('src/') &&
      cleanPath !== 'src/main.tsx' &&
      cleanPath !== 'src/App.tsx'
    ) {
      const targetPath = `src/modules/certificates/${cleanPath.slice('src/'.length)}`;
      zip.file(targetPath, content);
      includedPaths.push(targetPath);
    }
  }

  // Provide stub rawProjectFiles inside module so TypeScript compiles cleanly in host projects
  const moduleRawStub = `export const RAW_PROJECT_FILES: Record<string, string> = {};\n`;
  zip.file('src/modules/certificates/utils/rawProjectFiles.ts', moduleRawStub);
  includedPaths.push('src/modules/certificates/utils/rawProjectFiles.ts');

  const installDepsText = `# Quick Merge Steps for hr-ky-admin (React / TypeScript / Vite)
1. Copy src/modules/certificates/ into your project's src/modules/certificates/ folder
2. Copy public/assets/aistudio/unnamed_521b71dc.jpg into your project's public/assets/aistudio/
3. Install required npm packages in your existing project:
   npm install lucide-react html2canvas jspdf qrcode jszip motion
   npm install -D @types/qrcode @types/jszip
4. Import and mount <CertificateStudio /> in your route/page:
   import { CertificateStudio } from './modules/certificates/certificateModule';

   <CertificateStudio
     embedded
     employee={selectedEmployee}
     externalEmployees={employees}
     onSaveCertificate={(cert) => api.saveCertificate(cert)}
   />
`;
  zip.file('INSTALL_DEPS.txt', installDepsText);
  includedPaths.push('INSTALL_DEPS.txt');

  for (const assetPath of PUBLIC_BINARY_ASSETS) {
    try {
      const res = await fetch(assetPath);
      if (res.ok) {
        const buffer = await res.arrayBuffer();
        const destPath = `public${assetPath}`;
        zip.file(destPath, buffer);
        includedPaths.push(destPath);
      }
    } catch (err) {
      console.warn(`Could not bundle binary asset ${assetPath}:`, err);
    }
  }

  const archiveName = 'hr-ky-admin-certificate-module.zip';
  const blob = await zip.generateAsync({
    type: 'blob',
    compression: 'DEFLATE',
    compressionOptions: { level: 6 },
  });

  triggerZipDownload(blob, archiveName);

  return {
    fileCount: includedPaths.length,
    filePaths: includedPaths.sort(),
    archiveName,
  };
}
