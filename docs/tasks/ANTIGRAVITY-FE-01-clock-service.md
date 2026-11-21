# FE-01 — Hybrid Clock Service (Antigravity)

**Status:** Blocked — Antigravity integration not available; Flutter app not in this monorepo.  
**Contract:** `docs/attendance-api-contract.md` §3  
**Target repo:** External Flutter mobile application (not `mobile/flutter` reference-only tree).

## Objective

Reusable clock service for display + offset estimation only. Never feeds official attendance time.

## Requirements

1. Show time immediately from device local clock.
2. Sync via `GET /api/time` (or existing endpoint returning `server_time_utc`).
3. Estimate server offset:
   - `t0` local send, `t1` local receive (monotonic where available)
   - RTT = t1 - t0; offset ≈ server_utc - (t0 + RTT/2)
   - Prefer lowest-RTT samples; keep small reservoir (e.g. last 5–10).
4. Do **not** request server time every second.
5. Resync triggers: login, app resume, connectivity restored, |offset| exceeds threshold (e.g. > 2s), periodic slow refresh (e.g. 15–30 min).
6. Elapsed duration uses monotonic clock, not wall clock alone.
7. Mark synced time as estimate (`isEstimated: true`); UI may show “synced” badge only after successful sample.
8. Failed sync: keep last offset, degrade to device clock, do not block attendance UI.

## Acceptance

- Unit tests: manual clock change, wrong TZ, latency, failed sync, drift recovery.
- No attendance write path imports this service’s “now” as official time.
