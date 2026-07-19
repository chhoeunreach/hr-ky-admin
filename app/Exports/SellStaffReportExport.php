<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SellStaffReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(private readonly Collection $reports)
    {
    }

    public function collection(): Collection
    {
        return $this->reports->map(function ($report) {
            return [
                'seller_name' => $report->seller_name ?: ($report->user?->name ?? ''),
                'employee' => $report->user?->name ?? '',
                'branch' => $report->branch_name ?? '',
                'sell_type' => $report->display_service_type,
                'invoice_no' => $report->invoice_no ?? '',
                'original_invoice_no' => $report->original_invoice_no ?? '',
                'customer_name' => $report->customer_name ?? '',
                'customer_phone' => $report->customer_phone ?? '',
                'payment_method' => $report->payment_method ?? '',
                'items' => $report->lines_count ?? $report->lines->count(),
                'photos' => $report->photos_count ?? $report->photos->count(),
                'total_amount' => $report->total_amount ?? 0,
                'submitted_at' => optional($report->created_at)->format('Y-m-d H:i:s'),
                'note' => trim(strip_tags((string) ($report->note ?? ''))),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Seller Name',
            'Employee',
            'Branch',
            'Sell Type',
            'Invoice No',
            'Original Invoice No',
            'Customer Name',
            'Customer Phone',
            'Payment Method',
            'Items',
            'Photos',
            'Total Amount',
            'Submitted At',
            'Note',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
