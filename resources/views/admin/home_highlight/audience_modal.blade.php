<div class="modal fade hh-audience-modal" id="audience-modal-{{ $group['key'] }}" tabindex="-1" aria-labelledby="audience-title-{{ $group['key'] }}" aria-hidden="true" data-audience-modal data-group="{{ $group['key'] }}" data-label="{{ strtolower($group['label']) }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="audience-title-{{ $group['key'] }}">Select {{ strtolower($group['label']) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="audience-search-{{ $group['key'] }}">Search {{ strtolower($group['label']) }}</label>
                <input type="search" class="form-control" id="audience-search-{{ $group['key'] }}" placeholder="Search by name…" autocomplete="off" data-audience-search>
                <div class="hh-audience-toolbar">
                    <span class="text-muted small" data-audience-count aria-live="polite">{{ count($group['selected']) }} of {{ $group['options']->count() }} selected</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-select-all-audience>Select all</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-audience>Clear all</button>
                    </div>
                </div>
                <p class="small text-muted">Select all includes every option, even while searching. Empty selection includes all {{ strtolower($group['label']) }}.</p>
                <div class="hh-audience-options">
                    @foreach($group['options'] as $id => $label)
                        <label class="hh-audience-option" data-audience-option data-label="{{ $label }}">
                            <input type="checkbox" name="{{ $group['key'] }}[]" value="{{ $id }}" form="home-highlight-form" data-audience-value @checked(in_array((int) $id, $group['selected'], true))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    <p class="text-muted text-center py-4 mb-0" data-audience-empty @if($group['options']->isNotEmpty()) hidden @endif>No matching {{ strtolower($group['label']) }}.</p>
                </div>
            </div>
            <div class="modal-footer">
                <span class="small text-muted">Save the highlight to save your audience changes.</span>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
