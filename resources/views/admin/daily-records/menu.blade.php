@php
    $dailyRecordsActor = auth('admin')->user() ?: auth()->user();
@endphp
@if ($dailyRecordsActor?->is_active)
    @can('daily_records.manage')
        <li class="nav-item {{ request()->routeIs('admin.daily-records.*') ? 'active' : '' }}">
        <a href="{{ route('admin.daily-records.index') }}" data-href="{{ route('admin.daily-records.index') }}" class="nav-link">
            <i class="link-icon" data-feather="clipboard"></i>
            <span class="link-title">{{ __('daily_records.task_and_report_center') }}</span>
        </a>
        </li>
    @endcan
@endif
