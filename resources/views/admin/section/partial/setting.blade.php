@php use App\Helpers\AppHelper; @endphp
@if(AppHelper::checkSuperAdmin() || auth()->user()?->canAny([
    'role_permission',
    'general_setting',
    'app_setting',
    'feature_control',
    'fiscal_year',
    'payment_currency',
	    'notification',
	    'list_telegram_group',
	    'theme_setting',
	    'attendance_setting'
	]))
    <li class="nav-item  {{
                   request()->routeIs('admin.maintenance.*') ||
                      request()->routeIs('admin.roles.*') ||
                      request()->routeIs('admin.general-settings.*') ||
                      request()->routeIs('admin.app-settings.*') ||
                      request()->routeIs('admin.telegram-groups.*') ||
                      request()->routeIs('admin.notifications.*')||
                      request()->routeIs('admin.payment-currency.*')||
	                      request()->routeIs('admin.fiscal_year.*')||
	                      request()->routeIs('admin.theme-color-setting.*')||
	                      request()->routeIs('admin.attendance-monthly.controls*')||
	                      request()->routeIs('admin.feature.index')
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
        <div class="{{ request()->routeIs('admin.maintenance.*') ||
                      request()->routeIs('admin.roles.*') ||
                      request()->routeIs('admin.general-settings.*') ||
                      request()->routeIs('admin.app-settings.*') ||
                      request()->routeIs('admin.telegram-groups.*') ||
                      request()->routeIs('admin.notifications.*')||
                      request()->routeIs('admin.payment-currency.*')||

	                      request()->routeIs('admin.fiscal_year.*')||
	                      request()->routeIs('admin.theme-color-setting.*')||
	                      request()->routeIs('admin.attendance-monthly.controls*')||
	                      request()->routeIs('admin.feature.index')

                       ? '' : 'collapse'  }} " id="setting">

            <ul class="nav sub-menu">
                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item">
                        <a href="{{ route('admin.maintenance.diagnostics') }}"
                           class="nav-link {{ request()->routeIs('admin.maintenance.diagnostics') ? 'active' : '' }}">
                            {{ __('diagnostics.navigation') }}
                        </a>
                    </li>
                @endif
                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item">
                        <a
                            href="{{route('admin.roles.index')}}"
                            data-href="{{route('admin.roles.index')}}"
                            class="nav-link {{request()->routeIs('admin.roles.*') ? 'active' : ''}}">{{ __('index.roles_permissions') }}</a>
                    </li>
                @endif

	                @if(AppHelper::checkSuperAdmin())
	                    <li class="nav-item">
	                        <a
	                            href="{{route('admin.general-settings.index')}}"
	                            data-href="{{route('admin.general-settings.index')}}"
	                            class="nav-link {{request()->routeIs('admin.general-settings.*') ? 'active' : ''}}">{{ __('index.general_settings') }}</a>
	                    </li>
	                @endif

	                @can('attendance_setting')
	                    <li class="nav-item">
	                        <a
	                            href="{{route('admin.attendance-monthly.controls')}}"
	                            data-href="{{route('admin.attendance-monthly.controls')}}"
	                            class="nav-link {{request()->routeIs('admin.attendance-monthly.controls*') ? 'active' : ''}}">{{ __('index.monthly_attendance_controls') }}</a>
	                    </li>
	                @endcan

	                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item">
                        <a
                            href="{{route('admin.app-settings.index')}}"
                            data-href="{{route('admin.app-settings.index')}}"
                            class="nav-link {{request()->routeIs('admin.app-settings.*') ? 'active' : ''}}">{{ __('index.app_settings') }}</a>
                    </li>
                @endif

                @if(AppHelper::checkSuperAdmin() || auth()->user()?->can('list_telegram_group'))
                    <li class="nav-item">
                        <a
                            href="{{route('admin.telegram-groups.index')}}"
                            data-href="{{route('admin.telegram-groups.index')}}"
                            class="nav-link {{request()->routeIs('admin.telegram-groups.*') ? 'active' : ''}}">{{ __('index.telegram_groups') }}</a>
                    </li>
                @endif

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
                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item {{request()->routeIs('admin.feature.index')  ? 'active' : '' }}">
                        <a
                            href="{{route('admin.feature.index')}}"
                            data-href="{{route('admin.feature.index')}}"
                            class="nav-link {{request()->routeIs('admin.feature.index') ? 'active' : ''}}"> {{ __('index.feature_control') }}</a>
                    </li>
                @endif

                @can('fiscal_year')
                    <li class="nav-item {{ request()->routeIs('admin.fiscal_year.*')  ? 'active' : '' }}">
                        <a
                            href="{{route('admin.fiscal_year.index')}}"
                            data-href="{{route('admin.fiscal_year.index')}}"
                            class="nav-link {{request()->routeIs('admin.fiscal_year.*') ? 'active' : ''}}"> {{ __('index.fiscal_year') }}</a>
                    </li>
                @endcan
                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item {{ request()->routeIs('admin.theme-color-setting.*')  ? 'active' : '' }}">
                        <a
                            href="{{route('admin.theme-color-setting.index')}}"
                            data-href="{{route('admin.theme-color-setting.index')}}"
                            class="nav-link {{request()->routeIs('admin.theme-color-setting.*') ? 'active' : ''}}"> {{ __('index.theme_color') }}</a>
                    </li>
                @endif


            </ul>
        </div>
    </li>
@endif
