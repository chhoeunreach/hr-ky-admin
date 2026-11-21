# Security verification and test plan

## Verification run during implementation

- Latest Flutter full test suite after per-install identity handling: 537 passed, 3 opt-in live-network checks skipped, and no failures. Release-mode pinning tests require two valid hashes, reject one/zero-pin configurations, and reject unconfigured subdomains and authenticated requests to unlisted hosts. The image queue rejects missing endpoints, HTTP, unpinned hosts, and missing pin sets before network access. Manager and security-gate tests verify that a missing server-bound key enters administrator-managed recovery. The final debug APK's manifest confirms app backup remains enabled and excludes the per-install identity file from backup/device transfer. The server URL resolver suite passes with the selected `hr.rvstechsolution.com` production API hostname.
- Flutter analysis of security manager, security gate, startup, request wrappers and Dio/Connect integration reported no errors. `MoreScreenProvider` has informational style hints.
- Debug APK built with `KY_API_BASE_URL=https://hr.rvstechsolution.com/`; it is a verification artifact and not for distribution.
- Latest App Security-specific Laravel tests: 78 passed, 396 assertions; the full Laravel suite: 778 passed, zero failures and 7,736 assertions. The mobile login/logout fixture installs Passport schema and required user columns and exercises the real bearer-token GET logout flow; chat media assertions permit absolute hosts while checking canonical paths; the banner playback label is covered. Route-level distribution tests verify active APK metadata, checksum and controlled download, and confirm revoked releases return 404; the login-page Android link is also covered. App Security coverage includes synthetic trusted Android attestation, nearest-root extension selection with a later duplicate, valid RKP extension ordering, revocation tests, safe status refresh, configurable risk accumulation/explanations/expiry/no-auto-block, deduplicated serious-event and rejected-registration alerts, sequential and concurrent request replay protection, P-256 signing, session rotation/replay/revocation, signed-request tampering, account-scoped device migration, privacy-minimized intelligence, role-permission isolation, review/notification/step-up flows, per-account device limits, retired-key registration denial, legacy factory certificate-expiry handling with revocation enforcement and RKP date checks, JSON mutation APIs for devices and releases, and migration rollback tests.
- Read-only device checks found a Samsung SM-S908E on Android 16 / API 36 with the existing app version 1.3.0 (17); the installed APK passed `apksigner` and matched the owner-approved signer fingerprint. The hardware service list reported `IKeymasterDevice/strongbox`, but the updated app was not installed, so Keystore/attestation behavior remains untested. From that handset, the public config URL returned HTTP 200 with an HTML page titled `404` rather than JSON; the same GET route is present in local Laravel. The development Mac could not resolve the hostname. Production route deployment is unverified.
- A clean Pixel 3a API 36 emulator accepted installation of the current debug APK and launched `MainActivity`; the app process was present and no Android runtime fatal exception appeared in captured logs. This was a startup smoke check only, without account login or server security calls, and provides no hardware-attestation evidence. The physical Samsung device remained untouched.
- A later read-only check found the currently attached Samsung SM-N976N running Android 12 / API 31 with app version 1.3.1 (18) installed and verified-boot state `green`. Keystore2 and Samsung attestation services are advertised; this does not establish StrongBox availability or validate the app's key/attestation flow. No APK was installed or replaced. A current Mac request cannot resolve `hr.rvstechsolution.com`.
- The release artifact previously built is not current: it predates the selected API host and production pinset. Rebuild after verifying the host routes to Laravel and the owner supplies both SPKI pins. The owner has confirmed the expected signer SHA-256; verify the exact new APK before registering it. Without pins, release API connectivity fails closed.
- App Security migrations ran against a fresh isolated SQLite database; production MySQL was not modified.

These results verify compilation and existing regression behavior; they do not prove hardware attestation, release publishing, every signed API endpoint or production rollout.

## Required security tests before production enforcement

### Laravel

- Challenge expiry, single-use, wrong owner and concurrent challenge reuse.
- Invalid/changed request path, query, body, signature, timestamp, device/session, signer and package.
- Concurrent replay of one nonce: covered by `AppSecurityMiddlewareReplayTest::test_concurrent_requests_with_the_same_nonce_allow_at_most_one_request`; two worker processes race against the same SQLite file and exactly one reaches the protected handler.
- Malformed attestation ASN.1: signed-chain tests reject trailing BER values in both the attestation extension and embedded application ID, and reject unexpected KeyDescription fields.
- Attestation certificate dates: the documented factory root permits expired leaves unless revoked; an expired chain with a different root is rejected.
- Refresh-token rotation, refresh reuse, revoked/expired session, revoked device and user logout transaction.
- Unknown, archived, revoked and mandatory-update release policies; optional update behavior must be corrected/tested first.
- Customer/staff/admin role mapping and existing function-permission denial.
- APK malformed file, wrong package/signer, duplicate version, unavailable tools and private download authorization.
- Admin permission separation, state transition conflicts, reason validation and audit row completeness.
- Attestation chain signatures, trusted root rotation, challenge/key binding, security level, revocation data, and configured OS/patch floor enforcement; then test supported and unsupported device behavior on physical OEM devices.

### Flutter and Android devices

- Supported Android API levels; key persistence/invalidation and strongbox/TEE behavior.
- First install registration, pending approval, customer auto-approval, reinstall/key loss, account switch and network failures.
- Session revocation: receive 401, invalidate local session, open a new session only if the device remains authorized, and do not replay the failed business request.
- Multipart and Dio exact-byte parity; uploads must not be buffered entirely in memory and temporary data must be removed after success/failure.
- Official release, wrong signer, unknown/revoked release, mandatory update and offline service behavior.
- Test with real signed APKs and controlled accounts; no hardware/production-device tests have been completed.



Registration-velocity behavior is covered by `RegistrationVelocityDetectorTest`: it verifies threshold behavior, HMAC-keyed cache identity (the raw IP is not used in the cache key), and missing-IP handling. The detector is review-only and does not impose a device block. End-to-end service testing should additionally exercise customer auto-approval hold and shared Redis behavior in staging.

- Administrator audit CSV export has focused coverage for spreadsheet formula escaping, JSON state serialization, bounded row output, and role access; controller route behavior is covered by the protected permission middleware and authorization service.

- Cross-account key reuse tests cover exact fingerprint matching across user IDs, distinct-account counting, and the permitted case where accounts share an installation UUID but have separate account-bound keys.

- False-positive review tests verify required audit records, one-time dispositions, non-critical score recalculation, critical-event retention, role isolation, and rollback protection for review metadata.

- Queued mobile notification tests verify account targeting, idempotent inbox persistence across job retries, and data-preserving dedupe-key migration rollback.

- Step-up tests verify correct-password acceptance, wrong-password rejection, per-actor/IP throttling, the explicit emergency bypass, and integration with settings updates and device revocation.
