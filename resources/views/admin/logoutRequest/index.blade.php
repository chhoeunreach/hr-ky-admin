@extends('layouts.master')

@section('title', __('index.logout_requests'))

@section('main-content')

    <style>
        .logout-request-table { min-width: 1040px; margin-bottom: 0; }
        .logout-request-table thead th { background: #f8fafc; color: #526176; font-size: .72rem; font-weight: 700; padding: .75rem; text-transform: uppercase; white-space: nowrap; }
        .logout-request-table tbody td { border-color: #e7edf2; padding: .8rem .75rem; vertical-align: middle; }
        .logout-employee { display: flex; align-items: center; gap: .65rem; min-width: 210px; }
        .logout-employee img { width: 38px; height: 38px; border-radius: 50%; flex: 0 0 auto; object-fit: cover; }
        .logout-primary { color: #172033; font-weight: 650; line-height: 1.25; }
        .logout-secondary { color: #718096; display: block; font-size: .72rem; line-height: 1.45; overflow-wrap: anywhere; }
        .logout-device-badge { background: #edf7f5; border: 1px solid #cfe9e3; border-radius: 4px; color: #176b5e; display: inline-flex; font-size: .7rem; font-weight: 700; padding: .18rem .4rem; text-transform: uppercase; }
        .logout-action-form { margin: 0; }
    </style>

    <section class="content">

        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.logout_requests') }}</li>
            </ol>
        </nav>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.logout_request_filter')  }}</h6>
            </div>

            <form class="forms-sample card-body pb-0" action="{{ route('admin.logout-requests.index') }}" method="get">
                <div class="row align-items-center">

                    @if(!isset(auth()->user()->branch_id))
                        <div class="col-lg-3 col-md-6 mb-4">
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option  selected  disabled>{{ __('index.select_branch') }}
                                </option>
                                @if(isset($companyDetail))
                                    @foreach($companyDetail->branches()->get() as $key => $branch)
                                        <option value="{{$branch->id}}"
                                            {{ (isset($filterData['branch_id']) && $filterData['branch_id']  == $branch->id) ? 'selected': '' }}>
                                            {{ucfirst($branch->name)}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif


                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="department_id" id="department_id">
                            <option  selected  disabled>{{ __('index.select_department') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <select class="form-select" name="employee_id" id="employee_id">
                            <option  selected  disabled>{{ __('index.select_employee') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6 d-md-flex gap-2">
                        <button type="submit" class="btn btn-block btn-success mb-4">{{ __('index.filter') }}</button>

                        <a class="btn btn-block btn-primary  mb-4"
                           href="{{ route('admin.logout-requests.index') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.logout_requests') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table table-hover logout-request-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee_name') }}</th>
                            <th>{{ __('index.contact') }}</th>
                            <th>{{ __('index.branch') }} / {{ __('index.department') }}</th>
                            <th>Position / Role</th>
                            <th>Device</th>
                            <th>Requested</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logoutRequests as $key => $value)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    <div class="logout-employee">
                                        <img src="{{ $value->avatar_url }}" alt="{{ $value->name }}">
                                        <div>
                                            <div class="logout-primary">{{ removeSpecialChars($value->name) }}</div>
                                            @if($value->english_name && $value->english_name !== $value->name)
                                                <span class="logout-secondary">{{ $value->english_name }}</span>
                                            @endif
                                            <span class="logout-secondary">{{ __('index.employee_code') }}: {{ $value->employee_code ?: 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="logout-secondary">{{ $value->email ?: 'N/A' }}</span>
                                    <span class="logout-secondary">{{ $value->phone ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="logout-primary">{{ $value->branch?->name ?: 'N/A' }}</div>
                                    <span class="logout-secondary">{{ $value->department?->dept_name ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="logout-primary">{{ $value->post?->post_name ?: 'N/A' }}</div>
                                    <span class="logout-secondary">{{ $value->role?->name ?: 'N/A' }}</span>
                                    <span class="logout-secondary">{{ $value->employment_type ? ucfirst($value->employment_type) : 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="logout-device-badge">{{ $value->device_type ?: 'Unknown' }}</span>
                                    <span class="logout-secondary mt-1">{{ $value->latestDeviceLocation?->device_name ?: 'Device name unavailable' }}</span>
                                    @if($value->latestDeviceLocation?->battery_level !== null)
                                        <span class="logout-secondary">Battery: {{ $value->latestDeviceLocation->battery_level }}%</span>
                                    @endif
                                    <span class="logout-secondary">Last seen: {{ $value->latestDeviceLocation?->updated_at?->format('Y-m-d H:i') ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="logout-primary">{{ $value->updated_at?->format('Y-m-d') ?: 'N/A' }}</div>
                                    <span class="logout-secondary">{{ $value->updated_at?->format('H:i') ?: '' }}</span>
                                    <span class="badge bg-warning bg-opacity-15 text-dark mt-1">Pending</span>
                                </td>
                                <td class="text-center">
                                    @can('accept_logout_request')
                                        <form method="POST" action="{{ route('admin.logout-requests.accept', $value->id) }}" class="logout-action-form acceptLogoutRequestForm">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i data-feather="check" style="width:14px;height:14px;"></i> {{ __('index.take_action') }}
                                            </button>
                                        </form>
                                    @endcan
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

            $('.acceptLogoutRequestForm').on('submit', function (event) {
                event.preventDefault();
                const form = this;
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
                        form.submit();
                    }
                })
            })
        });

    </script>
    @include('admin.attendance.common.filter_scripts')
@endsection
