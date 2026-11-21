# FE-05 — Flutter Tests (Antigravity)

**Status:** Blocked — no Antigravity integration; Flutter app external.

## Required tests

| # | Scenario |
|---|----------|
| 1 | Manually changed device time does not change official attendance fields from server |
| 2 | Incorrect device timezone: UI clock may be wrong; submission still uses server time fields |
| 3 | Network latency: offset estimate accounts for RTT |
| 4 | Offline mode: pending state UI, no verified label |
| 5 | Duplicate taps: one request, one idempotency key |
| 6 | App background/resume: resync clock, no duplicate punch |
| 7 | Failed synchronization: app remains usable, retries later |
| 8 | Clock drift beyond threshold triggers resync |
| 9 | Retry after timeout reuses same `offline_request_id` |
| 10 | Server confirmation (`idempotent_replay` / success) drives UI state |

## Command (external repo)

Document actual commands in implementation report once Flutter repo path is provided, e.g.:

```bash
flutter test test/attendance/
```
