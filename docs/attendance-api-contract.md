# Attendance Time & Security API Contract

**Status:** Approved for parallel implementation  
**Date:** 2026-11-20  
**Owners:** OpenCode (contract), Codex (backend), Antigravity (Flutter — external repo)  
**Compatibility rule:** Additive only. Existing response envelope and field names remain stable unless noted as deprecated.

---

## 1. Shared principles

1. **Laravel owns all official attendance timestamps.**
2. Client clocks (device time, `recorded_at`) are **never** authoritative for check-in/out, late, OT, bonus, or shift date.
3. Official times are computed from a **server request receipt timestamp** captured as early as practical in the attendance pipeline.
4. Storage continues to use existing local schema (`Asia/Phnom_Penh` wall time for `time`/`date` columns) unless a separate migration is approved. New API fields expose **UTC ISO-8601** for clients.
5. Device GPS/NFC/QR evidence is untrusted input.
6. Idempotent retries reuse the same client attempt key.
7. Offline attempts are **pending** until the server confirms; never labeled verified attendance.

---

## 2. Endpoint inventory

| Method | Path | Auth | Purpose | Status |
|--------|------|------|---------|--------|
| `POST` | `/api/employees/attendance` | `auth:api` + `app.security` + `permission` + `MobileFunctionPermission` | Unified check-in/out (wifi/nfc/qr/qr_pass/face) | **Active — primary** |
| `POST` | `/api/employees/check-in` | same | Deprecated check-in | Keep contract, do not expand |
| `POST` | `/api/employees/check-out` | same | Deprecated check-out | Keep contract, do not expand |
| `GET` | `/api/employees/attendance-detail` | same | Monthly detail | Unchanged |
| `GET` | `/api/time` | none or light throttle (see §3) | Clock sync | **New (additive)** |
| `POST` | `/api/kiosk/v1/attendance` | `kiosk.device` | Face kiosk | Unchanged (reference pattern) |
| `GET` | `/api/dashboard/attendance-summary` | authenticated | Monthly summary | Unchanged |

---

## 3. Time synchronization (BE-02)

### `GET /api/time`

- **Auth:** Public (no bearer) **or** optional authenticated; must be cheap and cacheable at CDN edge if public.
- **Throttle:** e.g. `60,1` per IP (not per-second polling design).
- **Precision:** Generated at request handling time with `now()`; millisecond precision in ISO-8601 string; document that client RTT dominates error.

**Response `200`:**

```json
{
  "status": true,
  "message": "ok",
  "status_code": 200,
  "data": {
    "server_time_utc": "2026-11-20T04:12:00.123Z",
    "timezone": "Asia/Phnom_Penh",
    "timezone_offset": "+07:00"
  }
}
```

**Client rules (FE-01):**

- Display local device time immediately.
- Sync on login, resume, connectivity restore, and drift detection — **not every second**.
- Estimate offset with RTT/2 using monotonic elapsed clock; prefer low-RTT samples.
- Synchronized time is an **estimate for UI only**, never an official attendance timestamp.

Existing `server_time` fields (kiosk bootstrap, app-security heartbeat) remain as-is.

---

## 4. Attendance submission — `POST /api/employees/attendance`

### 4.1 Request headers

| Header | Required | Notes |
|--------|----------|-------|
| `Authorization: Bearer {token}` | Yes | Passport / `auth:api` |
| App-security headers | Per existing `app.security` middleware | Unchanged |
| `Idempotency-Key` | Recommended | Optional alternate to body field; if both present, body wins |

