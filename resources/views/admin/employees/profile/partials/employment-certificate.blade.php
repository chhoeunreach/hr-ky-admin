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

    $certificateLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : asset('assets/images/logo.png');

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

    // Employee information
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
    $branchWebsite = 'www.kneayerng.com';

    $certificateNumber = 'EMP-CERT-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT) . '/' . now()->format('Y');
    $issueDateKhmer = 'ធ្វើនៅរាជធានីភ្នំពេញ, ថ្ងៃទី ' . $toKhmerNum(now()->format('d')) . ' ខែ ' . ($khmerMonths[(int) now()->format('m')] ?? '') . ' ឆ្នាំ ' . $toKhmerNum(now()->format('Y'));

    $authorizedSignatoryName = auth('admin')->user()?->name ?: (auth()->user()?->name ?: 'ម៉េង សោភា');
    $authorizedSignatoryRole = 'ប្រធានផ្នែកធនធានមនុស្ស / HR Manager';
    $defaultPurpose = 'ការងាររដ្ឋបាល ទំនាក់ទំនងស្ថាប័នធនាគារ និងបំពេញបែបបទផ្សេងៗតាមផ្លូវច្បាប់';
@endphp

<div class="employee-complete-toolbar employee-employment-certificate-toolbar mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="printEmploymentCertificate()">
            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }} (A4)
        </button>
        <a href="{{ route('admin.employees.profile.employment-certificate.word', $employee->id) }}"
           id="downloadEmploymentCertificateWordBtn"
           class="btn btn-success btn-sm"
           target="_blank">
            <i class="link-icon" data-feather="file-text"></i> {{ __('index.employment_certificate_download_word') }}
        </a>
    </div>

    <!-- Quick Customizer Controls -->
    <div class="d-flex align-items-center gap-3 flex-wrap bg-light p-1.5 px-3 rounded border">
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="empCertToggleSalary" checked onchange="onEmpCertCustomChange()">
            <label class="form-check-label fw-bold text-dark small" for="empCertToggleSalary">
                💰 បញ្ជាក់ប្រាក់បៀវត្សរ៍ (Include Salary)
            </label>
        </div>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleEmpCertSettings()">
            ⚙️ កែប្រែព័ត៌មានលិខិត (Customize)
        </button>
    </div>
</div>

<!-- Collapsible Customization Bar -->
<div class="card mb-3 border shadow-sm d-none" id="empCertSettingsCard">
    <div class="card-body p-3 bg-light">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">លេខយោងលិខិត (Ref No.)</label>
                <input type="text" id="empCertRefInput" class="form-control form-control-sm" value="{{ $certificateNumber }}" oninput="onEmpCertCustomChange()">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">ឈ្មោះអ្នកតំណាង / ចុះហត្ថលេខា</label>
                <input type="text" id="empCertRepNameInput" class="form-control form-control-sm" value="{{ $authorizedSignatoryName }}" oninput="onEmpCertCustomChange()">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">តួនាទីអ្នកតំណាង</label>
                <input type="text" id="empCertRepRoleInput" class="form-control form-control-sm" value="{{ $authorizedSignatoryRole }}" oninput="onEmpCertCustomChange()">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">គោលបំណងចេញលិខិត (Purpose)</label>
                <input type="text" id="empCertPurposeInput" class="form-control form-control-sm" value="{{ $defaultPurpose }}" oninput="onEmpCertCustomChange()">
            </div>
        </div>
    </div>
</div>

