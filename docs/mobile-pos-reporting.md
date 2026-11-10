# Flutter POS Dashboard API

The Flutter POS Dashboard uses the existing authenticated reports endpoint:

```text
GET /api/sell-out-reports?paginated=1&date_from=2026-11-09&date_to=2026-11-09&page=1&per_page=20
Authorization: Bearer <employee access token>
Accept: application/json
```

Existing callers without `paginated=1` keep the legacy list contract. The HR `/api/dashboard` endpoint is unchanged.

## Contract and rules

The response contains `data`, pagination `meta`, `summary`, `categories`, `chart`, `options`, `permissions`, `capabilities`, `currency_symbol`, and `business_name`.

Filters: `date_from`, `date_to`, `branch_name`, `seller_name`, `search` (maximum 160 characters), `type`, `report_status`, `commission_mode`, `page`, `per_page`, and chart `bucket` (`day`, `week`, `month`). Type is `all`, `sell`, `material`, `iron`, `repair`, or `buy_in`. Page sizes are 20, 25, 100, 250, and 500. Defaults are today, page 1, and 20 rows. Dates use the application's Asia/Phnom_Penh calendar.

`export=csv` streams all matching rows, independently of page size. Error responses remain JSON with the appropriate 401/403/422 status even when a client requests CSV. CSV cells are escaped against spreadsheet formulas.

Authentication and permission middleware remain enabled. All reporting queries preserve the existing own-report scope; branch/employee options come from that same scope. Transactions cannot be read or edited by a different owner. Editing is restricted to reports created today.

Revenue includes Sell, Material, Iron, and Repair header amounts exactly once. Buy In remains separate spending. Commission defaults can now be managed globally in Payroll → Commission Settings; see `docs/commission-settings.md`. iCloud Cus uses its existing create/detail/edit and navigation screens; it is excluded from financial aggregation because no additional financial rules were authorized. Gross profit and unsupported operational/payment statuses remain unavailable.

Permitted period commission is calculated using the current admin policy across the complete filtered scope before status filtering or pagination. Existing service rates and work-category thresholds are shared through `ReportingRules`. Report previews do not update saved commissions.

## Deployment

Deploy the reporting controller, service, rules, model, exception-handler change, and reporting-index migration together. The existing report, line, user, office-time, and currency migrations must already be applied.

Once the configured business database is reachable, apply only this feature's index migration:

```sh
php artisan migrate --path=database/migrations/2026_11_10_000001_add_mobile_reporting_index.php
php artisan route:list --path=sell-out-reports
```

Then open Services → Dashboard in the updated Flutter app using an employee account. Check today's totals, a custom date range, search/type/branch filters, paging, CSV export, detail, creation, and same-day editing. Existing approved module layouts are retained.

## Verification

```sh
php vendor/bin/phpunit tests/Feature/MobilePosReportingTest.php
```

The default test run uses isolated in-memory SQLite, array caches, Passport test identities, actual permission middleware, and fake outbound jobs/HTTP. It never uses the configured business database. Only rate limiting is bypassed for repeated test requests.

For MySQL dialect verification, the same suite supports `POS_REPORT_MYSQL_SOCKET` pointing to a temporary, socket-only MySQL initialized under `/tmp/kneayerng-pos-mysql.<id>/mysql.sock`. It uses only the disposable `pos_reporting_test` database and credentials defined in the test; the suite recreates that database's test tables. It rejects other socket paths.

Verified on 10 November 2026: 15 backend tests / 171 assertions passed on SQLite and MySQL, including create/detail/edit for all six services, scoping, date boundaries, financial totals, conditions, CSV errors, and pagination. PHPUnit reports one existing configuration deprecation.

The configured application MySQL connection refused connections during the local audit. No business-database migration or data change was performed. Live-account verification requires that connection to be available.

## Admin-managed commission

See [Commission administration](commission-settings.md) for per-service rates/conditions, global controls, role visibility/calculation permissions, audit history, and the additional migration. Saved invoice resources now return persisted commissions, while authorized reporting previews use current rules. Hidden values are redacted by the API rather than only hidden in Flutter.
