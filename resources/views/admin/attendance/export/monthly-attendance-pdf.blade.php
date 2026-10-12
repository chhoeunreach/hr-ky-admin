<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance - {{ $month->format('F Y') }}</title>
    <style>
        @page { margin: 18px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 6px; color: #222; }
        h1 { font-size: 14px; margin-bottom: 4px; }
        p { margin-bottom: 12px; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        th, td { border: 1px solid #bbb; padding: 3px 2px; word-wrap: break-word; }
        th { background: #edf0f2; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Monthly Attendance - {{ $month->format('F Y') }}</h1>
    <p>Generated: {{ $generatedAt->format('Y-m-d H:i') }}</p>
    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
