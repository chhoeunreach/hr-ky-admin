@php
    $warningRecord = $discipline->first();
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
    $field = function ($label, $value, $type = 'text', $opts = []) {
        $name = $opts['name'] ?? \Illuminate\Support\Str::slug(explode(' / ', $label)[0], '_');
        $rows = $opts['rows'] ?? 1;
        $attrs = $opts['attrs'] ?? '';
        $readonly = !empty($opts['readonly']) ? ' readonly' : '';
        if ($type === 'textarea') {
            $control = '<textarea class="employee-warning-input" rows="' . (int) $rows . '" name="' . e($name) . '" aria-label="' . e($label) . '"' . $attrs . '>' . e($value ?? '') . '</textarea>';
        } else {
            $extra = $type === 'number' ? ' min="0" step="1"' : '';
            $control = '<input class="employee-warning-input" type="' . e($type) . '" name="' . e($name) . '" aria-label="' . e($label) . '" value="' . e($value ?? '') . '"' . $readonly . $extra . $attrs . '>';
        }
        return '<label class="employee-complete-field"><span>' . e($label) . '</span>' . $control . '</label>';
    };
    $companyLogo = \App\Helpers\AppHelper::getCompanyLogo()
        ? asset(\App\Models\Company::UPLOAD_PATH . \App\Helpers\AppHelper::getCompanyLogo())
        : null;
    $branchLogo = $employee->branch?->image
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->image)
        : null;
    $organizationLogo = $branchLogo ?: $companyLogo;
    $organizationName = $employee->company?->name ?: ($employee->branch?->name ?: config('app.name'));
    $printLogo = $organizationLogo ?: asset('assets/images/logo-mini.png');
    $positionLabel = $employee->post?->post_name ?: optional($employee->role)->name;
