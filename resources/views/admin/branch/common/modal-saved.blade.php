<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="UTF-8"><title>{{ __('index.dialog_success_title') }}</title></head>
<body>
    <p>{{ $message }}</p>
    <a href="{{ route('admin.branch.index') }}">{{ __('index.back') }}</a>
    <script>if (window.parent !== window) window.parent.postMessage({type: 'branch-action-saved'}, window.location.origin);</script>
</body>
</html>
