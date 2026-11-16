import React, { useState } from 'react';
import {
  Download,
  Globe,
  LayoutTemplate,
  ChevronDown,
  Check,
  Sparkles,
  FolderArchive,
  GitMerge,
  ArrowLeft,
  Loader2,
} from 'lucide-react';
import { TRANSLATIONS } from '../i18n/translations';
import { CERTIFICATE_PRESETS } from '../data/presets';
import { LAYOUT_TEMPLATE_VERSIONS, getLayoutMeta } from '../data/layoutTemplates';
import { AwardCategory, CertificateData, LayoutTemplateOption } from '../types/certificate';
import { downloadFullProjectZip } from '../utils/projectZipExporter';

interface HeaderProps {
  activePresetId: string;
  currentLayout: LayoutTemplateOption;
  currentLogoUrl?: string;
  onSelectPreset: (preset: CertificateData, options?: { styleOnly?: boolean; keepLogo?: boolean }) => void;
  onChangeLayout: (layout: LayoutTemplateOption) => void;
  onOpenVisualGallery: () => void;
  onSelectCategory: (category: 'staff' | 'course' | 'service' | 'leader' | 'all') => void;
  onOpenDigitalHrModal: () => void;
  onOpenBatchModal: () => void;
  onOpenVerifyModal: () => void;
  onOpenExportModal: () => void;
  onOpenWindowsModal: () => void;
  onBack?: () => void;
  uiLang: 'en' | 'km';
  onToggleLang: () => void;
}

