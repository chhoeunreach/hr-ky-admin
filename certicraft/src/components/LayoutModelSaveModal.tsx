import React, { useEffect, useState } from 'react';
import {
  AlertTriangle,
  Check,
  ExternalLink,
  Layers,
  Save,
  Star,
  X,
} from 'lucide-react';
import { CertificateData } from '../types/certificate';

export interface LayoutModelSavePayload {
  /** Existing template code to update, or empty to create a new layout model */
  templateCode?: string;
  templateTitle: string;
  templateKhmerTitle?: string;
  category: string;
  versionTag: string;
  description?: string;
  khmerDescription?: string;
  isDefault: boolean;
  certificate: CertificateData;
}

export interface LayoutModelSaveResult {
  success: boolean;
  message?: string;
  template?: {
    id?: number;
    template_code?: string;
    title?: string;
    [key: string]: any;
  };
}

interface LayoutModelSaveModalProps {
  isOpen: boolean;
  onClose: () => void;
  data: CertificateData;
  uiLang?: 'en' | 'km';
  templatesUrl?: string;
  onSave: (payload: LayoutModelSavePayload) => Promise<LayoutModelSaveResult>;
  /** Called after a successful save so the studio can keep the new model active */
  onSaved?: (template: LayoutModelSaveResult['template']) => void;
}

const CATEGORY_OPTIONS = [
  { id: 'leader', en: 'Leadership', km: 'មេដឹកនាំឆ្នើម' },
  { id: 'staff', en: 'Best of Staff', km: 'បុគ្គលិកឆ្នើម' },
  { id: 'service', en: 'Customer Service', km: 'សេវាកម្មឆ្នើម' },
  { id: 'course', en: 'Online Course / CPD', km: 'វគ្គសិក្សា CPD' },
  { id: 'custom', en: 'Custom Layout', km: 'គំរូផ្ទាល់ខ្លួន' },
];

