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

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.department_lists') }}</h6>
            </div>
            <form class="forms-sample card-body pb-0" action="{{route('admin.departments.index')}}" method="get">
                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                    <div class="col-lg-4 col-md-6 mb-4">
                        <select class="form-select form-select-lg" id="branch_id" name="branch">
                            <option {{ !isset($filterParameters['branch']) ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}</option>
                            @foreach($branch as $key => $value)
                                <option value="{{ $value->id }}" {{ (isset($filterParameters['branch']) && $value->id == $filterParameters['branch'] ) ? 'selected' : '' }}>
                                    {{ ucfirst($value->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-lg-4 col-md-6 mb-4">
                        <input type="text" placeholder="{{ __('index.search_by_department_name') }}" name="name" value="{{$filterParameters['name']}}" class="form-control">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <select class="form-select form-select-lg" name="per_page">
                            <option value="25" {{ (string) ($filterParameters['per_page'] ?? '') === '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ (string) ($filterParameters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                            <option value="10" {{ (string) ($filterParameters['per_page'] ?? '') === '10' ? 'selected' : '' }}>10</option>
                            <option value="all" {{ (string) ($filterParameters['per_page'] ?? '') === 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success me-md-2 mb-4">{{ __('index.filter') }}</button>

                        <a class="btn btn-block btn-primary me-md-2 me-0 mb-4" href="{{ route('admin.departments.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.department_lists') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
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

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.toggleStatus').change(function (event) {
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

            $('.deleteBranch').click(function (event) {
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
        });
    </script>
@endsection
