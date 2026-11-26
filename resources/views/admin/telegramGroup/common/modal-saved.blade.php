<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('index.dialog_success_title') ?? 'Success' }}</title>
</head>
<body>
    <p>{{ $message }}</p>
    <a href="javascript:void(0)" data-index-url="{{ route('admin.telegram-groups.index') }}" onclick="if(window.parent!==window)window.parent.postMessage({type:'telegram-group-action-saved',message:@json($message)},window.location.origin);">{{ __('index.close') ?? 'Close' }}</a>
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
