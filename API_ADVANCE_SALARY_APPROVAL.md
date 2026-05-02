# Advance Salary Approval (Mobile API)

## Feature flag

Dashboard response (`GET /api/dashboard`) includes an extra feature entry:

- `key`: `advance-salary-approve`
- `status`: `"1"` or `"0"` (string)

Default for normal employees is `"0"`. Assign the permission key `advance-salary-approve` to a role/user to get `"1"`.

## Endpoints (requires `auth:api`)

### Approve

`POST /api/employee/advance-salaries/{id}/approve`

Body:

```json
{ "released_amount": 123.45, "remark": "optional" }
```

Rules:

- Requires permission `advance-salary-approve` (else 403)
- Only works when current status is `pending` (else 409)

### Reject

`POST /api/employee/advance-salaries/{id}/reject`

Body:

```json
{ "remark": "optional" }
```

Rules:

- Requires permission `advance-salary-approve` (else 403)
- Only works when current status is `pending` (else 409)

