@extends('layouts.master')

@section('title', __('index.issue_certificate'))

@section('action', __('index.issue_certificate'))

@section('main-content')
<section class="content">
    @include('admin.section.flash_message')

    <nav class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">{{ __('index.certificate_management') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('index.issue_certificate') }}</li>
        </ol>
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary btn-sm">
            <i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back_to_directory') }}
        </a>
    </nav>

    <!-- Layout Model / វិញ្ញាបនបត្រ គំរូ Selector -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #092653 0%, #1e3a8a 100%); color: #fff; border-radius: 12px;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-warning text-dark px-2.5 py-0.5 fw-bold mb-1">{{ __('index.certificate_templates') }}</span>
                    <h6 class="mb-0 text-white fw-bold">ជ្រើសរើសគំរូ Layout ដែលបានកំណត់ក្នុង CertiCraft Studio</h6>
                    <small class="text-white-50">ជ្រើសរើសដើម្បីបំពេញទម្រង់, ខ្លឹមសារ, និងហត្ថលេខាដោយស្វ័យប្រវត្តិ</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select id="template_picker" class="form-select form-select-sm fw-bold" style="min-width: 280px;" onchange="onTemplateChange(this)">
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" {{ (isset($selectedTemplate) && $selectedTemplate->id == $tmpl->id) ? 'selected' : '' }}
                                data-id="{{ $tmpl->id }}"
                                data-code="{{ $tmpl->template_code }}"
                                data-title="{{ $tmpl->certificate_title }}"
                                data-khmer-title="{{ $tmpl->khmer_certificate_title }}"
                                data-cat="{{ $tmpl->category }}"
                                data-layout="{{ $tmpl->layout_template }}"
                                data-hours="{{ $tmpl->training_hours }}"
                                data-status="{{ $tmpl->compliance_status }}"
                                data-track="{{ $tmpl->hr_competency_track }}"
                                data-sub="{{ $tmpl->subtitle }}"
                                data-khmer-sub="{{ $tmpl->khmer_subtitle }}"
                                data-ach="{{ $tmpl->course_or_achievement_title }}"
                                data-khmer-ach="{{ $tmpl->khmer_course_or_achievement_title }}"
                                data-cit="{{ $tmpl->citation }}"
                                data-khmer-cit="{{ $tmpl->khmer_citation }}"
                                data-sig1-name="{{ $tmpl->signatories_json[0]['name'] ?? 'CHHOEUN REACH' }}"
                                data-sig1-khmer="{{ $tmpl->signatories_json[0]['khmerName'] ?? 'ឈឿន រាជ' }}"
                                data-sig1-role="{{ $tmpl->signatories_json[0]['role'] ?? 'IT Manager / Branch Manager' }}"
                                data-sig1-krole="{{ $tmpl->signatories_json[0]['khmerRole'] ?? 'ប្រធានផ្នែកព័ត៌មានវិទ្យា / ប្រធានសាខា' }}"
                                data-sig2-name="{{ $tmpl->signatories_json[1]['name'] ?? 'PET POEUN' }}"
                                data-sig2-khmer="{{ $tmpl->signatories_json[1]['khmerName'] ?? 'ម៉េង សោភា' }}"
                                data-sig2-role="{{ $tmpl->signatories_json[1]['role'] ?? 'HR Manager / HR Supervisor' }}"
                                data-sig2-krole="{{ $tmpl->signatories_json[1]['khmerRole'] ?? 'ប្រធានផ្នែកធនធានមនុស្ស' }}">
                                @php
                                    $displayKhmer = $tmpl->khmer_title ?: $tmpl->khmer_certificate_title;
                                    $displayEng = $tmpl->title ?: $tmpl->certificate_title;
                                    $label = '[' . $tmpl->version_tag . '] ' . ($displayKhmer ? $displayKhmer . ($displayEng && $displayEng !== $displayKhmer ? ' · ' . $displayEng : '') : $displayEng) . ' (' . $tmpl->template_code . ')';
                                @endphp
                                {{ $label }} {{ $tmpl->is_default ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.certificates.studio', ['template_id' => $selectedTemplate->id ?? 1]) }}" 
                       id="edit_in_studio_btn" 
                       class="btn btn-warning btn-sm text-dark fw-bold whitespace-nowrap" 
                       target="_blank"
                       title="បើកគំរូនេះក្នុង CertiCraft Studio ដើម្បីកែសម្រួលផ្ទាល់ភ្នែក">
                        <i class="link-icon" data-feather="layout"></i> កែក្នុង Studio 🎨
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.certificates.store') }}" method="POST">
        @csrf
        <input type="hidden" name="template_id" id="template_id" value="{{ $selectedTemplate->id ?? '' }}">
        <input type="hidden" name="layout_template" id="layout_template" value="{{ $selectedTemplate->layout_template ?? 'executive-verify' }}">
        <div class="row">
            <!-- Left Column: Certificate Content -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="file-text" class="me-1" style="width: 18px; height: 18px;"></i> Certificate Details (ខ្លឹមសារវិញ្ញាបនបត្រ)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Category & ID -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Credential ID (លេខសម្គាល់) <span class="text-danger">*</span></label>
                                <input type="text" name="credential_id" id="credential_id" class="form-control font-monospace" value="{{ old('credential_id', $suggestedId) }}" required>
                                <small class="text-muted">Unique identifier for QR verification (e.g. KY-LDR-2026-001)</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category (ប្រភេទ) <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-select" required onchange="onCategoryChange(this.value)">
                                    <option value="leader">Leadership Excellence (មេដឹកនាំឆ្នើម)</option>
                                    <option value="service">Customer Service Excellence (សេវាកម្មឆ្នើម)</option>
                                    <option value="course">Professional Training / CPD (វគ្គបណ្តុះបណ្តាល)</option>
                                    <option value="custom">Honor & Merit Recognition (កិត្តិយសពិសេស)</option>
                                </select>
                            </div>

                            <!-- Titles -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Title (ចំណងជើងខ្មែរ)</label>
                                <input type="text" name="khmer_title" id="khmer_title" class="form-control" value="{{ old('khmer_title', 'វិញ្ញាបនបត្រកិត្តិយស មេដឹកនាំឆ្នើម') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Title (ចំណងជើងអង់គ្លេស) <span class="text-danger">*</span></label>
                                <input type="text" name="certificate_title" id="certificate_title" class="form-control" value="{{ old('certificate_title', 'OUTSTANDING TEAM LEADER AWARD') }}" required>
                            </div>

                            <!-- Subtitles -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Subtitle (ឃ្លាណែនាំខ្មែរ)</label>
                                <textarea name="khmer_subtitle" id="khmer_subtitle" rows="2" class="form-control">{{ old('khmer_subtitle', 'គណៈកម្មការប្រតិបត្តិ និងក្រុមប្រឹក្សាភិបាល គ្នា​យើង សូមប្រសិទ្ធពរជ័យ និងកោតសរសើរដល់') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Subtitle (ឃ្លាណែនាំអង់គ្លេស)</label>
                                <textarea name="subtitle" id="subtitle" rows="2" class="form-control">{{ old('subtitle', 'Conferred in recognition of visionary mentorship and solidarity to') }}</textarea>
                            </div>

                            <!-- Course / Achievement -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Achievement/Course (សមិទ្ធផលខ្មែរ)</label>
                                <input type="text" name="khmer_course_or_achievement_title" id="khmer_course_or_achievement_title" class="form-control" value="{{ old('khmer_course_or_achievement_title', 'សមិទ្ធផលភាពជាអ្នកដឹកនាំយុទ្ធសាស្ត្រ និងសាមគ្គីភាពក្រុម') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Achievement/Course <span class="text-danger">*</span></label>
                                <input type="text" name="course_or_achievement_title" id="course_or_achievement_title" class="form-control" value="{{ old('course_or_achievement_title', 'Strategic Leadership & Team Empowerment Milestone') }}" required>
                            </div>

                            <!-- Citation -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Khmer Citation (អត្ថបទសរសើរខ្មែរ)</label>
                                <textarea name="khmer_citation" id="khmer_citation" rows="3" class="form-control">{{ old('khmer_citation', 'ដើម្បីជាសក្ខីភាពបញ្ជាក់អំពីការខិតខំប្រឹងប្រែង ការដឹកនាំក្រុមប្រកបដោយចក្ខុវិស័យខ្ពស់ ស្មារតី "គ្នា​យើង" និងសាមគ្គីភាពរឹងមាំ។') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">English Citation (អត្ថបទសរសើរអង់គ្លេស)</label>
                                <textarea name="citation" id="citation" rows="3" class="form-control">{{ old('citation', 'For embodying the highest standard of leadership under the ethos of "គ្នា​យើង" (United As One) with uncompromising integrity.') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signatories -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="edit-3" class="me-1" style="width: 18px; height: 18px;"></i> Signatories (ហត្ថលេខីផ្លូវការ)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Signatory 1 -->
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold text-secondary mb-2">Signatory 1 (Founder / Executive)</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Name (English)</label>
                                    <input type="text" name="signatory_1_name" class="form-control form-control-sm" value="Chhoeun Reach">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Name (Khmer)</label>
                                    <input type="text" name="signatory_1_khmer" class="form-control form-control-sm" value="ឈឿន រាជ">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Role (English)</label>
                                    <input type="text" name="signatory_1_role" class="form-control form-control-sm" value="Managing Director & Founder">
                                </div>
                                <div>
                                    <label class="form-label small">Role (Khmer)</label>
                                    <input type="text" name="signatory_1_khmer_role" class="form-control form-control-sm" value="ស្ថាបនិក និងនាយកប្រតិបត្តិ">
                                </div>
                            </div>

                            <!-- Signatory 2 -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-secondary mb-2">Signatory 2 (HR Executive)</h6>
                                <div class="mb-2">
                                    <label class="form-label small">Name (English)</label>
                                    <input type="text" name="signatory_2_name" class="form-control form-control-sm" value="PET POEUN">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Name (Khmer)</label>
                                    <input type="text" name="signatory_2_khmer" class="form-control form-control-sm" value="ម៉េង សោភា">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Role (English)</label>
                                    <input type="text" name="signatory_2_role" class="form-control form-control-sm" value="Chief People Officer">
                                </div>
                                <div>
                                    <label class="form-label small">Role (Khmer)</label>
                                    <input type="text" name="signatory_2_khmer_role" class="form-control form-control-sm" value="ប្រធានផ្នែកធនធានមនុស្ស">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Recipient & HR Metadata -->
            <div class="col-lg-4">
                <!-- Recipient Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="user-check" class="me-1" style="width: 18px; height: 18px;"></i> Recipient (ម្ចាស់ជ័យលាភី)
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Employee Picker -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Staff / Employee</label>
                            <select name="user_id" id="user_id" class="form-select" onchange="onEmployeeSelect(this)">
                                <option value="">-- Choose Employee (Optional) --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" 
                                            {{ (isset($selectedUser) && $selectedUser->id == $u->id) ? 'selected' : '' }}
                                            data-name="{{ $u->english_name ?: $u->name }}" 
                                            data-khmer-name="{{ $u->name }}"
                                            data-code="{{ $u->employee_code }}"
                                            data-dept="{{ $u->department_id }}"
                                            data-branch="{{ $u->branch_id }}">
                                        {{ $u->name }} ({{ $u->english_name ?: $u->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Selecting auto-fills recipient name and employee ID.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recipient Latin Name <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control" value="{{ old('recipient_name', isset($selectedUser) ? ($selectedUser->english_name ?: $selectedUser->name) : 'Reach Chhoeun') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recipient Khmer Name</label>
                            <input type="text" name="khmer_recipient_name" id="khmer_recipient_name" class="form-control" value="{{ old('khmer_recipient_name', isset($selectedUser) ? $selectedUser->name : 'លោក ឈឿន រាជ') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recipient Title / Role</label>
                            <input type="text" name="recipient_title" id="recipient_title" class="form-control" value="{{ old('recipient_title', 'PRINCIPAL TEAM LEADER & STRATEGIC CULTURE CHAMPION') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Khmer Title / Role</label>
                            <input type="text" name="khmer_recipient_title" id="khmer_recipient_title" class="form-control" value="{{ old('khmer_recipient_title', 'មេដឹកនាំក្រុមឆ្នើម និងគំរូវប្បធម៌សាមគ្គីភាពស្ថាប័ន') }}">
                        </div>
                    </div>
                </div>

                <!-- KNEAYERNG HRMS & Compliance -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-primary">
                            <i data-feather="award" class="me-1" style="width: 18px; height: 18px;"></i> KNEAYERNG HRMS & Accreditation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Training / CPD Hours (ម៉ោង)</label>
                            <input type="number" step="0.5" name="training_hours" id="training_hours" class="form-control" value="{{ old('training_hours', 60) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Accreditation Status</label>
                            <input type="text" name="compliance_status" id="compliance_status" class="form-control" value="{{ old('compliance_status', 'Distinction / Honor Roll') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Issue Date (English) <span class="text-danger">*</span></label>
                            <input type="text" name="issue_date" id="issue_date" class="form-control" value="{{ old('issue_date', date('F d, Y')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Issue Date (Khmer)</label>
                            <input type="text" name="khmer_issue_date" id="khmer_issue_date" class="form-control" value="{{ old('khmer_issue_date', 'ថ្ងៃទី ' . date('d') . ' ខែ ' . date('m') . ' ឆ្នាំ ' . date('Y')) }}">
                        </div>

                        <div class="d-grid gap-2 pt-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i data-feather="check" class="me-1"></i> Issue Certificate (ចេញវិញ្ញាបនបត្រ)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<script>
function onEmployeeSelect(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;
    const name = opt.getAttribute('data-name');
    const khmerName = opt.getAttribute('data-khmer-name');
    if (name) document.getElementById('recipient_name').value = name;
    if (khmerName) document.getElementById('khmer_recipient_name').value = khmerName;
}

function onTemplateChange(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt) return;

    const id = opt.getAttribute('data-id');
    const title = opt.getAttribute('data-title');
    const khmerTitle = opt.getAttribute('data-khmer-title');
    const cat = opt.getAttribute('data-cat');
    const layout = opt.getAttribute('data-layout');
    const hours = opt.getAttribute('data-hours');
    const status = opt.getAttribute('data-status');
    const sub = opt.getAttribute('data-sub');
    const khmerSub = opt.getAttribute('data-khmer-sub');
    const ach = opt.getAttribute('data-ach');
    const khmerAch = opt.getAttribute('data-khmer-ach');
    const cit = opt.getAttribute('data-cit');
    const khmerCit = opt.getAttribute('data-khmer-cit');

    if (id) document.getElementById('template_id').value = id;
    if (layout) document.getElementById('layout_template').value = layout;
    if (cat) document.getElementById('category').value = cat;
    if (title) document.getElementById('certificate_title').value = title;
    if (khmerTitle) document.getElementById('khmer_title').value = khmerTitle;
    if (sub) document.getElementById('subtitle').value = sub;
    if (khmerSub) document.getElementById('khmer_subtitle').value = khmerSub;
    if (ach) document.getElementById('course_or_achievement_title').value = ach;
    if (khmerAch) document.getElementById('khmer_course_or_achievement_title').value = khmerAch;
    if (cit) document.getElementById('citation').value = cit;
    if (khmerCit) document.getElementById('khmer_citation').value = khmerCit;
    if (hours) document.getElementById('training_hours').value = hours;
    if (status) document.getElementById('compliance_status').value = status;

    // Signatories
    const s1Name = opt.getAttribute('data-sig1-name');
    const s1Khmer = opt.getAttribute('data-sig1-khmer');
    const s1Role = opt.getAttribute('data-sig1-role');
    const s1Krole = opt.getAttribute('data-sig1-krole');
    const s2Name = opt.getAttribute('data-sig2-name');
    const s2Khmer = opt.getAttribute('data-sig2-khmer');
    const s2Role = opt.getAttribute('data-sig2-role');
    const s2Krole = opt.getAttribute('data-sig2-krole');

    if (s1Name) { const el = document.querySelector('input[name="signatory_1_name"]'); if(el) el.value = s1Name; }
    if (s1Khmer) { const el = document.querySelector('input[name="signatory_1_khmer"]'); if(el) el.value = s1Khmer; }
    if (s1Role) { const el = document.querySelector('input[name="signatory_1_role"]'); if(el) el.value = s1Role; }
    if (s1Krole) { const el = document.querySelector('input[name="signatory_1_khmer_role"]'); if(el) el.value = s1Krole; }
    if (s2Name) { const el = document.querySelector('input[name="signatory_2_name"]'); if(el) el.value = s2Name; }
    if (s2Khmer) { const el = document.querySelector('input[name="signatory_2_khmer"]'); if(el) el.value = s2Khmer; }
    if (s2Role) { const el = document.querySelector('input[name="signatory_2_role"]'); if(el) el.value = s2Role; }
    if (s2Krole) { const el = document.querySelector('input[name="signatory_2_khmer_role"]'); if(el) el.value = s2Krole; }

    const editBtn = document.getElementById('edit_in_studio_btn');
    if (editBtn && id) {
        editBtn.href = "{{ route('admin.certificates.studio') }}?template_id=" + id;
    }
}
</script>
@endsection
