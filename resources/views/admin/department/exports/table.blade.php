<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('index.department_lists') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;600;700&family=Roboto:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 {{ $orientation }}; margin: 12mm; }
        body { font-family: 'Noto Sans Khmer', Roboto, sans-serif; color: #243044; padding: 24px; font-size: 11px; }
        h1 { font-size: 20px; margin-bottom: 6px; } .summary { color: #6b7280; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #dfe4ec; text-align: left; padding: 8px; vertical-align: top; white-space: pre-line; overflow-wrap: anywhere; }
        th { background: #f2f5fa; font-weight: 700; } thead { display: table-header-group; } tr { break-inside: avoid; }
        .controls { background: #f5f7fb; border: 1px solid #dfe4ec; border-radius: 8px; padding: 12px; margin-bottom: 20px; }
        button { background: #4f46e5; color: white; border: 0; border-radius: 5px; padding: 8px 16px; cursor: pointer; }
        @media print { body { padding: 0; } .controls { display: none; } }
    </style>
</head>
<body>
    <h1>{{ __('index.department_lists') }}</h1>
    <p class="summary">{{ count($rows) }} {{ __('index.departments') }} · {{ __('index.department_employee_status_filter') }}: {{ $employeeStatus }}</p>
    <div class="controls">
        @if($format === 'pdf')<p>{{ __('index.department_pdf_help') }}</p>@endif
        <button type="button" onclick="window.print()">{{ $format === 'pdf' ? __('index.export_pdf') : __('index.print') }}</button>
    </div>
    <table>
        <thead><tr>@foreach($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
        <tbody>@forelse($rows as $row)<tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headings) }}">{{ __('index.no_records_found') }}</td></tr>@endforelse</tbody>
    </table>
    <script>window.addEventListener('load', async () => {if (document.fonts) await document.fonts.ready; window.print();});</script>
</body>
</html>
