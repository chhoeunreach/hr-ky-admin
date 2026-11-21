<div class="modal fade" id="createDocumentModal" tabindex="-1" aria-labelledby="createDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h6 class="modal-title fw-bold" id="createDocumentModalLabel">
                    <i class="link-icon me-1" data-feather="file-plus"></i> {{ __('index.add_new_document') }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDocumentForm" action="{{ route('admin.company-documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-3">
                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="create_category_code" class="form-label fw-bold required">
                                {{ __('index.document_category') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="create_category_code" name="category_code" required>
                                <option value="">{{ __('index.select_document_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->code }}" {{ request('category') === $category->code ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch -->
                        <div class="col-md-6">
                            <label for="create_branch_id" class="form-label fw-bold">
                                {{ __('index.branch') }}
                            </label>
                            <select class="form-select form-select-sm" id="create_branch_id" name="branch_id">
                                <option value="">🏢 {{ __('index.head_office_all_branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Master & Utility Sub-Type -->
                        <div class="col-md-6" id="create_sub_type_wrapper">
                            <label for="create_doc_sub_type" class="form-label fw-bold">
                                {{ __('index.doc_sub_type') }}
                            </label>
                            <select class="form-select form-select-sm" id="create_doc_sub_type" name="doc_sub_type">
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

                        <!-- Holder / Owner Name (ឈ្មោះសាមីខ្លួន / ម្ចាស់ផ្ទះ / ម្ចាស់ឯកសារ) -->
                        <div class="col-md-6" id="create_holder_name_wrapper">
                            <label for="create_holder_name" class="form-label fw-bold">
                                {{ __('index.doc_holder_name') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="create_holder_name" name="holder_name"
                                   placeholder="{{ __('index.doc_holder_name_placeholder') }}">
                        </div>

                        <!-- Quick Presets for Master Docs -->
                        <div class="col-12" id="create_master_presets" style="margin-top: 0;">
                            <div class="d-flex align-items-center gap-1 flex-wrap p-2 bg-light rounded border">
                                <small class="fw-bold text-muted me-1">ជ្រើសរើសរហ័ស (ឯកសារមេ):</small>
                                <button type="button" class="btn btn-xs btn-outline-primary btn-preset-sub" data-category="master" data-type="national_id" data-title="អត្តសញ្ញាណប័ណ្ណ">
                                    🆔 អត្តសញ្ញាណប័ណ្ណ
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info btn-preset-sub" data-category="master" data-type="passport" data-title="លិខិតឆ្លងដែន">
                                    🛂 លិខិតឆ្លងដែន
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success btn-preset-sub" data-category="master" data-type="family_book" data-title="សៀវភៅគ្រួសារ">
                                    📘 សៀវភៅគ្រួសារ
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning btn-preset-sub" data-category="master" data-type="residence_book" data-title="សៀវភៅស្នាក់នៅ">
                                    📗 សៀវភៅស្នាក់នៅ
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-preset-sub" data-category="master" data-type="other_master" data-title="ឯកសារពាក់ព័ន្ធមេ">
                                    📑 ឯកសារពាក់ព័ន្ធមេ
                                </button>
                            </div>
                        </div>

                        <!-- Quick Presets for Utilities & Rent -->
                        <div class="col-12" id="create_utility_presets" style="margin-top: 0; display: none;">
                            <div class="d-flex align-items-center gap-1 flex-wrap p-2 bg-light rounded border border-info">
                                <small class="fw-bold text-info me-1">ជ្រើសរើសរហ័ស (ទឹក ភ្លើង ជួលផ្ទះ):</small>
                                <button type="button" class="btn btn-xs btn-outline-primary btn-preset-sub" data-category="utility" data-type="rent" data-title="ថ្លៃជួលផ្ទះ/ទីតាំង">
                                    🏠 ថ្លៃជួលផ្ទះ
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning btn-preset-sub" data-category="utility" data-type="electricity" data-title="វិក្កយបត្រភ្លើង EDC">
                                    ⚡ វិក្កយបត្រភ្លើង EDC
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info btn-preset-sub" data-category="utility" data-type="water" data-title="វិក្កយបត្រទឹក PPWSA">
                                    💧 វិក្កយបត្រទឹក PPWSA
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-success btn-preset-sub" data-category="utility" data-type="internet" data-title="វិក្កយបត្រអ៊ីនធឺណិត">
                                    🌐 វិក្កយបត្រអ៊ីនធឺណិត
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-preset-sub" data-category="utility" data-type="waste" data-title="ថ្លៃសំរាម">
                                    🗑️ ថ្លៃសំរាម
                                </button>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="col-md-8">
                            <label for="create_title" class="form-label fw-bold required">
                                {{ __('index.document_title') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="create_title" name="title"
                                   placeholder="{{ __('index.title_placeholder') }}" required>
                        </div>

                        <!-- Doc Number -->
                        <div class="col-md-4">
                            <label for="create_doc_number" class="form-label fw-bold">
                                {{ __('index.document_number') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="create_doc_number" name="doc_number"
                                   placeholder="{{ __('index.doc_number_placeholder') }}">
                        </div>

                        <!-- Issuing Authority -->
                        <div class="col-md-6">
                            <label for="create_issuing_authority" class="form-label fw-bold">
                                {{ __('index.issuing_authority') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="create_issuing_authority" name="issuing_authority"
                                   placeholder="{{ __('index.authority_placeholder') }}">
                        </div>

                        <!-- Confidentiality -->
                        <div class="col-md-6">
                            <label for="create_confidentiality_level" class="form-label fw-bold">
                                {{ __('index.confidentiality') }}
                            </label>
                            <select class="form-select form-select-sm" id="create_confidentiality_level" name="confidentiality_level">
                                <option value="internal" selected>🔒 {{ __('index.confidentiality_internal') }}</option>
                                <option value="restricted">🛡️ {{ __('index.confidentiality_restricted') }}</option>
                                <option value="confidential">🛑 {{ __('index.confidentiality_confidential') }}</option>
                                <option value="public">🌐 {{ __('index.confidentiality_public') }}</option>
                            </select>
                        </div>

                        <!-- Issue Date & Expiry Date -->
                        <div class="col-md-4">
                            <label for="create_issue_date" class="form-label fw-bold">
                                {{ __('index.issue_date') }}
                            </label>
                            <input type="date" class="form-control form-control-sm" id="create_issue_date" name="issue_date">
                        </div>

                        <div class="col-md-4">
                            <label for="create_expiry_date" class="form-label fw-bold">
                                {{ __('index.expiry_date') }}
                            </label>
                            <input type="date" class="form-control form-control-sm" id="create_expiry_date" name="expiry_date">
                            <small class="text-muted" style="font-size: 10.5px;">{{ __('index.leave_blank_if_no_expiry') }}</small>
                        </div>

                        <div class="col-md-4">
                            <label for="create_alert_before_days" class="form-label fw-bold">
                                {{ __('index.alert_before_days') }}
                            </label>
                            <select class="form-select form-select-sm" id="create_alert_before_days" name="alert_before_days">
                                <option value="15">{{ __('index.days_before', ['count' => 15]) }}</option>
                                <option value="30" selected>{{ __('index.days_before_recommended', ['count' => 30]) }}</option>
                                <option value="60">{{ __('index.days_before', ['count' => 60]) }}</option>
                                <option value="90">{{ __('index.days_before', ['count' => 90]) }}</option>
                            </select>
                        </div>

                        <!-- Cost & Currency & Receipt -->
                        <div class="col-md-4">
                            <label for="create_cost_amount" class="form-label fw-bold">
                                {{ __('index.cost_fee') }}
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="create_cost_amount" name="cost_amount"
                                   placeholder="0.00">
                        </div>

                        <div class="col-md-3">
                            <label for="create_currency" class="form-label fw-bold">
                                {{ __('index.currency') }}
                            </label>
                            <select class="form-select form-select-sm" id="create_currency" name="currency">
                                <option value="USD" selected>USD ($)</option>
                                <option value="KHR">KHR (៛)</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label for="create_receipt_number" class="form-label fw-bold">
                                {{ __('index.receipt_number') }}
                            </label>
                            <input type="text" class="form-control form-control-sm" id="create_receipt_number" name="receipt_number"
                                   placeholder="{{ __('index.receipt_placeholder') }}">
                        </div>

                        <!-- File Upload -->
                        <div class="col-md-12">
                            <label for="create_file" class="form-label fw-bold required">
                                {{ __('index.attach_document') }} <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control form-control-sm" id="create_file" name="file"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" required>
                            <div class="form-text text-muted" style="font-size: 11px;">
                                {{ __('index.supported_file_types') }}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <label for="create_description" class="form-label fw-bold">
                                {{ __('index.notes_remarks') }}
                            </label>
                            <textarea class="form-control form-control-sm" id="create_description" name="description" rows="2"
                                      placeholder="{{ __('index.notes_remarks_placeholder') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 border-top bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        {{ __('index.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitCreateDoc">
                        <i class="link-icon me-1" data-feather="check-circle"></i> {{ __('index.save_document') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
