<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('index.dialog_success_title') ?? 'Success' }}</title>
</head>
<body>
    <p>{{ $message }}</p>
    <a href="{{ route('admin.telegram-groups.index') }}">{{ __('index.back') ?? 'Back' }}</a>
    <script>
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'telegram-group-action-saved',
                message: @json($message)
            }, window.location.origin);
        }
    </script>
</body>
</html>
