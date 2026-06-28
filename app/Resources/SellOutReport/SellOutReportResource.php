<?php

namespace App\Resources\SellOutReport;

use Illuminate\Http\Resources\Json\JsonResource;

class SellOutReportResource extends JsonResource
{
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
            'total_amount' => number_format((float) $this->total_amount, 2, '.', ''),
            'commission' => number_format((float) $this->commission, 2, '.', ''),
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];

        $data['lines_count'] = $this->lines_count ?? $this->lines->count();
        $data['photos_count'] = $this->photos_count ?? $this->photos->count();

        $data['lines'] = $this->lines->map(fn ($line) => [
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
            'unit_price' => number_format((float) $line->unit_price, 2, '.', ''),
            'subtotal' => number_format((float) $line->subtotal, 2, '.', ''),
            'photos' => $this->photos
                ->where('sell_out_report_line_id', $line->id)
                ->map(fn ($photo) => [
                    'id' => $photo->id,
                    'photo_path' => $photo->photo_path,
                    'photo_url' => $photo->photo_url,
                ])
                ->values(),
        ])->values();

        $data['photos'] = $this->photos
            ->whereNull('sell_out_report_line_id')
            ->map(fn ($photo) => [
                'id' => $photo->id,
                'photo_path' => $photo->photo_path,
                'photo_url' => $photo->photo_url ?: Storage::disk('public')->url($photo->photo_path),
            ])
            ->values();

        return $data;
    }
}
