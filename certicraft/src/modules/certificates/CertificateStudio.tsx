import React, { useCallback, useEffect, useRef, useState } from 'react';
import { BorderStyleOption, CertificateData, LayoutTemplateOption } from './types/certificate';
import { CERTIFICATE_PRESETS } from './data/presets';
import { FRAME_STYLES } from './data/frameStyles';
import { LAYOUT_TEMPLATE_VERSIONS } from './data/layoutTemplates';
import { Header } from './components/Header';
import { EditorSidebar } from './components/EditorSidebar';
import { CertificateRenderer } from './components/CertificateRenderer';
import { SmartCitationModal } from './components/SmartCitationModal';
import { SignatureModal } from './components/SignatureModal';
import { ExportModal } from './components/ExportModal';
import { VerifyModal } from './components/VerifyModal';
import { BatchGeneratorModal } from './components/BatchGeneratorModal';
import { DigitalHrModal } from './components/DigitalHrModal';
import { ExportWindowsModal } from './components/ExportWindowsModal';
import {
  LayoutModelSaveModal,
  LayoutModelSavePayload,
  LayoutModelSaveResult,
} from './components/LayoutModelSaveModal';
import { AppLanguage, TRANSLATIONS } from './i18n/translations';
import {
  ZoomIn,
  ZoomOut,
  Maximize2,
  Sparkles,
  Users,
  ShieldCheck,
  RotateCw,
  Printer,
  Clock,
  Frame,
  Layers,
  ChevronDown,
  Check,
  Camera,
  Save,
} from 'lucide-react';
import { printCertificate } from './utils/exportUtils';
import { HrAdminEmployeeInput, mapHrEmployeeToCertificate } from './utils/hrIntegration';

const DEFAULT_DRAFT_STORAGE_KEY = 'certicraft_saved_draft_v1';

export interface CertificateStudioProps {
  /** Embed cleanly inside an existing dashboard container such as hr-ky-admin */
  embedded?: boolean;
  /** Optional wrapper classes */
  className?: string;
  /** Initial interface language ('km' or 'en') */
  defaultLanguage?: AppLanguage;
  /** Optional preset id from CERTIFICATE_PRESETS */
  presetId?: string;
  /** Partial override applied to the initial certificate */
  initialData?: Partial<CertificateData>;
  /** Selected employee payload from hr-ky-admin or another HRMS */
  employee?: HrAdminEmployeeInput | null;
  /** Employee roster from hr-ky-admin for KNEAYERNG HRMS Picker & Batch Generator */
  externalEmployees?: HrAdminEmployeeInput[];
  /** Custom localStorage draft key when running multiple modules */
  storageKey?: string;
  /** Optionally hide the studio header when embedded inside an admin shell */
  hideHeader?: boolean;
  /** Optional back callback rendered in header when embedded in another app */
  onBack?: () => void;
  /** Save callback invoked when user clicks Save (e.g. persist to hr-ky-admin API) */
  onSaveCertificate?: (data: CertificateData) => void | Promise<void>;
  /** Save the current layout as a វិញ្ញាបនបត្រ គំរូ (Layout Model) */
  onSaveLayoutModel?: (
    payload: LayoutModelSavePayload
  ) => Promise<LayoutModelSaveResult>;
  /** Certificate templates list URL for the “view layout models” link */
  templatesUrl?: string;
  /** Live change listener */
  onChange?: (data: CertificateData) => void;
}

function mergeCertData(base: CertificateData, override?: Partial<CertificateData>): CertificateData {
  if (!override) return base;
  return {
    ...base,
    ...override,
    issuer: {
      ...base.issuer,
      ...(override.issuer || {}),
      customLogoUrl: override.issuer?.customLogoUrl || base.issuer?.customLogoUrl || '/assets/aistudio/unnamed_521b71dc.jpg',
    },
    digitalHr: {
      ...base.digitalHr,
      ...(override.digitalHr || {}),
      employeeId: override.digitalHr?.employeeId || base.digitalHr?.employeeId || 'EMP-KY-TL-2026-02',
    },
    styling: {
      ...base.styling,
      ...(override.styling || {}),
    },
    seal: {
      ...base.seal,
      ...(override.seal || {}),
    },
    signatories: (override.signatories && override.signatories.length > 0)
      ? override.signatories
      : base.signatories,
  };
}

