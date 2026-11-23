@php use App\Helpers\AppHelper; @endphp
@if(AppHelper::checkSuperAdmin() || auth('admin')->check() || auth()->check())
    @php $isDocSection = request()->routeIs('admin.company-documents.*'); @endphp
    <li class="nav-item {{ $isDocSection ? 'active' : '' }}">
        <a class="nav-link {{ $isDocSection ? '' : 'collapsed' }}" data-bs-toggle="collapse"
           href="#company_documents_menu"
           role="button"
           aria-expanded="{{ $isDocSection ? 'true' : 'false' }}"
           aria-controls="company_documents_menu">
            <i class="link-icon" data-feather="folder"></i>
            <span class="link-title">{{ __('index.company_documents') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isDocSection ? 'show' : '' }}" id="company_documents_menu">
            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index') }}"
                       class="nav-link {{ request()->routeIs('admin.company-documents.index') && !request('category') ? 'active' : '' }}">
                        📁 {{ __('index.all_documents') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index', ['category' => 'master']) }}"
                       class="nav-link {{ request('category') === 'master' ? 'active' : '' }}">
                        🏢 {{ __('index.doc_master') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index', ['category' => 'tax']) }}"
                       class="nav-link {{ request('category') === 'tax' ? 'active' : '' }}">
                        📑 {{ __('index.doc_tax') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index', ['category' => 'business_license']) }}"
                       class="nav-link {{ request('category') === 'business_license' ? 'active' : '' }}">
                        🏪 {{ __('index.doc_business_license') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index', ['category' => 'nssf']) }}"
                       class="nav-link {{ request('category') === 'nssf' ? 'active' : '' }}">
                        🏥 {{ __('index.doc_nssf') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index', ['category' => 'contract']) }}"
                       class="nav-link {{ request('category') === 'contract' ? 'active' : '' }}">
                        ✍️ {{ __('index.doc_contract') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.company-documents.index', ['category' => 'utility']) }}"
                       class="nav-link {{ request('category') === 'utility' ? 'active' : '' }}">
                        ⚡ {{ __('index.doc_utility') }}
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endif
