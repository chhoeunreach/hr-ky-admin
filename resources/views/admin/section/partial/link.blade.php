<li class="nav-item {{ request()->routeIs('admin.app-links.*') ? 'active' : '' }}">
    <a href="{{ route('admin.app-links.index') }}"
       data-href="{{ route('admin.app-links.index') }}" class="nav-link">
        <i class="link-icon" data-feather="link"></i>
        <span class="link-title">{{ __('index.link_list') ?? 'List Link' }}</span>
    </a>
</li>
