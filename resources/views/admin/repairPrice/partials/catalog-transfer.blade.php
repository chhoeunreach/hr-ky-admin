<div class="d-flex align-items-center flex-wrap gap-2 px-3 py-2 border-bottom no-print">
    <button type="button" class="directory-tool-button" data-bs-toggle="modal" data-bs-target="#catalogImport-{{ $catalogEntity }}"><i data-feather="upload"></i>{{ __('repair.catalog_import') }}</button>
    <a class="directory-tool-button" href="{{ route('admin.repair-price.catalog.export', ['entity' => $catalogEntity]) }}"><i data-feather="download"></i>{{ __('repair.catalog_export') }}</a>
    <a class="directory-tool-button" href="{{ route('admin.repair-price.catalog.export', ['entity' => $catalogEntity, 'template' => 1]) }}"><i data-feather="file-text"></i>{{ __('repair.catalog_template') }}</a>
</div>
<div class="modal fade" id="catalogImport-{{ $catalogEntity }}" tabindex="-1" aria-labelledby="catalogImportTitle-{{ $catalogEntity }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <form method="post" action="{{ route('admin.repair-price.catalog.import', ['entity' => $catalogEntity]) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="catalogImportTitle-{{ $catalogEntity }}">{{ __('repair.catalog_import') }} · {{ $catalogTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">{{ __('repair.catalog_import_help') }}</p>
                <a class="btn btn-sm btn-outline-primary mb-3" href="{{ route('admin.repair-price.catalog.export', ['entity' => $catalogEntity, 'template' => 1]) }}">{{ __('repair.catalog_template') }}</a>
                <label class="form-label" for="catalogFile-{{ $catalogEntity }}">{{ __('repair.catalog_file') }}</label>
                <input class="form-control" id="catalogFile-{{ $catalogEntity }}" name="file" type="file" accept=".csv,.xlsx,.xls" required aria-describedby="catalogHelp-{{ $catalogEntity }}">
                <p class="small text-muted mt-2" id="catalogHelp-{{ $catalogEntity }}">{{ __('repair.catalog_reference_help') }}</p>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="catalogUpdate-{{ $catalogEntity }}" name="update_existing" value="1">
                    <label class="form-check-label" for="catalogUpdate-{{ $catalogEntity }}">{{ __('repair.catalog_update_existing') }}</label>
                </div>
                <p class="small text-muted mt-2 mb-0">{{ __('repair.catalog_matching_help') }}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                <button class="btn btn-primary" type="submit">{{ __('repair.catalog_import') }}</button>
            </div>
        </form>
    </div></div>
</div>
