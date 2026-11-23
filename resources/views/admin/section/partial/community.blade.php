@canany(['flash_view.manage', 'home_highlight.manage', 'social_feed.moderate', 'social_reward.view'])
    @php
        $isCommunitySection = request()->routeIs('admin.community.*') || request()->routeIs('admin.social-marketing.*');
    @endphp
    <li class="nav-item {{ $isCommunitySection ? 'active' : '' }}">
        <a class="nav-link {{ $isCommunitySection ? '' : 'collapsed' }}"
           data-bs-toggle="collapse"
           href="#community_menu"
           data-href="#"
           role="button"
           aria-expanded="{{ $isCommunitySection ? 'true' : 'false' }}"
           aria-controls="community_menu">
            <i class="link-icon" data-feather="message-square"></i>
            <span class="link-title">{{ __('index.communication_hub') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isCommunitySection ? 'show' : '' }}" id="community_menu">
            <ul class="nav sub-menu">
                @can('flash_view.manage')
                    <li class="nav-item">
                        <a href="{{ route('admin.community.flashes') }}"
                           data-href="{{ route('admin.community.flashes') }}"
                           class="nav-link {{ request()->routeIs('admin.community.flash*') ? 'active' : '' }}">
                            {{ __('index.flash_view') }}
                        </a>
                    </li>
                @endcan
                @can('home_highlight.manage')
                    <li class="nav-item">
                        <a href="{{ route('admin.community.home-highlights.index') }}"
                           data-href="{{ route('admin.community.home-highlights.index') }}"
                           class="nav-link {{ request()->routeIs('admin.community.home-highlights.*') ? 'active' : '' }}">
                            {{ __('index.home_highlights') }}
                        </a>
                    </li>
                @endcan
                @can('social_feed.moderate')
                    <li class="nav-item">
                        <a href="{{ route('admin.community.feed') }}"
                           data-href="{{ route('admin.community.feed') }}"
                           class="nav-link {{ request()->routeIs('admin.community.feed', 'admin.community.comments') ? 'active' : '' }}">
                            {{ __('index.social_feed') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.community.reports') }}"
                           data-href="{{ route('admin.community.reports') }}"
                           class="nav-link {{ request()->routeIs('admin.community.reports') ? 'active' : '' }}">
                            {{ __('index.social_reports') }}
                        </a>
                    </li>
                @endcan
                @can('social_reward.view')
                    <li class="nav-item">
                        <a href="{{ route('admin.social-marketing.index') }}"
                           data-href="{{ route('admin.social-marketing.index') }}"
                           class="nav-link {{ request()->routeIs('admin.social-marketing.*') ? 'active' : '' }}">
                            {{ __('index.social_marketing') }}
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany
