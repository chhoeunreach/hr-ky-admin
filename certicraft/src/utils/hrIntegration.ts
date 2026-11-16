import { CERTIFICATE_PRESETS } from '../data/presets';
import { CertificateData } from '../types/certificate';

/**
 * Flexible employee shape accepted from hr-ky-admin or any existing HRMS API
 * (supports both camelCase and snake_case backend payloads).
 */
export interface HrAdminEmployeeInput {
  id?: string | number;
  employeeId?: string;
  employee_code?: string;
  name?: string;
  fullName?: string;
  full_name?: string;
  khmerName?: string;
  khmer_name?: string;
  position?: string;
  role?: string;
  title?: string;
  department?: string;
  photoUrl?: string;
  profile_photo?: string;
  avatar?: string;
  trainingHours?: number;
  training_hours?: number;
  issueDate?: string;
  issue_date?: string;
  credentialId?: string;
  credential_id?: string;
  complianceStatus?: 'Verified & Accredited' | 'Distinction / Honor Roll' | 'CPD Certified';
  competencyTrack?: string;
  achievementTitle?: string;
  khmerAchievementTitle?: string;
}

export interface NormalizedHrEmployee {
  id: string;
  name: string;
  khmerName: string;
  role: string;
  department: string;
  employeeId: string;
  photoUrl?: string;
  digitalHours: number;
  track: string;
  status: 'Verified & Accredited' | 'Distinction / Honor Roll' | 'CPD Certified';
  issueDate?: string;
  credentialId?: string;
}

export function normalizeHrEmployee(emp: HrAdminEmployeeInput, index = 0): NormalizedHrEmployee {
  const employeeId =
    emp.employeeId ||
    emp.employee_code ||
    (emp.id !== undefined ? `EMP-KY-${String(emp.id)}` : `EMP-KY-${index + 1}`);

  const name = emp.fullName || emp.full_name || emp.name || 'Employee Name';
  const khmerName = emp.khmerName || emp.khmer_name || '';
  const role = emp.position || emp.role || emp.title || 'Staff Member';
  const department = emp.department || 'Operations & Human Resources';
  const photoUrl = emp.photoUrl || emp.profile_photo || emp.avatar;
  const digitalHours = Number(emp.trainingHours ?? emp.training_hours ?? 45) || 45;
  const track = emp.competencyTrack || `${department} Excellence`;
  const status = emp.complianceStatus || 'Verified & Accredited';

  return {
    id: String(emp.id ?? employeeId),
    name,
    khmerName,
    role,
    department,
    employeeId,
    photoUrl,
    digitalHours,
    track,
    status,
    issueDate: emp.issueDate || emp.issue_date,
    credentialId: emp.credentialId || emp.credential_id,
  };
}

/**
 * Convert an hr-ky-admin employee payload into a ready-to-render CertificateData object.
 */
export function mapHrEmployeeToCertificate(
  emp: HrAdminEmployeeInput,
  basePreset: CertificateData = CERTIFICATE_PRESETS[0]
): CertificateData {
  const norm = normalizeHrEmployee(emp);

  return {
    ...basePreset,
    recipientName: norm.name,
    khmerRecipientName: norm.khmerName || basePreset.khmerRecipientName,
    recipientTitle: norm.role || basePreset.recipientTitle,
    courseOrAchievementTitle:
      emp.achievementTitle || basePreset.courseOrAchievementTitle,
    khmerCourseOrAchievementTitle:
      emp.khmerAchievementTitle || basePreset.khmerCourseOrAchievementTitle,
    recipientPhotoUrl: norm.photoUrl ?? basePreset.recipientPhotoUrl,
    showRecipientPhoto: norm.photoUrl ? true : basePreset.showRecipientPhoto,
    issueDate: norm.issueDate || basePreset.issueDate,
    credentialId:
      norm.credentialId ||
      `KY-CERT-${new Date().getFullYear()}-${norm.employeeId.replace(/[^A-Z0-9]/gi, '').slice(-5)}`,
    digitalHr: {
      employeeId: norm.employeeId,
      department: norm.department,
      trainingHours: norm.digitalHours,
      hrCompetencyTrack: norm.track,
      complianceStatus: norm.status,
      managerName: basePreset.digitalHr?.managerName || 'HR Executive Committee',
      showDigitalHoursBadge: basePreset.digitalHr?.showDigitalHoursBadge ?? true,
    },
  };
}
