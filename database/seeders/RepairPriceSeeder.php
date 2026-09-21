<?php

namespace Database\Seeders;

use App\Models\RepairPrice\RepairBrand;
use App\Models\RepairPrice\RepairCategory;
use App\Models\RepairPrice\RepairDevice;
use App\Models\RepairPrice\RepairDeviceType;
use App\Models\RepairPrice\RepairPrice;
use App\Models\RepairPrice\RepairService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RepairPriceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'brand' => 'Apple',
                'device_type' => 'Phone',
                'model' => 'iPhone 15 Pro Max',
                'model_number' => 'A3106',
                'category' => 'Screen',
                'category_kh' => 'អេក្រង់',
                'service' => 'Screen Replacement',
                'service_kh' => 'ដូរអេក្រង់',
                'description' => 'High quality OLED assembly replacement',
                'part_name' => 'OLED Screen Assembly',
                'part_type' => 'Original',
                'part_cost' => 180,
                'service_fee' => 20,
                'selling_price' => 220,
                'warranty_days' => 90,
                'estimated_minutes' => 60,
            ],
            [
                'brand' => 'Apple',
                'device_type' => 'Phone',
                'model' => 'iPhone 15 Pro Max',
                'model_number' => 'A3106',
                'category' => 'Battery',
                'category_kh' => 'ថ្ម',
                'service' => 'Battery Replacement',
                'service_kh' => 'ដូរថ្ម',
                'description' => 'Original spec battery replacement',
                'part_name' => 'Battery Cell',
                'part_type' => 'OEM',
                'part_cost' => 45,
                'service_fee' => 15,
                'selling_price' => 65,
                'warranty_days' => 90,
                'estimated_minutes' => 45,
            ],
            [
                'brand' => 'Samsung',
                'device_type' => 'Phone',
                'model' => 'Galaxy S24 Ultra',
                'model_number' => 'SM-S928B',
                'category' => 'Screen',
                'category_kh' => 'អេក្រង់',
                'service' => 'Screen Replacement',
                'service_kh' => 'ដូរអេក្រង់',
                'description' => 'Dynamic AMOLED 2X display replacement',
                'part_name' => 'AMOLED Assembly',
                'part_type' => 'Original',
                'part_cost' => 195,
                'service_fee' => 25,
                'selling_price' => 240,
                'warranty_days' => 90,
                'estimated_minutes' => 70,
            ],
            [
                'brand' => 'Samsung',
                'device_type' => 'Phone',
                'model' => 'Galaxy S24 Ultra',
                'model_number' => 'SM-S928B',
                'category' => 'Battery',
                'category_kh' => 'ថ្ម',
                'service' => 'Battery Replacement',
                'service_kh' => 'ដូរថ្ម',
                'description' => '5000mAh battery replacement',
                'part_name' => 'Battery Pack',
                'part_type' => 'Original',
                'part_cost' => 48,
                'service_fee' => 12,
                'selling_price' => 65,
                'warranty_days' => 90,
                'estimated_minutes' => 45,
            ],
            [
                'brand' => 'Apple',
                'device_type' => 'Laptop',
                'model' => 'MacBook Air M2',
                'model_number' => 'A2681',
                'category' => 'Keyboard',
                'category_kh' => 'ក្តារចុច',
                'service' => 'Keyboard Replacement',
                'service_kh' => 'ដូរក្តារចុច',
                'description' => 'Complete keyboard top case assembly',
                'part_name' => 'Keyboard Assembly',
                'part_type' => 'Original',
                'part_cost' => 95,
                'service_fee' => 25,
                'selling_price' => 120,
                'warranty_days' => 30,
                'estimated_minutes' => 120,
            ],
        ];

        foreach ($rows as $index => $row) {
            $brand = RepairBrand::updateOrCreate(
                ['slug' => Str::slug($row['brand'])],
                ['name' => $row['brand'], 'status' => true]
            );

            $deviceType = RepairDeviceType::updateOrCreate(
                ['slug' => Str::slug($row['device_type'])],
                ['name' => $row['device_type'], 'status' => true]
            );

            $device = RepairDevice::updateOrCreate(
                [
                    'repair_brand_id' => $brand->id,
                    'name' => $row['model'],
                ],
                [
                    'repair_device_type_id' => $deviceType->id,
                    'model_number' => $row['model_number'],
                    'status' => true,
                ]
            );

            $category = RepairCategory::updateOrCreate(
                ['slug' => Str::slug($row['category'])],
                [
                    'name' => $row['category'],
                    'name_kh' => $row['category_kh'],
                    'sort_order' => $index + 1,
                    'status' => true,
                ]
            );

            $service = RepairService::updateOrCreate(
                [
                    'repair_category_id' => $category->id,
                    'name' => $row['service'],
                ],
                [
                    'name_kh' => $row['service_kh'],
                    'description' => $row['description'],
                    'status' => true,
                ]
            );

            RepairPrice::updateOrCreate(
                [
                    'repair_device_id' => $device->id,
                    'repair_service_id' => $service->id,
                    'part_type' => $row['part_type'],
                ],
                [
                    'part_name' => $row['part_name'],
                    'part_cost' => $row['part_cost'],
                    'service_fee' => $row['service_fee'],
                    'selling_price' => $row['selling_price'],
                    'warranty_days' => $row['warranty_days'],
                    'estimated_minutes' => $row['estimated_minutes'],
                    'availability_status' => 'available',
                    'note' => null,
                ]
            );
        }
    }
}
