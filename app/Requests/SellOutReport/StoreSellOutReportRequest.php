<?php

namespace App\Requests\SellOutReport;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSellOutReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'seller_name' => ['required', 'string', 'max:255'],
            'original_invoice_no' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'extracted_text' => ['nullable', 'string'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_name' => ['required', 'string', 'max:255'],
            'lines.*.sku' => ['nullable', 'string', 'max:255'],
            'lines.*.imei' => ['nullable', 'string', 'max:255'],
            'lines.*.imei2' => ['nullable', 'string', 'max:255'],
            'lines.*.serial_number' => ['nullable', 'string', 'max:255'],
            'lines.*.model_number' => ['nullable', 'string', 'max:255'],
            'lines.*.color' => ['nullable', 'string', 'max:255'],
            'lines.*.storage' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
