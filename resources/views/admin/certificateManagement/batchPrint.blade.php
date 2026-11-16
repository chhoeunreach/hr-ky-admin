@php($autoPrint = $autoPrint ?? false)
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Certificates ({{ count($certificates) }})</title>

    @include('admin.certificateManagement.partials.certificate-head')

    <style>
        body { background: #e2e8f0; }
        .batch-page {
            width: max-content;
            margin: 0 auto 24px;
            page-break-after: always;
            break-after: page;
        }
        .batch-page:last-of-type {
            page-break-after: auto;
            break-after: auto;
        }
        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .batch-page { margin: 0 auto !important; }
        }
    </style>
</head>
<body class="p-4">

    @include('admin.certificateManagement.partials.print-customizer', ['mode' => 'batch'])

    @forelse($certificates as $certificate)
        <div class="batch-page">
            @include('admin.certificateManagement.partials.certificate-canvas', ['certificate' => $certificate])
        </div>
    @empty
        <div class="mx-auto bg-white p-8 rounded shadow text-center text-slate-600" style="max-width: 640px;">
            No certificates were found for this batch. Please issue certificates again.
        </div>
    @endforelse

    @if($autoPrint && count($certificates) > 0)
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 900);
        });
    </script>
    @endif
</body>
</html>
