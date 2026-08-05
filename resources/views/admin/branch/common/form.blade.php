<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.company_name') }} <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="company_id">
            <option selected value="{{ isset($company) ? $company->id : '' }}">{{ isset($company) ? $company->name : '' }}</option>
        </select>
    </div>


    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.branch_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" required name="name" value="{{ isset($branch) ? $branch->name : '' }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.branch_head') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="branch_head_id">
            <option value="" {{ !isset($branch) ? 'selected' : '' }} disabled>{{ __('index.select_branch_head') }}</option>
            @foreach($users as $key => $user)
                <option value="{{ $user->id }}" {{ isset($branch) && $branch->branch_head_id  == $user->id ? 'selected' : '' }}>{{ ucfirst($user->name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="address" class="form-label">{{ __('index.address') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="address" required name="address" value="{{ isset($branch) ? $branch->address : old('address') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="phone" class="form-label">{{ __('index.phone_number') }} <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" required name="phone" value="{{ isset($branch) ? $branch->phone : old('phone') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="logo" class="form-label">{{ __('index.branch_logo') }}</label>
        <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
        @if(isset($branch) && $branch->logo)
            <img src="{{ asset(\App\Models\Branch::UPLOAD_PATH.$branch->logo) }}"
                 alt="{{ __('index.branch_logo') }}"
                 style="object-fit: contain"
                 class="mt-3 ht-100 wd-100">
        @endif
    </div>

    <div class="col-12 mb-4">
        <label class="form-label">{{ __('index.payment_qr_codes') }}</label>
        <div id="paymentQrCodeRows">
            @php
                $paymentQrCodes = old('payment_qr_codes', isset($branch) ? ($branch->payment_qr_codes ?? []) : []);
                $paymentQrCodes = count($paymentQrCodes) ? $paymentQrCodes : [['payment_name' => '', 'qr_code' => '']];
            @endphp
            @foreach($paymentQrCodes as $qrIndex => $paymentQrCode)
                <div class="row align-items-end payment-qr-code-row">
                    <div class="col-lg-4 col-md-5 mb-3">
                        <label class="form-label">{{ __('index.payment_name') }}</label>
                        <input type="text"
                               class="form-control"
                               name="payment_qr_codes[{{ $qrIndex }}][payment_name]"
                               value="{{ $paymentQrCode['payment_name'] ?? '' }}"
                               placeholder="{{ __('index.payment_method_name') }}">
                    </div>
                    <div class="col-lg-4 col-md-5 mb-3">
                        <label class="form-label">{{ __('index.qr_image') }}</label>
                        <input type="file"
                               class="form-control"
                               name="payment_qr_codes[{{ $qrIndex }}][qr_code]"
                               accept="image/*">
                        @if(!empty($paymentQrCode['qr_code']))
                            <input type="hidden"
                                   name="payment_qr_codes[{{ $qrIndex }}][existing_qr_code]"
                                   value="{{ $paymentQrCode['qr_code'] }}">
                            <img src="{{ asset(\App\Models\Branch::UPLOAD_PATH.$paymentQrCode['qr_code']) }}"
                                 alt="{{ $paymentQrCode['payment_name'] ?? __('index.qr_image') }}"
                                 style="object-fit: contain"
                                 class="mt-2 ht-100 wd-100">
                        @endif
                    </div>
                    <div class="col-lg-2 col-md-2 mb-3">
                        <button type="button" class="btn btn-danger removePaymentQrCode">
                            <i class="link-icon" data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addPaymentQrCode">
            <i class="link-icon" data-feather="plus"></i> {{ __('index.add_payment_qr_code') }}
        </button>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_location_latitude" class="form-label">{{ __('index.branch_location_latitude') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="branch_location_latitude" required name="branch_location_latitude" value="{{ isset($branch) ? $branch->branch_location_latitude : old('branch_location_latitude') }}" autocomplete="off" placeholder="{{ __('index.enter_branch_location_latitude') }}">
    </div>

     <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_location_longitude" class="form-label">{{ __('index.branch_location_longitude') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="branch_location_longitude" required name="branch_location_longitude" value="{{ isset($branch) ? $branch->branch_location_longitude : old('branch_location_longitude') }}" autocomplete="off" placeholder="{{ __('index.enter_branch_location_longitude') }}">
    </div>

    <div class="col-lg-4 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" {{ !isset($branch) ? 'selected' : '' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($branch) && $branch->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active') }}</option>
            <option value="0" {{ isset($branch) && $branch->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive') }}</option>
        </select>
    </div>

    <div class="col-lg-6 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ isset($branch) ? __('index.update') : __('index.create') }}</button>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            let paymentQrCodeIndex = $('.payment-qr-code-row').length;

            $('#addPaymentQrCode').on('click', function () {
                $('#paymentQrCodeRows').append(`
                    <div class="row align-items-end payment-qr-code-row">
                        <div class="col-lg-4 col-md-5 mb-3">
                            <label class="form-label">{{ __('index.payment_name') }}</label>
                            <input type="text"
                                   class="form-control"
                                   name="payment_qr_codes[${paymentQrCodeIndex}][payment_name]"
                                   placeholder="{{ __('index.payment_method_name') }}">
                        </div>
                        <div class="col-lg-4 col-md-5 mb-3">
                            <label class="form-label">{{ __('index.qr_image') }}</label>
                            <input type="file"
                                   class="form-control"
                                   name="payment_qr_codes[${paymentQrCodeIndex}][qr_code]"
                                   accept="image/*">
                        </div>
                        <div class="col-lg-2 col-md-2 mb-3">
                            <button type="button" class="btn btn-danger removePaymentQrCode">
                                <i class="link-icon" data-feather="trash-2"></i>
                            </button>
                        </div>
                    </div>
                `);

                paymentQrCodeIndex++;

                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            });

            $('body').on('click', '.removePaymentQrCode', function () {
                if ($('.payment-qr-code-row').length > 1) {
                    $(this).closest('.payment-qr-code-row').remove();
                }
            });
        });
    </script>
@endsection
