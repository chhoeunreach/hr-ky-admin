<div class="employee-complete-toolbar mb-3">
    <a class="btn btn-outline-primary btn-sm"
       href="{{ route('admin.employees.profile.index', ['attendance_report' => 1, 'department_ids' => $employee->department_id ? [$employee->department_id] : [], 'branch_id' => $employee->branch_id]) }}">
        <i class="link-icon" data-feather="file-text"></i> {{ __('index.attendance_confirmation_report') }}
    </a>
</div>
