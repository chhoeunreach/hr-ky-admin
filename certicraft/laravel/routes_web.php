<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;

/*
|--------------------------------------------------------------------------
| Web & API Routes for Certificate Management & Digital HR
|--------------------------------------------------------------------------
|
| Paste these routes into your routes/web.php or routes/api.php file.
|
*/

Route::prefix('certificates')->group(function () {
    // 1. List all certificates & employee digital hours
    Route::get('/', [CertificateController::class, 'index'])->name('certificates.index');

    // 2. View rendered certificate in browser (A4 Responsive Canvas)
    Route::get('/{id}', [CertificateController::class, 'show'])->name('certificates.show');

    // 3. Download Vector PDF (via DomPDF or Browsershot)
    Route::get('/{id}/download-pdf', [CertificateController::class, 'downloadPdf'])->name('certificates.downloadPdf');

    // 4. Public QR verification endpoint (scanned by smartphones or auditors)
    Route::get('/{id}/verify', [CertificateController::class, 'verify'])->name('certificates.verify');
});
