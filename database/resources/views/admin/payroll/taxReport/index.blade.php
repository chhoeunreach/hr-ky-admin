@extends('layouts.master')

@section('title',__('index.employee_tax_report'))

@section('action',__('index.tax_report_generate'))

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.taxReport.common.breadcrumb')

        <div class="card mb-4">
            <div class="card-body pb-0">
                <h4 class="mb-4"> {{ __('index.tax_report') }}</h4>
                <form class="forms-sample" action="{{ route('admin.payroll.tax-report.index') }}" method="get">
                    <div class="payroll-fil border-bottom">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-4">
                                <h5 class="mb-2">{{ __('index.fiscal_year') }}</h5>

                                <select class="form-select form-select" name="year" id="year">
                                    <option disabled selected>{{ __('index.select_fiscal_year') }}</option>
                                    @foreach ($fiscalYears as $year)
                                        <option {{ ($filterData['year'] ?? old('year')) == $year->id ? 'selected': '' }} value="{{ $year->id }}">{{ $year->year }}</option>
                                    @endforeach
                                </select>


                            </div>

                            <div class="col-lg-5 col-md-6 mb-4">
                                <h5 class="mb-2">{{ __('index.employee') }}</h5>
                                <select class="form-select form-select" name="employee_id" id="employee_id">
                                    <option disabled selected>{{ __('index.select_employee') }}</option>
                                    @foreach($employees as $employee)
                                        <option @if( (isset($filterData['employee_id']) && $filterData['employee_id'] == $employee->id) || old('employee_id') == $employee->id) selected @endif value="{{$employee->id}}">{{ ucfirst($employee->name) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 mt-lg-4 mb-4">
                                <button type="submit" class="btn btn-primary me-md-2">{{ __('index.generate') }}</button>
                                <a href="{{ route('admin.payroll.tax-report.index') }}"  class="btn btn-warning">{{ __('index.clear') }}</a>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
    <section>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTableExample" class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">{{ __('index.fiscal_year') }}</th>
                            <th class="text-center">{{ __('index.employee_name') }} </th>
                            <th class="text-center">{{ __('index.tax_payable') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>


                        @if(isset($filterData['employee_id']) && isset($filterData['year']) && !empty($reportData))
                            <tr>
                                <td>#</td>
                                <td class="text-center">{{ $reportData['year'] ?? '' }}</td>
                                <td class="text-center">{{ $reportData['name'] ?? '' }}</td>
                                <td class="text-center">{{ $reportData['total_payable_tds'] ?? '' }}</td>
                                <td class="text-center">
                                    <a class="nav-link dropdown-toggle p-0" href="#" id="actionDropdown"
                                       role="button"
                                       data-bs-toggle="dropdown"
                                       aria-haspopup="true"
                                       aria-expanded="false"
                                       title="More Action"
                                    >
                                    </a>

                                    <div class="dropdown-menu p-0" aria-labelledby="actionDropdown">
                                        <ul class="list-unstyled mb-0">
                                            <li class="dropdown-item p-2 border-bottom">
                                                <a href="{{ route('admin.payroll.tax-report.detail',$reportData['id']) }}">
                                                    <button class="btn btn-primary btn-xs">{{ __('index.view') }}
                                                    </button>
                                                </a>
                                            </li>
                                            <li class="dropdown-item p-2 border-bottom">
                                                <a href="{{ route('admin.payroll.tax-report.print',$reportData['id']) }}" target="_blank">
                                                    <button class="btn btn-primary btn-xs">{{ __('index.print') }}
                                                    </button>
                                                </a>
                                            </li>
                                            <li class="dropdown-item p-2>
                                                <a href="{{ route('admin.payroll.tax-report.edit',$reportData['id']) }}">
                                                    <button class="btn btn-primary btn-xs">{{ __('index.edit') }}</button>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                        @else
                            @forelse($reportData as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ $report->year }}</td>
                                    <td class="text-center">{{ $report->name }}</td>
                                    <td class="text-center">{{ $report->total_payable_tds }}</td>
                                    <td class="text-center">
                                        <a class="nav-link dropdown-toggle p-0" href="#" id="actionDropdown"
                                           role="button"
                                           data-bs-toggle="dropdown"
                                           aria-haspopup="true"
                                           aria-expanded="false"
                                           title="More Action"
                                        >
                                        </a>

                                        <div class="dropdown-menu p-0" aria-labelledby="actionDropdown">
                                            <ul class="list-unstyled p-1 mb-0">
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a href="{{ route('admin.payroll.tax-report.detail',$report->id) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.view') }}
                                                        </button>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item p-2 border-bottom">
                                                    <a href="{{ route('admin.payroll.tax-report.print',$report->id) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.print') }}
                                                        </button>
                                                    </a>
                                                </li>
                                                <li class="dropdown-item p-2">
                                                    <a href="{{ route('admin.payroll.tax-report.edit',$report->id) }}">
                                                        <button class="btn btn-primary btn-xs">{{ __('index.edit') }}</button>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%">
                                        <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

@endsection

