@extends('layouts.master')

@section('title', __('index.employee_calendar') ?? 'Employee Calendar')

@section('action', __('index.employee_calendar') ?? 'Employee Calendar')

@section('button')
    <div class="float-md-end d-flex align-items-center gap-2 justify-content-center flex-wrap">
        <a href="{{ route('admin.employees.index') }}">
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="link-icon" data-feather="list"></i>{{ __('index.employee_lists') ?? 'Employee Lists' }}
            </button>
        </a>
        <a href="{{ route('admin.leave-request.add') }}">
            <button class="btn btn-primary d-flex align-items-center gap-2">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.leave_request') ?? 'Leave' }}
            </button>
        </a>
        <a href="{{ route('admin.time-leave-request.create') }}">
            <button class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="link-icon" data-feather="clock"></i>{{ __('index.time_leave_request') ?? 'Time Leave' }}
            </button>
        </a>
        <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2" id="exportCalendarCsvBtn" title="Export monthly schedule as CSV">
            <i class="link-icon" data-feather="download"></i>Export CSV
        </button>
        <button type="button" class="btn btn-outline-dark d-flex align-items-center gap-2" onclick="window.print()" title="Print Calendar">
            <i class="link-icon" data-feather="printer"></i>Print
        </button>
    </div>
@endsection

