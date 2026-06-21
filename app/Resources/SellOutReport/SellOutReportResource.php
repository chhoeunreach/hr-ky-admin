<?php

namespace App\Resources\SellOutReport;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SellOutReportResource extends JsonResource
{
    private bool $summary = false;

    public function summary(): self
    {
        $this->summary = true;

        return $this;
    }

    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'original_invoice_no' => $this->original_invoice_no ?? '',
            'seller_name' => $this->seller_name,
            'branch_name' => $this->branch_name,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'service_type' => $this->service_type,
            'payment_method' => $this->payment_method,
            'note' => $this->note ?? '',
            'extracted_text' => $this->extracted_text ?? '',
            'total_amount' => (float) $this->total_amount,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];

        if ($this->summary) {
            $data['lines_count'] = $this->lines_count ?? $this->lines->count();
            $data['photos_count'] = $this->photos_count ?? $this->photos->count();
        }

        $data['lines'] = $this->summary ? [] : $this->lines->map(fn ($line) => [
            'id' => $line->id,
            'product_name' => $line->product_name,
            'sku' => $line->sku,
            'imei' => $line->imei ?? '',
            'imei2' => $line->imei2 ?? '',
            'serial_number' => $line->serial_number ?? '',
            'model_number' => $line->model_number ?? '',
            'color' => $line->color,
            'storage' => $line->storage,
            'qty' => (int) $line->qty,
            'unit_price' => (float) $line->unit_price,
        ])->values();

        $data['photos'] = $this->summary ? [] : $this->photos->map(fn ($photo) => [
            'id' => $photo->id,
            'photo_path' => $photo->photo_path,
            'photo_url' => Storage::disk('public')->url($photo->photo_path),
        ])->values();

        return $data;
    }
}
