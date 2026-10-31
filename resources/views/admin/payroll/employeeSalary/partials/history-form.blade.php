<div data-salary-history-content>
    <h6 class="mb-2">{{ __($deleting ? 'index.salary_review_delete' : 'index.salary_review_edit') }}</h6>
    <p class="fw-semibold">{{ $employeeDetail->name }} · {{ $history->date_from }}</p>
    <p class="text-muted">{{ __($deleting ? 'index.salary_review_delete_help' : 'index.salary_review_edit_help') }}</p>
    <form method="post" data-salary-history-form @if(!$deleting) data-salary-review-edit @endif action="{{ route($deleting ? 'admin.employee-salaries.salary-revise-history.destroy' : 'admin.employee-salaries.salary-revise-history.update', [$employeeDetail->id, $history->id]) }}">
        @csrf
        @method($deleting ? 'DELETE' : 'PUT')
        @if(!$deleting)
            <div class="row g-3 mb-4">
                @foreach(['salary_revised_on' => 'salary_revised_on', 'date_from' => 'applicable_from', 'date_to' => 'date_to'] as $field => $label)
                    <div class="col-md-4">
                        <label for="review_{{ $field }}" class="form-label">{{ __('index.' . $label) }}</label>
                        <input class="form-control" type="date" id="review_{{ $field }}" name="{{ $field }}" value="{{ $history->{$field} }}" @if($field !== 'date_to') required max="{{ now()->toDateString() }}" @endif>
                    </div>
                @endforeach
                @foreach(['base_salary' => 'old_salary', 'revised_salary' => 'new_salary'] as $field => $label)
                    <div class="col-md-6">
                        <label for="review_{{ $field }}" class="form-label">{{ __('index.' . $label) }} · {{ __('index.annual_salary') }} ({{ $currency }})</label>
                        <input class="form-control" type="number" id="review_{{ $field }}" name="{{ $field }}" min="0" max="99999999.99" step="0.01" required value="{{ $history->{$field} }}">
                    </div>
                @endforeach
                <div class="col-md-6"><span class="text-muted small">{{ __('index.increment_value') }} ({{ $currency }})</span><output class="d-block fw-semibold" data-review-increment>{{ number_format((float) $history->increment_amount, 2) }}</output></div>
                <div class="col-md-6"><span class="text-muted small">{{ __('index.increment_by') }}</span><output class="d-block fw-semibold" data-review-percent>{{ number_format((float) $history->increment_percent, 2) }}%</output></div>
                <div class="col-md-6">
                    <label for="review_fiscal_year_id" class="form-label">{{ __('index.fiscal_year') }}</label>
                    <select class="form-select" id="review_fiscal_year_id" name="fiscal_year_id">
                        <option value="">{{ __('index.select_fiscal_year') }}</option>
                        @foreach($fiscalYears as $year)<option value="{{ $year->id }}" @selected((string) $history->fiscal_year_id === (string) $year->id)>{{ $year->year }}</option>@endforeach
                    </select>
                </div>
            </div>
            <fieldset class="mb-4">
                <legend class="h6 border-bottom pb-2">{{ __('index.salary_review_previous_components') }}</legend>
                <div class="row g-3">
                    @foreach(['base_monthly_salary' => 'salary_review_monthly_basic', 'base_weekly_salary' => 'salary_review_weekly_basic', 'base_monthly_allowance' => 'salary_review_monthly_allowance', 'base_weekly_allowance' => 'salary_review_weekly_allowance', 'hour_rate' => 'hourly_rate', 'monthly_hours' => 'salary_review_monthly_hours', 'weekly_hours' => 'salary_review_weekly_hours'] as $field => $label)
                        <div class="col-md-6">
                            <label for="review_{{ $field }}" class="form-label">{{ __('index.' . $label) }}@if(!in_array($field, ['monthly_hours', 'weekly_hours'], true)) ({{ $currency }})@endif</label>
                            <input class="form-control" type="number" id="review_{{ $field }}" name="{{ $field }}" min="0" max="99999999.99" step="0.01" value="{{ $history->{$field} }}">
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <label for="salaryReviewRemark" class="form-label">{{ __('index.remark') }}</label>
            <textarea class="form-control" id="salaryReviewRemark" name="remark" rows="5" maxlength="2000">{{ $history->remark }}</textarea>
        @endif
        <div class="text-danger my-3" role="alert" data-history-form-error></div>
        <div class="d-flex gap-2 mt-3">
            <button class="btn {{ $deleting ? 'btn-danger' : 'btn-primary' }}" type="submit">{{ __($deleting ? 'index.delete' : 'index.update') }}</button>
            <button class="btn btn-outline-secondary" type="button" data-history-back>{{ __('index.cancel') }}</button>
        </div>
    </form>
</div>
