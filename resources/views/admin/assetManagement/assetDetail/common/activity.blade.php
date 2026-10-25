<div class="card mt-4">
    <div class="card-header"><h6 class="mb-0">{{ __('index.asset_activity_history') }}</h6></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr>
                    <th>{{ __('index.date') }}</th><th>{{ __('index.asset_name') }}</th>
                    <th>{{ __('index.action') }}</th><th>{{ __('index.asset_activity_actor') }}</th>
                    <th>{{ __('index.asset_activity_before') }}</th><th>{{ __('index.asset_activity_after') }}</th>
                </tr></thead>
                <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $activity->asset_name }} (#{{ $activity->asset_id }})</td>
                        <td>{{ __('index.'.$activity->event) }}</td>
                        <td>{{ $activity->actor_name ?: __('index.not_available') }}</td>
                        @foreach([$activity->before, $activity->after] as $snapshot)
                            <td>
                                @foreach($snapshot ?? [] as $field => $value)
                                    <div class="mb-1"><strong>{{ \Illuminate\Support\Str::headline($field) }}:</strong>
                                        <span style="white-space: pre-wrap; overflow-wrap: anywhere">{{ is_scalar($value) ? strip_tags((string) $value) : '—' }}</span>
                                    </div>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('index.asset_activity_empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $activities->links() }}
    </div>
</div>
