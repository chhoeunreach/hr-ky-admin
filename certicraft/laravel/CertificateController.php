<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Optional: composer require barryvdh/laravel-dompdf
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Display a listing of issued certificates and KNEAYERNG HRMS records.
     */
    public function index()
    {
        // Sample in-memory or database query
        $certificates = [
            [
                'id' => 'KY-LDR-2026-001',
                'recipient_name' => 'Reach Chhoeun',
                'khmer_recipient_name' => 'ឈឿន រាជ',
                'certificate_title' => 'OUTSTANDING TEAM LEADER AWARD',
                'khmer_title' => 'វិញ្ញាបនបត្រកិត្តិយស មេដឹកនាំឆ្នើម',
                'employee_id' => 'EMP-KY-2026-08',
                'training_hours' => 60,
                'compliance_status' => 'Distinction / Honor Roll',
                'issue_date' => '2026-11-11',
            ],
            [
                'id' => 'KY-SVC-2026-108',
                'recipient_name' => 'Sokha Chan',
                'khmer_recipient_name' => 'ចាន់ សុខា',
                'certificate_title' => 'CUSTOMER SERVICE EXCELLENCE AWARD',
                'khmer_title' => 'វិញ្ញាបនបត្រ ឧត្តមភាពសេវាកម្មអតិថិជន',
                'employee_id' => 'EMP-KY-2026-44',
                'training_hours' => 45,
                'compliance_status' => 'Verified & Accredited',
                'issue_date' => '2026-11-11',
            ]
        ];

        return view('certificate.index', compact('certificates'));
    }

    /**
     * Show the rendered certificate in browser (HTML / Blade).
     */
    public function show($id)
    {
        $certificate = $this->getCertificateData($id);

        return view('certificate.template', compact('certificate'));
    }

    /**
     * Export certificate to PDF using Laravel DomPDF.
     */
    public function downloadPdf($id)
    {
        $certificate = $this->getCertificateData($id);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = Pdf::loadView('certificate.template', compact('certificate'))
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 300,
                ]);

            return $pdf->download("Certificate_{$id}.pdf");
        }

        // Fallback to view
        return view('certificate.template', compact('certificate'));
    }

    /**
     * Public QR verification endpoint.
     */
    public function verify($id)
    {
        $certificate = $this->getCertificateData($id);

        return response()->json([
            'status' => 'authenticated',
            'valid' => true,
            'credential_id' => $certificate['credentialId'],
            'recipient_name' => $certificate['recipientName'],
            'khmer_recipient_name' => $certificate['khmerRecipientName'] ?? null,
            'course_title' => $certificate['courseOrAchievementTitle'],
            'training_hours' => $certificate['digitalHr']['trainingHours'],
            'issuer' => $certificate['issuer']['name'],
            'issue_date' => $certificate['issueDate'],
            'tamper_evident_hash' => hash('sha256', $id . $certificate['recipientName']),
        ]);
    }

    /**
     * Helper to retrieve certificate data.
     */
    private function getCertificateData($id)
    {
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
                ]
            ]
        ];
    }
}
