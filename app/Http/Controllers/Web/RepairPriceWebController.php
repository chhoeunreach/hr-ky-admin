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
    private $view = 'admin.repairPrice.';

    public function index(Request $request)
    {
        abort_unless(Gate::allows('repair_price.view'), 403, __('message.unauthorized_access') ?? 'Unauthorized access.');

        try {
            $filterParameters = [
                'brand_id' => $request->brand_id ?? null,
                'device_id' => $request->device_id ?? null,
                'category_id' => $request->category_id ?? null,
                'search' => $request->search ?? null,
                'active_tab' => $request->active_tab ?? 'prices',
            ];

            $brands = RepairBrand::where('status', true)->orderBy('name')->get();
            $brandsList = RepairBrand::latest()->paginate(15, ['*'], 'brands_page');
            $deviceTypes = RepairDeviceType::where('status', true)->orderBy('name')->get();
            $series = RepairDeviceSeries::where('status', true)->orderBy('name')->get();
            $devices = RepairDevice::where('status', true)->orderBy('name')->get();
            $categories = RepairCategory::where('status', true)->orderBy('sort_order')->get();
            $categoriesList = RepairCategory::orderBy('sort_order')->latest()->paginate(15, ['*'], 'categories_page');
            $services = RepairService::where('status', true)->orderBy('name')->get();

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

            $prices = $pricesQuery->latest()->paginate(15)->appends($request->all());

            // 2. Devices Query (for Tab 2)
            $devicesList = RepairDevice::with(['brand', 'deviceType', 'series'])
                ->when($request->filled('brand_id'), fn ($q) => $q->where('repair_brand_id', $request->brand_id))
                ->latest()
                ->paginate(15, ['*'], 'devices_page');

            // 3. Services Query (for Tab 3)
            $servicesList = RepairService::with('category')
                ->when($request->filled('category_id'), fn ($q) => $q->where('repair_category_id', $request->category_id))
                ->latest()
                ->paginate(15, ['*'], 'services_page');

            // Role & Permission flags
            $canViewCost = Gate::allows('repair_price.view_cost');
            $canManageDevices = Gate::allows('repair_price.manage_devices');
            $canManageServices = Gate::allows('repair_price.manage_services');
            $canManagePrices = Gate::allows('repair_price.manage_prices');

            return view($this->view . 'index', compact(
                'prices',
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
        abort_unless(Gate::allows('repair_price.manage_categories'), 403, 'Unauthorized to manage categories.');

        $validated = $request->validate([
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
                ->with('success', 'Category added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateCategory(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_categories'), 403, 'Unauthorized to manage categories.');

        $category = RepairCategory::findOrFail($id);

        $validated = $request->validate([
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
                ->with('success', 'Category updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteCategory($id)
    {
        abort_unless(Gate::allows('repair_price.manage_categories'), 403, 'Unauthorized to manage categories.');

        try {
            $category = RepairCategory::withCount('services')->findOrFail($id);

            if ($category->services_count > 0) {
                return redirect()->back()
                    ->with('danger', 'This category is used by services. Please move/delete those services first.');
            }

            $category->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'categories'])
                ->with('success', 'Category deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storeBrand(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_brands'), 403, 'Unauthorized to manage brands.');

        $validated = $request->validate([
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
                ->with('success', 'Brand added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateBrand(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_brands'), 403, 'Unauthorized to manage brands.');

        $brand = RepairBrand::findOrFail($id);

        $validated = $request->validate([
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
                ->with('success', 'Brand updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteBrand($id)
    {
        abort_unless(Gate::allows('repair_price.manage_brands'), 403, 'Unauthorized to manage brands.');

        try {
            $brand = RepairBrand::withCount(['devices', 'series'])->findOrFail($id);

            if ($brand->devices_count > 0 || $brand->series_count > 0) {
                return redirect()->back()
                    ->with('danger', 'This brand is used by devices or series. Please move/delete those records first.');
            }

            $brand->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'brands'])
                ->with('success', 'Brand deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storeDevice(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, 'Unauthorized to manage devices.');

        $validated = $request->validate([
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'repair_device_type_id' => 'required|exists:repair_device_types,id',
            'repair_device_series_id' => 'nullable|exists:repair_device_series,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('repair_devices', 'name')->where(fn ($query) => $query->where('repair_brand_id', $request->repair_brand_id)),
            ],
            'model_number' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
        ], [
            'name.unique' => 'A device model with this name already exists for this brand.',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            RepairDevice::create($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'devices'])
                ->with('success', 'Device model added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateDevice(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, 'Unauthorized to manage devices.');

        $device = RepairDevice::findOrFail($id);

        $validated = $request->validate([
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'repair_device_type_id' => 'required|exists:repair_device_types,id',
            'repair_device_series_id' => 'nullable|exists:repair_device_series,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('repair_devices', 'name')
                    ->where(fn ($query) => $query->where('repair_brand_id', $request->repair_brand_id))
                    ->ignore($device->id),
            ],
            'model_number' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
        ], [
            'name.unique' => 'A device model with this name already exists for this brand.',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;

        try {
            $device->update($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'devices'])
                ->with('success', 'Device model updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteDevice($id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, 'Unauthorized to manage devices.');

        try {
            $device = RepairDevice::findOrFail($id);
            $device->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'devices'])
                ->with('success', 'Device model deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storeService(Request $request)
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, 'Unauthorized to manage services.');

        $validated = $request->validate([
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
                ->with('success', 'Repair service added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updateService(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, 'Unauthorized to manage services.');

        $service = RepairService::findOrFail($id);

        $validated = $request->validate([
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
                ->with('success', 'Repair service updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deleteService($id)
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, 'Unauthorized to manage services.');

        try {
            $service = RepairService::findOrFail($id);
            $service->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'services'])
                ->with('success', 'Repair service deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage());
        }
    }

    public function storePrice(Request $request)
    {
        abort_unless(Gate::allows('repair_price.create'), 403, 'Unauthorized to create repair prices.');

        $validated = $request->validate([
            'repair_device_id' => 'required|exists:repair_devices,id',
            'repair_service_id' => 'required|exists:repair_services,id',
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
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('danger', 'A repair price for this device model, service, and part type already exists. Please edit the existing price.')
                ->withInput();
        }

        try {
            $validated['part_cost'] = $validated['part_cost'] ?? 0;
            $validated['service_fee'] = $validated['service_fee'] ?? 0;

            RepairPrice::create($validated);
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', 'Repair price added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function updatePrice(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.update'), 403, 'Unauthorized to update repair prices.');

        $price = RepairPrice::findOrFail($id);

        $validated = $request->validate([
            'repair_device_id' => 'required|exists:repair_devices,id',
            'repair_service_id' => 'required|exists:repair_services,id',
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
                ->with('danger', 'Another repair price with this device model, service, and part type already exists.')
                ->withInput();
        }

        try {
            $validated['part_cost'] = $validated['part_cost'] ?? 0;
            $validated['service_fee'] = $validated['service_fee'] ?? 0;

            $before = $price->only(['part_cost', 'service_fee', 'selling_price']);
            $price->update($validated);

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

            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', 'Repair price updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    public function deletePrice($id)
    {
        abort_unless(Gate::allows('repair_price.delete'), 403, 'Unauthorized to delete repair prices.');

        try {
            $price = RepairPrice::findOrFail($id);
            $price->delete();
            return redirect()->route('admin.repair-price.index', ['active_tab' => 'prices'])
                ->with('success', 'Repair price deleted successfully.');
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
        abort_unless(Gate::allows('repair_price.manage_prices'), 403, 'Unauthorized to import repair prices.');
        return view($this->view . 'import');
    }

    public function processImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless(Gate::allows('repair_price.manage_prices'), 403, 'Unauthorized to import repair prices.');

        $request->validate([
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
                ->with('success', "Import completed successfully! Inserted: {$inserted} new records, Updated: {$updated} existing records.");
        } catch (Exception $e) {
            return redirect()->back()
                ->with('danger', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadSample(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(Gate::allows('repair_price.view'), 403, 'Unauthorized.');

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
