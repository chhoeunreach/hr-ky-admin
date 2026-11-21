@if($canViewAnalytics && $analytics)
    @php
        $maxInstallations = max(1, (int) $analytics['installations']->max('count'));
        $riskTotal = max(1, array_sum($analytics['risk_distribution']));
        $maxEvents = max(1, max($analytics['events']));
    @endphp
    <div class="row g-3 mb-4" aria-label="Security analytics for {{ $analytics['from'] }} through {{ $analytics['to'] }}">
        <div class="col-xl-6">
            <div class="security-panel-card h-100">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="fa-solid fa-chart-column text-primary"></i>
                        <span>{{ __('app_security.step_analytics') }} · {{ __('app_security.installations') }}</span>
                    </h5>
                    <span class="badge bg-light text-muted border px-2 py-1">14 days · {{ $analytics['from'] }} → {{ $analytics['to'] }}</span>
                </div>
                <div class="panel-body">
                    <div class="d-flex align-items-end gap-2 pt-3" style="height:170px" role="img" aria-label="Daily new device registrations for the last two weeks">
                        @foreach($analytics['installations'] as $day)
                            @php $pct = max(4, (int) round(130 * $day['count'] / $maxInstallations)); @endphp
                            <div class="flex-fill text-center d-flex flex-column align-items-center justify-content-end h-100" title="{{ $day['date'] }}: {{ $day['count'] }} registrations">
                                <span class="small fw-bold text-dark mb-1" style="font-size:10px">{{ $day['count'] }}</span>
                                <div class="w-100 rounded-3 transition-all" style="height:{{ $pct }}px; max-width:28px; background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);"></div>
                                <div class="text-muted mt-2" style="font-size:9.5px; font-weight:600">{{ substr($day['date'], 5) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="security-panel-card h-100">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="fa-solid fa-gauge-high text-warning"></i>
                        <span>Risk Distribution</span>
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">Fleet</span>
                </div>
                <div class="panel-body">
                    <p class="small text-muted mb-3">Installations segmented by current threat policy thresholds.</p>
                    @foreach(['high' => ['High Risk', 'bg-danger', '#ef4444'], 'medium' => ['Medium Risk', 'bg-warning', '#f59e0b'], 'low' => ['Low Risk', 'bg-success', '#10b981']] as $level => [$label, $colorClass, $hex])
                        @php
                            $count = $analytics['risk_distribution'][$level] ?? 0;
                            $percentage = round(100 * $count / $riskTotal);
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width:8px; height:8px; background:{{ $hex }}"></span>
                                    {{ $label }}
                                </span>
                                <div>
                                    <strong class="text-dark">{{ number_format($count) }}</strong>
                                    <span class="text-muted small">({{ $percentage }}%)</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar {{ $colorClass }}" style="width:{{ $percentage }}%; border-radius: 10px;"></div>
                            </div>
                        </div>
                    @endforeach
                    <div class="p-2 rounded-3 bg-light border small text-muted mt-3">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                        <strong>{{ number_format($analytics['suspicious_registrations']) }}</strong> flagged registrations in window.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="security-panel-card h-100">
                <div class="panel-header">
                    <h5 class="panel-title">
                        <i class="fa-solid fa-shield-virus text-danger"></i>
                        <span>Security Telemetry</span>
                    </h5>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">14 Days</span>
                </div>
                <div class="panel-body">
                    <p class="small text-muted mb-3">High-severity signals and authorization rejections.</p>
                    <div class="d-flex flex-column gap-2">
                        @foreach($analytics['events'] as $label => $value)
                            @php $evPct = round(100 * $value / $maxEvents); @endphp
                            <div class="p-2 rounded-3 border {{ $value > 0 ? 'bg-danger bg-opacity-10 border-danger border-opacity-25' : 'bg-light border-light' }}">
                                <div class="d-flex align-items-center justify-content-between small">
                                    <span class="fw-semibold {{ $value > 0 ? 'text-danger' : 'text-secondary' }}">{{ $label }}</span>
                                    <span class="badge {{ $value > 0 ? 'bg-danger' : 'bg-secondary' }} px-2 py-1">{{ number_format($value) }}</span>
                                </div>
                                <div class="progress mt-2" style="height: 4px; background: rgba(0,0,0,0.05);">
                                    <div class="progress-bar {{ $value > 0 ? 'bg-danger' : 'bg-secondary' }}" style="width:{{ $evPct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
