@can('employee.document.manage')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.documents.store', $employee->id) }}" class="employee-360-section" id="employeeDocumentsForm"
          data-ocr-worker="{{ asset('assets/vendors/tesseract/worker.min.js') }}"
          data-ocr-core="{{ asset('assets/vendors/tesseract/core') }}"
          data-ocr-lang="{{ asset('assets/vendors/tesseract/lang') }}">
        @csrf
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0">{{ __('index.add_documents') }}</h6>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addEmployeeDocumentRow"><i data-feather="plus"></i> {{ __('index.add_document') }}</button>
        </div>
        <div id="employeeDocumentRows"></div>
        <div id="employeeDocumentOcrFields"></div>
        <button type="submit" class="btn btn-primary">{{ __('index.upload_documents') }}</button>
    </form>

    <template id="employeeDocumentRowTemplate">
        <div class="border rounded p-3 mb-3 employee-document-row">
            <div class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-4"><label class="form-label">{{ __('index.type') }}</label><select class="form-select document-type" name="documents[__INDEX__][document_type]">@foreach(['national_id','employment_contract','cv','certificate','salary_letter','promotion_letter','warning_letter','performance_review','training_certificate','other'] as $item)<option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>@endforeach</select></div>
                <div class="col-lg-3 col-md-8"><label class="form-label">{{ __('index.title') }}</label><input class="form-control document-title" name="documents[__INDEX__][title]" required></div>
                <div class="col-lg-2 col-md-4"><label class="form-label">{{ __('index.document_date') }}</label><input class="form-control" type="date" name="documents[__INDEX__][document_date]"></div>
                <div class="col-lg-2 col-md-4"><label class="form-label">{{ __('index.expiry_date') }}</label><input class="form-control document-expiry" type="date" name="documents[__INDEX__][expiry_date]"></div>
                <div class="col-lg-3 col-md-4"><label class="form-label">{{ __('index.file') }}</label><input class="form-control document-file" type="file" name="documents[__INDEX__][file]" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx" required></div>
                <div class="col-md-10"><label class="form-label">{{ __('index.notes') }}</label><input class="form-control document-note" name="documents[__INDEX__][note]"></div>
                <div class="col-md-2 text-md-end"><button type="button" class="btn btn-outline-danger btn-sm remove-document-row" title="Remove document"><i data-feather="trash-2"></i></button></div>
            </div>
            <div class="small text-muted mt-2 document-file-choice" aria-live="polite"></div>
            <div class="document-ocr-result d-none mt-3 border-top pt-2">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold">{{ __('index.extracted_text') }}</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm retry-document-ocr" title="Read photo again"><i data-feather="refresh-cw"></i></button>
                </div>
                <pre class="document-ocr-text small mt-2 mb-0 p-2 bg-light" style="max-height:180px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere"></pre>
            </div>
        </div>
    </template>

    <div class="modal fade" id="employeeDocumentPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Prepare Photo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="d-flex gap-3 mb-3">
                        <label><input type="radio" name="document_photo_mode" value="original" checked> Original</label>
                        <label><input type="radio" name="document_photo_mode" value="crop"> Crop</label>
                    </div>
                    <div style="max-height:55vh;overflow:hidden"><img id="employeeDocumentPhotoPreview" alt="Selected document photo" style="display:block;max-width:100%;max-height:55vh;margin:auto"></div>
                    <p class="small text-muted mt-2 mb-0">For a National ID, text recognition will suggest ID details and the expiry date after you use this photo. Review the fields before saving.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button><button type="button" class="btn btn-primary" id="useEmployeeDocumentPhoto">Use Photo</button></div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('assets/vendors/tesseract/tesseract.min.js') }}"></script>
    <script src="{{ asset('assets/js/employee-profile-documents.js') }}?v={{ filemtime(public_path('assets/js/employee-profile-documents.js')) }}"></script>
@endcan

<div class="table-responsive"><table class="table table-sm employee-360-table"><thead><tr><th>{{ __('index.type') }}</th><th>{{ __('index.title') }}</th><th>{{ __('index.date') }}</th><th>{{ __('index.expiry_date') }}</th><th>{{ __('index.file') }}</th><th>{{ __('index.notes') }}</th></tr></thead><tbody>
@forelse($documents as $record)<tr><td>{{ \Illuminate\Support\Facades\Lang::has('index.' . $record->document_type) ? __('index.' . $record->document_type) : ucfirst(str_replace('_', ' ', $record->document_type)) }}</td><td>{{ $record->title }}</td><td>{{ optional($record->document_date)->format('Y-m-d') }}</td><td>{{ optional($record->expiry_date)->format('Y-m-d') }}</td><td>@if($record->file_path)<a href="{{ route('admin.employees.profile.documents.download', [$employee->id, $record->id]) }}">{{ __('index.download') }}</a>@else N/A @endif</td><td>{{ $record->note }}</td></tr>@empty<tr><td colspan="6" class="text-center">{{ __('index.no_records_found') }}</td></tr>@endforelse
</tbody></table></div>
