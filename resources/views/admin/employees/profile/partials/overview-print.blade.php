@php
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '____/____/________';
    $line = fn ($value) => filled($value) ? $value : '____________';
    $warningRecords = $discipline ?? collect();
    $generatedDate = now()->format('Y-m-d');
    $monthStart = now()->startOfMonth();
    $monthEnd = now()->endOfMonth();
    $totalWarnings = $warningRecords->count();
    $thisMonthWarnings = $warningRecords->filter(fn ($record) => $record->incident_date && $record->incident_date->between($monthStart, $monthEnd))->count();
    $writtenWarnings = $warningRecords->whereIn('record_type', ['written_warning', 'final_warning'])->count();
    $finalWarnings = $warningRecords->filter(fn ($record) => $record->record_type === 'final_warning' || strtolower((string) $record->warning_level) === 'final')->count();
    $pendingFollowUp = $warningRecords->whereNotIn('status', ['resolved', 'cancelled'])->count();
    $statusCounts = $warningRecords->groupBy(fn ($record) => $record->status ?: 'draft')->map->count();
    $acknowledgedCount = $warningRecords->filter(fn ($record) => filled($record->employee_acknowledged_at))->count();
    $typeLabels = [
        'verbal_warning' => 'Verbal Warning / ព្រមានមាត់',
        'written_warning' => 'Written Warning / ព្រមានជាលាយលក្ខណ៍អក្សរ',
        'final_warning' => 'Final Written Warning / ព្រមានចុងក្រោយ',
        'disciplinary_action' => 'Disciplinary Notice / លិខិតវិន័យ',
        'suspension' => 'Suspension / ផ្អាកការងារ',
        'other' => 'Other / ផ្សេងៗ',
    ];
    $typeCounts = $warningRecords->groupBy(fn ($record) => $record->record_type ?: 'other')->map->count();
    $severityCounts = $warningRecords->groupBy(fn ($record) => $record->severity ?: 'Other / ផ្សេងៗ')->map->count();
    $recentWarnings = $warningRecords->take(8);
@endphp

<div class="employee-complete-toolbar mt-3">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="printOverviewForm()">
        <i class="link-icon" data-feather="printer"></i> Overview On Print
    </button>
</div>

