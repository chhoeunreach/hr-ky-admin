@extends('layouts.master')

@section('title', __('index.sell_staff_report_detail'))

@section('action', __('index.sell_staff_report_detail'))

@section('styles')
    <style>
        .sell-out-photo-card {
            height: 100%;
        }

        .sell-out-photo-thumb {
            aspect-ratio: 4 / 3;
            background: #f8f9fa;
            cursor: zoom-in;
            object-fit: contain;
            width: 100%;
        }

        .sell-out-photo-thumb:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            outline: 0;
        }

        .sell-out-ocr-image {
            max-height: 55vh;
            object-fit: contain;
            width: 100%;
        }

        .sell-out-ocr-preview {
            background: #f8f9fa;
            position: relative;
        }

        .sell-out-ocr-preview .sell-out-ocr-image {
            cursor: pointer;
            display: block;
        }

        .sell-out-ocr-word-layer {
            bottom: 0;
            left: 0;
            pointer-events: none;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 1;
        }

        .sell-out-ocr-word {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(13, 110, 253, 0.42);
            border-radius: 0.25rem;
            color: #212529;
            cursor: text;
            font-size: 0.75rem;
            line-height: 1.1;
            overflow: hidden;
            padding: 0.05rem 0.2rem;
            pointer-events: auto;
            position: absolute;
            text-align: left;
            user-select: text;
            white-space: nowrap;
        }

        .sell-out-ocr-word:hover {
            background: rgba(13, 110, 253, 0.14);
        }

        .sell-out-ocr-quick-actions {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 0.75rem 0.75rem 0 0;
            bottom: 0;
            box-shadow: 0 -0.25rem 1rem rgba(0, 0, 0, 0.12);
            display: none;
            left: 0;
            padding: 0.75rem;
            position: absolute;
            right: 0;
            z-index: 2;
        }

        .sell-out-ocr-quick-actions.is-visible {
            display: block;
        }

        .sell-out-ocr-quick-message {
            background: rgba(33, 37, 41, 0.82);
            border-radius: 0.375rem;
            color: #fff;
            display: none;
            left: 50%;
            max-width: calc(100% - 2rem);
            padding: 0.5rem 0.75rem;
            position: absolute;
            text-align: center;
            top: 1rem;
            transform: translateX(-50%);
            z-index: 3;
        }

        .sell-out-ocr-result {
            min-height: 170px;
            resize: vertical;
        }

        .sell-out-serials {
            max-height: 220px;
            overflow-y: auto;
        }

        .sell-out-serial-row {
            gap: 0.5rem;
        }

        @media (max-width: 767.98px) {
            .sell-out-ocr-image {
                max-height: 38vh;
            }
        }
    </style>
@endsection

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
                                <div class="sell-out-photo-card border rounded p-2">
                                    <button type="button"
                                            class="btn p-0 border-0 bg-transparent w-100 sell-out-ocr-trigger"
                                            data-ocr-image-url="{{ $photo->photo_url }}"
                                            data-ocr-image-title="{{ $photo->original_name ?? __('index.photo') }}"
                                            aria-label="Open OCR for {{ $photo->original_name ?? __('index.photo') }}">
                                        <img src="{{ $photo->photo_url }}"
                                             alt="{{ $photo->original_name ?? __('index.photo') }}"
                                             class="img-fluid rounded border sell-out-photo-thumb"
                                             tabindex="0">
                                    </button>
                                    <div class="d-flex flex-wrap mt-2" style="gap: 0.5rem;">
                                        <a href="{{ $photo->photo_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                            View Image
                                        </a>
                                        <button type="button"
                                                class="btn btn-primary btn-sm sell-out-ocr-trigger"
                                                data-ocr-image-url="{{ $photo->photo_url }}"
                                                data-ocr-image-title="{{ $photo->original_name ?? __('index.photo') }}">
                                            Extract Text OCR
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="sellOutOcrModal" tabindex="-1" role="dialog" aria-labelledby="sellOutOcrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sellOutOcrModalLabel">Image OCR</h5>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6 mb-3 mb-lg-0">
                                <h6>Image Preview</h6>
                                <div class="sell-out-ocr-preview rounded border overflow-hidden">
                                    <img src="" alt="OCR image preview" id="sellOutOcrImage" class="sell-out-ocr-image">
                                    <div id="sellOutOcrWordLayer" class="sell-out-ocr-word-layer"></div>
                                    <div id="sellOutOcrQuickMessage" class="sell-out-ocr-quick-message">Reading text from image...</div>
                                    <div id="sellOutOcrQuickActions" class="sell-out-ocr-quick-actions">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <strong>Text detected</strong>
                                            <button type="button" class="btn btn-link btn-sm text-muted p-0" id="sellOutHideQuickActions">Close</button>
                                        </div>
                                        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                                            <button type="button" class="btn btn-primary btn-sm" id="sellOutQuickCopyText">
                                                Copy text
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="sellOutQuickCopySerial" disabled>
                                                Copy serial
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="mb-0">OCR Result</h6>
                                    <span id="sellOutOcrProgress" class="text-muted small"></span>
                                </div>
                                <textarea id="sellOutOcrText" class="form-control sell-out-ocr-result" readonly placeholder="Reading text from image..."></textarea>

                                <div class="mt-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0">Detected Serials</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="sellOutCopyFirstSerial" disabled>
                                            Copy Serial
                                        </button>
                                    </div>
                                    <div id="sellOutDetectedSerials" class="sell-out-serials border rounded p-2 text-muted">
                                        No serials detected yet.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" id="sellOutCopyOcrText" disabled>Copy Text</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/sell-out-ocr.js') }}"></script>
@endsection
