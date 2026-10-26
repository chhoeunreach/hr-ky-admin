@if(in_array($salaryRecord->approval_status, ['draft', 'pending'], true))
    <div class="d-flex flex-wrap gap-1">
        @can('salary_increment')
            @if($salaryRecord->approval_status === 'pending')
            @foreach(['approved' => 'approve', 'rejected' => 'reject'] as $status => $label)
                <form method="post" action="{{ route('admin.employees.profile.salary.status', [$employee->id, $salaryRecord->id]) }}">
                    @csrf @method('PATCH')
                    @if(request('employee_modal') === '1')<input type="hidden" name="employee_modal" value="1">@endif
                    <input type="hidden" name="approval_status" value="{{ $status }}">
                    <button class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-danger' }}" type="submit"
                            @disabled($status === 'approved' && (!$salaryRecord->effective_date || $salaryRecord->effective_date->isFuture()))>
                        {{ __('index.'.$label) }}
                    </button>
                </form>
            @endforeach
            @endif
        @endcan
        @can('employee.salary.history.manage')
            @foreach(($salaryRecord->approval_status === 'draft' ? ['pending' => 'submit', 'cancelled' => 'cancel'] : ['cancelled' => 'cancel']) as $status => $label)
                <form method="post" action="{{ route('admin.employees.profile.salary.status', [$employee->id, $salaryRecord->id]) }}">
                    @csrf @method('PATCH')
                    @if(request('employee_modal') === '1')<input type="hidden" name="employee_modal" value="1">@endif
                    <input type="hidden" name="approval_status" value="{{ $status }}">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">{{ __('index.'.$label) }}</button>
                </form>
            @endforeach
        @endcan
    </div>
    @if($salaryRecord->effective_date?->isFuture())
        <small class="text-muted d-block mt-1">{{ __('index.salary_manage_due') }} {{ $salaryRecord->effective_date->format('Y-m-d') }}</small>
    @endif
@endif
<div class="d-flex flex-wrap gap-1 mt-1">
    @can('employee.salary.history.edit')
        <a class="btn btn-outline-primary btn-sm" data-open-employee-form
           data-employee-form-title="{{ __('index.edit_salary_history') }} · {{ $employee->name }}"
           href="{{ route('admin.employees.profile.salary.edit', [$employee->id, $salaryRecord->id]) }}">{{ __('index.edit') }}</a>
    @endcan
    @can('employee.salary.history.delete')
        <form method="post" action="{{ route('admin.employees.profile.salary.destroy', [$employee->id, $salaryRecord->id]) }}"
              data-confirm="{{ __('index.salary_history_delete_confirm') }}" onsubmit="return window.confirm(this.dataset.confirm)">
            @csrf
            @method('DELETE')
            @if(request('employee_modal') === '1')
                <input type="hidden" name="employee_modal" value="1">
            @endif
            <button class="btn btn-outline-danger btn-sm" type="submit">{{ __('index.delete') }}</button>
        </form>
    @endcan
</div>
