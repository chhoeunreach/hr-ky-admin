<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>Blank Employee Performance Evaluation</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: "Noto Sans Khmer", Arial, sans-serif; color: #111827; font-size: 11px; line-height: 1.35; }
        .no-print { margin-bottom: 10px; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 10px; }
        .header h2, .header h3, .header p { margin: 2px 0; }
        .info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px 18px; margin-bottom: 10px; }
        .info div { min-height: 18px; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #111827; padding: 4px; vertical-align: top; }
        th { background: #f3f4f6; text-align: center; }
        .question { min-width: 230px; }
        .score-cell { width: 22px; height: 22px; text-align: center; }
        .box { display: inline-block; width: 12px; height: 12px; border: 1px solid #111827; }
        .comment { min-width: 105px; height: 28px; }
        .notes { margin-top: 12px; }
        .note-line { border-bottom: 1px solid #111827; min-height: 24px; margin-bottom: 8px; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 24px; text-align: center; }
        .line { border-bottom: 1px solid #111827; height: 30px; margin-bottom: 6px; }
        .rating { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px 12px; margin: 8px 0 10px; font-size: 10px; }
        @media print { .no-print { display: none; } body { font-size: 10.5px; } }
    </style>
</head>
<body>
<button class="no-print" onclick="window.print()">Print A4</button>

<div class="header">
    <h2>KNEAYERNG</h2>
    <h3>ឯកសារប្រវត្តិបុគ្គលិក និងការវាយតម្លៃសមត្ថភាពពេញលេញ</h3>
    <h3>COMPLETE EMPLOYEE PROFILE & PERFORMANCE EVALUATION</h3>
    <p>ឯកសារផ្ទៃក្នុង - Confidential HR Record</p>
</div>

<div class="info">
    <div>ឈ្មោះ: {{ $template->employee->english_name ?: $template->employee->name }}</div>
    <div>Employee ID: {{ $template->employee->employee_code ?: $template->employee->username }}</div>
    <div>តួនាទី: {{ $template->employee->post?->post_name ?: 'N/A' }}</div>
    <div>ផ្នែក: {{ $template->employee->department?->dept_name ?: 'N/A' }}</div>
    <div>សាខា: {{ $template->employee->branch?->name ?: 'N/A' }}</div>
    <div>រយៈពេលវាយតម្លៃ: {{ $template->jobDescription?->interview?->evaluation_period ?: '________________' }}</div>
    <div>អ្នកវាយតម្លៃ: {{ $template->jobDescription?->interview?->evaluator?->name ?: '________________' }}</div>
    <div>កាលបរិច្ឆេទ: ________________</div>
    <div>Document No: {{ $template->jobDescription?->document_number ?: '________________' }}</div>
    <div>Version: v{{ $template->version ?: $template->jobDescription?->version ?: 1 }}</div>
</div>

<div class="rating">
    <div>5 = ល្អឥតខ្ចោះ</div>
    <div>4 = ល្អណាស់</div>
    <div>3 = ល្អ</div>
    <div>2 = ត្រូវកែលម្អ</div>
    <div>1 = មិនទាន់សមស្រប</div>
    <div>N/A = មិនពាក់ព័ន្ធ</div>
</div>

<table>
    <thead>
    <tr>
        <th>ល.រ</th>
        <th class="question">សំណួរវាយតម្លៃ</th>
        <th>Weight</th>
        <th>5</th>
        <th>4</th>
        <th>3</th>
        <th>2</th>
        <th>1</th>
        <th>N/A</th>
        <th class="comment">មតិ / ចម្លើយ</th>
    </tr>
    </thead>
    <tbody>
    @foreach($template->questions as $question)
        <tr>
            <td style="text-align:center">{{ $loop->iteration }}</td>
            <td>
                <strong>{{ $question->question_kh }}</strong>
                @if($question->question_en)
                    <br><small>{{ $question->question_en }}</small>
                @endif
            </td>
            <td style="text-align:center">{{ $question->weight }}%</td>
            @for($score = 5; $score >= 1; $score--)
                <td class="score-cell"><span class="box"></span></td>
            @endfor
            <td class="score-cell"><span class="box"></span></td>
            <td></td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="notes">
    <p>ពិន្ទុសរុប: __________________ / 100 &nbsp;&nbsp;&nbsp; លទ្ធផល: __________________</p>
    <p>ចំណុចខ្លាំង:</p>
    <div class="note-line"></div>
    <div class="note-line"></div>
    <p>ចំណុចត្រូវកែលម្អ:</p>
    <div class="note-line"></div>
    <div class="note-line"></div>
    <p>គោលដៅបន្ទាប់:</p>
    <div class="note-line"></div>
    <div class="note-line"></div>
    <p>មតិបុគ្គលិក / អ្នកវាយតម្លៃ:</p>
    <div class="note-line"></div>
    <div class="note-line"></div>
</div>

<div class="signatures">
    <div><div class="line"></div>បុគ្គលិក<br>ហត្ថលេខា / កាលបរិច្ឆេទ</div>
    <div><div class="line"></div>អ្នកវាយតម្លៃ<br>ហត្ថលេខា / កាលបរិច្ឆេទ</div>
    <div><div class="line"></div>ប្រធានផ្នែក/HR<br>ហត្ថលេខា / កាលបរិច្ឆេទ</div>
</div>
</body>
</html>
