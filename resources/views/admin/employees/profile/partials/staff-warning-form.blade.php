@php
    $warningRecord = $discipline->first();
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '';
    $field = function ($label, $value, $type = 'text', $opts = []) use ($canManageWarningForm) {
        $name = $opts['name'] ?? \Illuminate\Support\Str::slug(explode(' / ', $label)[0], '_');
        $rows = $opts['rows'] ?? 1;
        $attrs = $opts['attrs'] ?? '';
        $readonly = !empty($opts['readonly']) ? ' readonly' : '';
        $disabled = $canManageWarningForm ? '' : ' disabled';
        if ($type === 'textarea') {
            $control = '<textarea class="employee-warning-input" rows="' . (int) $rows . '" name="' . e($name) . '" aria-label="' . e($label) . '"' . $disabled . $attrs . '>' . e($value ?? '') . '</textarea>';
        } else {
            $extra = $type === 'number' ? ' min="0" step="1"' : '';
            $control = '<input class="employee-warning-input" type="' . e($type) . '" name="' . e($name) . '" aria-label="' . e($label) . '" value="' . e($value ?? '') . '"' . $readonly . $disabled . $extra . $attrs . '>';
        }
        return '<label class="employee-complete-field"><span>' . e($label) . '</span>' . $control . '</label>';
    };
    $companyLogo = \App\Helpers\AppHelper::getCompanyLogo()
        ? asset(\App\Models\Company::UPLOAD_PATH . \App\Helpers\AppHelper::getCompanyLogo())
        : null;
    $branchLogo = $employee->branch?->logo
        ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo)
        : null;
    $organizationLogo = $branchLogo ?: $companyLogo;
    $organizationName = $employee->company?->name ?: ($employee->branch?->name ?: config('app.name'));
    $printLogo = $organizationLogo ?: asset('assets/images/logo-mini.png');
    $positionLabel = $employee->post?->post_name ?: optional($employee->role)->name;
@endphp
<div class="employee-warning-workspace employee-request-workspace mb-3">
    <header class="employee-request-workspace-header">
        <div>
            <span class="employee-request-eyebrow">{{ __('index.staff_warning_form') }}</span>
            <h3>{{ __('index.staff_warning_workspace_title') }}</h3>
            <p>{{ __('index.staff_warning_workspace_help') }}</p>
        </div>
    </header>
    @include('admin.employees.profile.partials.request-workflow-steps', [
        'currentStep' => 1,
        'stepTranslationPrefix' => 'staff_warning_step',
        'workflowLabel' => __('index.staff_warning_workflow'),
    ])
    <div class="employee-warning-form-actions" data-saved-form-toolbar="warning"></div>
</div>

<div class="employee-complete-toolbar employee-request-preview-heading">
    <strong>{{ __('index.staff_warning_preview') }}</strong>
    <span>{{ __('index.staff_warning_preview_help') }}</span>
    @if($canPrintWarningForm)
        <button type="button" class="btn btn-primary btn-sm" onclick="printStaffWarningForm()" data-print-busy-label="{{ __('index.staff_warning_saving_print') }}" data-save-linked-print disabled>
            <i class="link-icon" data-feather="printer"></i> {{ __('index.print') }}
        </button>
    @endif
