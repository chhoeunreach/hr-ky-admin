<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CertificateTemplate;

class CertificateTemplateSeeder extends Seeder
{
    /**
     * Get the pristine factory default configurations for all 6 official models (V1 - V6).
     *
     * @return array<string, array> Keyed by template_code
     */
    public static function getFactoryTemplates(): array
    {
        $companyName = 'KNEAYERNG PHONE SHOP';
        $companyKhmer = 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)';
        $logoUrl = asset('assets/aistudio/unnamed_521b71dc.jpg');
        $preamble = 'KINGDOM OF CAMBODIA · NATION RELIGION KING';
        $khmerPreamble = 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ';

        $signatories = [
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
                'name' => 'PET POEUN',
                'khmerName' => 'ម៉េង សោភា',
                'role' => 'HR Manager / HR Supervisor',
                'khmerRole' => 'ប្រធានផ្នែកធនធានមនុស្ស',
                'organization' => 'Human Resource Department',
                'signatureStyle' => 'great-vibes',
            ],
        ];

        $rawTemplates = [
            [
                'template_code' => 'TMPL-V1-EXECUTIVE',
                'version_tag' => 'V1',
                'title' => 'Verify Classic Gold',
                'khmer_title' => 'ទម្រង់ V1 · Verify QR មាសប្រណិត',
                'layout_template' => 'executive-verify',
                'category' => 'leader',
                'theme' => 'navy-gold',
                'description' => 'Portrait ID card, Date footer, Dual Signatory & Verify QR Code',
                'khmer_description' => 'រូបបុគ្គលិកបញ្ឈរ · កាលបរិច្ឆេទ · ហត្ថលេខា ២ · QR Verify ផ្លូវការ',
                'certificate_title' => 'CERTIFICATE OF APPRECIATION',
                'khmer_certificate_title' => 'វិញ្ញាបនបត្រកោតសរសើរ និងផ្ដល់ការតែងតាំង',
                'subtitle' => 'THIS CERTIFICATE IS PRESENTED TO',
                'khmer_subtitle' => 'វិញ្ញាបនបត្រនេះត្រូវបានប្រគល់ជូន',
                'course_or_achievement_title' => 'Team Leader Appointment & Operational Excellence',
                'khmer_course_or_achievement_title' => 'ការតែងតាំងជាប្រធានក្រុម និងឧត្តមភាពប្រតិបត្តិការ',
                'citation' => "In recognition of your dedication, leadership skills, and official appointment as Team Leader at Kneayerng Phone Shop.\nYour commitment to guiding your colleagues and driving operational excellence sets a commendable standard for our entire team.",
                'khmer_citation' => "ដើម្បីទទួលស្គាល់ការលះបង់ ជំនាញដឹកនាំ និងការតែងតាំងជាផ្លូវការជាប្រធានក្រុមនៅហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)។\nការប្តេជ្ញាចិត្តរបស់អ្នកក្នុងការណែនាំសហការី គឺជាគំរូដ៏ប្រសើរសម្រាប់ក្រុមទាំងមូល។",
                'citation_closing' => 'Congratulations on this well-deserved achievement, and we wish you continued success in your new role.',
                'training_hours' => 45,
                'hr_competency_track' => 'Team Leader & Operational Excellence',
                'compliance_status' => 'Verified & Accredited',
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'template_code' => 'TMPL-V2-ID',
                'version_tag' => 'V2',
                'title' => 'Verify + Staff ID',
                'khmer_title' => 'ទម្រង់ V2 · Verify + ID បុគ្គលិក',
                'layout_template' => 'executive-verify-v2',
                'category' => 'staff',
                'theme' => 'navy-gold',
                'description' => 'Circle ID photo, Stacked Date & Certificate ID + Gold Bar & QR',
                'khmer_description' => 'រូបបុគ្គលិករង្វង់ · កាលបរិច្ឆេទ & លេខសម្គាល់ · Gold Bar & QR Verify',
                'certificate_title' => 'OUTSTANDING EMPLOYEE OF THE YEAR',
                'khmer_certificate_title' => 'វិញ្ញាបនបត្រកិត្តិយស បុគ្គលិកឆ្នើមប្រចាំឆ្នាំ',
                'subtitle' => 'THIS CERTIFICATE IS PROUDLY CONFERRED UPON',
                'khmer_subtitle' => 'វិញ្ញាបនបត្រកិត្តិយសនេះត្រូវបានប្រសិទ្ធពរជ័យជូនចំពោះ',
                'course_or_achievement_title' => 'Exemplary Work Ethic & Stellar Core Values',
                'khmer_course_or_achievement_title' => 'ក្រមសីលធម៌ការងារគំរូ និងគុណតម្លៃស្នូលស្ថាប័ន',
                'citation' => 'In heartfelt recognition of continuous hard work, punctuality, teamwork, and invaluable contribution to store operations.',
                'khmer_citation' => 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការខិតខំប្រឹងប្រែង ការបំពេញភារកិច្ចដោយការទទួលខុសត្រូវខ្ពស់ និងស្មារតីសាមគ្គីភាពក្នុងក្រុម។',
                'citation_closing' => 'Awarded on this day in recognition of unwavering loyalty and exceptional workplace commitment.',
                'training_hours' => 50,
                'hr_competency_track' => 'Core Operational Excellence',
                'compliance_status' => 'Distinction / Honor Roll',
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'template_code' => 'TMPL-V3-RIBBON',
                'version_tag' => 'V3',
                'title' => 'Navy Ribbon',
                'khmer_title' => 'ទម្រង់ V3 · ស៊ុមបូប្រណិត ខៀវ-មាស',
                'layout_template' => 'executive-navy-ribbon',
                'category' => 'service',
                'theme' => 'navy-gold',
                'description' => 'Sweeping Navy-Gold corner ribbons, Org Sub-banner & Flexible HR',
                'khmer_description' => 'ស៊ុមបូខៀវ-មាស · ឈ្មោះស្ថាប័ន · ហត្ថលេខា HR ឬ ទោល',
                'certificate_title' => 'CUSTOMER SERVICE EXCELLENCE AWARD',
                'khmer_certificate_title' => 'វិញ្ញាបនបត្រ ឧត្តមភាពសេវាកម្មអតិថិជន',
                'subtitle' => 'PRESENTED IN SINCERE APPRECIATION TO',
                'khmer_subtitle' => 'សូមប្រសិទ្ធពរជ័យ និងកោតសរសើរចំពោះការលះបង់ និងឧត្តមភាពសេវាកម្មដល់',
                'course_or_achievement_title' => 'Five-Star Customer Experience & Brand Advocacy',
                'khmer_course_or_achievement_title' => 'បទពិសោធន៍សេវាកម្មកម្រិតផ្កាយ ៥ និងទំនុកចិត្តអតិថិជន',
                'citation' => 'In honor of delivering exceptional client experience, warmth, rapid service, and elevating customer satisfaction.',
                'khmer_citation' => 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការប្តេជ្ញាចិត្តខ្ពស់ និងការផ្តល់សេវាកម្មប្រកបដោយទំនុកចិត្ត និងវិជ្ជាជីវៈជូនអតិថិជន។',
                'citation_closing' => 'May your passion for exemplary service inspire your peers and brighten every customer interaction.',
                'training_hours' => 45,
                'hr_competency_track' => 'Customer Experience & Client Care',
                'compliance_status' => 'Verified & Accredited',
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'template_code' => 'TMPL-V4-ROYAL',
                'version_tag' => 'V4',
                'title' => 'Royal Heritage',
                'khmer_title' => 'ទម្រង់ V4 · បុរាណប្រណីត ក្បាច់ខ្មែរ',
                'layout_template' => 'classic',
                'category' => 'course',
                'theme' => 'knea-yoeng-gold',
                'description' => 'Guilloche crest watermark, Decree header, CPD plaque & Metrics',
                'khmer_description' => 'ក្បាច់បុរាណខ្មែរ · សាររដ្ឋបាល · តារាងម៉ោង HR & CPD Hours',
                'certificate_title' => 'CERTIFICATE OF PROFESSIONAL TRAINING COMPLETION',
                'khmer_certificate_title' => 'វិញ្ញាបនបត្របញ្ចប់ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ',
                'subtitle' => 'THIS IS TO OFFICIALLY ACCREDIT AND CERTIFY THAT',
                'khmer_subtitle' => 'គណៈកម្មការបណ្តុះបណ្តាល និងអភិវឌ្ឍន៍ធនធានមនុស្ស សូមបញ្ជាក់ថា',
                'course_or_achievement_title' => 'Advanced Retail Mastery & Leadership Curriculum',
                'khmer_course_or_achievement_title' => 'វគ្គបណ្តុះបណ្តាលភាពជាអ្នកដឹកនាំ និងប្រតិបត្តិការលក់រាយកម្រិតខ្ពស់',
                'citation' => 'Having successfully fulfilled all curriculum requirements, continuous workplace assessments, and competency milestones.',
                'khmer_citation' => 'បានបំពេញតាមគ្រប់លក្ខខណ្ឌតម្រូវនៃកម្មវិធីបណ្តុះបណ្តាល និងទទួលបានលទ្ធផលគាប់ប្រសើរក្នុងវិជ្ជាជីវៈ។',
                'citation_closing' => 'Given under our seal and official authority on this commemorative graduation day.',
                'training_hours' => 60,
                'hr_competency_track' => 'CPD Training & Professional Accreditation',
                'compliance_status' => 'Certified & Accredited',
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'template_code' => 'TMPL-V5-MEDAL',
                'version_tag' => 'V5',
                'title' => 'Medal Ribbon',
                'khmer_title' => 'ទម្រង់ V5 · មេដាយប្រណិត Ribbon',
                'layout_template' => 'executive-medal-ribbon',
                'category' => 'custom',
                'theme' => 'navy-gold',
                'description' => 'Left hanging Navy-Gold medal sash, Photo ID, Date + Cert ID & QR',
                'khmer_description' => 'បូមេដាយខៀវ-មាសផ្នែកឆ្វេង · រូបបុគ្គលិក · លេខសម្គាល់ · QR',
                'certificate_title' => 'HONOR & MERIT DISTINCTION AWARD',
                'khmer_certificate_title' => 'វិញ្ញាបនបត្រកិត្តិយស និងស្នាដៃឆ្នើមពិសេស',
                'subtitle' => 'BESTOWED WITH HIGHEST HONORS UPON',
                'khmer_subtitle' => 'គណៈកម្មការប្រតិបត្តិ និងក្រុមប្រឹក្សាភិបាល សូមប្រគល់ជូនដោយក្តីមោទនភាពចំពោះ',
                'course_or_achievement_title' => 'Special Organizational Merit & Milestone Achievement',
                'khmer_course_or_achievement_title' => 'ស្នាដៃពិសេស និងសមិទ្ធផលលេចធ្លោក្នុងស្ថាប័ន',
                'citation' => 'For demonstrating extraordinary resolve, ingenuity, and high-impact contribution exceeding institutional goals.',
                'khmer_citation' => 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការលះបង់ខ្ពស់ ស្នាដៃឆ្នើម និងការរួមចំណែកដ៏មានតម្លៃក្នុងការលើកកម្ពស់ស្ថាប័ន។',
                'citation_closing' => 'With our warmest appreciation for your exemplary loyalty and continued pursuit of excellence.',
                'training_hours' => 60,
                'hr_competency_track' => 'Special Institutional Distinction',
                'compliance_status' => 'Honor Roll Distinction',
                'is_default' => false,
                'sort_order' => 5,
            ],
            [
                'template_code' => 'TMPL-V6-SASH',
                'version_tag' => 'V6',
                'title' => 'Imperial Sash',
                'khmer_title' => 'ទម្រង់ V6 · រលកមាស-បូខៀវ Imperial',
                'layout_template' => 'executive-imperial-sash',
                'category' => 'leader',
                'theme' => 'navy-gold',
                'description' => 'Gold-sash medal, K crest, storefront watermark, laurels & QR',
                'khmer_description' => 'មេដាយបូមាសផ្នែកឆ្វេង · រូបបុគ្គលិក · ហាងគ្នាយើងស្រាល · QR',
                'certificate_title' => 'EXECUTIVE LEADERSHIP & EXCELLENCE',
                'khmer_certificate_title' => 'វិញ្ញាបនបត្រ ភាពជាអ្នកដឹកនាំជាន់ខ្ពស់',
                'subtitle' => 'CONFERRED IN SOLEMN RECOGNITION UPON',
                'khmer_subtitle' => 'វិញ្ញាបនបត្រនេះត្រូវបានប្រសិទ្ធពរជ័យ និងកោតសរសើរខ្ពង់ខ្ពស់ដល់',
                'course_or_achievement_title' => 'Strategic Branch Management & Team Governance',
                'khmer_course_or_achievement_title' => 'ការគ្រប់គ្រងសាខាយុទ្ធសាស្ត្រ និងការដឹកនាំក្រុមឆ្នើម',
                'citation' => 'For embodying the highest virtues of transformational governance, mentoring successors, and elevating operational prestige.',
                'khmer_citation' => 'ដើម្បីទទួលស្គាល់ការដឹកនាំប្រកបដោយចក្ខុវិស័យ ការបណ្តុះបណ្តាលសហការី និងការលើកកម្ពស់ប្រសិទ្ធភាពការងារសាខា។',
                'citation_closing' => 'May your leadership continue to inspire and drive sustained organizational triumph.',
                'training_hours' => 60,
                'hr_competency_track' => 'Transformational Executive Leadership',
                'compliance_status' => 'Executive Accreditation',
                'is_default' => false,
                'sort_order' => 6,
            ],
        ];

        $result = [];

        foreach ($rawTemplates as $t) {
            $t['issuer_name'] = $companyName;
            $t['issuer_khmer_name'] = $companyKhmer;
            $t['logo_url'] = $logoUrl;
            $t['authority_preamble'] = $preamble;
            $t['khmer_preamble'] = $khmerPreamble;
            $t['signatories_json'] = $signatories;
            $t['styling_json'] = [
                'primaryColor' => '#092653',
                'accentColor' => '#D4A32C',
                'bgColor' => '#FCFBF7',
                'fontDisplay' => 'playfair',
                'fontBody' => 'cormorant',
                'borderStyle' => $t['layout_template'] === 'executive-navy-ribbon' ? 'navy-gold-ribbon' : ($t['layout_template'] === 'classic' ? 'classic-vintage' : 'navy-gold-ribbon'),
                'showWatermark' => true,
                'watermarkType' => 'laurel',
                'watermarkOpacity' => 0.14,
                'recipientFontSize' => 42,
                'titleFontSize' => 34,
                'logoScale' => 1.0,
                'showLaurelWreath' => true,
                'logoOrnament' => 'classic-laurel',
                'dividerOrnament' => 'baroque-fleur',
                'dividerWidth' => 240,
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
            $t['seal_json'] = [
                'enabled' => true,
                'style' => 'crown-laurel',
                'primaryText' => '',
                'secondaryText' => '',
                'ribbonColor' => 'navy',
            ];
            $t['payload_json'] = [
                'id' => $t['template_code'],
                'category' => $t['category'],
                'layoutTemplate' => $t['layout_template'],
                'orientation' => 'landscape',
                'theme' => $t['theme'],
                'languageMode' => 'bilingual',
                'issuer' => [
                    'name' => 'KNEAYERNG',
                    'khmerName' => 'គ្នា​យើង',
                    'department' => 'PHONE SHOP',
                    'khmerDepartment' => 'ហាងទូរស័ព្ទដៃ គ្នា​យើង',
                    'logoType' => 'knea-yoeng',
                    'customLogoUrl' => $logoUrl,
                ],
                'authorityPreamble' => $preamble,
                'khmerPreamble' => $khmerPreamble,
                'certificateTitle' => $t['certificate_title'],
                'khmerTitle' => $t['khmer_certificate_title'],
                'bannerText' => 'THIS CERTIFICATE IS PRESENTED TO',
                'subtitle' => $t['subtitle'],
                'khmerSubtitle' => $t['khmer_subtitle'],
                'recipientName' => 'KHUN SREYNI',
                'khmerRecipientName' => 'ឃុន ស្រីនី',
                'recipientTitle' => 'Team Leader · KNEAYERNG PHONE SHOP',
                'khmerRecipientTitle' => 'ប្រធានក្រុម · ហាងទូរស័ព្ទដៃ គ្នា​យើង',
                'organizationSubBanner' => 'KNEAYERNG PHONE SHOP',
                'showRecipientPhoto' => true,
                'recipientPhotoPosition' => 'top-right',
                'recipientPhotoShape' => $t['layout_template'] === 'executive-verify-v2' ? 'circle' : 'rounded-portrait',
                'recipientPhotoSize' => 118,
                'recipientPhotoZoom' => 1,
                'recipientPhotoOffsetX' => 50,
                'recipientPhotoOffsetY' => 22,
                'courseOrAchievementTitle' => $t['course_or_achievement_title'],
                'khmerCourseOrAchievementTitle' => $t['khmer_course_or_achievement_title'],
                'citation' => $t['citation'],
                'khmerCitation' => $t['khmer_citation'],
                'citationClosing' => $t['citation_closing'] ?? '',
                'issueDate' => '15 November 2026',
                'khmerIssueDate' => '១៥ វិច្ឆិកា ២០២៦',
                'expiryDate' => 'Lifetime Credential',
                'credentialId' => 'KY-PS-VFY-2026-002',
                'showQrCode' => true,
                'qrVerificationUrl' => url('/certificates/verify/KY-PS-VFY-2026-002'),
                'digitalHr' => [
                    'employeeId' => 'EMP-KY-TL-2026-02',
                    'department' => 'Store Operations & Team Leadership',
                    'khmerDepartment' => 'ប្រតិបត្តិការហាង និងភាពជាអ្នកដឹកនាំ',
                    'trainingHours' => $t['training_hours'],
                    'hrCompetencyTrack' => $t['hr_competency_track'],
                    'khmerCompetencyTrack' => 'ភាពជាអ្នកដឹកនាំក្រុម និងឧត្តមភាពប្រតិបត្តិការ',
                    'complianceStatus' => $t['compliance_status'],
                    'managerName' => 'CHHOEUN REACH & SOPHEA MENG',
                    'showDigitalHoursBadge' => false,
                ],
                'signatories' => $signatories,
                'styling' => $t['styling_json'],
                'seal' => $t['seal_json'],
            ];

            $result[$t['template_code']] = $t;
        }

        return $result;
    }

    /**
     * Match a template's factory default by code or version tag.
     */
    public static function getFactoryTemplate(?string $codeOrTag): ?array
    {
        if (!$codeOrTag) return null;

        $all = self::getFactoryTemplates();
        $target = strtoupper(trim($codeOrTag));

        if (isset($all[$target])) {
            return $all[$target];
        }

        foreach ($all as $code => $data) {
            if (strtoupper($data['version_tag'] ?? '') === $target || strtoupper($code) === $target) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Reset an existing CertificateTemplate record back to pristine factory default.
     */
    public static function resetTemplateToDefault(CertificateTemplate $template): bool
    {
        $factory = self::getFactoryTemplate($template->template_code);
        if (!$factory) {
            $factory = self::getFactoryTemplate($template->version_tag);
        }
        if (!$factory) {
            // match by layout_template
            foreach (self::getFactoryTemplates() as $ft) {
                if ($ft['layout_template'] === $template->layout_template) {
                    $factory = $ft;
                    break;
                }
            }
        }
        if (!$factory) {
            $all = self::getFactoryTemplates();
            $factory = reset($all);
        }

        if (!$factory) {
            return false;
        }

        $template->fill([
            'title' => $factory['title'],
            'khmer_title' => $factory['khmer_title'],
            'layout_template' => $factory['layout_template'],
            'category' => $factory['category'],
            'theme' => $factory['theme'],
            'version_tag' => $factory['version_tag'],
            'description' => $factory['description'],
            'khmer_description' => $factory['khmer_description'],
            'certificate_title' => $factory['certificate_title'],
            'khmer_certificate_title' => $factory['khmer_certificate_title'],
            'subtitle' => $factory['subtitle'],
            'khmer_subtitle' => $factory['khmer_subtitle'],
            'authority_preamble' => $factory['authority_preamble'],
            'khmer_preamble' => $factory['khmer_preamble'],
            'course_or_achievement_title' => $factory['course_or_achievement_title'],
            'khmer_course_or_achievement_title' => $factory['khmer_course_or_achievement_title'],
            'citation' => $factory['citation'],
            'khmer_citation' => $factory['khmer_citation'],
            'citation_closing' => $factory['citation_closing'] ?? '',
            'training_hours' => $factory['training_hours'],
            'hr_competency_track' => $factory['hr_competency_track'],
            'compliance_status' => $factory['compliance_status'],
            'issuer_name' => $factory['issuer_name'],
            'issuer_khmer_name' => $factory['issuer_khmer_name'],
            'logo_url' => $factory['logo_url'],
            'signatories_json' => $factory['signatories_json'],
            'styling_json' => $factory['styling_json'],
            'seal_json' => $factory['seal_json'],
            'payload_json' => $factory['payload_json'],
        ]);

        $template->save();
        return true;
    }

    public function run(): void
    {
        $templates = self::getFactoryTemplates();

        foreach ($templates as $t) {
            CertificateTemplate::updateOrCreate(
                ['template_code' => $t['template_code']],
                $t
            );
        }
    }
}
