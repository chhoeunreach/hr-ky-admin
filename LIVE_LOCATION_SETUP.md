# Real-Time User Location Setup

This project adds a latest-location table and live admin map.

## Routes

- `GET /admin/live-map` - admin live map
- `GET /admin/live-map/locations` - admin JSON feed for latest locations
- `GET /track-location` - browser GPS tracking page for authenticated users
- `POST /track-location/update` - browser/session location update
- `POST /api/location/update` - mobile/API location update with `auth:api`

## API Payload

`POST /api/location/update`

```json
{
  "user_id": 123,
  "latitude": 11.5564,
  "longitude": 104.9282,
  "accuracy": 12.5,
  "battery_level": 82,
  "device_name": "iPhone 15"
}
```

The authenticated API user can only update their own `user_id`.

## Broadcasting `.env`

Polling works even when broadcasting is not configured. For WebSocket marker movement, install/configure a Pusher-compatible server such as Pusher, Soketi, or Laravel Reverb, then set:

```env
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=sync

PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

For a hosted Pusher account, use the credentials from Pusher and keep `PUSHER_SCHEME=https`.

## Install And Run

```bash
composer install
php artisan migrate
php artisan serve
php artisan queue:work
npm install
npm run dev
```

This code declares `pusher/pusher-php-server` in `composer.json`. Run `composer update pusher/pusher-php-server --with-all-dependencies` if your current `composer.lock` does not include it yet.

## Browser Tracking

Open `/track-location` as an authenticated user. The page requests GPS permission and sends a location update at most every 5 seconds using `navigator.geolocation.watchPosition`.

If permission is denied, the page shows the browser error and no location is sent.
