@can('generate_payroll')
<div class="modal fade" id="monthlyPayrollGenerateModal" tabindex="-1" aria-labelledby="monthlyPayrollGenerateTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <form method="post" action="{{ route('admin.monthly-payroll.generate') }}" data-payroll-generate-form data-restore-branches="{{ old('branch_selection') === 'explicit' ? '1' : '0' }}">
            @csrf
            <input type="hidden" name="return_view" value="{{ request('view') === 'yearly' ? 'yearly' : 'monthly' }}">
            <input type="hidden" name="branch_selection" value="explicit">
            <div class="modal-header"><h5 class="modal-title" id="monthlyPayrollGenerateTitle">{{ __('index.monthly_payroll_generate_preview') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button></div>
            <div class="modal-body">
                <p class="text-muted small">{{ __('index.monthly_payroll_generate_help') }}</p>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6"><label for="generatePayrollMonth" class="form-label">{{ __('index.month') }}</label><input id="generatePayrollMonth" type="month" name="month" class="form-control" value="{{ old('month', $filters['month']) }}" required></div>
                    <fieldset class="col-12">
                        <legend class="visually-hidden">{{ __('index.branch') }}</legend>
                        @php
                            $selectedBranches = old('branch_selection') === 'explicit' ? old('branch_ids', []) : old('branch_ids', $filters['branch_id'] ? [$filters['branch_id']] : []);
                            $selectedBranches = array_map('strval', $selectedBranches);
                        @endphp
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <span class="form-label mb-0">{{ __('index.branch') }}</span>
                            <div class="d-flex gap-2"><button type="button" class="btn btn-outline-primary btn-sm" data-payroll-branches-all>{{ __('index.all_branches') }}</button><button type="button" class="btn btn-outline-secondary btn-sm" data-payroll-branches-clear>{{ __('index.clear') }}</button></div>
                        </div>
                        <div class="monthly-payroll-branch-list" aria-describedby="payrollBranchHelp">
                            @foreach($branches as $branch)<label class="monthly-payroll-branch-option"><input type="checkbox" class="form-check-input" name="branch_ids[]" value="{{ $branch->id }}" @checked(in_array((string) $branch->id, $selectedBranches, true))><span>{{ $branch->name }}</span></label>@endforeach
                        </div>
                        <small id="payrollBranchHelp" class="text-muted d-block mt-2">{{ __('index.monthly_payroll_all_branches_help') }}</small>
                    </fieldset>
                </div>
                <div class="monthly-payroll-generation-options">
                    @foreach(['attendance'=>'use_attendance','include_tds'=>'include_tds','include_ssf'=>'include_ssf','include_pf'=>'include_pf','include_tada'=>'include_tada','include_advance_salary'=>'include_advance_salary'] as $key=>$label)
                        <input type="hidden" name="{{ $key }}" value="0">
                        <label class="form-check"><input type="checkbox" class="form-check-input" name="{{ $key }}" value="1" @checked(old($key, in_array($key, ['attendance','include_tds','include_ssf','include_pf']) ? 1 : 0))><span class="form-check-label">{{ __('index.'.$label) }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button><button type="submit" class="btn btn-primary">{{ __('index.monthly_payroll_generate_preview') }}</button></div>
        </form>
    </div></div>
</div>
@endcan
