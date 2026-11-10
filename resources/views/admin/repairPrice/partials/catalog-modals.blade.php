@can('repair_price.manage_devices')
    @foreach(['types' => 'Device Type', 'series' => 'Device Series'] as $entity => $label)
        @foreach(['add', 'edit'] as $mode)
            <div class="modal fade" id="{{ $mode }}Catalog{{ $entity }}Modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog"><div class="modal-content">
                    <form id="{{ $mode }}Catalog{{ $entity }}Form" method="post" @if($mode === 'add') action="{{ route('admin.repair-price.'.$entity.'.store') }}" @endif>
                        @csrf
                        @if($mode === 'edit') @method('PUT') @endif
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $mode === 'add' ? 'Add' : 'Edit' }} {{ $label }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            @if($entity === 'series')
                                <div class="mb-3">
                                    <label class="form-label">{{ __('index.brand') }} *</label>
                                    <select class="form-select" name="repair_brand_id" required>
                                        <option value="">{{ __('index.brand') }}</option>
                                        @foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('index.device_type') }} *</label>
                                    <select class="form-select" name="repair_device_type_id" required>
                                        <option value="">{{ __('index.device_type') }}</option>
                                        @foreach($deviceTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">{{ __('index.name') }} *</label>
                                <input class="form-control" name="name" required maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input class="form-control" name="slug" maxlength="255" placeholder="Auto generated if empty">
                            </div>
                            @if($entity === 'types')
                                <div class="mb-3"><label class="form-label">Icon</label><input class="form-control" name="icon" maxlength="255"></div>
                            @endif
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="status" value="1" checked><span class="form-check-label">{{ __('index.active') }}</span></label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ $mode === 'add' ? __('index.save') : __('index.update') }}</button>
                        </div>
                    </form>
                </div></div>
            </div>
        @endforeach
    @endforeach
@endcan
