@extends('layouts.master')

@section('title', __('index.notices'))

@section('action', __('index.lists'))



@section('main-content')

    <link rel="stylesheet" href="{{ asset('assets/css/directory-table-tools.css') }}?v={{ filemtime(public_path('assets/css/directory-table-tools.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/notice-directory.css') }}?v={{ filemtime(public_path('assets/css/notice-directory.css')) }}">
    <section class="content notice-directory">

        @include('admin.section.flash_message')
        <div id="noticeDirectoryMessage" class="alert d-none" role="status"></div>

        @include('admin.notice.common.breadcrumb')

        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'noticeFilterCollapse', 'directoryExpanded' => false])
            <div class="collapse" id="noticeFilterCollapse">
                <form id="noticeFilterForm" class="directory-filter-form" action="{{ route('admin.notices.index') }}" method="get">
                    <input type="hidden" name="sort" value="{{ request('sort', 'published') }}">
                    <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
                    <input type="hidden" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                    <div class="row g-3">
                        @if(!isset(auth()->user()->branch_id))
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label" for="branch_id">{{ __('index.branch') }}</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value="">{{ __('index.all') }}</option>
                                    @foreach($companyDetail?->branches ?? [] as $branch)
                                        <option value="{{ $branch->id }}" @selected(($filterParameters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label" for="notice">{{ __('index.notice_receiver') }}</label>
                            <select class="form-select" multiple name="notice_receiver[]" id="notice"></select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label" for="fromDate">{{ __('notice.publish_from') }}</label>
                            <input type="{{ \App\Helpers\AppHelper::ifDateInBsEnabled() ? 'text' : 'date' }}" id="fromDate" name="publish_date_from" value="{{ $filterParameters['publish_date_from'] }}" class="form-control fromDate">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label" for="toDate">{{ __('notice.publish_to') }}</label>
                            <input type="{{ \App\Helpers\AppHelper::ifDateInBsEnabled() ? 'text' : 'date' }}" id="toDate" name="publish_date_to" value="{{ $filterParameters['publish_date_to'] }}" class="form-control toDate">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label" for="noticeStatus">{{ __('index.status') }}</label>
                            <select class="form-select" name="is_active" id="noticeStatus">
                                <option value="">{{ __('index.all') }}</option>
                                @foreach(['1' => __('index.active'), '0' => __('index.inactive')] as $status => $label)
                                    <option value="{{ $status }}" @selected((string) ($filterParameters['is_active'] ?? '') === (string) $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.notices.index')])
                </form>
            </div>
        </div>

        <div class="card">
            @include('admin.section.directory-toolbar', [
                'directoryTable' => 'noticeTable', 'directoryForm' => 'noticeFilterForm',
                'directorySearch' => 'noticeSearch', 'directoryRecords' => $notices,
                'directoryTitle' => __('index.notice_lists'), 'directoryIcon' => 'bell',
                'directoryPermission' => 'create_notice', 'directoryAddUrl' => route('admin.notices.create'),
                'directoryAddNoticeModal' => true, 'directoryDefaultSize' => '25',
                'directoryCurrentSize' => $filterParameters['per_page'], 'directoryPageSizes' => [25, 50, 100, 200, 'all'],
                'directorySearchKey' => 'search', 'directoryLive' => true,
            ])
            <div id="notice-list-block" data-directory-total="{{ $notices->total() }}">
            <div class="card-body">
                <div class="table-responsive notice-table-scroll">
                    @include('admin.notice.common.table')
                </div>
            </div>
                <div class="notice-list-footer">
                    <span class="text-muted small">{{ __('notice.results', ['from' => $notices->firstItem() ?? 0, 'to' => $notices->lastItem() ?? 0, 'total' => $notices->total()]) }}</span>
                    {{ $notices->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

    </section>

    @include('admin.notice.show')
    @include('admin.notice.common.form-modal')

@endsection

@section('scripts')
    @include('admin.notice.common.scripts')
    <script src="{{ asset('assets/js/notice-directory.js') }}?v={{ filemtime(public_path('assets/js/notice-directory.js')) }}"></script>
@endsection
