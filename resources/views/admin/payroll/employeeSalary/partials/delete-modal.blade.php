@can('delete_salary')
    <div class="modal fade" id="employeeSalaryDeleteModal" tabindex="-1" aria-labelledby="employeeSalaryDeleteTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" data-salary-delete-form>
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeSalaryDeleteTitle">{{ __('index.delete') }} · {{ __('index.employee_salary') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-semibold" data-salary-delete-employee></p>
                    <p class="mb-0">{{ __('index.salary_delete_help') }}</p>
                    <div class="text-danger mt-2" role="alert" data-salary-delete-error></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('index.delete') }}</button>
                </div>
            </form>
        </div>
    </div>
@endcan