export const Header: React.FC<HeaderProps> = ({
  activePresetId,
  currentLayout,
  currentLogoUrl,
  onSelectPreset,
  onChangeLayout,
  onOpenVisualGallery,
  onSelectCategory,
  onOpenDigitalHrModal,
  onOpenBatchModal,
  onOpenVerifyModal: _onOpenVerifyModal,
  onOpenExportModal,
  onOpenWindowsModal,
  onBack,
  uiLang,
  onToggleLang,
}) => {
  const t = TRANSLATIONS[uiLang];
  const [isTemplateMenuOpen, setIsTemplateMenuOpen] = useState(false);
  const [filterCat, setFilterCat] = useState<AwardCategory | 'all'>('all');
  const [styleOnlyMode, setStyleOnlyMode] = useState(false);
  const [isDownloadingZip, setIsDownloadingZip] = useState(false);
  const [zipDoneCount, setZipDoneCount] = useState<number | null>(null);

  const handleDirectDownloadProjectZip = async () => {
    if (isDownloadingZip) return;
    setIsDownloadingZip(true);
    try {
      const res = await downloadFullProjectZip();
      setZipDoneCount(res.fileCount);
      setTimeout(() => setZipDoneCount(null), 3500);
    } catch (err) {
      console.error('Failed to download full project zip:', err);
    } finally {
      setIsDownloadingZip(false);
    }
  };

  const activeLayoutMeta = getLayoutMeta(currentLayout);

  const filteredPresets = CERTIFICATE_PRESETS.filter(
    (p) => filterCat === 'all' || p.category === filterCat
  );

  return (
    <header className="no-print flex items-center justify-between gap-3 px-4 md:px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 z-30 shrink-0">
      {/* Zone 1: Brand Logo + Instant Template Changer Next to Logo */}
      <div className="flex items-center gap-2.5 shrink-0">
        {onBack && (
          <button
            type="button"
            onClick={onBack}
            className="flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer shrink-0"
            title={uiLang === 'km' ? 'ត្រឡប់ទៅ hr-ky-admin' : 'Back to HR Admin'}
          >
            <ArrowLeft className="w-3.5 h-3.5" />
            <span className="hidden md:inline">{uiLang === 'km' ? 'ត្រឡប់' : 'Back'}</span>
          </button>
        )}
        <a
          href="#editor"
          className="text-base md:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100 font-cinzel whitespace-nowrap shrink-0 hover:opacity-90 flex items-center gap-2"
        >
          <img
            src={currentLogoUrl || '/assets/aistudio/unnamed_521b71dc.jpg'}
            alt="គ្នា​យើង Logo"
            className="w-8 h-8 rounded-full object-cover border-2 border-amber-400 shadow-xs shrink-0"
          />
          <span className="hidden sm:inline">CertiCraft Studio</span>
          <span className="text-[10px] font-sans font-semibold px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-300 font-khmer-sans">
            គ្នា​យើង
          </span>
        </a>

        {/* Template & V1-V4 Form Changer Directly Beside the Logo */}
        <div className="relative">
          <button
            type="button"
            onClick={() => setIsTemplateMenuOpen((prev) => !prev)}
            className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-amber-300/80 dark:border-amber-700/70 bg-gradient-to-r from-amber-50 to-amber-100/70 dark:from-amber-950/60 dark:to-amber-900/40 hover:from-amber-100 hover:to-amber-200/70 text-amber-900 dark:text-amber-200 text-xs font-bold shadow-2xs transition-all cursor-pointer"
            title={uiLang === 'km' ? 'ប្តូរទម្រង់ V1-V4 ឬ ម៉ូតវិញ្ញាបនបត្រ' : 'Switch Form V1–V4 or Certificate Template'}
          >
            <span className="px-1.5 py-0.2 rounded bg-amber-600 text-white text-[10px] font-mono leading-tight shrink-0">
              {activeLayoutMeta.versionTag}
            </span>
            <LayoutTemplate className="w-3.5 h-3.5 text-amber-700 dark:text-amber-300 shrink-0" />
            <span className="max-w-[130px] md:max-w-[165px] truncate font-khmer-sans">
              {uiLang === 'km'
                ? activeLayoutMeta.khmerLabel
                : activeLayoutMeta.label}
            </span>
            <ChevronDown className="w-3.5 h-3.5 opacity-75 shrink-0" />
          </button>

          {isTemplateMenuOpen && (
            <>
              <div
                className="fixed inset-0 z-40"
                onClick={() => setIsTemplateMenuOpen(false)}
              />
              <div className="absolute left-0 top-full mt-2 w-[340px] sm:w-[390px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden">
                {/* Header & Quick Layout Mode Switch */}
                <div className="p-3 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 space-y-2.5">
                  <div className="flex items-center justify-between gap-2">
                    <div className="flex items-center gap-1.5">
                      <Sparkles className="w-3.5 h-3.5 text-amber-600" />
                      <span className="text-xs font-bold text-slate-800 dark:text-slate-100 font-khmer-sans">
                        {uiLang === 'km'
                          ? `ជ្រើសរើសម៉ូតវិញ្ញាបនបត្រ (${CERTIFICATE_PRESETS.length})`
                          : `Change Certificate Template (${CERTIFICATE_PRESETS.length})`}
                      </span>
                    </div>
                    <button
                      type="button"
                      onClick={() => {
                        setIsTemplateMenuOpen(false);
                        onOpenVisualGallery();
                      }}
                      className="text-[10.5px] font-bold text-amber-600 hover:underline cursor-pointer"
                    >
                      {uiLang === 'km' ? 'ម៉ូតស៊ុម ១៧ →' : '17 Frames →'}
                    </button>
                  </div>

                  {/* Quick 6-Version Multi-Form Toggle (V1–V6) */}
                  <div className="grid grid-cols-6 gap-1">
                    {LAYOUT_TEMPLATE_VERSIONS.map((lt) => (
                      <button
                        key={lt.id}
                        type="button"
                        onClick={() => onChangeLayout(lt.id)}
                        className={`px-1.5 py-1.5 rounded-lg border text-center cursor-pointer transition-all font-khmer-sans ${
                          currentLayout === lt.id
                            ? 'border-amber-500 bg-amber-100/80 dark:bg-amber-950/50 text-amber-900 dark:text-amber-200 font-bold shadow-2xs'
                            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:border-amber-300'
                        }`}
                        title={lt.shortDesc}
                      >
                        <div className="text-[11px] font-mono font-extrabold text-amber-700 dark:text-amber-300">
                          {lt.versionTag}
                        </div>
                        <div className="text-[9.5px] leading-tight truncate">
                          {lt.label}
                        </div>
                      </button>
                    ))}
                  </div>

                  {/* Category Filter Pills + Style-Only Toggle */}
                  <div className="flex items-center justify-between gap-2 flex-wrap">
                    <div className="flex items-center gap-1 flex-wrap">
                      {[
                        { id: 'all', label: uiLang === 'km' ? 'ទាំងអស់' : 'All' },
                        { id: 'staff', label: uiLang === 'km' ? 'បុគ្គលិក' : 'Staff' },
                        { id: 'leader', label: uiLang === 'km' ? 'អ្នកដឹកនាំ' : 'Leader' },
                        { id: 'service', label: uiLang === 'km' ? 'សេវាកម្ម' : 'Service' },
                        { id: 'course', label: uiLang === 'km' ? 'វគ្គសិក្សា' : 'Course' },
                      ].map((cat) => (
                        <button
                          key={cat.id}
                          type="button"
                          onClick={() => setFilterCat(cat.id as AwardCategory | 'all')}
                          className={`px-2 py-0.5 rounded-md text-[10px] font-semibold cursor-pointer transition-colors font-khmer-sans ${
                            filterCat === cat.id
                              ? 'bg-amber-600 text-white'
                              : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'
                          }`}
                        >
                          {cat.label}
                        </button>
                      ))}
                    </div>

                    <label className="flex items-center gap-1 text-[10px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                      <input
                        type="checkbox"
                        checked={styleOnlyMode}
                        onChange={(e) => setStyleOnlyMode(e.target.checked)}
                        className="rounded text-amber-600"
                      />
                      <span className="font-khmer-sans">
                        {uiLang === 'km' ? 'រក្សាឈ្មោះដើម' : 'Keep my text'}
                      </span>
                    </label>
                  </div>
                </div>

                {/* Preset List */}
                <div className="max-h-80 overflow-y-auto p-2 space-y-1.5">
                  {filteredPresets.map((preset) => {
                    const isActive = preset.id === activePresetId;
                    return (
                      <button
                        key={preset.id}
                        type="button"
                        onClick={() => {
                          onSelectPreset(preset, { styleOnly: styleOnlyMode, keepLogo: true });
                          setIsTemplateMenuOpen(false);
                        }}
                        className={`w-full flex items-center gap-2.5 p-2 rounded-lg border text-left transition-all cursor-pointer ${
                          isActive
                            ? 'border-amber-500 bg-amber-50/90 dark:bg-amber-950/40 shadow-2xs'
                            : 'border-slate-100 dark:border-slate-800 hover:border-amber-300 hover:bg-slate-50 dark:hover:bg-slate-800/60'
                        }`}
                      >
                        {/* Color & Accent Swatch */}
                        <div
                          className="w-8 h-8 rounded-lg border-2 shrink-0 flex items-center justify-center shadow-2xs"
                          style={{
                            backgroundColor: preset.styling.primaryColor,
                            borderColor: preset.styling.accentColor,
                          }}
                        >
                          <span
                            className="w-2.5 h-2.5 rounded-full"
                            style={{ backgroundColor: preset.styling.accentColor }}
                          />
                        </div>

                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-1.5">
                            <span className="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">
                              {preset.certificateTitle}
                            </span>
                            <span className="text-[9px] uppercase font-bold px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 shrink-0">
                              {preset.category}
                            </span>
                            {preset.signatories.some((s) => /hr|human\s*resource|people/i.test(`${s.role} ${s.organization || ''}`)) && (
                              <span className="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 shrink-0">
                                HR
                              </span>
                            )}
                            {preset.showRecipientPhoto && (
                              <span className="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 shrink-0">
                                Photo
                              </span>
                            )}
                            {(preset.layoutTemplate === 'executive-verify' ||
                              preset.layoutTemplate === 'executive-verify-v2' ||
                              preset.layoutTemplate === 'executive-medal-ribbon' ||
                              preset.layoutTemplate === 'executive-imperial-sash') && (
                              <span className="text-[9px] font-bold px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 shrink-0">
                                Verify QR
                              </span>
                            )}
                          </div>
                          {preset.khmerTitle && (
                            <div className="text-[10.5px] font-khmer-sans text-amber-800 dark:text-amber-300 truncate">
                              {preset.khmerTitle}
                            </div>
                          )}
                          <div className="text-[9.5px] text-slate-500 dark:text-slate-400 truncate flex items-center gap-1.5">
                            <span className="font-mono font-bold text-amber-700 dark:text-amber-300">
                              {getLayoutMeta(preset.layoutTemplate).versionTag}
                            </span>
                            <span>·</span>
                            <span>{getLayoutMeta(preset.layoutTemplate).label}</span>
                            <span>·</span>
                            <span>{preset.styling.borderStyle}</span>
                          </div>
                        </div>

                        {isActive && <Check className="w-4 h-4 text-amber-600 shrink-0" />}
                      </button>
                    );
                  })}
                </div>
              </div>
            </>
          )}
        </div>
      </div>

      {/* Zone 2: Navigation Links */}
      <nav className="hidden 2xl:flex items-center gap-5 text-sm font-medium text-slate-600 dark:text-slate-300">
        <button
          onClick={() => onSelectCategory('staff')}
          className="text-amber-700 dark:text-amber-400 font-bold hover:text-amber-800 dark:hover:text-amber-300 transition-colors whitespace-nowrap shrink-0 cursor-pointer font-khmer-sans"
        >
          ★ {t.bestStaff}
        </button>
        <button
          onClick={() => onSelectCategory('leader')}
          className="hover:text-slate-900 dark:hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer font-khmer-sans"
        >
          {t.teamLeaders}
        </button>
        <button
          onClick={() => onSelectCategory('service')}
          className="hover:text-slate-900 dark:hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer font-khmer-sans"
        >
          {t.serviceAwards}
        </button>
        <button
          onClick={() => onSelectCategory('course')}
          className="hover:text-slate-900 dark:hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer font-khmer-sans"
        >
          {t.courseDiplomas}
        </button>
        <button
          onClick={onOpenDigitalHrModal}
          className="text-amber-700 dark:text-amber-400 font-bold hover:text-amber-800 dark:hover:text-amber-300 transition-colors whitespace-nowrap shrink-0 cursor-pointer flex items-center gap-1 font-khmer-sans"
        >
          <span>{t.digitalHr}</span>
        </button>
        <button
          onClick={onOpenBatchModal}
          className="hover:text-slate-900 dark:hover:text-white transition-colors whitespace-nowrap shrink-0 cursor-pointer font-khmer-sans"
        >
          {t.batchGenerator}
        </button>
      </nav>

      {/* Zone 3: Actions & Language Switcher */}
      <div className="flex items-center gap-2 shrink-0">
        {/* Khmer / English Toggle */}
        <button
          type="button"
          onClick={onToggleLang}
          className="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer whitespace-nowrap shrink-0"
          title="Switch Language / ប្តូរភាសា"
        >
          <Globe className="w-3.5 h-3.5 text-amber-600" />
          <span className="hidden md:inline">{uiLang === 'km' ? 'English' : '🇰🇭 ភាសាខ្មែរ'}</span>
          <span className="md:hidden">{uiLang === 'km' ? 'EN' : 'KM'}</span>
        </button>

        {/* Direct 1-Click Download Full Project (.zip) */}
        <button
          type="button"
          onClick={handleDirectDownloadProjectZip}
          disabled={isDownloadingZip}
          className="flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-lg border border-emerald-500/40 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 transition-colors cursor-pointer whitespace-nowrap shrink-0 disabled:opacity-60"
          title={uiLang === 'km' ? 'ទាញយកកូដគម្រោងពេញលេញ (.zip) ដោយចុចតែម្តង' : 'Download full runnable project (.zip) in 1 click'}
        >
          {isDownloadingZip ? (
            <Loader2 className="w-4 h-4 text-emerald-600 animate-spin" />
          ) : zipDoneCount !== null ? (
            <Check className="w-4 h-4 text-emerald-600" />
          ) : (
            <FolderArchive className="w-4 h-4 text-emerald-600" />
          )}
          <span className="hidden xl:inline font-khmer-sans">
            {isDownloadingZip
              ? (uiLang === 'km' ? 'កំពុងបង្កើត ZIP...' : 'Zipping...')
              : zipDoneCount !== null
                ? (uiLang === 'km' ? `ទាញយករួច (${zipDoneCount})` : `Downloaded (${zipDoneCount})`)
                : t.downloadFullProject}
          </span>
          <span className="xl:hidden">
            {zipDoneCount !== null ? 'ZIP ✓' : 'ZIP'}
          </span>
        </button>

        {/* Merge to hr-ky-admin / Windows & Laravel Modal Button */}
        <button
          type="button"
          onClick={onOpenWindowsModal}
          className="flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-lg border border-amber-500/35 bg-amber-500/10 hover:bg-amber-500/20 text-amber-800 dark:text-amber-300 transition-colors cursor-pointer whitespace-nowrap shrink-0"
          title={uiLang === 'km' ? 'ភ្ជាប់ចូលគម្រោង hr-ky-admin ឬ Laravel / Windows' : 'Merge into hr-ky-admin, existing React project, or Laravel'}
        >
          <GitMerge className="w-4 h-4 text-amber-600" />
          <span className="hidden sm:inline font-khmer-sans">{t.mergeToProject}</span>
          <span className="sm:hidden">Merge</span>
        </button>

        {/* Primary Export Certificate */}
        <button
          type="button"
          onClick={onOpenExportModal}
          className="flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 active:bg-amber-800 dark:bg-amber-600 dark:hover:bg-amber-500 rounded-lg shadow-sm transition-colors whitespace-nowrap shrink-0 cursor-pointer"
        >
          <Download className="w-4 h-4" />
          <span>{t.exportCertificate}</span>
        </button>
      </div>
    </header>
  );
};
