<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Location</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/core/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f7fb;
        }

        .tracking-card {
            width: min(520px, calc(100vw - 32px));
            border-radius: 8px;
        }

        .tracking-status {
            min-height: 112px;
        }
    </style>
</head>
<body>
<div class="card tracking-card">
    <div class="card-header">
        <h5 class="mb-1">Location Tracking</h5>
        <small class="text-muted">Keep this page open while you want to share your location.</small>
    </div>
    <div class="card-body">
        <div class="tracking-status border rounded p-3 mb-3">
            <p class="fw-bold mb-1" id="trackingState">Waiting for GPS permission...</p>
            <div class="text-muted small" id="trackingDetail">Your browser will ask for location access.</div>
        </div>

        <div class="row g-3 small text-muted">
            <div class="col-6">
                <span class="d-block">Latitude</span>
                <strong class="text-dark" id="latitudeValue">-</strong>
            </div>
            <div class="col-6">
                <span class="d-block">Longitude</span>
                <strong class="text-dark" id="longitudeValue">-</strong>
            </div>
            <div class="col-6">
                <span class="d-block">Accuracy</span>
                <strong class="text-dark" id="accuracyValue">-</strong>
            </div>
            <div class="col-6">
                <span class="d-block">Last Sent</span>
                <strong class="text-dark" id="lastSentValue">-</strong>
            </div>
        </div>
    </div>
</div>

<script>
    const updateUrl = @json(route('track-location.update'));
    const userId = @json(auth()->id());
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const minimumSendInterval = 5000;
    const forceSendInterval = 30000;
    const minimumMovementMeters = 10;
    let lastSentAt = 0;
    let lastSentPosition = null;
    let watchId = null;
    let batteryLevel = null;

    const state = document.getElementById('trackingState');
    const detail = document.getElementById('trackingDetail');
    const latitudeValue = document.getElementById('latitudeValue');
    const longitudeValue = document.getElementById('longitudeValue');
    const accuracyValue = document.getElementById('accuracyValue');
    const lastSentValue = document.getElementById('lastSentValue');

    if (navigator.getBattery) {
        navigator.getBattery().then(function (battery) {
            batteryLevel = Math.round(battery.level * 100);
        }).catch(function () {
            batteryLevel = null;
        });
    }

    function updateText(position) {
        latitudeValue.textContent = position.coords.latitude.toFixed(7);
        longitudeValue.textContent = position.coords.longitude.toFixed(7);
        accuracyValue.textContent = `${Math.round(position.coords.accuracy)} m`;
    }

    function toRadians(value) {
        return value * (Math.PI / 180);
    }

    function distanceInMeters(from, to) {
        if (!from || !to) {
            return Number.POSITIVE_INFINITY;
        }

        const earthRadius = 6371000;
        const latitudeDelta = toRadians(to.latitude - from.latitude);
        const longitudeDelta = toRadians(to.longitude - from.longitude);
        const latitudeA = toRadians(from.latitude);
        const latitudeB = toRadians(to.latitude);

        const haversine = Math.sin(latitudeDelta / 2) * Math.sin(latitudeDelta / 2) +
            Math.cos(latitudeA) * Math.cos(latitudeB) *
            Math.sin(longitudeDelta / 2) * Math.sin(longitudeDelta / 2);

        return earthRadius * 2 * Math.atan2(Math.sqrt(haversine), Math.sqrt(1 - haversine));
    }

    function shouldSend(position) {
        const now = Date.now();
        const currentPosition = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
        };
        const movedDistance = distanceInMeters(lastSentPosition, currentPosition);

        if (!lastSentAt) {
            return true;
        }

        if (now - lastSentAt >= forceSendInterval) {
            return true;
        }

        if (now - lastSentAt < minimumSendInterval) {
            return false;
        }

        return movedDistance >= minimumMovementMeters;
    }

    async function sendLocation(position) {
        updateText(position);

        if (!shouldSend(position)) {
            state.textContent = 'Location sharing active';
            detail.textContent = 'Waiting for meaningful movement before sending the next update.';
            return;
        }

        state.textContent = 'Sending location...';

        try {
            const response = await fetch(updateUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    user_id: userId,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    battery_level: batteryLevel,
                    device_name: navigator.userAgent
                })
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Unable to update location.');
            }

            lastSentAt = Date.now();
            lastSentPosition = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            state.textContent = 'Location sharing active';
            detail.textContent = `Your latest location was sent successfully with ${Math.round(position.coords.accuracy)} m accuracy.`;
            lastSentValue.textContent = new Date().toLocaleTimeString();
        } catch (error) {
            state.textContent = 'Location update failed';
            detail.textContent = error.message;
        }
    }

    function handleError(error) {
        state.textContent = 'GPS permission is not available';
        detail.textContent = error.message || 'Please allow location permission in your browser settings.';
    }

    if (!navigator.geolocation) {
        handleError({message: 'Geolocation is not supported by this browser.'});
    } else {
        watchId = navigator.geolocation.watchPosition(sendLocation, handleError, {
            enableHighAccuracy: true,
            maximumAge: 5000,
            timeout: 10000
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState !== 'visible' || !navigator.geolocation) {
            return;
        }

        navigator.geolocation.getCurrentPosition(sendLocation, handleError, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        });
    });
</script>
</body>
</html>
