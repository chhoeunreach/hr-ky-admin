@php
    $locale = \Illuminate\Support\Facades\App::getLocale();
    $languages = config('app.supported_locales', []);
    $currentLanguage = $languages[$locale] ?? $languages['en'];
    $authUser = auth('admin')->user();
    $authEmployee = auth()->user();
@endphp
<style>
    /* Top Navbar single-line layout & responsive styling */
    .navbar {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        height: 60px !important;
        padding: 0 16px !important;
        min-width: 0 !important;
        background: #ffffff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .navbar .sidebar-toggler {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 38px !important;
        width: 38px !important;
        padding: 0 !important;
        border: none !important;
        flex-shrink: 0 !important;
        color: #4b5563 !important;
        margin-right: 10px !important;
        border-radius: 6px;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .navbar .sidebar-toggler:hover {
        background-color: rgba(0, 0, 0, 0.05);
        color: var(--primary-color, #0d6efd) !important;
    }
    .navbar .navbar-content {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        min-width: 0 !important;
        padding: 0 !important;
        gap: 8px;
    }
    .navbar .search-form {
        flex: 0 1 240px !important;
        min-width: 80px !important;
        max-width: 240px !important;
        margin-right: auto !important;
    }
    @media (max-width: 576px) {
        .navbar .search-form {
            display: none !important;
        }
    }
    .navbar .navbar-nav {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        margin-left: auto !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        gap: 4px !important;
        margin-bottom: 0 !important;
        padding-left: 0 !important;
        list-style: none !important;
    }
    .navbar .navbar-nav .nav-item {
        display: inline-flex !important;
        align-items: center !important;
        flex-shrink: 0 !important;
    }
    .navbar .navbar-nav .nav-link,
    .navbar .navbar-nav .top-nav-action-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 6px 8px !important;
        color: #4b5563 !important;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease-in-out;
    }
    .navbar .navbar-nav .nav-link:hover,
    .navbar .navbar-nav .top-nav-action-btn:hover {
        color: var(--primary-color, #0d6efd) !important;
        background-color: rgba(0, 0, 0, 0.04);
    }
    .navbar .navbar-nav .nav-link svg,
    .navbar .navbar-nav .top-nav-action-btn svg {
        width: 19px !important;
        height: 19px !important;
        vertical-align: middle;
    }
    /* Server Status Pill */
    .nav-server-status {
        display: inline-flex;
        align-items: center;
        background-color: rgba(16, 185, 129, 0.08);
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.2;
        color: #059669;
        user-select: none;
        transition: all 0.2s ease;
    }
    .nav-server-status.is-offline {
        background-color: rgba(239, 68, 68, 0.08);
        border-color: rgba(239, 68, 68, 0.3);
        color: #dc2626;
    }
    .nav-status-pulse {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #10b981;
        position: relative;
    }
    .nav-server-status.is-offline .nav-status-pulse {
        background-color: #ef4444;
    }
    .nav-status-pulse::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        border: 1.5px solid #10b981;
        animation: nav-status-pulse-anim 1.8s infinite cubic-bezier(0.4, 0, 0.6, 1);
    }
    .nav-server-status.is-offline .nav-status-pulse::after {
        border-color: #ef4444;
    }
    @keyframes nav-status-pulse-anim {
        0% { transform: scale(0.9); opacity: 1; }
        100% { transform: scale(2.2); opacity: 0; }
    }
    #nav-search-listing > li.highlight {
        background-color:#e82e5f;
    }
    #nav-search-listing > li:hover{
        background-color: #e82e5f;
    }
    #nav-search-listing > li {
        border-bottom: 1px dashed #f1f1f1;
    }
    #nav-search-listing > li.highlight a,#nav-search-listing > li:hover a  {
        color: white;
    }
    #nav-search-listing > li a {
        text-transform: capitalize;
        color: #232323;
    }
</style>

