@canany([
    'list_router',
    'list_nfc',
    'list_qr',
])
    <li class="nav-item  {{
                   request()->routeIs('admin.routers.*') ||
                   request()->routeIs('admin.qr.*')||
                   request()->routeIs('admin.nfc.*')

                ? 'active' : ''
            }}"
    >
        <a class="nav-link" data-bs-toggle="collapse"
           href="#attendance_method"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="tool"></i>
            <span class="link-title"> {{ __('index.attendance_methods') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{
                      request()->routeIs('admin.routers.*') ||
                      request()->routeIs('admin.qr.*') ||
                      request()->routeIs('admin.nfc.*')

                       ? '' : 'collapse'  }} " id="attendance_method">

            <ul class="nav sub-menu">

                @can('list_router')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.routers.index')}}"
                            data-href="{{route('admin.routers.index')}}"
                            class="nav-link {{request()->routeIs('admin.routers.*') ? 'active' : ''}}">{{ __('index.routers') }}
                        </a>
                    </li>
                @endcan

                @can('list_nfc')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.nfc.index')}}"
                            data-href="{{route('admin.nfc.index')}}"
                            class="nav-link {{request()->routeIs('admin.nfc.*') ? 'active' : ''}}">{{ __('index.nfc') }}</a>
                    </li>
                @endcan

                @can('list_qr')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.qr.index')}}"
                            data-href="{{route('admin.qr.index')}}"
                            class="nav-link {{request()->routeIs('admin.qr.*') ? 'active' : ''}}">{{ __('index.qr') }}</a>
                    </li>

                @endcan
            </ul>
        </div>
    </li>
@endcanany


@canany([
    'role_permission',
    'general_setting',
    'app_setting',
    'feature_control',
    'fiscal_year',
    'payment_currency',
    'notification'
])
    <li class="nav-item  {{
                   request()->routeIs('admin.roles.*') ||
                   request()->routeIs('admin.notifications.*')||
                   request()->routeIs('admin.general-settings.*')||
                   request()->routeIs('admin.fiscal_year.*')||
                    request()->routeIs('admin.payment-currency.*')
                ? 'active' : ''
            }}"
    >
        <a class="nav-link" data-bs-toggle="collapse"
           href="#setting"
           data-href="#"
           role="button" aria-expanded="false" aria-controls="settings">
            <i class="link-icon" data-feather="settings"></i>
            <span class="link-title"> {{ __('index.settings') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.roles.*') ||
                      request()->routeIs('admin.general-settings.*') ||
                      request()->routeIs('admin.app-settings.*') ||
                      request()->routeIs('admin.notifications.*')||
                      request()->routeIs('admin.payment-currency.*')||
                      request()->routeIs('admin.fiscal_year.*')||
                      request()->routeIs('admin.feature.index')

                       ? '' : 'collapse'  }} " id="setting">

            <ul class="nav sub-menu">
                @can('role_permission')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.roles.index')}}"
                            data-href="{{route('admin.roles.index')}}"
                            class="nav-link {{request()->routeIs('admin.roles.*') ? 'active' : ''}}">{{ __('index.roles_permissions') }}</a>
                    </li>
                @endcan

                @can('general_setting')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.general-settings.index')}}"
                            data-href="{{route('admin.general-settings.index')}}"
                            class="nav-link {{request()->routeIs('admin.general-settings.*') ? 'active' : ''}}">{{ __('index.general_settings') }}</a>
                    </li>
                @endcan

                @can('app_setting')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.app-settings.index')}}"
                            data-href="{{route('admin.app-settings.index')}}"
                            class="nav-link {{request()->routeIs('admin.app-settings.*') ? 'active' : ''}}">{{ __('index.app_settings') }}</a>
                    </li>
                @endcan

                @can('notification')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.notifications.index')}}"
                            data-href="{{route('admin.notifications.index')}}"
                            class="nav-link {{request()->routeIs('admin.notifications.*') ? 'active' : ''}}">{{ __('index.notifications') }}</a>
                    </li>
                @endcan

                @can('payment_currency')
                    <li class="nav-item {{request()->routeIs('admin.payment-currency.*')  ? 'active' : '' }}">
                        <a
                            href="{{route('admin.payment-currency.index')}}"
                            data-href="{{route('admin.payment-currency.index')}}"
                            class="nav-link {{request()->routeIs('admin.payment-currency.*') ? 'active' : ''}}"> {{ __('index.payment_currency') }}</a>
                    </li>

                @endcan
                @can('feature_control')
                    <li class="nav-item {{request()->routeIs('admin.feature.index')  ? 'active' : '' }}">
                        <a
                            href="{{route('admin.feature.index')}}"
                            data-href="{{route('admin.feature.index')}}"
                            class="nav-link {{request()->routeIs('admin.feature.index') ? 'active' : ''}}"> {{ __('index.feature_control') }}</a>
                    </li>
                @endcan

                @can('fiscal_year')
                    <li class="nav-item {{ request()->routeIs('admin.fiscal_year.*')  ? 'active' : '' }}">
                        <a
                            href="{{route('admin.fiscal_year.index')}}"
                            data-href="{{route('admin.fiscal_year.index')}}"
                            class="nav-link {{request()->routeIs('admin.fiscal_year.*') ? 'active' : ''}}"> {{ __('index.fiscal_year') }}</a>
                    </li>
                @endcan


            </ul>
        </div>
    </li>
@endcanany
