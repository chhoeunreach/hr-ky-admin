# Attendance Time Verification — Implementation Report

**Date:** 2026-11-20  
**Branch:** `feat/attendance-time-verification`  
**Lead:** OpenCode  
**Backend agent:** Codex (CLI `codex exec`, verified)  
**Flutter agent:** Antigravity — **not available** (no CLI/API/MCP)

---

## 1. Existing system findings

Full detail: [`docs/attendance-time-audit.md`](attendance-time-audit.md)

| Finding | Severity |
|---------|----------|
| Client `recorded_at` used as official check-in/out time | **P0** |
| No row lock / unique day constraint → concurrent duplicate punches | **P0** |
| Static NFC/QR without expiry or single-use | **P1** |
| GPS treated as presence evidence; accuracy unused | **P1** |
| Attendance logs stored raw QR/NFC identifiers | **P1** |
| `attendance_method` setting not enforced on API | **P2** |
| No lightweight public time-sync endpoint for mobile | **P2** |
| Exception codes often `0` → HTTP 500 for business errors | **P2** |
| Flutter app not in this monorepo (reference widgets only) | Constraint |
| “NFC OR GPS before QR” not found as server rule | Gap / optional setting |

**Strengths preserved:** unified `POST /api/employees/attendance`, `offline_request_id` unique columns, face-kiosk `event_uuid` idempotency pattern, `qr_pass` HMAC+300s, branch radius helpers, night-shift columns/resources, Telegram via queue, mobile function permissions, app timezone `Asia/Phnom_Penh`.

---

## 2. Security vulnerabilities discovered

1. **Client-controlled official time** — device clock could rewrite attendance, lateness, OT, bonus inputs.  
2. **Race on duplicate attendance** — read-then-write without lock.  
3. **Replayable static NFC/QR** — photo of board QR or cloned tag = unlimited punches.  
4. **Spoofable GPS** — coordinates never cryptographically bound; accuracy ignored.  
5. **Secret leakage in logs** — raw identifiers in `attendance_logs`.  
6. **Method bypass** — disabled methods still accepted by API.  
7. **Business errors as 500** — `Exception::getCode()` often `0`.

---

## 3. Implementation plan (executed)

| Phase | Action | Result |
|-------|--------|--------|
| 1 | Audit codebase | `docs/attendance-time-audit.md` |
| 2 | Verify agents; create tasks | Codex OK; Antigravity task files only |
| 3 | API contract | `docs/attendance-api-contract.md` |
| 4 | Implement BE-01…BE-05 via Codex | Done on feature branch |
| 5 | Integration review + tests | This report |
| 6 | Final report | This file |

**Minimal change set:** one server receipt timestamp; additive time API; locks + idempotency; method/accuracy/log hashing; optional QR GPS policy (default off); tests. No UTC column migration; no Flutter rewrite; no production deploy.

---

## 4. Agent task assignments

| Agent | Tasks | Integration | Status |
|-------|-------|-------------|--------|
| **Codex** | BE-01…BE-05 | `codex exec` (CLI 0.171.0 after upgrade) | **Completed** |
| **Antigravity** | FE-01…FE-05 | **Unavailable** | Task files only — not executed |

**Antigravity artifacts (for external Flutter repo owner):**

- `docs/tasks/ANTIGRAVITY-FE-01-clock-service.md`
- `docs/tasks/ANTIGRAVITY-FE-02-attendance-submission.md`
- `docs/tasks/ANTIGRAVITY-FE-03-offline-handling.md`
- `docs/tasks/ANTIGRAVITY-FE-04-performance.md`
- `docs/tasks/ANTIGRAVITY-FE-05-flutter-tests.md`
- Tracker: `docs/tasks/TASK-TRACKER.md`
- Codex brief: `docs/tasks/CODEX-BE-TASKS.md`

**Orchestration rules observed:** inspected real integrations first; no fake agent completion; centralized tracker; feature branch for backend; Flutter not claimed done.

---

## 5. Modified files (backend)

