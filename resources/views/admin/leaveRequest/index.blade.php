
@extends('layouts.master')

@section('title',__('index.leave_requests'))

@section('action',__('index.lists'))

@section('button')
    @canany(['create_leave_request','access_admin_leave'])
        <a href="{{ route('admin.leave-request.add')}}">
            <button class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>{{ __('index.create_leave_request') }}
            </button>
        </a>
    @endcanany
@endsection

@section('styles')
    <style>
        .page-content {
            padding-top: 0.4rem !important;
        }

        .content {
            margin-top: 0 !important;
        }

        .leave-request-filter-card,
        .leave-request-toolbar {
            background: #fff;
        }

        .leave-request-toolbar {
            overflow-x: auto;
        }

        .leave-filter-trigger,
        .leave-export-btn {
            border-radius: 16px;
            padding: 0.58rem 0.95rem;
            font-weight: 500;
        }

        .leave-filter-trigger {
            border: 1px solid #8a9abd;
            color: #6f80a7;
            background: #fff;
        }

        .leave-filter-trigger:hover {
            color: #5f7098;
            border-color: #7488b4;
            background: #f8fbff;
        }

        .leave-filter-trigger svg {
            width: 18px;
            height: 18px;
        }

        .leave-export-btn {
            border: 1px solid #c8d4ea;
            background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);
            color: #4f628d;
            box-shadow: 0 6px 16px rgba(110, 135, 184, 0.12);
            transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .leave-export-btn:hover {
            color: #3f5381;
            background: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%);
            border-color: #aebfe0;
            box-shadow: 0 10px 20px rgba(110, 135, 184, 0.18);
            transform: translateY(-1px);
        }

        .leave-toolbar-select {
            min-width: 96px;
            border-radius: 14px;
            border-color: #dbe3f1;
            padding: 0.55rem 2rem 0.55rem 0.8rem;
        }

        .leave-search-input {
            min-width: 235px;
            border-radius: 16px;
            border: 1px solid #dbe3f1;
            padding: 0.58rem 0.95rem;
        }

        .leave-toolbar-title {
            font-size: 0.92rem;
            letter-spacing: 0.02em;
            line-height: 1.1;
        }

        .leave-toolbar-controls {
            margin-left: auto;
            flex-wrap: nowrap;
        }

        .leave-toolbar-meta {
            color: #5f6b85;
            font-weight: 500;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .leave-request-filter-card .card-body > .d-flex {
            padding-top: 0.72rem !important;
            padding-bottom: 0.72rem !important;
        }

        .card.border-0.shadow-sm .card-body {
            padding-top: 0.9rem;
            padding-bottom: 0.85rem;
        }

        .leave-request-filter-card {
            margin-bottom: 1rem !important;
        }

        @media (max-width: 767.98px) {
            .leave-search-input,
            .leave-toolbar-select {
                min-width: 100%;
            }

            .leave-toolbar-controls {
                margin-left: 0;
                width: 100%;
                flex-wrap: wrap;
            }

            .leave-toolbar-meta {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection

@section('main-content')
        <?php
        if (\App\Helpers\AppHelper::ifDateInBsEnabled()) {
            $filterData['min_year'] = '2076';
            $filterData['max_year'] = '2089';
            $filterData['month'] = 'np';
        } else {
            $filterData['min_year'] = '2020';
            $filterData['max_year'] = '2033';
            $filterData['month'] = 'en';
        }
        ?>

        @php
            $activeFilterCount = collect([
                $filterParameters['branch_id'] ?? null,
                $filterParameters['department_id'] ?? null,
                $filterParameters['requested_by'] ?? null,
                $filterParameters['leave_type'] ?? null,
                $filterParameters['search'] ?? null,
                $filterParameters['month'] ?? null,
                $filterParameters['status'] ?? null,
            ])->filter(fn ($value) => filled($value))->count();
        @endphp
 
    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')

            <div class="card leave-request-filter-card mb-4 border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center gap-3 px-4 py-3">
                        <button class="btn leave-filter-trigger d-inline-flex align-items-center gap-2"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#leaveRequestFilters"
                                aria-expanded="{{ $activeFilterCount > 0 ? 'true' : 'false' }}"
                                aria-controls="leaveRequestFilters">
                            <i data-feather="filter"></i>
                            <span>{{ __('index.filter') }}</span>
                        </button>
                        <div>
                            <h4 class="mb-0 text-uppercase fw-bold">{{ __('index.leave_requests') }}</h4>
                        </div>
                    </div>

                    <div class="collapse {{ $activeFilterCount > 0 ? 'show' : '' }}" id="leaveRequestFilters">
                        <form class="forms-sample px-4 pb-4" action="{{route('admin.leave-request.index')}}" method="get">
                            <input type="hidden" name="search" value="{{ $filterParameters['search'] }}">
                            <input type="hidden" name="per_page" value="{{ $filterParameters['per_page'] }}">

                            <div class="row align-items-center pt-2">
                                @if(!isset(auth()->user()->branch_id))
                                    <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                        <select class="form-select" id="branch_id" name="branch_id" required>
                                            <option selected disabled>{{ __('index.select_branch') }}</option>
                                            @if(isset($companyDetail))
                                                @foreach($companyDetail->branches()->get() as $key => $branch)
                                                    <option {{ $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @endif

                                <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                    <select class="form-select" id="department_id" name="department_id" required>
                                        <option selected disabled>{{ __('index.select_department') }}</option>
                                    </select>
                                </div>

                                <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                    <select class="form-select" id="requestedBy" name="requested_by" required>
                                        <option selected disabled>{{ __('index.select_employee') }}</option>
                                    </select>
                                </div>

                                <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                    <select class="form-select form-select-lg" name="leave_type" id="leaveType">
                                        <option value="" {{!isset($filterParameters['leave_type']) ? 'selected': ''}} >{{ __('index.all_leave_type') }}</option>
                                    </select>
                                </div>

                                <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                    <input type="number" min="{{ $filterData['min_year']}}"
                                           max="{{ $filterData['max_year']}}" step="1"
                                           placeholder="{{ __('index.leave_requested_year') }} : {{$filterData['min_year']}}"
                                           id="year"
                                           name="year" value="{{$filterParameters['year']}}"
                                           class="form-control">
                                </div>

                                <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                    <select class="form-select form-select-lg" name="month" id="month">
                                        <option
                                            value="" {{!isset($filterParameters['month']) ? 'selected': ''}} >{{ __('index.all_month') }}</option>
                                        @foreach($months as $key => $value)
                                            <option
                                                value="{{$key}}" {{ (isset($filterParameters['month']) && $key == $filterParameters['month'] ) ?'selected':'' }} >
                                                {{$value[$filterData['month']]}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xxl col-xl-3 col-md-6 mb-4">
                                    <select class="form-select form-select-lg" name="status" id="status">
                                        <option
                                            value="" {{!isset($filterParameters['status']) ? 'selected': ''}} >{{ __('index.all_status') }}</option>
                                        @foreach(\App\Models\LeaveRequestMaster::STATUS as  $value)
                                            <option
                                                value="{{$value}}" {{ (isset($filterParameters['status']) && $value == $filterParameters['status'] ) ?'selected':'' }} > {{ucfirst($value)}} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xxl col-xl-3 mb-4">
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" class="btn btn-secondary">{{ __('index.filter') }}</button>
                                        <a class="btn btn-primary" href="{{route('admin.leave-request.index')}}">{{ __('index.reset') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="get" action="{{ route('admin.leave-request.index') }}" class="leave-request-toolbar mb-4">
                        <input type="hidden" name="branch_id" value="{{ $filterParameters['branch_id'] }}">
                        <input type="hidden" name="department_id" value="{{ $filterParameters['department_id'] }}">
                        <input type="hidden" name="requested_by" value="{{ $filterParameters['requested_by'] }}">
                        <input type="hidden" name="leave_type" value="{{ $filterParameters['leave_type'] }}">
                        <input type="hidden" name="year" value="{{ $filterParameters['year'] }}">
                        <input type="hidden" name="month" value="{{ $filterParameters['month'] }}">
                        <input type="hidden" name="status" value="{{ $filterParameters['status'] }}">

                        <div class="d-flex align-items-center gap-3 flex-nowrap">
                            <h4 class="mb-0 text-uppercase fw-bold leave-toolbar-title">{{ __('index.leave_requests') }}</h4>

                            <div class="d-flex align-items-center gap-2 leave-toolbar-meta">
                                <span>Show</span>
                                <select class="form-select leave-toolbar-select"
                                        name="per_page"
                                        onchange="this.form.submit()">
                                    <option value="10" {{ (string) $filterParameters['per_page'] === '10' ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ (string) $filterParameters['per_page'] === '25' ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ (string) $filterParameters['per_page'] === '50' ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ (string) $filterParameters['per_page'] === '100' ? 'selected' : '' }}>100</option>
                                </select>
                                <span>entries</span>
                            </div>

                            <div class="d-flex align-items-center gap-3 leave-toolbar-controls">
                                <button type="button"
                                        id="copy-leave-request-export"
                                        data-href="{{ route('admin.leave-request.copy-export') }}"
                                        class="btn leave-export-btn">
                                    Copy Excel
                                </button>
                                <a class="btn leave-export-btn"
                                   href="{{ route('admin.leave-request.export', request()->query()) }}">
                                    Export
                                </a>
                                <input type="search"
                                       class="form-control leave-search-input"
                                       name="search"
                                       value="{{ $filterParameters['search'] }}"
                                       placeholder="Search ..."
                                       onchange="this.form.submit()">
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>{{ __('index.type') }}</th>
                                <th>{{ __('index.from') }}</th>
                                <th>{{ __('index.to') }}</th>
                                <th>{{ __('index.requested_date') }}</th>
                                <th>{{ __('index.requested_by') }}</th>
                                <th>{{ __('index.branch_name') }}</th>
                                <th>{{ __('index.department') }}</th>
                                <th class="text-center">{{ __('index.requested_days') }}</th>
                                @canany(['show_leave_request_detail','access_admin_leave'])
                                    <th class="text-center">{{ __('index.reason') }}</th>
                                @endcanany
                                @canany(['update_leave_request','access_admin_leave'])
                                    <th class="text-center">{{ __('index.status') }}</th>
                                @endcanany
                            </tr>
                            </thead>
                            <tbody>

                                <?php
                                $color = [
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending' => 'secondary',
                                    'cancelled' => 'danger'
                                ];

                                ?>
                            @forelse($leaveDetails as $key => $value)

                                @if(auth('admin')->user())
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                        <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)}}</td>
                                        <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)}}</td>
                                        <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                        <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                        <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                        <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                        <td class="text-center">{{($value->no_of_days )}}</td>

                                            <td class="text-center">
                                                <a href="#" class="showLeaveReason"
                                                   data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                   title="{{ __('index.show_leave_reason') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>

                                            </td>
                                            <td class="text-center">
                                                <a href=""
                                                   class="leaveRequestUpdate"
                                                   data-href="{{route('admin.leave-request.update-status',$value->id)}}"
                                                   data-status="{{$value->status}}"
                                                   data-remark="{{$value->admin_remark}}"
                                                   data-id="{{$value->id}}"
                                                >
                                                    <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                        {{ucfirst($value->status)}}
                                                    </button>
                                                </a>
                                            </td>

                                    </tr>
                                @else
                                    @php
                                        $inRole = false;
                                        $approver = null;
                                        // Get the next approver for pending leaves
                                        $approver = \App\Helpers\AppHelper::getNextApprover($value->id, $value->leave_type_id, $value->requested_by);
                                        $permissionKey = 'access_admin_leave';

                                        $roleArray = \App\Helpers\AppHelper::getRoleByPermission($permissionKey);

                                        if(auth()->user()){
                                            $inRole = in_array(auth()->user()->role_id, $roleArray);
                                        }

                                    @endphp
                                    @if(($approver == auth()->user()->id && $value->status =='pending')  || ($inRole && $value->status =='pending'))
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)}}</td>
                                            <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                            <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                            <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                            <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                            <td class="text-center">{{($value->no_of_days )}}</td>

                                            @canany(['show_leave_request_detail','access_admin_leave'])
                                                <td class="text-center">
                                                    <a href="#" class="showLeaveReason"
                                                       data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                       title="{{ __('index.show_leave_reason') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>

                                                </td>
                                            @endcanany

                                            @canany(['update_leave_request','access_admin_leave'])

                                                <td class="text-center">
                                                    <a href=""
                                                       class="leaveRequestUpdate"
                                                       data-href="{{route('admin.leave-request.update-status',$value->id)}}"
                                                       data-status="{{$value->status}}"
                                                       data-remark="{{$value->admin_remark}}"
                                                       data-id="{{$value->id}}"
                                                    >
                                                        <button class="btn btn-{{ $color[$value->status] }} btn-xs">
                                                            {{ucfirst($value->status)}}
                                                        </button>
                                                    </a>
                                                </td>
                                            @endcanany
                                        </tr>
                                    @elseif( ($value->requestApproval->where('leave_request_id', $value->id)->contains('approved_by', auth()->user()->id) || ($approver == auth()->user()->id && $value->status != 'pending')) || ($inRole && $value->status !='pending'))
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)}}</td>
                                            <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                            <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                            <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                            <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                            <td class="text-center">{{($value->no_of_days )}}</td>

                                            @canany(['show_leave_request_detail','access_admin_leave'])
                                                <td class="text-center">
                                                    <a href="#" class="showLeaveReason"
                                                       data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                       title="{{ __('index.show_leave_reason') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>

                                                </td>
                                            @endcanany

                                            @canany(['show_leave_request_detail','access_admin_leave'])
                                                <td class="text-center">

                                                    @php
                                                        $approval = $value->requestApproval
                                                                   ->where('leave_request_id', $value->id)
                                                                   ->where('approved_by', auth()->user()->id)
                                                                   ->first();

                                                    @endphp
                                                    @if(isset($approval))
                                                        <a href="javascript:void(0)" class="show-approval-info"
                                                           data-id="{{$value->id}}">
                                                            <button
                                                                class="btn btn-{{ $value->status == 'rejected' ? 'danger' : ($approval->status == 1 ? 'success' : 'danger') }} btn-xs">
                                                                {{  $value->status == 'rejected' ? 'Rejected' : ($approval->status == 1 ? 'Approved' : 'Rejected') }}
                                                            </button>
                                                        </a>
                                                    @else
                                                        <a href="javascript:void(0)" class="show-approval-info"
                                                           data-id="{{$value->id}}">
                                                            <button
                                                                class="btn btn-{{ $value->status == 'rejected' ? 'danger' : 'success' }} btn-xs">
                                                                {{  ucfirst($value->status) }}
                                                            </button>
                                                        </a>

                                                    @endif
                                                </td>
                                            @endcanany
                                        </tr>
                                    @else

                                    @endif
                                @endif


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
    <div class="dataTables_paginate mt-3">
        {{$leaveDetails->appends($_GET)->links()}}
    </div>

    @include('admin.leaveRequest.show')
    @include('admin.leaveRequest.common.form-model')
    @include('admin.leaveRequest.common.approval-info-model')
