@php
    $overviewStatus = strtolower((string) ($overview['employment']['status'] ?? ($employee->is_active ? 'active' : 'inactive')));
    $statusStyles = [
        'active' => ['bg-success', 'Active / សកម្ម'],
        'probation' => ['bg-info', 'Probation / សាកល្បង'],
        'on_leave' => ['bg-primary', 'On Leave / ឈប់សម្រាក'],
        'suspended' => ['bg-warning text-dark', 'Suspended / ផ្អាក'],
        'resigned' => ['bg-secondary', 'Resigned / លាឈប់'],
        'terminated' => ['bg-danger', 'Terminated / បញ្ចប់ការងារ'],
        'retired' => ['bg-dark', 'Retired / ចូលនិវត្តន៍'],
        'inactive' => ['bg-secondary', 'Inactive / អសកម្ម'],
    ];
    [$statusClass, $statusLabel] = $statusStyles[$overviewStatus] ?? ['bg-secondary', ucfirst($overviewStatus) ?: 'N/A'];
    $navUrl = route('admin.employees.profile.show', $employee->id);
@endphp

<div class="employee-360-hero employee-360-hero-v2">
    <img class="rounded-circle"
         src="{{ $employee->avatar_url }}"
         alt="{{ $employee->name }}"
         style="object-fit: cover;"
         onerror="this.onerror=null; this.src='{{ asset('assets/images/img.png') }}';">

    <div class="employee-360-hero-main">
        <div class="employee-360-title">
            <h4 class="mb-0">{{ $employee->english_name ?: $employee->name }}</h4>
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <p class="text-muted employee-360-hero-ids mb-1">
            @if($employee->employee_code)
                <span>{{ $employee->employee_code }}</span> ·
            @endif
            <span>{{ $overview['employment']['position'] ?: 'N/A' }}</span> ·
            <span>{{ $overview['employment']['department'] ?: 'N/A' }}</span> ·
            <span>{{ $overview['employment']['branch'] ?: 'N/A' }}</span>
        </p>
        <div class="employee-360-hero-meta">
            <span><i class="link-icon" data-feather="calendar"></i> Joined {{ $overview['employment']['joining_date'] ? \Illuminate\Support\Carbon::parse($overview['employment']['joining_date'])->format('d M Y') : 'N/A' }}</span>
            <span><i class="link-icon" data-feather="user-check"></i> {{ $overview['employment']['manager'] ?: 'N/A' }}</span>
            <span><i class="link-icon" data-feather="phone"></i> {{ $employee->phone ?: 'N/A' }}</span>
            <span><i class="link-icon" data-feather="mail"></i> {{ $employee->email ?: 'N/A' }}</span>
            <span><i class="link-icon" data-feather="briefcase"></i> {{ $overview['employment']['employment_type'] ?: 'N/A' }}</span>
        </div>
    </div>

    <div class="employee-360-hero-actions">
        <div class="employee-360-hero-nav">
            @if($previousEmployee)
                <a class="btn btn-outline-secondary btn-xs" href="{{ route('admin.employees.profile.show', $previousEmployee->id) }}" title="Previous Employee / បុគ្គលិកមុន">
                    <i class="link-icon" data-feather="chevron-left"></i>
                </a>
            @endif
            @if($nextEmployee)
                <a class="btn btn-outline-secondary btn-xs" href="{{ route('admin.employees.profile.show', $nextEmployee->id) }}" title="Next Employee / បុគ្គលិកបន្ទាប់">
                    <i class="link-icon" data-feather="chevron-right"></i>
                </a>
            @endif
        </div>

        <div class="btn-group employee-360-hero-buttons">
            @can('employee.profile.edit')
                <a class="btn btn-outline-primary btn-xs" href="{{ route('admin.employees.edit', $employee->id) }}">
                    <i class="link-icon" data-feather="edit"></i> Edit
                </a>
            @endcan
            @can('employee.complete_form.print')
                <button type="button" class="btn btn-outline-primary btn-xs" onclick="printEmployeeCompleteForm()">
                    <i class="link-icon" data-feather="printer"></i> Print Profile
                </button>
            @endcan
            <button type="button" class="btn btn-outline-secondary btn-xs dropdown-toggle" data-bs-toggle="dropdown">
                More Actions <i class="link-icon" data-feather="more-horizontal"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @can('employee.employment.manage')
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=employment"><i class="link-icon" data-feather="git-branch"></i> Change Department / Transfer</a></li>
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=employment"><i class="link-icon" data-feather="user-cog"></i> Change Position / Assign Manager</a></li>
                @endcan
                @can('employee.salary.manage')
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=salary"><i class="link-icon" data-feather="dollar-sign"></i> Salary Adjustment</a></li>
                @endcan
                @can('employee.profile.edit')
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=personal"><i class="link-icon" data-feather="toggle-left"></i> Employment Status</a></li>
                @endcan
            </ul>
        </div>
    </div>
</div>