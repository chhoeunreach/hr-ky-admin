import React, { useState } from 'react';
import {
  X,
  Clock,
  UserCheck,
  Building2,
  Award,
  Download,
  Plus,
  CheckCircle2,
  FileSpreadsheet,
  TrendingUp,
  Sparkles,
} from 'lucide-react';
import { CertificateData, DigitalHrRecord } from '../types/certificate';
import { HrAdminEmployeeInput, normalizeHrEmployee } from '../utils/hrIntegration';

interface DigitalHrModalProps {
  isOpen: boolean;
  onClose: () => void;
  currentData: CertificateData;
  externalEmployees?: HrAdminEmployeeInput[];
  onUpdateDigitalHr: (record: DigitalHrRecord) => void;
  onSelectEmployee: (
    name: string,
    role: string,
    record: DigitalHrRecord,
    extra?: { khmerName?: string; photoUrl?: string; credentialId?: string }
  ) => void;
}

interface HrEmployeeItem {
  id: string;
  name: string;
  khmerName?: string;
  photoUrl?: string;
  credentialId?: string;
  role: string;
  department: string;
  employeeId: string;
  digitalHours: number;
  track: string;
  status: 'Verified & Accredited' | 'Distinction / Honor Roll' | 'CPD Certified';
}

const INITIAL_ROSTER: HrEmployeeItem[] = [
  {
    id: 'emp-tl-1',
    name: 'KHUN SREYNI',
    role: 'Team Leader · KNEAYERNG PHONE SHOP',
    department: 'Store Operations & Team Leadership',
    employeeId: 'EMP-KY-TL-2026-02',
    digitalHours: 45,
    track: 'Team Leader & Operational Excellence',
    status: 'Verified & Accredited',
  },
  {
    id: 'emp-hr-1',
    name: 'SOPHEAP RATANA',
    role: 'Best of Staff & HR Honor Roll',
    department: 'Human Resources & Store Operations',
    employeeId: 'EMP-KY-HR-2026-01',
    digitalHours: 56,
    track: 'HR Certified Best of Staff Excellence',
    status: 'Verified & Accredited',
  },
  {
    id: 'emp-0',
    name: 'Dara Sothea',
    role: 'Best Employee of the Year & Values Ambassador',
    department: 'Operations & Human Resources',
    employeeId: 'EMP-KY-2026-STF01',
    digitalHours: 50,
    track: 'Core Organizational Excellence & Professional Conduct',
    status: 'Distinction / Honor Roll',
  },
  {
    id: 'emp-1',
    name: 'Reach Chhoeun',
    role: 'Principal Team Leader & Culture Champion',
    department: 'Leadership & People Operations',
    employeeId: 'EMP-KY-2026-08',
    digitalHours: 60,
    track: 'Transformational Leadership & Psychological Safety',
    status: 'Distinction / Honor Roll',
  },
  {
    id: 'emp-2',
    name: 'Sokha Chan',
    role: 'Senior Customer Care Ambassador',
    department: 'Customer Care & Hospitality',
    employeeId: 'EMP-KY-2026-44',
    digitalHours: 45,
    track: 'Five-Star Customer Delight & Conflict De-escalation',
    status: 'Verified & Accredited',
  },
  {
    id: 'emp-3',
    name: 'Vannak Meas',
    role: 'Certified Digital Systems Practitioner',
    department: 'Engineering & Digital Systems',
    employeeId: 'EMP-ACAD-2026-92',
    digitalHours: 80,
    track: 'Digital Transformation & Cloud Architecture',
    status: 'CPD Certified',
  },
  {
    id: 'emp-4',
    name: 'Dara Som',
    role: 'Client Success Lead',
    department: 'Customer Care & Hospitality',
    employeeId: 'EMP-KY-2026-115',
    digitalHours: 50,
    track: 'High-Touch Enterprise Account Management',
    status: 'Verified & Accredited',
  },
  {
    id: 'emp-5',
    name: 'Sophea Meng',
    role: 'Organizational Development Partner',
    department: 'Leadership & People Operations',
    employeeId: 'EMP-KY-2026-202',
    digitalHours: 75,
    track: 'Strategic Culture Architecture & Team Mentorship',
    status: 'Distinction / Honor Roll',
  },
];

