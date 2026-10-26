<div data-salary-history-content>
    <h6 class="mb-2">{{ __($deleting ? 'index.salary_review_delete' : 'index.salary_review_edit') }}</h6>
    <p class="fw-semibold">{{ $employeeDetail->name }} · {{ $history->date_from }}</p>
    <p class="text-muted">{{ __($deleting ? 'index.salary_review_delete_help' : 'index.salary_review_edit_help') }}</p>
    <form method="post" data-salary-history-form action="{{ route($deleting ? 'admin.employee-salaries.salary-revise-history.destroy' : 'admin.employee-salaries.salary-revise-history.update', [$employeeDetail->id, $history->id]) }}">
        @csrf
        @method($deleting ? 'DELETE' : 'PUT')
        @if(!$deleting)
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
