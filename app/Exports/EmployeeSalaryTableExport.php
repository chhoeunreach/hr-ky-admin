<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeSalaryTableExport extends StringValueBinder implements FromArray, WithHeadings, WithCustomValueBinder, ShouldAutoSize, WithStyles, WithCustomCsvSettings
{
    public function __construct(private array $headers, private array $rows) {}
    public function array(): array { return $this->rows; }
    public function headings(): array { return $this->headers; }
    public function getCsvSettings(): array { return ['use_bom' => true]; }
    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        return [1 => ['font' => ['bold' => true]]];
    }
}
