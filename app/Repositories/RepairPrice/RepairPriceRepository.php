<?php

namespace App\Repositories\RepairPrice;

use App\Models\RepairPrice\RepairBrand;
use App\Models\RepairPrice\RepairCategory;
use App\Models\RepairPrice\RepairDevice;
use App\Models\RepairPrice\RepairDeviceSeries;
use App\Models\RepairPrice\RepairDeviceType;
use App\Models\RepairPrice\RepairPrice;
use App\Models\RepairPrice\RepairPriceHistory;
use App\Models\RepairPrice\RepairService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RepairPriceRepository
{
    public function brands(): Collection
    {
        return RepairBrand::where('status', true)->orderBy('name')->get();
    }

    public function deviceTypes(): Collection
    {
        return RepairDeviceType::where('status', true)->orderBy('name')->get();
    }

    public function series(Request $request): Collection
    {
        return RepairDeviceSeries::with(['brand', 'deviceType'])
            ->where('status', true)
            ->when($request->filled('brand_id'), fn ($query) => $query->where('repair_brand_id', $request->brand_id))
            ->when($request->filled('brand'), fn ($query) => $query->whereHas('brand', fn ($brand) => $brand->where('slug', $request->brand)))
            ->when($request->filled('device_type_id'), fn ($query) => $query->where('repair_device_type_id', $request->device_type_id))
            ->when($request->filled('device_type'), fn ($query) => $query->whereHas('deviceType', fn ($type) => $type->where('slug', $request->device_type)))
            ->orderBy('name')
            ->get();
    }

    public function categories(): Collection
    {
        return RepairCategory::where('status', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function services(Request $request): LengthAwarePaginator
    {
        return RepairService::with('category')
            ->where('status', true)
            ->when($request->filled('category_id'), fn ($query) => $query->where('repair_category_id', $request->category_id))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $request->category)))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('name_kh', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage($request));
    }

    public function devices(Request $request): LengthAwarePaginator
    {
        return RepairDevice::with(['brand', 'deviceType', 'series'])
            ->where('status', true)
            ->when($request->filled('brand_id'), fn ($query) => $query->where('repair_brand_id', $request->brand_id))
            ->when($request->filled('brand'), fn ($query) => $query->whereHas('brand', fn ($brand) => $brand->where('slug', $request->brand)))
            ->when($request->filled('device_type_id'), fn ($query) => $query->where('repair_device_type_id', $request->device_type_id))
            ->when($request->filled('device_type'), fn ($query) => $query->whereHas('deviceType', fn ($type) => $type->where('slug', $request->device_type)))
            ->when($request->filled('series_id'), fn ($query) => $query->where('repair_device_series_id', $request->series_id))
            ->when($request->filled('series'), fn ($query) => $query->whereHas('series', fn ($series) => $series->where('slug', $request->series)))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('model_number', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
                        ->orWhereHas('series', fn ($series) => $series->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage($request));
    }

    public function deviceServices(RepairDevice $device, Request $request): LengthAwarePaginator
    {
        return RepairPrice::with(['service.category'])
            ->where('repair_device_id', $device->id)
            ->whereHas('service', fn ($query) => $query->where('status', true))
            ->when($request->filled('category_id'), fn ($query) => $query->whereHas('service', fn ($service) => $service->where('repair_category_id', $request->category_id)))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('service.category', fn ($category) => $category->where('slug', $request->category)))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('part_name', 'like', "%{$search}%")
                        ->orWhere('part_type', 'like', "%{$search}%")
                        ->orWhereHas('service', function ($service) use ($search) {
                            $service->where('name', 'like', "%{$search}%")
                                ->orWhere('name_kh', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('selling_price')
            ->paginate($this->perPage($request));
    }

    public function storeDevice(array $data): RepairDevice
    {
        return RepairDevice::create($data)->load(['brand', 'deviceType', 'series']);
    }

    public function updateDevice(RepairDevice $device, array $data): RepairDevice
    {
        $device->update($data);
        return $device->fresh(['brand', 'deviceType', 'series']);
    }

    public function deleteDevice(RepairDevice $device): bool
    {
        return (bool) $device->delete();
    }

    public function storeService(array $data): RepairService
    {
        return RepairService::create($data)->load('category');
    }

    public function updateService(RepairService $service, array $data): RepairService
    {
        $service->update($data);
        return $service->fresh('category');
    }

    public function deleteService(RepairService $service): bool
    {
        return (bool) $service->delete();
    }

    public function storePrice(array $data): RepairPrice
    {
        return RepairPrice::create($data)->load(['service.category']);
    }

    public function updatePrice(RepairPrice $price, array $data): RepairPrice
    {
        $before = $price->only(['part_cost', 'service_fee', 'selling_price']);
        $price->update($data);
        $after = $price->fresh();

        if ($before['part_cost'] != $after->part_cost || $before['service_fee'] != $after->service_fee || $before['selling_price'] != $after->selling_price) {
            RepairPriceHistory::create([
                'repair_price_id' => $after->id,
                'old_part_cost' => $before['part_cost'],
                'new_part_cost' => $after->part_cost,
                'old_service_fee' => $before['service_fee'],
                'new_service_fee' => $after->service_fee,
                'old_selling_price' => $before['selling_price'],
                'new_selling_price' => $after->selling_price,
                'changed_by' => Auth::guard('admin')->id() ?? Auth::guard('api')->id() ?? Auth::id(),
                'created_at' => now(),
            ]);
        }

        return $after->load(['service.category']);
    }

    public function deletePrice(RepairPrice $price): bool
    {
        return (bool) $price->delete();
    }

    public function normalizedSlug(string $value): string
    {
        return Str::slug($value);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 50);
    }
}
