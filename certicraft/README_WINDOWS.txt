======================================================================
  CertiCraft Studio - Windows & Laravel Quick Setup Guide
======================================================================

1. HOW TO RUN ON WINDOWS (Standalone / React):
   ------------------------------------------
   Step 1: Install Node.js (v18 or v20+) from https://nodejs.org
   Step 2: Double-click "INSTALL_WINDOWS.bat" to install dependencies.
   Step 3: Double-click "RUN_WINDOWS.bat" to launch the studio!
           Your browser will automatically open to http://localhost:3000.

2. HOW TO INTEGRATE WITH LARAVEL (PHP / MySQL / XAMPP / Laragon):
   --------------------------------------------------------------
   If you are running a Laravel project on Windows (e.g., using Laragon or XAMPP):
   
   A. Copy Controller:
      Copy `laravel/CertificateController.php` to your Laravel app's:
      `app/Http/Controllers/CertificateController.php`

   B. Copy Blade Template:
      Copy `laravel/certificate.blade.php` to:
      `resources/views/certificate/template.blade.php`

   C. Add Routes:
      Paste the routes from `laravel/routes_web.php` into your:
      `routes/web.php` or `routes/api.php`

   D. Run Migrations:
      Copy `laravel/create_certificates_table.php` to:
      `database/migrations/` and run `php artisan migrate`

   E. PDF Generation:
      Install DomPDF or Browsershot in Laravel:
      `composer require barryvdh/laravel-dompdf`

3. KHMER & ENGLISH DUAL-SCRIPT SUPPORT:
   -----------------------------------
   Google Fonts (Moul and Kantumruy Pro) are already linked in the HTML
   and Blade templates for authentic Cambodian government & institutional
   typography.

Author: Reach Chhoeun (chhoeunreach@gmail.com)
Brand: គ្នា​យើង (Knea Yoeng)
======================================================================
