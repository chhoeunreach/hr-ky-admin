# Laravel 10/11 & Windows Deployment Guide
### CertiCraft Studio — Khmer & English Certificate Generator with KNEAYERNG HRMS Hours

This guide explains how to install and run this application on **Windows** using either:
1. **Option A: Standalone Windows App** (Node.js + React + Vite) with 1-click batch files.
2. **Option B: Laravel 10/11 Web Project** (PHP + MySQL + Blade + DomPDF) on Windows via Laragon, XAMPP, or Docker.

---

## Option A: Run Standalone on Windows (1-Click)

1. Make sure **Node.js** (v18 or v20+) is installed: [https://nodejs.org](https://nodejs.org)
2. Double-click `INSTALL_WINDOWS.bat` to install packages.
3. Double-click `RUN_WINDOWS.bat`.
   - The command prompt will automatically launch Vite and open your browser at `http://localhost:3000`.

---

## Option B: Integrate into Laravel 10/11 on Windows (Laragon / XAMPP)

### 1. Requirements on Windows
- PHP 8.2 or 8.3
- Composer for Windows: [https://getcomposer.org](https://getcomposer.org)
- Laragon (Recommended for Windows) or XAMPP

### 2. Create or Open Your Laravel Project
```bash
composer create-project laravel/laravel my-certificate-portal
cd my-certificate-portal
```

### 3. Copy the Provided Files into Your Laravel App
- Copy `laravel/CertificateController.php` to `app/Http/Controllers/CertificateController.php`
- Copy `laravel/certificate.blade.php` to `resources/views/certificate/template.blade.php`
- Copy `laravel/create_certificates_table.php` to `database/migrations/2026_11_11_000000_create_certificates_table.php`
- Copy `public/assets/aistudio/unnamed_521b71dc.jpg` to `public/assets/aistudio/unnamed_521b71dc.jpg`

### 4. Install DomPDF for PDF Generation
```bash
composer require barryvdh/laravel-dompdf
```

### 5. Register Routes in `routes/web.php`
```php
use App\Http\Controllers\CertificateController;

Route::prefix('certificates')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/{id}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/{id}/download-pdf', [CertificateController::class, 'downloadPdf'])->name('certificates.downloadPdf');
    Route::get('/{id}/verify', [CertificateController::class, 'verify'])->name('certificates.verify');
});
```

### 6. Run Migrations & Start Server on Windows
```bash
php artisan migrate
php artisan serve
```
Open your browser at: `http://localhost:8000/certificates/KY-LDR-2026-001`

---

## Khmer Fonts & Print Compatibility
The templates automatically include **Moul** (for royal Khmer headings) and **Kantumruy Pro** (for official bodies and credentials), fully compatible with Windows printers and high-resolution PDF exports.
