@if(\App\Helpers\AppHelper::checkSuperAdmin())
    @php $isUserMgmtSection = request()->routeIs('admin.users.*'); @endphp
    <li class="nav-item {{ $isUserMgmtSection ? 'active' : '' }}">
        <a class="nav-link {{ $isUserMgmtSection ? '' : 'collapsed' }}" data-bs-toggle="collapse"
           href="#user-management"
           data-href="#"
           role="button" aria-expanded="{{ $isUserMgmtSection ? 'true' : 'false' }}" aria-controls="user-management">
            <i class="link-icon" data-feather="shield"></i>
            <span class="link-title"> {{ __('index.user_management') }} </span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isUserMgmtSection ? 'show' : '' }}" id="user-management">

            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a
                        href="{{route('admin.users.index')}}"
                        data-href="{{route('admin.users.index')}}"
                        class="nav-link {{request()->routeIs('admin.users.*') ? 'active' : ''}}">{{ __('index.users') }}</a>
                </li>
            </ul>
        </div>
    </li>
@endif
