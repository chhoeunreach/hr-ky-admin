@extends('layouts.master')

@section('title', __('index.add_sell_staff_report'))

@section('action', __('index.add_sell_staff_report'))

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.sell-staff-report.index') }}">{{ __('index.sell_staff_report') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('index.add_new') }}</li>
            </ol>
            <a href="{{ route('admin.sell-staff-report.index') }}" class="btn btn-secondary btn-sm">{{ __('index.button_back') }}</a>
        </nav>

        <form action="{{ route('admin.sell-staff-report.store') }}" method="post">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('index.sell_staff_report_detail') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">{{ __('index.original_invoice_no') }}</label>
                            <input type="text" name="original_invoice_no" class="form-control" value="{{ old('original_invoice_no') }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">{{ __('index.seller_name') }}</label>
                            <input type="text" name="seller_name" class="form-control" value="{{ old('seller_name') }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">{{ __('index.branch_name') }}</label>
                            <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name') }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">{{ __('index.customer_name') }}</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">{{ __('index.customer_phone') }}</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="service_type" class="form-select">
                                @foreach(\App\Models\TelegramGroup::sellOutEventOptions() as $eventKey => $label)
                                    <option value="{{ $label }}" {{ old('service_type', 'លក់') === $label ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label">{{ __('index.payment_method') }}</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method') }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('index.notes') }}</label>
                            <textarea name="note" rows="3" class="form-control">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">{{ __('index.product_lines') }}</h6>
                    <button type="button" class="btn btn-primary btn-sm" id="addLineBtn">{{ __('index.add_line') }}</button>
                </div>
                <div class="card-body">
                    @error('lines')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <div class="table-responsive">
                        <table class="table" id="productLinesTable">
                            <thead>
                            <tr>
                                <th>{{ __('index.product_name') }}</th>
                                <th>SKU</th>
                                <th>IMEI</th>
                                <th>IMEI 2</th>
                                <th>{{ __('index.serial_number') }}</th>
                                <th>{{ __('index.model_number') }}</th>
                                <th>{{ __('index.color') }}</th>
                                <th>{{ __('index.storage') }}</th>
                                <th>{{ __('index.qty') }}</th>
                                <th>{{ __('index.unit_price') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $oldLines = old('lines', [['qty' => 1]]);
                            @endphp
                            @foreach($oldLines as $index => $line)
                                <tr>
                                    <td><input type="text" name="lines[{{ $index }}][product_name]" class="form-control" value="{{ $line['product_name'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][sku]" class="form-control" value="{{ $line['sku'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][imei]" class="form-control" value="{{ $line['imei'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][imei2]" class="form-control" value="{{ $line['imei2'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][serial_number]" class="form-control" value="{{ $line['serial_number'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][model_number]" class="form-control" value="{{ $line['model_number'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][color]" class="form-control" value="{{ $line['color'] ?? '' }}"></td>
                                    <td><input type="text" name="lines[{{ $index }}][storage]" class="form-control" value="{{ $line['storage'] ?? '' }}"></td>
                                    <td><input type="number" min="1" step="0.01" name="lines[{{ $index }}][qty]" class="form-control" value="{{ $line['qty'] ?? 1 }}"></td>
                                    <td><input type="number" min="0" step="0.01" name="lines[{{ $index }}][unit_price]" class="form-control" value="{{ $line['unit_price'] ?? '' }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-line">{{ __('index.delete') }}</button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('admin.sell-staff-report.index') }}" class="btn btn-warning">{{ __('index.clear') }}</a>
                        <button type="submit" class="btn btn-success">{{ __('index.save') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector('#productLinesTable tbody');
            const addLineBtn = document.getElementById('addLineBtn');
            let lineIndex = tableBody.querySelectorAll('tr').length;

            const buildInput = (index, key, type = 'text', value = '') => {
                const step = type === 'number' ? ' step="0.01"' : '';
                const min = type === 'number' ? ' min="0"' : '';
                return `<input type="${type}"${min}${step} name="lines[${index}][${key}]" class="form-control" value="${value}">`;
            };

            addLineBtn.addEventListener('click', function () {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${buildInput(lineIndex, 'product_name')}</td>
                    <td>${buildInput(lineIndex, 'sku')}</td>
                    <td>${buildInput(lineIndex, 'imei')}</td>
                    <td>${buildInput(lineIndex, 'imei2')}</td>
                    <td>${buildInput(lineIndex, 'serial_number')}</td>
                    <td>${buildInput(lineIndex, 'model_number')}</td>
                    <td>${buildInput(lineIndex, 'color')}</td>
                    <td>${buildInput(lineIndex, 'storage')}</td>
                    <td><input type="number" min="1" step="0.01" name="lines[${lineIndex}][qty]" class="form-control" value="1"></td>
                    <td>${buildInput(lineIndex, 'unit_price', 'number')}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-line">{{ __('index.delete') }}</button></td>
                `;
                tableBody.appendChild(row);
                lineIndex += 1;
            });

            tableBody.addEventListener('click', function (event) {
                if (!event.target.classList.contains('remove-line')) {
                    return;
                }

                if (tableBody.querySelectorAll('tr').length === 1) {
                    return;
                }

                event.target.closest('tr').remove();
            });
        });
    </script>
@endsection
