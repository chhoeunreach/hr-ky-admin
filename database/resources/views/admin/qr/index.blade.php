
@extends('layouts.master')

@section('title',__('index.qr'))
@section('styles')
    <style>
        .qr > svg {
            height: 100px;
            width: 100px;
        }
    </style>
@endsection
@section('button')
    @can('create_qr')
        <a href="{{ route('admin.qr.create')}}">
            <button class="btn btn-primary add_qr">
                <i class="link-icon" data-feather="plus"></i>@lang('index.add_qr')
            </button>
        </a>
    @endcan
@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.qr.common.breadcrumb')



        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.branch')</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.qr_image')</th>
                            <th class="text-center">@lang('index.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($qrData as $qr)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $qr?->branch?->name }}</td>
                                <td>{{ $qr->title }}</td>
                                <td class="qr_code">
                                    <div class="qr">{!! $qr->qr_code !!}</div>
                                </td>

                                <td class="text-center">
                                    <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                        <li class="me-2">
                                            <a href="{{route('admin.qr.print',$qr->id)}}" target="_blank" class="text-success" title="@lang('index.print')">
                                                <i class="link-icon" data-feather="printer"></i>
                                            </a>
                                        </li>
                                        @can('edit_qr')
                                            <li class="me-2">
                                                <a href="{{route('admin.qr.edit',$qr->id)}}" class="text-warning" title="@lang('index.edit') ">
                                                    <i class="link-icon" data-feather="edit"></i>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('delete_qr')
                                            <li class="me-2">
                                                <a class="deleteQR"
                                                   data-href="{{route('admin.qr.destroy',$qr->id)}}" title="@lang('index.delete')">
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
                                    <p class="text-center"><b>@lang('index.no_records_found')</b></p>
                                </td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

{{--        <div class="dataTables_paginate mt-3">--}}
{{--            {{$qr->appends($_GET)->links()}}--}}
{{--        </div>--}}



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


            $('.deleteQR').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '@lang('index.delete_confirmation')',
                    showDenyButton: true,
                    confirmButtonText: `@lang('index.yes')`,
                    denyButtonText: `@lang('index.no')`,

                    padding:'10px 50px 10px 50px',
                    // width:'1000px',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })

            //
            // window.print();
            // window.onfocus = function () {
            //     window.close();
            // }
        });

    </script>
@endsection






