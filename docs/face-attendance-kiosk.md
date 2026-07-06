# Face attendance kiosk

The kiosk uses device authentication rather than an employee account.

## Provisioning

From **Employee Management → Face attendance**, create a branch kiosk. The
dashboard displays a one-time QR containing:

- payload type and version;
- the current server origin;
- a random revocable device token.

The administrator PIN is never included in the QR. It is entered separately on
the kiosk and verified against its bcrypt hash. The QR token and PIN are
exchanged once for a different runtime token; the QR token becomes invalid
immediately.

## API security

Kiosk endpoints are under `/api/kiosk/v1` and require:

```http
Authorization: Bearer kiosk_<token>
```

Enrollment endpoints additionally require:

```http
X-Kiosk-Admin-Pin: <6-12 digit PIN>
```

Tokens are stored server-side only as SHA-256 hashes. Face embeddings are
company/branch scoped and encrypted using Laravel's `encrypted:array` cast.
Keep `APP_KEY` stable.

## Operations

- `GET /bootstrap` synchronizes active enrolled profiles.
- `POST /provision` exchanges the QR token and PIN for a runtime token.
- `GET /employees` loads the branch enrollment directory.
- `POST /admin/verify-pin` completes kiosk administrator login.
- `POST /employees/{id}/face-profile` enrolls or replaces a face.
- `POST /attendance` submits an idempotent recognized event.

The Flutter client blocks attendance scanning until an enrolled profile is
available locally.
