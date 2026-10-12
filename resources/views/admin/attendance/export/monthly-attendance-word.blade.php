<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Attendance {{ $month->format('F Y') }}</title>
    <style>
        body {
            font-family: "Khmer OS Battambang", Arial, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        p {
            margin: 0 0 12px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: top;
            text-align: center;
        }

        th:nth-child(2), td:nth-child(2) { text-align: left; min-width: 180px; }

        th {
            background: #e5edf8;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Monthly Attendance - {{ $month->format('F Y') }}</h1>
    <p>Generated {{ $generatedAt->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
        <tr>
            @foreach($headings as $heading)
                <th>{!! nl2br(e($heading)) !!}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($row as $value)
                    <td>{!! nl2br(e($value)) !!}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