<div class="employee-complete-paper employee-employment-certificate-paper">
    <div class="employee-employment-certificate-content">

        <!-- Company Letterhead Header -->
        <header class="employee-employment-certificate-header">
            <div class="d-flex align-items-start justify-content-between gap-3 border-bottom pb-2.5">
                <div class="d-flex align-items-center gap-3">
                    <img class="employee-employment-certificate-logo" src="{{ $certificateLogo }}" alt="Company Logo" style="width: 58px; height: 58px; object-fit: contain;">
                    <div>
                        <div class="fw-bold font-moul text-primary" style="font-size: 14.5px; line-height: 1.4; color: #092653 !important;">
                            {{ $companyNameKhmer }}
                        </div>
                        <div class="small text-muted font-kantumruy" style="font-size: 11.5px; margin-top: 2px;">
                            <strong>អាសយដ្ឋាន៖</strong> <span id="empCertAddressSpan">{{ $branchAddress }}</span>
                        </div>
                        <div class="small text-muted font-kantumruy" style="font-size: 11px;">
                            <strong>ទូរស័ព្ទ៖</strong> {{ $branchPhone }} &nbsp;·&nbsp; <strong>អ៊ីមែល៖</strong> {{ $branchEmail }}
                        </div>
                    </div>
                </div>

                <div class="text-end font-kantumruy shrink-0" style="min-width: 175px;">
                    <div class="small text-muted">
                        <strong>លេខ៖</strong> <span id="empCertRefDisplay" class="font-monospace fw-bold text-dark">{{ $certificateNumber }}</span>
                    </div>
                    <div class="small text-muted mt-1" style="font-size: 11px;">
                        <span id="empCertDateDisplay">{{ $issueDateKhmer }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Document Main Title -->
        <div class="text-center my-3.5">
            <h2 class="font-moul mb-1 text-primary" style="font-size: 21px; color: #092653 !important; letter-spacing: 0.5px;">
                លិខិតបញ្ជាក់ការងារ
            </h2>
            <div class="font-cinzel text-secondary fw-bold" style="font-size: 12px; letter-spacing: 2px; text-transform: uppercase;">
                Certificate of Employment
            </div>
            <div class="mx-auto mt-1" style="width: 120px; height: 2px; background: linear-gradient(to right, transparent, #D4A32C, transparent);"></div>
        </div>

        <!-- Preamble / Introduction Statement -->
        <div class="font-kantumruy mb-3" style="font-size: 13px; line-height: 1.8; text-align: justify;">
            យើងខ្ញុំ <strong id="empCertRepNameDisplay" class="text-dark">{{ $authorizedSignatoryName }}</strong> ជា [<span id="empCertRepRoleDisplay">{{ $authorizedSignatoryRole }}</span>] នៃក្រុមហ៊ុន/សហគ្រាសខាងលើ សូមបញ្ជាក់ថា៖
        </div>

        <!-- Employment Verification Table (Exact User Specification) -->
        <table class="table table-bordered employee-employment-table font-kantumruy mb-3" style="font-size: 12.5px; border-color: #cbd5e1;">
            <tbody>
                <tr>
                    <td class="fw-bold bg-light" style="width: 28%; vertical-align: middle;">គោត្តនាម និងនាម</td>
                    <td style="width: 72%;">
                        <strong class="text-dark" style="font-size: 13.5px;">{{ $fullNameDisplay }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">ភេទ និង ថ្ងៃខែឆ្នាំកំណើត</td>
                    <td>
                        <span><strong>ភេទ៖</strong> {{ $genderDisplay }}</span>
                        <span class="mx-3 text-muted">|</span>
                        <span><strong>ថ្ងៃខែឆ្នាំកំណើត៖</strong> {{ $dobDisplay }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">លេខអត្តសញ្ញាណប័ណ្ណ / លិខិតឆ្លងដែន</td>
                    <td>
                        <span class="font-monospace fw-bold text-dark">{{ $nationalIdDisplay }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">មុខតំណែង</td>
                    <td>
                        <strong class="text-dark">{{ $positionDisplay }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">ផ្នែក / សាខា</td>
                    <td>
                        <span>{{ $departmentBranchDisplay }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">ថ្ងៃចូលបម្រើការងារ</td>
                    <td>
                        <span>{{ $joiningDateDisplay }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">ប្រភេទកិច្ចសន្យា</td>
                    <td>
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <span><span class="emp-cert-box">{{ $isUDC ? '☑' : '□' }}</span> មិនកំណត់ថិរវេលា (UDC)</span>
                            <span><span class="emp-cert-box">{{ $isFDC ? '☑' : '□' }}</span> មានកំណត់ថិរវេលា (FDC)</span>
                            <span><span class="emp-cert-box">{{ $isOtherContract ? '☑' : '□' }}</span> ផ្សេងៗ</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light" style="vertical-align: middle;">ស្ថានភាពការងារបច្ចុប្បន្ន</td>
                    <td>
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <span><span class="emp-cert-box">{{ $isCurrentActive ? '☑' : '□' }}</span> កំពុងបម្រើការងារ</span>
                            <span><span class="emp-cert-box">{{ !$isCurrentActive ? '☑' : '□' }}</span> ផ្សេងៗ៖ {{ !$isCurrentActive ? ($profile->employment_status ?: 'បានឈប់សម្រាក') : '................................................' }}</span>
                        </div>
                    </td>
                </tr>
                <tr id="empCertSalaryRow">
                    <td class="fw-bold bg-light" style="vertical-align: middle;">ប្រាក់បៀវត្សរ៍</td>
                    <td>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="font-monospace fw-bold text-success fs-6" id="empCertSalaryAmountSpan">{{ $salaryDisplay }}</span>
                                <span class="fw-semibold">USD / ខែ</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span><span class="emp-cert-box" id="empCertBoxCertified">☑</span> បញ្ជាក់</span>
                                <span><span class="emp-cert-box" id="empCertBoxUncertified">□</span> មិនបញ្ជាក់</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Concluding Confirmation Statement -->
        <div class="font-kantumruy mb-3" style="font-size: 12.5px; line-height: 1.85; text-align: justify;">
            <p class="mb-2">
                បុគ្គលខាងលើពិតជាបាន/កំពុងបម្រើការងារនៅក្រុមហ៊ុន/សហគ្រាសរបស់យើងខ្ញុំ តាមព័ត៌មានដែលបានបញ្ជាក់ខាងលើ។ លិខិតនេះត្រូវបានចេញជូនតាមសំណើរបស់សាមីខ្លួន ដើម្បីប្រើប្រាស់សម្រាប់ <strong id="empCertPurposeDisplay" class="text-dark">{{ $defaultPurpose }}</strong>។
            </p>
            <p class="mb-1">
                យើងខ្ញុំសូមបញ្ជាក់ថា ព័ត៌មានខាងលើត្រឹមត្រូវតាមឯកសារ និងព័ត៌មានដែលមាននៅក្នុងក្រុមហ៊ុន/សហគ្រាស គិតត្រឹមកាលបរិច្ឆេទចេញលិខិតនេះ។
            </p>
            <p class="fw-bold mb-0">សូមអរគុណ។</p>
        </div>

        <!-- Signature & Stamp Section -->
        <div class="row font-kantumruy mt-4 pt-2">
            <div class="col-6 text-center">
                <!-- Left Column placeholder / Stamp -->
                <div class="text-muted small" style="min-height: 80px;">
                    <div class="fw-bold text-dark mb-1">ត្រាក្រុមហ៊ុន/សហគ្រាស</div>
                    <small class="text-muted">(Company Official Stamp)</small>
                </div>
            </div>

            <div class="col-6 text-center">
                <div class="fw-bold text-dark mb-1">ហត្ថលេខា និងឈ្មោះអ្នកមានសិទ្ធិ</div>
                <small class="text-muted">(Authorized Signature & Name)</small>

                <div style="height: 65px;" class="d-flex align-items-end justify-content-center">
                    <!-- Signature space -->
                </div>

                <div class="fw-bold text-dark" style="font-size: 13.5px;" id="empCertSignNameDisplay">
                    ({{ $authorizedSignatoryName }})
                </div>
                <div class="small text-muted mt-0.5" id="empCertSignRoleDisplay">
                    តួនាទី៖ {{ $authorizedSignatoryRole }}
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .employee-employment-certificate-paper {
        background: #ffffff;
        width: 100%;
        max-width: 820px;
        min-height: 1040px;
        margin: 0 auto;
        padding: 40px 48px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-sizing: border-box;
    }
    .employee-employment-table td {
        padding: 7px 12px;
        line-height: 1.5;
        vertical-align: middle;
    }
    .emp-cert-box {
        font-size: 14px;
        font-weight: bold;
        margin-right: 2px;
    }
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        body * {
            visibility: hidden;
        }
        #employeeEmploymentCertificatePrintRoot,
        #employeeEmploymentCertificatePrintRoot * {
            visibility: visible;
        }
        #employeeEmploymentCertificatePrintRoot {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }
        .employee-employment-certificate-toolbar,
        #empCertSettingsCard {
            display: none !important;
        }
    }
</style>

<script>
    function toggleEmpCertSettings() {
        var card = document.getElementById('empCertSettingsCard');
        if (card) card.classList.toggle('d-none');
    }

    function onEmpCertCustomChange() {
        var toggleSalary = document.getElementById('empCertToggleSalary');
        var refInput = document.getElementById('empCertRefInput');
        var repNameInput = document.getElementById('empCertRepNameInput');
        var repRoleInput = document.getElementById('empCertRepRoleInput');
        var purposeInput = document.getElementById('empCertPurposeInput');

        var isSalaryChecked = toggleSalary ? toggleSalary.checked : true;
        var boxCert = document.getElementById('empCertBoxCertified');
        var boxUncert = document.getElementById('empCertBoxUncertified');
        var salaryAmount = document.getElementById('empCertSalaryAmountSpan');

        if (boxCert && boxUncert) {
            if (isSalaryChecked) {
                boxCert.innerText = '☑';
                boxUncert.innerText = '□';
                if (salaryAmount) salaryAmount.style.textDecoration = 'none';
            } else {
                boxCert.innerText = '□';
                boxUncert.innerText = '☑';
                if (salaryAmount) salaryAmount.style.textDecoration = 'line-through';
            }
        }

        if (refInput) {
            var refDisplay = document.getElementById('empCertRefDisplay');
            if (refDisplay) refDisplay.innerText = refInput.value;
        }

        if (repNameInput) {
            var repNameDisplay = document.getElementById('empCertRepNameDisplay');
            var signNameDisplay = document.getElementById('empCertSignNameDisplay');
            if (repNameDisplay) repNameDisplay.innerText = repNameInput.value;
            if (signNameDisplay) signNameDisplay.innerText = '(' + repNameInput.value + ')';
        }

        if (repRoleInput) {
            var repRoleDisplay = document.getElementById('empCertRepRoleDisplay');
            var signRoleDisplay = document.getElementById('empCertSignRoleDisplay');
            if (repRoleDisplay) repRoleDisplay.innerText = repRoleInput.value;
            if (signRoleDisplay) signRoleDisplay.innerText = 'តួនាទី៖ ' + repRoleInput.value;
        }

        if (purposeInput) {
            var purposeDisplay = document.getElementById('empCertPurposeDisplay');
            if (purposeDisplay) purposeDisplay.innerText = purposeInput.value;
        }

        // Update word download button URL with current query parameters
        var wordBtn = document.getElementById('downloadEmploymentCertificateWordBtn');
        if (wordBtn) {
            var base = @json(route('admin.employees.profile.employment-certificate.word', $employee->id));
            var params = new URLSearchParams();
            params.set('include_salary', isSalaryChecked ? '1' : '0');
            if (refInput && refInput.value) params.set('reference_number', refInput.value);
            if (repNameInput && repNameInput.value) params.set('authorized_name', repNameInput.value);
            if (repRoleInput && repRoleInput.value) params.set('authorized_role', repRoleInput.value);
            if (purposeInput && purposeInput.value) params.set('purpose', purposeInput.value);
            wordBtn.href = base + '?' + params.toString();
        }
    }
</script>