@can('employee.kpi.manage')
<td>
    @php
        $group = $kind === 'responsibility' ? 'responsibilities' : 'kpis';
        $parameters = ['employee' => $employee->id, $kind => $record->id];
        $fields = $kind === 'responsibility'
            ? ['title', 'description', 'kpi_target', 'weight', 'status', 'start_date', 'end_date']
            : ['name', 'description', 'target_value', 'actual_value', 'unit', 'weight', 'achievement_percentage', 'score', 'manager_comment'];
        $recordData = $record->only($fields);
        if ($kind === 'responsibility') {
            foreach (['start_date', 'end_date'] as $dateField) $recordData[$dateField] = $record->{$dateField}?->format('Y-m-d');
        }
    @endphp
    <div class="d-flex align-items-center gap-1">
        <button type="button" class="btn btn-outline-primary btn-xs" data-bs-toggle="modal"
                data-bs-target="{{ $kind === 'responsibility' ? '#addResponsibilityModal' : '#addKpiModal' }}"
                data-performance-edit data-record-id="{{ $record->id }}"
                data-edit-url="{{ route('admin.employees.profile.'.$group.'.update', $parameters) }}"
                data-edit-record='@json($recordData)'>
            {{ __('index.edit') }}
        </button>
        <form method="post" action="{{ route('admin.employees.profile.'.$group.'.destroy', $parameters) }}" data-performance-delete>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-xs">{{ __('index.delete') }}</button>
        </form>
    </div>
</td>
@endcan
