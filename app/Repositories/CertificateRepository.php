<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Certificate;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateRepository
{
    /**
     * Get paginated certificates with filters.
     */
    public function getAllCertificatesPaginated(array $filterParameters, array $select = ['*'], array $with = []): LengthAwarePaginator
    {
        return Certificate::select($select)
            ->with($with)
            ->when(!empty($filterParameters['search']), function (Builder $query) use ($filterParameters) {
                $search = $filterParameters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('credential_id', 'like', "%{$search}%")
                      ->orWhere('recipient_name', 'like', "%{$search}%")
                      ->orWhere('khmer_recipient_name', 'like', "%{$search}%")
                      ->orWhere('certificate_title', 'like', "%{$search}%")
                      ->orWhere('khmer_title', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filterParameters['category']), function (Builder $query) use ($filterParameters) {
                $query->where('category', $filterParameters['category']);
            })
            ->when(!empty($filterParameters['branch_id']), function (Builder $query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(!empty($filterParameters['department_id']), function (Builder $query) use ($filterParameters) {
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(!empty($filterParameters['employee_id']), function (Builder $query) use ($filterParameters) {
                $query->where('user_id', $filterParameters['employee_id']);
            })
            ->when(!empty($filterParameters['issue_date']), function (Builder $query) use ($filterParameters) {
                $query->where('issue_date', 'like', "%{$filterParameters['issue_date']}%");
            })
            ->latest('id')
            ->paginate(function_exists('getRecordPerPage') ? getRecordPerPage() : 15);
    }

    /**
     * Find certificate by ID or credential_id.
     */
    public function findCertificateById(string|int $id, array $select = ['*'], array $with = []): ?Certificate
    {
        return Certificate::select($select)
            ->with($with)
            ->where('id', $id)
            ->orWhere('credential_id', $id)
            ->first();
    }

    /**
     * Get all certificates awarded to a specific employee.
     */
    public function getEmployeeCertificates(int $userId, array $select = ['*']): Collection
    {
        return Certificate::select($select)
            ->where('user_id', $userId)
            ->latest('id')
            ->get();
    }

    /**
     * Store new certificate.
     */
    public function store(array $validatedData): Certificate
    {
        return DB::transaction(function () use ($validatedData) {
            if (empty($validatedData['credential_id'])) {
                $validatedData['credential_id'] = $this->generateCredentialId($validatedData['category'] ?? 'leader');
            }

            if (empty($validatedData['qr_verification_url'])) {
                $validatedData['qr_verification_url'] = route('certificates.verify', $validatedData['credential_id']);
            }

            return Certificate::create($validatedData);
        });
    }

    /**
     * Update existing certificate.
     */
    public function update(int $id, array $validatedData): bool
    {
        $certificate = $this->findCertificateById($id);
        if (!$certificate) {
            throw new Exception("Certificate not found.");
        }

        return $certificate->update($validatedData);
    }

    /**
     * Delete certificate.
     */
    public function delete(int $id): bool
    {
        $certificate = $this->findCertificateById($id);
        if (!$certificate) {
            throw new Exception("Certificate not found.");
        }

        return $certificate->delete();
    }

    /**
     * Batch issue certificates.
     */
    public function batchStore(array $recipients, array $commonData): array
    {
        return DB::transaction(function () use ($recipients, $commonData) {
            $createdCertificates = [];
            foreach ($recipients as $recipient) {
                $category = $commonData['category'] ?? 'leader';
                $credentialId = $this->generateCredentialId($category);

                $payload = null;
                if (!empty($commonData['payload_json']) && is_array($commonData['payload_json'])) {
                    $payload = $commonData['payload_json'];
                    $payload['id'] = $credentialId;
                    $payload['credentialId'] = $credentialId;
                    $payload['recipientName'] = $recipient['recipient_name'];
                    $payload['khmerRecipientName'] = $recipient['khmer_recipient_name'] ?? $recipient['recipient_name'];
                    $payload['recipientPhotoUrl'] = $recipient['recipient_photo_url'] ?? null;
                    $payload['qrVerificationUrl'] = route('certificates.verify', $credentialId);
                    if (isset($payload['digitalHr'])) {
                        $payload['digitalHr']['employeeId'] = $recipient['employee_id'] ?? null;
                    }
                    if (isset($commonData['certificate_title'])) {
                        $payload['certificateTitle'] = $commonData['certificate_title'];
                    }
                    if (isset($commonData['khmer_title'])) {
                        $payload['khmerTitle'] = $commonData['khmer_title'];
                    }
                    if (isset($commonData['course_or_achievement_title'])) {
                        $payload['courseOrAchievementTitle'] = $commonData['course_or_achievement_title'];
                    }
                    if (isset($commonData['khmer_course_or_achievement_title'])) {
                        $payload['khmerCourseOrAchievementTitle'] = $commonData['khmer_course_or_achievement_title'];
                    }
                    if (isset($commonData['citation'])) {
                        $payload['citation'] = $commonData['citation'];
                    }
                    if (isset($commonData['khmer_citation'])) {
                        $payload['khmerCitation'] = $commonData['khmer_citation'];
                    }
                    if (isset($commonData['training_hours'])) {
                        $payload['digitalHr']['trainingHours'] = (int) $commonData['training_hours'];
                    }
                    if (isset($commonData['compliance_status'])) {
                        $payload['digitalHr']['complianceStatus'] = $commonData['compliance_status'];
                    }
                    if (isset($commonData['issue_date'])) {
                        $payload['issueDate'] = $commonData['issue_date'];
                    }
                    if (isset($commonData['khmer_issue_date'])) {
                        $payload['khmerIssueDate'] = $commonData['khmer_issue_date'];
                    }
                    if (isset($commonData['organization_sub_banner'])) {
                        $payload['organizationSubBanner'] = $commonData['organization_sub_banner'];
                    }
                    if (isset($commonData['show_organization_sub_banner'])) {
                        $payload['visibility']['showOrganizationSubBanner'] = filter_var($commonData['show_organization_sub_banner'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (isset($commonData['show_storefront_watermark'])) {
                        $payload['visibility']['showStorefrontWatermark'] = filter_var($commonData['show_storefront_watermark'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (isset($commonData['show_digital_hours_badge'])) {
                        $payload['digitalHr']['showDigitalHoursBadge'] = filter_var($commonData['show_digital_hours_badge'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (!empty($commonData['hanging_medal_style'])) {
                        $payload['hangingMedalStyle'] = $commonData['hanging_medal_style'];
                    }
                    if (isset($commonData['seal_enabled'])) {
                        $payload['seal']['enabled'] = filter_var($commonData['seal_enabled'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (!empty($commonData['seal_style'])) {
                        $payload['seal']['style'] = $commonData['seal_style'];
                    }
                    if (!empty($commonData['seal_ribbon_color'])) {
                        $payload['seal']['ribbonColor'] = $commonData['seal_ribbon_color'];
                    }
                    if (isset($commonData['show_watermark'])) {
                        $payload['styling']['showWatermark'] = filter_var($commonData['show_watermark'], FILTER_VALIDATE_BOOLEAN);
                    }
                    if (isset($commonData['show_qr_code'])) {
                        $payload['showQrCode'] = filter_var($commonData['show_qr_code'], FILTER_VALIDATE_BOOLEAN);
                    }
                }

                $data = array_merge($commonData, [
                    'credential_id' => $credentialId,
                    'user_id' => $recipient['user_id'] ?? null,
                    'employee_id' => $recipient['employee_id'] ?? null,
                    'recipient_name' => $recipient['recipient_name'],
                    'khmer_recipient_name' => $recipient['khmer_recipient_name'] ?? null,
                    'recipient_title' => $recipient['recipient_title'] ?? ($commonData['recipient_title'] ?? null),
                    'khmer_recipient_title' => $recipient['khmer_recipient_title'] ?? ($commonData['khmer_recipient_title'] ?? null),
                    'recipient_photo_url' => $recipient['recipient_photo_url'] ?? null,
                    'branch_id' => $recipient['branch_id'] ?? ($commonData['branch_id'] ?? null),
                    'department_id' => $recipient['department_id'] ?? ($commonData['department_id'] ?? null),
                    'qr_verification_url' => route('certificates.verify', $credentialId),
                    'payload_json' => $payload,
                ]);

                $createdCertificates[] = Certificate::create($data);
            }

            return $createdCertificates;
        });
    }

    /**
     * Compute summary statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => Certificate::count(),
            'total_hours' => (float) Certificate::sum('training_hours'),
            'leader_count' => Certificate::where('category', 'leader')->count(),
            'service_count' => Certificate::where('category', 'service')->count(),
            'course_count' => Certificate::where('category', 'course')->count(),
            'custom_count' => Certificate::where('category', 'custom')->count(),
        ];
    }

    /**
     * Generate structured credential ID (e.g. KY-LDR-2026-001).
     */
    public function generateCredentialId(string $category = 'leader'): string
    {
        $catCode = match ($category) {
            'service' => 'SVC',
            'course' => 'CPD',
            'custom' => 'HNR',
            default => 'LDR',
        };

        $year = date('Y');
        $prefix = "KY-{$catCode}-{$year}-";

        // Find highest suffix
        $lastCert = Certificate::where('credential_id', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastCert && preg_match('/-(\d+)$/', $lastCert->credential_id, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        } else {
            $nextSeq = Certificate::count() + 1;
        }

        return $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }
}
