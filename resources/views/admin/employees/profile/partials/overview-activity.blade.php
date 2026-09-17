<div class="emp-overview-grid2 emp-overview-grid2-bottom">
    <div class="emp-overview-card">
        <div class="emp-card-head">
            <h6><i class="link-icon" data-feather="clock"></i> {{ __('index.recent_activity') }}</h6>
            @can('employee.audit.view')
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('history')">{{ __('index.view_all') }}</button>
            @endcan
        </div>
        <div class="emp-card-body">
            @if($overview['activity']->isNotEmpty())
                <ul class="emp-timeline">
                    @foreach($overview['activity'] as $item)
                        <li>
                            <span class="emp-timeline-dot"></span>
                            <div class="emp-timeline-content">
                                <div class="emp-timeline-title">{{ $item['title'] }}</div>
                                <div class="emp-timeline-meta">
                                    @if($item['by'])
                                        <span><i class="link-icon" data-feather="user"></i> {{ $item['by'] }}</span>
                                    @endif
                                    @if($item['date'])
                                        <span><i class="link-icon" data-feather="clock"></i> {{ $item['date'] ? $item['date']->format('d M Y H:i') : '' }}</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="emp-empty"><i class="link-icon" data-feather="clock"></i> {{ __('index.no_recent_activity') }}</div>
            @endif
        </div>
    </div>

    <div class="emp-overview-card">
        <div class="emp-card-head">
            <h6><i class="link-icon" data-feather="compass"></i> {{ __('index.employee_360_summary') }}</h6>
        </div>
        <div class="emp-card-body">
            <div class="emp-360-summary">
                <div class="emp-360-summary-item">
                    <i class="link-icon text-primary" data-feather="briefcase"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['employment'] }}</span><span class="emp-360-summary-label">{{ __('index.employment') }}</span></div>
                </div>
                <div class="emp-360-summary-item">
                    <i class="link-icon text-success" data-feather="check-circle"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['attendance'] }}</span><span class="emp-360-summary-label">{{ __('index.attendance_rate') }}</span></div>
                </div>
                <div class="emp-360-summary-item">
                    <i class="link-icon text-warning" data-feather="bar-chart-2"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['performance'] }}</span><span class="emp-360-summary-label">{{ __('index.performance') }}</span></div>
                </div>
                <div class="emp-360-summary-item">
                    <i class="link-icon text-info" data-feather="calendar"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['leave'] }}</span><span class="emp-360-summary-label">{{ __('index.leave_balance') }}</span></div>
                </div>
                <div class="emp-360-summary-item">
                    <i class="link-icon text-primary" data-feather="target"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['goals'] }}</span><span class="emp-360-summary-label">{{ __('index.goals_progress') }}</span></div>
                </div>
                <div class="emp-360-summary-item">
                    <i class="link-icon text-success" data-feather="award"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['training'] }}</span><span class="emp-360-summary-label">{{ __('index.training') }}</span></div>
                </div>
                <div class="emp-360-summary-item">
                    <i class="link-icon {{ $overview['summary360']['discipline'] === 'Active Warning' ? 'text-danger' : 'text-success' }}" data-feather="shield"></i>
                    <div><span class="emp-360-summary-status">{{ $overview['summary360']['discipline'] }}</span><span class="emp-360-summary-label">{{ __('index.disciplinary') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>