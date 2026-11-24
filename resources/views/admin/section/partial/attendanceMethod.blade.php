@php use App\Helpers\AppHelper; @endphp
@canany([
    'list_router',
    'list_nfc',
    'list_qr',
    'list_device',
    'attendance_setting',
    'list_employee'
])
    @php
        $isAttendanceMethodSection = request()->routeIs('admin.routers.*') ||
            request()->routeIs('admin.qr.*') ||
            request()->routeIs('admin.face-kiosks.*') ||
            request()->routeIs('admin.biometric-devices.*') ||
            request()->routeIs('admin.attendance-settings.*') ||
            request()->routeIs('admin.nfc.*');
    @endphp
    <li class="nav-item {{ $isAttendanceMethodSection ? 'active' : '' }}">
        <a class="nav-link {{ $isAttendanceMethodSection ? '' : 'collapsed' }}" data-bs-toggle="collapse"
           href="#attendance_method"
           data-href="#"
           role="button" aria-expanded="{{ $isAttendanceMethodSection ? 'true' : 'false' }}" aria-controls="attendance_method">
            <i class="link-icon" data-feather="cpu"></i>
            <span class="link-title"> {{ __('index.attendance_methods') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isAttendanceMethodSection ? 'show' : '' }}" id="attendance_method">
            <ul class="nav sub-menu">
                @can('list_device')
                    <li class="nav-item">
                        <a href="{{route('admin.biometric-devices.index')}}"
                           data-href="{{route('admin.biometric-devices.index')}}"
                           class="nav-link {{request()->routeIs('admin.biometric-devices.*') ? 'active' : ''}}">{{ __('index.biometric_device') }}</a>
                    </li>
                @endcan
                @can('list_router')
                    <li class="nav-item">
                        <a href="{{route('admin.routers.index')}}"
                           data-href="{{route('admin.routers.index')}}"
                           class="nav-link {{request()->routeIs('admin.routers.*') ? 'active' : ''}}">{{ __('index.routers') }}</a>
                    </li>
                @endcan
                @can('list_nfc')
                    <li class="nav-item">
                        <a href="{{route('admin.nfc.index')}}"
                           data-href="{{route('admin.nfc.index')}}"
                           class="nav-link {{request()->routeIs('admin.nfc.*') ? 'active' : ''}}">{{ __('index.nfc') }}</a>
                    </li>
                @endcan
                @can('list_qr')
                    <li class="nav-item">
                        <a href="{{route('admin.qr.index')}}"
                           data-href="{{route('admin.qr.index')}}"
                           class="nav-link {{request()->routeIs('admin.qr.*') ? 'active' : ''}}">{{ __('index.qr') }}</a>
                    </li>
                @endcan
                @can('list_employee')
                    <li class="nav-item">
                        <a href="{{ route('admin.face-kiosks.index') }}"
                           data-href="{{ route('admin.face-kiosks.index') }}"
                           class="nav-link {{ request()->routeIs('admin.face-kiosks.*') ? 'active' : '' }}">
                            {{ __('index.face_attendance') }}
                        </a>
                    </li>
                @endcan
                @can('attendance_setting')
                    <li class="nav-item">
                        <a href="{{route('admin.attendance-settings.index')}}"
                           data-href="{{route('admin.attendance-settings.index')}}"
                           class="nav-link {{request()->routeIs('admin.attendance-settings.*') ? 'active' : ''}}">{{ __('index.attendance_settings') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany
