<nav class="page-breadcrumb d-flex align-items-center justify-content-between mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payroll-queue.sessions.index') }}">{{ __('index.payroll_queue_system') }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">@yield('action')</li>
    </ol>

    <div class="d-flex align-items-center gap-2">
        <div class="btn-group" role="group">
            <a href="{{ route('admin.payroll-queue.dashboard') }}"
               class="btn btn-sm {{ request()->routeIs('admin.payroll-queue.index') || request()->routeIs('admin.payroll-queue.dashboard') ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="link-icon me-1" data-feather="activity"></i> {{ __('index.dashboard') }}
            </a>
            <a href="{{ route('admin.payroll-queue.sessions.index') }}"
               class="btn btn-sm {{ request()->routeIs('admin.payroll-queue.sessions.*') ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="link-icon me-1" data-feather="list"></i> {{ __('index.lists') }}
            </a>
        </div>
        @yield('button')
    </div>
</nav>
