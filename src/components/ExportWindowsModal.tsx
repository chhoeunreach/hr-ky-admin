import React, { useMemo, useState } from 'react';
import {
  X,
  Download,
  Terminal,
  Code2,
  Check,
  FileCode,
  Sparkles,
  FolderArchive,
  Copy,
  FolderTree,
  GitMerge,
  Layers,
} from 'lucide-react';
import {
  downloadFullProjectZip,
  downloadModuleMergeZip,
  getBundledProjectFileList,
  getModuleMergeFileList,
} from '../utils/projectZipExporter';

interface ExportWindowsModalProps {
  isOpen: boolean;
  onClose: () => void;
  uiLang?: 'en' | 'km';
}

export const ExportWindowsModal: React.FC<ExportWindowsModalProps> = ({
  isOpen,
  onClose,
  uiLang = 'en',
}) => {
  const [activeTab, setActiveTab] = useState<'zip' | 'merge' | 'laravel' | 'guide'>('zip');
  const [laravelSubTab, setLaravelSubTab] = useState<'controller' | 'blade' | 'routes' | 'migration'>('controller');
  const [isGeneratingZip, setIsGeneratingZip] = useState<'full' | 'module' | null>(null);
  const [downloadedNotice, setDownloadedNotice] = useState<string | null>(null);
  const [copiedKey, setCopiedKey] = useState<string | null>(null);
  const [treeMode, setTreeMode] = useState<'none' | 'full' | 'module'>('none');

  const bundledFiles = useMemo(() => getBundledProjectFileList(), []);
  const moduleFiles = useMemo(() => getModuleMergeFileList(), []);

  if (!isOpen) return null;

  const copyToClipboard = (text: string, key: string) => {
    navigator.clipboard.writeText(text);
    setCopiedKey(key);
    setTimeout(() => setCopiedKey(null), 2500);
  };

  const handleDownloadFullZip = async () => {
    setIsGeneratingZip('full');
    try {
      const result = await downloadFullProjectZip();
      setDownloadedNotice(`${result.archiveName} (${result.fileCount} files)`);
    } catch (err) {
      console.error('Failed to create full project zip package', err);
    } finally {
      setIsGeneratingZip(null);
    }
  };

  const handleDownloadModuleZip = async () => {
    setIsGeneratingZip('module');
    try {
      const result = await downloadModuleMergeZip();
      setDownloadedNotice(`${result.archiveName} (${result.fileCount} files)`);
    } catch (err) {
      console.error('Failed to create module merge zip package', err);
    } finally {
      setIsGeneratingZip(null);
    }
  };

  const isKm = uiLang === 'km';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in">
      <div className="relative w-full max-w-4xl max-h-[90vh] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50 shrink-0">
          <div className="flex items-center gap-3">
            <div className="p-2 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
              <GitMerge className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span>
                  {isKm
                    ? 'ទាញយកគម្រោងពេញ & ភ្ជាប់ទៅ hr-ky-admin'
                    : 'Download Full Project & Merge into hr-ky-admin'}
                </span>
                <span className="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                  Embeddable Module
                </span>
              </h3>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                {isKm
                  ? 'ទាញយកកូដគម្រោងពេញ (.zip) ឬកញ្ចប់ Module ដើម្បីបញ្ចូលក្នុង hr-ky-admin, React/Vite ឬ Laravel'
                  : 'Download full runnable project (.zip) or clean drop-in module pack to merge into hr-ky-admin or Laravel'}
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Tab Selection */}
        <div className="flex overflow-x-auto border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-6 gap-5 shrink-0">
          <button
            type="button"
            onClick={() => setActiveTab('zip')}
            className={`flex items-center gap-2 py-3 text-xs font-bold border-b-2 transition-colors cursor-pointer whitespace-nowrap ${
              activeTab === 'zip'
                ? 'border-amber-600 text-amber-600 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'
            }`}
          >
            <FolderArchive className="w-4 h-4" />
            <span>{isKm ? 'ទាញយកកញ្ចប់ ZIP' : 'Download Project & Module (.zip)'}</span>
          </button>

          <button
            type="button"
            onClick={() => setActiveTab('merge')}
            className={`flex items-center gap-2 py-3 text-xs font-bold border-b-2 transition-colors cursor-pointer whitespace-nowrap ${
              activeTab === 'merge'
                ? 'border-amber-600 text-amber-600 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'
            }`}
          >
            <Layers className="w-4 h-4" />
            <span>{isKm ? 'វិធីភ្ជាប់ទៅ hr-ky-admin' : 'Merge into hr-ky-admin (React)'}</span>
          </button>

          <button
            type="button"
            onClick={() => setActiveTab('laravel')}
            className={`flex items-center gap-2 py-3 text-xs font-bold border-b-2 transition-colors cursor-pointer whitespace-nowrap ${
              activeTab === 'laravel'
                ? 'border-amber-600 text-amber-600 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'
            }`}
          >
            <Code2 className="w-4 h-4" />
            <span>{isKm ? 'ការតភ្ជាប់ Laravel 10/11' : 'Laravel 10/11'}</span>
          </button>

          <button
            type="button"
            onClick={() => setActiveTab('guide')}
            className={`flex items-center gap-2 py-3 text-xs font-bold border-b-2 transition-colors cursor-pointer whitespace-nowrap ${
              activeTab === 'guide'
                ? 'border-amber-600 text-amber-600 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-slate-200'
            }`}
          >
            <Terminal className="w-4 h-4" />
            <span>{isKm ? 'សេចក្តីណែនាំ Windows' : 'Windows & Laragon'}</span>
          </button>
        </div>

        {/* Modal Body */}
        <div className="flex-1 overflow-y-auto p-6 space-y-5">
          {/* TAB 1: ONE-CLICK DOWNLOAD ZIPs */}
          {activeTab === 'zip' && (
            <div className="space-y-5">
              {downloadedNotice && (
                <div className="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                  <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>
                    {isKm ? `បានទាញយកជោគជ័យ៖ ${downloadedNotice}` : `Downloaded: ${downloadedNotice}`}
                  </span>
                </div>
              )}

              {/* Option A: Module-Only Drop-in Pack for hr-ky-admin / Existing Project */}
              <div className="p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent border-2 border-emerald-500/35">
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                  <div className="space-y-1.5 max-w-xl">
                    <span className="text-[11px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                      <GitMerge className="w-3.5 h-3.5" />
                      {isKm ? 'ណែនាំសម្រាប់ Merge ចូល hr-ky-admin' : 'Recommended for Merging into hr-ky-admin'}
                    </span>
                    <h4 className="text-lg font-bold text-slate-900 dark:text-slate-100">
                      {isKm
                        ? `ទាញយកកញ្ចប់ Module សម្រាប់ដាក់ចូលគម្រោងមានស្រាប់ (${moduleFiles.length} ឯកសារ)`
                        : `Download Drop-In Module Pack for hr-ky-admin (${moduleFiles.length} Files)`}
                    </h4>
                    <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                      {isKm
                        ? 'រៀបចំរួចជាស្រេចក្នុង folder `src/modules/certificates/` (មាន `<CertificateStudio />`, `certificateModule.ts`, `MERGE_GUIDE.md` និង `INSTALL_DEPS.txt`) មិនប៉ះពាល់ `package.json` ឬ `App.tsx` ដើមរបស់គម្រោងលោកអ្នកឡើយ។'
                        : 'Pre-packaged cleanly inside `src/modules/certificates/` with `<CertificateStudio />`, `hrIntegration.ts` adapter, `public/` assets, `MERGE_GUIDE.md`, and `INSTALL_DEPS.txt` — safe to merge without overwriting your host project root files.'}
                    </p>
                  </div>

                  <button
                    type="button"
                    onClick={handleDownloadModuleZip}
                    disabled={isGeneratingZip !== null}
                    className="flex items-center gap-2 px-5 py-3 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 rounded-xl shadow-lg transition-all cursor-pointer whitespace-nowrap shrink-0 disabled:opacity-50"
                  >
                    <Download className="w-4 h-4" />
                    <span>
                      {isGeneratingZip === 'module'
                        ? (isKm ? 'កំពុងបង្កើត...' : 'Packaging...')
                        : (isKm ? 'ទាញយក Module (.zip)' : 'Download Merge Module (.zip)')}
                    </span>
                  </button>
                </div>
              </div>

              {/* Option B: Full Standalone Project ZIP */}
              <div className="p-5 rounded-2xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30">
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                  <div className="space-y-1.5 max-w-xl">
                    <span className="text-[11px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400 flex items-center gap-1.5 font-cinzel">
                      <Sparkles className="w-3.5 h-3.5" />
                      Complete Full Project Repository + Windows & Laravel
                    </span>
                    <h4 className="text-lg font-bold text-slate-900 dark:text-slate-100">
                      {isKm
                        ? `ទាញយកកូដគម្រោងពេញលេញទាំងអស់ (${bundledFiles.length} ឯកសារ)`
                        : `Download Complete Full Project Source (${bundledFiles.length} Files)`}
                    </h4>
                    <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                      {isKm
                        ? 'រួមបញ្ចូល Source Code ពេញលេញទាំងអស់ (`src/`, `public/`, `package.json`, `vite.config.ts`), Windows `.bat`, `MERGE_GUIDE.md` និង Laravel 10/11។'
                        : 'Complete runnable React + Vite + TypeScript repository (`src/`, `public/`, `package.json`, `vite.config.ts`), Windows `.bat` scripts, `MERGE_GUIDE.md`, and Laravel Blade/Controller.'}
                    </p>
                  </div>

                  <button
                    type="button"
                    onClick={handleDownloadFullZip}
                    disabled={isGeneratingZip !== null}
                    className="flex items-center gap-2 px-5 py-3 text-xs sm:text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 active:scale-95 rounded-xl shadow-lg transition-all cursor-pointer whitespace-nowrap shrink-0 disabled:opacity-50"
                  >
                    <Download className="w-4 h-4" />
                    <span>
                      {isGeneratingZip === 'full'
                        ? (isKm ? 'កំពុងបង្កើត ZIP...' : 'Packaging ZIP...')
                        : (isKm ? 'ទាញយកគម្រោងពេញ (.zip)' : 'Download Full Project (.zip)')}
                    </span>
                  </button>
                </div>
              </div>

              {/* File list viewer */}
              <div>
                <div className="flex flex-wrap items-center justify-between gap-2 mb-3">
                  <h5 className="text-xs font-bold uppercase tracking-wider text-slate-500">
                    {isKm ? 'ត្រួតពិនិត្យបញ្ជីឯកសារក្នុងកញ្ចប់' : 'Inspect Package Contents'}
                  </h5>
                  <div className="flex items-center gap-3">
                    <button
                      type="button"
                      onClick={() => setTreeMode((prev) => (prev === 'module' ? 'none' : 'module'))}
                      className="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline cursor-pointer"
                    >
                      <FolderTree className="w-3.5 h-3.5" />
                      <span>{treeMode === 'module' ? 'Hide Module Files' : `Module Files (${moduleFiles.length})`}</span>
                    </button>
                    <button
                      type="button"
                      onClick={() => setTreeMode((prev) => (prev === 'full' ? 'none' : 'full'))}
                      className="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 hover:underline cursor-pointer"
                    >
                      <FolderTree className="w-3.5 h-3.5" />
                      <span>{treeMode === 'full' ? 'Hide Full Files' : `Full Project Files (${bundledFiles.length})`}</span>
                    </button>
                  </div>
                </div>

                {treeMode !== 'none' && (
                  <div className="mb-4 max-h-48 overflow-y-auto rounded-xl bg-slate-950 text-slate-200 p-3 font-mono text-[11px] border border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1">
                    {(treeMode === 'module' ? moduleFiles : bundledFiles).map((file) => (
                      <div key={file} className="truncate text-emerald-300/90">
                        {file}
                      </div>
                    ))}
                  </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div className="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-start gap-3">
                    <span className="p-2 rounded-lg bg-emerald-500/10 text-emerald-600 font-mono text-xs font-bold">API</span>
                    <div>
                      <div className="font-bold text-xs text-slate-900 dark:text-slate-100 font-mono">
                        &lt;CertificateStudio /&gt; + hrIntegration.ts
                      </div>
                      <div className="text-[11px] text-slate-500 mt-0.5">
                        {isKm
                          ? 'ទទួលទិន្នន័យ `employee`, `externalEmployees` និង `onSaveCertificate` ពី hr-ky-admin ដោយផ្ទាល់'
                          : 'Accepts `employee`, `externalEmployees`, `embedded`, `onBack`, and `onSaveCertificate` props.'}
                      </div>
                    </div>
                  </div>

                  <div className="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-start gap-3">
                    <span className="p-2 rounded-lg bg-amber-500/10 text-amber-600 font-mono text-xs font-bold">V1-V6</span>
                    <div>
                      <div className="font-bold text-xs text-slate-900 dark:text-slate-100 font-mono">
                        6 Templates · 17 Frames · Drag Photo
                      </div>
                      <div className="text-[11px] text-slate-500 mt-0.5">
                        {isKm
                          ? 'ទម្រង់ V1–V6, ម៉ូតស៊ុម ១៧, អូសរូបភាពដោយ Mouse, QR ផ្ទៀងផ្ទាត់ និង Export A4 PDF/PNG'
                          : 'All V1–V6 layouts, 17 frames, draggable photo frame, QR verification & A4 PDF/PNG export.'}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: MERGE INTO HR-KY-ADMIN */}
          {activeTab === 'merge' && (
            <div className="space-y-4">
              <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/70 text-xs text-emerald-900 dark:text-emerald-200 space-y-1">
                <div className="font-bold">
                  {isKm
                    ? 'ជំហានងាយៗ ៣ ដើម្បីបញ្ចូលមុខងារវិញ្ញាបនបត្រទៅក្នុង hr-ky-admin៖'
                    : '3 Quick Steps to Add Certificate Studio inside hr-ky-admin:'}
                </div>
                <ol className="list-decimal pl-5 space-y-0.5">
                  <li>
                    {isKm
                      ? 'ទាញយក "Download Merge Module (.zip)" ហើយពន្លាដាក់ `src/modules/certificates/` និង `public/assets/aistudio/` ចូលក្នុង project `hr-ky-admin`។'
                      : 'Download "Download Merge Module (.zip)" and copy `src/modules/certificates/` + `public/assets/aistudio/` into `hr-ky-admin`.'}
                  </li>
                  <li>
                    {isKm
                      ? 'ដំឡើង npm packages ខាងក្រោមក្នុង terminal របស់ `hr-ky-admin`។'
                      : 'Install the peer npm packages inside `hr-ky-admin`.'}
                  </li>
                  <li>
                    {isKm
                      ? 'Mount `<CertificateStudio embedded ... />` ក្នុង Route ឬទំព័របុគ្គលិករបស់អ្នក។'
                      : 'Mount `<CertificateStudio embedded ... />` inside a route or employee detail page.'}
                  </li>
                </ol>
              </div>

              {/* Step 1: npm install command */}
              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-200">
                    1. Install Dependencies in hr-ky-admin
                  </span>
                  <button
                    type="button"
                    onClick={() => copyToClipboard(NPM_INSTALL_SNIPPET, 'npm')}
                    className="flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer"
                  >
                    {copiedKey === 'npm' ? <Check className="w-3.5 h-3.5 text-emerald-500" /> : <Copy className="w-3.5 h-3.5" />}
                    <span>{copiedKey === 'npm' ? 'Copied!' : 'Copy'}</span>
                  </button>
                </div>
                <pre className="rounded-xl bg-slate-950 text-emerald-300 p-3 text-xs font-mono overflow-x-auto border border-slate-800">
                  <code>{NPM_INSTALL_SNIPPET}</code>
                </pre>
              </div>

              {/* Step 2: React integration component snippet */}
              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-200">
                    2. Mount &lt;CertificateStudio /&gt; in hr-ky-admin
                  </span>
                  <button
                    type="button"
                    onClick={() => copyToClipboard(HR_KY_ADMIN_USAGE_SNIPPET, 'hr-usage')}
                    className="flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer"
                  >
                    {copiedKey === 'hr-usage' ? <Check className="w-3.5 h-3.5 text-emerald-500" /> : <Copy className="w-3.5 h-3.5" />}
                    <span>{copiedKey === 'hr-usage' ? 'Copied!' : 'Copy TSX'}</span>
                  </button>
                </div>
                <pre className="rounded-xl bg-slate-950 text-slate-200 p-3.5 text-xs font-mono overflow-x-auto max-h-64 leading-relaxed border border-slate-800">
                  <code>{HR_KY_ADMIN_USAGE_SNIPPET}</code>
                </pre>
              </div>
            </div>
          )}

          {/* TAB 3: LARAVEL 10/11 INTEGRATION */}
          {activeTab === 'laravel' && (
            <div className="space-y-4">
              <div className="flex items-center justify-between flex-wrap gap-2">
                <div className="flex gap-2 flex-wrap">
                  <button
                    type="button"
                    onClick={() => setLaravelSubTab('controller')}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer ${
                      laravelSubTab === 'controller'
                        ? 'bg-amber-600 text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                    }`}
                  >
                    CertificateController.php
                  </button>
                  <button
                    type="button"
                    onClick={() => setLaravelSubTab('blade')}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer ${
                      laravelSubTab === 'blade'
                        ? 'bg-amber-600 text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                    }`}
                  >
                    certificate.blade.php
                  </button>
                  <button
                    type="button"
                    onClick={() => setLaravelSubTab('routes')}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer ${
                      laravelSubTab === 'routes'
                        ? 'bg-amber-600 text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                    }`}
                  >
                    routes/web.php
                  </button>
                  <button
                    type="button"
                    onClick={() => setLaravelSubTab('migration')}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer ${
                      laravelSubTab === 'migration'
                        ? 'bg-amber-600 text-white shadow-xs'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'
                    }`}
                  >
                    Migration (SQL)
                  </button>
                </div>

                <button
                  type="button"
                  onClick={() => {
                    const codeMap = {
                      controller: LARAVEL_CONTROLLER_CODE,
                      blade: LARAVEL_BLADE_CODE,
                      routes: LARAVEL_ROUTES_CODE,
                      migration: LARAVEL_MIGRATION_CODE,
                    };
                    copyToClipboard(codeMap[laravelSubTab], laravelSubTab);
                  }}
                  className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer"
                >
                  {copiedKey === laravelSubTab ? (
                    <>
                      <Check className="w-3.5 h-3.5 text-emerald-500" />
                      <span>{isKm ? 'បានចម្លង!' : 'Copied!'}</span>
                    </>
                  ) : (
                    <>
                      <Copy className="w-3.5 h-3.5" />
                      <span>{isKm ? 'ចម្លងកូដ' : 'Copy Code'}</span>
                    </>
                  )}
                </button>
              </div>

              <div className="relative rounded-xl bg-slate-950 p-4 border border-slate-800 overflow-hidden">
                <pre className="text-xs font-mono text-slate-300 overflow-x-auto max-h-96 leading-relaxed">
                  <code>
                    {laravelSubTab === 'controller' && LARAVEL_CONTROLLER_CODE}
                    {laravelSubTab === 'blade' && LARAVEL_BLADE_CODE}
                    {laravelSubTab === 'routes' && LARAVEL_ROUTES_CODE}
                    {laravelSubTab === 'migration' && LARAVEL_MIGRATION_CODE}
                  </code>
                </pre>
              </div>
            </div>
          )}

          {/* TAB 4: STEP-BY-STEP WINDOWS SETUP GUIDE */}
          {activeTab === 'guide' && (
            <div className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50">
                  <span className="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 font-bold flex items-center justify-center text-xs mb-3">
                    1
                  </span>
                  <h5 className="font-bold text-xs text-slate-900 dark:text-slate-100">
                    {isKm ? 'ដំឡើង Node.js' : 'Install Node.js'}
                  </h5>
                  <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    {isKm
                      ? 'ទាញយក Node.js (LTS version 18 ឬ 20) ពី nodejs.org ហើយដំឡើងតាមការណែនាំ Next, Next, Finish'
                      : 'Download Node.js (v18 or v20 LTS) from nodejs.org and follow standard Windows wizard.'}
                  </p>
                </div>

                <div className="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50">
                  <span className="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 font-bold flex items-center justify-center text-xs mb-3">
                    2
                  </span>
                  <h5 className="font-bold text-xs text-slate-900 dark:text-slate-100">
                    {isKm ? 'ចុច INSTALL_WINDOWS.bat' : 'Run INSTALL_WINDOWS.bat'}
                  </h5>
                  <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    {isKm
                      ? 'ចុចពីរដង (Double-click) លើ INSTALL_WINDOWS.bat ដើម្បីទាញយក Package ទាំងអស់ដោយស្វ័យប្រវត្តិ'
                      : 'Double click INSTALL_WINDOWS.bat to populate node_modules without needing command prompt knowledge.'}
                  </p>
                </div>

                <div className="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50">
                  <span className="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 font-bold flex items-center justify-center text-xs mb-3">
                    3
                  </span>
                  <h5 className="font-bold text-xs text-slate-900 dark:text-slate-100">
                    {isKm ? 'ចុច RUN_WINDOWS.bat' : 'Double Click RUN_WINDOWS.bat'}
                  </h5>
                  <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    {isKm
                      ? 'ចុចពីរដងលើ RUN_WINDOWS.bat នោះកម្មវិធីនឹងបើក Browser ទៅកាន់ http://localhost:3000 ដោយស្វ័យប្រវត្តិ!'
                      : 'Launches Vite and immediately opens http://localhost:3000 in your browser ready to create certificates.'}
                  </p>
                </div>
              </div>

              <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                <h5 className="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                  <FileCode className="w-4 h-4 text-amber-600" />
                  <span>{isKm ? 'ការប្រើប្រាស់ជាមួយ Laragon ឬ XAMPP លើ Windows' : 'Running inside Laragon or XAMPP on Windows'}</span>
                </h5>
                <p className="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                  {isKm
                    ? 'ប្រសិនបើលោកអ្នកប្រើប្រាស់ Laragon ឬ XAMPP សម្រាប់គម្រោង Laravel៖ គ្រាន់តែចម្លង CertificateController.php ទៅក្នុង app/Http/Controllers/ និង template.blade.php ទៅក្នុង resources/views/ នោះលោកអ្នកអាចបង្កើតវិញ្ញាបនបត្រចេញពីទិន្នន័យ MySQL Database របស់លោកអ្នកបានភ្លាមៗ!'
                    : 'If developing with Laragon or XAMPP on Windows: simply place the controller and blade template into your Laravel repository. Your certificate generator can now pull dynamic records directly from MySQL and export A4 PDFs!'}
                </p>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50 shrink-0">
          <div className="text-xs text-slate-500 dark:text-slate-400">
            {isKm ? 'ម៉ាកសញ្ញា៖ គ្នា​យើង (Knea Yoeng) · ឈឿន រាជ' : 'Author: Reach Chhoeun · គ្នា​យើង (Knea Yoeng)'}
          </div>
          <button
            type="button"
            onClick={onClose}
            className="px-5 py-2 text-xs font-semibold rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition-colors cursor-pointer"
          >
            {isKm ? 'បិទ' : 'Close'}
          </button>
        </div>
      </div>
    </div>
  );
};

const NPM_INSTALL_SNIPPET = `npm install lucide-react html2canvas jspdf qrcode jszip motion
npm install -D @types/qrcode @types/jszip`;

const HR_KY_ADMIN_USAGE_SNIPPET = `import { CertificateStudio, CertificateData } from './modules/certificates/certificateModule';

export function HrCertificatesPage({ selectedEmployee, employees, navigate }: any) {
  const handleSave = async (certificate: CertificateData) => {
    await fetch('/api/hr/certificates', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(certificate),
    });
  };

  return (
    <div className="h-[calc(100vh-64px)] w-full">
      <CertificateStudio
        embedded
        defaultLanguage="km"
        employee={selectedEmployee}
        externalEmployees={employees}
        onBack={() => navigate('/employees')}
        onSaveCertificate={handleSave}
      />
    </div>
  );
}`;

// Ready-to-copy Laravel code snippets
const LARAVEL_CONTROLLER_CODE = `<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use Barryvdh\\DomPDF\\Facade\\Pdf;

class CertificateController extends Controller
{
    public function show($id)
    {
        $certificate = [
            'id' => $id,
            'credentialId' => $id,
            'certificateTitle' => 'OUTSTANDING TEAM LEADER AWARD',
            'khmerTitle' => 'វិញ្ញាបនបត្រកិត្តិយស មេដឹកនាំឆ្នើម',
            'authorityPreamble' => 'KINGDOM OF CAMBODIA · NATION RELIGION KING',
            'khmerPreamble' => 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ',
            'recipientName' => 'Reach Chhoeun',
            'khmerRecipientName' => 'ឈឿន រាជ',
            'courseOrAchievementTitle' => 'Strategic Leadership & Team Empowerment Milestone',
            'khmerCourseOrAchievementTitle' => 'សមិទ្ធផលភាពជាអ្នកដឹកនាំយុទ្ធសាស្ត្រ និងសាមគ្គីភាពក្រុម',
            'citation' => 'For embodying the highest standard of leadership under the ethos of "គ្នា​យើង" (United As One).',
            'khmerCitation' => 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការដឹកនាំក្រុមប្រកបដោយចក្ខុវិស័យខ្ពស់ និងស្មារតី "គ្នា​យើង"។',
            'digitalHr' => [
                'employeeId' => 'EMP-KY-2026-08',
                'trainingHours' => 60,
                'complianceStatus' => 'Distinction / Honor Roll',
            ],
            'issueDate' => 'November 11, 2026',
            'khmerIssueDate' => 'ថ្ងៃទី ១១ ខែ វិច្ឆិកា ឆ្នាំ ២០២៦',
        ];

        return view('certificate.template', compact('certificate'));
    }

    public function downloadPdf($id)
    {
        $certificate = $this->getCertificateData($id);

        $pdf = Pdf::loadView('certificate.template', compact('certificate'))
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => true, 'dpi' => 300]);

        return $pdf->download("Certificate_{$id}.pdf");
    }
}`;

const LARAVEL_BLADE_CODE = `<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificate['khmerTitle'] ?? $certificate['certificateTitle'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Kantumruy+Pro:wght@400;600;700&family=Moul&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .font-moul { font-family: 'Moul', cursive, serif; }
        .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
        .font-cinzel { font-family: 'Cinzel', serif; }
        @page { size: A4 landscape; margin: 0; }
    </style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="relative w-[1123px] h-[794px] bg-[#FFFDF8] border-8 border-[#C89B3C] shadow-2xl p-12 text-center text-[#2D1328] flex flex-col justify-between">
        <div>
            <p class="font-moul text-sm text-amber-900">{{ $certificate['khmerPreamble'] }}</p>
            <p class="font-cinzel text-xs text-[#C89B3C] tracking-widest font-bold">{{ $certificate['authorityPreamble'] }}</p>
        </div>
        <div>
            <h1 class="font-moul text-2xl text-amber-900">{{ $certificate['khmerTitle'] }}</h1>
            <h2 class="font-cinzel text-xl font-bold tracking-wider">{{ $certificate['certificateTitle'] }}</h2>
        </div>
        <div class="my-2">
            <h2 class="font-moul text-3xl">{{ $certificate['khmerRecipientName'] }}</h2>
            <h3 class="font-cinzel text-2xl font-bold">{{ $certificate['recipientName'] }}</h3>
            <div class="w-64 h-0.5 bg-[#C89B3C] mx-auto mt-2"></div>
        </div>
        <div class="border-2 border-[#C89B3C] px-6 py-2 bg-amber-50 max-w-md mx-auto">
            <div class="font-cinzel text-xs font-bold text-amber-950">{{ $certificate['digitalHr']['trainingHours'] }}.0 ម៉ោងបណ្តុះបណ្តាលឌីជីថល HR</div>
            <div class="text-[10px] text-slate-600">ID: {{ $certificate['digitalHr']['employeeId'] }} · {{ $certificate['digitalHr']['complianceStatus'] }}</div>
        </div>
        <div class="max-w-xl mx-auto">
            <p class="font-kantumruy text-xs text-slate-700 italic">« {{ $certificate['khmerCitation'] }} »</p>
        </div>
        <div class="flex justify-between border-t border-[#C89B3C]/30 pt-3 text-xs">
            <div>{{ $certificate['khmerIssueDate'] }}</div>
            <div class="font-cinzel font-bold text-[#C89B3C]">គ្នា​យើង OFFICIAL SEAL</div>
        </div>
    </div>
</body>
</html>`;

const LARAVEL_ROUTES_CODE = `use App\\Http\\Controllers\\CertificateController;

Route::prefix('certificates')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/{id}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/{id}/download-pdf', [CertificateController::class, 'downloadPdf'])->name('certificates.downloadPdf');
    Route::get('/{id}/verify', [CertificateController::class, 'verify'])->name('certificates.verify');
});`;

const LARAVEL_MIGRATION_CODE = `Schema::create('certificates', function (Blueprint $table) {
    $table->id();
    $table->string('credential_id')->unique();
    $table->string('certificate_title');
    $table->string('khmer_title')->nullable();
    $table->string('recipient_name');
    $table->string('khmer_recipient_name')->nullable();
    $table->integer('training_hours')->default(0);
    $table->string('employee_id')->nullable();
    $table->string('compliance_status')->default('Verified & Accredited');
    $table->string('issue_date');
    $table->timestamps();
});`;
