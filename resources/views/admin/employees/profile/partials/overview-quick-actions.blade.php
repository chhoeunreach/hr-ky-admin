@php
    $navUrl = route('admin.employees.profile.show', $employee->id);
@endphp

<div class="emp-overview-card emp-quick-actions-card">
    <div class="emp-card-head">
        <h6><i class="link-icon" data-feather="zap"></i> Quick Actions</h6>
    </div>
    <div class="emp-card-body">
        <div class="emp-quick-actions">
            @if($canViewEmployment)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('employment')"><i class="link-icon" data-feather="git-branch"></i> Transfer / Department</button>
            @endif
            @if($canViewSalary)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('salary')"><i class="link-icon" data-feather="dollar-sign"></i> Salary Adjustment</button>
            @endif
            @if($canViewPerformance)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('evaluation')"><i class="link-icon" data-feather="bar-chart-2"></i> Evaluate Performance</button>
            @endif
            @if($canViewGoal)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('goals')"><i class="link-icon" data-feather="target"></i> Manage Goals</button>
            @endif
            @if($canViewDiscipline)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('discipline')"><i class="link-icon" data-feather="alert-octagon"></i> Staff Warning</button>
                @can('employee.warning_overview.print')
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printOverviewForm()"><i class="link-icon" data-feather="printer"></i> Print Overview Form</button>
                @endcan
            @endif
            @if($canViewDocument)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('personal')"><i class="link-icon" data-feather="archive"></i> View Documents</button>
            @endif
            @if($canViewAudit)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('history')"><i class="link-icon" data-feather="clock"></i> View History</button>
            @endif
            @if($canViewReward)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="employeeGotoTab('rewards')"><i class="link-icon" data-feather="award"></i> Rewards</button>
            @endif
        </div>
    </div>
</div>