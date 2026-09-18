@php
    $overviewStatus = strtolower((string) ($overview['employment']['status'] ?? ($employee->is_active ? 'active' : 'inactive')));
    $statusStyles = [
        'active' => ['bg-success', __('index.active')],
        'probation' => ['bg-info', __('index.probation')],
        'on_leave' => ['bg-primary', __('index.on_leave')],
        'suspended' => ['bg-warning text-dark', __('index.suspended')],
        'resigned' => ['bg-secondary', __('index.resigned')],
        'terminated' => ['bg-danger', __('index.terminated')],
        'retired' => ['bg-dark', __('index.retired')],
        'inactive' => ['bg-secondary', __('index.inactive')],
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
            <span><i class="link-icon" data-feather="calendar"></i> {{ __('index.joined') }} {{ $overview['employment']['joining_date'] ? \Illuminate\Support\Carbon::parse($overview['employment']['joining_date'])->format('d M Y') : 'N/A' }}</span>
            <span><i class="link-icon" data-feather="user-check"></i> {{ $overview['employment']['manager'] ?: 'N/A' }}</span>
            <span><i class="link-icon" data-feather="phone"></i> {{ $employee->phone ?: 'N/A' }}</span>
            <span><i class="link-icon" data-feather="mail"></i> {{ $employee->email ?: 'N/A' }}</span>
            <span><i class="link-icon" data-feather="briefcase"></i> {{ $overview['employment']['employment_type'] ?: 'N/A' }}</span>
        </div>
    </div>

    <div class="employee-360-hero-actions">
        <div class="employee-360-hero-nav">
            @if($previousEmployee)
                <a class="btn btn-outline-secondary btn-xs" href="{{ route('admin.employees.profile.show', $previousEmployee->id) }}" title="{{ __('index.previous_employee') }}">
                    <i class="link-icon" data-feather="chevron-left"></i>
                </a>
            @endif
            @if($nextEmployee)
                <a class="btn btn-outline-secondary btn-xs" href="{{ route('admin.employees.profile.show', $nextEmployee->id) }}" title="{{ __('index.next_employee') }}">
                    <i class="link-icon" data-feather="chevron-right"></i>
                </a>
            @endif
        </div>

        <div class="btn-group employee-360-hero-buttons">
            @can('employee.profile.edit')
                <a class="btn btn-outline-primary btn-xs" href="{{ route('admin.employees.edit', $employee->id) }}">
                    <i class="link-icon" data-feather="edit"></i> {{ __('index.edit') }}
                </a>
            @endcan
            @can('employee.complete_form.print')
                <button type="button" class="btn btn-outline-primary btn-xs" onclick="printEmployeeCompleteForm()">
                    <i class="link-icon" data-feather="printer"></i> {{ __('index.print_profile') }}
                </button>
            @endcan
            <button type="button" class="btn btn-outline-secondary btn-xs dropdown-toggle" data-bs-toggle="dropdown">
                {{ __('index.more_actions') }} <i class="link-icon" data-feather="more-horizontal"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @can('employee.employment.manage')
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=employment"><i class="link-icon" data-feather="git-branch"></i> {{ __('index.transfer_department') }}</a></li>
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=employment"><i class="link-icon" data-feather="settings"></i> {{ __('index.change_position_manager') }}</a></li>
                @endcan
                @can('employee.salary.manage')
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=salary"><i class="link-icon" data-feather="dollar-sign"></i> {{ __('index.salary_adjustment') }}</a></li>
                @endcan
                @can('employee.profile.edit')
                    <li><a class="dropdown-item" href="{{ $navUrl }}?tab=personal"><i class="link-icon" data-feather="toggle-left"></i> {{ __('index.employment_status') }}</a></li>
                @endcan
            </ul>
        </div>
    </div>
</div>
