<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = (int) (DB::table('companies')->value('id') ?? 1);
        $branchIds = DB::table('branches')->pluck('id')->toArray();
        $now = now();

        $departments = [
            'ផ្នែកមេឌៀរ',
            'ផ្នែករំលោះ',
            'ផ្នែកលក់',
            'គិតលុយ',
            'ជំនួយការរដ្ឋបាល',
            'ផ្នែកជាង',
            'បុគ្គលិកក្រុមហ៊ុន',
            'ថ្នាក់ដឹកនាំ',
            'ផ្នែកស្តុក',
            'ផ្នែកដឹកជញ្ជូន',
            'ផ្នែកអនឡាញ',
            'ផ្នែកមេផ្ទះ',
            'ផ្នែកទិន្ន័យ',
            'អ៊ុត',
            'ផ្នែកសន្តិសុខ',
            'បច្ចេកទេស',
            'ការហ្វេរ',
            'អ្នកនិពន្ធ',
            'អ្នកទទួលភ្ញៀវ',
        ];

        foreach ($branchIds as $branchId) {
            foreach ($departments as $departmentName) {
                DB::table('departments')->updateOrInsert(
                    [
                        'dept_name' => $departmentName,
                        'branch_id' => $branchId,
                    ],
                    [
                        'slug' => 'dept-' . substr(md5($departmentName . '|' . $branchId), 0, 12),
                        'address' => 'N/A',
                        'phone' => null,
                        'is_active' => 1,
                        'dept_head_id' => null,
                        'company_id' => $companyId,
                        'created_by' => null,
                        'updated_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
