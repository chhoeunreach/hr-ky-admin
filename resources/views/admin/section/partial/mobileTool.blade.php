@can('banner.manage')
<li class="nav-item {{ request()->routeIs('admin.mobile-tools.banner-slides*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.mobile-tools.banner-slides') }}"><i class="link-icon" data-feather="image"></i><span class="link-title">{{ __('index.banner_slides') }}</span></a></li>
@endcan
@can('quote.manage')
<li class="nav-item {{ request()->routeIs('admin.mobile-tools.quotes*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.mobile-tools.quotes') }}"><i class="link-icon" data-feather="message-square"></i><span class="link-title">{{ __('index.mobile_quotes') }}</span></a></li>
@endcan
