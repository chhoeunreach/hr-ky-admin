# Digital HR Mobile Flutter Client Modules

The main app is in the sibling `hrky-flutter` repository. This directory contains drop-in modular screens, widgets, and API clients in `lib/` for direct integration.

---

## 1. Store Phone & SIM Directory (`lib/store_phone_module.dart`)

Empowers employees and managers to look up branch phone numbers and view their assigned physical SIM cards and eSIMs:

- **Screen:** `StorePhoneDirectoryScreen(baseUrl: '...', authToken: '...')`
- **Tab 1: My SIM Cards (`GET /api/my-sim-cards`)**
  - Displays the active SIM cards assigned to the current authenticated employee.
  - Shows slot name (`SIM 1`, `SIM 2`, `eSIM`), operator badge (Smart, Cellcard, Metfone), purpose, linked hardware device, and ICCID.
- **Tab 2: Store Phone Directory (`GET /api/store-phone-numbers`)**
  - Full company directory with live search query (`?search=...`) and operator filter chips (`?operator=smart`).
  - One-tap dialer integration via `url_launcher` (`tel:`).
  - One-tap copy to clipboard.

---

## 2. Enterprise Company Documents (`lib/company_documents_module.dart`)

Allows employees and management to access official company documents, rules, compliance licenses, and view expiration warnings:

- **Screen:** `CompanyDocumentsScreen(baseUrl: '...', authToken: '...')`
- **Endpoints:**
  - `GET /api/v1/company-documents/alerts`: Fetches expired count and expiring-soon count (displayed in the top warning banner).
  - `GET /api/v1/company-documents?category=...`: Filterable document list with validity dates, document numbers, and one-tap download/open file launcher.

---

## 3. Payday Queue & QR Attendance (`lib/hr_queue_module.dart`)

Enables digital queue management during payroll disbursement and QR attendance:

- **Employee queue flow:**
  1. Employee scans the session QR from the home scanner.
  2. The app calls `POST /api/v1/payroll-queue/join` with the session token.
  3. The `PayrollQueueTicketDialog` polls `GET /api/v1/payroll-queue/ticket/{ticketId}` for real-time queue position and serving counter calling.
- **QR attendance in and out:**
  - Generate a fresh employee pass with `POST /api/v1/hr-queue/qr/generate`.
  - Use `HrAttendanceScannerScreen` to scan IN or OUT with GPS attached to `POST /api/v1/hr-queue/queue/scan`.

---

## Dependencies (for `pubspec.yaml` in Flutter app)

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.2.0
  url_launcher: ^6.3.0
  qr_flutter: ^4.1.0
  mobile_scanner: ^5.1.0
```
