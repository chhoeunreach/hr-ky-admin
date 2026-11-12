# Meeting attendance

From **Team Meetings**, choose **QR & Attendance** on a meeting. The register shows invited employees, pending/joined status, and the original check-in time. Search by name or filter by status, refresh to see new scans, and export the full register as CSV.

Users with `show_team_meeting` can view, print the QR, and export. Users with `edit_team_meeting` can enable or disable check-in, set its window, or replace the QR. Blank opening and closing times use the start and end of the meeting date in the configured application timezone. Replacing a QR immediately invalidates previous copies and preserves recorded attendance.

## Database setup

The feature requires these migrations, in order (run only those still pending):

```sh
php artisan migrate --path=database/migrations/2026_08_17_000001_create_meeting_attendances_table.php
php artisan migrate --path=database/migrations/2026_11_12_000005_add_meeting_checkin_controls.php
```

## Mobile API

Both endpoints require an API bearer token, `mobile.menu.meeting-attendance`, and the corresponding function permission:

| Endpoint | Function permission |
| --- | --- |
| `GET /api/team-meetings/attendance/status` | `mobile.function.TeamMeeting.meetingAttendanceStatus` |
| `POST /api/team-meetings/attendance/scan` | `mobile.function.TeamMeeting.scanMeetingAttendance` |

Status returns `data.meetings`, containing assigned, published meetings within the last year and upcoming meetings. Each record includes `id`, title, venue, meeting date/time, `attendance_status` (`disabled`, `upcoming`, `open`, `closed`), ISO 8601 `check_in_opens_at` / `check_in_closes_at`, `is_joined`, and check-in timestamps.

Send the scanner's full QR text as JSON:

```json
{"qr_payload": "meeting-qr:<encrypted content>"}
```

A successful scan returns `data.attendance` and `data.already_joined`. Repeat scans preserve the original timestamp and return `already_joined: true`. Only assigned employees in the meeting's company can join. The server rejects malformed/replaced codes and first-time check-ins outside the allowed window. The mobile app should display the response message for failures (401 authentication, 403 permissions/assignment, 404 inaccessible meeting, 422 invalid QR or closed check-in).

## Validation

```sh
vendor/bin/phpunit tests/Feature/MeetingCheckInTest.php
```

The tests use an isolated in-memory SQLite database, the actual attendance migrations, and exercise service rules, organizer controls, CSV export, and authenticated mobile HTTP requests.
