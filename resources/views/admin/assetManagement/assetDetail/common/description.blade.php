@php
    $description = trim(html_entity_decode(strip_tags(preg_replace('/<\/(p|div)>|<br\s*\/?>/i', "\n", $note ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
@endphp
@if(mb_strlen($description) > 250)
    <details class="asset-description">
        <summary>
            <span class="asset-description-excerpt">{{ mb_substr($description, 0, 250) }}…</span>
            <span class="asset-description-more">{{ __('index.asset_description_view_more') }}</span>
            <span class="asset-description-less">{{ __('index.asset_description_view_less') }}</span>
        </summary>
        <div class="asset-description-full">{{ $description }}</div>
    </details>
@else
    <span class="asset-description-full">{{ $description ?: __('index.not_available') }}</span>
@endif
