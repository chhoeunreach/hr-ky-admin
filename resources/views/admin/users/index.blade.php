@extends('layouts.master')

@section('title', __('index.users'))

@section('action', __('index.lists'))

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.users.common.breadcrumb')


        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'usersFilters', 'directoryExpanded' => false])
            <div class="collapse" id="usersFilters">
                <form id="usersFilterForm" class="directory-filter-form" action="{{ route('admin.users.index') }}" method="get">
                    <input type="hidden" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label for="usersNameFilter" class="form-label">{{ __('index.full_name') }}</label>
                            <input id="usersNameFilter" name="name" class="form-control" value="{{ $filterParameters['name'] ?? '' }}">
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label for="usersEmailFilter" class="form-label">{{ __('index.email') }}</label>
                            <input id="usersEmailFilter" name="email" class="form-control" value="{{ $filterParameters['email'] ?? '' }}">
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label for="usersStatusFilter" class="form-label">{{ __('index.status') }}</label>
                            <select id="usersStatusFilter" name="is_active" class="form-select">
                                <option value="">{{ __('index.all_status') }}</option>
                                <option value="1" @selected((string) ($filterParameters['is_active'] ?? '') === '1')>{{ __('index.active') }}</option>
                                <option value="0" @selected((string) ($filterParameters['is_active'] ?? '') === '0')>{{ __('index.inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.users.index')])
                </form>
            </div>
        </div>
        <div class="card">
            @include('admin.section.directory-toolbar', ['directoryTable' => 'usersDirectoryTable', 'directoryForm' => 'usersFilterForm', 'directorySearch' => 'usersDirectorySearch', 'directoryRecords' => $admins, 'directoryTitle' => __('index.user_list'), 'directoryIcon' => 'users', 'directoryCanAdd' => auth('admin')->check(), 'directoryAddUrl' => route('admin.users.create'), 'directoryDefaultSize' => '25', 'directorySearchKey' => 'search', 'directoryLive' => false])
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersDirectoryTable" class="table">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('index.full_name') }}</th>
                            <th class="text-center">{{ __('index.email') }}</th>
                            <th class="text-center">{{ __('index.is_active') }}</th>
                            <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($admins as $key => $value)
                            <tr>
                                <td class="text-center">{{ $admins->firstItem() + $key }}</td>
                                <td><a href="{{ route('admin.users.show', $value->id) }}" class="text-reset">{{ ucfirst($value->name) }}</a></td>
                                <td class="text-center">{{ $value->email }}</td>

                                    <td class="text-center">
                                        <label class="switch">
                                            <input class="toggleStatus"
                                                   href="{{ route('admin.users.toggle-status', $value->id) }}"
                                                   type="checkbox" {{ $value->is_active == 1 ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>


                                        <td class="text-center">
                                            <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown{{ $value->id }}"
                                               role="button"
                                               data-bs-toggle="dropdown"
                                               aria-haspopup="true"
                                               aria-expanded="false"
                                               title="{{ __('index.action') }}"
                                            >
                                            </a>

                                            <div class="dropdown-menu p-0" aria-labelledby="profileDropdown{{ $value->id }}">
                                                <ul class="list-unstyled p-1 mb-0">

                                                    @if($value->id == auth('admin')->user()->id)
                                                        <li class="dropdown-item py-2">
                                                            <a href="{{ route('admin.users.edit', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.edit_detail') }}</button>
                                                            </a>
                                                        </li>

                                                    @endif


                                                        @if($value->id != auth('admin')->user()->id || $value->id != 1)
                                                            <li class="dropdown-item py-2">
                                                                <a class="deleteEmployee"
                                                                   data-href="{{ route('admin.users.delete', $value->id) }}">
                                                                    <button class="btn btn-primary btn-xs">{{ __('index.delete_user') }}</button>
                                                                </a>
                                                            </li>
                                                        @endif


                                                    @if($value->id == auth('admin')->user()->id)
                                                        <li class="dropdown-item py-2">
                                                            <a class="changePassword"
                                                               data-href="{{ route('admin.users.change-password', $value->id) }}">
                                                                <button class="btn btn-primary btn-xs">{{ __('index.change_password') }}</button>
                                                            </a>
                                                        </li>
                                                    @endif


                                                </ul>
                                            </div>
                                        </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-3">
            {{ $admins->appends($_GET)->links() }}
        </div>

    </section>
    @include('admin.users.common.password')
@endsection

@section('scripts')
    @include('admin.users.common.scripts')
@endsection
