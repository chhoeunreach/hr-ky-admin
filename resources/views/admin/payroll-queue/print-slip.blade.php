<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ប័ណ្ណបើកប្រាក់ខែ - {{ $ticket->employee?->name ?: $ticket->ticket_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Kantumruy Pro', 'Khmer OS Battambang', Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #222;
            font-size: 13px;
        }
        .receipt-container {
            max-width: 480px;
            margin: 0 auto;
            border: 1px dashed #bbb;
            padding: 20px;
            border-radius: 8px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .header { margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: 700; }
        .title { font-size: 15px; font-weight: 600; margin-top: 4px; color: #0d6efd; }
        .meta-table, .data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta-table td { padding: 3px 0; }
        .data-table th, .data-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .data-table th { background: #f8f9fa; font-size: 12px; text-align: left; }
        .total-box {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            padding: 10px;
            border-radius: 6px;
            margin: 12px 0;
            text-align: center;
        }
        .total-amount { font-size: 22px; font-weight: 700; color: #2e7d32; }
        .signatures { display: flex; justify-content: space-between; margin-top: 35px; text-align: center; }
        .signature-line { margin-top: 45px; border-top: 1px dotted #888; width: 140px; }
        @media print {
            body { padding: 0; }
            .receipt-container { border: none; padding: 10px; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header text-center">
        <div class="company-name">{{ $ticket->session?->company?->name ?: config('app.name', 'HR Management') }}</div>
        <div class="title">បង្កាន់ដៃបើកប្រាក់ខែ (Payday Voucher)</div>
        <div style="font-size: 11px; color: #666; margin-top: 2px;">
            សំបុត្រលេខ: <strong>{{ $ticket->ticket_number }}</strong> | 
            កាលបរិច្ឆេទ: {{ $ticket->paid_at ? $ticket->paid_at->format('d/m/Y h:i A') : date('d/m/Y') }}
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 30%;">ឈ្មោះបុគ្គលិក:</td>
            <td><strong>{{ $ticket->employee?->name ?: '—' }}</strong> ({{ $ticket->employee?->employee_code ?: $ticket->employee?->username }})</td>
        </tr>
        <tr>
            <td>ផ្នែក / សាខា:</td>
            <td>{{ $ticket->employee?->department?->dept_name ?: '—' }} / {{ $ticket->employee?->branch?->name ?: '—' }}</td>
        </tr>
        <tr>
            <td>បើកប្រាក់ខែប្រចាំ:</td>
            <td><strong>{{ $ticket->session?->month ? \Carbon\Carbon::createFromFormat('!Y-m', $ticket->session->month)->translatedFormat('F Y') : '—' }}</strong></td>
        </tr>
        <tr>
            <td>វិធីសាស្ត្រទូទាត់:</td>
            <td>{{ $ticket->paymentMethod?->name ?: 'សាច់ប្រាក់សុទ្ធ (Cash)' }}</td>
        </tr>
    </table>

    @if($breakdown)
        <div style="font-weight: 600; margin-bottom: 4px; font-size: 12px; color: #198754;">ចំណូលបន្ថែម និងកម្រៃជើងសារ (Earnings & Commissions)</div>
        <table class="data-table">
            <tr>
                <td>ប្រាក់ខែគោល (Basic)</td>
                <td class="text-end">${{ number_format($breakdown['earnings']['basic_salary'] ?? 0, 2) }}</td>
            </tr>
            @if(($breakdown['earnings']['sell'] ?? 0) > 0)
                <tr>
                    <td>កម្រៃជើងសារ លក់ (Sales)</td>
                    <td class="text-end">+${{ number_format($breakdown['earnings']['sell'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['earnings']['iron'] ?? 0) > 0)
                <tr>
                    <td>កម្រៃជើងសារ អ៊ុត (Ironing)</td>
                    <td class="text-end">+${{ number_format($breakdown['earnings']['iron'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['earnings']['material'] ?? 0) > 0)
                <tr>
                    <td>កម្រៃជើងសារ សម្ភារ (Materials)</td>
                    <td class="text-end">+${{ number_format($breakdown['earnings']['material'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['earnings']['attendance_bonus'] ?? 0) > 0)
                <tr>
                    <td>ប្រាក់រង្វាន់ មិនឈប់ (Attendance)</td>
                    <td class="text-end">+${{ number_format($breakdown['earnings']['attendance_bonus'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['earnings']['punctuality_bonus'] ?? 0) > 0)
                <tr>
                    <td>ប្រាក់រង្វាន់ ទៀងទាត់ (Punctuality)</td>
                    <td class="text-end">+${{ number_format($breakdown['earnings']['punctuality_bonus'], 2) }}</td>
                </tr>
            @endif
            <tr style="background: #fdfdfd; font-weight: 600;">
                <td>សរុបចំណូល (Gross Total)</td>
                <td class="text-end" style="color: #198754;">${{ number_format($breakdown['earnings']['gross_total'] ?? 0, 2) }}</td>
            </tr>
        </table>

        <div style="font-weight: 600; margin-bottom: 4px; font-size: 12px; color: #dc3545;">ការកាត់ប្រាក់ខែ (Deductions)</div>
        <table class="data-table">
            @if(($breakdown['deductions']['leave'] ?? 0) > 0)
                <tr>
                    <td>កាត់ច្បាប់ (Leave/Absence)</td>
                    <td class="text-end">-${{ number_format($breakdown['deductions']['leave'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['deductions']['late_undertime'] ?? 0) > 0)
                <tr>
                    <td>កាត់យឺត / ចេញមុន (Late/Undertime)</td>
                    <td class="text-end">-${{ number_format($breakdown['deductions']['late_undertime'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['deductions']['salary_advance'] ?? 0) > 0)
                <tr>
                    <td>កាត់ខ្ចីលុយ (Advance Salary)</td>
                    <td class="text-end">-${{ number_format($breakdown['deductions']['salary_advance'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['deductions']['installment'] ?? 0) > 0)
                <tr>
                    <td>កាត់បង់រំលស់ (Installment)</td>
                    <td class="text-end">-${{ number_format($breakdown['deductions']['installment'], 2) }}</td>
                </tr>
            @endif
            @if(($breakdown['deductions']['penalty'] ?? 0) > 0)
                <tr>
                    <td>កាត់ពិន័យ (Penalty)</td>
                    <td class="text-end">-${{ number_format($breakdown['deductions']['penalty'], 2) }}</td>
                </tr>
            @endif
            <tr style="background: #fdfdfd; font-weight: 600;">
                <td>សរុបប្រាក់កាត់ (Total Deductions)</td>
                <td class="text-end" style="color: #dc3545;">-${{ number_format($breakdown['deductions']['total'] ?? 0, 2) }}</td>
            </tr>
        </table>
    @endif

    <div class="total-box">
        <div style="font-size: 12px; color: #555; text-transform: uppercase;">ប្រាក់ខែសុទ្ធបានបើក (Net Amount Paid)</div>
        <div class="total-amount">${{ number_format($ticket->net_amount_paid ?: ($breakdown['net_salary'] ?? 0), 2) }}</div>
    </div>

    @if($ticket->remarks)
        <div style="font-size: 11px; color: #666; margin-bottom: 10px;">
            សម្គាល់: {{ $ticket->remarks }}
        </div>
    @endif

    <div class="signatures">
        <div>
            <div>ហត្ថលេខាអ្នកបើក</div>
            <div class="signature-line"></div>
            <div style="font-size: 11px; margin-top: 3px;">{{ $ticket->disbursedBy?->name ?: 'HR Officer' }}</div>
        </div>
        <div>
            <div>ហត្ថលេខាអ្នកទទួល</div>
            <div class="signature-line"></div>
            <div style="font-size: 11px; margin-top: 3px;">{{ $ticket->employee?->name ?: 'បុគ្គលិក' }}</div>
        </div>
    </div>

    <div class="no-print text-center" style="margin-top: 25px;">
        <button onclick="window.print()" style="padding: 8px 20px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
            🖨️ បោះពុម្ពប័ណ្ណ
        </button>
        <button onclick="window.close()" style="padding: 8px 15px; background: #6c757d; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-left: 8px;">
            បិទ
        </button>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        window.print();
    });
</script>

</body>
</html>
