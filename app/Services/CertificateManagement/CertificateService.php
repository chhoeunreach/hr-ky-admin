<?php

namespace App\Services\CertificateManagement;

use App\Helpers\AppHelper;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\User;
use App\Repositories\CertificateRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CertificateService
{
    public function __construct(
        protected CertificateRepository $certificateRepository
    ) {}

    /**
     * Get paginated certificates with branch scoping & filters.
     */
    public function getAllCertificatesPaginated(array $filterParameters, array $select = ['*'], array $with = []): LengthAwarePaginator
    {
        try {
            return $this->certificateRepository->getAllCertificatesPaginated($filterParameters, $select, $with);
        } catch (\Throwable $e) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                ['path' => request()->url()]
            );
        }
    }

    /**
     * Find certificate by id or credential_id.
     */
    public function findCertificateById(string|int $id, array $select = ['*'], array $with = []): ?Certificate
    {
        return $this->certificateRepository->findCertificateById($id, $select, $with);
    }

    /**
     * Save new certificate.
     */
    public function saveCertificate(array $validatedData): Certificate
    {
        // Enrich with user data if user_id is provided
        if (!empty($validatedData['user_id'])) {
            $user = User::find($validatedData['user_id']);
            if ($user) {
                if (empty($validatedData['employee_id'])) {
                    $validatedData['employee_id'] = $user->employee_code ?: ('EMP-KY-' . $user->id);
                }
                if (empty($validatedData['branch_id'])) {
                    $validatedData['branch_id'] = $user->branch_id;
                }
                if (empty($validatedData['department_id'])) {
                    $validatedData['department_id'] = $user->department_id;
                }
            }
        }

        // Format signatories json if provided as flat fields
        if (empty($validatedData['signatories_json']) && !empty($validatedData['signatory_1_name'])) {
            $validatedData['signatories_json'] = [
                [
                    'name' => $validatedData['signatory_1_name'],
                    'khmerName' => $validatedData['signatory_1_khmer'] ?? $validatedData['signatory_1_name'],
                    'role' => $validatedData['signatory_1_role'] ?? 'Managing Director & Founder',
                    'khmerRole' => $validatedData['signatory_1_khmer_role'] ?? 'ស្ថាបនិក និងនាយកប្រតិបត្តិ',
                ],
                [
                    'name' => $validatedData['signatory_2_name'] ?? 'Sophea Meng',
                    'khmerName' => $validatedData['signatory_2_khmer'] ?? 'ម៉េង សោភា',
                    'role' => $validatedData['signatory_2_role'] ?? 'Chief People Officer',
                    'khmerRole' => $validatedData['signatory_2_khmer_role'] ?? 'ប្រធានផ្នែកធនធានមនុស្ស',
                ],
            ];
        }

        return $this->certificateRepository->store($validatedData);
    }

    /**
     * Update certificate.
     */
    public function updateCertificate(int $id, array $validatedData): bool
    {
        return $this->certificateRepository->update($id, $validatedData);
    }

    /**
     * Delete certificate.
     */
    public function deleteCertificate(int $id): bool
    {
        return $this->certificateRepository->delete($id);
    }

    /**
     * Batch issue certificates for multiple employees.
     */
    public function batchIssueCertificates(array $employeeIds, array $commonData): array
    {
        // Inherit branding, layout model and signatory defaults from the selected Layout Model
        $template = !empty($commonData['template_id'])
            ? CertificateTemplate::find($commonData['template_id'])
            : null;

        if ($template) {
            $commonData['template_id'] = $template->id;
            // Always use the selected Layout Model's layout/theme/language so Batch Issue renders the correct template
            $commonData['layout_template'] = $template->layout_template ?: ($commonData['layout_template'] ?? 'executive-medal-ribbon');
            $commonData['theme'] = $template->theme ?? ($commonData['theme'] ?? 'navy-gold');
            $commonData['language_mode'] = $template->language_mode ?? ($commonData['language_mode'] ?? 'bilingual');
            $commonData['issuer_name'] = $commonData['issuer_name'] ?? $template->issuer_name ?? 'KNEAYERNG PHONE SHOP';
            $commonData['issuer_khmer_name'] = $commonData['issuer_khmer_name'] ?? $template->issuer_khmer_name ?? 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)';
            $commonData['department'] = $commonData['department'] ?? $template->department ?? 'PHONE SHOP';
            $commonData['logo_url'] = $commonData['logo_url'] ?? $template->logo_url ?? asset('assets/aistudio/unnamed_521b71dc.jpg');
            $commonData['authority_preamble'] = $commonData['authority_preamble'] ?? $template->authority_preamble ?? 'KINGDOM OF CAMBODIA · NATION RELIGION KING';
            $commonData['khmer_preamble'] = $commonData['khmer_preamble'] ?? $template->khmer_preamble ?? 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ';
            $commonData['subtitle'] = $commonData['subtitle'] ?? $template->subtitle;
            $commonData['khmer_subtitle'] = $commonData['khmer_subtitle'] ?? $template->khmer_subtitle;
            $commonData['hr_competency_track'] = $commonData['hr_competency_track'] ?? $template->hr_competency_track;
            $commonData['signatories_json'] = !empty($template->signatories_json) ? $template->signatories_json : ($commonData['signatories_json'] ?? []);
            $commonData['payload_json'] = $template->payload_json ?: null;
        }

        $users = User::whereIn('id', $employeeIds)->get();
        $recipients = [];

        foreach ($users as $u) {
            $photo = $u->avatar ? asset(User::AVATAR_UPLOAD_PATH . $u->avatar) : null;

            $recipients[] = [
                'user_id' => $u->id,
                'employee_id' => $u->employee_code ?: ('EMP-KY-' . $u->id),
                'recipient_name' => $u->english_name ?: $u->name,
                'khmer_recipient_name' => $u->name,
                'recipient_title' => $u->post ? $u->post->post_name : ($commonData['recipient_title'] ?? null),
                'khmer_recipient_title' => $commonData['khmer_recipient_title'] ?? null,
                'recipient_photo_url' => $photo,
                'branch_id' => $u->branch_id,
                'department_id' => $u->department_id,
            ];
        }

        return $this->certificateRepository->batchStore($recipients, $commonData);
    }

    /**
     * Build an unsaved certificate template payload for Batch Issue live preview.
     * Mirrors the data a real batch certificate would receive without touching the DB.
     */
    public function buildPreviewCertificate(array $data, ?User $user = null, ?\App\Models\CertificateTemplate $template = null): array
    {
        $hasExplicitEmployee = false;
        if (!$user && !empty($data['employee_id'])) {
            $user = User::with('post')->find($data['employee_id']);
            $hasExplicitEmployee = true;
        } elseif ($user !== null) {
            $hasExplicitEmployee = true;
        }

        $tplPayload = (!empty($template?->payload_json) && is_array($template->payload_json)) ? $template->payload_json : [];

        if (!$hasExplicitEmployee) {
            $recipientName = !empty($tplPayload['recipientName']) ? $tplPayload['recipientName'] : ($data['recipient_name'] ?? 'KHUN SREYNI');
            $khmerRecipientName = !empty($tplPayload['khmerRecipientName']) ? $tplPayload['khmerRecipientName'] : ($data['khmer_recipient_name'] ?? 'ឃុន ស្រីនី');
            $recipientTitle = !empty($tplPayload['recipientTitle']) ? $tplPayload['recipientTitle'] : ($data['recipient_title'] ?? 'Team Leader · KNEAYERNG PHONE SHOP');
            $khmerRecipientTitle = !empty($tplPayload['khmerRecipientTitle']) ? $tplPayload['khmerRecipientTitle'] : ($data['khmer_recipient_title'] ?? 'ប្រធានក្រុម · ហាងទូរស័ព្ទដៃ គ្នា​យើង');
            $employeeCode = !empty($tplPayload['digitalHr']['employeeId']) ? $tplPayload['digitalHr']['employeeId'] : 'EMP-KY-TL-2026-02';
            $photo = $tplPayload['recipientPhotoUrl'] ?? null;
            $user = null;
        } else {
            $recipientName = $user?->english_name ?: ($user?->name ?: ($data['recipient_name'] ?? ($tplPayload['recipientName'] ?? 'KHUN SREYNI')));
            $khmerRecipientName = $user?->name ?: ($data['khmer_recipient_name'] ?? ($tplPayload['khmerRecipientName'] ?? 'ឃុន ស្រីនី'));
            $recipientTitle = $user?->post?->post_name ?: ($data['recipient_title'] ?? ($tplPayload['recipientTitle'] ?? 'Team Leader · KNEAYERNG PHONE SHOP'));
            $khmerRecipientTitle = $data['khmer_recipient_title'] ?? ($tplPayload['khmerRecipientTitle'] ?? 'ប្រធានក្រុម · ហាងទូរស័ព្ទដៃ គ្នា​យើង');
            $employeeCode = $user?->employee_code ?: ($tplPayload['digitalHr']['employeeId'] ?? ('EMP-KY-' . ($user?->id ?? '000')));
            $photo = ($user && $user->avatar) ? asset(User::AVATAR_UPLOAD_PATH . $user->avatar) : null;
        }

        $category = $data['category'] ?? $template?->category ?? 'course';
        $layoutTemplate = $template?->layout_template ?: ($data['layout_template'] ?? 'executive-medal-ribbon');
        $citation = $data['citation'] ?? ($tplPayload['citation'] ?? $template?->citation);
        $khmerCitation = $data['khmer_citation'] ?? ($tplPayload['khmerCitation'] ?? $template?->khmer_citation);
        $citationClosing = $tplPayload['citationClosing'] ?? null;
        $bannerText = $tplPayload['bannerText'] ?? 'THIS CERTIFICATE IS PRESENTED TO';
        $credentialId = $tplPayload['credentialId'] ?? 'KY-PS-VFY-2026-002';
        $issueDate = $data['issue_date'] ?? ($tplPayload['issueDate'] ?? date('F d, Y'));
        $khmerIssueDate = $data['khmer_issue_date'] ?? ($tplPayload['khmerIssueDate'] ?? null);

        $certificate = new Certificate();
        $certificate->forceFill([
            'credential_id' => $credentialId,
            'template_id' => $template?->id,
            'user_id' => $user?->id,
            'employee_id' => $employeeCode,
            'category' => $category,
            'layout_template' => $layoutTemplate,
            'theme' => $template?->theme ?? 'navy-gold',
            'language_mode' => $template?->language_mode ?? 'bilingual',
            'issuer_name' => $template?->issuer_name ?? 'KNEAYERNG PHONE SHOP',
            'issuer_khmer_name' => $template?->issuer_khmer_name ?? 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)',
            'department' => $template?->department ?? 'PHONE SHOP',
            'logo_url' => $template?->logo_url ?? asset('assets/aistudio/unnamed_521b71dc.jpg'),
            'certificate_title' => $data['certificate_title'] ?? $template?->certificate_title,
            'khmer_title' => $data['khmer_title'] ?? $template?->khmer_certificate_title,
            'subtitle' => $data['subtitle'] ?? $template?->subtitle,
            'khmer_subtitle' => $data['khmer_subtitle'] ?? $template?->khmer_subtitle,
            'authority_preamble' => $template?->authority_preamble ?? 'KINGDOM OF CAMBODIA · NATION RELIGION KING',
            'khmer_preamble' => $template?->khmer_preamble ?? 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ',
            'recipient_name' => $recipientName,
            'khmer_recipient_name' => $khmerRecipientName,
            'recipient_title' => $recipientTitle,
            'khmer_recipient_title' => $khmerRecipientTitle,
            'recipient_photo_url' => $photo,
            'branch_id' => $user?->branch_id,
            'department_id' => $user?->department_id,
            'course_or_achievement_title' => $data['course_or_achievement_title'] ?? $template?->course_or_achievement_title,
            'khmer_course_or_achievement_title' => $data['khmer_course_or_achievement_title'] ?? $template?->khmer_course_or_achievement_title,
            'citation' => $citation,
            'khmer_citation' => $khmerCitation,
            'training_hours' => $data['training_hours'] ?? $template?->training_hours ?? 45,
            'hr_competency_track' => $data['hr_competency_track'] ?? $template?->hr_competency_track,
            'compliance_status' => $data['compliance_status'] ?? $template?->compliance_status,
            'issue_date' => $issueDate,
            'khmer_issue_date' => $khmerIssueDate,
            'qr_verification_url' => null,
            'signatories_json' => $template?->signatories_json,
        ]);

        if ($user) {
            $certificate->setRelation('user', $user);
        }

        $payload = $certificate->toTemplateData();
        $payload['id'] = $credentialId;
        $payload['credentialId'] = $credentialId;
        $payload['layoutTemplate'] = $layoutTemplate;
        $payload['isPreview'] = true;
        $payload['bannerText'] = $bannerText;
        $payload['citationClosing'] = $citationClosing;

        if ($template) {
            $payload['template_id'] = $template->id;
            $payload['templateCode'] = $template->template_code;
            $payload['templateTitle'] = $template->title;
            $payload['templateKhmerTitle'] = $template->khmer_title;
            $payload['layoutTemplate'] = $template->layout_template ?: $layoutTemplate;
            if (!empty($template->theme)) {
                $payload['theme'] = $template->theme;
            }
            if (!empty($template->language_mode)) {
                $payload['languageMode'] = $template->language_mode;
            }
            if (!empty($template->logo_url)) {
                $payload['issuer']['logoUrl'] = $template->logo_url;
            }
            if (!empty($template->issuer_name)) {
                $payload['issuer']['name'] = $template->issuer_name;
            }
            if (!empty($template->issuer_khmer_name)) {
                $payload['issuer']['khmerName'] = $template->issuer_khmer_name;
            }
            if (!empty($template->department)) {
                $payload['issuer']['department'] = $template->department;
            }
            if (!empty($template->signatories_json)) {
                $payload['signatories'] = $template->signatories_json;
            }
            // Merge Studio default styling so every saved ornament/color setting is honoured
            $studioPayload = $template->toStudioPayload();
            $studioStyling = $studioPayload['styling'] ?? [];
            $extraStyling = !empty($template->styling_json)
                ? $template->styling_json
                : ($template->payload_json['styling'] ?? []);
            $payload['styling'] = array_merge(
                is_array($studioStyling) ? $studioStyling : [],
                is_array($extraStyling) ? $extraStyling : []
            );
            // Photo framing (position / size / zoom / offsets / visibility) exactly as configured in Studio
            foreach ([
                'showRecipientPhoto', 'recipientPhotoPosition', 'recipientPhotoShape', 'recipientPhotoSize',
                'recipientPhotoZoom', 'recipientPhotoOffsetX', 'recipientPhotoOffsetY',
                'recipientPhotoFrameOffsetX', 'recipientPhotoFrameOffsetY',
                'hangingMedalStyle', 'hangingMedalScale', 'visibility',
            ] as $k) {
                if (array_key_exists($k, $studioPayload)) {
                    $payload[$k] = $studioPayload[$k];
                }
            }
            if (isset($studioPayload['visibility']) && is_array($studioPayload['visibility'])) {
                $payload['visibility'] = array_merge(
                    is_array($payload['visibility'] ?? null) ? $payload['visibility'] : [],
                    $studioPayload['visibility']
                );
            }
            if (!empty($template->seal_json)) {
                $payload['seal'] = $template->seal_json;
            } elseif (!empty($template->payload_json['seal'])) {
                $payload['seal'] = $template->payload_json['seal'];
            }
            if (!empty($template->payload_json) && is_array($template->payload_json)) {
                $tp = $template->payload_json;
                foreach ([
                    'showRecipientPhoto', 'recipientPhotoPosition', 'recipientPhotoShape', 'recipientPhotoSize',
                    'recipientPhotoZoom', 'recipientPhotoOffsetX', 'recipientPhotoOffsetY',
                    'recipientPhotoFrameOffsetX', 'recipientPhotoFrameOffsetY',
                    'hangingMedalStyle', 'hangingMedalScale', 'orientation', 'bannerText', 'citationClosing',
                ] as $k) {
                    if (isset($tp[$k])) {
                        $payload[$k] = $tp[$k];
                    }
                }
                if (isset($tp['visibility']) && is_array($tp['visibility'])) {
                    $payload['visibility'] = array_merge($payload['visibility'] ?? [], $tp['visibility']);
                }
            }
        }

        if ($template) {
            // Finally fill every remaining key from the complete Layout Model payload so the
            // Batch preview shows exactly what CertiCraft Studio saved (organization sub-banner,
            // QR toggle, expiry/validity, digital HR badge, issuer fields, etc.).
            // The recipient/form-specific values built above always win over Studio defaults.
            $studioPayload = $template->toStudioPayload();
            $authoritative = array_filter($payload, static fn ($value) => $value !== null);
            $payload = array_replace_recursive($studioPayload, $authoritative);

            // Keep certificate-specific identity values authoritative.
            $payload['id'] = $credentialId;
            $payload['credentialId'] = $credentialId;
            $payload['template_id'] = $template->id;
            $payload['isPreview'] = true;
            $payload['recipientName'] = $recipientName;
            $payload['khmerRecipientName'] = $khmerRecipientName;
            $payload['recipientTitle'] = $recipientTitle;
            $payload['khmerRecipientTitle'] = $khmerRecipientTitle;
            $payload['recipientPhotoUrl'] = $photo;
            if (!isset($payload['digitalHr']) || !is_array($payload['digitalHr'])) {
                $payload['digitalHr'] = [];
            }
            $payload['digitalHr']['employeeId'] = $employeeCode;
            $payload['employeeId'] = $employeeCode;
        }

        return $payload;
    }

    /**
     * Get employee certificates.
     */
    public function getEmployeeCertificates(int $userId): Collection
    {
        return $this->certificateRepository->getEmployeeCertificates($userId);
    }

    /**
     * Get summary metrics.
     */
    public function getStatistics(): array
    {
        try {
            return $this->certificateRepository->getStatistics();
        } catch (\Throwable $e) {
            return [
                'total' => 0,
                'total_hours' => 0,
                'leader_count' => 0,
                'service_count' => 0,
                'course_count' => 0,
                'custom_count' => 0,
            ];
        }
    }

    /**
     * Resolve nested template array for Blade and PDF generation.
     */
    public function resolveTemplateData(string|int $id): array
    {
        try {
            $certificate = $this->findCertificateById($id);
            if ($certificate) {
                return $certificate->toTemplateData();
            }
        } catch (\Throwable $e) {
            // Gracefully fall back to demo template data if DB is disconnected
        }

        // Return empty or fallback
        return [
            'id' => $id,
            'credentialId' => $id,
            'category' => 'leader',
            'theme' => 'knea-yoeng-gold',
            'languageMode' => 'bilingual',
            'issuer' => [
                'name' => 'គ្នា​យើង · Knea Yoeng',
                'khmerName' => 'ស្ថាប័ន គ្នា​យើង (KNEA YOENG)',
                'department' => 'គណៈកម្មការអភិវឌ្ឍន៍ភាពជាអ្នកដឹកនាំ និងធនធានមនុស្ស',
                'logoUrl' => asset('assets/aistudio/unnamed_521b71dc.jpg'),
            ],
            'authorityPreamble' => 'KINGDOM OF CAMBODIA · NATION RELIGION KING',
            'khmerPreamble' => 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ',
            'certificateTitle' => 'OUTSTANDING TEAM LEADER AWARD',
            'khmerTitle' => 'វិញ្ញាបនបត្រកិត្តិយស មេដឹកនាំឆ្នើម',
            'subtitle' => 'Conferred in recognition of visionary mentorship and solidarity to',
            'khmerSubtitle' => 'គណៈកម្មការប្រតិបត្តិ និងក្រុមប្រឹក្សាភិបាល គ្នា​យើង សូមប្រសិទ្ធពរជ័យ និងកោតសរសើរដល់',
            'recipientTitle' => 'PRINCIPAL TEAM LEADER & STRATEGIC CULTURE CHAMPION',
            'khmerRecipientTitle' => 'មេដឹកនាំក្រុមឆ្នើម និងគំរូវប្បធម៌សាមគ្គីភាពស្ថាប័ន',
            'recipientName' => 'Reach Chhoeun',
            'khmerRecipientName' => 'លោក ឈឿន រាជ',
            'courseOrAchievementTitle' => 'Strategic Leadership & Team Empowerment Milestone',
            'khmerCourseOrAchievementTitle' => 'សមិទ្ធផលភាពជាអ្នកដឹកនាំយុទ្ធសាស្ត្រ និងសាមគ្គីភាពក្រុម',
            'citation' => 'For embodying the highest standard of leadership under the ethos of "គ្នា​យើង" (United As One) with uncompromising integrity.',
            'khmerCitation' => 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការខិតខំប្រឹងប្រែង ការដឹកនាំក្រុមប្រកបដោយចក្ខុវិស័យខ្ពស់ ស្មារតី "គ្នា​យើង" និងសាមគ្គីភាពរឹងមាំ។',
            'digitalHr' => [
                'employeeId' => 'EMP-KY-2026-08',
                'department' => 'Leadership Development',
                'trainingHours' => 60,
                'hrCompetencyTrack' => 'Transformational Leadership & Executive Cohesion',
                'complianceStatus' => 'Distinction / Honor Roll',
            ],
            'issueDate' => 'November 11, 2026',
            'khmerIssueDate' => 'ថ្ងៃទី ១១ ខែ វិច្ឆិកា ឆ្នាំ ២០២៦',
            'qrVerificationUrl' => route('certificates.verify', $id),
            'signatories' => [
                [
                    'name' => 'Chhoeun Reach',
                    'khmerName' => 'ឈឿន រាជ',
                    'role' => 'Managing Director & Founder',
                    'khmerRole' => 'ស្ថាបនិក និងនាយកប្រតិបត្តិ',
                ],
                [
                    'name' => 'Sophea Meng',
                    'khmerName' => 'ម៉េង សោភា',
                    'role' => 'Chief People Officer',
                    'khmerRole' => 'ប្រធានផ្នែកធនធានមនុស្ស',
                ],
            ],
        ];
    }
}
