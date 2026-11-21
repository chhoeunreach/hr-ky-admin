<form action="{{ $reviewAction }}" method="POST" class="p-3 bg-light border rounded d-grid gap-2 mb-3">
    @csrf
    @isset($expectedReportId)<input type="hidden" name="expected_report_id" value="{{ $expectedReportId }}">@endisset
    <div class="dr-form-group">
        <label class="dr-form-label">
            <i class="fa-solid fa-clipboard-check me-1"></i> {{ $extension ? __('daily_records.extension_decision_notes') : __('daily_records.report_review_feedback') }}
        </label>
        <textarea class="dr-form-control" name="message" rows="2" placeholder="{{ $extension ? __('daily_records.extension_placeholder') : __('daily_records.report_review_placeholder') }}" maxlength="10000">{{ old('message') }}</textarea>
    </div>
    <div class="d-flex flex-wrap gap-2 pt-1">
        <button class="dr-btn-primary btn-sm py-1 px-3" type="submit" name="action" value="approve">
            <i class="fa-solid fa-check"></i> {{ __('daily_records.approve') }}
        </button>
        <button class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1 py-1 px-3" type="submit" name="action" value="return">
            <i class="fa-solid fa-rotate-left"></i> {{ $extension ? __('daily_records.decline_request') : __('daily_records.return_for_correction') }}
        </button>
    </div>
</form>
