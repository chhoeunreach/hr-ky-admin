<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KNEAYERNG Android App</title>
    <style>
        body { margin: 0; background: #f3f6fb; color: #14243a; font: 16px/1.6 system-ui, sans-serif; }
        .wrap { max-width: 820px; margin: 7vh auto; padding: 24px; }
        .card { background: #fff; border: 1px solid #e1e8f0; border-radius: 20px; padding: clamp(24px, 6vw, 52px); box-shadow: 0 12px 40px #102a5612; }
        .brand { display: flex; align-items: center; gap: 18px; margin-bottom: 24px; }
        .brand img { width: 88px; height: 88px; object-fit: contain; border-radius: 14px; }
        h1 { margin: 0; color: #123e72; }
        .muted { color: #607087; }
        .download { display: inline-block; background: #1769aa; color: white; text-decoration: none; padding: 13px 22px; border-radius: 10px; font-weight: 700; }
        .hash { overflow-wrap: anywhere; background: #f3f6fb; padding: 12px; border-radius: 8px; font: 12px ui-monospace, monospace; }
        .steps { padding-left: 20px; }
    </style>
</head>
<body>
<main class="wrap">
    <section class="card">
        <header class="brand">
            <img src="{{ asset('assets/images/kneayerng-app-logo.png') }}" alt="KNEAYERNG logo">
            <div>
                <p class="muted">OFFICIAL PRIVATE DISTRIBUTION</p>
                <h1>KNEAYERNG for Android</h1>
            </div>
        </header>
        @if(!$release)
            <p>No Android release is currently available. Please check again later.</p>
        @else
            @php
                $minimumSdk = (int) $release->minimum_android_sdk;
                $androidNames = [28 => 'Android 9', 29 => 'Android 10', 30 => 'Android 11', 31 => 'Android 12', 32 => 'Android 12L', 33 => 'Android 13', 34 => 'Android 14', 35 => 'Android 15', 36 => 'Android 16'];
                $minimumAndroidName = $androidNames[$minimumSdk] ?? "Android SDK {$minimumSdk}";
            @endphp
            <p>Version {{ $release->version_name }} (build {{ $release->version_code }})</p>
            <p>{{ number_format($release->apk_size / 1048576, 1) }} MB · Minimum supported Android: {{ $minimumAndroidName }} (API {{ $minimumSdk }}) and later · Target API {{ $release->target_android_sdk }}</p>
            @if($release->release_notes)<p>{{ $release->release_notes }}</p>@endif
            <a class="download" href="{{ route('app.download-android') }}">Download official APK</a>
            <h2>Verify the APK</h2>
            <p class="muted">SHA-256 checksum</p>
            <div class="hash">{{ $release->apk_sha256 }}</div>
            <h2>Install</h2>
            <ol class="steps">
                <li>Download the APK on your Android device.</li>
                <li>Open the downloaded file and allow installation for your browser or file manager if Android asks.</li>
                <li>Compare the displayed SHA-256 value with the file checksum before installing.</li>
            </ol>
            <h2>Security</h2>
            <p>Download APK files only from this official website and verify their SHA-256 checksum. When App Security enforcement is enabled, Laravel also verifies the registered app release and applies the configured device approval and access policy.</p>
            <p class="muted">This is a direct APK distribution. The application is not hosted on Google Play. Android device support can vary by manufacturer and device configuration.</p>
        @endif
        <p><a href="{{ url('/privacy') }}">Privacy policy</a></p>
    </section>
</main>
</body>
</html>
