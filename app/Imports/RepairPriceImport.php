<?php

namespace App\Imports;

use App\Models\RepairPrice\RepairBrand;
use App\Models\RepairPrice\RepairCategory;
use App\Models\RepairPrice\RepairDevice;
use App\Models\RepairPrice\RepairDeviceType;
use App\Models\RepairPrice\RepairPrice;
use App\Models\RepairPrice\RepairPriceHistory;
use App\Models\RepairPrice\RepairService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RepairPriceImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $inserted = 0;
    private int $updated = 0;
    private bool $updateExisting;

    public function __construct(bool $updateExisting = true)
    {
        $this->updateExisting = $updateExisting;
    }

    /**
     * @throws Exception
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $row = collect($row);
            $line = $index + 2;

            $brandName = $this->stringValue($row, 'brand');
            $deviceTypeName = $this->stringValue($row, 'device_type', 'Smartphone');
            $modelName = $this->stringValue($row, 'model') ?? $this->stringValue($row, 'device');
            $categoryName = $this->stringValue($row, 'category');
            $serviceName = $this->stringValue($row, 'service');
            $sellingPrice = $this->numericValue($row, 'selling_price');

            if (!$brandName) {
                throw new Exception("Row {$line}: Brand is required.");
            }
            if (!$modelName) {
                throw new Exception("Row {$line}: Device/Model name is required.");
            }
            if (!$categoryName) {
                throw new Exception("Row {$line}: Category is required.");
            }
            if (!$serviceName) {
                throw new Exception("Row {$line}: Service name is required.");
            }
            if ($sellingPrice === null || $sellingPrice < 0) {
                throw new Exception("Row {$line}: Selling price must be a valid non-negative number.");
            }

            // 1. Resolve or Create Brand
            $brand = RepairBrand::firstOrCreate(
                ['slug' => Str::slug($brandName)],
                ['name' => $brandName, 'status' => true]
            );

            // 2. Resolve or Create Device Type
            $deviceType = RepairDeviceType::firstOrCreate(
                ['slug' => Str::slug($deviceTypeName)],
                ['name' => $deviceTypeName, 'status' => true]
            );

            // 3. Resolve or Create Device Model
            $modelNumber = $this->stringValue($row, 'model_number');
            $device = RepairDevice::firstOrCreate(
                [
                    'repair_brand_id' => $brand->id,
                    'name' => $modelName,
                ],
                [
                    'repair_device_type_id' => $deviceType->id,
                    'model_number' => $modelNumber,
                    'status' => true,
                ]
            );

            // 4. Resolve or Create Category
            $categoryNameKh = $this->stringValue($row, 'category_kh');
            $category = RepairCategory::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'name_kh' => $categoryNameKh,
                    'status' => true,
                ]
            );

            // 5. Resolve or Create Service
            $serviceNameKh = $this->stringValue($row, 'service_kh');
            $service = RepairService::firstOrCreate(
                [
                    'repair_category_id' => $category->id,
                    'name' => $serviceName,
                ],
                [
                    'name_kh' => $serviceNameKh,
                    'description' => $this->stringValue($row, 'description'),
                    'status' => true,
                ]
            );

            // 6. Resolve or Create Repair Price
            $partName = $this->stringValue($row, 'part_name');
            $partType = $this->stringValue($row, 'part_type', 'Original');
            $partCost = $this->numericValue($row, 'part_cost', 0);
            $serviceFee = $this->numericValue($row, 'service_fee', 0);
            $warrantyPeriod = $this->stringValue($row, 'warranty_period') ?? $this->stringValue($row, 'warranty', '3 Months');
            $status = $this->stringValue($row, 'status') ?? $this->stringValue($row, 'availability_status', 'available');

            if (!in_array($status, ['available', 'out_of_stock', 'pre_order'])) {
                $status = 'available';
            }

            $existingPrice = RepairPrice::where('repair_device_id', $device->id)
                ->where('repair_service_id', $service->id)
                ->where(function ($q) use ($partType) {
                    $q->where('part_type', $partType)
                      ->orWhereNull('part_type');
                })
                ->first();

            $priceData = [
                'repair_device_id' => $device->id,
                'repair_service_id' => $service->id,
                'part_name' => $partName,
                'part_type' => $partType,
                'part_cost' => $partCost,
                'service_fee' => $serviceFee,
                'selling_price' => $sellingPrice,
                'warranty_period' => $warrantyPeriod,
                'status' => $status,
                'availability_status' => $status,
                'updated_by' => Auth::id(),
            ];

            if ($existingPrice) {
                if ($this->updateExisting) {
                    $oldSellingPrice = $existingPrice->selling_price;
                    $oldCost = $existingPrice->part_cost;
                    $oldFee = $existingPrice->service_fee;

                    $existingPrice->update($priceData);

                    if ($oldSellingPrice != $sellingPrice || $oldCost != $partCost || $oldFee != $serviceFee) {
                        RepairPriceHistory::create([
                            'repair_price_id' => $existingPrice->id,
                            'old_part_cost' => $oldCost,
                            'new_part_cost' => $partCost,
                            'old_service_fee' => $oldFee,
                            'new_service_fee' => $serviceFee,
                            'old_selling_price' => $oldSellingPrice,
                            'new_selling_price' => $sellingPrice,
                            'changed_by' => Auth::id(),
                            'created_at' => now(),
                        ]);
                    }
                    $this->updated++;
                }
            } else {
                $priceData['created_by'] = Auth::id();
                RepairPrice::create($priceData);
                $this->inserted++;
            }
        }
    }

    public function insertedCount(): int
    {
        return $this->inserted;
    }

    public function updatedCount(): int
    {
        return $this->updated;
    }

    private function stringValue(Collection $row, string $key, ?string $default = null): ?string
    {
        if (!$row->has($key) || $row->get($key) === null) {
            return $default;
        }

        $value = trim((string) $row->get($key));

        return $value === '' ? $default : $value;
    }

    private function numericValue(Collection $row, string $key, ?float $default = null): ?float
    {
        if (!$row->has($key) || $row->get($key) === null || $row->get($key) === '') {
            return $default;
        }

        $clean = preg_replace('/[^0-9.]/', '', (string) $row->get($key));

        return is_numeric($clean) ? (float) $clean : $default;
    }
}
