<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::with(['company:id,name','branch:id,name','department:id,dept_name','post:id,post_name','officeTime:id,shift,opening_time,closing_time','supervisor:id,name'])->where('id','!=',1)->get();
    }

    /**
     * Map each row of data.
     *
     * @param User $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->address,
            $user->dob,
            $user->gender,
            $user->marital_status,
            $user->phone,
            $user->status,
            $user->employment_type,
            $user->user_type,
            $user->joining_date,
            $user->workspace_type == User::FIELD ? 'Field' : 'Office',
            $user?->company?->name ?? 'N/A',
            $user?->branch?->name ?? 'N/A',
            $user?->department?->dept_name ?? 'N/A',
            $user?->post?->post_name ?? 'N/A',
            $user?->officeTime?->shift .' ('.$user?->officeTime?->opening_time .'-'.$user?->officeTime?->closing_time.')' ?? 'N/A',
            $user?->supervisor?->name ?? 'N/A',
            $user->employee_code,
        ];
    }

    /**
     * Define the CSV headings.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Address',
            'Date of Birth',
            'Gender',
            'Marital Status',
            'Phone',
            'Status',
            'Employment Type',
            'User Type',
            'Joining Date',
            'Workspace Type',
            'Company',
            'Branch',
            'Department',
            'Post',
            'Shift',
            'Supervisor',
            'Employee Code',
        ];
    }
}
