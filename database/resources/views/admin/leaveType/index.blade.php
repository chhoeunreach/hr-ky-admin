
@extends('layouts.master')

@section('title',__('index.leave_type'))

@section('action',__('index.lists'))

@section('button')
    @canany(['leave_type_create','access_admin_leave'])
        <a href="{{ route('admin.leaves.create')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.add_leave_type') }}
            </button>
        </a>
    @endcan
@endsection


@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveType.common.breadcrumb')
        <div class="row">
{{--            <div class="col-lg-2">--}}
{{--                @include('admin.leaveRequest.common.leave_menu')--}}
{{--            </div>--}}
{{--            <div class="col-lg-10">--}}
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('index.type') }}</th>
                                    <th>{{ __('index.is_paid') }}</th>
                                    <th class="text-center">{{ __('index.allocated_days') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    @canany(['leave_type_edit','leave_type_delete','access_admin_leave'])
                                        <th class="text-center">{{ __('index.action') }}</th>
                                    @endcanany
                                </tr>
                                </thead>
                                <tbody>
                                <tr>

                                @forelse($leaveTypes as $key => $value)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{ucfirst($value->name)}}</td>
                                        <td>{{($value->leave_allocated) ? __('index.yes'):__('index.no')}}</td>
                                        <td class="text-center">{{($value->leave_allocated) ?? '-'}}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.leaves.toggle-status',$value->id)}}"
                                                       type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        @canany(['leave_type_edit','leave_type_delete','access_admin_leave'])
                                            <td class="text-center">
                                                <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                                    @canany(['leave_type_edit','access_admin_leave'])
                                                        <li class="me-2">
                                                            <a href="{{route('admin.leaves.edit',$value->id)}}" title="{{ __('index.edit_leave_type_detail') }}">
                                                                <i class="link-icon" data-feather="edit"></i>
                                                            </a>
                                                        </li>
                                                    @endcanany

                                                    @canany(['leave_type_delete','access_admin_leave'])
                                                        <li>
                                                            <a class="deleteLeaveType"
                                                               data-href="{{route('admin.leaves.delete',$value->id)}}" title="{{ __('index.delete_leave_type') }}">
                                                                <i class="link-icon"  data-feather="delete"></i>
                                                            </a>
                                                        </li>
                                                    @endcanany
                                                </ul>
                                            </td>
                                    @endcanany


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
{{--            </div>--}}
        </div>


    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
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
                    title: `{{ __('index.change_status_confirm') }}`,
                    showDenyButton: true,
                    confirmButtonText: `{{__('index.yes')}}`,
                    denyButtonText: `{{__('index.no')}}`,
                    padding:'10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteLeaveType').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: `{{__('index.delete_leave_confirmation')}}`,
                    showDenyButton: true,
                    confirmButtonText: `{{__('index.yes')}}`,
                    denyButtonText: `{{__('index.no')}}`,
                    padding:'10px 50px 10px 50px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        });

    </script>
@endsection