export const LayoutModelSaveModal: React.FC<LayoutModelSaveModalProps> = ({
  isOpen,
  onClose,
  data,
  uiLang = 'en',
  templatesUrl,
  onSave,
  onSaved,
}) => {
  const isKm = uiLang === 'km';
  const anyData = data as any;

  const [templateCode, setTemplateCode] = useState('');
  const [templateTitle, setTemplateTitle] = useState('');
  const [templateKhmerTitle, setTemplateKhmerTitle] = useState('');
  const [category, setCategory] = useState('custom');
  const [versionTag, setVersionTag] = useState('V1');
  const [description, setDescription] = useState('');
  const [khmerDescription, setKhmerDescription] = useState('');
  const [isDefault, setIsDefault] = useState(false);
  const [saveAsNew, setSaveAsNew] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [result, setResult] = useState<LayoutModelSaveResult | null>(null);

  useEffect(() => {
    if (!isOpen) return;
    const existingCode = anyData?.templateCode || anyData?.template_code || '';
    setTemplateCode(existingCode);
    setTemplateTitle(anyData?.templateTitle || anyData?.title || data.certificateTitle || '');
    setTemplateKhmerTitle(
      anyData?.templateKhmerTitle || anyData?.khmerTitle || data.khmerTitle || ''
    );
    // Brand new models start in the “Custom” bucket so they are easy to find.
    setCategory(existingCode ? data.category || 'custom' : 'custom');
    setVersionTag(anyData?.versionTag || 'V1');
    setDescription(anyData?.description || '');
    setKhmerDescription(anyData?.khmerDescription || '');
    setIsDefault(false);
    setSaveAsNew(!existingCode);
    setSaving(false);
    setError(null);
    setResult(null);
    // Reset only when reopened; typing must not be overwritten.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const resolvedTitle = templateTitle.trim() || templateKhmerTitle.trim();
    if (!resolvedTitle) {
      setError(isKm ? 'សូមបញ្ចូលឈ្មោះគំរូវិញ្ញាបនបត្រ។' : 'Please enter a layout model name.');
      return;
    }

    setSaving(true);
    setError(null);

    try {
      const response = await onSave({
        templateCode: saveAsNew ? undefined : templateCode.trim() || undefined,
        templateTitle: resolvedTitle,
        templateKhmerTitle: templateKhmerTitle.trim() || resolvedTitle,
        category,
        versionTag: versionTag.trim() || 'V1',
        description: description.trim() || undefined,
        khmerDescription: khmerDescription.trim() || undefined,
        isDefault,
        certificate: data,
      });

      if (response && response.success === false) {
        setError(response.message || (isKm ? 'រក្សាទុកមិនបានជោគជ័យ។' : 'Unable to save layout model.'));
      } else {
        setResult(response || { success: true });
        const savedTemplate = response?.template;
        if (savedTemplate?.template_code) {
          setTemplateCode(savedTemplate.template_code);
        }
        onSaved?.({
          ...(savedTemplate || {}),
          template_code:
            savedTemplate?.template_code || templateCode.trim() || undefined,
          title: savedTemplate?.title || templateTitle.trim(),
          khmer_title:
            savedTemplate?.khmer_title || templateKhmerTitle.trim() || undefined,
        });
      }
    } catch (err: any) {
      setError(err?.message || (isKm ? 'រក្សាទុកមិនបានជោគជ័យ។' : 'Unable to save layout model.'));
    } finally {
      setSaving(false);
    }
  };

  const resolvedTemplatesUrl =
    templatesUrl || '/admin/certificates/templates';

  return (
    <div className="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-xl max-h-[92vh] overflow-y-auto bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800">
        {/* Header */}
        <div className="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/40 dark:to-slate-900">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-amber-600 text-white rounded-lg shadow-sm">
              <Layers className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 font-khmer-sans">
                {saveAsNew
                  ? isKm
                    ? 'រក្សាទុកជា វិញ្ញាបនបត្រ គំរូថ្មី'
                    : 'Save as a new Layout Model'
                  : isKm
                  ? 'កែប្រែ វិញ្ញាបនបត្រ គំរូ'
                  : 'Update Layout Model'}
              </h3>
              <p className="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                {isKm
                  ? 'រក្សាទុក layout បច្ចុប្បន្នទៅបញ្ជី វិញ្ញាបនបត្រ គំរូ ក្នុង CertiCraft Studio។'
                  : 'Save the current certificate layout into the CertiCraft Studio templates list.'}
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1.5 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 cursor-pointer shrink-0"
            title={isKm ? 'បិទ' : 'Close'}
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {result?.success ? (
          /* Success state */
          <div className="p-6 space-y-4">
            <div className="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900">
              <span className="p-1 rounded-full bg-emerald-600 text-white shrink-0">
                <Check className="w-4 h-4" />
              </span>
              <div className="min-w-0">
                <h4 className="text-sm font-bold text-emerald-900 dark:text-emerald-200">
                  {isKm ? 'គំរូ Layout ត្រូវបានរក្សាទុក!' : 'Layout model saved!'}
                </h4>
                <p className="text-xs text-emerald-800 dark:text-emerald-300 mt-1 break-words">
                  {result.message ||
                    (isKm
                      ? 'គំរូនេះអាចជ្រើសរើសបាននៅទំព័រ វិញ្ញាបនបត្រ គំរូ។'
                      : 'This layout model is now available on the certificate templates page.')}
                </p>
                {(result.template?.template_code || templateCode) && (
                  <div className="mt-2 text-[10.5px] font-mono text-emerald-700 dark:text-emerald-300 bg-white/70 dark:bg-slate-900/60 rounded px-2 py-1 inline-block border border-emerald-200 dark:border-emerald-900">
                    {result.template?.template_code || templateCode}
                  </div>
                )}
              </div>
            </div>

            <div className="flex flex-wrap items-center justify-end gap-2">
              <a
                href={resolvedTemplatesUrl}
                className="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-colors"
              >
                <ExternalLink className="w-3.5 h-3.5" />
                <span>{isKm ? 'មើលបញ្ជី វិញ្ញាបនបត្រ គំរូ' : 'View layout models'}</span>
              </a>
              <button
                type="button"
                onClick={onClose}
                className="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
              >
                {isKm ? 'បន្តកែសម្រួល' : 'Continue editing'}
              </button>
            </div>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="p-5 space-y-4">
            {/* Layout summary */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
              <div className="px-2.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                <div className="text-[9.5px] uppercase tracking-wider text-slate-400 font-bold">
                  {isKm ? 'គំរូ Frame' : 'Layout'}
                </div>
                <div className="text-[11px] font-bold text-slate-800 dark:text-slate-100 truncate">
                  {data.layoutTemplate || 'classic'}
                </div>
              </div>
              <div className="px-2.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                <div className="text-[9.5px] uppercase tracking-wider text-slate-400 font-bold">
                  {isKm ? 'ថ្នាក់' : 'Category'}
                </div>
                <div className="text-[11px] font-bold text-slate-800 dark:text-slate-100 truncate capitalize">
                  {category}
                </div>
              </div>
              <div className="px-2.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                <div className="text-[9.5px] uppercase tracking-wider text-slate-400 font-bold">
                  {isKm ? 'ពណ៌' : 'Theme'}
                </div>
                <div className="text-[11px] font-bold text-slate-800 dark:text-slate-100 truncate">
                  {data.theme}
                </div>
              </div>
              <div className="px-2.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700">
                <div className="text-[9.5px] uppercase tracking-wider text-slate-400 font-bold">
                  KNEAYERNG HRMS
                </div>
                <div className="text-[11px] font-bold text-slate-800 dark:text-slate-100 truncate">
                  {data.digitalHr?.trainingHours || 0}h
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-[10.5px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  {isKm ? 'ឈ្មោះគំរូ (English)' : 'Layout Model Name (English)'} *
                </label>
                <input
                  type="text"
                  value={templateTitle}
                  onChange={(e) => setTemplateTitle(e.target.value)}
                  placeholder="e.g. Executive Leadership Layout V7"
                  className="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100"
                  autoFocus
                />
              </div>
              <div>
                <label className="block text-[10.5px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  {isKm ? 'ឈ្មោះគំរូ (ខ្មែរ)' : 'Layout Model Name (Khmer)'}
                </label>
                <input
                  type="text"
                  value={templateKhmerTitle}
                  onChange={(e) => setTemplateKhmerTitle(e.target.value)}
                  placeholder="ឧ. គំរូវិញ្ញាបនបត្រ មេដឹកនាំ"
                  className="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 font-khmer-sans"
                />
              </div>
            </div>

            {/* Category */}
            <div>
              <label className="block text-[10.5px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                {isKm ? 'ប្រភេទគំរូ' : 'Layout Category'}
              </label>
              <div className="grid grid-cols-2 sm:grid-cols-5 gap-1.5">
                {CATEGORY_OPTIONS.map((option) => {
                  const active = category === option.id;
                  return (
                    <button
                      key={option.id}
                      type="button"
                      onClick={() => setCategory(option.id)}
                      className={`px-2 py-2 rounded-lg border text-[10.5px] font-bold transition-colors cursor-pointer ${
                        active
                          ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-200'
                          : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:border-amber-300'
                      }`}
                    >
                      {isKm ? option.km : option.en}
                    </button>
                  );
                })}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-[10.5px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  {isKm ? 'លេខកំណែ (Version)' : 'Version Tag'}
                </label>
                <input
                  type="text"
                  value={versionTag}
                  onChange={(e) => setVersionTag(e.target.value)}
                  placeholder="V1"
                  className="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 font-mono"
                />
              </div>
              <div className="flex items-end pb-1">
                <label className="flex items-center gap-2 px-3 py-2 w-full rounded-lg border border-amber-200 dark:border-amber-800/70 bg-amber-50/60 dark:bg-amber-950/30 text-[11px] font-semibold text-amber-900 dark:text-amber-200 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={isDefault}
                    onChange={(e) => setIsDefault(e.target.checked)}
                    className="rounded text-amber-600"
                  />
                  <Star className="w-3.5 h-3.5 text-amber-600 shrink-0" />
                  <span>
                    {isKm ? 'កំណត់ជាគំរូលំនាំដើម (Default)' : 'Set as default layout model'}
                  </span>
                </label>
              </div>
            </div>

            <div>
              <label className="block text-[10.5px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                {isKm ? 'ការពិពណ៌នា (English)' : 'Description (English)'}
              </label>
              <textarea
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                rows={2}
                placeholder="Short description for the layout model list"
                className="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 resize-y"
              />
            </div>

            <div>
              <label className="block text-[10.5px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                {isKm ? 'ការពិពណ៌នា (ខ្មែរ)' : 'Description (Khmer)'}
              </label>
              <textarea
                value={khmerDescription}
                onChange={(e) => setKhmerDescription(e.target.value)}
                rows={2}
                placeholder="ការពិពណ៌នាខ្លីៗសម្រាប់បញ្ជីគំរូ"
                className="w-full px-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-100 resize-y font-khmer-sans"
              />
            </div>

            {templateCode && (
              <div className="space-y-2 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <label className="flex items-start gap-2 text-[11px] font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={saveAsNew}
                    onChange={(e) => setSaveAsNew(e.target.checked)}
                    className="mt-0.5 rounded text-amber-600"
                  />
                  <span>
                    {isKm
                      ? 'រក្សាទុកជាគំរូថ្មី (រក្សាគំរូដើមដោយមិនប្តូរ)'
                      : 'Save as a new layout model (keep the original unchanged)'}
                  </span>
                </label>
                <div className="text-[10px] text-slate-400 font-mono">
                  {saveAsNew
                    ? isKm
                      ? 'លេខកូដថ្មីនឹងបង្កើតដោយស្វ័យប្រវត្តិ ហើយបង្ហាញក្នុង Layout Models។'
                      : 'A new code will be generated and the model listed in Layout Models.'
                    : `${isKm ? 'លេខកូដគំរូ: ' : 'Template code: '}${templateCode}`}
                </div>
              </div>
            )}

            {error && (
              <div className="flex items-start gap-2 p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs">
                <AlertTriangle className="w-4 h-4 shrink-0 mt-0.5" />
                <span>{error}</span>
              </div>
            )}

            <div className="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
              <button
                type="button"
                onClick={onClose}
                className="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
              >
                {isKm ? 'បោះបង់' : 'Cancel'}
              </button>
              <button
                type="submit"
                disabled={saving}
                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-xs font-bold transition-colors cursor-pointer"
              >
                <Save className="w-3.5 h-3.5" />
                <span>
                  {saving
                    ? isKm
                      ? 'កំពុងរក្សាទុក...'
                      : 'Saving...'
                    : isKm
                    ? 'រក្សាទុកគំរូ'
                    : 'Save Layout Model'}
                </span>
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
};

export default LayoutModelSaveModal;
