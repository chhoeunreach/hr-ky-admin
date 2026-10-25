<td>{{ $value->created_by_name ?: $value->createdBy?->name ?: '—' }}</td>
<td>
    @forelse($value->managers as $manager)
        <span class="badge bg-light text-dark border me-1 mb-1">{{ $manager->name }}</span>
    @empty
        <span class="text-muted">—</span>
    @endforelse
</td>
