@extends('layouts.master')

@section('title', __('index.title_branch'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.branch.common.breadcrumb', ['title' => __('index.branch')])
        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'branchDirectoryFilters', 'directoryExpanded' => false])
            <div id="branchDirectoryFilters" class="collapse">
                <form id="branchFilterForm" class="directory-filter-form" action="{{ route('admin.branch.index') }}" method="get">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label for="branchNameFilter" class="form-label">{{ __('index.branch_name') }}</label>
                            <input id="branchNameFilter" type="text" placeholder="{{ __('index.search_by_branch_name') }}" name="name" value="{{ $filterParameters['name'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                    @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.branch.index')])
                </form>
            </div>
        </div>

        <div class="card">
            @include('admin.section.directory-toolbar', ['directoryTable' => 'branchDirectoryTable', 'directoryForm' => 'branchFilterForm', 'directorySearch' => 'branchListSearch', 'directoryRecords' => $branches, 'directoryTitle' => __('index.branch_lists'), 'directoryIcon' => 'map-pin', 'directoryPermission' => 'create_branch', 'directoryAddUrl' => route('admin.branch.create'), 'directoryDefaultSize' => '10', 'directorySearchKey' => 'name', 'directoryLive' => false])
            <div class="card-body">
                <div class="table-responsive">
                    <table id="branchDirectoryTable" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.branch_logo') }}</th>
                            <th>{{ __('index.branch_name') }}</th>
                            <th>{{ __('index.address') }}</th>
                            <th class="text-center">{{ __('index.phone') }}</th>
                            <th class="text-center">{{ __('index.payment_qr_codes') }}</th>
                            <th class="text-center">{{ __('index.total_employee') }}</th>
                            <th class="text-center">{{ __('index.status') }}</th>
                            @canany(['edit_branch','delete_branch'])
                                <th class="text-center" data-directory-actions>{{ __('index.action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($branches as $key => $value)
                            <tr>
                                <td>{{ $branches->firstItem() + $key }}</td>
                                <td>
                                    @if($value->logo)
                                        <img src="{{ asset(\App\Models\Branch::UPLOAD_PATH.$value->logo) }}"
                                             alt="{{ $value->name }}"
                                             style="object-fit: contain"
                                             class="ht-50 wd-50">
                                    @else
                                        {{ __('index.not_available') }}
                                    @endif
                                </td>
                                <td>{{ucfirst($value->name)}}</td>
                                <td class="directory-address-cell"><details><summary>{{ \Illuminate\Support\Str::limit($value->address, 70) }}</summary><div>{{ $value->address }}</div></details></td>
                                <td class="text-center">{{$value->phone}}</td>
                                <td class="text-center">
                                    @if(!empty($value->payment_qr_codes))
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            @foreach($value->payment_qr_codes as $paymentQrCode)
                                                <div>
                                                    <img src="{{ asset(\App\Models\Branch::UPLOAD_PATH.$paymentQrCode['qr_code']) }}"
                                                         alt="{{ $paymentQrCode['payment_name'] }}"
                                                         style="object-fit: contain"
                                                         class="ht-50 wd-50 d-block mx-auto">
                                                    <small>{{ $paymentQrCode['payment_name'] }}</small>
                                                    @if(!empty($paymentQrCode['payment_link']))
                                                        @php
                                                            $paymentLink = trim($paymentQrCode['payment_link']);
                                                            $paymentHref = preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//', $paymentLink)
                                                                ? $paymentLink
                                                                : 'https://' . $paymentLink;
                                                        @endphp
                                                        <a href="{{ $paymentHref }}"
                                                           target="_blank"
                                                           rel="noopener"
                                                           class="d-block small text-primary">
                                                            {{ __('index.payment_link') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ __('index.not_available') }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <p class="btn btn-info btn-sm mb-0" id="showBranchEmployees" data-employee='@json($value->employees)'>
                                        {{ $value->employees_count }}
                                    </p>
                                </td>
                                <td class="text-center">
                                    <label class="switch">
                                        <input class="toggleStatus" href="{{route('admin.branch.toggle-status',$value->id)}}"
                                               type="checkbox" {{($value->is_active) == 1 ?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>

                                @canany(['edit_branch','delete_branch'])
                                    <td class="text-center">
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            @can('edit_branch')
                                                <li class="me-2">
                                                    <a href="{{route('admin.branch.edit',$value->id)}}">
                                                        <i class="link-icon" data-feather="edit"></i>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_branch')
                                                <li>
                                                    <a class="deleteBranch" data-href="{{route('admin.branch.delete',$value->id)}}"><i class="link-icon"  data-feather="delete"></i></a>
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

        <div class="dataTables_paginate">
            {{$branches->appends($_GET)->links()}}
        </div>

        <div class="modal fade" id="showEmployees" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                    </div>
                    <div class="modal-body">
                        <div class="row employeeList"></div>
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
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }else if (result.isDenied) {
                        (status === 0)? $(this).prop('checked', true) :  $(this).prop('checked', false)
                    }
                })
            })

            $('.deleteBranch').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '{{ __('index.are_you_sure_delete_branch') }}',
                    showDenyButton: true,
                    confirmButtonText: `Yes`,
                    denyButtonText: `No`,
                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            });

            $('body').on('click', '#showBranchEmployees', function (event) {
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
