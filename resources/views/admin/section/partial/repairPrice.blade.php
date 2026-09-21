@can('repair_price.view')
    <li class="nav-item {{ request()->routeIs('admin.repair-price.*') ? 'active' : '' }}">
        <a href="{{ route('admin.repair-price.index') }}"
           data-href="{{ route('admin.repair-price.index') }}" class="nav-link">
            <i class="link-icon" data-feather="tool"></i>
            <span class="link-title">{{ __('index.repair_price') ?? 'Repair Price' }}</span>
        </a>
    </li>
@endcan
