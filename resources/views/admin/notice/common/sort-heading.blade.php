@php
    $noticeSortActive = request('sort', 'published') === $noticeSort;
    $noticeDirection = request('direction', 'desc') === 'asc' ? 'asc' : 'desc';
    $noticeNextDirection = $noticeSortActive && $noticeDirection === 'asc' ? 'desc' : 'asc';
    $noticeSortUrl = route('admin.notices.index', array_merge(request()->except('page'), ['sort' => $noticeSort, 'direction' => $noticeNextDirection]));
@endphp
<th aria-sort="{{ $noticeSortActive ? ($noticeDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a class="notice-sort {{ $noticeSortActive ? 'is-sorted' : '' }}" href="{{ $noticeSortUrl }}" data-notice-sort title="{{ __('notice.sort_'.$noticeNextDirection.'ending') }}">
        {{ $noticeLabel }}<i data-feather="{{ $noticeSortActive ? ($noticeDirection === 'asc' ? 'arrow-up' : 'arrow-down') : 'chevrons-down' }}"></i>
    </a>
</th>
