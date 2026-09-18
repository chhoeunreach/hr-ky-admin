@can('employee.document.manage')
    <style>
        #employeeFormDocumentsModal .modal-dialog { max-width: 760px; }
        #employeeFormDocumentsModal .modal-title { font-size: 1rem; }
        #employeeFormDocumentsModal .employee-form-document-row { border-radius: 6px; }
        #employeeFormDocumentsModal .employee-form-document-row .form-label { margin-bottom: .2rem; }
        #employeeFormDocumentsModal .employee-form-document-row .form-control,
        #employeeFormDocumentsModal .employee-form-document-row .form-select { min-width: 0; }
        #employeeFormDocumentsModal .document-photo-frame { max-width: 340px; max-height: 180px; margin: auto; }
        #employeeFormDocumentsModal .document-photo-preview { display: block; max-width: 100%; max-height: 180px; margin: auto; }
        #employeeFormDocumentsModal .document-ocr details { min-width: 0; flex: 1; }
        #employeeFormDocumentsModal .ocr-text { max-width: 100%; max-height: 220px; overflow: auto; white-space: pre-wrap; overflow-wrap: anywhere; }
        @media (max-width: 767px) {
            #employeeFormDocumentsModal .modal-dialog { margin: .5rem; }
        }
    </style>
    <div class="modal fade" id="employeeFormDocumentsModal" tabindex="-1" aria-labelledby="employeeFormDocumentsTitle" aria-hidden="true"
         data-ocr-worker="{{ asset('assets/vendors/tesseract/worker.min.js') }}"
         data-ocr-core="{{ asset('assets/vendors/tesseract/core') }}"
         data-ocr-lang="{{ asset('assets/vendors/tesseract/lang') }}"
         data-photo-title="{{ __('index.photo') }}"
         data-original-label="{{ __('index.photo_original') }}"
         data-crop-label="{{ __('index.photo_crop') }}"
         data-cancel-label="{{ __('index.cancel') }}">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header flex-wrap gap-2 py-2 px-3">
                    <h5 class="modal-title" id="employeeFormDocumentsTitle">{{ __('index.add_documents') }}</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm ms-auto" id="employeeFormAddDocument">
                        <i data-feather="plus"></i> {{ __('index.add_document') }}
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2 p-sm-3">
                    <div id="employeeFormDocumentRows"></div>
                </div>
                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">{{ __('index.done') }}</button>
                </div>
            </div>
        </div>
    </div>

    <template id="employeeFormDocumentTemplate">
        <div class="border p-2 mb-2 employee-form-document-row">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <span class="small fw-semibold">{{ __('index.document') }} <span class="document-number"></span></span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-document" title="{{ __('index.delete_document') }}" aria-label="{{ __('index.delete_document') }}">
                    <i data-feather="trash-2"></i>
                </button>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ __('index.type') }}</label>
                    <select class="form-select form-select-sm document-type" name="documents[__INDEX__][document_type]">
                        @foreach(['national_id','employment_contract','cv','certificate','salary_letter','promotion_letter','warning_letter','performance_review','training_certificate','other'] as $item)
                            <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">{{ __('index.title') }}</label>
                    <input class="form-control form-control-sm document-title" name="documents[__INDEX__][title]" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">{{ __('index.file') }}</label>
                    <input class="form-control form-control-sm document-file" type="file" name="documents[__INDEX__][file]" accept="image/jpeg,image/png,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ __('index.document_date') }}</label>
                    <input class="form-control form-control-sm" type="date" name="documents[__INDEX__][document_date]">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ __('index.expiry_date') }}</label>
                    <input class="form-control form-control-sm" type="date" name="documents[__INDEX__][expiry_date]">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">{{ __('index.notes') }}</label>
                    <input class="form-control form-control-sm" name="documents[__INDEX__][note]">
                </div>
            </div>
            <div class="document-photo d-none border-top mt-2 pt-2">
                <div class="document-photo-frame">
                    <img class="document-photo-preview" alt="Selected document photo">
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm change-photo-mode" title="{{ __('index.change_photo_mode') }}">{{ __('index.change_photo_mode') }}</button>
                    <div class="document-rotate-tools d-none">
                        <button type="button" class="btn btn-outline-secondary btn-sm me-1 rotate-document-left" title="Rotate left" aria-label="Rotate left">
                            <i data-feather="rotate-ccw"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rotate-document-right" title="Rotate right" aria-label="Rotate right">
                            <i data-feather="rotate-cw"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm use-document-photo">{{ __('index.apply') }}</button>
                    <span class="small text-muted photo-status" aria-live="polite"></span>
                </div>
            </div>
            <div class="document-ocr d-none border-top mt-2 pt-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small">{{ __('index.khmer_name') }}</label>
                        <input class="form-control form-control-sm ocr-khmer" type="text" maxlength="100">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">{{ __('index.english_name') }}</label>
                        <input class="form-control form-control-sm ocr-english" type="text" maxlength="100">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary btn-sm apply-ocr-names">{{ __('index.apply_again') }}</button>
                    </div>
                </div>
                <div class="small text-muted mt-2 ocr-status" aria-live="polite"></div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <details class="small">
                        <summary>OCR JSON</summary>
                        <pre class="ocr-text border bg-light p-2 mt-1 mb-0"></pre>
                    </details>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm retry-document-ocr" title="Run OCR again" aria-label="Run OCR again" disabled>
                            <i data-feather="refresh-cw"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm copy-ocr-json" title="Copy OCR JSON" aria-label="Copy OCR JSON" disabled>
                            <i data-feather="copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endcan
