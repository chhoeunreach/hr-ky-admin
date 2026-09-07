<div class="emp-overview-grid2">

    @if($canViewEmployment)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="briefcase"></i> Employment Summary</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('employment')">View Details</button>
            </div>
            <div class="emp-card-body">
                <div class="emp-info-grid">
                    <div><label>Department</label><span>{{ $overview['employment']['department'] ?: 'N/A' }}</span></div>
                    <div><label>Position</label><span>{{ $overview['employment']['position'] ?: 'N/A' }}</span></div>
                    <div><label>Branch</label><span>{{ $overview['employment']['branch'] ?: 'N/A' }}</span></div>
                    <div><label>Manager</label><span>{{ $overview['employment']['manager'] ?: 'N/A' }}</span></div>
                    <div><label>Employment Type</label><span>{{ $overview['employment']['employment_type'] ?: 'N/A' }}</span></div>
                    <div><label>Probation End</label><span>{{ $overview['employment']['probation_end_date'] ? \Illuminate\Support\Carbon::parse($overview['employment']['probation_end_date'])->format('d M Y') : 'N/A' }}</span></div>
                    <div><label>Joining Date</label><span>{{ $overview['employment']['joining_date'] ? \Illuminate\Support\Carbon::parse($overview['employment']['joining_date'])->format('d M Y') : 'N/A' }}</span></div>
                    <div><label>Years of Service</label><span>{{ $overview['employment']['years_of_service'] ?: 'N/A' }}</span></div>
                </div>
            </div>
        </div>
    @endif

    <div class="emp-overview-card">
        <div class="emp-card-head">
            <h6><i class="link-icon" data-feather="user"></i> Personal Data</h6>
            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('personal')">View Details</button>
        </div>
        <div class="emp-card-body">
            <div class="emp-info-grid">
                <div><label>Name (Khmer)</label><span>{{ $overview['personal']['name'] ?: 'N/A' }}</span></div>
                <div><label>Name (EN)</label><span>{{ $overview['personal']['english_name'] ?: 'N/A' }}</span></div>
                <div><label>Gender</label><span>{{ $overview['personal']['gender'] ? \Illuminate\Support\Str::title($overview['personal']['gender']) : 'N/A' }}</span></div>
                <div><label>Date of Birth</label><span>{{ $overview['personal']['dob'] ? \Illuminate\Support\Carbon::parse($overview['personal']['dob'])->format('d M Y') : 'N/A' }}</span></div>
                <div><label>Phone</label><span>{{ $overview['personal']['phone'] ?: 'N/A' }}</span></div>
                <div><label>Email</label><span>{{ $overview['personal']['email'] ?: 'N/A' }}</span></div>
                <div><label>Address</label><span>{{ $overview['personal']['address'] ?: 'N/A' }}</span></div>
                <div><label>Emergency Contact</label><span>{{ $overview['personal']['emergency_contact'] ?: 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    <div class="emp-overview-card">
        <div class="emp-card-head">
            <h6><i class="link-icon" data-feather="activity"></i> Attendance</h6>
            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('attendance')">View Details</button>
        </div>
        <div class="emp-card-body">
            <div class="emp-chart-block">
                <canvas id="attendanceChart" height="120"></canvas>
            </div>
            <div class="emp-info-grid">
                <div><label>Period</label><span>{{ $overview['attendance']['from'] ? \Illuminate\Support\Carbon::parse($overview['attendance']['from'])->format('d M Y') . ' → ' . \Illuminate\Support\Carbon::parse($overview['attendance']['to'])->format('d M Y') : 'N/A' }}</span></div>
                <div><label>Working Days</label><span>{{ $overview['attendance']['working_days'] }}</span></div>
                <div><label>Present</label><span>{{ $overview['attendance']['present'] }}</span></div>
                <div><label>Late</label><span>{{ $overview['attendance']['late'] }}</span></div>
                <div><label>Absent</label><span>{{ $overview['attendance']['absent'] }}</span></div>
                <div><label>Worked Hours</label><span>{{ $overview['attendance']['worked_hours'] ? number_format($overview['attendance']['worked_hours'], 1) . ' hrs' : 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    <div class="emp-overview-card">
        <div class="emp-card-head">
            <h6><i class="link-icon" data-feather="calendar"></i> Leave Balance</h6>
            <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('attendance')">View Details</button>
        </div>
        <div class="emp-card-body">
            @php
                $leavePct = $overview['leave']['allocated'] > 0 ? round(($overview['leave']['used'] / $overview['leave']['allocated']) * 100) : 0;
            @endphp
            <div class="emp-leave-summary">
                <div class="emp-leave-balance">
                    <div class="emp-leave-balance-value">{{ number_format($overview['leave']['balance'], 1) }}</div>
                    <div class="emp-leave-balance-label">Days Remaining</div>
                </div>
                <div class="emp-leave-bar">
                    <div class="progress emp-progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ min(100, $leavePct) }}%"><span class="sr-only">{{ $leavePct }}%</span></div>
                    </div>
                    <div class="emp-leave-bar-labels"><span>{{ $leavePct }}% used</span><span>{{ $overview['leave']['pending'] }} pending</span></div>
                </div>
            </div>
            <div class="emp-info-grid">
                <div><label>Allocated</label><span>{{ number_format($overview['leave']['allocated'], 1) }} days</span></div>
                <div><label>Used</label><span>{{ number_format($overview['leave']['used'], 1) }} days</span></div>
                <div><label>Balance</label><span class="text-primary fw-semibold">{{ number_format($overview['leave']['balance'], 1) }} days</span></div>
                <div><label>Pending Requests</label><span>{{ $overview['leave']['pending'] }}</span></div>
            </div>
        </div>
    </div>

    @if($canViewPerformance)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="bar-chart-2"></i> Performance</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('evaluation')">View Details</button>
            </div>
            <div class="emp-card-body">
                @if(isset($overview['performance']['score']))
                    <div class="emp-score-row">
                        <div class="emp-score-main">
                            <span class="emp-score-value">{{ number_format($overview['performance']['score'], 1) }}%</span>
                            <span class="emp-score-grade badge {{ $overview['performance']['grade'] ? 'bg-primary' : 'bg-secondary' }}">{{ $overview['performance']['grade'] ?: 'No Grade' }}</span>
                        </div>
                        <div class="emp-score-meta">
                            @if($overview['performance']['review_date'])
                                <div><label>Last Review</label><span>{{ \Illuminate\Support\Carbon::parse($overview['performance']['review_date'])->format('d M Y') }}</span></div>
                            @endif
                            @if($overview['performance']['reviewer'])
                                <div><label>Reviewer</label><span>{{ $overview['performance']['reviewer'] }}</span></div>
                            @endif
                            <div><label>Trend</label><span>{{ ucfirst($overview['performance']['trend']) }}</span></div>
                        </div>
                    </div>
                    @if($overview['performance']['chart'])
                        <div class="emp-chart-block">
                            <canvas id="performanceChart" height="120"></canvas>
                        </div>
                    @else
                        <div class="emp-empty">No review history available.</div>
                    @endif
                @else
                    <div class="emp-empty"><i class="link-icon" data-feather="bar-chart-2"></i> No performance review yet.</div>
                @endif
            </div>
        </div>
    @endif

    @if($canViewGoal)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="target"></i> Goals</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('goals')">View Details</button>
            </div>
            <div class="emp-card-body">
                <div class="emp-stat-row">
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['goals']['active'] }}</span><span class="emp-stat-label">Active</span></div>
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['goals']['completed'] }}</span><span class="emp-stat-label">Completed</span></div>
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['goals']['overdue'] }}</span><span class="emp-stat-label">Overdue</span></div>
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['goals']['avg_progress'] }}%</span><span class="emp-stat-label">Avg. Progress</span></div>
                </div>
                @if($overview['goals']['top']->isNotEmpty())
                    <div class="emp-goal-list">
                        @foreach($overview['goals']['top'] as $goal)
                            <div class="emp-goal-item">
                                <div class="emp-goal-top">
                                    <span class="emp-goal-title">{{ \Illuminate\Support\Str::limit($goal->title, 40) }}</span>
                                    <span class="emp-goal-pct">{{ $goal->progress }}%</span>
                                </div>
                                <div class="progress emp-progress">
                                    <div class="progress-bar {{ $goal->status === 'completed' ? 'bg-success' : ($goal->status === 'overdue' ? 'bg-danger' : ($goal->status === 'in_progress' ? 'bg-primary' : 'bg-secondary')) }}" role="progressbar" style="width: {{ min(100, (int) $goal->progress) }}%"></div>
                                </div>
                                <div class="emp-goal-meta">
                                    <span>{{ ucfirst(str_replace('_', ' ', $goal->status)) }}</span>
                                    @if($goal->due_date)
                                        <span>Due {{ \Illuminate\Support\Carbon::parse($goal->due_date)->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="emp-empty"><i class="link-icon" data-feather="target"></i> No goals defined yet.</div>
                @endif
            </div>
        </div>
    @endif

    @if($canViewSalary)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="dollar-sign"></i> Payroll</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('salary')">View Details</button>
            </div>
            <div class="emp-card-body">
                <div class="emp-info-grid">
                    <div><label>Base Salary</label><span>{{ $overview['payroll']['base_salary'] ? '$' . number_format($overview['payroll']['base_salary']) : 'N/A' }}</span></div>
                    <div><label>Allowances</label><span>{{ $overview['payroll']['allowances'] ? '$' . number_format($overview['payroll']['allowances']) : 'N/A' }}</span></div>
                    @if($overview['payroll']['last_adjustment'])
                        <div><label>Last Adjustment</label><span>{{ $overview['payroll']['last_adjustment']->effective_date ? \Illuminate\Support\Carbon::parse($overview['payroll']['last_adjustment']->effective_date)->format('d M Y') : 'N/A' }}</span></div>
                        <div><label>New Salary</label><span>${{ number_format($overview['payroll']['last_adjustment']->new_base_salary ?: $overview['payroll']['base_salary']) }}</span></div>
                    @else
                        <div><label>Last Adjustment</label><span>N/A</span></div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($canViewDocument)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="archive"></i> Documents</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('personal')">View Details</button>
            </div>
            <div class="emp-card-body">
                <div class="emp-info-grid">
                    <div><label>Total Documents</label><span>{{ $overview['documents']['total'] }}</span></div>
                    <div><label>Expiring Soon (30d)</label><span class="{{ $overview['documents']['expiring']->isNotEmpty() ? 'text-warning fw-semibold' : '' }}">{{ $overview['documents']['expiring']->count() }}</span></div>
                    <div><label>Expired</label><span class="{{ $overview['documents']['expired']->isNotEmpty() ? 'text-danger fw-semibold' : '' }}">{{ $overview['documents']['expired']->count() }}</span></div>
                    <div><label>Recent Upload</label><span>{{ $overview['documents']['recent']->isNotEmpty() ? \Illuminate\Support\Str::limit($overview['documents']['recent']->first()->title, 18) : 'N/A' }}</span></div>
                </div>
                @if($overview['documents']['expiring']->isNotEmpty() || $overview['documents']['expired']->isNotEmpty())
                    <div class="emp-doc-alert">
                        @foreach($overview['documents']['expired'] as $doc)
                            <div class="emp-doc-alert-item text-danger"><i class="link-icon" data-feather="alert-circle"></i> <span>{{ \Illuminate\Support\Str::limit($doc->title, 30) }}</span> <em>expired {{ $doc->expiry_date ? \Illuminate\Support\Carbon::parse($doc->expiry_date)->format('d M Y') : '' }}</em></div>
                        @endforeach
                        @foreach($overview['documents']['expiring'] as $doc)
                            <div class="emp-doc-alert-item text-warning"><i class="link-icon" data-feather="alert-triangle"></i> <span>{{ \Illuminate\Support\Str::limit($doc->title, 30) }}</span> <em>expires {{ $doc->expiry_date ? \Illuminate\Support\Carbon::parse($doc->expiry_date)->format('d M Y') : '' }}</em></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($canViewTraining)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="award"></i> Training</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('personal')">View Details</button>
            </div>
            <div class="emp-card-body">
                <div class="emp-stat-row">
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['training']['total'] }}</span><span class="emp-stat-label">Total Trainings</span></div>
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['training']['certificates'] }}</span><span class="emp-stat-label">Certificates</span></div>
                </div>
                @if($overview['training']['recent']->isNotEmpty())
                    <div class="emp-list">
                        @foreach($overview['training']['recent'] as $item)
                            <div class="emp-list-item">
                                <div class="emp-list-title">{{ \Illuminate\Support\Str::limit($item->training_title, 40) }}</div>
                                <div class="emp-list-meta">
                                    @if($item->training_date)
                                        <span>{{ \Illuminate\Support\Carbon::parse($item->training_date)->format('d M Y') }}</span>
                                    @endif
                                    @if(filled($item->certificate))
                                        <span class="text-success"><i class="link-icon" data-feather="check-circle"></i> Certificate</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="emp-empty"><i class="link-icon" data-feather="award"></i> No training records yet.</div>
                @endif
            </div>
        </div>
    @endif

    @if($canViewDiscipline)
        <div class="emp-overview-card">
            <div class="emp-card-head">
                <h6><i class="link-icon" data-feather="alert-octagon"></i> Disciplinary</h6>
                <button type="button" class="btn btn-outline-secondary btn-xs" onclick="employeeGotoTab('discipline')">View Details</button>
            </div>
            <div class="emp-card-body">
                <div class="emp-stat-row">
                    <div class="emp-stat"><span class="emp-stat-value {{ $overview['discipline']['active'] > 0 ? 'text-danger' : 'text-success' }}">{{ $overview['discipline']['active'] }}</span><span class="emp-stat-label">Active</span></div>
                    <div class="emp-stat"><span class="emp-stat-value">{{ $overview['discipline']['total'] }}</span><span class="emp-stat-label">Total</span></div>
                </div>
                @if($overview['discipline']['latest'])
                    <div class="emp-list">
                        <div class="emp-list-item">
                            <div class="emp-list-title">{{ \Illuminate\Support\Str::limit($overview['discipline']['latest']->title ?: $overview['discipline']['latest']->warning_type, 40) }}</div>
                            <div class="emp-list-meta">
                                @if($overview['discipline']['latest']->incident_date)
                                    <span>{{ \Illuminate\Support\Carbon::parse($overview['discipline']['latest']->incident_date)->format('d M Y') }}</span>
                                @endif
                                <span class="badge {{ $overview['discipline']['latest']->status === 'resolved' ? 'bg-success' : ($overview['discipline']['latest']->status === 'pending' || $overview['discipline']['latest']->status === 'active' ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst(str_replace('_', ' ', $overview['discipline']['latest']->status)) }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="emp-empty"><i class="link-icon" data-feather="check-circle"></i> No disciplinary records.</div>
                @endif
            </div>
        </div>
    @endif

</div>