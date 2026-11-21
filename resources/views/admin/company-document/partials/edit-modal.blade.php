<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fw-bold" id="editDocumentModalLabel">
                    <i class="link-icon me-1" data-feather="edit"></i> {{ __('index.edit_document') }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDocumentForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_doc_id" name="id">
                <div class="modal-body p-3">
                    <div class="row g-3">
                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="edit_category_code" class="form-label fw-bold required">
                                {{ __('index.document_category') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="edit_category_code" name="category_code" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->code }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch -->
                        <div class="col-md-6">
                            <label for="edit_branch_id" class="form-label fw-bold">
                                {{ __('index.branch') }}
                            </label>
                            <select class="form-select form-select-sm" id="edit_branch_id" name="branch_id">
                                <option value="">🏢 {{ __('index.head_office_all_branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Master & Utility Sub-Type -->
                        <div class="col-md-6">
                            <label for="edit_doc_sub_type" class="form-label fw-bold">
                                {{ __('index.doc_sub_type') }}
                            </label>
                            <select class="form-select form-select-sm" id="edit_doc_sub_type" name="doc_sub_type">
                                <option value="">{{ __('index.select_doc_sub_type') }}</option>
                                <optgroup label="🪪 {{ __('index.doc_master') }}">
                                    <option value="national_id">🆔 {{ __('index.doc_type_national_id') }}</option>
                                    <option value="passport">🛂 {{ __('index.doc_type_passport') }}</option>
                                    <option value="family_book">📘 {{ __('index.doc_type_family_book') }}</option>
                                    <option value="residence_book">📗 {{ __('index.doc_type_residence_book') }}</option>
                                    <option value="other_master">📑 {{ __('index.doc_type_other_master') }}</option>
                                </optgroup>
                                <optgroup label="⚡ {{ __('index.doc_utility') }}">
                                    <option value="rent">🏠 {{ __('index.doc_type_rent') }}</option>
                                    <option value="electricity">⚡ {{ __('index.doc_type_electricity') }}</option>
                                    <option value="water">💧 {{ __('index.doc_type_water') }}</option>
                                    <option value="internet">🌐 {{ __('index.doc_type_internet') }}</option>
                                    <option value="waste">🗑️ {{ __('index.doc_type_waste') }}</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Holder / Owner Name -->
                        <div class="col-md-6">
                            <label for="edit_holder_name" class="form-label fw-bold">
                                {{ __('index.doc_holder_name') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="edit_holder_name" name="holder_name"
                                   placeholder="{{ __('index.doc_holder_name_placeholder') }}">
                        </div>

                        <!-- Title -->
                        <div class="col-md-8">
                            <label for="edit_title" class="form-label fw-bold required">
                                {{ __('index.document_title') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="edit_title" name="title" required>
                        </div>

                        <!-- Doc Number -->
                        <div class="col-md-4">
                            <label for="edit_doc_number" class="form-label fw-bold">
                                {{ __('index.document_number') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="edit_doc_number" name="doc_number">
                        </div>

                        <!-- Issuing Authority -->
                        <div class="col-md-6">
                            <label for="edit_issuing_authority" class="form-label fw-bold">
                                {{ __('index.issuing_authority') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="edit_issuing_authority" name="issuing_authority">
                        </div>

                        <!-- Confidentiality -->
                        <div class="col-md-6">
                            <label for="edit_confidentiality_level" class="form-label fw-bold">
                                {{ __('index.confidentiality') }}
                            </label>
                            <select class="form-select form-select-sm" id="edit_confidentiality_level" name="confidentiality_level">
                                <option value="internal">🔒 {{ __('index.confidentiality_internal') }}</option>
                                <option value="restricted">🛡️ {{ __('index.confidentiality_restricted') }}</option>
                                <option value="confidential">🛑 {{ __('index.confidentiality_confidential') }}</option>
                                <option value="public">🌐 {{ __('index.confidentiality_public') }}</option>
                            </select>
                        </div>

                        <!-- Issue Date & Expiry Date -->
                        <div class="col-md-4">
                            <label for="edit_issue_date" class="form-label fw-bold">
                                {{ __('index.issue_date') }}
                            </label>
                            <input type="date" class="form-control form-control-sm" id="edit_issue_date" name="issue_date">
                        </div>

                        <div class="col-md-4">
                            <label for="edit_expiry_date" class="form-label fw-bold">
                                {{ __('index.expiry_date') }}
                            </label>
                            <input type="date" class="form-control form-control-sm" id="edit_expiry_date" name="expiry_date">
                            <small class="text-muted" style="font-size: 10.5px;">{{ __('index.leave_blank_if_no_expiry') }}</small>
                        </div>

                        <div class="col-md-4">
                            <label for="edit_alert_before_days" class="form-label fw-bold">
                                {{ __('index.alert_before_days') }}
                            </label>
                            <select class="form-select form-select-sm" id="edit_alert_before_days" name="alert_before_days">
                                <option value="15">{{ __('index.days_before', ['count' => 15]) }}</option>
                                <option value="30">{{ __('index.days_before', ['count' => 30]) }}</option>
                                <option value="60">{{ __('index.days_before', ['count' => 60]) }}</option>
                                <option value="90">{{ __('index.days_before', ['count' => 90]) }}</option>
                            </select>
                        </div>

                        <!-- Cost & Currency & Receipt -->
                        <div class="col-md-4">
                            <label for="edit_cost_amount" class="form-label fw-bold">
                                {{ __('index.cost_fee') }}
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="edit_cost_amount" name="cost_amount">
                        </div>

                        <div class="col-md-3">
                            <label for="edit_currency" class="form-label fw-bold">
                                {{ __('index.currency') }}
                            </label>
                            <select class="form-select form-select-sm" id="edit_currency" name="currency">
                                <option value="USD">USD ($)</option>
                                <option value="KHR">KHR (៛)</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label for="edit_receipt_number" class="form-label fw-bold">
                                {{ __('index.receipt_number') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="edit_receipt_number" name="receipt_number">
                        </div>

                        <!-- Replace File (Optional) -->
                        <div class="col-md-12">
                            <label for="edit_file" class="form-label fw-bold">
                                {{ __('index.replace_file_optional') }}
                            </label>
                            <input type="file" class="form-control form-control-sm" id="edit_file" name="file"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                            <div class="form-text text-muted" style="font-size: 11px;">
                                {{ __('index.supported_file_types') }}
                            </div>
                            <div id="currentFileDisplay" class="mt-1 small text-muted"></div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <label for="edit_description" class="form-label fw-bold">
                                {{ __('index.notes_remarks') }}
                            </label>
                            <textarea class="form-control form-control-sm" id="edit_description" name="description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 border-top bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        {{ __('index.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitEditDoc">
                        <i class="link-icon me-1" data-feather="save"></i> {{ __('index.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
