@extends('layouts.master')

@section('title', __('index.logout_requests'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.logout_requests') }}</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee_name') }}</th>
                            <th>{{ __('index.logout_request_status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logoutRequests as $key => $value)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td><strong>{{ removeSpecialChars($value->name) }}</strong></td>
                                <td>
                                    <button class="btn btn-primary btn-xs acceptLogoutRequest"
                                            data-href="{{ route('admin.logout-requests.accept', $value->id) }}">
                                        {{ __('index.take_action') }}
                                    </button>
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

            $('.acceptLogoutRequest').click(function (event) {
                event.preventDefault();
                let href = $(this).data('href');
                Swal.fire({
                    title: '{{ __('index.confirm_accept_logout_request') }}',
                    showDenyButton: true,
                    confirmButtonText: `{{ __('index.yes') }}`,
                    denyButtonText: `{{ __('index.no') }}`,
                    padding: '10px 50px 10px 50px',
                    // width:'500px',
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
