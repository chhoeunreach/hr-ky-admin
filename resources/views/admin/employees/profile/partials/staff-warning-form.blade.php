@php
    $warningRecord = $discipline->first();
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '____/____/________';
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
    $warningTypes = [
        'verbal_warning' => 'Verbal Warning / ព្រមានមាត់',
        'written_warning' => 'Written Warning / ព្រមានជាលាយលក្ខណ៍អក្សរ',
        'final_warning' => 'Final Written Warning / ព្រមានចុងក្រោយ',
        'disciplinary_action' => 'Disciplinary Notice / លិខិតវិន័យ',
        'performance_warning' => 'Performance Warning / ការងារ',
        'attendance_warning' => 'Attendance Warning / វត្តមាន',
        'policy_violation' => 'Policy Violation / រំលោភគោលការណ៍',
        'other' => 'Other / ផ្សេងៗ: __________',
    ];
    $statuses = [
        'draft' => 'Draft / ព្រាង',
        'issued' => 'Issued / បានចេញ',
        'acknowledged' => 'Acknowledged / បានទទួលស្គាល់',
        'resolved' => 'Resolved / បានដោះស្រាយ',
        'under_review' => 'Under Review / កំពុងពិនិត្យ',
        'escalated' => 'Escalated / បញ្ជូនបន្ត',
        'cancelled' => 'Cancelled / លុបចោល',
    ];
@endphp

@can('employee.warning_form.print')
    <div class="employee-complete-toolbar">
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="printStaffWarningForm()">
            <i class="link-icon" data-feather="printer"></i> Print Staff Warning
        </button>
    </div>
@endcan

<div class="employee-complete-paper employee-warning-paper">
    <div class="employee-complete-header">
        <img class="employee-complete-logo" src="{{ $formLogo }}" alt="Kneayerng Phone Shop Logo">
        <div class="employee-warning-title">Staff Warning / Disciplinary Action Form</div>
        <h4>បែបបទព្រមាន និងវិធានការវិន័យបុគ្គលិក</h4>
        <small class="employee-complete-confidential">HR RECORD - CONFIDENTIAL / ឯកសារធនធានមនុស្ស - រក្សាការសម្ងាត់</small>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>1. Employee Information / ព័ត៌មានបុគ្គលិក</h6>
        <div class="employee-complete-list">
            {!! $field('Warning No. / លេខព្រមាន', $warningRecord ? 'WRN-' . str_pad((string) $warningRecord->id, 5, '0', STR_PAD_LEFT) : null) !!}
            {!! $field('Warning Date / ថ្ងៃព្រមាន', $formatDate($warningRecord?->created_at)) !!}
            {!! $field('Employee ID / លេខបុគ្គលិក', $employee->employee_code ?: $employee->username) !!}
            {!! $field('Employee Name / ឈ្មោះ', $employee->english_name ?: $employee->name) !!}
            {!! $field('Department / ផ្នែក', $employee->department?->dept_name) !!}
            {!! $field('Position / តួនាទី', $employee->post?->post_name) !!}
            {!! $field('Branch / សាខា', $employee->branch?->name) !!}
            {!! $field('Supervisor / អ្នកគ្រប់គ្រង', $employee->supervisor?->name) !!}
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>2. Warning Type / ប្រភេទការព្រមាន</h6>
        <div class="employee-warning-checks">
            @foreach($warningTypes as $type => $label)
                <div class="employee-warning-check">
                    <span class="employee-warning-box">{{ $warningRecord?->record_type === $type ? '✓' : '' }}</span>
                    <span>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>3. Incident / Violation Information / ព័ត៌មានអំពីករណី</h6>
        <div class="employee-complete-list">
            {!! $field('Incident Date / ថ្ងៃកើតហេតុ', $formatDate($warningRecord?->incident_date)) !!}
            {!! $field('Incident Time / ម៉ោង', null) !!}
            {!! $field('Location / ទីតាំង', $employee->branch?->name) !!}
            {!! $field('Category / ប្រភេទ', $warningRecord?->severity ?: $warningRecord?->warning_level) !!}
        </div>
        <div class="mt-2">
            <strong>Description of Incident / ពិពណ៌នាអំពីហេតុការណ៍</strong>
            <div class="employee-warning-lines">{{ $warningRecord?->description }}</div>
        </div>
        <div class="mt-2">
            <strong>Company Policy / Rule Violated / គោលការណ៍ ឬបទបញ្ជាដែលបានរំលោភ</strong>
            <div class="employee-warning-lines"></div>
        </div>
        <div class="mt-2">
            {!! $field('Previous Related Warnings / ប្រវត្តិការព្រមានពាក់ព័ន្ធ', $discipline->count() > 1 ? ($discipline->count() - 1) . ' previous record(s)' : null) !!}
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>4. Employee Explanation / ការបំភ្លឺរបស់បុគ្គលិក</h6>
        <div class="employee-warning-lines tall"></div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>5. Corrective Action / វិធានការកែតម្រូវ</h6>
        <div class="employee-complete-list">
            {!! $field('Required Corrective Action / វិធានការកែតម្រូវ', $warningRecord?->action_taken) !!}
            {!! $field('Improvement Deadline / ថ្ងៃកំណត់កែលម្អ', null) !!}
            {!! $field('Follow-up Date / ថ្ងៃតាមដាន', null) !!}
            {!! $field('Responsible Supervisor / អ្នកទទួលខុសត្រូវ', $employee->supervisor?->name) !!}
        </div>
        <div class="mt-2">
            <strong>Consequence if Repeated / ផលវិបាកប្រសិនបើកើតឡើងម្តងទៀត</strong>
            <div class="employee-warning-lines"></div>
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>6. Status / ស្ថានភាព</h6>
        <div class="employee-warning-checks">
            @foreach($statuses as $status => $label)
                <div class="employee-warning-check">
                    <span class="employee-warning-box">{{ $warningRecord?->status === $status || ($warningRecord?->status === 'active' && $status === 'issued') ? '✓' : '' }}</span>
                    <span>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>7. Acknowledgement &amp; Signatures / ការទទួលស្គាល់ និងហត្ថលេខា</h6>
        <strong>Employee Comments / មតិយោបល់បុគ្គលិក</strong>
        <div class="employee-warning-lines"></div>
        <div class="employee-complete-signatures employee-warning-signatures mt-2">
            @foreach(['Employee / បុគ្គលិក', 'Supervisor / អ្នកគ្រប់គ្រង', 'HR / ធនធានមនុស្ស'] as $signature)
                <div class="employee-complete-signature">
                    <strong>{{ $signature }}</strong><br><br>
                    Signature: __________________<br><br>
                    Date: ____/____/________
                </div>
            @endforeach
        </div>
    </div>

    <div class="employee-warning-footer">
        HR Record - Confidential / ឯកសារធនធានមនុស្ស - រក្សាការសម្ងាត់
    </div>
</div>
