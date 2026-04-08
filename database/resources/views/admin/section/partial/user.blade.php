
@canany(['list_employee','list_logout_request'])
    <li class="nav-item  {{
                           request()->routeIs('admin.users.*') ||
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
                         request()->routeIs('admin.users.*') ||
                            request()->routeIs('admin.logout-requests.*')?'' : 'collapse'  }}"  id="employee_management">
            <ul class="nav sub-menu">
                @can('list_employee')
                    <li class="nav-item">
                        <a href="{{route('admin.users.index')}}"
                           data-href="{{route('admin.users.index')}}"
                           class="nav-link {{request()->routeIs('admin.users.*') ? 'active' : ''}}">{{ __('index.employees') }}</a>
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



