@extends('layouts.master')

@section('title',__('index.training'))

@section('action',__('index.lists'))

@section('button')
    @can('create_training')
        <a href="{{ route('admin.training.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_training') }}
            </button>
        </a>
    @endcan
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.trainingManagement.training.common.breadcrumb')

        <div class="card support-main">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.training_list') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.training') }}</th>
                            <th class="text-center">{{ __('index.employees') }}</th>
{{--                            <th class="text-center">{{ __('index.trainer') }}</th>--}}
                            <th class="text-center">{{ __('index.date') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['show_training','delete_training','update_training'])
                                <th class="text-center">{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            $color = [
                                \App\Enum\TrainingStatusEnum::completed->value => 'primary',
                                \App\Enum\TrainingStatusEnum::ongoing->value => 'success',
                                \App\Enum\TrainingStatusEnum::pending->value => 'secondary',
                                \App\Enum\TrainingStatusEnum::cancelled->value => 'warning',
                            ];


                            ?>
                            @forelse($trainingLists as $key => $value)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{ $value?->trainingType?->title }}</td>
                                    <td class="text-center">
                                        <a
                                           onclick="showEmployeeDetails({{ json_encode($value->employeeTraining, JSON_HEX_APOS) }})"
                                           title="{{ __('index.employee_list_title') }}">
                                            <i class="link-icon" data-feather="users"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if(is_null($value->end_date) || strtotime($value->start_date) == strtotime($value->end_date))

                                            {{ \App\Helpers\AppHelper::formatDateForView($value->start_date) }}
                                        @else
                                            {{ \App\Helpers\AppHelper::formatDateForView($value->start_date) }}
                                            - {{ \App\Helpers\AppHelper::formatDateForView($value->end_date) }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                            {{ ucfirst($value->status) }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('update_training')
                                                <li class="me-2">
                                                    <a href="{{route('admin.training.edit',$value->id)}}" title="{{ __('index.edit') }}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('show_training')
                                                <li class="me-2">
                                                    <a href="{{route('admin.training.show',$value->id)}}" title="{{ __('index.show_detail') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_training')
                                                <li>
                                                    <a class="delete"
                                                       data-title="{{$value->name}} Award Detail"
                                                       data-href="{{route('admin.training.delete',$value->id)}}"
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
            {{ $trainingLists->appends($_GET)->links() }}
        </div>
    </section>
    @include('admin.trainingManagement.training.employee')

@endsection

@section('scripts')
    @include('admin.trainingManagement.training.common.scripts')

    <script>
        function showEmployeeDetails(data) {
            if (data && data.length > 0) {
                $('.training_employee_id').empty();

                let employeeList = '<ul class="mb-0">';
                data.forEach(training => {
                    if (training.employee && training.employee.name) {
                        employeeList += `<li>${training.employee.name}</li>`;
                    }
                });
                employeeList += '</ul>';

                $('.training_employee_id').html(employeeList);
                $('.trainingEmployeeTitle').text('@lang('index.employee_list_title')');

                // Use Bootstrap 5 modal method
                const modal = new bootstrap.Modal(document.getElementById('trainingEmployeeDetail'));
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

