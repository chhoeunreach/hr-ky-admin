@can('repair_price.view')
    <li class="nav-item {{ request()->routeIs('admin.repair.*') || request()->routeIs('admin.repair-price.*') ? 'active' : '' }}">
        <a data-href="#"
           class="nav-link"
           data-bs-toggle="collapse"
           href="#repair_management_menu"
           role="button"
           aria-expanded="{{ request()->routeIs('admin.repair.*') || request()->routeIs('admin.repair-price.*') ? 'true' : 'false' }}"
           aria-controls="repair_management_menu">
            <i class="link-icon" data-feather="tool"></i>
            <span class="link-title">{{ __('index.repair_management') ?? 'Repair Management' }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>

        <div class="{{ request()->routeIs('admin.repair.*') || request()->routeIs('admin.repair-price.*') ? '' : 'collapse' }}" id="repair_management_menu">
            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a href="{{ route('admin.repair.dashboard') }}"
                       data-href="{{ route('admin.repair.dashboard') }}"
                       class="nav-link {{ request()->routeIs('admin.repair.dashboard') ? 'active' : '' }}">
                        {{ __('index.dashboard') ?? 'Overview' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.repair-price.index') }}"
                       data-href="{{ route('admin.repair-price.index') }}"
                       class="nav-link {{ request()->routeIs('admin.repair-price.*') ? 'active' : '' }}">
                        {{ __('index.repair_price') ?? 'Price Matrix & Costs' }}
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endcan
