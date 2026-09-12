@extends('layouts.master')

@section('title', __('index.department'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.departments.index')}}">{{ __('index.department_section') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.departments') }}</li>
            </ol>

            @can('create_department')
                <a href="{{ route('admin.departments.create')}}">
                    <button class="btn btn-primary add_department">
                        <i class="link-icon" data-feather="plus"></i>{{ __('index.add_department') }}
                    </button>
                </a>
            @endcan
        </nav>

        @php
            $hasDepartmentFilters = filled($filterParameters['branch'] ?? null)
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
                            data-bs-target="#departmentFilterCollapse"
                            aria-expanded="{{ $hasDepartmentFilters ? 'true' : 'false' }}"
                            aria-controls="departmentFilterCollapse">
                        <i class="link-icon" data-feather="filter"></i>
                        {{ __('index.filter') }}
                    </button>
                    <h6 class="card-title mb-0">{{ __('index.department_lists') }}</h6>
                </div>
            </div>
            <div id="departmentFilterCollapse" class="collapse{{ $hasDepartmentFilters ? ' show' : '' }}">
            <form class="forms-sample card-body pb-0" action="{{ route('admin.departments.index') }}" id="departmentFilterForm" method="get">
                <input type="hidden" id="departmentSearch" name="search" value="{{ $filterParameters['search'] ?? '' }}">
                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <select class="form-control" id="branch_id" name="branch">
                            <option value="" {{ empty($filterParameters['branch']) ? 'selected' : '' }}>{{ __('index.select_branch') }}</option>
                            @foreach($branch as $key => $value)
                                <option value="{{ $value->id }}" {{ (isset($filterParameters['branch']) && $value->id == $filterParameters['branch'] ) ? 'selected' : '' }}>
                                    {{ ucfirst($value->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-xxl-3 col-xl-3 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.search_by_department_name') }}" name="name" value="{{$filterParameters['name']}}" class="form-control">
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
                            <button type="submit" class="btn btn-block btn-success mb-4">{{ __('index.filter') }}</button>
                            <a class="btn btn-block btn-primary mb-4" href="{{ route('admin.departments.index') }}">{{ __('index.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </div>

        <div id="departmentListSection">
        <div class="card">
            <div class="card-header">
                <div class="department-toolbar">
                    <div class="department-toolbar-left">
                        <h6 class="card-title mb-0">{{ __('index.department_lists') }}</h6>
                        <div class="department-entry-control">
                            <span>Show</span>
                            <select class="form-control department-entry-select" id="per_page" name="per_page" form="departmentFilterForm">
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
                    <div class="department-toolbar-search">
                        <input type="text"
                               id="departmentListSearch"
                               class="department-list-search"
                               value="{{ $filterParameters['search'] ?? '' }}"
                               placeholder="Search ...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <style>
                    .department-toolbar {
                        display: grid;
                        grid-template-columns: auto 1fr;
                        align-items: center;
                        gap: 16px;
                    }

                    .department-toolbar-left {
                        display: flex;
                        align-items: center;
                        gap: 16px;
                        flex-wrap: wrap;
                    }

                    .department-toolbar-search {
                        display: flex;
                        justify-content: flex-end;
                    }

                    .department-entry-control {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        color: #111827;
                        font-weight: 500;
                    }

                    .department-entry-select {
                        min-width: 120px;
                    }

                    .department-list-search {
                        width: min(100%, 250px);
                        border: 1px solid #d7dfeb;
                        border-radius: 14px;
                        min-height: 44px;
                        padding: 0 14px;
                        color: #111827;
                        background: #f8fbff;
                        box-shadow: none;
                    }

                    .department-list-search:focus {
                        outline: none;
                        border-color: #93c5fd;
                        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
                    }

                    @media (max-width: 767.98px) {
                        .department-toolbar {
                            grid-template-columns: 1fr;
                        }

                        .department-toolbar-search {
                            justify-content: stretch;
                        }

                        .department-list-search {
                            width: 100%;
                        }
                    }
                </style>
                <div class="table-responsive">
                    <table id="departmentTable" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.name') }}</th>
                            <th>{{ __('index.department_head') }}</th>
                            <th class="text-center">{{ __('index.total_employees') }}</th>
                            <th>{{ __('index.address') }}</th>
                            <th>{{ __('index.phone') }}</th>
                            <th class="text-center">{{ __('index.branch_name') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>

                            @canany(['edit_department','delete_department'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($departments as $key => $value)
                            <tr>
                                <td>{{ ($departments->firstItem() ?? 0) + $key }}</td>
                                <td>{{ ucfirst($value->dept_name) }}</td>
                                <td>{{ isset($value->departmentHead) ? $value->departmentHead->name : __('index.not_available') }}</td>
                                <td class="text-center">
                                    <p class="btn btn-info btn-sm mb-0" id="showDepartmentEmployees" data-employee='@json($value->employees)'>
                                        {{ $value->employees_count }}
                                    </p>
                                </td>
                                <td>{{ $value->address }}</td>
                                <td>{{ $value->phone }}</td>
                                <td class="text-center">{{ $value->branch->name }}</td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{ route('admin.departments.toggle-status', $value->id) }}"
                                               type="checkbox" {{ ($value->is_active) == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['edit_department','delete_department'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('edit_department')
                                                <li class="me-2">
                                                    <a href="{{ route('admin.departments.edit', $value->id) }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_department')
                                                <li>
                                                    <a class="deleteBranch"
                                                       data-href="{{ route('admin.departments.delete', $value->id) }}">
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

        <div class="dataTables_paginate mt-3">
            {{ $departments->appends($_GET)->links() }}
        </div>
        </div>

        <div class="modal fade" id="showEmployees" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                    </div>
                    <div class="modal-body">
                        <div class="row employeeList">
                        </div>
                        <p class="postEmptyCase d-none">{{ __('index.post_empty') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $("#branch_id").select2({
                placeholder: @json(__('index.select_branch'))
            });
            $("#is_active").select2({});
            $("#per_page").select2({minimumResultsForSearch: Infinity});

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('change', '.toggleStatus', function (event) {
                event.preventDefault();
                var status = $(this).prop('checked') === true ? 1 : 0;
                var href = $(this).attr('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_change_status') }}',
                    showDenyButton: true,
                    confirmButtonText: `{{ __('index.yes') }}`,
                    denyButtonText: `{{ __('index.no') }}`,
                    padding:'10px 50px 10px 50px',
                    // width:'500px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    } else if (result.isDenied) {
                        (status === 0) ? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $(document).on('click', '.deleteBranch', function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_delete_department') }}',
                    showDenyButton: true,
                    confirmButtonText: `{{ __('index.yes') }}`,
                    denyButtonText: `{{ __('index.no') }}`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            });

            $('body').on('click', '#showDepartmentEmployees', function (event) {
                event.preventDefault();
                let employee = $(this).data('employee');
                $('.employee').remove();
                $('.modal-title').html('{{ __("index.employee_list_title") }}');

                if (employee.length > 0) {
                    $('.postEmptyCase').addClass('d-none');
                    employee.forEach(function (data) {
                        let avatar = data.avatar ? '{{ asset(\App\Models\User::AVATAR_UPLOAD_PATH) }}' + '/' + data.avatar : '{{ asset('assets/images/img.png') }}';
                        $('.employeeList').append(
                            '<div class="col-lg-6 d-flex align-items-center mb-3 employee">' +
                            '<img class="rounded-circle w-25 me-2 employeeImage" style="object-fit: cover" src="' + avatar + '" alt="profile">' +
                            '<span class="employeeName">' + data.name + '</span>' +
                            '</div>'
                        );
                    });
                } else {
                    $('.postEmptyCase').removeClass('d-none');
                }

                $('#showEmployees').modal('show');
            });

            let departmentSearchTimer = null;
            let departmentListController = null;
            let departmentListRequestId = 0;

            const initDepartmentListControls = () => {
                const perPage = $('#per_page');

                if (perPage.length && !perPage.hasClass('select2-hidden-accessible')) {
                    perPage.select2({minimumResultsForSearch: Infinity});
                }
            };

            const replaceDepartmentResults = (doc) => {
                const currentTableBody = document.querySelector('#departmentTable tbody');
                const nextTableBody = doc.querySelector('#departmentTable tbody');
                const currentPagination = document.querySelector('#departmentListSection .dataTables_paginate');
                const nextPagination = doc.querySelector('#departmentListSection .dataTables_paginate');

                if (!currentTableBody || !nextTableBody) {
                    return false;
                }

                currentTableBody.innerHTML = nextTableBody.innerHTML;

                if (currentPagination && nextPagination) {
                    currentPagination.innerHTML = nextPagination.innerHTML;
                }

                if (window.feather) {
                    feather.replace();
                }

                return true;
            };

            const refreshDepartmentList = () => {
                const form = document.getElementById('departmentFilterForm');
                const listSection = document.getElementById('departmentListSection');
                const tableBody = document.querySelector('#departmentTable tbody');

                if (!form || !listSection) {
                    form?.submit();
                    return;
                }

                const params = new URLSearchParams(new FormData(form));
                const requestUrl = `${form.action}?${params.toString()}`;
                const requestId = ++departmentListRequestId;

                if (departmentListController) {
                    departmentListController.abort();
                }

                departmentListController = new AbortController();

                if (tableBody) {
                    tableBody.style.opacity = '0.6';
                }

                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: departmentListController.signal
                })
                    .then((response) => response.text())
                    .then((html) => {
                        if (requestId !== departmentListRequestId) {
                            return;
                        }

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        if (!replaceDepartmentResults(doc)) {
                            window.location.href = requestUrl;
                            return;
                        }

                        window.history.replaceState({}, '', requestUrl);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        form.submit();
                    })
                    .finally(() => {
                        if (requestId === departmentListRequestId) {
                            if (tableBody) {
                                tableBody.style.opacity = '1';
                            }
                            departmentListController = null;
                        }
                    });
            };

            $(document).on('submit', '#departmentFilterForm', function (event) {
                event.preventDefault();
                refreshDepartmentList();
            });

            $(document).on('change', '#per_page', function () {
                refreshDepartmentList();
            });

            $(document).on('input', '#departmentListSearch', function () {
                const searchInput = document.getElementById('departmentSearch');
                if (searchInput) {
                    searchInput.value = this.value;
                }

                clearTimeout(departmentSearchTimer);
                departmentSearchTimer = setTimeout(() => {
                    refreshDepartmentList();
                }, 300);
            });

            $(document).on('click', '#departmentListSection .pagination a', function (event) {
                event.preventDefault();

                const form = document.getElementById('departmentFilterForm');
                const tableBody = document.querySelector('#departmentTable tbody');
                const requestUrl = this.href;
                const requestId = ++departmentListRequestId;

                if (!form) {
                    window.location.href = requestUrl;
                    return;
                }

                if (departmentListController) {
                    departmentListController.abort();
                }

                departmentListController = new AbortController();

                if (tableBody) {
                    tableBody.style.opacity = '0.6';
                }

                fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: departmentListController.signal
                })
                    .then((response) => response.text())
                    .then((html) => {
                        if (requestId !== departmentListRequestId) {
                            return;
                        }

                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        if (!replaceDepartmentResults(doc)) {
                            window.location.href = requestUrl;
                            return;
                        }

                        window.history.replaceState({}, '', requestUrl);
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        window.location.href = requestUrl;
                    })
                    .finally(() => {
                        if (requestId === departmentListRequestId) {
                            if (tableBody) {
                                tableBody.style.opacity = '1';
                            }
                            departmentListController = null;
                        }
                    });
            });

            initDepartmentListControls();
        });
    </script>
@endsection