export const CertificateStudio: React.FC<CertificateStudioProps> = ({
  embedded = false,
  className = '',
  defaultLanguage = 'km',
  presetId,
  initialData,
  employee,
  externalEmployees,
  storageKey = DEFAULT_DRAFT_STORAGE_KEY,
  hideHeader = false,
  onBack,
  onSaveCertificate,
  onSaveLayoutModel,
  templatesUrl,
  onChange,
}) => {
  const [uiLang, setUiLang] = useState<AppLanguage>(defaultLanguage);
  const t = TRANSLATIONS[uiLang];

  const [certificateData, setCertificateData] = useState<CertificateData>(() => {
    const chosenPreset =
      (presetId && CERTIFICATE_PRESETS.find((p) => p.id === presetId)) || CERTIFICATE_PRESETS[0];

    // Priority 1: If explicit employee is provided
    if (employee) {
      const mapped = mapHrEmployeeToCertificate(employee, chosenPreset);
      return mergeCertData(mapped, initialData);
    }

    // Priority 2: If initialData is provided from Laravel (default layout model or selected template)
    if (initialData) {
      return mergeCertData(chosenPreset, initialData);
    }

    // Priority 3: Fall back to localStorage draft if standalone
    try {
      const saved = localStorage.getItem(storageKey);
      if (saved) {
        const parsed = JSON.parse(saved) as CertificateData;
        if (parsed && parsed.id && parsed.issuer) {
          return mergeCertData(chosenPreset, parsed);
        }
      }
    } catch {
      // ignore storage errors
    }

    return chosenPreset;
  });

  // Sync whenever initialData updates from parent
  useEffect(() => {
    if (!initialData) return;
    setCertificateData((prev) => mergeCertData(prev, initialData));
  }, [initialData]);

  // Sync whenever an external `employee` prop changes in hr-ky-admin
  useEffect(() => {
    if (!employee) return;
    setCertificateData((prev) => mapHrEmployeeToCertificate(employee, prev));
  }, [employee]);

  // Notify parent when data updates
  useEffect(() => {
    onChange?.(certificateData);
  }, [certificateData, onChange]);

  const [draftSavedNotice, setDraftSavedNotice] = useState(false);

  const handleSaveDraft = async () => {
    try {
      localStorage.setItem(storageKey, JSON.stringify(certificateData));
      if (onSaveCertificate) {
        await onSaveCertificate(certificateData);
      }
      setDraftSavedNotice(true);
      setTimeout(() => setDraftSavedNotice(false), 2200);
    } catch (err) {
      console.error('Failed to save certificate:', err);
    }
  };

  // Zoom / scale control & auto-fit for preview stage
  const stageRef = useRef<HTMLElement | null>(null);
  const [autoFit, setAutoFit] = useState<boolean>(true);
  const [zoomScale, setZoomScale] = useState<number>(0.72);
  const [enableShimmer, setEnableShimmer] = useState<boolean>(false);

  const computeFitScale = useCallback(() => {
    const el = stageRef.current;
    if (!el) return 0.72;
    const isLandscape = certificateData.orientation === 'landscape';
    const targetW = isLandscape ? 1123 : 794;
    const targetH = isLandscape ? 794 : 1123;
    const availW = Math.max(240, el.clientWidth - 48);
    const availH = Math.max(240, el.clientHeight - 48);
    const fit = Math.min(availW / targetW, availH / targetH, 1.0);
    return Math.max(0.35, Math.round(fit * 100) / 100);
  }, [certificateData.orientation]);

  useEffect(() => {
    if (!autoFit) return;
    const updateFit = () => setZoomScale(computeFitScale());
    updateFit();
    const el = stageRef.current;
    if (!el || typeof ResizeObserver === 'undefined') {
      window.addEventListener('resize', updateFit);
      return () => window.removeEventListener('resize', updateFit);
    }
    const observer = new ResizeObserver(updateFit);
    observer.observe(el);
    return () => observer.disconnect();
  }, [autoFit, computeFitScale]);

  // Modals state
  const [isCitationModalOpen, setIsCitationModalOpen] = useState(false);
  const [isSignatureModalOpen, setIsSignatureModalOpen] = useState(false);
  const [activeSignatoryIndex, setActiveSignatoryIndex] = useState<number>(0);
  const [isExportModalOpen, setIsExportModalOpen] = useState(false);
  const [isVerifyModalOpen, setIsVerifyModalOpen] = useState(false);
  const [isBatchModalOpen, setIsBatchModalOpen] = useState(false);
  const [isDigitalHrModalOpen, setIsDigitalHrModalOpen] = useState(false);
  const [isWindowsModalOpen, setIsWindowsModalOpen] = useState(false);
  const [isLayoutModelModalOpen, setIsLayoutModelModalOpen] = useState(false);

  // Sidebar navigation tracking
  const [sidebarTab, setSidebarTab] = useState<'presets' | 'content' | 'styling' | 'hr' | 'signatures'>('presets');
  const [isFrameMenuOpen, setIsFrameMenuOpen] = useState(false);

  const activeFrameMeta =
    FRAME_STYLES.find((f) => f.id === certificateData.styling.borderStyle) || FRAME_STYLES[0];

  const handleSelectFrame = (borderStyle: BorderStyleOption) => {
    setCertificateData((prev) => ({
      ...prev,
      styling: {
        ...prev.styling,
        borderStyle,
      },
    }));
    setIsFrameMenuOpen(false);
  };

  const handleSelectHeaderPreset = (
    preset: CertificateData,
    options?: { styleOnly?: boolean; keepLogo?: boolean }
  ) => {
    setCertificateData((prev) => {
      const hasUploadedLogo =
        Boolean(prev.issuer.customLogoUrl) &&
        prev.issuer.customLogoUrl?.startsWith('data:');

      if (options?.styleOnly) {
        return {
          ...prev,
          id: preset.id,
          theme: preset.theme,
          layoutTemplate: preset.layoutTemplate || 'classic',
          recipientPhotoUrl: prev.recipientPhotoUrl,
          showRecipientPhoto: prev.showRecipientPhoto,
          recipientPhotoPosition: prev.recipientPhotoPosition,
          recipientPhotoShape: prev.recipientPhotoShape,
          recipientPhotoSize: prev.recipientPhotoSize,
          recipientPhotoZoom: prev.recipientPhotoZoom,
          recipientPhotoOffsetX: prev.recipientPhotoOffsetX,
          recipientPhotoOffsetY: prev.recipientPhotoOffsetY,
          seal: {
            ...prev.seal,
            style: preset.seal.style,
            ribbonColor: preset.seal.ribbonColor,
          },
          styling: {
            ...preset.styling,
            fieldStyles: prev.styling.fieldStyles,
            logoScale: prev.styling.logoScale,
          },
        };
      }

      if (options?.keepLogo && hasUploadedLogo) {
        return {
          ...preset,
          issuer: {
            ...preset.issuer,
            logoType: 'custom',
            customLogoUrl: prev.issuer.customLogoUrl,
          },
        };
      }

      return { ...preset };
    });
  };

  const handleChangeLayout = (layoutTemplate: LayoutTemplateOption) => {
    setCertificateData((prev) => {
      if (layoutTemplate === 'executive-verify') {
        return {
          ...prev,
          layoutTemplate,
          showQrCode: true,
          showRecipientPhoto: prev.showRecipientPhoto ?? true,
          recipientPhotoShape: 'rounded-portrait',
          issueDate: prev.issueDate || '15 November 2026',
        };
      }
      if (
        layoutTemplate === 'executive-verify-v2' ||
        layoutTemplate === 'executive-medal-ribbon' ||
        layoutTemplate === 'executive-imperial-sash'
      ) {
        return {
          ...prev,
          layoutTemplate,
          showQrCode: true,
          showRecipientPhoto: prev.showRecipientPhoto ?? true,
          recipientPhotoShape: layoutTemplate === 'executive-verify-v2' ? 'circle' : 'rounded-portrait',
          issueDate: prev.issueDate || '15 November 2026',
          credentialId: prev.credentialId || 'KY-CERT-2026-00045',
        };
      }
      return {
        ...prev,
        layoutTemplate,
      };
    });
  };

  const toggleLanguage = () => {
    setUiLang((prev) => (prev === 'km' ? 'en' : 'km'));
  };

  const handleQuickCategory = (cat: 'staff' | 'course' | 'service' | 'leader' | 'all') => {
    if (cat === 'all') return;
    const match = CERTIFICATE_PRESETS.find((p) => p.category === cat);
    if (match) {
      setCertificateData(match);
      setSidebarTab('presets');
    }
  };

  const handleOpenSignatureModal = (idx: number) => {
    setActiveSignatoryIndex(idx);
    setIsSignatureModalOpen(true);
  };

  const handleSaveSignatory = (idx: number, updated: CertificateData['signatories'][0]) => {
    const updatedList = [...certificateData.signatories];
    updatedList[idx] = updated;
    setCertificateData({ ...certificateData, signatories: updatedList });
  };

  const handleEditField = (fieldKey: string) => {
    if (fieldKey === 'citation') {
      setIsCitationModalOpen(true);
    } else if (fieldKey === 'seal') {
      setSidebarTab('signatures');
    } else if (fieldKey === 'dividerOrnament') {
      setSidebarTab('styling');
    } else if (fieldKey === 'digitalHr') {
      setIsDigitalHrModalOpen(true);
    } else {
      setSidebarTab('content');
    }
  };

  const shellDimensions = embedded
    ? 'h-full min-h-[760px] w-full'
    : 'h-screen w-screen';

  return (
    <div
      className={`print-app-shell flex flex-col ${shellDimensions} overflow-hidden bg-slate-100 dark:bg-slate-950 font-sans-clean select-none ${className}`}
    >
      {/* Dynamic A4 Print Page Orientation */}
      <style media="print">{`@page { size: A4 ${certificateData.orientation}; margin: 0mm; }`}</style>

      {!hideHeader && (
        <Header
          activePresetId={certificateData.id}
          currentLayout={certificateData.layoutTemplate || 'classic'}
          currentLogoUrl={certificateData.issuer.customLogoUrl}
          onSelectPreset={handleSelectHeaderPreset}
          onChangeLayout={handleChangeLayout}
          onOpenVisualGallery={() => setSidebarTab('styling')}
          onSelectCategory={handleQuickCategory}
          onOpenDigitalHrModal={() => setIsDigitalHrModalOpen(true)}
          onOpenBatchModal={() => setIsBatchModalOpen(true)}
          onOpenVerifyModal={() => setIsVerifyModalOpen(true)}
          onOpenExportModal={() => setIsExportModalOpen(true)}
          onOpenWindowsModal={() => setIsWindowsModalOpen(true)}
          onBack={onBack}
          uiLang={uiLang}
          onToggleLang={toggleLanguage}
        />
      )}

      {/* Workspace Secondary Utility Toolbar */}
      <div className="no-print flex flex-wrap items-center justify-between gap-2 px-4 py-1.5 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-xs shrink-0">
        {/* Category Tabs */}
        <div className="flex items-center gap-1 p-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg shrink-0">
          <button
            type="button"
            onClick={() => handleQuickCategory('staff')}
            className={`px-2.5 py-1 rounded-md font-medium transition-colors cursor-pointer font-khmer-sans whitespace-nowrap shrink-0 ${
              certificateData.category === 'staff'
                ? 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
            }`}
          >
            ★ {t.bestStaff}
          </button>
          <button
            type="button"
            onClick={() => handleQuickCategory('leader')}
            className={`px-2.5 py-1 rounded-md font-medium transition-colors cursor-pointer font-khmer-sans whitespace-nowrap shrink-0 ${
              certificateData.category === 'leader'
                ? 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
            }`}
          >
            {t.teamLeaders}
          </button>
          <button
            type="button"
            onClick={() => handleQuickCategory('service')}
            className={`px-2.5 py-1 rounded-md font-medium transition-colors cursor-pointer font-khmer-sans whitespace-nowrap shrink-0 ${
              certificateData.category === 'service'
                ? 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
            }`}
          >
            {t.serviceAwards}
          </button>
          <button
            type="button"
            onClick={() => handleQuickCategory('course')}
            className={`px-2.5 py-1 rounded-md font-medium transition-colors cursor-pointer font-khmer-sans whitespace-nowrap shrink-0 ${
              certificateData.category === 'course'
                ? 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
            }`}
          >
            {t.courseDiplomas}
          </button>
        </div>

        {/* Center: Quick action buttons */}
        <div className="hidden md:flex flex-wrap items-center gap-1.5 relative">
          {/* Quick Multi-Form Version Switcher (V1 | V2 | V3 | V4 | V5 | V6) */}
          <div
            className="flex items-center gap-0.5 p-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shrink-0"
            title={uiLang === 'km' ? 'ប្តូរទម្រង់ V1-V6 ភ្លាមៗ' : 'Switch Form Template Version (V1–V6)'}
          >
            {LAYOUT_TEMPLATE_VERSIONS.map((lt) => {
              const isActive = (certificateData.layoutTemplate || 'classic') === lt.id;
              return (
                <button
                  key={lt.id}
                  type="button"
                  onClick={() => handleChangeLayout(lt.id)}
                  className={`px-2 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer flex items-center gap-1 whitespace-nowrap shrink-0 ${
                    isActive
                      ? 'bg-amber-600 text-white shadow-2xs'
                      : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900'
                  }`}
                  title={`${lt.versionTag}: ${lt.label} — ${lt.shortDesc}`}
                >
                  <span>{lt.versionTag}</span>
                </button>
              );
            })}
          </div>

          {/* Quick Frame Style Switcher Dropdown */}
          <div className="relative shrink-0">
            <button
              type="button"
              onClick={() => setIsFrameMenuOpen((prev) => !prev)}
              className="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold text-slate-800 dark:text-slate-100 bg-slate-100 dark:bg-slate-800 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-slate-300 dark:border-slate-700 hover:border-amber-400 rounded-md transition-colors cursor-pointer whitespace-nowrap shrink-0"
              title="Switch Certificate Frame Style"
            >
              <span
                className="w-2.5 h-2.5 rounded-full border border-amber-400 shrink-0"
                style={{ backgroundColor: activeFrameMeta.badgeColor }}
              />
              <Frame className="w-3.5 h-3.5 text-amber-600 shrink-0" />
              <span className="max-w-[115px] truncate">
                {uiLang === 'km' ? activeFrameMeta.khmerLabel : activeFrameMeta.label}
              </span>
              <ChevronDown className="w-3 h-3 opacity-65 shrink-0" />
            </button>

            {isFrameMenuOpen && (
              <>
                <div
                  className="fixed inset-0 z-30"
                  onClick={() => setIsFrameMenuOpen(false)}
                />
                <div className="absolute left-0 top-full mt-1.5 w-72 max-h-96 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl p-2 z-40 space-y-1">
                  <div className="px-2 py-1 flex items-center justify-between border-b border-slate-100 dark:border-slate-800 mb-1">
                    <span className="text-[10.5px] font-bold uppercase tracking-wider text-slate-500">
                      {uiLang === 'km'
                        ? `ជ្រើសរើសម៉ូតស៊ុម (${FRAME_STYLES.length} ម៉ូត)`
                        : `Select Frame Style (${FRAME_STYLES.length})`}
                    </span>
                    <button
                      type="button"
                      onClick={() => {
                        setIsFrameMenuOpen(false);
                        setSidebarTab('styling');
                      }}
                      className="text-[10px] font-bold text-amber-600 hover:underline cursor-pointer"
                    >
                      {uiLang === 'km' ? 'មើលរូបភាពគំរូ' : 'Visual Gallery →'}
                    </button>
                  </div>

                  {FRAME_STYLES.map((frame) => {
                    const isActive = certificateData.styling.borderStyle === frame.id;
                    return (
                      <button
                        key={frame.id}
                        type="button"
                        onClick={() => handleSelectFrame(frame.id)}
                        className={`w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-left transition-colors cursor-pointer ${
                          isActive
                            ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 font-bold'
                            : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200'
                        }`}
                      >
                        <span
                          className="w-4 h-4 rounded-sm border shrink-0 shadow-2xs"
                          style={{
                            backgroundColor: frame.badgeColor,
                            borderColor: frame.secondaryColor,
                          }}
                        />
                        <div className="flex-1 min-w-0">
                          <div className="text-xs truncate">{frame.label}</div>
                          <div className="text-[10px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate">
                            {frame.khmerLabel}
                          </div>
                        </div>
                        {isActive && <Check className="w-3.5 h-3.5 text-amber-600 shrink-0" />}
                      </button>
                    );
                  })}
                </div>
              </>
            )}
          </div>

          <button
            type="button"
            onClick={() => setIsDigitalHrModalOpen(true)}
            className="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold text-amber-800 dark:text-amber-200 bg-gradient-to-r from-amber-50 to-amber-100 dark:from-amber-950/60 dark:to-amber-900/40 border border-amber-300 dark:border-amber-700/80 rounded-md transition-colors hover:border-amber-400 cursor-pointer shadow-2xs whitespace-nowrap shrink-0"
            title="Open KNEAYERNG HRMS & Training Hours Hub"
          >
            <Clock className="w-3.5 h-3.5 text-amber-600 shrink-0" />
            <span>HR: {certificateData.digitalHr?.trainingHours || 60}h</span>
          </button>

          <button
            type="button"
            onClick={() => setIsCitationModalOpen(true)}
            className="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100/70 border border-amber-200 dark:border-amber-800/60 rounded-md transition-colors cursor-pointer whitespace-nowrap shrink-0 font-khmer-sans"
            title="Open Citation Writer"
          >
            <Sparkles className="w-3.5 h-3.5 shrink-0" />
            <span>{uiLang === 'km' ? 'សេចក្តីសរសើរ' : 'Citation'}</span>
          </button>

          <button
            type="button"
            onClick={() => setIsBatchModalOpen(true)}
            className="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md transition-colors cursor-pointer whitespace-nowrap shrink-0 font-khmer-sans"
            title="Open Batch Roster & Presets"
          >
            <Users className="w-3.5 h-3.5 text-blue-600 shrink-0" />
            <span>
              {uiLang === 'km' ? 'បញ្ជីឈ្មោះ' : 'Roster'} ({externalEmployees?.length ? externalEmployees.length : CERTIFICATE_PRESETS.length})
            </span>
          </button>

          <button
            type="button"
            onClick={() => setIsVerifyModalOpen(true)}
            className="flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md transition-colors cursor-pointer whitespace-nowrap shrink-0 font-khmer-sans"
            title="Verify Certificate QR"
          >
            <ShieldCheck className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
            <span>{uiLang === 'km' ? 'ផ្ទៀងផ្ទាត់ QR' : 'QR Verify'}</span>
          </button>

          <label
            className={`flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-md border transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
              certificateData.showRecipientPhoto || certificateData.recipientPhotoUrl
                ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300'
                : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
            }`}
            title={uiLang === 'km' ? 'បញ្ចូល ឬប្តូររូបភាពប្រវត្តិរូបបុគ្គលិក' : 'Upload or Update Staff Profile Image'}
          >
            <Camera className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
            <span className="font-khmer-sans">
              {uiLang === 'km' ? 'រូបភាព' : 'Photo'}
            </span>
            <input
              type="file"
              accept="image/*"
              className="hidden"
              onChange={(e) => {
                const file = e.target.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                  const dataUrl = ev.target?.result as string;
                  if (dataUrl) {
                    setCertificateData((prev) => ({
                      ...prev,
                      recipientPhotoUrl: dataUrl,
                      showRecipientPhoto: true,
                    }));
                    setSidebarTab('content');
                  }
                };
                reader.readAsDataURL(file);
                e.target.value = '';
              }}
            />
          </label>
        </div>

        {/* Right: Zoom controls and orientation */}
        <div className="flex items-center gap-1.5 shrink-0">
          <div className="flex items-center border border-slate-200 dark:border-slate-700 rounded-md overflow-hidden bg-slate-50 dark:bg-slate-800 shrink-0">
            <button
              type="button"
              onClick={() => {
                setAutoFit(false);
                setZoomScale((prev) => Math.max(0.35, Math.round((prev - 0.1) * 100) / 100));
              }}
              className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 cursor-pointer"
              title="Zoom out"
            >
              <ZoomOut className="w-3.5 h-3.5" />
            </button>
            <span className="px-2 font-mono text-[11px] text-slate-700 dark:text-slate-300 tabular-nums whitespace-nowrap">
              {Math.round(zoomScale * 100)}%
            </span>
            <button
              type="button"
              onClick={() => {
                setAutoFit(false);
                setZoomScale((prev) => Math.min(1.4, Math.round((prev + 0.1) * 100) / 100));
              }}
              className="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 cursor-pointer"
              title="Zoom in"
            >
              <ZoomIn className="w-3.5 h-3.5" />
            </button>
            <button
              type="button"
              onClick={() => {
                setAutoFit(true);
                setZoomScale(computeFitScale());
              }}
              className={`px-2 py-1.5 text-[10.5px] font-bold border-l border-slate-200 dark:border-slate-700 cursor-pointer flex items-center gap-1 whitespace-nowrap ${
                autoFit
                  ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300'
                  : 'hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300'
              }`}
              title="Auto-fit full A4 certificate to screen"
            >
              <Maximize2 className="w-3 h-3" />
              <span className="font-khmer-sans">{uiLang === 'km' ? 'សមមាត្រ' : 'Fit'}</span>
            </button>
          </div>

          <button
            type="button"
            onClick={() =>
              setCertificateData((prev) => ({
                ...prev,
                orientation: prev.orientation === 'landscape' ? 'portrait' : 'landscape',
              }))
            }
            className="flex items-center gap-1 px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer whitespace-nowrap shrink-0 font-khmer-sans"
            title="Toggle landscape / portrait"
          >
            <RotateCw className="w-3.5 h-3.5 shrink-0" />
            <span className="capitalize">
              {uiLang === 'km'
                ? certificateData.orientation === 'landscape'
                  ? 'ផ្តេក'
                  : 'បញ្ឈរ'
                : certificateData.orientation}
            </span>
          </button>

          <button
            type="button"
            onClick={() => setEnableShimmer((prev) => !prev)}
            className={`flex items-center gap-1.5 px-2.5 py-1.5 border rounded-md cursor-pointer transition-colors whitespace-nowrap shrink-0 font-khmer-sans ${
              enableShimmer
                ? 'border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-semibold'
                : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
            }`}
            title="Toggle interactive gold foil light reflection"
          >
            <Sparkles className="w-3.5 h-3.5 text-amber-500 shrink-0" />
            <span>{uiLang === 'km' ? 'ពន្លឺចាំង' : 'Gleam'}</span>
          </button>

          {onSaveLayoutModel && (
            <button
              type="button"
              onClick={() => setIsLayoutModelModalOpen(true)}
              className="flex items-center gap-1.5 px-2.5 py-1.5 border border-amber-300 dark:border-amber-700 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-900/50 cursor-pointer transition-colors whitespace-nowrap shrink-0 font-bold"
              title={
                uiLang === 'km'
                  ? 'រក្សាទុក layout នេះជា វិញ្ញាបនបត្រ គំរូ'
                  : 'Save this layout to វិញ្ញាបនបត្រ គំរូ (Layout Models)'
              }
            >
              <Layers className="w-3.5 h-3.5 text-amber-600 shrink-0" />
              <span>{uiLang === 'km' ? 'គំរូ' : 'Layout'}</span>
            </button>
          )}

          <button
            type="button"
            onClick={handleSaveDraft}
            className={`flex items-center gap-1.5 px-2.5 py-1.5 border rounded-md cursor-pointer transition-colors whitespace-nowrap shrink-0 ${
              draftSavedNotice
                ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold'
                : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
            }`}
            title={uiLang === 'km' ? 'រក្សាទុកពង្រាង (Save Draft)' : 'Save Certificate Draft'}
          >
            {draftSavedNotice ? <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0" /> : <Save className="w-3.5 h-3.5 text-amber-600 shrink-0" />}
            <span>{draftSavedNotice ? (uiLang === 'km' ? 'រក្សាទុក!' : 'Saved!') : (uiLang === 'km' ? 'រក្សាទុក' : 'Save')}</span>
          </button>

          <button
            type="button"
            onClick={printCertificate}
            className="flex items-center gap-1 px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer whitespace-nowrap shrink-0"
            title="Direct Print A4"
          >
            <Printer className="w-3.5 h-3.5 shrink-0" />
          </button>
        </div>
      </div>

      {/* Main Workspace Body: Sidebar + Stage */}
      <div className="print-main-body flex-1 flex overflow-hidden">
        <EditorSidebar
          data={certificateData}
          onChange={setCertificateData}
          onOpenCitationModal={() => setIsCitationModalOpen(true)}
          onOpenSignatureModal={handleOpenSignatureModal}
          onOpenDigitalHrModal={() => setIsDigitalHrModalOpen(true)}
          onOpenWindowsModal={() => setIsWindowsModalOpen(true)}
          externalEmployees={externalEmployees}
          activeTab={sidebarTab}
          onTabChange={setSidebarTab}
          uiLang={uiLang}
        />

        <main
          ref={stageRef}
          className="print-stage flex-1 overflow-auto p-6 flex relative bg-slate-200/70 dark:bg-slate-950/85"
        >
          <div className="print-stage-inner m-auto shrink-0">
            <CertificateRenderer
              data={certificateData}
              scale={zoomScale}
              enableShimmer={enableShimmer}
              onEditField={handleEditField}
              onOpenSignatureModal={handleOpenSignatureModal}
              onOpenVerifyModal={() => setIsVerifyModalOpen(true)}
              onUpdateRecipientPhoto={(dataUrl) =>
                setCertificateData((prev) => ({
                  ...prev,
                  recipientPhotoUrl: dataUrl,
                  showRecipientPhoto: true,
                }))
              }
              onUpdatePhotoFrameOffset={(offsetX, offsetY) =>
                setCertificateData((prev) => ({
                  ...prev,
                  recipientPhotoFrameOffsetX: offsetX,
                  recipientPhotoFrameOffsetY: offsetY,
                }))
              }
            />
          </div>
        </main>
      </div>

      {/* Modals */}
      <DigitalHrModal
        isOpen={isDigitalHrModalOpen}
        onClose={() => setIsDigitalHrModalOpen(false)}
        currentData={certificateData}
        externalEmployees={externalEmployees}
        onUpdateDigitalHr={(record) => {
          setCertificateData((prev) => ({
            ...prev,
            digitalHr: record,
          }));
        }}
        onSelectEmployee={(name, role, record, extra) => {
          setCertificateData((prev) => ({
            ...prev,
            recipientName: name,
            recipientTitle: role,
            khmerRecipientName: extra?.khmerName || prev.khmerRecipientName,
            recipientPhotoUrl: extra?.photoUrl || prev.recipientPhotoUrl,
            showRecipientPhoto: extra?.photoUrl ? true : prev.showRecipientPhoto,
            credentialId: extra?.credentialId || prev.credentialId,
            digitalHr: record,
          }));
        }}
      />

      <SmartCitationModal
        isOpen={isCitationModalOpen}
        onClose={() => setIsCitationModalOpen(false)}
        category={certificateData.category}
        recipientName={certificateData.recipientName}
        courseOrAchievement={certificateData.courseOrAchievementTitle}
        onApplyCitation={(citation) => {
          setCertificateData((prev) => ({ ...prev, citation }));
        }}
      />

      <SignatureModal
        isOpen={isSignatureModalOpen}
        onClose={() => setIsSignatureModalOpen(false)}
        signatory={certificateData.signatories[activeSignatoryIndex] || null}
        signatoryIndex={activeSignatoryIndex}
        onSaveSignatory={handleSaveSignatory}
      />

      <ExportModal
        isOpen={isExportModalOpen}
        onClose={() => setIsExportModalOpen(false)}
        data={certificateData}
      />

      <VerifyModal
        isOpen={isVerifyModalOpen}
        onClose={() => setIsVerifyModalOpen(false)}
        data={certificateData}
      />

      <BatchGeneratorModal
        isOpen={isBatchModalOpen}
        onClose={() => setIsBatchModalOpen(false)}
        baseData={certificateData}
        externalEmployees={externalEmployees}
        onSelectRecipient={(name, title) => {
          setCertificateData((prev) => ({
            ...prev,
            recipientName: name,
            recipientTitle: title || prev.recipientTitle,
          }));
        }}
      />

      {onSaveLayoutModel && (
        <LayoutModelSaveModal
          isOpen={isLayoutModelModalOpen}
          onClose={() => setIsLayoutModelModalOpen(false)}
          data={certificateData}
          uiLang={uiLang}
          templatesUrl={templatesUrl}
          onSave={onSaveLayoutModel}
          onSaved={(template) => {
            if (!template) return;
            setCertificateData((prev) => ({
              ...prev,
              ...((template as any).template_code || (template as any).templateCode
                ? {
                    templateCode:
                      (template as any).template_code || (template as any).templateCode,
                  }
                : {}),
              ...((template as any).title ? { templateTitle: (template as any).title } : {}),
              ...((template as any).khmer_title
                ? { templateKhmerTitle: (template as any).khmer_title }
                : {}),
            } as CertificateData));
          }}
        />
      )}

      <ExportWindowsModal
        isOpen={isWindowsModalOpen}
        onClose={() => setIsWindowsModalOpen(false)}
        uiLang={uiLang}
      />
    </div>
  );
};

export default CertificateStudio;
