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

        <div class="search-box p-4 bg-white rounded mb-3 box-shadow pb-2">
            <form class="forms-sample" action="{{ route('admin.posts.index') }}" method="get">
                <div class="row align-items-center">
                    <div class="col-lg-2 mb-3">
                        <h5>{{ __('index.post_name') }} {{ __('index.posts') }}</h5>
                    </div>

                    <div class="col-lg-4 col-md-4 mb-3">
                        <select class="form-select form-select-lg" name="department">
                            <option value="" {{ !isset($filterParameters['department']) ? 'selected' : '' }}>{{ __('index.search_by_department') }}</option>
                            @foreach($departments as $key => $value)
                                <option value="{{ $key }}" {{ (isset($filterParameters['department']) && $key == $filterParameters['department']) ? 'selected' : '' }}>
                                    {{ ucfirst($value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4 mb-3">
                        <input type="text" placeholder="{{ __('index.search_by_post_name') }}" name="name" value="{{ $filterParameters['name'] }}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-4 d-flex">
                        <button type="submit" class="btn btn-block btn-secondary form-control me-md-2 me-0 mb-3">{{ __('index.filter') }}</button>
                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-3 form-control" href="{{ route('admin.posts.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.post_name') }}</th>
                            <th>{{ __('index.department') }}</th>
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
                                <td>{{ (($posts->currentPage() - 1 ) * (\App\Models\Post::RECORDS_PER_PAGE) + (++$key)) }}</td>
                                <td>{{ ucfirst($value->post_name) }}</td>
                                <td>{{ ucfirst($value->department->dept_name) }}</td>
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

        @include('admin.post.show')

    </section>
@endsection

@section('scripts')
    @include('admin.post.common.scripts')
@endsection
