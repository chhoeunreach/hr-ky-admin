@extends('layouts.master')

@section('title', __('index.sell_staff_report'))

@section('action', __('index.sell_staff_report'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.sell_staff_report') }}</li>
            </ol>
            <a href="{{ route('admin.sell-staff-report.create') }}" class="btn btn-primary btn-sm">{{ __('index.add_new') }}</a>
        </nav>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.sell_staff_report_filter') }}</h6>
            </div>
            <div class="card-body pb-0">
                <form action="{{ route('admin.sell-staff-report.index') }}" method="get">
                    <input type="hidden" name="date_from" value="{{ $filterData['date_from'] ?? '' }}">
                    <input type="hidden" name="date_to" value="{{ $filterData['date_to'] ?? '' }}">
                    <input type="hidden" name="branch_name" value="{{ $filterData['branch_name'] ?? '' }}">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <input type="text" name="date_range" class="form-control" value="{{ $filterData['date_from'] && $filterData['date_to'] ? $filterData['date_from'] . ' - ' . $filterData['date_to'] : '' }}" placeholder="{{ __('index.date_range') }}" readonly>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <select class="form-control" id="top_filter_branch">
                                <option value="">{{ __('index.all_branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-name="{{ $branch->name }}" {{ ($filterData['branch_name'] ?? '') == $branch->name ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <select class="form-control" name="department_id">
                                <option value="">{{ __('index.select_department') }}</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <select class="form-control" name="service_type">
                                <option value="">{{ __('index.all_sell_types') }}</option>
                                @foreach($sellTypes as $type)
                                    <option value="{{ $type }}" {{ ($filterData['service_type'] ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('index.filter') }}</button>
                            <a href="{{ route('admin.sell-staff-report.index') }}" class="btn btn-warning">{{ __('index.clear') }}</a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end border-top pt-3 mb-3">
                        <button type="submit" name="download_excel" value="1" class="btn btn-success">{{ __('index.export_excel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section>
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">{{ __('index.total_reports') }}</h6>
                        <h3 class="mb-0">{{ number_format((int) ($summary->total_reports ?? 0)) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">{{ __('index.total_amount') }}</h6>
                        <h3 class="mb-0">{{ number_format((float) ($summary->total_amount ?? 0), 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">{{ __('index.staff_summary') }}</h6>
                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#staffSummaryCollapse" aria-expanded="false" aria-controls="staffSummaryCollapse">
                    <i data-feather="chevron-down" class="collapse-icon"></i>
                </button>
            </div>
            <div class="collapse" id="staffSummaryCollapse">
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" id="staffSummarySearch" class="form-control" placeholder="{{ __('index.search') }}...">
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="staffSummaryTable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('index.seller_name') }}</th>
                                <th class="text-center">{{ __('index.total_reports') }}</th>
                                <th class="text-end">{{ __('index.total_amount') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($staffSummary as $staff)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $staff->seller_name }}</td>
                                    <td class="text-center">{{ number_format((int) $staff->total_reports) }}</td>
                                    <td class="text-end">{{ number_format((float) $staff->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="card-title mb-0">{{ __('index.sell_staff_report') }}</h6>
                <form action="{{ route('admin.sell-staff-report.index') }}" method="get" class="d-flex gap-2">
                    <input type="hidden" name="date_from" value="{{ $filterData['date_from'] ?? '' }}">
                    <input type="hidden" name="date_to" value="{{ $filterData['date_to'] ?? '' }}">
                    <input type="hidden" name="branch_name" value="{{ $filterData['branch_name'] ?? '' }}">
                    <input type="hidden" name="service_type" value="{{ $filterData['service_type'] ?? '' }}">
                    <input type="text" name="search" class="form-control" value="{{ $filterData['search'] ?? '' }}" placeholder="{{ __('index.search') }}">
                    <button type="submit" class="btn btn-primary">{{ __('index.search') }}</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.internal_invoice_no') }}</th>
                            <th>{{ __('index.sell_type') }}</th>
                            <th>{{ __('index.seller_name') }}</th>
                            <th>{{ __('index.branch_name') }}</th>
                            <th>Phone</th>
                            <th>Product</th>
                            <th>S/N</th>
                            <th class="text-center">{{ __('index.items') }}</th>
                            <th class="text-end">{{ __('index.total_amount') }}</th>
                            <th>{{ __('index.created_at') }}</th>
                            <th class="text-center">{{ __('index.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $reports->firstItem() + $loop->iteration }}</td>
                                <td><span title="{{ $report->invoice_no }}">{{ $report->invoice_no }}</span></td>
                                <td><span title="{{ $report->service_type }}">{{ $report->service_type ?: '-' }}</span></td>
                                <td><span class="text-truncate d-block" title="{{ $report->seller_name ?: ($report->user->name ?? '-') }}" style="max-width:120px">{{ $report->seller_name ?: ($report->user->name ?? '-') }}</span></td>
                                <td><span class="text-truncate d-block" title="{{ $report->branch_name ?? '-' }}" style="max-width:120px">{{ $report->branch_name ?? '-' }}</span></td>
                                <td><span title="{{ $report->customer_phone ?? '-' }}">{{ $report->customer_phone ?? '-' }}</span></td>
                                <td><span class="text-truncate d-block" title="{{ $report->lines->pluck('product_name')->filter()->unique()->implode(', ') }}" style="max-width:150px">{{ $report->lines->pluck('product_name')->filter()->unique()->implode(', ') ?: '-' }}</span></td>
                                <td><span class="text-truncate d-block" title="{{ $report->lines->pluck('serial_number')->filter()->unique()->implode(', ') }}" style="max-width:130px">{{ $report->lines->pluck('serial_number')->filter()->unique()->implode(', ') ?: '-' }}</span></td>
                                <td class="text-center">{{ $report->lines_count }}</td>
                                <td class="text-end">{{ number_format((float) $report->total_amount, 2) }}</td>
                                <td>{{ optional($report->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.sell-staff-report.show', $report->id) }}" class="btn btn-primary btn-xs">{{ __('index.view') }}</a>
                                    @can('edit_sell_staff_report')
                                        <a href="{{ route('admin.sell-staff-report.edit', $report->id) }}" class="btn btn-warning btn-xs">{{ __('index.edit') }}</a>
                                    @endcan
                                    @can('delete_sell_staff_report')
                                        <a href="javascript:void(0)" class="btn btn-danger btn-xs deleteSellStaffReport" data-href="{{ route('admin.sell-staff-report.delete', $report->id) }}">{{ __('index.delete') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if($reports->count())
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="7"></td>
                                    <td>{{ __('index.total') }}</td>
                                    <td class="text-center">{{ $reports->sum(fn($r) => $r->lines->sum('qty')) }}</td>
                                    <td class="text-end">{{ number_format((float) $reports->sum('total_amount'), 2) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                <div class="mt-3">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    <script>
        $(document).ready(function () {
            $('input[name="date_range"]').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                }
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                let form = $(this).closest('form');
                form.find('input[name="date_from"]').val(picker.startDate.format('YYYY-MM-DD'));
                form.find('input[name="date_to"]').val(picker.endDate.format('YYYY-MM-DD'));
            });

            $('input[name="date_range"]').on('cancel.daterangepicker', function () {
                $(this).val('');
                let form = $(this).closest('form');
                form.find('input[name="date_from"]').val('');
                form.find('input[name="date_to"]').val('');
            });
        });

        $(document).on('click', '.deleteSellStaffReport', function (event) {
            event.preventDefault();
            let href = $(this).data('href');
            Swal.fire({
                title: '{{ __('index.confirm_delete_sell_staff_report') }}',
                showDenyButton: true,
                confirmButtonText: `{{ __('index.yes') }}`,
                denyButtonText: `{{ __('index.no') }}`,
                padding: '10px 50px 10px 50px',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: href,
                        type: 'GET',
                        dataType: 'json',
                        headers: { 'Accept': 'application/json' },
                        success: function () {
                            window.location.reload();
                        },
                        error: function () {
                            window.location.reload();
                        }
                    });
                }
            });
        });

        $(document).on('change', '#top_filter_branch', function () {
            let selected = $(this).find(':selected');
            let branchId = selected.val();
            let branchName = selected.data('name') || '';
            let form = $(this).closest('form');
            form.find('input[name="branch_name"]').val(branchName);
            let deptSelect = form.find('select[name="department_id"]');
            deptSelect.html('<option value="">{{ __('index.select_department') }}</option>');
            if (branchId) {
                $.ajax({
                    url: '{{ url("admin/departments/get-All-Departments") }}/' + branchId,
                    type: 'GET',
                    success: function (response) {
                        if (response.data) {
                            $.each(response.data, function (idx, dept) {
                                deptSelect.append('<option value="' + dept.id + '">' + dept.dept_name + '</option>');
                            });
                        }
                    }
                });
            }
        });

        $('#staffSummaryCollapse').on('show.bs.collapse', function () {
            let icon = $(this).parent().find('.collapse-icon');
            icon.removeClass('feather-chevron-down').addClass('feather-chevron-up');
            if (icon[0]) {
                feather.replace();
            }
        }).on('hide.bs.collapse', function () {
            let icon = $(this).parent().find('.collapse-icon');
            icon.removeClass('feather-chevron-up').addClass('feather-chevron-down');
            if (icon[0]) {
                feather.replace();
            }
        });

        $(document).on('keyup', '#staffSummarySearch', function () {
            let value = $(this).val().toLowerCase();
            $('#staffSummaryTable tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

    </script>
@endsection
