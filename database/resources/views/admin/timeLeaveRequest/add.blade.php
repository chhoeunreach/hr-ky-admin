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
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="leave_type" class="form-label">{{__('index.requested_for')}}<span style="color: red">*</span></label>
                                    <select class="form-select" id="requestedBy" name="requested_by" required>
                                        <option selected disabled> {{__('index.select_employee')}}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" @if( $employee->id  == auth()->user()->id) hidden @endif {{ !is_null(old('requested_by')) && old('requested_by') == $employee->id ? 'selected': '' }}> {{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
                                    <div class="col-lg-3 col-md-6 mb-4">
                                        <label for="issue_date" class="form-label">{{__('index.leave_date')}}  <span style="color: red">*</span> </label>
                                        <input type="text" id="nepali_startDate"
                                               name="issue_date"
                                               value="{{ old('issue_date') }}"
                                               placeholder="yyyy-mm-dd"
                                               class="form-control startDate"/>
                                    </div>
                                @else
                                    <div class="col-lg-3 col-md-6 mb-4">
                                        <label for="leave_from" class="form-label">{{__('index.leave_date')}}<span style="color: red">*</span></label>
                                        <input class="form-control" type="date" name="issue_date" value="{{old('issue_date')}}" required  />
                                    </div>
                                @endif
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="start_time" class="form-label">{{__('index.from')}} <span style="color: red">*</span></label>
                                    <input class="form-control" type="time" name="leave_from" value="{{old('leave_from')}}" required  />
                                </div>
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <label for="end_time" class="form-label">{{__('index.to')}}</label>
                                    <input class="form-control end_time" type="time" name="leave_to" value="{{old('leave_to')}}"  />
                                </div>

                                <div class="col-lg-6 mb-4">
                                    <label for="note" class="form-label">{{__('index.reason')}}<span style="color: red">*</span></label>
                                    <textarea class="form-control" name="reasons" rows="5" >{{  old('reasons') }}</textarea>
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
            $('#nepali_startDate').nepaliDatePicker({
                language: "english",
                dateFormat: "YYYY-MM-DD",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
            });
        });
    </script>

@endsection

