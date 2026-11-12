# Flash View and Social Feed

Backend implementation lives in this `hr-ky-admin` repository; the employee UI lives in the sibling `hrky-flutter` app. Employee posts publish immediately; administrators moderate afterward. No new Flutter dependencies are required.

## Completed implementation

- [x] Database migration, models, permission registration, and protected API routes.
- [x] Admin Flash View list, create/edit form, media preview, scheduling/duration, priority, role/branch/department audience targeting, publication switch, explicit enable/off control, push-alert action, optional link button, and “show again” revision control.
- [x] Flash popups after dashboard initialization, with sequential dialogs and server dismissal receipts.
- [x] Once-per-employee/revision and once-per-running-app-session display policies.
- [x] Company-scoped social feed with pagination, direct publishing, own-post editing/deletion, comments, reactions, and in-feed sharing with captions.
- [x] Six reactions (Like, Love, Haha, Wow, Sad, Angry), one reaction per employee/post, reaction breakdowns, and reaction detail users.
- [x] Server-enforced company, department, selected-employee, management, and HR audience visibility.
- [x] Facebook-style post commands: save/unsave, copy link, report, permissioned pin/unpin, reaction detail list, and moderation report review.
- [x] Post notification follow/unfollow and hide-post commands, with server-side per-user feed filtering.
- [x] Nested comments/replies, own-comment editing, comment reactions, and social notification history with read/read-all actions.
- [x] Feed search, saved-posts page, multiple image attachments, video/document uploads, and document links.
- [x] Polls with single/multiple choice options, closing dates, vote replacement, and live result counts.
- [x] Professional create-post composer with profile/audience context, media action tiles, emoji/location helpers, polls, visibility controls, local drafts, and a fixed publish action for small screens.
- [x] Admin post/comment moderation with application audit log entries.
- [x] Text, images, video uploads/direct links, HTTPS links, and normalized YouTube/Facebook video embeds.
- [x] English and Khmer mobile labels, permission-aware controls, loading/error/retry states, and duplicate-submit prevention.
- [x] Server-side permission, ownership, upload, provider, and company-boundary checks.
- [x] Media replacement/deletion cleanup and an orphan-upload cleanup command with dry-run default.
- [x] Automated API, migration, permission, and Flutter interaction tests.

## Rollout steps

The configured local backend database `hr_for_draf` has been activated in place; existing HR records were preserved. The commands below remain the repeatable deployment procedure for another environment.

1. Deploy **both** backend and Flutter changes. The backend also needs the preceding mobile role-permission change.
2. Back up the deployment database and run `php artisan migrate` in `hr-ky-admin`. This release adds the additive community migrations `2026_11_11_000005_expand_social_feed_features`, `2026_11_11_000006_add_social_polls`, `2026_11_11_000007_add_social_post_preferences`, and `2026_11_11_000008_add_social_post_location` without dropping existing HR tables.
3. Run `php artisan config:clear`; rebuild config/route caches if your deployment normally uses them. The new `config/mobile_permissions.php` entries must be loaded.
4. Ensure `php artisan storage:link` is configured and `/storage/community` is served over HTTPS. The web/PHP process needs write access to `storage/app/public/community`.
5. Configure the web server and PHP to accept 100 MB video uploads, with a request-body limit above that size (for multipart overhead), `upload_max_filesize` at least `100M`, and `post_max_size` above `100M`. Images are limited to 10 MB. Supported uploads: JPEG, PNG, GIF, WebP, MP4, MOV, WebM. Actual playback depends on the device's supported codecs; MP4 is the preferred video format.
6. Assign role permissions in **Roles → Permissions**:
   - Web → **Flash View**: `flash_view.manage`.
   - Web → **Social Feed**: `social_feed.moderate`.
   - API → **Mobile Flash View**: menu access and `flash_view.view` (push alerts are sent only to roles with both grants).
   - API → **Mobile Social Feed**: menu access, `social_feed.view`, create/edit/delete/comment/delete-comment/edit-comment/react/share/save/search/report/pin/notifications/follow/hide/poll/vote abilities as appropriate.
   - New permissions are not automatically assigned to employee roles. Existing recognized admin access remains in place.
