@canany(['certificate_list', 'certificate_create', 'certificate_edit', 'certificate_delete', 'certificate_studio'])
@php
    $isCertSection = request()->routeIs('admin.certificates.*') || request()->routeIs('certificates.*');
@endphp
<li class="nav-item {{ $isCertSection ? 'active' : '' }}">
    <a class="nav-link {{ $isCertSection ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#certificateManagement" role="button" aria-expanded="{{ $isCertSection ? 'true' : 'false' }}" aria-controls="certificateManagement">
        <i class="link-icon" data-feather="award"></i>
        <span class="link-title"> {{ __('index.certificate_management') }} </span>
        <i class="link-arrow" data-feather="chevron-down"></i>
    </a>
    <div class="collapse {{ $isCertSection ? 'show' : '' }}" id="certificateManagement">
        <ul class="nav sub-menu">
            @can('certificate_list')
            <li class="nav-item">
                <a href="{{ route('admin.certificates.index') }}"
                   data-href="{{ route('admin.certificates.index') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.index') ? 'active' : '' }}">
                    {{ __('index.certificate_directory') }}
                </a>
            </li>
            @endcan
            @canany(['certificate_list', 'certificate_create', 'certificate_edit', 'certificate_delete', 'certificate_studio'])
            <li class="nav-item">
                <a href="{{ route('admin.certificates.templates') }}"
                   data-href="{{ route('admin.certificates.templates') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.templates') || request()->routeIs('admin.certificates.studio') || request()->is('certificates-studio') ? 'active' : '' }}">
                    {{ __('index.certificate_templates') }}
                </a>
            </li>
            @endcanany
            @can('certificate_create')
            <li class="nav-item">
                <a href="{{ route('admin.certificates.create') }}"
                   data-href="{{ route('admin.certificates.create') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.create') ? 'active' : '' }}">
                    {{ __('index.issue_certificate') }}
                </a>
            </li>
            @endcan
            @can('certificate_create')
            <li class="nav-item">
                <a href="{{ route('admin.certificates.batch') }}"
                   data-href="{{ route('admin.certificates.batch') }}"
                   class="nav-link {{ request()->routeIs('admin.certificates.batch*') ? 'active' : '' }}">
                    {{ __('index.batch_issue_certificate') }}
                </a>
            </li>
            @endcan
        </ul>
    </div>
</li>
@endcanany
