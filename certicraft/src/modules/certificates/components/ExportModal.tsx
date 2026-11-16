import React, { useState } from 'react';
import { X, Download, Printer, FileText, Image as ImageIcon, Code, Check } from 'lucide-react';
import { CertificateData } from '../types/certificate';
import {
  exportCertificateAsImage,
  exportCertificateAsPdf,
  exportCertificateAsJson,
  printCertificate,
} from '../utils/exportUtils';

interface ExportModalProps {
  isOpen: boolean;
  onClose: () => void;
  data: CertificateData;
}

export const ExportModal: React.FC<ExportModalProps> = ({
  isOpen,
  onClose,
  data,
}) => {
  if (!isOpen) return null;

  const [filename, setFilename] = useState(
    `${data.recipientName.toLowerCase().replace(/[^a-z0-9]/g, '_')}_certificate`
  );
  const [scale, setScale] = useState<number>(2);
  const [isExporting, setIsExporting] = useState<boolean>(false);
  const [exportedSuccess, setExportedSuccess] = useState<string | null>(null);

  const getTargetNode = (): HTMLElement | null => {
    return document.getElementById('certificate-print-node');
  };

  const handleExportPng = async () => {
    const node = getTargetNode();
    if (!node) return;
    setIsExporting(true);
    try {
      await exportCertificateAsImage(node, filename, scale);
      setExportedSuccess('PNG image exported successfully!');
      setTimeout(() => setExportedSuccess(null), 3000);
    } catch (err) {
      console.error(err);
    } finally {
      setIsExporting(false);
    }
  };

  const handleExportPdf = async () => {
    const node = getTargetNode();
    if (!node) return;
    setIsExporting(true);
    try {
      await exportCertificateAsPdf(node, data, filename);
      setExportedSuccess('A4 PDF generated and downloaded!');
      setTimeout(() => setExportedSuccess(null), 3000);
    } catch (err) {
      console.error(err);
    } finally {
      setIsExporting(false);
    }
  };

  const handleExportJson = () => {
    exportCertificateAsJson(data, `${filename}.json`);
    setExportedSuccess('JSON template saved!');
    setTimeout(() => setExportedSuccess(null), 3000);
  };

  const handlePrint = () => {
    onClose();
    setTimeout(() => {
      printCertificate();
    }, 150);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-md bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div>
            <h3 className="text-base font-semibold text-slate-900 dark:text-slate-100">
              Export Certificate
            </h3>
            <p className="text-xs text-slate-500 dark:text-slate-400">
              Download print-ready files or send directly to printer
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content */}
        <div className="p-6 space-y-4">
          {/* Filename */}
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              File Name
            </label>
            <input
              type="text"
              value={filename}
              onChange={(e) => setFilename(e.target.value)}
              className="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
            />
          </div>

          {/* Scale Resolution */}
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
              Resolution Quality
            </label>
            <div className="grid grid-cols-3 gap-2">
              {[
                { label: 'Standard (1x)', val: 1 },
                { label: 'High (2x · 150 DPI)', val: 2 },
                { label: 'Ultra (3x · 300 DPI)', val: 3 },
              ].map((res) => (
                <button
                  key={res.val}
                  type="button"
                  onClick={() => setScale(res.val)}
                  className={`py-1.5 text-xs font-medium rounded-lg border text-center transition-all cursor-pointer ${
                    scale === res.val
                      ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300'
                      : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50'
                  }`}
                >
                  {res.label}
                </button>
              ))}
            </div>
          </div>

          {/* Export Action Cards */}
          <div className="grid grid-cols-2 gap-3 pt-2">
            <button
              type="button"
              onClick={handleExportPdf}
              disabled={isExporting}
              className="flex flex-col items-center justify-center p-4 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-amber-500 hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-all cursor-pointer group text-center"
            >
              <div className="p-2.5 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-600 mb-2 group-hover:scale-105 transition-transform">
                <FileText className="w-5 h-5" />
              </div>
              <span className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                Download PDF
              </span>
              <span className="text-[10px] text-slate-500 mt-0.5">
                Print-ready A4 Vector
              </span>
            </button>

            <button
              type="button"
              onClick={handleExportPng}
              disabled={isExporting}
              className="flex flex-col items-center justify-center p-4 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-amber-500 hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-all cursor-pointer group text-center"
            >
              <div className="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 mb-2 group-hover:scale-105 transition-transform">
                <ImageIcon className="w-5 h-5" />
              </div>
              <span className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                Download PNG
              </span>
              <span className="text-[10px] text-slate-500 mt-0.5">
                High-res Graphic
              </span>
            </button>

            <button
              type="button"
              onClick={handlePrint}
              className="flex flex-col items-center justify-center p-4 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-amber-500 hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-all cursor-pointer group text-center"
            >
              <div className="p-2.5 rounded-lg bg-blue-50 dark:bg-blue-950/30 text-blue-600 mb-2 group-hover:scale-105 transition-transform">
                <Printer className="w-5 h-5" />
              </div>
              <span className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                Direct Print
              </span>
              <span className="text-[10px] text-slate-500 mt-0.5">
                Browser Print Dialog
              </span>
            </button>

            <button
              type="button"
              onClick={handleExportJson}
              className="flex flex-col items-center justify-center p-4 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-amber-500 hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-all cursor-pointer group text-center"
            >
              <div className="p-2.5 rounded-lg bg-purple-50 dark:bg-purple-950/30 text-purple-600 mb-2 group-hover:scale-105 transition-transform">
                <Code className="w-5 h-5" />
              </div>
              <span className="text-xs font-semibold text-slate-900 dark:text-slate-100">
                Save JSON Template
              </span>
              <span className="text-[10px] text-slate-500 mt-0.5">
                Reusable Configuration
              </span>
            </button>
          </div>

          {/* Feedback */}
          {isExporting && (
            <div className="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2">
              <div className="w-3.5 h-3.5 border-2 border-amber-600 border-t-transparent rounded-full animate-spin" />
              <span>Rendering high-resolution certificate...</span>
            </div>
          )}

          {exportedSuccess && (
            <div className="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
              <Check className="w-4 h-4 text-emerald-600" />
              <span>{exportedSuccess}</span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
