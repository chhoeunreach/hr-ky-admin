@php
    $notAvailable = __('index.not_available');

    // Helpers for Khmer numbers and dates
    $khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
    $toKhmerNum = fn ($num) => str_replace(range(0, 9), $khmerDigits, (string) $num);
    $khmerMonths = [
        1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា',
        5 => 'ឧសភា', 6 => 'មិថុនា', 7 => 'កក្កដា', 8 => 'សីហា',
        9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ',
    ];
    $formatKhmerDate = function ($date) use ($toKhmerNum, $khmerMonths) {
        if (!$date) return '................................................';
        $c = \Illuminate\Support\Carbon::parse($date);
        return 'ថ្ងៃទី ' . $toKhmerNum($c->format('d')) . ' ខែ ' . ($khmerMonths[(int) $c->format('m')] ?? '') . ' ឆ្នាំ ' . $toKhmerNum($c->format('Y'));
    };

    // Salary resolution
    $payrollSalary = $employee->employeeSalary;
    $payrollMonthlySalary = $payrollSalary ? (float) $payrollSalary->annual_salary / 12 : null;
    $baseSalary = filled($payrollSalary?->monthly_basic_salary)
        ? $payrollSalary->monthly_basic_salary
        : (filled($profile->current_base_salary)
            ? $profile->current_base_salary
            : ($latestSalary?->new_base_salary ?? $payrollMonthlySalary));

    $salaryCurrency = $contract?->salary_currency ?: 'USD';
    $salaryDisplay = filled($baseSalary) ? number_format((float) $baseSalary, 2) : '.........................';

    // Employee info
    $empKhmerName = $employee->name ?: '';
    $empEngName = $employee->english_name ?: '';
    $fullNameDisplay = trim($empKhmerName . ($empEngName ? ' (' . strtoupper($empEngName) . ')' : '')) ?: '................................................';

    $genderDisplay = match (strtolower((string) $employee->gender)) {
        'male', 'm' => 'ប្រុស',
        'female', 'f' => 'ស្រី',
        default => $employee->gender ?: '.......................',
    };

    $dobDisplay = $employee->dob ? $formatKhmerDate($employee->dob) : '................................................';
    $nationalIdDisplay = $profile->national_id ?: ($employee->national_id ?: '................................................');
    $positionDisplay = $employee->post?->post_name ?: '................................................';
    
    $deptName = $employee->department?->dept_name ?: '';
    $branchName = $employee->branch?->name ?: '';
    $departmentBranchDisplay = trim($deptName . ($deptName && $branchName ? ' / ' : '') . ($branchName ?: 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)')) ?: '................................................';

    $joiningDateDisplay = $employee->joining_date ? $formatKhmerDate($employee->joining_date) : '................................................';

    // Contract type detection
    $contractType = strtolower((string) ($contract?->contract_type ?? ''));
    $isUDC = ($contractType === 'udc' || str_contains($contractType, 'undetermined') || str_contains($contractType, 'មិនកំណត់'));
    $isFDC = ($contractType === 'fdc' || str_contains($contractType, 'fixed') || str_contains($contractType, 'កំណត់'));
    $isOtherContract = !$isUDC && !$isFDC && filled($contractType);

    // Employment status
    $employmentStatus = $profile->employment_status ?: ((int) $employee->is_active === 1 ? 'active' : 'inactive');
    $isCurrentActive = ($employmentStatus === 'active' || (int) $employee->is_active === 1);

    // Company & Branch Meta
    $companyNameKhmer = 'ក្រុមហ៊ុន/សហគ្រាស គ្នាយើង (KNEAYERNG PHONE SHOP)';
    $branchAddress = $employee->branch?->address ?: 'រាជធានីភ្នំពេញ ព្រះរាជាណាចក្រកម្ពុជា';
    $branchPhone = $employee->branch?->phone ?: '012 345 678 / 098 765 432';
    $branchEmail = $employee->branch?->email ?: 'kneayerngphoneshop@gmail.com';

    $refNum = $referenceNumber ?? ('EMP-CERT-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT) . '/' . now()->format('Y'));
    $issueDateKhmer = 'ធ្វើនៅរាជធានីភ្នំពេញ, ថ្ងៃទី ' . $toKhmerNum(now()->format('d')) . ' ខែ ' . ($khmerMonths[(int) now()->format('m')] ?? '') . ' ឆ្នាំ ' . $toKhmerNum(now()->format('Y'));

    $repName = $authorizedName ?? (auth('admin')->user()?->name ?: (auth()->user()?->name ?: 'ម៉េង សោភា'));
    $repRole = $authorizedRole ?? 'ប្រធានផ្នែកធនធានមនុស្ស / HR Manager';
    $certPurpose = $purpose ?? 'ការងាររដ្ឋបាល ទំនាក់ទំនងស្ថាប័នធនាគារ និងបំពេញបែបបទផ្សេងៗតាមផ្លូវច្បាប់';
    $includeSal = $includeSalary ?? true;
@endphp
<!DOCTYPE html>
<html lang="km" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>លិខិតបញ្ជាក់ការងារ - {{ $employee->english_name ?: $employee->name }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page EmploymentCertificate {
            size: 595.3pt 841.9pt; /* A4 Portrait */
            margin: 36pt 45pt 36pt 45pt;
            mso-page-orientation: portrait;
        }
        div.EmploymentCertificate { page: EmploymentCertificate; }
        body {
            font-family: "Khmer OS Battambang", "Khmer OS", "Khmer Busra", Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 100%;
            max-width: 595.3pt;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header-table td {
            vertical-align: top;
            padding-bottom: 8pt;
            border-bottom: 1.5pt solid #092653;
        }
        .company-title {
            color: #092653;
            font-size: 13pt;
            font-weight: bold;
            font-family: "Khmer OS Moul", "Khmer OS", Arial, sans-serif;
        }
        .meta-info {
            font-size: 9.5pt;
            color: #475569;
            line-height: 1.4;
        }
        .doc-title {
            text-align: center;
            margin-top: 18pt;
            margin-bottom: 16pt;
        }
        .doc-title h2 {
            font-family: "Khmer OS Moul", "Khmer OS", Arial, sans-serif;
            font-size: 17pt;
            color: #092653;
            margin: 0;
            padding: 0;
        }
        .doc-title .eng-sub {
            font-family: "Cinzel", "Times New Roman", serif;
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: 2px;
            color: #64748b;
            margin-top: 2pt;
        }
        .intro-text {
            font-size: 11pt;
            line-height: 1.7;
            margin-bottom: 12pt;
            text-align: justify;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
        }
        .data-table td {
            border: 1pt solid #94a3b8;
            padding: 5.5pt 8pt;
            font-size: 10.5pt;
            vertical-align: middle;
        }
        .data-table .label-col {
            width: 30%;
            background-color: #f8fafc;
            font-weight: bold;
            color: #1e293b;
        }
        .data-table .val-col {
            width: 70%;
        }
        .box-check {
            font-size: 12pt;
            font-family: "Arial", sans-serif;
            font-weight: bold;
            margin-right: 3pt;
        }
        .closing-text {
            font-size: 10.5pt;
            line-height: 1.7;
            margin-bottom: 16pt;
            text-align: justify;
        }
        .signature-table {
            width: 100%;
            margin-top: 28pt;
            table-layout: fixed;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
    </style>
</head>
<body>
<div class="EmploymentCertificate">
    <div class="page">

        <!-- Header Table -->
        <table class="header-table">
            <tr>
                <td style="width: 65%;">
                    <div class="company-title">{{ $companyNameKhmer }}</div>
                    <div class="meta-info" style="margin-top: 4pt;">
                        <strong>អាសយដ្ឋាន៖</strong> {{ $branchAddress }}<br>
                        <strong>ទូរស័ព្ទ៖</strong> {{ $branchPhone }} &nbsp;·&nbsp; <strong>អ៊ីមែល៖</strong> {{ $branchEmail }}
                    </div>
                </td>
                <td style="width: 35%; text-align: right;" class="meta-info">
                    <strong>លេខ៖</strong> {{ $refNum }}<br>
                    {{ $issueDateKhmer }}
                </td>
            </tr>
        </table>

        <!-- Document Title -->
        <div class="doc-title">
            <h2>លិខិតបញ្ជាក់ការងារ</h2>
            <div class="eng-sub">CERTIFICATE OF EMPLOYMENT</div>
        </div>

        <!-- Introduction Statement -->
        <div class="intro-text">
            យើងខ្ញុំ <strong>{{ $repName }}</strong> ជា [{{ $repRole }}] នៃក្រុមហ៊ុន/សហគ្រាសខាងលើ សូមបញ្ជាក់ថា៖
        </div>

        <!-- Verification Data Table -->
        <table class="data-table">
            <tr>
                <td class="label-col">គោត្តនាម និងនាម</td>
                <td class="val-col"><strong>{{ $fullNameDisplay }}</strong></td>
            </tr>
            <tr>
                <td class="label-col">ភេទ និង ថ្ងៃខែឆ្នាំកំណើត</td>
                <td class="val-col">
                    <strong>ភេទ៖</strong> {{ $genderDisplay }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>ថ្ងៃខែឆ្នាំកំណើត៖</strong> {{ $dobDisplay }}
                </td>
            </tr>
            <tr>
                <td class="label-col">លេខអត្តសញ្ញាណប័ណ្ណ / លិខិតឆ្លងដែន</td>
                <td class="val-col"><strong>{{ $nationalIdDisplay }}</strong></td>
            </tr>
            <tr>
                <td class="label-col">មុខតំណែង</td>
                <td class="val-col"><strong>{{ $positionDisplay }}</strong></td>
            </tr>
            <tr>
                <td class="label-col">ផ្នែក / សាខា</td>
                <td class="val-col">{{ $departmentBranchDisplay }}</td>
            </tr>
            <tr>
                <td class="label-col">ថ្ងៃចូលបម្រើការងារ</td>
                <td class="val-col">{{ $joiningDateDisplay }}</td>
            </tr>
            <tr>
                <td class="label-col">ប្រភេទកិច្ចសន្យា</td>
                <td class="val-col">
                    <span class="box-check">{{ $isUDC ? '☑' : '□' }}</span> មិនកំណត់ថិរវេលា (UDC) &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="box-check">{{ $isFDC ? '☑' : '□' }}</span> មានកំណត់ថិរវេលា (FDC) &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="box-check">{{ $isOtherContract ? '☑' : '□' }}</span> ផ្សេងៗ
                </td>
            </tr>
            <tr>
                <td class="label-col">ស្ថានភាពការងារបច្ចុប្បន្ន</td>
                <td class="val-col">
                    <span class="box-check">{{ $isCurrentActive ? '☑' : '□' }}</span> កំពុងបម្រើការងារ &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="box-check">{{ !$isCurrentActive ? '☑' : '□' }}</span> ផ្សេងៗ៖ {{ !$isCurrentActive ? ($profile->employment_status ?: 'បានឈប់សម្រាក') : '................................................' }}
                </td>
            </tr>
            <tr>
                <td class="label-col">ប្រាក់បៀវត្សរ៍</td>
                <td class="val-col">
                    <strong>{{ $salaryDisplay }}</strong> USD/ខែ &nbsp;&nbsp;&nbsp;&nbsp;
                    (<span class="box-check">{{ $includeSal ? '☑' : '□' }}</span> បញ្ជាក់ &nbsp;&nbsp; <span class="box-check">{{ !$includeSal ? '☑' : '□' }}</span> មិនបញ្ជាក់)
                </td>
            </tr>
        </table>

        <!-- Concluding Confirmation Statement -->
        <div class="closing-text">
            បុគ្គលខាងលើពិតជាបាន/កំពុងបម្រើការងារនៅក្រុមហ៊ុន/សហគ្រាសរបស់យើងខ្ញុំ តាមព័ត៌មានដែលបានបញ្ជាក់ខាងលើ។ លិខិតនេះត្រូវបានចេញជូនតាមសំណើរបស់សាមីខ្លួន ដើម្បីប្រើប្រាស់សម្រាប់ <strong>{{ $certPurpose }}</strong>។<br><br>
            យើងខ្ញុំសូមបញ្ជាក់ថា ព័ត៌មានខាងលើត្រឹមត្រូវតាមឯកសារ និងព័ត៌មានដែលមាននៅក្នុងក្រុមហ៊ុន/សហគ្រាស គិតត្រឹមកាលបរិច្ឆេទចេញលិខិតនេះ។<br><br>
            <strong>សូមអរគុណ។</strong>
        </div>

        <!-- Signature Block -->
        <table class="signature-table">
            <tr>
                <td>
                    <strong>ត្រាក្រុមហ៊ុន/សហគ្រាស</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">(Company Stamp)</span>
                </td>
                <td>
                    <strong>ហត្ថលេខា និងឈ្មោះអ្នកមានសិទ្ធិ</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">(Authorized Signature & Name)</span>
                    <br><br><br><br>
                    <strong>({{ $repName }})</strong><br>
                    <span style="font-size: 9.5pt; color: #334155;">តួនាទី៖ {{ $repRole }}</span>
                </td>
            </tr>
        </table>

    </div>
</div>
</body>
</html>