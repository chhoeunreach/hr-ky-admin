<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RepairPrice\RepairBrand;
use App\Models\RepairPrice\RepairCategory;
use App\Models\RepairPrice\RepairDevice;
use App\Models\RepairPrice\RepairDeviceSeries;
use App\Models\RepairPrice\RepairDeviceType;
use App\Models\RepairPrice\RepairPrice;
use App\Models\RepairPrice\RepairPriceHistory;
use App\Models\RepairPrice\RepairService;
use App\Imports\RepairPriceImport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class RepairPriceWebController extends Controller
{
    use \App\Http\Controllers\Web\Concerns\ValidatesRepairInput;

    private $view = 'admin.repairPrice.';

    public function index(Request $request)
    {
        abort_unless(Gate::allows('repair_price.view'), 403, __('message.unauthorized_access') ?? __('repair.unauthorized'));

        try {
            $tabs = ['prices', 'brands', 'categories', 'devices', 'services', 'types', 'series'];
            $activeTab = $request->input('active_tab');
            if (!in_array($activeTab, $tabs, true)) {
                $activeTab = collect($tabs)->first(fn ($tab) => $request->has($tab.'_page'), 'prices');
            }
            $filterParameters = [
                'brand_id' => $request->brand_id ?? null,
                'device_id' => $request->device_id ?? null,
                'category_id' => $request->category_id ?? null,
                'search' => $request->search ?? null,
                'active_tab' => $activeTab,
            ];

            $pageSize = (string) $request->input('per_page', '25');
            if (!in_array($pageSize, ['25', '50', '100', '200', 'all'], true)) $pageSize = '25';
            $filterParameters['per_page'] = $pageSize;
            $paginate = function ($query, $pageName) use ($pageSize) {
                return $query->paginate($pageSize === 'all' ? max(1, (clone $query)->count()) : (int) $pageSize, ['*'], $pageName);
            };
            $catalog = function ($query, array $paths, array $searchColumns) use ($request) {
                foreach ($paths as $parameter => [$relation, $column]) {
                    if (!$request->filled($parameter)) continue;
                    if ($relation) $query->whereHas($relation, fn ($q) => $q->where($column, $request->input($parameter)));
                    else $query->where($column, $request->input($parameter));
                }
                if ($request->filled('search')) {
                    $query->where(function ($q) use ($searchColumns, $request) {
                        foreach ($searchColumns as $column) $q->orWhere($column, 'like', '%'.$request->input('search').'%');
                    });
                }
                return $query;
            };
            $brands = RepairBrand::orderBy('name')->get();
            $deviceTypes = RepairDeviceType::orderBy('name')->get();
            $series = RepairDeviceSeries::with(['brand', 'deviceType'])->orderBy('name')->get();
            $devices = RepairDevice::with('brand')->orderBy('name')->get();
            $categories = RepairCategory::orderBy('sort_order')->get();
            $services = RepairService::with('category')->orderBy('name')->get();
            $brandsList = $paginate($catalog(RepairBrand::latest(), [
                'brand_id' => [null, 'id'], 'device_id' => ['devices', 'id'],
                'category_id' => ['devices.categories', 'repair_categories.id'],
            ], ['name', 'slug']), 'brands_page');
            $categoriesList = $paginate($catalog(RepairCategory::withCount('devices')->orderBy('sort_order')->latest(), [
                'brand_id' => ['devices', 'repair_brand_id'],
                'device_id' => ['devices', 'repair_devices.id'], 'category_id' => [null, 'id'],
            ], ['name', 'name_kh', 'slug']), 'categories_page');

            // 1. Price Query
            $pricesQuery = RepairPrice::with(['device.brand', 'device.series', 'service.category'])
                ->when($request->filled('brand_id'), function ($query) use ($request) {
                    $query->whereHas('device', fn ($q) => $q->where('repair_brand_id', $request->brand_id));
                })
                ->when($request->filled('device_id'), function ($query) use ($request) {
                    $query->where('repair_device_id', $request->device_id);
                })
                ->when($request->filled('category_id'), function ($query) use ($request) {
                    $query->whereHas('service', fn ($q) => $q->where('repair_category_id', $request->category_id));
                })
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->search;
                    $query->where(function ($sub) use ($search) {
                        $sub->where('part_name', 'like', "%{$search}%")
                            ->orWhere('part_type', 'like', "%{$search}%")
                            ->orWhereHas('device', fn ($d) => $d->where('name', 'like', "%{$search}%")->orWhere('model_number', 'like', "%{$search}%"))
                            ->orWhereHas('service', fn ($s) => $s->where('name', 'like', "%{$search}%")->orWhere('name_kh', 'like', "%{$search}%"));
                    });
                });

            $prices = $paginate($pricesQuery->latest(), 'page');
            $devicesList = $paginate($catalog(RepairDevice::with(['brand', 'deviceType', 'series', 'categories', 'prices.service'])->latest(), [
                'brand_id' => [null, 'repair_brand_id'], 'device_id' => [null, 'id'],
                'category_id' => ['categories', 'repair_categories.id'],
            ], ['name', 'model_number']), 'devices_page');
            $servicesList = $paginate($catalog(RepairService::with('category')->latest(), [
                'brand_id' => ['prices.device', 'repair_brand_id'], 'device_id' => ['prices', 'repair_device_id'],
                'category_id' => [null, 'repair_category_id'],
            ], ['name', 'name_kh', 'description']), 'services_page');
            $typesList = $paginate($catalog(RepairDeviceType::latest(), [
                'brand_id' => ['devices', 'repair_brand_id'], 'device_id' => ['devices', 'id'],
                'category_id' => ['devices.categories', 'repair_categories.id'],
            ], ['name', 'slug']), 'types_page');
            $seriesList = $paginate($catalog(RepairDeviceSeries::with(['brand', 'deviceType'])->latest(), [
                'brand_id' => [null, 'repair_brand_id'], 'device_id' => ['devices', 'id'],
                'category_id' => ['devices.categories', 'repair_categories.id'],
            ], ['name', 'slug']), 'series_page');
            foreach (['prices' => $prices, 'brands' => $brandsList, 'categories' => $categoriesList,
                'devices' => $devicesList, 'services' => $servicesList, 'types' => $typesList, 'series' => $seriesList] as $tab => $paginator) {
                $paginator->appends($request->only(['brand_id', 'device_id', 'category_id', 'search']))
                    ->appends(['active_tab' => $tab, 'per_page' => $pageSize]);
            }

            // Role & Permission flags
            $canViewCost = Gate::allows('repair_price.view_cost');
            $canManageDevices = Gate::allows('repair_price.manage_devices');
            $canManageServices = Gate::allows('repair_price.manage_services');
            $canManagePrices = Gate::allows('repair_price.manage_prices');

            return view($this->view . 'index', compact(
                'prices',
                'typesList',
                'seriesList',
                'devicesList',
                'servicesList',
                'brandsList',
                'categoriesList',
                'brands',
                'deviceTypes',
                'series',
                'devices',
                'categories',
                'services',
                'filterParameters',
                'canViewCost',
                'canManageDevices',
                'canManageServices',
                'canManagePrices'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function storeCategory(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_categories'), 403, __('repair.unauthorized'));

        if (!$request->filled('slug')) {
            $request->merge(['slug' => Str::slug($request->input('name', '')) ?: 'category-'.Str::lower(Str::random(8))]);
        }

        $validated = $this->validateRepairInput($request, [
            'name' => 'required|string|max:255|unique:repair_categories,name',
            'name_kh' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:repair_categories,slug',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            RepairCategory::create($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'categories'])
                ->with('success', __('repair.category_added'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateCategory(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_categories'), 403, __('repair.unauthorized'));

        $category = RepairCategory::findOrFail($id);

        if (!$request->filled('slug')) {
            $request->merge(['slug' => $category->slug]);
        }

        $validated = $this->validateRepairInput($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('repair_categories', 'name')->ignore($category->id)],
            'name_kh' => 'nullable|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('repair_categories', 'slug')->ignore($category->id)],
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            $category->update($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'categories'])
                ->with('success', __('repair.category_updated'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteCategory($id)
    {
        abort_unless(Gate::allows('repair_price.manage_categories'), 403, __('repair.unauthorized'));

        try {
            $category = RepairCategory::withCount('services')->findOrFail($id);

            if ($category->services_count > 0) {
                return redirect()->back()
                    ->with('danger', __('repair.category_used'));
            }

            $category->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'categories'])
                ->with('success', __('repair.category_deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storeBrand(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_brands'), 403, __('repair.unauthorized'));

        if (!$request->filled('slug')) {
            $request->merge(['slug' => Str::slug($request->input('name', '')) ?: 'brand-'.Str::lower(Str::random(8))]);
        }

        $validated = $this->validateRepairInput($request, [
            'name' => 'required|string|max:255|unique:repair_brands,name',
            'slug' => 'nullable|string|max:255|unique:repair_brands,slug',
            'logo' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            RepairBrand::create($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'brands'])
                ->with('success', __('repair.brand_added'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateBrand(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_brands'), 403, __('repair.unauthorized'));

        $brand = RepairBrand::findOrFail($id);

        if (!$request->filled('slug')) {
            $request->merge(['slug' => $brand->slug]);
        }

        $validated = $this->validateRepairInput($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('repair_brands', 'name')->ignore($brand->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('repair_brands', 'slug')->ignore($brand->id)],
            'logo' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            $brand->update($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'brands'])
                ->with('success', __('repair.brand_updated'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteBrand($id)
    {
        abort_unless(Gate::allows('repair_price.manage_brands'), 403, __('repair.unauthorized'));

        try {
            $brand = RepairBrand::withCount(['devices', 'series'])->findOrFail($id);

            if ($brand->devices_count > 0 || $brand->series_count > 0) {
                return redirect()->back()
                    ->with('danger', __('repair.brand_used'));
            }

            $brand->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'brands'])
                ->with('success', __('repair.brand_deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storeDevice(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, __('repair.unauthorized'));

        $validated = $this->validateRepairInput($request, [
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'repair_device_type_id' => 'required|exists:repair_device_types,id',
            'repair_device_series_id' => ['nullable', Rule::exists('repair_device_series', 'id')
                ->where('repair_brand_id', $request->repair_brand_id)
                ->where('repair_device_type_id', $request->repair_device_type_id)],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('repair_devices', 'name')->where(fn ($query) => $query->where('repair_brand_id', $request->repair_brand_id)),
            ],
            'model_number' => 'nullable|string|max:100',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|distinct|exists:repair_categories,id',
            'status' => 'nullable|boolean',
        ], [
            'name.unique' => __('repair.device_duplicate'),
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $inlinePrices = $this->validateDevicePrices($request);

        try {
            DB::transaction(function () use ($validated, $inlinePrices) {
                $categoryIds = $validated['category_ids'] ?? [];
                unset($validated['category_ids']);
                $device = RepairDevice::create($validated);
                $device->categories()->sync($categoryIds);
                $this->saveDevicePrices($device, $inlinePrices);
            });
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'devices'])
                ->with('success', __('repair.device_added'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateDevice(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, __('repair.unauthorized'));

        $device = RepairDevice::findOrFail($id);

        $validated = $this->validateRepairInput($request, [
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'repair_device_type_id' => 'required|exists:repair_device_types,id',
            'repair_device_series_id' => ['nullable', Rule::exists('repair_device_series', 'id')
                ->where('repair_brand_id', $request->repair_brand_id)
                ->where('repair_device_type_id', $request->repair_device_type_id)],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('repair_devices', 'name')
                    ->where(fn ($query) => $query->where('repair_brand_id', $request->repair_brand_id))
                    ->ignore($device->id),
            ],
            'model_number' => 'nullable|string|max:100',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|distinct|exists:repair_categories,id',
            'status' => 'nullable|boolean',
        ], [
            'name.unique' => __('repair.device_duplicate'),
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;
        $inlinePrices = $this->validateDevicePrices($request, $device);

        try {
            DB::transaction(function () use ($device, $validated, $inlinePrices) {
                $categoryIds = $validated['category_ids'] ?? [];
                unset($validated['category_ids']);
                RepairDevice::whereKey($device->id)->lockForUpdate()->firstOrFail();
                $device->update($validated);
                $device->categories()->sync($categoryIds);
                $this->saveDevicePrices($device, $inlinePrices);
            });
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'devices'])
                ->with('success', __('repair.device_updated'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    private function validateDevicePrices(Request $request, ?RepairDevice $device = null): array
    {
        $data = $this->validateRepairInput($request, [
            'new_prices' => 'sometimes|array',
            'new_prices.*' => 'nullable|numeric|min:0',
            'existing_prices' => 'sometimes|array',
            'existing_prices.*' => 'required|numeric|min:0',
        ]);
        $selected = array_map('strval', $request->input('category_ids', []) ?? []);
        foreach ($data['new_prices'] ?? [] as $serviceId => $amount) {
            if ($amount === null) continue;
            $service = ctype_digit((string) $serviceId) ? RepairService::find($serviceId) : null;
            if (!$service || !in_array((string) $service->repair_category_id, $selected, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['new_prices.'.$serviceId => __('repair.invalid_category_price')]);
            }
            abort_unless(Gate::any(['repair_price.create', 'repair_price.manage_prices']), 403, __('repair.unauthorized'));
        }
        foreach ($data['existing_prices'] ?? [] as $priceId => $amount) {
            $price = $device && ctype_digit((string) $priceId) ? $device->prices()->with('service')->find($priceId) : null;
            if (!$price || !in_array((string) $price->service->repair_category_id, $selected, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['existing_prices.'.$priceId => __('repair.invalid_category_price')]);
            }
            abort_unless(Gate::any(['repair_price.update', 'repair_price.manage_prices']), 403, __('repair.unauthorized'));
        }
        return $data;
    }

    private function saveDevicePrices(RepairDevice $device, array $data): void
    {
        foreach ($data['new_prices'] ?? [] as $serviceId => $amount) {
            if ($amount === null) continue;
            if ($device->prices()->where('repair_service_id', $serviceId)->whereNull('part_type')->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['new_prices.'.$serviceId => __('repair.price_duplicate')]);
            }
            $device->prices()->create([
                'repair_service_id' => $serviceId, 'selling_price' => $amount,
                'part_cost' => 0, 'service_fee' => 0, 'availability_status' => 'available',
            ]);
        }
        foreach ($data['existing_prices'] ?? [] as $priceId => $amount) {
            $price = $device->prices()->lockForUpdate()->findOrFail($priceId);
            if ((float) $price->selling_price === (float) $amount) continue;
            $oldPrice = $price->selling_price;
            $price->update(['selling_price' => $amount]);
            $price->histories()->create([
                'old_part_cost' => $price->part_cost, 'new_part_cost' => $price->part_cost,
                'old_service_fee' => $price->service_fee, 'new_service_fee' => $price->service_fee,
                'old_selling_price' => $oldPrice, 'new_selling_price' => $price->selling_price,
                'changed_by' => Auth::guard('admin')->id() ?? Auth::id(),
                'created_at' => now(),
            ]);
        }
    }

    public function deleteDevice($id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, __('repair.unauthorized'));

        try {
            $device = RepairDevice::findOrFail($id);
            if ($device->prices()->exists()) {
                return redirect()->back()->with('danger', __('repair.device_used'));
            }
            $device->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'devices'])
                ->with('success', __('repair.device_deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storeService(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, __('repair.unauthorized'));

        $validated = $this->validateRepairInput($request, [
            'repair_category_id' => 'required|exists:repair_categories,id',
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            RepairService::create($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'services'])
                ->with('success', __('repair.service_added'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateService(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, __('repair.unauthorized'));

        $service = RepairService::findOrFail($id);

        $validated = $this->validateRepairInput($request, [
            'repair_category_id' => 'required|exists:repair_categories,id',
            'name' => 'required|string|max:255',
            'name_kh' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            $service->update($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'services'])
                ->with('success', __('repair.service_updated'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteService($id)
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, __('repair.unauthorized'));

        try {
            $service = RepairService::findOrFail($id);
            if ($service->prices()->exists()) {
                return redirect()->back()->with('danger', __('repair.service_used'));
            }
            $service->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'services'])
                ->with('success', __('repair.service_deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storePrice(Request $request)
    {
        abort_unless(Gate::any(['repair_price.create', 'repair_price.manage_prices']), 403, __('repair.unauthorized'));

        $validated = $this->validateRepairInput($request, [
            'repair_device_id' => ['required_without:repair_device_ids', Rule::prohibitedIf($request->has('repair_device_ids')), 'integer', 'exists:repair_devices,id'],
            'repair_device_ids' => 'required_without:repair_device_id|array|min:1',
            'repair_device_ids.*' => 'required|integer|distinct|exists:repair_devices,id',
            'source_price_id' => 'nullable|integer|exists:repair_prices,id',
            'repair_category_id' => 'sometimes|required|exists:repair_categories,id',
            'repair_service_id' => ['required_without:repair_service_ids', Rule::prohibitedIf($request->has('repair_service_ids')), Rule::exists('repair_services', 'id')
                ->when($request->filled('repair_category_id'), fn ($rule) => $rule->where('repair_category_id', $request->repair_category_id))],
            'repair_service_ids' => 'required_without:repair_service_id|array|min:1',
            'repair_service_ids.*' => ['required', 'integer', 'distinct', Rule::exists('repair_services', 'id')
                ->when($request->filled('repair_category_id'), fn ($rule) => $rule->where('repair_category_id', $request->repair_category_id))],
            'per_pair_pricing' => 'sometimes|in:1',
            'pair_prices' => 'required_with:repair_service_ids|required_if:per_pair_pricing,1|array',
            'pair_prices.*' => 'array',
            'pair_prices.*.*' => 'array:part_cost,service_fee,selling_price',
            'pair_prices.*.*.part_cost' => 'nullable|numeric|min:0',
            'pair_prices.*.*.service_fee' => 'nullable|numeric|min:0',
            'pair_prices.*.*.selling_price' => 'required|numeric|min:0',
            'part_name' => 'nullable|string|max:255',
            'part_type' => 'nullable|string|max:100',
            'part_cost' => 'nullable|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'selling_price' => 'required_without_all:model_prices,pair_prices|numeric|min:0',
            'per_model_pricing' => 'sometimes|in:1',
            'model_prices' => ['required_if:per_model_pricing,1', Rule::prohibitedIf($request->has('repair_service_ids')), 'array'],
            'model_prices.*' => 'array:part_cost,service_fee,selling_price',
            'model_prices.*.part_cost' => 'nullable|numeric|min:0',
            'model_prices.*.service_fee' => 'nullable|numeric|min:0',
            'model_prices.*.selling_price' => 'required|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0',
            'availability_status' => 'required|in:available,out_of_stock,pre_order',
            'note' => 'nullable|string|max:10000',
        ]);

        $deviceIds = $validated['repair_device_ids'] ?? [$validated['repair_device_id']];
        $serviceIds = $validated['repair_service_ids'] ?? [$validated['repair_service_id']];
        if (isset($validated['model_prices']) || isset($validated['per_model_pricing'])) {
            $rules = [];
            foreach ($deviceIds as $deviceId) {
                $rules['model_prices.'.$deviceId] = 'required|array';
                $rules['model_prices.'.$deviceId.'.selling_price'] = 'required|numeric|min:0';
            }
            $this->validateRepairInput($request, $rules);
            if (array_diff(array_keys($validated['model_prices'] ?? []), $deviceIds)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['model_prices' => __('repair.only_selected_model_prices')]);
            }
        }

        if (isset($validated['pair_prices']) || isset($validated['per_pair_pricing']) || isset($validated['repair_service_ids'])) {
            $rules = [];
            foreach ($deviceIds as $deviceId) {
                foreach ($serviceIds as $serviceId) {
                    $rules['pair_prices.'.$deviceId.'.'.$serviceId] = 'required|array';
                    $rules['pair_prices.'.$deviceId.'.'.$serviceId.'.selling_price'] = 'required|numeric|min:0';
                }
            }
            $this->validateRepairInput($request, $rules);
            if (array_diff(array_keys($validated['pair_prices'] ?? []), $deviceIds)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['pair_prices' => __('repair.only_selected_pair_prices')]);
            }
            foreach ($validated['pair_prices'] ?? [] as $prices) {
                if (array_diff(array_keys($prices), $serviceIds)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['pair_prices' => __('repair.only_selected_pair_prices')]);
                }
            }
        }

        if (!empty($validated['source_price_id'])) {
            abort_unless(Gate::allows('repair_price.view'), 403, __('repair.unauthorized'));
        }

        try {
            $count = DB::transaction(function () use ($validated, $deviceIds, $serviceIds) {
                // Lock models consistently so concurrent batch submissions cannot create duplicates.
                $devices = RepairDevice::whereIn('id', $deviceIds)->orderBy('id')->lockForUpdate()->get();
                $conflicts = RepairPrice::with(['device:id,name', 'service:id,name'])
                    ->whereIn('repair_device_id', $deviceIds)
                    ->whereIn('repair_service_id', $serviceIds)
                    ->where('part_type', $validated['part_type'] ?? null)->get();
                if ($conflicts->isNotEmpty()) {
                    throw new \RuntimeException(__('repair.price_models_conflict', [
                        'models' => $conflicts->map(fn ($price) => $price->device->name.(count($serviceIds) > 1 ? ' — '.$price->service->name : ''))->unique()->implode(', '),
                    ]));
                }

                $data = $validated;
                $data['part_cost'] = $data['part_cost'] ?? 0;
                $data['service_fee'] = $data['service_fee'] ?? 0;
                $source = !empty($data['source_price_id']) && !Gate::allows('repair_price.view_cost')
                    ? RepairPrice::findOrFail($data['source_price_id']) : null;
                unset($data['repair_device_ids'], $data['repair_device_id'], $data['repair_category_id'], $data['source_price_id'],
                    $data['model_prices'], $data['per_model_pricing'], $data['repair_service_ids'], $data['pair_prices'], $data['per_pair_pricing']);
                $services = RepairService::whereIn('id', $serviceIds)->orderBy('id')->get();
                foreach ($devices as $device) {
                    foreach ($services as $service) {
                        $amounts = $validated['pair_prices'][$device->id][$service->id] ?? $validated['model_prices'][$device->id] ?? [];
                        $modelData = array_replace($data, $amounts);
                        $modelData['repair_service_id'] = $service->id;
                        $modelData['part_cost'] = $modelData['part_cost'] ?? 0;
                        $modelData['service_fee'] = $modelData['service_fee'] ?? 0;
                        if ($source && $source->repair_service_id === $service->id) {
                            $modelData['part_cost'] = $source->part_cost;
                            $modelData['service_fee'] = $source->service_fee;
                        }
                        $device->prices()->create($modelData);
                    }
                    $device->categories()->syncWithoutDetaching($services->pluck('repair_category_id')->unique()->all());
                }
                return $devices->count() * $services->count();
            });
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', $count === 1 ? __('repair.price_added') : (count($serviceIds) > 1
                    ? __('repair.prices_added_to_pairs', ['count' => $count, 'models' => count($deviceIds), 'services' => count($serviceIds)])
                    : __('repair.prices_added_to_models', ['count' => $count])));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updatePrice(Request $request, $id)
    {
        abort_unless(Gate::any(['repair_price.update', 'repair_price.manage_prices']), 403, __('repair.unauthorized'));

        $price = RepairPrice::findOrFail($id);

        $validated = $this->validateRepairInput($request, [
            'repair_device_id' => 'required|exists:repair_devices,id',
            'repair_category_id' => 'sometimes|required|exists:repair_categories,id',
            'repair_service_id' => ['required', Rule::exists('repair_services', 'id')
                ->when($request->filled('repair_category_id'), fn ($rule) => $rule->where('repair_category_id', $request->repair_category_id))],
            'note' => 'nullable|string|max:10000',
            'part_name' => 'nullable|string|max:255',
            'part_type' => 'nullable|string|max:100',
            'part_cost' => 'nullable|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0',
            'availability_status' => 'required|in:available,out_of_stock,pre_order',
        ]);

        $exists = RepairPrice::where('repair_device_id', $request->repair_device_id)
            ->where('repair_service_id', $request->repair_service_id)
            ->where('part_type', $request->part_type)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('danger', __('repair.price_conflict'))
                ->withInput();
        }

        try {
            $validated['part_cost'] = $validated['part_cost'] ?? 0;
            $validated['service_fee'] = $validated['service_fee'] ?? 0;

            DB::transaction(function () use ($price, $validated) {
                unset($validated['repair_category_id']);
                $before = $price->only(['part_cost', 'service_fee', 'selling_price']);
                $price->update($validated);
                $price->unsetRelation('device')->unsetRelation('service');
                $price->device->categories()->syncWithoutDetaching([$price->service->repair_category_id]);

                if ($before['part_cost'] != $price->part_cost || $before['service_fee'] != $price->service_fee || $before['selling_price'] != $price->selling_price) {
                    RepairPriceHistory::create([
                        'repair_price_id' => $price->id,
                        'old_part_cost' => $before['part_cost'],
                        'new_part_cost' => $price->part_cost,
                        'old_service_fee' => $before['service_fee'],
                        'new_service_fee' => $price->service_fee,
                        'old_selling_price' => $before['selling_price'],
                        'new_selling_price' => $price->selling_price,
                        'changed_by' => Auth::guard('admin')->id() ?? Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            });

            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', __('repair.price_updated'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deletePrice($id)
    {
        abort_unless(Gate::any(['repair_price.delete', 'repair_price.manage_prices']), 403, __('repair.unauthorized'));

        try {
            $price = RepairPrice::findOrFail($id);
            $price->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', __('repair.price_deleted'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function getSeriesByBrand($brandId)
    {
        $series = RepairDeviceSeries::where('repair_brand_id', $brandId)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($series);
    }

    public function import(): \Illuminate\View\View
    {
        abort_unless(Gate::allows('repair_price.manage_prices'), 403, __('repair.unauthorized'));
        return view($this->view . 'import');
    }

    public function processImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(Gate::allows('repair_price.manage_prices'), 403, __('repair.unauthorized'));

        $this->validateRepairInput($request, [
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'update_existing' => 'nullable|boolean',
        ]);

        try {
            $updateExisting = $request->boolean('update_existing', true);
            $import = new RepairPriceImport($updateExisting);

            DB::transaction(function () use ($import, $request) {
                Excel::import($import, $request->file('file'));
            });

            $inserted = $import->insertedCount();
            $updated = $import->updatedCount();

            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', __('repair.import_success', ['inserted' => $inserted, 'updated' => $updated]));
        } catch (Exception $e) {
            return redirect()->back()
                ->with('danger', __('repair.import_failed', ['error' => $e->getMessage()]))
                ->withInput();
        }
    }

    public function downloadSample(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(Gate::allows('repair_price.view'), 403, __('repair.unauthorized'));

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="repair_prices_sample_template.csv"',
        ];

        $columns = [
            'brand',
            'device_type',
            'model',
            'model_number',
            'category',
            'category_kh',
            'service',
            'service_kh',
            'description',
            'part_name',
            'part_type',
            'part_cost',
            'service_fee',
            'selling_price',
            'warranty',
            'status',
        ];

        $sampleRows = [
            [
                'Apple', 'Phone', 'iPhone 15 Pro Max', 'A3106', 'Screen', 'អេក្រង់', 'Screen Replacement', 'ដូរអេក្រង់', 'High quality OLED assembly replacement', 'OLED Screen Assembly', 'Original', '180.00', '20.00', '220.00', '3 Months', 'available'
            ],
            [
                'Apple', 'Phone', 'iPhone 15 Pro Max', 'A3106', 'Battery', 'ថ្ម', 'Battery Replacement', 'ដូរថ្ម', 'Original spec battery replacement', 'Battery Cell', 'OEM', '45.00', '15.00', '65.00', '3 Months', 'available'
            ],
            [
                'Samsung', 'Phone', 'Galaxy S24 Ultra', 'SM-S928B', 'Screen', 'អេក្រង់', 'Screen Replacement', 'ដូរអេក្រង់', 'Dynamic AMOLED 2X display replacement', 'AMOLED Assembly', 'Original', '195.00', '25.00', '240.00', '3 Months', 'available'
            ],
            [
                'Samsung', 'Phone', 'Galaxy S24 Ultra', 'SM-S928B', 'Battery', 'ថ្ម', 'Battery Replacement', 'ដូរថ្ម', '5000mAh battery replacement', 'Battery Pack', 'Original', '48.00', '12.00', '65.00', '3 Months', 'available'
            ],
            [
                'Apple', 'Laptop', 'MacBook Air M2', 'A2681', 'Keyboard', 'ក្តារចុច', 'Keyboard Replacement', 'ដូរក្តារចុច', 'Complete keyboard top case assembly', 'Keyboard Assembly', 'Original', '95.00', '25.00', '120.00', '1 Month', 'available'
            ]
        ];

        return response()->stream(function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, $headers);
    }
}
