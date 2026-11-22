@php
    $dailyRecordsActor = auth('admin')->user() ?: auth()->user();
@endphp
@if ($dailyRecordsActor?->is_active)
    @can('daily_records.manage')
        <li class="nav-item {{ request()->routeIs('admin.daily-records.*') && !request()->routeIs('admin.daily-records.branch-routes') ? 'active' : '' }}">
        <a href="{{ route('admin.daily-records.index') }}" data-href="{{ route('admin.daily-records.index') }}" class="nav-link">
            <i class="link-icon" data-feather="clipboard"></i>
            <span class="link-title">{{ __('daily_records.task_and_report_center') }}</span>
        </a>
        </li>
        <li class="nav-item {{ request()->routeIs('admin.daily-records.branch-routes') ? 'active' : '' }}">
            <a href="{{ route('admin.daily-records.branch-routes') }}" data-href="{{ route('admin.daily-records.branch-routes') }}" class="nav-link">
                <i class="link-icon" data-feather="map"></i>
                <span class="link-title">{{ __('daily_records.branch_route_management') }}</span>
            </a>
        </li>
    @endcan
@endif
