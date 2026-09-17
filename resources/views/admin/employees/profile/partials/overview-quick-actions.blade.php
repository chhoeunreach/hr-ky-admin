@php
    $navUrl = route('admin.employees.profile.show', $employee->id);
@endphp

<div class="emp-overview-card emp-quick-actions-card">
    <div class="emp-card-head">
        <h6><i class="link-icon" data-feather="zap"></i> {{ __('index.quick_actions') }}</h6>
    </div>
    <div class="emp-card-body">
        <div class="emp-quick-actions">
            @if($canViewEmployment)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('employment')"><i class="link-icon" data-feather="git-branch"></i> {{ __('index.transfer_department') }}</button>
            @endif
            @if($canViewSalary)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('salary')"><i class="link-icon" data-feather="dollar-sign"></i> {{ __('index.salary_adjustment') }}</button>
            @endif
            @if($canViewPerformance)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('evaluation')"><i class="link-icon" data-feather="bar-chart-2"></i> {{ __('index.evaluate_performance') }}</button>
            @endif
            @if($canViewGoal)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('goals')"><i class="link-icon" data-feather="target"></i> {{ __('index.manage_goals') }}</button>
            @endif
            @if($canViewDiscipline)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('discipline')"><i class="link-icon" data-feather="alert-octagon"></i> {{ __('index.staff_warning') }}</button>
                @can('employee.warning_overview.print')
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printOverviewForm()"><i class="link-icon" data-feather="printer"></i> {{ __('index.print_overview_form') }}</button>
                @endcan
            @endif
            @if($canViewDocument)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('personal')"><i class="link-icon" data-feather="archive"></i> {{ __('index.view_documents') }}</button>
            @endif
            @if($canViewAudit)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('history')"><i class="link-icon" data-feather="clock"></i> {{ __('index.view_history') }}</button>
            @endif
            @if($canViewReward)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('rewards')"><i class="link-icon" data-feather="award"></i> {{ __('index.rewards') }}</button>
            @endif
        </div>
    </div>
</div>