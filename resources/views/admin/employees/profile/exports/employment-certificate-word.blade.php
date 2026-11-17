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

    // Work Duration Calculation (Total Year, Month, Day)
    $calcDuration = function ($startDate, $endDate = null) use ($toKhmerNum) {
        if (!$startDate) return '................................................';
        try {
            $start = \Illuminate\Support\Carbon::parse($startDate)->startOfDay();
            $end = $endDate ? \Illuminate\Support\Carbon::parse($endDate)->endOfDay() : now()->endOfDay();
            if ($start->greaterThan($end)) return '................................................';
            $diff = $start->diff($end);
            $y = $diff->y;
            $m = $diff->m;
            $d = $diff->d;

            $partsKhmer = [];
            $partsEng = [];
            if ($y > 0) {
                $partsKhmer[] = $toKhmerNum($y) . ' ឆ្នាំ';
                $partsEng[] = $y . ' ' . ($y > 1 ? 'Years' : 'Year');
            }
            if ($m > 0) {
                $partsKhmer[] = $toKhmerNum($m) . ' ខែ';
                $partsEng[] = $m . ' ' . ($m > 1 ? 'Months' : 'Month');
            }
            if ($d > 0 || empty($partsKhmer)) {
                $partsKhmer[] = $toKhmerNum($d) . ' ថ្ងៃ';
                $partsEng[] = $d . ' ' . ($d > 1 ? 'Days' : 'Day');
            }

            $khmerText = implode(' ', $partsKhmer);
            $engText = implode(', ', $partsEng);
            return $khmerText . ($engText ? ' (' . $engText . ')' : '');
        } catch (\Throwable $e) {
            return '................................................';
        }
    };

    // Employment status
    $employmentStatus = $profile->employment_status ?: ((int) $employee->is_active === 1 ? 'active' : 'inactive');
    $isCurrentActive = ($employmentStatus === 'active' || (int) $employee->is_active === 1);

    $endDateForTenure = (!$isCurrentActive && filled($profile->last_working_date))
        ? $profile->last_working_date
        : now();
    $totalDurationDisplay = $workDuration ?? $calcDuration($employee->joining_date, $endDateForTenure);

    // Working Time (Time In & Time Out)
    $formatTime = function ($time) {
        if (!$time) return null;
        try {
            return \Illuminate\Support\Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            return (string) $time;
        }
    };

    $timeInDisplay = $timeIn ?? ($employee->officeTime?->opening_time
        ? $formatTime($employee->officeTime->opening_time)
        : '08:00 AM');
    $timeOutDisplay = $timeOut ?? ($employee->officeTime?->closing_time
        ? $formatTime($employee->officeTime->closing_time)
        : '05:00 PM');
    $shiftName = $employee->officeTime?->shift ?: '';

    // Contract type detection
    $contractType = strtolower((string) ($contract?->contract_type ?? ''));
    $isUDC = ($contractType === 'udc' || str_contains($contractType, 'undetermined') || str_contains($contractType, 'មិនកំណត់'));
    $isFDC = ($contractType === 'fdc' || str_contains($contractType, 'fixed') || str_contains($contractType, 'កំណត់'));
    $isOtherContract = !$isUDC && !$isFDC && filled($contractType);

    // Company & Branch Meta
    $companyNameKhmer = 'គ្នាយើង (KNEAYERNG PHONE SHOP)';
    $branchAddress = trim((string) ($branchAddress ?? ($employee->branch?->address ?: ($employee->company?->address ?: ''))));
    if (empty($branchAddress)) {
        $branchAddress = 'រាជធានីភ្នំពេញ ព្រះរាជាណាចក្រកម្ពុជា';
    }
    $branchPhone = trim((string) ($employee->branch?->phone ?: ($employee->company?->phone ?: '')));
    if (empty($branchPhone)) {
        $branchPhone = '012 345 678 / 098 765 432';
    }
    $branchEmail = trim((string) ($employee->company?->email ?: ''));
    if (empty($branchEmail)) {
        $branchEmail = 'kneayerngphoneshop@gmail.com';
    }

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
            margin: 22pt 36pt 22pt 36pt; /* Compact margins to avoid spillover */
            mso-page-orientation: portrait;
        }
        div.EmploymentCertificate { page: EmploymentCertificate; }
        body {
            font-family: "Khmer OS Battambang", "Khmer OS", "Khmer Busra", Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
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
            padding-bottom: 5pt;
            border-bottom: 1.5pt solid #092653;
        }
        .company-title {
            color: #092653;
            font-size: 12pt;
            font-weight: bold;
            font-family: "Khmer OS Moul", "Khmer OS", Arial, sans-serif;
        }
        .meta-info {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.35;
        }
        .doc-title {
            text-align: center;
            margin-top: 7pt;
            margin-bottom: 7pt;
        }
        .doc-title h2 {
            font-family: "Khmer OS Moul", "Khmer OS", Arial, sans-serif;
            font-size: 15pt;
            color: #092653;
            margin: 0;
            padding: 0;
        }
        .doc-title .eng-sub {
            font-family: "Cinzel", "Times New Roman", serif;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 2px;
            color: #64748b;
            margin-top: 1pt;
        }
        .intro-text {
            font-size: 10pt;
            line-height: 1.45;
            margin-bottom: 6pt;
            text-align: justify;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6pt;
        }
        .data-table td {
            border: 1pt solid #94a3b8;
            padding: 3.5pt 6pt;
            font-size: 9.5pt;
            line-height: 1.35;
            vertical-align: middle;
        }
        .data-table .label-col {
            width: 28%;
            background-color: #f8fafc;
            font-weight: bold;
            color: #1e293b;
        }
        .data-table .val-col {
            width: 72%;
        }
        .box-check {
            font-size: 11pt;
            font-family: "Arial", sans-serif;
            font-weight: bold;
            margin-right: 2pt;
        }
        .closing-text {
            font-size: 9.5pt;
            line-height: 1.45;
            margin-bottom: 8pt;
            text-align: justify;
        }
        .signature-table {
            width: 100%;
            margin-top: 8pt;
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
            យើងខ្ញុំ <strong>{{ $repName }}</strong> ជា [{{ $repRole }}] នៃ {{ $companyNameKhmer }} សូមបញ្ជាក់ថា៖
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
                <td class="label-col">រយៈពេលបម្រើការងារសរុប</td>
                <td class="val-col"><strong>{{ $totalDurationDisplay }}</strong> <span style="font-size: 9pt; color: #64748b;">(គិតត្រឹមកាលបរិច្ឆេទចេញលិខិត)</span></td>
            </tr>
            <tr>
                <td class="label-col">ម៉ោងបំពេញការងារ (Time In / Out)</td>
                <td class="val-col">
                    <strong>ម៉ោងចូល (Time In)៖</strong> {{ $timeInDisplay }} &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp; <strong>ម៉ោងចេញ (Time Out)៖</strong> {{ $timeOutDisplay }}
                    @if($shiftName)
                        <span style="font-size: 9.5pt; color: #475569;">({{ $shiftName }})</span>
                    @endif
                </td>
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
            បុគ្គលខាងលើពិតជាបាន/កំពុងបម្រើការងារនៅ {{ $companyNameKhmer }} តាមព័ត៌មានដែលបានបញ្ជាក់ខាងលើ។ លិខិតនេះត្រូវបានចេញជូនតាមសំណើរបស់សាមីខ្លួន ដើម្បីប្រើប្រាស់សម្រាប់ <strong>{{ $certPurpose }}</strong>。<br>
            យើងខ្ញុំសូមបញ្ជាក់ថា ព័ត៌មានខាងលើត្រឹមត្រូវតាមឯកសារ និងព័ត៌មានជាក់ស្ដែង គិតត្រឹមកាលបរិច្ឆេទចេញលិខិតនេះ。<br>
            <strong>សូមអរគុណ។</strong>
        </div>

        <!-- Signature Block -->
        <table class="signature-table">
            <tr>
                <td>
                    <strong>ត្រាផ្លូវការ</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">(Official Stamp)</span>
                </td>
                <td>
                    <strong>ហត្ថលេខា និងឈ្មោះអ្នកមានសិទ្ធិ</strong><br>
                    <span style="font-size: 8.5pt; color: #64748b;">(Authorized Signature & Name)</span>
                    <div style="height: 38pt;"></div>
                    <strong>({{ $repName }})</strong><br>
                    <span style="font-size: 9pt; color: #334155;">តួនាទី៖ {{ $repRole }}</span>
                </td>
            </tr>
        </table>

    </div>
</div>
</body>
</html>