<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('index.asset_lists') }}</title>
    <style>
        @page { size: A4 {{ $orientation }}; margin: 10mm; }
        body { font: 10pt 'Kantumruy Pro', 'Khmer OS Battambang', 'Noto Sans Khmer', Arial, sans-serif; color: #172b4d; }
        h1 { font-size: 18pt; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; overflow-wrap: anywhere; white-space: pre-line; vertical-align: top; text-align: left; }
        th { background: #f1f5f9; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; }
        .export-controls { margin: 16px 0; }
        @media print { .export-controls { display: none; } }
    </style>
</head>
<body>
    <h1>{{ __('index.asset_lists') }}</h1>
    @if($format !== 'word')
        <div class="export-controls">
            <p>A4 · {{ __('index.' . $orientation) }}</p>
            @if($format === 'pdf')<p>{{ __('index.assets_save_pdf_help') }}</p>@endif
            <button type="button" onclick="window.print()">{{ $format === 'pdf' ? __('index.export_pdf') : __('index.print') }}</button>
        </div>
    @endif
    <table>
        <thead><tr>@foreach($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse($rows as $row)
                <tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">{{ __('index.no_records_found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($format !== 'word' && request('asset_modal') !== '1')<script>window.addEventListener('load', async () => { if (document.fonts) await document.fonts.ready; window.print(); });</script>@endif
</body>
</html>
