<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>តាមដានការដឹកជញ្ជូន (Live Delivery Tracking) | {{ $delivery->tracking_code }}</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Google Fonts (Kantumruy Pro & Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Kantumruy Pro', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            position: relative;
        }
        #map {
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Top Header Bar */
        .top-bar {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
        }
        .brand-badge {
            pointer-events: auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 999px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .brand-logo-dot {
            width: 10px;
            height: 10px;
            background-color: #059669;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
            animation: pulse-dot 1.8s infinite;
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(5, 150, 105, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(5, 150, 105, 0); }
        }

        .status-pill {
            pointer-events: auto;
            background: #0284c7;
            color: white;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 999px;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        .status-pill.delivered {
            background: #059669;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }
        .status-pill.cancelled {
            background: #dc2626;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        /* Re-center floating button */
        .recenter-btn {
            position: absolute;
            right: 16px;
            bottom: 270px;
            z-index: 999;
            width: 44px;
            height: 44px;
            background: white;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: #1e293b;
            transition: transform 0.2s;
        }
        .recenter-btn:active {
            transform: scale(0.92);
        }

        /* Bottom Floating Card */
        .bottom-card {
            position: absolute;
            bottom: 16px;
            left: 16px;
            right: 16px;
            max-width: 520px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            border: 1px solid rgba(226, 232, 240, 0.9);
            transition: all 0.3s ease;
        }

        /* Stepper progress */
        .stepper-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin-bottom: 16px;
            padding: 0 8px;
        }
        .stepper-line-bg {
            position: absolute;
            top: 14px;
            left: 24px;
            right: 24px;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
        }
        .stepper-line-fill {
            position: absolute;
            top: 14px;
            left: 24px;
            height: 3px;
            background: #059669;
            z-index: 2;
            transition: width 0.4s ease;
        }
        .step-node {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: white;
            border: 3px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            transition: all 0.3s ease;
        }
        .step-node.active .step-circle {
            border-color: #059669;
            background: #059669;
            color: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.2);
        }
        .step-node.completed .step-circle {
            border-color: #059669;
            background: #059669;
            color: white;
        }
        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }
        .step-node.active .step-label {
            color: #059669;
            font-weight: 700;
        }

        /* Driver card row */
        .driver-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 14px;
        }
        .driver-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .driver-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e2e8f0;
            object-fit: cover;
            border: 2px solid #059669;
        }
        .driver-avatar-fallback {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #ecfdf5;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            border: 2px solid #a7f3d0;
        }
        .driver-name {
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
        }
        .driver-sub {
            font-size: 12px;
            color: #64748b;
        }
        .action-call-btn {
            background: #059669;
            color: white;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);
            transition: all 0.2s;
        }
        .action-call-btn:active {
            transform: scale(0.95);
        }

        /* Order Info details */
        .order-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 12px;
        }
        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .meta-label {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 500;
        }
        .meta-value {
            color: #1e293b;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .address-box {
            grid-column: span 2;
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px 10px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            border: 1px solid #f1f5f9;
        }
        .address-text {
            font-size: 12px;
            color: #334155;
            line-height: 1.4;
        }

        /* Custom Leaflet Marker Icons */
        .driver-marker-icon {
            background: #059669;
            border: 3px solid white;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        .dest-marker-icon {
            background: #ef4444;
            border: 3px solid white;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        .origin-marker-icon {
            background: #3b82f6;
            border: 3px solid white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        /* Delivered Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            from { transform: scale(0.85); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-icon {
            width: 64px;
            height: 64px;
            background: #ecfdf5;
            color: #059669;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 12px;
        }
        .proof-img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            border-radius: 12px;
            margin: 12px 0;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <!-- Leaflet Map Container -->
    <div id="map"></div>

    <!-- Top Floating Header -->
    <div class="top-bar">
        <div class="brand-badge">
            <span class="brand-logo-dot"></span>
            <span>KNEAYERNG DELIVERY</span>
        </div>
        <div class="status-pill {{ $delivery->status === 'delivered' ? 'delivered' : ($delivery->status === 'cancelled' ? 'cancelled' : '') }}" id="statusPill">
            <span id="statusIcon">{{ $delivery->status === 'delivered' ? '✅' : '🛵' }}</span>
            <span id="statusText">{{ $delivery->status_label }}</span>
        </div>
    </div>

    <!-- Recenter Map Button -->
    <button class="recenter-btn" onclick="fitDeliveryBounds()" title="បង្ហាញទាំងស្រុង">
        🎯
    </button>

    <!-- Bottom Tracking Info Card -->
    <div class="bottom-card">
        <!-- 4-Step Progress Indicator -->
        <div class="stepper-container">
            <div class="stepper-line-bg"></div>
            <div class="stepper-line-fill" id="stepperLineFill" style="width: 33%;"></div>
            
            <div class="step-node completed" id="step1">
                <div class="step-circle">1</div>
                <div class="step-label">រៀបចំ</div>
            </div>
            <div class="step-node {{ in_array($delivery->status, ['in_transit', 'arrived', 'delivered']) ? 'completed' : ($delivery->status === 'pending' ? 'active' : '') }}" id="step2">
                <div class="step-circle">2</div>
                <div class="step-label">ចេញដំណើរ</div>
            </div>
            <div class="step-node {{ in_array($delivery->status, ['arrived', 'delivered']) ? 'completed' : ($delivery->status === 'in_transit' ? 'active' : '') }}" id="step3">
                <div class="step-circle">3</div>
                <div class="step-label">មកដល់</div>
            </div>
            <div class="step-node {{ $delivery->status === 'delivered' ? 'active completed' : '' }}" id="step4">
                <div class="step-circle">4</div>
                <div class="step-label">បានប្រគល់</div>
            </div>
        </div>

        <!-- Driver Info Row -->
        <div class="driver-row">
            <div class="driver-info">
                @if($delivery->driver_selfie_url)
                    <img src="{{ $delivery->driver_selfie_url }}" alt="Driver Selfie" class="driver-avatar" style="border: 2px solid #059669;">
                @elseif($delivery->user && $delivery->user->avatar_url)
                    <img src="{{ $delivery->user->avatar_url }}" alt="Driver" class="driver-avatar">
                @else
                    <div class="driver-avatar-fallback">🛵</div>
                @endif
                <div>
                    <div class="driver-name" id="driverName">{{ $delivery->user?->name ?? 'អ្នកដឹកជញ្ជូន' }}</div>
                    <div class="driver-sub" id="driverSpeedDuration">
                        {{ $delivery->delivery_type === 'branch' ? '🏢 ដឹកអីវ៉ាន់ផ្ទេរតាមសាខា' : ($delivery->is_active ? 'កំពុងធ្វើដំណើរជិតដល់ហើយ' : 'ការដឹកជញ្ជូនត្រូវបានបញ្ចប់') }}
                    </div>
                </div>
            </div>
            @if($delivery->user && $delivery->user->phone)
                <a href="tel:{{ $delivery->user->phone }}" class="action-call-btn">
                    <span>📞</span>
                    <span>ទូរស័ព្ទ</span>
                </a>
            @endif
        </div>

        @if($delivery->product_photo_urls && count($delivery->product_photo_urls) > 0)
        <!-- Product / Parcel Photos Thumbnails -->
        <div style="margin-bottom: 12px;">
            <div style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px;">📦 រូបថតកញ្ចប់ទំនិញ (Package Photos):</div>
            <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px;">
                @foreach($delivery->product_photo_urls as $photoUrl)
                    <a href="{{ $photoUrl }}" target="_blank" style="display: block;">
                        <img src="{{ $photoUrl }}" alt="Product" style="width: 54px; height: 54px; border-radius: 8px; object-fit: cover; border: 1.5px solid #cbd5e1;">
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Order & Recipient Metadata -->
        <div class="order-meta-grid">
            <div class="meta-item">
                <span class="meta-label">លេខវិក្កយបត្រ (Invoice)</span>
                <span class="meta-value">{{ $delivery->invoice_number ?: $delivery->tracking_code }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">អ្នកទទួល (Recipient)</span>
                <span class="meta-value">{{ $delivery->customer_name }}</span>
            </div>
            <div class="address-box">
                <span>📍</span>
                <span class="address-text">{{ $delivery->destination_address ?: 'ទីតាំងអតិថិជន' }}</span>
            </div>
        </div>
    </div>

    <!-- Completed Celebration Modal -->
    <div class="modal-overlay" id="deliveredModal">
        <div class="modal-card">
            <div class="modal-icon">🎉</div>
            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 6px; color: #0f172a;">ទំនិញត្រូវបានប្រគល់រួចរាល់!</h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">សូមអរគុណសម្រាប់ការប្រើប្រាស់សេវាកម្មរបស់យើងខ្ញុំ។</p>
            <div id="proofContainer"></div>
            <button onclick="document.getElementById('deliveredModal').style.display='none'" style="width: 100%; padding: 12px; background: #059669; color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 14px;">
                យល់ព្រម
            </button>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Delivery data from server
        const token = "{{ $delivery->tracking_token }}";
        const initialStatus = "{{ $delivery->status }}";
        
        // Coordinates
        let driverLat = {{ $delivery->current_latitude ?? ($delivery->origin_latitude ?? 11.5564) }};
        let driverLng = {{ $delivery->current_longitude ?? ($delivery->origin_longitude ?? 104.9282) }};
        const destLat = {{ $delivery->destination_latitude ?? 'null' }};
        const destLng = {{ $delivery->destination_longitude ?? 'null' }};
        const originLat = {{ $delivery->origin_latitude ?? 'null' }};
        const originLng = {{ $delivery->origin_longitude ?? 'null' }};

        // Initialize Map
        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([driverLat, driverLng], 15);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Custom HTML Marker Icons
        const driverIcon = L.divIcon({
            className: 'driver-marker-wrapper',
            html: '<div class="driver-marker-icon" id="driverPinIcon">🛵</div>',
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        const destIcon = L.divIcon({
            className: 'dest-marker-wrapper',
            html: '<div class="dest-marker-icon">📍</div>',
            iconSize: [38, 38],
            iconAnchor: [19, 19]
        });

        const originIcon = L.divIcon({
            className: 'origin-marker-wrapper',
            html: '<div class="origin-marker-icon">🏪</div>',
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        // Add Markers
        let driverMarker = L.marker([driverLat, driverLng], { icon: driverIcon }).addTo(map);
        let destMarker = null;
        let originMarker = null;
        let routeLine = null;

        if (destLat && destLng) {
            destMarker = L.marker([destLat, destLng], { icon: destIcon }).addTo(map);
        }

        if (originLat && originLng) {
            originMarker = L.marker([originLat, originLng], { icon: originIcon }).addTo(map);
        }

        // Draw path line between driver and destination
        function updateRouteLine() {
            if (destLat && destLng) {
                const points = [
                    [driverLat, driverLng],
                    [destLat, destLng]
                ];
                if (routeLine) {
                    routeLine.setLatLngs(points);
                } else {
                    routeLine = L.polyline(points, {
                        color: '#059669',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '8, 8',
                    }).addTo(map);
                }
            }
        }
        updateRouteLine();

        // Fit map bounds to show both Driver and Destination
        function fitDeliveryBounds() {
            const markers = [];
            if (driverMarker) markers.push(driverMarker.getLatLng());
            if (destMarker) markers.push(destMarker.getLatLng());
            if (originMarker) markers.push(originMarker.getLatLng());

            if (markers.length > 1) {
                const bounds = L.latLngBounds(markers);
                map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });
            } else {
                map.setView([driverLat, driverLng], 16);
            }
        }
        fitDeliveryBounds();

        // Update Stepper Line & Active Nodes
        function updateStepper(status) {
            const lineFill = document.getElementById('stepperLineFill');
            const step2 = document.getElementById('step2');
            const step3 = document.getElementById('step3');
            const step4 = document.getElementById('step4');

            if (status === 'pending') {
                lineFill.style.width = '10%';
                step2.className = 'step-node active';
                step3.className = 'step-node';
                step4.className = 'step-node';
            } else if (status === 'in_transit') {
                lineFill.style.width = '45%';
                step2.className = 'step-node completed';
                step3.className = 'step-node active';
                step4.className = 'step-node';
            } else if (status === 'arrived') {
                lineFill.style.width = '75%';
                step2.className = 'step-node completed';
                step3.className = 'step-node completed';
                step4.className = 'step-node active';
            } else if (status === 'delivered') {
                lineFill.style.width = '100%';
                step2.className = 'step-node completed';
                step3.className = 'step-node completed';
                step4.className = 'step-node active completed';
            }
        }

        // Live Polling every 5 seconds
        let pollingInterval = null;
        let isDeliveredShown = initialStatus === 'delivered';

        function pollLiveTracking() {
            fetch(`/api/v1/delivery/public-track/${token}`)
                .then(res => res.json())
                .then(res => {
                    if (!res.status || !res.data) return;
                    const d = res.data;

                    // Update coordinates if changed
                    if (d.current_latitude && d.current_longitude) {
                        driverLat = parseFloat(d.current_latitude);
                        driverLng = parseFloat(d.current_longitude);
                        driverMarker.setLatLng([driverLat, driverLng]);
                        updateRouteLine();
                    }

                    // Rotate driver icon if heading available
                    if (d.heading !== null && d.heading !== undefined) {
                        const pinEl = document.getElementById('driverPinIcon');
                        if (pinEl) pinEl.style.transform = `rotate(${d.heading}deg)`;
                    }

                    // Update status label
                    const statusText = document.getElementById('statusText');
                    const statusPill = document.getElementById('statusPill');
                    if (statusText) statusText.innerText = d.status_label;

                    updateStepper(d.delivery_status);

                    if (d.delivery_status === 'delivered') {
                        statusPill.className = 'status-pill delivered';
                        document.getElementById('statusIcon').innerText = '✅';
                        document.getElementById('driverSpeedDuration').innerText = 'បានប្រគល់ជូនត្រឹមត្រូវ';

                        if (!isDeliveredShown) {
                            isDeliveredShown = true;
                            if (d.proof_photo_url) {
                                document.getElementById('proofContainer').innerHTML = 
                                    `<img src="${d.proof_photo_url}" class="proof-img" alt="Proof of delivery">`;
                            }
                            document.getElementById('deliveredModal').style.display = 'flex';
                        }
                        // Stop polling when finished
                        clearInterval(pollingInterval);
                    } else if (d.delivery_status === 'cancelled') {
                        statusPill.className = 'status-pill cancelled';
                        document.getElementById('statusIcon').innerText = '❌';
                        clearInterval(pollingInterval);
                    }
                })
                .catch(err => {
                    console.log('Tracking update check:', err);
                });
        }

        if (initialStatus !== 'delivered' && initialStatus !== 'cancelled') {
            pollingInterval = setInterval(pollLiveTracking, 5000);
        }
    </script>
</body>
</html>
