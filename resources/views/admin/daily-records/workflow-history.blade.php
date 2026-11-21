<div class="dr-card">
    <div class="dr-card-header">
        <h2 class="dr-card-title">
            <i class="fa-solid fa-clock-rotate-left text-primary"></i> {{ __('daily_records.audit_event_history') }}
        </h2>
    </div>
    <div class="dr-card-body">
        @if ($files->isNotEmpty())
            <div class="mb-4">
                <h3 class="dr-form-label"><i class="fa-solid fa-paperclip me-1"></i> {{ __('daily_records.attached_documents') }} ({{ $files->count() }})</h3>
                <div class="d-grid gap-1">
                    @foreach ($files as $file)
                        <a class="p-2 border rounded bg-light text-decoration-none d-flex align-items-center gap-2" href="{{ route('admin.daily-records.record-file', [$entry->id, $file->id]) }}">
                            <i class="fa-regular fa-file text-primary"></i>
                            <span class="flex-grow-1 text-dark fw-semibold small">{{ $file->name }}</span>
                            <i class="fa-solid fa-download text-muted small"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="dr-timeline" style="padding-left: 1.5rem;">
            @forelse ($history as $event)
                <div class="dr-timeline-item mb-3">
                    <div class="dr-timeline-node" style="width: 18px; height: 18px; left: -1.5rem; top: 2px;">
                        <i class="fa-solid fa-circle" style="font-size: 0.4rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="fw-bold small text-dark">{{ ucfirst(str_replace('_', ' ', $event->action)) }}</span>
                            <span class="dr-meta-badge" style="font-size: 0.72rem;">
                                {{ \App\Modules\DailyRecords\RecordTime::display($event->created_at) }} ICT
                            </span>
                        </div>
                        @foreach (json_decode($event->details, true) ?? [] as $key => $value)
                            @if (is_scalar($value))
                                <div class="small text-muted text-break mt-1" style="white-space: pre-wrap;">
                                    <strong>{{ ucfirst(str_replace('_',' ',$key)) }}:</strong> {{ $value }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted small mb-0">{{ __('daily_records.no_audit_history') }}</p>
            @endforelse
        </div>
        @if ($history->hasPages())
            <div class="pt-2 border-top">
                {{ $history->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
