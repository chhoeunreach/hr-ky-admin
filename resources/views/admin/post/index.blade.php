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

        <!-- Top KPI Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 4px solid #3b82f6 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium d-block mb-1">Total Positions</span>
                            <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_posts'] ?? $posts->total() }}</h3>
                        </div>
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="link-icon" data-feather="briefcase" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 4px solid #10b981 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium d-block mb-1">Active Positions</span>
                            <h3 class="fw-bold mb-0 text-success">{{ $stats['active_posts'] ?? 0 }}</h3>
                        </div>
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="link-icon" data-feather="check-circle" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%); border-left: 4px solid #8b5cf6 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium d-block mb-1">Total Employees</span>
                            <h3 class="fw-bold mb-0" style="color: #7c3aed;">{{ $stats['total_employees'] ?? 0 }}</h3>
                        </div>
                        <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                            <i class="link-icon" data-feather="users" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%); border-left: 4px solid #f97316 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium d-block mb-1">Covered Branches</span>
                            <h3 class="fw-bold mb-0" style="color: #ea580c;">{{ $stats['total_branches'] ?? 0 }}</h3>
                        </div>
                        <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1); color: #f97316;">
                            <i class="link-icon" data-feather="map-pin" style="width: 24px; height: 24px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between gap-2 py-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 rounded-3 px-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#postFilterCollapse"
                            aria-expanded="{{ $hasPostFilters ? 'true' : 'false' }}"
                            aria-controls="postFilterCollapse">
                        <i class="link-icon" data-feather="filter" style="width: 14px; height: 14px;"></i>
                        <span>{{ __('index.filter') }}</span>
                        @if($hasPostFilters)
                            <span class="badge bg-primary rounded-pill" style="font-size: 10px;">Active</span>
                        @endif
                    </button>
                    <h6 class="card-title mb-0 fw-bold">{{ __('index.post_lists') }}</h6>
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
                            <button type="submit" class="btn btn-block btn-success mb-4 rounded-3">{{  __('index.filter') }}</button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-block btn-primary mb-4 rounded-3">{{  __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </div>

        <div id="postListSection">
        <div class="card support-main border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
            <div class="card-header bg-white border-bottom py-3">
                <div class="post-toolbar">
                    <div class="post-toolbar-left">
                        <h6 class="card-title mb-0 fw-bold">{{ __('index.post_lists') }}</h6>
                        <div class="post-entry-control">
                            <span class="text-muted small">Show</span>
                            <select class="form-control post-entry-select" id="per_page" name="per_page" form="postFilterForm">
                                <option value="25" {{ (string)($filterParameters['per_page'] ?? '') === '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ (string)($filterParameters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ (string)($filterParameters['per_page'] ?? '') === '100' ? 'selected' : '' }}>100</option>
                                <option value="200" {{ (string)($filterParameters['per_page'] ?? '') === '200' ? 'selected' : '' }}>200</option>
                                <option value="500" {{ (string)($filterParameters['per_page'] ?? '') === '500' ? 'selected' : '' }}>500</option>
                                <option value="1000" {{ (string)($filterParameters['per_page'] ?? '') === '1000' ? 'selected' : '' }}>1,000</option>
                                <option value="all" {{ (string)($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>{{ __('index.all') }}</option>
                            </select>
                            <span class="text-muted small">entries</span>
                        </div>
                    </div>
                    <div class="post-toolbar-search d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 rounded-3 px-3 py-2" id="exportPostCsvBtn" title="Export as CSV">
                            <i class="link-icon" data-feather="download" style="width: 14px; height: 14px;"></i>
                            <span>Export</span>
                        </button>
                        <input type="text"
                               id="postListSearch"
                               class="post-list-search"
                               value="{{ $filterParameters['search'] ?? '' }}"
                               placeholder="Search positions...">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
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
                        min-width: 100px;
                        border-radius: 10px;
                    }

                    .post-list-search {
                        width: min(100%, 260px);
                        border: 1px solid #d7dfeb;
                        border-radius: 12px;
                        min-height: 38px;
                        padding: 0 14px;
                        color: #111827;
                        background: #f8fbff;
                        box-shadow: none;
                        font-size: 13px;
                    }

                    .post-list-search:focus {
                        outline: none;
                        border-color: #93c5fd;
                        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
                    }

                    #postTable th {
                        font-weight: 600;
                        font-size: 12px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        color: #64748b;
                        background: #f8fafc;
                        border-bottom: 1px solid #e2e8f0;
                        padding: 12px 16px;
                    }

                    #postTable td {
                        padding: 14px 16px;
                        vertical-align: middle;
                        border-bottom: 1px solid #f1f5f9;
                    }

                    #postTable tbody tr:hover {
                        background-color: #f8fbff;
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
                    <table id="postTable" class="table mb-0">
                        <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ __('index.post_name') }}</th>
                            <th>{{ __('index.department') }}</th>
                            <th>{{ __('index.branch') }}</th>
                            <th class="text-center">{{ __('index.total_employee') }}</th>
                            <th class="text-center" style="width: 140px;">{{ __('index.status') }}</th>

                            @canany(['edit_post','delete_post'])
                                <th class="text-center" style="width: 100px;">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($posts as $key => $value)
                            @php
                                $postDepts = isset($value->departments)
                                    ? $value->departments->unique('dept_name')->pluck('dept_name')->join('; ')
                                    : ($value->department->dept_name ?? '');
                                $postBranches = isset($value->branches)
                                    ? $value->branches->pluck('name')->join('; ')
                                    : ($value->branch->name ?? '');
                            @endphp
                            <tr data-post-name="{{ $value->post_name }}"
                                data-departments="{{ $postDepts }}"
                                data-branches="{{ $postBranches }}"
                                data-employees="{{ $value->employees_count }}"
                                data-status="{{ $value->is_active == 1 ? 'Active' : 'Inactive' }}">
                                <td class="text-muted small fw-medium">{{ ($posts->firstItem() ?? 0) + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                            <i class="link-icon" data-feather="briefcase" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark">{{ ucfirst($value->post_name) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(isset($value->departments) && $value->departments->count() > 0)
                                        @php
                                            $uniqueDepts = $value->departments->unique('dept_name')->values();
                                            $deptCount = $uniqueDepts->count();
                                            $displayDepts = $uniqueDepts->take(3);
                                            $remainingDeptCount = $deptCount - 3;
                                        @endphp
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            @foreach($displayDepts as $deptItem)
                                                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px; font-weight: 500;">
                                                    {{ $deptItem->dept_name }}
                                                </span>
                                            @endforeach
                                            @if($remainingDeptCount > 0)
                                                <span class="badge bg-secondary text-white px-2 py-1"
                                                      title="{{ $uniqueDepts->slice(3)->pluck('dept_name')->join(', ') }}"
                                                      style="font-size: 11px; cursor: help;">
                                                    +{{ $remainingDeptCount }} more
                                                </span>
                                            @endif
                                        </div>
                                    @elseif($value->department)
                                        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px; font-weight: 500;">
                                            {{ $value->department->dept_name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">{{ __('index.not_available') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($value->branches) && $value->branches->count() > 0)
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            @foreach($value->branches as $branchItem)
                                                <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 11px; font-weight: 500;">
                                                    {{ $branchItem->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($value->branch)
                                        <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 11px; font-weight: 500;">
                                            {{ $value->branch->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">{{ __('index.not_available') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 showEmployeeBtn"
                                            data-post-name="{{ $value->post_name }}"
                                            data-employee='@json($value->employees)'
                                            title="View {{ $value->employees_count }} employees in this position">
                                        <i class="link-icon" data-feather="users" style="width: 14px; height: 14px;"></i>
                                        <span class="fw-bold">{{ $value->employees_count }}</span>
                                    </button>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <span class="badge {{ $value->is_active == 1 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} px-2 py-1" style="font-size: 11px; font-weight: 600;">
                                            {{ $value->is_active == 1 ? __('index.active') : __('index.inactive') }}
                                        </span>
                                        <label class="switch mb-0">
                                            <input class="toggleStatus" href="{{ route('admin.posts.toggle-status', $value->id) }}"
                                                   type="checkbox" {{ ($value->is_active == 1) ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </td>

                                @canany(['edit_post','delete_post'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center gap-2">
                                            @can('edit_post')
                                                <li>
                                                    <a href="{{ route('admin.posts.edit', $value->id) }}"
                                                       class="btn btn-sm btn-outline-primary rounded-circle p-1 d-flex align-items-center justify-content-center shadow-none"
                                                       style="width: 32px; height: 32px;"
                                                       title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_post')
                                                <li>
                                                    @if($value->employees_count > 0)
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary rounded-circle p-1 d-flex align-items-center justify-content-center shadow-none"
                                                                style="width: 32px; height: 32px; opacity: 0.45; cursor: not-allowed;"
                                                                title="Cannot delete: {{ $value->employees_count }} employee(s) assigned">
                                                            <i class="link-icon" data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    @else
                                                        <a class="btn btn-sm btn-outline-danger rounded-circle p-1 d-flex align-items-center justify-content-center deletePost shadow-none"
                                                           href="#"
                                                           data-href="{{ route('admin.posts.delete', $value->id) }}"
                                                           style="width: 32px; height: 32px;"
                                                           title="{{ __('index.delete') }}">
                                                            <i class="link-icon"  data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                        </a>
                                                    @endif
                                                </li>
                                            @endcan
                                        </ul>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <div class="text-center py-5">
                                        <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                            <i class="link-icon" data-feather="briefcase" style="width: 32px; height: 32px;"></i>
                                        </div>
                                        <h6 class="fw-semibold text-secondary mb-1">{{ __('index.no_records_found') }}</h6>
                                        <p class="text-muted small mb-3">No positions found matching your filter criteria.</p>
                                        @can('create_post')
                                            <a href="{{ route('admin.posts.create') }}" class="btn btn-sm btn-primary rounded-3 px-3">
                                                <i class="link-icon" data-feather="plus" style="width: 14px; height: 14px;"></i>
                                                <span>{{ __('index.add_post') }}</span>
                                            </a>
                                        @endcan
                                    </div>
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
