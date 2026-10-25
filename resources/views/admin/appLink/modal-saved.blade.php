<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('index.link_create') }}</title>
</head>
<body>
    <p role="status">{{ __('message.link_added_successfully') }}</p>
    <a href="{{ $redirectUrl }}" target="_top">{{ __('index.back') }}</a>
    <script>
        if (window.parent !== window) {
            window.parent.postMessage({type: 'employee-request-form-saved', redirectUrl: @json($redirectUrl)}, window.location.origin);
        } else {
            window.location.replace(@json($redirectUrl));
        }
    </script>
</body>
</html>
