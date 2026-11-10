# Commission administration

## Open the editor

In the admin panel, open **Payroll → Commission Settings** (`/admin/commission-settings`). There is one global configuration for all branches, as requested. Existing report ownership and payroll permissions are unchanged.

Administrators have access through the existing admin authorization. Other back-office roles need the new `manage_commission_settings` permission, which can be assigned in the existing role-permission editor. This permission is not automatically granted to employees or existing finance roles.

## Manage each service

The form has rows for Sell, Material, Iron, Repair, Buy In, and iCloud Cus. Each row controls:

- Enabled/disabled commission earning.
- Rate and calculation basis: per item or per invoice.
- Whether a customer phone number longer than six characters is required.
- Minimum invoice amount.
- Minimum quantity for part-time and full-time work categories. A zero minimum removes that condition.

Seeded defaults preserve the prior rules: Sell 0.25 per item with 50/100 quantity thresholds; Material 0.25 per invoice with a minimum amount of 10; Iron and Repair 0.20 per invoice. Buy In and iCloud Cus start disabled with rate zero. An authorized administrator can explicitly enable/configure those types. Buy In spending still does not count as revenue; iCloud Cus remains outside the five-service financial Dashboard aggregate.

The global **Enable commission earning** switch controls earning on new or explicitly edited transactions. **Show commission** controls mobile visibility. **Allow report commission calculations** controls period previews; with it disabled, permitted viewers receive saved invoice commissions. The default mode can apply or bypass the configured conditions. Mode selection never bypasses a disabled service or the global earning switch.

## Mobile role controls

For each role, the administrator can control:

- Show commission.
- Allow calculations.
- Allow changing between with/without conditions.

These are mobile POS controls. They do not change role assignments, work categories, payroll access, or access to other employees' records. Roles without an override inherit the global controls and existing enabled access defaults.

Hidden commission is removed by the server from paginated reports, legacy lists, details, and CSV exports. Qualification counters/statuses and rules are also hidden. Hidden clients cannot use status filters to infer qualification. The server enforces the allowed mode independently of Flutter. Flutter hides the associated cards, controls, form previews, details, row badges, and locally exported columns. Without calculation permission, visible summaries use persisted commissions rather than a new preview.

Users receive changes on the next report refresh or page entry. The mobile app does not fall back to hardcoded visible rules when policy metadata is missing.

## Saved records and audit

Changing settings does not update existing invoice/payroll commissions. New transactions use the current policy. Explicitly editing an invoice recalculates that transaction under the current policy through its existing edit flow. Report previews use the current policy over the full filtered period, grouped by owner, normalized seller, work category, and service, before paging/status filtering. Each transaction's preview is rounded to two decimals before totals are summed.

Every successful settings save records the previous/new configuration, actor, revision, and timestamp in `commission_setting_changes`. The editor shows recent revisions. A stale form receives HTTP 409 instead of overwriting a newer edit. Invalid values and unauthorized saves are rejected without changing settings.

## Deploy

Deploy backend and mobile updates together, including every configured API host. Apply this feature's migration after the existing reporting/index migrations:

```sh
php artisan migrate --path=database/migrations/2026_11_10_000002_create_commission_settings.php
php artisan route:list --path=commission
```

The migration creates `commission_settings`, `commission_setting_changes`, the initial global configuration, and the management permission. It does not update sales reports or payroll. No live/business-database migration was run during implementation because the configured local MySQL connection was unavailable. Tests use their own isolated databases.

The authenticated mobile policy endpoint is `GET /api/sell-out-reports/commission-policy`. Both report list contracts and detail/write responses include effective `commission_policy` metadata. No role overrides belonging to other roles are exposed by that endpoint.

## Validate

```sh
php vendor/bin/phpunit tests/Feature/MobilePosReportingTest.php tests/Feature/CommissionSettingsTest.php
```

These tests exercise the real permission middleware with isolated SQLite fixtures. `POS_REPORT_MYSQL_SOCKET` also supports the existing disposable `/tmp/kneayerng-pos-mysql.<id>/mysql.sock` test instance and its `pos_reporting_test` database, without configured business credentials. Outbound jobs/HTTP are faked. The admin form is rendered through its real controller and Blade view with a test layout shell that avoids unrelated HR layout queries.

## Verification on 10 November 2026

- 43 backend test cases / 423 assertions passed on isolated SQLite and temporary MySQL.
- 94 Flutter tests passed across the report, dashboard, policy, scanner/form, and API suites, including a session-expiry race check.
- Targeted Flutter analysis reported no issues.
- Android debug APK and unsigned iOS simulator builds succeeded.
- The Flutter API contract fixture was regenerated from the updated Laravel HTTP response against the disposable MySQL fixtures and parsed by the API tests.

The configured business database remained unreachable during verification. No production configuration, report, payroll record, or business-database migration was changed.
