<div class="employee-360-section">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h6 class="mb-0"><i class="link-icon" data-feather="file-text" style="width:16px;height:16px;"></i> {{ __('index.documents') }}</h6>
            <small class="text-muted">{{ count($documents) }} {{ __('index.records') }}</small>
        </div>
        @can('employee.document.manage')
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeDocumentModal">
                <i data-feather="plus" style="width:14px;height:14px;"></i> {{ __('index.add_document') }}
            </button>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered employee-360-table align-middle">
            <thead>
            <tr>
                <th style="width: 140px;">{{ __('index.type') }}</th>
                <th>{{ __('index.title') }}</th>
                <th class="text-center" style="width: 110px;">{{ __('index.date') }}</th>
                <th class="text-center" style="width: 110px;">{{ __('index.expiry_date') }}</th>
                <th>{{ __('index.notes') }}</th>
                <th class="text-center" style="width: 160px;">{{ __('index.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($documents as $record)
                @php
                    $fileExt = strtolower(pathinfo($record->file_path ?? '', PATHINFO_EXTENSION));
                    $isExpired = $record->expiry_date && \Illuminate\Support\Carbon::parse($record->expiry_date)->isPast();
                @endphp
                <tr>
                    <td>
                        <span class="badge bg-secondary">
                            {{ \Illuminate\Support\Facades\Lang::has('index.' . $record->document_type) ? __('index.' . $record->document_type) : ucfirst(str_replace('_', ' ', $record->document_type)) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if(in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp']))
                                <i data-feather="image" class="text-primary me-2" style="width: 16px; height: 16px;"></i>
                            @elseif($fileExt === 'pdf')
                                <i data-feather="file-text" class="text-danger me-2" style="width: 16px; height: 16px;"></i>
                            @else
                                <i data-feather="file" class="text-secondary me-2" style="width: 16px; height: 16px;"></i>
                            @endif
                            <span class="fw-semibold">{{ $record->title }}</span>
                        </div>
                    </td>
                    <td class="text-center small">{{ optional($record->document_date)->format('Y-m-d') ?: 'N/A' }}</td>
                    <td class="text-center small">
                        @if($record->expiry_date)
                            <span class="{{ $isExpired ? 'text-danger fw-bold' : '' }}">
                                {{ $record->expiry_date->format('Y-m-d') }}
                            </span>
                            @if($isExpired)
                                <span class="badge bg-danger ms-1" style="font-size: 10px;">Expired</span>
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $record->note ?: 'N/A' }}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            @if($record->file_path)
                                <button type="button"
                                        class="btn btn-outline-info btn-xs view-document-btn"
                                        title="{{ __('index.view_document') }}"
                                        data-url="{{ route('admin.employees.profile.documents.view', [$employee->id, $record->id]) }}"
                                        data-download="{{ route('admin.employees.profile.documents.download', [$employee->id, $record->id]) }}"
                                        data-title="{{ $record->title }}"
                                        data-ext="{{ $fileExt }}">
                                    <i data-feather="eye" style="width: 13px; height: 13px;"></i>
                                </button>
                                <a href="{{ route('admin.employees.profile.documents.download', [$employee->id, $record->id]) }}"
                                   class="btn btn-outline-secondary btn-xs"
                                   title="{{ __('index.download') }}">
                                    <i data-feather="download" style="width: 13px; height: 13px;"></i>
                                </a>
                            @endif

                            @can('employee.document.manage')
                                <button type="button"
                                        class="btn btn-outline-primary btn-xs edit-document-btn"
                                        title="{{ __('index.edit_document') }}"
                                        data-url="{{ route('admin.employees.profile.documents.update', [$employee->id, $record->id]) }}"
                                        data-title="{{ $record->title }}"
                                        data-type="{{ $record->document_type }}"
                                        data-date="{{ optional($record->document_date)->format('Y-m-d') }}"
                                        data-expiry="{{ optional($record->expiry_date)->format('Y-m-d') }}"
                                        data-note="{{ $record->note }}">
                                    <i data-feather="edit-2" style="width: 13px; height: 13px;"></i>
                                </button>

                                <form action="{{ route('admin.employees.profile.documents.destroy', [$employee->id, $record->id]) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('index.confirm_delete_document') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs" title="{{ __('index.delete_document') }}">
                                        <i data-feather="trash-2" style="width: 13px; height: 13px;"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-3">{{ __('index.no_records_found') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('employee.document.manage')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    <!-- Add Document Modal -->
    <div class="modal fade" id="addEmployeeDocumentModal" tabindex="-1" aria-labelledby="addEmployeeDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeDocumentModalLabel"><i data-feather="upload" class="me-2"></i>{{ __('index.add_documents') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" enctype="multipart/form-data" action="{{ route('admin.employees.profile.documents.store', $employee->id) }}" id="employeeDocumentsForm"
                      data-ocr-worker="{{ asset('assets/vendors/tesseract/worker.min.js') }}"
                      data-ocr-core="{{ asset('assets/vendors/tesseract/core') }}"
                      data-ocr-lang="{{ asset('assets/vendors/tesseract/lang') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-muted small">Upload employee identification, contracts, certificates, or letters.</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addEmployeeDocumentRow">
                                <i data-feather="plus"></i> {{ __('index.add_document') }}
                            </button>
                        </div>
                        <div id="employeeDocumentRows"></div>
                        <div id="employeeDocumentOcrFields"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.upload_documents') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div class="modal fade" id="editEmployeeDocumentModal" tabindex="-1" aria-labelledby="editEmployeeDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeDocumentModalLabel"><i data-feather="edit" class="me-2"></i>{{ __('index.edit_document') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editDocumentForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.type') }} <span class="text-danger">*</span></label>
                                <select class="form-select" name="document_type" id="edit_document_type" required>
                                    @foreach(['national_id','employment_contract','cv','certificate','salary_letter','promotion_letter','warning_letter','performance_review','training_certificate','other'] as $item)
                                        <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.title') }} <span class="text-danger">*</span></label>
                                <input class="form-control" name="title" id="edit_title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.document_date') }}</label>
                                <input class="form-control" type="date" name="document_date" id="edit_document_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('index.expiry_date') }}</label>
                                <input class="form-control" type="date" name="expiry_date" id="edit_expiry_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('index.file') }} <small class="text-muted">(Leave blank to keep existing file)</small></label>
                                <input class="form-control" type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('index.notes') }}</label>
                                <textarea class="form-control" name="note" id="edit_note" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('index.save_changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Template for Add Document Rows -->
    <template id="employeeDocumentRowTemplate">
        <div class="border rounded p-3 mb-3 employee-document-row bg-light">
            <div class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small">{{ __('index.type') }}</label>
                    <select class="form-select form-select-sm document-type" name="documents[__INDEX__][document_type]">
                        @foreach(['national_id','employment_contract','cv','certificate','salary_letter','promotion_letter','warning_letter','performance_review','training_certificate','other'] as $item)
                            <option value="{{ $item }}">{{ \Illuminate\Support\Facades\Lang::has('index.' . $item) ? __('index.' . $item) : ucfirst(str_replace('_', ' ', $item)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-8">
                    <label class="form-label small">{{ __('index.title') }}</label>
                    <input class="form-control form-control-sm document-title" name="documents[__INDEX__][title]" required>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small">{{ __('index.document_date') }}</label>
                    <input class="form-control form-control-sm" type="date" name="documents[__INDEX__][document_date]">
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small">{{ __('index.expiry_date') }}</label>
                    <input class="form-control form-control-sm document-expiry" type="date" name="documents[__INDEX__][expiry_date]">
                </div>
                <div class="col-lg-3 col-md-4">
                    <label class="form-label small">{{ __('index.file') }}</label>
                    <input class="form-control form-control-sm document-file" type="file" name="documents[__INDEX__][file]" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx" required>
                </div>
                <div class="col-md-10 mt-2">
                    <label class="form-label small">{{ __('index.notes') }}</label>
                    <input class="form-control form-control-sm document-note" name="documents[__INDEX__][note]" placeholder="{{ __('index.notes') }}">
                </div>
                <div class="col-md-2 mt-2 text-md-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-document-row" title="Remove document">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </div>
            <div class="small text-muted mt-2 document-file-choice" aria-live="polite"></div>
            <div class="document-ocr-result d-none mt-3 border-top pt-2">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold">{{ __('index.extracted_text') }}</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm retry-document-ocr" title="Read photo again">
                        <i data-feather="refresh-cw"></i>
                    </button>
                </div>
                <pre class="document-ocr-text small mt-2 mb-0 p-2 bg-white rounded border" style="max-height:180px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere"></pre>
            </div>
        </div>
    </template>

    <!-- Cropper Modal -->
    <div class="modal fade" id="employeeDocumentPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prepare Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex gap-3 mb-3">
                        <label><input type="radio" name="document_photo_mode" value="original" checked> Original</label>
                        <label><input type="radio" name="document_photo_mode" value="crop"> Crop</label>
                    </div>
                    <div style="max-height:55vh;overflow:hidden">
                        <img id="employeeDocumentPhotoPreview" alt="Selected document photo" style="display:block;max-width:100%;max-height:55vh;margin:auto">
                    </div>
                    <p class="small text-muted mt-2 mb-0">For a National ID, text recognition will suggest ID details and the expiry date after you use this photo. Review the fields before saving.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('index.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="useEmployeeDocumentPhoto">Use Photo</button>
                </div>
            </div>
        </div>
    </div>
@endcan

<!-- Document Viewer Modal -->
<div class="modal fade" id="viewEmployeeDocumentModal" tabindex="-1" aria-labelledby="viewEmployeeDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i data-feather="file" class="text-primary"></i>
                    <h5 class="modal-title" id="viewEmployeeDocumentModalLabel">{{ __('index.document_viewer') }}</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-primary btn-sm" id="viewDocNewTabBtn">
                        <i data-feather="external-link" style="width: 14px; height: 14px;"></i> {{ __('index.open_in_new_tab') }}
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm" id="viewDocDownloadBtn">
                        <i data-feather="download" style="width: 14px; height: 14px;"></i> {{ __('index.download') }}
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 text-center" style="min-height: 480px; background-color: #f8f9fa;">
                <!-- Image Viewer -->
                <div id="documentImageViewerWrap" class="p-3 d-none">
                    <img id="documentImageViewer" src="" alt="Document Preview" class="img-fluid rounded border shadow-sm" style="max-height: 75vh; object-fit: contain; margin: 0 auto;">
                </div>

                <!-- PDF Viewer -->
                <div id="documentPdfViewerWrap" class="d-none" style="height: 75vh;">
                    <iframe id="documentPdfViewer" src="" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen></iframe>
                </div>

                <!-- Generic / Office Doc Fallback -->
                <div id="documentGenericViewerWrap" class="p-5 d-none">
                    <div class="card border-0 shadow-sm mx-auto" style="max-width: 480px;">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i data-feather="file-text" class="text-primary" style="width: 56px; height: 56px;"></i>
                            </div>
                            <h5 id="genericDocTitle" class="card-title mb-2"></h5>
                            <p class="text-muted small mb-4">{{ __('index.preview_not_available') }}</p>
                            <a href="#" class="btn btn-primary" id="genericDocDownloadBtn">
                                <i data-feather="download" class="me-1"></i> {{ __('index.download') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@can('employee.document.manage')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="{{ asset('assets/vendors/tesseract/tesseract.min.js') }}"></script>
    <script src="{{ asset('assets/js/employee-profile-documents.js') }}?v={{ filemtime(public_path('assets/js/employee-profile-documents.js')) }}"></script>
@endcan

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-add initial row when Add Document modal opens if empty
        const addDocModal = document.getElementById('addEmployeeDocumentModal');
        if (addDocModal) {
            addDocModal.addEventListener('shown.bs.modal', function () {
                const rows = document.getElementById('employeeDocumentRows');
                const addBtn = document.getElementById('addEmployeeDocumentRow');
                if (rows && rows.children.length === 0 && addBtn) {
                    addBtn.click();
                }
            });
        }

        // Edit Document Button Handler
        document.querySelectorAll('.edit-document-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const form = document.getElementById('editDocumentForm');
                form.action = this.getAttribute('data-url');
                document.getElementById('edit_title').value = this.getAttribute('data-title') || '';
                document.getElementById('edit_document_type').value = this.getAttribute('data-type') || 'other';
                document.getElementById('edit_document_date').value = this.getAttribute('data-date') || '';
                document.getElementById('edit_expiry_date').value = this.getAttribute('data-expiry') || '';
                document.getElementById('edit_note').value = this.getAttribute('data-note') || '';

                const modal = new bootstrap.Modal(document.getElementById('editEmployeeDocumentModal'));
                modal.show();
            });
        });

        // View Document Button Handler
        document.querySelectorAll('.view-document-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const url = this.getAttribute('data-url');
                const downloadUrl = this.getAttribute('data-download');
                const title = this.getAttribute('data-title') || '{{ __("index.document_preview") }}';
                const ext = (this.getAttribute('data-ext') || '').toLowerCase();

                document.getElementById('viewEmployeeDocumentModalLabel').innerText = title;
                document.getElementById('viewDocNewTabBtn').href = url;
                document.getElementById('viewDocDownloadBtn').href = downloadUrl;

                const imgWrap = document.getElementById('documentImageViewerWrap');
                const pdfWrap = document.getElementById('documentPdfViewerWrap');
                const genericWrap = document.getElementById('documentGenericViewerWrap');

                imgWrap.classList.add('d-none');
                pdfWrap.classList.add('d-none');
                genericWrap.classList.add('d-none');

                if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                    document.getElementById('documentImageViewer').src = url;
                    imgWrap.classList.remove('d-none');
                } else if (ext === 'pdf') {
                    document.getElementById('documentPdfViewer').src = url;
                    pdfWrap.classList.remove('d-none');
                } else {
                    document.getElementById('genericDocTitle').innerText = title;
                    document.getElementById('genericDocDownloadBtn').href = downloadUrl;
                    genericWrap.classList.remove('d-none');
                }

                const modal = new bootstrap.Modal(document.getElementById('viewEmployeeDocumentModal'));
                modal.show();
            });
        });
    });
</script>
