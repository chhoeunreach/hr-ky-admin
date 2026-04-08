@extends('layouts.master')

@section('title', __('index.notices'))

@section('action', __('index.lists'))

@section('button')
    @can('create_notice')
        <a href="{{ route('admin.notices.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i> @lang('index.create_notice')
            </button>
        </a>
    @endcan
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.notice.common.breadcrumb')

        <div class="card mb-4">
            <form class="forms-sample card-body pb-0" action="{{ route('admin.notices.index') }}" method="get">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  {{ !isset($filterParameters['branch_id']) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterParameters['branch_id']) && $filterParameters['branch_id'] == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="col-lg col-md-6 mb-4">
                        <select class="form-select" multiple name="notice_receiver[]" id="notice">

                        </select>
                    </div>

                    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                        <div class="col-lg col-md-6 mb-4">

                            <input type="text" id="fromDate" name="publish_date_from" value="{{ $filterParameters['publish_date_from'] }}" placeholder="mm/dd/yyyy" class="form-control fromDate">
                        </div>

                        <div class="col-lg col-md-6 mb-4">
                            <input type="text" id="toDate" name="publish_date_to" value="{{ $filterParameters['publish_date_to'] }}" placeholder="mm/dd/yyyy" class="form-control toDate">
                        </div>
                    @else
                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date" id="fromDate" name="publish_date_from" value="{{ $filterParameters['publish_date_from'] }}" class="form-control fromDate">
                        </div>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <input type="date" id="toDate" name="publish_date_to" value="{{ $filterParameters['publish_date_to'] }}" class="form-control toDate">
                        </div>
                    @endif

                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="d-flex float-md-end">
                            <button type="submit" class="btn btn-block btn-success me-2">@lang('index.filter')</button>
                            <a class="btn btn-block btn-primary" href="{{ route('admin.notices.index') }}">@lang('index.reset')</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">@lang('index.notice_lists')</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.publish_date')</th>
                            <th>@lang('index.notice_receiver')</th>
                            @can('show_notice')
                                <th class="text-center">@lang('index.description')</th>
                            @endcan
                            <th class="text-center">@lang('index.status')</th>
                            @canany(['edit_notice', 'delete_notice', 'send_notice'])
                                <th class="text-center">@lang('index.action')</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($notices as $key => $value)
                            <tr>
                                <td>{{ (($notices->currentPage() - 1) * (\App\Models\Notice::RECORDS_PER_PAGE)) + (++$key) }}</td>
                                <td>{{ ucfirst($value->title) }}</td>
                                <td>{{ convertDateTimeFormat($value->notice_publish_date) ?? __('index.not_published_yet') }}</td>
                                <td class="notice-receiver">
                                    <ul class="mb-0">
                                        @foreach ($value->noticeReceiversDetail as $receiver)
                                            <li>{{ $receiver->employee ? ucfirst($receiver->employee->name) : 'N/A' }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                @can('show_notice')
                                    <td class="text-center">
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
                                                    <a href="{{ route('admin.notices.edit', $value->id) }}" title="@lang('index.edit_notice')">
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
                                                    <a class="sendNotice" data-href="{{ route('admin.notices.send-notice', $value->id) }}" title="@lang('index.send_notice')">
                                                        <button class="btn btn-primary btn-xs text-nowrap">@lang('index.send_notice')</button>
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
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $notices->appends($_GET)->links() }}
        </div>

    </section>

    @include('admin.notice.show')

@endsection

@section('scripts')
    @include('admin.notice.common.scripts')
@endsection