@section('main-content')
    <section class="content employee-calendar-page">
        @include('admin.section.flash_message')
        @include('admin.employees.common.breadcrumb')

        @php
            $hasEmployeeFilters = filled($filterParameters['branch_id'] ?? null)
                || filled($filterParameters['department_id'] ?? null)
                || filled($filterParameters['post_id'] ?? null)
                || filled($filterParameters['employee_name'] ?? null)
                || filled($filterParameters['search'] ?? null)
                || filled($filterParameters['email'] ?? null)
                || filled($filterParameters['phone'] ?? null)
                || (($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null);

            $summary = $employeeCalendar['summary'] ?? [];
            $todayKey = now()->toDateString();
            $days = $employeeCalendar['days'] ?? [];
            $weeks = $employeeCalendar['weeks'] ?? [];
            $allEvents = $employeeCalendar['all_events'] ?? [];
            $upcomingEvents = $employeeCalendar['upcoming_events'] ?? [];
            $selectedDay = $employeeCalendar['selected_day'] ?? (collect($days)->firstWhere('is_today', true) ?? reset($days));
        @endphp

        <!-- Filter Card -->
        <div class="card mb-3 border-0 shadow-sm filter-wrapper">
            <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#employeeFilterCollapse"
                            aria-expanded="{{ $hasEmployeeFilters ? 'true' : 'false' }}"
                            aria-controls="employeeFilterCollapse">
                        <i class="link-icon" data-feather="filter"></i>
                        {{ __('index.filter') ?? 'Filter' }}
                        @if($hasEmployeeFilters)
                            <span class="badge bg-primary rounded-pill ms-1">Active</span>
                        @endif
                    </button>
                    <span class="text-muted small">Filter employees by branch, department, designation, or status</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($hasEmployeeFilters)
                        <a href="{{ route('admin.employees.calendar') }}" class="btn btn-sm btn-link text-danger text-decoration-none d-flex align-items-center gap-1">
                            <i class="link-icon" data-feather="x-circle"></i> Clear Filters
                        </a>
                    @endif
                </div>
            </div>
            <div id="employeeFilterCollapse" class="collapse{{ $hasEmployeeFilters ? ' show' : '' }}">
                <form class="forms-sample card-body pt-2 pb-1" action="{{ route('admin.employees.calendar') }}" id="employeeFilterForm" method="get">
                    <input type="hidden" id="search" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                    <input type="hidden" id="calendarMonth" name="calendar_month" value="{{ $employeeCalendar['month_value'] ?? now()->format('Y-m') }}">
                    <div class="row align-items-center">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                                <label class="form-label small text-muted mb-1">{{ __('index.branch') ?? 'Branch' }}</label>
                                <select class="form-control" id="branch" name="branch_id">
                                    <option value="" {{ empty($filterParameters['branch_id']) ? 'selected' : '' }}>{{ __('index.select_branch') ?? 'All Branches' }}</option>
                                    @foreach($branches as $branch)
                                        <option {{ ($filterParameters['branch_id'] == $branch->id) ? 'selected' : '' }} value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">{{ __('index.department') ?? 'Department' }}</label>
                            <select class="form-control" id="department" name="department_id">
                                <option value="" selected>{{ __('index.select_department') ?? 'All Departments' }}</option>
                            </select>
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">{{ __('index.post') ?? 'Designation' }}</label>
                            <select class="form-control" id="post" name="post_id">
                                <option value="" selected>{{ __('index.select_post') ?? 'All Designations' }}</option>
                            </select>
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">{{ __('index.employee_name') ?? 'Employee Name' }}</label>
                            <input type="text" placeholder="{{ __('index.employee_name') ?? 'Employee Name' }}" id="employeeName"
                                   name="employee_name" value="{{ $filterParameters['employee_name'] ?? '' }}"
                                   class="form-control">
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">{{ __('index.employee_email') ?? 'Email' }}</label>
                            <input type="text" placeholder="{{ __('index.employee_email') ?? 'Email' }}" id="email" name="email"
                                   value="{{ $filterParameters['email'] ?? '' }}" class="form-control">
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">{{ __('index.employee_phone') ?? 'Phone' }}</label>
                            <input type="number" placeholder="{{ __('index.employee_phone') ?? 'Phone' }}" id="phone" name="phone"
                                   value="{{ $filterParameters['phone'] ?? '' }}" class="form-control">
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="">All Status</option>
                                <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3">
                            <label class="form-label small text-muted mb-1">Quick Search</label>
                            <input type="text"
                                   id="employeeListSearch"
                                   class="form-control"
                                   value="{{ $filterParameters['search'] ?? '' }}"
                                   placeholder="Search keyword...">
                        </div>

                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-3 d-flex align-items-end gap-2">
                            <button type="submit" value="filter" class="btn btn-primary flex-grow-1">
                                <i class="link-icon" data-feather="search"></i> {{ __('index.filter') ?? 'Apply' }}
                            </button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.employees.calendar') }}">
                                {{ __('index.reset') ?? 'Reset' }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Metric Summary Row -->
        <div class="row g-3 mb-4 employee-calendar-kpi-row">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card total cursor-pointer filter-pill" data-category="all" title="Click to show all events">
                    <div class="kpi-icon"><i class="link-icon" data-feather="calendar"></i></div>
                    <div class="kpi-info">
                        <span class="kpi-value">{{ $summary['total_events'] ?? 0 }}</span>
                        <span class="kpi-label">Total Events</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card leave cursor-pointer filter-pill" data-category="leave" title="Click to filter leaves">
                    <div class="kpi-icon"><i class="link-icon" data-feather="sun"></i></div>
                    <div class="kpi-info">
                        <span class="kpi-value">{{ $summary['leave_requests'] ?? 0 }}</span>
                        <span class="kpi-label">Leaves</span>
                    </div>
                    @if(($summary['leave_pending'] ?? 0) > 0)
                        <span class="kpi-sub-badge pending" title="{{ $summary['leave_pending'] }} Pending">{{ $summary['leave_pending'] }} pend.</span>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card time-leave cursor-pointer filter-pill" data-category="time_leave" title="Click to filter time leaves">
                    <div class="kpi-icon"><i class="link-icon" data-feather="clock"></i></div>
                    <div class="kpi-info">
                        <span class="kpi-value">{{ $summary['time_leave_requests'] ?? 0 }}</span>
                        <span class="kpi-label">Time Leaves</span>
                    </div>
                    @if(($summary['time_leave_pending'] ?? 0) > 0)
                        <span class="kpi-sub-badge pending" title="{{ $summary['time_leave_pending'] }} Pending">{{ $summary['time_leave_pending'] }} pend.</span>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card birthday cursor-pointer filter-pill" data-category="birthday" title="Click to filter birthdays">
                    <div class="kpi-icon"><i class="link-icon" data-feather="gift"></i></div>
                    <div class="kpi-info">
                        <span class="kpi-value">{{ $summary['birthdays'] ?? 0 }}</span>
                        <span class="kpi-label">Birthdays</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card anniversary cursor-pointer filter-pill" data-category="anniversary" title="Click to filter work anniversaries">
                    <div class="kpi-icon"><i class="link-icon" data-feather="award"></i></div>
                    <div class="kpi-info">
                        <span class="kpi-value">{{ $summary['anniversaries'] ?? 0 }}</span>
                        <span class="kpi-label">Anniversaries</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="kpi-card holiday cursor-pointer filter-pill" data-category="holiday" title="Click to filter holidays">
                    <div class="kpi-icon"><i class="link-icon" data-feather="flag"></i></div>
                    <div class="kpi-info">
                        <span class="kpi-value">{{ ($summary['holidays'] ?? 0) + ($summary['company_events'] ?? 0) }}</span>
                        <span class="kpi-label">Holidays & Events</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Calendar Section -->
        <div class="card border-0 shadow-sm employee-calendar-card" id="employeeCalendarSection">
            <!-- Calendar Navigation & Controls Toolbar -->
            <div class="card-header bg-transparent border-bottom py-3">
                <div class="calendar-toolbar-wrapper">
                    <!-- Left: Navigation & Month Selector -->
                    <div class="calendar-nav-group">
                        <div class="btn-group" role="group" aria-label="Month navigation">
                            <a class="btn btn-outline-secondary"
                               title="Previous Month"
                               href="{{ request()->fullUrlWithQuery(['calendar_month' => $employeeCalendar['previous_month']]) }}">
                                <i class="link-icon" data-feather="chevron-left"></i>
                            </a>
                            <a class="btn btn-outline-secondary px-3"
                               title="Jump to Current Month"
                               href="{{ request()->fullUrlWithQuery(['calendar_month' => now()->format('Y-m')]) }}">
                                Today
                            </a>
                            <a class="btn btn-outline-secondary"
                               title="Next Month"
                               href="{{ request()->fullUrlWithQuery(['calendar_month' => $employeeCalendar['next_month']]) }}">
                                <i class="link-icon" data-feather="chevron-right"></i>
                            </a>
                        </div>
                        <input type="month"
                               class="form-control calendar-month-input"
                               id="employeeCalendarMonthPicker"
                               title="Pick Month"
                               value="{{ $employeeCalendar['month_value'] ?? now()->format('Y-m') }}">
                        <h4 class="calendar-month-heading mb-0 ms-2">
                            {{ $employeeCalendar['month_label'] ?? 'Employee Calendar' }}
                            <span class="badge bg-light text-dark border ms-1 fw-normal fs-6">{{ $summary['total_events'] ?? 0 }} events</span>
                        </h4>
                    </div>

                    <!-- Right: View Switcher & Live Search -->
                    <div class="calendar-action-group">
                        <!-- Live Search Input -->
                        <div class="calendar-search-box">
                            <i class="link-icon search-icon" data-feather="search"></i>
                            <input type="text"
                                   id="calendarLiveSearch"
                                   class="form-control form-control-sm"
                                   placeholder="Filter employee or event...">
                            <button type="button" id="calendarClearSearch" class="btn-clear d-none">&times;</button>
                        </div>

                        <!-- View Switcher Tabs -->
                        <div class="btn-group view-switcher-tabs" role="group" aria-label="View Switcher">
                            <button type="button" class="btn btn-sm btn-outline-primary active" data-view="month" id="viewMonthBtn">
                                <i class="link-icon" data-feather="grid"></i> Month
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-view="week" id="viewWeekBtn">
                                <i class="link-icon" data-feather="columns"></i> Week
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-view="agenda" id="viewAgendaBtn">
                                <i class="link-icon" data-feather="list"></i> Agenda
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Event Category Filter Pills -->
                <div class="calendar-filter-pills mt-3 pt-2 border-top d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-semibold me-1"><i class="link-icon me-1" data-feather="sliders"></i>Filter:</span>
                    <button type="button" class="btn btn-xs category-filter-btn active" data-category="all">
                        All <span class="badge rounded-pill bg-secondary ms-1">{{ $summary['total_events'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-xs category-filter-btn cat-birthday" data-category="birthday">
                        <span class="cat-dot dot-birthday"></span> Birthdays <span class="badge rounded-pill ms-1">{{ $summary['birthdays'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-xs category-filter-btn cat-leave" data-category="leave">
                        <span class="cat-dot dot-leave"></span> Leaves <span class="badge rounded-pill ms-1">{{ $summary['leave_requests'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-xs category-filter-btn cat-time-leave" data-category="time_leave">
                        <span class="cat-dot dot-time-leave"></span> Time Leaves <span class="badge rounded-pill ms-1">{{ $summary['time_leave_requests'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-xs category-filter-btn cat-anniversary" data-category="anniversary">
                        <span class="cat-dot dot-anniversary"></span> Anniversaries <span class="badge rounded-pill ms-1">{{ $summary['anniversaries'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-xs category-filter-btn cat-holiday" data-category="holiday">
                        <span class="cat-dot dot-holiday"></span> Holidays <span class="badge rounded-pill ms-1">{{ $summary['holidays'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="btn btn-xs category-filter-btn cat-event" data-category="company_event">
                        <span class="cat-dot dot-event"></span> Events <span class="badge rounded-pill ms-1">{{ $summary['company_events'] ?? 0 }}</span>
                    </button>
                </div>
            </div>

            <!-- Calendar Body with Split Sidebar -->
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Main Calendar Views Container -->
                    <div class="col-xl-9 col-lg-8 border-end main-calendar-pane">
                        <!-- VIEW 1: MONTH GRID VIEW -->
                        <div id="calendarMonthView" class="calendar-view-pane active">
                            <div class="employee-calendar-grid" aria-label="Employee calendar {{ $employeeCalendar['month_label'] ?? '' }}">
                                <!-- Weekday Headers -->
                                @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $wIndex => $weekday)
                                    <div class="employee-calendar-weekday{{ ($wIndex === 0 || $wIndex === 6) ? ' is-weekend' : '' }}">
                                        <span class="d-none d-md-inline">{{ $weekday }}</span>
                                        <span class="d-inline d-md-none">{{ substr($weekday, 0, 3) }}</span>
                                    </div>
                                @endforeach

                                <!-- Blank Days for Month Start Offset -->
                                @for($emptyDay = 0; $emptyDay < ($employeeCalendar['start_weekday'] ?? 0); $emptyDay++)
                                    <div class="employee-calendar-day empty-day"></div>
                                @endfor

                                <!-- Actual Days of Month -->
                                @foreach($days as $dateKey => $day)
                                    @php
                                        $dayEvents = $day['events'] ?? [];
                                        $eventsCount = count($dayEvents);
                                        $visibleLimit = 3;
                                    @endphp
                                    <div class="employee-calendar-day{{ $day['is_today'] ? ' is-today' : '' }}{{ ($day['is_weekend'] ?? false) ? ' is-weekend' : '' }}"
                                         data-date="{{ $dateKey }}"
                                         id="day-cell-{{ $dateKey }}">
                                        <!-- Day Number Header -->
                                        <div class="employee-calendar-day-header">
                                            <div class="day-number-wrap">
                                                <span class="day-number">{{ $day['day'] }}</span>
                                                @if($day['is_today'])
                                                    <span class="today-badge">Today</span>
                                                @endif
                                            </div>
                                            @if($eventsCount > 0)
                                                <span class="day-events-count badge rounded-pill bg-light text-secondary border">{{ $eventsCount }}</span>
                                            @endif
                                        </div>

                                        <!-- Day Events Stack -->
                                        <div class="employee-calendar-events">
                                            @foreach(array_slice($dayEvents, 0, $visibleLimit) as $event)
                                                <div class="employee-calendar-event-item {{ $event['type'] }}"
                                                     data-event-id="{{ $event['id'] }}"
                                                     data-category="{{ $event['type'] }}"
                                                     data-label="{{ strtolower($event['label'] . ' ' . $event['title'] . ' ' . ($event['employee_dept'] ?? '')) }}"
                                                     onclick="window.showEventDetailsModal('{{ $event['id'] }}')"
                                                     title="{{ $event['title'] }} - {{ $event['meta'] }}">
                                                    <div class="event-chip-content">
                                                        <span class="event-type-dot {{ $event['type'] }}"></span>
                                                        <span class="event-chip-title">{{ $event['label'] }}</span>
                                                    </div>
                                                    <span class="event-chip-meta">{{ $event['meta'] }}</span>
                                                </div>
                                            @endforeach

                                            <!-- "+X more" trigger -->
                                            @if($eventsCount > $visibleLimit)
                                                <button type="button"
                                                        class="btn-more-events"
                                                        data-date="{{ $dateKey }}"
                                                        onclick="window.showDayScheduleModal('{{ $dateKey }}')">
                                                    +{{ $eventsCount - $visibleLimit }} more...
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- VIEW 2: WEEK VIEW -->
                        <div id="calendarWeekView" class="calendar-view-pane d-none p-3">
                            <div class="week-view-toolbar d-flex align-items-center justify-content-between mb-3 bg-light p-2 rounded">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" id="prevWeekBtn">
                                        <i class="link-icon" data-feather="chevron-left"></i> Prev Week
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="nextWeekBtn">
                                        Next Week <i class="link-icon" data-feather="chevron-right"></i>
                                    </button>
                                </div>
                                <span class="fw-semibold text-muted" id="weekRangeLabel">Week Schedule</span>
                                <div class="text-muted small">7-day breakdown</div>
                            </div>
                            <div class="week-columns-wrapper" id="weekColumnsContainer">
                                <!-- Week columns will be rendered via JS -->
                            </div>
                        </div>

                        <!-- VIEW 3: AGENDA / TIMELINE LIST VIEW -->
                        <div id="calendarAgendaView" class="calendar-view-pane d-none p-3">
                            <div class="agenda-timeline-wrapper">
                                @php $hasAnyEvents = false; @endphp
                                @foreach($days as $dateKey => $day)
                                    @if(!empty($day['events']))
                                        @php $hasAnyEvents = true; @endphp
                                        <div class="agenda-day-group mb-4" data-date="{{ $dateKey }}">
                                            <div class="agenda-date-sticky d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                                <div class="agenda-date-badge {{ $day['is_today'] ? 'today' : '' }}">
                                                    <span class="agenda-day-num">{{ $day['day'] }}</span>
                                                    <span class="agenda-day-name">{{ $day['weekday'] }}</span>
                                                </div>
                                                <div class="agenda-date-info">
                                                    <h6 class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($dateKey)->format('l, F d, Y') }}</h6>
                                                    <small class="text-muted">{{ count($day['events']) }} event(s) scheduled</small>
                                                </div>
                                                @if($day['is_today'])
                                                    <span class="badge bg-success ms-auto">Today</span>
                                                @endif
                                            </div>

                                            <div class="agenda-events-list d-grid gap-2">
                                                @foreach($day['events'] as $event)
                                                    <div class="agenda-event-card {{ $event['type'] }}"
                                                         data-event-id="{{ $event['id'] }}"
                                                         data-category="{{ $event['type'] }}"
                                                         data-label="{{ strtolower($event['label'] . ' ' . $event['title'] . ' ' . ($event['employee_dept'] ?? '')) }}">
                                                        <div class="agenda-card-left">
                                                            @if(!empty($event['employee_avatar']))
                                                                <img src="{{ $event['employee_avatar'] }}" alt="{{ $event['employee_name'] }}" class="agenda-avatar">
                                                            @else
                                                                <div class="agenda-icon-placeholder {{ $event['type'] }}">
                                                                    <i class="link-icon" data-feather="{{ $event['type_icon'] ?? 'calendar' }}"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="agenda-card-body flex-grow-1">
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <h6 class="mb-0 fw-bold">{{ $event['title'] }}</h6>
                                                                    <span class="badge event-type-badge {{ $event['type'] }}">{{ $event['type_label'] }}</span>
                                                                    @if(!empty($event['status']) && $event['status'] !== 'celebration' && $event['status'] !== 'milestone')
                                                                        <span class="badge bg-light text-dark border">{{ ucfirst($event['status']) }}</span>
                                                                    @endif
                                                                </div>
                                                                <div class="text-muted small">
                                                                    @if(!empty($event['time']))
                                                                        <i class="link-icon me-1" data-feather="clock"></i>{{ $event['time'] }}
                                                                    @else
                                                                        <i class="link-icon me-1" data-feather="calendar"></i>{{ $event['duration'] ?? 'All Day' }}
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="agenda-card-details text-muted small d-flex align-items-center gap-3 flex-wrap">
                                                                @if(!empty($event['employee_dept']))
                                                                    <span><i class="link-icon me-1" data-feather="briefcase"></i>{{ $event['employee_dept'] }}</span>
                                                                @endif
                                                                @if(!empty($event['employee_branch']))
                                                                    <span><i class="link-icon me-1" data-feather="map-pin"></i>{{ $event['employee_branch'] }}</span>
                                                                @endif
                                                                @if(!empty($event['reason']))
                                                                    <span class="text-dark"><i class="link-icon me-1" data-feather="info"></i>{{ \Illuminate\Support\Str::limit($event['reason'], 60) }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="agenda-card-actions">
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    onclick="window.showEventDetailsModal('{{ $event['id'] }}')">
                                                                Details
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if(!$hasAnyEvents)
                                    <div class="text-center py-5">
                                        <i class="link-icon text-muted mb-2" data-feather="calendar" style="width:48px;height:48px;"></i>
                                        <h5 class="text-muted">No scheduled events found for this month</h5>
                                        <p class="text-muted small">Try adjusting your filters or select a different month.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar: Highlights, Selected Day & Legend -->
                    <div class="col-xl-3 col-lg-4 p-3 bg-light-subtle calendar-sidebar-pane">
                        <!-- Selected Day Widget -->
                        <div class="card border-0 shadow-sm mb-3 sidebar-widget-card" id="sidebarDayWidget">
                            <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                                <h6 class="card-title mb-0 fs-6 d-flex align-items-center gap-2">
                                    <i class="link-icon text-primary" data-feather="calendar"></i>
                                    <span id="selectedDayTitle">Day Schedule</span>
                                </h6>
                                <span class="badge bg-primary-subtle text-primary" id="selectedDayBadge">Today</span>
                            </div>
                            <div class="card-body p-2" id="selectedDayEventsContainer">
                                <!-- Rendered dynamically on day click or defaults to today -->
                                @php
                                    $todayEvents = $days[$todayKey]['events'] ?? [];
                                @endphp
                                @if(!empty($todayEvents))
                                    <div class="d-grid gap-2">
                                        @foreach($todayEvents as $evt)
                                            <div class="sidebar-event-item cursor-pointer p-2 rounded border bg-white"
                                                 onclick="window.showEventDetailsModal('{{ $evt['id'] }}')">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="event-type-dot {{ $evt['type'] }}"></span>
                                                    <strong class="small text-truncate flex-grow-1">{{ $evt['label'] }}</strong>
                                                    <span class="badge badge-xs bg-light text-dark">{{ $evt['type_label'] }}</span>
                                                </div>
                                                <div class="text-muted small text-truncate">{{ $evt['meta'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted small">
                                        <i class="link-icon mb-1" data-feather="smile"></i>
                                        <p class="mb-0">No events scheduled for today.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Upcoming Highlights Widget -->
                        <div class="card border-0 shadow-sm mb-3 sidebar-widget-card">
                            <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
                                <h6 class="card-title mb-0 fs-6 d-flex align-items-center gap-2">
                                    <i class="link-icon text-warning" data-feather="bell"></i>
                                    Upcoming Highlights
                                </h6>
                                <span class="badge bg-light text-secondary border">{{ count($upcomingEvents) }}</span>
                            </div>
                            <div class="card-body p-2" style="max-height: 380px; overflow-y: auto;">
                                @if(!empty($upcomingEvents))
                                    <div class="d-grid gap-2">
                                        @foreach(array_slice($upcomingEvents, 0, 8) as $upEvt)
                                            @php
                                                $evtDate = \Carbon\Carbon::parse($upEvt['date']);
                                                $diffDays = now()->startOfDay()->diffInDays($evtDate->startOfDay(), false);
                                                $timeTag = $diffDays == 0 ? 'Today' : ($diffDays == 1 ? 'Tomorrow' : ($diffDays > 1 ? "In {$diffDays} days" : $evtDate->format('M d')));
                                            @endphp
                                            <div class="upcoming-item p-2 rounded border bg-white cursor-pointer"
                                                 onclick="window.showEventDetailsModal('{{ $upEvt['id'] }}')">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="badge event-type-badge {{ $upEvt['type'] }}">{{ $upEvt['type_label'] }}</span>
                                                    <small class="text-muted fw-bold">{{ $timeTag }}</small>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if(!empty($upEvt['employee_avatar']))
                                                        <img src="{{ $upEvt['employee_avatar'] }}" class="rounded-circle" style="width:24px;height:24px;object-fit:cover" alt="">
                                                    @endif
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <strong class="d-block small text-truncate">{{ $upEvt['label'] }}</strong>
                                                        <small class="text-muted d-block text-truncate">{{ $upEvt['meta'] }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted small">
                                        <p class="mb-0">No upcoming events this month.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Calendar Legend Card -->
                        <div class="card border-0 shadow-sm sidebar-widget-card">
                            <div class="card-header bg-white py-2">
                                <h6 class="card-title mb-0 fs-6">Legend</h6>
                            </div>
                            <div class="card-body p-2">
                                <ul class="list-unstyled mb-0 d-grid gap-2 small">
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span><span class="cat-dot dot-birthday me-2"></span>Birthday</span>
                                        <span class="text-muted">{{ $summary['birthdays'] ?? 0 }}</span>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span><span class="cat-dot dot-leave me-2"></span>Leave</span>
                                        <span class="text-muted">{{ $summary['leave_requests'] ?? 0 }}</span>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span><span class="cat-dot dot-time-leave me-2"></span>Time Leave</span>
                                        <span class="text-muted">{{ $summary['time_leave_requests'] ?? 0 }}</span>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span><span class="cat-dot dot-anniversary me-2"></span>Anniversary</span>
                                        <span class="text-muted">{{ $summary['anniversaries'] ?? 0 }}</span>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span><span class="cat-dot dot-holiday me-2"></span>Public Holiday</span>
                                        <span class="text-muted">{{ $summary['holidays'] ?? 0 }}</span>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span><span class="cat-dot dot-event me-2"></span>Company Event</span>
                                        <span class="text-muted">{{ $summary['company_events'] ?? 0 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 1: EVENT DETAILS MODAL -->
        <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge fs-6 event-modal-type-badge" id="modalEventTypeBadge">Event</span>
                            <span class="badge bg-light text-dark border" id="modalEventStatusBadge">Status</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Employee / Host Header -->
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <img src="{{ asset('assets/images/img.png') }}"
                                 id="modalEmployeeAvatar"
                                 alt="Avatar"
                                 class="rounded-circle shadow-sm"
                                 style="width:58px;height:58px;object-fit:cover;">
                            <div class="overflow-hidden">
                                <h5 class="mb-1 fw-bold text-truncate" id="modalEmployeeName">Employee Name</h5>
                                <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
                                    <span id="modalEmployeeCode" class="badge bg-light text-secondary border"></span>
                                    <span id="modalEmployeeDept"></span>
                                    <span id="modalEmployeeBranch"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Event Information Grid -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-2 rounded bg-light">
                                    <small class="text-muted d-block mb-1">Date</small>
                                    <strong class="small text-dark" id="modalEventDate"></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded bg-light">
                                    <small class="text-muted d-block mb-1">Time / Duration</small>
                                    <strong class="small text-dark" id="modalEventDuration"></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Event Description / Reason -->
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1 fw-semibold">Description / Reason</label>
                            <div class="p-3 rounded bg-light text-dark small" id="modalEventReason" style="white-space: pre-line;">
                            </div>
                        </div>

                        <!-- Admin Remark (Optional) -->
                        <div class="mb-2 d-none" id="modalAdminRemarkWrapper">
                            <label class="form-label small text-muted mb-1 fw-semibold">Admin Remark</label>
                            <div class="p-2 rounded bg-warning-subtle text-dark small" id="modalAdminRemark">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="#" id="modalActionLink" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                            <span id="modalActionLinkLabel">View Details</span>
                            <i class="link-icon" data-feather="arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 2: DAY SCHEDULE MODAL -->
        <div class="modal fade" id="dayScheduleModal" tabindex="-1" aria-labelledby="dayScheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom py-3">
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="dayScheduleModalTitle">Day Schedule</h5>
                            <small class="text-muted" id="dayScheduleModalSubtitle">All events scheduled on this day</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3" style="max-height: 520px; overflow-y: auto;">
                        <div class="d-grid gap-2" id="dayScheduleModalList">
                            <!-- Populated dynamically via JavaScript -->
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Embedded Calendar Data for Client-Side Interactivity -->
    <script>
        window.employeeCalendarData = {
            monthLabel: @json($employeeCalendar['month_label'] ?? ''),
            monthValue: @json($employeeCalendar['month_value'] ?? ''),
            days: @json($days),
            weeks: @json($weeks),
            allEvents: @json($allEvents),
            upcomingEvents: @json($upcomingEvents),
            summary: @json($summary),
            todayKey: @json($todayKey),
            defaultAvatar: "{{ asset('assets/images/img.png') }}"
        };
    </script>

    <style>
        /* ============================================================
           EMPLOYEE CALENDAR PROFESSIONAL STYLES
           ============================================================ */
        .employee-calendar-page {
            --cal-primary: var(--primary-color, #0F766E);
            --cal-primary-hover: var(--hover-color, #115E59);
            --cal-pink: #ec4899;
            --cal-blue: #2563eb;
            --cal-amber: #f59e0b;
            --cal-purple: #8b5cf6;
            --cal-red: #ef4444;
            --cal-teal: #0d9488;
            --cal-border: #e2e8f0;
            --cal-bg-muted: #f8fafc;
        }

        /* KPI Metric Cards */
        .employee-calendar-kpi-row .kpi-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 14px 16px;
            border: 1px solid var(--cal-border);
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            transition: all 0.2s ease-in-out;
            overflow: hidden;
        }

        .employee-calendar-kpi-row .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }

        .employee-calendar-kpi-row .kpi-card.active {
            border-color: var(--cal-primary);
            box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.2);
            background: #f0fdfa;
        }

        .kpi-card .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kpi-card.total .kpi-icon { background: #e0f2fe; color: #0284c7; }
        .kpi-card.leave .kpi-icon { background: #dbeafe; color: var(--cal-blue); }
        .kpi-card.time-leave .kpi-icon { background: #fef3c7; color: #d97706; }
        .kpi-card.birthday .kpi-icon { background: #fce7f3; color: var(--cal-pink); }
        .kpi-card.anniversary .kpi-icon { background: #ede9fe; color: var(--cal-purple); }
        .kpi-card.holiday .kpi-icon { background: #fee2e2; color: var(--cal-red); }

        .kpi-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .kpi-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
        }

        .kpi-label {
            font-size: 0.76rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .kpi-sub-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 6px;
            background: #fef3c7;
            color: #b45309;
            font-weight: 600;
        }

        /* Calendar Controls Toolbar */
        .calendar-toolbar-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .calendar-nav-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .calendar-month-input {
            width: 154px;
            height: 38px;
            font-weight: 600;
            color: #334155;
        }

        .calendar-month-heading {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
        }

        .calendar-action-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .calendar-search-box {
            position: relative;
            width: 220px;
        }

        .calendar-search-box .search-icon {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            color: #94a3b8;
            pointer-events: none;
        }

        .calendar-search-box input {
            padding-left: 30px;
            padding-right: 26px;
            border-radius: 8px;
            font-size: 12px;
            height: 34px;
        }

        .calendar-search-box .btn-clear {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
        }

        /* Category Filter Buttons */
        .category-filter-btn {
            border-radius: 20px;
            border: 1px solid var(--cal-border);
            background: #ffffff;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            transition: all 0.15s ease;
        }

        .category-filter-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .category-filter-btn.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        .category-filter-btn.cat-birthday.active { background: var(--cal-pink); border-color: var(--cal-pink); }
        .category-filter-btn.cat-leave.active { background: var(--cal-blue); border-color: var(--cal-blue); }
        .category-filter-btn.cat-time-leave.active { background: #d97706; border-color: #d97706; }
        .category-filter-btn.cat-anniversary.active { background: var(--cal-purple); border-color: var(--cal-purple); }
        .category-filter-btn.cat-holiday.active { background: var(--cal-red); border-color: var(--cal-red); }
        .category-filter-btn.cat-event.active { background: var(--cal-teal); border-color: var(--cal-teal); }

        .cat-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }
        .dot-birthday { background: var(--cal-pink); }
        .dot-leave { background: var(--cal-blue); }
        .dot-time-leave { background: var(--cal-amber); }
        .dot-anniversary { background: var(--cal-purple); }
        .dot-holiday { background: var(--cal-red); }
        .dot-event { background: var(--cal-teal); }

        /* ============================================================
           CALENDAR MONTH GRID VIEW
           ============================================================ */
        .employee-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(130px, 1fr));
            gap: 1px;
            background: var(--cal-border);
            overflow-x: auto;
        }

        .employee-calendar-weekday {
            background: #f8fafc;
            padding: 10px 8px;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .employee-calendar-weekday.is-weekend {
            background: #f1f5f9;
            color: #64748b;
        }

        .employee-calendar-day {
            background: #ffffff;
            min-height: 132px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
            transition: background 0.15s ease;
        }

        .employee-calendar-day.empty-day {
            background: #fafafa;
        }

        .employee-calendar-day.is-weekend {
            background: #fafcff;
        }

        .employee-calendar-day:hover:not(.empty-day) {
            background: #f8fafc;
        }

        .employee-calendar-day.is-today {
            background: #f0fdfa !important;
            box-shadow: inset 0 0 0 2px var(--cal-primary);
        }

        .employee-calendar-day-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .day-number-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .day-number {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }

        .today-badge {
            font-size: 10px;
            font-weight: 700;
            background: var(--cal-primary);
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .day-events-count {
            font-size: 10px;
            font-weight: 600;
        }

        /* Event Chips on Day Cell */
        .employee-calendar-events {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
        }

        .employee-calendar-event-item {
            padding: 4px 6px;
            border-radius: 5px;
            font-size: 11px;
            line-height: 1.25;
            cursor: pointer;
            border-left: 3px solid #94a3b8;
            background: #f1f5f9;
            color: #0f172a;
            transition: all 0.15s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .employee-calendar-event-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            filter: brightness(0.96);
        }

        .employee-calendar-event-item.birthday {
            border-left-color: var(--cal-pink);
            background: #fdf2f8;
        }

        .employee-calendar-event-item.leave {
            border-left-color: var(--cal-blue);
            background: #eff6ff;
        }

        .employee-calendar-event-item.time_leave {
            border-left-color: var(--cal-amber);
            background: #fffbeb;
        }

        .employee-calendar-event-item.anniversary {
            border-left-color: var(--cal-purple);
            background: #f5f3ff;
        }

        .employee-calendar-event-item.holiday {
            border-left-color: var(--cal-red);
            background: #fef2f2;
        }

        .employee-calendar-event-item.company_event {
            border-left-color: var(--cal-teal);
            background: #f0fdfa;
        }

        .event-chip-content {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow: hidden;
        }

        .event-type-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .event-type-dot.birthday { background: var(--cal-pink); }
        .event-type-dot.leave { background: var(--cal-blue); }
        .event-type-dot.time_leave { background: var(--cal-amber); }
        .event-type-dot.anniversary { background: var(--cal-purple); }
        .event-type-dot.holiday { background: var(--cal-red); }
        .event-type-dot.company_event { background: var(--cal-teal); }

        .event-chip-title {
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #0f172a;
        }

        .event-chip-meta {
            font-size: 10px;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .btn-more-events {
            background: transparent;
            border: none;
            color: var(--cal-primary);
            font-size: 10px;
            font-weight: 700;
            text-align: left;
            padding: 2px 4px;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
        }
        .btn-more-events:hover {
            background: #e6fffa;
            text-decoration: underline;
        }

        /* ============================================================
           WEEK VIEW
           ============================================================ */
        .week-columns-wrapper {
            display: grid;
            grid-template-columns: repeat(7, minmax(130px, 1fr));
            gap: 12px;
            overflow-x: auto;
        }

        .week-col-card {
            background: #ffffff;
            border: 1px solid var(--cal-border);
            border-radius: 10px;
            padding: 10px;
            min-height: 480px;
            display: flex;
            flex-direction: column;
        }

        .week-col-card.is-today {
            border-color: var(--cal-primary);
            box-shadow: 0 0 0 1px var(--cal-primary);
            background: #fafefd;
        }

        .week-col-header {
            padding-bottom: 8px;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--cal-border);
            text-align: center;
        }

        .week-col-weekday {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        .week-col-daynum {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .week-events-stack {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .week-event-card {
            padding: 8px;
            border-radius: 8px;
            border-left: 3px solid #94a3b8;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .week-event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
        }

        .week-event-card.birthday { border-left-color: var(--cal-pink); background: #fdf2f8; }
        .week-event-card.leave { border-left-color: var(--cal-blue); background: #eff6ff; }
        .week-event-card.time_leave { border-left-color: var(--cal-amber); background: #fffbeb; }
        .week-event-card.anniversary { border-left-color: var(--cal-purple); background: #f5f3ff; }
        .week-event-card.holiday { border-left-color: var(--cal-red); background: #fef2f2; }
        .week-event-card.company_event { border-left-color: var(--cal-teal); background: #f0fdfa; }

        /* ============================================================
           AGENDA / TIMELINE VIEW
           ============================================================ */
        .agenda-timeline-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .agenda-date-badge {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #334155;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            line-height: 1.1;
            flex-shrink: 0;
        }

        .agenda-date-badge.today {
            background: var(--cal-primary);
            color: #ffffff;
        }

        .agenda-day-num {
            font-size: 16px;
            font-weight: 800;
        }

        .agenda-day-name {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .agenda-event-card {
            background: #ffffff;
            border: 1px solid var(--cal-border);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-left: 4px solid #94a3b8;
            transition: all 0.15s ease;
        }

        .agenda-event-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transform: translateX(2px);
        }

        .agenda-event-card.birthday { border-left-color: var(--cal-pink); }
        .agenda-event-card.leave { border-left-color: var(--cal-blue); }
        .agenda-event-card.time_leave { border-left-color: var(--cal-amber); }
        .agenda-event-card.anniversary { border-left-color: var(--cal-purple); }
        .agenda-event-card.holiday { border-left-color: var(--cal-red); }
        .agenda-event-card.company_event { border-left-color: var(--cal-teal); }

        .agenda-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
        }

        .agenda-icon-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .agenda-icon-placeholder.birthday { background: #fce7f3; color: var(--cal-pink); }
        .agenda-icon-placeholder.leave { background: #dbeafe; color: var(--cal-blue); }
        .agenda-icon-placeholder.time_leave { background: #fef3c7; color: #d97706; }
        .agenda-icon-placeholder.anniversary { background: #ede9fe; color: var(--cal-purple); }
        .agenda-icon-placeholder.holiday { background: #fee2e2; color: var(--cal-red); }
        .agenda-icon-placeholder.company_event { background: #ccfbf1; color: var(--cal-teal); }

        .event-type-badge.birthday { background: #fce7f3; color: #be185d; }
        .event-type-badge.leave { background: #dbeafe; color: #1d4ed8; }
        .event-type-badge.time_leave { background: #fef3c7; color: #b45309; }
        .event-type-badge.anniversary { background: #ede9fe; color: #6d28d9; }
        .event-type-badge.holiday { background: #fee2e2; color: #b91c1c; }
        .event-type-badge.company_event { background: #ccfbf1; color: #0f766e; }

        /* Sidebar Widgets */
        .sidebar-widget-card {
            border-radius: 10px;
        }

        .sidebar-event-item, .upcoming-item {
            transition: all 0.15s ease;
        }
        .sidebar-event-item:hover, .upcoming-item:hover {
            border-color: var(--cal-primary) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transform: translateX(2px);
        }

        /* Filter hide class */
        .event-hidden {
            display: none !important;
        }

        /* Print Optimization */
        @media print {
            .sidebar, .navbar, .page-header, .filter-wrapper, .employee-calendar-kpi-row,
            .calendar-action-group, .calendar-filter-pills, .calendar-sidebar-pane,
            .float-md-end {
                display: none !important;
            }
            .main-calendar-pane {
                width: 100% !important;
                border: none !important;
            }
            .employee-calendar-day {
                min-height: 90px !important;
            }
        }
    </style>
@endsection

@section('scripts')
    @include('admin.employees.common.scripts')
    <script>
        $(document).ready(function () {
            const data = window.employeeCalendarData || {};
            let activeCategory = 'all';
            let activeSearch = '';
            let currentWeekIndex = 0;

            // Initialize Feather Icons
            if (window.feather) {
                feather.replace();
            }

            // Find current week index based on today
            if (data.weeks && data.weeks.length) {
                const today = data.todayKey;
                data.weeks.forEach((wk, idx) => {
                    if (wk.some(d => d.date === today)) {
                        currentWeekIndex = idx;
                    }
                });
            }

            // Month Picker Change
            $('#employeeCalendarMonthPicker').on('change', function () {
                const month = $(this).val();
                if (!month) return;
                const form = document.getElementById('employeeFilterForm');
                if (form) {
                    $('#calendarMonth').val(month);
                    form.submit();
                } else {
                    const url = new URL(window.location.href);
                    url.searchParams.set('calendar_month', month);
                    window.location.href = url.toString();
                }
            });

            // View Switcher (Month, Week, Agenda)
            $('.view-switcher-tabs button').on('click', function () {
                const view = $(this).data('view');
                $('.view-switcher-tabs button').removeClass('active');
                $(this).addClass('active');

                $('.calendar-view-pane').addClass('d-none');
                if (view === 'month') {
                    $('#calendarMonthView').removeClass('d-none');
                } else if (view === 'week') {
                    $('#calendarWeekView').removeClass('d-none');
                    renderWeekView();
                } else if (view === 'agenda') {
                    $('#calendarAgendaView').removeClass('d-none');
                }

                if (window.feather) {
                    feather.replace();
                }
            });

            // Category Filter Pills & KPI Cards
            $('.category-filter-btn, .employee-calendar-kpi-row .filter-pill').on('click', function () {
                const cat = $(this).data('category');
                activeCategory = cat;

                $('.category-filter-btn').removeClass('active');
                $(`.category-filter-btn[data-category="${cat}"]`).addClass('active');

                $('.employee-calendar-kpi-row .kpi-card').removeClass('active');
                $(`.employee-calendar-kpi-row .filter-pill[data-category="${cat}"]`).addClass('active');

                applyFilters();
            });

            // Live Search Input
            $('#calendarLiveSearch').on('input', function () {
                activeSearch = $(this).val().trim().toLowerCase();
                if (activeSearch.length > 0) {
                    $('#calendarClearSearch').removeClass('d-none');
                } else {
                    $('#calendarClearSearch').addClass('d-none');
                }
                applyFilters();
            });

            $('#calendarClearSearch').on('click', function () {
                $('#calendarLiveSearch').val('');
                activeSearch = '';
                $(this).addClass('d-none');
                applyFilters();
            });

            // Apply Filters (Category & Search)
            function applyFilters() {
                // 1. Filter in Month View
                $('.employee-calendar-event-item').each(function () {
                    const itemCat = $(this).data('category');
                    const itemLabel = $(this).data('label') || '';

                    const matchesCat = (activeCategory === 'all') || (itemCat === activeCategory);
                    const matchesSearch = (!activeSearch) || (itemLabel.includes(activeSearch));

                    if (matchesCat && matchesSearch) {
                        $(this).removeClass('event-hidden');
                    } else {
                        $(this).addClass('event-hidden');
                    }
                });

                // 2. Filter in Week View
                $('.week-event-card').each(function () {
                    const itemCat = $(this).data('category');
                    const itemLabel = $(this).data('label') || '';

                    const matchesCat = (activeCategory === 'all') || (itemCat === activeCategory);
                    const matchesSearch = (!activeSearch) || (itemLabel.includes(activeSearch));

                    if (matchesCat && matchesSearch) {
                        $(this).removeClass('event-hidden');
                    } else {
                        $(this).addClass('event-hidden');
                    }
                });

                // 3. Filter in Agenda View
                $('.agenda-event-card').each(function () {
                    const itemCat = $(this).data('category');
                    const itemLabel = $(this).data('label') || '';

                    const matchesCat = (activeCategory === 'all') || (itemCat === activeCategory);
                    const matchesSearch = (!activeSearch) || (itemLabel.includes(activeSearch));

                    if (matchesCat && matchesSearch) {
                        $(this).removeClass('event-hidden');
                    } else {
                        $(this).addClass('event-hidden');
                    }
                });

                // Hide empty agenda groups if no child visible
                $('.agenda-day-group').each(function () {
                    const visibleCards = $(this).find('.agenda-event-card:not(.event-hidden)');
                    if (visibleCards.length === 0) {
                        $(this).addClass('event-hidden');
                    } else {
                        $(this).removeClass('event-hidden');
                    }
                });
            }

            // Render Week View
            function renderWeekView() {
                const weeks = data.weeks || [];
                if (!weeks.length) return;

                if (currentWeekIndex < 0) currentWeekIndex = 0;
                if (currentWeekIndex >= weeks.length) currentWeekIndex = weeks.length - 1;

                const week = weeks[currentWeekIndex];
                const startDateStr = week[0].date;
                const endDateStr = week[6].date;
                $('#weekRangeLabel').text(`${startDateStr} to ${endDateStr} (Week ${currentWeekIndex + 1} of ${weeks.length})`);

                let html = '';
                week.forEach(day => {
                    const dayEvents = day.events || [];
                    const isToday = day.is_today;
                    const isWeekend = day.is_weekend;

                    html += `
                        <div class="week-col-card ${isToday ? 'is-today' : ''}">
                            <div class="week-col-header">
                                <span class="week-col-weekday ${isWeekend ? 'text-danger' : ''}">${day.weekday}</span>
                                <div class="week-col-daynum">${day.day}</div>
                                ${isToday ? '<span class="today-badge">Today</span>' : ''}
                            </div>
                            <div class="week-events-stack">
                    `;

                    if (dayEvents.length === 0) {
                        html += `<div class="text-muted text-center py-4 small">No events</div>`;
                    } else {
                        dayEvents.forEach(evt => {
                            html += `
                                <div class="week-event-card ${evt.type}"
                                     data-event-id="${evt.id}"
                                     data-category="${evt.type}"
                                     data-label="${(evt.label + ' ' + evt.title + ' ' + (evt.employee_dept || '')).toLowerCase()}"
                                     onclick="window.showEventDetailsModal('${evt.id}')">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge badge-xs event-type-badge ${evt.type}">${evt.type_label}</span>
                                        ${evt.time ? `<small class="text-muted">${evt.time}</small>` : ''}
                                    </div>
                                    <strong class="small d-block text-truncate">${evt.label}</strong>
                                    <small class="text-muted d-block text-truncate">${evt.meta}</small>
                                </div>
                            `;
                        });
                    }

                    html += `
                            </div>
                        </div>
                    `;
                });

                $('#weekColumnsContainer').html(html);
                applyFilters();
                if (window.feather) feather.replace();
            }

            $('#prevWeekBtn').on('click', function () {
                if (currentWeekIndex > 0) {
                    currentWeekIndex--;
                    renderWeekView();
                }
            });

            $('#nextWeekBtn').on('click', function () {
                const weeks = data.weeks || [];
                if (currentWeekIndex < weeks.length - 1) {
                    currentWeekIndex++;
                    renderWeekView();
                }
            });

            // Day Cell Click to Select in Sidebar Widget
            $('.employee-calendar-day:not(.empty-day)').on('click', function (e) {
                if ($(e.target).closest('.employee-calendar-event-item, .btn-more-events').length) {
                    return; // Handled by specific event clicks
                }

                const dateStr = $(this).data('date');
                updateSelectedDaySidebar(dateStr);
            });

            function updateSelectedDaySidebar(dateStr) {
                const days = data.days || {};
                const dayData = days[dateStr];
                if (!dayData) return;

                $('#selectedDayTitle').text(dateStr);
                $('#selectedDayBadge').text(dayData.is_today ? 'Today' : dayData.weekday);

                const container = $('#selectedDayEventsContainer');
                const events = dayData.events || [];

                if (events.length === 0) {
                    container.html(`
                        <div class="text-center py-3 text-muted small">
                            <i class="link-icon mb-1" data-feather="smile"></i>
                            <p class="mb-0">No events scheduled on this date.</p>
                        </div>
                    `);
                } else {
                    let html = '<div class="d-grid gap-2">';
                    events.forEach(evt => {
                        html += `
                            <div class="sidebar-event-item cursor-pointer p-2 rounded border bg-white"
                                 onclick="window.showEventDetailsModal('${evt.id}')">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="event-type-dot ${evt.type}"></span>
                                    <strong class="small text-truncate flex-grow-1">${evt.label}</strong>
                                    <span class="badge badge-xs bg-light text-dark">${evt.type_label}</span>
                                </div>
                                <div class="text-muted small text-truncate">${evt.meta}</div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.html(html);
                }

                if (window.feather) feather.replace();
            }

            // Show Event Details Modal
            window.showEventDetailsModal = function (eventId) {
                const allEvents = data.allEvents || [];
                const event = allEvents.find(e => e.id === eventId);
                if (!event) return;

                $('#modalEventTypeBadge')
                    .text(event.type_label)
                    .attr('class', `badge fs-6 event-modal-type-badge event-type-badge ${event.type}`);

                const statusBadge = $('#modalEventStatusBadge');
                if (event.status && event.status !== 'celebration' && event.status !== 'milestone') {
                    statusBadge.text(event.status.toUpperCase()).removeClass('d-none');
                } else {
                    statusBadge.addClass('d-none');
                }

                $('#modalEmployeeAvatar').attr('src', event.employee_avatar || data.defaultAvatar);
                $('#modalEmployeeName').text(event.employee_name || event.label);
                
                if (event.employee_code) {
                    $('#modalEmployeeCode').text(`ID: ${event.employee_code}`).removeClass('d-none');
                } else {
                    $('#modalEmployeeCode').addClass('d-none');
                }

                $('#modalEmployeeDept').text(event.employee_dept ? `Dept: ${event.employee_dept}` : '');
                $('#modalEmployeeBranch').text(event.employee_branch ? `Branch: ${event.employee_branch}` : '');

                $('#modalEventDate').text(event.start_date ? `${event.start_date} ${event.end_date ? ' - ' + event.end_date : ''}` : event.date);
                $('#modalEventDuration').text(event.time || event.duration || 'All Day');
                $('#modalEventReason').text(event.reason || 'No description provided.');

                if (event.admin_remark) {
                    $('#modalAdminRemark').text(event.admin_remark);
                    $('#modalAdminRemarkWrapper').removeClass('d-none');
                } else {
                    $('#modalAdminRemarkWrapper').addClass('d-none');
                }

                const actionLink = $('#modalActionLink');
                if (event.url) {
                    actionLink.attr('href', event.url).removeClass('d-none');
                    $('#modalActionLinkLabel').text(event.url_label || 'View Record');
                } else {
                    actionLink.addClass('d-none');
                }

                const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
                modal.show();
                if (window.feather) feather.replace();
            };

            // Show Day Schedule Modal (+X more)
            window.showDayScheduleModal = function (dateStr) {
                const days = data.days || {};
                const dayData = days[dateStr];
                if (!dayData) return;

                $('#dayScheduleModalTitle').text(`Schedule for ${dateStr}`);
                $('#dayScheduleModalSubtitle').text(`${(dayData.events || []).length} event(s) scheduled`);

                let html = '';
                (dayData.events || []).forEach(evt => {
                    html += `
                        <div class="agenda-event-card ${evt.type}">
                            <div class="agenda-card-left">
                                <img src="${evt.employee_avatar || data.defaultAvatar}" class="agenda-avatar" alt="">
                            </div>
                            <div class="agenda-card-body flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="mb-0 fw-bold">${evt.title}</h6>
                                        <span class="badge event-type-badge ${evt.type}">${evt.type_label}</span>
                                    </div>
                                    <small class="text-muted">${evt.time || evt.duration || 'All Day'}</small>
                                </div>
                                <div class="text-muted small">
                                    ${evt.employee_dept ? `<span>Dept: ${evt.employee_dept}</span> | ` : ''}
                                    <span>${evt.meta}</span>
                                </div>
                            </div>
                            <div class="agenda-card-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="window.showEventDetailsModal('${evt.id}')">
                                    Details
                                </button>
                            </div>
                        </div>
                    `;
                });

                $('#dayScheduleModalList').html(html);
                const modal = new bootstrap.Modal(document.getElementById('dayScheduleModal'));
                modal.show();
                if (window.feather) feather.replace();
            };

            // Export Calendar as CSV
            $('#exportCalendarCsvBtn').on('click', function () {
                const allEvents = data.allEvents || [];
                if (!allEvents.length) {
                    alert('No events available to export.');
                    return;
                }

                let csvContent = 'data:text/csv;charset=utf-8,';
                csvContent += 'Date,Weekday,Event Type,Employee/Host,Employee Code,Department,Status,Duration/Time,Description\n';

                allEvents.forEach(evt => {
                    const clean = (val) => `"${(val || '').toString().replace(/"/g, '""')}"`;
                    const row = [
                        clean(evt.date),
                        clean(evt.weekday || ''),
                        clean(evt.type_label),
                        clean(evt.employee_name || evt.label),
                        clean(evt.employee_code || ''),
                        clean(evt.employee_dept || ''),
                        clean(evt.status || ''),
                        clean(evt.time || evt.duration || ''),
                        clean(evt.reason || '')
                    ];
                    csvContent += row.join(',') + '\n';
                });

                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', `Employee_Calendar_${data.monthValue || 'schedule'}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endsection
