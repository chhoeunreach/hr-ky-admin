@if(in_array($salaryRecord->approval_status, ['draft', 'pending'], true))
    <div class="d-flex flex-wrap gap-1">
        @can('salary_increment')
            @foreach(['approved' => 'approve', 'rejected' => 'reject'] as $status => $label)
                <form method="post" action="{{ route('admin.employees.profile.salary.status', [$employee->id, $salaryRecord->id]) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="approval_status" value="{{ $status }}">
                    <button class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-danger' }}" type="submit"
                            @disabled($status === 'approved' && (!$salaryRecord->effective_date || $salaryRecord->effective_date->isFuture()))>
                        {{ __('index.'.$label) }}
                    </button>
                </form>
            @endforeach
        @endcan
        @can('employee.salary.history.manage')
            @foreach(($salaryRecord->approval_status === 'draft' ? ['pending' => 'submit', 'cancelled' => 'cancel'] : ['cancelled' => 'cancel']) as $status => $label)
                <form method="post" action="{{ route('admin.employees.profile.salary.status', [$employee->id, $salaryRecord->id]) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="approval_status" value="{{ $status }}">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">{{ __('index.'.$label) }}</button>
                </form>
            @endforeach
        @endcan
    </div>
    @if($salaryRecord->effective_date?->isFuture())
        <small class="text-muted d-block mt-1">{{ __('index.salary_manage_due') }} {{ $salaryRecord->effective_date->format('Y-m-d') }}</small>
    @endif
@else
    <span class="text-muted">—</span>
@endif
