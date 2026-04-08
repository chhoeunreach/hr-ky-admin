
@extends('layouts.master')

@section('title',__('index.nfc'))
@section('styles')
    <style>
        .qr > svg {
            height: 100px;
            width: 100px;
        }
    </style>
@endsection
@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">@lang('index.dashboard')</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.nfc.index')}}">@lang('index.nfc_section')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('index.nfc')</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('index.title')</th>
                            <th>@lang('index.created_by')</th>
                            <th class="text-center">@lang('index.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>

                        @forelse($nfcData as $nfc)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $nfc->title }}</td>
                                <td>
                                   {{ $nfc->createdBy?->name }}
                                </td>

                                <td class="text-center">
                                    @can('delete_nfc')
                                        <ul class="d-flex list-unstyled mb-0 justify-content-center">
                                            <li class="me-2">
                                                <a class="deleteNFC"
                                                   data-href="{{route('admin.nfc.destroy',$nfc->id)}}" title="@lang('index.delete')">
                                                    <i class="link-icon"  data-feather="delete"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    @endcan
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


            $('.deleteNFC').click(function (event) {
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

        });

    </script>
@endsection






