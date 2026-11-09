@extends('layouts.master')

@section('title', __('index.department'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/department-directory.css') }}?v={{ filemtime(public_path('assets/css/department-directory.css')) }}">
@endsection

@section('main-content')

    <section class="content department-directory">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.departments.index')}}">{{ __('index.department_section') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.departments') }}</li>
            </ol>


        </nav>

        @include('admin.department.common.directory-filters')

        <div id="departmentListSection">
        <div class="card department-results-card">
            @include('admin.department.common.directory-toolbar')
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="departmentTable" class="table">
                        <thead>
                        <tr>
                            @include('admin.department.common.sort-heading', ['column' => 'id', 'label' => '#'])
                            @include('admin.department.common.sort-heading', ['column' => 'name', 'label' => __('index.name')])
                            @include('admin.department.common.sort-heading', ['column' => 'head', 'label' => __('index.department_head')])
                            @include('admin.department.common.sort-heading', ['column' => 'employees', 'label' => __('index.total_employees'), 'class' => 'text-center'])
                            @include('admin.department.common.sort-heading', ['column' => 'address', 'label' => __('index.address')])
                            @include('admin.department.common.sort-heading', ['column' => 'branch', 'label' => __('index.branch_name'), 'class' => 'text-center'])
                            @include('admin.department.common.sort-heading', ['column' => 'status', 'label' => __('index.status'), 'class' => 'text-center'])

                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($departments as $key => $value)
                            @include('admin.department.common.directory-row')
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

        @include('admin.department.common.details-modal')
        @include('admin.department.common.employees-modal')

        @include('admin.department.common.delete-modal')
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $("#branch_id").select2({
                placeholder: $("#branch_id").data('placeholder') || @json(__('index.select_branch')),
                allowClear: true,
                width: '100%'
            });
            $("#is_active").select2({});
            $("#employee_status").select2({minimumResultsForSearch: Infinity});
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

            let departmentSearchTimer = null;
            let departmentListController = null;
            let departmentListRequestId = 0;

            const setDepartmentLoading = loading => {
                document.getElementById('departmentListSection').setAttribute('aria-busy', String(loading));
                document.getElementById('departmentListLoading').hidden = !loading;
                document.getElementById('departmentReload').disabled = loading;
                document.querySelector('#departmentTable tbody').style.opacity = loading ? '0.5' : '1';
            };
            const replaceDepartmentResults = doc => {
                const nextBody = doc.querySelector('#departmentTable tbody');
                if (!nextBody) return false;
                document.querySelector('#departmentTable tbody').innerHTML = nextBody.innerHTML;
                for (const selector of ['#departmentTable thead', '#departmentListSection .dataTables_paginate', '#departmentResultCount', '#departmentResultMeta', '#departmentAppliedFilterCount']) {
                    const current = document.querySelector(selector);
                    const next = doc.querySelector(selector);
                    if (current && next) current.innerHTML = next.innerHTML;
                }
                document.dispatchEvent(new CustomEvent('department:table-updated'));
                if (window.feather) feather.replace();
                return true;
            };
            const loadDepartmentResults = async requestUrl => {
                const requestId = ++departmentListRequestId;
                departmentListController?.abort();
                departmentListController = new AbortController();
                setDepartmentLoading(true);
                try {
                    const response = await fetch(requestUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: departmentListController.signal});
                    if (!response.ok) throw new Error('Unable to refresh departments');
                    const html = await response.text();
                    if (requestId !== departmentListRequestId) return;
                    if (!replaceDepartmentResults(new DOMParser().parseFromString(html, 'text/html'))) {
                        window.location.href = requestUrl;
                        return;
                    }
                    window.history.replaceState({}, '', requestUrl);
                } catch (error) {
                    if (error.name !== 'AbortError' && requestId === departmentListRequestId) window.location.href = requestUrl;
                } finally {
                    if (requestId === departmentListRequestId) {
                        setDepartmentLoading(false);
                        departmentListController = null;
                    }
                }
            };
            const refreshDepartmentList = (page = 1) => {
                clearTimeout(departmentSearchTimer);
                const form = document.getElementById('departmentFilterForm');
                const params = new URLSearchParams(new FormData(form));
                if (Number.isInteger(page) && page > 1) params.set('page', page);
                loadDepartmentResults(`${form.action}?${params.toString()}`);
            };
            for (const event of ['department:deleted', 'department:employees-changed', 'department:positions-changed']) {
                document.addEventListener(event, () => refreshDepartmentList());
            }
            $(document).on('submit', '#departmentFilterForm', event => {
                event.preventDefault(); refreshDepartmentList();
            });
            $(document).on('change', '#per_page, #employee_status, #is_active, #branch_id', () => refreshDepartmentList());
            $(document).on('click', '#departmentReload', () => refreshDepartmentList(Number(new URLSearchParams(window.location.search).get('page')) || 1));
            $(document).on('input', '#departmentListSearch', function () {
                document.getElementById('departmentSearch').value = this.value;
                document.getElementById('departmentClearSearch').hidden = !this.value;
                clearTimeout(departmentSearchTimer);
                departmentSearchTimer = setTimeout(() => refreshDepartmentList(), 300);
            });
            $(document).on('click', '#departmentClearSearch', () => {
                document.getElementById('departmentListSearch').value = '';
                document.getElementById('departmentSearch').value = '';
                document.getElementById('departmentClearSearch').hidden = true;
                refreshDepartmentList();
            });
            $(document).on('click', '.department-sort-link', function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                const form = document.getElementById('departmentFilterForm');
                form.elements.namedItem('sort_by').value = this.dataset.sort;
                form.elements.namedItem('sort_direction').value = this.dataset.direction;
                refreshDepartmentList();
            });
            $(document).on('click', '#departmentListSection .pagination a', function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault(); loadDepartmentResults(this.href);
            });
        });
    </script>
    <script src="{{ asset('assets/js/department-delete.js') }}?v={{ filemtime(public_path('assets/js/department-delete.js')) }}"></script>
    <script src="{{ asset('assets/js/department-employees.js') }}?v={{ filemtime(public_path('assets/js/department-employees.js')) }}"></script>
    <script src="{{ asset('assets/js/department-directory.js') }}?v={{ filemtime(public_path('assets/js/department-directory.js')) }}"></script>
    <script src="{{ asset('assets/js/department-table-tools.js') }}?v={{ filemtime(public_path('assets/js/department-table-tools.js')) }}"></script>
@endsection
