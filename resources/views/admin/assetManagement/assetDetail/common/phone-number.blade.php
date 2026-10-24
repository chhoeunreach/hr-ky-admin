@php
    $numbers = array_values(array_filter(array_map('trim', preg_split('/[\r\n,;\/|]+/u', (string) ($phoneNumber ?? ''))), fn ($number) => $number !== ''));
@endphp
{{ $numbers ? implode("\n", $numbers) : __('index.not_available') }}
