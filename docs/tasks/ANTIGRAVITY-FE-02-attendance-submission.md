# FE-02 — Attendance Submission (Antigravity)

**Status:** Blocked — no Antigravity integration; Flutter app external.  
**Contract:** `docs/attendance-api-contract.md` §4

## Requirements

1. Use existing `POST /api/employees/attendance` (do not invent new endpoints).
2. Generate and **persist** idempotency key (`offline_request_id`) per logical attempt (UUID).
3. Reuse the same key for all retries of that attempt; new key only for a new logical punch.
4. Disable duplicate UI submissions while request in flight (button loading state).
5. Show confirmation **only** after server success response (including `idempotent_replay: true`).
6. Preserve current QR/NFC/GPS workflow and UI design.
7. Prefer `server_time_utc` from response for any success timestamp display (fallback: formatted server fields already returned).

## Acceptance

- Double-tap test creates one attendance row.
- Timeout + retry with same key returns `idempotent_replay: true` and one row.
