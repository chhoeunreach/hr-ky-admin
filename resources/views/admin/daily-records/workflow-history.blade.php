<div class="card"><div class="card-body">
    @if ($files->isNotEmpty())
        <h2 class="h6">Attachments</h2>
        <ul>
            @foreach ($files as $file)
                <li><a href="{{ route('admin.daily-records.record-file', [$entry->id, $file->id]) }}">{{ $file->name }}</a></li>
            @endforeach
        </ul>
    @endif
    <h2 class="h6">Activity history</h2>
    <ol class="list-group list-group-flush">
        @forelse ($history as $event)
            <li class="list-group-item px-0">
                <div>{{ ucfirst(str_replace('_', ' ', $event->action)) }} · {{ \App\Modules\DailyRecords\RecordTime::display($event->created_at) }} ICT</div>
                @foreach (json_decode($event->details, true) ?? [] as $key => $value)
                    @if (is_scalar($value))
                        <div class="small text-break" style="white-space:pre-wrap">{{ ucfirst(str_replace('_',' ',$key)) }} · {{ $value }}</div>
                    @endif
                @endforeach
            </li>
        @empty
            <li class="list-group-item px-0">No activity yet.</li>
        @endforelse
    </ol>
    {{ $history->withQueryString()->links() }}
</div></div>
