@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', __('index.employee_attendance_lists'))
@section('styles')
    {{--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">--}}

@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')
        <div class="search-box p-4 pb-0 bg-white rounded mb-3 box-shadow">
            <form class="forms-sample" action="{{ route('admin.attendance.export') }}" method="get">
                <h5 class="mb-3">{{ __('index.attendance') .' '. __('index.date') }}</h5>
                <div class="row align-items-center">

                    @if($isBsEnabled)
                        <div class="col-lg col-md-6 mb-4">
                            <input type="text" class="form-control startNpDate" id="start_date" name="start_date"
                                   required value="" autocomplete="off" placeholder="Start Date">
                        </div>
                        <div class="col-lg col-md-6 mb-4">
                            <input type="text" class="form-control npDeadline" id="end_date" name="end_date" value=""
                                   autocomplete="off" placeholder="End Date">
                        </div>
                    @else
                        <div class="col-lg col-md-4 mb-4">
                            <input type="text" class="form-control" id="attendance_date" name="attendance_date"
                                   value=""/>
                        </div>
                    @endif


                    <div class="col-lg-3 col-md-6 d-md-flex">
                        <button type="submit" class="btn btn-block btn-success form-control me-md-2 me-0 mb-md-4 mb-2">{{ __('index.csv_export') }}</button>

                        <a class="btn btn-block btn-primary form-control me-md-2 me-0 mb-4"
                           href="{{ route('admin.attendance.export') }}">{{ __('index.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>

    </section>

@endsection

@section('scripts')
    {{--    @include('admin.attendance.common.scripts')--}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(function () {
            $('input[name="attendance_date"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('input[name="attendance_date"]').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                addParameterDownloadExcel();
            });

            $('input[name="attendance_date"]').on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                addParameterDownloadExcel();
            });
        });

        $('#start_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });

        $('#end_date').nepaliDatePicker({
            language: "english",
            dateFormat: "MM/DD/YYYY",
            ndpYear: true,
            ndpMonth: true,
            ndpYearCount: 20,
            readOnlyInput: true,
            disableAfter: "2089-12-30",
        });
    </script>
@endsection

