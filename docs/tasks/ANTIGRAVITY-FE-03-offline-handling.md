# FE-03 — Offline Handling (Antigravity)

**Status:** Blocked — no Antigravity integration; Flutter app external.  
**Contract:** `docs/attendance-api-contract.md` §4.7

## Requirements

1. Distinguish **pending offline** attempts from **server-confirmed** attendance in data model + UI.
2. Never label offline device timestamp as verified/present.
3. Persist `offline_request_id`, payload hash, evidence expiry, sync state.
4. Do not auto-replay expired QR/NFC/`qr_pass` evidence after restore.
5. Respect admin review policies; no silent promotion of offline rows.
6. Connectivity restore: retry pending attempts with **same** idempotency keys, backoff, single-flight.

## Acceptance

- Offline badge ≠ Present badge.
- Expired pass shows “expired — generate new evidence”, not silent failure loop.
