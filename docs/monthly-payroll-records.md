# Saved monthly payroll

Apply the new table migration before deploying the updated payroll pages:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000001_create_monthly_payroll_records_table.php
```

The Monthly Payroll page now has Monthly and Yearly views. The yearly view lists
all twelve months and summarizes saved records for the selected branch/year.

Users with `view_monthly_payroll` and `generate_payroll` can choose **Generate Preview**.
Generation runs for the entire selected branch, regardless of department, search,
or pagination filters. The form exposes attendance, tax, contribution, travel,
and advance-salary options using the existing payroll calculator.

The preview runs the existing payroll calculator in a rollback-only transaction,
then caches a private preview for its creator for 30 minutes. No new payslips or
monthly records remain saved after preview generation. Cancel returns to the
report without saving.

The preview shows all 28 columns and totals with a separate **Save payroll**
button. Saving runs the calculation again in a transaction and verifies the
reviewed employee values and totals still match. Changed data rejects the save
and requires a fresh preview. A successful save consumes the preview token.

Existing monthly payslips are preserved in every status. Save creates only
missing payslips, then saves the full branch/month report in one transaction.
Empty months are not marked as saved. There is one saved record per branch/month;
repeating the action returns an error instead of replacing it.

Saved records contain the displayed columns, employee details, monetary values,
commission amounts, generation options, creator, and creation time. Monthly
views and exports use those saved values once the record exists. Subsequent
employee, payslip, and commission edits do not rewrite the saved report.
The displayed headings and column order always use the current fixed 28-column
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
