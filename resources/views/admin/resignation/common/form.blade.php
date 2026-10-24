@php
    $editingResignation = isset($resignationDetail);
    $defaultResignationDate = $isBsEnabled
        ? \App\Helpers\AppHelper::dateInYmdFormatEngToNep(now()->toDateString())
        : now()->toDateString();
    $selectedBranchId = old('branch_id', $resignationDetail->branch_id ?? $filterParameters['branch_id'] ?? auth()->user()?->branch_id);
    $selectedDepartmentId = old('department_id', $resignationDetail->department_id ?? $filterParameters['department_id'] ?? '');
    $selectedEmployeeId = old('employee_id', $resignationDetail->employee_id ?? $filterParameters['employee_id'] ?? '');
    $letterEmployee = $profileEmployee ?? ($resignationDetail ?? null)?->employee ?? null;
    $letterDetails = array_replace(
        $letterEmployee ? \App\Models\Resignation::employeeLetterInformation($letterEmployee) : [],
        ['made_at' => $letterEmployee?->branch?->name, 'letter_date' => now()->format('Y-m-d')],
        $resignationDetail->letter_details ?? [],
        old('letter_details', [])
    );
@endphp

<link rel="stylesheet" href="{{ asset('assets/css/resignation-form.css') }}">

<div class="resignation-form">
    <header class="resignation-form-header">
        <div>
            <h4>{{ $editingResignation ? __('index.resignation_edit_title') : __('index.resignation_create_title') }}</h4>
            <p>{{ __('index.resignation_form_help') }}</p>
        </div>
        <span class="resignation-required-note"><span aria-hidden="true">*</span> {{ __('index.resignation_required_fields') }}</span>
    </header>

    <section class="resignation-form-section" aria-labelledby="resignationEmployeeHeading">
        <div class="resignation-section-heading"><span class="resignation-section-number">01</span><div>
            <h5 id="resignationEmployeeHeading">{{ __('index.employee_information') }}</h5>
            <p>{{ __('index.resignation_employee_help') }}</p>
        </div></div>
        <div class="row g-3">
            @if(auth('admin')->check() || !auth()->user()?->branch_id)
                <div class="col-md-4">
                    <label for="branch_id" class="form-label">{{ __('index.branch') }} <span class="resignation-required">*</span></label>
                    <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" required>
                        <option value="" disabled @selected(!$selectedBranchId)>{{ __('index.select_branch') }}</option>
                        @foreach(($companyDetail ?? null)?->branches()->get() ?? [] as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranchId == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="col-md-4">
                <label for="department_id" class="form-label">{{ __('index.department') }} <span class="resignation-required">*</span></label>
                <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                    <option value="" disabled @selected(!$selectedDepartmentId)>{{ __('index.select_department') }}</option>
                    @foreach($filteredDepartment ?? [] as $department)
                        <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->dept_name }}</option>
                    @endforeach
                </select>
                @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="employee_id" class="form-label">{{ __('index.employee') }} <span class="resignation-required">*</span></label>
                <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                    <option value="" disabled @selected(!$selectedEmployeeId)>{{ __('index.select_employee') }}</option>
                    @foreach($filteredUsers ?? ($letterEmployee ? collect([$letterEmployee]) : []) as $user)
                        <option value="{{ $user->id }}" @selected($selectedEmployeeId == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="resignation-employee-details">
            <p class="resignation-field-help">{{ __('index.resignation_employee_details_help') }}</p>
            <div class="row g-3">
                @foreach([
                    'khmer_name' => 'khmer_name', 'latin_name' => 'latin_name', 'employee_code' => 'employee_id',
                    'gender' => 'gender', 'phone' => 'phone', 'joining_date' => 'joining_date',
                    'position' => 'position', 'department' => 'department', 'branch' => 'branch',
                ] as $field => $label)
                    <div class="col-md-4">
                        <label for="letter_{{ $field }}" class="form-label">{{ __('index.' . $label) }}</label>
                        @if($field === 'gender')
                            <select class="form-select @error('letter_details.gender') is-invalid @enderror" id="letter_gender" name="letter_details[gender]" data-letter-employee-field="gender">
                                <option value="">{{ __('index.resignation_select_gender') }}</option>
                                @foreach(['male', 'female', 'other'] as $gender)
                                    <option value="{{ $gender }}" @selected(strtolower($letterDetails['gender'] ?? '') === $gender)>{{ __('index.' . $gender) }}</option>
                                @endforeach
                            </select>
                        @else
                            <input class="form-control @error('letter_details.' . $field) is-invalid @enderror" id="letter_{{ $field }}" name="letter_details[{{ $field }}]"
                                   type="{{ $field === 'joining_date' ? 'date' : ($field === 'phone' ? 'tel' : 'text') }}"
                                   value="{{ $letterDetails[$field] ?? '' }}" data-letter-employee-field="{{ $field }}" maxlength="255">
                        @endif
                        @error('letter_details.' . $field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <p class="resignation-field-help mb-0" id="resignationEmployeeStatus" role="status" aria-live="polite"></p>
        </div>
    </section>

    <section class="resignation-form-section" aria-labelledby="resignationDetailsHeading">
        <div class="resignation-section-heading"><span class="resignation-section-number">02</span><div>
            <h5 id="resignationDetailsHeading">{{ __('index.resignation_details_title') }}</h5>
            <p>{{ __('index.resignation_details_help') }}</p>
        </div></div>
        <div class="row g-3">
            @foreach(['resignation_date' => 'resignation_date', 'last_working_day' => 'last_working_day'] as $field => $label)
                <div class="col-md-4 event-date-time">
                    <label for="{{ $field }}" class="form-label">{{ __('index.' . $label) }} <span class="resignation-required">*</span></label>
                    <input type="{{ $isBsEnabled ? 'text' : 'date' }}" class="form-control {{ $isBsEnabled ? 'nepaliDate' : '' }} @error($field) is-invalid @enderror"
                           id="{{ $field }}" name="{{ $field }}" required value="{{ old($field, ($resignationDetail ?? null)?->{$field} ?? ($field === 'resignation_date' ? $defaultResignationDate : '')) }}" @if($isBsEnabled) placeholder="yyyy-mm-dd" @endif>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
            <div class="col-md-4">
                <label for="status" class="form-label">{{ __('index.status') }} <span class="resignation-required">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    @foreach($resignationStatus as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $resignationDetail->status ?? 'pending') === $status->value)>{{ __('index.' . $status->value) }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="resignation_reason" class="form-label">{{ __('index.resignation_reason_label') }} ({{ __('index.optional') }})</label>
                <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" id="resignation_reason" rows="4" placeholder="{{ __('index.resignation_reason_placeholder') }}">{{ old('reason', html_entity_decode(strip_tags($resignationDetail->reason ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')) }}</textarea>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if($editingResignation)
                <div class="col-12">
                    <label for="admin_remark" class="form-label">{{ __('index.admin_remark') }}</label>
                    <textarea class="form-control @error('admin_remark') is-invalid @enderror" name="admin_remark" id="admin_remark" rows="3">{{ old('admin_remark', $resignationDetail->admin_remark) }}</textarea>
                    @error('admin_remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
        </div>
    </section>

    <section class="resignation-form-section" aria-labelledby="resignationHandoverHeading">
        <div class="resignation-section-heading"><span class="resignation-section-number">03</span><div>
            <h5 id="resignationHandoverHeading">{{ __('index.resignation_handover_title') }}</h5>
            <p>{{ __('index.resignation_handover_help') }}</p>
        </div></div>
        <div class="row g-3">
            <div class="col-md-7">
                <label for="letter_returned_property" class="form-label">{{ __('index.company_property_returned') }}</label>
                <textarea class="form-control @error('letter_details.returned_property') is-invalid @enderror" id="letter_returned_property" name="letter_details[returned_property]" rows="4" maxlength="10000" placeholder="{{ __('index.resignation_property_placeholder') }}">{{ $letterDetails['returned_property'] ?? '' }}</textarea>
                @error('letter_details.returned_property')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label for="document" class="form-label">{{ __('index.resignation_supporting_document') }}</label>
                <div class="resignation-file-control">
                    <input class="visually-hidden" type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp" aria-describedby="resignationDocumentHelp resignationSelectedFile">
                    <label class="btn btn-outline-secondary btn-sm mb-0" for="document">{{ __('index.resignation_choose_file') }}</label>
                    <span id="resignationSelectedFile" role="status" aria-live="polite">{{ __('index.resignation_no_file') }}</span>
                </div>
                <p class="resignation-field-help" id="resignationDocumentHelp">{{ __('index.resignation_document_help') }}</p>
                @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if(isset($resignationDetail->document))
            @php
                $fileExtension = pathinfo($resignationDetail->document, PATHINFO_EXTENSION);
            @endphp
            @if(in_array($fileExtension, ['jpeg', 'jpg', 'png', 'webp']))
                <img class="wd-200 ht-100" style="object-fit: cover;"
                     src="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                     alt="{{ __('index.resignation_supporting_document') }}" data-bs-toggle="modal" data-bs-target="#certificateModal-{{ $resignationDetail->id }}">

                <div class="modal fade" id="certificateModal-{{ $resignationDetail->id }}" tabindex="-1" aria-labelledby="imageModalLabel-{{ $resignationDetail->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel-{{ $resignationDetail->id }}">{{ __('index.resignation_view_image') }} <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="{{ __('index.resignation_close') }}"></button></h5>

                            </div>
                            <div class="modal-body text-center">
                                <img class="img-fluid" src="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}" alt="{{ __('index.resignation_supporting_document') }}">
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($fileExtension === 'pdf')
                <embed src="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                       type="application/pdf" width="150" height="100" />
                <a href="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                   target="_blank" class="mt-2">{{ __('index.resignation_preview_pdf') }}</a>
            @else
                <a href="{{ asset(\App\Models\Resignation::UPLOAD_PATH . $resignationDetail->document) }}"
                   download class="mt-2">{{ __('index.resignation_download_document') }}</a>
            @endif
        @endif
            </div>
        </div>
    </section>

    <section class="resignation-form-section" aria-labelledby="resignationLetterHeading">
        <div class="resignation-section-heading"><span class="resignation-section-number">04</span><div>
            <h5 id="resignationLetterHeading">{{ __('index.resignation_letter_details_title') }}</h5>
            <p>{{ __('index.resignation_letter_details_help') }}</p>
        </div></div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="letter_made_at" class="form-label">{{ __('index.resignation_place_of_signing') }}</label>
                <input class="form-control @error('letter_details.made_at') is-invalid @enderror" id="letter_made_at" name="letter_details[made_at]" value="{{ $letterDetails['made_at'] ?? '' }}" maxlength="255" placeholder="{{ __('index.resignation_place_placeholder') }}">
                @error('letter_details.made_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="letter_date" class="form-label">{{ __('index.resignation_letter_date') }}</label>
                <input type="date" class="form-control @error('letter_details.letter_date') is-invalid @enderror" id="letter_date" name="letter_details[letter_date]" value="{{ $letterDetails['letter_date'] ?? '' }}">
                @error('letter_details.letter_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <footer class="resignation-form-footer">
        <p>{{ __('index.resignation_save_help') }}</p>
        <div class="resignation-form-actions">
            <a class="btn btn-outline-secondary" href="{{ route('admin.resignation.index') }}" @if(request('request_modal') === '1') data-close-request-form @endif>{{ __('index.cancel') }}</a>
            @if(auth('admin')->check() || \Illuminate\Support\Facades\Gate::any(['update_resignation', 'create_resignation']))
                <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="check"></i> {{ $editingResignation ? __('index.resignation_save_changes') : __('index.resignation_save_request') }}</button>
            @endif
        </div>
    </footer>
</div>
