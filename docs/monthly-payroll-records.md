# Saved monthly payroll

Apply the new table migration before deploying the updated payroll pages:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000001_create_monthly_payroll_records_table.php
```

The Monthly Payroll page now has Monthly and Yearly views. The yearly view lists
all twelve months and summarizes saved records for the selected branch/year.

Users with `view_monthly_payroll` and `generate_payroll` can choose **Generate Preview**.
Select one or more branches, use **All branches**, or leave the selection empty
for all branches available to the current user. Branch-restricted users can only
preview and save their permitted branch, even if other IDs are submitted directly.
Generation runs for each entire selected branch, regardless of department, search,
or pagination filters. The form exposes attendance, tax, contribution, travel,
and advance-salary options using the existing payroll calculator.

The preview runs the existing payroll calculator in a rollback-only transaction,
then caches a private preview for its creator for 30 minutes. No new payslips or
monthly records remain saved after preview generation. Cancel returns to the
report without saving.

The preview shows all 28 columns and totals with a separate **Save payroll**
button. Saving runs the calculation again in a transaction and verifies the
reviewed employee values and totals still match. Multiple new branch records
save in a single transaction; a failure rolls back all new branch records.
Already-saved branches are included for review and remain unchanged. Branches
with no payroll data are listed separately and do not create empty records.
Changed data rejects the save
and requires a fresh preview. A successful save consumes the preview token.

Existing monthly payslips are preserved in every status. Save creates only
missing payslips, then saves the full branch/month report in one transaction.
Empty months are not marked as saved. There is one saved record per branch/month;
repeating the action returns an error instead of replacing it.

Saved records contain the displayed columns, employee details, monetary values,
commission amounts, generation options, creator, and creation time. Monthly
views and exports use those saved values once the record exists. Subsequent
employee, payslip, and commission edits do not rewrite the saved report.
The displayed headings and column order always use the current 28-column
layout, including when opening older saved records. Missing saved values remain
blank; changing the layout does not recalculate historical amounts.

`ខ្ចីលុយ` shows approved loan money actually released during the calendar month,
using `released_amount` and `amount_granted_date`. It includes loans already
settled, excludes pending/rejected requests, and is separate from the loan
repayment deducted on a payslip. Net salary is not recalculated from this column.
The yearly loan total adds the saved monthly loan amounts. New records freeze
these amounts at generation.

After updating the loan calculation, apply the targeted correction for older
saved records. It changes only their loan figures and loan totals, retaining a
backup for rollback:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000002_correct_monthly_payroll_loan_amounts.php
```

Months that have existing payslips but no saved record remain available in the
monthly view. They appear as **Not saved** in the yearly view until generated
and saved explicitly.

`កាត់` shows the sum of recorded payslip deductions. An expandable section inside the total shows unpaid leave/absence (`absent_deduction`), lateness/undertime
(`undertime`), salary advance repayments (the raw payslip `advance_salary`),
installments, warnings/penalties, and other deductions including tax, SSF and PF.
Salary components are included only when their type is `deductions`; their names
classify the breakdown. Each component is counted once, including generic កាត់
components under other deductions. Warnings without a recorded monetary salary
component do not create a charge. The combined absence/unpaid leave and undertime
amounts are shown as stored rather than estimating a split.

The restored 28-column layout keeps deduction categories inside កាត់ rather
than adding separate columns. CSV, Excel and print use the same deduction total.
Preview and newly saved records retain the breakdown inside the snapshot.
Older records without a breakdown retain their saved total. No historical
payslip or net salary is recalculated by this display.

Payroll headings and deduction detail labels use Khmer. The original មកយឺត,
ចេញមុន and ពិន័យ columns are retained in their original positions.
The loan disbursement column remains distinct from salary advance repayments.

The លើកទឹកចិត្ត cell offers an Add/Edit form for generated or review payslips
before a monthly record is saved. Both `view_monthly_payroll` and `edit_payroll`
are required, with the same branch restriction as the report. The entered amount
replaces the payslip bonus for that employee and month; net pay changes only by
the difference. Tax and deductions retain their recorded amounts, as stated in
the form. A zero amount removes the incentive. Other months are unaffected.
Saved, locked and paid payroll cannot be changed through this action. Changes
are protected against stale forms and concurrent saving; previews generated
before a change must be regenerated before Save. The existing snapshot and yearly
summary flows retain the updated bonus and net pay without extra columns.

ប្រាក់ shows the unpaid-leave portion of the recorded absence deduction,
using the saved monthly salary divided by 30 and multiplied by unpaid leave
days, limited to the recorded absence deduction. It is zero when attendance
deductions were disabled and blank if that setting was not recorded. It does
not use a named salary component or the current employee salary. It is already
included in កាត់ and is not subtracted again. Newly generated salaried monthly
payroll uses the fixed 30-day divisor for unpaid leave; the existing absence
and hourly rules stay as configured. Existing payslips and saved records are
not regenerated or rewritten by opening the report.

During Generate Preview / Save, មិនឈប់ is reconciled for editable monthly
payslips: បុគ្គលិក/default staff $20, ប្រធាន or a department containing ជាង $30,
and ថ្នាក់ដឹកនាំ $50 (management takes precedence). The month must have ended,
the employee must have joined by its first day, and every scheduled workday must
have approved attendance with check-in and check-out. Company weekends, active
company holidays and approved Day Off requests are allowed. Other pending,
review or approved leave/Time Leave requests on a workday, missing attendance,
or incomplete attendance make the award $0. Lateness alone does not remove this
full-attendance award; it remains subject to the existing lateness deductions.

The award is stored once as an earning component and adjusts net pay by the
difference from any previously recorded attendance award, independently of the
manual លើកទឹកចិត្ត bonus. Preview rolls these changes back; Save freezes the
reviewed amount. Paid/locked payslips and existing saved monthly records retain
their recorded amounts. The live report displays recorded amounts until a new
preview is generated; viewing a report never changes salary.
