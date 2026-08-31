@canany(['list_employee','d_card_print','employee.profile.view','employee.performance.create','view_employee_chat','list_logout_request'])
    <li class="nav-item  {{
                           request()->routeIs('admin.employees.*') ||
                           request()->routeIs('admin.employee.log') ||
                           request()->routeIs('admin.staff-evaluations.*') ||
                           request()->routeIs('admin.live-map*') ||
                           request()->routeIs('admin.d-card-print*') ||
                           request()->routeIs('admin.employee-chat*') ||
                           request()->routeIs('admin.telegram-employees.*') ||
                           request()->routeIs('admin.logout-requests.*')
                        ? 'active' : ''
                        }}   ">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#employee_management"
           role="button"
           aria-expanded="false"
           aria-controls="company">
            <i class="link-icon" data-feather="users"></i>
            <span class="link-title">{{ __('index.employee_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="{{
                         request()->routeIs('admin.employees.*') ||
                         request()->routeIs('admin.employee.log') ||
                         request()->routeIs('admin.staff-evaluations.*') ||
                         request()->routeIs('admin.live-map*') ||
                         request()->routeIs('admin.d-card-print*') ||
                         request()->routeIs('admin.employee-chat*') ||
                         request()->routeIs('admin.telegram-employees.*') ||
                         request()->routeIs('admin.logout-requests.*') ? '' : 'collapse'  }}"  id="employee_management">
            <ul class="nav sub-menu">
                @can('list_employee')
                    <li class="nav-item">
                        <a href="{{route('admin.employees.index')}}"
                           data-href="{{route('admin.employees.index')}}"
                           class="nav-link {{ request()->routeIs('admin.employees.index') || request()->routeIs('admin.employees.create') || request()->routeIs('admin.employees.edit') || request()->routeIs('admin.employees.show') ? 'active' : ''}}">{{ __('index.employees') }}</a>
                    </li>
                @endcan
                @can('employee.profile.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.employees.profile.index') }}"
                           data-href="{{ route('admin.employees.profile.index') }}"
                           class="nav-link {{ request()->routeIs('admin.employees.profile.*') ? 'active' : ''}}">Employee Profile</a>
                    </li>
                @endcan
                @can('employee.performance.create')
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.dashboard') }}"
                           data-href="{{ route('admin.staff-evaluations.dashboard') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.dashboard') ? 'active' : ''}}">Evaluation Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.job-descriptions.index') }}"
                           data-href="{{ route('admin.staff-evaluations.job-descriptions.index') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.job-descriptions.*') ? 'active' : ''}}">Job Descriptions</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.templates.index') }}"
                           data-href="{{ route('admin.staff-evaluations.templates.index') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.templates.*') ? 'active' : ''}}">Evaluation Templates</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.ai-create') }}"
                           data-href="{{ route('admin.staff-evaluations.ai-create') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.ai-create') ? 'active' : ''}}">AI Evaluation Generator</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.history') }}"
                           data-href="{{ route('admin.staff-evaluations.history') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.history') ? 'active' : ''}}">Evaluation History</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.reports') }}"
                           data-href="{{ route('admin.staff-evaluations.reports') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.reports') ? 'active' : ''}}">Performance Reports</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.staff-evaluations.settings') }}"
                           data-href="{{ route('admin.staff-evaluations.settings') }}"
                           class="nav-link {{ request()->routeIs('admin.staff-evaluations.settings') ? 'active' : ''}}">Evaluation Settings</a>
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
                           class="nav-link {{request()->routeIs('admin.telegram-employees.*') ? 'active' : ''}}">Telegram Employees</a>
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
