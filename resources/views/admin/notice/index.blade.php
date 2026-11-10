@extends('layouts.master')

@section('title', __('index.notices'))

@section('action', __('index.lists'))



@section('main-content')

    <link rel="stylesheet" href="{{ asset('assets/css/directory-table-tools.css') }}?v={{ filemtime(public_path('assets/css/directory-table-tools.css')) }}">
    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.notice.common.breadcrumb')

        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'noticeFilterCollapse', 'directoryExpanded' => false])
            <div class="collapse" id="noticeFilterCollapse">
                <form id="noticeFilterForm" class="directory-filter-form" action="{{ route('admin.notices.index') }}" method="get">
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
                <div class="table-responsive">
                    <table id="noticeTable" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.branch')</th>
                            <th>@lang('index.publish_date')</th>
                            <th>@lang('index.notice_receiver')</th>
                            @can('show_notice')
                                <th class="text-center">@lang('index.description')</th>
                            @endcan
                            <th class="text-center">@lang('index.status')</th>
                            @canany(['edit_notice', 'delete_notice', 'send_notice'])
                                <th class="text-center" data-directory-actions>@lang('index.action')</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($notices as $key => $value)
                            <tr>
                                <td>{{ $notices->firstItem() + $key }}</td>
                                <td>{{ ucfirst($value->title) }}</td>
                                <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                <td>{{ convertDateTimeFormat($value->notice_publish_date) ?? __('index.not_published_yet') }}</td>
                                <td class="notice-receiver">
                                    <ul class="mb-0">
                                        @foreach ($value->noticeReceiversDetail as $receiver)
                                            <li>{{ $receiver->employee ? ucfirst($receiver->employee->name) : 'N/A' }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                @can('show_notice')
                                    <td class="text-center" data-directory-value="{{ trim(strip_tags($value->description)) }}">
                                        <a href="#" id="showNoticeDescription" data-href="{{ route('admin.notices.show', $value->id) }}" data-id="{{ $value->id }}" title="@lang('index.show_notice_content')">
                                            <i class="link-icon" data-feather="eye"></i>
                                        </a>
                                    </td>
                                @endcan
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{ route('admin.notices.toggle-status', $value->id) }}" type="checkbox" {{ $value->is_active ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                @canany(['edit_notice', 'delete_notice', 'send_notice'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center align-items-center gap-2">
                                            @can('edit_notice')
                                                <li>
                                                    <a data-open-notice-form href="{{ route('admin.notices.edit', $value->id) }}" title="@lang('index.edit_notice')">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('delete_notice')
                                                <li>
                                                    <a class="delete" data-href="{{ route('admin.notices.delete', $value->id) }}" title="@lang('index.delete_notice_detail')">
                                                        <i class="link-icon" data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('send_notice')
                                                <li>
                                                    <a class="sendNotice" data-href="{{ route('admin.notices.send-notice', $value->id) }}" title="@lang('index.send_work_alert')">
                                                        <button class="btn btn-warning btn-xs text-nowrap d-inline-flex align-items-center gap-1">
                                                            <i data-feather="bell" style="width: 12px; height: 12px;"></i> @lang('index.send_work_alert')
                                                        </button>
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
                <div class="dataTables_paginate p-3">
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
