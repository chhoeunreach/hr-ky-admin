<?php

namespace App\Exports\DatabaseData;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceCollectionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function collection()
    {
        $select = ['user_id', 'attendance_date'];
        $with = ['employee:id,name,employee_code'];
        $attendanceCollection = new Collection();

        Attendance::with($with)->select($select)
            ->chunk(100, function ($attendances) use ($attendanceCollection) {
                foreach ($attendances as $data) {
                    $employeeCode = $data->employee->employee_code ?? '';
                    $attendanceDate = $data->attendance_date;

                    $attendanceCollection->push([
                        'id' => $attendanceDate . $employeeCode,
                        'date' => $attendanceDate,
                        'employee_id' => $employeeCode,
                        'employee_name' => $data->employee->name ?? '',
                    ]);
                }
            });

        return $attendanceCollection;
    }

    public function headings(): array
    {
        return [
            'Id',
            'Date',
            'ID',
            'Employee Name',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}
