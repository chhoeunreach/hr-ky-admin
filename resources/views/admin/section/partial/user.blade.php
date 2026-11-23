@canany(['list_employee','d_card_print','employee.profile.view','view_employee_chat','list_logout_request'])
    @php
        $isEmployeeSection = (request()->routeIs('admin.employees.*') && !(request()->routeIs('admin.employees.profile.index') && request()->boolean('attendance_report'))) ||
            request()->routeIs('admin.employee.log') ||
            request()->routeIs('admin.live-map*') ||
            request()->routeIs('admin.d-card-print*') ||
            request()->routeIs('admin.employee-chat*') ||
            request()->routeIs('admin.telegram-employees.*') ||
            request()->routeIs('admin.logout-requests.*');
    @endphp
    <li class="nav-item {{ $isEmployeeSection ? 'active' : '' }}">
        <a data-href="#"
           class="nav-link {{ $isEmployeeSection ? '' : 'collapsed' }}"
           data-bs-toggle="collapse"
           href="#employee_management"
           role="button"
           aria-expanded="{{ $isEmployeeSection ? 'true' : 'false' }}"
           aria-controls="employee_management">
            <i class="link-icon" data-feather="users"></i>
            <span class="link-title">{{ __('index.employee_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="collapse {{ $isEmployeeSection ? 'show' : '' }}" id="employee_management">
            <ul class="nav sub-menu">
                @can('list_employee')
                    <li class="nav-item">
                        <a href="{{route('admin.employees.index')}}"
                           data-href="{{route('admin.employees.index')}}"
                           class="nav-link {{ request()->routeIs('admin.employees.index') || request()->routeIs('admin.employees.create') || request()->routeIs('admin.employees.edit') || request()->routeIs('admin.employees.show') ? 'active' : ''}}">{{ __('index.employees') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.employees.calendar') }}"
                           data-href="{{ route('admin.employees.calendar') }}"
                           class="nav-link {{ request()->routeIs('admin.employees.calendar') ? 'active' : ''}}">{{ __('index.employees_calendar') }}</a>
                    </li>
                @endcan
                @can('employee.profile.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.employees.profile.index') }}"
                           data-href="{{ route('admin.employees.profile.index') }}"
                           class="nav-link {{ request()->routeIs('admin.employees.profile.*') && !(request()->routeIs('admin.employees.profile.index') && request()->boolean('attendance_report')) ? 'active' : ''}}">{{ __('index.employee_profile') }}</a>
                    </li>
                @endcan
                @can('list_employee')
                    <li class="nav-item">
                        <a href="{{route('admin.employee.log')}}"
                           data-href="{{route('admin.employee.log')}}"
                           class="nav-link {{request()->routeIs('admin.employee.log') ? 'active' : ''}}">{{ __('index.location_logs') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.live-map')}}"
                           data-href="{{route('admin.live-map')}}"
                           class="nav-link {{request()->routeIs('admin.live-map*') ? 'active' : ''}}">{{ __('index.live_map') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('admin.telegram-employees.index')}}"
                           data-href="{{route('admin.telegram-employees.index')}}"
                           class="nav-link {{request()->routeIs('admin.telegram-employees.*') ? 'active' : ''}}">{{ __('index.telegram_employees') }}</a>
                    </li>
                @endcan
                @can('d_card_print')
                    <li class="nav-item">
                        <a href="{{route('admin.d-card-print.index')}}"
                           data-href="{{route('admin.d-card-print.index')}}"
                           class="nav-link {{request()->routeIs('admin.d-card-print*') ? 'active' : ''}}">{{ __('index.d_card_print') }}</a>
                    </li>
                @endcan
                @can('view_employee_chat')
                    <li class="nav-item">
                        <a href="{{ route('admin.employee-chat') }}"
                           data-href="{{ route('admin.employee-chat') }}"
                           class="nav-link {{request()->routeIs('admin.employee-chat*') ? 'active' : ''}}">{{ __('index.live_chat') }}</a>
                    </li>
                @endcan

                @can('list_logout_request')
                    <li class="nav-item">
                        <a href="{{route('admin.logout-requests.index')}}"
                           data-href="{{route('admin.logout-requests.index')}}"
                           class="nav-link {{request()->routeIs('admin.logout-requests.*') ? 'active' : ''}}">{{ __('index.logout_requests') }}</a>
                    </li>
                @endcan

            </ul>
        </div>
    </li>
@endcanany
