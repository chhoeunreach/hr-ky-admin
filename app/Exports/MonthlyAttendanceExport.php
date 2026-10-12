<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class MonthlyAttendanceExport implements FromCollection, WithHeadings, WithColumnWidths, WithStrictNullComparison, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly array $headings,
        private readonly Collection $rows
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('C2');
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()
            ->setWrapText(true)->setVertical('center')->setHorizontal('center');
        $sheet->getStyle('B')->getAlignment()->setHorizontal('left');

        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDF0F2']]],
        ];
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 6, 'B' => 45];
        $dayColumnEnd = count($this->headings) - 12;
        for ($column = 3; $column <= count($this->headings); $column++) {
            $widths[Coordinate::stringFromColumnIndex($column)] = $column <= $dayColumnEnd ? 7 : 13;
        }

        return $widths;
    }
}
