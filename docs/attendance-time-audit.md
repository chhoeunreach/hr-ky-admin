# Attendance Time Verification Audit

**Date:** 2026-11-20  
**Scope:** Laravel backend (`hr-ky-admin`) + Flutter references in this monorepo  
**Goal:** Identify every time source, security gap, and minimal safe change set before implementation.

---

## 1. Repository / Integration Inventory

| Item | Path | Notes |
|------|------|-------|
| Laravel app | `app/` | Primary backend |
| API routes | `routes/api.php` | Mobile + kiosk |
| Web routes | `routes/web.php` | Admin attendance, QR/NFC admin |
| Attendance API | `app/Http/Controllers/Api/AttendanceApiController.php` | Main check-in/out path |
| Attendance service | `app/Services/Attendance/AttendanceService.php` | Timestamps, location, shifts |
| Attendance helper | `app/Helpers/AttendanceHelper.php` | Worked hour, night shift, OT |
| App helper | `app/Helpers/AppHelper.php` | Settings, night shift, shift lookup |
| QR service | `app/Services/Qr/QrCodeService.php` | Static QR identifiers |
| NFC service | `app/Services/Nfc/NfcService.php` | Static NFC identifiers |
| Face kiosk | `app/Http/Controllers/Api/Kiosk/KioskApiController.php` | Better idempotency pattern |
| Flutter in repo | `mobile/flutter/lib/hr_queue_module.dart` | Reference screens only |
| Full Flutter app | **Not in this repo** | Separate repository (see `docs/app-security/architecture.md`) |
| Codex CLI | Available (`codex exec`) | Verified executable |
| Antigravity | **Not available** | No CLI/API/MCP found |

**Critical integration finding:** The production Flutter mobile app is **not** present under this workspace. Only QR/queue reference widgets exist. FE tasks cannot be implemented or verified against a full Flutter project here without the external app repository path.

---

## 2. Actual Time Sources Used

| Source | Where | Authority today | Risk |
|--------|-------|-----------------|------|
| Server wall clock (`Carbon::now()`, `date()`, `strtotime()`) | Throughout helpers/services/controllers | Used for most official times | Local timezone `Asia/Phnom_Penh`; not stored as UTC |
| **Client `recorded_at`** | `AttendanceApiController::employeeAttendance` → `AttendanceService::attendanceRecordedAt()` | **Treated as official check-in/out time** | **CRITICAL:** device clock can rewrite official attendance |
| Client `captured_at` (kiosk) | `KioskApiController::attendance` | Used with future/same-day checks | Better than mobile path but still client-supplied |
| Device biometric timestamps | `BioAttendanceController` / iclock | Device clock | Expected for devices; not cryptographic |
| `attendance_date` derived from `recorded_at` | `AttendanceService::newCheckIn/newCheckOut` | Shift date | Wrong device date shifts the attendance day |
| Dashboard/live UI clocks | Blade + Flutter home clock | Display only | Must not feed attendance writes |

### 2.1 Critical path (mobile attendance)

```
POST /api/employees/attendance
  middleware: auth:api, app.security, permission, MobileFunctionPermission
  → validate attendance_type, latitude, longitude, identifier?, offline_request_id?, recorded_at?
  → optional GPS branch-radius check (enable-employee-location)
  → optional offline_request_id dedupe (SELECT then INSERT)
  → DB::beginTransaction
      verify NFC | QR | qr_pass | wifi | face
      night vs day, single vs multi attendance
      newCheckIn / newCheckOut using attendanceRecordedAt()
  → commit → attendance log → notifications → telegram
```

**Authoritative time today:**

```php
// app/Services/Attendance/AttendanceService.php
private function attendanceRecordedAt(array $validatedData): Carbon
{
    if (!empty($validatedData['recorded_at'])) {
        return Carbon::parse($validatedData['recorded_at']); // CLIENT-CONTROLLED
    }
    return Carbon::now();
}
```

Validation only requires `recorded_at` to be a `date` — no future window, no max skew, no server-receipt requirement.

---

## 3. Request Lifecycle (Check-in / Check-out)

### 3.1 Active endpoint

- **`POST /api/employees/attendance`** — unified employee attendance (wifi/nfc/qr/qr_pass/face).
- Deprecated but still routed: `POST /api/employees/check-in`, `POST /api/employees/check-out`.

### 3.2 Flow responsibilities

