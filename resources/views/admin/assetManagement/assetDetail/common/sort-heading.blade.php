@php
    $activeSort = request('sort') === $column;
    $nextDirection = $activeSort && request('direction', 'asc') === 'asc' ? 'desc' : 'asc';
    $sortUrl = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection, 'page' => null, 'export' => null]);
@endphp
<a class="asset-sort-link" href="{{ $sortUrl }}" title="{{ __('index.asset_sort_' . $nextDirection, ['column' => $label]) }}">{{ $label }}</a>
