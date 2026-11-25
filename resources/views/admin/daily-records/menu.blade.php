@php
    $dailyRecordsActor = auth('admin')->user() ?: auth()->user();
    $isDailyRecords = request()->routeIs('admin.daily-records.*') || request()->routeIs('admin.deliveries.*');
@endphp
@if ($dailyRecordsActor?->is_active)
    @can('daily_records.manage')
        <li class="nav-item {{ $isDailyRecords ? 'active' : '' }}">
            <a class="nav-link {{ $isDailyRecords ? '' : 'collapsed' }}"
               data-bs-toggle="collapse"
               href="#dailyRecordsMenu"
               data-href="#"
               role="button"
               aria-expanded="{{ $isDailyRecords ? 'true' : 'false' }}"
               aria-controls="dailyRecordsMenu">
                <i class="link-icon" data-feather="check-square"></i>
                <span class="link-title">{{ __('daily_records.work_and_operations') }}</span>
                <i class="link-arrow" data-feather="chevron-down"></i>
            </a>
            <div class="collapse {{ $isDailyRecords ? 'show' : '' }}" id="dailyRecordsMenu">
                <ul class="nav sub-menu">
                    <li class="nav-item {{ (request()->routeIs('admin.daily-records.index') || request()->routeIs('admin.daily-records.create')) ? 'active' : '' }}">
                        <a href="{{ route('admin.daily-records.index') }}"
                           data-href="{{ route('admin.daily-records.index') }}"
                           class="nav-link {{ (request()->routeIs('admin.daily-records.index') || request()->routeIs('admin.daily-records.create')) ? 'active' : '' }}">
                            {{ __('daily_records.task_and_report_center') }}
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.daily-records.branch-routes') ? 'active' : '' }}">
                        <a href="{{ route('admin.daily-records.branch-routes') }}"
                           data-href="{{ route('admin.daily-records.branch-routes') }}"
                           class="nav-link {{ request()->routeIs('admin.daily-records.branch-routes') ? 'active' : '' }}">
                            {{ __('daily_records.branch_route_management') }}
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.deliveries.index') }}"
                           data-href="{{ route('admin.deliveries.index') }}"
                           class="nav-link {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
                            {{ __('deliveries.delivery_management') }}
                        </a>
                    </li>
                    <li class="nav-item {{ (request()->routeIs('admin.daily-records.submissions') || request()->routeIs('admin.daily-records.submission')) ? 'active' : '' }}">
                        <a href="{{ route('admin.daily-records.submissions') }}"
                           data-href="{{ route('admin.daily-records.submissions') }}"
                           class="nav-link {{ (request()->routeIs('admin.daily-records.submissions') || request()->routeIs('admin.daily-records.submission')) ? 'active' : '' }}">
                            {{ __('daily_records.daily_submissions') }}
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.daily-records.categories') ? 'active' : '' }}">
                        <a href="{{ route('admin.daily-records.categories') }}"
                           data-href="{{ route('admin.daily-records.categories') }}"
                           class="nav-link {{ request()->routeIs('admin.daily-records.categories') ? 'active' : '' }}">
                            {{ __('daily_records.categories') }}
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan
@endif
