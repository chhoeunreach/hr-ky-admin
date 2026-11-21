# Task Tracker — Attendance Time Verification

**Project:** Digital HRMS Secure Attendance Time Verification  
**Lead:** OpenCode  
**Date started:** 2026-11-20

## Agent integrations

| Agent | Integration | Verified | Notes |
|-------|-------------|----------|-------|
| Codex | CLI `codex exec` | Yes | Non-interactive backend tasks |
| Antigravity | CLI/API/MCP | **No** | Not installed; task files only |

## Progress

| Phase | Status | Artifact |
|-------|--------|----------|
| 1 Existing system audit | **Complete** | `docs/attendance-time-audit.md` |
| 2 Task delegation | **Complete** | Codex executed; Antigravity task files only |
| 3 Shared API contract | **Complete** | `docs/attendance-api-contract.md` |
| 4 Integration review | **Complete** | OpenCode review + tests |
| 5 Quality gates | **Complete (backend)** | Flutter FE blocked — see report |
| 6 Final report | **Complete** | `docs/attendance-implementation-report.md` |

## Backend tasks (Codex)

| ID | Task | Status | Branch / notes |
|----|------|--------|----------------|
| BE-01 | Authoritative server time | **Complete** | `received_at` only; `recorded_at` audit-only |
| BE-02 | Lightweight time API | **Complete** | `GET /api/time` |
| BE-03 | Attendance security | **Complete** | Locks, idempotency, method/accuracy, hashed logs |
| BE-04 | Calculations on server time | **Complete** | Night/day use receipt time |
| BE-05 | Backend tests | **Complete** | 55 Attendance tests green + regressions |

## Flutter tasks (Antigravity — external repo)

| ID | Task | Status | Artifact |
|----|------|--------|----------|
| FE-01 | Hybrid clock service | **Blocked — no agent/repo** | `docs/tasks/ANTIGRAVITY-FE-01-clock-service.md` |
| FE-02 | Attendance submission idempotency | **Blocked** | `docs/tasks/ANTIGRAVITY-FE-02-attendance-submission.md` |
| FE-03 | Offline handling | **Blocked** | `docs/tasks/ANTIGRAVITY-FE-03-offline-handling.md` |
| FE-04 | Performance | **Blocked** | `docs/tasks/ANTIGRAVITY-FE-04-performance.md` |
| FE-05 | Flutter tests | **Blocked** | `docs/tasks/ANTIGRAVITY-FE-05-flutter-tests.md` |

## File ownership (no simultaneous edits)

| Area | Owner | Paths |
|------|-------|-------|
| Backend attendance | Codex | `app/Http/Controllers/Api/AttendanceApiController.php`, `app/Services/Attendance/*`, `app/Helpers/AttendanceHelper.php`, `app/Http/Middleware/*`, `routes/api.php`, tests |
| Docs / contract | OpenCode | `docs/attendance-*.md` |
| Flutter app | Antigravity (external) | Outside this monorepo |

## Open risks

1. Full Flutter application not in this repository.  
2. “NFC OR GPS before QR” not found as server rule — implement as optional setting only if product confirms (default: preserve current behavior).  
3. Existing working tree has unrelated uncommitted changes — backend work must use isolated branch/worktree.  
4. Historical time columns remain local wall clock — no destructive UTC migration without approval.  
