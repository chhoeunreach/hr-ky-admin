@php
    $currentSort = $filterParameters['sort_by'] ?? 'id';
    $currentDirection = $filterParameters['sort_direction'] ?? 'desc';
    $isSorted = $currentSort === $column;
    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
@endphp
<th data-department-column="{{ $column }}" class="{{ $class ?? '' }}" aria-sort="{{ $isSorted ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a class="department-sort-link d-inline-flex align-items-center gap-1 text-reset text-decoration-none"
       href="{{ route('admin.departments.index', array_merge(request()->query(), ['sort_by' => $column, 'sort_direction' => $nextDirection, 'page' => null])) }}"
       data-sort="{{ $column }}" data-direction="{{ $nextDirection }}">
        {{ $label }}
        <span aria-hidden="true" class="{{ $isSorted ? 'text-primary' : 'text-muted' }}">{{ $isSorted ? ($currentDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
    </a>
</th>