@endphp
<div class="employee-complete-document">
    <div class="employee-complete-toolbar">
        <button type="button" class="btn btn-primary btn-sm" onclick="printForm('staff-warning-form')">
            <i class="link-icon" data-feather="printer"></i> Print Staff Warning
        </button>
        <button type="button" class="btn btn-light btn-sm" onclick="window.location.reload()">
            <i class="link-icon" data-feather="rotate-ccw"></i> Reset
        </button>
    </div>

    <div class="employee-warning-paper" data-warning-a4 data-warning-number="">
        <div data-warning-a4-content>
        <div class="employee-complete-header">
            <img src="{{ $printLogo }}" alt="{{ $organizationName }}">
            <div class="employee-warning-title-block">
                <div class="employee-warning-company">{{ $organizationName }}</div>
                <div class="employee-warning-title">Staff Warning / Disciplinary Action Form</div>
                <div class="employee-warning-subtitle">ទម្រង់ព្រមាន/វិធានវិន័យបុគ្គលិក</div>
            </div>
            <div class="employee-warning-meta">
                <div>Printed {{ now()->format('Y-m-d') }} · {{ $employee->employee_code ?: '-' }}</div>
            </div>
        </div>

        <section class="employee-complete-section">
            <h6>Staff &amp; Incident / បុគ្គលិក និងហេតុការណ៍</h6>
            <div class="employee-complete-grid warning-grid-4">
                {!! $field('Name / ឈ្មោះ', $employee->name, 'text', ['name' => 'employee_name', 'readonly' => true]) !!}
                {!! $field('ID / លេខកូដ', $employee->employee_code, 'text', ['name' => 'employee_id', 'readonly' => true]) !!}
                {!! $field('Dept. / នាយកដ្ឋាន', $employee->department?->dept_name, 'text', ['name' => 'department', 'readonly' => true]) !!}
                {!! $field('Position / តួនាទី', $positionLabel, 'text', ['name' => 'position', 'readonly' => true]) !!}
                {!! $field('Incident Date / ថ្ងៃកើតហេតុ', $formatDate($warningRecord?->warning_date), 'date', ['name' => 'warning_date']) !!}
                {!! $field('Time / ម៉ោង', '', 'text', ['name' => 'time_of_incident']) !!}
                {!! $field('Location / ទីតាំង', '', 'text', ['name' => 'location']) !!}
                {!! $field('Type / ប្រភេទ', $warningRecord?->warning_type, 'text', ['name' => 'warning_type']) !!}
                {!! $field('Level / កម្រិត', '', 'text', ['name' => 'warning_level']) !!}
                {!! $field('Warning No. / លើកទី', $discipline->count() ?: 1, 'number', ['name' => 'number_of_warning']) !!}
            </div>
        </section>

        <section class="employee-complete-section">
            <h6>Details, Explanation &amp; Action / ព័ត៌មាន ការពន្យល់ និងសកម្មភាព</h6>
            <div class="employee-complete-grid warning-grid-2">
                <div class="full-span">{!! $field('Incident Description / ការពិពណ៌នាហេតុការណ៍', $warningRecord?->warning_reason, 'textarea', ['name' => 'incident_description', 'rows' => 1]) !!}</div>
                <div class="full-span">{!! $field('Policy/Rule Violated / គោលការណ៍ដែលរំលោភ', '', 'textarea', ['name' => 'policy_rule_violated', 'rows' => 1]) !!}</div>
                <div class="full-span">{!! $field('Employee Explanation / ការពន្យល់បុគ្គលិក', '', 'textarea', ['name' => 'employee_explanation', 'rows' => 1]) !!}</div>
                <div class="full-span">{!! $field('Corrective Action Required / សកម្មភាពកែតម្រូវ', '', 'textarea', ['name' => 'required_corrective_action', 'rows' => 1]) !!}</div>
                {!! $field('Improvement Deadline / កាលកំណត់កែតម្រូវ', '', 'date', ['name' => 'deadline_for_improvement']) !!}
                {!! $field('Follow-up Date / ថ្ងៃតាមដាន', '', 'date', ['name' => 'follow_up_date']) !!}
                <div class="full-span">{!! $field('Consequence if No Improvement / ផលវិបាកបើមិនកែតម្រូវ', '', 'text', ['name' => 'consequence_if_no_improvement']) !!}</div>
            </div>
        </section>

        <section class="employee-complete-section employee-warning-status-section">
            <h6>Status &amp; Acknowledgement / ស្ថានភាព និងការទទួលស្គាល់</h6>
            <div class="employee-warning-status">
                <input type="hidden" name="warning_status" value="">
                <label class="employee-warning-status-item"><input type="checkbox" name="status_verbal"> Verbal / ពាក្យសម្ដី</label>
                <label class="employee-warning-status-item"><input type="checkbox" name="status_first"> 1st Written / លិខិតលើកទី១</label>
                <label class="employee-warning-status-item"><input type="checkbox" name="status_second"> 2nd Written / លិខិតលើកទី២</label>
                <label class="employee-warning-status-item"><input type="checkbox" name="status_final"> Final / ចុងក្រោយ</label>
            </div>
            <p class="employee-warning-ack-text">I acknowledge receipt and understanding of this warning, agree to comply with corrective action, and understand failure to improve may lead to further disciplinary action up to termination. / ខ្ញុំទទួលស្គាល់ថាបានទទួល និងយល់ការព្រមាននេះ សូមអនុវត្តសកម្មភាពកែតម្រូវ និងយល់ថាបើមិនកែលម្អអាចនាំទៅរកវិធានវិន័យបន្ថែមរហូតដល់បញ្ចប់ការងារ។</p>
            <div class="employee-complete-grid warning-grid-4 warning-sign-grid">
                {!! $field('Employee Signature / ហត្ថលេខាបុគ្គលិក', '', 'text', ['name' => 'employee_signature']) !!}
                {!! $field('Date / ថ្ងៃ', '', 'date', ['name' => 'date']) !!}
                {!! $field('Supervisor/Manager / អ្នកគ្រប់គ្រង', '', 'text', ['name' => 'supervisor_manager_signature']) !!}
                {!! $field('Date / ថ្ងៃ', '', 'date', ['name' => 'date_1']) !!}
            </div>
        </section>

        <div class="employee-complete-footer">
            <p>Official confidential staff record — keep in personnel file. / ឯកសារផ្លូវការសម្ងាត់ — រក្សាទុកក្នុងឯកសារបុគ្គលិក។</p>
        </div>
        </div>
    </div>
</div>
