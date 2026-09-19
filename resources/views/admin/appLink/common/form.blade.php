<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="name" class="form-label">Link Name <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required
               value="{{ isset($linkDetail) ? $linkDetail->name : old('name') }}"
               autocomplete="off" placeholder="e.g. Official Telegram Channel">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="link_type" class="form-label">Link Type <span style="color: red">*</span></label>
        <select class="form-select" id="link_type" name="link_type" required>
            <option value="" disabled {{ !isset($linkDetail) ? 'selected' : '' }}>Select Link Type</option>
            @foreach($linkTypes as $key => $label)
                <option value="{{ $key }}" {{ (isset($linkDetail) && $linkDetail->link_type == $key) || old('link_type') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="url" class="form-label">URL / Link <span style="color: red">*</span></label>
        <input type="url" class="form-control" id="url" name="url" required
               value="{{ isset($linkDetail) ? $linkDetail->url : old('url') }}"
               autocomplete="off" placeholder="https://t.me/yourchannel">
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="order" class="form-label">Display Order</label>
        <input type="number" class="form-control" id="order" name="order" min="0"
               value="{{ isset($linkDetail) ? $linkDetail->order : (old('order') ?? 0) }}"
               placeholder="0">
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="status" class="form-label">Status</label>
        <div class="mt-2">
            <label class="switch">
                <input type="checkbox" name="status" value="1" {{ (!isset($linkDetail) || $linkDetail->status) ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
            <span class="ms-2">Active</span>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <label for="image" class="form-label">Photo / Icon</label>
        <input class="form-control" type="file" id="image" name="image" accept=".jpeg,.png,.jpg,.webp,.svg">
        <div class="mt-3">
            <img class="{{ (isset($linkDetail) && $linkDetail->image) ? '' : 'd-none' }}"
                 id="image-preview"
                 src="{{ (isset($linkDetail) && $linkDetail->image) ? asset(\App\Models\AppLink::UPLOAD_PATH . $linkDetail->image) : '' }}"
                 style="object-fit: contain; max-height: 120px; border-radius: 8px; border: 1px solid #dee2e6; padding: 4px;"
                 width="120" height="120">
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <label for="description" class="form-label">Other / Description</label>
        <textarea class="form-control" name="description" id="description" rows="4"
                  placeholder="Additional notes or description">{{ isset($linkDetail) ? $linkDetail->description : old('description') }}</textarea>
    </div>

    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="{{ isset($linkDetail) ? 'check' : 'plus' }}"></i>
            {{ isset($linkDetail) ? 'Update Link' : 'Create Link' }}
        </button>
        <a href="{{ route('admin.app-links.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </div>
</div>
