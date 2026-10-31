@php
    $warningRecord = $discipline->first();
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
    $field = function ($label, $value, $type = 'text', $readonly = false) {
        $name = \Illuminate\Support\Str::slug(explode(' / ', $label)[0], '_');
        $control = $type === 'textarea'
            ? '<textarea class="employee-warning-input" rows="2" name="' . e($name) . '" aria-label="' . e($label) . '">' . e($value ?? '') . '</textarea>'
            : '<input class="employee-warning-input" type="' . e($type) . '" name="' . e($name) . '" aria-label="' . e($label) . '" value="' . e($value ?? '') . '"' . ($readonly ? ' readonly' : '') . ($type === 'number' ? ' min="0" step="1"' : '') . '>';
        return '<label class="employee-complete-field"><span>' . e($label) . '</span>' . $control . '</label>';
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

<link rel="stylesheet" href="{{ asset('assets/css/staff-warning-a4.css') }}?v={{ filemtime(public_path('assets/css/staff-warning-a4.css')) }}">
<script defer src="{{ asset('assets/js/staff-warning-a4.js') }}?v={{ filemtime(public_path('assets/js/staff-warning-a4.js')) }}"></script>
<div class="employee-complete-paper employee-warning-paper" data-warning-a4>
    <div class="employee-warning-content" data-warning-a4-content>
    <div class="employee-complete-header">
        <img class="employee-complete-logo" src="{{ $formLogo }}" alt="{{ $employee->branch?->name ?: __('index.branch') }}">
        <div class="employee-warning-heading">
            <div class="employee-warning-title">Staff Warning / Disciplinary Action Form</div>
            <h4>បែបបទព្រមាន និងវិធានការវិន័យបុគ្គលិក</h4>
            <small class="employee-complete-confidential">HR RECORD - CONFIDENTIAL / ឯកសារធនធានមនុស្ស - រក្សាការសម្ងាត់</small>
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>1. Employee Information / ព័ត៌មានបុគ្គលិក</h6>
        <div class="employee-complete-list">
            <div class="employee-complete-field"><span>Warning No. / លេខព្រមាន</span><strong data-warning-number aria-live="polite">{{ __('index.warning_number_generating') }}</strong></div>
            {!! $field('Warning Date / ថ្ងៃព្រមាន', $formatDate($warningRecord?->created_at) ?: now()->format('Y-m-d'), 'date') !!}
            {!! $field('Employee ID / លេខបុគ្គលិក', $employee->employee_code ?: $employee->username, 'text', true) !!}
            {!! $field('Employee Name / ឈ្មោះ', $employee->english_name ?: $employee->name, 'text', true) !!}
            {!! $field('Department / ផ្នែក', $employee->department?->dept_name, 'text', true) !!}
            {!! $field('Position / តួនាទី', $employee->post?->post_name, 'text', true) !!}
            {!! $field('Branch / សាខា', $employee->branch?->name, 'text', true) !!}
            {!! $field('Supervisor / អ្នកគ្រប់គ្រង', $employee->supervisor?->name) !!}
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>2. Warning Type / ប្រភេទការព្រមាន</h6>
        <div class="employee-warning-checks">
            @foreach($warningTypes as $type => $label)
                <label class="employee-warning-check">
                    <input type="radio" class="employee-warning-box" name="warning_type" value="{{ $type }}" aria-label="{{ $label }}" @checked(($warningRecord?->record_type ?: 'written_warning') === $type)>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>3. Incident / Violation Information / ព័ត៌មានអំពីករណី</h6>
        <div class="employee-complete-list">
            {!! $field('Incident Date / ថ្ងៃកើតហេតុ', $formatDate($warningRecord?->incident_date), 'date') !!}
            {!! $field('Incident Time / ម៉ោង', null, 'time') !!}
            {!! $field('Location / ទីតាំង', $employee->branch?->name) !!}
            {!! $field('Category / ប្រភេទ', $warningRecord?->severity ?: $warningRecord?->warning_level) !!}
        </div>
        <div class="mt-2">
            <strong>Description of Incident / ពិពណ៌នាអំពីហេតុការណ៍</strong>
            <textarea class="employee-warning-lines" name="incident_description" rows="2" aria-label="Description of Incident / ពិពណ៌នាអំពីហេតុការណ៍">{{ $warningRecord?->description }}</textarea>
        </div>
        <div class="mt-2">
            <strong>Company Policy / Rule Violated / គោលការណ៍ ឬបទបញ្ជាដែលបានរំលោភ</strong>
            <textarea class="employee-warning-lines" name="company_policy" rows="2" aria-label="Company Policy / គោលការណ៍ក្រុមហ៊ុន"></textarea>
        </div>
        <div class="mt-2">
            {!! $field('Previous Related Warnings / ប្រវត្តិការព្រមានពាក់ព័ន្ធ', max(0, $discipline->count() - 1), 'number') !!}
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>4. Employee Explanation / ការបំភ្លឺរបស់បុគ្គលិក</h6>
        <textarea class="employee-warning-lines tall" name="employee_explanation" rows="3" aria-label="Employee Explanation / ការបំភ្លឺរបស់បុគ្គលិក"></textarea>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>5. Corrective Action / វិធានការកែតម្រូវ</h6>
        <div class="employee-complete-list">
            {!! $field('Required Corrective Action / វិធានការកែតម្រូវ', $warningRecord?->action_taken, 'textarea') !!}
            {!! $field('Improvement Deadline / ថ្ងៃកំណត់កែលម្អ', null, 'date') !!}
            {!! $field('Follow-up Date / ថ្ងៃតាមដាន', null, 'date') !!}
            {!! $field('Responsible Supervisor / អ្នកទទួលខុសត្រូវ', $employee->supervisor?->name) !!}
        </div>
        <div class="mt-2">
            <strong>Consequence if Repeated / ផលវិបាកប្រសិនបើកើតឡើងម្តងទៀត</strong>
            <textarea class="employee-warning-lines" name="consequence_if_repeated" rows="2" aria-label="Consequence if Repeated / ផលវិបាកប្រសិនបើកើតឡើងម្តងទៀត"></textarea>
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>6. Status / ស្ថានភាព</h6>
        <div class="employee-warning-checks">
            @foreach($statuses as $status => $label)
                <label class="employee-warning-check">
                    <input type="radio" class="employee-warning-box" name="warning_status" value="{{ $status }}" aria-label="{{ $label }}" @checked(($warningRecord?->status ?: 'issued') === $status || ($warningRecord?->status === 'active' && $status === 'issued'))>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="employee-complete-section employee-warning-section">
        <h6>7. Acknowledgement &amp; Signatures / ការទទួលស្គាល់ និងហត្ថលេខា</h6>
        <strong>Employee Comments / មតិយោបល់បុគ្គលិក</strong>
        <textarea class="employee-warning-lines" name="employee_comments" rows="2" aria-label="Employee Comments / មតិយោបល់បុគ្គលិក"></textarea>
        <div class="employee-complete-signatures employee-warning-signatures mt-2">
            @foreach(['Employee / បុគ្គលិក', 'Supervisor / អ្នកគ្រប់គ្រង', 'HR / ធនធានមនុស្ស'] as $signature)
                <div class="employee-complete-signature">
                    <strong>{{ $signature }}</strong>
                    <div class="employee-warning-signature-line"></div>
                    <label class="employee-warning-signature-date">Date / កាលបរិច្ឆេទ:<input type="date" name="signature_date_{{ $loop->index }}" aria-label="{{ $signature }} - Date / កាលបរិច្ឆេទ"></label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="employee-warning-footer">
        HR Record - Confidential / ឯកសារធនធានមនុស្ស - រក្សាការសម្ងាត់
    </div>
    </div>
</div>
