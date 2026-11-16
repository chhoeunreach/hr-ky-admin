<?php

namespace App\Models;

use App\Helpers\AppHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'credential_id',
        'template_id',
        'category',
        'language_mode',
        'theme',
        'issuer_name',
        'issuer_khmer_name',
        'department',
        'logo_url',
        'certificate_title',
        'khmer_title',
        'authority_preamble',
        'khmer_preamble',
        'subtitle',
        'khmer_subtitle',
        'recipient_name',
        'khmer_recipient_name',
        'recipient_title',
        'khmer_recipient_title',
        'course_or_achievement_title',
        'khmer_course_or_achievement_title',
        'citation',
        'khmer_citation',
        'employee_id',
        'user_id',
        'branch_id',
        'department_id',
        'training_hours',
        'hr_competency_track',
        'compliance_status',
        'issue_date',
        'khmer_issue_date',
        'expiry_date',
        'qr_verification_url',
        'signatories_json',
        'metrics_json',
        'layout_template',
        'recipient_photo_url',
        'payload_json',
        'created_by',
    ];

    protected $casts = [
        'signatories_json' => 'array',
        'metrics_json' => 'array',
        'payload_json' => 'array',
        'training_hours' => 'integer',
    ];

    const CATEGORIES = [
        'leader' => 'Leadership Excellence',
        'service' => 'Customer Service Excellence',
        'course' => 'Professional Training / CPD',
        'custom' => 'Honor & Merit Recognition',
    ];

    public static function boot()
    {
        parent::boot();

        if (Auth::check() && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use ($branchId) {
                if (AppHelper::canAccessAllBranches()) {
                    return;
                }
                $builder->where(function ($query) use ($branchId) {
                    $query->whereNull('branch_id')
                          ->orWhereIn('branch_id', AppHelper::accessibleBranchIds());
                });
            });
        }
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function departmentRel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Format into the nested array structure required by the blade template & renderer.
     */
    public function toTemplateData(): array
    {
        $signatories = is_array($this->signatories_json) ? $this->signatories_json : json_decode($this->signatories_json ?? '[]', true);
        if (empty($signatories)) {
            $signatories = [
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
            ];
        }

        $logo = $this->logo_url;
        if (!$logo) {
            $logo = asset('assets/aistudio/unnamed_521b71dc.jpg');
        }

        $photo = $this->recipient_photo_url;
        if (!$photo && $this->user && $this->user->avatar) {
            $photo = asset(User::AVATAR_UPLOAD_PATH . $this->user->avatar);
        }
        if (!$photo) {
            $photo = null;
        }

        $payloadJson = is_array($this->payload_json) ? $this->payload_json : json_decode($this->payload_json ?? '[]', true);
        $payloadJson = is_array($payloadJson) ? $payloadJson : [];
        $templatePayload = ($this->template && is_array($this->template->payload_json))
            ? $this->template->payload_json
            : [];
        $payloadJsonDh = is_array($payloadJson['digitalHr'] ?? null) ? $payloadJson['digitalHr'] : [];
        $templatePayloadDh = is_array($templatePayload['digitalHr'] ?? null) ? $templatePayload['digitalHr'] : [];
        $styling = null;
        $seal = null;
        if (!empty($payloadJson['styling'])) {
            $styling = $payloadJson['styling'];
        } elseif ($this->template && !empty($this->template->styling_json)) {
            $styling = $this->template->styling_json;
        } elseif (!empty($templatePayload['styling'])) {
            $styling = $templatePayload['styling'];
        }

        if (!empty($payloadJson['seal'])) {
            $seal = $payloadJson['seal'];
        } elseif ($this->template && !empty($this->template->seal_json)) {
            $seal = $this->template->seal_json;
        } elseif (!empty($templatePayload['seal'])) {
            $seal = $templatePayload['seal'];
        }

        if (!empty($payloadJson['signatories']) && empty($signatories)) {
            $signatories = $payloadJson['signatories'];
        }

        return [
            'id' => $this->credential_id,
            'credentialId' => $this->credential_id,
            'template_id' => $this->template_id,
            'category' => $this->category ?? 'leader',
            'layoutTemplate' => $this->layout_template ?: ($this->template?->layout_template ?? 'executive-medal-ribbon'),
            'theme' => $this->theme ?? ($this->template?->theme ?? 'navy-gold'),
            'languageMode' => $this->language_mode ?? 'bilingual',
            'styling' => $styling,
            'seal' => $seal,
            'showRecipientPhoto' => $payloadJson['showRecipientPhoto'] ?? true,
            'recipientPhotoShape' => $payloadJson['recipientPhotoShape'] ?? null,
            'recipientPhotoPosition' => $payloadJson['recipientPhotoPosition'] ?? 'top-right',
            'recipientPhotoSize' => $payloadJson['recipientPhotoSize'] ?? 118,
            'recipientPhotoZoom' => $payloadJson['recipientPhotoZoom'] ?? 1,
            'recipientPhotoOffsetX' => $payloadJson['recipientPhotoOffsetX'] ?? 50,
            'recipientPhotoOffsetY' => $payloadJson['recipientPhotoOffsetY'] ?? 22,
            'recipientPhotoFrameOffsetX' => $payloadJson['recipientPhotoFrameOffsetX'] ?? 0,
            'recipientPhotoFrameOffsetY' => $payloadJson['recipientPhotoFrameOffsetY'] ?? 0,
            'hangingMedalStyle' => $payloadJson['hangingMedalStyle'] ?? null,
            'visibility' => $payloadJson['visibility'] ?? ($templatePayload['visibility'] ?? []),
            'issuer' => [
                'name' => $this->issuer_name ?: ($this->template?->issuer_name ?: 'គ្នា​យើង · Knea Yoeng'),
                'khmerName' => $this->issuer_khmer_name ?: ($this->template?->issuer_khmer_name ?: 'ស្ថាប័ន គ្នា​យើង (KNEA YOENG)'),
                'department' => $this->department ?: ($this->template?->department ?: 'គណៈកម្មការអភិវឌ្ឍន៍ភាពជាអ្នកដឹកនាំ និងធនធានមនុស្ស'),
                'khmerDepartment' => $payloadJson['issuer']['khmerDepartment'] ?? ($templatePayload['issuer']['khmerDepartment'] ?? null),
                'logoType' => $payloadJson['issuer']['logoType'] ?? ($templatePayload['issuer']['logoType'] ?? null),
                'logoUrl' => $logo,
                'customLogoUrl' => $payloadJson['issuer']['customLogoUrl'] ?? ($templatePayload['issuer']['customLogoUrl'] ?? $logo),
            ],
            'organizationSubBanner' => $payloadJson['organizationSubBanner'] ?? ($templatePayload['organizationSubBanner'] ?? null),
            'authorityPreamble' => $this->authority_preamble ?: 'KINGDOM OF CAMBODIA · NATION RELIGION KING',
            'khmerPreamble' => $this->khmer_preamble ?: 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ',
            'certificateTitle' => $this->certificate_title,
            'khmerTitle' => $this->khmer_title ?: $this->certificate_title,
            'subtitle' => $this->subtitle ?: 'Conferred in recognition of visionary mentorship and solidarity to',
            'khmerSubtitle' => $this->khmer_subtitle ?: 'គណៈកម្មការប្រតិបត្តិ និងក្រុមប្រឹក្សាភិបាល គ្នា​យើង សូមប្រសិទ្ធពរជ័យ និងកោតសរសើរដល់',
            'recipientTitle' => $this->recipient_title ?: 'PRINCIPAL TEAM LEADER & STRATEGIC CULTURE CHAMPION',
            'khmerRecipientTitle' => $this->khmer_recipient_title ?: 'មេដឹកនាំក្រុមឆ្នើម និងគំរូវប្បធម៌សាមគ្គីភាពស្ថាប័ន',
            'recipientName' => $this->recipient_name,
            'khmerRecipientName' => $this->khmer_recipient_name ?: $this->recipient_name,
            'recipientPhotoUrl' => $photo,
            'courseOrAchievementTitle' => $this->course_or_achievement_title,
            'khmerCourseOrAchievementTitle' => $this->khmer_course_or_achievement_title ?: $this->course_or_achievement_title,
            'citation' => $this->citation ?: 'For embodying the highest standard of leadership under the ethos of "គ្នា​យើង" (United As One) with uncompromising integrity.',
            'khmerCitation' => $this->khmer_citation ?: 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការខិតខំប្រឹងប្រែង ការដឹកនាំក្រុមប្រកបដោយចក្ខុវិស័យខ្ពស់ ស្មារតី "គ្នា​យើង" និងសាមគ្គីភាពរឹងមាំ។',
            'digitalHr' => [
                'employeeId' => $this->employee_id ?: ($this->user ? $this->user->employee_code : 'EMP-KY-001'),
                'department' => $this->department ?: ($this->departmentRel ? $this->departmentRel->dept_name : 'Leadership Development'),
                'khmerDepartment' => $payloadJsonDh['khmerDepartment'] ?? ($templatePayloadDh['khmerDepartment'] ?? null),
                'trainingHours' => $this->training_hours ?? 60,
                'hrCompetencyTrack' => $this->hr_competency_track ?: 'Transformational Leadership & Executive Cohesion',
                'khmerCompetencyTrack' => $payloadJsonDh['khmerCompetencyTrack'] ?? ($templatePayloadDh['khmerCompetencyTrack'] ?? null),
                'complianceStatus' => $this->compliance_status ?: 'Distinction / Honor Roll',
                'managerName' => $payloadJsonDh['managerName'] ?? ($templatePayloadDh['managerName'] ?? null),
                'showDigitalHoursBadge' => (bool) ($payloadJsonDh['showDigitalHoursBadge'] ?? ($templatePayloadDh['showDigitalHoursBadge'] ?? false)),
            ],
            'issueDate' => $this->issue_date ?: date('F d, Y'),
            'khmerIssueDate' => $this->khmer_issue_date ?: ('ថ្ងៃទី ' . date('d') . ' ខែ ' . date('m') . ' ឆ្នាំ ' . date('Y')),
            'expiryDate' => $payloadJson['expiryDate'] ?? ($templatePayload['expiryDate'] ?? null),
            'showQrCode' => (bool) ($payloadJson['showQrCode'] ?? ($templatePayload['showQrCode'] ?? true)),
            'qrVerificationUrl' => (!empty($this->qr_verification_url) && !str_contains($this->qr_verification_url, 'verify.kneayoeng.org') && !str_contains($this->qr_verification_url, 'verify.academics.org'))
                ? $this->qr_verification_url
                : route('certificates.verify', $this->credential_id),
            'bannerText' => $payloadJson['bannerText'] ?? ($templatePayload['bannerText'] ?? 'THIS CERTIFICATE IS PRESENTED TO'),
            'citationClosing' => $payloadJson['citationClosing'] ?? ($templatePayload['citationClosing'] ?? null),
            'signatories' => $signatories,
        ];
    }

    /**
     * Format payload for the React CertificateStudio.
     */
    public function toStudioPayload(): array
    {
        if (!empty($this->payload_json)) {
            $parsed = is_array($this->payload_json) ? $this->payload_json : json_decode($this->payload_json, true);
            if (is_array($parsed) && !empty($parsed)) {
                $parsed['id'] = $this->credential_id;
                $parsed['credentialId'] = $this->credential_id;
                if (empty($parsed['qrVerificationUrl']) || str_contains($parsed['qrVerificationUrl'], 'verify.kneayoeng.org') || str_contains($parsed['qrVerificationUrl'], 'verify.academics.org')) {
                    $parsed['qrVerificationUrl'] = route('certificates.verify', $this->credential_id);
                }
                return $parsed;
            }
        }

        return $this->toTemplateData();
    }
}
