@php
    $detailData = [
        'name' => $value->dept_name,
        'heads' => $value->departmentHeads->pluck('name')->values()->all(),
        'branches' => $value->branches->pluck('name')->values()->all(),
        'employees' => $value->employees_count,
        'employee_status' => ($filterParameters['employee_status'] ?? 'all') === 'all' ? __('index.all') : (($filterParameters['employee_status'] ?? '') === 'active' ? __('index.active') : __('index.inactive')),
        'entries' => $value->departmentEntries->map(fn ($entry) => [
            'branch' => $entry->branch?->name, 'head' => $entry->departmentHead?->name,
            'address' => $entry->address, 'phone' => $entry->phone,
            'employees' => $entry->assignedEmployees->count(),
            'status' => $entry->is_active ? __('index.active') : __('index.inactive'),
        ])->values()->all(),
        'defaults_url' => route('admin.departments.performance-defaults', ['id' => $value->id, 'employee_modal' => '1']),
        'edit_url' => route('admin.departments.edit', $value->id),
    ];
@endphp
<tr data-department-id="{{ $value->id }}" data-department-details='@json($detailData)'>
    <td data-department-column="id" class="text-muted">{{ ($departments->firstItem() ?? 0) + $key }}</td>
    <td data-department-column="name" class="department-name-cell">
        <button type="button" class="department-name-link show-department-details">{{ ucfirst($value->dept_name) }}</button>
        <small class="d-block text-muted mt-1">{{ trans_choice('index.department_branch_count', $value->branches->count(), ['count' => $value->branches->count()]) }}</small>
    </td>
    <td data-department-column="head">
        <div class="d-flex flex-column gap-1">
            @forelse($value->departmentHeads->take(2) as $head)
                <span class="department-person-name">{{ $head->name }}</span>
            @empty
                <span class="text-muted">{{ __('index.not_available') }}</span>
            @endforelse
            @if($value->departmentHeads->count() > 2)
                <button type="button" class="department-more-link show-department-details">{{ __('index.department_more_count', ['count' => $value->departmentHeads->count() - 2]) }}</button>
            @endif
        </div>
    </td>
    <td data-department-column="employees" class="text-center">
        <button type="button" class="department-employee-count show-department-employees" title="{{ __('index.employee_list_title') }}" data-url="{{ route('admin.departments.employees', ['id' => $value->id, 'employee_status' => $filterParameters['employee_status'] ?? 'all']) }}">
            <i data-feather="users" aria-hidden="true"></i>{{ number_format($value->employees_count) }}
        </button>
    </td>
    <td data-department-column="address"><button type="button" class="department-address-preview show-department-details">{{ \Illuminate\Support\Str::limit($value->address ?: __('index.not_available'), 70) }}</button></td>
    <td data-department-column="branch" class="text-center">
        <div class="d-flex flex-column align-items-center gap-1">
            @forelse($value->branches->take(2) as $branchItem)
                <span class="department-branch-badge">{{ $branchItem->name }}</span>
            @empty
                <span class="text-muted">{{ __('index.not_available') }}</span>
            @endforelse
            @if($value->branches->count() > 2)
                <button type="button" class="department-more-link show-department-details">{{ __('index.department_more_count', ['count' => $value->branches->count() - 2]) }}</button>
            @endif
        </div>
    </td>
    <td data-department-column="status" class="text-center">
        <div class="d-flex align-items-center justify-content-center gap-2">
            <span class="badge bg-light {{ $value->is_active ? 'text-success' : 'text-secondary' }}">{{ $value->is_active ? __('index.active') : __('index.inactive') }}</span>
            @can('edit_department')
                <label class="switch mb-0"><input class="toggleStatus" href="{{ route('admin.departments.toggle-status', $value->id) }}" type="checkbox" {{ $value->is_active ? 'checked' : '' }} aria-label="{{ __('index.status') }}"><span class="slider round"></span></label>
            @endcan
        </div>
    </td>
    <td>
        <div class="department-row-actions">
            <button type="button" class="department-icon-button show-department-details" title="{{ __('index.department_view_details') }}" aria-label="{{ __('index.department_view_details') }}"><i data-feather="eye"></i></button>
            @can('edit_department')
                <a class="department-icon-button" href="{{ route('admin.departments.edit', $value->id) }}" title="{{ __('index.edit') }}" aria-label="{{ __('index.edit') }}"><i data-feather="edit-2"></i></a>
            @endcan
            @can('delete_department')
                <button type="button" class="department-icon-button text-danger deleteBranch" data-href="{{ route('admin.departments.delete', $value->id) }}" title="{{ __('index.department_delete_button') }}" aria-label="{{ __('index.department_delete_button') }}"><i data-feather="trash-2"></i></button>
            @endcan
        </div>
    </td>
</tr>
