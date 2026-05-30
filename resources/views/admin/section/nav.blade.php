@php
    $locale = \Illuminate\Support\Facades\App::getLocale();
    $languages = config('app.supported_locales', []);
    $currentLanguage = $languages[$locale] ?? $languages['en'];
    $authUser = auth('admin')->user();
    $authEmployee = auth()->user();
@endphp
<style>
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

<!-- partial:partials/_navbar.html -->
<nav class="navbar">
    <a href="#" class="sidebar-toggler">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
{{--        <form class="search-form">--}}
{{--            <div class="input-group">--}}
{{--                <div class="input-group-text">--}}
{{--                    <i data-feather="bell"></i>--}}
{{--                </div>--}}
{{--                <h4 class="me-5">Attendance Application </h4>--}}
{{--            </div>--}}
{{--        </form>--}}

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
                            <ul id="nav-search-listing" class="list-group list-group-flush" >

                            </ul>
                        </div>
                </div>
            </div>
        </form>

        <ul class="navbar-nav">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="flag-icon flag-icon-{{ $currentLanguage['flag'] }}" title="{{ $locale }}" id="{{ $locale }}"></i>
                    <span class="ml-1">{{ $currentLanguage['name'] }}</span>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="langDropdown">

                    <ul class="list-unstyled p-1">

                        @foreach($languages as $languageCode => $language)
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item changeLang @if($locale == $languageCode || ($languageCode == 'en' && $locale == '')) active text-white @endif" data-lang="{{ $languageCode }}">
                                    <i class="flag-icon flag-icon-{{ $language['flag'] }}" title="{{ $languageCode }}" id="{{ $languageCode }}"></i>
                                    <span class="ml-1">{{ $language['name'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <button type="button" class="navbar-toggler align-self-center">
                    <span title="{{ __('index.light_mode') }}" id="sun">
                        <i class="link-icon" data-feather="sun"></i>
                    </span>

                    <span title="{{ __('index.dark_mode') }}" id="moon">
                        <i class="link-icon" data-feather="moon"></i>
                    </span>
                </button>
            </li>

            @can('notification')
                <li class="nav-item dropdown" id="notificationsNavBar" data-href="{{route('admin.nav-notifications')}}">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i data-feather="bell"></i>
                        <div class="indicator">
                            <div class="circle"></div>
                        </div>
                    </a>
                    <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
{{--                        <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom ">--}}
{{--                            <a href="" id="navAdminNotificationCreate" data-href="{{route('admin.notifications.create')}}"  class="text-muted"><i class="link-icon" data-feather="plus"></i>  Create Notification </a>--}}
{{--                        </div>--}}


                        <div class="p-1 mt-2" id="notifications-detail">
                            <a class="text-muted p-0 px-3 py-2 " style="font-size: 12px;">{{ __('index.latest_notifications') }} </a>
                        </div>

                        <div class="p-1" id="notifications-detail">


                        </div>

                        <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                            <a href="" id="navAdminNotificationList" data-href="{{ route('admin.notifications.index') }}">{{ __('index.view_all') }}</a>
                        </div>
                    </div>
                </li>
            @endcan
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="wd-30 ht-30 rounded-circle" style="object-fit: cover"
                             src="{{ isset($authUser->avatar) ?  asset(\App\Models\Admin::AVATAR_UPLOAD_PATH.$authUser->avatar) :( isset($authEmployee->avatar) ?  asset(\App\Models\User::AVATAR_UPLOAD_PATH.$authEmployee->avatar)  : asset('assets/images/img.png'))  }}"     alt="profile">

                    </a>
                    <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                        <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                            <div class="mb-3">
                                <img class="wd-80 ht-80 rounded-circle" style="object-fit: cover" src="{{isset($authUser->avatar) ?  asset(\App\Models\Admin::AVATAR_UPLOAD_PATH.$authUser->avatar) :( isset($authEmployee->avatar) ?  asset(\App\Models\User::AVATAR_UPLOAD_PATH.$authEmployee->avatar)  : asset('assets/images/img.png'))  }}" alt="">
                            </div>
                            <div class="text-center">
                                <p class="tx-16 fw-bolder">{{ ucfirst($authUser->name ?? $authEmployee->name) }}</p>
                                <p class="tx-12 text-muted">{{ $authUser->email ?? $authEmployee->email }}</p>
                            </div>
                        </div>
                        <ul class="list-unstyled p-1">

                            <li class="dropdown-item py-2">
                                <a href="{{  isset($authEmployee) ? route('admin.employees.show',$authEmployee->id) : route('admin.users.show',$authUser->id) }}" class="text-body ms-0">
                                    <i class="me-2 icon-md" data-feather="user"></i>
                                    <span>{{ __('index.profile') }}</span>
                                </a>
                            </li>

                            @if( isset($authEmployee))
                                @can('request_leave')
                                    <li class="dropdown-item py-2">
                                        <a href="{{ route('admin.leave-request.create') }}" class="text-body ms-0">
                                            <i class="me-2 icon-md" data-feather="info"></i>
                                            <span>{{ __('index.request_leave') }}</span>
                                        </a>
                                    </li>
                                @endcan
                            @endif
                            @can('app_qr')
                            <li class="dropdown-item py-2">
                                <a class="text-body ms-0 qr-modal" title="{{ __('index.app_qr') }}" target="_blank" href='{{route('admin.showQR')}}'>
                                    <i class="me-2 icon-md" data-feather="image"></i>
                                    <span>{{ __('index.app_qr') }}</span>
                                </a>
                            </li>
                            @endcan
                            <li class="dropdown-item py-2">
                                <a href="{{ route('admin.logout') }}"
                                   onclick="event.preventDefault();
                                                       document.getElementById('logout-form').submit();" class="text-body ms-0">
                                        <i class="me-2 icon-md" data-feather="log-out"> </i>{{ __('index.log_out') }}
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
<!-- partial -->



