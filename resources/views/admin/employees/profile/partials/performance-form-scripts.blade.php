@once
@php
    $performanceFormConfig = [
        'reopenType' => old('performance_record_type'), 'reopenId' => old('performance_record_id'),
        'values' => collect(old())->only(['title', 'description', 'kpi_target', 'weight', 'status', 'start_date', 'end_date', 'name', 'target_value', 'actual_value', 'unit', 'achievement_percentage', 'score', 'manager_comment'])->all(),
        'create' => ['responsibility' => __('index.add_responsibility'), 'kpi' => __('index.add_kpi')],
        'edit' => ['responsibility' => __('index.edit_responsibility'), 'kpi' => __('index.edit_employee_kpi')],
        'update' => __('index.update'), 'deleteConfirm' => __('index.performance_delete_confirm'),
    ];
@endphp
<script type="application/json" id="employeePerformanceFormConfig">@json($performanceFormConfig)</script>
<script src="{{ asset('assets/js/employee-performance-forms.js') }}?v={{ filemtime(public_path('assets/js/employee-performance-forms.js')) }}" defer></script>
@endonce
