# Telegram Bot Setup

This project reads Telegram bot credentials from the database settings saved in the admin panel.

## Environment Variables

The bot token is managed here:

```text
Settings > Telegram Bot
```

You can keep these values in `.env` as legacy defaults, but the saved database token is used first:

```dotenv
TELEGRAM_BOT_TOKEN=123456789:ABC_your_bot_token_here
TELEGRAM_CHAT_ID=123456789
TELEGRAM_WEBHOOK_SECRET=your_random_secret_here
```

`APP_URL` is also required for webhooks. This project already has `APP_URL`; keep it set to the public HTTPS URL of the app.

## Create A Bot

1. Open Telegram and message `@BotFather`.
2. Send `/newbot`.
3. Follow BotFather instructions.
4. Copy the bot token into Settings > Telegram Bot.

## Get The Chat ID

1. Send `/chatid` to the bot.
2. Copy the returned chat ID into `TELEGRAM_CHAT_ID` or into Settings > Telegram Bot > Employee Alerts.

If the webhook is not set yet, you can also send any message to the bot and open this URL in a browser, replacing `YOUR_BOT_TOKEN`:

```text
https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
```

Find `chat.id` in the JSON response.

For groups, add the bot to the group, send a group message, and use the group `chat.id`. Group chat IDs often start with `-100`.

## Employee Private Bot Alerts

Group routing sends app events to Telegram groups. Employee private alerts are controlled separately.

```text
Settings > Telegram Groups
Settings > Telegram Bot
```

Use Telegram Bot to:

- Save the bot token.
- Save the bot username.
- Save the webhook secret.
- Set connect link validity in minutes.
- Copy the webhook URL.
- Test the saved bot token.
- Save each employee's personal Telegram chat ID.
- Send a private Telegram message to one employee.
- Broadcast an alert to all linked employees, optionally filtered by branch or department.

Employees can link themselves by messaging the bot:

```text
/link EMPLOYEE_CODE
```

They can remove the link with:

```text
/unlink
```

## Test Sending

Run:

```bash
php artisan telegram:test
```

To test an HTML-formatted message:

```bash
php artisan telegram:test --html
```

The command sends to the configured default chat. If it fails, check the saved bot token, chat routing, and the current daily log under storage/logs.

To test a specific chat ID:

```bash
php artisan telegram:test --chat-id=-1001234567890
```

To test the same event routing used by the app:

```bash
php artisan telegram:test --action=attendance_checkin --branch="Branch Name" --department="Department Name"
```

## Webhook

Telegram can post updates to:

```text
POST /telegram/webhook
```

Set the webhook with:

```bash
php artisan telegram:webhook set
```

When `TELEGRAM_WEBHOOK_SECRET` is configured, the webhook verifies Telegram's `X-Telegram-Bot-Api-Secret-Token` header.

To inspect or delete the webhook:

```bash
php artisan telegram:webhook info
php artisan telegram:webhook delete
```

Supported commands:

```text
/start
/help
/status
/chatid
```

## Maintenance History: 2026-09-25

### Incident Summary

The production HR project at **/var/www/hr-ky-admin1** recorded:

- Telegram API 502 Bad Gateway and 429 Too Many Requests responses.
- Photo uploads timing out after 20 seconds.
- Sell Out routing failures for material, purchase, and iCloud customer events.
- Attendance requests waiting for synchronous Telegram calls.
- Bot credentials appearing inside exception URLs in laravel.log.
- A single Laravel log growing to approximately 358 MB.

### Root Causes

1. Attendance sent Telegram messages, photos, and locations inside the HTTP request.
2. Requests immediately retried after 200 ms, including rate-limited requests.
3. Exception messages included the full Telegram API URL and bot token.
4. Four routing rows used **ច្បាអំពៅ** while events supplied **ច្បារអំពៅ**.
5. No destination is configured for **sell_out_icloud_cus**.
6. Production used the non-rotating single log at debug level.

### Code Changes

Attendance now dispatches the existing queue job after commit:

~~~php
SendAttendanceTelegramNotification::dispatch(
    $type,
    (int) $user->id,
    (int) $attendance->id,
)->afterCommit();
~~~

The job retries transient failures without holding the attendance request open:

~~~php
public int $tries = 4;
public int $timeout = 120;

public function backoff(): array
{
    return [10, 30, 60];
}
~~~

The worker calls **AttendanceTelegramNotificationService::sendNow()**, preserving selfie, message, location, branch, and department behavior. Failed calls throw so the queue can retry them.

Immediate HTTP retries were removed from **TelegramService**. Token-bearing exception text is sanitized before logging:

~~~php
private function redactSensitiveData(string $value): string
{
    return preg_replace(
        '/bot\d+:[A-Za-z0-9_-]+/',
        'bot[REDACTED]',
        $value
    ) ?? 'Telegram request failed.';
}
~~~

Missing routing is now a notice rather than a system error. A destination must still be configured before delivery can occur.

The logging stack now uses daily rotation:

~~~php
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily'],
    'ignore_exceptions' => false,
],
~~~

Production settings:

~~~dotenv
LOG_CHANNEL=stack
LOG_LEVEL=warning
QUEUE_CONNECTION=redis
~~~

### Files Changed

- app/Jobs/SendAttendanceTelegramNotification.php
- app/Services/Attendance/AttendanceTelegramNotificationService.php
- app/Services/Attendance/AttendanceTelegramNotifier.php
- app/Services/TelegramService.php
- config/logging.php

### Routing Data Correction

Four routing rows were normalized from **ច្បាអំពៅ** to **ច្បារអំពៅ**. Equivalent SQL:

~~~sql
UPDATE telegram_groups
SET branch_name = REPLACE(branch_name, 'ច្បាអំពៅ', 'ច្បារអំពៅ')
WHERE branch_name LIKE '%ច្បាអំពៅ%';
~~~

After correction, **sell_out_material** and **sell_out_purchase** each resolve a Chbar Ampov destination. **sell_out_icloud_cus** still requires assignment through **Settings > Telegram Groups**.

### Deployment Commands

Run from **/var/www/hr-ky-admin1** after deploying tested files:

~~~bash
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
supervisorctl restart hr-worker:*
~~~

The old token-bearing log was cleared after daily logging became active:

~~~bash
truncate -s 0 storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
~~~

Pre-deployment backup:

~~~text
/root/backups/hr-telegram-20260925-0645
~~~

### Verification Results

- All five changed production PHP files passed php -l.
- Queue dispatch and token redaction passed focused in-memory checks.
- Local and production SHA-256 checksums matched.
- The HR worker restarted and remained RUNNING.
- Redis queue length was 0; Laravel reported no failed jobs.
- The HR site returned its expected HTTP 302 login redirect in about 0.08 seconds.
- Daily warning-level logging was active.
- No Telegram token pattern was found in the new logs.

### Required Manual Actions

1. Revoke the exposed token through **@BotFather** and generate a replacement.
2. Save it under **Settings > Telegram Bot**; the database value takes priority over .env.
3. Update TELEGRAM_BOT_TOKEN in production .env as the fallback.
4. Assign sell_out_icloud_cus to the correct Telegram group. Do not guess because customer information may be sent to it.
5. Test attendance check-in/out and each configured Sell Out event.

After changing the token or environment settings:

~~~bash
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
supervisorctl restart hr-worker:*
~~~

### Operational Checks

~~~bash
supervisorctl status hr-worker:*
redis-cli llen queues:default
php artisan queue:failed
tail -f storage/logs/laravel-$(date +%F).log
tail -f /var/log/nginx/error.log
~~~

Never place a production bot token in documentation, source control, shell commands, tickets, or chat messages.
