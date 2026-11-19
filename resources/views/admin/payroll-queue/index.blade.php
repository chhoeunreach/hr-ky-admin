@extends('layouts.master')

@section('title', 'ប្រព័ន្ធគ្រប់គ្រងជួរ និងរង់ចាំបើកប្រាក់ខែ (Payday Queue)')

@section('action_button')
    <a href="{{ route('admin.payroll-queue.display', ['sessionId' => $session->id]) }}" 
       target="_blank" class="btn btn-dark btn-sm me-2">
        <i class="link-icon me-1" data-feather="tv"></i> អេក្រង់ធំ (TV Display)
    </a>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal">
        <i class="link-icon me-1" data-feather="qr-code"></i> បង្ហាញ QR កូដ
    </button>
@endsection

@section('main-content')
<div class="container-fluid py-2">
    {{-- Header Banner & Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <h4 class="fw-bold mb-1 text-primary">
                        <i class="link-icon me-1" data-feather="users"></i> ប្រព័ន្ធគ្រប់គ្រងជួរបើកប្រាក់ខែ
                    </h4>
                    <p class="text-muted small mb-0">
                        {{ $session->title }} | វគ្គកូដ: <code>{{ $session->session_code }}</code>
                    </p>
                </div>
                <div class="col-md-7">
                    <form action="{{ route('admin.payroll-queue.index') }}" method="GET" class="row g-2 justify-content-md-end align-items-center">
                        <div class="col-auto">
                            <label class="form-label small mb-0 text-muted">ខែបើកប្រាក់:</label>
                            <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label small mb-0 text-muted">សាខា:</label>
                            <select name="branch_id" class="form-select form-select-sm">
                                <option value="">គ្រប់សាខាទាំងអស់</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto align-self-end">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="link-icon" data-feather="filter"></i> ចម្រាញ់
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">បុគ្គលិកចូលជួរសរុប</div>
                        <div class="fs-4 fw-bold" id="statTotalJoined">{{ $stats['total_joined'] }} នាក់</div>
                    </div>
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                        <i class="link-icon text-white" data-feather="users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-dark-50 small fw-semibold">កំពុងរង់ចាំ (Waiting)</div>
                        <div class="fs-4 fw-bold" id="statTotalWaiting">{{ $stats['total_waiting'] }} នាក់</div>
                    </div>
                    <div class="p-2 bg-dark bg-opacity-10 rounded-circle">
                        <i class="link-icon text-dark" data-feather="clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">កំពុងបម្រើ (Now Serving)</div>
                        <div class="fs-4 fw-bold" id="statTotalServing">{{ $stats['total_serving'] }} / 5 តុ</div>
                    </div>
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                        <i class="link-icon text-white" data-feather="monitor"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">បើកប្រាក់ខែជោគជ័យ (Paid)</div>
                        <div class="fs-4 fw-bold" id="statTotalPaid">{{ $stats['total_paid'] }} នាក់ (${{ number_format($stats['total_amount_paid'], 2) }})</div>
                    </div>
                    <div class="p-2 bg-white bg-opacity-25 rounded-circle">
                        <i class="link-icon text-white" data-feather="check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel 1: Now Serving (5 Counters) --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary p-2 rounded-circle me-2">
                    <i class="link-icon text-white" data-feather="layers" style="width: 18px; height: 18px;"></i>
                </span>
                <div>
                    <h5 class="card-title mb-0 fw-bold">ផ្នែកទី ១៖ កំពុងបម្រើសេវា (Now Serving - ៥ នាក់ / តុ ១-៥)</h5>
                    <small class="text-muted">បញ្ជរបើកប្រាក់ខែសកម្មសម្រាប់បុគ្គលិកដែលកំពុងត្រូវបានហៅ</small>
                </div>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnRefreshStatus" title="ទាញទិន្នន័យថ្មី">
                <i class="link-icon" data-feather="refresh-cw"></i> ធ្វើបច្ចុប្បន្នភាព
            </button>
        </div>
        <div class="card-body p-3" id="servingCountersContainer">
            @include('admin.payroll-queue.partials.serving-counters', ['counters' => $counters, 'session' => $session])
        </div>
    </div>

    {{-- Panel 2: Queue Lists (Waiting List & Paid Successfully) --}}
    <div id="waitingPaidPanelsContainer">
        @include('admin.payroll-queue.partials.waiting-paid-panels', [
            'waiting_tickets' => $waiting_tickets,
            'paid_tickets' => $paid_tickets,
            'stats' => $stats,
            'session' => $session,
        ])
    </div>
</div>

{{-- QR Code Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="qrModalLabel">
                    <i class="link-icon me-1" data-feather="qr-code"></i> QR កូដសម្រាប់បុគ្គលិកស្កេនចូលជួរ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <h5 class="fw-bold mb-1">{{ $session->title }}</h5>
                <p class="text-muted small mb-3">ស្កេនដើម្បីចូលជួររង់ចាំបើកប្រាក់ខែតាមទូរស័ព្ទ</p>
                <div class="p-3 bg-light border rounded d-inline-block mb-3 shadow-sm">
                    {!! $qrSvg !!}
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm text-center" id="joinUrlInput" value="{{ $joinUrl }}" readonly>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="btnCopyJoinUrl">
                        <i class="link-icon" data-feather="copy"></i> ចម្លង
                    </button>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ $joinUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="link-icon me-1" data-feather="external-link"></i> បើកតំណភ្ជាប់
                    </a>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                        <i class="link-icon me-1" data-feather="printer"></i> បោះពុម្ព QR
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Disburse Salary Modal --}}
@include('admin.payroll-queue.partials.disburse-modal')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sessionId = {{ $session->id }};
    const liveStatusUrl = "{{ route('admin.payroll-queue.live-status', ['sessionId' => $session->id]) }}";
    const callNextUrl = "{{ route('admin.payroll-queue.call-next', ['sessionId' => $session->id]) }}";

    // Pleasant 2-tone airport/bank counter audio chime
    function playChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now); // D5
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.5);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.2); // A5
            gain2.gain.setValueAtTime(0.25, now + 0.2);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.2);
            osc2.stop(now + 0.8);
        } catch (e) {
            console.log('Audio chime not supported or blocked');
        }
    }

    // Feather icons re-render helper
    function reInitFeather() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    // Refresh live status via AJAX
    function refreshLiveStatus() {
        fetch(liveStatusUrl)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('servingCountersContainer').innerHTML = data.counters_html;
                    document.getElementById('waitingPaidPanelsContainer').innerHTML = data.panels_html;
                    
                    if (data.stats) {
                        document.getElementById('statTotalJoined').textContent = data.stats.total_joined + ' នាក់';
                        document.getElementById('statTotalWaiting').textContent = data.stats.total_waiting + ' នាក់';
                        document.getElementById('statTotalServing').textContent = data.stats.total_serving + ' / 5 តុ';
                        document.getElementById('statTotalPaid').textContent = data.stats.total_paid + ' នាក់ ($' + Number(data.stats.total_amount_paid).toFixed(2) + ')';
                    }
                    reInitFeather();
                }
            })
            .catch(err => console.error('Error refreshing queue:', err));
    }

    // Auto-polling every 4 seconds
    let pollingInterval = setInterval(refreshLiveStatus, 4000);

    // Manual refresh button
    document.getElementById('btnRefreshStatus')?.addEventListener('click', function() {
        refreshLiveStatus();
    });

    // Copy Join URL button
    document.getElementById('btnCopyJoinUrl')?.addEventListener('click', function() {
        const input = document.getElementById('joinUrlInput');
        input.select();
        document.execCommand('copy');
        alert('បានចម្លងតំណភ្ជាប់ដោយជោគជ័យ!');
    });

    // Delegated click for Call Next (Counter 1..5)
    document.addEventListener('click', function (e) {
        const btnCallNext = e.target.closest('.btn-call-next');
        if (btnCallNext) {
            e.preventDefault();
            const counter = btnCallNext.dataset.counter;
            fetch(callNextUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ counter_number: counter })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playChime();
                    refreshLiveStatus();
                } else {
                    alert(data.message || 'មិនអាចហៅបានទេ!');
                }
            });
            return;
        }

        // Specific Call from Waiting List dropdown
        const btnCallSpecific = e.target.closest('.btn-call-specific');
        if (btnCallSpecific) {
            e.preventDefault();
            const counter = btnCallSpecific.dataset.counter;
            fetch(callNextUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ counter_number: counter })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playChime();
                    refreshLiveStatus();
                } else {
                    alert(data.message || 'មិនអាចហៅបានទេ!');
                }
            });
            return;
        }

        // Recall Ticket
        const btnRecall = e.target.closest('.btn-recall');
        if (btnRecall) {
            e.preventDefault();
            const ticketId = btnRecall.dataset.ticketId;
            fetch(`/admin/payroll-queue/ticket/${ticketId}/recall`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playChime();
                    refreshLiveStatus();
                }
            });
            return;
        }

        // Skip Ticket
        const btnSkip = e.target.closest('.btn-skip');
        if (btnSkip) {
            e.preventDefault();
            if (!confirm('តើអ្នកពិតជាចង់រំលងសំបុត្រនេះមែនទេ?')) return;
            const ticketId = btnSkip.dataset.ticketId;
            fetch(`/admin/payroll-queue/ticket/${ticketId}/skip`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                refreshLiveStatus();
            });
            return;
        }

        // Print Slip
        const btnPrintSlip = e.target.closest('.btn-print-slip');
        if (btnPrintSlip) {
            e.preventDefault();
            const ticketId = btnPrintSlip.dataset.ticketId;
            const printWindow = window.open(`/admin/payroll-queue/ticket/${ticketId}/print-slip`, '_blank', 'width=550,height=750');
            return;
        }

        // Open Disburse Modal
        const btnDisburse = e.target.closest('.btn-disburse');
        if (btnDisburse) {
            e.preventDefault();
            const ticketId = btnDisburse.dataset.ticketId;
            openDisburseModal(ticketId);
            return;
        }
    });

    // Open Disburse Modal & fetch breakdown
    const disburseModalEl = document.getElementById('disburseSalaryModal');
    const disburseModal = new bootstrap.Modal(disburseModalEl);
    const loadingSpinner = document.getElementById('modalLoadingSpinner');
    const contentContainer = document.getElementById('modalContentContainer');
    const btnConfirm = document.getElementById('btnConfirmDisburse');

    function openDisburseModal(ticketId) {
        document.getElementById('modalTicketId').value = ticketId;
        loadingSpinner.style.display = 'block';
        contentContainer.style.display = 'none';
        btnConfirm.disabled = true;
        disburseModal.show();

        fetch(`/admin/payroll-queue/ticket/${ticketId}/breakdown`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    alert(res.message || 'មិនអាចទាញទិន្នន័យបានទេ!');
                    disburseModal.hide();
                    return;
                }
                const d = res.data;
                const emp = d.employee;
                const ticket = d.ticket;
                const session = ticket.session;

                // Profile info
                document.getElementById('modalEmpName').textContent = emp.name || '—';
                document.getElementById('modalEmpCode').textContent = emp.employee_code || emp.username || '—';
                document.getElementById('modalEmpDept').textContent = emp.department ? emp.department.dept_name : '—';
                document.getElementById('modalEmpBranch').textContent = emp.branch ? emp.branch.name : '—';
                document.getElementById('modalTicketNumberBadge').textContent = ticket.ticket_number;
                document.getElementById('modalSalaryMonth').textContent = session ? session.month : '—';
                if (emp.avatar_url) {
                    document.getElementById('modalEmpAvatar').src = emp.avatar_url;
                }

                // Deductions breakdown
                const ded = d.deductions;
                document.getElementById('modalDedLeave').textContent = '-$' + Number(ded.leave).toFixed(2);
                document.getElementById('modalDedLate').textContent = '-$' + Number(ded.late_undertime).toFixed(2);
                document.getElementById('modalDedAdvance').textContent = '-$' + Number(ded.salary_advance).toFixed(2);
                document.getElementById('modalDedInstallment').textContent = '-$' + Number(ded.installment).toFixed(2);
                document.getElementById('modalDedPenalty').textContent = '-$' + Number(ded.penalty).toFixed(2);
                document.getElementById('modalDedOther').textContent = '-$' + Number(ded.other).toFixed(2);
                document.getElementById('modalTotalDeductions').textContent = '-$' + Number(ded.total).toFixed(2);
                document.getElementById('modalTotalDeductionBadge').textContent = '-$' + Number(ded.total).toFixed(2);

                // Earnings breakdown
                const earn = d.earnings;
                document.getElementById('modalEarnBasic').textContent = '$' + Number(earn.basic_salary).toFixed(2);
                document.getElementById('modalEarnSell').textContent = '+$' + Number(earn.sell).toFixed(2);
                document.getElementById('modalEarnIron').textContent = '+$' + Number(earn.iron).toFixed(2);
                document.getElementById('modalEarnMaterial').textContent = '+$' + Number(earn.material).toFixed(2);
                document.getElementById('modalEarnAttendance').textContent = '+$' + Number(earn.attendance_bonus).toFixed(2);
                document.getElementById('modalEarnPunctual').textContent = '+$' + Number(earn.punctuality_bonus).toFixed(2);
                document.getElementById('modalTotalEarnings').textContent = '+$' + Number(earn.gross_total).toFixed(2);
                document.getElementById('modalTotalGrossBadge').textContent = '+$' + Number(earn.gross_total).toFixed(2);

                // Net display & formula
                document.getElementById('modalNetSalaryDisplay').textContent = '$' + Number(d.net_salary).toFixed(2);
                document.getElementById('modalFormulaGross').textContent = '$' + Number(earn.gross_total).toFixed(2);
                document.getElementById('modalFormulaDed').textContent = '$' + Number(ded.total).toFixed(2);

                // Payment methods populate
                const selectPayment = document.getElementById('modalPaymentMethod');
                selectPayment.innerHTML = '';
                if (d.payment_methods && d.payment_methods.length > 0) {
                    d.payment_methods.forEach(pm => {
                        const opt = document.createElement('option');
                        opt.value = pm.id;
                        opt.textContent = pm.name;
                        selectPayment.appendChild(opt);
                    });
                } else {
                    selectPayment.innerHTML = '<option value="1">សាច់ប្រាក់សុទ្ធ (Cash)</option>';
                }

                loadingSpinner.style.display = 'none';
                contentContainer.style.display = 'block';
                btnConfirm.disabled = false;
                reInitFeather();
            })
            .catch(err => {
                alert('មានបញ្ហាក្នុងការទាញយកទិន្នន័យ!');
                disburseModal.hide();
            });
    }

    // Submit Disbursement
    document.getElementById('disburseSalaryForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const ticketId = document.getElementById('modalTicketId').value;
        const formData = new FormData(this);
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបើកប្រាក់ខែ...';

        fetch(`/admin/payroll-queue/ticket/${ticketId}/disburse`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="link-icon me-1" data-feather="check"></i> បញ្ជាក់បើកប្រាក់ខែ';
            
            if (data.success) {
                disburseModal.hide();
                // Open quick print slip
                window.open(`/admin/payroll-queue/ticket/${ticketId}/print-slip`, '_blank', 'width=550,height=750');
                refreshLiveStatus();
            } else {
                alert(data.message || 'មិនអាចបើកប្រាក់ខែបានទេ!');
            }
        })
        .catch(err => {
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="link-icon me-1" data-feather="check"></i> បញ្ជាក់បើកប្រាក់ខែ';
            alert('មានបញ្ហាបច្ចេកទេសក្នុងការបើកប្រាក់ខែ!');
        });
    });
});
</script>
<style>
.calling-pulse {
    animation: pulseBorder 1.5s infinite;
}
@keyframes pulseBorder {
    0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.6); }
    70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
}
.border-dashed {
    border: 2px dashed #dee2e6 !important;
}
</style>
@endpush
