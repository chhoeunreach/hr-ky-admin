@can('generate_payroll')
<div class="modal fade" id="monthlyPayrollGenerateModal" tabindex="-1" aria-labelledby="monthlyPayrollGenerateTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" action="{{ route('admin.monthly-payroll.generate') }}" data-payroll-generate-form>
            @csrf
            <input type="hidden" name="return_view" value="{{ request('view') === 'yearly' ? 'yearly' : 'monthly' }}">
            <div class="modal-header"><h5 class="modal-title" id="monthlyPayrollGenerateTitle">{{ __('index.monthly_payroll_generate_save') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button></div>
            <div class="modal-body">
                <p class="text-muted small">{{ __('index.monthly_payroll_generate_help') }}</p>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6"><label for="generatePayrollMonth" class="form-label">{{ __('index.month') }}</label><input id="generatePayrollMonth" type="month" name="month" class="form-control" value="{{ old('month', $filters['month']) }}" required></div>
                    <div class="col-sm-6"><label for="generatePayrollBranch" class="form-label">{{ __('index.branch') }}</label><select id="generatePayrollBranch" name="branch_id" class="form-select" required><option value="">{{ __('index.select_branch') }}</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected((string) old('branch_id', $filters['branch_id']) === (string) $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
                </div>
                <div class="monthly-payroll-generation-options">
                    @foreach(['attendance'=>'use_attendance','include_tds'=>'include_tds','include_ssf'=>'include_ssf','include_pf'=>'include_pf','include_tada'=>'include_tada','include_advance_salary'=>'include_advance_salary'] as $key=>$label)
                        <label class="form-check"><input type="checkbox" class="form-check-input" name="{{ $key }}" value="1" @checked(old($key, in_array($key, ['attendance','include_tds','include_ssf','include_pf']) ? 1 : 0))><span class="form-check-label">{{ __('index.'.$label) }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button><button type="submit" class="btn btn-primary">{{ __('index.monthly_payroll_generate_save') }}</button></div>
        </form>
    </div></div>
</div>
@endcan
