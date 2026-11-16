import React, { useEffect, useMemo, useState } from 'react';
import {
  Layers,
  FileEdit,
  Palette,
  Award,
  Sparkles,
  PenTool,
  Upload,
  Check,
  Clock,
  Frame,
  Type,
  RotateCcw,
  Camera,
  User,
  Plus,
  Trash2,
  Search,
  RefreshCw,
  QrCode,
  Eye,
  EyeOff,
  Sliders,
} from 'lucide-react';
import {
  CertificateData,
  CertificateVisibility,
  AwardCategory,
  BorderStyleOption,
  SealStyleOption,
  FontStyleOption,
  DigitalHrRecord,
  FieldTypographyKey,
  FieldTypographyStyle,
  LogoOrnamentStyle,
  DividerOrnamentStyle,
  BannerStyleOption,
  WatermarkStyleOption,
} from '../types/certificate';
import { CERTIFICATE_PRESETS } from '../data/presets';
import { LAYOUT_TEMPLATE_VERSIONS, getLayoutMeta } from '../data/layoutTemplates';
import { FRAME_STYLES } from '../data/frameStyles';
import {
  LOGO_ORNAMENT_STYLES,
  DIVIDER_ORNAMENT_STYLES,
  BANNER_RIBBON_STYLES,
  WATERMARK_STYLES,
  SEAL_STYLE_CATALOG,
} from '../data/ornamentStyles';
import { CertificateBorders } from './CertificateBorders';
import { LogoFlankingOrnament, LogoDividerOrnament } from './LogoOrnaments';
import { PresentationBannerRibbon, WatermarkPatternLayer } from './CertificatePatterns';
import { FieldTypographyControl } from './FieldTypographyControl';
import { FIELD_TYPOGRAPHY_CATALOG } from '../utils/typography';
import { KMonogramCrest } from './IssuerLogo';
import {
  HrAdminEmployeeInput,
  normalizeHrEmployee,
  NormalizedHrEmployee,
} from '../utils/hrIntegration';

import { TRANSLATIONS } from '../i18n/translations';

const QUICK_EMPLOYEE_DIRECTORY = [
  {
    employeeId: 'EMP-KY-TL-2026-02',
    name: 'KHUN SREYNI',
    khmerName: 'ឃុន ស្រីនី',
    role: 'Team Leader · KNEAYERNG PHONE SHOP',
    khmerRole: 'ប្រធានក្រុម · ហាងទូរស័ព្ទដៃ គ្នា​យើង',
    department: 'Store Operations & Team Leadership',
  },
  {
    employeeId: 'EMP-KY-HR-2026-01',
    name: 'SOPHEAP RATANA',
    khmerName: 'សុភាព រតនា',
    role: 'Best of Staff & HR Honor Roll',
    khmerRole: 'បុគ្គលិកឆ្នើមប្រចាំឆ្នាំ និងគំរូវិន័យការងារ',
    department: 'Human Resources & Store Operations',
  },
  {
    employeeId: 'EMP-KY-2026-STF01',
    name: 'Dara Sothea',
    khmerName: 'សុធា ដារ៉ា',
    role: 'Best Employee of the Year',
    khmerRole: 'បុគ្គលិកឆ្នើមប្រចាំឆ្នាំ',
    department: 'Operations & Human Resources',
  },
  {
    employeeId: 'EMP-KY-2026-08',
    name: 'Reach Chhoeun',
    khmerName: 'ឈឿន រាជ',
    role: 'Principal Team Leader & Culture Champion',
    khmerRole: 'មេដឹកនាំក្រុមឆ្នើម',
    department: 'Leadership & People Operations',
  },
  {
    employeeId: 'EMP-KY-2026-44',
    name: 'Sokha Chan',
    khmerName: 'ចាន់ សុខា',
    role: 'Senior Customer Care Ambassador',
    khmerRole: 'ឯកអគ្គរាជទូតសេវាកម្មអតិថិជន',
    department: 'Customer Care & Hospitality',
  },
  {
    employeeId: 'EMP-ACAD-2026-92',
    name: 'Vannak Meas',
    khmerName: 'មាស វណ្ណៈ',
    role: 'Certified Digital Practitioner',
    khmerRole: 'សិក្ខាកាមឆ្នើមផ្នែកឌីជីថល',
    department: 'Engineering & Digital Systems',
  },
];

type QuickEmployee = NormalizedHrEmployee & { khmerRole?: string };

const DIRECTORY_QUICK_EMPLOYEES: QuickEmployee[] = QUICK_EMPLOYEE_DIRECTORY.map((emp) => ({
  id: emp.employeeId,
  employeeId: emp.employeeId,
  name: emp.name,
  khmerName: emp.khmerName,
  role: emp.role,
  khmerRole: emp.khmerRole,
  department: emp.department,
  digitalHours: 45,
  track: `${emp.department} Excellence`,
  status: 'Verified & Accredited',
}));

function toQuickEmployee(emp: HrAdminEmployeeInput, index: number): QuickEmployee {
  return normalizeHrEmployee(emp, index);
}

interface EditorSidebarProps {
  data: CertificateData;
  onChange: (updated: CertificateData) => void;
  onOpenCitationModal: () => void;
  onOpenSignatureModal: (index: number) => void;
  onOpenDigitalHrModal: () => void;
  onOpenWindowsModal?: () => void;
  /** Real employee roster synced from hr-ky-admin / Laravel */
  externalEmployees?: HrAdminEmployeeInput[];
  activeTab?: string;
  onTabChange?: (tab: 'presets' | 'content' | 'styling' | 'hr' | 'signatures') => void;
  uiLang?: 'en' | 'km';
}

