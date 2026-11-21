<div class="modal fade" id="disburseSalaryModal" tabindex="-1" aria-labelledby="disburseSalaryModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-white text-primary p-2 rounded-circle me-2">
                        <i class="link-icon" data-feather="check-circle" style="width: 20px; height: 20px;"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold" id="disburseSalaryModalLabel">ផ្ទាំងបញ្ជាក់ និងបើកប្រាក់ខែ (Disburse Salary)</h5>
                        <small class="text-white-50">ផ្ទៀងផ្ទាត់ការកាត់ និងចំណូលបន្ថែមមុនពេលបើកប្រាក់ខែ</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="disburseSalaryForm">
                @csrf
                <input type="hidden" id="modalTicketId" name="ticket_id">

                <div class="modal-body p-4">
                    {{-- Loading Spinner --}}
                    <div id="modalLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">កំពុងផ្ទុក...</span>
                        </div>
                        <div class="mt-3 text-muted fw-semibold">កំពុងទាញទិន្នន័យប្រាក់ខែបុគ្គលិក...</div>
                    </div>

                    {{-- Content Container --}}
                    <div id="modalContentContainer" style="display: none;">
                        {{-- Employee Profile Card --}}
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <img id="modalEmpAvatar" src="{{ asset('assets/images/img.png') }}" alt="Avatar" 
                                         class="rounded-circle border me-3" style="width: 58px; height: 58px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 id="modalEmpName" class="fw-bold text-dark mb-1">—</h5>
                                                <div class="text-muted small">
                                                    អត្តលេខ: <span id="modalEmpCode" class="fw-semibold text-primary">—</span> | 
                                                    ផ្នែក: <span id="modalEmpDept" class="fw-semibold">—</span> | 
                                                    សាខា: <span id="modalEmpBranch" class="fw-semibold">—</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary fs-6 px-3 py-1 fw-bold" id="modalTicketNumberBadge">#A-000</span>
                                                <div class="text-muted small mt-1">ខែ: <span id="modalSalaryMonth" class="fw-bold">—</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Breakdown: Deductions vs Earnings --}}
                        <div class="row g-3 mb-4">
                            {{-- Left: Deductions Breakdown (កាត់ប្រាក់ខែ) --}}
                            <div class="col-md-6">
                                <div class="card border-danger border-opacity-25 h-100">
                                    <div class="card-header bg-danger bg-opacity-10 py-2 border-danger border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-danger">
                                            <i class="link-icon me-1" data-feather="minus-circle" style="width: 16px; height: 16px;"></i>
                                            ការកាត់ប្រាក់ខែ (Deductions)
                                        </span>
                                        <span class="badge bg-danger" id="modalTotalDeductionBadge">-$0.00</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>📝 កាត់ច្បាប់ (Leave/Absence)</span>
                                                <span class="fw-bold text-danger" id="modalDedLeave">-$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>⏳ កាត់យឺត / ចេញមុន (Late/Undertime)</span>
                                                <span class="fw-bold text-danger" id="modalDedLate">-$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>💸 កាត់ខ្ចីលុយ (Advance Salary)</span>
                                                <span class="fw-bold text-danger" id="modalDedAdvance">-$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>🏢 កាត់បង់រំលស់ (Installment)</span>
                                                <span class="fw-bold text-danger" id="modalDedInstallment">-$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>⚠️ ពិន័យ / ព្រមាន (Penalty)</span>
                                                <span class="fw-bold text-danger" id="modalDedPenalty">-$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>🔹 កាត់ផ្សេងៗ (SSF, PF, TDS)</span>
                                                <span class="fw-bold text-muted" id="modalDedOther">-$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 bg-light fw-bold">
                                                <span>សរុបប្រាក់កាត់</span>
                                                <span class="text-danger fs-6" id="modalTotalDeductions">-$0.00</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Earnings Breakdown (ចំណូលបន្ថែម) --}}
                            <div class="col-md-6">
                                <div class="card border-success border-opacity-25 h-100">
                                    <div class="card-header bg-success bg-opacity-10 py-2 border-success border-opacity-25 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-success">
                                            <i class="link-icon me-1" data-feather="plus-circle" style="width: 16px; height: 16px;"></i>
                                            ប្រាក់ខែគោល & ចំណូលបន្ថែម (Earnings)
                                        </span>
                                        <span class="badge bg-success" id="modalTotalGrossBadge">+$0.00</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>💵 ប្រាក់ខែគោល (Basic Salary)</span>
                                                <span class="fw-bold text-dark" id="modalEarnBasic">$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>🏷️ កម្រៃជើងសារ លក់ (Sales)</span>
                                                <span class="fw-bold text-success" id="modalEarnSell">+$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>👔 កម្រៃជើងសារ អ៊ុត (Ironing)</span>
                                                <span class="fw-bold text-success" id="modalEarnIron">+$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>📦 កម្រៃជើងសារ សម្ភារ (Materials)</span>
                                                <span class="fw-bold text-success" id="modalEarnMaterial">+$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>🌟 ការមិនឈប់ (Attendance Bonus)</span>
                                                <span class="fw-bold text-success" id="modalEarnAttendance">+$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <span>⏰ ការមកទៀងទាត់ (Punctuality)</span>
                                                <span class="fw-bold text-success" id="modalEarnPunctual">+$0.00</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 bg-light fw-bold">
                                                <span>សរុបចំណូល (Gross Salary)</span>
                                                <span class="text-success fs-6" id="modalTotalEarnings">+$0.00</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Net Salary Highlight Card --}}
                        <div class="card bg-success bg-opacity-10 border-success mb-4 text-center py-3">
                            <div class="text-uppercase small fw-bold text-success mb-1">
                                <i class="link-icon me-1" data-feather="check"></i> ប្រាក់ខែសុទ្ធជាក់ស្តែងត្រូវបើក (Net Salary Payable)
                            </div>
                            <div class="display-5 fw-bold text-success" id="modalNetSalaryDisplay">$0.00</div>
                            <div class="small text-muted mt-1">
                                [ចំណូលសរុប <span id="modalFormulaGross">$0.00</span>] - [ប្រាក់កាត់សរុប <span id="modalFormulaDed">$0.00</span>]
                            </div>
                        </div>

                        <div class="card border-primary border-opacity-25 mb-4" id="payrollQuickUpdateCard" style="display:none">
                            <div class="card-body d-flex flex-wrap align-items-end gap-2">
                                <div class="flex-grow-1">
                                    <label for="modalQuickBonus" class="form-label fw-bold mb-1">Quick update: approved bonus</label>
                                    <input type="number" min="0" step="0.01" class="form-control" id="modalQuickBonus">
                                    <div class="form-text">Available while the payslip is still a draft and before the monthly payroll snapshot is saved.</div>
                                </div>
                                <button type="button" class="btn btn-outline-primary" id="btnQuickUpdateBonus">Save update</button>
                            </div>
                        </div>

                        {{-- Payment Method & Date --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modalPaymentMethod" class="form-label fw-bold">វិធីសាស្ត្រទូទាត់ (Payment Method) <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalPaymentMethod" name="payment_method_id" required>
                                    {{-- Dynamically populated --}}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modalPaidOn" class="form-label fw-bold">កាលបរិច្ឆេទបើក (Payment Date) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="modalPaidOn" name="paid_on" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label for="modalRemarks" class="form-label fw-semibold">កំណត់ចំណាំ / លេខយោង (Remarks / Reference No)</label>
                                <input type="text" class="form-control" id="modalRemarks" name="remarks" placeholder="ឧ. ABA Txn ID: 982348 ឬ សាច់ប្រាក់នៅបញ្ជរ">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-success fw-bold px-4" id="btnConfirmDisburse" data-payment-allowed="{{ auth('admin')->check() || Gate::allows('payroll_payment') ? '1' : '0' }}" disabled>
                        <i class="link-icon me-1" data-feather="check"></i> បញ្ជាក់បើកប្រាក់ខែ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
