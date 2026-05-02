<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdvanceSalaryExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(private readonly Collection $advanceSalaries)
    {
    }

    public function collection()
    {
        return $this->advanceSalaries->map(function ($advanceSalary) {
            $requestedBy = $advanceSalary->requestedBy;
            $verifiedBy = $advanceSalary->verifiedBy;
            $requestedDate = $advanceSalary->advance_requested_date;
            $monthYear = $requestedDate ? date('m-Y', strtotime($requestedDate)) : '';

            return [
                'date' => $requestedDate ? date('Y-m-d', strtotime($requestedDate)) : '',
                'username_month_year' => ($requestedBy->username ?? '') . ($monthYear ? '-' . $monthYear : ''),
                'username' => $requestedBy->username ?? '',
                'name' => $requestedBy->name ?? '',
                'reason' => trim(strip_tags((string) ($advanceSalary->description ?? ''))),
                'branch' => $requestedBy?->branch?->name ?? '',
                'phone_number' => $requestedBy->phone ?? '',
                'type' => 'Advance Salary',
                'loan_amount' => $advanceSalary->requested_amount ?? 0,
                'payment' => $advanceSalary->released_amount ?? 0,
                'approved_by' => $verifiedBy->name ?? 'Admin',
                'remark' => $advanceSalary->remark ?? '',
                'month_year' => $monthYear,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Username+Month-Year',
            'Username',
            'Name',
            'Reason',
            'Branch',
            'Phone Number',
            'Type',
            'Loan Amount',
            'Payment',
            'Approve By',
            'Remark',
            'Month-Year',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

