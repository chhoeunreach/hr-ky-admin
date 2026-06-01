<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyAttendanceBonusExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'ខែ',
            'លេខ',
            'ID',
            'ឈ្មោះបុគ្គលិក',
            'ឈ្មោះអង់គ្លេស',
            'ទីតាំងការងារ',
            'លេខទូរស័ព្ទ',
            'ប្រភេទចំណាយ',
            'មូលហេតុ',
            'បានបង់',
            'ផ្ដល់តាមរយៈ',
            'អ្នកទទួល',
            'ផ្សេងៗ',
            'ខែឆ្នាំ',
            'IDខែឆ្នាំមូលហេតុ',
            'staff-email',
            'email',
            'create_at',
            'by',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A:S')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
