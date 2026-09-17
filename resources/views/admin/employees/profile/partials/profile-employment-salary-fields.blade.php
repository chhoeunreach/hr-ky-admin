<div class="employee-360-section">
    <h6>{{ __('index.employment_information') }}</h6>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">{{ __('index.employment_status') }}</label>
            <select class="form-control" name="employment_status">
                @foreach(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(old('employment_status', $profile->employment_status) === $status)>{{ \Illuminate\Support\Facades\Lang::has('index.' . $status) ? __('index.' . $status) : ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        @foreach([
            'probation_period' => __('index.probation_period'),
            'probation_end_date' => __('index.probation_end_date'),
            'contract_start_date' => __('index.contract_start_date'),
            'contract_end_date' => __('index.contract_end_date'),
            'last_working_date' => __('index.last_working_date'),
            'weekly_day_off' => __('index.weekly_day_off'),
        ] as $field => $label)
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ $label }}</label>
                <input class="form-control" name="{{ $field }}" type="{{ str_contains($field, 'date') ? 'date' : 'text' }}" value="{{ old($field, optional($profile->{$field})->format('Y-m-d') ?: $profile->{$field}) }}">
            </div>
        @endforeach
        <div class="col-md-12 mb-3">
            <label class="form-label">{{ __('index.employment_end_reason') }}</label>
            <textarea class="form-control" name="employment_end_reason" rows="2">{{ old('employment_end_reason', $profile->employment_end_reason) }}</textarea>
        </div>
    </div>
</div>

@can('employee.salary.manage')
    <div class="employee-360-section">
        <h6>{{ __('index.salary_and_benefits') }}</h6>
        <div class="row">
            @foreach([
                'starting_salary' => __('index.starting_salary'),
                'current_base_salary' => __('index.current_base_salary'),
                'allowances' => __('index.allowances'),
                'commission' => __('index.commission'),
                'attendance_bonus' => __('index.attendance_bonus'),
                'punctuality_bonus' => __('index.punctuality_bonus'),
                'overtime' => __('index.overtime'),
                'payment_method' => __('index.payment_method'),
                'salary_payment_date' => __('index.salary_payment_date'),
            ] as $field => $label)
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <input class="form-control" name="{{ $field }}" value="{{ old($field, $profile->{$field}) }}">
                </div>
            @endforeach
            <div class="col-md-12 mb-3">
                <label class="form-label">{{ __('index.other_benefits') }}</label>
                <textarea class="form-control" name="other_benefits" rows="3">{{ old('other_benefits', $profile->other_benefits) }}</textarea>
            </div>
        </div>
    </div>
@endcan
