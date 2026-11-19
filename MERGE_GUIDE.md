# Merging CertiCraft Studio into `hr-ky-admin` (or Existing Project)

This package can run **standalone** OR be added directly as an embeddable module inside **`hr-ky-admin`** (React / TypeScript / Vite / Next.js) or connected to **Laravel 10/11**.

---

## Option 1: Merge into `hr-ky-admin` (React / TypeScript)

### 1. Copy the Module Files
Copy the `src/` folder contents from **`hr-ky-admin-certificate-module.zip`** into your existing project, for example:
```text
src/
  modules/
    certificates/
      CertificateStudio.tsx
      certificateModule.ts
      components/...
      data/...
      types/...
      utils/...
      i18n/...
      index.css
```
Also copy the default logo asset to your project's `public/` folder:
```text
public/assets/aistudio/unnamed_521b71dc.jpg
```

### 2. Install Required Dependencies in `hr-ky-admin`
Run in your `hr-ky-admin` root directory:
```bash
npm install lucide-react html2canvas jspdf qrcode jszip motion
npm install -D @types/qrcode @types/jszip
```

### 3. Add Fonts & Print Styles
Include Google Fonts (`Cinzel`, `Kantumruy Pro`, `Moul`, `Battambang`, `Playfair Display`, `Cormorant Garamond`, `Great Vibes`, `Alex Brush`, `Montserrat`, `Plus Jakarta Sans`) in your main `index.html`, and import the certificate CSS utilities + `@media print` A4 rules from `src/index.css` into your global stylesheet.

### 4. Mount `<CertificateStudio />` Inside `hr-ky-admin`
```tsx
import { CertificateStudio, CertificateData } from './modules/certificates/certificateModule';

export function HrCertificatesPage({ selectedEmployee, staffList, navigate }: any) {
  const handleSaveToHrAdmin = async (cert: CertificateData) => {
    // Save issued certificate JSON / metadata directly to your hr-ky-admin API
    await fetch('/api/hr/certificates', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(cert),
    });
  };

  return (
    <div className="h-[calc(100vh-64px)] w-full">
      <CertificateStudio
        embedded
        defaultLanguage="km"
        employee={selectedEmployee}       // Auto-fills name, Khmer name, role, photo, HR ID & hours
        externalEmployees={staffList}     // Feeds hr-ky-admin staff into KNEAYERNG HRMS & Batch PDFs
        onBack={() => navigate('/employees')}
        onSaveCertificate={handleSaveToHrAdmin}
      />
    </div>
  );
}
```

### 5. Supported `employee` Fields (camelCase or snake_case)
`mapHrEmployeeToCertificate()` automatically reads:
- `employeeId` / `employee_code`
- `fullName` / `name`
- `khmerName` / `khmer_name`
- `position` / `role`
- `department`
- `photoUrl` / `profile_photo` / `avatar`
- `trainingHours` / `training_hours`
- `credentialId`, `issueDate`, `complianceStatus`

---

## Option 2: Laravel 10/11 Backend Integration (`laravel/`)
Copy `laravel/CertificateController.php`, `laravel/certificate.blade.php`, `laravel/routes_web.php`, and `laravel/create_certificates_table.php` into your Laravel HR Admin project.
