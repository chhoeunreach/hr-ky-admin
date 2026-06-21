@extends('layouts.master')

@section('title', __('index.sell_staff_report_detail'))

@section('action', __('index.sell_staff_report_detail'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.sell-staff-report.index') }}">{{ __('index.sell_staff_report') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $report->invoice_no }}</li>
            </ol>
            <a href="{{ route('admin.sell-staff-report.index') }}" class="btn btn-secondary btn-sm">{{ __('index.button_back') }}</a>
        </nav>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.sell_staff_report_detail') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3"><strong>{{ __('index.internal_invoice_no') }}:</strong><br>{{ $report->invoice_no }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.original_invoice_no') }}:</strong><br>{{ $report->original_invoice_no ?? '-' }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.seller_name') }}:</strong><br>{{ $report->seller_name ?: ($report->user->name ?? '-') }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.branch_name') }}:</strong><br>{{ $report->branch_name ?? '-' }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.customer_name') }}:</strong><br>{{ $report->customer_name ?? '-' }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.customer_phone') }}:</strong><br>{{ $report->customer_phone ?? '-' }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.payment_method') }}:</strong><br>{{ $report->payment_method ?? '-' }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.total_amount') }}:</strong><br>{{ number_format((float) $report->total_amount, 2) }}</div>
                    <div class="col-md-3 mb-3"><strong>{{ __('index.created_at') }}:</strong><br>{{ optional($report->created_at)->format('Y-m-d H:i') }}</div>
                    <div class="col-md-12 mb-3"><strong>{{ __('index.notes') }}:</strong><br>{{ $report->note ?? '-' }}</div>
                    <div class="col-md-12 mb-3"><strong>{{ __('index.extracted_text') }}:</strong><br><pre class="mb-0 text-wrap">{{ $report->extracted_text ?? '-' }}</pre></div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('index.items') }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.product_name') }}</th>
                            <th>SKU</th>
                            <th>{{ __('index.primary_identifier') }}</th>
                            <th>IMEI</th>
                            <th>{{ __('index.serial_number') }}</th>
                            <th class="text-center">{{ __('index.qty') }}</th>
                            <th class="text-end">{{ __('index.unit_price') }}</th>
                            <th class="text-end">{{ __('index.subtotal') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($report->lines as $line)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $line->product_name ?? '-' }}</td>
                                <td>{{ $line->sku ?? '-' }}</td>
                                <td>{{ $line->primary_identifier ?? '-' }}</td>
                                <td>{{ $line->imei ?? '-' }}</td>
                                <td>{{ $line->serial_number ?? '-' }}</td>
                                <td class="text-center">{{ number_format((float) $line->qty, 2) }}</td>
                                <td class="text-end">{{ $line->unit_price !== null ? number_format((float) $line->unit_price, 2) : '-' }}</td>
                                <td class="text-end">{{ $line->subtotal !== null ? number_format((float) $line->subtotal, 2) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center"><b>{{ __('index.no_records_found') }}</b></td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($report->photos->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('index.photos') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($report->photos as $photo)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="{{ $photo->photo_url }}" target="_blank">
                                    <img src="{{ $photo->photo_url }}" alt="{{ $photo->original_name ?? __('index.photo') }}" class="img-fluid rounded border">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection
