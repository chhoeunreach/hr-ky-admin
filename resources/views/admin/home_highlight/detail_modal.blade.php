<div class="modal fade hh-detail" id="highlight-detail-{{ $item->id }}" tabindex="-1" aria-labelledby="highlight-detail-title-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="highlight-detail-title-{{ $item->id }}">{{ $item->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($item->image_url)<img class="hh-detail-cover" src="{{ $item->image_url }}" alt="Highlight cover" loading="lazy">@endif
                <div class="hh-detail-body">{!! $item->body !!}</div>
                @if($item->link_url && filter_var($item->link_url, FILTER_VALIDATE_URL) && strtolower(parse_url($item->link_url, PHP_URL_SCHEME) ?? '') === 'https')
                    <p><a href="{{ $item->link_url }}" target="_blank" rel="noopener noreferrer">{{ $item->link_label ?: 'Read more' }}</a></p>
                @endif
                <div class="hh-detail-grid border-top pt-3">
                    <div><h6>Schedule</h6><p class="small mb-0">{{ !$item->is_active ? 'Draft' : ($item->starts_at && $item->starts_at->isFuture() ? 'Scheduled' : ($item->ends_at && $item->ends_at->lte(now()) ? 'Expired' : 'Live')) }}<br>Starts: {{ $item->starts_at?->format('M j, Y H:i') ?? 'Immediately' }}<br>Ends: {{ $item->ends_at?->format('M j, Y H:i') ?? 'No end date' }}<br>Time zone: {{ config('app.timezone') }}<br>Priority: {{ $item->priority }}</p></div>
                    <div><h6>Dates</h6><p class="small mb-0">Created: {{ $item->created_at?->format('M j, Y H:i') ?? '—' }}<br>Updated: {{ $item->updated_at?->format('M j, Y H:i') ?? '—' }}</p></div>
                    @foreach(['Roles' => ['role_ids', $roleNames], 'Branches' => ['branch_ids', $branchNames], 'Departments' => ['department_ids', $departmentNames], 'Employees' => ['user_ids', $userNames]] as $label => [$attribute, $names])
                        <div><h6>{{ $label }}</h6>
                            @if(!$item->$attribute)<p class="small mb-0">All {{ strtolower($label) }}</p>
                            @else<ul class="small mb-0">@foreach($item->$attribute as $id)<li>{{ $names[$id] ?? '#'.$id }}</li>@endforeach</ul>@endif
                        </div>
                    @endforeach
                </div>
                <p class="small text-muted mt-3 mb-0">Employees must match every audience group with selections.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a class="btn btn-primary" href="{{ route('admin.community.home-highlights.edit', $item) }}">Edit highlight</a>
            </div>
        </div>
    </div>
</div>
