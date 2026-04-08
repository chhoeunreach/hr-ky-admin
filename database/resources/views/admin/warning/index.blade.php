@extends('layouts.master')

@section('title',__('index.warning'))

@section('action',__('index.lists'))

@section('button')
    @can('create_warning')
        <a href="{{ route('admin.warning.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_warning') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.warning.common.breadcrumb')

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.warning_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.subject') }}</th>
                            <th class="text-center">{{ __('index.employees') }}</th>
                            <th class="text-center">{{ __('index.warning_date') }}</th>
                            @canany(['show_warning','delete_warning','update_warning'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($warningLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value->subject }}</td>
                                    <td class="text-center">
                                        <a
                                           onclick="showEmployees({{ json_encode($value->warningEmployee, JSON_HEX_APOS) }})"
                                           title="{{ __('index.employee_list_title') }}">
                                            <i class="link-icon" data-feather="users"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ \App\Helpers\AppHelper::formatDateForView($value->warning_date) }}
                                    </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_warning')
                                                <li class="me-2">
                                                    <a href="{{route('admin.warning.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_warning')
                                                <li class="me-2">
                                                    <a href="{{route('admin.warning.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_warning')
                                                <li>
                                                    <a class="deleteWarning"
                                                       data-title="{{$value->subject}} Detail"
                                                       data-href="{{route('admin.warning.delete',$value->id)}}"
                                                       title="{{ __('index.delete') }}">
                                                        <i class="link-icon"  data-feather="delete"></i>
                                                    </a>
                                                </li>
                                            @endcan
                                          </ul>
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
            {{ $warningLists->appends($_GET)->links() }}
        </div>
    </section>
    @include('admin.warning.employee')

@endsection

@section('scripts')
    @include('admin.warning.common.scripts')

    <script>
        function showEmployees(data) {
            console.log(data);
            if (data && data.length > 0) {
                $('.warning_employee_id').empty();

                let employeeList = '<ul>';
                data.forEach(warning => {
                    if (warning.employee && warning.employee.name) {
                        employeeList += `<li>${warning.employee.name}</li>`;
                    }
                });
                employeeList += '</ul>';

                $('.warning_employee_id').html(employeeList);
                $('.warningEmployeeTitle').text('@lang('index.employee_list_title')');

                // Use Bootstrap 5 modal method
                const modal = new bootstrap.Modal(document.getElementById('warningEmployeeDetail'));
                modal.show();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Employees Not Found',
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    </script>
@endsection

