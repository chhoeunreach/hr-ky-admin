<form action="{{ $reviewAction }}" method="POST" class="border rounded p-2 d-grid gap-2 mb-3">
    @csrf
    @isset($expectedReportId)<input type="hidden" name="expected_report_id" value="{{ $expectedReportId }}">@endisset
    <label>{{ $extension ? 'Extension decision' : 'Report review' }}
        <textarea class="form-control" name="message" placeholder="{{ $extension ? 'Explain a declined request' : 'Explain what needs correction' }}" maxlength="10000">{{ old('message') }}</textarea>
    </label>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-sm btn-primary" type="submit" name="action" value="approve">Approve</button>
        <button class="btn btn-sm btn-outline-warning" type="submit" name="action" value="return">{{ $extension ? 'Decline' : 'Return for correction' }}</button>
    </div>
</form>
