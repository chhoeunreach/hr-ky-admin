<div class="emp-kpi-row">
    <div class="emp-kpi-card">
        <div class="emp-kpi-icon emp-kpi-teal"><i class="link-icon" data-feather="check-circle"></i></div>
        <div class="emp-kpi-body">
            <div class="emp-kpi-label">Attendance</div>
            <div class="emp-kpi-value">{{ $overview['attendance']['rate'] }}%<small>/ month</small></div>
            <div class="emp-kpi-sub">
                <span>Present {{ $overview['attendance']['present'] }}</span>
                <span>Late {{ $overview['attendance']['late'] }}</span>
                <span>Absent {{ $overview['attendance']['absent'] }}</span>
            </div>
        </div>
    </div>

    <div class="emp-kpi-card">
        <div class="emp-kpi-icon emp-kpi-blue"><i class="link-icon" data-feather="calendar"></i></div>
        <div class="emp-kpi-body">
            <div class="emp-kpi-label">Leave Balance</div>
            <div class="emp-kpi-value">{{ number_format($overview['leave']['balance'], 1) }}<small> days</small></div>
            <div class="emp-kpi-sub">
                <span>Used {{ number_format($overview['leave']['used'], 1) }}</span>
                <span>Allocated {{ number_format($overview['leave']['allocated'], 1) }}</span>
            </div>
        </div>
    </div>

    @if($canViewPerformance)
        <div class="emp-kpi-card">
            <div class="emp-kpi-icon emp-kpi-amber"><i class="link-icon" data-feather="bar-chart-2"></i></div>
            <div class="emp-kpi-body">
                <div class="emp-kpi-label">Performance</div>
                <div class="emp-kpi-value">
                    @if(isset($overview['performance']['score']))
                        {{ number_format($overview['performance']['score'], 1) }}<small>%</small>
                    @else
                        N/A
                    @endif
                </div>
                <div class="emp-kpi-sub">
                    @if($overview['performance']['grade'])
                        <span>Grade {{ $overview['performance']['grade'] }}</span>
                    @else
                        <span>No Review</span>
                    @endif
                    @if($overview['performance']['trend'] !== 'N/A')
                        <span>{{ ucfirst($overview['performance']['trend']) }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($canViewSalary)
        <div class="emp-kpi-card">
            <div class="emp-kpi-icon emp-kpi-green"><i class="link-icon" data-feather="dollar-sign"></i></div>
            <div class="emp-kpi-body">
                <div class="emp-kpi-label">Salary</div>
                <div class="emp-kpi-value">{{ $overview['payroll']['base_salary'] ? number_format($overview['payroll']['base_salary']) : 'N/A' }}<small>$ / month</small></div>
                <div class="emp-kpi-sub">
                    @if($overview['payroll']['allowances'])
                        <span>Allowance {{ number_format($overview['payroll']['allowances']) }}</span>
                    @endif
                    @if($overview['payroll']['last_adjustment'])
                        <span>Updated {{ $overview['payroll']['last_adjustment']->effective_date ? \Illuminate\Support\Carbon::parse($overview['payroll']['last_adjustment']->effective_date)->format('M Y') : '' }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="emp-kpi-card">
        <div class="emp-kpi-icon emp-kpi-purple"><i class="link-icon" data-feather="briefcase"></i></div>
        <div class="emp-kpi-body">
            <div class="emp-kpi-label">Years of Service</div>
            <div class="emp-kpi-value">{{ $overview['employment']['years_of_service'] ?: 'N/A' }}</div>
            <div class="emp-kpi-sub">
                <span>{{ $overview['employment']['employment_type'] ?: 'N/A' }}</span>
                <span>{{ ucfirst(str_replace('_', ' ', $overview['employment']['status'])) }}</span>
            </div>
        </div>
    </div>
</div>