</div>
<div class="employee-warning-preview" role="region" aria-label="{{ __('index.staff_warning_preview') }}" tabindex="0">
<div class="employee-complete-document">

    <div class="employee-warning-paper" data-warning-a4>
        <div data-warning-a4-content>
        <div class="employee-complete-header">
            <img src="{{ $printLogo }}" alt="{{ $organizationName }}">
            <div class="employee-warning-title-block">
                <div class="employee-warning-company">{{ $organizationName }}</div>
                <div class="employee-warning-title">{{ __('index.staff_warning_document_title') }}</div>
                <div class="employee-warning-subtitle">{{ __('index.staff_warning_document_subtitle') }}</div>
            </div>
            <div class="employee-warning-meta">
                <div>{{ __('index.warning_number') }} <strong data-warning-number>WRN-______</strong></div>
                <div>{{ __('index.staff_warning_confidential') }}</div>
            </div>
        </div>

        <section class="employee-complete-section">
            <h6>1. {{ __('index.staff_warning_employee_information') }}</h6>
            <div class="employee-complete-grid warning-grid-4">
                {!! $field(__('index.staff_warning_employee_name'), $employee->english_name ?: $employee->name, 'text', ['name' => 'employee_name', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_employee_id'), $employee->employee_code ?: $employee->username, 'text', ['name' => 'employee_id', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_department'), $employee->department?->dept_name, 'text', ['name' => 'department', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_job_title'), $positionLabel, 'text', ['name' => 'position', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_branch'), $employee->branch?->name, 'text', ['name' => 'branch', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_supervisor'), $employee->supervisor?->name, 'text', ['name' => 'supervisor', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_issued_by'), '', 'text', ['name' => 'issued_by']) !!}
                {!! $field(__('index.staff_warning_issue_date'), now()->format('Y-m-d'), 'date', ['name' => 'issue_date']) !!}
            </div>
        </section>

        <section class="employee-complete-section">
            <h6>2. {{ __('index.staff_warning_incident_section') }}</h6>
            <div class="employee-complete-grid warning-grid-2">
                {!! $field(__('index.staff_warning_incident_date'), $formatDate($warningRecord?->warning_date), 'date', ['name' => 'warning_date']) !!}
                {!! $field(__('index.staff_warning_incident_time'), '', 'time', ['name' => 'time_of_incident']) !!}
                {!! $field(__('index.staff_warning_location'), '', 'text', ['name' => 'location']) !!}
                <label class="employee-complete-field"><span>{{ __('index.staff_warning_category') }}</span><select class="employee-warning-input" name="warning_type" aria-label="{{ __('index.staff_warning_category') }}" @disabled(!$canManageWarningForm)><option value="">{{ __('index.staff_warning_select_category') }}</option>@foreach(['attendance' => 'attendance', 'conduct' => 'conduct', 'performance' => 'performance', 'policy_violation' => 'policy', 'safety' => 'safety', 'other' => 'other'] as $category => $translation)<option value="{{ $category }}" @selected(str_replace(' ', '_', strtolower((string) $warningRecord?->warning_type)) === $category)>{{ __('index.staff_warning_category_' . $translation) }}</option>@endforeach</select></label>
                <div class="employee-warning-severity full-span" role="group" aria-label="{{ __('index.staff_warning_level') }}">
                    <span>{{ __('index.staff_warning_level') }}</span>
                    <label><input type="radio" name="warning_level" value="verbal" @disabled(!$canManageWarningForm)> {{ __('index.staff_warning_level_verbal') }}</label>
                    <label><input type="radio" name="warning_level" value="first_written" @disabled(!$canManageWarningForm)> {{ __('index.staff_warning_level_first') }}</label>
                    <label><input type="radio" name="warning_level" value="second_written" @disabled(!$canManageWarningForm)> {{ __('index.staff_warning_level_second') }}</label>
                    <label><input type="radio" name="warning_level" value="final" @disabled(!$canManageWarningForm)> {{ __('index.staff_warning_level_final') }}</label>
                </div>
                <div class="full-span">{!! $field(__('index.staff_warning_incident_description'), $warningRecord?->warning_reason, 'textarea', ['name' => 'incident_description', 'rows' => 2]) !!}</div>
                <div class="full-span">{!! $field(__('index.staff_warning_policy_reference'), '', 'textarea', ['name' => 'policy_rule_violated', 'rows' => 1]) !!}</div>
                <div class="full-span">{!! $field(__('index.staff_warning_witnesses'), '', 'text', ['name' => 'witnesses_supporting_records']) !!}</div>
                {!! $field(__('index.staff_warning_previous_warnings'), $discipline->count() > 1 ? __('index.staff_warning_previous_count', ['count' => $discipline->count() - 1]) : __('index.staff_warning_none_recorded'), 'text', ['name' => 'previous_warnings', 'readonly' => true]) !!}
                {!! $field(__('index.staff_warning_related_reference'), '', 'text', ['name' => 'previous_warning_reference']) !!}
            </div>
        </section>

        <section class="employee-complete-section employee-warning-status-section">
            <h6>3. {{ __('index.staff_warning_response_section') }}</h6>
            <div class="employee-complete-grid warning-grid-2">
                <div class="full-span">{!! $field(__('index.staff_warning_employee_response'), '', 'textarea', ['name' => 'employee_explanation', 'rows' => 2]) !!}</div>
                <div class="full-span">{!! $field(__('index.staff_warning_corrective_action'), '', 'textarea', ['name' => 'required_corrective_action', 'rows' => 2]) !!}</div>
                {!! $field(__('index.staff_warning_improvement_deadline'), '', 'date', ['name' => 'deadline_for_improvement']) !!}
                {!! $field(__('index.staff_warning_follow_up_date'), '', 'date', ['name' => 'follow_up_date']) !!}
                <div class="full-span">{!! $field(__('index.staff_warning_next_steps'), '', 'text', ['name' => 'consequence_if_no_improvement']) !!}</div>
            </div>
            <h6 class="employee-warning-ack-heading">4. {{ __('index.staff_warning_ack_section') }}</h6>
            <p class="employee-warning-ack-text">{{ __('index.staff_warning_acknowledgement') }}</p>
            <div class="employee-complete-grid warning-sign-grid">
                {!! $field(__('index.staff_warning_employee_signature'), '', 'text', ['name' => 'employee_signature']) !!}
                {!! $field(__('index.staff_warning_date'), '', 'date', ['name' => 'employee_signature_date']) !!}
                {!! $field(__('index.staff_warning_manager_signature'), '', 'text', ['name' => 'supervisor_manager_signature']) !!}
                {!! $field(__('index.staff_warning_date'), '', 'date', ['name' => 'manager_signature_date']) !!}
                {!! $field(__('index.staff_warning_hr_signature'), '', 'text', ['name' => 'hr_signature']) !!}
                {!! $field(__('index.staff_warning_date'), '', 'date', ['name' => 'hr_signature_date']) !!}
            </div>
        </section>

        <div class="employee-complete-footer">
            <p>{{ __('index.staff_warning_footer') }}</p>
        </div>
        </div>
    </div>
</div>
</div>
