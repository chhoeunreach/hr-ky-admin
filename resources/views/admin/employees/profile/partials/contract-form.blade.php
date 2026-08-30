@php
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '____/____/________';
    $dateInput = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : null;
    $valueOrLine = fn ($value) => filled($value) ? $value : '____________________';
    $field = function ($label, $value) use ($valueOrLine) {
        return '<div class="employee-complete-field"><span>' . e($label) . '</span><strong>' . e($valueOrLine($value)) . '</strong></div>';
    };
    $companyLogo = \App\Helpers\AppHelper::getCompanyLogo()
        ? asset(\App\Models\Company::UPLOAD_PATH . \App\Helpers\AppHelper::getCompanyLogo())
        : null;
    $branchLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : null;
    $formLogo = $branchLogo ?: ($companyLogo ?: asset('assets/images/img.png'));
    $defaultContractNo = 'CON-' . str_pad((string) $employee->id, 5, '0', STR_PAD_LEFT);
    $contractResponsibilities = $canViewKpi ? $responsibilities->pluck('title')->filter()->join(', ') : null;
    $defaultWorkTime = $employee->officeTime
        ? trim(($employee->officeTime->shift ? $employee->officeTime->shift . ' ' : '') . '(' . $employee->officeTime->opening_time . ' - ' . $employee->officeTime->closing_time . ')')
        : null;
    $contractAttachments = collect($contract->attachments ?? []);
    $oldAttachments = old('attachments', $contractAttachments->all());
    $isAttachmentChecked = fn ($key) => in_array($key, $oldAttachments, true);

    $contractNo = old('contract_no', $contract->contract_no ?: $defaultContractNo);
    $contractDate = old('contract_date', $dateInput($contract->contract_date) ?: now()->format('Y-m-d'));
    $shopName = old('shop_name', $contract->shop_name ?: 'គ្នាយើង');
    $shopRepresentative = old('shop_representative', $contract->shop_representative);
    $shopAddress = old('shop_address', $contract->shop_address ?: 'គល់ស្ពាន ៧ មករា, ទឹកល្អក ១, ផ្លូវ ៩៧០B កម្ពុជាក្រោម, ភ្នំពេញ');
    $birthAddress = old('birth_address', $contract->birth_address);
    $guardianPhone = old('guardian_phone', $contract->guardian_phone ?: $profile->emergency_contact_phone);
    $jobTitle = old('job_title', $contract->job_title ?: $employee->post?->post_name);
    $mainResponsibilities = old('main_responsibilities', $contract->main_responsibilities ?: $contractResponsibilities);
    $additionalResponsibilities = old('additional_responsibilities', $contract->additional_responsibilities);
    $assetResponsibilities = old('asset_responsibilities', $contract->asset_responsibilities ?: 'បុគ្គលិកត្រូវទទួលខុសត្រូវក្នុងការថែរក្សាទ្រព្យសម្បត្តិ និងឧបករណ៍របស់ហាងតាមការប្រើប្រាស់។');
    $probationSalary = old('probation_salary', $canViewSalary ? ($contract->probation_salary ?: ($profile->starting_salary ?: $profile->current_base_salary)) : null);
    $extraSalary = old('extra_salary', $canViewSalary ? ($contract->extra_salary ?: $profile->allowances) : null);
    $monthlySalary = old('monthly_salary', $canViewSalary ? ($contract->monthly_salary ?: $profile->current_base_salary) : null);
    $salaryCurrency = old('salary_currency', $contract->salary_currency ?: 'USD');
    $probationPeriodText = old('probation_period_text', $contract->probation_period_text ?: $profile->probation_period);
    $mainContractPeriod = old('main_contract_period', $contract->main_contract_period);
    $contractStartDate = old('contract_start_date', $dateInput($contract->contract_start_date ?: $profile->contract_start_date ?: $employee->joining_date));
    $contractEndDate = old('contract_end_date', $dateInput($contract->contract_end_date ?: $profile->contract_end_date));
    $paymentDateText = old('payment_date_text', $canViewSalary ? ($contract->payment_date_text ?: ($profile->salary_payment_date ?: '១ ដល់ ៥')) : null);
    $benefits = old('benefits', $canViewSalary ? ($contract->benefits ?: ($profile->other_benefits ?: 'ប្រាក់ឧបត្ថម្ភ, អាហារ, ថ្លៃស្នាក់នៅ (លក្ខខណ្ឌផ្សេងៗ)')) : null);
    $workingTime = old('working_time', $contract->working_time ?: $defaultWorkTime);
    $workingDays = old('working_days', $contract->working_days ?: 'ចន្ទ → អាទិត្យ');
    $holidayText = old('holiday_text', $contract->holiday_text ?: '២ ថ្ងៃ - ថ្ងៃឈប់ធំៗ៖ ចូលឆ្នាំខ្មែរ និង ភ្ជុំបិណ្ឌ');
    $disciplineRules = old('discipline_rules', $contract->discipline_rules);
    $confidentiality = old('confidentiality', $contract->confidentiality ?: 'មិនត្រូវបង្ហាញព័ត៌មានអតិថិជន ឬរបស់ហាង។');
    $terminationTerms = old('termination_terms', $contract->termination_terms ?: 'អាចបញ្ចប់ក្នុងករណី៖ ផុតកំណត់កិច្ចសន្យា, រំលោភវិន័យ, ព្រមព្រៀងតាមស្ម័គ្រចិត្ត, ឬជូនដំណឹង 15-30 ថ្ងៃ។');
    $generalDuties = old('general_duties', $contract->general_duties ?: 'ភាគី “ក” ត្រូវផ្តល់ប្រាក់ខែតាមកិច្ចសន្យា។ ភាគី “ខ” ត្រូវអនុវត្តការងារត្រឹមត្រូវ។');
    $partyAName = old('party_a_signature_name', $contract->party_a_signature_name);
    $partyBName = old('party_b_signature_name', $contract->party_b_signature_name ?: $employee->name);
    $partyADate = old('party_a_signed_date', $dateInput($contract->party_a_signed_date));
    $partyBDate = old('party_b_signed_date', $dateInput($contract->party_b_signed_date));
    $status = old('status', $contract->status ?: 'draft');