| Step | Owner | Issues |
|------|-------|--------|
| Auth + mobile function permission | Middleware | Present |
| Attendance method allow-list (`attendance_method` setting) | **Not enforced on API** | Setting exists but API accepts any enum type |
| GPS required | Only if `enable-employee-location` + radius enabled | Accuracy not enforced; remote users can pass null coords when validation skipped |
| NFC/QR validity | Static DB lookup | No expiry, no single-use, no replay protection (except `qr_pass`) |
| Idempotency | `offline_request_id` app check + unique columns | Race between SELECT and INSERT; unique only helps if both concurrent inserts hit constraint (unhandled → 500) |
| Duplicate same-day rows | `findEmployeeTodayAttendanceDetail` | **No unique `(user_id, attendance_date)`** → concurrent double check-in possible |
| Night shift | `AppHelper::isOnNightShift` + `checkNightShiftCheckOut` | Uses `Carbon::now()` / `date('Y-m-d')` for windows |
| Official timestamps | `attendanceRecordedAt` | Client time accepted |
| Response timestamps | `TodayAttendanceResource` / `NightAttendanceResource` | Formatted display strings only; **no ISO-8601 server time in response** |

### 3.3 Evidence types

| Type | Verification | Expiry | Replay protection | Server-side proof of presence |
|------|--------------|--------|-------------------|-------------------------------|
| `nfc` | Match `nfc_attendances.identifier` | None | None | No — static tag string |
| `qr` | Match `qr_attendances.identifier` | None | None | No — static QR string |
| `qr_pass` | HMAC-SHA256 over `user_id\|timestamp\|location_token` | 300 seconds | Partial (timestamp window only) | Weak — location_token is static config, not GPS |
| `wifi` | Optional BSSID override setting | N/A | N/A | Weak — client BSSID |
| `face` | Kiosk device middleware + face profile | Device session | `event_uuid` idempotency | Device-authenticated kiosk only |

**NFC OR GPS before QR:** No server-side rule of the form “NFC **OR** valid GPS must precede QR” was found in this repository. QR is accepted with GPS coordinates that are only radius-checked when location settings are enabled. If that policy exists, it is either client-only or outside this repo — it is **not enforced here**.

---

## 4. Database Time Assumptions

| Column | Type | Meaning |
|--------|------|---------|
| `attendances.attendance_date` | `date` | Local calendar day (Asia/Phnom_Penh) |
| `attendances.check_in_at` / `check_out_at` | `time` | Local wall time, **no date, no timezone** |
| `attendances.night_checkin` / `night_checkout` | `datetime` (after alter) | Local datetime for cross-midnight |
| `attendances.worked_hour` / `overtime` / `undertime` | `double` (minutes) | Derived |
| `attendances.check_in_offline_request_id` / `check_out_offline_request_id` | `string(100)` **unique** | Offline idempotency keys |
| `qr_attendances.identifier` | `string` unique-ish lookup | Static QR payload |
| `nfc_attendances.identifier` | `string` lookup | Static NFC payload |
| `attendance_logs.identifier` | string | **May store raw QR/NFC token** (sensitive) |

**App timezone:** `config/app.php` → `'timezone' => 'Asia/Phnom_Penh'`.  
**No UTC storage.** DB and app share local wall time. Changing display timezone without a storage migration will break existing rows.

**Missing constraints:**

- No `UNIQUE(user_id, attendance_date)` on `attendances` (blocks safe multi-row unless scoped by office_time / multiple-attendance mode).
- No request-receipt timestamp column.
- No server-side official timestamp distinct from client `recorded_at`.

---

## 5. Schedules, Grace, Night Shift, OT, Bonus

| Concern | Implementation | File |
|---------|----------------|------|
| Office hours | `office_times.opening_time/closing_time` | `OfficeTime` model |
| Early/late grace | `is_early_check_in`, `checkin_before`, `is_late_check_in`, `checkin_after`, checkout equivalents | `AttendanceService::newCheckIn/newCheckOut` |
| Night shift flag | `office_times.shift_type = night` | `AppHelper::isOnNightShift` |
| Night check-in/out windows | `AttendanceHelper::checkNightShiftCheckOut` using `Carbon::now()` | Cross-midnight via `strtotime(... +1 day)` |
| Worked hour | `AttendanceHelper::calculateWorkedHour` / `calculateNightWorkedHour` | Time-only diffs; night uses `ADDTIME(closing, 24:00:00)` |
| OT / undertime | Diff vs scheduled working minutes | `getOverAndUnderTimeData` |
| Monthly bonus / deduction | `MonthlyAttendanceRules`, `EmployeeMonthlyAttendanceSummary` | Shared with admin monthly report |
| Attendance limit (multi punches) | `attendance_limit` setting | `AppHelper::getAttendanceLimit()` |

**Night / midnight risks:**

- Day path stores `time` only — checkout after midnight can attach to wrong calendar day if `attendance_date` is not shifted.
- Night path stores full datetime but `newCheckIn` sets `night_checkin` from `$checkInAt` which may be a time-only string depending on path.
- `checkNightShiftCheckOut` depends on server `now()` — correct only if server clock is trusted (it is, once `recorded_at` is removed as authority).

---

## 6. Security Vulnerabilities (Severity-Ordered)

### P0 — Client-controlled official attendance time

- **Where:** `AttendanceService::attendanceRecordedAt()` via `recorded_at` on `POST /api/employees/attendance`.
- **Impact:** Any authenticated employee can backdate/future-date check-in/out, overtime, lateness, and bonus outcomes by changing device clock or forging `recorded_at`.
- **Minimal fix:** Ignore client `recorded_at` for official time; capture request receipt time early in middleware/pipeline; optionally store client claim only as audit metadata with bounded skew.

### P0 — No concurrency lock / unique day constraint

- **Where:** `handleSingleAttendance` / `handleMultipleAttendance` read-then-write without `lockForUpdate`.
- **Impact:** Parallel requests create duplicate check-ins or double check-outs.
- **Minimal fix:** Row lock on existing attendance for the day, or partial unique index appropriate to multi-attendance mode; catch unique violation and return prior success (idempotent).

### P1 — Static NFC/QR without expiry or single-use

- **Where:** `NFCRepository::verifyNfc`, `QRCodeRepository::getByIdentifier`.
- **Impact:** Shared photo/tag grants permanent attendance ability; replay unlimited.
- **Minimal fix (minimal safe):** Keep static board QR/NFC for physical presence UX but bind each scan with server-side single-use challenge or short-lived signed pass (pattern already exists in `qr_pass` / face kiosk `event_uuid`). Add audit log with hashed identifier, not raw token.

### P1 — GPS treated as presence proof

- **Where:** Latitude/longitude always accepted; radius only if settings enabled; accuracy stored but unused; field users skip radius.
- **Impact:** Spoofed GPS + static QR = attendance without presence.
- **Minimal fix:** Enforce max accuracy threshold when location required; keep device GPS as untrusted evidence; do not claim cryptographic presence. If “NFC OR GPS before QR” is required product policy, implement as explicit server setting + validation step before QR accept.

### P1 — Attendance logs may store raw identifiers

- **Where:** `AttendanceApiController::storeAttendanceLog` passes `identifier` into `attendance_logs`.
- **Impact:** DB leak exposes reusable QR/NFC secrets.
- **Minimal fix:** Store `hash('sha256', identifier)` or type+length only.

### P2 — `attendance_method` setting not enforced on API

- Admin can disable methods in settings; API still accepts any `EmployeeAttendanceTypeEnum` value.
- **Minimal fix:** Reject types not present in enabled `attendance_method` values (except system kiosk face path).

### P2 — Exception HTTP codes

- Controllers pass `Exception::getCode()` (often `0`) into `sendErrorResponse`, which maps invalid codes to 500. Client sees 500 for business rule violations.
- **Minimal fix:** Use explicit HTTP status constants; keep message contract stable.

### P2 — No dedicated lightweight time API for attendance clients

- Kiosk bootstrap and app-security heartbeat return `server_time`, but general mobile attendance has no public time-sync contract.
- **Minimal fix:** Add optional backward-compatible `GET /api/time` (or extend dashboard) with `server_time_utc` + timezone.

### P3 — Performance

- Multiple sequential queries per attendance (today row, count, leave, shift, holiday).
- Telegram already queued (good).
- Location writes (`user_locations`, `employee_locations`) on every punch.
- No dedicated throttle on `employees/attendance`.

---

## 7. API Compatibility Risks

| Risk | Detail |
|------|--------|
| Response shape | Clients expect `{status, message, status_code, data}` and formatted `check_in_at` strings via resources. |
| Deprecated check-in/out | Still registered; changing contracts breaks older app builds. |
| `recorded_at` field | Existing apps may send it; must remain accepted but non-authoritative (or ignored with explicit field semantics). |
| `offline_request_id` | Already part of contract; keep column names and unique behavior. |
| Time format in UI | `AttendanceHelper::changeTimeFormatForAttendanceView` — do not switch to ISO without client updates. |
| Night resource fields | `night_checkin` / `night_checkout` + resource keys must stay stable. |

---

## 8. Existing Strengths to Preserve

1. Unified `POST /api/employees/attendance` with typed evidence.
2. `offline_request_id` unique columns + app-level dedupe.
3. Face kiosk: device auth, `event_uuid` idempotency, future/same-day checks — **pattern to reuse**.
4. `qr_pass` HMAC + 300s expiry — **pattern to extend**.
5. Branch radius validation helper + settings (`attendance_location_radius`).
6. Night shift columns and dedicated resources.
7. Attendance activity logs (partial) and Telegram via queue.
8. Mobile function permission map for attendance endpoints.
9. App timezone already `Asia/Phnom_Penh`.
10. Monthly bonus/deduction rules shared between admin and mobile summary.

---

## 9. Flutter / Client Findings (This Repo)

| Finding | Detail |
|---------|--------|
| No full app | Only `mobile/flutter/lib/hr_queue_module.dart` (QR generate/scan reference screens) |
| No clock service | No sync, offset estimation, or drift handling in repo |
| No idempotency client | No persisted attempt keys in repo |
| No offline queue | No pending-vs-confirmed model in repo |
| External app required | FE-01…FE-05 must target the separate Flutter repository |

**Implication:** Antigravity cannot be given executable work against a non-existent Flutter tree here. Task files will be written for the external repo owner; implementation in this monorepo is limited to API contract + any Dart reference snippets if explicitly added later.

---

## 10. Test Coverage Gaps

Existing tests touch face kiosk idempotency, monthly bonus, late excuse, kiosk security. **Missing:**

- Client `recorded_at` cannot change official time  
- Concurrent duplicate check-in/out  
- NFC/QR replay after successful use  
- Timezone / night cross-midnight calculation  
- GPS accuracy rejection  
- Offline retry same `offline_request_id` returns same attendance  
- Method allow-list enforcement  
- Time API precision/timezone fields  

---

## 11. Recommended Minimal Change Set

### Backend (implement first)

1. **BE-01** Request receipt timestamp (middleware or controller entry) as sole official time source; store UTC-oriented handling consistent with existing local schema (see API contract for field strategy); stop trusting `recorded_at` for official fields.
2. **BE-02** Optional `GET /api/time` → `{server_time_utc, timezone}` using `now()` / `now()->utc()`.
3. **BE-03** Security hardening: enforce enabled methods; accuracy cap when location required; hash identifiers in logs; replay/idempotency via existing offline keys + locking; optional setting for NFC-OR-GPS-before-QR if product confirms.
4. **BE-04** Keep existing schedule/night/OT logic; only change the time source feeding it; add tests for cross-midnight.
5. **BE-05** Feature/unit tests for above without breaking public response envelope.

### Flutter (external repo)

- FE-01…FE-05 as specified, consuming the shared contract; never treat client clock as official.

### Out of scope without approval

- Mass UTC migration of historical `time` columns  
- Replacing static site QR/NFC with full challenge-response in one release  
- Production deploy / destructive DB ops  

---

## 12. Agent Orchestration Status

| Agent | Integration verified | Action |
|-------|----------------------|--------|
| **Codex** | Yes — `codex exec` | Delegate BE-01…BE-05 after API contract |
| **Antigravity** | **No** CLI/API/MCP | Generate `docs/tasks/ANTIGRAVITY-*.md`; do not claim execution |

Worktree/branch strategy: isolated branch for backend work; no simultaneous edits to same files; no merge without tests.

---

## 13. Key File Index (for implementers)

```
app/Http/Controllers/Api/AttendanceApiController.php
app/Http/Controllers/Api/Kiosk/KioskApiController.php
app/Services/Attendance/AttendanceService.php
app/Services/Attendance/AttendanceLogService.php
app/Helpers/AttendanceHelper.php
app/Helpers/AppHelper.php
app/Services/Qr/QrCodeService.php
app/Services/Nfc/NfcService.php
app/Repositories/QRCodeRepository.php
app/Repositories/NFCRepository.php
app/Repositories/AttendanceRepository.php
app/Resources/Attendance/TodayAttendanceResource.php
app/Resources/Attendance/NightAttendanceResource.php
app/Models/Attendance.php
app/Models/OfficeTime.php
app/Enum/EmployeeAttendanceTypeEnum.php
app/Http/Middleware/MobileFunctionPermission.php
routes/api.php
config/app.php
config/mobile_permissions.php
database/migrations/2026_09_15_000001_add_offline_request_ids_to_attendances_table.php
database/migrations/2026_05_28_000001_add_attendance_list_performance_indexes.php
tests/Feature/FaceKioskAttendanceApiTest.php
tests/Feature/MonthlyAttendanceBonusTest.php
mobile/flutter/lib/hr_queue_module.dart
```