export const EditorSidebar: React.FC<EditorSidebarProps> = ({
  data,
  onChange,
  onOpenCitationModal,
  onOpenSignatureModal,
  onOpenDigitalHrModal,
  onOpenWindowsModal,
  externalEmployees,
  activeTab: initialActiveTab,
  onTabChange,
  uiLang = 'en',
}) => {
  const t = TRANSLATIONS[uiLang];
  const [tab, setTab] = useState<'presets' | 'content' | 'styling' | 'hr' | 'signatures'>(
    (initialActiveTab as any) || 'presets'
  );
  const [presetCategoryFilter, setPresetCategoryFilter] = useState<AwardCategory | 'all'>('all');
  const [keepMyContentOnPreset, setKeepMyContentOnPreset] = useState<boolean>(false);
  const [frameFilter, setFrameFilter] = useState<'all' | 'ribbon' | 'heritage' | 'modern'>('all');
  const [selectedTypographyField, setSelectedTypographyField] = useState<FieldTypographyKey>('recipientName');
  const [employeeSearch, setEmployeeSearch] = useState('');
  const [showVisibilityPanel, setShowVisibilityPanel] = useState<boolean>(true);
  const [lastBorderStyle, setLastBorderStyle] = useState<BorderStyleOption>(
    data.styling.borderStyle !== 'none' ? data.styling.borderStyle : 'navy-gold-ribbon'
  );

  // Real employee roster from hr-ky-admin (falls back to demo directory when running standalone)
  const quickEmployees = useMemo<QuickEmployee[]>(() => {
    if (externalEmployees && externalEmployees.length > 0) {
      const seen = new Set<string>();
      return externalEmployees
        .map(toQuickEmployee)
        .filter((emp) => {
          if (!emp.name) return false;
          const key = emp.employeeId.trim().toLowerCase();
          if (seen.has(key)) return false;
          seen.add(key);
          return true;
        });
    }
    return DIRECTORY_QUICK_EMPLOYEES;
  }, [externalEmployees]);

  const normalizedEmployeeQuery = employeeSearch.trim().toLowerCase();
  const filteredQuickEmployees = useMemo<QuickEmployee[]>(() => {
    if (!normalizedEmployeeQuery) return quickEmployees.slice(0, 12);
    const tokens = normalizedEmployeeQuery.split(/\s+/).filter(Boolean);
    return quickEmployees
      .filter((emp) => {
        const haystack = [
          emp.name,
          emp.khmerName,
          emp.employeeId,
          emp.role,
          emp.department,
          emp.khmerRole,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase();
        return tokens.every((token) => haystack.includes(token));
      })
      .slice(0, 40);
  }, [quickEmployees, normalizedEmployeeQuery]);

  const handleSelectQuickEmployee = (emp: QuickEmployee) => {
    const isSameEmployee = data.digitalHr?.employeeId === emp.employeeId;
    const generatedCredentialId = `KY-CERT-${new Date().getFullYear()}-${emp.employeeId
      .replace(/[^A-Z0-9-]/gi, '')
      .slice(-6)}`;

    onChange({
      ...data,
      recipientName: emp.name,
      khmerRecipientName: emp.khmerName || (isSameEmployee ? data.khmerRecipientName : undefined),
      recipientTitle: emp.role || data.recipientTitle,
      khmerRecipientTitle: emp.khmerRole || (isSameEmployee ? data.khmerRecipientTitle : undefined),
      recipientPhotoUrl: isSameEmployee ? data.recipientPhotoUrl || emp.photoUrl : emp.photoUrl,
      showRecipientPhoto: isSameEmployee
        ? data.showRecipientPhoto || Boolean(emp.photoUrl)
        : Boolean(emp.photoUrl),
      credentialId:
        emp.credentialId ||
        (isSameEmployee && data.credentialId ? data.credentialId : generatedCredentialId),
      digitalHr: {
        ...(data.digitalHr || {
          managerName: 'HR Executive Committee',
          showDigitalHoursBadge: false,
        }),
        employeeId: emp.employeeId,
        department: emp.department,
        trainingHours: emp.digitalHours,
        hrCompetencyTrack: emp.track,
        complianceStatus: emp.status,
      },
    });
  };

  useEffect(() => {
    if (initialActiveTab) {
      setTab(initialActiveTab as any);
    }
  }, [initialActiveTab]);

  useEffect(() => {
    if (data.styling.borderStyle && data.styling.borderStyle !== 'none') {
      setLastBorderStyle(data.styling.borderStyle);
    }
  }, [data.styling.borderStyle]);

  const handleTabSwitch = (next: 'presets' | 'content' | 'styling' | 'hr' | 'signatures') => {
    setTab(next);
    onTabChange?.(next);
  };

  const updateField = <K extends keyof CertificateData>(key: K, value: CertificateData[K]) => {
    onChange({ ...data, [key]: value });
  };

  const updateIssuer = (field: string, val: any) => {
    onChange({
      ...data,
      issuer: { ...data.issuer, [field]: val },
    });
  };

  const updateSeal = (field: string, val: any) => {
    onChange({
      ...data,
      seal: { ...data.seal, [field]: val },
    });
  };

  const updateStyling = (field: string, val: any) => {
    onChange({
      ...data,
      styling: { ...data.styling, [field]: val },
    });
  };

  const vis: CertificateVisibility = data.visibility || {};
  const layoutId = data.layoutTemplate || 'classic';
  const isExecutiveVerifyLayout =
    layoutId === 'executive-verify' ||
    layoutId === 'executive-verify-v2' ||
    layoutId === 'executive-medal-ribbon' ||
    layoutId === 'executive-imperial-sash';
  const defaultShowHangingMedal =
    layoutId === 'executive-medal-ribbon' || layoutId === 'executive-imperial-sash';
  const defaultShowTitleLaurels = layoutId === 'executive-imperial-sash';
  const defaultShowStorefrontWm = layoutId === 'executive-imperial-sash';
  const defaultShowPreFooterDiv = layoutId === 'executive-medal-ribbon';
  const defaultShowCertId =
    layoutId === 'executive-verify-v2' ||
    layoutId === 'executive-medal-ribbon' ||
    layoutId === 'executive-imperial-sash' ||
    layoutId === 'classic';

  const updateVisibility = (key: keyof CertificateVisibility, val: boolean) => {
    onChange({
      ...data,
      visibility: {
        ...(data.visibility || {}),
        [key]: val,
      },
    });
  };

  const renderInlineEyeToggle = (key: keyof CertificateVisibility, fallbackDefault = true) => {
    const isVisible = vis[key] ?? fallbackDefault;
    return (
      <button
        type="button"
        onClick={() => updateVisibility(key, !isVisible)}
        className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-bold border transition-colors cursor-pointer ${
          isVisible
            ? 'border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/70 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300'
            : 'border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'
        }`}
        title={isVisible ? 'Visible — click to hide' : 'Hidden — click to show'}
      >
        {isVisible ? <Eye className="w-2.5 h-2.5" /> : <EyeOff className="w-2.5 h-2.5" />}
        <span>{isVisible ? (uiLang === 'km' ? 'បង្ហាញ' : 'Show') : (uiLang === 'km' ? 'លាក់' : 'Hide')}</span>
      </button>
    );
  };

  const applyVisibilityPreset = (preset: 'all' | 'minimal' | 'blank-stamp') => {
    if (preset === 'all') {
      onChange({
        ...data,
        showRecipientPhoto: true,
        showQrCode: true,
        seal: { ...data.seal, enabled: true },
        signatories: data.signatories.map((s) => ({ ...s, hidden: false })),
        visibility: {},
        styling: {
          ...data.styling,
          borderStyle: data.styling.borderStyle === 'none' ? lastBorderStyle : data.styling.borderStyle,
          showWatermark: true,
          showLaurelWreath: true,
        },
      });
    } else if (preset === 'minimal') {
      onChange({
        ...data,
        visibility: {
          ...(data.visibility || {}),
          showHangingMedal: false,
          showStorefrontWatermark: false,
          showTitleLaurels: false,
          showHeaderDivider: false,
          showRecipientDivider: false,
          showPreFooterDivider: false,
          showAuthorityPreamble: false,
          showSecurityFooter: false,
        },
        styling: {
          ...data.styling,
          showWatermark: false,
          showLaurelWreath: false,
        },
      });
    } else {
      onChange({
        ...data,
        showQrCode: false,
        seal: { ...data.seal, enabled: false },
        visibility: {
          ...(data.visibility || {}),
          showSignatories: false,
          showIssueDate: false,
          showCertificateId: false,
        },
      });
    }
  };

  const handleSelectLogoOrnament = (ornament: LogoOrnamentStyle) => {
    onChange({
      ...data,
      styling: {
        ...data.styling,
        logoOrnament: ornament,
        showLaurelWreath: ornament !== 'none',
      },
    });
  };

  const handleSelectDividerOrnament = (divider: DividerOrnamentStyle) => {
    onChange({
      ...data,
      styling: {
        ...data.styling,
        dividerOrnament: divider,
      },
    });
  };

  const handleCustomDividerUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      const result = ev.target?.result as string;
      if (result) {
        onChange({
          ...data,
          styling: {
            ...data.styling,
            dividerOrnament: 'custom',
            customDividerUrl: result,
          },
        });
      }
    };
    reader.readAsDataURL(file);
    e.target.value = '';
  };

  const activeLogoOrnament: LogoOrnamentStyle =
    data.styling.logoOrnament ?? (data.styling.showLaurelWreath === false ? 'none' : 'classic-laurel');
  const activeDividerOrnament: DividerOrnamentStyle =
    data.styling.dividerOrnament || 'baroque-fleur';
  const activeDividerColor = data.styling.dividerColor || data.styling.accentColor || '#D4A32C';
  const activeDividerWidth = data.styling.dividerWidth ?? 260;
  const activeDividerScale = data.styling.dividerScale ?? 1;
  const activeDividerOffsetY = data.styling.dividerOffsetY ?? 0;
  const activeDividerOpacity = data.styling.dividerOpacity ?? 1;

  const renderLogoOrnamentPickers = () => (
    <div className="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800">
      {/* 1. Side Flanking Ornaments around Logo */}
      <div>
        <div className="flex items-center justify-between mb-1.5">
          <label className="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 font-khmer-sans">
            {uiLang === 'km'
              ? `ក្បាច់សងខាង Logo (${LOGO_ORNAMENT_STYLES.length - 1} ម៉ូត)`
              : `Logo Side Ornaments (${LOGO_ORNAMENT_STYLES.length - 1} Motifs)`}
          </label>
        </div>
        <div className="grid grid-cols-2 gap-1.5">
          {LOGO_ORNAMENT_STYLES.map((orn) => {
            const isActive = activeLogoOrnament === orn.id;
            return (
              <button
                key={orn.id}
                type="button"
                onClick={() => handleSelectLogoOrnament(orn.id)}
                className={`p-1.5 rounded-lg border text-left transition-all cursor-pointer relative ${
                  isActive
                    ? 'border-amber-500 bg-amber-50/90 dark:bg-amber-950/40 ring-1 ring-amber-400/50'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-amber-300'
                }`}
              >
                <div className="h-10 w-full rounded bg-[#FCFBF7] border border-slate-200/70 flex items-center justify-center overflow-hidden relative mb-1">
                  {orn.id === 'none' ? (
                    <span className="text-[9px] text-slate-400 font-medium">Clean / គ្មាន</span>
                  ) : (
                    <LogoFlankingOrnament
                      style={orn.id}
                      accentColor={data.styling.accentColor}
                      primaryColor={data.styling.primaryColor}
                      className="w-24 h-11"
                    />
                  )}
                  <div className="w-4 h-4 rounded-full border border-amber-400 bg-white absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-80" />
                </div>
                <div className="flex items-center justify-between gap-1 px-0.5">
                  <div className="min-w-0">
                    <div className="text-[10.5px] font-bold text-slate-800 dark:text-slate-200 truncate">
                      {orn.label}
                    </div>
                    <div className="text-[9.5px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate">
                      {orn.khmerLabel}
                    </div>
                  </div>
                  {isActive && <Check className="w-3 h-3 text-amber-600 shrink-0" />}
                </div>
              </button>
            );
          })}
        </div>
      </div>

      {/* 2. Full Customizer for Divider Ornament Below Logo (ក្បាច់ខណ្ឌក្រោម Logo) */}
      <div className="p-2.5 rounded-xl bg-amber-50/40 dark:bg-slate-800/60 border border-amber-300/70 dark:border-amber-800/50 space-y-2.5">
        <div className="flex items-center justify-between gap-2">
          <label className="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 font-khmer-sans">
            {uiLang === 'km'
              ? `ក្បាច់ខណ្ឌក្រោម Logo (${DIVIDER_ORNAMENT_STYLES.length} ម៉ូត · កែសេរី)`
              : `Divider Below Logo (${DIVIDER_ORNAMENT_STYLES.length} · Full Custom)`}
          </label>
          <button
            type="button"
            onClick={() =>
              onChange({
                ...data,
                styling: {
                  ...data.styling,
                  dividerOrnament: 'baroque-fleur',
                  dividerColor: undefined,
                  dividerWidth: 260,
                  dividerScale: 1,
                  dividerOffsetY: 0,
                  dividerOpacity: 1,
                },
              })
            }
            className="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 dark:text-amber-300 hover:underline cursor-pointer"
            title="Reset divider customization"
          >
            <RotateCcw className="w-2.5 h-2.5" />
            <span>Reset</span>
          </button>
        </div>

        {/* Style Motif Grid */}
        <div className="grid grid-cols-2 gap-1.5">
          {DIVIDER_ORNAMENT_STYLES.map((divOrn) => {
            const isActive = activeDividerOrnament === divOrn.id;
            return (
              <button
                key={divOrn.id}
                type="button"
                onClick={() => handleSelectDividerOrnament(divOrn.id)}
                className={`p-1.5 rounded-lg border text-left transition-all cursor-pointer ${
                  isActive
                    ? 'border-amber-500 bg-amber-50/90 dark:bg-amber-950/40 ring-1 ring-amber-400/50'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-amber-300'
                }`}
              >
                <div className="h-6 w-full rounded bg-[#FCFBF7] border border-slate-200/70 flex items-center justify-center overflow-hidden mb-1 px-1">
                  {divOrn.id === 'none' ? (
                    <span className="text-[9px] text-slate-400 font-medium">None / គ្មាន</span>
                  ) : divOrn.id === 'custom' ? (
                    data.styling.customDividerUrl ? (
                      <img
                        src={data.styling.customDividerUrl}
                        alt="Custom Divider"
                        className="h-5 w-full object-contain"
                      />
                    ) : (
                      <span className="text-[9px] text-amber-700 font-semibold flex items-center gap-1">
                        <Upload className="w-2.5 h-2.5" />
                        PNG / SVG
                      </span>
                    )
                  ) : (
                    <LogoDividerOrnament
                      style={divOrn.id}
                      accentColor={activeDividerColor}
                      primaryColor={data.styling.primaryColor}
                      className="w-full h-5"
                    />
                  )}
                </div>
                <div className="flex items-center justify-between gap-1 px-0.5">
                  <div className="min-w-0">
                    <div className="text-[10.5px] font-bold text-slate-800 dark:text-slate-200 truncate">
                      {divOrn.label}
                    </div>
                    <div className="text-[9.5px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate">
                      {divOrn.khmerLabel}
                    </div>
                  </div>
                  {isActive && <Check className="w-3 h-3 text-amber-600 shrink-0" />}
                </div>
              </button>
            );
          })}
        </div>

        {/* Custom Ornament PNG / SVG Upload */}
        <div className="flex items-center justify-between gap-2 p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
          <div className="min-w-0">
            <div className="text-[10.5px] font-bold text-slate-700 dark:text-slate-200 truncate font-khmer-sans">
              {uiLang === 'km' ? 'បញ្ចូលរូបក្បាច់ខណ្ឌផ្ទាល់ខ្លួន' : 'Custom Divider Image / SVG'}
            </div>
            <div className="text-[9.5px] text-slate-500 truncate">
              {data.styling.customDividerUrl ? 'Custom graphic active' : 'PNG, SVG, WebP supported'}
            </div>
          </div>
          <div className="flex items-center gap-1 shrink-0">
            <label className="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold cursor-pointer">
              <Upload className="w-3 h-3" />
              <span>{uiLang === 'km' ? 'បញ្ចូលរូប' : 'Upload'}</span>
              <input
                type="file"
                accept="image/*,.svg"
                onChange={handleCustomDividerUpload}
                className="hidden"
              />
            </label>
            {data.styling.customDividerUrl && (
              <button
                type="button"
                onClick={() =>
                  onChange({
                    ...data,
                    styling: {
                      ...data.styling,
                      customDividerUrl: undefined,
                      dividerOrnament:
                        data.styling.dividerOrnament === 'custom'
                          ? 'baroque-fleur'
                          : data.styling.dividerOrnament,
                    },
                  })
                }
                className="px-1.5 py-1 rounded-md border border-rose-200 text-rose-600 text-[10px] font-semibold hover:bg-rose-50 cursor-pointer"
              >
                ×
              </button>
            )}
          </div>
        </div>

        {/* Full Sliders & Color Calibration when Divider is visible */}
        {activeDividerOrnament !== 'none' && (
          <div className="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2.5">
            {/* Color Calibration */}
            <div>
              <div className="flex items-center justify-between mb-1">
                <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-khmer-sans">
                  {uiLang === 'km' ? 'ពណ៌ក្បាច់ខណ្ឌ (Color)' : 'Divider Color'}
                </span>
                <div className="flex items-center gap-1.5">
                  {['#D4A32C', '#092653', '#9A6B12', '#E9C253', '#7C2D12'].map((swatch) => (
                    <button
                      key={swatch}
                      type="button"
                      onClick={() => updateStyling('dividerColor', swatch)}
                      className={`w-4 h-4 rounded-full border cursor-pointer transition-transform hover:scale-110 ${
                        activeDividerColor.toUpperCase() === swatch
                          ? 'ring-2 ring-amber-500 border-white'
                          : 'border-slate-300'
                      }`}
                      style={{ backgroundColor: swatch }}
                      title={swatch}
                    />
                  ))}
                  <input
                    type="color"
                    value={activeDividerColor}
                    onChange={(e) => updateStyling('dividerColor', e.target.value)}
                    className="w-5 h-5 rounded border border-slate-300 cursor-pointer"
                    title="Pick any color"
                  />
                </div>
              </div>
            </div>

            {/* Width + Scale */}
            <div className="grid grid-cols-2 gap-2.5">
              <div>
                <div className="flex justify-between text-[10px] mb-0.5">
                  <span className="text-slate-600 dark:text-slate-400">
                    {uiLang === 'km' ? 'ប្រវែង (Width)' : 'Width'}
                  </span>
                  <span className="font-mono font-bold text-amber-600">{activeDividerWidth}px</span>
                </div>
                <input
                  type="range"
                  min="120"
                  max="480"
                  step="10"
                  value={activeDividerWidth}
                  onChange={(e) => updateStyling('dividerWidth', parseInt(e.target.value, 10))}
                  className="w-full"
                />
              </div>

              <div>
                <div className="flex justify-between text-[10px] mb-0.5">
                  <span className="text-slate-600 dark:text-slate-400">
                    {uiLang === 'km' ? 'ទំហំ (Scale)' : 'Scale'}
                  </span>
                  <span className="font-mono font-bold text-amber-600">
                    {Math.round(activeDividerScale * 100)}%
                  </span>
                </div>
                <input
                  type="range"
                  min="0.6"
                  max="1.8"
                  step="0.05"
                  value={activeDividerScale}
                  onChange={(e) => updateStyling('dividerScale', parseFloat(e.target.value))}
                  className="w-full"
                />
              </div>
            </div>

            {/* Vertical Offset + Opacity */}
            <div className="grid grid-cols-2 gap-2.5">
              <div>
                <div className="flex justify-between text-[10px] mb-0.5">
                  <span className="text-slate-600 dark:text-slate-400">
                    {uiLang === 'km' ? 'រំកិលលើ/ក្រោម' : 'Offset Y'}
                  </span>
                  <span className="font-mono font-bold text-amber-600">
                    {activeDividerOffsetY > 0 ? `+${activeDividerOffsetY}` : activeDividerOffsetY}px
                  </span>
                </div>
                <input
                  type="range"
                  min="-16"
                  max="24"
                  step="1"
                  value={activeDividerOffsetY}
                  onChange={(e) => updateStyling('dividerOffsetY', parseInt(e.target.value, 10))}
                  className="w-full"
                />
              </div>

              <div>
                <div className="flex justify-between text-[10px] mb-0.5">
                  <span className="text-slate-600 dark:text-slate-400">
                    {uiLang === 'km' ? 'ភាពច្បាស់ (Opacity)' : 'Opacity'}
                  </span>
                  <span className="font-mono font-bold text-amber-600">
                    {Math.round(activeDividerOpacity * 100)}%
                  </span>
                </div>
                <input
                  type="range"
                  min="0.2"
                  max="1"
                  step="0.05"
                  value={activeDividerOpacity}
                  onChange={(e) => updateStyling('dividerOpacity', parseFloat(e.target.value))}
                  className="w-full"
                />
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );

  const updateFieldStyle = (key: FieldTypographyKey, style: FieldTypographyStyle | undefined) => {
    const nextFieldStyles = { ...(data.styling.fieldStyles || {}) };
    if (!style) {
      delete nextFieldStyles[key];
    } else {
      nextFieldStyles[key] = style;
    }
    updateStyling('fieldStyles', nextFieldStyles);
  };

  const resetAllFieldStyles = () => {
    updateStyling('fieldStyles', {});
  };

  const customizedFieldCount = Object.keys(data.styling.fieldStyles || {}).length;

  const updateDigitalHr = (field: keyof DigitalHrRecord, val: any) => {
    onChange({
      ...data,
      digitalHr: {
        ...(data.digitalHr || {
          employeeId: 'EMP-KY-2026-08',
          department: 'Leadership & Culture Development',
          trainingHours: 60,
          hrCompetencyTrack: 'Transformational Leadership',
          complianceStatus: 'Verified & Accredited',
          managerName: 'HR Executive Committee',
          showDigitalHoursBadge: true,
        }),
        [field]: val,
      },
    });
  };

  const applyPreset = (preset: CertificateData) => {
    if (keepMyContentOnPreset) {
      onChange({
        ...data,
        id: preset.id,
        theme: preset.theme,
        layoutTemplate: preset.layoutTemplate || 'classic',
        recipientPhotoShape: preset.recipientPhotoShape || data.recipientPhotoShape,
        seal: {
          ...data.seal,
          style: preset.seal.style,
          ribbonColor: preset.seal.ribbonColor,
        },
        styling: {
          ...preset.styling,
          fieldStyles: data.styling.fieldStyles,
          logoScale: data.styling.logoScale,
          dividerOrnament: data.styling.dividerOrnament || preset.styling.dividerOrnament,
          customDividerUrl: data.styling.customDividerUrl,
          dividerColor: data.styling.dividerColor,
          dividerWidth: data.styling.dividerWidth,
          dividerScale: data.styling.dividerScale,
          dividerOffsetY: data.styling.dividerOffsetY,
          dividerOpacity: data.styling.dividerOpacity,
        },
      });
      return;
    }
    onChange({ ...preset });
  };

  const switchFormVersion = (versionId: CertificateData['layoutTemplate']) => {
    if (!versionId) return;
    if (versionId === 'executive-verify') {
      onChange({
        ...data,
        layoutTemplate: versionId,
        showQrCode: true,
        showRecipientPhoto: data.showRecipientPhoto ?? true,
        recipientPhotoShape: 'rounded-portrait',
        issueDate: data.issueDate || '15 November 2026',
      });
    } else if (
      versionId === 'executive-verify-v2' ||
      versionId === 'executive-medal-ribbon' ||
      versionId === 'executive-imperial-sash'
    ) {
      onChange({
        ...data,
        layoutTemplate: versionId,
        showQrCode: true,
        showRecipientPhoto: data.showRecipientPhoto ?? true,
        recipientPhotoShape: versionId === 'executive-verify-v2' ? 'circle' : 'rounded-portrait',
        issueDate: data.issueDate || '15 November 2026',
        credentialId: data.credentialId || 'KY-CERT-2026-00045',
      });
    } else {
      updateField('layoutTemplate', versionId);
    }
  };

  const addMetric = () => {
    const newMetrics = [
      ...data.metrics,
      { id: String(Date.now()), label: 'Achievement', value: 'High Honors' },
    ];
    updateField('metrics', newMetrics);
  };

  const removeMetric = (id: string) => {
    updateField('metrics', data.metrics.filter((m) => m.id !== id));
  };

  const updateMetric = (id: string, field: 'label' | 'value', val: string) => {
    updateField(
      'metrics',
      data.metrics.map((m) => (m.id === id ? { ...m, [field]: val } : m))
    );
  };

  const handleCustomLogoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => {
        const result = ev.target?.result as string;
        onChange({
          ...data,
          issuer: {
            ...data.issuer,
            logoType: 'custom',
            customLogoUrl: result,
          },
        });
      };
      reader.readAsDataURL(file);
      e.target.value = '';
    }
  };

  const handleRecipientPhotoUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (ev) => {
        const result = ev.target?.result as string;
        onChange({
          ...data,
          recipientPhotoUrl: result,
          showRecipientPhoto: true,
        });
      };
      reader.readAsDataURL(file);
      e.target.value = '';
    }
  };

  const handleAddHrSignatory = () => {
    const newSig = {
      id: `s-${Date.now()}`,
      name: 'SOPHEA MENG',
      khmerName: 'ម៉េង សោភា',
      role: 'HR Manager (HR Signature)',
      khmerRole: 'ប្រធានផ្នែកធនធានមនុស្ស (HR)',
      organization: 'Human Resources Department',
      signatureStyle: 'great-vibes' as const,
    };
    updateField('signatories', [...data.signatories, newSig]);
  };

  const handleRemoveSignatory = (index: number) => {
    if (data.signatories.length <= 1) return;
    updateField(
      'signatories',
      data.signatories.filter((_, i) => i !== index)
    );
  };

  const filteredPresets =
    presetCategoryFilter === 'all'
      ? CERTIFICATE_PRESETS
      : CERTIFICATE_PRESETS.filter((p) => p.category === presetCategoryFilter);

  return (
    <aside className="no-print w-full md:w-96 lg:w-[410px] bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col h-full shrink-0 z-20">
      {/* Sidebar Header Tabs */}
      <div className="grid grid-cols-5 p-1.5 bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 gap-1">
        <button
          type="button"
          onClick={() => handleTabSwitch('presets')}
          className={`flex flex-col items-center justify-center py-2 text-[11px] font-medium rounded-lg transition-colors cursor-pointer ${
            tab === 'presets'
              ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-bold'
              : 'text-slate-500 hover:text-slate-900'
          }`}
        >
          <Layers className="w-3.5 h-3.5 mb-0.5" />
          <span>Presets</span>
        </button>
        <button
          type="button"
          onClick={() => handleTabSwitch('content')}
          className={`flex flex-col items-center justify-center py-2 text-[11px] font-medium rounded-lg transition-colors cursor-pointer ${
            tab === 'content'
              ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-bold'
              : 'text-slate-500 hover:text-slate-900'
          }`}
        >
          <FileEdit className="w-3.5 h-3.5 mb-0.5" />
          <span>Fields</span>
        </button>
        <button
          type="button"
          onClick={() => handleTabSwitch('styling')}
          className={`flex flex-col items-center justify-center py-2 text-[11px] font-medium rounded-lg transition-colors cursor-pointer ${
            tab === 'styling'
              ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-bold'
              : 'text-slate-500 hover:text-slate-900'
          }`}
        >
          <Palette className="w-3.5 h-3.5 mb-0.5" />
          <span>Design</span>
        </button>
        <button
          type="button"
          onClick={() => handleTabSwitch('hr')}
          className={`flex flex-col items-center justify-center py-2 text-[11px] font-medium rounded-lg transition-colors cursor-pointer ${
            tab === 'hr'
              ? 'bg-amber-500 text-white shadow-xs font-bold'
              : 'text-amber-700 dark:text-amber-400 hover:text-amber-900'
          }`}
        >
          <Clock className="w-3.5 h-3.5 mb-0.5" />
          <span>Digital HR</span>
        </button>
        <button
          type="button"
          onClick={() => handleTabSwitch('signatures')}
          className={`flex flex-col items-center justify-center py-2 text-[11px] font-medium rounded-lg transition-colors cursor-pointer ${
            tab === 'signatures'
              ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-bold'
              : 'text-slate-500 hover:text-slate-900'
          }`}
        >
          <Award className="w-3.5 h-3.5 mb-0.5" />
          <span>Seals</span>
        </button>
      </div>

      {/* Tab Panels */}
      <div className="flex-1 overflow-y-auto p-4 space-y-5 text-xs text-slate-800 dark:text-slate-200">
        
        {/* ===================== TAB 1: PRESETS & MULTI-FORM V1-V4 ===================== */}
        {tab === 'presets' && (
          <div className="space-y-4">
            {/* Flexible Multi-Form Version Switcher (V1, V2, V3, V4) */}
            <div className="p-3 rounded-xl bg-gradient-to-br from-amber-50/90 to-white dark:from-slate-800 dark:to-slate-900 border border-amber-300/80 dark:border-amber-800/60 space-y-2 shadow-2xs">
              <div className="flex items-center justify-between gap-2">
                <span className="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 font-khmer-sans">
                  {uiLang === 'km' ? 'ប្តូរទម្រង់វិញ្ញាបនបត្រ (V1 – V6)' : 'Switch Form Version (V1 – V6)'}
                </span>
                <span className="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-amber-600 text-white">
                  {getLayoutMeta(data.layoutTemplate).versionTag}
                </span>
              </div>
              <div className="grid grid-cols-2 gap-1.5">
                {LAYOUT_TEMPLATE_VERSIONS.map((lt) => {
                  const isActive = (data.layoutTemplate || 'classic') === lt.id;
                  return (
                    <button
                      key={lt.id}
                      type="button"
                      onClick={() => switchFormVersion(lt.id)}
                      className={`p-2 rounded-lg border text-left transition-all cursor-pointer ${
                        isActive
                          ? 'border-amber-500 bg-amber-100/80 dark:bg-amber-950/50 text-amber-900 dark:text-amber-100 ring-1 ring-amber-400/40'
                          : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-amber-300 text-slate-700 dark:text-slate-300'
                      }`}
                    >
                      <div className="flex items-center justify-between gap-1">
                        <span className="text-[11px] font-mono font-extrabold text-amber-700 dark:text-amber-300">
                          {lt.versionTag} · {lt.label}
                        </span>
                        {isActive && <Check className="w-3 h-3 text-amber-600 shrink-0" />}
                      </div>
                      <div className="text-[9.5px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate mt-0.5">
                        {uiLang === 'km' ? lt.khmerShortDesc : lt.shortDesc}
                      </div>
                    </button>
                  );
                })}
              </div>
              <p className="text-[10px] text-slate-500 dark:text-slate-400 font-khmer-sans">
                {uiLang === 'km'
                  ? 'ការប្តូរ V1–V6 រក្សាទុកឈ្មោះ រូបភាព Logo និងអក្សររបស់អ្នកដោយស្វ័យប្រវត្តិ'
                  : 'Switching V1–V6 keeps your current text, photo, logo & divider.'}
              </p>
            </div>

            <div>
              <div className="flex items-center justify-between mb-2">
                <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                  Award Category Presets
                </label>
                <label className="flex items-center gap-1 text-[10px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                  <input
                    type="checkbox"
                    checked={keepMyContentOnPreset}
                    onChange={(e) => setKeepMyContentOnPreset(e.target.checked)}
                    className="rounded text-amber-600"
                  />
                  <span className="font-khmer-sans">
                    {uiLang === 'km' ? 'រក្សាអក្សរដើម' : 'Keep my text'}
                  </span>
                </label>
              </div>
              <div className="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-lg">
                <button
                  type="button"
                  onClick={() => setPresetCategoryFilter('all')}
                  className={`flex-1 py-1 text-center text-xs font-medium rounded-md cursor-pointer ${
                    presetCategoryFilter === 'all'
                      ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400'
                  }`}
                >
                  All
                </button>
                <button
                  type="button"
                  onClick={() => setPresetCategoryFilter('staff')}
                  className={`flex-1 py-1 text-center text-xs font-medium rounded-md cursor-pointer ${
                    presetCategoryFilter === 'staff'
                      ? 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                      : 'text-slate-600 dark:text-slate-400'
                  }`}
                >
                  Staff
                </button>
                <button
                  type="button"
                  onClick={() => setPresetCategoryFilter('leader')}
                  className={`flex-1 py-1 text-center text-xs font-medium rounded-md cursor-pointer ${
                    presetCategoryFilter === 'leader'
                      ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400'
                  }`}
                >
                  Leader
                </button>
                <button
                  type="button"
                  onClick={() => setPresetCategoryFilter('service')}
                  className={`flex-1 py-1 text-center text-xs font-medium rounded-md cursor-pointer ${
                    presetCategoryFilter === 'service'
                      ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400'
                  }`}
                >
                  Service
                </button>
                <button
                  type="button"
                  onClick={() => setPresetCategoryFilter('course')}
                  className={`flex-1 py-1 text-center text-xs font-medium rounded-md cursor-pointer ${
                    presetCategoryFilter === 'course'
                      ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs'
                      : 'text-slate-600 dark:text-slate-400'
                  }`}
                >
                  Course
                </button>
              </div>
            </div>

            <div className="space-y-2.5">
              {filteredPresets.map((preset) => {
                const isSelected = data.id === preset.id;
                const vMeta = getLayoutMeta(preset.layoutTemplate);
                return (
                  <div
                    key={preset.id}
                    onClick={() => applyPreset(preset)}
                    className={`p-3.5 rounded-xl border transition-all cursor-pointer text-left relative group ${
                      isSelected
                        ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 shadow-xs'
                        : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-800/40'
                    }`}
                  >
                    <div className="flex items-center justify-between gap-2">
                      <div className="flex items-center gap-1.5">
                        <span className="px-1.5 py-0.2 rounded bg-slate-900 dark:bg-amber-600 text-white font-mono text-[9.5px] font-bold">
                          {vMeta.versionTag}
                        </span>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                          {preset.category === 'staff'
                            ? (uiLang === 'km' ? '★ បុគ្គលិកឆ្នើម' : '★ Best of Staff')
                            : preset.category === 'course'
                            ? 'Online Course'
                            : preset.category === 'service'
                            ? 'Customer Service'
                            : 'Team Leader'}
                        </span>
                      </div>
                      {isSelected && (
                        <span className="p-0.5 rounded-full bg-amber-500 text-white">
                          <Check className="w-3 h-3" />
                        </span>
                      )}
                    </div>
                    {preset.khmerTitle && (
                      <h4 className="font-bold text-amber-900 dark:text-amber-200 text-xs mt-1 font-khmer-sans leading-tight">
                        {preset.khmerTitle}
                      </h4>
                    )}
                    <h5 className="font-semibold text-slate-900 dark:text-slate-100 text-[11px] mt-0.5 font-cinzel">
                      {preset.certificateTitle}
                    </h5>
                    <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                      {preset.khmerCourseOrAchievementTitle || preset.courseOrAchievementTitle}
                    </p>
                    <div className="flex items-center gap-2 text-[10px] text-slate-400 mt-2 flex-wrap">
                      <span>{preset.issuer.khmerName || preset.issuer.name}</span>
                      <span>·</span>
                      <span className="text-amber-700 dark:text-amber-300 font-semibold font-mono">
                        {preset.digitalHr?.trainingHours}h Digital HR
                      </span>
                      {preset.signatories.some((s) => /hr|human\s*resource|people/i.test(`${s.role} ${s.organization || ''}`)) && (
                        <span className="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-bold text-[9px]">
                          ✍ HR Signature
                        </span>
                      )}
                      {preset.showRecipientPhoto && (
                        <span className="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold text-[9px]">
                          📷 Profile Photo
                        </span>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>

            {/* Windows & Laravel Export Quick Card */}
            {onOpenWindowsModal && (
              <div className="p-3.5 rounded-xl border border-amber-500/30 bg-amber-50/50 dark:bg-amber-950/20 mt-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <span className="text-base">🪟</span>
                    <span className="text-xs font-bold text-slate-900 dark:text-slate-100">
                      {uiLang === 'km' ? 'នាំចេញទៅ Windows & Laravel' : 'Windows & Laravel Ready'}
                    </span>
                  </div>
                  <button
                    type="button"
                    onClick={onOpenWindowsModal}
                    className="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition-colors cursor-pointer"
                  >
                    {uiLang === 'km' ? 'ទាញយក ZIP' : 'Get Package'}
                  </button>
                </div>
                <p className="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                  {uiLang === 'km'
                    ? 'ចុច RUN_WINDOWS.bat ដើម្បីដំណើរការលើ Windows ឬប្រើប្រាស់ជាមួយ Laravel 10/11'
                    : 'Download ready-to-run Windows batch scripts and Laravel 10/11 drop-in files.'}
                </p>
              </div>
            )}
          </div>
        )}

        {/* ===================== TAB 2: CONTENT & FIELDS ===================== */}
        {tab === 'content' && (
          <div className="space-y-4">
            {/* Employee Search & Quick Selection */}
            <div className="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 space-y-2">
              <div className="flex items-center justify-between gap-2">
                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5 font-khmer-sans">
                  <Search className="w-3.5 h-3.5 text-amber-600" />
                  <span>{uiLang === 'km' ? 'ស្វែងរក និងជ្រើសរើសបុគ្គលិក' : 'Employee Quick Search & Select'}</span>
                </span>
                <button
                  type="button"
                  onClick={onOpenDigitalHrModal}
                  className="text-[10px] font-bold text-amber-600 hover:underline cursor-pointer"
                >
                  {uiLang === 'km' ? 'បញ្ជី HR →' : 'Full Roster →'}
                </button>
              </div>
              <div className="relative">
                <Search className="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input
                  type="text"
                  value={employeeSearch}
                  onChange={(e) => setEmployeeSearch(e.target.value)}
                  placeholder={uiLang === 'km' ? 'វាយឈ្មោះ ឬ ID (ឧ. KHUN SREYNI)...' : 'Search name or ID (e.g. KHUN SREYNI)...'}
                  className="w-full pl-8 pr-2.5 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100"
                />
              </div>
              <div className="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pt-0.5">
                {filteredQuickEmployees.length === 0 ? (
                  <p className="text-[10.5px] text-slate-500 dark:text-slate-400 py-1">
                    {uiLang === 'km'
                      ? 'រកមិនឃើញបុគ្គលិកដែលត្រូវនឹងការស្វែងរកឡើយ។'
                      : 'No employees match your search.'}
                  </p>
                ) : (
                  filteredQuickEmployees.map((emp) => {
                    const isCurrent = data.digitalHr?.employeeId === emp.employeeId;
                    return (
                      <button
                        key={emp.employeeId}
                        type="button"
                        onClick={() => handleSelectQuickEmployee(emp)}
                        title={`${emp.name}${emp.khmerName ? ` · ${emp.khmerName}` : ''} · ${emp.role} · ${emp.department}`}
                        className={`px-2 py-1 rounded-md border text-[10px] font-semibold transition-colors cursor-pointer flex items-center gap-1 ${
                          isCurrent
                            ? 'border-amber-500 bg-amber-100/80 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 font-bold'
                            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-amber-400'
                        }`}
                      >
                        <span>{emp.name}</span>
                        {emp.khmerName && (
                          <span className="text-[9px] font-khmer-sans opacity-80">{emp.khmerName}</span>
                        )}
                        <span className="text-[9px] font-mono opacity-70">{emp.employeeId}</span>
                      </button>
                    );
                  })
                )}
              </div>
              <div className="flex items-center justify-between text-[9.5px] text-slate-400 dark:text-slate-500">
                <span>
                  {uiLang === 'km'
                    ? `បង្ហាញ ${filteredQuickEmployees.length} / ${quickEmployees.length} នាក់`
                    : `Showing ${filteredQuickEmployees.length} of ${quickEmployees.length}`}
                </span>
                {quickEmployees.length > filteredQuickEmployees.length && (
                  <span>
                    {uiLang === 'km' ? 'វាយឈ្មោះ ឬ ID ដើម្បីស្វែងរក' : 'Type a name or ID to narrow down'}
                  </span>
                )}
              </div>
            </div>

            {/* Certificate Language & Dual-Script Mode */}
            <div className="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30">
              <label className="block text-[11px] font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200 mb-1.5 font-cinzel">
                {uiLang === 'km' ? 'របៀបបង្ហាញភាសាវិញ្ញាបនបត្រ' : 'Certificate Language Mode'}
              </label>
              <div className="grid grid-cols-3 gap-1.5">
                {[
                  { id: 'bilingual', label: uiLang === 'km' ? 'ទ្វេភាសា (ខ្មែរ+EN)' : 'Bilingual (KM+EN)' },
                  { id: 'khmer', label: uiLang === 'km' ? 'ភាសាខ្មែរ' : 'Khmer Only' },
                  { id: 'english', label: uiLang === 'km' ? 'អង់គ្លេស' : 'English Only' },
                ].map((m) => (
                  <button
                    key={m.id}
                    type="button"
                    onClick={() => updateField('languageMode', m.id as any)}
                    className={`py-1.5 px-2 text-center text-xs font-semibold rounded-lg transition-colors cursor-pointer ${
                      (data.languageMode || 'bilingual') === m.id
                        ? 'bg-amber-600 text-white shadow-xs font-bold'
                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700'
                    }`}
                  >
                    {m.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Profile Photo & Brand Logo Image Updater */}
            <div className="p-3 rounded-xl bg-gradient-to-br from-slate-50 to-amber-50/40 dark:from-slate-800/80 dark:to-slate-900 border border-amber-300/70 dark:border-amber-800/60 space-y-3">
              <div className="flex items-center justify-between gap-2">
                <div className="flex items-center gap-1.5">
                  <Camera className="w-3.5 h-3.5 text-amber-600" />
                  <span className="text-[11px] font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200 font-khmer-sans">
                    {uiLang === 'km' ? 'រូបភាពប្រវត្តិរូប & Logo (Image Profile)' : 'Profile Photo & Brand Logo'}
                  </span>
                </div>
                <label className="flex items-center gap-1.5 text-[10.5px] font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={Boolean(data.showRecipientPhoto)}
                    onChange={(e) => updateField('showRecipientPhoto', e.target.checked)}
                    className="rounded text-amber-600"
                  />
                  <span>{uiLang === 'km' ? 'បង្ហាញរូបភាពបុគ្គលិក' : 'Show Photo'}</span>
                </label>
              </div>

              {/* Staff / Recipient Profile Photo Row */}
              <div className="flex items-center gap-3 p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700">
                <div className="w-12 h-12 rounded-full overflow-hidden border-2 border-amber-400 bg-amber-50 dark:bg-slate-800 flex items-center justify-center shrink-0">
                  {data.recipientPhotoUrl ? (
                    <img
                      src={data.recipientPhotoUrl}
                      alt="Staff Profile"
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <User className="w-5 h-5 text-amber-700/70" />
                  )}
                </div>
                <div className="flex-1 min-w-0 space-y-1">
                  <div className="text-[11px] font-bold text-slate-800 dark:text-slate-100 font-khmer-sans">
                    {uiLang === 'km' ? 'រូបភាពប្រវត្តិរូបបុគ្គលិក (Staff Profile)' : 'Staff / Recipient Profile Photo'}
                  </div>
                  <div className="flex items-center gap-1.5 flex-wrap">
                    <label className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-600 hover:bg-amber-700 text-white text-[10.5px] font-bold cursor-pointer transition-colors shadow-2xs">
                      <Upload className="w-3 h-3" />
                      <span>{uiLang === 'km' ? 'បញ្ចូល / ប្តូររូបភាព' : 'Upload / Update Photo'}</span>
                      <input
                        type="file"
                        accept="image/*"
                        onChange={handleRecipientPhotoUpload}
                        className="hidden"
                      />
                    </label>
                    {data.recipientPhotoUrl && (
                      <button
                        type="button"
                        onClick={() => updateField('recipientPhotoUrl', undefined)}
                        className="px-2 py-1 rounded-md border border-rose-200 dark:border-rose-800 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-[10px] font-semibold cursor-pointer"
                      >
                        {uiLang === 'km' ? 'លុបរូប' : 'Remove'}
                      </button>
                    )}
                  </div>
                </div>
              </div>

              {/* Position, Shape & Size controls when Staff Photo is active */}
              {(data.showRecipientPhoto || data.recipientPhotoUrl) && (
                <div className="space-y-2 pt-1">
                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <span className="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                        {uiLang === 'km' ? 'ទីតាំងរូបភាព' : 'Photo Position'}
                      </span>
                      <div className="grid grid-cols-3 gap-1">
                        {[
                          { id: 'top-right', label: 'Top R' },
                          { id: 'top-left', label: 'Top L' },
                          { id: 'beside-name', label: 'Name' },
                        ].map((pos) => (
                          <button
                            key={pos.id}
                            type="button"
                            onClick={() => updateField('recipientPhotoPosition', pos.id as any)}
                            className={`py-1 rounded text-[10px] font-semibold border cursor-pointer ${
                              (data.recipientPhotoPosition || 'top-right') === pos.id
                                ? 'border-amber-500 bg-amber-100/80 dark:bg-amber-950/50 text-amber-800 dark:text-amber-200 font-bold'
                                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600'
                            }`}
                          >
                            {pos.label}
                          </button>
                        ))}
                      </div>
                    </div>

                    <div>
                      <span className="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                        {uiLang === 'km' ? 'រាងស៊ុមរូប' : 'Frame Shape'}
                      </span>
                      <div className="grid grid-cols-3 gap-1">
                        {[
                          { id: 'circle', label: 'Circle' },
                          { id: 'rounded-portrait', label: 'Card' },
                          { id: 'square', label: 'Square' },
                        ].map((sh) => (
                          <button
                            key={sh.id}
                            type="button"
                            onClick={() => updateField('recipientPhotoShape', sh.id as any)}
                            className={`py-1 rounded text-[10px] font-semibold border cursor-pointer ${
                              (data.recipientPhotoShape || 'circle') === sh.id
                                ? 'border-amber-500 bg-amber-100/80 dark:bg-amber-950/50 text-amber-800 dark:text-amber-200 font-bold'
                                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600'
                            }`}
                          >
                            {sh.label}
                          </button>
                        ))}
                      </div>
                    </div>
                  </div>

                  <div>
                    <div className="flex justify-between text-[10.5px] mb-0.5">
                      <span className="text-slate-600 dark:text-slate-400">
                        {uiLang === 'km' ? 'ទំហំរូបភាពបុគ្គលិក' : 'Profile Photo Size'}
                      </span>
                      <span className="font-mono font-bold text-amber-600">
                        {data.recipientPhotoSize || 86}px
                      </span>
                    </div>
                    <input
                      type="range"
                      min="60"
                      max="130"
                      step="2"
                      value={data.recipientPhotoSize || 86}
                      onChange={(e) => updateField('recipientPhotoSize', parseInt(e.target.value, 10))}
                      className="w-full"
                    />
                  </div>

                  {/* Manual & Mouse-Drag Position Offset for Photo Frame */}
                  <div className="p-2 rounded-lg bg-white/80 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 space-y-1.5">
                    <div className="flex items-center justify-between gap-1">
                      <span className="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 font-khmer-sans">
                        {uiLang === 'km' ? 'រំកិលស៊ុមរូប (ទាញដោយ Mouse ឬកែដៃ)' : 'Move Photo on Page (Mouse Drag / Nudge)'}
                      </span>
                      {(Boolean(data.recipientPhotoFrameOffsetX) || Boolean(data.recipientPhotoFrameOffsetY)) && (
                        <button
                          type="button"
                          onClick={() =>
                            onChange({
                              ...data,
                              recipientPhotoFrameOffsetX: 0,
                              recipientPhotoFrameOffsetY: 0,
                            })
                          }
                          className="text-[9.5px] font-semibold text-amber-600 hover:underline cursor-pointer shrink-0"
                        >
                          Reset
                        </button>
                      )}
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                      <div>
                        <div className="flex justify-between text-[9.5px] text-slate-500">
                          <span>{uiLang === 'km' ? 'ឆ្វេង / ស្តាំ (X)' : 'Left / Right (X)'}</span>
                          <span className="font-mono font-bold text-amber-600">
                            {(data.recipientPhotoFrameOffsetX ?? 0) > 0
                              ? `+${data.recipientPhotoFrameOffsetX}`
                              : data.recipientPhotoFrameOffsetX ?? 0}
                            px
                          </span>
                        </div>
                        <input
                          type="range"
                          min="-280"
                          max="120"
                          step="2"
                          value={data.recipientPhotoFrameOffsetX ?? 0}
                          onChange={(e) => updateField('recipientPhotoFrameOffsetX', parseInt(e.target.value, 10))}
                          className="w-full"
                        />
                      </div>
                      <div>
                        <div className="flex justify-between text-[9.5px] text-slate-500">
                          <span>{uiLang === 'km' ? 'លើ / ក្រោម (Y)' : 'Up / Down (Y)'}</span>
                          <span className="font-mono font-bold text-amber-600">
                            {(data.recipientPhotoFrameOffsetY ?? 0) > 0
                              ? `+${data.recipientPhotoFrameOffsetY}`
                              : data.recipientPhotoFrameOffsetY ?? 0}
                            px
                          </span>
                        </div>
                        <input
                          type="range"
                          min="-70"
                          max="260"
                          step="2"
                          value={data.recipientPhotoFrameOffsetY ?? 0}
                          onChange={(e) => updateField('recipientPhotoFrameOffsetY', parseInt(e.target.value, 10))}
                          className="w-full"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Image Crop / Zoom & Repositioning */}
                  {data.recipientPhotoUrl && (
                    <div className="p-2 rounded-lg bg-white/80 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 space-y-1.5">
                      <div className="flex items-center justify-between">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                          {uiLang === 'km' ? 'ពង្រីក & រំកិលរូប (Crop / Pan)' : 'Crop Zoom & Reposition'}
                        </span>
                        <button
                          type="button"
                          onClick={() =>
                            onChange({
                              ...data,
                              recipientPhotoZoom: 1,
                              recipientPhotoOffsetX: 50,
                              recipientPhotoOffsetY: 22,
                            })
                          }
                          className="text-[9.5px] font-semibold text-amber-600 hover:underline cursor-pointer"
                        >
                          Reset
                        </button>
                      </div>
                      <div className="grid grid-cols-3 gap-2">
                        <div>
                          <div className="flex justify-between text-[9.5px] text-slate-500">
                            <span>Zoom</span>
                            <span className="font-mono font-bold text-amber-600">
                              {(data.recipientPhotoZoom || 1).toFixed(1)}x
                            </span>
                          </div>
                          <input
                            type="range"
                            min="1"
                            max="2.2"
                            step="0.05"
                            value={data.recipientPhotoZoom || 1}
                            onChange={(e) => updateField('recipientPhotoZoom', parseFloat(e.target.value))}
                            className="w-full"
                          />
                        </div>
                        <div>
                          <div className="flex justify-between text-[9.5px] text-slate-500">
                            <span>Pos X</span>
                            <span className="font-mono font-bold text-amber-600">
                              {data.recipientPhotoOffsetX ?? 50}%
                            </span>
                          </div>
                          <input
                            type="range"
                            min="0"
                            max="100"
                            step="1"
                            value={data.recipientPhotoOffsetX ?? 50}
                            onChange={(e) => updateField('recipientPhotoOffsetX', parseInt(e.target.value, 10))}
                            className="w-full"
                          />
                        </div>
                        <div>
                          <div className="flex justify-between text-[9.5px] text-slate-500">
                            <span>Pos Y</span>
                            <span className="font-mono font-bold text-amber-600">
                              {data.recipientPhotoOffsetY ?? 22}%
                            </span>
                          </div>
                          <input
                            type="range"
                            min="0"
                            max="100"
                            step="1"
                            value={data.recipientPhotoOffsetY ?? 22}
                            onChange={(e) => updateField('recipientPhotoOffsetY', parseInt(e.target.value, 10))}
                            className="w-full"
                          />
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Quick Employee Profile ID, Prefix & Show/Hide */}
                  <div className="space-y-1.5 pt-1">
                    <div className="flex items-center justify-between">
                      <span className="text-[9.5px] font-semibold uppercase tracking-wider text-slate-500">
                        {uiLang === 'km' ? 'បង្ហាញស្លាកលេខ ID ក្រោមរូបភាព' : 'Photo ID Ribbon Badge'}
                      </span>
                      {renderInlineEyeToggle('showEmployeeIdBadge', true)}
                    </div>
                    <div className="grid grid-cols-3 gap-1.5">
                      <div>
                        <span className="block text-[9px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">
                          Prefix
                        </span>
                        <input
                          type="text"
                          value={data.employeeIdPrefix ?? 'ID:'}
                          onChange={(e) => updateField('employeeIdPrefix', e.target.value)}
                          placeholder="ID:"
                          className="w-full px-2 py-1 text-[11px] font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md text-slate-800 dark:text-slate-100"
                        />
                      </div>
                      <div className="col-span-2">
                        <span className="block text-[9px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">
                          {uiLang === 'km' ? 'លេខសម្គាល់បុគ្គលិក (ID)' : 'Employee ID'}
                        </span>
                        <input
                          type="text"
                          value={data.digitalHr?.employeeId || ''}
                          onChange={(e) => updateDigitalHr('employeeId', e.target.value)}
                          placeholder="EMP-KY-TL-2026-02"
                          className="w-full px-2 py-1 text-[11px] font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md text-slate-800 dark:text-slate-100"
                        />
                      </div>
                    </div>
                    <div>
                      <span className="block text-[9px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">
                        {uiLang === 'km' ? 'ផ្នែក / តួនាទី' : 'Department / Track'}
                      </span>
                      <input
                        type="text"
                        value={data.digitalHr?.department || ''}
                        onChange={(e) => updateDigitalHr('department', e.target.value)}
                        placeholder="Team Leadership"
                        className="w-full px-2 py-1 text-[11px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md text-slate-800 dark:text-slate-100"
                      />
                    </div>
                  </div>
                </div>
              )}

              {/* Quick Center Circular Brand / Profile Logo Updater */}
              <div className="flex items-center justify-between gap-2 pt-2 border-t border-amber-200/70 dark:border-slate-700">
                <div className="flex items-center gap-2 min-w-0">
                  <img
                    src={data.issuer.customLogoUrl || '/assets/aistudio/unnamed_521b71dc.jpg'}
                    alt="Center Logo"
                    className="w-8 h-8 rounded-full border border-amber-400 object-cover bg-white shrink-0"
                  />
                  <div className="min-w-0">
                    <div className="text-[10.5px] font-bold text-slate-700 dark:text-slate-200 truncate font-khmer-sans">
                      {uiLang === 'km' ? 'រូបភាព Logo / ប្រវត្តិរូបកណ្តាល' : 'Center Circular Logo'}
                    </div>
                    <div className="text-[9.5px] text-slate-500 truncate">
                      {data.issuer.name}
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-1 shrink-0">
                  {(data.issuer.logoType !== 'knea-yoeng' ||
                    (data.issuer.customLogoUrl &&
                      data.issuer.customLogoUrl !== '/assets/aistudio/unnamed_521b71dc.jpg')) && (
                    <button
                      type="button"
                      onClick={() =>
                        onChange({
                          ...data,
                          issuer: {
                            ...data.issuer,
                            logoType: 'knea-yoeng',
                            customLogoUrl: '/assets/aistudio/unnamed_521b71dc.jpg',
                          },
                        })
                      }
                      className="px-2 py-1 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-200 text-[10px] font-bold cursor-pointer"
                      title="Restore default គ្នា​យើង circular logo"
                    >
                      Default
                    </button>
                  )}
                  <label className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-amber-400 bg-white dark:bg-slate-900 hover:bg-amber-50 text-amber-800 dark:text-amber-300 text-[10.5px] font-bold cursor-pointer">
                    <Upload className="w-3 h-3" />
                    <span>{uiLang === 'km' ? 'ប្តូរ Logo' : 'Update Logo'}</span>
                    <input type="file" accept="image/*" onChange={handleCustomLogoUpload} className="hidden" />
                  </label>
                </div>
              </div>
            </div>

            {/* Institutional Authority Preamble (Khmer & English) */}
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-[10.5px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                  {uiLang === 'km' ? 'សេចក្តីប្រកាសស្ថាប័ន' : 'Authority Preamble'}
                </span>
                {renderInlineEyeToggle('showAuthorityPreamble', true)}
              </div>
              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                  {uiLang === 'km' ? 'សេចក្តីប្រកាសរដ្ឋ / ព្រះរាជសារ (ខ្មែរ)' : 'Khmer State / Authority Preamble'}
                </label>
                <input
                  type="text"
                  placeholder="e.g. ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ"
                  value={data.khmerPreamble || ''}
                  onChange={(e) => updateField('khmerPreamble', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs font-khmer-sans"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {uiLang === 'km' ? 'សេចក្តីប្រកាសស្ថាប័ន (អង់គ្លេស)' : 'Institutional Decree (English)'}
                  </label>
                  <FieldTypographyControl
                    compact
                    fieldKey="authorityPreamble"
                    value={data.styling.fieldStyles?.authorityPreamble}
                    onChange={(s) => updateFieldStyle('authorityPreamble', s)}
                    uiLang={uiLang}
                  />
                </div>
                <input
                  type="text"
                  placeholder="e.g. BY THE AUTHORITY OF THE GOVERNING BOARD..."
                  value={data.authorityPreamble || ''}
                  onChange={(e) => updateField('authorityPreamble', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs font-medium"
                />
              </div>
            </div>

            {/* Certificate Titles (Khmer & English) */}
            <div className="space-y-2">
              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'ចំណងជើងជាភាសាខ្មែរ (Moul Font)' : 'Khmer Certificate Title'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showKhmerTitle', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="khmerTitle"
                      value={data.styling.fieldStyles?.khmerTitle}
                      onChange={(s) => updateFieldStyle('khmerTitle', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.khmerTitle || ''}
                  placeholder="e.g. វិញ្ញាបនបត្រកិត្តិយស មេដឹកនាំឆ្នើម"
                  onChange={(e) => updateField('khmerTitle', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-khmer-sans font-bold"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'ចំណងជើងជាភាសាអង់គ្លេស' : 'English Certificate Title'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showEnglishTitle', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="certificateTitle"
                      value={data.styling.fieldStyles?.certificateTitle}
                      onChange={(s) => updateFieldStyle('certificateTitle', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.certificateTitle}
                  onChange={(e) => updateField('certificateTitle', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-bold"
                />
              </div>
            </div>

            {/* Subtitles & Banner Ribbon */}
            <div className="space-y-2">
              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {uiLang === 'km' ? 'អនុចំណងជើងជាភាសាខ្មែរ' : 'Khmer Subtitle / Tagline'}
                  </label>
                  {renderInlineEyeToggle('showSubtitle', true)}
                </div>
                <input
                  type="text"
                  value={data.khmerSubtitle || ''}
                  placeholder="e.g. គណៈកម្មការប្រតិបត្តិ និងក្រុមប្រឹក្សាភិបាល គ្នា​យើង សូមប្រសិទ្ធពរជ័យ និងកោតសរសើរដល់"
                  onChange={(e) => updateField('khmerSubtitle', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs font-khmer-sans"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'បដាមាស (Banner Ribbon)' : 'Gold Banner Ribbon'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showBannerRibbon', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="bannerText"
                      value={data.styling.fieldStyles?.bannerText}
                      onChange={(s) => updateFieldStyle('bannerText', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.bannerText || ''}
                  placeholder="e.g. THIS CERTIFICATE IS PRESENTED TO"
                  onChange={(e) => updateField('bannerText', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs font-semibold tracking-wide"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {uiLang === 'km' ? 'អនុចំណងជើងជាភាសាអង់គ្លេស' : 'English Subtitle'}
                  </label>
                  <FieldTypographyControl
                    compact
                    fieldKey="subtitle"
                    value={data.styling.fieldStyles?.subtitle}
                    onChange={(s) => updateFieldStyle('subtitle', s)}
                    uiLang={uiLang}
                  />
                </div>
                <input
                  type="text"
                  value={data.subtitle}
                  onChange={(e) => updateField('subtitle', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs"
                />
              </div>
            </div>

            {/* Recipient (Khmer & English) */}
            <div className="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
              <div className="space-y-2">
                <div>
                  <div className="flex items-center justify-between mb-1 gap-1.5">
                    <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                      {uiLang === 'km' ? 'ឈ្មោះជ័យលាភី (ខ្មែរ)' : 'Recipient Name (Khmer)'}
                    </label>
                    <div className="flex items-center gap-1 shrink-0">
                      {renderInlineEyeToggle('showKhmerRecipientName', true)}
                      <FieldTypographyControl
                        compact
                        fieldKey="khmerRecipientName"
                        value={data.styling.fieldStyles?.khmerRecipientName}
                        onChange={(s) => updateFieldStyle('khmerRecipientName', s)}
                        uiLang={uiLang}
                      />
                    </div>
                  </div>
                  <input
                    type="text"
                    value={data.khmerRecipientName || ''}
                    placeholder="e.g. ឈឿន រាជ"
                    onChange={(e) => updateField('khmerRecipientName', e.target.value)}
                    className="w-full px-3 py-2 text-sm font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-khmer-sans"
                  />
                </div>
                <div>
                  <div className="flex items-center justify-between mb-1 gap-1.5">
                    <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                      {uiLang === 'km' ? 'ឈ្មោះជ័យលាភី (អង់គ្លេស)' : 'Recipient Name (English)'}
                    </label>
                    <div className="flex items-center gap-1 shrink-0">
                      {renderInlineEyeToggle('showEnglishRecipientName', true)}
                      <FieldTypographyControl
                        compact
                        fieldKey="recipientName"
                        value={data.styling.fieldStyles?.recipientName}
                        onChange={(s) => updateFieldStyle('recipientName', s)}
                        uiLang={uiLang}
                      />
                    </div>
                  </div>
                  <input
                    type="text"
                    value={data.recipientName}
                    onChange={(e) => updateField('recipientName', e.target.value)}
                    className="w-full px-3 py-2 text-sm font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                  />
                </div>
              </div>

              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'ឈ្មោះសាខា ឬស្ថាប័ន (Sub-Banner)' : 'Organization Sub-Banner'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showOrganizationSubBanner', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="organizationSubBanner"
                      value={data.styling.fieldStyles?.organizationSubBanner}
                      onChange={(s) => updateFieldStyle('organizationSubBanner', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.organizationSubBanner || ''}
                  onChange={(e) => updateField('organizationSubBanner', e.target.value)}
                  placeholder="e.g. KNEAYERNG PHONE SHOP"
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs font-semibold tracking-wider uppercase"
                />
              </div>

              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                  {uiLang === 'km' ? 'គោរមងារ ឬតួនាទី' : 'Recipient Role / Honorific'}
                </label>
                <input
                  type="text"
                  value={data.khmerRecipientTitle || data.recipientTitle || ''}
                  onChange={(e) => {
                    updateField('recipientTitle', e.target.value);
                    updateField('khmerRecipientTitle', e.target.value);
                  }}
                  placeholder="e.g. មេដឹកនាំក្រុមឆ្នើម / Principal Team Leader"
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-xs font-khmer-sans"
                />
              </div>
            </div>

            {/* Course / Award Subject (Khmer & English) */}
            <div className="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-[10.5px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                  {uiLang === 'km' ? 'វគ្គសិក្សា ឬសមិទ្ធផល' : 'Achievement Subject'}
                </span>
                {renderInlineEyeToggle('showAchievementTitle', true)}
              </div>
              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                  {uiLang === 'km' ? 'វគ្គសិក្សា ឬសមិទ្ធផល (ខ្មែរ)' : 'Course / Achievement (Khmer)'}
                </label>
                <input
                  type="text"
                  value={data.khmerCourseOrAchievementTitle || ''}
                  placeholder="e.g. សមិទ្ធផលភាពជាអ្នកដឹកនាំយុទ្ធសាស្ត្រ និងសាមគ្គីភាពក្រុម"
                  onChange={(e) => updateField('khmerCourseOrAchievementTitle', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-khmer-sans text-xs"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {uiLang === 'km' ? 'វគ្គសិក្សា ឬសមិទ្ធផល (អង់គ្លេស)' : 'Course / Achievement (English)'}
                  </label>
                  <FieldTypographyControl
                    compact
                    fieldKey="courseOrAchievementTitle"
                    value={data.styling.fieldStyles?.courseOrAchievementTitle}
                    onChange={(s) => updateFieldStyle('courseOrAchievementTitle', s)}
                    uiLang={uiLang}
                  />
                </div>
                <input
                  type="text"
                  value={data.courseOrAchievementTitle}
                  onChange={(e) => updateField('courseOrAchievementTitle', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-semibold text-xs"
                />
              </div>
            </div>

            {/* Citation Narrative (Khmer & English) */}
            <div className="space-y-2">
              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'អត្ថន័យសរសើរជាភាសាខ្មែរ' : 'Khmer Citation'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showKhmerCitation', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="khmerCitation"
                      value={data.styling.fieldStyles?.khmerCitation}
                      onChange={(s) => updateFieldStyle('khmerCitation', s)}
                      uiLang={uiLang}
                    />
                    <button
                      type="button"
                      onClick={onOpenCitationModal}
                      className="inline-flex items-center gap-0.5 text-[10px] font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 cursor-pointer"
                    >
                      <Sparkles className="w-3 h-3" />
                      <span>Smart</span>
                    </button>
                  </div>
                </div>
                <textarea
                  rows={2}
                  value={data.khmerCitation || ''}
                  placeholder="អត្ថន័យសរសើរជាភាសាខ្មែរ..."
                  onChange={(e) => updateField('khmerCitation', e.target.value)}
                  className="w-full p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-khmer-sans text-xs leading-relaxed"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'អត្ថន័យសរសើរជាភាសាអង់គ្លេស' : 'English Citation'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showEnglishCitation', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="citation"
                      value={data.styling.fieldStyles?.citation}
                      onChange={(s) => updateFieldStyle('citation', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <textarea
                  rows={2}
                  value={data.citation}
                  onChange={(e) => updateField('citation', e.target.value)}
                  className="w-full p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-serif text-xs leading-relaxed"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    {uiLang === 'km' ? 'ប្រយោគបញ្ចប់ជូនពរ' : 'Closing Congratulations Line'}
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showCitationClosing', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="citationClosing"
                      value={data.styling.fieldStyles?.citationClosing}
                      onChange={(s) => updateFieldStyle('citationClosing', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.citationClosing || ''}
                  placeholder="e.g. Congratulations on this well-deserved achievement..."
                  onChange={(e) => updateField('citationClosing', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 font-serif italic text-xs"
                />
              </div>
            </div>

            {/* Issuer Details & Custom Logo Upload */}
            <div className="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-3">
              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    Issuing Organization
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showIssuerName', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="issuerName"
                      value={data.styling.fieldStyles?.issuerName}
                      onChange={(s) => updateFieldStyle('issuerName', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.issuer.name}
                  onChange={(e) => updateIssuer('name', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                />
              </div>

              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    Department / Faculty
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showIssuerDepartment', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="issuerDepartment"
                      value={data.styling.fieldStyles?.issuerDepartment}
                      onChange={(s) => updateFieldStyle('issuerDepartment', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.issuer.department}
                  onChange={(e) => updateIssuer('department', e.target.value)}
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                />
              </div>

              {/* Logo Selection & Direct Upload */}
              <div>
                <div className="flex items-center justify-between mb-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    Logo / Emblem
                  </label>
                  <label className="text-[11px] font-semibold text-amber-600 hover:text-amber-700 cursor-pointer flex items-center gap-1">
                    <Upload className="w-3 h-3" />
                    <span>Upload New Logo</span>
                    <input type="file" accept="image/*" onChange={handleCustomLogoUpload} className="hidden" />
                  </label>
                </div>

                {/* Featured Brand Logos: Default គ្នា​យើង Emblem & K-Monogram */}
                <div className="grid grid-cols-2 gap-1.5 mb-2">
                  <button
                    type="button"
                    onClick={() => {
                      onChange({
                        ...data,
                        issuer: {
                          ...data.issuer,
                          logoType: 'knea-yoeng',
                          customLogoUrl: '/assets/aistudio/unnamed_521b71dc.jpg',
                        },
                      });
                    }}
                    className={`p-2 rounded-lg border flex items-center gap-2 transition-all cursor-pointer ${
                      data.issuer.logoType === 'knea-yoeng' || data.issuer.customLogoUrl === '/assets/aistudio/unnamed_521b71dc.jpg'
                        ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 font-bold'
                        : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-300'
                    }`}
                  >
                    <img
                      src="/assets/aistudio/unnamed_521b71dc.jpg"
                      alt="គ្នា​យើង Logo"
                      className="w-6 h-6 rounded-full border border-amber-400 object-cover shadow-xs shrink-0"
                    />
                    <div className="text-left min-w-0">
                      <span className="font-bold text-[11px] block truncate">គ្នា​យើង (Default)</span>
                      <span className="text-[9.5px] text-slate-500 block truncate">Circle Emblem</span>
                    </div>
                  </button>

                  <button
                    type="button"
                    onClick={() => {
                      onChange({
                        ...data,
                        issuer: {
                          ...data.issuer,
                          logoType: 'k-monogram',
                          customLogoUrl: undefined,
                        },
                      });
                    }}
                    className={`p-2 rounded-lg border flex items-center gap-2 transition-all cursor-pointer ${
                      data.issuer.logoType === 'k-monogram' && !data.issuer.customLogoUrl
                        ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 font-bold'
                        : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-300'
                    }`}
                  >
                    <KMonogramCrest primaryColor={data.styling.primaryColor || '#092653'} accentColor={data.styling.accentColor || '#D4A32C'} size={24} />
                    <div className="text-left min-w-0">
                      <span className="font-bold text-[11px] block truncate">K Monogram</span>
                      <span className="text-[9.5px] text-slate-500 block truncate">Navy & Gold K</span>
                    </div>
                  </button>
                </div>

                <div className="grid grid-cols-5 gap-1.5">
                  {(['crest', 'academic', 'laurel', 'shield', 'star'] as const).map((logo) => (
                    <button
                      key={logo}
                      type="button"
                      onClick={() => updateIssuer('logoType', logo)}
                      className={`p-1.5 text-center rounded-lg border capitalize cursor-pointer ${
                        data.issuer.logoType === logo
                          ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700'
                          : 'border-slate-200 dark:border-slate-800 text-slate-600'
                      }`}
                    >
                      {logo}
                    </button>
                  ))}
                </div>
              </div>

              {renderLogoOrnamentPickers()}
            </div>

            {/* Date, Credential ID & QR Verification Customizer */}
            <div className="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    Issue Date
                  </label>
                  <div className="flex items-center gap-1 shrink-0">
                    {renderInlineEyeToggle('showIssueDate', true)}
                    <FieldTypographyControl
                      compact
                      fieldKey="issueDate"
                      value={data.styling.fieldStyles?.issueDate}
                      onChange={(s) => updateFieldStyle('issueDate', s)}
                      uiLang={uiLang}
                    />
                  </div>
                </div>
                <input
                  type="text"
                  value={data.issueDate}
                  onChange={(e) => updateField('issueDate', e.target.value)}
                  className="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg"
                />
              </div>
              <div>
                <div className="flex items-center justify-between mb-1 gap-1.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 truncate">
                    Credential ID
                  </label>
                  <div className="flex items-center gap-1.5 shrink-0">
                    {renderInlineEyeToggle('showCertificateId', defaultShowCertId)}
                    <button
                      type="button"
                      onClick={() => {
                        const code = `KY-PS-VFY-${new Date().getFullYear()}-${String(Math.floor(100 + Math.random() * 900))}`;
                        onChange({
                          ...data,
                          credentialId: code,
                          qrVerificationUrl: `https://verify.kneayoeng.org/cert/${code}`,
                        });
                      }}
                      className="inline-flex items-center gap-1 text-[10.5px] font-bold text-amber-600 hover:text-amber-700 cursor-pointer"
                    >
                      <RefreshCw className="w-3 h-3" />
                      <span>Generate ID</span>
                    </button>
                  </div>
                </div>
                <input
                  type="text"
                  value={data.credentialId}
                  onChange={(e) => {
                    const nextId = e.target.value;
                    onChange({
                      ...data,
                      credentialId: nextId,
                      qrVerificationUrl: `https://verify.kneayoeng.org/cert/${nextId}`,
                    });
                  }}
                  className="w-full px-2.5 py-1.5 font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg"
                />
              </div>

              <div className="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2">
                <div className="flex items-center justify-between mb-0.5">
                  <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center gap-1">
                    <QrCode className="w-3 h-3 text-amber-600" />
                    <span>{uiLang === 'km' ? 'កំណត់ QR Code & ប៊ូតុង Verify' : 'QR Code & Verify Badge'}</span>
                  </label>
                  <label className="flex items-center gap-1 text-[10px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={data.showQrCode}
                      onChange={(e) => updateField('showQrCode', e.target.checked)}
                      className="rounded text-amber-600"
                    />
                    <span>Show QR</span>
                  </label>
                </div>
                <input
                  type="url"
                  value={data.qrVerificationUrl}
                  onChange={(e) => updateField('qrVerificationUrl', e.target.value)}
                  placeholder="https://verify.kneayoeng.org/cert/..."
                  className="w-full px-2.5 py-1.5 font-mono text-[11px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg"
                />
                {data.showQrCode && (
                  <div className="space-y-2 pt-1">
                    <div className="grid grid-cols-3 gap-1.5">
                      <div className="col-span-2">
                        <span className="block text-[9.5px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">
                          {uiLang === 'km' ? 'អក្សរក្រោម QR' : 'QR Badge Label'}
                        </span>
                        <input
                          type="text"
                          value={data.qrLabel ?? 'VERIFY CERTIFICATE'}
                          onChange={(e) => updateField('qrLabel', e.target.value)}
                          placeholder="VERIFY CERTIFICATE (blank = hide)"
                          className="w-full px-2 py-1 text-[11px] font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md"
                        />
                      </div>
                      <div>
                        <span className="block text-[9.5px] font-semibold uppercase tracking-wider text-slate-500 mb-0.5">
                          Badge Color
                        </span>
                        <div className="flex items-center gap-1">
                          <input
                            type="color"
                            value={data.qrBadgeColor || data.styling.primaryColor || '#092653'}
                            onChange={(e) => updateField('qrBadgeColor', e.target.value)}
                            className="w-7 h-7 rounded border border-slate-300 cursor-pointer"
                          />
                          {data.qrBadgeColor && (
                            <button
                              type="button"
                              onClick={() => updateField('qrBadgeColor', undefined)}
                              className="text-[9.5px] text-slate-500 hover:text-rose-600 cursor-pointer"
                              title="Reset QR pill color"
                            >
                              ×
                            </button>
                          )}
                        </div>
                      </div>
                    </div>
                    <div>
                      <div className="flex justify-between text-[10px] mb-0.5">
                        <span className="text-slate-500">QR Size</span>
                        <span className="font-mono font-bold text-amber-600">{data.qrSize || 64}px</span>
                      </div>
                      <input
                        type="range"
                        min="48"
                        max="88"
                        step="2"
                        value={data.qrSize || 64}
                        onChange={(e) => updateField('qrSize', parseInt(e.target.value, 10))}
                        className="w-full"
                      />
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        )}

        {/* ===================== TAB 3: DESIGN & FULL EASY CUSTOMIZE ===================== */}
        {tab === 'styling' && (
          <div className="space-y-4">
            {/* Master Show / Hide Elements Control Center */}
            <div className="p-3 bg-gradient-to-br from-slate-50 to-amber-50/40 dark:from-slate-800 dark:to-slate-900 rounded-xl border border-amber-300/80 dark:border-amber-800/60 space-y-2.5 shadow-2xs">
              <div className="flex items-center justify-between gap-2">
                <button
                  type="button"
                  onClick={() => setShowVisibilityPanel((v) => !v)}
                  className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 cursor-pointer"
                >
                  <Sliders className="w-3.5 h-3.5" />
                  <span className="font-khmer-sans">
                    {uiLang === 'km' ? 'បើក/បិទ គ្រប់ផ្នែកទាំងអស់ (Show / Hide)' : 'Show / Hide Every Element'}
                  </span>
                </button>
                <span className="text-[10px] font-semibold text-slate-500">
                  {showVisibilityPanel ? '▼' : '▶'}
                </span>
              </div>

              {/* Quick Visibility Modes */}
              <div className="grid grid-cols-3 gap-1">
                <button
                  type="button"
                  onClick={() => applyVisibilityPreset('all')}
                  className="py-1 px-1.5 rounded-md border border-emerald-300/80 bg-emerald-50/70 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-200 text-[10px] font-bold hover:bg-emerald-100 cursor-pointer"
                >
                  {uiLang === 'km' ? 'បង្ហាញទាំងអស់' : 'Show All'}
                </button>
                <button
                  type="button"
                  onClick={() => applyVisibilityPreset('minimal')}
                  className="py-1 px-1.5 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-[10px] font-bold hover:bg-slate-100 cursor-pointer"
                >
                  {uiLang === 'km' ? 'បែបសាមញ្ញ' : 'Minimal'}
                </button>
                <button
                  type="button"
                  onClick={() => applyVisibilityPreset('blank-stamp')}
                  className="py-1 px-1.5 rounded-md border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-200 text-[10px] font-bold hover:bg-amber-100 cursor-pointer"
                  title="Hide digital stamp, signatures, date & QR to print a blank form for wet stamp"
                >
                  {uiLang === 'km' ? 'ទម្រង់ទទេ(បោះត្រា)' : 'Wet Stamp Shell'}
                </button>
              </div>

              {showVisibilityPanel && (
                <div className="space-y-2 pt-1">
                  {(
                    [
                      {
                        groupTitle: uiLang === 'km' ? 'ស៊ុម ផ្ទៃ & ក្បាច់មេដាយ' : 'Frame, Background & Accents',
                        items: [
                          {
                            label: uiLang === 'km' ? 'ស៊ុមជុំវិញ' : 'Frame Border',
                            active: data.styling.borderStyle !== 'none',
                            toggle: () =>
                              updateStyling(
                                'borderStyle',
                                data.styling.borderStyle === 'none' ? lastBorderStyle : 'none'
                              ),
                          },
                          {
                            label: uiLang === 'km' ? 'ផ្ទៃ Watermark' : 'Watermark',
                            active: data.styling.showWatermark !== false,
                            toggle: () => updateStyling('showWatermark', !(data.styling.showWatermark !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'មេដាយព្យួរលើឆ្វេង' : 'Hanging Medal',
                            active: vis.showHangingMedal ?? defaultShowHangingMedal,
                            toggle: () =>
                              updateVisibility('showHangingMedal', !(vis.showHangingMedal ?? defaultShowHangingMedal)),
                          },
                          {
                            label: uiLang === 'km' ? 'រូបភាពហាងស្តើង' : 'Storefront BG',
                            active: vis.showStorefrontWatermark ?? defaultShowStorefrontWm,
                            toggle: () =>
                              updateVisibility(
                                'showStorefrontWatermark',
                                !(vis.showStorefrontWatermark ?? defaultShowStorefrontWm)
                              ),
                          },
                          {
                            label: uiLang === 'km' ? 'ពន្លឺ Shimmer' : 'Gold Shimmer',
                            active: Boolean(data.styling.enableShimmer),
                            toggle: () => updateStyling('enableShimmer', !data.styling.enableShimmer),
                          },
                        ],
                      },
                      {
                        groupTitle: uiLang === 'km' ? 'ផ្នែកខាងលើ & Logo' : 'Header & Brand Identity',
                        items: [
                          {
                            label: uiLang === 'km' ? 'រូបភាព Logo' : 'Company Logo',
                            active: vis.showLogo !== false,
                            toggle: () => updateVisibility('showLogo', !(vis.showLogo !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ឈ្មោះក្រុមហ៊ុន' : 'Company Name',
                            active: vis.showIssuerName !== false,
                            toggle: () => updateVisibility('showIssuerName', !(vis.showIssuerName !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ផ្នែក / ហាង' : 'Department',
                            active: vis.showIssuerDepartment !== false,
                            toggle: () => updateVisibility('showIssuerDepartment', !(vis.showIssuerDepartment !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ស្លឹកមាសសងខាង Logo' : 'Logo Laurels',
                            active: data.styling.showLaurelWreath !== false,
                            toggle: () => {
                              const next = !(data.styling.showLaurelWreath !== false);
                              onChange({
                                ...data,
                                styling: {
                                  ...data.styling,
                                  showLaurelWreath: next,
                                  logoOrnament: next ? 'classic-laurel' : 'none',
                                },
                              });
                            },
                          },
                          {
                            label: uiLang === 'km' ? 'ក្បាច់ខណ្ឌក្រោម Logo' : 'Logo Divider',
                            active: vis.showHeaderDivider !== false,
                            toggle: () => updateVisibility('showHeaderDivider', !(vis.showHeaderDivider !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'សេចក្តីប្រកាសរដ្ឋ' : 'State Preamble',
                            active: vis.showAuthorityPreamble !== false,
                            toggle: () =>
                              updateVisibility('showAuthorityPreamble', !(vis.showAuthorityPreamble !== false)),
                          },
                        ],
                      },
                      {
                        groupTitle: uiLang === 'km' ? 'ចំណងជើង រូបភាព & ឈ្មោះបុគ្គលិក' : 'Titles, Photo & Recipient',
                        items: [
                          {
                            label: uiLang === 'km' ? 'ចំណងជើងខ្មែរ' : 'Khmer Title',
                            active: vis.showKhmerTitle !== false,
                            toggle: () => updateVisibility('showKhmerTitle', !(vis.showKhmerTitle !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ចំណងជើងអង់គ្លេស' : 'English Title',
                            active: vis.showEnglishTitle !== false,
                            toggle: () => updateVisibility('showEnglishTitle', !(vis.showEnglishTitle !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ស្លឹកមាសសងខាងចំណងជើង' : 'Title Laurels',
                            active: vis.showTitleLaurels ?? defaultShowTitleLaurels,
                            toggle: () =>
                              updateVisibility('showTitleLaurels', !(vis.showTitleLaurels ?? defaultShowTitleLaurels)),
                          },
                          {
                            label: uiLang === 'km' ? 'បដាមាស Presented To' : 'Banner Ribbon',
                            active: vis.showBannerRibbon !== false,
                            toggle: () => updateVisibility('showBannerRibbon', !(vis.showBannerRibbon !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'អនុចំណងជើង' : 'Subtitle Line',
                            active: vis.showSubtitle !== false,
                            toggle: () => updateVisibility('showSubtitle', !(vis.showSubtitle !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'រូបភាពបុគ្គលិក' : 'Employee Photo',
                            active: Boolean(data.showRecipientPhoto),
                            toggle: () => updateField('showRecipientPhoto', !data.showRecipientPhoto),
                          },
                          {
                            label: uiLang === 'km' ? 'ស្លាក ID ក្រោមរូប' : 'Photo ID Tag',
                            active: vis.showEmployeeIdBadge !== false,
                            toggle: () => updateVisibility('showEmployeeIdBadge', !(vis.showEmployeeIdBadge !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ឈ្មោះខ្មែរ' : 'Khmer Name',
                            active: vis.showKhmerRecipientName !== false,
                            toggle: () =>
                              updateVisibility('showKhmerRecipientName', !(vis.showKhmerRecipientName !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ឈ្មោះអង់គ្លេស' : 'English Name',
                            active: vis.showEnglishRecipientName !== false,
                            toggle: () =>
                              updateVisibility('showEnglishRecipientName', !(vis.showEnglishRecipientName !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'បន្ទាត់ពេជ្រក្រោមឈ្មោះ' : 'Name Divider',
                            active: vis.showRecipientDivider !== false,
                            toggle: () =>
                              updateVisibility('showRecipientDivider', !(vis.showRecipientDivider !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ស្លាកសាខា Sub-Banner' : 'Sub-Banner',
                            active: vis.showOrganizationSubBanner !== false,
                            toggle: () =>
                              updateVisibility(
                                'showOrganizationSubBanner',
                                !(vis.showOrganizationSubBanner !== false)
                              ),
                          },
                        ],
                      },
                      {
                        groupTitle: uiLang === 'km' ? 'ខ្លឹមសារ ត្រា & ផ្នែកខាងក្រោម' : 'Body, Seal & Footer',
                        items: [
                          {
                            label: uiLang === 'km' ? 'ប្រធានបទសមិទ្ធផល' : 'Subject Title',
                            active: vis.showAchievementTitle !== false,
                            toggle: () =>
                              updateVisibility('showAchievementTitle', !(vis.showAchievementTitle !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'អត្ថន័យខ្មែរ' : 'Khmer Citation',
                            active: vis.showKhmerCitation !== false,
                            toggle: () => updateVisibility('showKhmerCitation', !(vis.showKhmerCitation !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'អត្ថន័យអង់គ្លេស' : 'English Citation',
                            active: vis.showEnglishCitation !== false,
                            toggle: () => updateVisibility('showEnglishCitation', !(vis.showEnglishCitation !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ប្រយោគបញ្ចប់' : 'Closing Line',
                            active: vis.showCitationClosing !== false,
                            toggle: () => updateVisibility('showCitationClosing', !(vis.showCitationClosing !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'បន្ទាត់មាសលើហត្ថលេខា' : 'Footer Rule',
                            active: vis.showPreFooterDivider ?? defaultShowPreFooterDiv,
                            toggle: () =>
                              updateVisibility(
                                'showPreFooterDivider',
                                !(vis.showPreFooterDivider ?? defaultShowPreFooterDiv)
                              ),
                          },
                          {
                            label: uiLang === 'km' ? 'ផ្លាកម៉ោង HR' : 'Digital HR Tag',
                            active: Boolean(data.digitalHr?.showDigitalHoursBadge),
                            toggle: () =>
                              updateDigitalHr('showDigitalHoursBadge', !data.digitalHr?.showDigitalHoursBadge),
                          },
                          {
                            label: uiLang === 'km' ? 'តារាងពិន្ទុ Metrics' : 'Metrics Badges',
                            active: vis.showMetrics !== false,
                            toggle: () => updateVisibility('showMetrics', !(vis.showMetrics !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'កាលបរិច្ឆេទ' : 'Issue Date',
                            active: vis.showIssueDate !== false,
                            toggle: () => updateVisibility('showIssueDate', !(vis.showIssueDate !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'លេខសម្គាល់ ID' : 'Certificate ID',
                            active: vis.showCertificateId ?? defaultShowCertId,
                            toggle: () =>
                              updateVisibility('showCertificateId', !(vis.showCertificateId ?? defaultShowCertId)),
                          },
                          {
                            label: uiLang === 'km' ? 'ហត្ថលេខាទាំងអស់' : 'Signatories',
                            active: vis.showSignatories !== false,
                            toggle: () => updateVisibility('showSignatories', !(vis.showSignatories !== false)),
                          },
                          {
                            label: uiLang === 'km' ? 'ត្រាមាសកណ្តាល' : 'Gold Stamp',
                            active: Boolean(data.seal.enabled),
                            toggle: () => updateSeal('enabled', !data.seal.enabled),
                          },
                          {
                            label: uiLang === 'km' ? 'កូដ QR Verify' : 'QR Verify',
                            active: Boolean(data.showQrCode),
                            toggle: () => updateField('showQrCode', !data.showQrCode),
                          },
                          {
                            label: uiLang === 'km' ? 'អក្សរសុវត្ថិភាព' : 'Security Line',
                            active: vis.showSecurityFooter !== false,
                            toggle: () => updateVisibility('showSecurityFooter', !(vis.showSecurityFooter !== false)),
                          },
                        ],
                      },
                    ] as const
                  ).map((grp) => (
                    <div key={grp.groupTitle} className="space-y-1">
                      <div className="text-[9.5px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 font-khmer-sans">
                        {grp.groupTitle}
                      </div>
                      <div className="grid grid-cols-2 gap-1">
                        {grp.items.map((item) => (
                          <button
                            key={item.label}
                            type="button"
                            onClick={item.toggle}
                            className={`flex items-center justify-between gap-1 px-2 py-1 rounded-md border text-[10px] font-semibold transition-all cursor-pointer ${
                              item.active
                                ? 'border-amber-400/70 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 shadow-2xs'
                                : 'border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-900/60 text-slate-400 dark:text-slate-500 line-through'
                            }`}
                          >
                            <span className="truncate font-khmer-sans">{item.label}</span>
                            {item.active ? (
                              <Eye className="w-3 h-3 text-emerald-600 shrink-0" />
                            ) : (
                              <EyeOff className="w-3 h-3 text-slate-400 shrink-0" />
                            )}
                          </button>
                        ))}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
            {/* Per-Field Font & Font Size Studio */}
            <div className="p-3 bg-gradient-to-br from-amber-50/80 to-white dark:from-slate-800 dark:to-slate-900 rounded-xl border border-amber-300/80 dark:border-amber-800/60 space-y-2.5 shadow-2xs">
              <div className="flex items-center justify-between gap-2">
                <span className="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                  <Type className="w-3.5 h-3.5" />
                  <span>
                    {uiLang === 'km' ? 'កែពុម្ពអក្សរ & ទំហំតាមប្រអប់នីមួយៗ' : 'Per-Field Font & Size Studio'}
                  </span>
                </span>
                {customizedFieldCount > 0 && (
                  <button
                    type="button"
                    onClick={resetAllFieldStyles}
                    className="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded cursor-pointer"
                  >
                    <RotateCcw className="w-2.5 h-2.5" />
                    <span>Reset All ({customizedFieldCount})</span>
                  </button>
                )}
              </div>

              <div>
                <label className="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                  {uiLang === 'km' ? 'ជ្រើសរើសប្រអប់អក្សរ' : 'Select Certificate Field'}
                </label>
                <select
                  value={selectedTypographyField}
                  onChange={(e) => setSelectedTypographyField(e.target.value as FieldTypographyKey)}
                  className="w-full px-2.5 py-1.5 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 cursor-pointer"
                >
                  {FIELD_TYPOGRAPHY_CATALOG.map((meta) => {
                    const isCustom = Boolean(data.styling.fieldStyles?.[meta.key]);
                    return (
                      <option key={meta.key} value={meta.key}>
                        {isCustom ? '● ' : ''}
                        {uiLang === 'km' ? `${meta.khmerLabel} (${meta.label})` : meta.label}
                      </option>
                    );
                  })}
                </select>
              </div>

              <FieldTypographyControl
                key={selectedTypographyField}
                fieldKey={selectedTypographyField}
                compact={false}
                defaultOpen
                value={data.styling.fieldStyles?.[selectedTypographyField]}
                onChange={(s) => updateFieldStyle(selectedTypographyField, s)}
                uiLang={uiLang}
              />
            </div>

            {/* Custom Sizing Sliders */}
            <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
              <span className="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block">
                Full Flexible Size Controls
              </span>

              {/* Recipient Size */}
              <div>
                <div className="flex justify-between text-[11px] mb-1">
                  <span className="text-slate-600 dark:text-slate-400">Recipient Name Font Size</span>
                  <span className="font-mono font-bold text-amber-600">{data.styling.recipientFontSize || 40}px</span>
                </div>
                <input
                  type="range"
                  min="28"
                  max="56"
                  value={data.styling.recipientFontSize || 40}
                  onChange={(e) => updateStyling('recipientFontSize', parseInt(e.target.value))}
                  className="w-full"
                />
              </div>

              {/* Title Size */}
              <div>
                <div className="flex justify-between text-[11px] mb-1">
                  <span className="text-slate-600 dark:text-slate-400">Certificate Title Font Size</span>
                  <span className="font-mono font-bold text-amber-600">{data.styling.titleFontSize || 30}px</span>
                </div>
                <input
                  type="range"
                  min="22"
                  max="40"
                  value={data.styling.titleFontSize || 30}
                  onChange={(e) => updateStyling('titleFontSize', parseInt(e.target.value))}
                  className="w-full"
                />
              </div>

              {/* Logo Scale */}
              <div>
                <div className="flex justify-between text-[11px] mb-1">
                  <span className="text-slate-600 dark:text-slate-400">Logo Scale</span>
                  <span className="font-mono font-bold text-amber-600">{Math.round((data.styling.logoScale || 1) * 100)}%</span>
                </div>
                <input
                  type="range"
                  min="0.7"
                  max="1.4"
                  step="0.05"
                  value={data.styling.logoScale || 1}
                  onChange={(e) => updateStyling('logoScale', parseFloat(e.target.value))}
                  className="w-full"
                />
              </div>

              {/* Quick Toggle */}
              <div className="flex items-center justify-between pt-1">
                <span className="text-[11px] text-slate-600 dark:text-slate-400">
                  {uiLang === 'km' ? 'បង្ហាញក្បាច់សងខាង Logo' : 'Show Flanking Logo Ornament'}
                </span>
                <input
                  type="checkbox"
                  checked={activeLogoOrnament !== 'none'}
                  onChange={(e) =>
                    handleSelectLogoOrnament(e.target.checked ? 'classic-laurel' : 'none')
                  }
                  className="rounded text-amber-600"
                />
              </div>

              {renderLogoOrnamentPickers()}
            </div>

            {/* Custom Color Pickers & Paper Background */}
            <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                  {uiLang === 'km' ? 'កែពណ៌ផ្ទៃក្រដាស & អក្សរទាំងអស់' : 'Paper Background & Color Calibration'}
                </span>
                <button
                  type="button"
                  onClick={() =>
                    onChange({
                      ...data,
                      styling: {
                        ...data.styling,
                        bgColor: '#FCFBF7',
                        primaryColor: '#092653',
                        accentColor: '#D4A32C',
                        secondaryTextColor: undefined,
                        signatureInkColor: undefined,
                      },
                    })
                  }
                  className="text-[10px] font-semibold text-amber-700 hover:underline cursor-pointer"
                >
                  Reset Colors
                </button>
              </div>

              {/* Paper Swatches + Picker */}
              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="text-[10px] font-semibold text-slate-500">
                    {uiLang === 'km' ? 'ពណ៌ផ្ទៃក្រដាស (Paper Background)' : 'Paper Canvas Background'}
                  </label>
                  <div className="flex items-center gap-1">
                    {[
                      { hex: '#FCFBF7', name: 'Luxury Ivory' },
                      { hex: '#FFFFFF', name: 'Pure White' },
                      { hex: '#F5ECD8', name: 'Royal Parchment' },
                      { hex: '#F0F6FF', name: 'Ice Navy' },
                      { hex: '#FDF2F4', name: 'Rose Pearl' },
                    ].map((sw) => (
                      <button
                        key={sw.hex}
                        type="button"
                        onClick={() => updateStyling('bgColor', sw.hex)}
                        className={`w-4 h-4 rounded-full border cursor-pointer transition-transform hover:scale-110 ${
                          (data.styling.bgColor || '#FCFBF7').toUpperCase() === sw.hex
                            ? 'ring-2 ring-amber-500 border-slate-400'
                            : 'border-slate-300'
                        }`}
                        style={{ backgroundColor: sw.hex }}
                        title={sw.name}
                      />
                    ))}
                    <input
                      type="color"
                      value={data.styling.bgColor || '#FCFBF7'}
                      onChange={(e) => updateStyling('bgColor', e.target.value)}
                      className="w-5 h-5 rounded border border-slate-300 cursor-pointer"
                      title="Custom paper color"
                    />
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block text-[10px] text-slate-500 mb-1">Primary Ink (Navy)</label>
                  <div className="flex items-center gap-1.5">
                    <input
                      type="color"
                      value={data.styling.primaryColor}
                      onChange={(e) => updateStyling('primaryColor', e.target.value)}
                      className="w-7 h-7 rounded border cursor-pointer"
                    />
                    <input
                      type="text"
                      value={data.styling.primaryColor}
                      onChange={(e) => updateStyling('primaryColor', e.target.value)}
                      className="w-20 px-1.5 py-1 text-[11px] font-mono border rounded bg-white dark:bg-slate-900"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-[10px] text-slate-500 mb-1">Accent Gold Foil</label>
                  <div className="flex items-center gap-1.5">
                    <input
                      type="color"
                      value={data.styling.accentColor}
                      onChange={(e) => updateStyling('accentColor', e.target.value)}
                      className="w-7 h-7 rounded border cursor-pointer"
                    />
                    <input
                      type="text"
                      value={data.styling.accentColor}
                      onChange={(e) => updateStyling('accentColor', e.target.value)}
                      className="w-20 px-1.5 py-1 text-[11px] font-mono border rounded bg-white dark:bg-slate-900"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-[10px] text-slate-500 mb-1">Citation Ink</label>
                  <div className="flex items-center gap-1.5">
                    <input
                      type="color"
                      value={data.styling.secondaryTextColor || '#16305C'}
                      onChange={(e) => updateStyling('secondaryTextColor', e.target.value)}
                      className="w-7 h-7 rounded border cursor-pointer"
                    />
                    <input
                      type="text"
                      value={data.styling.secondaryTextColor || '#16305C'}
                      onChange={(e) => updateStyling('secondaryTextColor', e.target.value)}
                      className="w-20 px-1.5 py-1 text-[11px] font-mono border rounded bg-white dark:bg-slate-900"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-[10px] text-slate-500 mb-1">Signature Ink</label>
                  <div className="flex items-center gap-1.5">
                    <input
                      type="color"
                      value={data.styling.signatureInkColor || data.styling.primaryColor || '#092653'}
                      onChange={(e) => updateStyling('signatureInkColor', e.target.value)}
                      className="w-7 h-7 rounded border cursor-pointer"
                    />
                    <input
                      type="text"
                      value={data.styling.signatureInkColor || data.styling.primaryColor || '#092653'}
                      onChange={(e) => updateStyling('signatureInkColor', e.target.value)}
                      className="w-20 px-1.5 py-1 text-[11px] font-mono border rounded bg-white dark:bg-slate-900"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* Hanging Medal Ribbon & Architectural Accents Customizer */}
            <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 font-khmer-sans">
                  {uiLang === 'km' ? 'កែមេដាយព្យួរ & ស្លឹកមាស (Medal & Laurels)' : 'Hanging Medal & Decorative Accents'}
                </span>
                <label className="flex items-center gap-1 text-[10px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={vis.showHangingMedal ?? defaultShowHangingMedal}
                    onChange={(e) => updateVisibility('showHangingMedal', e.target.checked)}
                    className="rounded text-amber-600"
                  />
                  <span>Show Medal</span>
                </label>
              </div>

              {(vis.showHangingMedal ?? defaultShowHangingMedal) && isExecutiveVerifyLayout && (
                <div className="space-y-2">
                  <div className="grid grid-cols-3 gap-1">
                    {[
                      { id: 'auto', label: 'Auto' },
                      { id: 'navy-sash', label: 'Navy Sash' },
                      { id: 'gold-sash', label: 'Gold Sash' },
                    ].map((ms) => (
                      <button
                        key={ms.id}
                        type="button"
                        onClick={() => updateField('hangingMedalStyle', ms.id as any)}
                        className={`py-1 rounded text-[10px] font-semibold border cursor-pointer ${
                          (data.hangingMedalStyle || 'auto') === ms.id
                            ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 font-bold'
                            : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600'
                        }`}
                      >
                        {ms.label}
                      </button>
                    ))}
                  </div>
                  <div>
                    <div className="flex justify-between text-[10px] mb-0.5">
                      <span className="text-slate-500">Medal Size Scale</span>
                      <span className="font-mono font-bold text-amber-600">
                        {Math.round((data.hangingMedalScale ?? 1) * 100)}%
                      </span>
                    </div>
                    <input
                      type="range"
                      min="0.7"
                      max="1.25"
                      step="0.05"
                      value={data.hangingMedalScale ?? 1}
                      onChange={(e) => updateField('hangingMedalScale', parseFloat(e.target.value))}
                      className="w-full"
                    />
                  </div>
                </div>
              )}

              <div className="grid grid-cols-2 gap-1.5 pt-1 border-t border-slate-200/70 dark:border-slate-700">
                <label className="flex items-center justify-between px-2 py-1 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[10px] font-semibold cursor-pointer">
                  <span>Title Laurels</span>
                  <input
                    type="checkbox"
                    checked={vis.showTitleLaurels ?? defaultShowTitleLaurels}
                    onChange={(e) => updateVisibility('showTitleLaurels', e.target.checked)}
                    className="rounded text-amber-600"
                  />
                </label>
                <label className="flex items-center justify-between px-2 py-1 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-[10px] font-semibold cursor-pointer">
                  <span>Storefront BG</span>
                  <input
                    type="checkbox"
                    checked={vis.showStorefrontWatermark ?? defaultShowStorefrontWm}
                    onChange={(e) => updateVisibility('showStorefrontWatermark', e.target.checked)}
                    className="rounded text-amber-600"
                  />
                </label>
              </div>
            </div>

            {/* Layout Template Architecture (V1 – V4) */}
            <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
              <label className="block text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                {uiLang === 'km' ? 'ទម្រង់វិញ្ញាបនបត្រ (V1 – V6)' : 'Form Layout Structure (V1 – V6)'}
              </label>
              <div className="grid grid-cols-2 gap-1.5">
                {LAYOUT_TEMPLATE_VERSIONS.map((lt) => {
                  const isActive = (data.layoutTemplate || 'classic') === lt.id;
                  return (
                    <button
                      key={lt.id}
                      type="button"
                      onClick={() => switchFormVersion(lt.id)}
                      className={`px-2 py-2 text-left rounded-lg border cursor-pointer transition-all ${
                        isActive
                          ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 font-bold'
                          : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:border-amber-300'
                      }`}
                    >
                      <div className="text-[11px] font-bold flex items-center justify-between">
                        <span>{lt.versionTag} · {lt.label}</span>
                        {isActive && <Check className="w-3 h-3 text-amber-600 shrink-0" />}
                      </div>
                      <div className="text-[9.5px] font-normal text-slate-500 dark:text-slate-400 truncate mt-0.5 font-khmer-sans">
                        {uiLang === 'km' ? lt.khmerLabel : lt.shortDesc}
                      </div>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Presentation Banner Ribbon Styles (4 Cloned Vector Ribbons) */}
            <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5">
              <label className="block text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 font-khmer-sans">
                {uiLang === 'km'
                  ? `ម៉ូតបដាបង្ហាញឈ្មោះ (${BANNER_RIBBON_STYLES.length} ម៉ូត)`
                  : `Presentation Banner Ribbons (${BANNER_RIBBON_STYLES.length})`}
              </label>
              <div className="grid grid-cols-2 gap-2">
                {BANNER_RIBBON_STYLES.map((bn) => {
                  const isActive = (data.styling.bannerStyle || 'gold-swallowtail') === bn.id;
                  return (
                    <button
                      key={bn.id}
                      type="button"
                      onClick={() => updateStyling('bannerStyle', bn.id as BannerStyleOption)}
                      className={`p-1.5 rounded-lg border text-left cursor-pointer transition-all ${
                        isActive
                          ? 'border-amber-500 ring-1 ring-amber-400/50 bg-amber-50/70 dark:bg-amber-950/30'
                          : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-amber-300'
                      }`}
                    >
                      <div className="h-8 w-full rounded bg-[#FCFBF7] border border-slate-200/70 flex items-center justify-center overflow-hidden mb-1 px-1">
                        <div className="scale-[0.28] origin-center pointer-events-none">
                          <PresentationBannerRibbon
                            style={bn.id}
                            text="PRESENTED TO"
                            accentColor={data.styling.accentColor}
                            primaryColor={data.styling.primaryColor}
                            width={480}
                          />
                        </div>
                      </div>
                      <div className="flex items-center justify-between gap-1 px-0.5">
                        <div className="min-w-0">
                          <div className="text-[10.5px] font-bold text-slate-800 dark:text-slate-200 truncate">
                            {bn.label}
                          </div>
                          {uiLang === 'km' && (
                            <div className="text-[9.5px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate">
                              {bn.khmerLabel}
                            </div>
                          )}
                        </div>
                        {isActive && <Check className="w-3 h-3 text-amber-600 shrink-0" />}
                      </div>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Watermark & Guilloche Security Pattern Picker (6 Cloned Patterns) */}
            <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5">
              <div className="flex items-center justify-between gap-2">
                <label className="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 font-khmer-sans">
                  {uiLang === 'km'
                    ? `ក្បាច់ផ្ទៃខាងក្រោយ & Guilloche (${WATERMARK_STYLES.length})`
                    : `Watermark & Guilloche (${WATERMARK_STYLES.length})`}
                </label>
                <label className="flex items-center gap-1 text-[10px] font-semibold text-slate-600 dark:text-slate-300 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={data.styling.showWatermark !== false}
                    onChange={(e) => updateStyling('showWatermark', e.target.checked)}
                    className="rounded text-amber-600"
                  />
                  <span>{uiLang === 'km' ? 'បង្ហាញ' : 'Enable'}</span>
                </label>
              </div>

              <div className="grid grid-cols-2 gap-2">
                {WATERMARK_STYLES.map((wm) => {
                  const isActive = (data.styling.watermarkType || 'laurel') === wm.id;
                  return (
                    <button
                      key={wm.id}
                      type="button"
                      onClick={() =>
                        onChange({
                          ...data,
                          styling: {
                            ...data.styling,
                            showWatermark: true,
                            watermarkType: wm.id as WatermarkStyleOption,
                          },
                        })
                      }
                      className={`p-1.5 rounded-lg border text-left cursor-pointer transition-all ${
                        isActive
                          ? 'border-amber-500 ring-1 ring-amber-400/50 bg-amber-50/70 dark:bg-amber-950/30'
                          : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-amber-300'
                      }`}
                    >
                      <div className="relative h-12 w-full rounded bg-[#FCFBF7] border border-slate-200/70 overflow-hidden mb-1">
                        <WatermarkPatternLayer
                          type={wm.id}
                          opacity={0.45}
                          primaryColor={data.styling.primaryColor}
                          accentColor={data.styling.accentColor}
                          issuerName={data.issuer.name}
                          issuerDepartment={data.issuer.department}
                          showLaurelSides={false}
                        />
                        {wm.id === 'laurel' && (
                          <div className="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-amber-700/75">
                            Side Laurels & Waves
                          </div>
                        )}
                      </div>
                      <div className="flex items-center justify-between gap-1 px-0.5">
                        <div className="min-w-0">
                          <div className="text-[10.5px] font-bold text-slate-800 dark:text-slate-200 truncate">
                            {wm.label}
                          </div>
                          {uiLang === 'km' && (
                            <div className="text-[9.5px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate">
                              {wm.khmerLabel}
                            </div>
                          )}
                        </div>
                        {isActive && <Check className="w-3 h-3 text-amber-600 shrink-0" />}
                      </div>
                    </button>
                  );
                })}
              </div>

              {data.styling.showWatermark !== false && (
                <div>
                  <div className="flex justify-between text-[10.5px] mb-0.5">
                    <span className="text-slate-600 dark:text-slate-400">
                      {uiLang === 'km' ? 'ភាពច្បាស់ផ្ទៃខាងក្រោយ (Opacity)' : 'Pattern Opacity'}
                    </span>
                    <span className="font-mono font-bold text-amber-600">
                      {Math.round((data.styling.watermarkOpacity || 0.14) * 100)}%
                    </span>
                  </div>
                  <input
                    type="range"
                    min="0.04"
                    max="0.4"
                    step="0.02"
                    value={data.styling.watermarkOpacity || 0.14}
                    onChange={(e) => updateStyling('watermarkOpacity', parseFloat(e.target.value))}
                    className="w-full"
                  />
                </div>
              )}
            </div>

            {/* Border Frame Style Gallery (Switchable SVG Frames + Hide Frame) */}
            <div>
              <div className="flex items-center justify-between mb-2">
                <label className="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                  <Frame className="w-3.5 h-3.5 text-amber-600" />
                  <span>
                    {uiLang === 'km'
                      ? `ម៉ូតស៊ុមវិញ្ញាបនបត្រ (${FRAME_STYLES.length} ម៉ូត)`
                      : `Certificate Frame Styles (${FRAME_STYLES.length})`}
                  </span>
                </label>
                <button
                  type="button"
                  onClick={() =>
                    updateStyling(
                      'borderStyle',
                      data.styling.borderStyle === 'none' ? lastBorderStyle : 'none'
                    )
                  }
                  className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold border cursor-pointer ${
                    data.styling.borderStyle === 'none'
                      ? 'border-slate-300 bg-slate-100 text-slate-600'
                      : 'border-emerald-300 bg-emerald-50 text-emerald-700'
                  }`}
                >
                  {data.styling.borderStyle === 'none' ? (
                    <EyeOff className="w-3 h-3" />
                  ) : (
                    <Eye className="w-3 h-3" />
                  )}
                  <span>{data.styling.borderStyle === 'none' ? 'Hidden' : 'Visible'}</span>
                </button>
              </div>

              {/* Frame Category Filter */}
              <div className="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-lg mb-2.5">
                {[
                  { id: 'all', label: uiLang === 'km' ? 'ទាំងអស់' : 'All' },
                  { id: 'ribbon', label: uiLang === 'km' ? 'បូជ្រុង' : 'Ribbons' },
                  { id: 'heritage', label: uiLang === 'km' ? 'បុរាណ/ក្បាច់' : 'Heritage' },
                  { id: 'modern', label: uiLang === 'km' ? 'ទំនើប' : 'Modern' },
                ].map((fc) => (
                  <button
                    key={fc.id}
                    type="button"
                    onClick={() => setFrameFilter(fc.id as any)}
                    className={`flex-1 py-1 text-[10.5px] font-semibold rounded-md cursor-pointer transition-colors ${
                      frameFilter === fc.id
                        ? 'bg-white dark:bg-slate-700 text-amber-700 dark:text-amber-300 shadow-2xs'
                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'
                    }`}
                  >
                    {fc.label}
                  </button>
                ))}
              </div>

              <div className="grid grid-cols-2 gap-2.5">
                {FRAME_STYLES.filter((f) => frameFilter === 'all' || f.category === frameFilter).map((b) => {
                  const isActive = data.styling.borderStyle === b.id;
                  return (
                    <button
                      key={b.id}
                      type="button"
                      onClick={() => updateStyling('borderStyle', b.id as BorderStyleOption)}
                      className={`p-1.5 rounded-xl border text-left cursor-pointer transition-all group relative ${
                        isActive
                          ? 'border-amber-500 ring-1 ring-amber-400/50 bg-amber-50/70 dark:bg-amber-950/30 shadow-xs'
                          : 'border-slate-200 dark:border-slate-800 hover:border-amber-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900'
                      }`}
                    >
                      {/* Live Miniature SVG Frame Thumbnail */}
                      <div className="relative w-full aspect-[106/75] bg-[#FCFBF7] rounded-md overflow-hidden border border-slate-200/80 mb-1.5">
                        <CertificateBorders
                          style={b.id}
                          primaryColor={b.badgeColor}
                          accentColor={b.secondaryColor}
                        />
                        <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none opacity-45">
                          <div className="w-8 h-1 rounded-full mb-1" style={{ backgroundColor: b.badgeColor }} />
                          <div className="w-12 h-0.5 rounded-full" style={{ backgroundColor: b.secondaryColor }} />
                        </div>
                        {isActive && (
                          <span className="absolute top-1 right-1 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-xs z-10">
                            <Check className="w-2.5 h-2.5" strokeWidth={3} />
                          </span>
                        )}
                      </div>

                      <div className="px-0.5">
                        <div
                          className={`text-[11px] leading-tight truncate ${
                            isActive ? 'font-bold text-amber-800 dark:text-amber-300' : 'font-semibold text-slate-800 dark:text-slate-200'
                          }`}
                        >
                          {b.label}
                        </div>
                        {uiLang === 'km' && (
                          <div className="text-[9.5px] font-khmer-sans text-slate-500 dark:text-slate-400 truncate mt-0.5">
                            {b.khmerLabel}
                          </div>
                        )}
                      </div>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Typography */}
            <div>
              <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Display Headline Font
              </label>
              <div className="grid grid-cols-2 gap-2">
                {[
                  { id: 'cinzel', label: 'Cinzel (Monumental)' },
                  { id: 'khmer', label: 'Khmer Heritage (Moul)' },
                  { id: 'playfair', label: 'Playfair (Editorial)' },
                  { id: 'cormorant', label: 'Cormorant (Classical)' },
                  { id: 'sans', label: 'Plus Jakarta (Clean)' },
                ].map((f) => (
                  <button
                    key={f.id}
                    type="button"
                    onClick={() => updateStyling('fontDisplay', f.id as FontStyleOption)}
                    className={`px-2.5 py-1.5 text-xs rounded-lg border text-left cursor-pointer ${
                      data.styling.fontDisplay === f.id
                        ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700 font-bold'
                        : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300'
                    }`}
                  >
                    {f.label}
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* ===================== TAB 4: DIGITAL HR INTEGRATION ===================== */}
        {tab === 'hr' && (
          <div className="space-y-4">
            {/* Highlight Digital HR Hours Box */}
            <div className="p-4 rounded-xl bg-gradient-to-r from-amber-50 to-amber-100/60 dark:from-amber-950/40 dark:to-amber-900/30 border border-amber-300 dark:border-amber-800 flex flex-col gap-3">
              <div className="flex items-center justify-between">
                <div>
                  <span className="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 block">
                    Accredited Digital HR Training Hours
                  </span>
                  <div className="flex items-baseline gap-2 mt-0.5">
                    <span className="text-3xl font-extrabold font-mono text-amber-900 dark:text-amber-200 tabular-nums">
                      {data.digitalHr?.trainingHours || 60}
                    </span>
                    <span className="text-xs font-semibold text-amber-800 dark:text-amber-300">
                      Digital Hours Certified
                    </span>
                  </div>
                </div>

                <button
                  type="button"
                  onClick={onOpenDigitalHrModal}
                  className="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow-xs cursor-pointer"
                >
                  Open HR Hub
                </button>
              </div>

              {/* Quick Stepper Buttons */}
              <div className="flex items-center gap-1.5 pt-1 border-t border-amber-200 dark:border-amber-800/60">
                <span className="text-[11px] text-amber-800 dark:text-amber-300 font-medium">Quick Credit:</span>
                <button
                  type="button"
                  onClick={() => updateDigitalHr('trainingHours', (data.digitalHr?.trainingHours || 0) + 5)}
                  className="px-2 py-0.5 bg-white dark:bg-slate-800 border border-amber-300 rounded font-semibold text-amber-800 text-[11px] cursor-pointer"
                >
                  +5 hrs
                </button>
                <button
                  type="button"
                  onClick={() => updateDigitalHr('trainingHours', (data.digitalHr?.trainingHours || 0) + 10)}
                  className="px-2 py-0.5 bg-white dark:bg-slate-800 border border-amber-300 rounded font-semibold text-amber-800 text-[11px] cursor-pointer"
                >
                  +10 hrs
                </button>
                <button
                  type="button"
                  onClick={() => updateDigitalHr('trainingHours', (data.digitalHr?.trainingHours || 0) + 20)}
                  className="px-2 py-0.5 bg-white dark:bg-slate-800 border border-amber-300 rounded font-semibold text-amber-800 text-[11px] cursor-pointer"
                >
                  +20 hrs
                </button>
              </div>
            </div>

            {/* Employee ID */}
            <div>
              <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                Employee HR ID
              </label>
              <input
                type="text"
                value={data.digitalHr?.employeeId || ''}
                onChange={(e) => updateDigitalHr('employeeId', e.target.value)}
                placeholder="e.g. EMP-KY-2026-08"
                className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono font-bold"
              />
            </div>

            {/* Competency Track */}
            <div>
              <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                HR Competency Track
              </label>
              <input
                type="text"
                value={data.digitalHr?.hrCompetencyTrack || ''}
                onChange={(e) => updateDigitalHr('hrCompetencyTrack', e.target.value)}
                placeholder="e.g. Transformational Team Leadership"
                className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg"
              />
            </div>

            {/* Display Badge Toggle */}
            <div className="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
              <input
                type="checkbox"
                id="sidebarBadgeToggle"
                checked={data.digitalHr?.showDigitalHoursBadge !== false}
                onChange={(e) => updateDigitalHr('showDigitalHoursBadge', e.target.checked)}
                className="rounded text-amber-600"
              />
              <label htmlFor="sidebarBadgeToggle" className="font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                Show "Digital HR Certified Hours" Gold Badge on Certificate
              </label>
            </div>
          </div>
        )}

        {/* ===================== TAB 5: SEALS & SIGNATURES ===================== */}
        {tab === 'signatures' && (
          <div className="space-y-4">
            {/* Official Seal Controls */}
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <label className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                  Official Seal / Stamp
                </label>
                <input
                  type="checkbox"
                  checked={data.seal.enabled}
                  onChange={(e) => updateSeal('enabled', e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500"
                />
              </div>

              {data.seal.enabled && (
                <>
                  <div className="grid grid-cols-2 gap-1.5">
                    {SEAL_STYLE_CATALOG.map((s) => (
                      <button
                        key={s.id}
                        type="button"
                        onClick={() => updateSeal('style', s.id as SealStyleOption)}
                        className={`p-2 rounded-lg border text-left cursor-pointer transition-all ${
                          data.seal.style === s.id
                            ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-200 font-bold ring-1 ring-amber-400/40'
                            : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-amber-300'
                        }`}
                      >
                        <div className="text-[11px] font-bold leading-tight truncate">{s.label}</div>
                        <div className="text-[9.5px] font-khmer-sans font-normal text-slate-500 dark:text-slate-400 truncate mt-0.5">
                          {s.khmerLabel}
                        </div>
                      </button>
                    ))}
                  </div>

                  {/* Ribbon Tail Color Selector */}
                  <div>
                    <label className="block text-[10px] uppercase font-semibold text-slate-500 mb-1">
                      Seal Satin Ribbon Color
                    </label>
                    <div className="grid grid-cols-4 gap-1.5">
                      {[
                        { id: 'navy', label: 'Royal Navy' },
                        { id: 'ruby', label: 'Crimson' },
                        { id: 'emerald', label: 'Emerald' },
                        { id: 'gold', label: 'Gold' },
                      ].map((rc) => (
                        <button
                          key={rc.id}
                          type="button"
                          onClick={() => updateSeal('ribbonColor', rc.id)}
                          className={`py-1.5 px-2 text-[11px] font-medium rounded-lg border text-center cursor-pointer ${
                            (data.seal.ribbonColor || 'ruby') === rc.id
                              ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700 font-bold'
                              : 'border-slate-200 dark:border-slate-800 text-slate-600'
                          }`}
                        >
                          {rc.label}
                        </button>
                      ))}
                    </div>
                  </div>

                  <div>
                    <label className="block text-[10px] uppercase font-semibold text-slate-500 mb-1">
                      Seal Curved Top Text
                    </label>
                    <input
                      type="text"
                      value={data.seal.primaryText}
                      onChange={(e) => updateSeal('primaryText', e.target.value)}
                      className="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg uppercase font-bold"
                    />
                  </div>

                  <div>
                    <label className="block text-[10px] uppercase font-semibold text-slate-500 mb-1">
                      Seal Bottom Subtext
                    </label>
                    <input
                      type="text"
                      value={data.seal.secondaryText}
                      onChange={(e) => updateSeal('secondaryText', e.target.value)}
                      className="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg uppercase"
                    />
                  </div>
                </>
              )}

              {/* QR Verification Toggle */}
              <div className="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                <span className="text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                  Show QR Credential Code
                </span>
                <input
                  type="checkbox"
                  checked={data.showQrCode}
                  onChange={(e) => updateField('showQrCode', e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500"
                />
              </div>
            </div>

            {/* Authorized Signatories */}
            <div className="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
              <div className="flex items-center justify-between flex-wrap gap-1.5">
                <div className="flex items-center gap-2">
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {uiLang === 'km' ? 'អ្នកចុះហត្ថលេខា (Signatories & HR)' : 'Authorized Signatories'}
                  </label>
                  {renderInlineEyeToggle('showSignatories', true)}
                </div>
                <button
                  type="button"
                  onClick={handleAddHrSignatory}
                  className="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-600 hover:bg-amber-700 text-white text-[10.5px] font-bold cursor-pointer shadow-2xs transition-colors"
                >
                  <Plus className="w-3 h-3" />
                  <span>{uiLang === 'km' ? 'បន្ថែមហត្ថលេខា HR' : 'Add HR Signature'}</span>
                </button>
              </div>

              <div className="flex flex-col gap-1.5 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <div className="flex items-center justify-between">
                  <span className="text-[10.5px] font-semibold text-slate-600 dark:text-slate-300">
                    Signatory Name Font
                  </span>
                  <FieldTypographyControl
                    compact
                    fieldKey="signatoryName"
                    value={data.styling.fieldStyles?.signatoryName}
                    onChange={(s) => updateFieldStyle('signatoryName', s)}
                    uiLang={uiLang}
                  />
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-[10.5px] font-semibold text-slate-600 dark:text-slate-300">
                    Role & Sub-Tags Font
                  </span>
                  <FieldTypographyControl
                    compact
                    fieldKey="signatoryRole"
                    value={data.styling.fieldStyles?.signatoryRole}
                    onChange={(s) => updateFieldStyle('signatoryRole', s)}
                    uiLang={uiLang}
                  />
                </div>
              </div>

              {data.signatories.map((sig, idx) => {
                const isHr = /hr|human\s*resource|people/i.test(`${sig.role} ${sig.khmerRole || ''} ${sig.organization || ''}`);
                const isSigHidden = Boolean(sig.hidden);
                return (
                  <div
                    key={sig.id || idx}
                    className={`p-3 border rounded-xl space-y-2 transition-opacity ${
                      isSigHidden
                        ? 'bg-slate-100/70 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-800 opacity-65'
                        : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700'
                    }`}
                  >
                    <div className="flex items-center justify-between gap-2">
                      <div className="flex items-center gap-1.5">
                        <span className="font-semibold text-xs text-slate-800 dark:text-slate-200">
                          Signatory #{idx + 1}
                        </span>
                        {isHr && (
                          <span className="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-[9.5px] font-bold">
                            HR
                          </span>
                        )}
                        {isSigHidden && (
                          <span className="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[9.5px] font-bold">
                            Hidden
                          </span>
                        )}
                      </div>
                      <div className="flex items-center gap-1.5">
                        <button
                          type="button"
                          onClick={() => {
                            const updated = [...data.signatories];
                            updated[idx] = { ...updated[idx], hidden: !isSigHidden };
                            updateField('signatories', updated);
                          }}
                          className={`inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border cursor-pointer ${
                            isSigHidden
                              ? 'border-slate-300 bg-white text-slate-600'
                              : 'border-emerald-300 bg-emerald-50 text-emerald-700'
                          }`}
                          title={isSigHidden ? 'Show signatory on certificate' : 'Hide signatory from certificate'}
                        >
                          {isSigHidden ? <EyeOff className="w-3 h-3" /> : <Eye className="w-3 h-3" />}
                          <span>{isSigHidden ? 'Show' : 'Hide'}</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => onOpenSignatureModal(idx)}
                          className="inline-flex items-center gap-1 text-[11px] font-medium text-amber-600 hover:text-amber-700 cursor-pointer"
                        >
                          <PenTool className="w-3 h-3" />
                          <span>Edit</span>
                        </button>
                        {data.signatories.length > 1 && (
                          <button
                            type="button"
                            onClick={() => handleRemoveSignatory(idx)}
                            className="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded cursor-pointer"
                            title="Remove signatory"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        )}
                      </div>
                    </div>

                    <input
                      type="text"
                      value={sig.name}
                      onChange={(e) => {
                        const updated = [...data.signatories];
                        updated[idx] = { ...updated[idx], name: e.target.value };
                        updateField('signatories', updated);
                      }}
                      placeholder="Signatory Name (English)"
                      className="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md font-medium"
                    />

                    <input
                      type="text"
                      value={sig.khmerName || ''}
                      onChange={(e) => {
                        const updated = [...data.signatories];
                        updated[idx] = { ...updated[idx], khmerName: e.target.value };
                        updateField('signatories', updated);
                      }}
                      placeholder="Signatory Name (Khmer - ឈ្មោះខ្មែរ)"
                      className="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md font-khmer-sans text-xs"
                    />

                    <input
                      type="text"
                      value={sig.role}
                      onChange={(e) => {
                        const updated = [...data.signatories];
                        updated[idx] = { ...updated[idx], role: e.target.value };
                        updateField('signatories', updated);
                      }}
                      placeholder="Title (e.g. HR Manager / Branch Manager)"
                      className="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md"
                    />

                    <input
                      type="text"
                      value={sig.khmerRole || ''}
                      onChange={(e) => {
                        const updated = [...data.signatories];
                        updated[idx] = { ...updated[idx], khmerRole: e.target.value };
                        updateField('signatories', updated);
                      }}
                      placeholder="Khmer Role (e.g. ប្រធានផ្នែកធនធានមនុស្ស HR)"
                      className="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md font-khmer-sans text-xs"
                    />

                    <input
                      type="text"
                      value={sig.organization || ''}
                      onChange={(e) => {
                        const updated = [...data.signatories];
                        updated[idx] = { ...updated[idx], organization: e.target.value };
                        updateField('signatories', updated);
                      }}
                      placeholder="Sub-tags (e.g. Human Resources Department)"
                      className="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md text-xs"
                    />
                  </div>
                );
              })}
            </div>
          </div>
        )}
      </div>
    </aside>
  );
};
