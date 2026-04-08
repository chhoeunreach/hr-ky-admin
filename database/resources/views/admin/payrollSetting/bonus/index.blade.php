@extends('layouts.master')
@section('title',__('index.salary_components'))
@section('sub_page',__('index.lists'))
@section('page')
        <a href="{{ route('admin.bonus.index')}}">
            {{ __('index.bonus') }}
        </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')

        <div class="row">
            <div class="col-lg-2 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <div class="justify-content-end">
                            @can('add_salary_component')
                                <a class="btn btn-success"
                                   href="{{ route('admin.bonus.create')}}">
                                    <i class="link-icon" data-feather="plus"></i>{{ __('index.add_bonus') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th class="text-center">{{ __('index.value_type') }}</th>
                                    <th class="text-center">{{ __('index.value') }}</th>
                                    <th class="text-center">{{ __('index.status') }}</th>
                                    @can('bonus')
                                    <th class="text-center">{{ __('index.action') }}</th>
                                    @endcan
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                @forelse($bonusList as $key => $value)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{ucfirst($value->title)}}</td>
                                        <td class="text-center">
                                            {{ \App\Enum\BonusTypeEnum::from($value->value_type)->getFormattedName() }}
                                        </td>

                                        <td class="text-center">{{ $value->value  }}{{$value->value_type == \App\Enum\BonusTypeEnum::fixed->value ? '': '%'}}</td>
                                        <td class="text-center">
                                            <label class="switch">
                                                <input class="toggleStatus" href="{{route('admin.bonus.toggle-status',$value->id)}}"
                                                       type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>

                                        @can('bonus')
                                        <td class="text-center">
                                            <ul class="d-flex list-unstyled mb-0 justify-content-center">

                                                    <li class="me-2">
                                                        <a href="{{route('admin.bonus.edit',$value->id)}}"
                                                           title="Edit Detail">
                                                            <i class="link-icon" data-feather="edit"></i>
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="delete"
                                                           data-href="{{route('admin.bonus.delete',$value->id)}}"
                                                           title="Delete">
                                                            <i class="link-icon" data-feather="delete"></i>
                                                        </a>
                                                    </li>
                                            </ul>
                                        </td>
                                        @endcan
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
            </div>
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
                    title: '{{ __('index.change_status_confirm') }}',
                    showDenyButton: true,
                    confirmButtonText: '{{ __('index.yes') }}',
                    denyButtonText: '{{ __('index.no') }}',
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

            $('.delete').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title:`{{ __('index.delete_confirmation') }}`,
                    showDenyButton: true,
                    confirmButtonText: '{{ __('index.yes') }}',
                    denyButtonText: '{{ __('index.no') }}',
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






