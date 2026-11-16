import React, { useState } from 'react';
import { X, Users, Download, Plus, Trash2, CheckCircle2 } from 'lucide-react';
import { CertificateData } from '../types/certificate';
import { exportCertificateAsPdf } from '../utils/exportUtils';
import { HrAdminEmployeeInput, normalizeHrEmployee } from '../utils/hrIntegration';

interface BatchGeneratorModalProps {
  isOpen: boolean;
  onClose: () => void;
  baseData: CertificateData;
  externalEmployees?: HrAdminEmployeeInput[];
  onSelectRecipient: (recipientName: string, recipientTitle?: string) => void;
}

interface RecipientRow {
  id: string;
  name: string;
  title: string;
  metric?: string;
}

export const BatchGeneratorModal: React.FC<BatchGeneratorModalProps> = ({
  isOpen,
  onClose,
  baseData,
  externalEmployees,
  onSelectRecipient,
}) => {
  const defaultRows: RecipientRow[] = React.useMemo(() => {
    if (externalEmployees && externalEmployees.length > 0) {
      return externalEmployees.map((emp, idx) => {
        const norm = normalizeHrEmployee(emp, idx);
        return {
          id: norm.id,
          name: norm.name,
          title: norm.role,
          metric: `ID: ${norm.employeeId}`,
        };
      });
    }
    return [
      { id: '1', name: 'Sarah Jenkins', title: 'Senior Support Specialist', metric: 'CSAT: 99.6%' },
      { id: '2', name: 'Marcus Brody', title: 'Lead Support Engineer', metric: 'CSAT: 98.9%' },
      { id: '3', name: 'Elena Rostova', title: 'Operations Lead', metric: 'CSAT: 99.2%' },
      { id: '4', name: 'David Kim', title: 'Support Champion', metric: 'CSAT: 99.0%' },
      { id: '5', name: 'Amara Okafor', title: 'Client Relations Associate', metric: 'CSAT: 99.5%' },
    ];
  }, [externalEmployees]);

  const [rawText, setRawText] = useState(
    () => defaultRows.map((r) => [r.name, r.title, r.metric].filter(Boolean).join(', ')).join('\n')
  );

  const [recipients, setRecipients] = useState<RecipientRow[]>(defaultRows);

  const [isProcessing, setIsProcessing] = useState(false);
  const [progressMsg, setProgressMsg] = useState('');

  if (!isOpen) return null;

  const handleParseText = () => {
    const lines = rawText
      .split('\n')
      .map((l) => l.trim())
      .filter((l) => l.length > 0);

    const parsed: RecipientRow[] = lines.map((line, idx) => {
      const parts = line.split(',').map((p) => p.trim());
      return {
        id: String(Date.now() + idx),
        name: parts[0] || 'Recipient',
        title: parts[1] || '',
        metric: parts[2] || '',
      };
    });

    setRecipients(parsed);
  };

  const handleBatchDownloadPdfs = async () => {
    const node = document.getElementById('certificate-print-node');
    if (!node || recipients.length === 0) return;

    setIsProcessing(true);
    for (let i = 0; i < recipients.length; i++) {
      const r = recipients[i];
      setProgressMsg(`Generating certificate ${i + 1} of ${recipients.length} for ${r.name}...`);
      
      // Temporarily switch active preview
      onSelectRecipient(r.name, r.title);
      // Wait for React to re-render DOM
      await new Promise((resolve) => setTimeout(resolve, 350));

      const updatedNode = document.getElementById('certificate-print-node');
      if (updatedNode) {
        const cleanName = r.name.toLowerCase().replace(/[^a-z0-9]/g, '_');
        await exportCertificateAsPdf(updatedNode, baseData, `certificate_${cleanName}.pdf`);
      }
      await new Promise((resolve) => setTimeout(resolve, 350));
    }

    setProgressMsg('All certificates downloaded successfully!');
    setTimeout(() => {
      setIsProcessing(false);
      setProgressMsg('');
    }, 2500);
  };

  const removeRow = (id: string) => {
    setRecipients(recipients.filter((r) => r.id !== id));
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div className="flex items-center gap-2.5">
            <div className="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 rounded-lg">
              <Users className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-base font-semibold text-slate-900 dark:text-slate-100">
                Batch Certificate Generator
              </h3>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                Generate tailored certificates for multiple students or team members
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content */}
        <div className="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          {/* CSV / Plain Text Input */}
          <div>
            <div className="flex items-center justify-between mb-1.5">
              <label className="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Paste Roster (Name, Title / Role, Extra Metric)
              </label>
              <button
                type="button"
                onClick={handleParseText}
                className="text-xs font-semibold text-amber-600 hover:text-amber-700 cursor-pointer"
              >
                Parse Roster
              </button>
            </div>
            <textarea
              rows={4}
              value={rawText}
              onChange={(e) => setRawText(e.target.value)}
              className="w-full p-2.5 text-xs font-mono bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-500"
              placeholder="Name, Role, Score"
            />
            <p className="text-[11px] text-slate-500 mt-1">
              One recipient per line: Name, Optional Title, Optional Metric
            </p>
          </div>

          {/* Parsed Recipients List */}
          <div>
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Recipients Queue ({recipients.length})
              </span>
            </div>

            <div className="border border-slate-200 dark:border-slate-800 rounded-lg divide-y divide-slate-100 dark:divide-slate-800 max-h-48 overflow-y-auto">
              {recipients.map((r, idx) => (
                <div
                  key={r.id || idx}
                  className="flex items-center justify-between px-3 py-2 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/50"
                >
                  <div className="flex items-center gap-3">
                    <span className="font-mono text-slate-400 text-[11px] w-4">
                      {idx + 1}
                    </span>
                    <div>
                      <span className="font-semibold text-slate-800 dark:text-slate-200">
                        {r.name}
                      </span>
                      {r.title && (
                        <span className="text-slate-500 ml-2">· {r.title}</span>
                      )}
                      {r.metric && (
                        <span className="text-amber-600 ml-2 font-mono text-[11px]">
                          ({r.metric})
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="flex items-center gap-2">
                    <button
                      type="button"
                      onClick={() => {
                        onSelectRecipient(r.name, r.title);
                        onClose();
                      }}
                      className="px-2 py-1 text-[11px] font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded cursor-pointer"
                    >
                      Preview
                    </button>
                    <button
                      type="button"
                      onClick={() => removeRow(r.id)}
                      className="p-1 text-slate-400 hover:text-red-600 rounded cursor-pointer"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Progress message */}
          {progressMsg && (
            <div className="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-800 dark:text-blue-200 flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-blue-600 shrink-0" />
              <span>{progressMsg}</span>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
          <div className="text-xs text-slate-500">
            {recipients.length} certificates ready to batch produce
          </div>
          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg"
            >
              Cancel
            </button>
            <button
              type="button"
              onClick={handleBatchDownloadPdfs}
              disabled={isProcessing || recipients.length === 0}
              className="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-slate-900 dark:bg-slate-100 dark:text-slate-900 rounded-lg hover:bg-slate-800 dark:hover:bg-slate-200 cursor-pointer disabled:opacity-50"
            >
              <Download className="w-4 h-4" />
              <span>Batch Download PDFs</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
