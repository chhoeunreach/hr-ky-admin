@can('flash_view.manage')
<li class="nav-item {{ request()->routeIs('admin.community.flash*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.community.flashes') }}"><i class="link-icon" data-feather="zap"></i><span class="link-title">{{ __('index.flash_view') }}</span></a></li>
@endcan
@can('home_highlight.manage')
<li class="nav-item {{ request()->routeIs('admin.community.home-highlights.*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.community.home-highlights.index') }}"><i class="link-icon" data-feather="star"></i><span class="link-title">{{ __('index.home_highlights') }}</span></a></li>
@endcan
@can('social_feed.moderate')
<li class="nav-item {{ request()->routeIs('admin.community.feed', 'admin.community.comments') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.community.feed') }}"><i class="link-icon" data-feather="message-circle"></i><span class="link-title">{{ __('index.social_feed') }}</span></a></li>
<li class="nav-item {{ request()->routeIs('admin.community.reports') ? 'active' : '' }}"><a class="nav-link" href="{{ route('admin.community.reports') }}"><i class="link-icon" data-feather="flag"></i><span class="link-title">{{ __('index.social_reports') }}</span></a></li>
@endcan
