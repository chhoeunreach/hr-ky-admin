<table id="noticeTable" class="table notice-table align-middle">
    <thead><tr>
        <th class="notice-number">#</th>
        @include('admin.notice.common.sort-heading', ['noticeSort' => 'title', 'noticeLabel' => __('index.title')])
        <th>{{ __('index.branch') }}</th>
        @include('admin.notice.common.sort-heading', ['noticeSort' => 'published', 'noticeLabel' => __('index.publish_date')])
        <th>{{ __('index.notice_receiver') }}</th>
        @can('show_notice')<th class="text-center">{{ __('index.description') }}</th>@endcan
        @include('admin.notice.common.sort-heading', ['noticeSort' => 'status', 'noticeLabel' => __('index.status')])
        @canany(['edit_notice', 'delete_notice', 'send_notice'])
            <th class="text-end" data-directory-actions>{{ __('index.action') }}</th>
        @endcanany
    </tr></thead>
    <tbody>
    @forelse($notices as $key => $value)
        @php
            $receivers = $value->noticeReceiversDetail->pluck('employee.name')->filter()->values();
            $descriptionText = trim(html_entity_decode(strip_tags($value->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        @endphp
        <tr>
            <td class="notice-number">{{ $notices->firstItem() + $key }}</td>
            <td class="notice-title-cell" data-directory-value="{{ $value->title }}">
                <span class="notice-title">{{ $value->title }}</span>
                @can('show_notice')<p class="notice-preview">{{ Str::limit($descriptionText, 110) }}</p>@endcan
            </td>
            <td><span class="notice-branch">{{ $value->branch?->name ?? '—' }}</span></td>
            <td class="notice-date">{{ $value->notice_publish_date ? convertDateTimeFormat($value->notice_publish_date) : __('index.not_published_yet') }}</td>
            <td class="notice-receivers" data-directory-value="{{ $receivers->implode(', ') }}">
                @forelse($receivers->take(2) as $name)
                    <span class="notice-person"><span class="notice-avatar" aria-hidden="true">{{ Str::upper(Str::substr($name, 0, 1)) }}</span>{{ $name }}</span>
                @empty
                    <span class="text-muted">{{ __('notice.no_receivers') }}</span>
                @endforelse
                @if($receivers->count() > 2)
                    <details class="notice-more">
                        <summary>{{ __('notice.more_receivers', ['count' => $receivers->count() - 2]) }}</summary>
                        @foreach($receivers->slice(2) as $name)<span class="notice-person">{{ $name }}</span>@endforeach
                    </details>
                @endif
            </td>
            @can('show_notice')
                <td class="text-center" data-directory-value="{{ $descriptionText }}">
                    <button type="button" class="notice-action" data-notice-description data-href="{{ route('admin.notices.show', $value->id) }}" title="{{ __('index.show_notice_content') }}" aria-label="{{ __('index.show_notice_content') }}"><i data-feather="eye"></i></button>
                </td>
            @endcan
            <td data-directory-value="{{ $value->is_active ? __('index.active') : __('index.inactive') }}">
                <div class="notice-status-control">
                    <span class="notice-status {{ $value->is_active ? 'is-active' : 'is-inactive' }}"><span aria-hidden="true"></span>{{ $value->is_active ? __('index.active') : __('index.inactive') }}</span>
                    @can('edit_notice')
                        <label class="switch notice-status-switch">
                            <input class="toggleStatus" href="{{ route('admin.notices.toggle-status', $value->id) }}" type="checkbox" role="switch" aria-label="{{ __('index.confirm_change_notice_status') }}" @checked($value->is_active)>
                            <span class="slider round"></span>
                        </label>
                    @endcan
                </div>
            </td>
            @canany(['edit_notice', 'delete_notice', 'send_notice'])
                <td><div class="notice-actions">
                    @can('edit_notice')
                        <button type="button" class="notice-action notice-edit" data-open-notice-form data-url="{{ route('admin.notices.edit', $value->id) }}" title="{{ __('index.edit_notice') }}" aria-label="{{ __('index.edit_notice') }}"><i data-feather="edit-2"></i></button>
                    @endcan
                    @can('send_notice')
                        <button type="button" class="notice-action notice-send sendNotice" data-href="{{ route('admin.notices.send-notice', $value->id) }}" title="{{ __('index.send_work_alert') }}" aria-label="{{ __('index.send_work_alert') }}"><i data-feather="bell"></i></button>
                    @endcan
                    @can('delete_notice')
                        <button type="button" class="notice-action notice-delete delete" data-href="{{ route('admin.notices.delete', $value->id) }}" title="{{ __('index.delete_notice_detail') }}" aria-label="{{ __('index.delete_notice_detail') }}"><i data-feather="trash-2"></i></button>
                    @endcan
                </div></td>
            @endcanany
        </tr>
    @empty
        <tr><td colspan="{{ 6 + (Gate::allows('show_notice') ? 1 : 0) + (Gate::any(['edit_notice', 'delete_notice', 'send_notice']) ? 1 : 0) }}" class="notice-empty"><i data-feather="bell"></i><p>{{ __('index.no_records_found') }}</p></td></tr>
    @endforelse
    </tbody>
</table>
