<?php

namespace App\Http\Controllers\Api\RepairPrice;

use App\Http\Controllers\Controller;
use App\Http\Resources\RepairPrice\RepairBrandResource;
use App\Http\Resources\RepairPrice\RepairCategoryResource;
use App\Http\Resources\RepairPrice\RepairDeviceResource;
use App\Http\Resources\RepairPrice\RepairServicePriceResource;
use App\Models\RepairPrice\RepairDevice;
use App\Repositories\RepairPrice\RepairPriceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class RepairPriceController extends Controller
{
    public function __construct(private readonly RepairPriceRepository $repository)
    {
    }

    public function brands(): JsonResponse
    {
        $this->authorizeView();
        return $this->success('Repair brands loaded successfully', RepairBrandResource::collection($this->repository->brands()));
    }

    public function deviceTypes(): JsonResponse
    {
        $this->authorizeView();
        return $this->success('Repair device types loaded successfully', $this->repository->deviceTypes());
    }

    public function series(Request $request): JsonResponse
    {
        $this->authorizeView();
        return $this->success('Repair device series loaded successfully', $this->repository->series($request));
    }

    public function devices(Request $request): JsonResponse
    {
        $this->authorizeView();
        $devices = $this->repository->devices($request);
        return $this->paginated('Repair devices loaded successfully', RepairDeviceResource::collection($devices), $devices);
    }

    public function device(RepairDevice $device): JsonResponse
    {
        $this->authorizeView();
        $device->load(['brand', 'deviceType', 'series']);
        return $this->success('Repair device loaded successfully', new RepairDeviceResource($device));
    }

    public function categories(): JsonResponse
    {
        $this->authorizeView();
        return $this->success('Repair categories loaded successfully', RepairCategoryResource::collection($this->repository->categories()));
    }

    public function services(Request $request): JsonResponse
    {
        $this->authorizeView();
        $services = $this->repository->services($request);
        return $this->paginated('Repair services loaded successfully', $services->items(), $services);
    }

    public function deviceServices(RepairDevice $device, Request $request): JsonResponse
    {
        $this->authorizeView();
        $prices = $this->repository->deviceServices($device, $request);
        return $this->paginated('Repair prices loaded successfully', RepairServicePriceResource::collection($prices), $prices);
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorizeView();
        return $this->devices($request);
    }

    
    public function permissions(): JsonResponse
    {
        $user = Auth::user();
        $roleSlug = $user?->role?->slug ?? 'employee';
        $roleName = $user?->role?->name ?? 'Employee';
        $roleId = $user?->role_id ?? null;

        $isAdmin = in_array($roleSlug, ['admin', 'supper-admin']) || in_array($roleId, [1, 7]);

        $canView = $isAdmin || Gate::allows('repair_price.view');
        $canViewCost = $isAdmin || Gate::allows('repair_price.view_cost');
        $canManagePrices = $isAdmin || Gate::allows('repair_price.manage_prices');
        $canCreatePrices = $isAdmin || Gate::allows('repair_price.create');
        $canUpdatePrices = $isAdmin || Gate::allows('repair_price.update');
        $canDeletePrices = $isAdmin || Gate::allows('repair_price.delete');
        $canManageDevices = $isAdmin || Gate::allows('repair_price.manage_devices');
        $canManageServices = $isAdmin || Gate::allows('repair_price.manage_services');

        // Determine effective repair role: admin, manager, employee, or unauthorized
        if ($isAdmin || ($canManageDevices && $canManageServices)) {
            $effectiveRole = 'admin';
        } elseif ($canViewCost || $canManagePrices) {
            $effectiveRole = 'manager';
        } elseif ($canView) {
            $effectiveRole = 'employee';
        } else {
            $effectiveRole = 'unauthorized';
        }

        return $this->success('Repair permissions loaded successfully', [
            'role' => $effectiveRole,
            'role_name' => $roleName,
            'effective_role' => $effectiveRole,
            'is_admin' => $isAdmin,
            'can_view' => $canView,
            'can_view_cost' => $canViewCost,
            'can_manage_prices' => $canManagePrices,
            'can_create_prices' => $canCreatePrices,
            'can_update_prices' => $canUpdatePrices,
            'can_delete_prices' => $canDeletePrices,
            'can_manage_devices' => $canManageDevices,
            'can_manage_services' => $canManageServices,
        ]);
    }

    private function authorizeView(): void
    {
        abort_unless(Gate::allows('repair_price.view'), 403, 'You are not allowed to view repair prices.');
    }

    private function success(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function paginated(string $message, mixed $data, mixed $paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
