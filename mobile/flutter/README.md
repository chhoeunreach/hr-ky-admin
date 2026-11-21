# HR payroll queue Flutter screens

The main app is in the sibling `hrky-flutter` repository. Its home scanner supports payroll queue QR codes. This directory also contains sample screens in `lib/hr_queue_module.dart` for separate integrations using `qr_flutter` and `mobile_scanner`.

## Employee queue flow

1. Payroll staff create a session in **Admin → Payroll Queue**, selecting a branch, department, and optionally an employee. The QR modal can send an app invitation to that scope.
2. The employee scans the session QR from the home scanner. The app extracts the token and calls `POST /api/v1/payroll-queue/join` with the employee's session.
3. The ticket dialog polls `GET /api/v1/payroll-queue/ticket/{ticketId}` for position and counter assignment. The route only returns the signed-in employee's own ticket. Both endpoints require the Payroll Management mobile menu and View Payslip Detail permission.
4. Payroll staff call the next ticket into a counter, review the saved payslip, optionally quick-update its draft bonus, and commit payment in the secured payroll room screen. Paid tickets move into the Finished/Paid panel.

Do not send a client-supplied employee ID when joining; the API takes the employee identity from the authenticated token.

## QR attendance in and out

For attendance, generate a fresh employee pass with `POST /api/v1/hr-queue/qr/generate` before each scan (passes expire after five minutes). Use `HrAttendanceScannerScreen` to choose **IN** or **OUT**. Post the pass, action, latitude, and longitude to `POST /api/v1/hr-queue/queue/scan`; include a selfie when attendance policy requires one. The server checks the signature, expiry, selected action, and location through the existing attendance rules. Check-in records canonical attendance and joins the daily HR queue; check-out records attendance and does not create a ticket.

The same scanner endpoint accepts the admin-created payroll session QR URL. It returns `qr_kind: payroll_session` and the new ticket, without treating that session QR as an attendance check-in or check-out. Obtain GPS with the host app's location implementation and send the signed-in API token for scans.
