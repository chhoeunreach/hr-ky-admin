@php
    $employee = $assignment->user;
    $notAvailable = __('index.not_available');
    $receiptNumber = 'AR-' . str_pad((string) $assignment->id, 6, '0', STR_PAD_LEFT);
    $description = trim(html_entity_decode(strip_tags(preg_replace('/<\/(p|div)>|<br\s*\/?>/i', "\n", $asset->note ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('index.asset_receipt') }} — {{ $receiptNumber }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/asset-receipt.css') }}?v={{ filemtime(public_path('assets/css/asset-receipt.css')) }}">
</head>
<body>
    <nav class="asset-receipt-toolbar" aria-label="{{ __('index.asset_receipt') }}">
        <a href="{{ route('admin.assets.index') }}">{{ __('index.back') }}</a>
        <div><strong>{{ __('index.resignation_a4_document_preview') }}</strong><span>A4 · 210 × 297 mm</span></div>
        <button type="button" id="printAssetReceipt">{{ __('index.asset_print_receipt') }}</button>
    </nav>
    <main class="asset-receipt-preview">
        <article class="asset-receipt-paper">
            <header class="asset-receipt-header">
                <div><strong>{{ $assignment->branch?->name ?: $asset->branch?->name ?: config('app.name') }}</strong><span>{{ __('index.hr_department') }}</span></div>
                <dl><div><dt>{{ __('index.form_number') }}</dt><dd>{{ $receiptNumber }}</dd></div><div><dt>{{ __('index.assigned_date') }}</dt><dd>{{ \Illuminate\Support\Carbon::parse($assignment->assigned_date)->format('Y-m-d') }}</dd></div></dl>
            </header>
            <h1>{{ __('index.asset_receipt') }}</h1>
            <section>
                <h2>{{ __('index.employee_information') }}</h2>
                <dl class="asset-receipt-grid">
                    @foreach([
                        'employee_name' => $employee?->name, 'latin_name' => $employee?->english_name,
                        'employee_id' => $employee?->employee_code ?: $employee?->username,
                        'branch' => $assignment->branch?->name, 'department' => $assignment->department?->dept_name,
                        'phone' => $employee?->phone,
                    ] as $label => $value)
                        <div><dt>{{ __('index.' . $label) }}</dt><dd>{{ $value ?: $notAvailable }}</dd></div>
                    @endforeach
                </dl>
            </section>
            <section>
                <h2>{{ __('index.asset_detail') }}</h2>
                <table>
                    <thead><tr><th>{{ __('index.asset_name') }}</th><th>{{ __('index.asset_code') }}</th><th>{{ __('index.asset_serial_number') }}</th><th>{{ __('index.type') }}</th></tr></thead>
                    <tbody><tr><td>{{ $asset->name }}</td><td>{{ $asset->asset_code ?: $notAvailable }}</td><td>{{ $asset->asset_serial_no ?: $notAvailable }}</td><td>{{ $asset->type?->name ?: $notAvailable }}</td></tr></tbody>
                </table>
                <dl class="asset-receipt-grid">
                    <div><dt>{{ __('index.asset_receipt_quantity') }}</dt><dd>1</dd></div>
                    <div><dt>{{ __('index.is_working') }}</dt><dd>{{ in_array($asset->is_working, ['yes', 'no'], true) ? __('index.' . $asset->is_working) : $notAvailable }}</dd></div>
                </dl>
                <h3>{{ __('index.description') }}</h3>
                <div class="asset-receipt-description">{{ $description ?: $notAvailable }}</div>
            </section>
            <section>
                <h2>{{ __('index.asset_receipt_acknowledgement') }}</h2>
                <p>{{ __('index.asset_receipt_statement') }}</p>
                <h3>{{ __('index.remark') }}</h3>
                <div class="asset-receipt-remarks"></div>
            </section>
            <div class="asset-receipt-signatures">
                <div><strong>{{ __('index.asset_received_by') }}</strong><div class="asset-receipt-signature-space"></div><span>{{ $employee?->name ?: $notAvailable }}</span><small>{{ __('index.date') }}: ______________</small></div>
                <div><strong>{{ __('index.asset_issued_by') }}</strong><div class="asset-receipt-signature-space"></div><span>{{ __('index.name') }}: ______________</span><small>{{ __('index.date') }}: ______________</small></div>
                <div><strong>{{ __('index.asset_approved_by') }}</strong><div class="asset-receipt-signature-space"></div><span>{{ __('index.name') }}: ______________</span><small>{{ __('index.date') }}: ______________</small></div>
            </div>
            <footer>{{ $receiptNumber }} · {{ $asset->asset_code ?: $asset->name }}</footer>
        </article>
    </main>
    <script>
        document.getElementById('printAssetReceipt').addEventListener('click', async () => {
            if (document.fonts) await document.fonts.ready;
            window.print();
        });
    </script>
</body>
</html>
