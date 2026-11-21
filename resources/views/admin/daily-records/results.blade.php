<div class="row g-2">
    @foreach ($summary as $label => $count)
        <div class="col-6 col-sm-4 col-xl"><div class="card h-100"><div class="card-body p-3">
            <div class="small text-muted">{{ $label }}</div>
            <div class="h5 mb-0">{{ number_format($count) }}</div>
        </div></div></div>
    @endforeach
</div>

@if ($timeline)
    <div class="card"><div class="card-body">
        <h2 class="h5">Employee work timeline (ICT)</h2>
        <ol class="list-group list-group-flush mb-3">
            @forelse ($timeline as $activity)
                <li class="list-group-item px-0">
                    <time>{{ \App\Modules\DailyRecords\RecordTime::display($activity->created_at) }}</time>
                    · {{ $activity->employee }} · {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                    <a href="{{ request()->fullUrlWithQuery(['assignment' => $activity->entry_id ? null : $activity->assignment_id, 'entry' => $activity->entry_id, 'history' => null]) }}">{{ $activity->title }}</a>
                    @if ($activity->status)<span class="small text-muted"> · {{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>@endif
                </li>
            @empty
                <li class="list-group-item px-0">No matching activity.</li>
            @endforelse
        </ol>
        {{ $timeline->links() }}
    </div></div>
@endif

@if ($groups)
    <div class="card"><div class="card-body">
        <h2 class="h5">{{ ['performance' => 'Employee operational indicators', 'department' => 'Department summary', 'location' => 'Location summary'][$mode] }}</h2>
        @if ($mode === 'performance')
            <p class="small text-muted">Use these indicators to review work and its supporting records.</p>
        @endif
        <div class="table-responsive"><table class="table align-middle mb-3">
            <thead><tr><th>{{ ucfirst($mode === 'performance' ? 'Employee' : $mode) }}</th><th>Assigned</th><th>Approved</th><th>On-time completion</th><th>Overdue</th><th>Returned</th><th>Awaiting review</th><th>Incidents resolved</th></tr></thead>
            <tbody>
            @forelse ($groups as $group)
                @php
                    $field = ['performance' => 'employee_id', 'department' => 'department_id', 'location' => 'branch_id'][$mode];
                @endphp
                <tr>
                    <th scope="row">
                        @if ($group->group_id)
                            <a href="{{ route('admin.daily-records.index', array_merge($filters, ['view' => $mode === 'performance' ? 'monthly' : 'tasks', $field => $group->group_id])) }}">{{ $group->label }}</a>
                        @else Unspecified @endif
                    </th>
                    <td>{{ $group->new_assignments }}</td><td>{{ $group->approved }}</td>
                    <td>{{ $group->approved ? number_format(100 * $group->on_time / $group->approved, 1).'%' : '—' }}</td>
                    <td>{{ $group->overdue }}</td><td>{{ $group->returned }}</td><td>{{ $group->submitted }}</td><td>{{ $group->resolved }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No matching activity.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        {{ $groups->links() }}
    </div></div>
@elseif ($mode === 'calendar')
    <div class="card"><div class="card-body">
        <h2 class="h5 mb-3">Task deadlines (ICT)</h2>
        @forelse ($tasks->getCollection()->groupBy(fn ($task) => \App\Modules\DailyRecords\RecordTime::day($task->due_at)) as $day => $agenda)
            <section class="mb-3 border rounded p-3">
                <h3 class="h6">{{ $day }}</h3>
                <ul class="mb-0">
                    @foreach ($agenda as $item)
                        <li>{{ substr(\App\Modules\DailyRecords\RecordTime::display($item->due_at), 11) }}
                            <a href="{{ request()->fullUrlWithQuery(['assignment' => $item->id]) }}">{{ $item->title }}</a>
                            · {{ $item->employee }} · {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p>No deadlines match the selected month.</p>
        @endforelse
        {{ $tasks->links() }}
    </div></div>
@else
    <div class="card"><div class="card-body">
        <h2 class="h5 mb-3">Employee tasks</h2>
        <form method="POST" action="{{ route('admin.daily-records.bulk-review') }}">
            @csrf
            <div class="table-responsive"><table class="table align-middle mb-3">
                <thead><tr><th><span class="visually-hidden">Select</span></th><th>Employee</th><th>Department</th><th>Location</th><th>Task</th><th>Priority</th><th>Assigned (ICT)</th><th>Deadline (ICT)</th><th>Status</th><th>Progress</th><th>Last update (ICT)</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse ($tasks as $task)
                    @php
                        $overdue = !$task->completed_at && !$task->archived_at && !$task->superseded_at
                            && in_array($task->status, \App\Modules\DailyRecords\Tasks::INCOMPLETE)
                            && $task->due_at < $overdueAt;
                    @endphp
                    <tr>
                        <td>
                            @if ($task->pending_report_id && !$task->archived_at)
                                <input type="checkbox" class="form-check-input" name="approvals[{{ $task->id }}]" value="{{ $task->pending_report_id }}" aria-label="Select {{ $task->title }} for {{ $task->employee }}">
                            @endif
                        </td>
                        <th scope="row"><a href="{{ route('admin.daily-records.index', ['view' => 'timeline', 'employee_id' => $task->employee_id]) }}">{{ $task->employee }}</a></th>
                        <td>{{ $task->department }}</td><td>{{ $task->location }}</td><td>{{ $task->title }}</td><td>{{ ucfirst($task->priority) }}</td>
                        <td>{{ \App\Modules\DailyRecords\RecordTime::display($task->created_at) }}</td>
                        <td>{{ \App\Modules\DailyRecords\RecordTime::display($task->due_at) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }} @if ($overdue)<span class="badge bg-danger">Overdue</span>@endif</td>
                        <td>{{ $task->progress }}%</td>
                        <td>{{ \App\Modules\DailyRecords\RecordTime::display($task->updated_at) }}</td>
                        <td><a href="{{ request()->fullUrlWithQuery(['assignment' => $task->id, 'entry' => null, 'fragment' => null]) }}">{{ $task->status === 'submitted' ? 'Open report' : 'Open task' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="12">No matching assignments.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if ($tasks->getCollection()->contains('status', 'submitted'))
                <button class="btn btn-sm btn-outline-primary mb-3" type="submit">Approve selected reports</button>
            @endif
        </form>
        {{ $tasks->links() }}
    </div></div>
    <div class="card"><div class="card-body">
        <h2 class="h5 mb-3">Daily records</h2>
        <div class="table-responsive"><table class="table align-middle mb-3">
            <thead><tr><th>Employee</th><th>Record</th><th>Type</th><th>Status</th><th>Occurrence (ICT)</th><th>Last update (ICT)</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td><a href="{{ route('admin.daily-records.index', ['view' => 'timeline', 'employee_id' => $entry->employee_id]) }}">{{ $entry->employee }}</a></td>
                    <td>{{ $entry->title }} @if ($entry->critical)<span class="badge bg-danger">Critical</span>@endif</td>
                    <td>{{ ucfirst($entry->kind) }}</td><td>{{ ucfirst($entry->status ?? '—') }}</td>
                    <td>{{ \App\Modules\DailyRecords\RecordTime::display($entry->occurred_at) }}</td>
                    <td>{{ \App\Modules\DailyRecords\RecordTime::display($entry->updated_at) }}</td>
                    <td><a href="{{ $entry->kind === 'incident' ? route('admin.daily-records.incident', $entry->id) : request()->fullUrlWithQuery(['entry' => $entry->id, 'assignment' => null, 'fragment' => null]) }}">Open record</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No matching records.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        {{ $entries->links() }}
    </div></div>
@endif