@endsection

@section('scripts')
    @include('admin.leaveRequest.common.scripts')
    <script>
        const copyLeaveRequestTextToClipboard = async (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return;
            }

            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
        };

        document.addEventListener('click', async function (event) {
            const copyButton = event.target.closest('#copy-leave-request-export');
            if (!copyButton) {
                return;
            }

            event.preventDefault();

            const copyUrl = new URL(copyButton.getAttribute('data-href'), window.location.origin);
            const currentUrl = new URL(window.location.href);

            ['branch_id', 'department_id', 'requested_by', 'leave_type', 'year', 'month', 'status', 'search'].forEach((key) => {
                const value = currentUrl.searchParams.get(key);
                if (value) {
                    copyUrl.searchParams.set(key, value);
                }
            });

            const originalHtml = copyButton.innerHTML;
            copyButton.disabled = true;
            copyButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Copying';

            try {
                const response = await fetch(copyUrl.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Unable to copy leave request export data.');
                }

                await copyLeaveRequestTextToClipboard(data.text);
                Swal.fire('Copied', 'Export data is ready to paste into Excel.', 'success');
            } catch (error) {
                Swal.fire('Copy failed', error.message || 'Unable to copy leave request export data. Please try again.', 'error');
            } finally {
                copyButton.disabled = false;
                copyButton.innerHTML = originalHtml;
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.showLeaveReason').forEach(function (element) {
                element.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = this.getAttribute('data-href');

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {

                            if (data && data.data) {
                                const leaveRequest = data.data;
                                document.getElementById('referredBy').innerText = leaveRequest.name || 'Admin';
                                document.getElementById('description').innerText = leaveRequest.reasons || 'N/A';
                                document.getElementById('adminRemark').innerText = leaveRequest.admin_remark || 'N/A';

                                const modalElement = document.getElementById('addslider');

                                if (modalElement) {
                                    const modal = new bootstrap.Modal(modalElement);
                                    modal.show();
                                } else {
                                    console.error('Modal element not found');
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });


    </script>
@endsection
