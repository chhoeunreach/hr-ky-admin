<li class="nav-item {{ request()->routeIs('admin.certificates.*') || request()->routeIs('certificates.*') ? 'active' : '' }}">
    <a class="nav-link" data-bs-toggle="collapse" href="#certificateManagement" role="button" aria-expanded="{{ request()->routeIs('admin.certificates.*') || request()->routeIs('certificates.*') ? 'true' : 'false' }}" aria-controls="certificateManagement">
        <i class="link-icon" data-feather="award"></i>
        <span class="link-title"> {{ __('index.certificate_management') }} </span>
        <i class="link-arrow" data-feather="chevron-down"></i>
    </a>
    <div class="{{ request()->routeIs('admin.certificates.*') || request()->routeIs('certificates.*') ? '' : 'collapse' }}" id="certificateManagement">
        <ul class="nav sub-menu">
            <li class="nav-item">
                <a href="{{ route('admin.certificates.index') }}"
                   data-href="{{ route('admin.certificates.index') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.index') ? 'active' : '' }}">
                    {{ __('index.certificate_directory') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.certificates.templates') }}"
                   data-href="{{ route('admin.certificates.templates') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.templates') || request()->routeIs('admin.certificates.studio') || request()->is('certificates-studio') ? 'active' : '' }}">
                    {{ __('index.certificate_templates') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.certificates.create') }}"
                   data-href="{{ route('admin.certificates.create') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.create') ? 'active' : '' }}">
                    {{ __('index.issue_certificate') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.certificates.batch') }}"
                   data-href="{{ route('admin.certificates.batch') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.batch*') ? 'active' : '' }}">
                    {{ __('index.batch_issue_certificate') }}
                </a>
            </li>
        </ul>
    </div>
</li>