### 4.2 Request body

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `attendance_type` | enum | Yes | `wifi\|nfc\|qr\|qr_pass\|face\|biometric` — must be in enabled `attendance_method` (server-enforced) |
| `latitude` | number | Yes* | *May be omitted only when location feature fully disabled server-side; otherwise required |
| `longitude` | number | Yes* | Same as latitude |
| `accuracy` | number | No | Horizontal accuracy meters; **enforced max** when location required (server default e.g. 100m — confirm in impl) |
| `identifier` | string | Conditional | Required for `nfc`, `qr`, `qr_pass` |
| `attendance_status_type` | `checkIn\|checkOut` | Conditional | Required for `wifi` |
| `router_bssid` | string | No | Wifi evidence |
| `note` | string | No | |
| `selfie` | file | Per setting | Unchanged |
| `offline_request_id` | string ≤100 | Recommended | **Idempotency key** for offline/online retries |
| `recorded_at` | string/date | No | **Non-authoritative.** Accepted only as audit claim; may be ignored for official time. Skew > server-configured bound is logged, not used |
| `device_name`, `app_version`, … | string | No | Location metadata only |

**Idempotency key format:**

- UUID v4 recommended: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
- Or ULID/kotlin-uuid, max 100 chars, charset safe for MySQL string unique index.
- **Same logical attempt → same key forever** (including retries after timeout, 5xx, process death).
- New attempt (user deliberately punches again after success/failure terminal) → new key.
- Server maps key → attendance row via `check_in_offline_request_id` / `check_out_offline_request_id`.

### 4.3 Official timestamp policy (BE-01)

| Field | Source |
|-------|--------|
| Server receipt | Middleware/controller entry: `$requestReceivedAt = now()` (app timezone) |
| `attendance_date` | Derived from receipt in `Asia/Phnom_Penh` (or existing local logic once receipt is official) |
| `check_in_at` / `check_out_at` / `night_*` | Derived from receipt (+ existing grace/time-leave adjustments) |
| `recorded_at` (client) | Audit only (`client_recorded_at` optional column if migration approved; else log-only) |

**Response must include authoritative server times.**

### 4.4 Success response `200` (additive fields)

Existing envelope preserved:

```json
{
  "status": true,
  "message": "Check in successful",
  "status_code": 200,
  "data": {
    "check_in_at": "09:15 AM",
    "check_out_at": "-",
    "productive_time_in_min": 0,
    "check_in_location": { "latitude": 11.55, "longitude": 104.92 },
    "check_out_location": { "latitude": null, "longitude": null },
    "check_in_selfie": null,
    "check_out_selfie": null,
    "latest_location": { "latitude": 11.55, "longitude": 104.92 },
    "within_branch_radius": true,
    "location_validation_message": "…",
    "server_time_utc": "2026-11-20T02:15:00.000Z",
    "attendance_id": 12345,
    "idempotent_replay": false,
    "attendance_status": "checked_in"
  }
}
```

| New field | Type | Meaning |
|-----------|------|---------|
| `server_time_utc` | ISO-8601 Z | Official receipt time (UTC) |
| `attendance_id` | int | Stable row id for this punch record |
| `idempotent_replay` | bool | `true` when offline key matched existing success |
| `attendance_status` | enum | `checked_in`, `checked_out`, `checked_in_night`, `checked_out_night` |

Night resources may keep `night_checkin` / `night_checkout` display keys; also include `server_time_utc`.

### 4.5 Error responses

Envelope unchanged: `{ "status": false, "message": "...", "status_code": <http>, "data"?: {errors} }`

| HTTP | `message` / code semantics | When |
|------|----------------------------|------|
| 401 | unauthenticated | No/invalid token |
| 403 | permission / location outside radius / disabled method / policy | AuthZ & policy |
| 404 | not found | Rare |
| 409 | already checked in/out; concurrent conflict | State machine |
| 422 | validation_failed | Schema errors |
| 400 | business rule (late, early, invalid_qr, invalid_nfc, …) | Existing messages |
| 429 | throttle | Rate limit |
| 500 | server error | Unexpected |

**Stable business error strings (keep existing lang keys):**  
`invalid_qr`, `invalid_nfc`, `alert_checkin`, `checkout_alert`, `already checked in`, `already checked out`, `not_checked_in_yet`, `early_checkout`, `late_checkin`, etc.

**Replay / invalid evidence:** Prefer `400`/`403` with existing messages; do not invent new success paths for invalid tokens.

### 4.6 Retry behavior

