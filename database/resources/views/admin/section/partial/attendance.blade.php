@canany(['list_attendance'])
    <li class="nav-item  {{ request()->routeIs('admin.attendances.*') || request()->routeIs('admin.attendance.*')  ? 'active' : '' }}   ">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#attendance_management"
           role="button"
           aria-expanded="false"
           aria-controls="company">
            <i class="link-icon" data-feather="users"></i>
            <span class="link-title">{{ __('index.attendance_section') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="{{ request()->routeIs('admin.attendances.*') || request()->routeIs('admin.attendance.*')  ? '' : 'collapse'  }}"  id="attendance_management">
            <ul class="nav sub-menu">

                <li class="nav-item">
                    <a href="{{route('admin.attendances.index')}}"
                       data-href="{{route('admin.attendances.index')}}"
                       class="nav-link {{ request()->routeIs('admin.attendances.*') ? 'active' : ''}}">{{ __('index.attendance') }}</a>
                </li>

                <li class="nav-item">
                    <a href="{{route('admin.attendance.log')}}"
                       data-href="{{route('admin.attendance.log')}}"
                       class="nav-link {{ request()->routeIs('admin.attendance.log') ? 'active' : ''}}">Attendance Logs</a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.attendance.export')}}"
                       data-href="{{route('admin.attendance.export')}}"
                       class="nav-link {{request()->routeIs('admin.attendance.export') ? 'active' : ''}}">Attendance Report</a>
                </li>

            </ul>
        </div>
    </li>
@endcanany



