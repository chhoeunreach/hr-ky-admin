
@extends('layouts.master')

@if(request('request_modal') === '1')
    @section('embedded', '1')
@endif

@section('title',__('index.leave_requests'))

@section('action',__('index.lists'))

@section('styles')
    @if(request('request_modal') === '1')
        <style>
            .page-wrapper { margin-left: 0 !important; width: 100% !important; }
            .page-content { padding: 1rem !important; margin-top: 0 !important; }
        </style>
    @endif
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

    <section class="content">

        @include('admin.section.flash_message')
        @include('admin.employees.profile.partials.request-saved-next-step', ['savedRequestType' => 'leave', 'printPermission' => 'employee.leave_form.print', 'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id'])
        @if(request('request_modal') !== '1')
            @include('admin.leaveRequest.common.breadcrumb')
        @endif

        <div class="directory-filter-panel mb-4">
            @include('admin.section.directory-filter-heading', ['directoryCollapse' => 'leaveRequestFilters', 'directoryExpanded' => false])
            <div class="collapse" id="leaveRequestFilters">
                <form id="leaveRequestFilterForm" class="directory-filter-form" action="{{route('admin.leave-request.index')}}" method="get">
                            @if(request('request_modal') === '1')
                                <input type="hidden" name="request_modal" value="1">
                            @endif
                            <input type="hidden" name="search" value="{{ $filterParameters['search'] }}">

                            <div class="row g-3">
                                @if(!isset(auth()->user()->branch_id))
                                    <div class="col-lg-3 col-md-6">
                                        <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            <option value="">{{ __('index.all_branches') }}</option>
                                            @if(isset($companyDetail))
                                                @foreach($companyDetail->branches()->get() as $key => $branch)
                                                    <option {{ $filterParameters['branch_id'] == $branch->id ? 'selected' : '' }} value="{{$branch->id}}">{{ucfirst($branch->name)}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @endif

                                <div class="col-lg-3 col-md-6">
                                    <label for="department_id" class="form-label">{{ __('index.department') }}</label>
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value="">{{ __('index.all_departments') }}</option>
                                        @if(filled($filterParameters['department_id']))<option value="{{ $filterParameters['department_id'] }}" selected>{{ $leaveDetails->first()?->department?->dept_name ?? __('index.department').' #'.$filterParameters['department_id'] }}</option>@endif
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="requestedBy" class="form-label">{{ __('index.employee') }}</label>
                                    <select class="form-select" id="requestedBy" name="requested_by">
                                        <option value="">{{ __('index.all_employees') }}</option>
                                        @if(filled($filterParameters['requested_by']))<option value="{{ $filterParameters['requested_by'] }}" selected>{{ $leaveDetails->first()?->leaveRequestedBy?->name ?? __('index.employee').' #'.$filterParameters['requested_by'] }}</option>@endif
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="leaveType" class="form-label">{{ __('index.leave_type') }}</label>
                                    <select class="form-select form-select-lg" name="leave_type" id="leaveType">
                                        <option value="" {{!isset($filterParameters['leave_type']) ? 'selected': ''}} >{{ __('index.all_leave_type') }}</option>
                                        @foreach($leaveTypes as $leaveTypeId => $leaveTypeName)<option value="{{ $leaveTypeId }}" @selected((string) $filterParameters['leave_type'] === (string) $leaveTypeId)>{{ $leaveTypeName }}</option>@endforeach
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="year" class="form-label">{{ __('index.year') }}</label>
                                    <input type="number" min="{{ $filterData['min_year']}}"
                                           max="{{ $filterData['max_year']}}" step="1"
                                           placeholder="{{ __('index.leave_requested_year') }} : {{$filterData['min_year']}}"
                                           id="year"
                                           name="year" value="{{$filterParameters['year']}}"
                                           class="form-control">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="month" class="form-label">{{ __('index.month') }}</label>
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

                                <div class="col-lg-3 col-md-6">
                                    <label for="status" class="form-label">{{ __('index.status') }}</label>
                                    <select class="form-select form-select-lg" name="status" id="status">
                                        <option
                                            value="" {{!isset($filterParameters['status']) ? 'selected': ''}} >{{ __('index.all_status') }}</option>
                                        @foreach(\App\Models\LeaveRequestMaster::STATUS as  $value)
                                            <option
                                                value="{{$value}}" {{ (isset($filterParameters['status']) && $value == $filterParameters['status'] ) ?'selected':'' }} > {{ucfirst($value)}} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @include('admin.section.directory-filter-actions', ['directoryResetUrl' => route('admin.leave-request.index', request('request_modal') === '1' ? array_filter(['request_modal' => '1', 'requested_by' => request('requested_by')]) : [])])
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            @include('admin.section.directory-toolbar', ['directoryTable' => 'leaveRequestTable', 'directoryForm' => 'leaveRequestFilterForm', 'directorySearch' => 'leaveRequestSearch', 'directoryRecords' => $leaveDetails, 'directoryTitle' => __('index.leave_requests'), 'directoryIcon' => 'calendar', 'directoryCanAdd' => auth('admin')->check() || \Illuminate\Support\Facades\Gate::any(['create_leave_request','access_admin_leave']), 'directoryAddRequestModal' => request('request_modal') !== '1', 'directoryAddUrl' => route('admin.leave-request.add', array_filter(['requested_by' => request('requested_by'), 'request_modal' => request('request_modal') === '1' ? '1' : null])), 'directoryDefaultSize' => '25', 'directoryPageSizes' => [10,25,50,100,200,500,1000,'all'], 'directorySearchKey' => 'search', 'directoryLive' => false])
            <div class="card-body" id="leave-request-list-block" data-directory-total="{{ $leaveDetails->total() }}">
                    <div class="table-responsive">
                        <table id="leaveRequestTable" class="table">
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
                                        <td class="text-center">{{ ($leaveDetails->firstItem() ?? 0) + $loop->index }} @include('admin.employees.profile.partials.request-print-link', ['requestRecord' => $value, 'printPermission' => 'employee.leave_form.print', 'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id'])</td>
                                        <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                        <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)}}</td>
                                        <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)}}</td>
                                        <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                        <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                        <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                        <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                        <td class="text-center">{{($value->no_of_days )}}</td>

                                            <td class="text-center" data-directory-value="{{ $value->reasons }}">
                                                <a href="#" class="showLeaveReason"
                                                   data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                   title="{{ __('index.show_leave_reason') }}">
                                                    <i class="link-icon" data-feather="eye"></i>
                                                </a>

                                            </td>
                                            <td class="text-center" data-directory-value="{{ ucfirst($value->status) }}">
                                                <a href=""
                                                   class="leaveRequestUpdate"
                                                   data-href="{{route('admin.leave-request.update-status',$value->id)}}"
                                                   data-status="{{$value->status}}"
                                                   data-remark="{{$value->admin_remark}}"
                                                   data-id="{{$value->id}}"
                                                   data-leave-type-id="{{$value->leave_type_id}}"
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
                                            <td>{{ ($leaveDetails->firstItem() ?? 0) + $loop->index }} @include('admin.employees.profile.partials.request-print-link', ['requestRecord' => $value, 'printPermission' => 'employee.leave_form.print', 'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id'])</td>
                                            <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)}}</td>
                                            <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                            <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                            <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                            <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                            <td class="text-center">{{($value->no_of_days )}}</td>

                                            @canany(['show_leave_request_detail','access_admin_leave'])
                                                <td class="text-center" data-directory-value="{{ $value->reasons }}">
                                                    <a href="#" class="showLeaveReason"
                                                       data-href="{{ route('admin.leave-request.show', $value->id) }}"
                                                       title="{{ __('index.show_leave_reason') }}">
                                                        <i class="link-icon" data-feather="eye"></i>
                                                    </a>

                                                </td>
                                            @endcanany

                                            @canany(['update_leave_request','access_admin_leave'])

                                                <td class="text-center" data-directory-value="{{ ucfirst($value->status) }}">
                                                    <a href=""
                                                       class="leaveRequestUpdate"
                                                       data-href="{{route('admin.leave-request.update-status',$value->id)}}"
                                                       data-status="{{$value->status}}"
                                                       data-remark="{{$value->admin_remark}}"
                                                       data-id="{{$value->id}}"
                                                   data-leave-type-id="{{$value->leave_type_id}}"
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
                                            <td>{{ ($leaveDetails->firstItem() ?? 0) + $loop->index }} @include('admin.employees.profile.partials.request-print-link', ['requestRecord' => $value, 'printPermission' => 'employee.leave_form.print', 'printTab' => 'leave-letter', 'printRequestField' => 'leave_request_id'])</td>
                                            <td>{{ $value->leaveType ? ucfirst($value->leaveType->name) : ''}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_from)}}</td>
                                            <td>{{\App\Helpers\AppHelper::convertLeaveDateFormat($value->leave_to)}}</td>
                                            <td>{{\App\Helpers\AppHelper::formatDateForView($value->leave_requested_date)}}</td>
                                            <td>{{$value->leaveRequestedBy ? ucfirst($value->leaveRequestedBy->name) : 'N/A'}} </td>
                                            <td>{{ $value->branch ? ucfirst($value->branch->name) : 'N/A' }}</td>
                                            <td>{{ $value->department ? ucfirst($value->department->dept_name) : 'N/A' }}</td>
                                            <td class="text-center">{{($value->no_of_days )}}</td>

                                            @canany(['show_leave_request_detail','access_admin_leave'])
                                                <td class="text-center" data-directory-value="{{ $value->reasons }}">
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
    <div id="leave-request-pagination" class="dataTables_paginate mt-3">
        {{$leaveDetails->appends($_GET)->links()}}
    </div>

    @include('admin.leaveRequest.show')
    @include('admin.leaveRequest.common.form-model')
    @include('admin.leaveRequest.common.approval-info-model')
@endsection

@section('scripts')
    @include('admin.leaveRequest.common.scripts')
    <script>
        document.addEventListener('click', function (event) {
            const element = event.target.closest('.showLeaveReason');

            if (!element) {
                return;
            }

            event.preventDefault();
            const url = element.getAttribute('data-href');

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
                            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                            modal.show();
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        });


    </script>
@endsection
