<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{

    public function run(): void
    {
        Company::updateOrCreate(
            ['email' => 'kneayerng@gmail.com'],
            [
                'name' => 'Kneayerng',
                'phone' => '016910505',
                'owner_name' => 'chheansovanra',
                'address' => 'Phnom Penh, Cambodia ',
                'logo' => '',
            ]
        );
    }
}
