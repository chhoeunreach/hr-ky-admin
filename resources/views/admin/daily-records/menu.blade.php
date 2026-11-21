@php
    $dailyRecordsActor = auth('admin')->user() ?: auth()->user();
@endphp
@if ($dailyRecordsActor?->is_active)
    @can('daily_records.manage')
        <li class="nav-item {{ request()->routeIs('admin.daily-records.*') ? 'active' : '' }}">
        <a href="{{ route('admin.daily-records.index') }}" data-href="{{ route('admin.daily-records.index') }}" class="nav-link">
            <i class="link-icon" data-feather="clipboard"></i>
            <span class="link-title">Task &amp; Report Center</span>
        </a>
        </li>
    @endcan
@endif
