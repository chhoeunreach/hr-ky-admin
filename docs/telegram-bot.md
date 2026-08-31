# Telegram Bot Setup

This project reads Telegram settings from server-side environment variables only.

## Environment Variables

Add these values to `.env`:

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
4. Copy the bot token into `TELEGRAM_BOT_TOKEN`.

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
- Test the saved `TELEGRAM_BOT_TOKEN`.
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

The command sends to `TELEGRAM_CHAT_ID`. If it fails, check `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, and `storage/logs/laravel.log`.

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