| Client situation | Behavior |
|------------------|----------|
| Timeout / network error after send | Retry **same** `offline_request_id` |
| 5xx | Retry same key with backoff |
| 4xx validation | Fix payload; new key only if logical attempt changed |
| 409 already complete | Treat as terminal success or terminal failure per message; same key safe |
| Success with `idempotent_replay: true` | Show existing confirmation; do not double-animate |

Server: unique violation on offline key → load existing row → return success payload with `idempotent_replay: true` (face kiosk pattern).

### 4.7 Offline review behavior

- Client stores pending attempt: `{ offline_request_id, payload, status: pending, created_at }`.
- UI: **Pending sync** ≠ **Present/Verified**.
- Do not auto-replay expired `qr_pass` (300s) or any evidence the server marks expired.
- Admin review policies unchanged; no silent conversion of offline device time into verified attendance.

---

## 5. Attendance status values

| Client-facing | Server row signal |
|---------------|-------------------|
| `checked_in` | `check_in_at` set, `check_out_at` null (day) |
| `checked_out` | both day times set |
| `checked_in_night` | `night_checkin` set, `night_checkout` null |
| `checked_out_night` | both night times set |
| `pending_offline` | Client-only; never returned as verified from server |
| `absent` / leave / … | Existing monthly detail `day_status` values unchanged |

---

## 6. Security rules (BE-03) — contract-level requirements

1. Reject `attendance_type` not in enabled `attendance_method` (except kiosk face device path).
2. If location feature enabled: require lat/lng; enforce `accuracy <= configured max`; enforce branch radius for `workspace_type = office` as today.
3. NFC: server-side registered identifier only; log **hash**, not raw.
4. QR static: same as NFC for logging; if policy flag `require_nfc_or_gps_before_qr` enabled, require recent valid NFC proof **or** in-radius GPS **before** accepting QR (implement as setting; default = current behavior until product confirms).
5. `qr_pass`: keep HMAC + 300s window; reject future timestamps beyond small skew.
6. Idempotency + DB transaction + lock/unique as specified in BE tasks.
7. Audit log fields: `employee_id`, `attendance_type`, `action`, `source`, `attendance_id`, `identifier_hash`, `server_received_at`, `idempotency_key` (if column exists), result — **never raw QR/NFC secret**.

---

## 7. Calculations (BE-04)

All of the following use **official receipt time** (and existing schedule rules):

- check-in / check-out  
- late arrival / early departure (grace windows)  
- overtime / undertime / worked duration  
- attendance bonus eligibility inputs  
- night shift and cross-midnight  

Shift date = official local date at receipt (or night-shift date rules already implemented — do not change business rules, only the time input).

---

## 8. Backward compatibility checklist

- [ ] Old clients sending `recorded_at` still receive HTTP 200 and same display fields  
- [ ] Old clients ignoring new fields (`server_time_utc`, etc.) keep working  
- [ ] `offline_request_id` column names and uniqueness preserved  
- [ ] Deprecated check-in/out endpoints unchanged in shape  
- [ ] Face kiosk tests still pass  
- [ ] Monthly summary / bonus tests still pass  
- [ ] No required new request headers for legacy apps  

---

## 9. Flutter obligations (FE — external repo)

| ID | Obligation |
|----|------------|
| FE-01 | Hybrid clock service; no 1 Hz polling; offset estimate only for UI |
| FE-02 | Persist idempotency key per logical attempt; disable double-submit; confirm only after server response |
| FE-03 | Distinguish pending offline vs confirmed; never show device time as verified |
| FE-04 | Smooth UI, no redundant requests, timeouts handled off main isolate where needed |
| FE-05 | Tests: clock change, TZ, latency, offline, double tap, resume, failed sync, drift, retry, server confirm |

---

## 10. Explicit non-goals (this iteration)

- Rewriting Flutter UI/navigation  
- Full cryptographic NFC presence proofs  
- Migrating all historical attendance rows to UTC  
- Changing monthly bonus dollar rules  
- Production deploy without Project Owner approval  
