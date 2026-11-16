<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $table = 'certificate_templates';

    protected $fillable = [
        'template_code',
        'title',
        'khmer_title',
        'category',
        'layout_template',
        'version_tag',
        'language_mode',
        'theme',
        'description',
        'khmer_description',
        'issuer_name',
        'issuer_khmer_name',
        'department',
        'logo_url',
        'certificate_title',
        'khmer_certificate_title',
        'subtitle',
        'khmer_subtitle',
        'authority_preamble',
        'khmer_preamble',
        'course_or_achievement_title',
        'khmer_course_or_achievement_title',
        'citation',
        'khmer_citation',
        'citation_closing',
        'training_hours',
        'hr_competency_track',
        'compliance_status',
        'signatories_json',
        'styling_json',
        'seal_json',
        'payload_json',
        'preview_image',
        'is_default',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'signatories_json' => 'array',
        'styling_json' => 'array',
        'seal_json' => 'array',
        'payload_json' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'training_hours' => 'integer',
        'sort_order' => 'integer',
    ];

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'template_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Convert template to full React Studio payload.
     */
    public function toStudioPayload(): array
    {
        $payload = [];
        if (!empty($this->payload_json) && is_array($this->payload_json)) {
            $payload = $this->payload_json;
        }

        $defaultStyling = [
            'primaryColor' => '#092653',
            'accentColor' => '#D4A32C',
            'bgColor' => '#FCFBF7',
            'fontDisplay' => 'playfair',
            'fontBody' => 'cormorant',
            'borderStyle' => $this->layout_template === 'executive-navy-ribbon' ? 'navy-gold-ribbon' : ($this->layout_template === 'classic' ? 'classic-vintage' : 'navy-gold-ribbon'),
            'showWatermark' => true,
            'watermarkType' => 'laurel',
            'watermarkOpacity' => 0.14,
            'recipientFontSize' => 42,
            'titleFontSize' => 34,
            'logoScale' => 1.0,
            'showLaurelWreath' => true,
            'logoOrnament' => 'classic-laurel',
            'dividerOrnament' => 'baroque-fleur',
            'dividerWidth' => 280,
            'sealPosition' => 'bottom-center',
            'fieldStyles' => [
                'khmerTitle' => [
                    'fontFamily' => 'khmer-moul',
                    'fontSize' => 24,
                ],
                'khmerRecipientName' => [
                    'fontFamily' => 'khmer-moul',
                    'fontSize' => 30,
                ],
            ],
        ];

        $defaultSeal = [
            'enabled' => true,
            'style' => 'crown-laurel',
            'primaryText' => '',
            'secondaryText' => '',
            'year' => '',
            'ribbonColor' => 'navy',
        ];

        $defaultSignatories = [
            [
                'id' => 's1',
                'name' => 'CHHOEUN REACH',
                'khmerName' => 'ឈឿន រាជ',
                'role' => 'IT Manager / Branch Manager',
                'khmerRole' => 'ប្រធានផ្នែកព័ត៌មានវិទ្យា / ប្រធានសាខា',
                'organization' => 'Admin • HR • System • Stock',
                'signatureStyle' => 'alex-brush',
            ],
            [
                'id' => 's2',
                'name' => 'SOPHEA MENG',
                'khmerName' => 'ម៉េង សោភា',
                'role' => 'HR Manager / HR Supervisor',
                'khmerRole' => 'ប្រធានផ្នែកធនធានមនុស្ស',
                'organization' => 'Human Resource Department',
                'signatureStyle' => 'great-vibes',
            ],
        ];

        return [
            'id' => $payload['id'] ?? $this->template_code,
            'templateId' => $this->id,
            'templateCode' => $this->template_code,
            'category' => $payload['category'] ?? $this->category,
            'layoutTemplate' => $payload['layoutTemplate'] ?? $this->layout_template,
            'orientation' => $payload['orientation'] ?? 'landscape',
            'theme' => $payload['theme'] ?? $this->theme,
            'languageMode' => $payload['languageMode'] ?? $this->language_mode,
            'issuer' => [
                'name' => $payload['issuer']['name'] ?? $this->issuer_name ?: 'KNEAYERNG',
                'khmerName' => $payload['issuer']['khmerName'] ?? $this->issuer_khmer_name ?: 'គ្នា​យើង',
                'department' => $payload['issuer']['department'] ?? $this->department ?: 'PHONE SHOP',
                'khmerDepartment' => $payload['issuer']['khmerDepartment'] ?? 'ហាងទូរស័ព្ទដៃ គ្នា​យើង',
                'logoType' => $payload['issuer']['logoType'] ?? 'knea-yoeng',
                'customLogoUrl' => $payload['issuer']['customLogoUrl'] ?? ($this->logo_url ?: asset('assets/aistudio/unnamed_521b71dc.jpg')),
            ],
            'authorityPreamble' => $payload['authorityPreamble'] ?? $this->authority_preamble,
            'khmerPreamble' => $payload['khmerPreamble'] ?? $this->khmer_preamble ?: 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ',
            'certificateTitle' => $payload['certificateTitle'] ?? $this->certificate_title,
            'khmerTitle' => $payload['khmerTitle'] ?? $this->khmer_certificate_title,
            'bannerText' => $payload['bannerText'] ?? 'THIS CERTIFICATE IS PRESENTED TO',
            'subtitle' => $payload['subtitle'] ?? $this->subtitle ?: 'THIS CERTIFICATE IS PRESENTED TO',
            'khmerSubtitle' => $payload['khmerSubtitle'] ?? $this->khmer_subtitle ?: 'វិញ្ញាបនបត្រនេះត្រូវបានប្រគល់ជូន',
            'recipientName' => $payload['recipientName'] ?? 'KHUN SREYNI',
            'khmerRecipientName' => $payload['khmerRecipientName'] ?? 'ឃុន ស្រីនី',
            'recipientTitle' => $payload['recipientTitle'] ?? 'Team Leader · KNEAYERNG PHONE SHOP',
            'khmerRecipientTitle' => $payload['khmerRecipientTitle'] ?? 'ប្រធានក្រុម · ហាងទូរស័ព្ទដៃ គ្នា​យើង',
            'organizationSubBanner' => $payload['organizationSubBanner'] ?? 'KNEAYERNG PHONE SHOP',
            'showRecipientPhoto' => $payload['showRecipientPhoto'] ?? true,
            'recipientPhotoUrl' => $payload['recipientPhotoUrl'] ?? null,
            'recipientPhotoPosition' => $payload['recipientPhotoPosition'] ?? 'top-right',
            'recipientPhotoShape' => $payload['recipientPhotoShape'] ?? ($this->layout_template === 'executive-verify-v2' ? 'circle' : 'rounded-portrait'),
            'recipientPhotoSize' => $payload['recipientPhotoSize'] ?? 86,
            'recipientPhotoZoom' => $payload['recipientPhotoZoom'] ?? 1,
            'recipientPhotoOffsetX' => $payload['recipientPhotoOffsetX'] ?? 50,
            'recipientPhotoOffsetY' => $payload['recipientPhotoOffsetY'] ?? 25,
            'recipientPhotoFrameOffsetX' => $payload['recipientPhotoFrameOffsetX'] ?? 0,
            'recipientPhotoFrameOffsetY' => $payload['recipientPhotoFrameOffsetY'] ?? 0,
            'visibility' => is_array($payload['visibility'] ?? null) ? $payload['visibility'] : [],
            'courseOrAchievementTitle' => $payload['courseOrAchievementTitle'] ?? $this->course_or_achievement_title,
            'khmerCourseOrAchievementTitle' => $payload['khmerCourseOrAchievementTitle'] ?? $this->khmer_course_or_achievement_title,
            'citation' => $payload['citation'] ?? $this->citation,
            'khmerCitation' => $payload['khmerCitation'] ?? $this->khmer_citation,
            'citationClosing' => $payload['citationClosing'] ?? $this->citation_closing,
            'issueDate' => $payload['issueDate'] ?? '15 November 2026',
            'khmerIssueDate' => $payload['khmerIssueDate'] ?? '១៥ វិច្ឆិកា ២០២៦',
            'expiryDate' => $payload['expiryDate'] ?? 'Lifetime Credential',
            'credentialId' => ($credId = $payload['credentialId'] ?? 'KY-PS-VFY-2026-002'),
            'showQrCode' => $payload['showQrCode'] ?? true,
            'qrVerificationUrl' => (empty($payload['qrVerificationUrl']) || str_contains($payload['qrVerificationUrl'], 'verify.kneayoeng.org') || str_contains($payload['qrVerificationUrl'], 'verify.academics.org') || str_contains($payload['qrVerificationUrl'], 'verify-credential.org'))
                ? url('/certificates/verify/' . $credId)
                : $payload['qrVerificationUrl'],
            'digitalHr' => [
                'employeeId' => $payload['digitalHr']['employeeId'] ?? 'EMP-KY-TL-2026-02',
                'department' => $payload['digitalHr']['department'] ?? 'Store Operations & Team Leadership',
                'khmerDepartment' => $payload['digitalHr']['khmerDepartment'] ?? 'ប្រតិបត្តិការហាង និងភាពជាអ្នកដឹកនាំ',
                'trainingHours' => $payload['digitalHr']['trainingHours'] ?? ($this->training_hours ?: 45),
                'hrCompetencyTrack' => $payload['digitalHr']['hrCompetencyTrack'] ?? ($this->hr_competency_track ?: 'Team Leader & Operational Excellence'),
                'khmerCompetencyTrack' => $payload['digitalHr']['khmerCompetencyTrack'] ?? 'ភាពជាអ្នកដឹកនាំក្រុម និងឧត្តមភាពប្រតិបត្តិការ',
                'complianceStatus' => $payload['digitalHr']['complianceStatus'] ?? ($this->compliance_status ?: 'Verified & Accredited'),
                'managerName' => $payload['digitalHr']['managerName'] ?? 'CHHOEUN REACH & SOPHEA MENG',
                'showDigitalHoursBadge' => $payload['digitalHr']['showDigitalHoursBadge'] ?? false,
            ],
            'signatories' => (!empty($this->signatories_json) && is_array($this->signatories_json)) ? $this->signatories_json : $defaultSignatories,
            'styling' => array_merge($defaultStyling, is_array($this->styling_json) ? $this->styling_json : []),
            'seal' => array_merge($defaultSeal, is_array($this->seal_json) ? $this->seal_json : []),
        ];
    }
}
