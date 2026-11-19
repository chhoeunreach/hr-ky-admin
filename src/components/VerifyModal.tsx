import React from 'react';
import { X, CheckCircle, ShieldCheck, ExternalLink } from 'lucide-react';
import { CertificateData } from '../types/certificate';

interface VerifyModalProps {
  isOpen: boolean;
  onClose: () => void;
  data: CertificateData;
}

export const VerifyModal: React.FC<VerifyModalProps> = ({
  isOpen,
  onClose,
  data,
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-lg bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        {/* Verification Status Header */}
        <div className="p-6 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/50 flex items-start justify-between">
          <div className="flex items-start gap-3">
            <div className="p-2 bg-emerald-500 text-white rounded-lg shadow-xs">
              <ShieldCheck className="w-6 h-6" />
            </div>
            <div>
              <div className="flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300 font-bold text-sm">
                <span>Verified Credential Record</span>
                <CheckCircle className="w-4 h-4 text-emerald-600" />
              </div>
              <p className="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">
                Cryptographically matched against issuer registry
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Verification Body */}
        <div className="p-6 space-y-4 text-xs">
          <div className="grid grid-cols-2 gap-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
              <span className="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">
                Recipient
              </span>
              <p className="text-sm font-bold text-slate-900 dark:text-slate-100 mt-0.5">
                {data.recipientName}
              </p>
              {data.recipientTitle && (
                <p className="text-[11px] text-slate-500">{data.recipientTitle}</p>
              )}
            </div>
            <div>
              <span className="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">
                Credential ID
              </span>
              <p className="font-mono-code tabular-nums font-bold text-slate-900 dark:text-slate-100 mt-0.5">
                {data.credentialId}
              </p>
            </div>
          </div>

          <div>
            <span className="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">
              Award / Specialization
            </span>
            <p className="font-semibold text-slate-800 dark:text-slate-200 text-sm mt-0.5">
              {data.courseOrAchievementTitle}
            </p>
            <p className="text-slate-500 mt-1 italic font-serif leading-relaxed">
              "{data.citation}"
            </p>
          </div>

          {/* KNEAYERNG HRMS Training Hours Record */}
          {data.digitalHr && (
            <div className="p-3 rounded-lg bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center justify-between">
              <div>
                <span className="text-[10px] uppercase font-bold text-amber-800 dark:text-amber-400 block">
                  KNEAYERNG HRMS Accredited Hours
                </span>
                <span className="text-sm font-extrabold font-mono text-amber-900 dark:text-amber-200">
                  {data.digitalHr.trainingHours} Hours Certified
                </span>
                <span className="text-[11px] text-slate-500 block">
                  Track: {data.digitalHr.hrCompetencyTrack} (ID: {data.digitalHr.employeeId})
                </span>
              </div>
              <span className="px-2 py-0.5 text-[10px] font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 rounded border border-amber-300 dark:border-amber-700">
                {data.digitalHr.complianceStatus}
              </span>
            </div>
          )}

          <div className="grid grid-cols-2 gap-4 pt-2">
            <div>
              <span className="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">
                Issuing Body
              </span>
              <p className="font-medium text-slate-800 dark:text-slate-200 mt-0.5">
                {data.issuer.name}
              </p>
              <p className="text-slate-500 text-[11px]">{data.issuer.department}</p>
            </div>
            <div>
              <span className="text-[10px] uppercase font-semibold text-slate-500 tracking-wider">
                Date Issued
              </span>
              <p className="font-medium text-slate-800 dark:text-slate-200 mt-0.5">
                {data.issueDate}
              </p>
              {data.expiryDate && (
                <p className="text-slate-500 text-[11px]">Valid until: {data.expiryDate}</p>
              )}
            </div>
          </div>

          {/* Signatories on record */}
          {data.signatories && data.signatories.length > 0 && (
            <div className="pt-2 border-t border-slate-100 dark:border-slate-800">
              <span className="text-[10px] uppercase font-semibold text-slate-500 tracking-wider block mb-1">
                Authorized Signatories
              </span>
              <div className="space-y-1">
                {data.signatories.map((sig, i) => (
                  <div key={i} className="flex items-center justify-between text-[11px]">
                    <span className="font-medium text-slate-800 dark:text-slate-200">
                      {sig.name}
                    </span>
                    <span className="text-slate-500">{sig.role}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Verification link */}
          <div className="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-lg flex items-center justify-between text-[11px]">
            <span className="text-slate-500 truncate mr-2">
              URL: {data.qrVerificationUrl}
            </span>
            <a
              href={data.qrVerificationUrl}
              target="_blank"
              rel="noreferrer"
              className="text-amber-600 hover:text-amber-700 font-medium inline-flex items-center gap-1 shrink-0"
            >
              <span>Visit Link</span>
              <ExternalLink className="w-3 h-3" />
            </a>
          </div>
        </div>

        {/* Footer */}
        <div className="px-6 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 flex justify-end">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-1.5 text-xs font-semibold text-white bg-slate-900 dark:bg-slate-100 dark:text-slate-900 rounded-lg cursor-pointer"
          >
            Close Check
          </button>
        </div>
      </div>
    </div>
  );
};
