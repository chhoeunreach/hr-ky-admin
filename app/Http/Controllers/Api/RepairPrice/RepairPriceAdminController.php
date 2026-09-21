<?php

namespace App\Http\Controllers\Api\RepairPrice;

use App\Http\Controllers\Controller;
use App\Http\Requests\RepairPrice\RepairDeviceRequest;
use App\Http\Requests\RepairPrice\RepairPriceRequest;
use App\Http\Requests\RepairPrice\RepairServiceRequest;
use App\Http\Resources\RepairPrice\RepairDeviceResource;
use App\Http\Resources\RepairPrice\RepairServicePriceResource;
use App\Models\RepairPrice\RepairDevice;
use App\Models\RepairPrice\RepairPrice;
use App\Models\RepairPrice\RepairService;
use App\Repositories\RepairPrice\RepairPriceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RepairPriceAdminController extends Controller
{
    public function __construct(private readonly RepairPriceRepository $repository)
    {
    }

    public function storeDevice(RepairDeviceRequest $request): JsonResponse
    {
        return $this->success('Repair device created successfully', new RepairDeviceResource($this->repository->storeDevice($request->validated())));
    }

    public function updateDevice(RepairDeviceRequest $request, RepairDevice $device): JsonResponse
    {
        return $this->success('Repair device updated successfully', new RepairDeviceResource($this->repository->updateDevice($device, $request->validated())));
    }

    public function destroyDevice(RepairDevice $device): JsonResponse
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403, 'Unauthorized to manage devices.');

        $this->repository->deleteDevice($device);
        return $this->success('Repair device deleted successfully', ['id' => $device->id]);
    }

    public function storeService(RepairServiceRequest $request): JsonResponse
    {
        return $this->success('Repair service created successfully', $this->repository->storeService($request->validated()));
    }

    public function updateService(RepairServiceRequest $request, RepairService $service): JsonResponse
    {
        return $this->success('Repair service updated successfully', $this->repository->updateService($service, $request->validated()));
    }

    public function destroyService(RepairService $service): JsonResponse
    {
        abort_unless(Gate::allows('repair_price.manage_services'), 403, 'Unauthorized to manage services.');

        $this->repository->deleteService($service);
        return $this->success('Repair service deleted successfully', ['id' => $service->id]);
    }

    public function storePrice(RepairPriceRequest $request): JsonResponse
    {
        return $this->success('Repair price created successfully', new RepairServicePriceResource($this->repository->storePrice($request->validated())));
    }

    public function updatePrice(RepairPriceRequest $request, RepairPrice $price): JsonResponse
    {
        return $this->success('Repair price updated successfully', new RepairServicePriceResource($this->repository->updatePrice($price, $request->validated())));
    }

    public function destroyPrice(RepairPrice $price): JsonResponse
    {
        abort_unless(Gate::allows('repair_price.delete'), 403, 'Unauthorized to delete repair prices.');

        $this->repository->deletePrice($price);
        return $this->success('Repair price deleted successfully', ['id' => $price->id]);
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
}