export const DigitalHrModal: React.FC<DigitalHrModalProps> = ({
  isOpen,
  onClose,
  currentData,
  externalEmployees,
  onUpdateDigitalHr,
  onSelectEmployee,
}) => {
  const hrData = currentData.digitalHr || {
    employeeId: 'EMP-KY-2026-08',
    department: 'Leadership & Culture Development',
    trainingHours: 60,
    hrCompetencyTrack: 'Transformational Leadership',
    complianceStatus: 'Verified & Accredited',
    managerName: 'Executive HR Committee',
    showDigitalHoursBadge: true,
  };

  const [activeTab, setActiveTab] = useState<'profile' | 'roster' | 'log'>('profile');
  const [employeeId, setEmployeeId] = useState(hrData.employeeId);
  const [department, setDepartment] = useState(hrData.department);
  const [trainingHours, setTrainingHours] = useState(hrData.trainingHours);
  const [hrCompetencyTrack, setHrCompetencyTrack] = useState(hrData.hrCompetencyTrack);
  const [complianceStatus, setComplianceStatus] = useState(hrData.complianceStatus);
  const [managerName, setManagerName] = useState(hrData.managerName || 'HR Executive Committee');
  const [showBadge, setShowBadge] = useState(hrData.showDigitalHoursBadge);

  const mergedRoster = React.useMemo<HrEmployeeItem[]>(() => {
    if (!externalEmployees || externalEmployees.length === 0) return INITIAL_ROSTER;
    const mapped = externalEmployees.map((emp, idx) => normalizeHrEmployee(emp, idx));
    const seenIds = new Set(mapped.map((m) => m.employeeId));
    return [...mapped, ...INITIAL_ROSTER.filter((item) => !seenIds.has(item.employeeId))];
  }, [externalEmployees]);

  const [deptFilter, setDeptFilter] = useState<string>('All');
  const [notification, setNotification] = useState<string | null>(null);

  // New Training session log state
  const [sessionTitle, setSessionTitle] = useState('');
  const [sessionHours, setSessionHours] = useState<number>(10);

  if (!isOpen) return null;

  const roster = mergedRoster;

  const handleSaveHr = () => {
    const updated: DigitalHrRecord = {
      employeeId,
      department,
      trainingHours,
      hrCompetencyTrack,
      complianceStatus,
      managerName,
      showDigitalHoursBadge: showBadge,
    };
    onUpdateDigitalHr(updated);
    setNotification('Digital HR records updated on certificate!');
    setTimeout(() => setNotification(null), 2500);
  };

  const handleAddHours = (extra: number) => {
    const newTotal = trainingHours + extra;
    setTrainingHours(newTotal);
    onUpdateDigitalHr({
      ...hrData,
      trainingHours: newTotal,
      employeeId,
      department,
      hrCompetencyTrack,
      complianceStatus,
      showDigitalHoursBadge: showBadge,
    });
    setNotification(`Added +${extra} Digital Hours (Total: ${newTotal} hrs)`);
    setTimeout(() => setNotification(null), 2500);
  };

  const handleExportCsv = () => {
    const headers = ['Employee Name', 'Role', 'Department', 'Employee ID', 'Digital Training Hours', 'Competency Track', 'Accreditation Status'];
    const rows = roster.map((r) => [
      `"${r.name}"`,
      `"${r.role}"`,
      `"${r.department}"`,
      `"${r.employeeId}"`,
      r.digitalHours,
      `"${r.track}"`,
      `"${r.status}"`,
    ]);
    const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `digital_hr_training_hours_report_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    setNotification('HR Training Hours CSV Report downloaded!');
    setTimeout(() => setNotification(null), 2500);
  };

  const filteredRoster = deptFilter === 'All' ? roster : roster.filter((r) => r.department === deptFilter);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-amber-500/5">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-amber-500 text-white rounded-lg shadow-sm">
              <Clock className="w-5 h-5" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">
                  Digital HR & Training Hours Platform
                </h3>
                <span className="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 rounded-full">
                  HRMS Sync
                </span>
              </div>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                Log, certify, and display accredited Digital Training Hours and employee competency records
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

        {/* Tab switcher */}
        <div className="flex border-b border-slate-200 dark:border-slate-800 px-6 gap-6 bg-slate-50 dark:bg-slate-950/40 text-xs">
          <button
            type="button"
            onClick={() => setActiveTab('profile')}
            className={`py-3 font-semibold border-b-2 cursor-pointer transition-colors ${
              activeTab === 'profile'
                ? 'border-amber-600 text-amber-700 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            }`}
          >
            Active Certificate HR Details
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('roster')}
            className={`py-3 font-semibold border-b-2 cursor-pointer transition-colors ${
              activeTab === 'roster'
                ? 'border-amber-600 text-amber-700 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            }`}
          >
            Employee HR Roster ({roster.length})
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('log')}
            className={`py-3 font-semibold border-b-2 cursor-pointer transition-colors ${
              activeTab === 'log'
                ? 'border-amber-600 text-amber-700 dark:text-amber-400'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            }`}
          >
            Log Digital Hours
          </button>
        </div>

        {/* Content Body */}
        <div className="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          {notification && (
            <div className="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
              <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
              <span>{notification}</span>
            </div>
          )}

          {/* ======================= TAB 1: ACTIVE CERTIFICATE HR PROFILE ======================= */}
          {activeTab === 'profile' && (
            <div className="space-y-4 text-xs">
              {/* Highlight Digital Hours Box */}
              <div className="p-4 rounded-xl bg-gradient-to-r from-amber-50 to-amber-100/60 dark:from-amber-950/30 dark:to-amber-900/20 border border-amber-200 dark:border-amber-800/60 flex items-center justify-between">
                <div>
                  <span className="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 block">
                    Accredited Digital Training Hours
                  </span>
                  <div className="flex items-baseline gap-2 mt-0.5">
                    <span className="text-3xl font-extrabold font-mono text-amber-900 dark:text-amber-200 tabular-nums">
                      {trainingHours}
                    </span>
                    <span className="text-xs font-semibold text-amber-700 dark:text-amber-300">
                      Digital HR Hours Certified
                    </span>
                  </div>
                  <p className="text-[11px] text-amber-800/80 dark:text-amber-300/80 mt-1">
                    Accredited for Continuing Professional Development (CPD) & Annual Performance Review
                  </p>
                </div>

                {/* Quick Add Steppers */}
                <div className="flex flex-col gap-1.5 shrink-0">
                  <div className="flex gap-1.5">
                    <button
                      type="button"
                      onClick={() => handleAddHours(5)}
                      className="px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-slate-800 border border-amber-300 text-amber-800 hover:bg-amber-50 cursor-pointer shadow-2xs"
                    >
                      +5 hrs
                    </button>
                    <button
                      type="button"
                      onClick={() => handleAddHours(10)}
                      className="px-2.5 py-1 text-xs font-semibold rounded bg-white dark:bg-slate-800 border border-amber-300 text-amber-800 hover:bg-amber-50 cursor-pointer shadow-2xs"
                    >
                      +10 hrs
                    </button>
                  </div>
                  <button
                    type="button"
                    onClick={() => handleAddHours(20)}
                    className="px-2.5 py-1 text-xs font-semibold rounded bg-amber-600 text-white hover:bg-amber-700 cursor-pointer shadow-2xs"
                  >
                    +20 hrs Masterclass
                  </button>
                </div>
              </div>

              {/* Form Grid */}
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Employee ID
                  </label>
                  <input
                    type="text"
                    value={employeeId}
                    onChange={(e) => setEmployeeId(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono text-slate-900 dark:text-slate-100"
                  />
                </div>

                <div>
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Department / Division
                  </label>
                  <input
                    type="text"
                    value={department}
                    onChange={(e) => setDepartment(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                  />
                </div>
              </div>

              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                  HR Competency Track
                </label>
                <input
                  type="text"
                  value={hrCompetencyTrack}
                  onChange={(e) => setHrCompetencyTrack(e.target.value)}
                  placeholder="e.g. Transformational Leadership, Customer Care L3"
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Accreditation Status
                  </label>
                  <select
                    value={complianceStatus}
                    onChange={(e: any) => setComplianceStatus(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                  >
                    <option value="Verified & Accredited">Verified & Accredited</option>
                    <option value="Distinction / Honor Roll">Distinction / Honor Roll</option>
                    <option value="CPD Certified">CPD Certified</option>
                  </select>
                </div>

                <div>
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Approving HR Manager / Director
                  </label>
                  <input
                    type="text"
                    value={managerName}
                    onChange={(e) => setManagerName(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
                  />
                </div>
              </div>

              {/* Display on Certificate Checkbox */}
              <div className="flex items-center gap-2 pt-2">
                <input
                  type="checkbox"
                  id="showBadgeCheckbox"
                  checked={showBadge}
                  onChange={(e) => setShowBadge(e.target.checked)}
                  className="rounded text-amber-600 focus:ring-amber-500"
                />
                <label htmlFor="showBadgeCheckbox" className="font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                  Display Official "Digital HR Certified Hours" Gold Badge on Certificate
                </label>
              </div>
            </div>
          )}

          {/* ======================= TAB 2: HR EMPLOYEE ROSTER ======================= */}
          {activeTab === 'roster' && (
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-1.5">
                  <span className="text-xs text-slate-500">Filter Department:</span>
                  <select
                    value={deptFilter}
                    onChange={(e) => setDeptFilter(e.target.value)}
                    className="text-xs px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md"
                  >
                    <option value="All">All Departments</option>
                    <option value="Leadership & People Operations">Leadership</option>
                    <option value="Customer Care & Hospitality">Customer Care</option>
                    <option value="Engineering & Digital Systems">Engineering</option>
                  </select>
                </div>

                <button
                  type="button"
                  onClick={handleExportCsv}
                  className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-lg hover:bg-amber-100/80 cursor-pointer"
                >
                  <FileSpreadsheet className="w-3.5 h-3.5" />
                  <span>Export HR Audit CSV</span>
                </button>
              </div>

              {/* Roster Table */}
              <div className="border border-slate-200 dark:border-slate-800 rounded-lg divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
                {filteredRoster.map((emp) => (
                  <div
                    key={emp.id}
                    className="p-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                  >
                    <div className="space-y-0.5">
                      <div className="flex items-center gap-2">
                        <span className="font-bold text-xs text-slate-900 dark:text-slate-100">
                          {emp.name}
                        </span>
                        <span className="font-mono text-[10px] text-slate-500">
                          ({emp.employeeId})
                        </span>
                        <span className="px-1.5 py-0.2 bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 text-[10px] font-bold rounded">
                          {emp.digitalHours} hrs
                        </span>
                      </div>
                      <p className="text-[11px] text-slate-500">{emp.role} · {emp.department}</p>
                      <p className="text-[10px] text-amber-700 dark:text-amber-400 font-medium">
                        Track: {emp.track}
                      </p>
                    </div>

                    <button
                      type="button"
                      onClick={() => {
                        onSelectEmployee(
                          emp.name,
                          emp.role,
                          {
                            employeeId: emp.employeeId,
                            department: emp.department,
                            trainingHours: emp.digitalHours,
                            hrCompetencyTrack: emp.track,
                            complianceStatus: emp.status,
                            managerName: 'HR Executive Committee',
                            showDigitalHoursBadge: true,
                          },
                          {
                            khmerName: emp.khmerName,
                            photoUrl: emp.photoUrl,
                            credentialId: emp.credentialId,
                          }
                        );
                        setNotification(`Loaded ${emp.name} into Certificate!`);
                        setTimeout(() => setNotification(null), 2500);
                      }}
                      className="px-3 py-1.5 text-xs font-semibold bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 rounded-md hover:bg-slate-800 cursor-pointer shadow-2xs"
                    >
                      Issue Certificate
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* ======================= TAB 3: LOG TRAINING HOURS ======================= */}
          {activeTab === 'log' && (
            <div className="space-y-4 text-xs">
              <div className="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 rounded-lg text-blue-900 dark:text-blue-200">
                <span className="font-bold block mb-0.5">Continuing Professional Development (CPD) Logging</span>
                Record newly completed masterclasses, workshops, or service mentorship sessions to credit digital hours.
              </div>

              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                  Workshop / Training Session Title
                </label>
                <input
                  type="text"
                  value={sessionTitle}
                  onChange={(e) => setSessionTitle(e.target.value)}
                  placeholder="e.g. Q4 Advanced Empathy & Customer Delight Workshop"
                  className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Hours to Credit
                  </label>
                  <input
                    type="number"
                    min="1"
                    max="100"
                    value={sessionHours}
                    onChange={(e) => setSessionHours(parseInt(e.target.value) || 0)}
                    className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono"
                  />
                </div>

                <div>
                  <label className="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Recipient
                  </label>
                  <div className="px-3 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-semibold truncate">
                    {currentData.recipientName} ({employeeId})
                  </div>
                </div>
              </div>

              <button
                type="button"
                onClick={() => {
                  if (sessionHours > 0) {
                    handleAddHours(sessionHours);
                    setSessionTitle('');
                  }
                }}
                className="w-full py-2.5 bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 font-semibold rounded-lg hover:bg-slate-800 cursor-pointer flex items-center justify-center gap-2"
              >
                <Plus className="w-4 h-4" />
                <span>Credit +{sessionHours} Digital Hours to Certificate</span>
              </button>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
          <div className="text-xs text-slate-500">
            Total Accredited: <span className="font-bold text-amber-700">{trainingHours} Digital Hours</span>
          </div>

          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg cursor-pointer"
            >
              Close
            </button>
            <button
              type="button"
              onClick={() => {
                handleSaveHr();
                onClose();
              }}
              className="px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm cursor-pointer"
            >
              Apply to Certificate
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
