# FE-04 — Performance (Antigravity)

**Status:** Blocked — no Antigravity integration; Flutter app external.

## Requirements

1. Smooth animations; no jank on attendance screens.
2. Avoid unnecessary rebuilds (const, selective providers/selectors).
3. No redundant network requests (coalesce dashboard refresh after punch).
4. Graceful timeouts; user-visible recovery, no hung spinner forever.
5. Preserve existing UI and navigation structure.
6. Heavy parse/file work off main isolate where applicable.
7. Clock sync must not poll every second (see FE-01).

## Acceptance

- Profile attendance flow: no repeated identical GETs within short window unless triggered.
