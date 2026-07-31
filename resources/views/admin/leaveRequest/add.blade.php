@extends('layouts.master')

@section('title',__('index.leave_request'))

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
                              action="{{route('admin.leave-request.save')}}" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="requestedBy" class="form-label">{{ __('index.requested_for') }}<span style="color: red">*</span></label>
                                    <select class="form-select" id="requestedBy" name="requested_by" required>
                                        <option selected disabled>{{ __('index.select_employee') }}</option>
                                        @foreach($employees ?? [] as $employee)
                                            <option value="{{ $employee->id }}"
                                                    data-branch-id="{{ $employee->branch_id }}"
                                                    data-branch-name="{{ $employee->branch->name ?? '' }}"
                                                    data-department-id="{{ $employee->department_id }}"
                                                    data-department-name="{{ $employee->department->dept_name ?? '' }}"
                                                {{ (old('requested_by') == $employee->id || (($preselectedEmployee->id ?? null) == $employee->id)) ? 'selected' : '' }}>
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
                                    <input type="hidden" id="branch_id" name="branch_id" value="{{ old('branch_id', $preselectedEmployee->branch_id ?? '') }}">
                                </div>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <label for="department_display" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="department_display"
                                           placeholder="{{ __('index.select_department') }}"
                                           readonly>
                                    <input type="hidden" id="department_id" name="department_id" value="{{ old('department_id', $preselectedEmployee->department_id ?? '') }}">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="leaveType" class="form-label">{{ __('index.leave_type') }}<span style="color: red">*</span></label>
                                    <select class="form-select" id="leaveType" name="leave_type_id" required>
                                        <option selected disabled>{{ __('index.select_leave_type') }} </option>

                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="leave_from" class="form-label">{{ __('index.from_date') }}<span style="color: red">*</span></label>
                                    @if($bsEnabled)
                                        <input type="text" class="form-control leave_from" id="leave_from" value="{{old('leave_from')}}" name="leave_from" autocomplete="off">
                                    @else
                                        <input class="form-control" type="date" name="leave_from" value="{{old('leave_from')}}" required  />
                                    @endif
                                </div>
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="leave_to" class="form-label">{{ __('index.to_date') }}<span style="color: red">*</span></label>
                                    @if($bsEnabled)
                                        <input type="text" class="form-control leave_to" id="leave_to" value="{{old('leave_to')}}" name="leave_to" autocomplete="off">
                                    @else
                                        <input class="form-control" type="date" name="leave_to" value="{{old('leave_to')}}" required  />

                                    @endif
                                </div>
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="info" class="form-label">{{ __('index.total_duration') }} </label>
                                    <input class="form-control bg-light" type="text" readonly id="no_of_days" name="no_of_days" value="{{old('end_time')}}"  />
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label for="note" class="form-label">{{ __('index.reason') }}<span style="color: red">*</span></label>
                                    <textarea class="form-control" name="reasons" rows="6" >{{  old('reasons') }}</textarea>
                                </div>

                                <div class="col-lg-12 mb-4 text-start">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('index.submit') }}
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

            @if($bsEnabled)
            $('.leave_from').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
                onChange: function () {
                    calculateDays();
                }
            });

            $('.leave_to').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
                onChange: function () {
                    calculateDays();
                }
            });
            @else
            $('input[name="leave_from"], input[name="leave_to"]').on('change', function () {
                calculateDays();
            });
            @endif

            function calculateDays() {
                let from = $('input[name="leave_from"]').val();
                let to   = $('input[name="leave_to"]').val();
                let noOfDaysField = $('#no_of_days');

                if (!from || !to) {
                    noOfDaysField.val('');
                    return;
                }

                try {
                    @if($bsEnabled)
                    if (typeof NepaliFunctions !== "undefined") {
                        // Convert BS → AD using library
                        let fromAd = NepaliFunctions.BS2AD(from);
                        let toAd   = NepaliFunctions.BS2AD(to);
                        processDates(fromAd, toAd);
                    } else {
                        console.warn("NepaliFunctions not available. Duration will be empty.");
                        noOfDaysField.val('');
                    }
                    @else
                    processDates(from, to);
                    @endif
                } catch (e) {
                    console.error("Error calculating duration:", e.message);
                    noOfDaysField.val('');
                }
            }

            function processDates(from, to) {
                let noOfDaysField = $('#no_of_days');

                let start = new Date(from);
                let end   = new Date(to);

                if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                    noOfDaysField.val('');
                    return;
                }

                let diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                noOfDaysField.val(diff > 0 ? diff : 0);
            }
        });
        $(document).ready(function () {
            $("#requestedBy").select2();
            $("#leaveType").select2();

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

            const loadLeaveTypes = async () => {
                const selectedEmployee = $('#requestedBy').val();
                if (!selectedEmployee) return;
                try {
                    $('#leaveType').empty().append('<option selected disabled>{{ __("index.select_leave_type") }}</option>');

                    const response = await fetch(`{{ url('admin/leaves/get-employee-leave-types') }}/${selectedEmployee}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });

                    const data = await response.json();
                    $('#leaveType').empty();
                    $('#leaveType').append('<option selected disabled>{{ __("index.select_leave_type") }}</option>');

                    if (data.leveTypes && data.leveTypes.length > 0) {
                        data.leveTypes.forEach(type => {
                            $('#leaveType').append(
                                `<option value="${type.id}">${type.name}</option>`
                            );
                        });
                    } else {
                        $('#leaveType').append('<option disabled>{{ __("index.leave_type_not_found") }}</option>');
                    }

                } catch (error) {
                    $('#leaveType').append('<option disabled>{{ __("index.error_loading_leave_types") }}</option>');
                }
            };

            $('#requestedBy').change(function () {
                syncEmployeeBranchAndDepartment();
                loadLeaveTypes();
            });

            syncEmployeeBranchAndDepartment();
            loadLeaveTypes();
        });

    </script>


@endsection
