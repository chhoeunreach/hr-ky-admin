@canany(['list_type','list_assets'])
    <li class="nav-item {{ request()->routeIs('admin.asset-types.*') || request()->routeIs('admin.assets.*')
                        ? 'active' : '' }} ">
        <a class="nav-link" data-bs-toggle="collapse" href="#assets" data-href="#" role="button" aria-expanded="false" aria-controls="assets">
            <i class="link-icon" data-feather="loader"></i>
            <span class="link-title">{{ __('index.asset_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="{{ request()->routeIs('admin.asset-types.*') || request()->routeIs('admin.assets.*')
                   ?'' : 'collapse'  }}" id="assets">
            <ul class="nav sub-menu">

                @can('list_type')
                    <li class="nav-item">
                        <a
                            href="{{route('admin.asset-types.index')}}"
                            data-href="{{route('admin.asset-types.index')}}"
                            class="nav-link {{ request()->routeIs('admin.asset-types.*') ? 'active' : '' }}">{{ __('index.asset_types') }}</a>
                    </li>
                @endcan

                @can('list_assets')
                    <li class="nav-item">
                        <a href="{{route('admin.assets.index')}}"
                           data-href="{{route('admin.assets.index')}}"
                           class="nav-link {{ request()->routeIs('admin.assets.*') ? 'active' : '' }}">{{ __('index.assets') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcanany
