# CODEX BACKEND TASK BRIEF — Attendance Time Verification (BE-01…BE-05)

You are Codex, Senior Laravel Backend & Security Engineer, working under OpenCode lead.

## Branch
`feat/attendance-time-verification` (already checked out). Working tree may contain **unrelated** dirty files — **do not revert or commit unrelated files**. Only modify attendance-related paths listed below.

## Required reading (in repo)
1. `docs/attendance-time-audit.md`
2. `docs/attendance-api-contract.md` ← **implement against this contract**
3. Existing code:
   - `app/Http/Controllers/Api/AttendanceApiController.php`
   - `app/Services/Attendance/AttendanceService.php`
   - `app/Helpers/AttendanceHelper.php`
   - `app/Helpers/AppHelper.php`
   - `app/Http/Controllers/Api/Kiosk/KioskApiController.php` (idempotency pattern)
   - `routes/api.php`
   - `config/app.php` (timezone Asia/Phnom_Penh)
   - `tests/Feature/FaceKioskAttendanceApiTest.php`

## Allowed files to edit
- `app/Http/Controllers/Api/AttendanceApiController.php`
- `app/Http/Controllers/Api/TimeApiController.php` (new) or equivalent
- `app/Services/Attendance/AttendanceService.php`
- `app/Services/Attendance/AttendanceLogService.php`
- `app/Helpers/AttendanceHelper.php` (only if needed for night/date from official time)
- `app/Http/Middleware/*` (new request-receipt middleware if used)
- `app/Http/Kernel.php` (register middleware only if needed)
- `app/Resources/Attendance/TodayAttendanceResource.php`
- `app/Resources/Attendance/NightAttendanceResource.php`
- `app/Models/Attendance.php` (fillable only if new audit columns — prefer no migration)
- `routes/api.php`
- `config/*` only if a new attendance setting default is required
- `lang/en/*` only for new keys if necessary
- `tests/Feature/Attendance*.php`, `tests/Unit/Attendance*.php` (new/updated)

**Do not** edit payroll, community, social, HR queue, or unrelated modified files.

## BE-01 — Authoritative server time
1. Capture trusted receipt timestamp early in `employeeAttendance` (or middleware) as `$receivedAt = now()` once.
2. **Remove client `recorded_at` as authority.** `AttendanceService::attendanceRecordedAt()` must use receipt time only. `recorded_at` may remain in validation as optional non-authoritative (ignored for official fields), or stored only in logs — never written to `check_in_at`/`check_out_at`/`night_*`/`attendance_date`.
3. Keep app timezone `Asia/Phnom_Penh` for existing local `time`/`date` storage (no UTC column migration).
4. Ensure night shift + day shift both derive from the same receipt instant.
5. Document chrony/NTP check in a short comment block or in response to lead (ops checklist goes in final report — do not deploy).

## BE-02 — Lightweight time API
- Add `GET /api/time` per contract §3 (public, throttled).
- Response envelope matches `AppHelper::sendSuccessResponse` style with `server_time_utc`, `timezone`, `timezone_offset`.
- Use `now()` → `copy()->utc()->format('Y-m-d\TH:i:s.v\Z')` (or equivalent millisecond ISO-8601).

## BE-03 — Attendance security
1. Enforce enabled `attendance_method` for mobile `employeeAttendance` (allow face via kiosk path as today).
2. When location required: validate lat/lng presence; enforce accuracy if `accuracy` provided and setting/config max set (use conservative default e.g. 100 if no setting — prefer reading existing radius setting scale; if no accuracy setting exists, validate only when accuracy present and > 1000m reject, and document).
3. Hash identifiers in attendance logs (`hash('sha256', $identifier)` or truncated hash) — do not store raw QR/NFC in new log writes.
4. Idempotency: keep `offline_request_id`; on unique constraint violation catch and return existing success with `idempotent_replay: true`.
5. Concurrency: inside transaction, `lockForUpdate` on today’s attendance row when present; for new insert rely on race-safe check + unique offline key. If multi-attendance mode allows multiple rows, lock user row or use `Cache::lock` / `lockForUpdate` on `users` for punch serialization — pick minimal safe approach and document.
6. Optional setting `require_nfc_or_gps_before_qr` (slug, default **off**): when on, QR/`qr` type requires either valid recent NFC evidence flag from same session **or** GPS within branch radius. If too large for this pass, implement setting flag + clear server rejection when enabled without GPS in radius (NFC-first may be client sequence — enforce at least GPS-or-fail when flag on and document limitation).
7. Return explicit HTTP codes (409 conflicts, 400 business, 403 policy) without breaking message keys.

## BE-04 — Calculations
- Feed official receipt time into existing late/early/OT/worked/night logic only.
- Do not change business rules for grace periods or bonus formulas.
- Ensure `attendance_date` for night shifts remains correct when receipt is after midnight (preserve existing night date behavior; fix only if receipt-time change would break it — add tests).

## BE-05 — Tests
Create Feature tests (SQLite in-memory per phpunit.xml) covering:
1. Client `recorded_at` in the past/future does **not** change `check_in_at` official time (assert within tolerance of frozen `Carbon::setTestNow`).
2. `GET /api/time` returns UTC + timezone.
3. Same `offline_request_id` twice → second response `idempotent_replay: true`, single row.
4. Concurrent double submit (simulate sequential same-key) safe.
5. Invalid QR/NFC rejected.
6. Method not in `attendance_method` rejected (when setting configured).
7. Night shift check-in/out uses official time (freeze now across midnight).
8. Attendance response includes `server_time_utc`.
9. Existing face kiosk test pattern still valid (run it).

Run: `vendor/bin/phpunit --filter Attendance` and face kiosk tests. Report results.

## Constraints
- Backward compatible response envelope `{status, message, status_code, data}`.
- No destructive migrations; prefer no schema change. If a column is truly required, add a **new** additive migration only.
- No production deploy commands.
- Do not commit unless asked; leave changes in working tree for lead review.
- Minimal diff; preserve Flutter-facing field names in resources.

## Deliverable to lead
When done, reply with:
1. List of modified/created files
2. Design decisions (time source, locking, method enforcement)
3. Test command + pass/fail summary
4. Any contract deviations
5. Remaining risks
