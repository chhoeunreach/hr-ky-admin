<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchTableSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = (int) (DB::table('companies')->value('id') ?? 1);

        $branches = [
            ['id' => 1, 'name' => 'វីអាយភី'],
            ['id' => 2, 'name' => 'កម្ពុជាក្រោម'],
            ['id' => 3, 'name' => 'ស្តុកធំ'],
            ['id' => 4, 'name' => 'កាប់គោ'],
            ['id' => 5, 'name' => 'សើវីសសិនធើ'],
            ['id' => 6, 'name' => 'អ៊ីអន'],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(
                ['id' => $branch['id']],
                [
                    'name' => $branch['name'],
                    'address' => 'N/A',
                    'phone' => '0000000000',
                    'branch_head_id' => null,
                    'company_id' => $companyId,
                    'branch_location_latitude' => null,
                    'branch_location_longitude' => null,
                    'is_active' => 1,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

