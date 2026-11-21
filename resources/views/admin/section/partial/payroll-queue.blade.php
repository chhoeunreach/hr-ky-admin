@canany(['view_monthly_payroll', 'view_payroll_list'])
    @php
        $isPayrollQueueSection = request()->routeIs('admin.payroll-queue.*');
        $isPayrollQueueDashboard = request()->routeIs('admin.payroll-queue.index') || request()->routeIs('admin.payroll-queue.dashboard');
        $isPayrollQueueLists = request()->routeIs('admin.payroll-queue.sessions.*');
    @endphp
    <li class="nav-item {{ $isPayrollQueueSection ? 'active' : '' }}">
        <a class="nav-link {{ $isPayrollQueueSection ? '' : 'collapsed' }}"
           data-bs-toggle="collapse"
           href="#payrollQueueMenu"
           data-href="#"
           role="button"
           aria-expanded="{{ $isPayrollQueueSection ? 'true' : 'false' }}"
           aria-controls="payrollQueueMenu">
            <i class="link-icon" data-feather="users"></i>
            <span class="link-title">{{ __('index.payroll_queue_system') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isPayrollQueueSection ? 'show' : '' }}" id="payrollQueueMenu">
            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a href="{{ route('admin.payroll-queue.dashboard') }}"
                       data-href="{{ route('admin.payroll-queue.dashboard') }}"
                       class="nav-link {{ $isPayrollQueueDashboard ? 'active' : '' }}">{{ __('index.dashboard') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.payroll-queue.sessions.index') }}"
                       data-href="{{ route('admin.payroll-queue.sessions.index') }}"
                       class="nav-link {{ $isPayrollQueueLists ? 'active' : '' }}">{{ __('index.lists') }}</a>
                </li>
            </ul>
        </div>
    </li>
@endcanany
