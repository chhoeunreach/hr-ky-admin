# Community activation status — 2026-11-11

The requested activation targets the configured MySQL database `hr_for_draf` in `hr-ky-admin`. Runtime activation is complete; the pending community migrations were applied in place without resetting the existing HR records.

## Database state

`hr_for_draf` contains the existing HR data plus the complete community schema. The Flash View, social post/comment/reaction, media, save/report, notification, poll, and per-user preference tables are present, including Flash View branch/department targeting and alert history columns. Social posts also have the additive `location_name` field used by the professional create-post composer. The current database has 587 users (256 active users); existing records were preserved.

An earlier PHPUnit run used the application's cached production configuration instead of the intended SQLite test configuration. The current database contains existing HR records, and this activation run did not reset or replace them.

## Preservation and safeguards

A private snapshot of the former test-only tables, schemas, records, and configuration cache is stored under:

`storage/app/private/community-activation-20261111-075737/`

This snapshot preserves the test state; it is not a backup of data from before the reset. The directory is excluded from Git and has private filesystem permissions.

`phpunit.xml` and `tests/bootstrap.php` force an isolated SQLite test connection before Laravel boots, and `tests/TestCase.php` refuses cached, non-testing, or non-SQLite defaults. The selected community/permission/admin backend suite passes with those safeguards (28 tests, 757 assertions). The full legacy suite remains separate because its older fixtures do not grant the newly enforced mobile function permissions; those failures do not modify `hr_for_draf`.

## Activation result

Migrations `2026_11_11_000001` through `2026_11_11_000008` completed successfully and configuration cache was rebuilt for `hr_for_draf` in the production environment. Admin authentication bypasses the web permission gate; employee roles receive no new community permissions automatically, so grant them from Roles → Permissions as required. The employee app places Social Feed beside Home when its menu permission is enabled. Push alerts require both Flash View menu/view grants and a registered FCM token.
