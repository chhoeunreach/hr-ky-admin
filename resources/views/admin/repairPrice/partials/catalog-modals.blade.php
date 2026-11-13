@can('repair_price.manage_devices')
    @foreach(['types', 'series'] as $entity)
        @foreach(['add', 'edit'] as $mode)
            <div class="modal fade management-modal" id="{{ $mode }}Catalog{{ $entity }}Modal" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $mode }}Catalog{{ $entity }}Title">
                <div class="modal-dialog"><div class="modal-content">
                    <form id="{{ $mode }}Catalog{{ $entity }}Form" method="post" @if($mode === 'add') action="{{ route('admin.repair-price.'.$entity.'.store') }}" @endif>
                        @csrf
                        @if($mode === 'edit') @method('PUT') @endif
                        <div class="modal-header">
                            <h5 class="modal-title" id="{{ $mode }}Catalog{{ $entity }}Title">{{ __('repair.'.($mode === 'add' ? 'add_' : 'edit_').($entity === 'types' ? 'type' : 'series')) }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            @if($entity === 'series')
                                <div class="mb-3">
                                    <label class="form-label" for="{{ $mode }}Catalog{{ $entity }}-repair_brand_id">{{ __('index.brand') }} *</label>
                                    <select class="form-select" id="{{ $mode }}Catalog{{ $entity }}-repair_brand_id" name="repair_brand_id" required>
                                        <option value="">{{ __('index.brand') }}</option>
                                        @foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="{{ $mode }}Catalog{{ $entity }}-repair_device_type_id">{{ __('index.device_type') }} *</label>
                                    <select class="form-select" id="{{ $mode }}Catalog{{ $entity }}-repair_device_type_id" name="repair_device_type_id" required>
                                        <option value="">{{ __('index.device_type') }}</option>
                                        @foreach($deviceTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label" for="{{ $mode }}Catalog{{ $entity }}-name">{{ __('index.name') }} *</label>
                                <input class="form-control" id="{{ $mode }}Catalog{{ $entity }}-name" name="name" required maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="{{ $mode }}Catalog{{ $entity }}-slug">{{ __('repair.slug') }}</label>
                                <input class="form-control" id="{{ $mode }}Catalog{{ $entity }}-slug" name="slug" maxlength="255" placeholder="{{ __('repair.auto_slug') }}">
                            </div>
                            @if($entity === 'types')
                                <div class="mb-3"><label class="form-label" for="{{ $mode }}Catalog{{ $entity }}-icon">{{ __('repair.icon') }}</label><input class="form-control" id="{{ $mode }}Catalog{{ $entity }}-icon" name="icon" maxlength="255"></div>
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
