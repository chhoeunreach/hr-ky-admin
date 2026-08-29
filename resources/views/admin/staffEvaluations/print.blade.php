<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>Employee Performance Evaluation</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { font-family: "Noto Sans Khmer", Arial, sans-serif; color: #111827; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 12px; }
        .header h2, .header h3 { margin: 2px 0; }
        .info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 20px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111827; padding: 5px; vertical-align: top; }
        th { background: #f3f4f6; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 32px; text-align: center; }
        .line { border-bottom: 1px solid #111827; height: 32px; margin-bottom: 6px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<button class="no-print" onclick="window.print()">Print</button>
<div class="header">
    <h2>KNEAYERNG</h2>
    <h3>ទម្រង់វាយតម្លៃការងារបុគ្គលិក</h3>
    <h3>EMPLOYEE PERFORMANCE EVALUATION</h3>
</div>
<div class="info">
    <div>ឈ្មោះ: {{ $evaluation->employee->english_name ?: $evaluation->employee->name }}</div>
    <div>Employee ID: {{ $evaluation->employee->employee_code ?: $evaluation->employee->username }}</div>
    <div>តួនាទី: {{ $evaluation->employee->post?->post_name ?: 'N/A' }}</div>
    <div>ផ្នែក: {{ $evaluation->employee->department?->dept_name ?: 'N/A' }}</div>
    <div>សាខា: {{ $evaluation->employee->branch?->name ?: 'N/A' }}</div>
    <div>រយៈពេលវាយតម្លៃ: {{ $evaluation->evaluation_period }}</div>
    <div>អ្នកវាយតម្លៃ: {{ $evaluation->evaluator?->name ?: 'N/A' }}</div>
    <div>ពិន្ទុសរុប: {{ number_format((float)$evaluation->total_score, 2) }} / 100 · {{ $evaluation->result_label }}</div>
</div>
<table>
    <thead>
    <tr>
        <th>ល.រ</th><th>សំណួរវាយតម្លៃ</th><th>Weight</th><th>5</th><th>4</th><th>3</th><th>2</th><th>1</th><th>N/A</th><th>មតិ</th>
    </tr>
    </thead>
    <tbody>
    @foreach($evaluation->answers as $answer)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $answer->question_kh }}</td>
            <td>{{ $answer->weight }}%</td>
            @for($score = 5; $score >= 1; $score--)
                <td style="text-align:center">{{ (int)$answer->score === $score ? '✓' : '' }}</td>
            @endfor
            <td style="text-align:center">{{ $answer->is_na ? '✓' : '' }}</td>
            <td>{{ $answer->comment }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<p>ចំណុចខ្លាំង: ____________________________________</p>
<p>ចំណុចត្រូវកែលម្អ: ____________________________________</p>
<p>គោលដៅបន្ទាប់: ____________________________________</p>
<p>មតិអ្នកវាយតម្លៃ: {{ $evaluation->evaluator_comment }} ____________________________________</p>
<div class="signatures">
    <div><div class="line"></div>បុគ្គលិក<br>ហត្ថលេខា / កាលបរិច្ឆេទ</div>
    <div><div class="line"></div>អ្នកវាយតម្លៃ<br>ហត្ថលេខា / កាលបរិច្ឆេទ</div>
    <div><div class="line"></div>ប្រធានផ្នែក/HR<br>ហត្ថលេខា / កាលបរិច្ឆេទ</div>
</div>
</body>
</html>