7. Open **Flash View** in the admin sidebar, create a draft, preview it, select audience roles and a schedule, and publish it. Blank audience selection includes all users with Flash View permission. Times are explicitly labeled using the backend's configured timezone.
8. Install the updated app in staging; refresh the dashboard to obtain current role permissions. Verify the Social Feed tab immediately beside Home on phone/tablet navigation, the popup, and the fallback entry under More/drawer.
9. Verify real video playback on Android and iOS with public, embeddable videos. Confirm company/role audiences using separate accounts before production release.

## Content behavior

Flash View titles and bodies are plain text. Media fields accept HTTPS URLs or a YouTube/Facebook iframe snippet; the server extracts and normalizes the provider URL and discards the submitted HTML. Arbitrary iframe providers are not supported. Facebook requires a full public `/videos/…`, `/reel/…`, or `/watch/?v=…` video URL; shortened share URLs are rejected. Private videos or videos with embedding disabled can fail to play. The app also offers an external-link fallback.

The WebView uses the installed app identifier as its HTTPS base origin for YouTube player identification and does not autoplay videos. Reference: [YouTube embedded player requirements](https://developers.google.com/youtube/terms/required-minimum-functionality#embedded-player-api-client-identity), [Facebook embedded video documentation](https://developers.facebook.com/docs/plugins/embedded-video-player/).

Popups are fetched when the home dashboard refreshes, after birthday/update dialogs close. Forced-update flows suppress promotional popups. The API returns at most ten eligible popups, ordered by priority then newest first. Once-only dismissal syncs with the backend; a failed dismissal request may cause the popup to reappear after app restart. Session dismissal is isolated by account/login token. “Show again” increments the popup revision. Publishing a new popup does not send a push notification; it appears on a subsequent dashboard refresh.

The feed is visible inside the employee's company, not publicly on the internet. Sharing creates a new feed entry pointing to the original; removing the original makes its shared content unavailable. Shared-post edits change only the sharer's caption. Reactions use one exact value per employee/post and can be changed or removed. Department/employee audience IDs are validated against the authenticated company. Social notifications are stored in-app; device push delivery for feed interactions is not enabled. Polls are available in the feed. @mentions, private groups, real-time WebSockets, and public external share URLs remain outside this release.

Uploads are served as public media URLs, as with the app's existing public media storage. Feed/API access is company-controlled, but a person with a copied media URL can access that file. Use authenticated/signed media delivery in a separate change if confidential files must be protected after their URL is copied.

## Cleanup

Replaced/deleted media is removed when no active popup/post references it. Failed uploads can leave an orphan file if the database save fails. Preview orphan cleanup first:

```sh
php artisan community:prune-media
```

To remove unreferenced community files older than 24 hours:

```sh
php artisan community:prune-media --delete
```

The command protects references across primary/backup hostnames and retains references in soft-deleted posts. It can be added to the deployment's normal maintenance schedule. It has not been executed against live uploads.

## Verification commands

From `hr-ky-admin`, with an uncached test configuration and isolated SQLite fixtures:

```sh
APP_CONFIG_CACHE=/tmp/hrky-community-test-config.php vendor/bin/phpunit tests/Feature/CommunityFeatureTest.php tests/Feature/MobileFunctionPermissionTest.php tests/Feature/MobilePermissionMigrationTest.php tests/Feature/EmployeeFunctionPermissionTest.php tests/Feature/AdvanceSalaryApprovalPermissionTest.php
```

From `hrky-flutter`:

```sh
flutter test --no-pub
flutter analyze --no-pub
```

The repository already contains analyzer warnings and informational lints. Device playback and the staging/production rollout remain manual checks; automated WebView tests do not prove that a third-party video permits embedding.

Latest local verification: 231 Flutter tests passed (three live-network checks skipped); the selected backend community/permission/admin suite passed 28 tests with 757 assertions. Community analysis has two non-blocking Flutter deprecation infos for the framework's poll radio API; there are no analyzer errors. Full-repository analysis still reports existing warnings/informational lints. PHPUnit reports an existing configuration deprecation.

Android verification: `flutter build apk --debug --no-pub --target-platform android-arm64` succeeded. The debug APK is generated at `hrky-flutter/build/app/outputs/flutter-apk/app-debug.apk`. It has not been installed on a device during this verification.

Runtime readiness check: the configured backend reports the production environment with cached configuration, uses `hr_for_draf`, and contains the community post, media, poll, notification, save, report, reaction, and preference tables. Existing employee permissions remain unchanged until an administrator grants the new community permissions.
