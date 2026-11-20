<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('index.leave_request_form') }} - {{ $employee?->name ?: 'Employee' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/employee-leave-print.css') }}">
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 10mm 6mm 10mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            background: #eef1f5;
            color: #111827;
            font-family: "Kantumruy Pro", "Battambang", "Noto Sans Khmer", Arial, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            margin: 0;
            padding: 15px 0;
        }
        .print-toolbar {
            max-width: 195mm;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 4px;
        }
        .btn-print {
            background: #0f766e;
            color: #ffffff;
            border: none;
            padding: 7px 18px;
            border-radius: 4px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-close {
            background: #64748b;
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .employee-leave-letter-paper {
            --certificate-ink: #111827;
            --certificate-accent: #111827;
            background: #ffffff;
            border: 1px solid #64748b;
            border-top: 4px solid #111827;
            max-width: 195mm;
            min-height: 275mm;
            margin: 0 auto;
            padding: 8mm 12mm;
            box-shadow: 0 3px 18px rgba(15, 23, 42, 0.12);
            position: relative;
        }
        .employee-resignation-national-heading {
            text-align: center;
            margin-bottom: 3mm;
            line-height: 1.6;
        }
        .employee-resignation-national-heading strong {
            display: block;
            font-size: 12pt;
            font-weight: 700;
            color: #111827;
        }
        .employee-resignation-national-heading span {
            display: block;
            font-size: 10pt;
            font-weight: 600;
            color: #111827;
        }
        .employee-salary-certificate-letterhead {
            align-items: flex-start;
            border-bottom: 1px solid #475569;
            display: flex;
            justify-content: space-between;
            padding-bottom: 6px;
            margin-bottom: 4px;
        }
        .employee-salary-certificate-company {
            align-items: center;
            display: flex;
            gap: 10px;
            min-width: 0;
        }
        .employee-salary-certificate-logo {
            height: 48px;
            width: 48px;
            object-fit: contain;
        }
        .employee-salary-certificate-company-name {
            color: #111827;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
        }
        .employee-salary-certificate-company-subtitle {
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .employee-salary-certificate-document-meta {
            background: #ffffff;
            border: 1px solid #64748b;
            display: grid;
            font-size: 8.5pt;
            gap: 2px 8px;
            grid-template-columns: auto auto;
            padding: 3px 8px;
            text-align: right;
        }
        .employee-salary-certificate-document-meta span {
            color: #475569;
            font-weight: 700;
        }
        .employee-salary-certificate-document-meta input {
            border: 0;
            background: transparent;
            font-weight: 700;
            text-align: right;
            color: #111827;
            padding: 0;
            width: auto;
        }
        .employee-print-form-heading {
            margin: 6px 0 8px;
            text-align: center;
        }
        .employee-print-form-heading h2 {
            color: #111827;
            font-family: "Khmer OS Muol Light", "Noto Serif Khmer", "Kantumruy Pro", serif;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0;
            margin: 0;
            text-transform: uppercase;
        }
        .employee-print-form-title-rule {
            background: #111827;
            height: 1px;
            margin: 3px auto 0;
            width: 66mm;
        }
        .employee-print-form input,
        .employee-print-form textarea {
            color: #111827;
            font: inherit;
        }
        .employee-print-form-input {
            background: transparent;
            border: 0;
            border-bottom: 1px dotted #475569;
            border-radius: 0;
            min-width: 0;
            outline: 0;
            padding: 1px 3px;
            width: 100%;
        }
        .employee-print-form-routing {
            border-bottom: 1px solid #64748b;
            border-top: 1px solid #64748b;
            margin: 6px 0 8px;
            padding: 5px 8px;
        }
        .employee-print-form-routing > strong {
            display: block;
            font-size: 10.5px;
            margin-bottom: 3px;
            font-weight: 800;
        }
        .employee-print-form-routing > div {
            align-items: end;
            display: grid;
            gap: 6px;
            grid-template-columns: minmax(130px, auto) minmax(180px, 1fr);
            margin: 2px 0;
            font-size: 9.5pt;
        }
        .employee-print-form-section {
            border: 1px solid #64748b;
            margin-top: 6px;
            padding: 6px 8px 7px;
        }
        .employee-print-form-section h3 {
            background: #e5e7eb;
            border-bottom: 1px solid #64748b;
            color: #111827;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0;
            margin: -6px -8px 6px;
            padding: 4px 8px;
        }
        .employee-print-form-grid {
            display: grid;
            gap: 5px 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .employee-print-form-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .employee-print-form-field {
            align-items: baseline;
            border-bottom: 1px dotted #64748b;
            display: grid;
            gap: 6px;
            grid-template-columns: auto minmax(0, 1fr);
            min-height: 22px;
            padding: 2px 2px 1px;
        }
        .employee-print-form-field span {
            color: #475569;
            font-weight: 700;
        }
        .employee-print-form-field strong {
            overflow-wrap: anywhere;
            color: #111827;
        }
        .employee-print-form-field input {
            background: transparent;
            border: 0;
            min-width: 0;
            outline: 0;
            padding: 0;
            width: 100%;
            font-weight: 700;
        }
        .employee-print-form-options {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 7px 14px;
            margin: 3px 0 6px;
        }
        .employee-print-form-options > span {
            font-weight: 800;
        }
        .employee-print-form-textarea {
            background-color: transparent;
            background-image: repeating-linear-gradient(to bottom, transparent 0, transparent 20px, #64748b 21px);
            border: 0;
            border-radius: 0;
            line-height: 21px;
            min-height: 42px;
            outline: 0;
            padding: 0 3px;
            resize: none;
            width: 100%;
            display: block;
            font-size: 10px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
        .employee-print-form-textarea.compact {
            min-height: 38px;
        }
        .employee-print-form-approval {
            background: #f3f4f6;
        }
        .employee-print-form-signatures {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 14mm;
            text-align: center;
            break-inside: avoid;
        }
        .employee-print-form-signatures > div {
            border-top: 1px solid #111827;
            display: flex;
            flex-direction: column;
            padding-top: 4px;
        }
        .employee-print-form-signatures span {
            font-weight: 800;
            font-size: 10px;
        }
        .employee-print-form-signatures strong {
            min-height: 4px;
            font-size: 9.5px;
        }
        .employee-print-form-signatures small {
            color: #475569;
            font-size: 8.5px;
        }
        .employee-print-form-notes {
            border-top: 1px solid #64748b;
            font-size: 8px;
            line-height: 1.4;
            margin-top: 8px;
            padding-top: 4px;
            break-inside: avoid;
        }
        .employee-print-form-notes ol {
            margin: 2px 0 0;
            padding-left: 16px;
        }
        .employee-print-form-notes li {
            margin-bottom: 1px;
        }
        .employee-request-print-record {
            color: #475569;
            font-size: 8px;
            margin-top: 3mm;
            text-align: right;
        }
        @media print {
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-toolbar {
                display: none !important;
            }
            .employee-leave-letter-paper {
                border: 0 !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 0 !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .employee-print-form-textarea {
                overflow: visible !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <div>
            <strong>{{ __('index.leave_request_form') }} #{{ $leave->id }}</strong>
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ {{ __('index.print') ?? 'Print' }}</button>
            <button type="button" class="btn-close" onclick="window.close()">{{ __('index.close') ?? 'Close' }}</button>
        </div>
    </div>

    @php
        $notAvailable = __('index.not_available') ?? '—';
        $certificateLogo = $branch?->logo
            ? asset(\App\Models\Branch::UPLOAD_PATH . $branch->logo)
            : ($employee?->branch?->logo ? asset(\App\Models\Branch::UPLOAD_PATH . $employee->branch->logo) : asset('assets/images/logo.png'));
    @endphp

    <div class="employee-complete-paper employee-leave-letter-paper employee-print-form" data-source-request-id="{{ $leave->id }}" data-request-tab="leave-letter">
        <div class="employee-resignation-national-heading">
            <strong>{{ __('index.kingdom_of_cambodia') }}</strong>
            <span>{{ __('index.nation_religion_king') }}</span>
        </div>

        <header class="employee-salary-certificate-letterhead">
            <div class="employee-salary-certificate-company">
                <img class="employee-salary-certificate-logo" src="{{ $certificateLogo }}" alt="Company Logo" onerror="this.style.display='none'">
                <div>
                    <div class="employee-salary-certificate-company-name">{{ $branch?->name ?: ($employee?->branch?->name ?: ($company?->name ?: config('app.name'))) }}</div>
                    <div class="employee-salary-certificate-company-subtitle">{{ __('index.hr_department') }}</div>
                </div>
            </div>
            <div class="employee-salary-certificate-document-meta employee-print-form-meta">
                <span>{{ __('index.form_number') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.form_number') }}" value="{{ $leave->id }}" readonly>
                <span>{{ __('index.request_date') }}</span><input class="employee-print-form-input" type="text" aria-label="{{ __('index.request_date') }}" value="{{ $leave->leave_requested_date ? \Illuminate\Support\Carbon::parse($leave->leave_requested_date)->format('d M Y') : date('d M Y') }}" readonly>
            </div>
        </header>

        <div class="employee-salary-certificate-heading employee-print-form-heading">
            <h2>{{ __('index.leave_request_form') }}</h2>
            <div class="employee-print-form-title-rule"></div>
        </div>

        <section class="employee-print-form-routing">
            <strong>{{ __('index.respectfully_submitted_to') }}</strong>
            <div><span>{{ __('index.through_department_head') }}</span><input class="employee-print-form-input" type="text" value="{{ $employee?->supervisor?->name ?: '' }}"></div>
            <div><span>{{ __('index.through_branch_manager') }}</span><input class="employee-print-form-input" type="text"></div>
            <div><span>{{ __('index.submitted_to_hr') }}</span><input class="employee-print-form-input" type="text" value="{{ $leave->leaveRequestUpdatedBy?->name ?: '' }}"></div>
        </section>

        <section class="employee-print-form-section">
            <h3>{{ __('index.employee_information') }}</h3>
            <div class="employee-print-form-grid">
                <div class="employee-print-form-field"><span>{{ __('index.employee_name') }}</span><strong>{{ $employee?->name ?: ($employee?->english_name ?: $notAvailable) }}</strong></div>
                <div class="employee-print-form-field"><span>{{ __('index.employee_id') }}</span><strong>{{ $employee?->employee_code ?: ($employee?->username ?: $notAvailable) }}</strong></div>
                <div class="employee-print-form-field"><span>{{ __('index.department') }}</span><strong>{{ $leave->department?->dept_name ?: ($employee?->department?->dept_name ?: $notAvailable) }}</strong></div>
                <div class="employee-print-form-field"><span>{{ __('index.position') }}</span><strong>{{ $employee?->post?->post_name ?: $notAvailable }}</strong></div>
                <div class="employee-print-form-field"><span>{{ __('index.phone') }}</span><strong>{{ $employee?->phone ?: $notAvailable }}</strong></div>
                <div class="employee-print-form-field"><span>{{ __('index.direct_supervisor') }}</span><strong>{{ $employee?->supervisor?->name ?: $notAvailable }}</strong></div>
            </div>
        </section>

        <section class="employee-print-form-section">
            <h3>{{ __('index.leave_details') }}</h3>
            <div class="employee-print-form-field"><span>{{ __('index.leave_type') }}</span><strong>{{ $leave->leaveType?->name ?: $notAvailable }}</strong></div>
            <div class="employee-print-form-grid employee-print-form-grid-3">
                <div class="employee-print-form-field"><span>{{ __('index.leave_from') }}</span><strong>{{ $leave->leave_from ? \Illuminate\Support\Carbon::parse($leave->leave_from)->format('d M Y') : $notAvailable }}</strong></div>
                <div class="employee-print-form-field"><span>{{ __('index.leave_to') }}</span><strong>{{ $leave->leave_to ? \Illuminate\Support\Carbon::parse($leave->leave_to)->format('d M Y') : $notAvailable }}</strong></div>
                <div class="employee-print-form-field is-blank"><span>{{ __('index.number_of_days') }}</span><input type="number" min="0" step="0.5" value="{{ $leave->no_of_days }}" readonly></div>
            </div>
        </section>

        <section class="employee-print-form-section">
            <h3>{{ __('index.reason_for_leave') }}</h3>
            <textarea class="employee-print-form-textarea" rows="3" aria-label="{{ __('index.reason_for_leave') }}" readonly>{{ $leave->reasons }}</textarea>
        </section>

        <section class="employee-print-form-section">
            <h3>{{ __('index.work_handover') }}</h3>
            <div class="employee-print-form-grid">
                <div class="employee-print-form-field is-blank"><span>{{ __('index.handover_to') }}</span><input type="text"></div>
                <div class="employee-print-form-field is-blank"><span>{{ __('index.contact_during_leave') }}</span><input type="text" value="{{ $employee?->phone ?: '' }}"></div>
            </div>
            <textarea class="employee-print-form-textarea compact" rows="2" aria-label="{{ __('index.work_handover') }}"></textarea>
        </section>

        <section class="employee-print-form-section employee-print-form-approval">
            <h3>{{ __('index.for_office_use') }}</h3>
            <div class="employee-print-form-options">
                <span>{{ __('index.status') }}:</span>
                <strong>{{ $leave ? __('index.' . $leave->status) : $notAvailable }}</strong>
            </div>
            <textarea class="employee-print-form-textarea compact" rows="2" placeholder="{{ __('index.remark') }}" aria-label="{{ __('index.remark') }}" readonly>{{ $leave->admin_remark }}</textarea>
        </section>

        <div class="employee-print-form-signatures">
            <div><span>{{ __('index.employee_signature') }}</span><strong>{{ $employee?->name }}</strong><small>{{ __('index.date') }}: ____________</small></div>
            <div><span>{{ __('index.supervisor_signature') }}</span><strong>{{ $employee?->supervisor?->name }}</strong><small>{{ __('index.date') }}: ____________</small></div>
            <div><span>{{ __('index.hr_approval') }}</span><strong>{{ $leave->leaveRequestUpdatedBy?->name }}</strong><small>{{ __('index.date') }}: ____________</small></div>
        </div>

        <div class="employee-print-form-notes">
            <strong>{{ __('index.note') }}:</strong>
            <ol>
                <li>{{ __('index.leave_form_notice_advance') }}</li>
                <li>{{ __('index.leave_form_notice_medical') }}</li>
            </ol>
        </div>
        <div class="employee-request-print-record" data-print-record>{{ __('index.print_record_number') }}: #{{ $leave->id }}</div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1' || window.parent === window) {
                setTimeout(function() {
                    window.print();
                }, 250);
            }
        });
    </script>
</body>
</html>
