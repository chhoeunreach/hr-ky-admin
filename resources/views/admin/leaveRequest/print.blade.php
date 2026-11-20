<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក - {{ $employee?->name ?: 'Employee' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Kantumruy Pro', 'Khmer OS Battambang', 'Segoe UI', Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 15px;
            background: #fff;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
        }
        .page {
            max-width: 780px;
            margin: 0 auto;
            padding: 25px 35px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            position: relative;
        }
        /* National Heading */
        .national-heading {
            text-align: center;
            margin-bottom: 20px;
        }
        .national-heading h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .national-heading p {
            margin: 3px 0 0 0;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            letter-spacing: 1px;
        }
        .national-rule {
            width: 70px;
            height: 2px;
            background: #0f172a;
            margin: 6px auto 0 auto;
        }
        /* Header / Letterhead */
        .letterhead {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .company-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .company-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 6px;
        }
        .company-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f766e;
        }
        .company-sub {
            font-size: 12px;
            color: #64748b;
        }
        .doc-meta {
            text-align: right;
            font-size: 12px;
            color: #475569;
        }
        .doc-meta strong {
            color: #0f172a;
        }
        /* Document Title */
        .doc-title-block {
            text-align: center;
            margin-bottom: 16px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title-sub {
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        /* Routing Block */
        .routing-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0f766e;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .routing-block span {
            color: #64748b;
            margin-right: 6px;
        }
        /* Sections */
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f766e;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        /* Grid Tables */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .grid-table td {
            padding: 5px 8px;
            border-bottom: 1px dashed #e2e8f0;
            vertical-align: top;
        }
        .grid-table td.label {
            color: #64748b;
            width: 25%;
            font-weight: 500;
        }
        .grid-table td.value {
            color: #0f172a;
            font-weight: 600;
            width: 25%;
        }
        /* Reason Box */
        .content-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12.5px;
            color: #1e293b;
            min-height: 40px;
        }
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
        }
        .status-approved { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .status-pending { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .status-rejected { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            margin-bottom: 10px;
            text-align: center;
        }
        .sig-block {
            width: 30%;
        }
        .sig-role {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 45px;
        }
        .sig-name {
            font-size: 12px;
            font-weight: 600;
            border-top: 1px dotted #94a3b8;
            padding-top: 4px;
            color: #0f172a;
        }
        .sig-date {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        /* Notes */
        .notice-block {
            background: #f1f5f9;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 11px;
            color: #475569;
            margin-top: 15px;
        }
        .notice-block strong { color: #0f172a; }
        .notice-block ol { margin: 3px 0 0 16px; padding: 0; }
        /* No-print toolbar */
        .print-toolbar {
            max-width: 780px;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #0f766e;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-close {
            background: #64748b;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        @media print {
            body { padding: 0; background: #fff; }
            .page { border: none; padding: 0; max-width: 100%; width: 100%; }
            .print-toolbar { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <div>
            <strong>ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក #{{ $leave->id }}</strong>
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ បោះពុម្ព (Print)</button>
            <button type="button" class="btn-close" onclick="window.close()">បិទ (Close)</button>
        </div>
    </div>

    <div class="page">
        <!-- National Heading -->
        <div class="national-heading">
            <h3>ព្រះរាជាណាចក្រកម្ពុជា</h3>
            <p>ជាតិ សាសនា ព្រះមហាក្សត្រ</p>
            <div class="national-rule"></div>
        </div>

        <!-- Letterhead -->
        <header class="letterhead">
            <div class="company-info">
                @php
                    $logoUrl = $branch?->logo ? asset(\App\Models\Branch::UPLOAD_PATH . $branch->logo) : asset('assets/images/logo.png');
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo" class="company-logo" onerror="this.style.display='none'">
                <div>
                    <div class="company-name">{{ $branch?->name ?: ($company?->name ?: config('app.name')) }}</div>
                    <div class="company-sub">នាយកដ្ឋានធនធានមនុស្ស (HR Department)</div>
                </div>
            </div>
            <div class="doc-meta">
                <div>លេខលិខិត: <strong>#LR-{{ str_pad($leave->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                <div>កាលបរិច្ឆេទ: <strong>{{ $leave->leave_requested_date ? \Carbon\Carbon::parse($leave->leave_requested_date)->format('d-M-Y') : date('d-M-Y') }}</strong></div>
            </div>
        </header>

        <!-- Document Title -->
        <div class="doc-title-block">
            <h1 class="doc-title">ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក</h1>
            <div class="doc-title-sub">LEAVE REQUEST FORM</div>
        </div>

        <!-- Routing -->
        <div class="routing-block">
            <div><span>សូមគោរពជូន:</span> <strong>លោក/លោកស្រី ប្រធានផ្នែក (Head of Department)</strong></div>
            <div><span>ឆ្លងកាត់:</span> <strong>លោក/លោកស្រី ប្រធានសាខា (Branch Manager)</strong></div>
            <div><span>ដាក់ជូន:</span> <strong>នាយកដ្ឋានធនធានមនុស្ស (Human Resources Department)</strong></div>
        </div>

        <!-- Section 1: Employee Information -->
        <div class="section">
            <div class="section-title">I. ព័ត៌មានបុគ្គលិក (Employee Information)</div>
            <table class="grid-table">
                <tr>
                    <td class="label">ឈ្មោះបុគ្គលិក:</td>
                    <td class="value">{{ $employee?->name ?: '—' }}</td>
                    <td class="label">ឈ្មោះជាឡាតាំង:</td>
                    <td class="value">{{ $employee?->english_name ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="label">អត្តលេខបុគ្គលិក:</td>
                    <td class="value">{{ $employee?->employee_code ?: ($employee?->username ?: '—') }}</td>
                    <td class="label">លេខទូរស័ព្ទ:</td>
                    <td class="value">{{ $employee?->phone ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="label">ផ្នែក / ការិយាល័យ:</td>
                    <td class="value">{{ $leave->department?->dept_name ?: ($employee?->department?->dept_name ?: '—') }}</td>
                    <td class="label">មុខតំណែង:</td>
                    <td class="value">{{ $employee?->post?->post_name ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="label">សាខា / ទីតាំង:</td>
                    <td class="value">{{ $branch?->name ?: '—' }}</td>
                    <td class="label">អ្នកគ្រប់គ្រងផ្ទាល់:</td>
                    <td class="value">{{ $employee?->supervisor?->name ?: '—' }}</td>
                </tr>
            </table>
        </div>

        <!-- Section 2: Leave Details -->
        <div class="section">
            <div class="section-title">II. ព័ត៌មានលម្អិតនៃការសុំច្បាប់ (Leave Details)</div>
            <table class="grid-table">
                <tr>
                    <td class="label">ប្រភេទច្បាប់:</td>
                    <td class="value">
                        <strong>{{ $leave->leaveType?->name ?: '—' }}</strong>
                        <span style="font-size: 11px; color: #64748b;">
                            ({{ !is_null($leave->leaveType?->leave_allocated) ? 'Paid Leave' : 'Unpaid Leave' }})
                        </span>
                    </td>
                    <td class="label">ចំនួនថ្ងៃសរុប:</td>
                    <td class="value">
                        <strong style="color: #0f766e; font-size: 14px;">{{ $leave->no_of_days }} ថ្ងៃ</strong> (Days)
                    </td>
                </tr>
                <tr>
                    <td class="label">ចាប់ពីថ្ងៃ:</td>
                    <td class="value"><strong>{{ \Carbon\Carbon::parse($leave->leave_from)->format('d-M-Y') }}</strong></td>
                    <td class="label">ដល់ថ្ងៃ:</td>
                    <td class="value"><strong>{{ \Carbon\Carbon::parse($leave->leave_to)->format('d-M-Y') }}</strong></td>
                </tr>
                @if($leave->start_time || $leave->end_time)
                <tr>
                    <td class="label">ម៉ោងចេញ/ចូល:</td>
                    <td class="value" colspan="3">{{ $leave->start_time ?: '—' }} ដល់ {{ $leave->end_time ?: '—' }}</td>
                </tr>
                @endif
            </table>
            
            <div style="margin-top: 8px;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 3px;">មូលហេតុនៃការសុំច្បាប់ (Reason for Leave):</div>
                <div class="content-box">
                    {{ $leave->reasons ?: 'មិនបានបញ្ជាក់មូលហេតុ' }}
                </div>
            </div>
        </div>

        <!-- Section 3: Work Handover -->
        <div class="section">
            <div class="section-title">III. ការផ្ទេរការងារ (Work Handover)</div>
            <table class="grid-table">
                <tr>
                    <td class="label">អ្នកទទួលខុសត្រូវជំនួស:</td>
                    <td class="value" style="color: #64748b;">___________________________</td>
                    <td class="label">លេខទំនាក់ទំនងពេលឈប់:</td>
                    <td class="value">{{ $employee?->phone ?: '___________________________' }}</td>
                </tr>
            </table>
        </div>

        <!-- Section 4: For Office Use -->
        <div class="section">
            <div class="section-title">IV. សម្រាប់រដ្ឋបាល និងធនធានមនុស្ស (For Office & HR Use)</div>
            <table class="grid-table">
                <tr>
                    <td class="label">ស្ថានភាពសំណើ:</td>
                    <td class="value">
                        @if($leave->status === 'approved')
                            <span class="status-badge status-approved">✔ បានអនុម័ត (Approved)</span>
                        @elseif($leave->status === 'rejected')
                            <span class="status-badge status-rejected">✖ បដិសេធ (Rejected)</span>
                        @else
                            <span class="status-badge status-pending">⏳ កំពុងរង់ចាំ (Pending)</span>
                        @endif
                    </td>
                    <td class="label">អនុម័តដោយ:</td>
                    <td class="value">{{ $leave->leaveRequestUpdatedBy?->name ?: 'Admin' }}</td>
                </tr>
                <tr>
                    <td class="label">មតិរដ្ឋបាល / មូលហេតុ:</td>
                    <td class="value" colspan="3">
                        {{ $leave->admin_remark ?: ($leave->status === 'approved' ? 'បានអនុម័តតាមការស្នើសុំ' : '—') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Section 5: Signatures -->
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-role">ហត្ថលេខាសាមីខ្លួន<br><small style="font-weight: normal; color: #64748b;">(Employee Signature)</small></div>
                <div class="sig-name">{{ $employee?->name ?: '....................................' }}</div>
                <div class="sig-date">កាលបរិច្ឆេទ: ...../...../202...</div>
            </div>

            <div class="sig-block">
                <div class="sig-role">ប្រធានផ្នែក / អ្នកគ្រប់គ្រង<br><small style="font-weight: normal; color: #64748b;">(Supervisor Signature)</small></div>
                <div class="sig-name">{{ $employee?->supervisor?->name ?: '....................................' }}</div>
                <div class="sig-date">កាលបរិច្ឆេទ: ...../...../202...</div>
            </div>

            <div class="sig-block">
                <div class="sig-role">នាយកដ្ឋានធនធានមនុស្ស<br><small style="font-weight: normal; color: #64748b;">(HR Department Approval)</small></div>
                <div class="sig-name">{{ $leave->leaveRequestUpdatedBy?->name ?: '....................................' }}</div>
                <div class="sig-date">កាលបរិច្ឆេទ: ...../...../202...</div>
            </div>
        </div>

        <!-- Notice -->
        <div class="notice-block">
            <strong>កំណត់សម្គាល់ (Notice):</strong>
            <ol>
                <li>បុគ្គលិកត្រូវដាក់ពាក្យស្នើសុំច្បាប់យ៉ាងតិច ៣ ថ្ងៃមុនកាលបរិច្ឆេទឈប់សម្រាក (លើកលែងតែករណីជំងឺ ឬមានការបន្ទាន់)។</li>
                <li>ករណីឈប់សម្រាកព្យាបាលជំងឺលើសពី ២ ថ្ងៃ ត្រូវភ្ជាប់មកជាមួយនូវលិខិតបញ្ជាក់សុខភាពពីវេជ្ជបណ្ឌិត ឬមន្ទីរពេទ្យទទួលស្គាល់ជាផ្លូវការ។</li>
            </ol>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Auto trigger print if requested or opened standalone
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1' || window.parent === window) {
                setTimeout(function() {
                    window.print();
                }, 300);
            }
        });
    </script>
</body>
</html>
