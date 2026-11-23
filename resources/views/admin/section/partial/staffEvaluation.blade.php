@can('employee.performance.create')
    @php
        $isStaffEvalSection = request()->routeIs('admin.staff-evaluations.*');
    @endphp
    <li class="nav-item {{ $isStaffEvalSection ? 'active' : '' }}">
        <a class="nav-link {{ $isStaffEvalSection ? '' : 'collapsed' }}"
           data-bs-toggle="collapse"
           href="#staff_evaluation_menu"
           data-href="#"
           role="button"
           aria-expanded="{{ $isStaffEvalSection ? 'true' : 'false' }}"
           aria-controls="staff_evaluation_menu">
            <i class="link-icon" data-feather="trending-up"></i>
            <span class="link-title">{{ __('index.staff_evaluations') }}</span>
            <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ $isStaffEvalSection ? 'show' : '' }}" id="staff_evaluation_menu">
            <ul class="nav sub-menu">
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.dashboard') }}"
                       data-href="{{ route('admin.staff-evaluations.dashboard') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.dashboard') ? 'active' : '' }}">{{ __('index.evaluation_dashboard') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.job-descriptions.index') }}"
                       data-href="{{ route('admin.staff-evaluations.job-descriptions.index') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.job-descriptions.*') ? 'active' : '' }}">{{ __('index.job_descriptions') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.templates.index') }}"
                       data-href="{{ route('admin.staff-evaluations.templates.index') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.templates.*') ? 'active' : '' }}">{{ __('index.evaluation_templates') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.ai-create') }}"
                       data-href="{{ route('admin.staff-evaluations.ai-create') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.ai-create') ? 'active' : '' }}">{{ __('index.ai_evaluation_generator') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.history') }}"
                       data-href="{{ route('admin.staff-evaluations.history') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.history') ? 'active' : '' }}">{{ __('index.evaluation_history') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.reports') }}"
                       data-href="{{ route('admin.staff-evaluations.reports') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.reports') ? 'active' : '' }}">{{ __('index.performance_reports') }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.staff-evaluations.settings') }}"
                       data-href="{{ route('admin.staff-evaluations.settings') }}"
                       class="nav-link {{ request()->routeIs('admin.staff-evaluations.settings') ? 'active' : '' }}">{{ __('index.evaluation_settings') }}</a>
                </li>
            </ul>
        </div>
    </li>
@endcan
