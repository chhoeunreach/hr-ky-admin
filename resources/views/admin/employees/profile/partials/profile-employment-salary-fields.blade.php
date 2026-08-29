<div class="employee-360-section">
    <h6>Employment Information</h6>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <label class="form-label">Employment Status</label>
            <select class="form-control" name="employment_status">
                @foreach(['active', 'probation', 'suspended', 'resigned', 'terminated', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(old('employment_status', $profile->employment_status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        @foreach([
            'probation_period' => 'Probation Period',
            'probation_end_date' => 'Probation End Date',
            'contract_start_date' => 'Contract Start Date',
            'contract_end_date' => 'Contract End Date',
            'weekly_day_off' => 'Weekly Day Off',
        ] as $field => $label)
            <div class="col-lg-3 col-md-6 mb-3">
                <label class="form-label">{{ $label }}</label>
                <input class="form-control" name="{{ $field }}" type="{{ str_contains($field, 'date') ? 'date' : 'text' }}" value="{{ old($field, optional($profile->{$field})->format('Y-m-d') ?: $profile->{$field}) }}">
            </div>
        @endforeach
    </div>
</div>

@can('employee.salary.manage')
    <div class="employee-360-section">
        <h6>Salary & Benefits</h6>
        <div class="row">
            @foreach([
                'starting_salary' => 'Starting Salary',
                'current_base_salary' => 'Current Base Salary',
                'allowances' => 'Allowances',
                'commission' => 'Commission',
                'attendance_bonus' => 'Attendance Bonus',
                'punctuality_bonus' => 'Punctuality Bonus',
                'overtime' => 'Overtime',
                'payment_method' => 'Payment Method',
                'salary_payment_date' => 'Salary Payment Date',
            ] as $field => $label)
                <div class="col-lg-3 col-md-6 mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <input class="form-control" name="{{ $field }}" value="{{ old($field, $profile->{$field}) }}">
                </div>
            @endforeach
            <div class="col-md-12 mb-3">
                <label class="form-label">Other Benefits</label>
                <textarea class="form-control" name="other_benefits" rows="3">{{ old('other_benefits', $profile->other_benefits) }}</textarea>
            </div>
        </div>
    </div>
@endcan