<div class="employee-complete-paper employee-overview-paper mt-3">
    <div class="employee-complete-header">
        <img class="employee-complete-logo" src="{{ $avatar }}" alt="{{ $employee->name }}">
        <div class="employee-overview-title">Staff Warning Overview</div>
        <div class="employee-overview-subtitle">ទិដ្ឋភាពទូទៅ — ការព្រមាន និងវិន័យបុគ្គលិក</div>
        <span class="employee-complete-confidential">CONFIDENTIAL HR RECORD</span>
    </div>

    <div class="employee-complete-meta">
        <div>
            <small>Report Period / រយៈពេល</small>
            <strong>{{ now()->startOfMonth()->format('Y-m-d') }} → {{ now()->endOfMonth()->format('Y-m-d') }}</strong>
        </div>
        <div>
            <small>Branch / សាខា</small>
            <strong>{{ $line($employee->branch?->name) }}</strong>
        </div>
        <div>
            <small>Generated Date / កាលបរិច្ឆេទ</small>
            <strong>{{ $generatedDate }}</strong>
        </div>
        <div>
            <small>Employee / បុគ្គលិក</small>
            <strong>{{ $line($employee->name) }}</strong>
        </div>
        <div>
            <small>Department / ផ្នែក</small>
            <strong>{{ $line($employee->department?->dept_name) }}</strong>
        </div>
        <div>
            <small>Employee ID</small>
            <strong>{{ $line($employee->employee_code ?: $employee->username) }}</strong>
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>1. KEY SUMMARY / សង្ខេបសំខាន់</h6>
        <div class="employee-overview-summary">
            <div><small>Total Warnings<br>សរុប</small><strong>{{ $totalWarnings }}</strong></div>
            <div><small>This Month<br>ខែនេះ</small><strong>{{ $thisMonthWarnings }}</strong></div>
            <div><small>Written<br>លាយលក្ខណ៍អក្សរ</small><strong>{{ $writtenWarnings }}</strong></div>
            <div><small>Final<br>ចុងក្រោយ</small><strong>{{ $finalWarnings }}</strong></div>
            <div><small>Pending Follow-up<br>រង់ចាំតាមដាន</small><strong>{{ $pendingFollowUp }}</strong></div>
        </div>
    </div>

    <div class="employee-complete-section">
        <h6>2. WARNING STATUS / ស្ថានភាព</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <tbody>
            <tr>
                <td>Draft / ព្រាង<br><strong>Count: {{ $statusCounts->get('draft', 0) }}</strong></td>
                <td>Issued / បានចេញ<br><strong>Count: {{ $statusCounts->get('active', 0) }}</strong></td>
                <td>Acknowledged / ទទួលស្គាល់<br><strong>Count: {{ $acknowledgedCount }}</strong></td>
                <td>Resolved / បានដោះស្រាយ<br><strong>Count: {{ $statusCounts->get('resolved', 0) }}</strong></td>
            </tr>
            <tr>
                <td>Under Review / កំពុងពិនិត្យ<br><strong>Count: {{ $statusCounts->get('under_review', 0) }}</strong></td>
                <td>Escalated / បញ្ជូនបន្ត<br><strong>Count: {{ $statusCounts->get('escalated', 0) }}</strong></td>
                <td>Cancelled / លុបចោល<br><strong>Count: {{ $statusCounts->get('cancelled', 0) }}</strong></td>
                <td>Open / កំពុងបើក<br><strong>Count: {{ $pendingFollowUp }}</strong></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>3. WARNING TYPE / ប្រភេទការព្រមាន</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <thead><tr><th>Type / ប្រភេទ</th><th>Count</th><th>%</th><th>Remarks</th></tr></thead>
            <tbody>
            @foreach($typeLabels as $key => $label)
                @php
                    $count = $typeCounts->get($key, 0);
                    $percent = $totalWarnings > 0 ? round(($count / $totalWarnings) * 100) . '%' : '0%';
                @endphp
                <tr><td>{{ $label }}</td><td>{{ $count }}</td><td>{{ $percent }}</td><td>____________</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>4. VIOLATION CATEGORY / ប្រភេទកំហុស</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <thead><tr><th>Category</th><th>Count</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($severityCounts as $severity => $count)
                <tr><td>{{ ucfirst(str_replace('_', ' ', $severity)) }}</td><td>{{ $count }}</td><td>____________</td></tr>
            @empty
                <tr><td>Attendance / Late / វត្តមាន ឬយឺត</td><td>___</td><td>____________</td></tr>
                <tr><td>Work Performance / លទ្ធផលការងារ</td><td>___</td><td>____________</td></tr>
                <tr><td>Company Policy / គោលការណ៍</td><td>___</td><td>____________</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>5. DEPARTMENT / BRANCH ANALYSIS / វិភាគតាមផ្នែក និងសាខា</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <thead><tr><th>Department / Branch</th><th>Total</th><th>This Month</th><th>Open</th><th>Resolved</th></tr></thead>
            <tbody>
            <tr>
                <td>{{ $line($employee->department?->dept_name) }} / {{ $line($employee->branch?->name) }}</td>
                <td>{{ $totalWarnings }}</td>
                <td>{{ $thisMonthWarnings }}</td>
                <td>{{ $pendingFollowUp }}</td>
                <td>{{ $statusCounts->get('resolved', 0) }}</td>
            </tr>
            @for($i = 0; $i < 3; $i++)
                <tr><td>________________</td><td>___</td><td>___</td><td>___</td><td>___</td></tr>
            @endfor
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>6. REPEATED WARNING EMPLOYEES / បុគ្គលិកមានការព្រមានជាបន្តបន្ទាប់</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <thead><tr><th>Employee</th><th>Department</th><th>Warnings</th><th>Latest Warning</th><th>Status</th></tr></thead>
            <tbody>
            <tr>
                <td>{{ $line($employee->name) }}</td>
                <td>{{ $line($employee->department?->dept_name) }}</td>
                <td>{{ $totalWarnings }}</td>
                <td>{{ $formatDate($warningRecords->first()?->incident_date) }}</td>
                <td>{{ $line($warningRecords->first()?->status) }}</td>
            </tr>
            @for($i = 0; $i < 3; $i++)
                <tr><td>____________</td><td>____________</td><td>___</td><td>____/____/____</td><td>____________</td></tr>
            @endfor
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>7. UPCOMING FOLLOW-UP / ការតាមដានជិតដល់</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <thead><tr><th>Employee</th><th>Warning</th><th>Follow-up Date</th><th>Responsible</th><th>Status</th></tr></thead>
            <tbody>
            @for($i = 0; $i < 4; $i++)
                <tr><td>____________</td><td>____________</td><td>____/____/____</td><td>____________</td><td>____________</td></tr>
            @endfor
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>8. RECENT WARNINGS / ការព្រមានថ្មីៗ</h6>
        <table class="table table-sm employee-overview-table mb-0">
            <thead><tr><th>Warning No.</th><th>Employee</th><th>Type</th><th>Category</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($recentWarnings as $record)
                <tr>
                    <td>W-{{ str_pad((string) $record->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $record->record_type)) }}</td>
                    <td>{{ $line($record->severity) }}</td>
                    <td>{{ $formatDate($record->incident_date) }}</td>
                    <td>{{ $line($record->status) }}</td>
                </tr>
            @empty
                @for($i = 0; $i < 5; $i++)
                    <tr><td>W-_____</td><td>____________</td><td>____________</td><td>____________</td><td>____/____/____</td><td>____________</td></tr>
                @endfor
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="employee-complete-section">
        <h6>9. MANAGEMENT NOTES / កំណត់សម្គាល់</h6>
        <div class="employee-warning-lines">_________________________________________________________________________________________________________<br>_________________________________________________________________________________________________________<br>_________________________________________________________________________________________________________</div>
    </div>

    <div class="employee-overview-signatures">
        <div class="employee-complete-signature"><strong>Prepared By / រៀបចំដោយ</strong><br><br>Signature: __________________<br>Date: ____/____/________</div>
        <div class="employee-complete-signature"><strong>Checked By / ត្រួតពិនិត្យដោយ</strong><br><br>Signature: __________________<br>Date: ____/____/________</div>
        <div class="employee-complete-signature"><strong>Approved By / អនុម័តដោយ</strong><br><br>Signature: __________________<br>Date: ____/____/________</div>
    </div>

    <div class="employee-overview-footer">CONFIDENTIAL HR DOCUMENT / ឯកសារធនធានមនុស្ស — រក្សាការសម្ងាត់</div>
</div>
