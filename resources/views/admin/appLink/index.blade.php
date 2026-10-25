@extends('layouts.master')

@section('title', __('index.link_list') ?? 'List Link')

@section('action', 'List')

@section('button')
    <a href="{{ route('admin.app-links.create') }}" data-open-request-form data-request-modal-title="{{ __('index.link_create') }}">
        <button class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i> Add New Link
        </button>
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.appLink.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-body">
                <form class="forms-sample" action="{{ route('admin.app-links.index') }}" method="get">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <input type="text" name="search" class="form-control" placeholder="Search name, username, phone, email, URL..."
                                   value="{{ $filterParameters['search'] ?? '' }}">
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <select class="form-select" name="link_type" id="filter_link_type">
                                <option value="">{{ __('index.link_all_platforms') }}</option>
                                @foreach($linkTypes as $key => $label)
                                    <option value="{{ $key }}" {{ (isset($filterParameters['link_type']) && $filterParameters['link_type'] == $key) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <select class="form-select" name="account_type" data-account-type data-platform-id="filter_link_type"
                                    data-selected="{{ $filterParameters['account_type'] ?? '' }}">
                                <option value="">{{ __('index.link_all_account_types') }}</option>
                                @foreach(\App\Models\AppLink::accountTypes() as $platform => $types)
                                    <optgroup label="{{ $linkTypes[$platform] }}">
                                        @foreach($types as $key => $label)
                                            <option value="{{ $key }}" @selected(($filterParameters['account_type'] ?? '') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="1" {{ (isset($filterParameters['status']) && $filterParameters['status'] === '1') ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (isset($filterParameters['status']) && $filterParameters['status'] === '0') ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex">
                                <button type="submit" class="btn btn-success me-2">Filter</button>
                                <a class="btn btn-secondary" href="{{ route('admin.app-links.index') }}">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">List of Links</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>{{ __('index.link_platform') }}</th>
                            <th>{{ __('index.link_account_type') }}</th>
                            <th>{{ __('index.link_username') }}</th>
                            <th>{{ __('index.link_password') }}</th>
                            <th>{{ __('index.phone_number') }}</th>
                            <th>{{ __('index.email') }}</th>
                            <th>URL</th>
                            <th>{{ __('index.link_created_by') }}</th>
                            <th>{{ __('index.link_managed_by') }}</th>
                            <th>Order</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $badgeColors = [
                                'facebook' => 'bg-primary',
                                'tiktok' => 'bg-dark',
                                'telegram' => 'bg-info',
                                'instagram' => 'bg-danger',
                                'youtube' => 'bg-danger',
                                'website' => 'bg-success',
                                'other' => 'bg-secondary',
                            ];
                        @endphp
                        @forelse ($links as $key => $value)
                            <tr>
                                <td>{{ (($links->currentPage() - 1) * (\App\Models\AppLink::RECORDS_PER_PAGE)) + (++$key) }}</td>
                                <td>
                                    @if($value->image)
                                        <img src="{{ asset(\App\Models\AppLink::UPLOAD_PATH . $value->image) }}"
                                             alt="{{ $value->name }}"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 6px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            <i data-feather="link" style="width: 18px; height: 18px; color: #6c757d;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $value->name }}</strong>
                                    @if($value->description)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($value->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $badgeColors[$value->link_type] ?? 'bg-secondary' }}">
                                        {{ $linkTypes[$value->link_type] ?? ucfirst($value->link_type) }}
                                    </span>
                                </td>
                                <td>{{ \App\Models\AppLink::accountTypes()[$value->link_type][$value->account_type] ?? '—' }}</td>
                                <td>{{ $value->username ?: '—' }}</td>
                                <td><span title="{{ __('index.link_password_saved') }}">{{ $value->getRawOriginal('password') ? '••••••••' : '—' }}</span></td>
                                <td>{{ $value->phone_number ?: '—' }}</td>
                                <td>{{ $value->email ?: '—' }}</td>
                                <td>
                                    <a href="{{ $value->url }}" target="_blank" rel="noopener noreferrer" class="text-truncate d-inline-block" style="max-width: 250px;">
                                        {{ $value->url }} <i data-feather="external-link" style="width: 12px; height: 12px;"></i>
                                    </a>
                                </td>
                                @include('admin.appLink.common.ownership')
                                <td>{{ $value->order }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{ route('admin.app-links.toggle-status', $value->id) }}" type="checkbox" {{ $value->status ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center align-items-center gap-2">
                                        <li>
                                            <a href="{{ route('admin.app-links.edit', $value->id) }}" title="Edit Link">
                                                <i class="link-icon" data-feather="edit"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="delete" data-href="{{ route('admin.app-links.delete', $value->id) }}" title="Delete Link">
                                                <i class="link-icon" data-feather="trash-2"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-4">
                                    <b>@lang('index.no_records_found')</b>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $links->appends($_GET)->links() }}
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.appLink.common.scripts')
@endsection
