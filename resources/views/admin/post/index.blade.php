@extends('layouts.master')
@section('title', __('index.post'))

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.posts.index') }}">{{ __('index.post_section') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.posts') }}</li>
            </ol>

            @can('create_post')
                <a href="{{ route('admin.posts.create') }}">
                    <button class="btn btn-primary add_department">
                        <i class="link-icon" data-feather="plus"></i>{{ __('index.add_post') }}
                    </button>
                </a>
            @endcan
        </nav>


        @php
            $hasPostFilters = filled($filterParameters['branch_id'] ?? null)
                || filled($filterParameters['department_id'] ?? null)
                || filled($filterParameters['name'] ?? null)
                || filled($filterParameters['search'] ?? null)
                || (($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null)
                || (($filterParameters['per_page'] ?? '25') !== '25');
        @endphp

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#postFilterCollapse"
                            aria-expanded="{{ $hasPostFilters ? 'true' : 'false' }}"
                            aria-controls="postFilterCollapse">
                        <i class="link-icon" data-feather="filter"></i>
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.post_lists') }}</h6>
                </div>
            </div>
            <div id="postFilterCollapse" class="collapse{{ $hasPostFilters ? ' show' : '' }}">
            <form class="forms-sample card-body pb-0" action="{{ route('admin.posts.index') }}" id="postFilterForm" method="get">
                <input type="hidden" id="postSearch" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                <div class="row align-items-center">
                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                            <select class="form-control" id="branch_id" name="branch_id[]" multiple data-placeholder="{{ __('index.select_branch') }}">
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ in_array((string) $branch->id, $filterParameters['branch_id'] ?? [], true) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif


                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" name="department_id[]" id="department_id" multiple data-placeholder="{{ __('index.select_department') }}">

                        </select>
                    </div>


                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.search_by_post_name') }}" name="name" value="{{ $filterParameters['name'] }}" class="form-control">

                    </div>

                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="">{{ __('index.all') }} {{ __('index.status') }}</option>
                            <option value="1" {{ (string)($filterParameters['is_active'] ?? '') === '1' ? 'selected' : '' }}>{{ __('index.active') }}</option>
                            <option value="0" {{ (string)($filterParameters['is_active'] ?? '') === '0' ? 'selected' : '' }}>{{ __('index.inactive') }}</option>
                        </select>
                    </div>

                    <div class="col-xxl-4 col-xl-4 col-md-6">
                        <div class="d-md-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-block btn-success mb-4">{{  __('index.filter') }}</button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-block btn-primary mb-4">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </div>

        <div id="postListSection">
        <div class="card  support-main">
            <div class="card-header">
                <div class="post-toolbar">
                    <div class="post-toolbar-left">
                        <h6 class="card-title mb-0">{{ __('index.post_lists') }}</h6>
                        <div class="post-entry-control">
                            <span>Show</span>
                            <select class="form-control post-entry-select" id="per_page" name="per_page" form="postFilterForm">
                                <option value="25" {{ (string)($filterParameters['per_page'] ?? '') === '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (string)($filterParameters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (string)($filterParameters['per_page'] ?? '') === '100' ? 'selected' : '' }}>100</option>
                                <option value="200" {{ (string)($filterParameters['per_page'] ?? '') === '200' ? 'selected' : '' }}>200</option>
                                <option value="500" {{ (string)($filterParameters['per_page'] ?? '') === '500' ? 'selected' : '' }}>500</option>
                                <option value="1000" {{ (string)($filterParameters['per_page'] ?? '') === '1000' ? 'selected' : '' }}>1,000</option>
                                <option value="all" {{ (string)($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('index.all') }}</option>
                            </select>
                            <span>entries</span>
                        </div>
                    </div>
                    <div class="post-toolbar-search">
                        <input type="text"
                               id="postListSearch"
                               class="post-list-search"
                               value="{{ $filterParameters['search'] ?? '' }}"
                               placeholder="Search ...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <style>
                    .post-toolbar {
                        display: grid;
                        grid-template-columns: auto 1fr;
                        align-items: center;
                        gap: 16px;
                    }

                    .post-toolbar-left {
                        display: flex;
                        align-items: center;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .post-toolbar-search {
                        display: flex;
                        justify-content: flex-end;
                    }

                    .post-entry-control {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        color: #111827;
                        font-weight: 500;
                    }

                    .post-entry-select {
                        min-width: 120px;
                    }

                    .post-list-search {
                        width: min(100%, 250px);
                        border: 1px solid #d7dfeb;
                        border-radius: 14px;
                        min-height: 44px;
                        padding: 0 14px;
                        color: #111827;
                        background: #f8fbff;
                        box-shadow: none;
                    }

                    .post-list-search:focus {
                        outline: none;
                        border-color: #93c5fd;
                        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
                    }

                    @media (max-width: 767.98px) {
                        .post-toolbar {
                            grid-template-columns: 1fr;
                        }

                        .post-toolbar-search {
                            justify-content: stretch;
                        }

                        .post-list-search {
                            width: 100%;
                        }
                    }
                </style>
                <div class="table-responsive">
                    <table id="postTable" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.post_name') }}</th>
                            <th>{{ __('index.department') }}</th>
                            <th>{{ __('index.branch') }}</th>
                            <th class="text-center">{{ __('index.total_employee') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>

                            @canany(['edit_post','delete_post'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($posts as $key => $value)
                            <tr>
                                <td>{{ ($posts->firstItem() ?? 0) + $key }}</td>
                                <td>{{ ucfirst($value->post_name) }}</td>
                                <td>{{ ucfirst($value->department?->dept_name ?? __('index.not_available')) }}</td>
                                <td>{{ ucfirst($value->branch?->name ?? __('index.not_available')) }}</td>
                                <td class="text-center">
                                    <p class="btn btn-info btn-sm" id="showEmployee" data-employee="{{ $value->employees }}">
                                        {{ $value->employees_count }}
                                    </p>
                                </td>

                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{ route('admin.posts.toggle-status', $value->id) }}"
                                               type="checkbox" {{ ($value->is_active == 1) ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['edit_post','delete_post'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('edit_post')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.posts.edit', $value->id) }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_post')
                                                <li>
                                                    <a class="deletePost" href="#"
                                                       data-href="{{ route('admin.posts.delete', $value->id) }}">
                                                        <i class="link-icon"  data-feather="delete"></i>
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
                                    <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dataTables_paginate mt-4">
            {{ $posts->appends($_GET)->links() }}
        </div>
        </div>

        @include('admin.post.show')

    </section>
@endsection

@section('scripts')
    @include('admin.post.common.scripts')
@endsection
