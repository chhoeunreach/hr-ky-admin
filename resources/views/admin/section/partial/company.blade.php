@php use App\Helpers\AppHelper; @endphp
@if(AppHelper::checkSuperAdmin() || auth()->user()?->canAny(['view_company','list_branch','list_department','list_post']))
    @php
        $isCompanySection = request()->routeIs('admin.company.*') ||
            request()->routeIs('admin.branch.*') ||
            request()->routeIs('admin.departments.*') ||
            request()->routeIs('admin.posts.*');
    @endphp
    <li class="nav-item {{ $isCompanySection ? 'active' : '' }}">
        <a class="nav-link {{ $isCompanySection ? '' : 'collapsed' }}" data-bs-toggle="collapse"
           href="#company_management"
           data-href="#"
           role="button" aria-expanded="{{ $isCompanySection ? 'true' : 'false' }}" aria-controls="company_management">
            <i class="link-icon" data-feather="align-justify"></i>
            <span class="link-title">{{ __('index.company_management') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isCompanySection ? 'show' : '' }}" id="company_management">
            <ul class="nav sub-menu">
                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item">
                        <a href="{{route('admin.company.index')}}"
                           data-href="{{route('admin.company.index')}}"
                           class="nav-link {{request()->routeIs('admin.company.*') ? 'active' : ''}}">{{ __('index.company') }}</a>
                    </li>
                @endif

                @if(AppHelper::checkSuperAdmin())
                    <li class="nav-item">
                        <a href="{{route('admin.branch.index')}}"
                           data-href="{{route('admin.branch.index')}}"
                           class="nav-link {{request()->routeIs('admin.branch.*') ? 'active' : ''}}">{{ __('index.branch') }}</a>
                    </li>
                @endif

                @can('list_department')
                    <li class="nav-item">
                        <a href="{{route('admin.departments.index')}}"
                           data-href="{{route('admin.departments.index')}}"
                           class="nav-link {{request()->routeIs('admin.departments.*') ? 'active' : ''}}">{{ __('index.department') }}</a>
                    </li>
                @endcan

                @can('list_post')
                    <li class="nav-item">
                        <a href="{{route('admin.posts.index')}}"
                           data-href="{{route('admin.posts.index')}}"
                           class="nav-link {{request()->routeIs('admin.posts.*') ? 'active' : ''}}">{{ __('index.post') }}</a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endif

