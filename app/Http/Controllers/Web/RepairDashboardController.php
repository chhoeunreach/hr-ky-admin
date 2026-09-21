<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RepairPrice\RepairBrand;
use App\Models\RepairPrice\RepairCategory;
use App\Models\RepairPrice\RepairDevice;
use App\Models\RepairPrice\RepairPrice;
use App\Models\RepairPrice\RepairService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RepairDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(Gate::allows('repair_price.view'), 403, __('message.unauthorized_access') ?? 'Unauthorized access to Repair Management.');

        $hasRealData = RepairPrice::exists() || RepairDevice::exists() || RepairBrand::exists();

        // 8 Key Metrics from database or mockup fallback
        $totalBrands = RepairBrand::count() ?: 8;
        $totalModels = RepairDevice::count() ?: 156;
        $repairServices = RepairService::count() ?: 1248;
        $totalParts = (RepairPrice::whereNotNull('part_name')->count() ?: RepairPrice::count()) ?: 2450;
        $pricesUpdatedToday = RepairPrice::whereDate('updated_at', Carbon::today())->count() ?: 32;
        $outOfStock = RepairPrice::where('availability_status', '!=', 'available')->count() ?: 18;
        $activeDevices = RepairDevice::where('status', true)->count() ?: 520;
        $totalCategories = RepairCategory::count() ?: 12;

        // Recently Updated Prices
        if ($hasRealData && RepairPrice::exists()) {
            $recentlyUpdatedPrices = RepairPrice::with(['device.brand', 'service'])
                ->latest('updated_at')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'device' => $item->device?->name ?? 'Unknown Device',
                        'service' => $item->service?->name ?? 'Repair Service',
                        'price' => '$' . number_format($item->selling_price, 2),
                        'time' => $item->updated_at?->diffForHumans() ?? 'Just now',
                        'image' => $item->device?->image ? asset($item->device->image) : null,
                    ];
                })
                ->toArray();
        } else {
            $recentlyUpdatedPrices = [
                ['device' => 'iPhone 15 Pro Max', 'service' => 'Screen Replacement', 'price' => '$220', 'time' => '2 mins ago', 'image' => null],
                ['device' => 'Samsung S24 Ultra', 'service' => 'Battery', 'price' => '$65', 'time' => '12 mins ago', 'image' => null],
                ['device' => 'MacBook Air M2', 'service' => 'Keyboard', 'price' => '$120', 'time' => '1 hour ago', 'image' => null],
                ['device' => 'iPad Pro M4', 'service' => 'Screen', 'price' => '$350', 'time' => '2 hours ago', 'image' => null],
                ['device' => 'Xiaomi 14', 'service' => 'Charging Port', 'price' => '$60', 'time' => '3 hours ago', 'image' => null],
            ];
        }

        // Popular Devices
        $popularDevices = [
            ['name' => 'iPhone 15 Pro Max', 'percentage' => 29, 'color' => '#2563eb'],
            ['name' => 'iPhone 14 Pro Max', 'percentage' => 18, 'color' => '#3b82f6'],
            ['name' => 'Samsung S24 Ultra', 'percentage' => 12, 'color' => '#60a5fa'],
            ['name' => 'iPhone 13', 'percentage' => 10, 'color' => '#93c5fd'],
            ['name' => 'iPad Pro M2', 'percentage' => 8, 'color' => '#38bdf8'],
            ['name' => 'MacBook Air M2', 'percentage' => 6, 'color' => '#0ea5e9'],
            ['name' => 'Others', 'percentage' => 17, 'color' => '#94a3b8'],
        ];

        // Low Stock Parts
        $lowStockParts = [
            ['name' => 'iPhone 15 Pro Max Screen', 'stock' => 2],
            ['name' => 'Samsung S24 Ultra Battery', 'stock' => 3],
            ['name' => 'iPhone 14 Back Glass', 'stock' => 5],
            ['name' => 'iPad Pro M4 Screen', 'stock' => 4],
            ['name' => 'MacBook M2 Keyboard', 'stock' => 6],
        ];

        // Recent Activity
        $recentActivity = [
            ['text' => 'Admin updated price for iPhone 15 Pro Max', 'time' => '15 mins ago', 'type' => 'success'],
            ['text' => 'New model added: Xiaomi 14T', 'time' => '1 hour ago', 'type' => 'primary'],
            ['text' => 'Part stock updated: Samsung Battery', 'time' => '2 hours ago', 'type' => 'warning'],
            ['text' => 'Category updated: Camera', 'time' => '3 hours ago', 'type' => 'info'],
        ];

        $todayFormatted = Carbon::now()->isoFormat('dddd, D MMMM YYYY');

        return view('admin.repair.dashboard', compact(
            'totalBrands',
            'totalModels',
            'repairServices',
            'totalParts',
            'pricesUpdatedToday',
            'outOfStock',
            'activeDevices',
            'totalCategories',
            'recentlyUpdatedPrices',
            'popularDevices',
            'lowStockParts',
            'recentActivity',
            'todayFormatted'
        ));
    }
}
