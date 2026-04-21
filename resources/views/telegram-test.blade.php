<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telegram Test</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 24px; }
        label { display: block; margin-top: 12px; font-weight: 600; }
        input, textarea { width: 520px; max-width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
        button { margin-top: 16px; padding: 10px 14px; border-radius: 10px; border: 0; background: #111827; color: #fff; cursor: pointer; }
        .ok { padding: 10px 12px; border-radius: 10px; background: #ecfdf5; color: #065f46; margin-bottom: 16px; }
        .err { padding: 10px 12px; border-radius: 10px; background: #fef2f2; color: #991b1b; margin-bottom: 16px; }
        .hint { margin-top: 14px; color: #6b7280; }
    </style>
</head>
<body>
    <h2>Telegram Notification Test</h2>

    @if (session('status'))
        <div class="ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="err">
            <div><strong>Failed:</strong></div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <div class="hint">Check <code>storage/logs/laravel.log</code> for Telegram API error details.</div>
        </div>
    @endif

    <form method="POST" action="{{ route('telegram.notify') }}">
        @csrf

        <label>branchName</label>
        <input name="branchName" value="{{ old('branchName', 'អ៊ីអន') }}" />

        <label>departmentName</label>
        <input name="departmentName" value="{{ old('departmentName', 'management') }}" />

        <label>messageText</label>
        <textarea name="messageText" rows="4">{{ old('messageText', 'Test from Laravel at '.now()) }}</textarea>

        <label>latitude (optional)</label>
        <input name="latitude" value="{{ old('latitude') }}" />

        <label>longitude (optional)</label>
        <input name="longitude" value="{{ old('longitude') }}" />

        <button type="submit">Send</button>
    </form>

    <p class="hint">
        This posts to <code>/telegram/notify</code> and uses your routing + default chat logic.
    </p>
</body>
</html>