@endphp

@can('employee.document.manage')
    <div class="employee-360-section">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h6 class="mb-2 mb-md-0">Contract Add / Update Before Print</h6>
            <div class="d-flex gap-2">
                <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="collapse"
                        data-bs-target="#contractEditCollapse"
                        aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"
                        aria-controls="contractEditCollapse">
                    Expand / Collapse
                </button>
                <button type="submit" form="contractEditForm" class="btn btn-primary btn-sm">Save / Update Contract</button>
            </div>
        </div>

        <form id="contractEditForm"
              method="POST"
              action="{{ route('admin.employees.profile.contract.save', $employee->id) }}">
            @csrf

        <div id="contractEditCollapse" class="collapse {{ $errors->any() ? 'show' : '' }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Contract No</label>
                <input type="text" name="contract_no" class="form-control" value="{{ $contractNo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Contract Date</label>
                <input type="date" name="contract_date" class="form-control" value="{{ $contractDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['draft' => 'Draft', 'active' => 'Active', 'expired' => 'Expired', 'terminated' => 'Terminated', 'cancelled' => 'Cancelled'] as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Job Title</label>
                <input type="text" name="job_title" class="form-control" value="{{ $jobTitle }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Shop Name</label>
                <input type="text" name="shop_name" class="form-control" value="{{ $shopName }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Representative</label>
                <input type="text" name="shop_representative" class="form-control" value="{{ $shopRepresentative }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Guardian Phone</label>
                <input type="text" name="guardian_phone" class="form-control" value="{{ $guardianPhone }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Shop Address</label>
                <textarea name="shop_address" rows="2" class="form-control">{{ $shopAddress }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Birth Address</label>
                <textarea name="birth_address" rows="2" class="form-control">{{ $birthAddress }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="contract_start_date" class="form-control" value="{{ $contractStartDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="contract_end_date" class="form-control" value="{{ $contractEndDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Main Contract Period</label>
                <input type="text" name="main_contract_period" class="form-control" value="{{ $mainContractPeriod }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Probation Period</label>
                <input type="text" name="probation_period_text" class="form-control" value="{{ $probationPeriodText }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Working Time</label>
                <input type="text" name="working_time" class="form-control" value="{{ $workingTime }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Working Days</label>
                <input type="text" name="working_days" class="form-control" value="{{ $workingDays }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Holiday Text</label>
                <input type="text" name="holiday_text" class="form-control" value="{{ $holidayText }}">
            </div>

            @can('employee.salary.manage')
                <div class="col-md-3">
                    <label class="form-label">Probation Salary</label>
                    <input type="number" step="0.01" name="probation_salary" class="form-control" value="{{ $probationSalary }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Extra Salary</label>
                    <input type="number" step="0.01" name="extra_salary" class="form-control" value="{{ $extraSalary }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Monthly Salary</label>
                    <input type="number" step="0.01" name="monthly_salary" class="form-control" value="{{ $monthlySalary }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Currency</label>
                    <select name="salary_currency" class="form-select">
                        <option value="USD" @selected($salaryCurrency === 'USD')>USD</option>
                        <option value="KHR" @selected($salaryCurrency === 'KHR')>KHR</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Date</label>
                    <input type="text" name="payment_date_text" class="form-control" value="{{ $paymentDateText }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Benefits</label>
                    <input type="text" name="benefits" class="form-control" value="{{ $benefits }}">
                </div>
            @endcan

            <div class="col-md-6">
                <label class="form-label">Main Responsibilities</label>
                <textarea name="main_responsibilities" rows="3" class="form-control">{{ $mainResponsibilities }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Additional Responsibilities</label>
                <textarea name="additional_responsibilities" rows="3" class="form-control">{{ $additionalResponsibilities }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">Asset Responsibilities</label>
                <textarea name="asset_responsibilities" rows="2" class="form-control">{{ $assetResponsibilities }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confidentiality</label>
                <textarea name="confidentiality" rows="2" class="form-control">{{ $confidentiality }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Termination Terms</label>
                <textarea name="termination_terms" rows="2" class="form-control">{{ $terminationTerms }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">General Duties</label>
                <textarea name="general_duties" rows="2" class="form-control">{{ $generalDuties }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">Extra Discipline Rules</label>
                <textarea name="discipline_rules" rows="2" class="form-control">{{ $disciplineRules }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Party A Name</label>
                <input type="text" name="party_a_signature_name" class="form-control" value="{{ $partyAName }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Party A Date</label>
                <input type="date" name="party_a_signed_date" class="form-control" value="{{ $partyADate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Party B Name</label>
                <input type="text" name="party_b_signature_name" class="form-control" value="{{ $partyBName }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Party B Date</label>
                <input type="date" name="party_b_signed_date" class="form-control" value="{{ $partyBDate }}">
            </div>
            <div class="col-md-12">
                <label class="form-label d-block">Attachments</label>
                @foreach(['id_copy' => 'អត្តសញ្ញាណប័ណ្ណចម្លង', 'birth_certificate' => 'សំបុត្រកំណើតចម្លង', 'family_book' => 'សៀវភៅគ្រួសារចម្លង', 'residence_book' => 'សៀវភៅស្នាក់នៅចម្លង'] as $key => $label)
                    <label class="form-check form-check-inline">
                        <input type="checkbox" name="attachments[]" class="form-check-input" value="{{ $key }}" @checked($isAttachmentChecked($key))>
                        <span class="form-check-label">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        </div>
        </form>
    </div>
@endcan

@can('employee.contract_form.print')
    <div class="employee-complete-toolbar">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="printContractForm()">
            <i class="link-icon" data-feather="printer"></i> Print Saved Contract
        </button>
    </div>
@endcan

<div class="employee-complete-paper employee-contract-paper">
    <div class="employee-complete-header">
        <img class="employee-complete-logo" src="{{ $formLogo }}" alt="Kneayerng Phone Shop Logo">
        <div class="employee-complete-khmer-brand">ហាងទូរស័ព្ទ គ្នាយើង</div>
        <div class="employee-complete-brand">KNEAYERNG PHONESHOP</div>
        <div class="employee-contract-title">កិច្ចសន្យាការងារ</div>
    </div>

    <div class="employee-complete-meta employee-contract-meta">
        <div>
            <small>កិច្ចសន្យា លេខ</small>
            <strong>{{ $contractNo }}</strong>
        </div>
        <div>
            <small>កាលបរិច្ឆេទ</small>
            <strong>{{ $contractDate }}</strong>
        </div>
    </div>

    <div class="employee-contract-party-title">ភាគីទាំងពីរ</div>
    <div class="employee-complete-section">
        <h6>ភាគី “ក” (ម្ចាស់ហាង)</h6>
        <div class="employee-complete-list">
            {!! $field('ឈ្មោះហាង', $shopName) !!}
            {!! $field('តំណាងដោយ', $shopRepresentative) !!}
            {!! $field('អាសយដ្ឋាន', $shopAddress) !!}
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>ភាគី “ខ” (បុគ្គលិក)</h6>
        <div class="employee-complete-list">
            {!! $field('អត្តលេខ', $employee->employee_code ?: $employee->username) !!}
            {!! $field('ឈ្មោះខ្មែរ', $employee->name) !!}
            {!! $field('ឡាតាំង', $employee->english_name) !!}
            {!! $field('ភេទ', $employee->gender) !!}
            {!! $field('កំណើតថ្ងៃទី', $formatDate($employee->dob)) !!}
            {!! $field('សញ្ជាតិ', $profile->nationality) !!}
            {!! $field('ស្ថានភាពគ្រួសារ', $employee->marital_status) !!}
            {!! $field('លេខទូរស័ព្ទផ្ទាល់ខ្លួន', $employee->phone) !!}
            {!! $field('លេខទូរស័ព្ទអាណាព្យាបាល', $guardianPhone) !!}
            {!! $field('អាសយដ្ឋានកំណើត', $birthAddress) !!}
            {!! $field('អាសយដ្ឋានបច្ចុប្បន្ន', $profile->current_address ?: $employee->address) !!}
            {!! $field('អត្តសញ្ញាណប័ណ្ណ/លិខិតឆ្លងដែន លេខ', $profile->national_id) !!}
        </div>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ១៖ តួនាទី និងភារកិច្ច</h6>
        <p>ភាគី "ខ" យល់ព្រមធ្វើការ ជា៖ <strong>{{ $valueOrLine($jobTitle) }}</strong></p>
        <p>ភារកិច្ចសំខាន់ៗ: {{ $mainResponsibilities ?: '...........................................................................................' }}</p>
        <p>ភារកិច្ចបន្ថែម: {{ $additionalResponsibilities ?: '............................................................................................................................' }}</p>
        <p>ការថែរក្សាទ្រព្យសម្បត្តិរបស់ហាង: {{ $assetResponsibilities }}</p>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ២៖ ថ្លៃឈ្នួល រយៈពេលកិច្ចសន្យា</h6>
        <div class="employee-complete-list">
            {!! $field('ប្រាក់ខែពេលសាកល្បង/ចុងក្រោយ', $probationSalary ? $probationSalary . ' ' . $salaryCurrency : null) !!}
            {!! $field('ប្រាក់ខែបន្ថែម', $extraSalary ? $extraSalary . ' ' . $salaryCurrency : null) !!}
            {!! $field('ប្រាក់ខែប្រចាំខែ', $monthlySalary ? $monthlySalary . ' ' . $salaryCurrency : null) !!}
            {!! $field('រយៈពេលសាកល្បង', $probationPeriodText) !!}
            {!! $field('រយៈពេលកិច្ចសន្យាចម្បង', $mainContractPeriod) !!}
            {!! $field('ចាប់ពីថ្ងៃទី', $formatDate($contractStartDate)) !!}
            {!! $field('ដល់ថ្ងៃទី', $formatDate($contractEndDate)) !!}
        </div>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ៣៖ អត្ថប្រយោជន៍</h6>
        <p>ប្រាក់បន្ថែម OT៖ គិតតាមម៉ោង (លក្ខខណ្ឌផ្សេងៗ)</p>
        <p>បង់ប្រាក់៖ ថ្ងៃទី {{ $paymentDateText ?: '១ ដល់ ៥' }} ដើមខែ</p>
        <p>អត្ថប្រយោជន៍៖ {{ $benefits ?: 'ប្រាក់ឧបត្ថម្ភ, អាហារ, ថ្លៃស្នាក់នៅ (លក្ខខណ្ឌផ្សេងៗ)' }}</p>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ៤៖ ពេលធ្វើការ និងថ្ងៃឈប់សម្រាក</h6>
        <p>ពេលធ្វើការ៖ {{ $workingTime ?: '.................. (ម៉ោង ………… ដល់ …………)' }}</p>
        <p>ថ្ងៃធ្វើការ៖ {{ $workingDays }}</p>
        <p>វិស្សមកាលប្រចាំខែ៖ {{ $holidayText }}</p>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ៥៖ វិន័យ និងបទបញ្ជា</h6>
        <p><strong>១. ការមកយឺត និងការខកខាន</strong></p>
        <ul>
            <li>៥-១៥ នាទី → អនុគ្រោះ</li>
            <li>១៦-៣០ នាទី → កាត់ប្រាក់ 0.50$</li>
            <li>លើស ៣០ នាទី → កាត់ប្រាក់ 1.50$</li>
            <li>លើស ៣ ដង/ខែ → កាត់ប្រាក់បន្ថែម 30$ ជាមួយនឹងការព្រមានចុងក្រោយ</li>
        </ul>
        <p><strong>២. ការសុំចេញកណ្ដាលថ្ងៃ / ចេញមុនម៉ោង</strong></p>
        <div class="employee-contract-rule-box">
            <ul class="employee-contract-diamond-list">
                <li>រាល់ការឈប់សម្រាកត្រូវមានអ្នកជំនួស</li>
                <li>បន្ទាប់ពី checkin ហើយរាល់ការចេញចូលត្រូវផ្ដល់ព័ត៍មាន</li>
                <li>រាល់ពេលសុំចេញក្រៅឆ្ងាយពីកន្លែងការងារត្រូវសុំ Management</li>
                <li>រាល់ការសុំយឺត 1 ម៉ោងឡើងទៅត្រូវសុំមុន ១ ថ្ងៃ</li>
                <li>យឺតលើសពី 30 នាទីដោយមិនផ្ដល់ព័ត៍មាន (កាត់ <span class="employee-contract-red">30$</span> នឹងព្រមាន ១)</li>
                <li>គ្រប់បុគ្គលិកទាំងអស់ដែលពិន័យលុយ <span class="employee-contract-red">30$</span> ចាប់ពី 3 ដងអាចប្រឈមការសម្រាកពីការងារ។</li>
                <li>ចេញ ៤ ម៉ោង → កាត់ <span class="employee-contract-red">50%</span> ប្រាក់ប្រចាំថ្ងៃ</li>
                <li>ចេញក្រោម ៣ ម៉ោង → កាត់ <span class="employee-contract-red">25%-30%</span></li>
                <li>មិនសុំចេញ → ចាត់ទុកជាការខកខាន និងវិន័យបន្ថែម</li>
            </ul>
        </div>
        <p><strong>៣. ការស្កែនចូល-ចេញវត្តមាន</strong></p>
        <ul>
            <li>មិនស្កែន ១ ដង → ព្រមានមាត់</li>
            <li>មិនស្កែន ២ ដង/ខែ → ព្រមានសរសេរ និងកាត់ប្រាក់ <span class="employee-contract-red">5$</span></li>
            <li>លើស ៣ ដង/ខែ → កាត់ប្រាក់ <span class="employee-contract-red">30$</span> និងពិន័យបន្ថែម</li>
        </ul>
        <p>ត្រូវគោរពម៉ោង, ស្មោះត្រង់, មិនបំពានទ្រព្យសម្បត្តិហាង។</p>
        @if(filled($disciplineRules))
            <p>{{ $disciplineRules }}</p>
        @endif
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ៦៖ ភាពសម្ងាត់</h6>
        <p>{{ $confidentiality }}</p>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ៧៖ ការបញ្ចប់កិច្ចសន្យា</h6>
        <p>{{ $terminationTerms }}</p>
    </div>

    <div class="employee-contract-article">
        <h6>មាត្រា ៨៖ កាតព្វកិច្ចទូទៅ</h6>
        <p>{{ $generalDuties }}</p>
    </div>

    <div class="employee-complete-section">
        <h6>ហត្ថលេខា</h6>
        <div class="employee-complete-signatures employee-contract-signatures">
            <div class="employee-complete-signature">
                <strong>ភាគី "ក" (តំណាងម្ចាស់ហាង)</strong><br><br>
                ................................................<br>
                ឈ្មោះ និងហត្ថលេខា: {{ $valueOrLine($partyAName) }}<br>
                កាលបរិច្ឆេទ: {{ $formatDate($partyADate) }}
            </div>
            <div class="employee-complete-signature">
                <strong>ភាគី "ខ" (បុគ្គលិក)</strong><br><br>
                ................................................<br>
                ឈ្មោះ និងហត្ថលេខា: {{ $valueOrLine($partyBName) }}<br>
                កាលបរិច្ឆេទ: {{ $formatDate($partyBDate) }}
            </div>
        </div>
    </div>

    <div class="employee-contract-attachments">
        <strong>ឯកសារភ្ជាប់</strong>
        <div>{{ $isAttachmentChecked('id_copy') ? '☑' : '☐' }} អត្តសញ្ញាណប័ណ្ណចម្លង / {{ $isAttachmentChecked('birth_certificate') ? '☑' : '☐' }} សំបុត្រកំណើតចម្លង</div>
        <div>{{ $isAttachmentChecked('family_book') ? '☑' : '☐' }} សៀវភៅគ្រួសារចម្លង / {{ $isAttachmentChecked('residence_book') ? '☑' : '☐' }} សៀវភៅស្នាក់នៅចម្លង</div>
    </div>
</div>

@can('employee.document.manage')
    @if(($contractHistories ?? collect())->isNotEmpty())
        <div class="employee-360-section mt-3">
            <h6>Permanent Contract History</h6>
            <div class="table-responsive">
                <table class="table table-sm employee-360-table mb-0">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Contract No</th>
                        <th>Status</th>
                        <th>Job Title</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contractHistories as $history)
                        <tr>
                            <td>{{ $history->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $history->action)) }}</td>
                            <td>{{ data_get($history->snapshot, 'contract_no', '-') }}</td>
                            <td>{{ data_get($history->snapshot, 'status', '-') }}</td>
                            <td>{{ data_get($history->snapshot, 'job_title', '-') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endcan
