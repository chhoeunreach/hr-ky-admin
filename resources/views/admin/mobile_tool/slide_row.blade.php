@php($isTemplate = !is_numeric($index))
<div class="mt-slide">
    <div class="mt-slide-head">
        <span class="mt-slide-title">{{ $isTemplate ? 'New slide' : 'Slide '.($index + 1) }}</span>
        <div class="mt-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-move-up onclick="moveSlide(this.closest('.mt-slide'), -1)">Move up</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-move-down onclick="moveSlide(this.closest('.mt-slide'), 1)">Move down</button>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSlide(this.closest('.mt-slide'))" aria-label="Remove slide">
            <i data-feather="trash-2" style="width:14px;height:14px"></i> Remove
        </button>
        </div>
    </div>
    <input type="hidden" name="slides[{{ $index }}][id]" value="{{ old("slides.$index.id", $slide['id'] ?? '') }}" data-slide-id>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="slide_image_{{ $index }}">Upload image</label>
            <input class="form-control" type="file" id="slide_image_{{ $index }}" name="slides[{{ $index }}][image]" accept="image/jpeg,image/png,image/gif,image/webp" data-slide-image>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slide_url_{{ $index }}">…or image URL</label>
            <input class="form-control" type="text" id="slide_url_{{ $index }}" name="slides[{{ $index }}][imageUrl]" maxlength="2048" data-slide-image-url
                   value="{{ old("slides.$index.imageUrl", $slide['imageUrl'] ?? '') }}" placeholder="https://example.com/banner.jpg">
            <small class="text-muted">Leave empty and upload nothing to remove the image.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="slide_title_{{ $index }}">Title</label>
            <input class="form-control" type="text" id="slide_title_{{ $index }}" name="slides[{{ $index }}][title]" maxlength="180" data-slide-title
                   value="{{ old("slides.$index.title", $slide['title'] ?? '') }}" placeholder="Optional caption">
            <label class="form-label mt-2" for="slide_link_{{ $index }}">Link URL (optional)</label>
            <input class="form-control" type="text" id="slide_link_{{ $index }}" name="slides[{{ $index }}][linkUrl]" maxlength="2048"
                   value="{{ old("slides.$index.linkUrl", $slide['linkUrl'] ?? '') }}" placeholder="https://…">
        </div>
        <div class="col-12">
            @include('admin.mobile_tool.banner_preview')
        </div>
        <div class="col-md-12">
            <div class="form-check ms-1">
                <input type="hidden" name="slides[{{ $index }}][enabled]" value="0">
                <input class="form-check-input" type="checkbox" data-slide-enabled name="slides[{{ $index }}][enabled]" value="1"
                       id="slide_enabled_{{ $index }}" @checked(old("slides.$index.enabled", $slide['enabled'] ?? true))>
                <label class="form-check-label" for="slide_enabled_{{ $index }}">Visible on mobile</label>
            </div>
        </div>
    </div>
</div>