<!-- Top Navigation Bar -->
<nav class="navbar">
    <a href="#" class="sidebar-toggler" title="Menu">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
        <form class="search-form mb-0">
            <div class="input-group">
                <div class="input-group-text">
                    <i data-feather="search"></i>
                </div>
                <div id="admin-search-menu">
                    <input class="form-control mt-0"
                           id="nav-search"
                           name="nav-search"
                           type="text"
                           autocomplete="off"
                           placeholder="{{ __('index.search_menu') }}(ctrl+q)"
                           aria-label="{{ __('index.search') }}">

                    <div class="card card-admin-search" data-toggle="" style="position: absolute !important;">
                        <ul id="nav-search-listing" class="list-group list-group-flush"></ul>
                    </div>
                </div>
            </div>
        </form>

        <ul class="navbar-nav">

            {{-- Server Status --}}
            <li class="nav-item me-1" id="navServerStatus">
                <div class="nav-server-status" id="topNavServerStatus" title="{{ __('index.server_status') }}: {{ __('index.server_ok') }}">
                    <span class="nav-status-pulse me-1"></span>
                    <span class="nav-status-label d-none d-sm-inline">{{ __('index.server_ok') }}</span>
                </div>
            </li>

            {{-- Sent Location / GPS Tracker --}}
            <li class="nav-item me-1" id="navSentLocation">
                <a href="{{ route('track-location') }}" target="_blank" class="top-nav-action-btn" title="{{ __('index.sent_location') }}">
                    <i data-feather="map-pin"></i>
                    <span class="d-none d-xl-inline ms-1" style="font-size: 12px; font-weight: 500;">{{ __('index.sent_location') }}</span>
                </a>
            </li>

            {{-- Chat / Employee Chat --}}
            <li class="nav-item me-1" id="navEmployeeChat">
                <a href="{{ route('admin.employee-chat') }}" class="top-nav-action-btn" title="{{ __('index.chat') }}">
                    <i data-feather="message-square"></i>
                    <span class="d-none d-xl-inline ms-1" style="font-size: 12px; font-weight: 500;">{{ __('index.chat') }}</span>
                </a>
            </li>

            {{-- Notification --}}
            @can('notification')
                <li class="nav-item dropdown" id="notificationsNavBar" data-href="{{ route('admin.nav-notifications') }}">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ __('index.latest_notifications') }}">
                        <i data-feather="bell"></i>
                        <div class="indicator">
                            <div class="circle"></div>
                        </div>
                    </a>
                    <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
                        <div class="p-1 mt-2" id="notifications-detail">
                            <a class="text-muted p-0 px-3 py-2 " style="font-size: 12px;">{{ __('index.latest_notifications') }} </a>
                        </div>
                        <div class="p-1" id="notifications-detail"></div>
                        <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                            <a href="" id="navAdminNotificationList" data-href="{{ route('admin.notifications.index') }}">{{ __('index.view_all') }}</a>
                        </div>
                    </div>
                </li>
            @endcan

            {{-- Language Selector --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ $currentLanguage['name'] ?? 'Language' }}">
                    <i class="flag-icon flag-icon-{{ $currentLanguage['flag'] }}" title="{{ $locale }}" id="{{ $locale }}"></i>
                    <span class="ms-1 d-none d-md-inline">{{ $currentLanguage['name'] }}</span>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="langDropdown">
                    <ul class="list-unstyled p-1">
                        @foreach($languages as $languageCode => $language)
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item changeLang @if($locale == $languageCode || ($languageCode == 'en' && $locale == '')) active text-white @endif" data-lang="{{ $languageCode }}">
                                    <i class="flag-icon flag-icon-{{ $language['flag'] }}" title="{{ $languageCode }}" id="{{ $languageCode }}"></i>
                                    <span class="ms-1">{{ $language['name'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>

            {{-- Dark / Light Mode --}}
            <li class="nav-item">
                <button type="button" class="navbar-toggler align-self-center border-0 p-1" style="background: transparent;">
                    <span title="{{ __('index.light_mode') }}" id="sun">
                        <i class="link-icon" data-feather="sun"></i>
                    </span>
                    <span title="{{ __('index.dark_mode') }}" id="moon">
                        <i class="link-icon" data-feather="moon"></i>
                    </span>
                </button>
            </li>

            {{-- Profile Dropdown --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ ucfirst($authUser->name ?? $authEmployee->name ?? 'Profile') }}">
                    <img class="wd-30 ht-30 rounded-circle" style="object-fit: cover"
                         src="{{ isset($authUser->avatar) ? asset(\App\Models\Admin::AVATAR_UPLOAD_PATH.$authUser->avatar) : (isset($authEmployee->avatar) ? asset(\App\Models\User::AVATAR_UPLOAD_PATH.$authEmployee->avatar) : asset('assets/images/img.png')) }}" alt="profile">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            <img class="wd-80 ht-80 rounded-circle" style="object-fit: cover" src="{{ isset($authUser->avatar) ? asset(\App\Models\Admin::AVATAR_UPLOAD_PATH.$authUser->avatar) : (isset($authEmployee->avatar) ? asset(\App\Models\User::AVATAR_UPLOAD_PATH.$authEmployee->avatar) : asset('assets/images/img.png')) }}" alt="">
                        </div>
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">{{ ucfirst($authUser->name ?? $authEmployee->name) }}</p>
                            <p class="tx-12 text-muted">{{ $authUser->email ?? $authEmployee->email }}</p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li class="dropdown-item py-2">
                            <a href="{{ isset($authEmployee) ? route('admin.employees.show', $authEmployee->id) : route('admin.users.show', $authUser->id) }}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="user"></i>
                                <span>{{ __('index.profile') }}</span>
                            </a>
                        </li>

                        @if(isset($authEmployee))
                            @can('request_leave')
                                <li class="dropdown-item py-2">
                                    <a href="{{ route('admin.leave-request.create') }}" class="text-body ms-0" data-open-leave-request-form>
                                        <i class="me-2 icon-md" data-feather="info"></i>
                                        <span>{{ __('index.request_leave') }}</span>
                                    </a>
                                </li>
                            @endcan
                        @endif

                        @can('app_qr')
                            <li class="dropdown-item py-2">
                                <a class="text-body ms-0 qr-modal" title="{{ __('index.app_qr') }}" target="_blank" href="{{ route('admin.showQR') }}">
                                    <i class="me-2 icon-md" data-feather="image"></i>
                                    <span>{{ __('index.app_qr') }}</span>
                                </a>
                            </li>
                        @endcan

                        <li class="dropdown-item py-2">
                            <a href="{{ route('admin.logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>{{ __('index.log_out') }}</span>
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>
    </div>
</nav>

<script>
    (function () {
        function updateServerStatus() {
            var statusEl = document.getElementById('topNavServerStatus');
            if (!statusEl) return;
            var isOnline = window.navigator.onLine;
            var labelEl = statusEl.querySelector('.nav-status-label');
            if (isOnline) {
                statusEl.classList.remove('is-offline');
                statusEl.setAttribute('title', "{{ __('index.server_status') }}: {{ __('index.server_ok') }}");
                if (labelEl) labelEl.textContent = "{{ __('index.server_ok') }}";
            } else {
                statusEl.classList.add('is-offline');
                statusEl.setAttribute('title', "{{ __('index.server_status') }}: Offline");
                if (labelEl) labelEl.textContent = "Offline";
            }
        }
        window.addEventListener('online', updateServerStatus);
        window.addEventListener('offline', updateServerStatus);
        document.addEventListener('DOMContentLoaded', updateServerStatus);
    })();
</script>
