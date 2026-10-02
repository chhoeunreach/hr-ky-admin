@canany(['list_attendance', 'list_monthly_attendance', 'list_attendance_log', 'attendance_setting', 'employee.profile.view'])
    @php
        $isDailyAttendanceChecklist = request()->routeIs('admin.employees.profile.index') && request()->boolean('attendance_report');
        $isAttendanceSection = $isDailyAttendanceChecklist || request()->routeIs('admin.attendances.*', 'admin.attendance.*', 'admin.attendance-monthly.index', 'admin.attendance-monthly.filter-options');
    @endphp
    <li class="nav-item {{ $isAttendanceSection ? 'active' : '' }}">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#attendance_management"
           role="button"
           aria-expanded="{{ $isAttendanceSection ? 'true' : 'false' }}"
           aria-controls="attendance_management">
            <i class="link-icon" data-feather="user-check"></i>
            <span class="link-title">{{ __('index.attendance_section') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="{{ $isAttendanceSection ? '' : 'collapse' }}" id="attendance_management">
            <ul class="nav sub-menu">

                <li class="nav-item">
                    @can('list_attendance')
                    <a href="{{route('admin.attendances.index')}}"
                       data-href="{{route('admin.attendances.index')}}"
                       class="nav-link {{ request()->routeIs('admin.attendances.*') ? 'active' : ''}}">{{ __('index.attendance') }}</a>
                    @endcan
                </li>

                <li class="nav-item">
                    @can('list_monthly_attendance')
                    <a href="{{route('admin.attendance-monthly.index')}}"
                       data-href="{{route('admin.attendance-monthly.index')}}"
                       class="nav-link {{ request()->routeIs('admin.attendance-monthly.index') ? 'active' : ''}}">{{ __('index.attendance_monthly') }}</a>
                    @endcan
                </li>

                @can('list_attendance_log')
                <li class="nav-item">
                    <a href="{{route('admin.attendance.log')}}"
                       data-href="{{route('admin.attendance.log')}}"
                       class="nav-link {{ request()->routeIs('admin.attendance.log') ? 'active' : ''}}">{{ __('index.attendance_logs') }}</a>
                </li>
                @endcan
                @can('employee.profile.view')
                <li class="nav-item">
                    <a href="{{ route('admin.employees.profile.index', ['attendance_report' => 1]) }}"
                       data-href="{{ route('admin.employees.profile.index', ['attendance_report' => 1]) }}"
                       class="nav-link {{ $isDailyAttendanceChecklist ? 'active' : '' }}">{{ __('index.attendance_confirmation_report') }}</a>
                </li>
                @endcan
                <li class="nav-item">
                    <a href="{{route('admin.attendance.export')}}"
                       data-href="{{route('admin.attendance.export')}}"
                       class="nav-link {{request()->routeIs('admin.attendance.export') ? 'active' : ''}}">{{ __('index.attendance_report') }}</a>
                </li>

            </ul>
        </div>
    </li>
@endcanany
