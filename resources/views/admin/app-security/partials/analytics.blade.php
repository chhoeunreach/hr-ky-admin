@if($canViewAnalytics && $analytics)
    @php
        $maxInstallations = max(1, (int) $analytics['installations']->max('count'));
        $riskTotal = max(1, array_sum($analytics['risk_distribution']));
        $maxEvents = max(1, max($analytics['events']));
    @endphp
    <div class="row g-3 mb-4" aria-label="Security analytics for {{ $analytics['from'] }} through {{ $analytics['to'] }}">
        <div class="col-xl-6">
            <div class="security-card">
                <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Installation trend</h5><span class="small text-muted">14 days · {{ $analytics['from'] }} to {{ $analytics['to'] }}</span></div>
                <div class="d-flex align-items-end gap-1" style="height:150px" role="img" aria-label="Daily new device registrations for the last two weeks">
                    @foreach($analytics['installations'] as $day)
                        <div class="flex-fill text-center" title="{{ $day['date'] }}: {{ $day['count'] }} registrations">
                            <div class="bg-primary rounded-top mx-auto" style="height:{{ max(3, (int) round(118 * $day['count'] / $maxInstallations)) }}px;max-width:28px"></div>
                            <div class="small text-muted mt-1" style="font-size:9px">{{ substr($day['date'], 5) }}</div>
                            <div class="small fw-semibold" style="font-size:10px">{{ $day['count'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="security-card">
                <h5>Current risk distribution</h5>
                <p class="small text-muted">Registered installations by current policy thresholds</p>
                @foreach(['high' => ['High', 'bg-danger'], 'medium' => ['Medium', 'bg-warning'], 'low' => ['Low', 'bg-success']] as $level => [$label, $color])
                    <div class="d-flex justify-content-between small"><span>{{ $label }}</span><strong>{{ number_format($analytics['risk_distribution'][$level]) }}</strong></div>
                    <div class="progress mb-3" role="progressbar" aria-label="{{ $label }} risk share" aria-valuenow="{{ round(100 * $analytics['risk_distribution'][$level] / $riskTotal) }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar {{ $color }}" style="width:{{ round(100 * $analytics['risk_distribution'][$level] / $riskTotal) }}%"></div></div>
                @endforeach
                <div class="small text-muted">{{ number_format($analytics['suspicious_registrations']) }} new registrations met the review threshold during this period.</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="security-card">
                <h5>Security events</h5><p class="small text-muted">Last 14 days</p>
                @foreach($analytics['events'] as $label => $value)
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2"><span class="small">{{ $label }}</span><span class="badge {{ $value > 0 ? 'bg-danger' : 'bg-secondary' }}">{{ number_format($value) }}</span></div>
                    <div class="progress mb-2" style="height:4px"><div class="progress-bar {{ $value > 0 ? 'bg-danger' : 'bg-secondary' }}" style="width:{{ round(100 * $value / $maxEvents) }}%"></div></div>
                @endforeach
            </div>
        </div>
    </div>
@endif