### Created

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/TimeApiController.php` | `GET /api/time` |
| `config/attendance.php` | `client_clock_skew_seconds` (300) |
| `lang/en/attendance.php` | New message keys |
| `tests/Feature/AttendanceTimeVerificationTest.php` | BE-05 suite |
| `docs/attendance-time-audit.md` | Phase 1 |
| `docs/attendance-api-contract.md` | Phase 3 |
| `docs/tasks/*` | Agent tasks + tracker |
| `docs/attendance-implementation-report.md` | This report |

### Modified (attendance-related)

| File | Change summary |
|------|----------------|
| `app/Http/Controllers/Api/AttendanceApiController.php` | Receipt time; method allow-list; accuracy; idempotency/locks; evidence verify; response fields |
| `app/Services/Attendance/AttendanceService.php` | `attendanceRecordedAt` → `received_at`; date-locked lookups; night datetime on check-in |
| `app/Services/Attendance/AttendanceLogService.php` | SHA-256 identifier hash |
| `app/Helpers/AttendanceHelper.php` | Night/holiday helpers accept official date/time |
| `app/Resources/Attendance/TodayAttendanceResource.php` | Optional receipt time for productive minutes |
| `app/Resources/Attendance/NightAttendanceResource.php` | Same |
| `routes/api.php` | `GET time` + throttle |

**Not modified for this feature:** payroll, community, social, HR queue, production DB migrations (none added).

---

## 6. Database changes

**None required for this release.**

- Reused existing unique indexes: `attendances_check_in_offline_request_unique`, `attendances_check_out_offline_request_unique`.
- New optional settings (seed/admin as needed, not hard-required): `require_nfc_or_gps_before_qr`, `attendance_location_accuracy`.
- Storage remains local wall time (`Asia/Phnom_Penh`); no destructive UTC migration.

---

## 7. API changes

| Endpoint | Change |
|----------|--------|
| `GET /api/time` | **New** — `{server_time_utc, timezone, timezone_offset}`, throttled `60,1` |
| `POST /api/employees/attendance` | Additive response fields: `server_time_utc`, `attendance_id`, `idempotent_replay`, `attendance_status`. Optional header `Idempotency-Key`. `recorded_at` accepted but **non-authoritative**. |
| Deprecated check-in/out | Unchanged shape |
| Kiosk attendance | Unchanged (`captured_at` behavior retained) |

Envelope `{status, message, status_code, data}` preserved.

---

## 8. Flutter changes

**Not implemented in this repository** — production Flutter app is a separate codebase.

What was delivered instead:

- Full FE task specifications under `docs/tasks/ANTIGRAVITY-*.md`
- Contract rules for hybrid clock, idempotent submission, offline pending vs confirmed, performance, tests

**Lead verification of Flutter obligation:** cannot mark FE complete until external repo implements and tests are run there.

---

## 9. Test commands and results

```bash
vendor/bin/phpunit --filter Attendance
# Tests: 55, Assertions: 313 — PASS (1 pre-existing PHPUnit XML deprecation)

vendor/bin/phpunit tests/Feature/FaceKioskAttendanceApiTest.php
# Tests: 3, Assertions: 30 — PASS

vendor/bin/phpunit tests/Feature/MonthlyAttendanceBonusTest.php \
  tests/Feature/AttendanceMonthlyLateExcuseTest.php
# Tests: 25, Assertions: 139 — PASS

vendor/bin/phpunit tests/Feature/MeetingCheckInTest.php \
  tests/Feature/EmployeeMonthlyAttendanceSummaryTest.php \
  tests/Feature/TimeLeaveToolbarTest.php
# Tests: 30, Assertions: 132 — PASS

php -l <modified attendance PHP files>
# No syntax errors
```

### Coverage highlights (`AttendanceTimeVerificationTest`)

- Public time API UTC + timezone  
- Past/future `recorded_at` does not change official check-in  
- Receipt date wins when processing crosses midnight  
- Same idempotency key → single row, `idempotent_replay: true`  
- Cross-user key collision → 409  
- Invalid QR/NFC rejected  
- Disabled method rejected; kiosk face path allowed  
- Location accuracy + radius enforcement  
- QR policy (when on) requires in-radius GPS; logs hash only  
- Signed `qr_pass` bound to user + receipt window  
- Night checkout after midnight stays on check-in date  

### Tests not executed

| Test area | Reason |
|-----------|--------|
| Full `vendor/bin/phpunit` suite | Not required for gate; attendance + related features run green |
| Parallel MySQL concurrency | SQLite covers sequential + unique collision; MySQL lock race needs staging |
| Flutter FE-01…FE-05 | App not in repo; Antigravity unavailable |
| Production NTP (`chronyc`) | Ops host access not available from this session |
| Manual QR/NFC physical devices | Hardware not attached |

---

## 10. Remaining risks

1. **Static QR/NFC still reusable** when policy flag off (default) — product may need single-use passes later.  
2. **GPS remains client-supplied** — not cryptographic presence; accuracy cap mitigates only extreme spoofing.  
3. **QR policy NFC path** — when `require_nfc_or_gps_before_qr` is on, server enforces GPS-in-radius; session-bound NFC proof not implemented (documented in brief).  
4. **SQLite vs MySQL locking** — parallel double-punch under real MySQL not proven in CI.  
5. **Historical `attendance_logs`** may still contain raw identifiers.  
6. **External Flutter** must adopt contract (clock service, idempotency, offline labels) before full client security story is complete.  
7. **Kiosk `captured_at`** still client/kiosk-supplied (out of mobile scope).  
8. **Working tree** still contains unrelated pre-existing dirty files — commit only attendance-related paths when owner requests commit.

---

## 11. Rollback instructions

```bash
# Discard uncommitted attendance work on feature branch
git checkout -- \
  app/Http/Controllers/Api/AttendanceApiController.php \
  app/Services/Attendance/AttendanceService.php \
  app/Services/Attendance/AttendanceLogService.php \
  app/Helpers/AttendanceHelper.php \
  app/Resources/Attendance/TodayAttendanceResource.php \
  app/Resources/Attendance/NightAttendanceResource.php \
  routes/api.php

# Remove new files
rm -f app/Http/Controllers/Api/TimeApiController.php \
      config/attendance.php \
      lang/en/attendance.php \
      tests/Feature/AttendanceTimeVerificationTest.php

# Or abandon branch entirely
git checkout main
git branch -D feat/attendance-time-verification   # only if not merged
```

No migrations to roll back. Settings rows (if seeded) can be deleted from `attendance_settings` by slug.

---

## 12. Production deployment checklist

**Do not deploy without explicit Project Owner approval.**

- [ ] Commit **only** attendance-related files (review `git diff` carefully; exclude unrelated dirty files)  
- [ ] Code review of `AttendanceApiController`, `AttendanceService`, resources, tests  
- [ ] Staging: run full PHPUnit suite + smoke `POST /api/employees/attendance` + `GET /api/time`  
- [ ] Verify mobile app still sends `offline_request_id` / accepts new response fields  
- [ ] Confirm `attendance_method` production values match intended methods  
- [ ] Decide default for `require_nfc_or_gps_before_qr` and `attendance_location_accuracy`  
- [ ] App hosts: `chronyc tracking` and `chronyc sources -v` — NTP healthy, offset << 1s  
- [ ] DB backups current; no destructive SQL  
- [ ] Feature flag / gradual rollout if needed; monitor attendance error rates  
- [ ] Telegram/notification queue workers healthy  
- [ ] Coordinate Flutter release (FE tasks) against contract before claiming full program done  
- [ ] Post-deploy: verify a real check-in returns `server_time_utc` and `idempotent_replay: false`

---

## 13. Quality gate status

| Gate | Status |
|------|--------|
| Official time from trusted backend | **Pass** (`received_at` / `now()` only) |
| Device clock cannot change official records | **Pass** (tests; `recorded_at` audit-only) |
| Clock UI smooth | **N/A here** — FE task file for Flutter |
| Time sync not 1 Hz | **Pass** (endpoint throttled; contract forbids 1 Hz) |
| Duplicate requests safe | **Pass** (lock + unique + replay) |
| NFC OR GPS before QR where configured | **Partial** — optional GPS-in-radius when flag on; NFC session proof not implemented |
| Offline never shown as confirmed | **Server-side** `idempotent_replay` + contract; **client FE-03 pending** |
| Cross-midnight tests | **Pass** |
| Existing functionality/UI preserved | **Pass** (face kiosk, bonus, monthly, meeting tests) |
| Backward compatibility | **Pass** (additive fields; envelope stable) |
| Security-sensitive changes reviewed | **Pass** (this review + tests) |

**Program completion:** Backend BE-01…BE-05 **done and verified**. Flutter FE-01…FE-05 **blocked** on missing Antigravity integration and missing Flutter repository. Full Phase 5 “project complete” requires FE work in the external app repo.
