<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $heading }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; font-size: 7pt; }
        th, td { border: 1px solid #ccc; padding: 4px; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #eee; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>{{ $heading }}</h1>
    @if ($period[0] || $period[1])
        <p>Asia/Phnom_Penh. {{ \App\Modules\DailyRecords\RecordTime::display($period[0]) }} to {{ \App\Modules\DailyRecords\RecordTime::display($period[1]) }} (exclusive).</p>
    @endif
    <p>
        @foreach ($summary as $label => $count)
            <strong>{{ $label }}</strong> {{ $count }}@if (!$loop->last) · @endif
        @endforeach
    </p>
    <table>
        <thead><tr>@foreach ($columns as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>@foreach ($row as $cell)<td>{{ \Illuminate\Support\Str::limit((string) $cell, 450) }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($columns) }}">No matching records.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
