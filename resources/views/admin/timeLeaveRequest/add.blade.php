@extends('layouts.master')

@section('title',__('index.time_leave_request'))

@section('action',__('index.create'))

@section('main-content')
    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.leaveRequest.common.breadcrumb')
        <div class="row">
{{--            <div class="col-lg-2">--}}
{{--                @include('admin.leaveRequest.common.leave_menu')--}}
{{--            </div>--}}
{{--            <div class="col-lg-10">--}}
                <div class="card">
                    <div class="card-body pb-0">
                        <form class="forms-sample"
                              action="{{route('admin.time-leave-request.store')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="requestedBy" class="form-label">{{__('index.requested_for')}}<span style="color: red">*</span></label>
                                    <select class="form-select" id="requestedBy" name="requested_by" required>
                                        <option selected disabled> {{__('index.select_employee')}}</option>
                                        @foreach($employees ?? [] as $employee)
                                            <option value="{{ $employee->id }}"
                                                    data-branch-id="{{ $employee->branch_id }}"
                                                    data-branch-name="{{ $employee->branch->name ?? '' }}"
                                                    data-department-id="{{ $employee->department_id }}"
                                                    data-department-name="{{ $employee->department->dept_name ?? '' }}"
                                                {{ old('requested_by') == $employee->id ? 'selected' : '' }}>
                                                {{ ucfirst($employee->name) }}{{ $employee->username ? ' (' . $employee->username . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="branch_display" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="branch_display"
                                           placeholder="{{ __('index.select_branch') }}"
                                           readonly>
                                    <input type="hidden" id="branch_id" name="branch_id" value="{{ old('branch_id') }}">
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="department_display" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="department_display"
                                           placeholder="{{ __('index.select_department') }}"
                                           readonly>
                                    <input type="hidden" id="department_id" name="department_id" value="{{ old('department_id') }}">
                                </div>
                                @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <label for="issue_date" class="form-label">{{__('index.leave_date')}}  <span style="color: red">*</span> </label>
                                        <input type="text" id="nepali_startDate"
                                               name="issue_date"
                                               value="{{ old('issue_date') }}"
                                               placeholder="yyyy-mm-dd"
                                               class="form-control startDate"/>
                                    </div>
                                @else
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <label for="leave_from" class="form-label">{{__('index.leave_date')}}<span style="color: red">*</span></label>
                                        <input class="form-control" type="date" name="issue_date" value="{{old('issue_date')}}" required  />
                                    </div>
                                @endif
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="start_time" class="form-label">{{__('index.from')}} <span style="color: red">*</span></label>
                                    <input class="form-control" type="time" name="leave_from" value="{{old('leave_from')}}" required  />
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="end_time" class="form-label">{{__('index.to')}}</label>
                                    <input class="form-control end_time" type="time" name="leave_to" value="{{old('leave_to')}}"  />
                                </div>

                                <div class="col-lg-4 mb-4">
                                    <label for="note" class="form-label">{{__('index.reason')}}<span style="color: red">*</span></label>
                                    <textarea class="form-control" name="reasons" rows="6" >{{  old('reasons') }}</textarea>
                                </div>

                                <div class="col-lg-12 mb-4 text-start">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('index.submit')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
{{--            </div>--}}
        </div>

    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $("#requestedBy").select2({});

            $('#nepali_startDate').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
            });
        });

        $(document).ready(function () {
            const syncEmployeeBranchAndDepartment = () => {
                const selectedEmployee = $('#requestedBy').find(':selected');
                const branchId = selectedEmployee.data('branch-id') || '';
                const branchName = selectedEmployee.data('branch-name') || '';
                const departmentId = selectedEmployee.data('department-id') || '';
                const departmentName = selectedEmployee.data('department-name') || '';

                $('#branch_id').val(branchId);
                $('#branch_display').val(branchName);
                $('#department_id').val(departmentId);
                $('#department_display').val(departmentName);
            };

            $('#requestedBy').on('change', syncEmployeeBranchAndDepartment);
            syncEmployeeBranchAndDepartment();

        });
    </script>

@endsection
